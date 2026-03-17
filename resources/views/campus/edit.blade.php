@extends('layouts.theme')

@section('title', 'Edit Campus / Franchise')

@section('content')
    <div class="campus-form-shell">
        <div class="box-typical box-typical-dashboard panel panel-default campus-form-card">
            <header class="box-typical-header panel-heading d-flex align-items-center justify-content-between">
                <div>
                    <h3 class="panel-title mb-0">Edit Campus / Franchise</h3>
                    <small class="text-muted">Update campus location, contact details, status, and operational setup.</small>
                </div>
                <div class="d-flex" style="gap:10px;">
                    <a href="{{ route('campus.index') }}" class="btn btn-default">Back to Campuses</a>
                    <a href="{{ route('inventory.index', ['campus_id' => $campus->id]) }}" class="btn btn-primary">View Inventory</a>
                </div>
            </header>
            <div class="box-typical-body panel-body">
                <form method="POST" action="{{ route('campus.update', $campus) }}">
                    @csrf
                    @method('PUT')
                    @include('campus.partials.form')

                    <div class="text-right mt-3">
                        <button type="submit" class="btn btn-primary">Update Campus</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .campus-form-shell {
            padding: 10px;
        }

        .campus-form-card {
            max-width: 1250px;
            margin: 0 auto;
        }

        .required::after {
            content: ' *';
            color: #e53935;
        }

        .campus-type-options {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            margin-top: 8px;
        }

        .campus-type-option {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border: 1px solid #d9e2ef;
            border-radius: 8px;
            background: #fff8f3;
            cursor: pointer;
        }

        .campus-type-option input {
            margin: 0;
        }
    </style>
@endpush

@include('partials.country_city_script')

@push('scripts')
    @include('campus.partials.form-scripts')
@endpush
