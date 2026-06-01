<?php

namespace Tests\Feature;

use App\Models\Campus;
use App\Models\User;
use Database\Seeders\CampusSeeder;
use Database\Seeders\CampusUserSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampusUserSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_campus_user_seeder_creates_idempotent_users_for_each_campus(): void
    {
        $this->seed([
            RolePermissionSeeder::class,
            CampusSeeder::class,
            CampusUserSeeder::class,
        ]);

        $expectedCampusUsers = 0;

        foreach (Campus::query()->orderBy('id')->get() as $campus) {
            $users = User::query()
                ->where('campus_id', $campus->id)
                ->with('roles:id,slug')
                ->orderBy('email')
                ->get();

            $expectedCount = $campus->campus_type === 'franchise' ? 2 : 3;
            $expectedCampusUsers += $expectedCount;

            $this->assertCount($expectedCount, $users, 'Unexpected fake user count for campus ' . $campus->code);
            $this->assertTrue(
                $users->pluck('email')->every(
                    fn (string $email): bool => str_starts_with($email, strtolower($campus->code) . '.')
                )
            );
            $this->assertSame(
                1,
                $users->filter(fn (User $user): bool => $user->roles->pluck('slug')->contains('read-only'))->count()
            );
            $this->assertSame(
                $expectedCount - 1,
                $users->filter(fn (User $user): bool => $user->roles->pluck('slug')->contains('member'))->count()
            );
        }

        $this->assertSame($expectedCampusUsers + 1, User::query()->count());

        $this->seed(CampusUserSeeder::class);

        $this->assertSame($expectedCampusUsers + 1, User::query()->count());
    }
}
