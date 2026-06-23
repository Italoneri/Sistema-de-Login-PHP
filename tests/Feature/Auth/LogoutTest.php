<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function destroysSessionAndRedirectsToLogin(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    #[Test]
    public function dashboardBecomesInaccessibleAfterLogout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/logout');

        $this->get('/dashboard')->assertRedirect('/login');
    }
}
