<?php

namespace App\Http\Controllers;

use App\Models\Campus;
use App\Models\Lead;
use App\Models\LeadFollowup;
use App\Models\Program;
use App\Models\FeeCollection;
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

class RegistrationController extends Controller
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
        $selectedCampusId = (int) ($request->old('campus_id', $lead?->campus_id) ?? 0);
        $selectedCampus = $selectedCampusId > 0
            ? $campuses->firstWhere('id', $selectedCampusId)
            : null;
        $preview = $selectedCampus
            ? $this->previewNumbers($selectedCampus->code)
            : ['registration_number' => '', 'receipt_number' => ''];

        return view('registration.create', [
            'lead' => $lead,
            'campuses' => $campuses,
            'programs' => $programs,
            'preview' => $preview,
            'defaultCampusId' => $selectedCampus?->id,
        ]);
    }

    public function store(Request $request): \Illuminate\Http\Response|RedirectResponse|JsonResponse
    {
        $validated = $request->validate(
            $this->registrationRules(),
            $this->registrationMessages(),
            $this->registrationAttributes()
        );

        try {
            $campus = Campus::findOrFail($validated['campus_id']);

            // Fixed fee and no discount per request
            $fee = 2000;
            $discount = 0;
            $net = $fee - $discount;

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
                    'type' => null,
                    'name' => $validated['student_name'],
                    'email' => $validated['email'],
                    'phone' => $validated['phone'],
                    'origin' => 'Registration',
                    'marketing_source' => 'Registration',
                    'status' => 'pending',
                    'details' => [
                        'gender' => $validated['gender'] ?? null,
                        'education' => $validated['education'] ?? null,
                        'address' => $validated['address'] ?? null,
                        'guardian_name' => $validated['guardian_name'] ?? null,
                        'guardian_phone' => $validated['guardian_phone'] ?? null,
                        'cnic' => $validated['cnic'] ?? null,
                        'passport_number' => $validated['passport_number'] ?? null,
                        'date_of_birth' => $validated['date_of_birth'] ?? null,
                    ],
                ]);

                LeadFollowup::create([
                    'lead_id' => $lead->id,
                    'campus_id' => $lead->campus_id,
                    'user_id' => $request->user()?->id,
                    'note' => 'Initial follow-up created via direct registration.',
                    'method' => null,
                    'probability' => null,
                    'next_action_date' => null,
                    'stage' => 'new',
                    'lead_status' => 'pending',
                ]);
            }

            $registration = $this->createRegistrationAtomically($campus->code, [
                'lead_id' => $lead->id,
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
                'address' => $validated['address'],
                'remarks' => $validated['remarks'] ?? null,
                'fee' => $fee,
                'discount' => $discount,
                'net_payable' => $net,
                'status' => 'registered',
                'registered_at' => Carbon::now(),
            ]);

            $hasRegistrationFee = FeeCollection::where('registration_id', $registration->id)
                ->where('fee_type', 'registration')
                ->exists();
            if (!$hasRegistrationFee) {
                FeeCollection::create([
                    'lead_id' => $registration->lead_id,
                    'registration_id' => $registration->id,
                    'campus_id' => $registration->campus_id,
                    'program_id' => $registration->program_id,
                    'fee_type' => 'registration',
                    'amount' => $fee,
                    'discount_percent' => 0,
                    'discount_amount' => $discount,
                    'net_amount' => $net,
                    'receipt_number' => $registration->receipt_number,
                    'status' => 'paid',
                    'paid_at' => Carbon::now(),
                    'created_by' => $request->user()?->id,
                    'notes' => 'Registration fee collected.',
                ]);
            }

            if ($registration->lead_id) {
                $lead = Lead::find($registration->lead_id);
                if ($lead) {
                    $leadStatus = $lead->status === 'enrolled' ? 'enrolled' : 'registered';
                    $lead->update([
                        'status' => $leadStatus,
                        'campus_id' => $registration->campus_id,
                        'program_id' => $registration->program_id,
                        'name' => $registration->student_name,
                        'email' => $registration->email,
                        'phone' => $registration->phone,
                    ]);

                    LeadFollowup::create([
                        'lead_id' => $lead->id,
                        'campus_id' => $registration->campus_id,
                        'user_id' => $request->user()?->id,
                        'method' => 'walk-in',
                        'probability' => 100,
                        'note' => 'Lead registered via registration form.',
                        'stage' => 'registered',
                        'lead_status' => $leadStatus,
                    ]);

                }
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'Registration created successfully.',
                    'redirect_url' => route('registration.voucher', $registration),
                ]);
            }

            return response()->view('shared.voucher_redirect', [
                'voucherUrl' => route('registration.voucher', $registration),
                'redirectUrl' => route('registration.status'),
                'heading' => 'Registration Created',
                'message' => 'Registration saved. Opening the registration voucher in a new tab...',
            ]);
        } catch (Throwable $e) {
            report($e);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Unable to save the registration right now. Please try again.',
                ], 500);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Unable to save the registration right now. Please try again.');
        }
    }

    public function preview(Request $request)
    {
        $request->validate([
            'campus_id' => ['required', 'exists:campuses,id'],
        ]);

        $campus = Campus::query()->whereKey($request->integer('campus_id'))->firstOrFail();

        return response()->json($this->previewNumbers($campus->code));
    }

    public function status(): View
    {
        $registrations = $this->scopeQueryToUserCampus(Registration::query(), auth()->user())
            ->with(['campus', 'admission'])
            ->orderByDesc('registered_at')
            ->orderByDesc('id')
            ->get();

        return view('registration.status', compact('registrations'));
    }

    public function voucher(Registration $registration): View
    {
        $this->ensureCampusAccess((int) ($registration->campus_id ?? 0), auth()->user(), 'You are not allowed to access registrations from another campus.');

        $registration->load(['campus', 'program', 'lead']);
        return view('registration.voucher', compact('registration'));
    }

    /**
     * Compute the NEXT registration & receipt numbers for a campus + current month,
     * based on the highest existing sequence (not a count, so gaps don't break it).
     * Use this for read-only previews — actual save uses a transactional retry loop.
     */
    private function previewNumbers(string $campusCode): array
    {
        $prefix = $campusCode . '-' . Carbon::now()->format('my') . '-';
        $regNext = $this->nextSequence(Registration::query(), 'registration_number', $prefix);
        $recNext = $this->nextSequence(Registration::query(), 'receipt_number', $prefix);

        return [
            'registration_number' => $prefix . str_pad((string) $regNext, 2, '0', STR_PAD_LEFT),
            'receipt_number' => $prefix . str_pad((string) $recNext, 6, '0', STR_PAD_LEFT),
        ];
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
     * Generate unique numbers and insert the Registration atomically.
     * Retries on UNIQUE-constraint violations (which can happen on concurrent inserts).
     *
     * @param  array<string,mixed>  $registrationAttributes  All columns except registration_number/receipt_number.
     * @return Registration
     */
    private function createRegistrationAtomically(string $campusCode, array $registrationAttributes): Registration
    {
        $maxAttempts = 10;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            $numbers = $this->previewNumbers($campusCode);

            try {
                return DB::transaction(function () use ($numbers, $registrationAttributes) {
                    return Registration::create(array_merge($registrationAttributes, [
                        'registration_number' => $numbers['registration_number'],
                        'receipt_number' => $numbers['receipt_number'],
                    ]));
                });
            } catch (QueryException $e) {
                // SQLite: "UNIQUE constraint failed". MySQL: "Duplicate entry".
                $msg = $e->getMessage();
                if (str_contains($msg, 'UNIQUE') || str_contains($msg, 'Duplicate entry') || $e->getCode() === '23000') {
                    // Race lost — regenerate and retry.
                    continue;
                }
                throw $e;
            }
        }

        throw new RuntimeException('Unable to generate unique registration numbers after ' . $maxAttempts . ' attempts.');
    }

    private function registrationRules(): array
    {
        return [
            'lead_id' => ['nullable', 'exists:leads,id'],
            'campus_id' => ['required', 'exists:campuses,id'],
            'program_id' => ['required', 'exists:programs,id'],
            'student_name' => ['required', 'string', 'min:3', 'max:255'],
            'phone' => ['required', 'regex:/^03\d{9}$/', 'unique:registrations,phone'],
            'guardian_name' => ['required', 'string', 'min:3', 'max:255'],
            'guardian_phone' => ['required', 'regex:/^03\d{9}$/'],
            'cnic' => ['required', 'regex:/^\d{13}$/', 'unique:registrations,cnic'],
            'passport_number' => ['nullable', 'string', 'min:5', 'max:50'],
            'email' => ['required', 'email', 'max:255'],
            'education' => ['required', 'string', 'max:255'],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'gender' => ['required', 'in:male,female,other'],
            'address' => ['required', 'string', 'min:10', 'max:1000'],
            'remarks' => ['nullable', 'string', 'max:2000'],
            'fee' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    private function registrationMessages(): array
    {
        return [
            'phone.regex' => 'The primary contact number must be 11 digits and start with 03.',
            'phone.unique' => 'This primary contact number is already registered.',
            'guardian_phone.regex' => 'The guardian contact number must be 11 digits and start with 03.',
            'cnic.regex' => 'The CNIC must be exactly 13 digits.',
            'cnic.unique' => 'This CNIC is already registered.',
            'date_of_birth.before' => 'The date of birth must be earlier than today.',
        ];
    }

    private function registrationAttributes(): array
    {
        return [
            'campus_id' => 'campus',
            'program_id' => 'program',
            'student_name' => 'full name',
            'phone' => 'primary contact number',
            'guardian_name' => 'guardian name',
            'guardian_phone' => 'guardian contact number',
            'cnic' => 'CNIC',
            'passport_number' => 'passport number',
            'date_of_birth' => 'date of birth',
            'address' => 'postal address',
            'remarks' => 'remarks',
        ];
    }
}
