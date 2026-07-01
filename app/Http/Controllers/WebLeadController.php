<?php

namespace App\Http\Controllers;

use App\Models\FinanceOtherCharge;
use App\Models\WebLead;
use App\Support\ResolvesLeadFollowupNotifications;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WebLeadController extends Controller
{
    use ResolvesLeadFollowupNotifications;

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
        $sourceTabs = ['all' => 'All Pending'] + WebLead::leadManagementSourceLabels();

        $badgeColors = [
            'all' => 'badge-default',
            WebLead::SOURCE_QUICK_LEAD => 'badge-primary',
            WebLead::SOURCE_WEBSITE_ENROLLMENT => 'badge-success',
            WebLead::SOURCE_WEBSITE_ADMISSION => 'badge-info',
            WebLead::SOURCE_BROCHURE_DOWNLOAD => 'badge-warning',
        ];

        $activeTab = $request->string('tab')->toString();

        $perPage = (int) $request->integer('per_page', 25);
        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 25;
        }

        $search = trim($request->string('search')->toString());

        if (! array_key_exists($activeTab, $sourceTabs)) {
            $activeTab = 'all';
        }

        $managedSourceTypes = array_keys(WebLead::leadManagementSourceLabels());

        $countQuery = WebLead::query()
            ->pending()
            ->whereIn('source_type', $managedSourceTypes);

        $sourceCounts = (clone $countQuery)
            ->selectRaw('source_type, COUNT(*) as aggregate')
            ->groupBy('source_type')
            ->pluck('aggregate', 'source_type')
            ->map(fn ($count) => (int) $count)
            ->all();

        $tabCounts = ['all' => (clone $countQuery)->count()];

        foreach ($sourceTabs as $sourceType => $label) {
            if ($sourceType === 'all') {
                continue;
            }

            $tabCounts[$sourceType] = (int) ($sourceCounts[$sourceType] ?? 0);
        }

        $webLeadsQuery = WebLead::query()
            ->pending()
            ->whereIn('source_type', $managedSourceTypes)
            ->with(['convertedLead', 'handledBy']);

        if ($activeTab !== 'all') {
            $webLeadsQuery->where('source_type', $activeTab);
        }

        if ($search !== '') {
            $webLeadsQuery->where(function (Builder $query) use ($search): void {
                $query
                    ->where('full_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('interested_program', 'like', "%{$search}%")
                    ->orWhere('preferred_campus', 'like', "%{$search}%");
            });
        }

        $webLeads = $webLeadsQuery
            ->latest('submitted_at')
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();

        return view('web_leads.index', compact(
            'webLeads',
            'sourceTabs',
            'badgeColors',
            'tabCounts',
            'activeTab',
            'search',
            'perPage'
        ));
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
            ->route('web-leads.index')
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
            'feealert', 'fee alert', 'fee_alert', 'fee-alert' => WebLead::SOURCE_FEE_ALERT,
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

    private function notificationTabs(array $tabs, array $tabCounts): array
    {
        $notificationTabs = [];

        foreach ($tabs as $key => $label) {
            $notificationTabs[$key] = [
                'label' => $label,
                'count' => $tabCounts[$key] ?? 0,
                'url' => route('web-leads.index', ['tab' => $key]),
                'external' => false,
            ];
        }

        $notificationTabs['overdue_invoices'] = [
            'label' => 'Overdue Invoices',
            'count' => $this->overdueInvoiceCount(),
            'url' => route('finance.receivables', ['status' => 'overdue']),
            'external' => true,
        ];

        $notificationTabs['follow_up'] = [
            'label' => 'Lead Follow Up',
            'count' => $this->followupNotificationCount(),
            'url' => route('leads.followups'),
            'external' => true,
        ];

        return $notificationTabs;
    }

    private function overdueInvoiceCount(): int
    {
        if (! Schema::hasTable('finance_other_charges')
            || ! Schema::hasColumn('finance_other_charges', 'status')
            || ! Schema::hasColumn('finance_other_charges', 'balance_amount')
        ) {
            return 0;
        }

        FinanceOtherCharge::syncLifecycleStatuses();

        return (int) $this->scopeQueryToCurrentUserCampus(FinanceOtherCharge::query())
            ->where('status', 'overdue')
            ->count();
    }

    private function followupNotificationCount(): int
    {
        if (! Schema::hasTable('lead_followups') || ! Schema::hasTable('leads')) {
            return 0;
        }

        return $this->latestDueLeadFollowupNotifications(
            auth()->user(),
            fn (Builder $leadQuery, $user = null) => $this->scopeQueryToCurrentUserCampus($leadQuery),
            ['training', 'certification', 'study_abroad']
        )->count();
    }

    private function scopeQueryToCurrentUserCampus(Builder $query): Builder
    {
        $user = auth()->user();

        if (! $user || $user->isAdmin()) {
            return $query;
        }

        $campusId = (int) ($user->campus_id ?? 0);

        return $campusId > 0
            ? $query->where('campus_id', $campusId)
            : $query;
    }
}
