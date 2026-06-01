<?php

namespace App\Support;

use App\Models\Campus;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

trait ResolvesCampusScope
{
    protected function userCampusScopeId(?User $user = null): ?int
    {
        $resolvedUser = $user ?? auth()->user();

        if (! $resolvedUser instanceof User || $resolvedUser->isAdmin()) {
            return null;
        }

        $campusId = (int) ($resolvedUser->campus_id ?? 0);

        return $campusId > 0 ? $campusId : null;
    }

    protected function effectiveCampusFilter(?int $requestedCampusId = null, ?User $user = null): ?int
    {
        $campusScopeId = $this->userCampusScopeId($user);

        if ($campusScopeId) {
            return $campusScopeId;
        }

        return $requestedCampusId && $requestedCampusId > 0
            ? $requestedCampusId
            : null;
    }

    protected function scopeQueryToUserCampus(Builder $query, ?User $user = null, string $column = 'campus_id'): Builder
    {
        $campusScopeId = $this->userCampusScopeId($user);

        return $query->when(
            $campusScopeId,
            fn (Builder $builder, int $campusId) => $builder->where($column, $campusId)
        );
    }

    /**
     * @param  array<int, string>  $columns
     */
    protected function campusOptionsForUser(?User $user = null, array $columns = ['*']): Collection
    {
        $campusScopeId = $this->userCampusScopeId($user);

        return Campus::query()
            ->when($campusScopeId, fn (Builder $builder, int $campusId) => $builder->whereKey($campusId))
            ->orderBy('name')
            ->get($columns);
    }

    protected function ensureCampusAccess(
        ?int $campusId,
        ?User $user = null,
        string $message = 'You are not allowed to access records from another campus.'
    ): void {
        $campusScopeId = $this->userCampusScopeId($user);

        if ($campusScopeId && $campusId && $campusId !== $campusScopeId) {
            abort(403, $message);
        }
    }
}
