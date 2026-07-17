@extends('layouts.theme')

@section('title', 'Create News')

@section('content')
    @include('news.partials.form-shell', [
        'mode' => 'create',
        'title' => 'Create News',
        'action' => route('news.store'),
        'submitLabel' => 'Create News',
    ])
@endsection
