<?php

namespace App\Http\Controllers;

use App\Models\Admission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class StudentRecordController extends Controller
{
    private const STATUS_OPTIONS = [
        'enrolled' => 'Enrolled',
        'concluded' => 'Concluded',
        'frozen' => 'Frozen',
        'incomplete' => 'Incomplete',
        'suspended' => 'Suspended',
        'admission_cancelled' => 'Admission Cancelled',
        'dropped' => 'Dropped',
    ];

    public function index(Request $request, ?string $scope = null)
    {
        $scope = $scope ?: 'all_students';
        $config = $this->resolveScope($scope);

        if ($request->ajax()) {
            $query = Admission::query()
                ->with([
                    'campus:id,code,name',
                    'program:id,code,title,name',
                    'batch:id,code,name',
                    'registration:id,registration_number',
                ])
                ->select('admissions.*');

            $this->applyScope($query, $config['scope']);

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->editColumn('student_name', fn (Admission $admission) => e($admission->student_name ?: 'N/A'))
                ->editColumn('roll_number', fn (Admission $admission) => e($admission->roll_number ?: 'N/A'))
                ->editColumn('registration_number', fn (Admission $admission) => e($admission->registration_number ?: optional($admission->registration)->registration_number ?: 'N/A'))
                ->editColumn('admission_date', fn (Admission $admission) => optional($admission->admission_date)->format('d-M-Y') ?? 'N/A')
                ->addColumn('campus_code', fn (Admission $admission) => e(optional($admission->campus)->code ?? optional($admission->campus)->name ?? 'N/A'))
                ->addColumn('program_name', fn (Admission $admission) => e(optional($admission->program)->title ?? optional($admission->program)->name ?? 'N/A'))
                ->addColumn('batch_name', fn (Admission $admission) => e(optional($admission->batch)->code ?? optional($admission->batch)->name ?? 'N/A'))
                ->addColumn('status_badge', function (Admission $admission) {
                    $label = self::STATUS_OPTIONS[$admission->student_status] ?? ucfirst(str_replace('_', ' ', (string) $admission->student_status));
                    $class = match ($admission->student_status) {
                        'enrolled' => 'label-success',
                        'concluded' => 'label-primary',
                        'frozen' => 'label-warning',
                        'incomplete' => 'label-default',
                        'suspended' => 'label-info',
                        'admission_cancelled' => 'label-danger',
                        'dropped' => 'label-danger',
                        default => 'label-default',
                    };

                    return '<span class="label ' . $class . '">' . e($label) . '</span>';
                })
                ->addColumn('certificate_status', function (Admission $admission) {
                    if (!$admission->certificate_delivered_at) {
                        return '<span class="label label-default">Pending</span>';
                    }

                    return '<span class="label label-success">Delivered</span><div class="text-muted small mt-1">'
                        . e(optional($admission->certificate_delivered_at)->format('d-M-Y'))
                        . '</div>';
                })
                ->addColumn('actions', fn (Admission $admission) => view('student.partials.action', [
                    'admission' => $admission,
                    'statusOptions' => self::STATUS_OPTIONS,
                ])->render())
                ->filterColumn('campus_code', function ($query, $keyword) {
                    $query->whereHas('campus', function ($campusQuery) use ($keyword) {
                        $campusQuery
                            ->where('code', 'like', "%{$keyword}%")
                            ->orWhere('name', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('program_name', function ($query, $keyword) {
                    $query->whereHas('program', function ($programQuery) use ($keyword) {
                        $programQuery
                            ->where('title', 'like', "%{$keyword}%")
                            ->orWhere('name', 'like', "%{$keyword}%")
                            ->orWhere('code', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('batch_name', function ($query, $keyword) {
                    $query->whereHas('batch', function ($batchQuery) use ($keyword) {
                        $batchQuery
                            ->where('code', 'like', "%{$keyword}%")
                            ->orWhere('name', 'like', "%{$keyword}%");
                    });
                })
                ->rawColumns(['status_badge', 'certificate_status', 'actions'])
                ->make(true);
        }

        return view('student.records.index', [
            'scope' => $config['scope'],
            'pageTitle' => $config['title'],
            'pageDescription' => $config['description'],
        ]);
    }

    public function updateStatus(Request $request, Admission $admission): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys(self::STATUS_OPTIONS))],
        ]);

        $admission->update([
            'student_status' => $validated['status'],
            'status_updated_at' => now(),
        ]);

        return back()->with('status', 'Student status updated.');
    }

    public function markCertificateDelivered(Request $request, Admission $admission): RedirectResponse
    {
        $validated = $request->validate([
            'certificate_delivery_notes' => ['nullable', 'string'],
        ]);

        $admission->update([
            'certificate_delivered_at' => now(),
            'certificate_delivered_by' => $request->user()?->id,
            'certificate_delivery_notes' => $validated['certificate_delivery_notes'] ?? $admission->certificate_delivery_notes,
        ]);

        return back()->with('status', 'Certificate marked as delivered.');
    }

    private function applyScope($query, string $scope): void
    {
        if ($scope === 'all_students') {
            return;
        }

        if ($scope === 'alumni') {
            $query->whereNotNull('certificate_delivered_at');
            return;
        }

        $status = $scope === 'active' ? 'enrolled' : $scope;
        $query->where('student_status', $status);
    }

    private function resolveScope(string $scope): array
    {
        return match ($scope) {
            'active' => [
                'scope' => 'active',
                'title' => 'Active Students',
                'description' => 'All students currently in enrolled status.',
            ],
            'concluded' => [
                'scope' => 'concluded',
                'title' => 'Concluded Students',
                'description' => 'Students whose academic status is concluded.',
            ],
            'frozen' => [
                'scope' => 'frozen',
                'title' => 'Frozen Students',
                'description' => 'Students currently marked as frozen.',
            ],
            'incomplete' => [
                'scope' => 'incomplete',
                'title' => 'Incomplete Students',
                'description' => 'Students with incomplete admission or academic processing.',
            ],
            'suspended' => [
                'scope' => 'suspended',
                'title' => 'Suspended Students',
                'description' => 'Students currently suspended.',
            ],
            'admission_cancelled' => [
                'scope' => 'admission_cancelled',
                'title' => 'Admission Cancelled',
                'description' => 'Students whose admissions have been cancelled.',
            ],
            'dropped' => [
                'scope' => 'dropped',
                'title' => 'Dropped Students',
                'description' => 'Students marked as dropped.',
            ],
            'alumni' => [
                'scope' => 'alumni',
                'title' => 'Alumni Students',
                'description' => 'Students whose certificates have been delivered.',
            ],
            default => [
                'scope' => 'all_students',
                'title' => 'All Students',
                'description' => 'All admitted students regardless of current status.',
            ],
        };
    }
}
