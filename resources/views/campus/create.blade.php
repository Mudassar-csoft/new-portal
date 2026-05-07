@extends('layouts.theme')

@section('title', 'Create Campus / Franchise')

@section('content')
    <div class="campus-form-shell">
        <div class="box-typical box-typical-dashboard panel panel-default campus-form-card">
            <header class="box-typical-header panel-heading d-flex justify-content-between">
                <div>
                    <h3 class="panel-title mb-0">Create Campus / Franchise</h3>
                    <!-- <small class="text-muted">Add a new company-owned campus or franchise branch with code, contact, and royalty setup.</small> -->
                </div>
                <!-- <a href="{{ route('campus.index') }}" class="btn btn-outline-primary">Back to Campuses</a> -->
            </header>
            <div class="box-typical-body panel-body">
                <form method="POST" action="{{ route('campus.store') }}">
                    @csrf
                    @include('campus.partials.form')

                     <div  class="form-actions mb-2 mt-3 text-right">
							<!-- <button type="submit" class="btn btn-primary">Create Lead</button> -->
							<button type="submit" class="btn btn-inline btn-primary-outline " style="padding: 0.4rem;"> Create Campus</button>

							<a href="{{ url()->previous() }}" class="btn btn-inline btn-danger-outline" style="padding: 0.4rem; ">Cancel</a>
						</div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .campus-form-shell {
            /* padding: 10px; */
        }

        .campus-form-card {
            /* max-width: 1250px; */
            margin: 0 auto;
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
    </style>
@endpush

@include('partials.country_city_script')

@push('scripts')
    @include('campus.partials.form-scripts')
@endpush
