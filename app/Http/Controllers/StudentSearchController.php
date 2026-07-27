<?php

namespace App\Http\Controllers;

use App\Models\Admission;
use App\Models\CoworkingRegistration;
use App\Models\Lead;
use App\Models\Registration;
use App\Support\ResolvesCampusScope;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentSearchController extends Controller
{
    use ResolvesCampusScope;

    public function index(Request $request): View
    {
        $query = trim((string) $request->query('q', ''));
        $normalizedDigits = preg_replace('/\D+/', '', $query);

        $admissions = collect();
        $registrations = collect();
        $coworkingRegistrations = collect();
        $leads = collect();
        $resultType = null;

        if ($query !== '') {
            $needle = '%' . $query . '%';
            $normalizedDigitsNeedle = $normalizedDigits !== '' ? '%' . $normalizedDigits . '%' : null;

            $admissions = $this->scopeQueryToUserCampus(Admission::query(), $request->user())
                ->with(['program:id,code,title,name', 'campus:id,code,name', 'batch:id,code,name'])
                ->where(function ($q) use ($needle, $normalizedDigitsNeedle) {
                    $q->where('student_name', 'like', $needle)
                        ->orWhere('phone', 'like', $needle)
                        ->orWhere('roll_number', 'like', $needle)
                        ->orWhere('registration_number', 'like', $needle)
                        ->orWhere('cnic', 'like', $needle)
                        ->orWhere('email', 'like', $needle)
                        ->orWhere('guardian_phone', 'like', $needle);

                    if ($normalizedDigitsNeedle !== null && $normalizedDigitsNeedle !== $needle) {
                        $q->orWhere('cnic', 'like', $normalizedDigitsNeedle);
                    }
                })
                ->orderByDesc('admission_date')
                ->orderByDesc('id')
                ->limit(50)
                ->get();

            if ($admissions->isNotEmpty()) {
                $resultType = 'admissions';
            } else {
                $registrations = $this->scopeQueryToUserCampus(Registration::query(), $request->user())
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

                if ($registrations->isNotEmpty()) {
                    $resultType = 'registrations';
                } else {
                    $coworkingRegistrations = $this->scopeQueryToUserCampus(CoworkingRegistration::query(), $request->user())
                        ->with(['campus:id,code,name'])
                        ->where(function ($q) use ($needle, $normalizedDigitsNeedle) {
                            $q->where('full_name', 'like', $needle)
                                ->orWhere('phone', 'like', $needle)
                                ->orWhere('guardian_phone', 'like', $needle)
                                ->orWhere('cnic', 'like', $needle)
                                ->orWhere('email', 'like', $needle)
                                ->orWhere('registration_number', 'like', $needle)
                                ->orWhere('receipt_number', 'like', $needle);

                            if ($normalizedDigitsNeedle !== null && $normalizedDigitsNeedle !== $needle) {
                                $q->orWhere('phone', 'like', $normalizedDigitsNeedle)
                                    ->orWhere('guardian_phone', 'like', $normalizedDigitsNeedle)
                                    ->orWhere('cnic', 'like', $normalizedDigitsNeedle);
                            }
                        })
                        ->orderByDesc('id')
                        ->limit(50)
                        ->get();

                    if ($coworkingRegistrations->isNotEmpty()) {
                        $resultType = 'coworking';
                    } else {
                        $leads = $this->scopeQueryToUserCampus(Lead::query(), $request->user())
                            ->with(['program:id,code,title,name', 'campus:id,code,name'])
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

                        if ($leads->isNotEmpty()) {
                            $resultType = 'leads';
                        }
                    }
                }
            }
        }

        $totalMatches = match ($resultType) {
            'admissions' => $admissions->count(),
            'registrations' => $registrations->count(),
            'coworking' => $coworkingRegistrations->count(),
            'leads' => $leads->count(),
            default => 0,
        };

        return view('student-search.index', [
            'query' => $query,
            'admissions' => $admissions,
            'registrations' => $registrations,
            'coworkingRegistrations' => $coworkingRegistrations,
            'leads' => $leads,
            'resultType' => $resultType,
            'totalMatches' => $totalMatches,
        ]);
    }
}
