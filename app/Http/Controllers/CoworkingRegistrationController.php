<?php

namespace App\Http\Controllers;

use App\Models\Campus;
use App\Models\CoworkingRegistration;
use App\Models\CoworkingRegistrationReceipt;
use App\Models\Lead;
use App\Models\LeadFollowup;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class CoworkingRegistrationController extends Controller
{
    public function create(Request $request): View
    {
        $lead = null;
        if ($request->filled('lead_id')) {
            $lead = Lead::with(['campus', 'coworkingRegistration'])->find($request->input('lead_id'));
        }

        $campuses = Campus::query()->orderBy('name')->get(['id', 'code', 'city', 'city_abbr', 'name', 'title']);
        $selectedCampus = $this->resolveLeadCampus($lead, $campuses);
        $defaultRegistrationDate = old('registration_date', now()->toDateString());
        $preview = $selectedCampus
            ? $this->previewNumbers($selectedCampus->code, $this->parseRegistrationDate($defaultRegistrationDate))
            : ['registration_number' => '', 'receipt_number' => ''];

        return view('coworking_registration.create', [
            'lead' => $lead,
            'campuses' => $campuses,
            'preview' => $preview,
            'defaultCampusId' => $selectedCampus?->id,
            'defaultRegistrationDate' => $defaultRegistrationDate,
            'defaultNextDueDate' => $this->calculateNextDueDate($this->parseRegistrationDate($defaultRegistrationDate))->toDateString(),
        ]);
    }

    public function edit(Request $request, CoworkingRegistration $coworkingRegistration): View
    {
        $coworkingRegistration->load(['lead.campus', 'campus']);

        $lead = $coworkingRegistration->lead;
        $campuses = Campus::query()->orderBy('name')->get(['id', 'code', 'city', 'city_abbr', 'name', 'title']);
        $selectedCampus = $coworkingRegistration->campus
            ?? $this->resolveLeadCampus($lead, $campuses);

        return view('coworking_registration.create', [
            'lead' => $lead,
            'registration' => $coworkingRegistration,
            'campuses' => $campuses,
            'preview' => [
                'registration_number' => $coworkingRegistration->registration_number,
                'receipt_number' => $coworkingRegistration->receipt_number,
            ],
            'defaultCampusId' => $selectedCampus?->id ?? $coworkingRegistration->campus_id,
            'defaultRegistrationDate' => old('registration_date', optional($coworkingRegistration->registration_date)->toDateString()),
            'defaultNextDueDate' => old('next_due_date', optional($coworkingRegistration->next_due_date)->toDateString()),
            'isEditMode' => true,
            'formAction' => route('coworking-registrations.update', $coworkingRegistration),
            'submitLabel' => 'Update Registration',
        ]);
    }

    public function preview(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'campus_id' => ['required', 'exists:campuses,id'],
            'registration_date' => ['nullable', 'date'],
        ]);

        $campus = Campus::query()->findOrFail($validated['campus_id']);
        $registrationDate = $this->parseRegistrationDate($validated['registration_date'] ?? null);

        return response()->json(array_merge(
            $this->previewNumbers($campus->code, $registrationDate),
            ['next_due_date' => $this->calculateNextDueDate($registrationDate)->toDateString()]
        ));
    }

    public function store(Request $request): \Illuminate\Http\Response|RedirectResponse|JsonResponse
    {
        $validated = $request->validate(
            $this->registrationRules(),
            $this->registrationMessages(),
            $this->registrationAttributes()
        );

        try {
            $campus = Campus::query()->findOrFail($validated['campus_id']);
            $registrationDate = $this->parseRegistrationDate($validated['registration_date']);
            $nextDueDate = $this->calculateNextDueDate($registrationDate);

            $lead = null;
            if (! empty($validated['lead_id'])) {
                $lead = Lead::query()->with('coworkingRegistration')->find($validated['lead_id']);
            }

            if ($lead && $lead->type !== 'coworking') {
                throw ValidationException::withMessages([
                    'lead_id' => ['This lead is not a coworking lead.'],
                ]);
            }

            if ($lead?->coworkingRegistration) {
                throw ValidationException::withMessages([
                    'lead_id' => ['A coworking registration already exists for this lead.'],
                ]);
            }

            $result = DB::transaction(function () use ($validated, $campus, $registrationDate, $nextDueDate, $lead, $request) {
                $resolvedLead = $lead ?: Lead::query()->create([
                    'campus_id' => $campus->id,
                    'type' => 'coworking',
                    'name' => $validated['full_name'],
                    'email' => $validated['email'],
                    'phone' => $validated['phone'],
                    'city' => $campus->city,
                    'origin' => 'Coworking Registration',
                    'marketing_source' => 'Coworking Registration',
                    'status' => 'pending',
                    'details' => [],
                ]);

                $registration = $this->createRegistrationAtomically($campus->code, $registrationDate, [
                    'lead_id' => $resolvedLead->id,
                    'campus_id' => $campus->id,
                    'full_name' => $validated['full_name'],
                    'phone' => $validated['phone'],
                    'guardian_name' => $validated['guardian_name'],
                    'guardian_phone' => $validated['guardian_phone'],
                    'cnic' => $validated['cnic'],
                    'email' => $validated['email'],
                    'education' => $validated['education'],
                    'date_of_birth' => $validated['date_of_birth'],
                    'nature_of_work' => $validated['nature_of_work'],
                    'timing' => $validated['timing'],
                    'gender' => $validated['gender'],
                    'address' => $validated['address'],
                    'registration_date' => $registrationDate->toDateString(),
                    'next_due_date' => $nextDueDate->toDateString(),
                    'coworking_charges' => $validated['coworking_charges'],
                    'security_fee' => $validated['security_fee'],
                    'remarks' => $validated['remarks'] ?? null,
                    'status' => 'registered',
                    'created_by' => $request->user()?->id,
                ]);

                $securityReceipt = $this->createReceiptAtomically($campus->code, $registrationDate, 'security_fee', [
                    'coworking_registration_id' => $registration->id,
                    'lead_id' => $resolvedLead->id,
                    'campus_id' => $campus->id,
                    'receipt_type' => 'security_fee',
                    'amount' => $validated['security_fee'],
                    'paid_at' => $registrationDate->copy()->startOfDay(),
                    'notes' => 'Security fee collected at the time of coworking registration.',
                    'created_by' => $request->user()?->id,
                ]);

                $chargeReceipt = $this->createReceiptAtomically($campus->code, $registrationDate, 'coworking_charge', [
                    'coworking_registration_id' => $registration->id,
                    'lead_id' => $resolvedLead->id,
                    'campus_id' => $campus->id,
                    'receipt_type' => 'coworking_charge',
                    'amount' => $validated['coworking_charges'],
                    'paid_at' => $registrationDate->copy()->startOfDay(),
                    'notes' => 'Initial coworking charges collected at the time of registration.',
                    'created_by' => $request->user()?->id,
                ]);

                $this->syncLeadFromRegistration($resolvedLead, $campus, $validated, $registration, $request);

                return compact('registration', 'securityReceipt', 'chargeReceipt', 'resolvedLead');
            });

            $voucherUrls = [
                route('coworking-registrations.voucher', $result['registration']),
                route('coworking-registrations.receipts.voucher', $result['securityReceipt']),
                route('coworking-registrations.receipts.voucher', $result['chargeReceipt']),
            ];

            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'Coworking registration created successfully.',
                    'open_urls' => $voucherUrls,
                ]);
            }

            return response()->view('shared.voucher_redirect', [
                'voucherUrl' => $voucherUrls[0],
                'voucherUrls' => $voucherUrls,
                'redirectUrl' => route('leads.coworking.followups'),
                'heading' => 'Coworking Registration Created',
                'message' => 'Registration and fee slips are opening in new tabs...',
            ]);
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            report($e);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Unable to save the coworking registration right now. Please try again.',
                ], 500);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Unable to save the coworking registration right now. Please try again.');
        }
    }

    public function update(Request $request, CoworkingRegistration $coworkingRegistration): RedirectResponse|JsonResponse
    {
        $validated = $request->validate(
            $this->registrationRules($coworkingRegistration),
            $this->registrationMessages(),
            $this->registrationAttributes()
        );

        try {
            $campus = Campus::query()->findOrFail($validated['campus_id']);
            $registrationDate = $this->parseRegistrationDate($validated['registration_date']);
            $nextDueDate = $this->calculateNextDueDate($registrationDate);

            DB::transaction(function () use ($validated, $campus, $registrationDate, $nextDueDate, $coworkingRegistration, $request) {
                $coworkingRegistration->update([
                    'campus_id' => $campus->id,
                    'full_name' => $validated['full_name'],
                    'phone' => $validated['phone'],
                    'guardian_name' => $validated['guardian_name'],
                    'guardian_phone' => $validated['guardian_phone'],
                    'cnic' => $validated['cnic'],
                    'email' => $validated['email'],
                    'education' => $validated['education'],
                    'date_of_birth' => $validated['date_of_birth'],
                    'nature_of_work' => $validated['nature_of_work'],
                    'timing' => $validated['timing'],
                    'gender' => $validated['gender'],
                    'address' => $validated['address'],
                    'registration_date' => $registrationDate->toDateString(),
                    'next_due_date' => $nextDueDate->toDateString(),
                    'coworking_charges' => $validated['coworking_charges'],
                    'security_fee' => $validated['security_fee'],
                    'remarks' => $validated['remarks'] ?? null,
                ]);

                $this->updatePrimaryReceiptsFromRegistration(
                    $coworkingRegistration,
                    $campus,
                    $registrationDate,
                    $validated,
                    $request
                );

                if ($coworkingRegistration->lead) {
                    $this->syncLeadFromRegistration(
                        $coworkingRegistration->lead,
                        $campus,
                        $validated,
                        $coworkingRegistration,
                        $request,
                        false
                    );
                }
            });

            $status = 'Coworking registration updated successfully.';

            if ($request->expectsJson()) {
                return response()->json(['status' => $status]);
            }

            return redirect()
                ->route('coworking-registrations.show', $coworkingRegistration)
                ->with('status', $status);
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            report($e);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Unable to update the coworking registration right now. Please try again.',
                ], 500);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Unable to update the coworking registration right now. Please try again.');
        }
    }

    public function collectCharge(Request $request, CoworkingRegistration $coworkingRegistration): RedirectResponse|Response|JsonResponse
    {
        $validated = $request->validate([
            'charge_date' => ['required', 'date'],
            'charge_amount' => ['required', 'numeric', 'min:1'],
        ]);

        if ($coworkingRegistration->status !== 'registered') {
            return redirect()
                ->route('coworking-registrations.show', $coworkingRegistration)
                ->with('error', 'Charges can only be collected for active coworking members.');
        }

        $pendingChargeExists = $coworkingRegistration->receipts()
            ->where('receipt_type', 'coworking_charge')
            ->whereNull('paid_at')
            ->exists();

        if ($pendingChargeExists) {
            return redirect()
                ->route('coworking-registrations.show', $coworkingRegistration)
                ->with('error', 'A pending coworking charge already exists for this member.');
        }

        try {
            $chargeDate = Carbon::parse($validated['charge_date'])->startOfDay();
            $campus = $coworkingRegistration->campus ?? $coworkingRegistration->lead?->campus;
            $dueDate = $coworkingRegistration->next_due_date;
            $nextDueDate = $dueDate
                ? $dueDate->copy()->addMonthNoOverflow()
                : $chargeDate->copy()->addMonthNoOverflow();

            $chargeReceipt = DB::transaction(function () use ($campus, $chargeDate, $validated, $coworkingRegistration, $request, $dueDate) {
                return $this->createReceiptAtomically($campus?->code ?? 'CI', $chargeDate, 'coworking_charge', [
                    'coworking_registration_id' => $coworkingRegistration->id,
                    'lead_id' => $coworkingRegistration->lead_id,
                    'campus_id' => $campus?->id,
                    'receipt_type' => 'coworking_charge',
                    'amount' => $validated['charge_amount'],
                    'paid_at' => $chargeDate,
                    'notes' => 'Coworking charge collected for due date ' . optional($dueDate)->format('Y-m-d'),
                    'created_by' => $request->user()?->id,
                ]);
            });

            $coworkingRegistration->update([
                'next_due_date' => $nextDueDate->toDateString(),
            ]);

            $voucherUrls = [
                route('coworking-registrations.receipts.voucher', $chargeReceipt),
            ];

            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'Coworking charge collected successfully.',
                    'voucher_urls' => $voucherUrls,
                ]);
            }

            return response()->view('shared.voucher_redirect', [
                'voucherUrls' => $voucherUrls,
                'redirectUrl' => route('coworking-registrations.show', $coworkingRegistration),
                'heading' => 'Coworking Charge Collected',
                'message' => 'Opening the coworking charge receipt in a new tab...',
            ]);
        } catch (Throwable $e) {
            report($e);

            return redirect()
                ->route('coworking-registrations.show', $coworkingRegistration)
                ->with('error', 'Unable to collect the coworking charge right now. Please try again.');
        }
    }

    public function deactivate(Request $request, CoworkingRegistration $coworkingRegistration): RedirectResponse
    {
        $validated = $request->validate(
            $this->deactivationRules($coworkingRegistration),
            [],
            $this->deactivationAttributes()
        );

        if ($coworkingRegistration->status === 'inactive') {
            return redirect()
                ->route('coworking-registrations.show', $coworkingRegistration)
                ->with('status', 'This coworking member is already inactive.');
        }

        try {
            DB::transaction(function () use ($validated, $coworkingRegistration, $request) {
                $leaveDate = Carbon::parse($validated['leave_date'])->startOfDay();
                $snapshot = $this->buildDeactivationSnapshot($coworkingRegistration, $leaveDate, (float) $validated['damage_deduction_amount']);
                $campus = $coworkingRegistration->campus ?? $coworkingRegistration->lead?->campus;

                $coworkingRegistration->update([
                    'status' => 'inactive',
                    'leave_date' => $leaveDate->toDateString(),
                    'used_days' => $snapshot['used_days'],
                    'daily_deduction_amount' => $snapshot['daily_deduction_amount'],
                    'usage_deduction_amount' => $snapshot['usage_deduction_amount'],
                    'damage_deduction_amount' => $snapshot['damage_deduction_amount'],
                    'refund_amount' => $snapshot['refund_amount'],
                    'damage_notes' => $validated['damage_notes'] ?? null,
                    'inactive_reason' => $validated['inactive_reason'],
                    'inactive_remarks' => $validated['inactive_remarks'] ?? null,
                    'refund_processed_at' => now(),
                ]);

                $securityReceipt = $coworkingRegistration->receipts()
                    ->where('receipt_type', 'security_fee')
                    ->oldest('id')
                    ->first();

                $refundReceipt = $this->createReceiptAtomically(
                    $campus?->code ?? 'CI',
                    $leaveDate,
                    'security_refund',
                    [
                        'coworking_registration_id' => $coworkingRegistration->id,
                        'lead_id' => $coworkingRegistration->lead_id,
                        'campus_id' => $campus?->id,
                        'receipt_type' => 'security_refund',
                        'amount' => $snapshot['refund_amount'],
                        'paid_at' => now(),
                        'notes' => $this->buildSecurityRefundNotes(
                            $coworkingRegistration,
                            $snapshot,
                            $validated,
                            $securityReceipt?->receipt_number
                        ),
                        'created_by' => $request->user()?->id,
                    ]
                );

                if ($coworkingRegistration->lead) {
                    $details = $coworkingRegistration->lead->details ?? [];
                    $details['security_refund_status'] = 'refunded';
                    $details['security_refund_receipt_number'] = $refundReceipt->receipt_number;
                    $details['inactive_reason'] = $validated['inactive_reason'];

                    $coworkingRegistration->lead->update([
                        'details' => $details,
                    ]);
                }
            });

            return redirect()
                ->route('coworking-registrations.show', $coworkingRegistration)
                ->with('status', 'Coworking member marked inactive and security refund recorded.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            report($e);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Unable to process the inactive request right now. Please try again.');
        }
    }

    public function voucher(CoworkingRegistration $coworkingRegistration): View
    {
        $coworkingRegistration->load(['campus', 'lead', 'receipts']);

        return view('coworking_registration.voucher', [
            'registration' => $coworkingRegistration,
        ]);
    }

    public function show(CoworkingRegistration $coworkingRegistration): View
    {
        $coworkingRegistration->load([
            'campus',
            'lead.campus',
            'receipts' => fn ($query) => $query->orderBy('id'),
        ]);

        return view('coworking_registration.show', [
            'registration' => $coworkingRegistration,
        ]);
    }

    public function receiptVoucher(CoworkingRegistrationReceipt $receipt): View
    {
        $receipt->load(['campus', 'lead', 'coworkingRegistration']);

        return view('coworking_registration.receipt_voucher', compact('receipt'));
    }

    private function syncLeadFromRegistration(
        Lead $lead,
        Campus $campus,
        array $validated,
        CoworkingRegistration $registration,
        Request $request,
        bool $createFollowup = true
    ): void {
        $details = $lead->details ?? [];
        $details = array_merge($details, [
            'preferred_location' => $campus->code,
            'gender' => $validated['gender'],
            'guardian_name' => $validated['guardian_name'],
            'guardian_phone' => $validated['guardian_phone'],
            'cnic' => $validated['cnic'],
            'current_education' => $validated['education'],
            'date_of_birth' => $validated['date_of_birth'],
            'nature_of_work' => $validated['nature_of_work'],
            'timing' => $validated['timing'],
            'next_due_date' => $registration->next_due_date?->toDateString(),
            'coworking_charges' => $registration->coworking_charges,
            'security_fee' => $registration->security_fee,
            'remarks' => $validated['remarks'] ?? data_get($details, 'remarks'),
            'address' => $validated['address'],
        ]);

        $lead->update([
            'campus_id' => $campus->id,
            'type' => 'coworking',
            'name' => $validated['full_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'city' => $lead->city ?: $campus->city,
            'status' => 'registered',
            'details' => $details,
        ]);

        if (! $createFollowup) {
            return;
        }

        LeadFollowup::create([
            'lead_id' => $lead->id,
            'campus_id' => $campus->id,
            'user_id' => $request->user()?->id,
            'method' => 'walk-in',
            'probability' => 100,
            'note' => 'Lead registered via coworking registration form.',
            'next_action_date' => null,
            'stage' => 'registered',
            'lead_status' => 'registered',
        ]);
    }

    private function createRegistrationAtomically(string $campusCode, Carbon $registrationDate, array $attributes): CoworkingRegistration
    {
        $maxAttempts = 10;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            $numbers = $this->previewNumbers($campusCode, $registrationDate);

            try {
                return DB::transaction(function () use ($numbers, $attributes) {
                    return CoworkingRegistration::query()->create(array_merge($attributes, [
                        'registration_number' => $numbers['registration_number'],
                        'receipt_number' => $numbers['receipt_number'],
                    ]));
                });
            } catch (QueryException $e) {
                if ($this->isUniqueConstraint($e)) {
                    continue;
                }

                throw $e;
            }
        }

        throw new RuntimeException('Unable to generate unique coworking registration numbers after ' . $maxAttempts . ' attempts.');
    }

    private function createReceiptAtomically(string $campusCode, Carbon $registrationDate, string $receiptType, array $attributes): CoworkingRegistrationReceipt
    {
        $maxAttempts = 10;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            $receiptNumber = $this->previewChargeReceiptNumber($campusCode, $registrationDate, $receiptType);

            try {
                return DB::transaction(function () use ($receiptNumber, $attributes) {
                    return CoworkingRegistrationReceipt::query()->create(array_merge($attributes, [
                        'receipt_number' => $receiptNumber,
                    ]));
                });
            } catch (QueryException $e) {
                if ($this->isUniqueConstraint($e)) {
                    continue;
                }

                throw $e;
            }
        }

        throw new RuntimeException('Unable to generate unique coworking receipt numbers after ' . $maxAttempts . ' attempts.');
    }

    private function previewNumbers(string $campusCode, Carbon $registrationDate): array
    {
        $monthToken = $registrationDate->format('my');
        $registrationPrefix = $campusCode . '-CWS-' . $monthToken . '-';
        $receiptPrefix = $campusCode . '-' . $monthToken . '-';

        return [
            'registration_number' => $registrationPrefix . str_pad((string) $this->nextSequence(
                CoworkingRegistration::query(),
                'registration_number',
                $registrationPrefix
            ), 5, '0', STR_PAD_LEFT),
            'receipt_number' => $receiptPrefix . str_pad((string) $this->nextSequence(
                CoworkingRegistration::query(),
                'receipt_number',
                $receiptPrefix
            ), 5, '0', STR_PAD_LEFT),
        ];
    }

    private function previewChargeReceiptNumber(string $campusCode, Carbon $registrationDate, string $receiptType): string
    {
        $prefix = $this->chargeReceiptPrefix($campusCode, $registrationDate, $receiptType);

        return $prefix . str_pad((string) $this->nextSequence(
            CoworkingRegistrationReceipt::query(),
            'receipt_number',
            $prefix
        ), 5, '0', STR_PAD_LEFT);
    }

    private function chargeReceiptPrefix(string $campusCode, Carbon $registrationDate, string $receiptType): string
    {
        $token = $registrationDate->format('my');

        return match ($receiptType) {
            'security_fee' => $campusCode . '-SEC-' . $token . '-',
            'security_refund' => $campusCode . '-SRF-' . $token . '-',
            default => $campusCode . '-CHG-' . $token . '-',
        };
    }

    private function nextSequence($baseQuery, string $column, string $prefix): int
    {
        $max = (clone $baseQuery)
            ->where($column, 'like', $prefix . '%')
            ->get([$column])
            ->map(function ($row) use ($column, $prefix) {
                $tail = substr((string) $row->{$column}, strlen($prefix));

                return ctype_digit($tail) ? (int) $tail : 0;
            })
            ->max();

        return ((int) $max) + 1;
    }

    private function parseRegistrationDate(?string $value): Carbon
    {
        return filled($value)
            ? Carbon::parse($value)->startOfDay()
            : now()->startOfDay();
    }

    private function calculateNextDueDate(Carbon $registrationDate): Carbon
    {
        return $registrationDate->copy()->addMonthNoOverflow();
    }

    private function resolveLeadCampus(?Lead $lead, Collection $campuses): ?Campus
    {
        if (! $lead) {
            return null;
        }

        if ($lead->campus) {
            return $campuses->firstWhere('id', $lead->campus->id) ?? $lead->campus;
        }

        $candidates = array_filter([
            data_get($lead->details, 'preferred_location'),
            $lead->city,
            data_get($lead->details, 'area'),
        ], fn ($value) => trim((string) $value) !== '');

        foreach ($candidates as $candidate) {
            $needle = Str::lower(trim((string) $candidate));

            $campus = $campuses->first(function (Campus $campus) use ($needle) {
                return in_array($needle, array_filter([
                    Str::lower((string) $campus->code),
                    Str::lower((string) $campus->city),
                    Str::lower((string) $campus->city_abbr),
                    Str::lower((string) $campus->name),
                    Str::lower((string) $campus->title),
                ], fn ($value) => $value !== ''), true);
            });

            if ($campus) {
                return $campus;
            }
        }

        return null;
    }

    private function isUniqueConstraint(QueryException $e): bool
    {
        $message = $e->getMessage();

        return str_contains($message, 'UNIQUE')
            || str_contains($message, 'Duplicate entry')
            || $e->getCode() === '23000';
    }

    private function registrationRules(?CoworkingRegistration $registration = null): array
    {
        $registrationId = $registration?->id;

        return [
            'lead_id' => ['nullable', 'exists:leads,id'],
            'campus_id' => ['required', 'exists:campuses,id'],
            'full_name' => ['required', 'string', 'min:3', 'max:255'],
            'phone' => ['required', 'regex:/^03\d{9}$/', Rule::unique('coworking_registrations', 'phone')->ignore($registrationId)],
            'guardian_name' => ['required', 'string', 'min:3', 'max:255'],
            'guardian_phone' => ['required', 'regex:/^03\d{9}$/'],
            'cnic' => ['required', 'regex:/^\d{13}$/', Rule::unique('coworking_registrations', 'cnic')->ignore($registrationId)],
            'email' => ['required', 'email', 'max:255'],
            'education' => ['required', 'string', 'max:255'],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'nature_of_work' => ['required', 'string', 'max:255'],
            'timing' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'in:male,female,other'],
            'address' => ['required', 'string', 'min:10', 'max:1000'],
            'registration_date' => ['required', 'date'],
            'coworking_charges' => ['required', 'numeric', 'min:1'],
            'security_fee' => ['required', 'numeric', 'min:0'],
            'remarks' => ['required', 'string', 'max:2000'],
        ];
    }

    private function deactivationRules(CoworkingRegistration $registration): array
    {
        return [
            'leave_date' => ['required', 'date', 'after_or_equal:' . optional($registration->registration_date)->toDateString()],
            'damage_deduction_amount' => ['required', 'numeric', 'min:0'],
            'damage_notes' => ['nullable', 'string', 'max:2000'],
            'inactive_reason' => ['required', 'string', 'min:3', 'max:255'],
            'inactive_remarks' => ['nullable', 'string', 'max:2000'],
        ];
    }

    private function registrationMessages(): array
    {
        return [
            'phone.regex' => 'The primary contact number must be 11 digits and start with 03.',
            'phone.unique' => 'This primary contact number is already registered.',
            'guardian_phone.regex' => 'The guardian contact number must be 11 digits and start with 03.',
            'cnic.regex' => 'The CNIC must be exactly 13 digits.',
            'cnic.unique' => 'This CNIC is already registered in coworking registrations.',
            'date_of_birth.before' => 'The date of birth must be earlier than today.',
        ];
    }

    private function registrationAttributes(): array
    {
        return [
            'campus_id' => 'campus',
            'full_name' => 'full name',
            'phone' => 'primary contact number',
            'guardian_name' => 'guardian name',
            'guardian_phone' => 'guardian contact number',
            'cnic' => 'CNIC',
            'date_of_birth' => 'date of birth',
            'nature_of_work' => 'nature of work',
            'timing' => 'timing',
            'address' => 'postal address',
            'registration_date' => 'registration date',
            'coworking_charges' => 'coworking charges',
            'security_fee' => 'security fee',
        ];
    }

    private function deactivationAttributes(): array
    {
        return [
            'leave_date' => 'leave date',
            'damage_deduction_amount' => 'damage deduction',
            'damage_notes' => 'damage details',
            'inactive_reason' => 'reason for leaving',
            'inactive_remarks' => 'remarks',
        ];
    }

    private function updatePrimaryReceiptsFromRegistration(
        CoworkingRegistration $registration,
        Campus $campus,
        Carbon $registrationDate,
        array $validated,
        Request $request
    ): void {
        $baseAttributes = [
            'lead_id' => $registration->lead_id,
            'campus_id' => $campus->id,
            'created_by' => $request->user()?->id,
        ];

        $registration->receipts()
            ->where('receipt_type', 'security_fee')
            ->oldest('id')
            ->first()
            ?->update(array_merge($baseAttributes, [
                'amount' => $validated['security_fee'],
                'paid_at' => $registrationDate->copy()->startOfDay(),
                'notes' => 'Security fee collected at the time of coworking registration.',
            ]));

        $registration->receipts()
            ->where('receipt_type', 'coworking_charge')
            ->oldest('id')
            ->first()
            ?->update(array_merge($baseAttributes, [
                'amount' => $validated['coworking_charges'],
                'paid_at' => $registrationDate->copy()->startOfDay(),
                'notes' => 'Initial coworking charges collected at the time of registration.',
            ]));
    }

    private function buildDeactivationSnapshot(
        CoworkingRegistration $registration,
        Carbon $leaveDate,
        float $damageDeduction
    ): array {
        $registrationDate = $registration->registration_date
            ? Carbon::parse($registration->registration_date)->startOfDay()
            : $leaveDate->copy();
        $nextDueDate = $registration->next_due_date
            ? Carbon::parse($registration->next_due_date)->startOfDay()
            : $registrationDate->copy()->addMonthNoOverflow();
        $cycleDays = max(1, $registrationDate->diffInDays($nextDueDate));
        $usedDays = max(0, $nextDueDate->diffInDays($leaveDate));
        $dailyDeductionAmount = round(((float) $registration->coworking_charges) / $cycleDays, 2);
        $usageDeductionAmount = round($dailyDeductionAmount * $usedDays, 2);
        $securityFee = (float) $registration->security_fee;
        $damageDeductionAmount = round(max(0, $damageDeduction), 2);
        $refundAmount = max(0, round($securityFee - $usageDeductionAmount - $damageDeductionAmount, 2));

        return [
            'used_days' => $usedDays,
            'daily_deduction_amount' => $dailyDeductionAmount,
            'usage_deduction_amount' => $usageDeductionAmount,
            'damage_deduction_amount' => $damageDeductionAmount,
            'refund_amount' => $refundAmount,
        ];
    }

    private function buildSecurityRefundNotes(
        CoworkingRegistration $registration,
        array $snapshot,
        array $validated,
        ?string $securityReceiptNumber
    ): string {
        $lines = array_filter([
            'Security fee refund processed for coworking registration.',
            $securityReceiptNumber ? 'Original Security Receipt: ' . $securityReceiptNumber : null,
            'Security Fee: PKR ' . number_format((float) $registration->security_fee, 2, '.', ''),
            'Daily Deduction: PKR ' . number_format((float) $snapshot['daily_deduction_amount'], 2, '.', '') . ' x ' . $snapshot['used_days'] . ' day(s)',
            'Day Wise Deduction: PKR ' . number_format((float) $snapshot['usage_deduction_amount'], 2, '.', ''),
            'Damage Deduction: PKR ' . number_format((float) $snapshot['damage_deduction_amount'], 2, '.', ''),
            'Refund Amount: PKR ' . number_format((float) $snapshot['refund_amount'], 2, '.', ''),
            filled($validated['damage_notes'] ?? null) ? 'Damage Details: ' . $validated['damage_notes'] : null,
            'Reason for Leaving: ' . $validated['inactive_reason'],
            filled($validated['inactive_remarks'] ?? null) ? 'Remarks: ' . $validated['inactive_remarks'] : null,
        ]);

        return implode(PHP_EOL, $lines);
    }
}

