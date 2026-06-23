<?php

declare(strict_types=1);

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

require __DIR__.'/auth.php';

// middleware 'verified' exige e-mail confirmado, além de autenticado.
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');
