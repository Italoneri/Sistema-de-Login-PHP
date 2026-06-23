<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class RegisterController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        // O cast 'password' => 'hashed' no Model User já aplica
        // Hash::make() (argon2id, config/hashing.php) automaticamente.
        $user = User::create($request->validated());

        // Dispara o evento nativo do Laravel: o listener
        // SendEmailVerificationNotification (registrado em
        // AppServiceProvider) envia o e-mail de verificação com link
        // assinado automaticamente.
        event(new Registered($user));

        // Não autenticamos o usuário automaticamente aqui — ele precisa
        // confirmar o e-mail antes de poder acessar o dashboard, já que
        // a rota /dashboard exige o middleware 'verified'.
        return redirect()
            ->route('login')
            ->with('status', 'Cadastro realizado. Verifique seu e-mail antes de fazer login.');
    }
}
