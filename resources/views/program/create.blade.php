@extends('layouts.theme')

@section('title', 'Create Programme')

@section('content')
    <div class="program-shell">
        <div class="box-typical box-typical-dashboard panel panel-default program-create-card">
            <header class="box-typical-header panel-heading program-header">
                <div class="tbl w-100">
                    <div class="tbl-row">
                        <div class="tbl-cell tbl-cell-title p-0 m-0">
                            <h2 class="panel-title program-title">
                                Create Programme <span class="ml-2">(All fields marked with <span class="text-danger semibold">*</span> are required)</span>
                            </h2>
                        </div>
                    </div>
                </div>
                <!-- <a href="{{ route('program.index') }}" class="btn btn-default">Back to Programmes</a> -->
            </header>
            <div class="box-typical-body panel-body program-body">
                <form method="POST" action="{{ route('program.store') }}" enctype="multipart/form-data">
                    @csrf
                    @include('program.partials.form')

                    <div class="form-actions mb-2 mt-3 text-right mr-0">
                        <button type="submit" class="btn btn-inline btn-primary-outline" style="padding: 0.4rem; padding-left:10px; margin-left:5px">Create</button>
                        <a href="{{ url()->previous() }}" class="btn btn-inline btn-danger-outline" style="padding: 0.4rem; padding-left:10px;">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .program-shell {
            font-family: 'Proxima Nova', sans-serif;
            position: relative;
            min-height: 100vh;
            width: 100%;
            overflow: visible;
            padding: 0;
            margin: 0;
        }

        .program-create-card {
            overflow: visible !important;
            max-height: none !important;
        }

        .program-create-card .panel-heading {
            padding: 10px 20px;
        }

        .program-body {
            padding: 10px 10px 5px;
            overflow: visible !important;
        }

        .program-title {
            font-size: 18px;
            font-weight: 500;
            color: #1f2937;
            line-height: 1.4;
        }

        .program-title span {
            font-size: 14px;
            font-weight: 400;
            color: #1f2937;
        }

        .program-create-card .form-row {
            padding: 3px 10px;
        }

        .program-create-card .form-group {
            margin-bottom: 8px;
        }

        .program-create-card .form-row > .form-group.col-lg-3,
        .program-create-card .form-row > .form-group.col-lg-4 {
            flex: 0 0 25%;
            max-width: 25%;
        }

        .program-create-card label,
        .program-create-card .form-label {
            color: #343434;
            font-size: 12px;
            font-weight: 600;
            line-height: 1.2;
            margin-bottom: 6px;
        }

        .program-create-card .form-control,
        .program-create-card .form-control-file {
            font-size: 12px;
        }

        .program-create-card .form-control {
            height: 37px !important;
            min-height: 37px !important;
            padding: 0.375rem 0.625rem !important;
            border: 1px solid #ccc;
            border-radius: 0.25rem;
            color: #343434;
        }

        .program-create-card .program-code-field[readonly] {
            background: #f4f8fb !important;
            color: #566a7f;
            cursor: not-allowed;
        }

        .program-create-card textarea.form-control {
            height: 82px !important;
            min-height: 82px !important;
            resize: vertical;
        }

        .program-create-card .alert {
            margin: 6px 10px 10px;
        }

        .required::after {
            content: ' *';
            color: #e53935;
        }

        .program-discount-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding: 5px 10px;
            margin: 4px 10px 8px;
        }

        .program-discount-header h4 {
            font-size: 14px;
            font-weight: 600;
            color: #343434;
        }

        .program-discount-row {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin: 0 10px 10px;
            padding: 10px;
            border: 1px solid #dbe5f1;
            border-radius: 6px;
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

        .program-upload {
            display: flex !important;
            align-items: center;
            gap: 10px;
            width: 100%;
            min-height: 37px;
            padding: 6px 10px;
            margin: 0;
            border: 1px dashed #b8d7ea;
            border-radius: 7px;
            background: linear-gradient(180deg, #fbfdff 0%, #f3faff 100%);
            color: #566a7f !important;
            cursor: pointer;
            transition: border-color 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
        }

        .program-upload:hover,
        .program-upload:focus-within {
            border-color: #00a8ff;
            box-shadow: 0 0 0 3px rgba(0, 168, 255, 0.10);
            background: #f8fcff;
        }

        .program-upload input[type="file"] {
            position: absolute;
            width: 1px;
            height: 1px;
            opacity: 0;
            pointer-events: none;
        }

        .program-upload-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 25px;
            min-width: 25px;
            height: 25px;
            border-radius: 999px;
            background: rgba(0, 168, 255, 0.12);
            color: #00a8ff;
            font-size: 13px;
        }

        .program-upload-copy {
            display: flex;
            flex-direction: column;
            min-width: 0;
            line-height: 1.15;
        }

        .program-upload-title {
            max-width: 180px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            color: #343434;
            font-size: 12px;
            font-weight: 600;
        }

        .program-upload-hint {
            margin-top: 2px;
            color: #8a99a8;
            font-size: 9px;
            font-weight: 500;
        }

        .program-discount-action .btn,
        #add-program-discount {
            font-size: 12px;
            padding: 0.35rem 0.65rem;
        }

        .program-create-card hr {
            margin: 8px 10px;
        }

        @media (max-width: 1199px) {
            .program-create-card .form-row > .form-group.col-lg-3,
            .program-create-card .form-row > .form-group.col-lg-4 {
                flex: 0 0 50%;
                max-width: 50%;
            }
        }

        @media (max-width: 767px) {
            .program-create-card .form-row > .form-group.col-lg-3,
            .program-create-card .form-row > .form-group.col-lg-4 {
                flex: 0 0 100%;
                max-width: 100%;
            }
        }
    </style>
