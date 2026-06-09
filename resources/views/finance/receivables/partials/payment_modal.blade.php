@php
    $paymentCharge = $paymentCharge ?? ($charge ?? null);
    $paymentAction = $paymentCharge ? route('finance.receivables.collect', $paymentCharge) : '';
    $paymentInvoiceNumber = $paymentCharge
        ? ($paymentCharge->invoice_number ?: ($paymentCharge->voucher_number ?: 'N/A'))
        : 'Select Invoice';
    $paymentCustomerName = $paymentCharge?->student_name ?? 'Select invoice from the list';
    $paymentBalanceAmount = $paymentCharge ? (float) $paymentCharge->balance_amount : 0;
    $paymentBalanceAmountJs = number_format($paymentBalanceAmount, 2, '.', '');
    $paymentCampusCode = $paymentCharge?->campus?->code ?? 'GEN';
    $paymentRefPreview = $paymentCampusCode . '-RCV-' . now()->format('my') . '-AUTO';
    $paymentMethodOld = old('payment_method');
    if ($paymentMethodOld === 'bank') {
        $paymentMethodOld = 'online';
    }
    $paymentErrorsPresent =
        $errors->has('payment_date')
        || $errors->has('amount')
        || $errors->has('payment_method')
        || $errors->has('receiver_name')
        || $errors->has('depositor_name')
        || $errors->has('bank_name')
        || $errors->has('account_no')
        || $errors->has('transfer_id')
        || $errors->has('cheque_no')
        || $errors->has('cheque_date')
        || $errors->has('cheque_payee_name')
        || $errors->has('attachment');
@endphp

<div class="modal fade" id="invoice-payment-modal" tabindex="-1" role="dialog" aria-labelledby="invoice-payment-modal-title" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <form method="POST" action="{{ $paymentAction }}" enctype="multipart/form-data" id="invoice-payment-modal-form">
                @csrf
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="invoice-payment-modal-title">Pay Invoice</h5>
                        <div class="text-muted small">Collect payment after invoice creation using the selected method.</div>
                    </div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="charge_id" id="invoice-payment-charge-id" value="{{ old('charge_id', $paymentCharge?->id) }}">

                    <div class="invoice-payment-modal-summary">
                        <div>
                            <div class="summary-kicker">Invoice</div>
                            <div class="summary-value" data-payment-summary="invoice">{{ $paymentInvoiceNumber }}</div>
                            <div class="summary-subtitle" data-payment-summary="customer">{{ $paymentCustomerName }}</div>
                        </div>
                        <div class="text-right">
                            <div class="summary-kicker">Outstanding Balance</div>
                            <div class="summary-value text-primary" data-payment-summary="balance">Rs. {{ number_format($paymentBalanceAmount, 0) }}</div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6 col-lg-3">
                            <label class="form-label required">Payment Method</label>
                            <select name="payment_method" class="form-control" id="invoice-payment-method" required>
                                <option value="cash" @selected(($paymentMethodOld ?? 'cash') === 'cash')>Cash</option>
                                <option value="online" @selected(($paymentMethodOld ?? '') === 'online')>Online Transfer</option>
                                <option value="cheque" @selected(($paymentMethodOld ?? '') === 'cheque')>Cheque</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6 col-lg-3">
                            <label class="form-label required">Amount</label>
                            <input type="number" step="0.01" min="0.01" name="amount" id="invoice-payment-amount" class="form-control" value="{{ old('amount', $paymentBalanceAmount > 0 ? number_format($paymentBalanceAmount, 2, '.', '') : '') }}" required>
                        </div>
                        <div class="form-group col-md-6 col-lg-3">
                            <label class="form-label required">Date</label>
                            <input type="date" name="payment_date" id="invoice-payment-date" class="form-control" value="{{ old('payment_date', now()->toDateString()) }}" required>
                        </div>
                        <div class="form-group col-md-6 col-lg-3">
                            <label class="form-label">Ref No</label>
                            <input type="text" class="form-control" id="invoice-payment-ref-preview" value="{{ $paymentRefPreview }}" readonly>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="form-label required">Receiver Name</label>
                            <input type="text" name="receiver_name" class="form-control" value="{{ old('receiver_name') }}" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="form-label required">Depositor Name</label>
                            <input type="text" name="depositor_name" class="form-control" value="{{ old('depositor_name') }}" required>
                        </div>
                    </div>

                    <div class="form-row d-none" data-payment-methods="online cheque">
                        <div class="form-group col-md-12">
                            <label class="form-label required">Bank Name</label>
                            <input type="text" name="bank_name" class="form-control" value="{{ old('bank_name') }}" data-required-for-method="1">
                        </div>
                    </div>

                    <div class="form-row d-none" data-payment-methods="online">
                        <div class="form-group col-md-6">
                            <label class="form-label required">Account No</label>
                            <input type="text" name="account_no" class="form-control" value="{{ old('account_no') }}" data-required-for-method="1">
                        </div>
                        <div class="form-group col-md-6">
                            <label class="form-label required">Transfer ID / Online Payment Ref</label>
                            <input type="text" name="transfer_id" class="form-control" value="{{ old('transfer_id') }}" data-required-for-method="1">
                        </div>
                    </div>

                    <div class="form-row d-none" data-payment-methods="cheque">
                        <div class="form-group col-md-4">
                            <label class="form-label required">Cheque No</label>
                            <input type="text" name="cheque_no" class="form-control" value="{{ old('cheque_no') }}" data-required-for-method="1">
                        </div>
                        <div class="form-group col-md-4">
                            <label class="form-label required">Cheque Date</label>
                            <input type="date" name="cheque_date" class="form-control" value="{{ old('cheque_date') }}" data-required-for-method="1">
                        </div>
                        <div class="form-group col-md-4">
                            <label class="form-label required">Pay Name On Cheque</label>
                            <input type="text" name="cheque_payee_name" class="form-control" value="{{ old('cheque_payee_name') }}" data-required-for-method="1">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="form-label required">Transaction Proof Image</label>
                            <input type="file" name="attachment" class="form-control-file" accept="image/*" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="form-label">Internal Remarks</label>
                            <input type="text" name="payment_remarks" class="form-control" value="{{ old('payment_remarks') }}" placeholder="Finance remarks">
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-inline btn-danger-outline" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-inline btn-primary-outline">Save Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

