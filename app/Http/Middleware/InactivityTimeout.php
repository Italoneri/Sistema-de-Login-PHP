<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Timeout de INATIVIDADE — diferente do 'lifetime' do config/session.php,
 * que é absoluto desde a criação do cookie. Aqui contamos a partir do
 * último request do usuário, renovando 'last_activity' a cada interação.
 * Só se aplica a rotas autenticadas (registrado em routes/web.php).
 */
class InactivityTimeout
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $lastActivity = $request->session()->get('last_activity');
            $timeoutSeconds = config('security.inactivity_timeout_minutes') * 60;

            if ($lastActivity !== null && (time() - $lastActivity) > $timeoutSeconds) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->with('status', 'Sessão expirada por inatividade.');
            }

            $request->session()->put('last_activity', time());
        }

        return $next($request);
    }
}
