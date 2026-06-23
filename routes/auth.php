<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;

// middleware('guest') impede usuário já autenticado de acessar
// cadastro/login/reset de senha novamente.
Route::middleware('guest')->group(function () {
    Route::get('/registro', [RegisterController::class, 'create'])->name('register');
    Route::post('/registro', [RegisterController::class, 'store']);

    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);

    Route::get('/esqueci-senha', [PasswordResetController::class, 'forgotPasswordForm'])->name('password.request');
    Route::post('/esqueci-senha', [PasswordResetController::class, 'sendResetLink'])->name('password.email');

    Route::get('/redefinir-senha/{token}', [PasswordResetController::class, 'resetPasswordForm'])->name('password.reset');
    Route::post('/redefinir-senha', [PasswordResetController::class, 'reset'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LogoutController::class, 'destroy'])->name('logout');

    Route::get('/verificar-email', [EmailVerificationController::class, 'notice'])->name('verification.notice');

    // Middleware 'signed' valida a assinatura criptográfica da URL gerada
    // por URL::temporarySignedRoute() — é isso que torna o link seguro
    // sem precisar guardar token em texto/hash numa tabela customizada.
    Route::get('/verificar-email/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware('signed')
        ->name('verification.verify');

    // throttle:6,1 (6 requisições por minuto) evita spam de reenvio.
    Route::post('/verificar-email/reenviar', [EmailVerificationController::class, 'resend'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
});
