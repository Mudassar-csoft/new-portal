@extends('layouts.theme')

@section('title', 'Create Batch')

@section('content')
    <div class="lead-shell">
        <div id="batch-form-loader" class="lead-loader">
            <div class="lead-spinner">
                <div class="dot"></div>
                <div class="dot"></div>
                <div class="dot"></div>
            </div>
            <p>Preparing batch form...</p>
        </div>

        <div id="batch-form-content" class="lead-content">
            <div class="box-typical box-typical-dashboard panel panel-default lead-create-card">
                <header class="box-typical-header panel-heading lead-header">
                    <div class="tbl w-100">
                        <div class="tbl-row">
                            <div class="tbl-cell tbl-cell-title p-0 m-0">
                                <h2 class="panel-title lead-title">
                                    Create Batch <span class="ml-2">(All fields marked with <span class="text-danger semibold">*</span> are required)</span>
                                </h2>
                            </div>
                        </div>
                    </div>
                </header>

                <div class="box-typical-body panel-body lead-body">
                    <form method="POST" action="{{ route('batch.store') }}">
                        @csrf
                        @include('batch.partials.form')

                        <div class="form-actions mb-2 mt-3 text-right mr-0">
                            <button type="submit" class="btn btn-inline btn-primary-outline ci-inline-pad-04 ci-inline-pl-10 ci-inline-ml-5">Create Batch</button>
                            <a href="{{ url()->previous() }}" class="btn btn-inline btn-danger-outline ci-inline-pad-04 ci-inline-pl-10">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        :root {
            --typo-batch-create-font-weight-1: 600;
            --typo-batch-create-font-size-2: 12px;
        }

        .lead-shell {
            font-family: 'Proxima Nova', sans-serif;
            position: relative;
            min-height: 100vh;
            width: 100%;
            overflow: visible;
            padding: 0;
            margin: 0;
        }

        .lead-loader {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 100vh;
            background: rgba(245, 247, 251, 0.95);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            z-index: 10;
            gap: 12px;
        }

        .lead-spinner {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .lead-spinner .dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #12a0ff;
            animation: bounce 0.9s ease-in-out infinite;
        }

        .lead-spinner .dot:nth-child(2) { animation-delay: 0.15s; background: #1f8ef1; }
        .lead-spinner .dot:nth-child(3) { animation-delay: 0.3s;  background: #36b1ff; }

        .lead-loader p {
            margin: 0;
            color: #54667a;
            font-weight: var(--typo-batch-create-font-weight-1);
        }

        .lead-content {
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.4s ease;
            position: relative;
            min-height: 400px;
        }

        body.batch-form-ready .lead-content { opacity: 1; visibility: visible; }
        body.batch-form-ready #batch-form-loader { display: none; }

        @keyframes bounce {
            0%, 80%, 100% { transform: translateY(0); opacity: 0.6; }
            40% { transform: translateY(-12px); opacity: 1; }
        }

        .lead-create-card {
            overflow: visible !important;
            max-height: none !important;
        }

        .lead-create-card .panel-heading { padding: 10px 20px; }

        .lead-body {
            padding: 10px 10px 5px;
            overflow: visible !important;
        }

        .lead-title {
            font-size: 18px;
            font-weight: 500;
            color: #1f2937;
            line-height: 1.4;
        }

        .lead-title span {
            font-size: 14px;
            font-weight: 400;
            color: #1f2937;
        }

        .lead-create-card .form-row { padding: 3px 10px; }
        .lead-create-card .form-group { margin-bottom: 8px; }

        .lead-create-card label,
        .lead-create-card .form-label {
            color: #343434;
            font-size: var(--typo-batch-create-font-size-2);
            font-weight: var(--typo-batch-create-font-weight-1);
            line-height: 1.2;
            margin-bottom: 6px;
        }

        .lead-create-card .form-control {
            font-size: var(--typo-batch-create-font-size-2);
            height: 37px !important;
            min-height: 37px !important;
            padding: 0.375rem 0.625rem !important;
            border: 1px solid #ccc;
            border-radius: 0.25rem;
            color: #343434;
        }

        .lead-create-card textarea.form-control {
            height: 82px !important;
            min-height: 82px !important;
            resize: vertical;
        }

        .required::after {
            content: '*';
            color: #e53935;
            margin-left: 4px;
        }

        .lead-create-card input[name="session"][type="radio"] {
            margin: 0;
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
        }

        .lead-create-card input[name="session"][type="radio"]:checked { border-color: #00a8ff; }

        .lead-create-card input[name="session"][type="radio"]:checked::before {
            content: '';
            position: absolute;
            top: 2px;
            left: 2px;
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background-color: #00a8ff;
        }
    </style>
@endpush

@push('scripts')
    <script>
        (function () {
            function updateBatchCode() {
                var programSelect = document.getElementById('batch-program');
                var startDate = document.getElementById('batch-start-date');
                var previewField = document.getElementById('batch-code-preview');

                if (!programSelect || !startDate || !previewField) {
                    return;
                }

                var selectedOption = programSelect.selectedOptions[0];
                var programCode = selectedOption ? selectedOption.getAttribute('data-code') : '';
                var dateValue = startDate.value;

                if (!programCode || !dateValue) {
                    previewField.value = '';
                    return;
                }

                var date = new Date(dateValue);
                if (Number.isNaN(date.getTime())) {
                    previewField.value = '';
                    return;
                }

                var month = String(date.getMonth() + 1).padStart(2, '0');
                var year = String(date.getFullYear()).slice(-2);

                previewField.value = programCode.toUpperCase() + month + '-' + year;
            }

            function revealBatchFormPage() {
                setTimeout(function () {
                    document.body.classList.add('batch-form-ready');
                }, 200);
            }

            document.addEventListener('DOMContentLoaded', function () {
                revealBatchFormPage();

                var programSelect = document.getElementById('batch-program');
                var startDate = document.getElementById('batch-start-date');

                if (programSelect) programSelect.addEventListener('change', updateBatchCode);
                if (startDate) startDate.addEventListener('change', updateBatchCode);

                updateBatchCode();
            });
        })();
    </script>
@endpush
