@extends('errors.layout')

@section('code', '429')
@section('title', 'Demasiados Pedidos')
@section('message', 'Enviou demasiados pedidos em pouco tempo. Aguarde alguns momentos e tente novamente.')

@section('glow-color', '#f97316')
@section('icon-bg', 'rgba(249,115,22,.1)')
@section('divider-color', '#f97316')

@section('icon')
    <svg fill="none" stroke="#f97316" stroke-width="1.8" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/>
    </svg>
@endsection

@section('actions')
    <a href="javascript:location.reload()" class="btn btn-primary">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182"/></svg>
        Tentar Novamente
    </a>
    <a href="{{ url('/') }}" class="btn btn-secondary">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></svg>
        Página Inicial
    </a>
@endsection
