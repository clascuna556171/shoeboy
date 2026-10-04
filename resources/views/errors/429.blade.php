@extends('errors.layout')

@section('code', '429')
@section('title', 'Too many attempts')
@section('message', "You've made too many requests in a short time. Please wait a moment and try again.")

@section('actions')
    <a href="{{ url('/') }}" class="app-btn app-btn-secondary">Back to Workspace</a>
@endsection
