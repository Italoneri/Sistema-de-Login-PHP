<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function authenticatesUserWithValidCredentialsAndRedirectsToDashboard(): void
    {
        $user = User::factory()->create(['password' => Hash::make('SenhaCorreta!2026')]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'SenhaCorreta!2026',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    #[Test]
    public function returnsGenericErrorMessageForWrongPassword(): void
    {
        $user = User::factory()->create(['password' => Hash::make('SenhaCorreta!2026')]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'SenhaErrada!2026',
        ]);

        $response->assertSessionHasErrors(['email' => 'Credenciais inválidas.']);
        $this->assertGuest();
    }

    #[Test]
    public function returnsIdenticalGenericErrorMessageForNonexistentEmail(): void
    {
        $response = $this->post('/login', [
            'email' => 'naoexiste@example.com',
            'password' => 'QualquerSenha!2026',
        ]);

        // A mensagem precisa ser idêntica à de senha errada (acima) —
        // é o que garante que não há enumeração de contas via login.
        $response->assertSessionHasErrors(['email' => 'Credenciais inválidas.']);
        $this->assertGuest();
    }
}
