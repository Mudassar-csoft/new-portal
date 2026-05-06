<?php

namespace App\Providers;

use App\Models\Admission;
use App\Models\Batch;
use App\Models\BatchTimetable;
use App\Models\Campus;
use App\Models\Lead;
use App\Models\LeadFollowup;
use App\Models\LeadTransfer;
use App\Models\Program;
use App\Models\Registration;
use App\Models\StudentAttendance;
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
            $sidebarCounts = [
                'training_followups' => 0,
                'training_transfers' => 0,
                'training_all_leads' => 0,
                'training_web_leads' => 0,
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
                'campus_company' => 0,
                'campus_franchise' => 0,
                'campus_suspended_company' => 0,
                'campus_suspended_franchise' => 0,
            ];

            try {
                $today = now()->startOfDay();
                $recentWindow = $today->copy()->subDays(30);

                if (Schema::hasTable('leads')) {
                    $sidebarCounts['training_all_leads'] = Lead::query()
                        ->training()
                        ->count();
                }

                if (Schema::hasTable('lead_followups')) {
                    $sidebarCounts['training_followups'] = LeadFollowup::query()
                        ->whereHas('lead', fn (Builder $leadQuery) => $leadQuery->training())
                        ->distinct()
                        ->count('lead_id');
                }

                if (Schema::hasTable('lead_transfers')) {
                    $sidebarCounts['training_transfers'] = LeadTransfer::query()
                        ->whereHas('lead', fn (Builder $leadQuery) => $leadQuery->training())
                        ->count();
                }

                if (Schema::hasTable('web_leads')) {
                    $sidebarCounts['training_web_leads'] = WebLead::query()
                        ->pending()
                        ->count();
                }

                if (Schema::hasTable('registrations')) {
                    $sidebarCounts['all_registrations'] = Registration::query()->count();
                }

                if (Schema::hasTable('admissions')) {
                    $sidebarCounts['all_admissions'] = Admission::query()->count();
                    $sidebarCounts['student_active'] = Admission::query()->where('student_status', 'enrolled')->count();
                    $sidebarCounts['student_frozen'] = Admission::query()->where('student_status', 'frozen')->count();
                    $sidebarCounts['student_concluded'] = Admission::query()->where('student_status', 'concluded')->count();
                    $sidebarCounts['student_incomplete'] = Admission::query()->where('student_status', 'incomplete')->count();
                    $sidebarCounts['student_suspended'] = Admission::query()->where('student_status', 'suspended')->count();
                    $sidebarCounts['student_admission_cancelled'] = Admission::query()->where('student_status', 'admission_cancelled')->count();
                    $sidebarCounts['student_dropped'] = Admission::query()->where('student_status', 'dropped')->count();
                    $sidebarCounts['student_all'] = Admission::query()->count();
                    $sidebarCounts['student_alumni'] = Admission::query()
                        ->whereNotNull('certificate_delivered_at')
                        ->count();
                }

                if (Schema::hasTable('student_attendances')) {
                    $sidebarCounts['student_attendance'] = StudentAttendance::query()
                        ->whereDate('attendance_date', $today)
                        ->select('admission_id')
                        ->distinct()
                        ->count('admission_id');
                }

                if (Schema::hasTable('batches')) {
                    $sidebarCounts['batch_all'] = Batch::query()->count();
                    $sidebarCounts['batch_create'] = $sidebarCounts['batch_all'];
                    $sidebarCounts['batch_upcoming'] = Batch::query()
                        ->whereDate('start_date', '>', $today)
                        ->count();
                    $sidebarCounts['batch_recently_started'] = Batch::query()
                        ->whereDate('start_date', '>=', $recentWindow)
                        ->whereDate('start_date', '<=', $today)
                        ->count();
                    $sidebarCounts['batch_in_progress'] = Batch::query()
                        ->whereDate('start_date', '<=', $today)
                        ->where(function (Builder $builder) use ($today) {
                            $builder
                                ->whereNull('end_date')
                                ->orWhereDate('end_date', '>=', $today);
                        })
                        ->count();
                    $sidebarCounts['batch_recently_ended'] = Batch::query()
                        ->whereNotNull('end_date')
                        ->whereDate('end_date', '>=', $recentWindow)
                        ->whereDate('end_date', '<', $today)
                        ->count();
                    $sidebarCounts['batch_completed'] = Batch::query()
                        ->whereNotNull('end_date')
                        ->whereDate('end_date', '<', $today)
                        ->count();
                }

                if (Schema::hasTable('batch_timetables')) {
                    $sidebarCounts['batch_timetable'] = BatchTimetable::query()->count();
                }

                if (Schema::hasTable('programs')) {
                    $sidebarCounts['program_all'] = Program::query()->count();
                    $sidebarCounts['program_create'] = $sidebarCounts['program_all'];
                    $sidebarCounts['program_ongoing'] = Program::query()->where('status', 'active')->count();
                    $sidebarCounts['program_suspended'] = Program::query()->where('status', 'inactive')->count();
                }

                if (Schema::hasTable('campuses')) {
                    $sidebarCounts['campus_create'] = Campus::query()->count();
                    $sidebarCounts['campus_company'] = Campus::query()->where('campus_type', 'company')->count();
                    $sidebarCounts['campus_franchise'] = Campus::query()->where('campus_type', 'franchise')->count();
                    $sidebarCounts['campus_suspended_company'] = Campus::query()
                        ->where('campus_type', 'company')
                        ->where('status', 'inactive')
                        ->count();
                    $sidebarCounts['campus_suspended_franchise'] = Campus::query()
                        ->where('campus_type', 'franchise')
                        ->where('status', 'inactive')
                        ->count();
                }
            } catch (Throwable) {
                // Keep zero counts when tables are unavailable.
            }

            $view->with('sidebarCounts', $sidebarCounts);
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
}
