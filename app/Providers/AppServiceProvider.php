<?php

namespace App\Providers;

use App\Models\Admission;
use App\Models\Batch;
use App\Models\BatchTimetable;
use App\Models\Campus;
use App\Models\Certificate;
use App\Models\FinanceOtherCharge;
use App\Models\Lead;
use App\Models\LeadFollowup;
use App\Models\LeadTransfer;
use App\Models\Program;
use App\Models\Registration;
use App\Models\StudentAttendance;
use App\Models\User;
use App\Models\WebLead;
use App\Support\ResolvesLeadFollowupNotifications;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    use ResolvesLeadFollowupNotifications;

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
            $currentUser = auth()->user();
            $webLeadSourceLabels = WebLead::sourceLabels();
            $webLeadNotificationCounts = array_fill_keys(array_keys($webLeadSourceLabels), 0);
            $webLeadNotifications = [];
            $followupNotifications = collect();
            $followupNotificationCount = 0;
            $invoiceOverdueNotifications = collect();
            $invoiceOverdueNotificationCount = 0;
            $dashboardCampuses = collect();
            $activeDashboardCampus = null;
            $dashboardAllowsAllCampuses = (bool) ($currentUser?->isAdmin() ?? false);
            $activeDashboardCampusId = (int) session('dashboard_campus_id', 0);

            foreach (array_keys($webLeadSourceLabels) as $sourceType) {
                $webLeadNotifications[$sourceType] = collect();
            }

            try {
                if (Schema::hasTable('campuses')) {
                    if ($dashboardAllowsAllCampuses) {
                        $dashboardCampuses = Campus::query()
                            ->orderBy('name')
                            ->get(['id', 'code', 'name', 'campus_type']);

                        if ($activeDashboardCampusId > 0) {
                            $activeDashboardCampus = $dashboardCampuses->firstWhere('id', $activeDashboardCampusId);
                        }
                    } elseif ($currentUser?->campus_id) {
                        $dashboardCampuses = Campus::query()
                            ->whereKey($currentUser->campus_id)
                            ->get(['id', 'code', 'name', 'campus_type']);

                        $activeDashboardCampus = $dashboardCampuses->first();

                        if ($activeDashboardCampus) {
                            session(['dashboard_campus_id' => $activeDashboardCampus->id]);
                        } else {
                            session()->forget('dashboard_campus_id');
                        }
                    } else {
                        session()->forget('dashboard_campus_id');
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
                    if ($currentUser?->hasAnyPermission(['lead.followup.view']) ?? false) {
                        $followupNotifications = $followupNotifications->concat(
                            $this->latestDueLeadFollowupNotifications(
                                $currentUser,
                                fn (Builder $leadQuery, ?User $user) => $this->scopeLeadQueryToUserCampus($leadQuery, $user),
                                ['training', 'certification', 'study_abroad']
                            )
                        );
                    }

                    if ($currentUser?->hasAnyPermission(['lead.coworking.view']) ?? false) {
                        $followupNotifications = $followupNotifications->concat(
                            $this->latestDueLeadFollowupNotifications(
                                $currentUser,
                                fn (Builder $leadQuery, ?User $user) => $this->scopeLeadQueryToUserCampus($leadQuery, $user),
                                ['coworking']
                            )
                        );
                    }

                    $followupNotifications = $followupNotifications
                        ->sort(function ($left, $right) {
                            $leftTimestamp = $left->notification_due_at?->getTimestamp() ?? PHP_INT_MAX;
                            $rightTimestamp = $right->notification_due_at?->getTimestamp() ?? PHP_INT_MAX;

                            return $leftTimestamp <=> $rightTimestamp ?: ($right->id <=> $left->id);
                        })
                        ->values();

                    $followupNotificationCount = $followupNotifications->count();
                    $followupNotifications = $followupNotifications->take(5)->values();
                }

                if (($currentUser?->hasAnyPermission(['finance.receivable.view', 'finance.receivable.update', 'finance.receivable.create']) ?? false)
                    && Schema::hasTable('finance_other_charges')
                    && Schema::hasColumn('finance_other_charges', 'due_date')
                    && Schema::hasColumn('finance_other_charges', 'balance_amount')
                ) {
                    FinanceOtherCharge::syncLifecycleStatuses();

                    $overdueInvoices = $this->scopeQueryToUserCampus(
                        FinanceOtherCharge::query()->with(['campus:id,code,name']),
                        $currentUser
                    )
                        ->where('status', 'overdue');

                    $invoiceOverdueNotificationCount = (clone $overdueInvoices)->count();
                    $invoiceOverdueNotifications = (clone $overdueInvoices)
                        ->orderBy('due_date')
                        ->orderByDesc('id')
                        ->take(5)
                        ->get(['id', 'campus_id', 'invoice_number', 'student_name', 'due_date', 'balance_amount']);
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
                'invoiceOverdueNotifications' => $invoiceOverdueNotifications,
                'invoiceOverdueNotificationCount' => $invoiceOverdueNotificationCount,
                'dashboardCampuses' => $dashboardCampuses,
                'activeDashboardCampus' => $activeDashboardCampus,
                'dashboardAllowsAllCampuses' => $dashboardAllowsAllCampuses,
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
            'training_followup_schedule' => 0,
            'training_transfers' => 0,
            'training_all_leads' => 0,
            'training_today_leads' => 0,
            'training_web_leads' => 0,
            'certification_followups' => 0,
            'certification_followup_schedule' => 0,
            'certification_transfers' => 0,
            'certification_all_leads' => 0,
            'certification_today_leads' => 0,
            'study_abroad_followups' => 0,
            'study_abroad_followup_schedule' => 0,
            'study_abroad_transfers' => 0,
            'study_abroad_all_leads' => 0,
            'study_abroad_today_leads' => 0,
            'coworking_followups' => 0,
            'coworking_followup_schedule' => 0,
            'coworking_all_leads' => 0,
            'coworking_today_leads' => 0,
            'all_registrations' => 0,
            'all_admissions' => 0,
            'admission_today' => 0,
            'admission_current_month' => 0,
            'admission_current_year' => 0,
            'admission_pending' => 0,
            'admission_requested' => 0,
            'admission_approved' => 0,
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
                $sidebarCounts['training_all_leads'] = $this->scopeLeadQueryToUserCampus(
                    Lead::query()->training(),
                    $user
                )->count();

                $sidebarCounts['training_today_leads'] = $this->scopeLeadQueryToUserCampus(
                    Lead::query()->training(),
                    $user
                )
                    ->whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()])
                    ->count();

                $sidebarCounts['certification_all_leads'] = $this->scopeLeadQueryToUserCampus(
                    Lead::query()->certification(),
                    $user
                )->count();

                $sidebarCounts['certification_today_leads'] = $this->scopeLeadQueryToUserCampus(
                    Lead::query()->certification(),
                    $user
                )
                    ->whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()])
                    ->count();

                $sidebarCounts['study_abroad_all_leads'] = $this->scopeLeadQueryToUserCampus(
                    Lead::query()->studyAbroad(),
                    $user
                )->count();

                $sidebarCounts['study_abroad_today_leads'] = $this->scopeLeadQueryToUserCampus(
                    Lead::query()->studyAbroad(),
                    $user
                )
                    ->whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()])
                    ->count();
            }

            if ($can('lead.followup.view') && Schema::hasTable('lead_followups') && Schema::hasTable('leads')) {
                $sidebarCounts['training_followups'] = LeadFollowup::query()
                    ->whereHas('lead', fn (Builder $leadQuery) => $this->scopeLeadQueryToUserCampus($leadQuery->training(), $user))
                    ->distinct()
                    ->count('lead_id');

                $sidebarCounts['training_followup_schedule'] = $this->scopeLeadQueryToUserCampus(
                    Lead::query()->training(),
                    $user
                )
                    ->whereHas('latestFollowup', fn (Builder $followupQuery) => $followupQuery->whereNotNull('next_action_date'))
                    ->count();

                $sidebarCounts['certification_followups'] = LeadFollowup::query()
                    ->whereHas('lead', fn (Builder $leadQuery) => $this->scopeLeadQueryToUserCampus($leadQuery->certification(), $user))
                    ->distinct()
                    ->count('lead_id');

                $sidebarCounts['certification_followup_schedule'] = $this->scopeLeadQueryToUserCampus(
                    Lead::query()->certification(),
                    $user
                )
                    ->whereHas('latestFollowup', fn (Builder $followupQuery) => $followupQuery->whereNotNull('next_action_date'))
                    ->count();

                $sidebarCounts['study_abroad_followups'] = LeadFollowup::query()
                    ->whereHas('lead', fn (Builder $leadQuery) => $this->scopeLeadQueryToUserCampus($leadQuery->studyAbroad(), $user))
                    ->distinct()
                    ->count('lead_id');

                $sidebarCounts['study_abroad_followup_schedule'] = $this->scopeLeadQueryToUserCampus(
                    Lead::query()->studyAbroad(),
                    $user
                )
                    ->whereHas('latestFollowup', fn (Builder $followupQuery) => $followupQuery->whereNotNull('next_action_date'))
                    ->count();
            }

            if ($can('lead.coworking.view') && Schema::hasTable('lead_followups') && Schema::hasTable('leads')) {
                $sidebarCounts['coworking_followups'] = LeadFollowup::query()
                    ->whereHas('lead', fn (Builder $leadQuery) => $this->scopeLeadQueryToUserCampus($leadQuery->coworking(), $user))
                    ->distinct()
                    ->count('lead_id');

                $sidebarCounts['coworking_followup_schedule'] = $this->scopeLeadQueryToUserCampus(
                    Lead::query()->coworking(),
                    $user
                )
                    ->whereHas('latestFollowup', fn (Builder $followupQuery) => $followupQuery->whereNotNull('next_action_date'))
                    ->count();

                $sidebarCounts['coworking_all_leads'] = $this->scopeLeadQueryToUserCampus(
                    Lead::query()->coworking(),
                    $user
                )->count();

                $sidebarCounts['coworking_today_leads'] = $this->scopeLeadQueryToUserCampus(
                    Lead::query()->coworking(),
                    $user
                )
                    ->whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()])
                    ->count();
            }

            if (($can('lead.view') || $can('lead.transfer.approve')) && Schema::hasTable('lead_transfers') && Schema::hasTable('leads')) {
                $sidebarCounts['training_transfers'] = LeadTransfer::query()
                    ->whereHas('lead', fn (Builder $leadQuery) => $this->scopeLeadQueryToUserCampus($leadQuery->training(), $user))
                    ->count();

                $sidebarCounts['certification_transfers'] = LeadTransfer::query()
                    ->whereHas('lead', fn (Builder $leadQuery) => $this->scopeLeadQueryToUserCampus($leadQuery->certification(), $user))
                    ->count();

                $sidebarCounts['study_abroad_transfers'] = LeadTransfer::query()
                    ->whereHas('lead', fn (Builder $leadQuery) => $this->scopeLeadQueryToUserCampus($leadQuery->studyAbroad(), $user))
                    ->count();
            }

            if ($can('registration.view') && Schema::hasTable('registrations')) {
                $sidebarCounts['all_registrations'] = $this->scopeQueryToUserCampus(
                    Registration::query(),
                    $user
                )->count();
            }

            if (($can('admission.view') || $can('student.view')) && Schema::hasTable('admissions')) {
                $hasApprovalStatus = Schema::hasColumn('admissions', 'approval_status');
                $admissionBaseQuery = $this->scopeQueryToUserCampus(Admission::query(), $user);

                if ($can('admission.view') && $hasApprovalStatus) {
                    $approvalCounts = (clone $admissionBaseQuery)
                        ->selectRaw('approval_status, COUNT(*) as aggregate')
                        ->groupBy('approval_status')
                        ->pluck('aggregate', 'approval_status');

                    $sidebarCounts['admission_pending'] = (int) ($approvalCounts[Admission::APPROVAL_STATUS_PENDING] ?? 0);
                    $sidebarCounts['admission_requested'] = (int) ($approvalCounts[Admission::APPROVAL_STATUS_REQUESTED] ?? 0);
                    $sidebarCounts['admission_approved'] = (int) ($approvalCounts[Admission::APPROVAL_STATUS_APPROVED] ?? 0);
                }

                if ($can('admission.view')) {
                    $sidebarCounts['admission_today'] = (int) (clone $admissionBaseQuery)
                        ->where(function ($query) use ($todayDate) {
                            $query
                                ->whereDate('admission_date', $todayDate)
                                ->orWhere(function ($nested) use ($todayDate) {
                                    $nested
                                        ->whereNull('admission_date')
                                        ->whereDate('created_at', $todayDate);
                                });
                        })
                        ->count();

                    $sidebarCounts['admission_current_month'] = (int) (clone $admissionBaseQuery)
                        ->where(function ($query) use ($today) {
                            $query
                                ->where(function ($nested) use ($today) {
                                    $nested
                                        ->whereNotNull('admission_date')
                                        ->whereYear('admission_date', $today->year)
                                        ->whereMonth('admission_date', $today->month);
                                })
                                ->orWhere(function ($nested) use ($today) {
                                    $nested
                                        ->whereNull('admission_date')
                                        ->whereYear('created_at', $today->year)
                                        ->whereMonth('created_at', $today->month);
                                });
                        })
                        ->count();

                    $sidebarCounts['admission_current_year'] = (int) (clone $admissionBaseQuery)
                        ->where(function ($query) use ($today) {
                            $query
                                ->whereYear('admission_date', $today->year)
                                ->orWhere(function ($nested) use ($today) {
                                    $nested
                                        ->whereNull('admission_date')
                                        ->whereYear('created_at', $today->year);
                                });
                        })
                        ->count();
                }

                $studentAdmissionQuery = $hasApprovalStatus
                    ? (clone $admissionBaseQuery)->approved()
                    : clone $admissionBaseQuery;

                $admissionSummary = $studentAdmissionQuery
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
                    $sidebarCounts['all_admissions'] = $hasApprovalStatus
                        ? (int) ((clone $admissionBaseQuery)->count())
                        : (int) ($admissionSummary?->total ?? 0);
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
                $sidebarCounts['student_attendance'] = $this->scopeQueryToUserCampus(
                    StudentAttendance::query(),
                    $user
                )
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

    private function scopeLeadQueryToUserCampus(Builder $query, ?User $user): Builder
    {
        $campusScopeId = $this->userCampusScopeId($user);

        return $query->when(
            $campusScopeId,
            fn (Builder $builder, int $campusId) => $builder->where('campus_id', $campusId)
        );
    }

    private function scopeQueryToUserCampus(Builder $query, ?User $user, string $column = 'campus_id'): Builder
    {
        $campusScopeId = $this->userCampusScopeId($user);

        return $query->when(
            $campusScopeId,
            fn (Builder $builder, int $campusId) => $builder->where($column, $campusId)
        );
    }

    private function userCampusScopeId(?User $user): ?int
    {
        if (! $user || $user->isAdmin()) {
            return null;
        }

        $campusId = (int) ($user->campus_id ?? 0);

        return $campusId > 0 ? $campusId : null;
    }
}
