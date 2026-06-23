<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    public function forgotPasswordForm(): View
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(ForgotPasswordRequest $request): RedirectResponse
    {
        // Password::sendResetLink() já gera o token aleatório, salva só o
        // HASH dele em password_reset_tokens (não o token em texto claro)
        // e envia a notificação nativa com o link.
        Password::sendResetLink($request->only('email'));

        // Resposta SEMPRE idêntica, exista ou não o e-mail informado —
        // o retorno de sendResetLink() (RESET_LINK_SENT vs INVALID_USER)
        // é deliberadamente ignorado aqui para não permitir enumeração
        // de contas.
        return back()->with('status', 'Se o e-mail existir em nossa base, um link de redefinição foi enviado.');
    }

    public function resetPasswordForm(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    public function reset(ResetPasswordRequest $request): RedirectResponse
    {
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                // Hash::make() via cast 'password' => 'hashed' do Model.
                $user->forceFill(['password' => $password])->setRememberToken(Str::random(60));
                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', 'Senha redefinida com sucesso. Faça login.');
        }

        // Mensagem genérica também aqui: token inválido e token expirado
        // recebem a mesma resposta.
        return back()->withErrors(['email' => 'Não foi possível redefinir a senha. O link pode ter expirado.']);
    }
}
