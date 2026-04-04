@extends('errors.layout')

@section('title', 'Page Not Found')
@section('code', '404')
@section('caption', 'The page may have been moved, the link may be outdated, or the address may contain a typo.')
@section('headline', 'Page not found')
@section('message', 'The page you requested could not be found. Check the URL and try again, or return to the dashboard to continue working.')

@section('meta')
    <div class="error-meta-label">Requested path</div>
    <p class="error-meta-value">{{ '/'.ltrim(request()->path(), '/') }}</p>
@endsection

@section('actions')
    <a href="{{ url()->previous() }}" class="btn btn-outline-soft">Go Back</a>
    <a href="{{ url('/') }}" class="btn btn-primary-solid">Go To Dashboard</a>
@endsection
