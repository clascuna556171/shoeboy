@extends('errors.layout')

@section('code', '419')
@section('title', 'Session expired')
@section('message', 'Your session timed out for security. Please sign in again to continue.')

@section('actions')
    <a href="{{ route('login') }}" class="app-btn app-btn-primary">Sign in again</a>
@endsection
