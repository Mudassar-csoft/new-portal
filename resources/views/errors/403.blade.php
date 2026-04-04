@extends('errors.layout')

@section('title', 'Access Denied')
@section('code', '403')
@section('caption', 'Your account reached a protected area, but this action is not allowed with the current permissions.')
@section('headline', 'Access denied')
@section('message', 'You are signed in, but you do not have permission to open this page or complete this action. If you should have access, ask an administrator to update your role or permissions.')

@section('meta')
    <div class="error-meta-label">What happened</div>
    <p class="error-meta-value">The server understood the request but refused to authorize it.</p>
@endsection

@section('actions')
    <a href="{{ url()->previous() }}" class="btn btn-outline-soft">Go Back</a>
    <a href="{{ url('/') }}" class="btn btn-primary-solid">Go To Dashboard</a>
@endsection
