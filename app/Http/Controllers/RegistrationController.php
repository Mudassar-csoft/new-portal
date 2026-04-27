<?php

namespace App\Http\Controllers;

use App\Models\Campus;
use App\Models\Lead;
use App\Models\LeadFollowup;
use App\Models\Program;
use App\Models\FeeCollection;
use App\Models\Registration;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class RegistrationController extends Controller
{
    public function create(Request $request): View
    {
        $lead = null;
        if ($request->filled('lead_id')) {
            $lead = Lead::with(['campus', 'program'])->find($request->input('lead_id'));
        }

        $campuses = Campus::orderBy('name')->get();
        $programs = Program::orderBy('title')->get();
        $selectedCampusId = (int) ($request->old('campus_id', $lead?->campus_id) ?? 0);
        $selectedCampus = $selectedCampusId > 0 ? $campuses->firstWhere('id', $selectedCampusId) : null;
        $preview = $selectedCampus
            ? $this->previewNumbers($selectedCampus->code)
            : ['registration_number' => '', 'receipt_number' => ''];

        return view('registration.create', [
            'lead' => $lead,
            'campuses' => $campuses,
            'programs' => $programs,
            'preview' => $preview,
        ]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate(
            $this->registrationRules(),
            $this->registrationMessages(),
            $this->registrationAttributes()
        );

        try {
            $campus = Campus::findOrFail($validated['campus_id']);
            $regNumbers = $this->ensureUniqueNumbers($this->previewNumbers($campus->code));

            // Fixed fee and no discount per request
            $fee = 2000;
            $discount = 0;
            $net = $fee - $discount;

            $registration = Registration::create([
                'lead_id' => $validated['lead_id'] ?? null,
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

            return redirect()->route('registration.voucher', $registration);
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

        $campus = Campus::findOrFail($request->campus_id);
        return response()->json($this->previewNumbers($campus->code));
    }

    public function status(): View
    {
        $registrations = Registration::with(['program'])
            ->orderByDesc('registered_at')
            ->orderByDesc('id')
            ->get();

        return view('registration.status', compact('registrations'));
    }

    public function voucher(Registration $registration): View
    {
        $registration->load(['campus', 'program', 'lead']);
        return view('registration.voucher', compact('registration'));
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

    private function ensureUniqueNumbers(array $numbers): array
    {
        $attempts = 0;
        while (
            Registration::where('registration_number', $numbers['registration_number'])
                ->orWhere('receipt_number', $numbers['receipt_number'])
                ->exists()
        ) {
            $numbers['registration_number'] = $this->incrementNumber($numbers['registration_number'], 2);
            $numbers['receipt_number'] = $this->incrementNumber($numbers['receipt_number'], 6);
            $attempts++;
            if ($attempts > 20) {
                throw new RuntimeException('Unable to generate unique registration numbers.');
            }
        }

        return $numbers;
    }

    private function incrementNumber(string $value, int $pad): string
    {
        $parts = explode('-', $value);
        $last = array_pop($parts);
        $next = str_pad((string)((int)$last + 1), $pad, '0', STR_PAD_LEFT);
        $parts[] = $next;
        return implode('-', $parts);
    }

    private function registrationRules(): array
    {
        return [
            'lead_id' => ['nullable', 'exists:leads,id'],
            'campus_id' => ['required', 'exists:campuses,id'],
            'program_id' => ['required', 'exists:programs,id'],
            'student_name' => ['required', 'string', 'min:3', 'max:255'],
            'phone' => ['required', 'regex:/^03\d{9}$/'],
            'guardian_name' => ['required', 'string', 'min:3', 'max:255'],
            'guardian_phone' => ['required', 'regex:/^03\d{9}$/'],
            'cnic' => ['required', 'regex:/^\d{13}$/'],
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
            'guardian_phone.regex' => 'The guardian contact number must be 11 digits and start with 03.',
            'cnic.regex' => 'The CNIC must be exactly 13 digits.',
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
