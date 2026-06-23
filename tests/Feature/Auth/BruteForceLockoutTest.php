<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\LoginAttempt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BruteForceLockoutTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow(null);

        parent::tearDown();
    }

    #[Test]
    public function blocksLoginAfterFiveFailedAttemptsForSameEmail(): void
    {
        $user = User::factory()->create(['password' => Hash::make('SenhaCorreta!2026')]);

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => $user->email, 'password' => 'SenhaErrada'.$i]);
        }

        $this->assertSame(5, LoginAttempt::where('email', $user->email)->where('successful', false)->count());

        // 6ª tentativa, mesmo com a senha CORRETA, deve ser bloqueada —
        // o bloqueio age antes de checar a senha.
        $response = $this->post('/login', ['email' => $user->email, 'password' => 'SenhaCorreta!2026']);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();

        // Nenhuma tentativa nova deve ter sido registrada: o bloqueio
        // retorna antes de chamar Auth::attempt()/registerAttempt().
        $this->assertSame(5, LoginAttempt::where('email', $user->email)->count());
    }

    #[Test]
    public function unlocksAfterTimeWindowExpires(): void
    {
        $user = User::factory()->create(['password' => Hash::make('SenhaCorreta!2026')]);

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => $user->email, 'password' => 'SenhaErrada'.$i]);
        }

        $blockedResponse = $this->post('/login', ['email' => $user->email, 'password' => 'SenhaCorreta!2026']);
        $blockedResponse->assertSessionHasErrors('email');

        // Janela configurada é de 15 minutos (config/security.php) —
        // avança 16 minutos pra simular o bloqueio expirando.
        Carbon::setTestNow(Carbon::now()->addMinutes(16));

        $response = $this->post('/login', ['email' => $user->email, 'password' => 'SenhaCorreta!2026']);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    #[Test]
    public function setsRetryAfterHeaderWhenBlocked(): void
    {
        $user = User::factory()->create(['password' => Hash::make('SenhaCorreta!2026')]);

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => $user->email, 'password' => 'SenhaErrada'.$i]);
        }

        $response = $this->post('/login', ['email' => $user->email, 'password' => 'SenhaCorreta!2026']);

        $response->assertHeader('Retry-After');
    }
}
