<?php

namespace App\Providers;

use App\Models\Lead;
use App\Models\LeadFollowup;
use App\Models\LeadTransfer;
use App\Models\WebLead;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
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
            ];

            try {
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
            } catch (Throwable) {
                // Keep zero counts when tables are unavailable.
            }

            $view->with('sidebarCounts', $sidebarCounts);
        });

        View::composer('layouts.header', function ($view): void {
            $webLeadSourceLabels = WebLead::sourceLabels();
            $webLeadNotificationCounts = array_fill_keys(array_keys($webLeadSourceLabels), 0);
            $webLeadNotifications = [];

            foreach (array_keys($webLeadSourceLabels) as $sourceType) {
                $webLeadNotifications[$sourceType] = collect();
            }

            try {
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
            } catch (Throwable) {
                // Keep empty notification data when the table is unavailable.
            }

            $view->with([
                'webLeadSourceLabels' => $webLeadSourceLabels,
                'webLeadNotificationCounts' => $webLeadNotificationCounts,
                'webLeadNotifications' => $webLeadNotifications,
                'webLeadNotificationTotal' => array_sum($webLeadNotificationCounts),
            ]);
        });
    }
}
