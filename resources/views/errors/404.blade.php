@extends('errors.layout')

@section('code', '404')
@section('title', 'Página Não Encontrada')
@section('message', 'A página que procura não existe ou foi movida. Verifique o endereço e tente novamente.')

@section('glow-color', '#6366f1')
@section('icon-bg', 'rgba(99,102,241,.1)')
@section('divider-color', '#6366f1')

@section('icon')
    <svg fill="none" stroke="#6366f1" stroke-width="1.8" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607z"/>
    </svg>
@endsection
