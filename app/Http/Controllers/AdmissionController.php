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
use App\Models\User;
use App\Services\FinanceAccountingService;
use App\Support\ResolvesCampusScope;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class AdmissionController extends Controller
{
    use ResolvesCampusScope;

    public function create(Request $request): View
    {
        $lead = null;
        $sourceRegistration = null;
        $sourceAdmission = null;

        if ($request->filled('source_admission_id')) {
            $sourceAdmission = Admission::query()
                ->with(['registration.lead', 'campus', 'program', 'batch'])
                ->findOrFail($request->integer('source_admission_id'));

            $this->ensureCampusAccess((int) ($sourceAdmission->campus_id ?? 0), $request->user(), 'You are not allowed to use a student admission from another campus.');
            $sourceRegistration = $sourceAdmission->registration;
        }

        if ($request->filled('source_registration_id')) {
            $sourceRegistration = Registration::query()
                ->with(['lead', 'campus', 'program', 'admission'])
                ->findOrFail($request->integer('source_registration_id'));

            $this->ensureCampusAccess((int) ($sourceRegistration->campus_id ?? 0), $request->user(), 'You are not allowed to use a student registration from another campus.');
        }

        if ($request->filled('lead_id')) {
            $lead = Lead::with(['campus', 'program'])->findOrFail($request->integer('lead_id'));

            // Lead campus can legitimately differ from the source registration/admission's
            // campus (e.g. a lead transferred campuses before registering). Access to those
            // records is already validated above, so only gate directly on the lead's own
            // campus when there is no already-authorized source registration/admission.
            if (! $sourceRegistration && ! $sourceAdmission) {
                $this->ensureCampusAccess((int) ($lead->campus_id ?? 0), $request->user(), 'You are not allowed to use a lead from another campus.');
            }
        }

        if (! $lead) {
            $lead = $sourceRegistration?->lead ?? $sourceAdmission?->registration?->lead;
        }

        $campuses = Campus::query()
            ->orderBy('name')
            ->get();
        $programs = Program::query()
            ->where('status', 'active')
            ->orderByRaw('COALESCE(title, name)')
            ->get();
        $batches = $this->admissionBatches();

        $isAnotherCourseEnrollment = $this->isAnotherCourseEnrollment($sourceRegistration, $sourceAdmission);
        $formDefaults = $this->buildAdmissionFormDefaults($lead, $sourceRegistration, $sourceAdmission, $isAnotherCourseEnrollment);
        $selectedProgramId = (int) ($request->old('program_id', $formDefaults['program_id'] ?? 0) ?? 0);
        $existingRegistration = null;

        if (! $isAnotherCourseEnrollment) {
            $existingRegistration = $sourceRegistration;

            if (! $existingRegistration && $lead) {
                $existingRegistration = Registration::query()
                    ->where('lead_id', $lead->id)
                    ->when($selectedProgramId > 0, fn ($query) => $query->where('program_id', $selectedProgramId))
                    ->latest()
                    ->first();
            }
        }

        $disallowedProgramIds = $isAnotherCourseEnrollment
            ? $this->resolveStudentProgramIds($lead, $sourceRegistration, $sourceAdmission)
            : [];

        if ($disallowedProgramIds !== []) {
            $programs = $programs
                ->reject(fn (Program $program) => in_array((int) $program->id, $disallowedProgramIds, true))
                ->values();
        }

        $availableProgramCount = $programs->count();
        $hasAlternativePrograms = $availableProgramCount > 0;

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

        $selectedCampusId = (int) ($request->old('campus_id', $formDefaults['campus_id'] ?? ($lead?->campus_id ?? 0)) ?? 0);
        $previewCampus = $selectedCampusId > 0
            ? $campuses->firstWhere('id', $selectedCampusId)
            : ($sourceAdmission?->campus ?? $sourceRegistration?->campus ?? $lead?->campus ?? $campuses->first());
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
            'sourceRegistration',
            'sourceAdmission',
            'formDefaults',
            'isAnotherCourseEnrollment',
            'disallowedProgramIds',
            'availableProgramCount',
            'hasAlternativePrograms',
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
        $programId = $request->integer('program_id');
        $campus = $campusId ? Campus::query()->find($campusId) : null;
        $batch = $batchId ? Batch::query()->find($batchId) : null;

        if ($campus && $batch && (int) $batch->campus_id !== (int) $campus->id) {
            $batch = null;
        }

        $registrationNumber = null;
        if ($leadId) {
            $lead = Lead::query()->findOrFail($leadId);
            $this->ensureCampusAccess((int) ($lead->campus_id ?? 0), $request->user(), 'You are not allowed to use a lead from another campus.');

            $existing = Registration::query()
                ->where('lead_id', $leadId)
                ->when($programId, fn ($query, int $selectedProgramId) => $query->where('program_id', $selectedProgramId))
                ->latest()
                ->first();
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
        $request->merge([
            'cnic' => preg_replace('/\D+/', '', (string) $request->input('cnic')),
        ]);

        $validated = $request->validate([
            'lead_id' => ['nullable', 'exists:leads,id'],
            'source_registration_id' => ['nullable', 'exists:registrations,id'],
            'source_admission_id' => ['nullable', 'exists:admissions,id'],
            'campus_id' => ['required', 'exists:campuses,id'],
            'program_id' => [
                'required',
                Rule::exists('programs', 'id')->where(fn ($query) => $query->where('status', 'active')),
            ],
            'batch_id' => ['required', 'exists:batches,id'],
            'student_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'regex:/^03\d{9}$/'],
            'guardian_name' => ['required', 'string', 'max:255'],
            'guardian_phone' => ['required', 'string', 'max:50'],
            'cnic' => ['required', 'regex:/^\d{13}$/'],
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
        ], [
            'cnic.regex' => 'The CNIC must be exactly 13 digits.',
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
                ->where('program_id', $program->id)
                ->first();

            if (! $batch) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'batch_id' => ['The selected batch does not belong to the selected campus and course.'],
                ]);
            }

            [
                'feePackage' => $feePackage,
                'discountPercent' => $discountPercent,
                'discountAmount' => $discountAmount,
                'discountedFee' => $discountedFee,
            ] = $this->resolveAdmissionPricing($program, $campus, $validated);

            $sourceAdmission = !empty($validated['source_admission_id'])
                ? Admission::query()->with(['registration.lead'])->findOrFail($validated['source_admission_id'])
                : null;
            $sourceRegistration = !empty($validated['source_registration_id'])
                ? Registration::query()->with(['lead', 'admission'])->findOrFail($validated['source_registration_id'])
                : ($sourceAdmission?->registration);

            if ($sourceAdmission) {
                $this->ensureCampusAccess((int) ($sourceAdmission->campus_id ?? 0), $request->user(), 'You are not allowed to use a student admission from another campus.');
            }

            if ($sourceRegistration) {
                $this->ensureCampusAccess((int) ($sourceRegistration->campus_id ?? 0), $request->user(), 'You are not allowed to use a student registration from another campus.');
            }

            $isAnotherCourseEnrollment = $this->isAnotherCourseEnrollment(
                $sourceRegistration,
                $sourceAdmission,
                (int) $validated['program_id']
            );
            $lead = null;
            if (!empty($validated['lead_id'])) {
                $lead = Lead::query()->findOrFail($validated['lead_id']);

                // Lead campus can legitimately differ from the source registration/admission's
                // campus (e.g. a lead transferred campuses before registering). Access to those
                // records is already validated above, so only gate directly on the lead's own
                // campus when there is no already-authorized source registration/admission.
                if (! $sourceRegistration && ! $sourceAdmission) {
                    $this->ensureCampusAccess((int) ($lead->campus_id ?? 0), $request->user(), 'You are not allowed to use a lead from another campus.');
                }
            }
            if (! $lead) {
                $lead = $sourceRegistration?->lead ?? $sourceAdmission?->registration?->lead;
            }
            if (!$lead) {
                $lead = Lead::query()
                    ->where('phone', $validated['phone'])
                    ->where(function ($query) {
                        $query->where('type', 'training')
                            ->orWhereNull('type');
                    })
                    ->latest('id')
                    ->first();
            }

            if ($this->studentAlreadyHasProgramAdmission($validated['program_id'], $lead, $validated['phone'], $validated['cnic'])) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'program_id' => ['This student is already enrolled in the selected course. Please choose a different course.'],
                ]);
            }

            if (!$lead) {
                $lead = Lead::create([
                    'campus_id' => $validated['campus_id'],
                    'program_id' => $validated['program_id'],
                    'created_by' => $request->user()?->id,
                    'type' => 'training',
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
                $leadUpdates = [
                    'name' => $validated['student_name'],
                    'email' => $validated['email'],
                    'phone' => $validated['phone'],
                    'city' => $validated['city'],
                ];

                if (blank($lead->type)) {
                    $leadUpdates['type'] = 'training';
                }

                if (! $isAnotherCourseEnrollment) {
                    $leadUpdates['campus_id'] = $validated['campus_id'];
                    $leadUpdates['program_id'] = $validated['program_id'];
                }

                $lead->update($leadUpdates);
            }

            $registration = null;

            if (! $isAnotherCourseEnrollment) {
                $registration = $sourceRegistration;

                if (! $registration && $lead) {
                    $registration = Registration::query()
                        ->where('lead_id', $lead->id)
                        ->where('program_id', $validated['program_id'])
                        ->latest()
                        ->first();
                }
            }
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
                    'guardian_name' => $validated['guardian_name'],
                    'guardian_phone' => $validated['guardian_phone'],
                    'cnic' => $validated['cnic'],
                    'passport_number' => $validated['passport_number'] ?? null,
                    'email' => $validated['email'],
                    'education' => $validated['education'],
                    'date_of_birth' => $validated['date_of_birth'],
                    'gender' => $validated['gender'],
                    'address' => $validated['postal_address'],
                    'remarks' => $validated['remarks'],
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
                    $registrationFee = FeeCollection::create([
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

                    $this->syncFeeCollectionSafely($registrationFee);
                }
            } else {
                $registration->update([
                    'campus_id' => $validated['campus_id'],
                    'program_id' => $validated['program_id'],
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
                    'address' => $validated['postal_address'],
                    'remarks' => $validated['remarks'],
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
                    'approval_status' => Admission::APPROVAL_STATUS_PENDING,
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
                    if ($programMaxInstallments <= 1) {
                        throw ValidationException::withMessages([
                            'fee_type' => ['The selected program does not allow installment admissions.'],
                        ]);
                    }

                    $rawInstallmentAmounts = array_map(
                        static fn ($value) => is_string($value) ? trim($value) : $value,
                        array_values((array) $request->input('installment_amounts', []))
                    );
                    $filledInstallmentAmounts = array_values(array_filter(
                        $rawInstallmentAmounts,
                        static fn ($value) => $value !== null && $value !== ''
                    ));

                    if (!empty($filledInstallmentAmounts)) {
                        $normalizedAmounts = array_map(
                            static fn ($value) => round((float) $value, 2),
                            $filledInstallmentAmounts
                        );

                        if (collect($normalizedAmounts)->contains(fn (float $amount) => $amount < 0)) {
                            throw ValidationException::withMessages([
                                'installment_amounts' => ['Installment amounts cannot be negative.'],
                            ]);
                        }

                        $amounts = array_values(array_filter(
                            $normalizedAmounts,
                            static fn (float $amount): bool => $amount > 0
                        ));

                        if (empty($amounts)) {
                            throw ValidationException::withMessages([
                                'installment_amounts' => ['Each installment amount must be greater than zero.'],
                            ]);
                        }

                        if (count($amounts) < 2) {
                            throw ValidationException::withMessages([
                                'installment_amounts' => ['Please enter at least two installment amounts or choose full fee.'],
                            ]);
                        }

                        if (count($amounts) > $programMaxInstallments) {
                            throw ValidationException::withMessages([
                                'installment_amounts' => ['This program allows up to ' . $programMaxInstallments . ' installments.'],
                            ]);
                        }

                        if (abs(round(array_sum($amounts), 2) - round($base, 2)) > 0.01) {
                            throw ValidationException::withMessages([
                                'installment_amounts' => ['Installment amounts must add up to the discounted fee.'],
                            ]);
                        }
                    } else {
                        $count = $programMaxInstallments;
                        $split = round($base / $count, 2);
                        $amounts = array_fill(0, $count, $split);
                        $diff = round($base - array_sum($amounts), 2);
                        if ($diff !== 0.0) {
                            $amounts[$count - 1] = round($amounts[$count - 1] + $diff, 2);
                        }
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

                    $feeCollection = FeeCollection::create($this->filterToExistingTableColumns('fee_collections', [
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
                    ]));

                    if ($isPaid) {
                        $this->syncFeeCollectionSafely($feeCollection);
                    }
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
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            report($e);
            $errorMessage = $this->admissionSaveErrorMessage($e);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $errorMessage,
                ], 500);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', $errorMessage);
        }
    }

    public function status(Request $request): View
    {
        $user = $request->user();
        $canAdmissionView = $user?->hasAnyPermission(['admission.view']) ?? false;
        $canReviewApproval = $this->canReviewAdmissions($user);
        $reviewerOnly = $canReviewApproval && ! $canAdmissionView;

        $activeScope = (string) $request->query('scope', 'pending');
        if (!in_array($activeScope, ['all', 'pending', 'requested', 'approved'], true)) {
            $activeScope = $reviewerOnly ? 'requested' : 'pending';
        }

        if ($reviewerOnly && $activeScope !== 'requested') {
            $activeScope = 'requested';
        }

        $activePeriod = $activeScope === 'all'
            ? (string) $request->query('period', 'all')
            : 'all';
        if (!in_array($activePeriod, ['all', 'today', 'month', 'year'], true)) {
            $activePeriod = 'all';
        }

        $search = trim((string) $request->query('search', ''));
        $perPage = (int) $request->integer('per_page', 25);
        if (!in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 25;
        }

        $campuses = $this->campusOptionsForUser($user, ['id', 'code', 'name']);
        $programs = Program::query()
            ->where('status', 'active')
            ->orderByRaw('COALESCE(title, name)')
            ->get(['id', 'code', 'title', 'name']);

        $campusId = (int) $request->query('campus_id', 0);
        if ($campusId > 0 && !$campuses->contains('id', $campusId)) {
            $campusId = 0;
        }

        $programId = (int) $request->query('program_id', 0);
        if ($programId > 0 && !$programs->contains('id', $programId)) {
            $programId = 0;
        }

        $fromDate = trim((string) $request->query('from', ''));
        $toDate = trim((string) $request->query('to', ''));
        $dateFrom = $this->parseAdmissionFilterDate($fromDate);
        $dateTo = $this->parseAdmissionFilterDate($toDate);
        if ($dateFrom && $dateTo && $dateFrom->gt($dateTo)) {
            [$dateFrom, $dateTo] = [$dateTo, $dateFrom];
        }

        $applyAdmissionFilters = function ($query) use ($campusId, $programId, $dateFrom, $dateTo) {
            return $query
                ->when($campusId > 0, fn ($q) => $q->where('campus_id', $campusId))
                ->when($programId > 0, fn ($q) => $q->where('program_id', $programId))
                ->when($dateFrom, function ($q) use ($dateFrom) {
                    $q->where(function ($builder) use ($dateFrom) {
                        $builder
                            ->whereDate('admission_date', '>=', $dateFrom->toDateString())
                            ->orWhere(function ($nested) use ($dateFrom) {
                                $nested
                                    ->whereNull('admission_date')
                                    ->whereDate('created_at', '>=', $dateFrom->toDateString());
                            });
                    });
                })
                ->when($dateTo, function ($q) use ($dateTo) {
                    $q->where(function ($builder) use ($dateTo) {
                        $builder
                            ->whereDate('admission_date', '<=', $dateTo->toDateString())
                            ->orWhere(function ($nested) use ($dateTo) {
                                $nested
                                    ->whereNull('admission_date')
                                    ->whereDate('created_at', '<=', $dateTo->toDateString());
                            });
                    });
                });
        };

        $countBaseQuery = $applyAdmissionFilters($this->scopeQueryToUserCampus(Admission::query(), $user));
        $requestedCountBaseQuery = $applyAdmissionFilters($this->scopeAdmissionStatusQuery(Admission::query(), $user, 'requested'));

        $baseQuery = $applyAdmissionFilters($this->scopeAdmissionStatusQuery(Admission::query(), $user, $activeScope))
            ->with([
                'program:id,code,title,name',
                'campus:id,code,name',
            ])
            ->withCount([
                'feeCollections as pending_admission_fee_count' => fn ($query) => $query
                    ->where('fee_type', 'admission')
                    ->where('status', 'pending'),
            ]);

        $scopeCounts = [
            'all' => $canAdmissionView ? (clone $countBaseQuery)->count() : 0,
            'pending' => $canAdmissionView ? (clone $countBaseQuery)->where('approval_status', Admission::APPROVAL_STATUS_PENDING)->count() : 0,
            'requested' => ($canAdmissionView || $canReviewApproval)
                ? (clone $requestedCountBaseQuery)->where('approval_status', Admission::APPROVAL_STATUS_REQUESTED)->count()
                : 0,
            'approved' => $canAdmissionView ? (clone $countBaseQuery)->where('approval_status', Admission::APPROVAL_STATUS_APPROVED)->count() : 0,
        ];

        $periodCounts = [
            'all' => $scopeCounts['all'],
            'today' => $canAdmissionView ? $this->applyAdmissionPeriodFilter(clone $countBaseQuery, 'today')->count() : 0,
            'month' => $canAdmissionView ? $this->applyAdmissionPeriodFilter(clone $countBaseQuery, 'month')->count() : 0,
            'year' => $canAdmissionView ? $this->applyAdmissionPeriodFilter(clone $countBaseQuery, 'year')->count() : 0,
        ];

        $admissions = (clone $baseQuery)
            ->when($activeScope === 'pending', fn ($query) => $query->where('approval_status', Admission::APPROVAL_STATUS_PENDING))
            ->when($activeScope === 'requested', fn ($query) => $query->where('approval_status', Admission::APPROVAL_STATUS_REQUESTED))
            ->when($activeScope === 'approved', fn ($query) => $query->where('approval_status', Admission::APPROVAL_STATUS_APPROVED))
            ->when($activeScope === 'all', fn ($query) => $this->applyAdmissionPeriodFilter($query, $activePeriod))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($builder) use ($search) {
                    $builder
                        ->where('student_name', 'like', '%' . $search . '%')
                        ->orWhere('guardian_name', 'like', '%' . $search . '%')
                        ->orWhere('phone', 'like', '%' . $search . '%')
                        ->orWhere('roll_number', 'like', '%' . $search . '%')
                        ->orWhere('registration_number', 'like', '%' . $search . '%')
                        ->orWhereHas('program', function ($programQuery) use ($search) {
                            $programQuery
                                ->where('title', 'like', '%' . $search . '%')
                                ->orWhere('name', 'like', '%' . $search . '%')
                                ->orWhere('code', 'like', '%' . $search . '%');
                        })
                        ->orWhereHas('campus', function ($campusQuery) use ($search) {
                            $campusQuery
                                ->where('code', 'like', '%' . $search . '%')
                                ->orWhere('name', 'like', '%' . $search . '%');
                        });
                });
            })
            ->orderByDesc('admission_date')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        return view('admission.status', compact(
            'admissions',
            'activeScope',
            'scopeCounts',
            'activePeriod',
            'periodCounts',
            'search',
            'perPage',
            'campuses',
            'programs',
            'campusId',
            'programId',
            'fromDate',
            'toDate'
        ));
    }

    public function uploadDocuments(Request $request, Admission $admission): RedirectResponse
    {
        $this->ensureCampusAccess((int) ($admission->campus_id ?? 0), $request->user(), 'You are not allowed to update admissions from another campus.');
        abort_unless(
            $request->user()?->hasAnyPermission(['admission.create', 'admission.update']) ?? false,
            403
        );

        $currentStatus = $admission->approval_status ?? Admission::APPROVAL_STATUS_APPROVED;

        if ($currentStatus === Admission::APPROVAL_STATUS_APPROVED) {
            return back()->with('error', 'Approved admissions cannot be sent for approval again.');
        }

        if ($currentStatus !== Admission::APPROVAL_STATUS_PENDING) {
            return back()->with('error', 'Only pending admissions can upload documents for approval.');
        }

        $identityDocumentType = Admission::normalizeIdentityDocumentType(
            (string) $request->input('identity_document_type')
        );

        $validated = $request->validate([
            'identity_document_type' => [
                'required',
                Rule::in([
                    Admission::IDENTITY_DOCUMENT_TYPE_CNIC,
                    Admission::IDENTITY_DOCUMENT_TYPE_B_FORM,
                ]),
            ],
            'document_cnic_front' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'document_cnic_back' => Admission::requiresIdentityDocumentBack($identityDocumentType)
                ? ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120']
                : ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'document_admission_form' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'document_paid_slip' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ], [], [
            'identity_document_type' => 'identity document type',
            'document_cnic_front' => Admission::identityDocumentPrimaryLabelFor($identityDocumentType),
            'document_cnic_back' => 'CNIC back side',
            'document_admission_form' => 'admission form',
            'document_paid_slip' => 'paid slip with authorized stamp',
        ]);

        $directory = 'admissions/' . $admission->id;
        $oldPaths = array_filter([
            $admission->document_cnic_front_path,
            $admission->document_cnic_back_path,
            $admission->document_admission_form_path,
            $admission->document_paid_slip_path,
        ]);

        $newPaths = [
            'identity_document_type' => $validated['identity_document_type'],
            'document_cnic_front_path' => $validated['document_cnic_front']->store($directory, 'public'),
            'document_cnic_back_path' => Admission::requiresIdentityDocumentBack($validated['identity_document_type'])
                ? $validated['document_cnic_back']->store($directory, 'public')
                : null,
            'document_admission_form_path' => $validated['document_admission_form']->store($directory, 'public'),
            'document_paid_slip_path' => $validated['document_paid_slip']->store($directory, 'public'),
        ];

        foreach ($oldPaths as $oldPath) {
            Storage::disk('public')->delete($oldPath);
        }

        $admission->update(array_merge($newPaths, [
            'approval_status' => Admission::APPROVAL_STATUS_REQUESTED,
            'documents_uploaded_at' => now(),
            'documents_uploaded_by' => $request->user()?->id,
            'approval_reviewed_at' => null,
            'approval_reviewed_by' => null,
        ]));

        return back()->with('status', 'Admission documents uploaded and sent for approval.');
    }

    public function reviewApproval(Request $request, Admission $admission): RedirectResponse
    {
        abort_unless($this->canReviewAdmissions($request->user()), 403);

        if (($admission->approval_status ?? Admission::APPROVAL_STATUS_APPROVED) !== Admission::APPROVAL_STATUS_REQUESTED) {
            return back()->with('error', 'Only admissions waiting for approval can be reviewed.');
        }

        $validated = $request->validate([
            'review_action' => ['required', 'in:approve,revert'],
            'approval_remarks' => ['required', 'string', 'max:2000'],
        ]);

        $nextStatus = $validated['review_action'] === 'approve'
            ? Admission::APPROVAL_STATUS_APPROVED
            : Admission::APPROVAL_STATUS_PENDING;

        $admission->update([
            'approval_status' => $nextStatus,
            'approval_reviewed_at' => now(),
            'approval_reviewed_by' => $request->user()?->id,
            'approval_remarks' => $validated['approval_remarks'],
        ]);

        return back()->with(
            'status',
            $nextStatus === Admission::APPROVAL_STATUS_APPROVED
                ? 'Admission approved successfully.'
                : 'Admission reverted to pending successfully.'
        );
    }

    public function viewDocument(Request $request, Admission $admission, string $document): BinaryFileResponse
    {
        if (! $this->canReviewAdmissions($request->user())) {
            $this->ensureCampusAccess((int) ($admission->campus_id ?? 0), $request->user(), 'You are not allowed to access admissions from another campus.');
        }

        $relativePath = match ($document) {
            'cnic-front' => $admission->document_cnic_front_path,
            'cnic-back' => $admission->document_cnic_back_path,
            'admission-form' => $admission->document_admission_form_path,
            'paid-slip' => $admission->document_paid_slip_path,
            default => abort(404),
        };

        abort_unless(filled($relativePath), 404);

        $absolutePath = $this->resolveAdmissionDocumentAbsolutePath((string) $relativePath);
        abort_unless($absolutePath, 404);

        return response()->file($absolutePath, [
            'Content-Disposition' => 'inline; filename="' . basename($absolutePath) . '"',
        ]);
    }

    public function voucher(Request $request, Admission $admission): View
    {
        $this->ensureCampusAccess((int) ($admission->campus_id ?? 0), auth()->user(), 'You are not allowed to access admissions from another campus.');

        $admission->load(['program', 'campus', 'batch', 'registration.lead']);

        $admissionFeeRows = FeeCollection::query()
            ->with(['campus', 'creator'])
            ->where('admission_id', $admission->id)
            ->where('fee_type', 'admission')
            ->orderBy('installment_no')
            ->orderBy('due_at')
            ->orderBy('paid_at')
            ->orderBy('id')
            ->get();
        $paidFees = $admissionFeeRows
            ->where('status', 'paid')
            ->values();

        $selectedFee = null;

        if ($request->filled('fee_collection')) {
            $selectedFee = $paidFees->firstWhere('id', $request->integer('fee_collection'));
            abort_unless($selectedFee, 404);
        }

        if (! $selectedFee) {
            $selectedFee = $paidFees
                ->where('receipt_number', $admission->receipt_number)
                ->sortBy(fn (FeeCollection $fee) => [
                    optional($fee->paid_at)->timestamp ?? 0,
                    (int) ($fee->installment_no ?? 0),
                    $fee->id,
                ])
                ->first() ?? $paidFees->first();
        }

        $registrationFeeTotal = 0.0;
        $admissionFeeTotal = (float) ($selectedFee?->net_amount ?? 0);
        $totalPaid = $admissionFeeTotal;

        return view('admission.voucher', compact(
            'admission',
            'admissionFeeRows',
            'selectedFee',
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

    private function admissionBatches()
    {
        $today = now()->startOfDay();
        $recentWindow = $today->copy()->subDays(30);

        return Batch::query()
            ->whereHas('program', fn ($query) => $query->where('status', 'active'))
            ->where(function ($query) use ($today, $recentWindow) {
                $query
                    ->where(function ($builder) use ($today, $recentWindow) {
                        $builder
                            ->whereDate('start_date', '>=', $recentWindow)
                            ->whereDate('start_date', '<=', $today);
                    })
                    ->orWhere(function ($builder) use ($today) {
                        $builder
                            ->whereDate('start_date', '<=', $today)
                            ->where(function ($nested) use ($today) {
                                $nested
                                    ->whereNull('end_date')
                                    ->orWhereDate('end_date', '>=', $today);
                            });
                    });
            })
            ->orderBy('name')
            ->get();
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

    /**
     * @param  array<string, mixed>  $validated
     * @return array{feePackage: float, discountPercent: float, discountAmount: float, discountedFee: float}
     */
    private function resolveAdmissionPricing(Program $program, Campus $campus, array $validated): array
    {
        $feePackage = round((float) (!is_null($program->fee) ? $program->fee : ($validated['fee_package'] ?? 0)), 2);
        $maxDiscountPercent = round($this->resolveDiscountPercent($program->id, $campus->id), 2);
        $maxDiscountAmount = round($feePackage * ($maxDiscountPercent / 100), 2);
        $submittedDiscountAmount = array_key_exists('discount_amount', $validated) && $validated['discount_amount'] !== null
            ? round((float) $validated['discount_amount'], 2)
            : $maxDiscountAmount;

        if ($submittedDiscountAmount < 0) {
            throw ValidationException::withMessages([
                'discount_amount' => ['Discount cannot be negative.'],
            ]);
        }

        if ($submittedDiscountAmount > $maxDiscountAmount + 0.009) {
            throw ValidationException::withMessages([
                'discount_amount' => ['Discount cannot exceed the allowed limit of ' . $maxDiscountAmount . ' (' . $maxDiscountPercent . '%).'],
            ]);
        }

        $discountAmount = $submittedDiscountAmount;
        $discountPercent = $feePackage > 0
            ? round(($discountAmount / $feePackage) * 100, 2)
            : 0.0;
        $discountedFee = round(max(0, $feePackage - $discountAmount), 2);

        return [
            'feePackage' => $feePackage,
            'discountPercent' => $discountPercent,
            'discountAmount' => $discountAmount,
            'discountedFee' => $discountedFee,
        ];
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
                    return Admission::create($this->filterToExistingTableColumns('admissions', array_merge($attributes, [
                        'roll_number' => $rollNumber,
                        'receipt_number' => $receiptNumber,
                    ])));
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

    private function buildAdmissionFormDefaults(
        ?Lead $lead,
        ?Registration $sourceRegistration,
        ?Admission $sourceAdmission,
        bool $isAnotherCourseEnrollment
    ): array {
        $leadDetails = $lead?->details ?? [];

        $defaults = [
            'campus_id' => $lead?->campus_id,
            'program_id' => $isAnotherCourseEnrollment ? null : $lead?->program_id,
            'student_name' => $lead?->name,
            'phone' => $lead?->phone,
            'guardian_name' => data_get($leadDetails, 'guardian_name'),
            'guardian_phone' => data_get($leadDetails, 'guardian_phone'),
            'cnic' => data_get($leadDetails, 'cnic'),
            'passport_number' => data_get($leadDetails, 'passport_number'),
            'email' => $lead?->email,
            'education' => data_get($leadDetails, 'education'),
            'date_of_birth' => data_get($leadDetails, 'date_of_birth'),
            'gender' => data_get($leadDetails, 'gender', 'male'),
            'country' => data_get($leadDetails, 'country'),
            'city' => $lead?->city,
            'area' => data_get($leadDetails, 'area'),
            'postal_address' => data_get($leadDetails, 'postal_address', data_get($leadDetails, 'address')),
            'remarks' => data_get($leadDetails, 'remarks'),
        ];

        if ($sourceRegistration) {
            $defaults = array_merge($defaults, [
                'campus_id' => $sourceRegistration->campus_id,
                'student_name' => $sourceRegistration->student_name,
                'phone' => $sourceRegistration->phone,
                'guardian_name' => $sourceRegistration->guardian_name,
                'guardian_phone' => $sourceRegistration->guardian_phone,
                'cnic' => $sourceRegistration->cnic,
                'passport_number' => $sourceRegistration->passport_number,
                'email' => $sourceRegistration->email,
                'education' => $sourceRegistration->education,
                'date_of_birth' => optional($sourceRegistration->date_of_birth)->format('Y-m-d'),
                'gender' => $sourceRegistration->gender,
                'postal_address' => $sourceRegistration->address,
                'remarks' => $sourceRegistration->remarks,
            ]);
        }

        if ($sourceAdmission) {
            $defaults = array_merge($defaults, [
                'campus_id' => $sourceAdmission->campus_id,
                'student_name' => $sourceAdmission->student_name,
                'phone' => $sourceAdmission->phone,
                'guardian_name' => $sourceAdmission->guardian_name,
                'guardian_phone' => $sourceAdmission->guardian_phone,
                'cnic' => $sourceAdmission->cnic,
                'passport_number' => $sourceAdmission->passport_number,
                'email' => $sourceAdmission->email,
                'education' => $sourceAdmission->education,
                'date_of_birth' => optional($sourceAdmission->date_of_birth)->format('Y-m-d'),
                'gender' => $sourceAdmission->gender,
                'country' => $sourceAdmission->country,
                'city' => $sourceAdmission->city,
                'area' => $sourceAdmission->area,
                'postal_address' => $sourceAdmission->postal_address,
                'remarks' => $sourceAdmission->remarks,
            ]);
        }

        if ($isAnotherCourseEnrollment) {
            $defaults['program_id'] = null;
        }

        return $defaults;
    }

    private function isAnotherCourseEnrollment(
        ?Registration $sourceRegistration,
        ?Admission $sourceAdmission,
        ?int $selectedProgramId = null
    ): bool {
        if ($sourceAdmission) {
            return true;
        }

        if (! $sourceRegistration) {
            return false;
        }

        $hasExistingAdmission = $sourceRegistration->relationLoaded('admission')
            ? $sourceRegistration->admission !== null
            : $sourceRegistration->admission()->exists();

        if ($hasExistingAdmission) {
            return true;
        }

        if ($selectedProgramId !== null && $selectedProgramId > 0) {
            $sourceProgramId = (int) ($sourceRegistration->program_id ?? 0);

            if ($sourceProgramId > 0 && $sourceProgramId !== $selectedProgramId) {
                return true;
            }
        }

        return false;
    }

    private function syncFeeCollectionSafely(FeeCollection $feeCollection): void
    {
        try {
            app(FinanceAccountingService::class)->syncFeeCollection($feeCollection);
        } catch (Throwable $e) {
            report($e);
        }
    }

    private function admissionSaveErrorMessage(Throwable $e): string
    {
        $message = $e instanceof QueryException
            ? ($e->getPrevious()?->getMessage() ?: $e->getMessage())
            : $e->getMessage();

        $message = trim((string) preg_replace('/\s+/', ' ', (string) $message));
        $message = preg_replace('/ \(Connection:.*$/', '', $message) ?: $message;

        if ($message === '') {
            return 'Unable to save the admission right now. Please try again.';
        }

        return Str::limit($message, 500);
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function filterToExistingTableColumns(string $table, array $attributes): array
    {
        static $columnCache = [];

        if (!array_key_exists($table, $columnCache)) {
            $columnCache[$table] = array_flip(Schema::getColumnListing($table));
        }

        return array_filter(
            $attributes,
            fn ($value, $column) => array_key_exists($column, $columnCache[$table]),
            ARRAY_FILTER_USE_BOTH
        );
    }

    /**
     * @return array<int, int>
     */
    private function resolveStudentProgramIds(?Lead $lead, ?Registration $sourceRegistration, ?Admission $sourceAdmission): array
    {
        $phone = $sourceAdmission?->phone ?? $sourceRegistration?->phone ?? $lead?->phone;
        $cnic = $sourceAdmission?->cnic ?? $sourceRegistration?->cnic ?? data_get($lead?->details, 'cnic');

        $query = Admission::query()->whereNotNull('program_id');

        $query->where(function ($builder) use ($lead, $phone, $cnic) {
            $hasConstraint = false;

            if ($lead?->id) {
                $builder->orWhereHas('registration', fn ($registrationQuery) => $registrationQuery->where('lead_id', $lead->id));
                $hasConstraint = true;
            }

            if (filled($phone)) {
                $builder->orWhere('phone', $phone);
                $hasConstraint = true;
            }

            if (filled($cnic)) {
                $builder->orWhere('cnic', $cnic);
                $hasConstraint = true;
            }

            if (! $hasConstraint) {
                $builder->whereRaw('1 = 0');
            }
        });

        return $query->distinct()->pluck('program_id')->map(fn ($id) => (int) $id)->all();
    }

    private function studentAlreadyHasProgramAdmission(int $programId, ?Lead $lead, ?string $phone, ?string $cnic): bool
    {
        $query = Admission::query()->where('program_id', $programId);

        $query->where(function ($builder) use ($lead, $phone, $cnic) {
            $hasConstraint = false;

            if ($lead?->id) {
                $builder->orWhereHas('registration', fn ($registrationQuery) => $registrationQuery->where('lead_id', $lead->id));
                $hasConstraint = true;
            }

            if (filled($phone)) {
                $builder->orWhere('phone', $phone);
                $hasConstraint = true;
            }

            if (filled($cnic)) {
                $builder->orWhere('cnic', $cnic);
                $hasConstraint = true;
            }

            if (! $hasConstraint) {
                $builder->whereRaw('1 = 0');
            }
        });

        return $query->exists();
    }

    private function resolveAdmissionDocumentAbsolutePath(string $relativePath): ?string
    {
        $normalizedPath = ltrim(str_replace('\\', '/', $relativePath), '/');

        if ($normalizedPath === '' || Str::contains($normalizedPath, ['../', '..\\'])) {
            return null;
        }

        $candidates = array_unique(array_filter([
            Storage::disk('public')->exists($normalizedPath)
                ? Storage::disk('public')->path($normalizedPath)
                : null,
            file_exists(storage_path('app/public/' . $normalizedPath))
                ? storage_path('app/public/' . $normalizedPath)
                : null,
            file_exists(public_path('storage/' . $normalizedPath))
                ? public_path('storage/' . $normalizedPath)
                : null,
        ]));

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function canReviewAdmissions(?User $user): bool
    {
        return $user?->hasAnyPermission(['admission.review']) ?? false;
    }

    private function scopeAdmissionStatusQuery(Builder $query, ?User $user, string $scope): Builder
    {
        if ($scope === 'requested' && $this->canReviewAdmissions($user)) {
            return $query;
        }

        return $this->scopeQueryToUserCampus($query, $user);
    }

    private function parseAdmissionFilterDate(string $value): ?Carbon
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->startOfDay();
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function applyAdmissionPeriodFilter($query, string $period)
    {
        return match ($period) {
            'today' => $query->where(function ($builder) {
                $builder
                    ->whereDate('admission_date', today())
                    ->orWhere(function ($nested) {
                        $nested
                            ->whereNull('admission_date')
                            ->whereDate('created_at', today());
                    });
            }),
            'month' => $query->where(function ($builder) {
                $builder
                    ->where(function ($nested) {
                        $nested
                            ->whereNotNull('admission_date')
                            ->whereYear('admission_date', now()->year)
                            ->whereMonth('admission_date', now()->month);
                    })
                    ->orWhere(function ($nested) {
                        $nested
                            ->whereNull('admission_date')
                            ->whereYear('created_at', now()->year)
                            ->whereMonth('created_at', now()->month);
                    });
            }),
            'year' => $query->where(function ($builder) {
                $builder
                    ->whereYear('admission_date', now()->year)
                    ->orWhere(function ($nested) {
                        $nested
                            ->whereNull('admission_date')
                            ->whereYear('created_at', now()->year);
                    });
            }),
            default => $query,
        };
    }
}
