<?php

namespace Database\Seeders;

use App\Models\Campus;
use App\Models\User;
use App\Models\User\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class CampusUserSeeder extends Seeder
{
    public function run(): void
    {
        $roles = $this->resolveRoles();

        Campus::query()
            ->orderBy('id')
            ->get()
            ->each(function (Campus $campus) use ($roles): void {
                foreach ($this->profilesForCampus($campus) as $profile) {
                    $this->seedCampusUser($campus, $profile, $roles);
                }
            });
    }

    /**
     * @return Collection<string, Role>
     */
    private function resolveRoles(): Collection
    {
        $roles = Role::query()
            ->whereIn('slug', ['member', 'read-only'])
            ->get()
            ->keyBy('slug');

        if ($roles->count() === 2) {
            return $roles;
        }

        $this->call(RolePermissionSeeder::class);

        return Role::query()
            ->whereIn('slug', ['member', 'read-only'])
            ->get()
            ->keyBy('slug');
    }

    /**
     * @return array<int, array{name: string, email: string, role: string}>
     */
    private function profilesForCampus(Campus $campus): array
    {
        $emailPrefix = Str::lower($campus->code ?: 'campus-' . $campus->id);
        $label = $campus->title ?: $campus->name;

        $profiles = [
            [
                'name' => $label . ' Manager',
                'email' => $emailPrefix . '.manager@career.test',
                'role' => 'member',
            ],
            [
                'name' => $label . ' Admissions Officer',
                'email' => $emailPrefix . '.admissions@career.test',
                'role' => 'member',
            ],
            [
                'name' => $label . ' Viewer',
                'email' => $emailPrefix . '.viewer@career.test',
                'role' => 'read-only',
            ],
        ];

        if ($campus->campus_type === 'franchise') {
            unset($profiles[1]);
        }

        return array_values($profiles);
    }

    /**
     * @param  array{name: string, email: string, role: string}  $profile
     * @param  Collection<string, Role>  $roles
     */
    private function seedCampusUser(Campus $campus, array $profile, Collection $roles): void
    {
        $user = User::withoutGlobalScope('not_deleted')->firstOrNew([
            'email' => $profile['email'],
        ]);

        $user->campus_id = $campus->id;
        $user->name = $profile['name'];
        $user->email_verified_at = $user->email_verified_at ?: now();
        $user->at_deleted = null;

        if (!$user->exists) {
            $user->password = 'password';
            $user->remember_token = Str::random(10);
        }

        $user->save();

        $role = $roles->get($profile['role']);

        if ($role) {
            $user->roles()->sync([
                $role->id => ['assigned_by' => null],
            ]);
        }

        $user->permissions()->sync([]);
    }
}
