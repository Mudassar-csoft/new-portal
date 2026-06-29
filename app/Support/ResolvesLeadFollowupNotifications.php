<?php

namespace App\Support;

use App\Models\LeadFollowup;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Throwable;

trait ResolvesLeadFollowupNotifications
{
    /**
     * @param  array<int, string>  $types
     */
    protected function latestDueLeadFollowupNotifications(?User $user, callable $scopeLeadQuery, array $types): Collection
    {
        $notificationNow = now();
        $todayStart = $notificationNow->copy()->startOfDay();
        $todayEnd = $notificationNow->copy()->endOfDay();

        $latestFollowupIds = LeadFollowup::query()
            ->selectRaw('MAX(id)')
            ->whereHas('lead', fn (Builder $leadQuery) => $scopeLeadQuery(
                $leadQuery->whereIn('type', $types),
                $user
            ))
            ->groupBy('lead_id');

        return LeadFollowup::with(['lead'])
            ->whereIn('id', $latestFollowupIds)
            ->get()
            ->map(function (LeadFollowup $followup) {
                $followup->notification_due_at = $this->resolveFollowupNotificationDateTime($followup);
                $followup->notification_starts_at = $followup->notification_due_at instanceof Carbon
                    ? $followup->notification_due_at->copy()->subHours(2)
                    : null;

                return $followup;
            })
            ->filter(function (LeadFollowup $followup) use ($notificationNow, $todayEnd, $todayStart): bool {
                if (! $followup->notification_due_at instanceof Carbon) {
                    return false;
                }

                if (
                    $followup->notification_due_at->lt($todayStart)
                    || $followup->notification_due_at->gt($todayEnd)
                ) {
                    return false;
                }

                if (! $followup->notification_starts_at instanceof Carbon) {
                    return false;
                }

                return $followup->notification_starts_at->lessThanOrEqualTo($notificationNow)
                    && $followup->notification_due_at->greaterThanOrEqualTo($notificationNow);
            })
            ->sort(function (LeadFollowup $left, LeadFollowup $right) {
                $leftTimestamp = $left->notification_due_at?->getTimestamp() ?? PHP_INT_MAX;
                $rightTimestamp = $right->notification_due_at?->getTimestamp() ?? PHP_INT_MAX;

                return $leftTimestamp <=> $rightTimestamp ?: ($right->id <=> $left->id);
            })
            ->values();
    }

    protected function resolveFollowupNotificationDateTime(LeadFollowup $followup): ?Carbon
    {
        $nextActionAt = $followup->next_action_date instanceof Carbon
            ? $followup->next_action_date->copy()
            : null;

        $leadNextFollowupAt = $this->parseFollowupNotificationDateTime(
            data_get($followup->lead?->details, 'next_followup_at')
        );

        if (! $nextActionAt) {
            return $leadNextFollowupAt;
        }

        if (
            $leadNextFollowupAt
            && $nextActionAt->format('H:i:s') === '00:00:00'
            && $leadNextFollowupAt->isSameDay($nextActionAt)
        ) {
            return $leadNextFollowupAt;
        }

        return $nextActionAt;
    }

    protected function parseFollowupNotificationDateTime(mixed $value): ?Carbon
    {
        $stringValue = trim((string) $value);
        if ($stringValue === '') {
            return null;
        }

        foreach (['Y-m-d\TH:i', 'Y-m-d H:i:s', 'Y-m-d H:i', 'Y-m-d'] as $format) {
            try {
                $dateTime = Carbon::createFromFormat($format, $stringValue);

                return $format === 'Y-m-d'
                    ? $dateTime->startOfDay()
                    : $dateTime;
            } catch (Throwable) {
                continue;
            }
        }

        try {
            return Carbon::parse($stringValue);
        } catch (Throwable) {
            return null;
        }
    }
}
