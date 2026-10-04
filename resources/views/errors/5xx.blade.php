@extends('errors.layout')

@section('code', isset($exception) ? $exception->getStatusCode() : '5xx')
@section('title', 'Server error')
@section('message', 'Something went wrong on our end. Please try again in a moment.')

@section('actions')
    <a href="{{ url('/') }}" class="app-btn app-btn-primary">Back to Workspace</a>
@endsection
