@extends('layouts.theme')

@section('title', 'Create Programme')

@section('content')
    <div class="program-form-shell">
        <div class="box-typical box-typical-dashboard panel panel-default program-form-card">
            <header class="box-typical-header panel-heading d-flex justify-content-between">
                <div>
                    <h3 class="panel-title mb-0">Create Programme</h3>
                    <!-- <small class="text-muted">Set up programme pricing, discounts, outline file, and admission visibility.</small> -->
                </div>
                <!-- <a href="{{ route('program.index') }}" class="btn btn-default">Back to Programmes</a> -->
            </header>
            <div class="box-typical-body panel-body">
                <form method="POST" action="{{ route('program.store') }}" enctype="multipart/form-data">
                    @csrf
                    @include('program.partials.form')

                     <div  class="form-actions mb-2 mt-3 text-right">
							<!-- <button type="submit" class="btn btn-primary">Create Lead</button> -->
							<button type="submit" class="btn btn-inline btn-primary-outline " style="padding: 0.4rem;"> Create Programme</button>

							<a href="{{ url()->previous() }}" class="btn btn-inline btn-danger-outline" style="padding: 0.4rem; ">Cancel</a>
						</div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .program-form-shell {
            /* padding: 10px; */
        }

        .program-form-card {
            /* max-width: 1250px; */
            margin: 0 auto;
            overflow-x: hidden;
        }

        .program-form-card form .form-row,
        .program-form-card form .row {
            margin-left: 0 !important;
            margin-right: 0 !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
        }

        .program-form-card form .form-row > .col,
        .program-form-card form .form-row > [class*=col-],
        .program-form-card form .row > .col,
        .program-form-card form .row > [class*=col-] {
            padding-left: 10px !important;
            padding-right: 10px !important;
        }

        .program-form-card form > .form-group,
        .program-form-card form > .text-right,
        .program-form-card form > .form-actions {
            padding-left: 10px !important;
            padding-right: 10px !important;
        }

        .required::after {
            content: ' *';
            color: #e53935;
        }

        .program-discount-header {
            display: flex;
            justify-content: space-between;
            align-items: left;
            gap: 12px;
            padding: 10px 27px;
            margin-bottom: 14px;
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
        }

        .program-discount-row {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            margin-bottom: 12px;
            padding: 14px;
            border: 1px solid #dbe5f1;
            border-radius: 10px;
            background: #f8fbff;
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
        }

        .program-discount-col {
            flex: 1 1 220px;
            min-width: 0;
        }

        .program-discount-action {
            display: flex;
            align-items: end;
        }

        .program-discount-col .form-control {
            width: 100%;
            max-width: 100%;
        }

        @media (max-width: 767px) {
            .program-form-card {
                overflow-x: hidden;
            }

            .program-discount-header {
                flex-wrap: wrap;
                padding: 10px 14px;
            }

            .program-discount-row {
                gap: 10px;
            }

            .program-discount-col,
            .program-discount-action {
                flex: 1 1 100%;
                width: 100%;
            }
        }
    </style>
@endpush

@push('scripts')
    @include('program.partials.discount-script')
@endpush
