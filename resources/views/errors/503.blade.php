@extends('errors.layout')

@section('code', '503')
@section('title', 'Down for maintenance')
@section('message', 'The system is temporarily unavailable while we perform maintenance. Please check back shortly.')

@section('actions')
    <a href="{{ url('/') }}" class="app-btn app-btn-primary">Reload</a>
@endsection
