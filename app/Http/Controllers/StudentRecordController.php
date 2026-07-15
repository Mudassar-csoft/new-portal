<?php

namespace App\Http\Controllers;

use App\Models\Admission;
use App\Models\Batch;
use App\Models\Campus;
use App\Models\FeeCollection;
use App\Services\FinanceAccountingService;
use App\Support\ResolvesCampusScope;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
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
        $scopeCounts = $this->studentScopeCounts($request->user());

        if ($request->ajax()) {
            $query = $this->baseStudentRecordsQuery($request->user())
                ->with([
                    'campus:id,code,name',
                    'program:id,code,title,name',
                    'batch:id,code,name',
                    'registration:id,registration_number',
                ])
                ->select('admissions.*');

            $this->applyScope($query, $config['scope']);
            $searchValue = trim((string) $request->input('search.value', ''));

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->editColumn('student_name', function (Admission $admission) {
                    $studentName = e($admission->student_name ?: 'N/A');
                    $registrationId = (int) ($admission->registration_id ?: optional($admission->registration)->id ?: 0);

                    if ($registrationId <= 0) {
                        return $studentName;
                    }

                    return sprintf(
                        '<a href="%s" class="table-name-link">%s</a>',
                        e(route('student.show', $registrationId)),
                        $studentName
                    );
                })
                ->editColumn('roll_number', fn (Admission $admission) => e($admission->roll_number ?: 'N/A'))
                ->editColumn('phone', fn (Admission $admission) => e($admission->phone ?: 'N/A'))
                ->editColumn('registration_number', fn (Admission $admission) => e($admission->registration_number ?: optional($admission->registration)->registration_number ?: 'N/A'))
                ->editColumn('admission_date', fn (Admission $admission) => optional($admission->admission_date)->format('d-M-Y') ?? 'N/A')
                ->addColumn('campus_code', fn (Admission $admission) => e(optional($admission->campus)->code ?? optional($admission->campus)->name ?? 'N/A'))
                ->addColumn('program_name', fn (Admission $admission) => e(optional($admission->program)->title ?? optional($admission->program)->name ?? 'N/A'))
                ->addColumn('batch_name', fn (Admission $admission) => e(optional($admission->batch)->code ?? optional($admission->batch)->name ?? 'N/A'))
                ->addColumn('certificate_status', function (Admission $admission) {
                    [$label, $class] = $this->certificateStatusMeta($admission);
                    $details = '';

                    if (($admission->student_status ?? null) === Admission::CERTIFICATE_STATUS_DELIVERED && $admission->certificate_delivered_at) {
                        $details = '<div class="text-muted small mt-1">' . e($admission->certificate_delivered_at->format('d-M-Y')) . '</div>';
                    }

                    return '<span class="label ' . e($class) . '">' . e($label) . '</span>' . $details;
                })
                ->addColumn('actions', fn (Admission $admission) => view('student.partials.action', [
                    'admission' => $admission,
                    'statusOptions' => self::STATUS_OPTIONS,
                ])->render())
                ->filter(function (Builder $query) use ($searchValue) {
                    if ($searchValue === '') {
                        return;
                    }

                    $this->applyStudentRecordsSearch($query, $searchValue);
                })
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
                ->rawColumns(['student_name', 'certificate_status', 'actions'])
                ->make(true);
        }

        return view('student.records.index', [
            'scope' => $config['scope'],
            'scopeCounts' => $scopeCounts,
            'pageTitle' => $config['title'],
            'pageDescription' => $config['description'],
        ]);
    }

    public function show(Request $request, \App\Models\Registration $registration): View
    {
        if (! $this->canReviewAdmissions($request->user())) {
            $this->ensureCampusAccess((int) ($registration->campus_id ?? 0), $request->user(), 'You are not allowed to access student records from another campus.');
        }

        $registration->load([
            'lead',
            'campus',
            'program',
        ]);

        $this->backfillRegistrationFee($registration);

        $admissions = Admission::query()
            ->with([
                'batch',
                'campus',
                'program',
                'documentsUploadedBy',
                'approvalReviewedBy',
                'certificateDeliveredBy',
            ])
            ->where('registration_id', $registration->id)
            ->latest('id')
            ->get();
        $latestAdmission = $admissions->first();

        $feeCollectionsQuery = FeeCollection::query()
            ->with([
                'admission.batch',
                'admission.campus',
                'admission.program',
                'program',
            ])
            ->where('registration_id', $registration->id);

        if ($admissions->isEmpty()) {
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
            'admissions' => $admissions,
            'latestAdmission' => $latestAdmission,
            'feeCollections' => $feeCollections,
            'totalFee' => $totalFee,
            'pendingFee' => $pendingFee,
            'canAdmissionReview' => $this->canReviewAdmissions($request->user()),
            'statusOptions' => self::STATUS_OPTIONS,
            'certificateStatusLabels' => array_intersect_key(
                Admission::STUDENT_STATUS_LABELS,
                array_flip(Admission::CERTIFICATE_WORKFLOW_STATUSES)
            ),
            'certificateStatusClasses' => array_intersect_key(
                Admission::STUDENT_STATUS_BADGE_CLASSES,
                array_flip(Admission::CERTIFICATE_WORKFLOW_STATUSES)
            ),
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

        $validated = $request->validate([
            'paid_amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        $paid = round((float) $validated['paid_amount'], 2);
        try {
            $alreadyCollected = false;

            DB::transaction(function () use ($feeCollection, $paid, $request, &$alreadyCollected) {
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
                    'created_by' => $request->user()?->id,
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
            'student_status' => Admission::CERTIFICATE_STATUS_DELIVERED,
            'status_updated_at' => now(),
            'certificate_delivered_at' => now(),
            'certificate_delivered_by' => $request->user()?->id,
            'certificate_delivery_notes' => $validated['certificate_delivery_notes'] ?? $admission->certificate_delivery_notes,
        ]);

        return back()->with('status', 'Certificate marked as delivered.');
    }

    public function transferMeta(Request $request, Admission $admission): JsonResponse
    {
        $this->ensureCampusAccess((int) ($admission->campus_id ?? 0), $request->user(), 'You are not allowed to update student records from another campus.');

        $admission->loadMissing([
            'campus:id,code,name',
            'program:id,code,title,name',
            'batch:id,program_id,campus_id,code,name,status,start_time,end_time',
            'batch.timetables:id,batch_id,day_of_week,start_time,end_time,status',
        ]);

        $campuses = Campus::query()
            ->orderByRaw("CASE WHEN COALESCE(code, '') = '' THEN 1 ELSE 0 END")
            ->orderBy('code')
            ->orderBy('name')
            ->get(['id', 'code', 'name']);

        $batches = Batch::query()
            ->with([
                'campus:id,code,name',
                'timetables:id,batch_id,day_of_week,start_time,end_time,status',
            ])
            ->where('program_id', $admission->program_id)
            ->when($admission->batch_id, fn ($query, $batchId) => $query->whereKeyNot($batchId))
            ->where(function ($query) {
                $query
                    ->whereNull('status')
                    ->orWhereNotIn('status', ['completed', 'cancelled']);
            })
            ->orderBy('campus_id')
            ->orderBy('code')
            ->orderBy('name')
            ->get(['id', 'program_id', 'campus_id', 'code', 'name', 'status', 'start_time', 'end_time']);

        return response()->json([
            'admission' => [
                'id' => $admission->id,
                'student_name' => $admission->student_name ?: 'N/A',
                'current_campus_id' => (int) ($admission->campus_id ?? 0),
                'current_campus' => $this->formatCampusLabel($admission->campus),
                'program' => $this->formatProgramLabel($admission->program),
                'current_batch' => $this->formatBatchLabel($admission->batch),
                'current_timing' => $this->formatBatchTiming($admission->batch),
            ],
            'campuses' => $campuses
                ->map(fn (Campus $campus) => [
                    'id' => $campus->id,
                    'label' => $this->formatCampusLabel($campus),
                ])
                ->values(),
            'batches' => $batches
                ->map(fn (Batch $batch) => [
                    'id' => $batch->id,
                    'campus_id' => (int) $batch->campus_id,
                    'label' => $this->formatBatchLabel($batch),
                    'timing' => $this->formatBatchTiming($batch),
                    'status' => (string) ($batch->status ?? 'active'),
                ])
                ->values(),
        ]);
    }

    public function transfer(Request $request, Admission $admission): RedirectResponse
    {
        $this->ensureCampusAccess((int) ($admission->campus_id ?? 0), $request->user(), 'You are not allowed to update student records from another campus.');

        $validated = $request->validate([
            'campus_id' => ['required', 'integer', 'exists:campuses,id'],
            'batch_id' => ['required', 'integer', 'exists:batches,id'],
            'remarks' => ['nullable', 'string', 'max:2000'],
        ]);

        $targetCampus = Campus::query()->findOrFail($validated['campus_id']);
        $targetBatch = Batch::query()
            ->with(['campus:id,code,name', 'program:id,code,title,name'])
            ->findOrFail($validated['batch_id']);

        if ((int) $targetBatch->campus_id !== (int) $targetCampus->id) {
            return back()->with('error', 'Selected batch does not belong to the selected campus.');
        }

        if ((int) ($admission->program_id ?? 0) > 0 && (int) $targetBatch->program_id !== (int) $admission->program_id) {
            return back()->with('error', 'Selected batch does not match the student program.');
        }

        if (in_array((string) ($targetBatch->status ?? 'active'), ['completed', 'cancelled'], true)) {
            return back()->with('error', 'Selected batch is not available for transfer.');
        }

        if ((int) ($admission->campus_id ?? 0) === (int) $targetCampus->id && (int) ($admission->batch_id ?? 0) === (int) $targetBatch->id) {
            return back()->with('error', 'Please choose a different campus or batch.');
        }

        DB::transaction(function () use ($admission, $request, $targetCampus, $targetBatch, $validated) {
            $lockedAdmission = Admission::query()
                ->with([
                    'campus:id,code,name',
                    'batch:id,code,name',
                ])
                ->lockForUpdate()
                ->findOrFail($admission->id);

            $fromCampusLabel = $this->formatCampusLabel($lockedAdmission->campus);
            $fromBatchLabel = $this->formatBatchLabel($lockedAdmission->batch);
            $toCampusLabel = $this->formatCampusLabel($targetCampus);
            $toBatchLabel = $this->formatBatchLabel($targetBatch);

            $lockedAdmission->update([
                'campus_id' => $targetCampus->id,
                'batch_id' => $targetBatch->id,
                'remarks' => $this->appendAdmissionRemark(
                    $lockedAdmission->remarks,
                    $this->buildTransferRemark(
                        $request->user()?->name,
                        $fromCampusLabel,
                        $fromBatchLabel,
                        $toCampusLabel,
                        $toBatchLabel,
                        $validated['remarks'] ?? null
                    )
                ),
            ]);

            FeeCollection::query()
                ->where('admission_id', $lockedAdmission->id)
                ->whereNull('paid_at')
                ->where(function ($query) {
                    $query
                        ->whereNull('status')
                        ->orWhere('status', '!=', 'paid');
                })
                ->update([
                    'campus_id' => $targetCampus->id,
                    'program_id' => $targetBatch->program_id,
                ]);
        });

        return back()->with(
            'status',
            'Student transferred to ' . $this->formatCampusLabel($targetCampus) . ' / ' . $this->formatBatchLabel($targetBatch) . '. Paid fee stays on the previous campus and pending fee moves to the new campus.'
        );
    }

    private function applyScope($query, string $scope): void
    {
        if ($scope === 'all_students') {
            return;
        }

        if ($scope === 'alumni') {
            $query->where(function ($studentQuery) {
                $studentQuery
                    ->where('student_status', Admission::CERTIFICATE_STATUS_DELIVERED)
                    ->orWhereNotNull('certificate_delivered_at');
            });
            return;
        }

        $status = $scope === 'active' ? 'enrolled' : $scope;
        $query->where('student_status', $status);
    }

    private function baseStudentRecordsQuery($user): Builder
    {
        return $this->scopeQueryToUserCampus(
            Admission::query()->approved(),
            $user
        );
    }

    private function studentScopeCounts($user): array
    {
        $baseQuery = $this->baseStudentRecordsQuery($user);

        return [
            'active' => (clone $baseQuery)->where('student_status', 'enrolled')->count(),
            'frozen' => (clone $baseQuery)->where('student_status', 'frozen')->count(),
            'concluded' => (clone $baseQuery)->where('student_status', 'concluded')->count(),
            'incomplete' => (clone $baseQuery)->where('student_status', 'incomplete')->count(),
            'suspended' => (clone $baseQuery)->where('student_status', 'suspended')->count(),
            'admission_cancelled' => (clone $baseQuery)->where('student_status', 'admission_cancelled')->count(),
            'dropped' => (clone $baseQuery)->where('student_status', 'dropped')->count(),
            'all_students' => (clone $baseQuery)->count(),
            'alumni' => (clone $baseQuery)
                ->where(function (Builder $query) {
                    $query
                        ->where('student_status', Admission::CERTIFICATE_STATUS_DELIVERED)
                        ->orWhereNotNull('certificate_delivered_at');
                })
                ->count(),
        ];
    }

    private function applyStudentRecordsSearch(Builder $query, string $keyword): void
    {
        $like = '%' . trim($keyword) . '%';

        $query->where(function (Builder $builder) use ($like) {
            $builder
                ->where('student_name', 'like', $like)
                ->orWhere('roll_number', 'like', $like)
                ->orWhere('phone', 'like', $like)
                ->orWhere('registration_number', 'like', $like)
                ->orWhereHas('registration', function (Builder $registrationQuery) use ($like) {
                    $registrationQuery->where('registration_number', 'like', $like);
                })
                ->orWhereHas('campus', function (Builder $campusQuery) use ($like) {
                    $campusQuery
                        ->where('code', 'like', $like)
                        ->orWhere('name', 'like', $like);
                })
                ->orWhereHas('program', function (Builder $programQuery) use ($like) {
                    $programQuery
                        ->where('title', 'like', $like)
                        ->orWhere('name', 'like', $like)
                        ->orWhere('code', 'like', $like);
                })
                ->orWhereHas('batch', function (Builder $batchQuery) use ($like) {
                    $batchQuery
                        ->where('code', 'like', $like)
                        ->orWhere('name', 'like', $like);
                });
        });
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

    private function studentStatusLabel(?string $status): string
    {
        return Admission::STUDENT_STATUS_LABELS[$status] ?? ucfirst(str_replace('_', ' ', (string) $status));
    }

    private function studentStatusClass(?string $status): string
    {
        return Admission::STUDENT_STATUS_BADGE_CLASSES[$status] ?? 'label-default';
    }

    /**
     * @return array{0:string,1:string}
     */
    private function certificateStatusMeta(Admission $admission): array
    {
        $status = (string) ($admission->student_status ?? '');

        if ($status === Admission::CERTIFICATE_REQUESTABLE_STATUS) {
            return ['Pending', 'label-default'];
        }

        if (in_array($status, Admission::CERTIFICATE_WORKFLOW_STATUSES, true)) {
            return [
                $this->studentStatusLabel($status),
                $this->studentStatusClass($status),
            ];
        }

        if ($admission->certificate_delivered_at) {
            return ['Delivered', 'label-default'];
        }

        return ['Pending', 'label-default'];
    }

    private function formatCampusLabel(?Campus $campus): string
    {
        if (! $campus instanceof Campus) {
            return 'N/A';
        }

        $code = trim((string) ($campus->code ?? ''));
        $name = trim((string) ($campus->name ?? ''));

        if ($code !== '' && $name !== '') {
            return $code . ' - ' . $name;
        }

        return $code !== '' ? $code : ($name !== '' ? $name : 'N/A');
    }

    private function formatProgramLabel($program): string
    {
        if (! $program) {
            return 'N/A';
        }

        $title = trim((string) ($program->title ?? ''));
        $name = trim((string) ($program->name ?? ''));
        $code = trim((string) ($program->code ?? ''));
        $label = $title !== '' ? $title : ($name !== '' ? $name : 'N/A');

        if ($code !== '' && $label !== 'N/A') {
            return $label . ' (' . $code . ')';
        }

        return $label;
    }

    private function formatBatchLabel(?Batch $batch): string
    {
        if (! $batch instanceof Batch) {
            return 'N/A';
        }

        $code = trim((string) ($batch->code ?? ''));
        $name = trim((string) ($batch->name ?? ''));

        if ($code !== '' && $name !== '' && strcasecmp($code, $name) !== 0) {
            return $code . ' - ' . $name;
        }

        return $code !== '' ? $code : ($name !== '' ? $name : ('Batch #' . $batch->id));
    }

    private function formatBatchTiming(?Batch $batch): string
    {
        if (! $batch instanceof Batch) {
            return 'Timing not set';
        }

        $slots = collect($batch->relationLoaded('timetables') ? $batch->timetables : [])
            ->filter(function ($slot) {
                $status = strtolower((string) ($slot->status ?? 'active'));

                return $status === '' || $status === 'active';
            })
            ->sortBy(function ($slot) {
                return sprintf(
                    '%02d-%s',
                    $this->daySortOrder((string) ($slot->day_of_week ?? '')),
                    (string) ($slot->start_time ?? '')
                );
            })
            ->values();

        if ($slots->isNotEmpty()) {
            return $slots
                ->map(function ($slot) {
                    $day = $this->formatDayLabel((string) ($slot->day_of_week ?? ''));
                    $start = $slot->start_time ? Carbon::parse($slot->start_time)->format('h:i A') : null;
                    $end = $slot->end_time ? Carbon::parse($slot->end_time)->format('h:i A') : null;

                    if ($start && $end) {
                        return $day . ' ' . $start . ' - ' . $end;
                    }

                    return $day;
                })
                ->implode(', ');
        }

        if ($batch->start_time && $batch->end_time) {
            return Carbon::parse($batch->start_time)->format('h:i A') . ' - ' . Carbon::parse($batch->end_time)->format('h:i A');
        }

        return 'Timing not set';
    }

    private function formatDayLabel(string $day): string
    {
        $normalized = strtolower(trim($day));

        return match ($normalized) {
            'monday' => 'Mon',
            'tuesday' => 'Tue',
            'wednesday' => 'Wed',
            'thursday' => 'Thu',
            'friday' => 'Fri',
            'saturday' => 'Sat',
            'sunday' => 'Sun',
            default => $day !== '' ? ucfirst($day) : 'Day',
        };
    }

    private function daySortOrder(string $day): int
    {
        return match (strtolower(trim($day))) {
            'monday' => 1,
            'tuesday' => 2,
            'wednesday' => 3,
            'thursday' => 4,
            'friday' => 5,
            'saturday' => 6,
            'sunday' => 7,
            default => 99,
        };
    }

    private function appendAdmissionRemark(?string $currentRemarks, string $newRemark): string
    {
        $current = trim((string) $currentRemarks);
        $remark = trim($newRemark);

        if ($current === '') {
            return $remark;
        }

        if ($remark === '') {
            return $current;
        }

        return $current . PHP_EOL . $remark;
    }

    private function buildTransferRemark(
        ?string $userName,
        string $fromCampus,
        string $fromBatch,
        string $toCampus,
        string $toBatch,
        ?string $remarks
    ): string {
        $message = sprintf(
            '[%s] Admission transferred from %s / %s to %s / %s',
            now()->format('d-M-Y h:i A'),
            $fromCampus,
            $fromBatch,
            $toCampus,
            $toBatch
        );

        if ($userName) {
            $message .= ' by ' . $userName;
        }

        $remarks = trim((string) $remarks);

        if ($remarks !== '') {
            $message .= '. Remarks: ' . $remarks;
        } else {
            $message .= '.';
        }

        return $message;
    }

    private function canReviewAdmissions($user): bool
    {
        return $user?->hasAnyPermission(['admission.review']) ?? false;
    }
}
