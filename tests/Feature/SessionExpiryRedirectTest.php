<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SessionExpiryRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_requests_with_expired_or_invalid_csrf_token_redirect_to_login(): void
    {
        Route::post('/test/session-expired', function () {
            throw new TokenMismatchException();
        });

        $this->from(route('login'))
            ->post('/test/session-expired')
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors([
                'session' => 'Your session has expired. Please log in again.',
            ]);
    }
}
