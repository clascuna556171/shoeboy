@extends('errors.layout')

@section('code', '500')
@section('title', 'Something went wrong')
@section('message', 'An unexpected error occurred on our end. Please try again in a moment.')

@section('actions')
    <a href="{{ url('/') }}" class="app-btn app-btn-primary">Back to Workspace</a>
@endsection
