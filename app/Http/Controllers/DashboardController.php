<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    // Acesso já restrito pelo middleware ['auth', 'verified'] na rota.
    public function index(Request $request): View
    {
        return view('dashboard', ['user' => $request->user()]);
    }
}
