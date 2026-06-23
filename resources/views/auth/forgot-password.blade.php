@extends('layouts.app')

@section('content')
    <h1>Esqueci minha senha</h1>

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <label for="email">E-mail</label>
        <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>

        <button type="submit">Enviar link de redefinição</button>
    </form>

    <div class="links">
        <a href="{{ route('login') }}">Voltar para login</a>
    </div>
@endsection
