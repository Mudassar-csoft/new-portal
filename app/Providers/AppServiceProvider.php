<?php

namespace App\Providers;

use App\Models\Admission;
use App\Models\Batch;
use App\Models\BatchTimetable;
use App\Models\Campus;
use App\Models\Certificate;
use App\Models\Lead;
use App\Models\LeadFollowup;
use App\Models\LeadTransfer;
use App\Models\Program;
use App\Models\Registration;
use App\Models\StudentAttendance;
use App\Models\User;
use App\Models\WebLead;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.nav', function ($view): void {
            $view->with('sidebarCounts', $this->resolveSidebarCounts(auth()->user()));
        });

        View::composer('layouts.header', function ($view): void {
            $webLeadSourceLabels = WebLead::sourceLabels();
            $webLeadNotificationCounts = array_fill_keys(array_keys($webLeadSourceLabels), 0);
            $webLeadNotifications = [];
            $followupNotifications = collect();
            $followupNotificationCount = 0;
            $dashboardCampuses = collect();
            $activeDashboardCampus = null;
            $activeDashboardCampusId = (int) session('dashboard_campus_id', 0);

            foreach (array_keys($webLeadSourceLabels) as $sourceType) {
                $webLeadNotifications[$sourceType] = collect();
            }

            try {
                if (Schema::hasTable('campuses')) {
                    $dashboardCampuses = Campus::query()
                        ->orderBy('name')
                        ->get(['id', 'code', 'name', 'campus_type']);

                    if ($activeDashboardCampusId > 0) {
                        $activeDashboardCampus = $dashboardCampuses->firstWhere('id', $activeDashboardCampusId);
                    }
                }

                if (Schema::hasTable('web_leads')) {
                    $webLeadNotificationCounts = WebLead::query()
                        ->pending()
                        ->selectRaw('source_type, COUNT(*) as aggregate')
                        ->groupBy('source_type')
                        ->pluck('aggregate', 'source_type')
                        ->map(fn ($count) => (int) $count)
                        ->union($webLeadNotificationCounts)
                        ->all();

                    foreach (array_keys($webLeadSourceLabels) as $sourceType) {
                        $webLeadNotifications[$sourceType] = WebLead::query()
                            ->pending()
                            ->ofSource($sourceType)
                            ->latest('submitted_at')
                            ->latest('id')
                            ->take(5)
                            ->get();
                    }
                }

                if (Schema::hasTable('lead_followups') && Schema::hasTable('leads')) {
                    $followupNotifications = LeadFollowup::with(['lead'])
                        ->whereNotNull('next_action_date')
                        ->whereHas('lead', fn (Builder $leadQuery) => $leadQuery->training())
                        ->orderBy('next_action_date')
                        ->latest('id')
                        ->get()
                        ->unique('lead_id')
                        ->values();

                    $followupNotificationCount = $followupNotifications->count();
                    $followupNotifications = $followupNotifications->take(5)->values();
                }
            } catch (Throwable) {
                // Keep empty notification data when the table is unavailable.
            }

            $view->with([
                'webLeadSourceLabels' => $webLeadSourceLabels,
                'webLeadNotificationCounts' => $webLeadNotificationCounts,
                'webLeadNotifications' => $webLeadNotifications,
                'webLeadNotificationTotal' => array_sum($webLeadNotificationCounts),
                'followupNotifications' => $followupNotifications,
                'followupNotificationCount' => $followupNotificationCount,
                'dashboardCampuses' => $dashboardCampuses,
                'activeDashboardCampus' => $activeDashboardCampus,
            ]);
        });
    }

    /**
     * @return array<string, int>
     */
    private function sidebarCountDefaults(): array
    {
        return [
            'training_followups' => 0,
            'training_transfers' => 0,
            'training_all_leads' => 0,
            'training_web_leads' => 0,
            'coworking_followups' => 0,
            'all_registrations' => 0,
            'all_admissions' => 0,
            'student_attendance' => 0,
            'student_active' => 0,
            'student_frozen' => 0,
            'student_concluded' => 0,
            'student_incomplete' => 0,
            'student_suspended' => 0,
            'student_admission_cancelled' => 0,
            'student_dropped' => 0,
            'student_all' => 0,
            'student_alumni' => 0,
            'batch_create' => 0,
            'batch_upcoming' => 0,
            'batch_recently_started' => 0,
            'batch_in_progress' => 0,
            'batch_recently_ended' => 0,
            'batch_completed' => 0,
            'batch_all' => 0,
            'batch_timetable' => 0,
            'program_create' => 0,
            'program_ongoing' => 0,
            'program_suspended' => 0,
            'program_all' => 0,
            'campus_create' => 0,
            'campus_all' => 0,
            'campus_company' => 0,
            'campus_franchise' => 0,
            'campus_suspended_company' => 0,
            'campus_suspended_franchise' => 0,
            'certificate_requested' => 0,
            'certificate_approved' => 0,
            'certificate_printing' => 0,
            'certificate_ready' => 0,
            'certificate_delivered' => 0,
            'certificate_all' => 0,
        ];
    }

    /**
     * @return array<string, int>
     */
    private function resolveSidebarCounts(?User $user): array
    {
        $sidebarCounts = $this->sidebarCountDefaults();

        if (!$user) {
            return $sidebarCounts;
        }

        $can = fn (string ...$permissions): bool => $user->hasAnyPermission($permissions);

        try {
            $today = now()->startOfDay();
            $todayDate = $today->toDateString();
            $recentWindowDate = $today->copy()->subDays(30)->toDateString();

            if (($can('lead.followup.view') || $can('lead.view') || $can('lead.transfer.approve')) && Schema::hasTable('leads')) {
                $sidebarCounts['training_all_leads'] = Lead::query()
                    ->training()
                    ->count();
            }

            if ($can('lead.followup.view') && Schema::hasTable('lead_followups') && Schema::hasTable('leads')) {
                $sidebarCounts['training_followups'] = LeadFollowup::query()
                    ->whereHas('lead', fn (Builder $leadQuery) => $leadQuery->training())
                    ->distinct()
                    ->count('lead_id');

                $sidebarCounts['coworking_followups'] = LeadFollowup::query()
                    ->whereHas('lead', fn (Builder $leadQuery) => $leadQuery->coworking())
                    ->distinct()
                    ->count('lead_id');
            }

            if (($can('lead.view') || $can('lead.transfer.approve')) && Schema::hasTable('lead_transfers') && Schema::hasTable('leads')) {
                $sidebarCounts['training_transfers'] = LeadTransfer::query()
                    ->whereHas('lead', fn (Builder $leadQuery) => $leadQuery->training())
                    ->count();
            }

            if ($can('registration.view') && Schema::hasTable('registrations')) {
                $sidebarCounts['all_registrations'] = Registration::query()->count();
            }

            if (($can('admission.view') || $can('student.view')) && Schema::hasTable('admissions')) {
                $admissionSummary = Admission::query()
                    ->selectRaw('COUNT(*) as total')
                    ->selectRaw("SUM(CASE WHEN student_status = 'enrolled' THEN 1 ELSE 0 END) as student_active")
                    ->selectRaw("SUM(CASE WHEN student_status = 'frozen' THEN 1 ELSE 0 END) as student_frozen")
                    ->selectRaw("SUM(CASE WHEN student_status = 'concluded' THEN 1 ELSE 0 END) as student_concluded")
                    ->selectRaw("SUM(CASE WHEN student_status = 'incomplete' THEN 1 ELSE 0 END) as student_incomplete")
                    ->selectRaw("SUM(CASE WHEN student_status = 'suspended' THEN 1 ELSE 0 END) as student_suspended")
                    ->selectRaw("SUM(CASE WHEN student_status = 'admission_cancelled' THEN 1 ELSE 0 END) as student_admission_cancelled")
                    ->selectRaw("SUM(CASE WHEN student_status = 'dropped' THEN 1 ELSE 0 END) as student_dropped")
                    ->selectRaw('SUM(CASE WHEN certificate_delivered_at IS NOT NULL THEN 1 ELSE 0 END) as student_alumni')
                    ->first();

                if ($can('admission.view')) {
                    $sidebarCounts['all_admissions'] = (int) ($admissionSummary?->total ?? 0);
                }

                if ($can('student.view')) {
                    $sidebarCounts['student_active'] = (int) ($admissionSummary?->student_active ?? 0);
                    $sidebarCounts['student_frozen'] = (int) ($admissionSummary?->student_frozen ?? 0);
                    $sidebarCounts['student_concluded'] = (int) ($admissionSummary?->student_concluded ?? 0);
                    $sidebarCounts['student_incomplete'] = (int) ($admissionSummary?->student_incomplete ?? 0);
                    $sidebarCounts['student_suspended'] = (int) ($admissionSummary?->student_suspended ?? 0);
                    $sidebarCounts['student_admission_cancelled'] = (int) ($admissionSummary?->student_admission_cancelled ?? 0);
                    $sidebarCounts['student_dropped'] = (int) ($admissionSummary?->student_dropped ?? 0);
                    $sidebarCounts['student_all'] = (int) ($admissionSummary?->total ?? 0);
                    $sidebarCounts['student_alumni'] = (int) ($admissionSummary?->student_alumni ?? 0);
                }
            }

            if ($can('student.view') && Schema::hasTable('student_attendances')) {
                $sidebarCounts['student_attendance'] = StudentAttendance::query()
                    ->whereDate('attendance_date', $today)
                    ->select('admission_id')
                    ->distinct()
                    ->count('admission_id');
            }

            if (($can('batch.create') || $can('batch.view')) && Schema::hasTable('batches')) {
                $batchSummary = Batch::query()
                    ->selectRaw('COUNT(*) as total')
                    ->selectRaw("SUM(CASE WHEN start_date > '{$todayDate}' THEN 1 ELSE 0 END) as batch_upcoming")
                    ->selectRaw("SUM(CASE WHEN start_date >= '{$recentWindowDate}' AND start_date <= '{$todayDate}' THEN 1 ELSE 0 END) as batch_recently_started")
                    ->selectRaw("SUM(CASE WHEN start_date <= '{$todayDate}' AND (end_date IS NULL OR end_date >= '{$todayDate}') THEN 1 ELSE 0 END) as batch_in_progress")
                    ->selectRaw("SUM(CASE WHEN end_date IS NOT NULL AND end_date >= '{$recentWindowDate}' AND end_date < '{$todayDate}' THEN 1 ELSE 0 END) as batch_recently_ended")
                    ->selectRaw("SUM(CASE WHEN end_date IS NOT NULL AND end_date < '{$todayDate}' THEN 1 ELSE 0 END) as batch_completed")
                    ->first();

                $sidebarCounts['batch_all'] = (int) ($batchSummary?->total ?? 0);
                $sidebarCounts['batch_create'] = (int) ($batchSummary?->total ?? 0);
                $sidebarCounts['batch_upcoming'] = (int) ($batchSummary?->batch_upcoming ?? 0);
                $sidebarCounts['batch_recently_started'] = (int) ($batchSummary?->batch_recently_started ?? 0);
                $sidebarCounts['batch_in_progress'] = (int) ($batchSummary?->batch_in_progress ?? 0);
                $sidebarCounts['batch_recently_ended'] = (int) ($batchSummary?->batch_recently_ended ?? 0);
                $sidebarCounts['batch_completed'] = (int) ($batchSummary?->batch_completed ?? 0);
            }

            if ($can('batch-timetable.view') && Schema::hasTable('batch_timetables')) {
                $sidebarCounts['batch_timetable'] = BatchTimetable::query()->count();
            }

            if (($can('program.create') || $can('program.view')) && Schema::hasTable('programs')) {
                $programSummary = Program::query()
                    ->selectRaw('COUNT(*) as total')
                    ->selectRaw("SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as program_ongoing")
                    ->selectRaw("SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as program_suspended")
                    ->first();

                $sidebarCounts['program_all'] = (int) ($programSummary?->total ?? 0);
                $sidebarCounts['program_create'] = (int) ($programSummary?->total ?? 0);
                $sidebarCounts['program_ongoing'] = (int) ($programSummary?->program_ongoing ?? 0);
                $sidebarCounts['program_suspended'] = (int) ($programSummary?->program_suspended ?? 0);
            }

            if (($can('campus.create') || $can('campus.view')) && Schema::hasTable('campuses')) {
                $campusSummary = Campus::query()
                    ->selectRaw('COUNT(*) as total')
                    ->selectRaw("SUM(CASE WHEN campus_type = 'company' THEN 1 ELSE 0 END) as campus_company")
                    ->selectRaw("SUM(CASE WHEN campus_type = 'franchise' THEN 1 ELSE 0 END) as campus_franchise")
                    ->selectRaw("SUM(CASE WHEN campus_type = 'company' AND status = 'inactive' THEN 1 ELSE 0 END) as campus_suspended_company")
                    ->selectRaw("SUM(CASE WHEN campus_type = 'franchise' AND status = 'inactive' THEN 1 ELSE 0 END) as campus_suspended_franchise")
                    ->first();

                $sidebarCounts['campus_all'] = (int) ($campusSummary?->total ?? 0);
                $sidebarCounts['campus_create'] = (int) ($campusSummary?->total ?? 0);
                $sidebarCounts['campus_company'] = (int) ($campusSummary?->campus_company ?? 0);
                $sidebarCounts['campus_franchise'] = (int) ($campusSummary?->campus_franchise ?? 0);
                $sidebarCounts['campus_suspended_company'] = (int) ($campusSummary?->campus_suspended_company ?? 0);
                $sidebarCounts['campus_suspended_franchise'] = (int) ($campusSummary?->campus_suspended_franchise ?? 0);
            }

            if ($can('certificate.view') && Schema::hasTable('certificates')) {
                $certificateCounts = Certificate::query()
                    ->selectRaw('status, COUNT(*) as aggregate')
                    ->groupBy('status')
                    ->pluck('aggregate', 'status');

                $sidebarCounts['certificate_requested'] = (int) ($certificateCounts[Certificate::STATUS_REQUESTED] ?? 0);
                $sidebarCounts['certificate_approved'] = (int) ($certificateCounts[Certificate::STATUS_APPROVED] ?? 0);
                $sidebarCounts['certificate_printing'] = (int) ($certificateCounts[Certificate::STATUS_PRINTING] ?? 0);
                $sidebarCounts['certificate_ready'] = (int) ($certificateCounts[Certificate::STATUS_READY] ?? 0);
                $sidebarCounts['certificate_delivered'] = (int) ($certificateCounts[Certificate::STATUS_DELIVERED] ?? 0);
                $sidebarCounts['certificate_all'] = (int) $certificateCounts->sum();
            }
        } catch (Throwable) {
            // Keep zero counts when tables are unavailable.
        }

        return $sidebarCounts;
    }
}
