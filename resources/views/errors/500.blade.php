@extends('errors.layout')

@section('title', 'Server Error')
@section('code', '500')
@section('caption', 'Something unexpected failed while processing the request. This is usually temporary, but it should be checked.')
@section('headline', 'Internal server error')
@section('message', 'The CRM could not finish this request because of an internal error. Try the same action again in a moment. If the issue continues, report it to the administrator.')

@section('meta')
    <div class="error-meta-label">Suggested next step</div>
    <p class="error-meta-value">Refresh the page or return to the dashboard. If the error repeats, share the time and page URL with the administrator.</p>
@endsection

@section('actions')
    <a href="{{ url()->current() }}" class="btn btn-outline-soft">Try Again</a>
    <a href="{{ url('/') }}" class="btn btn-primary-solid">Go To Dashboard</a>
@endsection
