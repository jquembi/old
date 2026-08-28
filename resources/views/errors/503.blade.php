@extends('errors.layout')

@section('code', '503')
@section('title', 'Em Manutenção')
@section('message', 'O sistema está temporariamente indisponível para manutenção programada. Voltaremos em breve.')

@section('glow-color', '#8b5cf6')
@section('icon-bg', 'rgba(139,92,246,.1)')
@section('divider-color', '#8b5cf6')

@section('icon')
    <svg fill="none" stroke="#8b5cf6" stroke-width="1.8" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17l-5.95-5.95a1.5 1.5 0 010-2.12l.88-.88a1.5 1.5 0 012.12 0l.94.94a2.25 2.25 0 003.18 0l.94-.94a1.5 1.5 0 012.12 0l.88.88a1.5 1.5 0 010 2.12l-5.95 5.95a1.5 1.5 0 01-2.12 0z"/>
        <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17l-5.95 5.95m12.36-5.95l5.95 5.95M6.72 13.01l-1.47 1.47M17.28 13.01l1.47 1.47"/>
    </svg>
@endsection

@section('actions')
    <a href="javascript:location.reload()" class="btn btn-primary">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182"/></svg>
        Verificar Novamente
    </a>
@endsection
