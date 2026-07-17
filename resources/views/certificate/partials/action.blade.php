@php
    $actionId = $actionId ?? ('cert-action-' . $cert->id);
    $status = $cert->certificate_status ?? 'requested';
    $user = auth()->user();
    $requestedScopeOnly = ($activeScope ?? null) === 'requested';
    $approvedScopeOnly = ($activeScope ?? null) === 'approved';
    $printingScopeOnly = ($activeScope ?? null) === 'printing';
    $readyScopeOnly = ($activeScope ?? null) === 'ready';
    $canEditRemarks = ($user?->isAdmin() ?? false) && ($user?->hasAnyPermission(['certificate.update']) ?? false);
    $canApprove = $user?->hasAnyPermission(['certificate.approve']) ?? false;
    $canReject = $user?->hasAnyPermission(['certificate.reject']) ?? false;
    $canSendToPrinting = $user?->hasAnyPermission(['certificate.send-to-printing']) ?? false;
    $canMarkReady = $user?->hasAnyPermission(['certificate.mark-ready']) ?? false;
    $canMarkDelivered = $user?->hasAnyPermission(['certificate.mark-delivered']) ?? false;
    $canPreview = $user?->hasAnyPermission(['certificate.view']) ?? false;
    $canDelete = $user?->hasAnyPermission(['certificate.delete']) ?? false;

    $showEditRemarks = ! $requestedScopeOnly && ! $approvedScopeOnly && ! $printingScopeOnly && ! $readyScopeOnly && $canEditRemarks;
    $showApprove = $status === 'requested' && $canApprove;
    $showReject = ! $approvedScopeOnly && in_array($status, ['requested', 'approved'], true) && $canReject;
    $showSendToPrinting = ! $requestedScopeOnly && $status === 'approved' && $canSendToPrinting;
    $showMarkReady = ! $requestedScopeOnly && $status === 'printing' && $canMarkReady;
    $showMarkDelivered = ! $requestedScopeOnly && $status === 'ready' && $canMarkDelivered;
    $showPreview = in_array($status, ['printing', 'ready', 'delivered'], true) && $canPreview;
    $showDelete = ! $requestedScopeOnly && ! $approvedScopeOnly && ! $printingScopeOnly && ! $readyScopeOnly && $status !== 'delivered' && $canDelete;
    $hasActions = $showEditRemarks || $showApprove || $showReject || $showSendToPrinting || $showMarkReady || $showMarkDelivered || $showPreview || $showDelete;
@endphp

