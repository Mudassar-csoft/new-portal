<?php

namespace Tests\Feature;

use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_admin_can_log_in_with_default_credentials(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $response = $this->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticated();
    }

    public function test_login_with_keep_me_signed_in_sets_remember_me_state(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $response = $this->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
            'remember' => '1',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticated();

        $user = \App\Models\User::query()->where('email', 'admin@example.com')->firstOrFail();
        $this->assertNotNull($user->fresh()->remember_token);

        $cookieNames = collect($response->baseResponse->headers->getCookies())
            ->map(fn ($cookie) => $cookie->getName())
            ->all();

        $this->assertTrue(
            collect($cookieNames)->contains(fn (string $name) => str_starts_with($name, 'remember_web_')),
            'Expected a remember-me cookie to be present on the login response.'
        );
    }

    public function test_login_without_keep_me_signed_in_does_not_set_remember_me_cookie(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $response = $this->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticated();

        $cookieNames = collect($response->baseResponse->headers->getCookies())
            ->map(fn ($cookie) => $cookie->getName())
            ->all();

        $this->assertFalse(
            collect($cookieNames)->contains(fn (string $name) => str_starts_with($name, 'remember_web_')),
            'Did not expect a remember-me cookie when the checkbox was left unchecked.'
        );
    }
}
