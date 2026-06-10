<?php

namespace App\Http\Controllers;

use App\Models\Admission;
use App\Models\Batch;
use App\Models\Campus;
use App\Models\FeeCollection;
use App\Models\Lead;
use App\Models\LeadFollowup;
use App\Models\Program;
use App\Models\ProgramCampusDiscount;
use App\Models\Registration;
use App\Support\ResolvesCampusScope;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class AdmissionController extends Controller
{
    use ResolvesCampusScope;

    public function create(Request $request): View
    {
        $lead = null;
        if ($request->filled('lead_id')) {
            $lead = Lead::with(['campus', 'program'])->findOrFail($request->integer('lead_id'));
            $this->ensureCampusAccess((int) ($lead->campus_id ?? 0), $request->user(), 'You are not allowed to use a lead from another campus.');
        }

        $campuses = Campus::query()
            ->orderBy('name')
            ->get();
        $programs = Program::orderBy('title')->get();
        $batches = Batch::query()
            ->orderBy('name')
            ->get();

        $existingRegistration = $lead
            ? Registration::where('lead_id', $lead->id)->latest()->first()
            : null;

        $programMap = $programs->mapWithKeys(fn ($p) => [
            (string) $p->id => [
                'fee' => (float) ($p->fee ?? 0),
                'installments' => max(1, (int) ($p->installments ?? 1)),
            ],
        ])->toArray();

        $discountMap = ProgramCampusDiscount::query()
            ->where('status', 'active')
            ->get(['program_id', 'campus_id', 'discount_percent'])
            ->mapWithKeys(fn ($d) => [
                $d->program_id . ':' . ($d->campus_id ?? 'any') => (float) $d->discount_percent,
            ])
            ->toArray();
        $programMap = (object) $programMap;
        $discountMap = (object) $discountMap;

        $batchList = $batches->map(fn ($b) => [
            'id' => $b->id,
            'campus_id' => $b->campus_id,
            'program_id' => $b->program_id,
            'code' => $b->code,
            'name' => $b->name,
            'start_time' => $b->start_time,
            'end_time' => $b->end_time,
        ])->values()->toArray();

        $previewCampus = $lead?->campus ?? $campuses->first();
        $previewBatch = $previewCampus
            ? $batches->first(fn ($b) => (int) $b->campus_id === (int) $previewCampus->id)
            : null;
        $previewRollNumber = ($previewCampus && $previewBatch)
            ? $this->generateRollNumber($previewCampus->code, $previewBatch->code)
            : '';
        $previewRegistrationNumber = $existingRegistration?->registration_number
            ?? ($previewCampus ? $this->previewNumbers($previewCampus->code)['registration_number'] : '');
        $previewReceiptNumber = $previewCampus
            ? $this->generateAdmissionReceiptNumber($previewCampus->code)
            : '';

        return view('admission.create', compact(
            'campuses',
            'programs',
            'batches',
            'batchList',
            'lead',
            'existingRegistration',
            'programMap',
            'discountMap',
            'previewRollNumber',
            'previewRegistrationNumber',
            'previewReceiptNumber'
        ));
    }

    public function previewNumbersAjax(Request $request): JsonResponse
    {
        $campusId = $request->integer('campus_id');
        $batchId = $request->integer('batch_id');
        $leadId = $request->integer('lead_id');
        $campus = $campusId ? Campus::query()->find($campusId) : null;
        $batch = $batchId ? Batch::query()->find($batchId) : null;

        if ($campus && $batch && (int) $batch->campus_id !== (int) $campus->id) {
            $batch = null;
        }

        $registrationNumber = null;
        if ($leadId) {
            $lead = Lead::query()->findOrFail($leadId);
            $this->ensureCampusAccess((int) ($lead->campus_id ?? 0), $request->user(), 'You are not allowed to use a lead from another campus.');

            $existing = Registration::where('lead_id', $leadId)->latest()->first();
            $registrationNumber = $existing?->registration_number;
        }
        if (!$registrationNumber && $campus) {
            $registrationNumber = $this->previewNumbers($campus->code)['registration_number'];
        }

        $rollNumber = ($campus && $batch)
            ? $this->generateRollNumber($campus->code, $batch->code)
            : null;

        $receiptNumber = $campus
            ? $this->generateAdmissionReceiptNumber($campus->code)
            : null;

        return response()->json([
            'registration_number' => $registrationNumber ?? '',
            'roll_number' => $rollNumber ?? '',
            'receipt_number' => $receiptNumber ?? '',
        ]);
    }

    public function store(Request $request): \Illuminate\Http\Response|RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'lead_id' => ['nullable', 'exists:leads,id'],
            'campus_id' => ['required', 'exists:campuses,id'],
            'program_id' => ['required', 'exists:programs,id'],
            'batch_id' => ['required', 'exists:batches,id'],
            'student_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'regex:/^03\d{9}$/', 'unique:admissions,phone'],
            'guardian_name' => ['required', 'string', 'max:255'],
            'guardian_phone' => ['required', 'string', 'max:50'],
            'cnic' => ['required', 'string', 'max:50', 'unique:admissions,cnic'],
            'passport_number' => ['nullable', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255'],
            'education' => ['required', 'string', 'max:255'],
            'date_of_birth' => ['required', 'date'],
            'gender' => ['required', 'in:male,female,other'],
            'country' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'area' => ['nullable', 'string', 'max:150'],
            'postal_address' => ['required', 'string', 'max:500'],
            'registration_number' => ['nullable', 'string', 'max:100'],
            'roll_number' => ['nullable', 'string', 'max:100'],
            'admission_date' => ['required', 'date'],
            'fee_package' => ['nullable', 'numeric'],
            'discount_amount' => ['nullable', 'numeric'],
            'discount_percent' => ['nullable', 'numeric'],
            'discounted_fee' => ['nullable', 'numeric'],
            'fee_type' => ['required', 'in:full,installments'],
            'remarks' => ['required', 'string', 'max:1000'],
            'receipt_number' => ['nullable', 'string', 'max:100'],
        ]);

        $validated['country'] = $validated['country'] ?? null;
        $validated['city'] = $validated['city'] ?? null;
        $validated['area'] = $validated['area'] ?? null;
        $validated['fee_package'] = $validated['fee_package'] ?? null;
        $validated['discount_percent'] = $validated['discount_percent'] ?? null;
        $validated['discount_amount'] = $validated['discount_amount'] ?? null;
        $validated['discounted_fee'] = $validated['discounted_fee'] ?? null;
        $validated['passport_number'] = $validated['passport_number'] ?? null;
        $validated['registration_number'] = $validated['registration_number'] ?? null;
        $validated['roll_number'] = $validated['roll_number'] ?? null;
        $validated['receipt_number'] = $validated['receipt_number'] ?? null;

        try {
            $campus = Campus::findOrFail($validated['campus_id']);
            $program = Program::findOrFail($validated['program_id']);
            $batch = Batch::query()
                ->whereKey($validated['batch_id'])
                ->where('campus_id', $campus->id)
                ->first();

            if (! $batch) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'batch_id' => ['The selected batch does not belong to the selected campus.'],
                ]);
            }

            $feePackage = $validated['fee_package'];
            $discountPercent = $validated['discount_percent'];
            $discountAmount = $validated['discount_amount'];
            $discountedFee = $validated['discounted_fee'];

            if (!is_null($program->fee)) {
                $feePackage = $program->fee;
                $discountPercent = $this->resolveDiscountPercent($program->id, $campus->id);
                $discountAmount = round($feePackage * ($discountPercent / 100), 2);
                $discountedFee = $feePackage - $discountAmount;
            }
            $feePackage = $feePackage ?? 0;
            $discountPercent = $discountPercent ?? 0;
            $discountAmount = $discountAmount ?? 0;
            $discountedFee = $discountedFee ?? ($feePackage - $discountAmount);

            $lead = null;
            if (!empty($validated['lead_id'])) {
                $lead = Lead::query()->findOrFail($validated['lead_id']);
                $this->ensureCampusAccess((int) ($lead->campus_id ?? 0), $request->user(), 'You are not allowed to use a lead from another campus.');
            }
            if (!$lead) {
                $lead = Lead::query()
                    ->where('phone', $validated['phone'])
                    ->first();
            }

            if (!$lead) {
                $lead = Lead::create([
                    'campus_id' => $validated['campus_id'],
                    'program_id' => $validated['program_id'],
                    'created_by' => $request->user()?->id,
                    'type' => null,
                    'name' => $validated['student_name'],
                    'email' => $validated['email'],
                    'phone' => $validated['phone'],
                    'city' => $validated['city'],
                    'origin' => 'Admission',
                    'marketing_source' => 'Admission',
                    'status' => 'pending',
                    'details' => [
                        'gender' => $validated['gender'],
                        'education' => $validated['education'],
                        'country' => $validated['country'],
                        'area' => $validated['area'],
                        'postal_address' => $validated['postal_address'],
                        'guardian_name' => $validated['guardian_name'],
                        'guardian_phone' => $validated['guardian_phone'],
                        'cnic' => $validated['cnic'],
                        'passport_number' => $validated['passport_number'] ?? null,
                        'date_of_birth' => $validated['date_of_birth'],
                    ],
                ]);

                LeadFollowup::create([
                    'lead_id' => $lead->id,
                    'campus_id' => $lead->campus_id,
                    'user_id' => $request->user()?->id,
                    'note' => 'Initial follow-up created via admission form.',
                    'method' => null,
                    'probability' => null,
                    'next_action_date' => null,
                    'stage' => 'new',
                    'lead_status' => 'pending',
                ]);
            } else {
                $lead->update([
                    'campus_id' => $validated['campus_id'],
                    'program_id' => $validated['program_id'],
                    'name' => $validated['student_name'],
                    'email' => $validated['email'],
                    'phone' => $validated['phone'],
                    'city' => $validated['city'],
                ]);
            }

            $registration = Registration::where('lead_id', $lead->id)->latest()->first();
            if (!$registration) {
                $regNumbers = $this->previewNumbers($campus->code);
                $registration = Registration::create([
                    'lead_id' => $lead->id,
                    'campus_id' => $validated['campus_id'],
                    'program_id' => $validated['program_id'],
                    'registration_number' => $regNumbers['registration_number'],
                    'receipt_number' => $regNumbers['receipt_number'],
                    'student_name' => $validated['student_name'],
                    'phone' => $validated['phone'],
                    'email' => $validated['email'],
                    'fee' => 2000,
                    'discount' => 0,
                    'net_payable' => 2000,
                    'status' => 'registered',
                    'registered_at' => Carbon::now(),
                ]);

                $hasRegistrationFee = FeeCollection::where('registration_id', $registration->id)
                    ->where('fee_type', 'registration')
                    ->exists();
                if (!$hasRegistrationFee) {
                    FeeCollection::create([
                        'lead_id' => $lead->id,
                        'registration_id' => $registration->id,
                        'campus_id' => $validated['campus_id'],
                        'program_id' => $validated['program_id'],
                        'fee_type' => 'registration',
                        'amount' => 2000,
                        'discount_percent' => 0,
                        'discount_amount' => 0,
                        'net_amount' => 2000,
                        'receipt_number' => $registration->receipt_number,
                        'status' => 'paid',
                        'paid_at' => Carbon::now(),
                        'created_by' => $request->user()?->id,
                        'notes' => 'Registration fee auto-collected during admission.',
                    ]);
                }
            } else {
                $registration->update([
                    'campus_id' => $validated['campus_id'],
                    'program_id' => $validated['program_id'],
                    'student_name' => $validated['student_name'],
                    'phone' => $validated['phone'],
                    'email' => $validated['email'],
                ]);
            }

            if ($lead->status !== 'enrolled') {
                $lead->update([
                    'status' => 'registered',
                    'campus_id' => $validated['campus_id'],
                    'program_id' => $validated['program_id'],
                ]);

                LeadFollowup::create([
                    'lead_id' => $lead->id,
                    'campus_id' => $validated['campus_id'],
                    'user_id' => $request->user()?->id,
                    'method' => 'walk-in',
                    'probability' => 100,
                    'note' => 'Lead registered via admission form.',
                    'stage' => 'registered',
                    'lead_status' => 'registered',
                ]);

                $lead->update(['status' => 'enrolled']);

                LeadFollowup::create([
                    'lead_id' => $lead->id,
                    'campus_id' => $validated['campus_id'],
                    'user_id' => $request->user()?->id,
                    'method' => 'walk-in',
                    'probability' => 100,
                    'note' => 'Lead enrolled via admission form.',
                    'stage' => 'enroll',
                    'lead_status' => 'enrolled',
                ]);
            }

            $registrationNumber = $validated['registration_number'] ?? $registration->registration_number ?? null;
            if (!$registrationNumber) {
                $registrationNumber = $this->previewNumbers($campus->code)['registration_number'];
            }

            $batchForNumber = $batch;
            $providedRoll = $validated['roll_number'] ?? null;
            if ($providedRoll) {
                $expectedPrefix = $campus->code . '-' . ($batchForNumber?->code ?? 'NB') . '-';
                if (!str_starts_with($providedRoll, $expectedPrefix)) {
                    $providedRoll = null;
                }
            }

            $admission = $this->createAdmissionAtomically(
                campusCode: $campus->code,
                batchCode: $batchForNumber?->code,
                providedRollNumber: $providedRoll,
                providedReceiptNumber: $validated['receipt_number'] ?? null,
                attributes: [
                    'registration_id' => $registration->id,
                    'campus_id' => $validated['campus_id'],
                    'program_id' => $validated['program_id'],
                    'batch_id' => $validated['batch_id'],
                    'student_name' => $validated['student_name'],
                    'phone' => $validated['phone'],
                    'guardian_name' => $validated['guardian_name'],
                    'guardian_phone' => $validated['guardian_phone'],
                    'cnic' => $validated['cnic'],
                    'passport_number' => $validated['passport_number'] ?? null,
                    'email' => $validated['email'],
                    'education' => $validated['education'],
                    'date_of_birth' => $validated['date_of_birth'],
                    'gender' => $validated['gender'],
                    'country' => $validated['country'],
                    'city' => $validated['city'],
                    'area' => $validated['area'],
                    'postal_address' => $validated['postal_address'],
                    'registration_number' => $registrationNumber,
                    'admission_date' => $validated['admission_date'],
                    'fee_package' => $feePackage,
                    'discount_amount' => $discountAmount,
                    'discount_percent' => $discountPercent,
                    'discounted_fee' => $discountedFee,
                    'fee_type' => $validated['fee_type'],
                    'student_status' => 'enrolled',
                    'status_updated_at' => now(),
                    'remarks' => $validated['remarks'],
                ]
            );
            $receiptNumber = $admission->receipt_number;

            $hasAdmissionFee = FeeCollection::where('admission_id', $admission->id)
                ->where('fee_type', 'admission')
                ->exists();
            if (!$hasAdmissionFee) {
                $programMaxInstallments = max(1, (int) ($program->installments ?? 1));
                $feeType = $validated['fee_type'];
                $base = $discountedFee > 0 ? $discountedFee : $feePackage;
                $amounts = [];

                if ($feeType === 'installments') {
                    $inputAmounts = array_values(array_filter(
                        (array) $request->input('installment_amounts', []),
                        fn ($v) => $v !== null && $v !== ''
                    ));

                    if (!empty($inputAmounts)) {
                        $count = min(count($inputAmounts), $programMaxInstallments);
                        $amounts = array_slice(
                            array_map(fn ($v) => round((float) $v, 2), $inputAmounts),
                            0,
                            $count
                        );
                    } else {
                        $count = $programMaxInstallments;
                        $split = round($base / $count, 2);
                        $amounts = array_fill(0, $count, $split);
                        $diff = round($base - array_sum($amounts), 2);
                        if ($diff !== 0.0) {
                            $amounts[$count - 1] = round($amounts[$count - 1] + $diff, 2);
                        }
                    }

                    $amounts = array_values(array_filter($amounts, fn ($a) => $a > 0));
                    if (empty($amounts)) {
                        $amounts = [$base];
                        $feeType = 'full';
                    }
                } else {
                    $amounts = [$base];
                }

                $installmentsTotal = $feeType === 'installments' ? count($amounts) : null;
                $admissionDate = Carbon::parse($validated['admission_date']);

                foreach ($amounts as $index => $amount) {
                    $isFirstInstallment = $index === 0;
                    $isInstallment = $feeType === 'installments';
                    $isPaid = !$isInstallment || $isFirstInstallment;

                    FeeCollection::create([
                        'lead_id' => $lead?->id,
                        'registration_id' => $registration?->id,
                        'admission_id' => $admission->id,
                        'campus_id' => $validated['campus_id'],
                        'program_id' => $validated['program_id'],
                        'fee_type' => 'admission',
                        'installment_no' => $isInstallment ? $index + 1 : null,
                        'installments_total' => $installmentsTotal,
                        'amount' => $amount,
                        'discount_percent' => $discountPercent,
                        'discount_amount' => $discountAmount,
                        'net_amount' => $amount,
                        'receipt_number' => $receiptNumber,
                        'status' => $isPaid ? 'paid' : 'pending',
                        'paid_at' => $isPaid ? $admissionDate : null,
                        'due_at' => $isInstallment
                            ? $admissionDate->copy()->addMonthsNoOverflow($index)
                            : $admissionDate,
                        'created_by' => $request->user()?->id,
                        'notes' => $isPaid
                            ? ($isInstallment ? 'First installment paid at admission.' : 'Admission fee collected.')
                            : 'Installment ' . ($index + 1) . ' scheduled.',
                    ]);
                }
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'Admission created successfully.',
                    'redirect_url' => route('admission.voucher', $admission),
                ]);
            }

            return response()->view('shared.voucher_redirect', [
                'voucherUrl' => route('admission.voucher', $admission),
                'redirectUrl' => route('admission.status'),
                'heading' => 'Admission Enrolled',
                'message' => 'Admission saved. Opening the fee voucher in a new tab...',
            ]);
        } catch (Throwable $e) {
            report($e);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Unable to save the admission right now. Please try again.',
                ], 500);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Unable to save the admission right now. Please try again.');
        }
    }

    public function status(): View
    {
        $admissions = $this->scopeQueryToUserCampus(Admission::query(), auth()->user())
            ->with(['program', 'campus'])
            ->orderByDesc('admission_date')
            ->orderByDesc('id')
            ->get();

        return view('admission.status', compact('admissions'));
    }

    public function voucher(Admission $admission): View
    {
        $this->ensureCampusAccess((int) ($admission->campus_id ?? 0), auth()->user(), 'You are not allowed to access admissions from another campus.');

        $admission->load(['program', 'campus', 'batch', 'registration.lead']);

        $fees = FeeCollection::query()
            ->where(function ($q) use ($admission) {
                $q->where('admission_id', $admission->id)
                    ->orWhere('registration_id', $admission->registration_id);
            })
            ->where('status', 'paid')
            ->get();

        $registrationFeeTotal = (float) $fees->where('fee_type', 'registration')->sum('net_amount');
        $admissionFeeTotal = (float) $fees->where('fee_type', 'admission')->sum('net_amount');
        $totalPaid = $registrationFeeTotal + $admissionFeeTotal;

        return view('admission.voucher', compact(
            'admission',
            'registrationFeeTotal',
            'admissionFeeTotal',
            'totalPaid'
        ));
    }

    private function previewNumbers(string $campusCode): array
    {
        $now = Carbon::now();
        $monthYear = $now->format('my'); // e.g. 0126

        $countForMonth = Registration::where('registration_number', 'like', $campusCode . '-' . $monthYear . '-%')->count() + 1;
        $countPadded = str_pad((string)$countForMonth, 2, '0', STR_PAD_LEFT);

        $receiptCount = Registration::where('receipt_number', 'like', $campusCode . '-' . $monthYear . '-%')->count() + 1;
        $receiptPadded = str_pad((string)$receiptCount, 6, '0', STR_PAD_LEFT);

        return [
            'registration_number' => $campusCode . '-' . $monthYear . '-' . $countPadded,
            'receipt_number' => $campusCode . '-' . $monthYear . '-' . $receiptPadded,
        ];
    }

    private function resolveDiscountPercent(int $programId, int $campusId): float
    {
        $discount = ProgramCampusDiscount::query()
            ->where('program_id', $programId)
            ->where(function ($query) use ($campusId) {
                $query->whereNull('campus_id')->orWhere('campus_id', $campusId);
            })
            ->where('status', 'active')
            ->orderByRaw('campus_id is null')
            ->first();

        return (float)($discount->discount_percent ?? 0);
    }

    private function generateAdmissionReceiptNumber(string $campusCode): string
    {
        $prefix = $campusCode . '-' . Carbon::now()->format('my') . '-';
        $next = $this->nextSequence(Admission::query(), 'receipt_number', $prefix);
        return $prefix . str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }

    private function generateRollNumber(string $campusCode, ?string $batchCode = null): string
    {
        $batchPart = $batchCode ? trim($batchCode) : 'NB';
        $prefix = $campusCode . '-' . $batchPart . '-';
        $next = $this->nextSequence(Admission::query(), 'roll_number', $prefix);
        return $prefix . str_pad((string) $next, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Highest existing seq for a column with a `{prefix}{seq}` pattern, plus 1.
     */
    private function nextSequence($baseQuery, string $column, string $prefix): int
    {
        $max = (clone $baseQuery)
            ->where($column, 'like', $prefix . '%')
            ->get([$column])
            ->map(function ($row) use ($prefix, $column) {
                $tail = substr($row->{$column}, strlen($prefix));
                return ctype_digit($tail) ? (int) $tail : 0;
            })
            ->max();

        return ((int) $max) + 1;
    }

    /**
     * Generate unique roll/receipt numbers and INSERT the Admission atomically.
     * Retries on UNIQUE-constraint violations.
     */
    private function createAdmissionAtomically(
        string $campusCode,
        ?string $batchCode,
        ?string $providedRollNumber,
        ?string $providedReceiptNumber,
        array $attributes,
    ): Admission {
        $maxAttempts = 10;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            $rollNumber = $providedRollNumber ?: $this->generateRollNumber($campusCode, $batchCode);
            $receiptNumber = $providedReceiptNumber ?: $this->generateAdmissionReceiptNumber($campusCode);

            try {
                return DB::transaction(function () use ($rollNumber, $receiptNumber, $attributes) {
                    return Admission::create(array_merge($attributes, [
                        'roll_number' => $rollNumber,
                        'receipt_number' => $receiptNumber,
                    ]));
                });
            } catch (QueryException $e) {
                $msg = $e->getMessage();
                if (str_contains($msg, 'UNIQUE') || str_contains($msg, 'Duplicate entry') || $e->getCode() === '23000') {
                    // If the user provided a number that conflicts, fail loudly
                    if ($providedRollNumber || $providedReceiptNumber) {
                        throw $e;
                    }
                    // Otherwise regenerate and retry
                    continue;
                }
                throw $e;
            }
        }

        throw new RuntimeException('Unable to generate unique admission numbers after ' . $maxAttempts . ' attempts.');
    }
}
