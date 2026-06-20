<?php

namespace App\Http\Controllers;

use App\Models\Admission;
use App\Models\Campus;
use App\Models\Certificate;
use App\Models\Program;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CertificateController extends Controller
{
    private const ALLOWED_SCOPES = [
        'all',
        'requested',
        'approved',
        'printing',
        'ready',
        'delivered',
        'rejected',
    ];

    public function index(Request $request): View
    {
        $scope = (string) $request->query('scope', 'all');
        if (!in_array($scope, self::ALLOWED_SCOPES, true)) {
            $scope = 'all';
        }

        $query = Certificate::query()
            ->with(['admission', 'campus', 'program', 'requester', 'approver', 'deliverer']);

        $this->applyScope($query, $scope);
        $this->applyFilters($query, $request);

        $certificates = $query
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('certificate.index', [
            'certificates' => $certificates,
            'activeScope' => $scope,
            'scopeCards' => $this->buildScopeCards($request),
            'campuses' => Campus::query()->orderBy('name')->get(['id', 'code', 'name']),
            'programs' => Program::query()->orderByRaw('COALESCE(title, name)')->get(['id', 'code', 'title', 'name']),
            'filters' => [
                'scope' => $scope,
                'campus_id' => $request->integer('campus_id') ?: null,
                'program_id' => $request->integer('program_id') ?: null,
                'search' => $request->input('search'),
            ],
            'pageTitle' => $this->resolvePageTitle($scope),
        ]);
    }

    public function create(Request $request): View
    {
        $admissionId = $request->integer('admission_id') ?: null;
        $selectedAdmission = $admissionId
            ? Admission::query()->approved()->with(['program', 'campus'])->find($admissionId)
            : null;

        return view('certificate.create', [
            'admissions' => Admission::query()
                ->approved()
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

        $admission = Admission::query()->approved()->findOrFail($validated['admission_id']);

        Certificate::create([
            'admission_id' => $admission->id,
            'campus_id' => $admission->campus_id,
            'program_id' => $admission->program_id,
            'certificate_number' => $this->generateCertificateNumber(),
            'status' => Certificate::STATUS_REQUESTED,
            'requested_by' => optional($request->user())->id,
            'requested_at' => now(),
            'remarks' => $validated['remarks'] ?? null,
        ]);

        return redirect()->route('certificate.index')
            ->with('status', 'Certificate request created.');
    }

    public function edit(Certificate $certificate): View
    {
        $certificate->load(['admission.program', 'admission.campus']);

        return view('certificate.edit', [
            'certificate' => $certificate,
        ]);
    }

    public function update(Request $request, Certificate $certificate): RedirectResponse
    {
        $validated = $request->validate([
            'remarks' => ['nullable', 'string'],
        ]);

        $certificate->update([
            'remarks' => $validated['remarks'] ?? null,
        ]);

        return redirect()->route('certificate.index')
            ->with('status', 'Certificate updated.');
    }

    public function destroy(Certificate $certificate): RedirectResponse
    {
        if ($certificate->status === Certificate::STATUS_DELIVERED) {
            return redirect()->route('certificate.index')
                ->with('error', 'Delivered certificates cannot be deleted.');
        }

        $certificate->delete();

        return redirect()->route('certificate.index')
            ->with('status', 'Certificate removed.');
    }

    public function approve(Request $request, Certificate $certificate): RedirectResponse
    {
        if ($certificate->status !== Certificate::STATUS_REQUESTED) {
            return redirect()->route('certificate.index')
                ->with('error', 'Only requested certificates can be approved.');
        }

        $certificate->update([
            'status' => Certificate::STATUS_APPROVED,
            'approved_by' => optional($request->user())->id,
            'approved_at' => now(),
        ]);

        return redirect()->route('certificate.index')
            ->with('status', 'Certificate approved.');
    }

    public function reject(Request $request, Certificate $certificate): RedirectResponse
    {
        if (!in_array($certificate->status, [Certificate::STATUS_REQUESTED, Certificate::STATUS_APPROVED], true)) {
            return redirect()->route('certificate.index')
                ->with('error', 'Only requested or approved certificates can be rejected.');
        }

        $validated = $request->validate([
            'rejection_reason' => ['nullable', 'string', 'max:255'],
        ]);

        $certificate->update([
            'status' => Certificate::STATUS_REJECTED,
            'rejected_by' => optional($request->user())->id,
            'rejected_at' => now(),
            'rejection_reason' => $validated['rejection_reason'] ?? null,
        ]);

        return redirect()->route('certificate.index')
            ->with('status', 'Certificate rejected.');
    }

    public function sendToPrinting(Certificate $certificate): RedirectResponse
    {
        if ($certificate->status !== Certificate::STATUS_APPROVED) {
            return redirect()->route('certificate.index')
                ->with('error', 'Only approved certificates can be sent to printing.');
        }

        $certificate->update([
            'status' => Certificate::STATUS_PRINTING,
            'printing_at' => now(),
        ]);

        return redirect()->route('certificate.index')
            ->with('status', 'Certificate sent to printing.');
    }

    public function markReady(Certificate $certificate): RedirectResponse
    {
        if ($certificate->status !== Certificate::STATUS_PRINTING) {
            return redirect()->route('certificate.index')
                ->with('error', 'Only printing certificates can be marked ready.');
        }

        $certificate->update([
            'status' => Certificate::STATUS_READY,
            'ready_at' => now(),
        ]);

        return redirect()->route('certificate.index')
            ->with('status', 'Certificate marked ready for collection.');
    }

    public function markDelivered(Request $request, Certificate $certificate): RedirectResponse
    {
        if ($certificate->status !== Certificate::STATUS_READY) {
            return redirect()->route('certificate.index')
                ->with('error', 'Only ready certificates can be marked delivered.');
        }

        $validated = $request->validate([
            'delivered_to' => ['nullable', 'string', 'max:255'],
        ]);

        $certificate->update([
            'status' => Certificate::STATUS_DELIVERED,
            'delivered_at' => now(),
            'delivered_by' => optional($request->user())->id,
            'delivered_to' => $validated['delivered_to'] ?? $certificate->admission?->student_name,
        ]);

        // Mirror onto the admission for legacy compatibility
        if ($certificate->admission_id) {
            Admission::query()->where('id', $certificate->admission_id)->update([
                'certificate_delivered_at' => now(),
                'certificate_delivered_by' => optional($request->user())->id,
                'certificate_delivery_notes' => 'Certificate ' . $certificate->certificate_number . ' delivered.',
            ]);
        }

        return redirect()->route('certificate.index')
            ->with('status', 'Certificate delivered.');
    }

    private function applyScope(Builder $query, string $scope): void
    {
        if ($scope !== 'all' && in_array($scope, self::ALLOWED_SCOPES, true)) {
            $query->where('status', $scope);
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
                        ->where('certificate_number', 'like', "%{$keyword}%")
                        ->orWhere('delivered_to', 'like', "%{$keyword}%")
                        ->orWhereHas('admission', function (Builder $a) use ($keyword) {
                            $a->where('student_name', 'like', "%{$keyword}%")
                                ->orWhere('roll_number', 'like', "%{$keyword}%")
                                ->orWhere('registration_number', 'like', "%{$keyword}%");
                        });
                });
            });
    }

    private function buildScopeCards(Request $request): array
    {
        $counts = Certificate::query()
            ->tap(fn (Builder $q) => $this->applyFilters($q, $request))
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

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
            'requested' => 'Certificates — Request for Approval',
            'approved' => 'Certificates — Approved',
            'printing' => 'Certificates — On Printing',
            'ready' => 'Certificates — Ready',
            'delivered' => 'Certificates — Delivered',
            'rejected' => 'Certificates — Rejected',
            default => 'Certificate Management',
        };
    }

    private function generateCertificateNumber(): string
    {
        $year = Carbon::now()->year;
        $prefix = 'CERT-' . $year . '-';

        return DB::transaction(function () use ($prefix, $year) {
            $maxSeq = Certificate::query()
                ->where('certificate_number', 'like', $prefix . '%')
                ->lockForUpdate()
                ->count();

            $candidate = $prefix . str_pad((string) ($maxSeq + 1), 5, '0', STR_PAD_LEFT);

            // Fallback in case of legacy gaps
            $i = $maxSeq + 1;
            while (Certificate::query()->where('certificate_number', $candidate)->exists()) {
                $i++;
                $candidate = $prefix . str_pad((string) $i, 5, '0', STR_PAD_LEFT);
            }

            return $candidate;
        });
    }
}
