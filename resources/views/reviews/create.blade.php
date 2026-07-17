@extends('layouts.theme')

@section('title', 'Create Review')

@section('content')
    @include('reviews.partials.form-shell', [
        'mode' => 'create',
        'title' => 'Create Review',
        'action' => route('reviews.store'),
        'submitLabel' => 'Create Review',
    ])
@endsection
