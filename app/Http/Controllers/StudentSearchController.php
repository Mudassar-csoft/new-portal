<?php

namespace App\Http\Controllers;

use App\Models\Admission;
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

        $admissions = collect();
        $registrations = collect();
        $leads = collect();

        if ($query !== '') {
            $needle = '%' . $query . '%';

            $admissions = $this->scopeQueryToUserCampus(Admission::query(), $request->user())
                ->with(['program:id,code,title,name', 'campus:id,code,name', 'batch:id,code,name'])
                ->where(function ($q) use ($needle) {
                    $q->where('student_name', 'like', $needle)
                        ->orWhere('phone', 'like', $needle)
                        ->orWhere('roll_number', 'like', $needle)
                        ->orWhere('registration_number', 'like', $needle)
                        ->orWhere('cnic', 'like', $needle)
                        ->orWhere('email', 'like', $needle)
                        ->orWhere('guardian_phone', 'like', $needle);
                })
                ->orderByDesc('admission_date')
                ->orderByDesc('id')
                ->limit(50)
                ->get();

            $registrations = $this->scopeQueryToUserCampus(Registration::query(), $request->user())
                ->with(['program:id,code,title,name', 'campus:id,code,name'])
                ->where(function ($q) use ($needle) {
                    $q->where('student_name', 'like', $needle)
                        ->orWhere('phone', 'like', $needle)
                        ->orWhere('registration_number', 'like', $needle)
                        ->orWhere('receipt_number', 'like', $needle);
                })
                ->orderByDesc('id')
                ->limit(50)
                ->get();

            $leads = $this->scopeQueryToUserCampus(Lead::query(), $request->user())
                ->with(['program:id,code,title,name', 'campus:id,code,name'])
                ->where(function ($q) use ($needle) {
                    $q->where('name', 'like', $needle)
                        ->orWhere('phone', 'like', $needle)
                        ->orWhere('email', 'like', $needle);
                })
                ->orderByDesc('id')
                ->limit(50)
                ->get();
        }

        return view('student-search.index', [
            'query' => $query,
            'admissions' => $admissions,
            'registrations' => $registrations,
            'leads' => $leads,
            'totalMatches' => $admissions->count() + $registrations->count() + $leads->count(),
        ]);
    }
}
