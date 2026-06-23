@extends('layouts.app')

@section('content')
    <h1>Redefinir senha</h1>

    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <label for="email">E-mail</label>
        <input type="email" id="email" name="email" value="{{ old('email', $email) }}" required autofocus>

        <label for="password">Nova senha (mínimo 12 caracteres)</label>
        <input type="password" id="password" name="password" minlength="12" required>

        <label for="password_confirmation">Confirmar nova senha</label>
        <input type="password" id="password_confirmation" name="password_confirmation" minlength="12" required>

        <button type="submit">Redefinir senha</button>
    </form>
@endsection
