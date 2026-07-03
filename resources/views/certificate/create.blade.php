@extends('layouts.theme')

@section('title', 'Request Certificate')

@section('content')
    <div class="lead-shell">
        <div id="certificate-form-loader" class="lead-loader">
            <div class="lead-spinner">
                <div class="dot"></div>
                <div class="dot"></div>
                <div class="dot"></div>
            </div>
            <p>Preparing certificate form...</p>
        </div>

        <div id="certificate-form-content" class="lead-content">
            <div class="box-typical box-typical-dashboard panel panel-default lead-create-card">
                <header class="box-typical-header panel-heading lead-header">
                    <div class="tbl w-100">
                        <div class="tbl-row">
                            <div class="tbl-cell tbl-cell-title p-0 m-0">
                                <h2 class="panel-title lead-title">
                                    Request New Certificate <span class="ml-2">(All fields marked with <span class="text-danger semibold">*</span> are required)</span>
                                </h2>
                            </div>
                        </div>
                    </div>
                </header>

                <div class="box-typical-body panel-body lead-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('certificate.store') }}">
                        @csrf
                        <div class="form-row mt-1">
                            <div class="form-group col-md-12">
                                <label class="form-label required">Student / Admission</label>
                                <select name="admission_id" class="form-control" required>
                                    <option value="">- Select admission -</option>
                                    @foreach($admissions as $a)
                                        <option value="{{ $a->id }}"
                                            @selected(old('admission_id', $selectedAdmission?->id) == $a->id)>
                                            {{ $a->student_name }}
                                            @if($a->roll_number) — Roll {{ $a->roll_number }} @endif
                                            @if($a->registration_number) — Reg {{ $a->registration_number }} @endif
                                            @if($a->program) ({{ $a->program->title ?? $a->program->name }}) @endif
                                            @if($a->campus) [{{ $a->campus->code ?? $a->campus->name }}] @endif
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Pick the student admission this certificate is being issued for.</small>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <label class="form-label">Remarks</label>
                                <textarea name="remarks" class="form-control" rows="3" placeholder="Optional notes (special instructions, special grading, etc.)">{{ old('remarks') }}</textarea>
                            </div>
                        </div>

                        <div class="form-actions mb-2 mt-3 text-right mr-0">
                            <button type="submit" class="btn btn-inline btn-primary-outline ci-inline-pad-04 ci-inline-pl-10 ci-inline-ml-5">Submit Request</button>
                            <a href="{{ route('certificate.index') }}" class="btn btn-inline btn-danger-outline ci-inline-pad-04 ci-inline-pl-10">Cancel</a>
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
            --typo-certificate-create-font-weight-1: 600;
        }

        .lead-shell { font-family: 'Proxima Nova', sans-serif; position: relative; min-height: 100vh; width: 100%; overflow: visible; padding: 0; margin: 0; }
        .lead-loader { position: absolute; top: 0; left: 0; right: 0; height: 100vh; background: rgba(245,247,251,0.95); display: flex; align-items: center; justify-content: center; flex-direction: column; z-index: 10; gap: 12px; }
        .lead-spinner { display: inline-flex; align-items: center; gap: 8px; }
        .lead-spinner .dot { width: 12px; height: 12px; border-radius: 50%; background: #12a0ff; animation: bounce 0.9s ease-in-out infinite; }
        .lead-spinner .dot:nth-child(2) { animation-delay: 0.15s; background: #1f8ef1; }
        .lead-spinner .dot:nth-child(3) { animation-delay: 0.3s; background: #36b1ff; }
        .lead-loader p { margin: 0; color: #54667a; font-weight: var(--typo-certificate-create-font-weight-1); }
        @keyframes bounce { 0%, 80%, 100% { transform: translateY(0); opacity: 0.6; } 40% { transform: translateY(-12px); opacity: 1; } }
        .lead-content { opacity: 0; visibility: hidden; transition: opacity 0.4s ease; position: relative; min-height: 400px; }
        body.cert-form-ready .lead-content { opacity: 1; visibility: visible; }
        body.cert-form-ready #certificate-form-loader { display: none; }

        .lead-create-card { overflow: visible !important; max-height: none !important; }
        .lead-create-card .panel-heading { padding: 10px 20px; }
        .lead-body { padding: 10px 10px 5px; overflow: visible !important; }
        .lead-title { font-size: 18px; font-weight: 500; color: #1f2937; line-height: 1.4; }
        .lead-title span { font-size: 14px; font-weight: 400; color: #1f2937; }

        .lead-create-card .form-row { padding: 3px 10px; }
        .lead-create-card .form-group { margin-bottom: 8px; }
        .lead-create-card label, .lead-create-card .form-label { color: #343434; font-size: 14px !important; font-weight: var(--typo-certificate-create-font-weight-1); line-height: 1.2; margin-bottom: 6px; }
        .lead-create-card .form-control { font-size: 12px; min-height: 37px !important; padding: 0.375rem 0.625rem !important; border: 1px solid #ccc; border-radius: 0.25rem; color: #343434; }
        .lead-create-card textarea.form-control { min-height: 82px !important; resize: vertical; }
        .required::after { content: '*'; color: #e53935; margin-left: 4px; }
    </style>
@endpush

@push('scripts')
    <script>
        (function () {
            document.addEventListener('DOMContentLoaded', function () {
                setTimeout(function () { document.body.classList.add('cert-form-ready'); }, 200);
            });
        })();
    </script>
@endpush
