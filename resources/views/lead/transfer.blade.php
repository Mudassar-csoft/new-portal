@extends(request()->boolean('embed') ? 'layouts.embed' : 'layouts.theme')

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

                        <form method="POST" action="{{ route('leads.transfer.store', $lead) }}" id="lead-transfer-form" class="lead-transfer-form">
                            @csrf
                            @if(request()->boolean('embed'))
                                <input type="hidden" name="embed" value="1">
                            @endif
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
                                @if(request()->boolean('embed'))
                                    <button type="button" class="btn btn-inline btn-danger-outline embed-cancel">Cancel</button>
                                @else
                                    <a href="{{ route('leads.show', $lead) }}" class="btn btn-inline btn-danger-outline">Cancel</a>
                                @endif
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
            padding: 18px 0 24px;
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
            min-height: 260px;
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
            padding: 24px 26px 22px;
            overflow: visible !important;
        }

        .lead-create-card {
            overflow: visible !important;
            max-height: none !important;
            border: 1px solid #e3edf7;
            border-radius: 24px;
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.08);
            overflow: hidden !important;
            background: #fff;
        }

        .lead-create-card .panel-heading {
            padding: 18px 24px;
            border-bottom: 1px solid #e8eef5;
            background: linear-gradient(180deg, #fbfdff 0%, #f5f9ff 100%);
        }

        .lead-title {
            font-size: 28px;
            font-weight: 800;
            line-height: 1.2;
            color: #183b68;
        }

        .lead-title small {
            font-size: 16px;
            font-weight: 600;
            color: #70839a !important;
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

        .lead-transfer-form .form-row {
            margin-left: -10px;
            margin-right: -10px;
        }

        .lead-transfer-form .form-row > [class*="col-"] {
            padding-left: 10px;
            padding-right: 10px;
        }

        .lead-transfer-form .form-group {
            margin-bottom: 18px;
        }

        .lead-transfer-form label {
            display: inline-block;
            margin-bottom: 8px;
            font-weight: 700;
            color: #223a57;
        }

        .lead-transfer-form .form-control {
            min-height: 46px;
            border-radius: 12px;
            border: 1px solid #d6e2f0;
            padding: 10px 14px;
            background: #fff;
            box-shadow: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .lead-transfer-form textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }

        .lead-transfer-form .form-control:focus {
            border-color: #14a2f6;
            box-shadow: 0 0 0 3px rgba(20, 162, 246, 0.12);
        }

        .lead-transfer-form .form-control[disabled] {
            background: #f4f8fc;
            color: #5f7289;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            padding-top: 18px;
            margin-top: 8px;
            border-top: 1px solid #e8eef5;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.68) 0%, #fff 28%);
        }

        .form-actions .btn {
            min-width: 170px;
            height: 44px;
            border-radius: 12px;
            font-weight: 700;
        }

        .btn.btn-primary-outline {
            color: #fff;
            background: linear-gradient(120deg, #0099f8, #17b3ff);
            border-color: transparent;
            box-shadow: 0 14px 28px rgba(0, 153, 248, 0.24);
        }

        .btn.btn-primary-outline:hover,
        .btn.btn-primary-outline:focus {
            color: #fff;
            background: linear-gradient(120deg, #0088dd, #0ea4ef);
            border-color: transparent;
        }

        .btn.btn-danger-outline {
            color: #d64545;
            border: 1px solid rgba(214, 69, 69, 0.32);
            background: #fff;
        }

        .btn.btn-danger-outline:hover,
        .btn.btn-danger-outline:focus {
            color: #fff;
            background: #dc3545;
            border-color: #dc3545;
        }

        @media (max-width: 767px) {
            .lead-shell {
                padding: 16px;
            }

            .lead-body {
                padding: 20px 18px 18px;
            }

            .lead-title {
                font-size: 24px;
            }

            .lead-title small {
                display: block;
                margin-top: 6px;
                margin-left: 0 !important;
            }

            .form-actions {
                flex-direction: column-reverse;
            }

            .form-actions .btn {
                width: 100%;
            }
        }

        @if(request()->boolean('embed'))
            .lead-shell {
                min-height: auto;
                padding: 18px;
            }

            .lead-create-card {
                box-shadow: none;
                border-radius: 20px;
            }

            .lead-loader {
                height: 100%;
            }
        @endif
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
    @if(request()->boolean('embed'))
        <script>
            (function () {
                function clearErrors(form) {
                    form.querySelectorAll('.field-error').forEach(function (node) {
                        node.textContent = '';
                    });

                    form.querySelectorAll('.is-invalid').forEach(function (field) {
                        field.classList.remove('is-invalid');
                    });
                }

                function renderErrors(form, errors) {
                    Object.entries(errors || {}).forEach(function (entry) {
                        var key = entry[0];
                        var messages = entry[1] || [];
                        var message = messages.length ? messages[0] : 'Invalid value.';
                        var field = form.querySelector('[name="' + key + '"]');

                        if (field) {
                            field.classList.add('is-invalid');
                            var formGroup = field.closest('.form-group');
                            var errorNode = formGroup ? formGroup.querySelector('.field-error') : null;
                            if (errorNode) {
                                errorNode.textContent = message;
                            }
                        }
                    });
                }

                document.addEventListener('DOMContentLoaded', function () {
                    var form = document.getElementById('lead-transfer-form');

                    document.querySelectorAll('.embed-cancel').forEach(function (button) {
                        button.addEventListener('click', function () {
                            if (window.parent) {
                                window.parent.postMessage({ type: 'lead-modal-close' }, '*');
                            }
                        });
                    });

                    if (!form) {
                        return;
                    }

                    form.addEventListener('submit', async function (event) {
                        event.preventDefault();
                        clearErrors(form);

                        try {
                            var response = await fetch(form.action, {
                                method: 'POST',
                                headers: {
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'X-CSRF-TOKEN': form.querySelector('[name="_token"]')?.value || ''
                                },
                                credentials: 'same-origin',
                                body: new FormData(form)
                            });

                            if (response.status === 422) {
                                var data = await response.json();
                                renderErrors(form, data.errors || {});

                                if (window.swal) {
                                    swal({
                                        title: 'Error',
                                        text: data.message || 'Please fix the highlighted fields and try again.',
                                        type: 'error'
                                    });
                                }

                                return;
                            }

                            var responseData = {};
                            var contentType = response.headers.get('content-type') || '';
                            if (contentType.indexOf('application/json') !== -1) {
                                responseData = await response.json();
                            }

                            if (!response.ok) {
                                throw new Error(responseData.message || 'Unable to submit transfer request.');
                            }

                            if (window.parent) {
                                window.parent.postMessage({
                                    type: 'lead-modal-close',
                                    reload: true,
                                    status: responseData.status || 'Transfer request submitted for approval.'
                                }, '*');
                            }
                        } catch (error) {
                            if (window.swal) {
                                swal({
                                    title: 'Error',
                                    text: error.message || 'Unable to submit transfer request.',
                                    type: 'error'
                                });
                            }
                        }
                    });
                });
            })();
        </script>
    @endif
@endpush
