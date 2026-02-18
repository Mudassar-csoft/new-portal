@extends('layouts.theme')

@section('title', 'Finance Dashboard')

@section('content')
    @include('finance.partials.dashboard_content', ['pageTitle' => 'Finance Dashboard'])
@endsection
