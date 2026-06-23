<?php

use App\Http\Middleware\InactivityTimeout;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Laravel 12 não usa mais app/Http/Kernel.php — middleware
        // global do grupo 'web' é registrado aqui. InactivityTimeout
        // só age quando há usuário autenticado (checa Auth::check()
        // internamente), então é seguro deixá-lo no grupo geral.
        $middleware->web(append: [
            SecurityHeaders::class,
            InactivityTimeout::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Com APP_DEBUG=false (obrigatório em produção, ver .env.example),
        // o handler padrão do Laravel já garante que nenhuma exception
        // renderiza stack trace pro usuário — o erro completo vai só
        // pro canal de log configurado em config/logging.php.
    })->create();
