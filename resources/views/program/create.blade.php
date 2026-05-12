@extends('layouts.theme')

@section('title', 'Create Programme')

@section('content')
    <div class="lead-shell">
        <div id="program-form-loader" class="lead-loader">
            <div class="lead-spinner">
                <div class="dot"></div>
                <div class="dot"></div>
                <div class="dot"></div>
            </div>
            <p>Preparing programme form...</p>
        </div>

        <div id="program-form-content" class="lead-content">
            <div class="box-typical box-typical-dashboard panel panel-default lead-create-card">
                <header class="box-typical-header panel-heading lead-header">
                    <div class="tbl w-100">
                        <div class="tbl-row">
                            <div class="tbl-cell tbl-cell-title p-0 m-0">
                                <h2 class="panel-title lead-title">
                                    Create Programme <span class="ml-2">(All fields marked with <span class="text-danger semibold">*</span> are required)</span>
                                </h2>
                            </div>
                        </div>
                    </div>
                </header>

                <div class="box-typical-body panel-body lead-body">
                    <form method="POST" action="{{ route('program.store') }}" enctype="multipart/form-data">
                        @csrf
                        @include('program.partials.form')

                        <div class="form-actions mb-2 mt-3 text-right mr-0">
                            <button type="submit" class="btn btn-inline btn-primary-outline" style="padding: 0.4rem; padding-left:10px; margin-left:5px">Create Programme</button>
                            <a href="{{ url()->previous() }}" class="btn btn-inline btn-danger-outline" style="padding: 0.4rem; padding-left:10px;">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
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

        .lead-spinner .dot:nth-child(2) {
            animation-delay: 0.15s;
            background: #1f8ef1;
        }

        .lead-spinner .dot:nth-child(3) {
            animation-delay: 0.3s;
            background: #36b1ff;
        }

        .lead-loader p {
            margin: 0;
            color: #54667a;
            font-weight: 600;
        }

        .lead-content {
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.4s ease;
            position: relative;
            min-height: 400px;
        }

        body.program-form-ready .lead-content {
            opacity: 1;
            visibility: visible;
        }

        body.program-form-ready #program-form-loader {
            display: none;
        }

        @keyframes bounce {
            0%, 80%, 100% {
                transform: translateY(0);
                opacity: 0.6;
            }
            40% {
                transform: translateY(-12px);
                opacity: 1;
            }
        }

        .lead-create-card {
            overflow: visible !important;
            max-height: none !important;
        }

        .lead-create-card .panel-heading {
            padding: 10px 20px;
        }

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

        .lead-create-card .form-row {
            padding: 3px 10px;
        }

        .lead-create-card .form-group {
            margin-bottom: 8px;
        }

        .lead-create-card .form-row > .form-group.col-lg-3,
        .lead-create-card .form-row > .form-group.col-lg-4 {
            flex: 0 0 25%;
            max-width: 25%;
        }

        .lead-create-card label,
        .lead-create-card .form-label {
            color: #343434;
            font-size: 12px;
            font-weight: 600;
            line-height: 1.2;
            margin-bottom: 6px;
        }

        .lead-create-card .form-control,
        .lead-create-card .form-control-file {
            font-size: 12px;
        }

        .lead-create-card .form-control {
            height: 37px !important;
            min-height: 37px !important;
            padding: 0.375rem 0.625rem !important;
            border: 1px solid #ccc;
            border-radius: 0.25rem;
            color: #343434;
        }

        .lead-create-card .program-code-field[readonly] {
            background: #f4f8fb !important;
            color: #566a7f;
            cursor: not-allowed;
        }

        .lead-create-card textarea.form-control {
            height: 82px !important;
            min-height: 82px !important;
            resize: vertical;
        }

        .lead-create-card .alert {
            margin: 6px 10px 10px;
        }

        .required::after {
            content: '*';
            color: #e53935;
            margin-left: 4px;
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
            width: auto;
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

        .lead-create-card hr {
            margin: 8px 10px;
        }

        @media (max-width: 1199px) {
            .lead-create-card .form-row > .form-group.col-lg-3,
            .lead-create-card .form-row > .form-group.col-lg-4 {
                flex: 0 0 50%;
                max-width: 50%;
            }
        }

        @media (max-width: 767px) {
            .lead-create-card .form-row > .form-group.col-lg-3,
            .lead-create-card .form-row > .form-group.col-lg-4 {
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

            function revealProgramFormPage() {
                setTimeout(function () {
                    document.body.classList.add('program-form-ready');
                }, 200);
            }

            document.addEventListener('DOMContentLoaded', function () {
                revealProgramFormPage();

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
