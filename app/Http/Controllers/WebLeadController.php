<?php

namespace App\Http\Controllers;

use App\Models\WebLead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WebLeadController extends Controller
{
    public function storePublic(Request $request): JsonResponse
    {
        $this->ensureValidToken($request);

        $validated = validator(
            $this->normalizeIncomingPayload($request),
            [
                'source_type' => ['required', Rule::in(array_keys(WebLead::sourceLabels()))],
                'full_name' => ['required', 'string', 'max:255'],
                'email' => ['nullable', 'email', 'max:255', 'required_without:phone'],
                'phone' => ['nullable', 'string', 'max:50', 'required_without:email'],
                'country' => ['nullable', 'string', 'max:255'],
                'city' => ['nullable', 'string', 'max:255'],
                'area' => ['nullable', 'string', 'max:255'],
                'interested_program' => ['nullable', 'string', 'max:255'],
                'preferred_campus' => ['nullable', 'string', 'max:255'],
                'teaching_method' => ['nullable', Rule::in(['online', 'campus', 'hybrid'])],
                'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
                'message' => ['nullable', 'string'],
                'submitted_at' => ['nullable', 'date'],
            ]
        )->validate();

        $webLead = WebLead::create([
            ...$validated,
            'source_site' => config('services.web_leads.source_site', 'career.edu.pk'),
            'status' => WebLead::STATUS_NEW,
            'submitted_at' => $validated['submitted_at'] ?? now(),
            'payload' => $request->all(),
        ]);

        return response()->json([
            'message' => 'Web lead saved successfully.',
            'id' => $webLead->id,
            'status' => $webLead->status,
        ], 201);
    }

    public function index(Request $request): View
    {
        $webLeads = WebLead::query()
            ->whereIn('status', [WebLead::STATUS_NEW, WebLead::STATUS_NOT_INTERESTED])
            ->with(['convertedLead', 'handledBy'])
            ->latest('submitted_at')
            ->latest('id')
            ->get();

        $tabs = [
            WebLead::SOURCE_QUICK_LEAD => 'Quick Lead',
            WebLead::SOURCE_WEBSITE_ENROLLMENT => 'Website Enrollment',
            WebLead::SOURCE_WEBSITE_ADMISSION => 'Website Admissions',
            WebLead::SOURCE_BROCHURE_DOWNLOAD => 'Brochure Download',
            'web_not_interest' => 'Web Not Interest',
        ];

        $badgeColors = [
            WebLead::SOURCE_QUICK_LEAD => 'badge-primary',
            WebLead::SOURCE_WEBSITE_ENROLLMENT => 'badge-success',
            WebLead::SOURCE_WEBSITE_ADMISSION => 'badge-info',
            WebLead::SOURCE_BROCHURE_DOWNLOAD => 'badge-warning',
            'web_not_interest' => 'badge-danger',
        ];

        $tabCounts = [];
        foreach ($tabs as $sourceType => $label) {
            $tabCounts[$sourceType] = $sourceType === 'web_not_interest'
                ? $webLeads->where('status', WebLead::STATUS_NOT_INTERESTED)->count()
                : $webLeads
                    ->where('status', WebLead::STATUS_NEW)
                    ->where('source_type', $sourceType)
                    ->count();
        }

        $activeTab = $request->string('tab')->toString();
        if (! array_key_exists($activeTab, $tabs)) {
            $activeTab = array_key_first($tabs);
        }

        return view('web_leads.index', compact('webLeads', 'tabs', 'badgeColors', 'tabCounts', 'activeTab'));
    }

    public function show(WebLead $webLead): View
    {
        $webLead->load(['convertedLead.program', 'convertedLead.campus', 'handledBy']);

        return view('web_leads.show', compact('webLead'));
    }

    public function markNotInterested(Request $request, WebLead $webLead): RedirectResponse
    {
        if (! $webLead->isActionable()) {
            return redirect()
                ->route('web-leads.show', $webLead)
                ->with('error', 'This web lead has already been processed.');
        }

        $webLead->update([
            'status' => WebLead::STATUS_NOT_INTERESTED,
            'handled_by' => $request->user()?->id,
            'handled_at' => now(),
        ]);

        return redirect()
            ->route('web-leads.index', ['tab' => 'web_not_interest'])
            ->with('status', 'Web lead marked as not interested.');
    }

    private function normalizeIncomingPayload(Request $request): array
    {
        return [
            'source_type' => $this->normalizeSourceType($request->input('source_type', $request->input('type'))),
            'full_name' => $request->input('full_name', $request->input('name')),
            'email' => $request->input('email'),
            'phone' => $request->input('phone', $request->input('mobile', $request->input('contact_number'))),
            'country' => $request->input('country'),
            'city' => $request->input('city'),
            'area' => $request->input('area'),
            'interested_program' => $request->input('interested_program', $request->input('program', $request->input('course'))),
            'preferred_campus' => $request->input('preferred_campus', $request->input('campus')),
            'teaching_method' => $this->normalizeTeachingMethod($request->input('teaching_method')),
            'gender' => $this->normalizeGender($request->input('gender')),
            'message' => $request->input('message', $request->input('remarks', $request->input('notes'))),
            'submitted_at' => $request->input('submitted_at', $request->input('created_at')),
        ];
    }

    private function normalizeSourceType(mixed $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        return match (Str::lower(trim($value))) {
            'quicklead', 'quick lead' => WebLead::SOURCE_QUICK_LEAD,
            'website enrollment', 'website enrollement' => WebLead::SOURCE_WEBSITE_ENROLLMENT,
            'website admission' => WebLead::SOURCE_WEBSITE_ADMISSION,
            'brochure', 'brochure download' => WebLead::SOURCE_BROCHURE_DOWNLOAD,
            default => Str::of($value)->trim()->lower()->replace([' ', '-'], '_')->toString(),
        };
    }

    private function normalizeTeachingMethod(mixed $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        return match (strtolower(trim($value))) {
            'on-campus', 'on campus', 'physical' => 'campus',
            default => strtolower(trim($value)),
        };
    }

    private function normalizeGender(mixed $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        return Str::lower(trim($value));
    }

    private function ensureValidToken(Request $request): void
    {
        $expectedToken = (string) config('services.web_leads.token', '');

        if ($expectedToken === '') {
            return;
        }

        $providedToken = (string) $request->header('X-Web-Lead-Token', '');

        abort_unless(hash_equals($expectedToken, $providedToken), 401, 'Invalid web lead token.');
    }
}
