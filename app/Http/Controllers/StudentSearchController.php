<?php

namespace App\Http\Controllers;

use App\Models\Admission;
use App\Models\CoworkingRegistration;
use App\Models\Lead;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentSearchController extends Controller
{
    public function index(Request $request): View
    {
        $query = trim((string) $request->query('q', ''));
        $normalizedDigits = preg_replace('/\D+/', '', $query);

        $admissions = collect();
        $registrations = collect();
        $coworkingRegistrations = collect();
        $leads = collect();

        if ($query !== '') {
            $needle = '%' . $query . '%';
            $normalizedDigitsNeedle = $normalizedDigits !== '' ? '%' . $normalizedDigits . '%' : null;

            // Cascading priority search: only fall through to the next tier
            // when the previous one has no matches, instead of showing all
            // four categories at once. Order: Admission -> Registration -> Lead -> Coworking.
            // Search results are visible across all campuses; only opening
            // or acting on a record is restricted to the user's own campus.
            $admissions = Admission::query()
                ->with(['program:id,code,title,name', 'campus:id,code,name', 'batch:id,code,name'])
                ->where(function ($q) use ($needle, $normalizedDigitsNeedle) {
                    $q->where('student_name', 'like', $needle)
                        ->orWhere('phone', 'like', $needle)
                        ->orWhere('roll_number', 'like', $needle)
                        ->orWhere('registration_number', 'like', $needle)
                        ->orWhere('cnic', 'like', $needle)
                        ->orWhere('email', 'like', $needle);

                    if ($normalizedDigitsNeedle !== null && $normalizedDigitsNeedle !== $needle) {
                        $q->orWhere('cnic', 'like', $normalizedDigitsNeedle);
                    }
                })
                ->orderByDesc('admission_date')
                ->orderByDesc('id')
                ->limit(50)
                ->get();

            if ($admissions->isEmpty()) {
                $registrations = Registration::query()
                    ->with(['program:id,code,title,name', 'campus:id,code,name'])
                    ->where(function ($q) use ($needle, $normalizedDigitsNeedle) {
                        $q->where('student_name', 'like', $needle)
                            ->orWhere('phone', 'like', $needle)
                            ->orWhere('cnic', 'like', $needle)
                            ->orWhere('registration_number', 'like', $needle)
                            ->orWhere('receipt_number', 'like', $needle);

                        if ($normalizedDigitsNeedle !== null && $normalizedDigitsNeedle !== $needle) {
                            $q->orWhere('cnic', 'like', $normalizedDigitsNeedle);
                        }
                    })
                    ->orderByDesc('id')
                    ->limit(50)
                    ->get();
            }

            if ($admissions->isEmpty() && $registrations->isEmpty()) {
                $leads = Lead::query()
                    ->with(['program:id,code,title,name', 'campus:id,code,name'])
                    // Leads that already converted into a registration/admission/coworking
                    // membership are shown via those sections instead, not as a lead too.
                    ->whereNotIn('status', ['registered', 'enrolled'])
                    ->where(function ($q) use ($needle, $normalizedDigitsNeedle) {
                        $q->where('name', 'like', $needle)
                            ->orWhere('phone', 'like', $needle)
                            ->orWhere('email', 'like', $needle);

                        if ($normalizedDigitsNeedle !== null && $normalizedDigitsNeedle !== $needle) {
                            $q->orWhere('phone', 'like', $normalizedDigitsNeedle);
                        }
                    })
                    ->orderByDesc('id')
                    ->limit(50)
                    ->get();
            }

            if ($admissions->isEmpty() && $registrations->isEmpty() && $leads->isEmpty()) {
                $coworkingRegistrations = CoworkingRegistration::query()
                    ->with(['campus:id,code,name'])
                    ->where(function ($q) use ($needle, $normalizedDigitsNeedle) {
                        $q->where('full_name', 'like', $needle)
                            ->orWhere('phone', 'like', $needle)
                            ->orWhere('cnic', 'like', $needle)
                            ->orWhere('email', 'like', $needle)
                            ->orWhere('registration_number', 'like', $needle)
                            ->orWhere('receipt_number', 'like', $needle);

                        if ($normalizedDigitsNeedle !== null && $normalizedDigitsNeedle !== $needle) {
                            $q->orWhere('phone', 'like', $normalizedDigitsNeedle)
                                ->orWhere('cnic', 'like', $normalizedDigitsNeedle);
                        }
                    })
                    ->orderByDesc('id')
                    ->limit(50)
                    ->get();
            }
        }

        $totalMatches = $admissions->count()
            + $registrations->count()
            + $coworkingRegistrations->count()
            + $leads->count();

        $matchedCategories = collect([
            'admissions' => $admissions->isNotEmpty(),
            'registrations' => $registrations->isNotEmpty(),
            'coworking' => $coworkingRegistrations->isNotEmpty(),
            'leads' => $leads->isNotEmpty(),
        ])->filter()->keys();

        return view('student-search.index', [
            'query' => $query,
            'admissions' => $admissions,
            'registrations' => $registrations,
            'coworkingRegistrations' => $coworkingRegistrations,
            'leads' => $leads,
            'matchedCategories' => $matchedCategories,
            'totalMatches' => $totalMatches,
        ]);
    }
}
