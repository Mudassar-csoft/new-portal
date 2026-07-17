<?php

namespace App\Http\Controllers;

use App\Models\Admission;
use App\Models\Program;
use App\Support\ResolvesCampusScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CertificateController extends Controller
{
    use ResolvesCampusScope;

    private const ALLOWED_SCOPES = [
        'all',
        'requested',
        'approved',
        'printing',
        'ready',
        'delivered',
    ];

    public function index(Request $request): View
    {
        $scope = (string) $request->query('scope', 'all');
        $perPage = $this->resolvePerPage($request);
        if (! in_array($scope, self::ALLOWED_SCOPES, true)) {
            $scope = 'all';
        }

        $query = $this->certificateWorkflowQuery($request->user())
            ->with(['campus', 'program']);

        $this->applyScope($query, $scope);
        $this->applyFilters($query, $request);

        $certificates = $query
            ->orderByDesc('status_updated_at')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        return view('certificate.index', [
            'certificates' => $certificates,
            'activeScope' => $scope,
            'scopeCards' => $this->buildScopeCards($request),
            'campuses' => $this->campusOptionsForUser($request->user(), ['id', 'code', 'name']),
            'programs' => Program::query()->orderByRaw('COALESCE(title, name)')->get(['id', 'code', 'title', 'name']),
            'filters' => [
                'scope' => $scope,
                'campus_id' => $request->integer('campus_id') ?: null,
                'program_id' => $request->integer('program_id') ?: null,
                'search' => $request->input('search'),
                'per_page' => $perPage,
            ],
            'pageTitle' => $this->resolvePageTitle($scope),
            'statusLabels' => $this->certificateStatusLabels(),
            'statusClasses' => $this->certificateStatusClasses(),
        ]);
    }

    public function create(Request $request): View
    {
        $admissionId = $request->integer('admission_id') ?: null;
        $selectedAdmission = $admissionId
            ? $this->certificateEligibleAdmissionsQuery($request->user())->with(['program', 'campus'])->find($admissionId)
            : null;
        $admissions = $this->certificateEligibleAdmissionsQuery($request->user())
            ->with(['program:id,code,title,name', 'campus:id,code,name'])
            ->orderByDesc('admission_date')
            ->limit(500)
            ->get(['id', 'student_name', 'roll_number', 'registration_number', 'program_id', 'campus_id', 'admission_date']);

        if ($selectedAdmission && ! $admissions->contains('id', $selectedAdmission->id)) {
            $admissions->prepend($selectedAdmission);
        }

        return view('certificate.create', [
            'admissions' => $admissions,
            'selectedAdmission' => $selectedAdmission,
        ]);
    }

    public function preview(Request $request, Admission $admission): View
    {
        $this->ensureCampusAccess((int) ($admission->campus_id ?? 0), auth()->user(), 'You are not allowed to access certificate records from another campus.');
        $this->ensurePrintableCertificateAdmission($admission);

        $admission->loadMissing([
            'registration:id,student_name',
            'program:id,code,title,name',
            'campus:id,code,name,title,city',
        ]);

        return view('certificate.preview', [
            'previewItems' => collect([
                $this->buildCertificatePreviewItem($admission),
            ]),
            'backUrl' => $this->resolvePreviewBackUrl($request),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'admission_id' => ['required', 'exists:admissions,id'],
            'remarks' => ['nullable', 'string'],
        ]);

        $admission = $this->certificateEligibleAdmissionsQuery($request->user())->find($validated['admission_id']);
        if (! $admission) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Only concluded or completed students can be moved into certificate request.');
        }

        $updates = [
            'certificate_status' => Admission::CERTIFICATE_STATUS_REQUESTED,
            'certificate_origin_status' => $admission->resolveCertificateOriginStatus(),
            'status_updated_at' => now(),
        ];

        if (filled($validated['remarks'] ?? null)) {
            $updates['remarks'] = $validated['remarks'];
        }

        $admission->update($updates);

        return redirect()->route('certificate.index', ['scope' => 'requested'])
            ->with('status', 'Certificate request created.');
    }

    public function edit(Admission $admission): View
    {
        $this->ensureCampusAccess((int) ($admission->campus_id ?? 0), auth()->user(), 'You are not allowed to access certificate records from another campus.');
        $this->ensureCertificateWorkflowAdmission($admission);

        return view('certificate.edit', [
            'admission' => $admission->load(['program', 'campus']),
            'statusLabels' => $this->certificateStatusLabels(),
            'statusClasses' => $this->certificateStatusClasses(),
        ]);
    }

    public function update(Request $request, Admission $admission): RedirectResponse
    {
        $this->ensureCampusAccess((int) ($admission->campus_id ?? 0), $request->user(), 'You are not allowed to update certificate records from another campus.');
        $this->ensureCertificateWorkflowAdmission($admission);

        $validated = $request->validate([
            'remarks' => ['nullable', 'string'],
        ]);

        $admission->update([
            'remarks' => $this->normalizeCertificateRemarkInput($validated['remarks'] ?? null),
        ]);

        return redirect()->route('certificate.index')
            ->with('status', 'Certificate updated.');
    }

    public function destroy(Request $request, Admission $admission): RedirectResponse
    {
        $this->ensureCampusAccess((int) ($admission->campus_id ?? 0), $request->user(), 'You are not allowed to update certificate records from another campus.');
        $this->ensureCertificateWorkflowAdmission($admission);

        if (($admission->certificate_status ?? null) === Admission::CERTIFICATE_STATUS_DELIVERED) {
            return redirect()->route('certificate.index')
                ->with('error', 'Delivered certificates cannot be deleted.');
        }

        $admission->update([
            'certificate_status' => null,
            'certificate_origin_status' => null,
            'status_updated_at' => now(),
        ]);

        return redirect()->route('certificate.index')
            ->with('status', 'Certificate request removed. Student is back in pending certificate stage.');
    }

    public function approve(Request $request, Admission $admission): RedirectResponse
    {
        $this->ensureCampusAccess((int) ($admission->campus_id ?? 0), $request->user(), 'You are not allowed to update certificate records from another campus.');

        if (($admission->certificate_status ?? null) !== Admission::CERTIFICATE_STATUS_REQUESTED) {
            return redirect()->route('certificate.index')
                ->with('error', 'Only requested certificates can be approved.');
        }

        $validated = $request->validate([
            'remarks' => ['nullable', 'string'],
        ]);

        $admission->update([
            'certificate_status' => Admission::CERTIFICATE_STATUS_APPROVED,
            'status_updated_at' => now(),
            'remarks' => $this->mergeCertificateRemarks($admission->remarks, $validated['remarks'] ?? null),
        ]);

        return redirect()->route('certificate.index')
            ->with('status', 'Certificate approved.');
    }

    public function bulkApprove(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'admission_ids' => ['required', 'array', 'min:1'],
            'admission_ids.*' => ['integer', 'distinct', 'exists:admissions,id'],
            'remarks' => ['nullable', 'string'],
        ]);

        $ids = collect($validated['admission_ids'] ?? [])
            ->map(fn (mixed $id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return redirect()->back()->with('error', 'Select at least one certificate request to approve.');
        }

        $remarks = $validated['remarks'] ?? null;
        $approvedCount = 0;

        DB::transaction(function () use ($request, $ids, $remarks, &$approvedCount): void {
            $admissions = $this->scopeQueryToUserCampus(Admission::query(), $request->user())
                ->whereIn('id', $ids->all())
                ->where('certificate_status', Admission::CERTIFICATE_STATUS_REQUESTED)
                ->lockForUpdate()
                ->get();

            $approvedCount = $admissions->count();

            foreach ($admissions as $admission) {
                $admission->update([
                    'certificate_status' => Admission::CERTIFICATE_STATUS_APPROVED,
                    'status_updated_at' => now(),
                    'remarks' => $this->mergeCertificateRemarks($admission->remarks, $remarks),
                ]);
            }
        });

        if ($approvedCount === 0) {
            return redirect()->back()->with('error', 'No selected certificate requests were eligible for approval.');
        }

        return redirect()->back()->with('status', $approvedCount.' certificate request(s) approved.');
    }

    public function bulkSendToPrinting(Request $request): RedirectResponse
    {
        return $this->performBulkWorkflowTransition(
            $request,
            Admission::CERTIFICATE_STATUS_APPROVED,
            Admission::CERTIFICATE_STATUS_PRINTING,
            'Select at least one approved certificate to send to printing.',
            'No selected approved certificates were eligible for printing.',
            ' certificate(s) sent to printing.'
        );
    }

    public function bulkMarkReady(Request $request): RedirectResponse
    {
        return $this->performBulkWorkflowTransition(
            $request,
            Admission::CERTIFICATE_STATUS_PRINTING,
            Admission::CERTIFICATE_STATUS_READY,
            'Select at least one printing certificate to mark ready.',
            'No selected printing certificates were eligible to mark ready.',
            ' certificate(s) marked ready for collection.'
        );
    }

    public function bulkPreview(Request $request): View
    {
        $ids = $this->extractBulkAdmissionIds($request);

        abort_if($ids->isEmpty(), 404);

        $admissions = $this->scopeQueryToUserCampus(Admission::query(), $request->user())
            ->with([
                'registration:id,student_name',
                'program:id,code,title,name',
                'campus:id,code,name,title,city',
            ])
            ->whereIn('id', $ids->all())
            ->whereIn('certificate_status', [
                Admission::CERTIFICATE_STATUS_PRINTING,
                Admission::CERTIFICATE_STATUS_READY,
            ])
            ->orderByRaw('COALESCE(student_name, registration_number, roll_number)')
            ->get();

        abort_if($admissions->isEmpty(), 404);

        return view('certificate.preview', [
            'previewItems' => $admissions->map(fn (Admission $admission) => $this->buildCertificatePreviewItem($admission)),
            'backUrl' => $this->resolvePreviewBackUrl($request, 'printing'),
        ]);
    }

    public function reject(Request $request, Admission $admission): RedirectResponse
    {
        $this->ensureCampusAccess((int) ($admission->campus_id ?? 0), $request->user(), 'You are not allowed to update certificate records from another campus.');

        if (! in_array(($admission->certificate_status ?? null), [
            Admission::CERTIFICATE_STATUS_REQUESTED,
            Admission::CERTIFICATE_STATUS_APPROVED,
        ], true)) {
            return redirect()->route('certificate.index')
                ->with('error', 'Only requested or approved certificates can be rejected.');
        }

        $validated = $request->validate([
            'remarks' => ['nullable', 'string'],
        ]);

        $admission->update([
            'certificate_status' => null,
            'certificate_origin_status' => null,
            'status_updated_at' => now(),
            'remarks' => $this->mergeCertificateRemarks($admission->remarks, $validated['remarks'] ?? null),
        ]);

        return redirect()->route('certificate.index')
            ->with('status', 'Certificate request rejected. Student moved back to pending certificate stage.');
    }

    public function sendToPrinting(Request $request, Admission $admission): RedirectResponse
    {
        $this->ensureCampusAccess((int) ($admission->campus_id ?? 0), $request->user(), 'You are not allowed to update certificate records from another campus.');

        if (($admission->certificate_status ?? null) !== Admission::CERTIFICATE_STATUS_APPROVED) {
            return redirect()->route('certificate.index')
                ->with('error', 'Only approved certificates can be sent to printing.');
        }

        $admission->update([
            'certificate_status' => Admission::CERTIFICATE_STATUS_PRINTING,
            'status_updated_at' => now(),
        ]);

        return redirect()->route('certificate.index')
            ->with('status', 'Certificate sent to printing.');
    }

    public function markReady(Request $request, Admission $admission): RedirectResponse
    {
        $this->ensureCampusAccess((int) ($admission->campus_id ?? 0), $request->user(), 'You are not allowed to update certificate records from another campus.');

        if (($admission->certificate_status ?? null) !== Admission::CERTIFICATE_STATUS_PRINTING) {
            return redirect()->route('certificate.index')
                ->with('error', 'Only printing certificates can be marked ready.');
        }

        $admission->update([
            'certificate_status' => Admission::CERTIFICATE_STATUS_READY,
            'status_updated_at' => now(),
        ]);

        return redirect()->route('certificate.index')
            ->with('status', 'Certificate marked ready for collection.');
    }

    public function markDelivered(Request $request, Admission $admission): RedirectResponse
    {
        $this->ensureCampusAccess((int) ($admission->campus_id ?? 0), $request->user(), 'You are not allowed to update certificate records from another campus.');

        if (($admission->certificate_status ?? null) !== Admission::CERTIFICATE_STATUS_READY) {
            return redirect()->route('certificate.index')
                ->with('error', 'Only ready certificates can be marked delivered.');
        }

        $validated = $request->validate([
            'delivered_to' => ['required', 'string', 'max:255'],
            'delivered_cnic' => ['nullable', 'string', 'max:100'],
            'delivered_phone' => ['nullable', 'string', 'max:100'],
            'delivered_at' => ['required', 'date'],
            'remarks' => ['nullable', 'string'],
        ]);

        $deliveredTo = trim((string) ($validated['delivered_to'] ?? ''));
        $deliveredCnic = trim((string) ($validated['delivered_cnic'] ?? ''));
        $deliveredPhone = trim((string) ($validated['delivered_phone'] ?? ''));
        $deliveryRemarks = trim((string) ($validated['remarks'] ?? ''));
        $deliveredAt = Carbon::parse((string) $validated['delivered_at'])->startOfDay();

        $admission->update([
            'certificate_status' => Admission::CERTIFICATE_STATUS_DELIVERED,
            'status_updated_at' => now(),
            'certificate_delivered_at' => $deliveredAt,
            'certificate_delivered_by' => optional($request->user())->id,
            'certificate_delivery_notes' => $this->buildCertificateDeliveryNotes(
                $admission->certificate_delivery_notes,
                $deliveredTo,
                $deliveredCnic,
                $deliveredPhone,
                $deliveredAt,
                $deliveryRemarks
            ),
        ]);

        return redirect()->route('certificate.index')
            ->with('status', 'Certificate delivered.');
    }

    private function applyScope(Builder $query, string $scope): void
    {
        if ($scope !== 'all' && in_array($scope, self::ALLOWED_SCOPES, true)) {
            $query->where('certificate_status', $scope);
        }
    }

    private function resolvePerPage(Request $request): int
    {
        $perPage = (int) $request->query('per_page', 25);

        return in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 25;
    }

    private function applyFilters(Builder $query, Request $request): void
    {
        $query
            ->when($request->integer('campus_id'), fn (Builder $q, int $id) => $q->where('campus_id', $id))
            ->when($request->integer('program_id'), fn (Builder $q, int $id) => $q->where('program_id', $id))
            ->when($request->filled('search'), function (Builder $q) use ($request) {
                $keyword = trim((string) $request->input('search'));
                $q->where(function (Builder $inner) use ($keyword) {
                    $inner
                        ->where('student_name', 'like', "%{$keyword}%")
                        ->orWhere('roll_number', 'like', "%{$keyword}%")
                        ->orWhere('registration_number', 'like', "%{$keyword}%")
                        ->orWhere('remarks', 'like', "%{$keyword}%")
                        ->orWhere('certificate_delivery_notes', 'like', "%{$keyword}%");
                });
            });
    }

    private function buildScopeCards(Request $request): array
    {
        $counts = $this->certificateWorkflowQuery($request->user())
            ->tap(fn (Builder $query) => $this->applyFilters($query, $request))
            ->selectRaw('certificate_status, COUNT(*) as aggregate')
            ->groupBy('certificate_status')
            ->pluck('aggregate', 'certificate_status');

        $total = (int) $counts->sum();

        $labels = [
            'all' => 'All Certificates',
            'requested' => 'Request for Approval',
            'approved' => 'Approved',
            'printing' => 'On Printing',
            'ready' => 'Ready',
            'delivered' => 'Delivered',
        ];

        $cards = [];
        foreach ($labels as $scope => $label) {
            $count = $scope === 'all' ? $total : (int) ($counts[$scope] ?? 0);
            $cards[] = ['scope' => $scope, 'label' => $label, 'count' => $count];
        }

        return $cards;
    }

    private function resolvePageTitle(string $scope): string
    {
        return match ($scope) {
            'requested' => 'Certificates - Request for Approval',
            'approved' => 'Certificates - Approved',
            'printing' => 'Certificates - On Printing',
            'ready' => 'Certificates - Ready',
            'delivered' => 'Certificates - Delivered',
            default => 'Certificate Management',
        };
    }

    private function certificateEligibleAdmissionsQuery($user = null): Builder
    {
        return $this->scopeQueryToUserCampus(Admission::query(), $user)
            ->whereIn('student_status', Admission::CERTIFICATE_REQUESTABLE_STATUSES)
            ->where(function (Builder $query): void {
                $query
                    ->whereNull('certificate_status')
                    ->orWhere('certificate_status', '');
            });
    }

    private function certificateWorkflowQuery($user = null): Builder
    {
        return $this->scopeQueryToUserCampus(Admission::query(), $user)
            ->certificateWorkflow();
    }

    private function ensureCertificateWorkflowAdmission(Admission $admission): void
    {
        if (! Admission::isCertificateWorkflowStatus((string) ($admission->certificate_status ?? ''))) {
            abort(404);
        }
    }

    private function ensurePrintableCertificateAdmission(Admission $admission): void
    {
        $this->ensureCertificateWorkflowAdmission($admission);

        abort_unless(
            in_array((string) ($admission->certificate_status ?? ''), [
                Admission::CERTIFICATE_STATUS_PRINTING,
                Admission::CERTIFICATE_STATUS_READY,
                Admission::CERTIFICATE_STATUS_DELIVERED,
            ], true),
            404
        );
    }

    private function extractBulkAdmissionIds(Request $request)
    {
        $validated = $request->validate([
            'admission_ids' => ['required', 'array', 'min:1'],
            'admission_ids.*' => ['integer', 'distinct', 'exists:admissions,id'],
        ]);

        return collect($validated['admission_ids'] ?? [])
            ->map(fn (mixed $id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values();
    }

    private function performBulkWorkflowTransition(
        Request $request,
        string $fromStatus,
        string $toStatus,
        string $emptySelectionMessage,
        string $notEligibleMessage,
        string $successSuffix
    ): RedirectResponse {
        $ids = $this->extractBulkAdmissionIds($request);

        if ($ids->isEmpty()) {
            return redirect()->back()->with('error', $emptySelectionMessage);
        }

        $updatedCount = 0;
        $timestamp = now();

        DB::transaction(function () use ($request, $ids, $fromStatus, $toStatus, $timestamp, &$updatedCount): void {
            $admissions = $this->scopeQueryToUserCampus(Admission::query(), $request->user())
                ->whereIn('id', $ids->all())
                ->where('certificate_status', $fromStatus)
                ->lockForUpdate()
                ->get();

            $updatedCount = $admissions->count();

            foreach ($admissions as $admission) {
                $admission->update([
                    'certificate_status' => $toStatus,
                    'status_updated_at' => $timestamp,
                ]);
            }
        });

        if ($updatedCount === 0) {
            return redirect()->back()->with('error', $notEligibleMessage);
        }

        return redirect()->back()->with('status', $updatedCount.$successSuffix);
    }

    /**
     * @return array<string, string>
     */
    private function certificateStatusLabels(): array
    {
        return array_intersect_key(
            Admission::STUDENT_STATUS_LABELS,
            array_flip(Admission::CERTIFICATE_WORKFLOW_STATUSES)
        );
    }

    /**
     * @return array<string, string>
     */
    private function certificateStatusClasses(): array
    {
        return array_intersect_key(
            Admission::STUDENT_STATUS_BADGE_CLASSES,
            array_flip(Admission::CERTIFICATE_WORKFLOW_STATUSES)
        );
    }

    private function mergeCertificateRemarks(?string $currentRemarks, ?string $newRemarks): string
    {
        $current = trim((string) $currentRemarks);
        $incoming = trim((string) $newRemarks);

        if ($incoming === '') {
            return $current;
        }

        if ($current === '') {
            return $incoming;
        }

        return $current . PHP_EOL . $incoming;
    }

    private function normalizeCertificateRemarkInput(?string $remarks): string
    {
        return trim((string) $remarks);
    }

    private function buildCertificateDeliveryNotes(
        ?string $existingNotes,
        string $deliveredTo,
        string $deliveredCnic,
        string $deliveredPhone,
        Carbon $deliveredAt,
        string $remarks
    ): string {
        $lines = [
            'Delivered to: ' . $deliveredTo,
            'CNIC: ' . ($deliveredCnic !== '' ? $deliveredCnic : 'N/A'),
            'Phone: ' . ($deliveredPhone !== '' ? $deliveredPhone : 'N/A'),
            'Delivery Date: ' . $deliveredAt->format('d-m-Y'),
        ];

        if ($remarks !== '') {
            $lines[] = 'Remarks: ' . $remarks;
        }

        $details = implode(PHP_EOL, $lines);
        $current = trim((string) $existingNotes);

        if ($current === '') {
            return $details;
        }

        return $current . PHP_EOL . PHP_EOL . $details;
    }

    private function resolveCertificateStudentName(Admission $admission): string
    {
        return trim((string) ($admission->student_name ?: $admission->registration?->student_name ?: 'Student'));
    }

    /**
     * @return array{admission: \App\Models\Admission, studentName: string, programTitle: string, dateLine: string}
     */
    private function buildCertificatePreviewItem(Admission $admission): array
    {
        return [
            'admission' => $admission,
            'studentName' => $this->resolveCertificateStudentName($admission),
            'programTitle' => $admission->program?->title ?: $admission->program?->name ?: 'Training Programme',
            'dateLine' => $this->resolveCertificateDateLine($admission),
        ];
    }

    private function resolvePreviewBackUrl(Request $request, string $defaultScope = 'printing'): string
    {
        $scope = (string) $request->query('scope', $defaultScope);

        if (! in_array($scope, self::ALLOWED_SCOPES, true)) {
            $scope = $defaultScope;
        }

        return route('certificate.index', array_filter([
            'scope' => $scope !== 'all' ? $scope : null,
        ]));
    }

    private function resolveCertificateDateLine(Admission $admission): string
    {
        $legacyOverrides = [
            5006 => 'Given this day of 31-01-2020',
            5004 => 'Given this day of 30-09-2019',
            5005 => 'Given this day of 30-04-2019',
            5200 => 'Given this day of 10-08-2024',
            5201 => 'Given this day of 11-11-2025',
            5202 => 'Given this day of 20-07-2025',
            5203 => 'Given this day of 15-01-2025',
            5227 => 'Given this day of March 10, 2022',
            5248 => 'Given this day of Feb 14, 2025',
            5296 => 'Course Duration 01-JUL-2024 TO 31-DEC-2024',
            5375 => 'Course Duration 02-OCT-2024 TO 31-MAR-2025',
            5490 => 'Course Duration 01-FEB-2015 TO 30-MAR-2015',
            5491 => 'Course Duration 01-APR-2015 TO 30-MAY-2015',
            5492 => 'Course Duration 01-JUN-2015 TO 30-AUG-2015',
            5493 => 'Course Duration 01-SEP-2015 TO 30-NOV-2015',
            5510 => 'Course Duration 01-DEC-2018 TO 30-MAY-2019',
            5511 => 'Course Duration 01-JULY-2019 TO 31-DEC-2019',
            5512 => 'Course Duration 02-FEB-2020 TO 31-JULY-2020',
            5568 => 'Course Duration 02-FEB-2022 TO 30-May-2022',
            5796 => 'Course Duration 02-FEB-2020 TO 30-JULY-2020',
            5887 => 'Course Duration 01-JUN-2023 TO 01-DEC-2023',
        ];

        $admissionId = (int) $admission->id;
        if (array_key_exists($admissionId, $legacyOverrides)) {
            return $legacyOverrides[$admissionId];
        }

        $certificateDate = $this->resolveFirstValidCertificateDate($admission) ?? now();

        return 'Given this day of ' . $certificateDate->format('d-m-Y');
    }

    private function resolveFirstValidCertificateDate(Admission $admission): ?\Illuminate\Support\Carbon
    {
        foreach (['status_updated_at', 'certificate_delivered_at', 'admission_date'] as $attribute) {
            $rawValue = $admission->getRawOriginal($attribute);

            if (!is_string($rawValue) || trim($rawValue) === '' || str_starts_with($rawValue, '0000-00-00')) {
                continue;
            }

            try {
                return Carbon::parse($rawValue);
            } catch (\Throwable) {
                continue;
            }
        }

        return null;
    }
}
