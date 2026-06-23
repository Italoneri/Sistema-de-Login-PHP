@extends('layouts.app')

@section('content')
    <h1>Confirme seu e-mail</h1>

    <p>Enviamos um link de verificação para o seu e-mail. Clique nele para liberar o acesso ao dashboard.</p>

    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit">Reenviar e-mail de verificação</button>
    </form>

    <div class="links">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" style="background:#6b7280;">Sair</button>
        </form>
    </div>
@endsection
