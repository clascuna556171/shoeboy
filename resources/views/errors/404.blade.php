@extends('errors.layout')

@section('code', '404')
@section('title', 'Page not found')
@section('message', "The page you're looking for doesn't exist or may have been moved.")

@section('actions')
    <a href="{{ url('/') }}" class="app-btn app-btn-primary">Back to Workspace</a>
@endsection
