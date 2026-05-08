@extends('layouts.theme')

@section('title', 'Edit Batch')

@section('content')
    <div class="batch-form-shell">
        <div class="box-typical box-typical-dashboard panel panel-default batch-form-card">
            <header class="box-typical-header panel-heading d-flex justify-content-between">
                <div>
                    <h3 class="panel-title mb-0">Edit Batch</h3>
                    <!-- <small class="text-muted">Update batch timing, instructor, status, and session details.</small> -->
                </div>
                <!-- <div class="d-flex" style="gap:10px;">
                    <a href="{{ route('batch.index') }}" class="btn btn-default">Back to Batches</a>
                    <a href="{{ route('batch.timetable.index', ['batch_id' => $batch->id]) }}" class="btn btn-primary">Batch Time Table</a>
                </div> -->
            </header>
            <div class="box-typical-body panel-body">
                <form method="POST" action="{{ route('batch.update', $batch) }}">
                    @csrf
                    @method('PUT')
                    @include('batch.partials.form')

                    <div class="text-right mt-3">
                        <button type="submit" class="btn btn-primary-outline">Update Batch</button>
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
            max-width: 1250px;
            margin: 0 auto;
        }

        .required::after {
            content: ' *';
            color: #e53935;
        }

        .session-radio-group {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            margin-top: 8px;
        }

        .session-radio {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border: 1px solid #d9e2ef;
            border-radius: 8px;
            background: #f8fbff;
            cursor: pointer;
        }

        .session-radio input {
            margin: 0;
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
                    return;
                }

                var date = new Date(dateValue);
                if (Number.isNaN(date.getTime())) {
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