@once
    @push('styles')
        <style>
        :root {
            --dimension-certificate-partials-action-1: 22px;
            --color-certificate-partials-action-1: #303740;
        }

            .follow-action-dropdown .dropdown-menu.lead-action-menu {
                min-width: 220px;
                padding: 1px 0;
                border: 1px solid #dfe5eb;
                border-radius: 6px;
                background: #fff;
                box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
                text-align: left !important;
            }
            .follow-action-dropdown .dropdown-item.lead-action-item,
            .follow-action-dropdown form button.dropdown-item.lead-action-item {
                display: flex !important; align-items: center; gap: 8px; width: 100%;
                text-align: left !important; padding: 6px 18px !important;
                color: var(--color-certificate-partials-action-1) !important; font-size: 0.9375rem !important; font-weight: 500;
                background: transparent !important; border: 0; transition: background-color 0.18s ease;
            }
            .follow-action-dropdown .dropdown-item.lead-action-item:hover,
            .follow-action-dropdown form button.dropdown-item.lead-action-item:hover {
                background: #f7fafc !important; text-decoration: none;
            }
            .follow-action-dropdown .lead-action-icon { width: var(--dimension-certificate-partials-action-1); min-width: var(--dimension-certificate-partials-action-1); height: var(--dimension-certificate-partials-action-1); display: inline-flex; align-items: center; justify-content: center; }
            .follow-action-dropdown .lead-action-icon.lead-icon-blue { color: #19b6e6; }
            .follow-action-dropdown .lead-action-icon.lead-icon-black { color: var(--color-certificate-partials-action-1); }
            .follow-action-dropdown .lead-action-icon.lead-icon-green { color: #2db853; }
            .follow-action-dropdown .lead-action-icon.lead-icon-yellow { color: #f5b400; }
            .follow-action-dropdown .lead-action-icon.lead-icon-red { color: #ef4e4e; }
            .follow-action-dropdown form { margin: 0; }
            .swal-delivery-input,
            .swal-delivery-textarea {
                display: block;
                width: 100%;
                margin: 0 0 10px;
                padding: 10px 12px;
                border: 1px solid #d0d7de;
                border-radius: 6px;
                font-size: 0.875rem;
                color: #1f2937;
                background: #fff;
            }
            .swal-delivery-textarea {
                min-height: 90px;
                resize: vertical;
            }
        </style>
    @endpush
@endonce

@if($hasActions)
<div class="dropdown follow-action-dropdown">
    @include('partials.action-dropdown-toggle')
    <div class="dropdown-menu dropdown-menu-right lead-action-menu" aria-labelledby="{{ $actionId }}">
        @if($showEditRemarks)
            <a class="dropdown-item lead-action-item" href="{{ route('certificate.edit', $cert) }}">
                <span class="lead-action-icon lead-icon-blue"><i class="fa fa-pencil"></i></span>
                <span class="lead-action-label">Edit Remarks</span>
            </a>
        @endif

        @if($showApprove)
            <form method="POST" action="{{ route('certificate.approve', $cert) }}" onsubmit="return promptCertificateRemark(this, 'Approve this certificate request?');">
                @csrf
                @method('PATCH')
                <input type="hidden" name="remarks" value="">
                <button type="submit" class="dropdown-item lead-action-item">
                    <span class="lead-action-icon lead-icon-green"><i class="fa fa-check"></i></span>
                    <span class="lead-action-label">Approve</span>
                </button>
            </form>
        @endif

        @if($showReject)
            <form method="POST" action="{{ route('certificate.reject', $cert) }}" onsubmit="return promptCertificateRemark(this, 'Reject this certificate?');">
                @csrf
                @method('PATCH')
                <input type="hidden" name="remarks" value="">
                <button type="submit" class="dropdown-item lead-action-item">
                    <span class="lead-action-icon lead-icon-red"><i class="fa fa-ban"></i></span>
                    <span class="lead-action-label">Reject</span>
                </button>
            </form>
        @endif

        @if($showSendToPrinting)
            <form method="POST" action="{{ route('certificate.send-to-printing', $cert) }}">
                @csrf
                @method('PATCH')
                <button type="submit" class="dropdown-item lead-action-item">
                    <span class="lead-action-icon lead-icon-yellow"><i class="fa fa-print"></i></span>
                    <span class="lead-action-label">Send to Printing</span>
                </button>
            </form>
        @endif

        @if($showMarkReady)
            <form method="POST" action="{{ route('certificate.mark-ready', $cert) }}">
                @csrf
                @method('PATCH')
                <button type="submit" class="dropdown-item lead-action-item">
                    <span class="lead-action-icon lead-icon-green"><i class="fa fa-flag-checkered"></i></span>
                    <span class="lead-action-label">Mark Ready</span>
                </button>
            </form>
        @endif

        @if($showPreview)
            <a class="dropdown-item lead-action-item" href="{{ route('certificate.preview', ['admission' => $cert, 'scope' => $activeScope]) }}" target="_blank" rel="noopener">
                <span class="lead-action-icon lead-icon-blue"><i class="fa fa-eye"></i></span>
                <span class="lead-action-label">Preview</span>
            </a>
        @endif

        @if($showMarkDelivered)
            <form method="POST" action="{{ route('certificate.mark-delivered', $cert) }}" onsubmit="return promptDelivery(this, '{{ addslashes($cert->student_name ?? '') }}');">
                @csrf
                @method('PATCH')
                <input type="hidden" name="delivered_to" value="">
                <input type="hidden" name="delivered_cnic" value="">
                <input type="hidden" name="delivered_phone" value="">
                <input type="hidden" name="delivered_at" value="">
                <input type="hidden" name="remarks" value="">
                <button type="submit" class="dropdown-item lead-action-item">
                    <span class="lead-action-icon lead-icon-green"><i class="fa fa-hand-o-right"></i></span>
                    <span class="lead-action-label">Mark Delivered</span>
                </button>
            </form>
        @endif

        @if($showDelete)
            <form method="POST" action="{{ route('certificate.destroy', $cert) }}" onsubmit="return confirm('Remove this certificate request and move the student back to pending?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="dropdown-item lead-action-item">
                    <span class="lead-action-icon lead-icon-red"><i class="fa fa-trash"></i></span>
                    <span class="lead-action-label">Delete</span>
                </button>
            </form>
        @endif
    </div>
</div>
@endif

@once
    @push('scripts')
        <script>
            function promptCertificateRemark(form, message) {
                if (!form) return false;

                if (window.swal) {
                    swal({
                        title: message || 'Add remarks',
                        text: 'Remarks (optional)',
                        type: 'input',
                        showCancelButton: true,
                        closeOnConfirm: false,
                        animation: 'slide-from-top',
                        inputPlaceholder: 'Enter remarks'
                    }, function (inputValue) {
                        if (inputValue === false) {
                            return false;
                        }

                        var remarksInput = form.querySelector('input[name=remarks]');
                        if (remarksInput) {
                            remarksInput.value = inputValue || '';
                        }

                        swal.close();
                        form.submit();
                    });

                    return false;
                }

                var remarks = window.prompt((message || 'Add remarks') + '\n\nRemarks (optional):', '');
                if (remarks === null) return false;
                var remarksInput = form.querySelector('input[name=remarks]');
                if (remarksInput) {
                    remarksInput.value = remarks;
                }
                return true;
            }

            function promptDelivery(form, defaultName) {
                if (!form) return false;

                if (window.swal) {
                    var today = new Date();
                    var yyyy = today.getFullYear();
                    var mm = String(today.getMonth() + 1).padStart(2, '0');
                    var dd = String(today.getDate()).padStart(2, '0');
                    var defaultDate = yyyy + '-' + mm + '-' + dd;

                    swal({
                        title: 'Mark Certificate Delivered',
                        text:
                            '<input id="swal-delivered-to" class="swal-delivery-input" placeholder="Deliver Name" value="' + escapeHtml(defaultName || '') + '">' +
                            '<input id="swal-delivered-cnic" class="swal-delivery-input" placeholder="CNIC">' +
                            '<input id="swal-delivered-phone" class="swal-delivery-input" placeholder="Phone">' +
                            '<input id="swal-delivered-date" class="swal-delivery-input" type="date" value="' + defaultDate + '">' +
                            '<textarea id="swal-delivery-remarks" class="swal-delivery-textarea" placeholder="Remarks"></textarea>',
                        html: true,
                        showCancelButton: true,
                        closeOnConfirm: false,
                        confirmButtonText: 'Mark Delivered',
                        cancelButtonText: 'Cancel'
                    }, function () {
                        var deliveredToInput = document.getElementById('swal-delivered-to');
                        var deliveredCnicInput = document.getElementById('swal-delivered-cnic');
                        var deliveredPhoneInput = document.getElementById('swal-delivered-phone');
                        var deliveredDateInput = document.getElementById('swal-delivered-date');
                        var deliveryRemarksInput = document.getElementById('swal-delivery-remarks');
                        var deliveredTo = deliveredToInput ? deliveredToInput.value.trim() : '';
                        var deliveredCnic = deliveredCnicInput ? deliveredCnicInput.value.trim() : '';
                        var deliveredPhone = deliveredPhoneInput ? deliveredPhoneInput.value.trim() : '';
                        var deliveredDate = deliveredDateInput ? deliveredDateInput.value.trim() : '';
                        var remarks = deliveryRemarksInput ? deliveryRemarksInput.value.trim() : '';

                        if (!deliveredTo || !deliveredDate) {
                            if (typeof swal.showInputError === 'function') {
                                swal.showInputError('Deliver name and date are required.');
                            } else {
                                window.alert('Deliver name and date are required.');
                            }
                            return false;
                        }

                        form.querySelector('input[name=delivered_to]').value = deliveredTo;
                        form.querySelector('input[name=delivered_cnic]').value = deliveredCnic;
                        form.querySelector('input[name=delivered_phone]').value = deliveredPhone;
                        form.querySelector('input[name=delivered_at]').value = deliveredDate;
                        form.querySelector('input[name=remarks]').value = remarks;

                        swal.close();
                        form.submit();
                    });

                    return false;
                }

                var to = window.prompt('Deliver name?', defaultName || '');
                if (to === null || !to.trim()) return false;

                var cnic = window.prompt('CNIC?', '') || '';
                if (cnic === null) return false;

                var phone = window.prompt('Phone?', '') || '';
                if (phone === null) return false;

                var date = window.prompt('Delivery date (YYYY-MM-DD)?', new Date().toISOString().slice(0, 10)) || '';
                if (date === null || !date.trim()) return false;

                var remarks = window.prompt('Remarks?', '') || '';
                if (remarks === null) return false;

                form.querySelector('input[name=delivered_to]').value = to.trim();
                form.querySelector('input[name=delivered_cnic]').value = cnic.trim();
                form.querySelector('input[name=delivered_phone]').value = phone.trim();
                form.querySelector('input[name=delivered_at]').value = date.trim();
                form.querySelector('input[name=remarks]').value = remarks.trim();
                return true;
            }

            function escapeHtml(value) {
                return String(value || '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }
        </script>
    @endpush
@endonce
