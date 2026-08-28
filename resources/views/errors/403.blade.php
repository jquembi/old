@extends('errors.layout')

@section('code', '403')
@section('title', 'Acesso Proibido')
@section('message', 'Não tem permissão para aceder a este recurso. Contacte o administrador se acredita que isto é um erro.')

@section('glow-color', '#ef4444')
@section('icon-bg', 'rgba(239,68,68,.1)')
@section('divider-color', '#ef4444')

@section('icon')
    <svg fill="none" stroke="#ef4444" stroke-width="1.8" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636"/>
    </svg>
@endsection
