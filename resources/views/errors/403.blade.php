@extends('errors.layout')

@section('code', '403')
@section('title', 'Access denied')
@section('message', "You don't have permission to view this page.")

@section('actions')
    <a href="{{ url('/') }}" class="app-btn app-btn-primary">Back to Workspace</a>
@endsection
