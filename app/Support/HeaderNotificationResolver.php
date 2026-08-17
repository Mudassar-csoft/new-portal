<?php

namespace App\Support;

use App\Models\FinanceOtherCharge;
use App\Models\User;
use App\Models\WebLead;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Throwable;

class HeaderNotificationResolver
{
    use ResolvesLeadFollowupNotifications;

    /**
     * @return array<string, mixed>
     */
    public function resolve(?User $user): array
    {
        $webLeadSourceLabels = WebLead::leadManagementSourceLabels();
        $payload = $this->defaults($webLeadSourceLabels);

        if (! $user) {
            return $payload;
        }

        if (($user->hasAnyPermission(['web-lead.view']) ?? false) && Schema::hasTable('web_leads')) {
            $payload['canViewWebLeadNotifications'] = true;

            try {
                $managedSourceTypes = array_keys($webLeadSourceLabels);
                $pendingWebLeads = WebLead::query()
                    ->pending()
                    ->whereIn('source_type', $managedSourceTypes);

                $sourceCounts = (clone $pendingWebLeads)
                    ->selectRaw('source_type, COUNT(*) as aggregate')
                    ->groupBy('source_type')
                    ->pluck('aggregate', 'source_type');

                $payload['webLeadNotificationCounts'] = array_replace(
                    $payload['webLeadNotificationCounts'],
                    collect($managedSourceTypes)->mapWithKeys(
                        fn (string $sourceType): array => [$sourceType => (int) ($sourceCounts[$sourceType] ?? 0)]
                    )->all()
                );

                $payload['webLeadNotifications'] = collect($managedSourceTypes)
                    ->mapWithKeys(function (string $sourceType) use ($pendingWebLeads): array {
                        return [
                            $sourceType => (clone $pendingWebLeads)
                                ->where('source_type', $sourceType)
                                ->latest('submitted_at')
                                ->latest('id')
                                ->limit(5)
                                ->get(),
                        ];
                    });
            } catch (Throwable) {
                $payload['webLeadNotifications'] = collect(array_keys($webLeadSourceLabels))
                    ->mapWithKeys(fn (string $sourceType): array => [$sourceType => collect()]);
                $payload['webLeadNotificationCounts'] = array_fill_keys(array_keys($webLeadSourceLabels), 0);
            }
        }

        if (
            ($user->hasAnyPermission(['lead.followup.view']) ?? false)
            && Schema::hasTable('lead_followups')
            && Schema::hasTable('leads')
        ) {
            $payload['canViewFollowupNotifications'] = true;

            try {
                $followupNotifications = $this->latestDueLeadFollowupNotifications(
                    $user,
                    fn (Builder $leadQuery, $scopedUser = null) => $this->scopeLeadQueryToUserCampus($leadQuery, $scopedUser),
                    ['training', 'certification', 'study_abroad']
                );

                $payload['followupNotificationCount'] = $followupNotifications->count();
                $payload['followupNotifications'] = $followupNotifications->take(5)->values();
            } catch (Throwable) {
                $payload['followupNotifications'] = collect();
                $payload['followupNotificationCount'] = 0;
            }
        }

        if ($user->hasAnyPermission(['finance.receivable.view', 'finance.receivable.create', 'finance.receivable.update']) ?? false) {
            try {
                if (FinanceOtherCharge::hasInvoiceSchema()) {
                    $payload['canViewInvoiceNotifications'] = true;

                    $overdueInvoicesQuery = $this->scopeQueryToUserCampus(
                        FinanceOtherCharge::query()->with(['campus:id,code,name']),
                        $user
                    )
                        ->where('balance_amount', '>', 0)
                        ->whereNotNull('due_date')
                        ->whereDate('due_date', '<', now()->toDateString());

                    $payload['invoiceOverdueNotificationCount'] = (int) (clone $overdueInvoicesQuery)->count();
                    $payload['invoiceOverdueNotifications'] = (clone $overdueInvoicesQuery)
                        ->orderBy('due_date')
                        ->orderBy('id')
                        ->limit(5)
                        ->get();
                }
            } catch (Throwable) {
                $payload['invoiceOverdueNotifications'] = collect();
                $payload['invoiceOverdueNotificationCount'] = 0;
            }
        }

        $payload['webLeadNotificationTotal'] = array_sum($payload['webLeadNotificationCounts']);
        $payload['notificationTotal'] = (int) $payload['webLeadNotificationTotal']
            + (int) $payload['followupNotificationCount']
            + (int) $payload['invoiceOverdueNotificationCount'];

        return $payload;
    }

    /**
     * @param  array<string, string>  $webLeadSourceLabels
     * @return array<string, mixed>
     */
    private function defaults(array $webLeadSourceLabels): array
    {
        return [
            'webLeadSourceLabels' => $webLeadSourceLabels,
            'webLeadNotificationCounts' => array_fill_keys(array_keys($webLeadSourceLabels), 0),
            'webLeadNotifications' => collect(array_keys($webLeadSourceLabels))
                ->mapWithKeys(fn (string $sourceType): array => [$sourceType => collect()]),
            'webLeadNotificationTotal' => 0,
            'canViewWebLeadNotifications' => false,
            'followupNotifications' => collect(),
            'followupNotificationCount' => 0,
            'canViewFollowupNotifications' => false,
            'invoiceOverdueNotifications' => collect(),
            'invoiceOverdueNotificationCount' => 0,
            'canViewInvoiceNotifications' => false,
            'notificationTotal' => 0,
        ];
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
