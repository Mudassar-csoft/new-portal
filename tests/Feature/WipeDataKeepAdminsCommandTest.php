<?php

namespace Tests\Feature;

use App\Models\Campus;
use App\Models\User;
use App\Models\User\Role;
use App\Models\UserLoginLog;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class WipeDataKeepAdminsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_wipes_non_admin_data_and_keeps_admin_users(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $adminRole = Role::query()->where('slug', 'admin')->firstOrFail();
        $memberRole = Role::query()->where('slug', 'member')->firstOrFail();

        $campus = Campus::query()->create([
            'name' => 'North Campus',
            'title' => 'North Campus',
            'slug' => 'north-campus',
            'code' => 'NC',
            'city' => 'Lahore',
        ]);

        $extraAdmin = User::factory()->create([
            'name' => 'Second Admin',
            'email' => 'second-admin@example.com',
            'campus_id' => $campus->id,
        ]);
        $extraAdmin->roles()->sync([$adminRole->id]);

        $member = User::factory()->create([
            'name' => 'Member User',
            'email' => 'member@example.com',
            'campus_id' => $campus->id,
        ]);
        $member->roles()->sync([
            $memberRole->id => ['assigned_by' => $extraAdmin->id],
        ]);

        UserLoginLog::query()->create([
            'user_id' => $member->id,
            'action' => 'login',
            'logged_at' => now(),
        ]);

        DB::table('sessions')->insert([
            'id' => 'member-session',
            'user_id' => $member->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'payload' => 'payload',
            'last_activity' => now()->timestamp,
        ]);

        $this->artisan('app:wipe-data-keep-admins --force')->assertExitCode(0);

        $this->assertDatabaseHas('users', ['email' => 'admin@example.com']);
        $this->assertDatabaseHas('users', ['email' => 'second-admin@example.com']);
        $this->assertDatabaseMissing('users', ['email' => 'member@example.com']);
        $this->assertDatabaseMissing('campuses', ['id' => $campus->id]);
        $this->assertDatabaseCount('user_login_logs', 0);
        $this->assertDatabaseCount('sessions', 0);
        $this->assertDatabaseHas('role_user', [
            'user_id' => $extraAdmin->id,
            'role_id' => $adminRole->id,
        ]);
        $this->assertNull($extraAdmin->fresh()->campus_id);
    }
}
