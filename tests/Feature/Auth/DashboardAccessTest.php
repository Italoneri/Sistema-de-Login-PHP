<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DashboardAccessTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function guestIsRedirectedToLoginWhenAccessingDashboard(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    #[Test]
    public function authenticatedAndVerifiedUserCanAccessDashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee($user->name);
    }

    #[Test]
    public function authenticatedButUnverifiedUserCannotAccessDashboard(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertRedirect('/verificar-email');
    }

    #[Test]
    public function responseIncludesSecurityHeaders(): void
    {
        $response = $this->get('/login');

        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $this->assertNotNull($response->headers->get('Content-Security-Policy'));
    }
}