@endpush

@push('scripts')
    @include('program.partials.discount-script')
    <script>
        (function () {
            var existingProgramCodes = new Set(@json($existingProgramCodes ?? []));

            function cleanWord(value) {
                return String(value || '').replace(/[^a-z0-9]/gi, '').toUpperCase();
            }

            function isAvailable(code) {
                return code && !existingProgramCodes.has(code);
            }

            function makeProgramCode(title) {
                var words = String(title || '')
                    .trim()
                    .split(/\s+/)
                    .map(cleanWord)
                    .filter(Boolean);

                if (!words.length) {
                    return '';
                }

                if (words.length === 1) {
                    return words[0].slice(0, 2);
                }

                return words.map(function (word) {
                    return word.charAt(0);
                }).join('');
            }

            function makeUniqueProgramCode(title) {
                var baseCode = makeProgramCode(title);
                var compactTitle = cleanWord(title);

                if (!baseCode) {
                    return '';
                }

                if (isAvailable(baseCode)) {
                    return baseCode;
                }

                for (var length = 2; length <= Math.min(compactTitle.length, 6); length++) {
                    var candidate = compactTitle.slice(0, length);

                    if (isAvailable(candidate)) {
                        return candidate;
                    }
                }

                var counter = 2;
                while (!isAvailable(baseCode + counter)) {
                    counter++;
                }

                return baseCode + counter;
            }

            document.addEventListener('DOMContentLoaded', function () {
                var input = document.getElementById('program-outline-upload');
                var label = document.querySelector('[data-upload-label]');
                var titleInput = document.getElementById('program-title');
                var codeInput = document.getElementById('program-code');
                var codeTouched = codeInput ? (!!codeInput.value && !codeInput.readOnly) : false;

                if (input && label) {
                    input.addEventListener('change', function () {
                        label.textContent = this.files && this.files.length
                            ? this.files[0].name
                            : 'Choose outline file';
                    });
                }

                if (titleInput && codeInput) {
                    if (!codeInput.readOnly) {
                        codeInput.addEventListener('input', function () {
                            codeTouched = true;
                            codeInput.value = codeInput.value.toUpperCase();
                        });
                    }

                    titleInput.addEventListener('input', function () {
                        if (codeTouched) {
                            return;
                        }

                        codeInput.value = makeUniqueProgramCode(titleInput.value);
                    });

                    if (!codeInput.value && titleInput.value) {
                        codeInput.value = makeUniqueProgramCode(titleInput.value);
                    }
                }
            });
        })();
    </script>
@endpush
