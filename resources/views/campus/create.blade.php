@extends('layouts.theme')

@section('title', 'Create Campus / Franchise')

@section('content')
    <div class="lead-shell">
        <div id="campus-form-loader" class="lead-loader">
            <div class="lead-spinner">
                <div class="dot"></div>
                <div class="dot"></div>
                <div class="dot"></div>
            </div>
            <p>Preparing campus form...</p>
        </div>

        <div id="campus-form-content" class="lead-content">
            <div class="box-typical box-typical-dashboard panel panel-default lead-create-card">
                <header class="box-typical-header panel-heading lead-header">
                    <div class="tbl w-100">
                        <div class="tbl-row">
                            <div class="tbl-cell tbl-cell-title p-0 m-0">
                                <h2 class="panel-title lead-title">
                                    Create New Campus / Franchise <span class="ml-2">(All fields marked with <span class="text-danger semibold">*</span> are required)</span>
                                </h2>
                            </div>
                        </div>
                    </div>
                </header>

                <div class="box-typical-body panel-body lead-body">
                    <form method="POST" action="{{ route('campus.store') }}">
                        @csrf
                        @include('campus.partials.form')

                        <div class="form-actions mb-2 mt-3 text-right mr-0">
                            <button type="submit" class="btn btn-inline btn-primary-outline" style="padding: 0.4rem; padding-left:10px; margin-left:5px">Create Campus</button>
                            <a href="{{ url()->previous() }}" class="btn btn-inline btn-danger-outline" style="padding: 0.4rem; padding-left:10px;">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    @include('campus.partials.form-styles')
@endpush

@include('partials.country_city_script')

@push('scripts')
    @include('campus.partials.form-scripts')
@endpush
