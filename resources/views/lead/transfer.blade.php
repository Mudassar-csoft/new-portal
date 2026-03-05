@extends('layouts.theme')

@section('title', 'Transfer Lead')

@section('content')
    <div class="lead-shell">
        <div id="lead-loader" class="lead-loader">
            <div class="lead-spinner">
                <div class="dot"></div>
                <div class="dot"></div>
                <div class="dot"></div>
            </div>
            <p>Preparing transfer form...</p>
        </div>

        <div id="lead-content" class="lead-content">
            <div class="box-typical box-typical-dashboard panel panel-default lead-create-card">
                <header class="box-typical-header panel-heading lead-header">
                    <h2 class="panel-title lead-title form-label">
                        Transfer Lead
                        <small class="text-muted ml-2">{{ $lead->name ?? 'Lead' }}</small>
                    </h2>
                </header>

                <div class="box-typical-body panel-body lead-body">
                    @if(empty($lead))
                        <p class="text-danger mb-0">Lead not found.</p>
                    @else
                        @if(session('status'))
                            <div class="alert alert-success">{{ session('status') }}</div>
                        @endif

                        @if($errors->any())
                            <div class="alert alert-danger">{{ $errors->first() }}</div>
                        @endif

                        <form method="POST" action="{{ route('leads.transfer.store', $lead) }}">
                            @csrf
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>Current Campus</label>
                                    <input type="text" class="form-control" value="{{ $lead->campus->name ?? 'N/A' }}" disabled>
                                </div>
                                <div class="form-group col-md-6">
                                    <label class="required">Transfer To</label>
                                    <select class="form-control @error('to_campus_id') is-invalid @enderror" name="to_campus_id" required>
                                        <option value="">- Select campus -</option>
                                        @foreach($campuses as $campus)
                                            <option value="{{ $campus->id }}"
                                                {{ (int) old('to_campus_id') === (int) $campus->id ? 'selected' : '' }}
                                                {{ $campus->id === $lead->campus_id ? 'disabled' : '' }}>
                                                {{ $campus->name }} ({{ $campus->code }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('to_campus_id')
                                        <div class="field-error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-12">
                                    <label>Reason / Note</label>
                                    <textarea class="form-control @error('reason') is-invalid @enderror" name="reason" rows="4" placeholder="Why is this lead being transferred?">{{ old('reason') }}</textarea>
                                    @error('reason')
                                        <div class="field-error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-actions text-right mt-2">
                                <button type="submit" class="btn btn-inline btn-primary-outline">Submit Transfer</button>
                                <a href="{{ route('leads.show', $lead) }}" class="btn btn-inline btn-danger-outline">Cancel</a>
                            </div>
                        </form>
                    @endif
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
            overflow: hidden;
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

        body.transfer-form-ready .lead-content {
            opacity: 1;
            visibility: visible;
        }

        body.transfer-form-ready #lead-loader {
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

        .lead-body {
            padding: 10px 10px 5px;
            overflow: visible !important;
        }

        .lead-create-card {
            overflow: visible !important;
            max-height: none !important;
        }

        .lead-create-card .panel-heading {
            padding: 10px 20px;
        }

        .lead-title {
            font-size: 18px;
            font-weight: 500;
        }

        .required::after {
            content: '*';
            color: #e53935;
            margin-left: 4px;
        }

        .field-error {
            color: #e53935;
            font-size: 12px;
            margin-top: 4px;
        }

        .form-control.is-invalid {
            border-color: #e53935;
            box-shadow: 0 0 0 2px rgba(229, 57, 53, 0.12);
        }

        .form-row {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 12px;
        }

        .form-row .form-group {
            margin-bottom: 0;
            flex: 1 1 48%;
            min-width: 260px;
        }

        .form-row .form-group.col-md-12 {
            flex-basis: 100%;
        }
    </style>
@endpush

@push('scripts')
    <script>
        (function () {
            document.addEventListener('DOMContentLoaded', function () {
                setTimeout(function () {
                    document.body.classList.add('transfer-form-ready');
                }, 150);
            });
        })();
    </script>
@endpush
