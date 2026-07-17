@extends('layouts.theme')

@section('title', 'Edit Review')

@section('content')
    @include('reviews.partials.form-shell', [
        'mode' => 'edit',
        'title' => 'Edit Review',
        'action' => route('reviews.update', $review),
        'submitLabel' => 'Update Review',
    ])
@endsection