@once
    @push('styles')
        <style>
            .invoice-payment-modal-summary {
                display: flex;
                justify-content: space-between;
                gap: 16px;
                align-items: flex-start;
                padding: 14px 16px;
                border-radius: 10px;
                background: #f5faff;
                border: 1px solid #dceefe;
                margin-bottom: 18px;
            }
            .invoice-payment-modal-summary .summary-kicker {
                font-size: 12px;
                font-weight: 700;
                letter-spacing: .08em;
                text-transform: uppercase;
                color: #64748b;
                margin-bottom: 4px;
            }
            .invoice-payment-modal-summary .summary-value {
                font-size: 22px;
                font-weight: 700;
                color: #0f172a;
                line-height: 1.15;
            }
            .invoice-payment-modal-summary .summary-subtitle {
                margin-top: 4px;
                color: #475569;
            }
            @media (max-width: 760px) {
                .invoice-payment-modal-summary {
                    flex-direction: column;
                }
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            jQuery(function ($) {
                var modal = $('#invoice-payment-modal');

                if (!modal.length) {
                    return;
                }

                var form = $('#invoice-payment-modal-form');
                var methodInput = $('#invoice-payment-method');
                var amountInput = $('#invoice-payment-amount');
                var dateInput = $('#invoice-payment-date');
                var chargeIdInput = $('#invoice-payment-charge-id');
                var refPreviewInput = $('#invoice-payment-ref-preview');
                var fallback = {
                    action: @json($paymentAction),
                    chargeId: @json(old('charge_id', $paymentCharge?->id)),
                    invoiceNumber: @json($paymentInvoiceNumber),
                    customerName: @json($paymentCustomerName),
                    balanceAmount: @json($paymentBalanceAmountJs),
                    paymentRefPreview: @json($paymentRefPreview),
                    paymentDate: @json(now()->toDateString()),
                    restoredMethod: @json($paymentMethodOld ?: 'cash'),
                    restoredAmount: @json(old('amount')),
                };

                function money(value) {
                    var amount = Number(value || 0);
                    return 'Rs. ' + amount.toLocaleString(undefined, {
                        maximumFractionDigits: 2,
                        minimumFractionDigits: 0
                    });
                }

                function clearModalFields() {
                    form.find('input[type="text"], input[type="number"], input[type="date"], textarea').not('#invoice-payment-ref-preview').val('');
                    form.find('input[type="file"]').val('');
                }

                function syncMethodFields() {
                    var activeMethod = methodInput.val();

                    modal.find('[data-payment-methods]').each(function () {
                        var group = $(this);
                        var methods = String(group.data('paymentMethods') || '').split(/\s+/);
                        var active = methods.indexOf(activeMethod) !== -1;

                        group.toggleClass('d-none', !active);

                        group.find('input, select, textarea').each(function () {
                            var input = $(this);
                            if (input.is('[data-required-for-method]')) {
                                input.prop('required', active);
                            }

                            if (!active && input.attr('type') !== 'hidden') {
                                input.val('');
                            }
                        });
                    });
                }

                function populateModal(triggerData, resetFields) {
                    var data = $.extend({}, fallback, triggerData || {});
                    var balanceAmount = Number(data.balanceAmount || 0);

                    if (resetFields) {
                        clearModalFields();
                        methodInput.val('cash');
                        dateInput.val(fallback.paymentDate);
                        amountInput.val(balanceAmount > 0 ? balanceAmount.toFixed(2) : '');
                    }

                    if (data.action) {
                        form.attr('action', data.action);
                    }

                    chargeIdInput.val(data.chargeId || '');
                    refPreviewInput.val(data.paymentRefPreview || fallback.paymentRefPreview);
                    amountInput.attr('max', balanceAmount > 0 ? balanceAmount.toFixed(2) : '');

                    modal.find('[data-payment-summary="invoice"]').text(data.invoiceNumber || fallback.invoiceNumber);
                    modal.find('[data-payment-summary="customer"]').text(data.customerName || fallback.customerName);
                    modal.find('[data-payment-summary="balance"]').text(money(balanceAmount));

                    syncMethodFields();
                }

                $(document).on('click', '[data-payment-trigger="invoice"]', function (event) {
                    event.preventDefault();

                    populateModal({
                        action: $(this).data('paymentAction'),
                        chargeId: $(this).data('chargeId'),
                        invoiceNumber: $(this).data('invoiceNumber'),
                        customerName: $(this).data('customerName'),
                        balanceAmount: $(this).data('balanceAmount'),
                        paymentRefPreview: $(this).data('paymentRefPreview')
                    }, true);

                    modal.modal('show');
                });

                methodInput.on('change', syncMethodFields);

                syncMethodFields();

                if (@json($paymentErrorsPresent)) {
                    var trigger = $('[data-payment-trigger="invoice"][data-charge-id="' + String(fallback.chargeId || '') + '"]').first();

                    if (trigger.length) {
                        populateModal({
                            action: trigger.data('paymentAction'),
                            chargeId: trigger.data('chargeId'),
                            invoiceNumber: trigger.data('invoiceNumber'),
                            customerName: trigger.data('customerName'),
                            balanceAmount: trigger.data('balanceAmount'),
                            paymentRefPreview: trigger.data('paymentRefPreview')
                        }, false);
                    } else {
                        populateModal({
                            action: fallback.action,
                            chargeId: fallback.chargeId,
                            invoiceNumber: fallback.invoiceNumber,
                            customerName: fallback.customerName,
                            balanceAmount: fallback.balanceAmount,
                            paymentRefPreview: fallback.paymentRefPreview
                        }, false);
                    }

                    if (fallback.restoredMethod) {
                        methodInput.val(fallback.restoredMethod);
                        syncMethodFields();
                    }

                    modal.modal('show');
                }
            });
        </script>
    @endpush
@endonce
