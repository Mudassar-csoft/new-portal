@extends('layouts.theme')

@section('title', 'Create Campus / Franchise')

@section('content')
    <div class="campus-shell">
        <div class="campus-content">
            <div class="box-typical box-typical-dashboard panel panel-default campus-create-card">
                <header class="box-typical-header panel-heading campus-header">
                    <div class="tbl w-100">
                        <div class="tbl-row">
                            <div class="tbl-cell tbl-cell-title p-0 m-0">
                                <h2 class="panel-title campus-title">Create New Campus / Franchise <span class="ml-2">(All fields marked with <span class="text-danger semibold">*</span> are required)</span></h2>
                            </div>
                        </div>
                    </div>
                </header>
                <div class="box-typical-body panel-body campus-body">
                    <form method="POST" action="{{ route('campus.store') }}">
                        @csrf
                        @include('campus.partials.form')

                        <div class="form-actions mb-2 mt-3 text-right">
                            <button type="submit" class="btn btn-inline btn-primary-outline" style="padding: 0.4rem;">Create Campus</button>
                            <a href="{{ url()->previous() }}" class="btn btn-inline btn-danger-outline" style="padding: 0.4rem;">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .campus-shell {
            font-family: 'Proxima Nova', sans-serif;
            position: relative;
            min-height: 100vh;
            width: 100%;
            overflow: hidden;
            padding: 0;
            margin: 0;
        }

        .campus-content {
            position: relative;
            min-height: 400px;
        }

        .campus-create-card {
            overflow: visible !important;
            max-height: none !important;
        }

        .campus-create-card .panel-heading {
            padding: 10px 20px;
        }

        .campus-create-card .panel-body {
            max-height: none !important;
            overflow: visible !important;
        }

        .campus-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .campus-title {
            font-size: 18px;
            font-weight: 500;
            margin: 0;
        }

        .campus-title > span {
            font-size: 14px;
            font-weight: 400;
        }

        .campus-body {
            padding: 10px 10px 5px;
            overflow: visible !important;
        }

        .required::after {
            content: ' *';
            color: #e53935;
        }

        .campus-type-options {
            margin-top: 8px;
        }

        .campus-type-option {
            display: inline-flex;
            align-items: center;
            gap: 0;
            cursor: pointer;
            padding: 0;
            background: transparent;
            border: 0;
        }

        .campus-type-option .form-check-input {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            width: 14px;
            height: 14px !important;
            border: 2px solid grey;
            border-radius: 50%;
            outline: none;
            cursor: pointer;
            position: relative;
            background-color: #fff;
            transition: background 0.2s, box-shadow 0.2s;
            margin: 0;
        }

        .campus-type-option .form-check-input:checked {
            border-color: #00a8ff;
        }

        .campus-type-option .form-check-input:checked::before {
            content: '';
            position: absolute;
            top: 2px;
            left: 2px;
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background-color: #00a8ff;
        }

        .campus-type-option .form-check-label {
            font-size: .75rem;
            margin-bottom: 0;
            cursor: pointer;
            color: #343434;
        }

        .campus-body .alert {
            margin: 8px 12px 12px;
        }

        .campus-body textarea.form-control {
            resize: vertical;
        }
    </style>
@endpush

@include('partials.country_city_script')

@push('scripts')
    @include('campus.partials.form-scripts')
@endpush
