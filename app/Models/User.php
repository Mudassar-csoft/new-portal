<?php

namespace App\Models;

use App\Models\Campus;
use App\Models\User\Permission;
use App\Models\User\Role;
use App\Support\AccessMap;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    private ?bool $resolvedIsAdmin = null;

    private ?Collection $resolvedPermissionSlugs = null;

    /**
     * @var array<string, bool>
     */
    private array $resolvedPermissionChecks = [];

    /**
     * @var array<string, bool>
     */
    private array $resolvedModuleAccess = [];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'campus_id',
        'name',
        'email',
        'password',
        'avatar_path',
        'at_deleted',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'at_deleted' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope('not_deleted', function ($query) {
            $query->whereNull('at_deleted');
        });
    }

    public function scopeWithTrashed($query)
    {
        return $query->withoutGlobalScope('not_deleted');
    }

    public function scopeOnlyTrashed($query)
    {
        return $query->withoutGlobalScope('not_deleted')->whereNotNull('at_deleted');
    }

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)
            ->withPivot(['assigned_by'])
            ->withTimestamps();
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class)
            ->withTimestamps();
    }

    public function loginLogs(): HasMany
    {
        return $this->hasMany(UserLoginLog::class);
    }

    public function isAdmin(): bool
    {
        if ($this->resolvedIsAdmin !== null) {
            return $this->resolvedIsAdmin;
        }

        if ($this->relationLoaded('roles')) {
            return $this->resolvedIsAdmin = $this->roles
                ->pluck('slug')
                ->intersect(['owner', 'admin'])
                ->isNotEmpty();
        }

        return $this->resolvedIsAdmin = $this->roles()
            ->whereIn('slug', ['owner', 'admin'])
            ->exists();
    }

    /**
     * @param  string|array<int, string>  $permissions
     */
    public function hasAnyPermission(string|array $permissions): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        $permissionList = collect(is_array($permissions) ? $permissions : [$permissions])
            ->filter(fn ($permission) => is_string($permission) && $permission !== '')
            ->values();

        if ($permissionList->isEmpty()) {
            return false;
        }

        $cacheKey = $permissionList
            ->map(fn (string $permission) => trim($permission))
            ->filter()
            ->sort()
            ->implode('|');

        if ($cacheKey === '') {
            return false;
        }

        if (array_key_exists($cacheKey, $this->resolvedPermissionChecks)) {
            return $this->resolvedPermissionChecks[$cacheKey];
        }

        return $this->resolvedPermissionChecks[$cacheKey] = $this->permissionSlugs()
            ->intersect($permissionList)
            ->isNotEmpty();
    }

    public function canAccessModule(string $moduleKey): bool
    {
        if (array_key_exists($moduleKey, $this->resolvedModuleAccess)) {
            return $this->resolvedModuleAccess[$moduleKey];
        }

        $permissions = AccessMap::permissionsForModule($moduleKey);

        if ($permissions === []) {
            return $this->resolvedModuleAccess[$moduleKey] = false;
        }

        return $this->resolvedModuleAccess[$moduleKey] = $this->hasAnyPermission($permissions);
    }

    public function permissionSlugs(): Collection
    {
        if ($this->resolvedPermissionSlugs !== null) {
            return $this->resolvedPermissionSlugs;
        }

        $this->loadMissing([
            'permissions:id,slug',
            'roles:id,slug',
            'roles.permissions:id,slug',
        ]);

        return $this->resolvedPermissionSlugs = $this->permissions
            ->pluck('slug')
            ->merge(
                $this->roles->flatMap(fn (Role $role) => $role->permissions->pluck('slug'))
            )
            ->filter()
            ->unique()
            ->values();
    }
}
