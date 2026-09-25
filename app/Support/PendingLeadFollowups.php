<?php

namespace App\Support;

use App\Models\LeadFollowup;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class PendingLeadFollowups
{
    use ResolvesCampusScope;
    use ResolvesLeadFollowupNotifications;

    /** @return Collection<int, LeadFollowup> */
    public function forUser(User $user): Collection
    {
        $types = [];
        if ($user->hasAnyPermission(['lead.followup.view'])) {
            $types = ['training', 'certification', 'study_abroad'];
        }
        if ($user->hasAnyPermission(['lead.coworking.view'])) {
            $types[] = 'coworking';
        }
        if ($types === []) {
            return new Collection;
        }

        // Determine the latest follow-up before filtering its owner, so an older
        // follow-up cannot reappear after another user takes over the lead.
        $latestFollowupIds = LeadFollowup::query()
            ->selectRaw('MAX(id)')
            ->groupBy('lead_id');
        $closedStatuses = ['enrolled', 'not_interested_admission'];
        if (! $user->hasAnyPermission(['lead.followup.not-interesting'])) {
            $closedStatuses[] = 'not_interesting';
        }

        $notificationNow = now($this->followupNotificationTimezone());

        return LeadFollowup::query()
            ->with('lead')
            ->whereIn('id', $latestFollowupIds)
            ->where('user_id', $user->id)
            ->whereNotIn('stage', ['not_interesting', 'not_interested_admission', 'enroll'])
            ->whereHas('lead', function (Builder $query) use ($user, $types, $closedStatuses): void {
                $this->scopeQueryToUserCampus($query, $user)
                    ->whereIn('type', $types)
                    ->whereNotIn('status', $closedStatuses)
                    ->where(fn (Builder $openQuery) => $openQuery
                        ->where('type', 'training')
                        ->orWhere('status', '!=', 'registered'));
            })
            ->get()
            ->filter(function (LeadFollowup $followup) use ($notificationNow): bool {
                $followup->notification_due_at = $this->resolveFollowupNotificationDateTime($followup);

                return $followup->notification_due_at !== null
                    && $followup->notification_due_at->lessThanOrEqualTo($notificationNow);
            })
            ->sortBy(fn (LeadFollowup $followup) => $followup->notification_due_at->getTimestamp())
            ->values();
    }
}
