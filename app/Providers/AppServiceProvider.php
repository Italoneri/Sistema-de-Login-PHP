<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Sem Breeze/Jetstream para registrar isso automaticamente:
        // conecta manualmente o evento Registered ao listener nativo que
        // dispara o e-mail de verificação com link assinado.
        Event::listen(Registered::class, SendEmailVerificationNotification::class);
    }
}
