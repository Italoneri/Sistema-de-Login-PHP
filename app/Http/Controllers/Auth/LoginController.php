<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\BruteForceProtectionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function __construct(
        private readonly BruteForceProtectionService $bruteForce,
    ) {
    }

    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->validated();
        $ip = (string) $request->ip();

        // Checa bloqueio ANTES de tocar em Auth::attempt() — evita gastar
        // uma verificação de hash (custosa de propósito, argon2id) em
        // requests que já sabemos que vão ser rejeitados.
        if ($this->bruteForce->isBlocked($credentials['email'], $ip)) {
            $retryAfterSeconds = $this->bruteForce->secondsUntilUnlock($credentials['email'], $ip);
            $retryAfterMinutes = (int) ceil($retryAfterSeconds / 60);

            $response = back()->withErrors([
                'email' => "Muitas tentativas de login. Tente novamente em {$retryAfterMinutes} minuto(s).",
            ]);
            $response->headers->set('Retry-After', (string) $retryAfterSeconds);

            return $response;
        }

        $authenticated = Auth::attempt($credentials, $request->boolean('remember'));

        // Registra a tentativa independente do resultado — é o que
        // alimenta o bloqueio acima na próxima requisição.
        $this->bruteForce->registerAttempt($credentials['email'], $ip, $authenticated, $request->userAgent());

        if (! $authenticated) {
            // Mensagem genérica: nunca revela se o e-mail existe ou se
            // foi só a senha que errou (evita enumeração de contas).
            return back()->withErrors(['email' => 'Credenciais inválidas.'])->onlyInput('email');
        }

        // Mitigação de session fixation: gera um novo ID de sessão
        // pós-autenticação, invalidando qualquer ID que um atacante
        // possa ter fixado antes do login.
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }
}
