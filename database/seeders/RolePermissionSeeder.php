<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\User\Permission;
use App\Models\User\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Leads
            ['resource' => 'lead', 'action' => 'view'],
            ['resource' => 'lead', 'action' => 'create'],
            ['resource' => 'lead', 'action' => 'update'],
            ['resource' => 'lead', 'action' => 'delete'],
            ['resource' => 'lead', 'action' => 'followup.view'],
            ['resource' => 'lead', 'action' => 'followup.update'],
            // Registration
            ['resource' => 'registration', 'action' => 'view'],
            ['resource' => 'registration', 'action' => 'create'],
            ['resource' => 'registration', 'action' => 'update'],
            ['resource' => 'registration', 'action' => 'delete'],
            // Admission
            ['resource' => 'admission', 'action' => 'view'],
            ['resource' => 'admission', 'action' => 'create'],
            ['resource' => 'admission', 'action' => 'update'],
            ['resource' => 'admission', 'action' => 'delete'],
            // Users/Roles/Permissions
            ['resource' => 'user', 'action' => 'view'],
            ['resource' => 'user', 'action' => 'create'],
            ['resource' => 'user', 'action' => 'update'],
            ['resource' => 'user', 'action' => 'delete'],
            ['resource' => 'role', 'action' => 'manage'],
            ['resource' => 'permission', 'action' => 'manage'],
        ];

        DB::transaction(function () use ($permissions) {
            $permissionIds = collect($permissions)->mapWithKeys(function ($perm) {
                $slug = "{$perm['resource']}.{$perm['action']}";
                $permission = Permission::firstOrCreate(
                    ['slug' => $slug],
                    [
                        'resource' => $perm['resource'],
                        'action' => $perm['action'],
                        'description' => Str::headline($slug),
                    ]
                );
                return [$slug => $permission->id];
            });

            $roleSets = [
                'owner' => $permissionIds->values(),
                'admin' => $permissionIds->values(),
                'member' => $permissionIds->filter(function ($id, $slug) {
                    return !str_contains($slug, 'user.') && !str_contains($slug, 'role.') && !str_contains($slug, 'permission.');
                })->values(),
                'read-only' => $permissionIds->filter(function ($id, $slug) {
                    return str_contains($slug, '.view');
                })->values(),
            ];

            $roles = collect($roleSets)->mapWithKeys(function ($permIds, $slug) {
                $role = Role::firstOrCreate(
                    ['slug' => $slug],
                    ['name' => Str::headline($slug), 'description' => ucfirst($slug)]
                );
                $role->permissions()->sync($permIds);
                return [$slug => $role];
            });

            $user = User::firstOrCreate(
                ['email' => 'admin@example.com'],
                [
                    'name' => 'Admin User',
                    'password' => bcrypt('password'),
                ]
            );

            $user->roles()->sync([$roles['owner']->id]);
        });
    }
}
