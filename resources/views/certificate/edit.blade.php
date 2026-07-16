@extends('layouts.theme')

@section('title', 'Edit Certificate')

@section('content')
    <div class="lead-shell">
        <div id="certificate-form-loader" class="lead-loader">
            <div class="lead-spinner">
                <div class="dot"></div>
                <div class="dot"></div>
                <div class="dot"></div>
            </div>
            <p>Loading certificate...</p>
        </div>

        <div id="certificate-form-content" class="lead-content">
            <div class="box-typical box-typical-dashboard panel panel-default lead-create-card">
                <header class="box-typical-header panel-heading lead-header">
                    <div class="tbl w-100">
                        <div class="tbl-row">
                            <div class="tbl-cell tbl-cell-title p-0 m-0">
                                <h2 class="panel-title lead-title">
                                    Certificate <strong>{{ 'CERT-ADM-' . str_pad((string) $admission->id, 6, '0', STR_PAD_LEFT) }}</strong>
                                </h2>
                            </div>
                            <div class="tbl-cell text-right" style="width: 200px;">
                                <a href="{{ route('certificate.index') }}" class="btn btn-inline btn-default ci-inline-pad-04 ci-inline-pl-10">Back</a>
                            </div>
                        </div>
                    </div>
                </header>

                <div class="box-typical-body panel-body lead-body">
                    <div class="cert-summary">
                        <div><span class="label-mute">Student:</span> <strong>{{ $admission->student_name ?? 'N/A' }}</strong></div>
                        <div><span class="label-mute">Roll No:</span> {{ $admission->roll_number ?? 'N/A' }}</div>
                        <div><span class="label-mute">Programme:</span> {{ $admission->program?->title ?? $admission->program?->name ?? 'N/A' }}</div>
                        <div><span class="label-mute">Campus:</span> {{ $admission->campus?->code ?? $admission->campus?->name ?? 'N/A' }}</div>
                        <div><span class="label-mute">Status:</span> <span class="label {{ $statusClasses[$admission->certificate_status] ?? 'label-default' }}">{{ $statusLabels[$admission->certificate_status] ?? ucfirst((string) $admission->certificate_status) }}</span></div>
                        <div><span class="label-mute">Status Updated:</span> {{ optional($admission->status_updated_at)->format('d-M-Y H:i') ?? 'N/A' }}</div>
                        @if($admission->certificate_delivered_at)
                            <div><span class="label-mute">Delivered:</span> {{ $admission->certificate_delivered_at->format('d-M-Y H:i') }}</div>
                        @endif
                        @if($admission->certificate_delivery_notes)
                            <div><span class="label-mute">Delivery Notes:</span> {{ $admission->certificate_delivery_notes }}</div>
                        @endif
                    </div>

        @include('partials.validation-errors-alert')

                    <form method="POST" action="{{ route('certificate.update', $admission) }}">
                        @csrf
                        @method('PUT')
                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <label class="form-label">Remarks</label>
                                <textarea name="remarks" class="form-control" rows="3" placeholder="Notes about this certificate">{{ old('remarks', $admission->remarks) }}</textarea>
                            </div>
                        </div>

                        <div class="form-actions mb-2 mt-3 text-right mr-0">
                            <button type="submit" class="btn btn-inline btn-primary-outline ci-inline-pad-04 ci-inline-pl-10">Save Remarks</button>
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
            --dimension-certificate-edit-1: 100vh;
            --dimension-certificate-edit-2: 12px;
            --space-certificate-edit-1: 12px;
            --color-certificate-edit-1: #54667a;
            --typo-certificate-edit-font-weight-1: 600;
        }

        .lead-shell { font-family: 'Proxima Nova', sans-serif; position: relative; min-height: var(--dimension-certificate-edit-1); width: 100%; overflow: visible; padding: 0; margin: 0; }
        .lead-loader { position: absolute; top: 0; left: 0; right: 0; height: var(--dimension-certificate-edit-1); background: rgba(245,247,251,0.95); display: flex; align-items: center; justify-content: center; flex-direction: column; z-index: 10; gap: var(--space-certificate-edit-1); }
        .lead-spinner { display: inline-flex; align-items: center; gap: 8px; }
        .lead-spinner .dot { width: var(--dimension-certificate-edit-2); height: var(--dimension-certificate-edit-2); border-radius: 50%; background: #12a0ff; animation: bounce 0.9s ease-in-out infinite; }
        .lead-spinner .dot:nth-child(2) { animation-delay: 0.15s; background: #1f8ef1; }
        .lead-spinner .dot:nth-child(3) { animation-delay: 0.3s; background: #36b1ff; }
        .lead-loader p { margin: 0; color: var(--color-certificate-edit-1); font-weight: var(--typo-certificate-edit-font-weight-1); }
        @keyframes bounce { 0%, 80%, 100% { transform: translateY(0); opacity: 0.6; } 40% { transform: translateY(-12px); opacity: 1; } }
        .lead-content { opacity: 0; visibility: hidden; transition: opacity 0.4s ease; position: relative; min-height: 400px; }
        body.cert-form-ready .lead-content { opacity: 1; visibility: visible; }
        body.cert-form-ready #certificate-form-loader { display: none; }

        .lead-create-card { overflow: visible !important; max-height: none !important; }
        .lead-create-card .panel-heading { padding: 10px 20px; }
        .lead-body { padding: 14px 18px; overflow: visible !important; }
        .lead-title { font-size: 1.125rem; font-weight: 500; color: #1f2937; line-height: 1.4; }

        .cert-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 8px 18px;
            padding: 12px 14px;
            margin-bottom: 14px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: #f8fbff;
            font-size: 0.8125rem;
        }
        .label-mute { color: var(--color-certificate-edit-1); font-weight: var(--typo-certificate-edit-font-weight-1); margin-right: 4px; }
        .tbl-row { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: var(--space-certificate-edit-1); }
        .tbl-cell.text-right { flex: 0 0 auto; text-align: right; }
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
