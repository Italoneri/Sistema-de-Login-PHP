<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function sendsIdenticalResponseForExistingAndNonexistentEmail(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $responseExisting = $this->post('/esqueci-senha', ['email' => $user->email]);
        $responseNonexistent = $this->post('/esqueci-senha', ['email' => 'naoexiste@example.com']);

        $expectedMessage = 'Se o e-mail existir em nossa base, um link de redefinição foi enviado.';
        $responseExisting->assertSessionHas('status', $expectedMessage);
        $responseNonexistent->assertSessionHas('status', $expectedMessage);
    }

    #[Test]
    public function resetsPasswordWithValidToken(): void
    {
        $user = User::factory()->create(['password' => Hash::make('SenhaAntiga!2026')]);
        $token = Password::createToken($user);

        $response = $this->post('/redefinir-senha', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'SenhaNovaForte!2026',
            'password_confirmation' => 'SenhaNovaForte!2026',
        ]);

        $response->assertRedirect('/login');
        $this->assertTrue(Hash::check('SenhaNovaForte!2026', $user->refresh()->password));
        $this->assertStringStartsWith('$argon2id$', $user->password);
    }

    #[Test]
    public function rejectsInvalidToken(): void
    {
        $user = User::factory()->create(['password' => Hash::make('SenhaAntiga!2026')]);

        $response = $this->post('/redefinir-senha', [
            'token' => 'token-invalido',
            'email' => $user->email,
            'password' => 'SenhaNovaForte!2026',
            'password_confirmation' => 'SenhaNovaForte!2026',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertTrue(Hash::check('SenhaAntiga!2026', $user->refresh()->password));
    }
}
