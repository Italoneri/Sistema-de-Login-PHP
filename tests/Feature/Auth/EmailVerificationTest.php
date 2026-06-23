<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function unverifiedUserIsRedirectedAwayFromDashboardToVerificationNotice(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertRedirect('/verificar-email');
    }

    #[Test]
    public function validSignedLinkMarksEmailAsVerified(): void
    {
        Event::fake([Verified::class]);

        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        $response->assertRedirect('/dashboard');
        $this->assertNotNull($user->refresh()->email_verified_at);
        Event::assertDispatched(Verified::class);
    }

    #[Test]
    public function rejectsLinkWithTamperedSignature(): void
    {
        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        // Troca o hash do e-mail por outro qualquer — a assinatura da URL
        // deixa de corresponder ao conteúdo, então o middleware 'signed'
        // tem que rejeitar antes de chegar no controller.
        $tamperedUrl = str_replace(sha1($user->email), sha1('outro@example.com'), $verificationUrl);

        $response = $this->actingAs($user)->get($tamperedUrl);

        $response->assertForbidden();
        $this->assertNull($user->refresh()->email_verified_at);
    }

    #[Test]
    public function rejectsExpiredVerificationLink(): void
    {
        $user = User::factory()->unverified()->create();

        $expiredUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->subMinutes(1),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($expiredUrl);

        $response->assertForbidden();
        $this->assertNull($user->refresh()->email_verified_at);
    }

    #[Test]
    public function alreadyVerifiedUserResendingIsRedirectedToDashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/verificar-email/reenviar');

        $response->assertRedirect('/dashboard');
    }
}
