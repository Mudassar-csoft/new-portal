<?php

namespace App\Http\Controllers;

use App\Models\Admission;
use App\Models\Batch;
use App\Models\Campus;
use App\Models\Lead;
use App\Models\LeadFollowup;
use App\Models\Program;
use App\Models\ProgramCampusDiscount;
use App\Models\Registration;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdmissionController extends Controller
{
    public function create(Request $request): View
    {
        $lead = null;
        if ($request->filled('lead_id')) {
            $lead = Lead::with(['campus', 'program'])->find($request->input('lead_id'));
        }

        $campuses = Campus::orderBy('name')->get();
        $programs = Program::orderBy('title')->get();
        $batches = Batch::orderBy('name')->get();

        return view('admission.create', compact('campuses', 'programs', 'batches', 'lead'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'lead_id' => ['nullable', 'exists:leads,id'],
            'campus_id' => ['required', 'exists:campuses,id'],
            'program_id' => ['required', 'exists:programs,id'],
            'batch_id' => ['required', 'exists:batches,id'],
            'student_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'guardian_name' => ['required', 'string', 'max:255'],
            'guardian_phone' => ['required', 'string', 'max:50'],
            'cnic' => ['required', 'string', 'max:50'],
            'passport_number' => ['nullable', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255'],
            'education' => ['required', 'string', 'max:255'],
            'date_of_birth' => ['required', 'date'],
            'gender' => ['required', 'in:male,female,other'],
            'country' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'area' => ['required', 'string', 'max:150'],
            'postal_address' => ['required', 'string', 'max:500'],
            'registration_number' => ['nullable', 'string', 'max:100', 'unique:admissions,registration_number'],
            'roll_number' => ['required', 'string', 'max:100', 'unique:admissions,roll_number'],
            'admission_date' => ['required', 'date'],
            'fee_package' => ['nullable', 'numeric'],
            'discount_amount' => ['nullable', 'numeric'],
            'discount_percent' => ['nullable', 'numeric'],
            'discounted_fee' => ['nullable', 'numeric'],
            'fee_type' => ['required', 'in:full,installments'],
            'remarks' => ['required', 'string', 'max:1000'],
            'receipt_number' => ['nullable', 'string', 'max:100', 'unique:admissions,receipt_number'],
        ]);

        $campus = Campus::findOrFail($validated['campus_id']);
        $program = Program::findOrFail($validated['program_id']);

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
            $lead = Lead::find($validated['lead_id']);
        }

        if (!$lead) {
            $lead = Lead::create([
                'campus_id' => $validated['campus_id'],
                'program_id' => $validated['program_id'],
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
        $receiptNumber = $validated['receipt_number'] ?? $this->generateAdmissionReceiptNumber($campus->code);

        Admission::create([
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
            'roll_number' => $validated['roll_number'],
            'admission_date' => $validated['admission_date'],
            'fee_package' => $feePackage,
            'discount_amount' => $discountAmount,
            'discount_percent' => $discountPercent,
            'discounted_fee' => $discountedFee,
            'fee_type' => $validated['fee_type'],
            'remarks' => $validated['remarks'],
            'receipt_number' => $receiptNumber,
        ]);

        return redirect()->route('admission.status')->with('status', 'Admission created.');
    }

    public function status(): View
    {
        $admissions = Admission::with(['program', 'batch'])
            ->orderByDesc('admission_date')
            ->orderByDesc('id')
            ->get();

        return view('admission.status', compact('admissions'));
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
        $now = Carbon::now();
        $monthYear = $now->format('my'); // e.g. 0126

        $countForMonth = Admission::where('receipt_number', 'like', $campusCode . '-' . $monthYear . '-%')->count() + 1;
        $countPadded = str_pad((string)$countForMonth, 6, '0', STR_PAD_LEFT);

        return $campusCode . '-' . $monthYear . '-' . $countPadded;
    }
}
