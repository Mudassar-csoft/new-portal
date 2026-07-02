<?php

namespace App\Http\Controllers;

use App\Models\Admission;
use App\Models\Program;
use App\Support\ResolvesCampusScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            ->paginate(15)
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

        return view('certificate.create', [
            'admissions' => $this->certificateEligibleAdmissionsQuery($request->user())
                ->with(['program:id,code,title,name', 'campus:id,code,name'])
                ->orderByDesc('admission_date')
                ->limit(500)
                ->get(['id', 'student_name', 'roll_number', 'registration_number', 'program_id', 'campus_id', 'admission_date']),
            'selectedAdmission' => $selectedAdmission,
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
                ->with('error', 'Only enrolled students can be moved into certificate request.');
        }

        $updates = [
            'student_status' => Admission::CERTIFICATE_STATUS_REQUESTED,
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
            'remarks' => $validated['remarks'] ?? null,
        ]);

        return redirect()->route('certificate.index')
            ->with('status', 'Certificate updated.');
    }

    public function destroy(Request $request, Admission $admission): RedirectResponse
    {
        $this->ensureCampusAccess((int) ($admission->campus_id ?? 0), $request->user(), 'You are not allowed to update certificate records from another campus.');
        $this->ensureCertificateWorkflowAdmission($admission);

        if (($admission->student_status ?? null) === Admission::CERTIFICATE_STATUS_DELIVERED) {
            return redirect()->route('certificate.index')
                ->with('error', 'Delivered certificates cannot be deleted.');
        }

        $admission->update([
            'student_status' => Admission::CERTIFICATE_REQUESTABLE_STATUS,
            'status_updated_at' => now(),
        ]);

        return redirect()->route('certificate.index')
            ->with('status', 'Certificate request removed. Student is back in pending certificate stage.');
    }

    public function approve(Request $request, Admission $admission): RedirectResponse
    {
        $this->ensureCampusAccess((int) ($admission->campus_id ?? 0), $request->user(), 'You are not allowed to update certificate records from another campus.');

        if (($admission->student_status ?? null) !== Admission::CERTIFICATE_STATUS_REQUESTED) {
            return redirect()->route('certificate.index')
                ->with('error', 'Only requested certificates can be approved.');
        }

        $validated = $request->validate([
            'remarks' => ['nullable', 'string'],
        ]);

        $admission->update([
            'student_status' => Admission::CERTIFICATE_STATUS_APPROVED,
            'status_updated_at' => now(),
            'remarks' => $this->mergeCertificateRemarks($admission->remarks, $validated['remarks'] ?? null),
        ]);

        return redirect()->route('certificate.index')
            ->with('status', 'Certificate approved.');
    }

    public function reject(Request $request, Admission $admission): RedirectResponse
    {
        $this->ensureCampusAccess((int) ($admission->campus_id ?? 0), $request->user(), 'You are not allowed to update certificate records from another campus.');

        if (! in_array(($admission->student_status ?? null), [
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
            'student_status' => Admission::CERTIFICATE_REQUESTABLE_STATUS,
            'status_updated_at' => now(),
            'remarks' => $this->mergeCertificateRemarks($admission->remarks, $validated['remarks'] ?? null),
        ]);

        return redirect()->route('certificate.index')
            ->with('status', 'Certificate request rejected. Student moved back to pending certificate stage.');
    }

    public function sendToPrinting(Request $request, Admission $admission): RedirectResponse
    {
        $this->ensureCampusAccess((int) ($admission->campus_id ?? 0), $request->user(), 'You are not allowed to update certificate records from another campus.');

        if (($admission->student_status ?? null) !== Admission::CERTIFICATE_STATUS_APPROVED) {
            return redirect()->route('certificate.index')
                ->with('error', 'Only approved certificates can be sent to printing.');
        }

        $admission->update([
            'student_status' => Admission::CERTIFICATE_STATUS_PRINTING,
            'status_updated_at' => now(),
        ]);

        return redirect()->route('certificate.index')
            ->with('status', 'Certificate sent to printing.');
    }

    public function markReady(Request $request, Admission $admission): RedirectResponse
    {
        $this->ensureCampusAccess((int) ($admission->campus_id ?? 0), $request->user(), 'You are not allowed to update certificate records from another campus.');

        if (($admission->student_status ?? null) !== Admission::CERTIFICATE_STATUS_PRINTING) {
            return redirect()->route('certificate.index')
                ->with('error', 'Only printing certificates can be marked ready.');
        }

        $admission->update([
            'student_status' => Admission::CERTIFICATE_STATUS_READY,
            'status_updated_at' => now(),
        ]);

        return redirect()->route('certificate.index')
            ->with('status', 'Certificate marked ready for collection.');
    }

    public function markDelivered(Request $request, Admission $admission): RedirectResponse
    {
        $this->ensureCampusAccess((int) ($admission->campus_id ?? 0), $request->user(), 'You are not allowed to update certificate records from another campus.');

        if (($admission->student_status ?? null) !== Admission::CERTIFICATE_STATUS_READY) {
            return redirect()->route('certificate.index')
                ->with('error', 'Only ready certificates can be marked delivered.');
        }

        $validated = $request->validate([
            'delivered_to' => ['nullable', 'string', 'max:255'],
        ]);

        $deliveredTo = trim((string) ($validated['delivered_to'] ?? ''));

        $admission->update([
            'student_status' => Admission::CERTIFICATE_STATUS_DELIVERED,
            'status_updated_at' => now(),
            'certificate_delivered_at' => now(),
            'certificate_delivered_by' => optional($request->user())->id,
            'certificate_delivery_notes' => $deliveredTo !== ''
                ? 'Delivered to: ' . $deliveredTo
                : 'Certificate delivered.',
        ]);

        return redirect()->route('certificate.index')
            ->with('status', 'Certificate delivered.');
    }

    private function applyScope(Builder $query, string $scope): void
    {
        if ($scope !== 'all' && in_array($scope, self::ALLOWED_SCOPES, true)) {
            $query->where('student_status', $scope);
        }
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
            ->selectRaw('student_status, COUNT(*) as aggregate')
            ->groupBy('student_status')
            ->pluck('aggregate', 'student_status');

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
            ->where('student_status', Admission::CERTIFICATE_REQUESTABLE_STATUS);
    }

    private function certificateWorkflowQuery($user = null): Builder
    {
        return $this->scopeQueryToUserCampus(Admission::query(), $user)
            ->certificateWorkflow();
    }

    private function ensureCertificateWorkflowAdmission(Admission $admission): void
    {
        if (! in_array((string) ($admission->student_status ?? ''), Admission::CERTIFICATE_WORKFLOW_STATUSES, true)) {
            abort(404);
        }
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

    private function mergeCertificateRemarks(?string $currentRemarks, ?string $newRemarks): ?string
    {
        $current = trim((string) $currentRemarks);
        $incoming = trim((string) $newRemarks);

        if ($incoming === '') {
            return $current !== '' ? $current : null;
        }

        if ($current === '') {
            return $incoming;
        }

        return $current . PHP_EOL . $incoming;
    }
}
