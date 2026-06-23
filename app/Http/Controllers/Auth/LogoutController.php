<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogoutController extends Controller
{
    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        // invalidate() destrói os dados da sessão no storage (tabela
        // `sessions`, já que SESSION_DRIVER=database) e gera um novo ID.
        // regenerateToken() troca o token CSRF — sem isso, um token CSRF
        // capturado antes do logout continuaria válido depois.
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
