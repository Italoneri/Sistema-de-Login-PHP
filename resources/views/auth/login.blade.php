@extends('layouts.app')

@section('content')
    <h1>Entrar</h1>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <label for="email">E-mail</label>
        <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>

        <label for="password">Senha</label>
        <input type="password" id="password" name="password" required>

        <label style="display:flex; align-items:center; gap:8px; font-weight:normal;">
            <input type="checkbox" name="remember" style="width:auto;"> Lembrar de mim
        </label>

        <button type="submit">Entrar</button>
    </form>

    <div class="links">
        <a href="{{ route('password.request') }}">Esqueci minha senha</a>
        &middot;
        <a href="{{ route('register') }}">Criar conta</a>
    </div>
@endsection
