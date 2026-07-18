@php
    $modalId = 'pay-expense-' . $expense->id;
    $selectedPayeeId = old('payee_id', $expense->payee_id ?: optional(optional($expense->bill)->billType)->payee_id);
@endphp

<a
    class="dropdown-item text-primary"
    href="#{{ $modalId }}"
    data-toggle="modal"
    data-target="#{{ $modalId }}"
>
    Pay Now
</a>

@push('modals')
<div class="modal fade finance-pay-modal" id="{{ $modalId }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="POST" action="{{ route('finance.expense.markPaid', $expense) }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h4 class="modal-title">Pay Expense</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info mb-3">
                        <strong>Voucher:</strong> {{ $expense->voucher_no ?? 'N/A' }}
                        <span class="mx-2">|</span>
                        <strong>Expense:</strong> {{ $expense->expenseType->name ?? ucfirst($expense->category ?? 'expense') }}
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="form-label">Campus</label>
                            <input
                                type="text"
                                class="form-control"
                                value="{{ $expense->campus->code ?? 'N/A' }}{{ !empty($expense->campus->name) ? ' - ' . $expense->campus->name : '' }}"
                                readonly
                            >
                        </div>
                        <div class="form-group col-md-6">
                            <label class="form-label">Payee / Supplier</label>
                            <select name="payee_id" class="form-control">
                                <option value="">- Select Payee / Supplier -</option>
                                @foreach(($settlementPayees ?? collect()) as $payee)
                                    <option value="{{ $payee->id }}" @selected((int) $selectedPayeeId === (int) $payee->id)>
                                        {{ $payee->display_name ?: $payee->full_name }}{{ $payee->company_name ? ' | ' . $payee->company_name : '' }} ({{ ucfirst($payee->type) }}){{ $payee->status !== 'active' ? ' - ' . ucfirst($payee->status) : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label class="form-label required">Payment Date</label>
                            <input type="date" name="payment_date" class="form-control" value="{{ old('payment_date', now()->toDateString()) }}" required>
                        </div>
                        <div class="form-group col-md-4">
                            <label class="form-label required">Amount</label>
                            <input
                                type="number"
                                step="0.01"
                                min="1"
                                name="amount"
                                class="form-control"
                                value="{{ old('amount', (float) $expense->amount) }}"
                                @unless($canAdjustAmount ?? false) readonly @endunless
                                required
                            >
                        </div>
                        <div class="form-group col-md-4">
                            <label class="form-label required">Payment Method</label>
                            <select name="payment_method" class="form-control js-payment-method" required>
                                @foreach($paymentMethods as $value => $label)
                                    <option value="{{ $value }}" @selected(old('payment_method', 'cash') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label class="form-label">Receipt No</label>
                            <input
                                type="text"
                                class="form-control js-receipt-preview"
                                value="{{ $receiptPreviewByMethod['cash'] ?? 'Auto generated on submit' }}"
                                readonly
                            >
                        </div>
                        <div class="form-group col-md-4 js-payment-ref-wrap">
                            <label class="form-label">Payment Ref No</label>
                            <input type="text" name="payment_ref_no" class="form-control" value="{{ old('payment_ref_no') }}" placeholder="Bank/transfer/e-wallet receipt">
                        </div>
                        <div class="form-group col-md-4 js-bank-name-wrap">
                            <label class="form-label">Bank Name</label>
                            <input type="text" name="bank_name" class="form-control" value="{{ old('bank_name') }}">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-4 js-cheque-no-wrap">
                            <label class="form-label">Cheque No</label>
                            <input type="text" name="cheque_no" class="form-control" value="{{ old('cheque_no') }}">
                        </div>
                        <div class="form-group col-md-4">
                            <label class="form-label required">Transaction Proof</label>
                            <input type="file" name="attachment" class="form-control-file" required>
                        </div>
                        <div class="form-group col-md-4">
                            <label class="form-label">Remarks</label>
                            <input type="text" name="remarks" class="form-control" value="{{ old('remarks') }}" placeholder="Optional note">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-inline btn-danger-outline" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-inline btn-primary-outline">Submit Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endpush
