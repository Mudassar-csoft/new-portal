<?php

namespace App\Http\Controllers;

use App\Models\Campus;
use App\Models\Lead;
use App\Models\LeadFollowup;
use App\Models\Program;
use App\Models\Registration;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

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

        $campus = Campus::findOrFail($validated['campus_id']);
        $regNumbers = $this->previewNumbers($campus->code);

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

        if ($registration->lead_id) {
            $lead = Lead::find($registration->lead_id);
            if ($lead) {
                $lead->update([
                    'status' => 'registered',
                    'campus_id' => $registration->campus_id,
                    'program_id' => $registration->program_id,
                ]);

                LeadFollowup::create([
                    'lead_id' => $lead->id,
                    'campus_id' => $registration->campus_id,
                    'user_id' => $request->user()?->id,
                    'method' => 'walk-in',
                    'probability' => 100,
                    'note' => 'Lead registered via registration form.',
                    'stage' => 'registered',
                    'lead_status' => 'registered',
                ]);
            }
        }

        return redirect()->route('registration.status')->with('status', 'Registration created.');
    }

    public function preview(Request $request)
    {
        $request->validate([
            'campus_id' => ['required', 'exists:campuses,id'],
        ]);

        $campus = Campus::findOrFail($request->campus_id);
        return response()->json($this->previewNumbers($campus->code));
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
}
