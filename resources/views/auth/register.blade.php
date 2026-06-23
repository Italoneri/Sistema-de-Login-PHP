@extends('layouts.app')

@section('content')
    <h1>Criar conta</h1>

    <form method="POST" action="{{ route('register') }}">
        {{-- @csrf gera o token CSRF (random_bytes internamente) e o
        middleware VerifyCsrfToken valida com hash_equals() no servidor. --}}
        @csrf

        <label for="name">Nome</label>
        <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus>

        <label for="email">E-mail</label>
        <input type="email" id="email" name="email" value="{{ old('email') }}" required>

        <label for="password">Senha (mínimo 12 caracteres)</label>
        <input type="password" id="password" name="password" minlength="12" required>

        <label for="password_confirmation">Confirmar senha</label>
        <input type="password" id="password_confirmation" name="password_confirmation" minlength="12" required>

        <button type="submit">Cadastrar</button>
    </form>

    <div class="links">
        <a href="{{ route('login') }}">Já tem conta? Entrar</a>
    </div>
@endsection
