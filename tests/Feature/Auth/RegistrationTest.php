<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function createsUserWithArgon2idHashedPasswordOnValidRegistration(): void
    {
        Event::fake([Registered::class]);

        $response = $this->post('/registro', [
            'name' => 'Usuario Teste',
            'email' => 'usuario@example.com',
            'password' => 'SenhaForte!Teste2026',
            'password_confirmation' => 'SenhaForte!Teste2026',
        ]);

        $response->assertRedirect('/login');

        $user = User::where('email', 'usuario@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue(Hash::check('SenhaForte!Teste2026', $user->password));
        $this->assertStringStartsWith('$argon2id$', $user->password);
        $this->assertNull($user->email_verified_at);

        Event::assertDispatched(Registered::class);
    }

    #[Test]
    public function rejectsRegistrationWithDuplicateEmail(): void
    {
        User::factory()->create(['email' => 'duplicado@example.com']);

        $response = $this->post('/registro', [
            'name' => 'Outro Usuario',
            'email' => 'duplicado@example.com',
            'password' => 'SenhaForte!Teste2026',
            'password_confirmation' => 'SenhaForte!Teste2026',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertSame(1, User::where('email', 'duplicado@example.com')->count());
    }

    #[Test]
    public function rejectsPasswordShorterThanTwelveCharacters(): void
    {
        $response = $this->post('/registro', [
            'name' => 'Usuario Teste',
            'email' => 'curta@example.com',
            'password' => 'Curta123',
            'password_confirmation' => 'Curta123',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertDatabaseMissing('users', ['email' => 'curta@example.com']);
    }

    #[Test]
    public function rejectsMismatchedPasswordConfirmation(): void
    {
        $response = $this->post('/registro', [
            'name' => 'Usuario Teste',
            'email' => 'naoconfirma@example.com',
            'password' => 'SenhaForte!Teste2026',
            'password_confirmation' => 'SenhaDiferente!2026',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertDatabaseMissing('users', ['email' => 'naoconfirma@example.com']);
    }

    #[Test]
    public function rejectsPasswordFoundInKnownDataLeak(): void
    {
        $this->fakeUncompromisedVerifier(safe: false);

        $response = $this->post('/registro', [
            'name' => 'Usuario Teste',
            'email' => 'vazada@example.com',
            'password' => 'password123456',
            'password_confirmation' => 'password123456',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertDatabaseMissing('users', ['email' => 'vazada@example.com']);
    }
}
