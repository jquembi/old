@extends('errors.layout')

@section('code', '419')
@section('title', 'Sessão Expirada')
@section('message', 'A sua sessão expirou por inactividade. Recarregue a página para continuar de onde parou.')

@section('glow-color', '#f59e0b')
@section('icon-bg', 'rgba(245,158,11,.1)')
@section('divider-color', '#f59e0b')

@section('icon')
    <svg fill="none" stroke="#f59e0b" stroke-width="1.8" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/>
    </svg>
@endsection

@section('actions')
    <a href="javascript:location.reload()" class="btn btn-primary">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182"/></svg>
        Recarregar Página
    </a>
    <a href="{{ url('/') }}" class="btn btn-secondary">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></svg>
        Página Inicial
    </a>
@endsection
