@extends('layouts.theme')

@section('title', 'Create Permission')

@section('content')
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Permission</title>

    <!-- Bootstrap CSS -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>
        /* ========== GLOBAL FIXES ========== */
        * {
            box-sizing: border-box;
        }

        body {
            background-color: #f1f4f7;
            overflow-x: hidden; /* FIX right cut issue */
            /* font-family: Arial, sans-serif; */
        }

        /* ========== WRAPPER ========== */
        .permission-wrapper {
            padding: 30px 15px;
        }

        /* ========== CARD ========== */
        .permission-card {
            border-radius: 6px;
            overflow: hidden;
        }

        .permission-card .card-header {
            background-color: #fff;
            border-bottom: 1px solid #e5e5e5;
        }

        .permission-card h5 {
            margin-bottom: 2px;
            font-weight: 600;
        }

        /* ========== FORM ========== */
        .form-control {
            width: 100%;
        }

        textarea {
            resize: none;
        }

        /* ========== BUTTONS ========== */
        .btn-group-custom {
            display: flex;
            justify-content: flex-end;
            flex-wrap: wrap;
            gap: 10px;
        }

        /* ========== RESPONSIVE ========== */
        @media (max-width: 768px) {
            .permission-wrapper {
                padding: 15px;
            }

            .card-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .card-header .btn {
                margin-top: 10px;
            }
        }

        @media (max-width: 576px) {
            .btn-group-custom button {
                width: 100%;
            }
        }
    </style>
</head>
<body>

<div class="container-fluid permission-wrapper">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10 col-sm-12">
            <div class="card permission-card">

                <!-- Header -->
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5>Create Permission</h5>
                        <small class="text-muted">
                            Define a permission for a resource and action.
                        </small>
                    </div>
                    <a href="{{ route('permissions.index') }}" class="btn btn-secondary btn-sm">Back</a>
                </div>

                <!-- Body -->
                <div class="card-body">
                    <form method="POST" action="{{ route('permissions.store') }}">
                        @csrf

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>
                                    Resource <span class="text-danger">*</span>
                                </label>
                                <input
                                    type="text"
                                    class="form-control @error('resource') is-invalid @enderror"
                                    name="resource"
                                    placeholder="lead"
                                    value="{{ old('resource') }}"
                                >
                                @error('resource')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group col-md-6">
                                <label>
                                    Action <span class="text-danger">*</span>
                                </label>
                                <input
                                    type="text"
                                    class="form-control @error('action') is-invalid @enderror"
                                    name="action"
                                    placeholder="view"
                                    value="{{ old('action') }}"
                                >
                                @error('action')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Description</label>
                            <textarea
                                class="form-control @error('description') is-invalid @enderror"
                                name="description"
                                rows="3"
                                placeholder="Optional description"
                            >{{ old('description') }}</textarea>
                            @error('description')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Buttons -->
                        <div class="btn-group-custom mt-4">
                            <button type="submit" class="btn btn-inline btn-primary-outline">
                                Create Permission
                            </button>
                            <button type="button" class="btn btn-inline btn-secondary-outline">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>

</body>
</html>

@endsection

@push('styles')
	<style>
		.required::after { content: '*'; color: #e74c3c; margin-left: 4px; }
	</style>
@endpush
