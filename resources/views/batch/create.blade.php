@extends('layouts.theme')

@section('title', 'Create Batch')

@section('content')
    <div class="batch-form-shell">
        <div class="box-typical box-typical-dashboard panel panel-default batch-form-card">
            <header class="box-typical-header panel-heading d-flex justify-content-between">
                <div>
                    <h3 class="panel-title mb-0">Create Batch    <span class="">(All fields marked with * are required)</span></h3>
                 
                    <!-- <small class="text-mu
                     ted">Create a new batch with campus, program, timing, and instructor details.</small> -->
                </div>
                <!-- <div class="d-flex" style="gap:10px;">
                    <a href="{{ route('batch.index') }}" class="btn btn-default">Back to Batches</a>
                    <a href="{{ route('batch.timetable.index') }}" class="btn btn-primary">Manage Time Table</a>
                </div> -->
            </header>
            <div class="box-typical-body panel-body">
                <form method="POST" action="{{ route('batch.store') }}">
                    @csrf
                    @include('batch.partials.form')

                    <div  class="form-actions mb-2 mt-3 text-right">
							<!-- <button type="submit" class="btn btn-primary">Create Lead</button> -->
							<button type="submit" class="btn btn-inline btn-primary-outline " style="padding: 0.4rem;"> Create Batch</button>

							<a href="{{ url()->previous() }}" class="btn btn-inline btn-danger-outline" style="padding: 0.4rem; ">Cancel</a>
						</div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .batch-form-shell {
            padding: 10px;
        }

        .batch-form-card {
            /* max-width: 1250px; */
            margin: 0 auto;
        }

        .required::after {
            content: ' *';
            color: #e53935;
        }

        .batch-form-card input[name="session"][type="radio"] {
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

        .batch-form-card input[name="session"][type="radio"]:checked {
            border-color: #00a8ff;
        }

        .batch-form-card input[name="session"][type="radio"]:checked::before {
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

            document.addEventListener('DOMContentLoaded', function () {
                var programSelect = document.getElementById('batch-program');
                var startDate = document.getElementById('batch-start-date');

                if (programSelect) {
                    programSelect.addEventListener('change', updateBatchCode);
                }

                if (startDate) {
                    startDate.addEventListener('change', updateBatchCode);
                }

                updateBatchCode();
            });
        })();
    </script>
@endpush
