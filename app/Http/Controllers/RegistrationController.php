<?php

namespace App\Http\Controllers;

use App\Models\Campus;
use App\Models\Lead;
use App\Models\LeadFollowup;
use App\Models\Program;
use App\Models\FeeCollection;
use App\Models\Registration;
use Carbon\Carbon;
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

        $preview = $lead && $lead->campus ? $this->previewNumbers($lead->campus->code) : ['registration_number' => '', 'receipt_number' => ''];

        return view('registration.create', [
            'lead' => $lead,
            'campuses' => $campuses,
            'programs' => $programs,
            'preview' => $preview,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'lead_id' => ['nullable', 'exists:leads,id'],
            'campus_id' => ['required', 'exists:campuses,id'],
            'program_id' => ['required', 'exists:programs,id'],
            'student_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'fee' => ['nullable', 'numeric'],
            'discount' => ['nullable', 'numeric'],
        ]);

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
                'email' => $validated['email'] ?? null,
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

            return redirect()->route('registration.voucher', $registration);
        } catch (Throwable $e) {
            report($e);
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
}
