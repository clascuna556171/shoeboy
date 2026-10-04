@extends('errors.layout')

@section('code', isset($exception) ? $exception->getStatusCode() : '4xx')
@section('title', 'Request error')
@section('message', "We couldn't process that request. Please check the details and try again.")

@section('actions')
    <a href="{{ url('/') }}" class="app-btn app-btn-secondary">Back to Workspace</a>
@endsection
