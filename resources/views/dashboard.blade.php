@extends('layouts.app')

@section('content')
    <h1>Dashboard</h1>

    {{-- {{ }} já escapa via htmlspecialchars(ENT_QUOTES, 'UTF-8') internamente — nunca usar {!! !!} aqui com dado de usuário. --}}
    <p>Bem-vindo(a), {{ $user->name }}.</p>
    <p style="color:#6b7280; font-size:0.9rem;">{{ $user->email }}</p>

    <form method="POST" action="{{ route('logout') }}" style="margin-top:24px;">
        @csrf
        <button type="submit">Sair</button>
    </form>
@endsection
