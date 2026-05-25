@extends('layouts.theme')

@section('title', 'Pay Utility Bills')

@section('content')
    <div class="finance-shell">
        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="box-typical box-typical-dashboard panel panel-default finance-card">
            <header class="box-typical-header panel-heading finance-header">
                <h3 class="panel-title">Pay Utility Bill (Approval Required)</h3>
            </header>
            <div class="box-typical-body panel-body">
                @php
                    $billTypes = $bills
                        ->filter(fn ($bill) => $bill->billType)
                        ->map(fn ($bill) => [
                            'id' => $bill->bill_type_id,
                            'name' => $bill->billType->service_name ?: $bill->billType->display_name,
                        ])
                        ->unique('id')
                        ->sortBy('name')
                        ->values();
                @endphp
                <form method="POST" action="{{ route('finance.utility.pay.store') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="bill_id" id="selectedBillId" value="{{ old('bill_id') }}">
                    <input type="hidden" name="payment_date" value="{{ old('payment_date', now()->toDateString()) }}">
                    <div class="form-row mt-3">
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label required">Campus</label>
                            <select id="campusFilter" class="form-control" required>
                                <option value="">- Select -</option>
                                @foreach($campuses as $campus)
                                    <option value="{{ $campus->id }}" @selected(old('campus_id') == $campus->id)>
                                        {{ $campus->code }} - {{ $campus->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label required">Bill Type</label>
                            <select id="billTypeFilter" class="form-control" required>
                                <option value="">- Select -</option>
                                @foreach($billTypes as $type)
                                    <option value="{{ $type['id'] }}" @selected(old('bill_type_id') == $type['id'])>{{ $type['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label required">Reference Number</label>
                            <select id="referenceNumberFilter" class="form-control" required>
                                <option value="">- Select -</option>
                            </select>
                        </div>
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label required">Bill Month</label>
                            <select id="billMonthDisplay" class="form-control" required>
                                <option value="">- Select -</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label required">Bill Issue Date</label>
                            <input type="text" id="issueDateDisplay" class="form-control" readonly>
                        </div>
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label required">Bill Due Date</label>
                            <input type="text" id="dueDateDisplay" class="form-control" readonly>
                        </div>
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label required">Payable Amount Within Due Date</label>
                            <input type="text" id="amountWithinDueDisplay" class="form-control" readonly>
                        </div>
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label">Fine</label>
                            <input type="text" id="fineDisplay" class="form-control" readonly>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label required">Payable Amount</label>
                            <input type="number" step="0.01" min="1" name="paid_amount" id="paidAmountInput" class="form-control" value="{{ old('paid_amount') }}" required readonly>
                        </div>
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label required">Payment Method</label>
                            <select name="payment_method" class="form-control" required>
                                <option value="">- Select -</option>
                                <option value="cash" @selected(old('payment_method') === 'cash')>Cash</option>
                                <option value="cheque" @selected(old('payment_method') === 'cheque')>Cheque</option>
                                <option value="online_transfer" @selected(old('payment_method') === 'online_transfer')>Online Transfer</option>
                                <option value="jazzcash" @selected(old('payment_method') === 'jazzcash')>JazzCash</option>
                                <option value="easypaisa" @selected(old('payment_method') === 'easypaisa')>Easypaisa</option>
                                <option value="bank" @selected(old('payment_method') === 'bank')>Bank</option>
                            </select>
                        </div>
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label required">Payment Reference Number</label>
                            <input type="text" name="payment_ref_no" class="form-control" value="{{ old('payment_ref_no') }}">
                        </div>
                       
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label required">Upload Image</label>
                            <input type="file" name="attachment" class="form-control-file" required>
                        </div>
                   
                    </div>

                      <div class="form-row">
                        <div class="form-group col-12">
                            <label class="form-label">Remarks</label>
                            <textarea name="remarks" class="form-control" rows="3" style="padding:10px;">{{ old('remarks') }}</textarea>
                        </div>
                    </div>

                    

                    {{--
                    <div class="form-row">
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label">Bank Name</label>
                            <input type="text" name="bank_name" class="form-control" value="{{ old('bank_name') }}">
                        </div>
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label">Cheque No</label>
                            <input type="text" name="cheque_no" class="form-control" value="{{ old('cheque_no') }}">
                        </div>
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label">Bank Receipt No</label>
                            <input type="text" name="bank_receipt_no" class="form-control" value="{{ old('bank_receipt_no') }}">
                        </div>
                    </div>
                    --}}

                    <div class="text-right">
                        <button type="submit" class="btn btn-primary-outline ">Submit For Approval</button>
                        <button type="submit" class="btn btn-danger-outline ">Cancel</button>
                    </div>
                </form>
            </div>
        </section>

        <section class="box-typical box-typical-dashboard panel panel-default finance-card mt-3">
            <header class="box-typical-header panel-heading finance-header">
                <h3 class="panel-title">Utility Payment History</h3>
            </header>
            <div class="box-typical-body panel-body">
                <div class="table-responsive">
                    <table class="table table-bordered finance-table">
                        <thead>
                            <tr>
                                <th>Sr#</th>
                                <th>Receipt</th>
                                <th>Bill Ref</th>
                                <th>Campus</th>
                                <th>Bill Type</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payments as $index => $payment)
                                <tr>
                                    <td>{{ $payments->firstItem() + $index }}</td>
                                    <td>{{ $payment->receipt_no ?? '-' }}</td>
                                    <td>{{ $payment->bill->reference_number ?? 'N/A' }}</td>
                                    <td>{{ $payment->bill->campus->code ?? 'N/A' }}</td>
                                    <td>{{ $payment->bill->billType->name ?? 'N/A' }}</td>
                                    <td>Rs. {{ number_format((float) $payment->paid_amount, 0) }}</td>
                                    <td>{{ ucfirst($payment->payment_method ?? '-') }}</td>
                                    <td>{{ optional($payment->payment_date)->format('d-M-Y') }}</td>
                                    <td>
                                        @if($payment->attachment_path)
                                            <a class="btn btn-primary btn-sm" href="{{ asset('storage/' . $payment->attachment_path) }}" target="_blank">Image</a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted">No utility payment found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $payments->links() }}
            </div>
        </section>
    </div>
@endsection

@push('styles')
    <style>
        .box-typical .panel-heading {
            padding: 7px 20px;
        }

        .box-typical.box-typical-dashboard .box-typical-body {
            overflow: hidden;
            margin: 5px;
        }

        .finance-shell { padding: 8px 0 16px; }
        .finance-header { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; }
        .required::after { content: ' *'; color: #343434; }
        .finance-table thead th { background: #1ea7ff; color: #fff; }
        #issueDateDisplay[readonly],
        #dueDateDisplay[readonly],
        #amountWithinDueDisplay[readonly],
        #fineDisplay[readonly],
        #paidAmountInput[readonly] {
            background: #f3f6fa;
        }
    </style>
@endpush

@push('scripts')
    @php
        $billPayload = $bills->map(function ($bill) {
            $balance = max(0, (float) $bill->amount - (float) $bill->paid_amount);

            return [
                'id' => $bill->id,
                'campus_id' => $bill->campus_id,
                'bill_type_id' => $bill->bill_type_id,
                'reference_number' => $bill->reference_number,
                'bill_month' => optional($bill->bill_month)->format('F Y'),
                'issue_date' => optional($bill->issue_date)->format('m/d/Y'),
                'due_date' => optional($bill->due_date)->format('m/d/Y'),
                'amount_within_due_date' => number_format((float) $bill->amount_within_due_date, 2, '.', ''),
                'fine' => number_format((float) $bill->fine, 2, '.', ''),
                'balance' => number_format($balance, 2, '.', ''),
            ];
        })->values();
    @endphp
    <script>
        (function () {
            var bills = @json($billPayload);

            var campusFilter = document.getElementById('campusFilter');
            var billTypeFilter = document.getElementById('billTypeFilter');
            var referenceNumberFilter = document.getElementById('referenceNumberFilter');
            var billMonthDisplay = document.getElementById('billMonthDisplay');
            var selectedBillId = document.getElementById('selectedBillId');
            var issueDateDisplay = document.getElementById('issueDateDisplay');
            var dueDateDisplay = document.getElementById('dueDateDisplay');
            var amountWithinDueDisplay = document.getElementById('amountWithinDueDisplay');
            var fineDisplay = document.getElementById('fineDisplay');
            var paidAmountInput = document.getElementById('paidAmountInput');
            var oldBillId = selectedBillId ? selectedBillId.value : '';

            function resetSelect(select, placeholder) {
                select.innerHTML = '';
                var option = document.createElement('option');
                option.value = '';
                option.textContent = placeholder;
                select.appendChild(option);
            }

            function resetBillFields() {
                if (selectedBillId) {
                    selectedBillId.value = '';
                }
                resetSelect(billMonthDisplay, '- Select -');
                issueDateDisplay.value = '';
                dueDateDisplay.value = '';
                amountWithinDueDisplay.value = '';
                fineDisplay.value = '';
                paidAmountInput.value = '';
            }

            function filteredBills() {
                return bills.filter(function (bill) {
                    var campusMatch = !campusFilter.value || String(bill.campus_id) === campusFilter.value;
                    var typeMatch = !billTypeFilter.value || String(bill.bill_type_id) === billTypeFilter.value;
                    return campusMatch && typeMatch;
                });
            }

            function populateReferenceNumbers(selectedReference) {
                resetSelect(referenceNumberFilter, '- Select -');

                filteredBills().forEach(function (bill) {
                    var option = document.createElement('option');
                    option.value = String(bill.id);
                    option.textContent = bill.reference_number;
                    if (selectedReference && selectedReference === String(bill.id)) {
                        option.selected = true;
                    }
                    referenceNumberFilter.appendChild(option);
                });
            }

            function populateBillDetails(billId) {
                var bill = bills.find(function (item) {
                    return String(item.id) === String(billId);
                });

                if (!bill) {
                    resetBillFields();
                    return;
                }

                if (selectedBillId) {
                    selectedBillId.value = bill.id;
                }

                resetSelect(billMonthDisplay, '- Select -');
                var monthOption = document.createElement('option');
                monthOption.value = bill.bill_month || '';
                monthOption.textContent = bill.bill_month || '- Select -';
                monthOption.selected = true;
                billMonthDisplay.appendChild(monthOption);

                issueDateDisplay.value = bill.issue_date || '';
                dueDateDisplay.value = bill.due_date || '';
                amountWithinDueDisplay.value = bill.amount_within_due_date || '';
                fineDisplay.value = bill.fine || '0.00';
                paidAmountInput.value = bill.balance || '';
            }

            function syncFromBillId(billId) {
                var bill = bills.find(function (item) {
                    return String(item.id) === String(billId);
                });

                if (!bill) {
                    return;
                }

                if (campusFilter) {
                    campusFilter.value = String(bill.campus_id);
                }

                if (billTypeFilter) {
                    billTypeFilter.value = String(bill.bill_type_id);
                }

                populateReferenceNumbers(String(bill.id));
                populateBillDetails(String(bill.id));
            }

            if (campusFilter) {
                campusFilter.addEventListener('change', function () {
                    populateReferenceNumbers();
                    resetBillFields();
                });
            }

            if (billTypeFilter) {
                billTypeFilter.addEventListener('change', function () {
                    populateReferenceNumbers();
                    resetBillFields();
                });
            }

            if (referenceNumberFilter) {
                referenceNumberFilter.addEventListener('change', function () {
                    populateBillDetails(this.value);
                });
            }

            if (oldBillId) {
                syncFromBillId(oldBillId);
            } else {
                populateReferenceNumbers();
            }
        })();
    </script>
@endpush
