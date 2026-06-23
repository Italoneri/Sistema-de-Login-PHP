<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
    public function notice(): View
    {
        return view('auth.verify-email');
    }

    /**
     * EmailVerificationRequest (nativo do Laravel) já valida, antes de
     * chegar aqui, que a assinatura da URL é válida e não expirou —
     * isso é o que torna o link seguro sem precisar guardar um token
     * em texto/hash numa tabela customizada.
     */
    public function verify(EmailVerificationRequest $request): RedirectResponse
    {
        $request->fulfill();

        return redirect()->route('dashboard')->with('status', 'E-mail verificado com sucesso.');
    }

    public function resend(Request $request): RedirectResponse
    {
        if ($request->user()?->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        // Rate limit aplicado na rota (throttle:6,1) evita spam de
        // reenvio de e-mail.
        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'Novo link de verificação enviado para seu e-mail.');
    }
}
