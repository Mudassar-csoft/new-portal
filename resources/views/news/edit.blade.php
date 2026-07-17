@extends('layouts.theme')

@section('title', 'Edit News')

@section('content')
    @include('news.partials.form-shell', [
        'mode' => 'edit',
        'title' => 'Edit News',
        'action' => route('news.update', $news),
        'submitLabel' => 'Update News',
    ])
@endsection
