<?php

namespace App\Http\Controllers;

use App\Models\Admission;
use App\Models\FeeCollection;
use App\Services\FinanceAccountingService;
use App\Support\ResolvesCampusScope;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class StudentRecordController extends Controller
{
    use ResolvesCampusScope;

    private const STATUS_OPTIONS = [
        'enrolled' => 'Enrolled',
        'concluded' => 'Concluded',
        'frozen' => 'Frozen',
        'incomplete' => 'Incomplete',
        'suspended' => 'Suspended',
        'admission_cancelled' => 'Cancelled',
        'dropped' => 'Dropped',
    ];

    public function index(Request $request, ?string $scope = null)
    {
        $scope = $scope ?: 'all_students';
        $config = $this->resolveScope($scope);

        if ($request->ajax()) {
            $query = $this->scopeQueryToUserCampus(Admission::query()->approved(), $request->user())
                ->with([
                    'campus:id,code,name',
                    'program:id,code,title,name',
                    'batch:id,code,name',
                    'registration:id,registration_number',
                ])
                ->select('admissions.*');

            $this->applyScope($query, $config['scope']);

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->editColumn('student_name', fn (Admission $admission) => e($admission->student_name ?: 'N/A'))
                ->editColumn('roll_number', fn (Admission $admission) => e($admission->roll_number ?: 'N/A'))
                ->editColumn('phone', fn (Admission $admission) => e($admission->phone ?: 'N/A'))
                ->editColumn('registration_number', fn (Admission $admission) => e($admission->registration_number ?: optional($admission->registration)->registration_number ?: 'N/A'))
                ->editColumn('admission_date', fn (Admission $admission) => optional($admission->admission_date)->format('d-M-Y') ?? 'N/A')
                ->addColumn('campus_code', fn (Admission $admission) => e(optional($admission->campus)->code ?? optional($admission->campus)->name ?? 'N/A'))
                ->addColumn('program_name', fn (Admission $admission) => e(optional($admission->program)->title ?? optional($admission->program)->name ?? 'N/A'))
                ->addColumn('batch_name', fn (Admission $admission) => e(optional($admission->batch)->code ?? optional($admission->batch)->name ?? 'N/A'))
                ->addColumn('status_badge', function (Admission $admission) {
                    $label = self::STATUS_OPTIONS[$admission->student_status] ?? ucfirst(str_replace('_', ' ', (string) $admission->student_status));
                    $class = match ($admission->student_status) {
                        'enrolled' => 'label-success',
                        'concluded' => 'label-primary',
                        'frozen' => 'label-warning',
                        'incomplete' => 'label-default',
                        'suspended' => 'label-info',
                        'admission_cancelled' => 'label-danger',
                        'dropped' => 'label-danger',
                        default => 'label-default',
                    };

                    return '<span class="label ' . $class . '">' . e($label) . '</span>';
                })
                ->addColumn('certificate_status', function (Admission $admission) {
                    if (!$admission->certificate_delivered_at) {
                        return '<span class="label label-default">Pending</span>';
                    }

                    return '<span class="label label-success">Delivered</span><div class="text-muted small mt-1">'
                        . e(optional($admission->certificate_delivered_at)->format('d-M-Y'))
                        . '</div>';
                })
                ->addColumn('actions', fn (Admission $admission) => view('student.partials.action', [
                    'admission' => $admission,
                    'statusOptions' => self::STATUS_OPTIONS,
                ])->render())
                ->filterColumn('campus_code', function ($query, $keyword) {
                    $query->whereHas('campus', function ($campusQuery) use ($keyword) {
                        $campusQuery
                            ->where('code', 'like', "%{$keyword}%")
                            ->orWhere('name', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('program_name', function ($query, $keyword) {
                    $query->whereHas('program', function ($programQuery) use ($keyword) {
                        $programQuery
                            ->where('title', 'like', "%{$keyword}%")
                            ->orWhere('name', 'like', "%{$keyword}%")
                            ->orWhere('code', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('batch_name', function ($query, $keyword) {
                    $query->whereHas('batch', function ($batchQuery) use ($keyword) {
                        $batchQuery
                            ->where('code', 'like', "%{$keyword}%")
                            ->orWhere('name', 'like', "%{$keyword}%");
                    });
                })
                ->rawColumns(['status_badge', 'certificate_status', 'actions'])
                ->make(true);
        }

        return view('student.records.index', [
            'scope' => $config['scope'],
            'pageTitle' => $config['title'],
            'pageDescription' => $config['description'],
        ]);
    }

    public function show(\App\Models\Registration $registration): View
    {
        $this->ensureCampusAccess((int) ($registration->campus_id ?? 0), auth()->user(), 'You are not allowed to access student records from another campus.');

        $registration->load([
            'lead',
            'campus',
            'program',
        ]);

        $this->backfillRegistrationFee($registration);

        $admission = Admission::query()
            ->with([
                'batch',
                'campus',
                'program',
                'certificateDeliveredBy',
            ])
            ->where('registration_id', $registration->id)
            ->latest('id')
            ->first();
        $feeCollectionsQuery = FeeCollection::query()
            ->where('registration_id', $registration->id);

        if (! $admission) {
            $feeCollectionsQuery->where(function ($query) {
                $query
                    ->whereNull('admission_id')
                    ->orWhere('fee_type', 'registration');
            });
        }

        $feeCollections = $feeCollectionsQuery
            ->orderBy('paid_at')
            ->orderBy('id')
            ->get();

        $totalFee = $feeCollections->sum('net_amount');
        $pendingFee = $feeCollections->where('status', '!=', 'paid')->sum('net_amount');

        return view('student.show', [
            'registration' => $registration,
            'admission' => $admission,
            'feeCollections' => $feeCollections,
            'totalFee' => $totalFee,
            'pendingFee' => $pendingFee,
            'statusOptions' => self::STATUS_OPTIONS,
        ]);
    }

    private function backfillRegistrationFee(\App\Models\Registration $registration): void
    {
        $hasFee = FeeCollection::where('registration_id', $registration->id)
            ->where('fee_type', 'registration')
            ->exists();
        if ($hasFee) {
            return;
        }

        $amount = (float) ($registration->fee ?? 0);
        if ($amount <= 0) {
            return;
        }
        $discount = (float) ($registration->discount ?? 0);
        $net = (float) ($registration->net_payable ?? ($amount - $discount));

        $registrationFee = FeeCollection::create([
            'lead_id' => $registration->lead_id,
            'registration_id' => $registration->id,
            'campus_id' => $registration->campus_id,
            'program_id' => $registration->program_id,
            'fee_type' => 'registration',
            'amount' => $amount,
            'discount_percent' => 0,
            'discount_amount' => $discount,
            'net_amount' => $net,
            'receipt_number' => $registration->receipt_number,
            'status' => 'paid',
            'paid_at' => $registration->registered_at ?? $registration->created_at,
            'notes' => 'Registration fee backfilled from registration record.',
        ]);

        app(FinanceAccountingService::class)->syncFeeCollection($registrationFee);
    }

    public function collectInstallment(Request $request, FeeCollection $feeCollection): RedirectResponse
    {
        $this->ensureCampusAccess((int) ($feeCollection->campus_id ?? 0), $request->user(), 'You are not allowed to update fee records from another campus.');

        if ($feeCollection->admission_id) {
            $approvedAdmissionExists = Admission::query()
                ->approved()
                ->whereKey($feeCollection->admission_id)
                ->exists();

            if (! $approvedAdmissionExists) {
                return back()->with('error', 'Installments can only be collected for approved admissions.');
            }
        }

        $validated = $request->validate([
            'paid_amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        $paid = round((float) $validated['paid_amount'], 2);
        try {
            $alreadyCollected = false;

            DB::transaction(function () use ($feeCollection, $paid, &$alreadyCollected) {
                $lockedFee = FeeCollection::query()
                    ->with(['campus', 'admission.campus', 'registration.campus'])
                    ->lockForUpdate()
                    ->findOrFail($feeCollection->id);

                if ($lockedFee->status === 'paid') {
                    $alreadyCollected = true;
                    return;
                }

                $original = round((float) $lockedFee->net_amount, 2);
                $diff = round($original - $paid, 2);
                $nextPending = $this->nextPendingAdmissionInstallmentsQuery($lockedFee)
                    ->lockForUpdate()
                    ->get();

                $this->ensureInstallmentDifferenceCanBeDistributed($diff, $nextPending);

                $lockedFee->update([
                    'amount' => $paid,
                    'net_amount' => $paid,
                    'receipt_number' => $lockedFee->fee_type === 'admission' && $lockedFee->admission_id
                        ? $this->generateFeeReceiptNumber($lockedFee)
                        : $lockedFee->receipt_number,
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);

                $this->redistributeInstallmentDifference($nextPending, $diff);
            });

            if ($alreadyCollected) {
                return back()->with('status', 'Installment already collected.');
            }

            app(FinanceAccountingService::class)->syncFeeCollection($feeCollection->fresh());
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first() ?: 'Unable to collect installment.');
        }

        return back()->with('status', 'Installment collected.');
    }

    public function updateFee(Request $request, FeeCollection $feeCollection): RedirectResponse
    {
        $this->ensureCampusAccess((int) ($feeCollection->campus_id ?? 0), $request->user(), 'You are not allowed to update fee records from another campus.');

        $validated = $request->validate([
            'net_amount' => ['required', 'numeric', 'min:0'],
            'paid_at' => ['required', 'date'],
        ]);

        $feeCollection->update([
            'net_amount' => $validated['net_amount'],
            'paid_at' => $validated['paid_at'],
        ]);

        if ($feeCollection->status === 'paid' && $feeCollection->paid_at) {
            app(FinanceAccountingService::class)->syncFeeCollection($feeCollection->fresh());
        }

        return back()->with('status', 'Fee details updated.');
    }

    public function updateStatus(Request $request, Admission $admission): RedirectResponse
    {
        $this->ensureCampusAccess((int) ($admission->campus_id ?? 0), $request->user(), 'You are not allowed to update student records from another campus.');

        if (($admission->approval_status ?? Admission::APPROVAL_STATUS_APPROVED) !== Admission::APPROVAL_STATUS_APPROVED) {
            return back()->with('error', 'Student status can only be updated after admission approval.');
        }

        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys(self::STATUS_OPTIONS))],
        ]);

        $admission->update([
            'student_status' => $validated['status'],
            'status_updated_at' => now(),
        ]);

        return back()->with('status', 'Student status updated.');
    }

    public function markCertificateDelivered(Request $request, Admission $admission): RedirectResponse
    {
        $this->ensureCampusAccess((int) ($admission->campus_id ?? 0), $request->user(), 'You are not allowed to update student records from another campus.');

        if (($admission->approval_status ?? Admission::APPROVAL_STATUS_APPROVED) !== Admission::APPROVAL_STATUS_APPROVED) {
            return back()->with('error', 'Certificates can only be delivered for approved admissions.');
        }

        $validated = $request->validate([
            'certificate_delivery_notes' => ['nullable', 'string'],
        ]);

        $admission->update([
            'certificate_delivered_at' => now(),
            'certificate_delivered_by' => $request->user()?->id,
            'certificate_delivery_notes' => $validated['certificate_delivery_notes'] ?? $admission->certificate_delivery_notes,
        ]);

        return back()->with('status', 'Certificate marked as delivered.');
    }

    private function applyScope($query, string $scope): void
    {
        if ($scope === 'all_students') {
            return;
        }

        if ($scope === 'alumni') {
            $query->whereNotNull('certificate_delivered_at');
            return;
        }

        $status = $scope === 'active' ? 'enrolled' : $scope;
        $query->where('student_status', $status);
    }

    private function resolveScope(string $scope): array
    {
        return match ($scope) {
            'active' => [
                'scope' => 'active',
                'title' => 'Active Students',
                'description' => 'All students currently in enrolled status.',
            ],
            'concluded' => [
                'scope' => 'concluded',
                'title' => 'Concluded Students',
                'description' => 'Students whose academic status is concluded.',
            ],
            'frozen' => [
                'scope' => 'frozen',
                'title' => 'Frozen Students',
                'description' => 'Students currently marked as frozen.',
            ],
            'incomplete' => [
                'scope' => 'incomplete',
                'title' => 'Incomplete Students',
                'description' => 'Students with incomplete admission or academic processing.',
            ],
            'suspended' => [
                'scope' => 'suspended',
                'title' => 'Suspended Students',
                'description' => 'Students currently suspended.',
            ],
            'admission_cancelled' => [
                'scope' => 'admission_cancelled',
                'title' => 'Cancelled',
                'description' => 'Students whose admissions have been cancelled.',
            ],
            'dropped' => [
                'scope' => 'dropped',
                'title' => 'Dropped Students',
                'description' => 'Students marked as dropped.',
            ],
            'alumni' => [
                'scope' => 'alumni',
                'title' => 'Alumni Students',
                'description' => 'Students whose certificates have been delivered.',
            ],
            default => [
                'scope' => 'all_students',
                'title' => 'All Students',
                'description' => 'All admitted students regardless of current status.',
            ],
        };
    }

    private function generateFeeReceiptNumber(FeeCollection $feeCollection): string
    {
        $campusCode = $feeCollection->campus?->code
            ?? optional($feeCollection->admission?->campus)->code
            ?? optional($feeCollection->registration?->campus)->code
            ?? 'GEN';

        $prefix = $campusCode . '-' . Carbon::now()->format('my') . '-';
        $next = FeeCollection::query()
            ->where('receipt_number', 'like', $prefix . '%')
            ->get(['receipt_number'])
            ->map(function (FeeCollection $row) use ($prefix) {
                $tail = substr((string) $row->receipt_number, strlen($prefix));

                return ctype_digit($tail) ? (int) $tail : 0;
            })
            ->max();

        return $prefix . str_pad((string) (((int) $next) + 1), 6, '0', STR_PAD_LEFT);
    }

    private function nextPendingAdmissionInstallmentsQuery(FeeCollection $feeCollection)
    {
        return FeeCollection::query()
            ->where('admission_id', $feeCollection->admission_id)
            ->where('fee_type', 'admission')
            ->where('status', 'pending')
            ->where('id', '!=', $feeCollection->id)
            ->when($feeCollection->installment_no, function ($query) use ($feeCollection) {
                $query->where('installment_no', '>', $feeCollection->installment_no);
            })
            ->orderBy('installment_no')
            ->orderBy('id');
    }

    private function ensureInstallmentDifferenceCanBeDistributed(float $diff, $nextPendingInstallments): void
    {
        if ($diff === 0.0) {
            return;
        }

        if ($nextPendingInstallments->isEmpty()) {
            throw ValidationException::withMessages([
                'paid_amount' => ['Exact installment amount is required because no next pending installment is available.'],
            ]);
        }

        if ($diff < 0.0) {
            $remainingScheduled = round($nextPendingInstallments->sum(fn (FeeCollection $fee) => (float) $fee->net_amount), 2);

            if ($remainingScheduled + 0.00001 < abs($diff)) {
                throw ValidationException::withMessages([
                    'paid_amount' => ['Paid amount cannot exceed the remaining scheduled installment balance.'],
                ]);
            }
        }
    }

    private function redistributeInstallmentDifference($nextPendingInstallments, float $diff): void
    {
        if ($diff === 0.0 || $nextPendingInstallments->isEmpty()) {
            return;
        }

        $remainder = $diff;

        foreach ($nextPendingInstallments as $nextPendingInstallment) {
            if ($remainder === 0.0) {
                break;
            }

            if ($remainder > 0.0) {
                $newAmount = round((float) $nextPendingInstallment->net_amount + $remainder, 2);

                $nextPendingInstallment->update([
                    'amount' => $newAmount,
                    'net_amount' => $newAmount,
                ]);

                $remainder = 0.0;
                break;
            }

            $newAmount = round((float) $nextPendingInstallment->net_amount + $remainder, 2);

            if ($newAmount <= 0.0) {
                $remainder = $newAmount;
                $nextPendingInstallment->delete();
                continue;
            }

            $nextPendingInstallment->update([
                'amount' => $newAmount,
                'net_amount' => $newAmount,
            ]);

            $remainder = 0.0;
        }
    }
}
