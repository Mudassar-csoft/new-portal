<?php

namespace App\Http\Controllers;

use App\Models\Campus;
use App\Models\FinanceOtherCharge;
use App\Models\Lead;
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

    private const DEFAULT_WEB_LEAD_CAMPUS_CODE = 'CIFSD04';
    private const DEFAULT_WEB_LEAD_CITY = 'Faisalabad';
    private const DEFAULT_WEB_LEAD_COUNTRY = 'Pakistan';

    public function storePublic(Request $request): JsonResponse
    {
        $this->ensureValidToken($request);
        $normalizedPayload = $this->normalizeIncomingPayload($request);

        $validated = validator(
            $normalizedPayload,
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

        if (! empty($validated['phone'])) {
            if ($this->hasExistingLeadForPhone($validated['phone'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'A CRM lead with this phone number already exists.',
                    'error' => 'duplicate_phone',
                ], 409);
            }

            if ($this->hasExistingWebLeadForPhoneAndType($validated['phone'], (string) $validated['source_type'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'A web lead with this phone number and lead type already exists.',
                    'error' => 'duplicate_phone_type',
                ], 409);
            }
        }

        $webLead = WebLead::create([
            ...$validated,
            'source_site' => config('services.web_leads.source_site', 'career.edu.pk'),
            'status' => WebLead::STATUS_NEW,
            'submitted_at' => $validated['submitted_at'] ?? now(),
            'payload' => $this->buildPayloadForStorage($request, $validated),
        ]);

        return response()->json([
            'success' => true,
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
        $sourceType = $this->normalizeSourceType($request->input('source_type', $request->input('type')));
        $resolvedCampus = $this->resolveIncomingCampus($request);
        $fullName = $this->normalizeText($request->input('full_name', $request->input('name')));
        $email = $this->normalizeText($request->input('email'));
        $phone = $this->normalizePhone(
            $request->input('phone', $request->input('mobile', $request->input('contact_number', $request->input('primary_contact'))))
        );
        $country = $this->normalizeText($request->input('country', $request->input('country_name')))
            ?: self::DEFAULT_WEB_LEAD_COUNTRY;
        $city = $this->normalizeText($request->input('city'))
            ?: self::DEFAULT_WEB_LEAD_CITY;
        $area = $this->normalizeText(
            $request->input('area', $request->input('state', $request->input('postal_address')))
        );
        $interestedProgram = $this->normalizeText(
            $request->input('interested_program', $request->input('program', $request->input('course')))
        );
        $preferredCampus = $this->normalizeText($request->input('preferred_campus', $request->input('campus')))
            ?: ($resolvedCampus?->code ?: self::DEFAULT_WEB_LEAD_CAMPUS_CODE);
        $message = $this->combineMessageParts([
            $this->normalizeText($request->input('message')),
            $this->normalizeText($request->input('question_or_comment')),
            $this->normalizeText($request->input('remarks')),
            $this->normalizeText($request->input('notes')),
        ]);

        return [
            'source_type' => $sourceType,
            'full_name' => $fullName,
            'email' => $email,
            'phone' => $phone,
            'country' => $country,
            'city' => $city,
            'area' => $area,
            'interested_program' => $interestedProgram,
            'preferred_campus' => $preferredCampus,
            'teaching_method' => $this->normalizeTeachingMethod($request->input('teaching_method')),
            'gender' => $this->normalizeGender($request->input('gender')),
            'message' => $message ?: $this->defaultMessageForSource($sourceType, $interestedProgram, $preferredCampus),
            'submitted_at' => $request->input('submitted_at', $request->input('created_at')),
        ];
    }

    private function normalizeSourceType(mixed $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        return match (Str::lower(trim($value))) {
            'lead', 'enroll lead', 'enrollment', 'website enrollment', 'website enrollement' => WebLead::SOURCE_WEBSITE_ENROLLMENT,
            'admission', 'website admission' => WebLead::SOURCE_WEBSITE_ADMISSION,
            'quicklead', 'quick lead' => WebLead::SOURCE_QUICK_LEAD,
            'brochure', 'brochure lead', 'brochurelead', 'brochure download' => WebLead::SOURCE_BROCHURE_DOWNLOAD,
            'feealert', 'fee alert', 'fee_alert', 'fee-alert' => WebLead::SOURCE_FEE_ALERT,
            default => Str::of($value)->trim()->lower()->replace([' ', '-'], '_')->toString(),
        };
    }

    private function normalizePhone(mixed $value): ?string
    {
        if (! is_string($value) && ! is_numeric($value)) {
            return null;
        }

        $normalized = preg_replace('/\D+/', '', trim((string) $value));

        if ($normalized === '') {
            return null;
        }

        if (Str::startsWith($normalized, '92') && strlen($normalized) === 12) {
            $normalized = '0' . substr($normalized, 2);
        }

        if (strlen($normalized) === 10 && Str::startsWith($normalized, '3')) {
            $normalized = '0' . $normalized;
        }

        return $normalized;
    }

    private function normalizeText(mixed $value): ?string
    {
        if (! is_string($value) && ! is_numeric($value)) {
            return null;
        }

        $normalized = trim((string) $value);

        return $normalized !== '' ? $normalized : null;
    }

    /**
     * @param  list<?string>  $parts
     */
    private function combineMessageParts(array $parts): ?string
    {
        $parts = array_values(array_filter($parts, static fn (?string $value) => $value !== null && $value !== ''));

        if ($parts === []) {
            return null;
        }

        return implode(PHP_EOL . PHP_EOL, array_unique($parts));
    }

    private function defaultMessageForSource(?string $sourceType, ?string $program, ?string $campus): string
    {
        $programText = $program ? ' for ' . $program : '';
        $campusText = $campus ? ' at ' . $campus : '';

        return match ($sourceType) {
            WebLead::SOURCE_WEBSITE_ADMISSION => 'Website admission request received' . $programText . $campusText . '.',
            WebLead::SOURCE_BROCHURE_DOWNLOAD => 'Website brochure request received' . $programText . $campusText . '.',
            WebLead::SOURCE_QUICK_LEAD => 'Website quick lead received' . $programText . $campusText . '.',
            default => 'Website enrollment inquiry received' . $programText . $campusText . '.',
        };
    }

    private function resolveIncomingCampus(Request $request): ?Campus
    {
        $campusId = $request->input('campus_id');

        if (is_numeric($campusId)) {
            $campus = Campus::query()->find((int) $campusId, ['id', 'code', 'name']);

            if ($campus) {
                return $campus;
            }
        }

        $campusText = $this->normalizeText($request->input('preferred_campus', $request->input('campus')));

        if ($campusText !== null) {
            $needle = Str::lower($campusText);
            $campus = Campus::query()
                ->where(function (Builder $query) use ($needle): void {
                    $query->whereRaw('LOWER(code) = ?', [$needle])
                        ->orWhereRaw('LOWER(name) = ?', [$needle]);
                })
                ->first(['id', 'code', 'name']);

            if ($campus) {
                return $campus;
            }
        }

        return Campus::query()
            ->whereRaw('LOWER(code) = ?', [Str::lower(self::DEFAULT_WEB_LEAD_CAMPUS_CODE)])
            ->first(['id', 'code', 'name']);
    }

    private function buildPayloadForStorage(Request $request, array $validated): array
    {
        $resolvedCampus = $this->resolveIncomingCampus($request);
        $payload = $request->all();
        $payload['campus_id'] = $resolvedCampus?->id ?? $request->input('campus_id');
        $payload['resolved_source_type'] = $validated['source_type'] ?? null;
        $payload['resolved_phone'] = $validated['phone'] ?? null;
        $payload['resolved_preferred_campus'] = $validated['preferred_campus'] ?? null;
        $payload['resolved_city'] = $validated['city'] ?? null;
        $payload['resolved_country'] = $validated['country'] ?? null;
        $payload['source_type'] = $validated['source_type'] ?? ($payload['source_type'] ?? null);

        return $payload;
    }

    private function hasExistingLeadForPhone(string $phone): bool
    {
        return Lead::query()
            ->where('phone', $phone)
            ->exists();
    }

    private function hasExistingWebLeadForPhoneAndType(string $phone, string $sourceType): bool
    {
        return WebLead::query()
            ->where('phone', $phone)
            ->where('source_type', $sourceType)
            ->exists();
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

        $providedToken = (string) $request->header(
            'X-Web-Lead-Token',
            $request->query('token', $request->input('token', ''))
        );

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
