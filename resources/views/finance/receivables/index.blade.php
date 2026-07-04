@extends('layouts.theme')

@section('title', 'Invoice Generator')

@section('content')
    @php
        $statusColors = [
            'pending' => 'badge-warning',
            'partial' => 'badge-info',
            'paid' => 'badge-success',
            'overdue' => 'badge-danger',
        ];
        $itemRows = old('items', [['description' => '', 'quantity' => 1, 'unit_price' => '']]);
    @endphp

    <div class="finance-shell">
        @include('partials.session-status-alert')

        @include('partials.validation-errors-alert')

        @unless($invoiceSchemaReady ?? true)
            <div class="alert alert-warning">
                Invoice generator upgrade is not active on this database yet. Run <code>php artisan migrate --force</code> to enable due dates, line items, payment history, and overdue tracking.
            </div>
        @endunless

        <!-- <div class="row finance-summary-row">
            <div class="col-lg-4 col-md-6">
                <div class="invoice-summary-card tone-open">
                    <div class="summary-value">Rs. {{ number_format((float) ($summary['outstanding'] ?? 0), 0) }}</div>
                    <div class="summary-label">Open Invoice Balance</div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="invoice-summary-card tone-overdue">
                    <div class="summary-value">Rs. {{ number_format((float) ($summary['overdue'] ?? 0), 0) }}</div>
                    <div class="summary-label">Overdue Balance</div>
                </div>
            </div>
            <div class="col-lg-2 col-md-6">
                <div class="invoice-summary-card tone-count">
                    <div class="summary-value">{{ number_format((int) ($summary['overdue_count'] ?? 0)) }}</div>
                    <div class="summary-label">Overdue Invoices</div>
                </div>
            </div>
            <div class="col-lg-2 col-md-6">
                <div class="invoice-summary-card tone-collected">
                    <div class="summary-value">Rs. {{ number_format((float) ($summary['collected_this_month'] ?? 0), 0) }}</div>
                    <div class="summary-label">Collected This Month</div>
                </div>
            </div>
        </div> -->

        @if($canCreateReceivables && ($invoiceSchemaReady ?? true))
            <section class="box-typical box-typical-dashboard panel panel-default finance-card">
                <header class="box-typical-header panel-heading finance-header">
                    <div>
                        <h3 class="panel-title form-label mb-1">Invoice Generator</h3>
                        <!-- <div class="text-muted small">Create an invoice now. Payments can be collected later from the Pay Now action.</div> -->
                    </div>
                </header>
                <div class="box-typical-body panel-body">
                    <form method="POST" action="{{ route('finance.receivables.store') }}" id="invoice-create-form">
                        @csrf

                        <div class="form-row">
                            <div class="form-group col-md-6 col-lg-3">
                                <label class="form-label required">Campus / Franchise</label>
                                <select name="campus_id" class="form-control" required>
                                    <option value="">- Select -</option>
                                    @foreach($campuses as $campus)
                                        <option value="{{ $campus->id }}" @selected(old('campus_id', $filters['campus_id'] ?? null) == $campus->id)>
                                            {{ $campus->code }} - {{ $campus->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-6 col-lg-3">
                                <label class="form-label required">Charge Type</label>
                                <select name="charge_type_id" class="form-control" id="charge-type-select" required>
                                    <option value="">- Select -</option>
                                    @foreach($chargeTypes as $type)
                                        <option
                                            value="{{ $type->id }}"
                                            data-name="{{ $type->name }}"
                                            data-default-amount="{{ (float) $type->default_amount }}"
                                            @selected(old('charge_type_id') == $type->id)
                                        >
                                            {{ $type->name }} @if($type->default_amount) (Rs. {{ number_format((float) $type->default_amount, 0) }}) @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-6 col-lg-3">
                                <label class="form-label required">Student / Customer / Source</label>
                                <input type="text" name="student_name" class="form-control" value="{{ old('student_name') }}" required>
                            </div>
                            <div class="form-group col-md-6 col-lg-3">
                                <label class="form-label">Phone</label>
                                <input type="text" name="bill_to_phone" class="form-control" value="{{ old('bill_to_phone') }}">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6 col-lg-3">
                                <label class="form-label required">Invoice Date</label>
                                <input type="date" name="invoice_date" class="form-control" value="{{ old('invoice_date', now()->toDateString()) }}" required>
                            </div>
                            <div class="form-group col-md-6 col-lg-3">
                                <label class="form-label required">Due Date</label>
                                <input type="date" name="due_date" class="form-control" value="{{ old('due_date', now()->addDays(7)->toDateString()) }}" required>
                            </div>
                            <div class="form-group col-md-6 col-lg-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="bill_to_email" class="form-control" value="{{ old('bill_to_email') }}">
                            </div>
                            <div class="form-group col-md-6 col-lg-3">
                                <label class="form-label">Address</label>
                                <input type="text" name="bill_to_address" class="form-control" value="{{ old('bill_to_address') }}">
                            </div>
                        </div>

                        <div class="table-responsive invoice-item-table-wrap">
                            <table class="table table-bordered finance-table" id="invoice-items-table">
                                <thead>
                                    <tr>
                                        <th style="width: 46%;">Item Description</th>
                                        <th style="width: 14%;">Qty</th>
                                        <th style="width: 18%;">Rate</th>
                                        <th style="width: 18%;">Line Total</th>
                                        <th style="width: 4%;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($itemRows as $index => $item)
                                        <tr class="invoice-item-row">
                                            <td>
                                                <input
                                                    type="text"
                                                    class="form-control item-description"
                                                    name="items[{{ $index }}][description]"
                                                    value="{{ $item['description'] ?? '' }}"
                                                    placeholder="Item or service description"
                                                    required
                                                >
                                            </td>
                                            <td>
                                                <input
                                                    type="number"
                                                    step="0.01"
                                                    min="0.01"
                                                    class="form-control item-quantity"
                                                    name="items[{{ $index }}][quantity]"
                                                    value="{{ $item['quantity'] ?? 1 }}"
                                                    required
                                                >
                                            </td>
                                            <td>
                                                <input
                                                    type="number"
                                                    step="0.01"
                                                    min="0"
                                                    class="form-control item-unit-price"
                                                    name="items[{{ $index }}][unit_price]"
                                                    value="{{ $item['unit_price'] ?? '' }}"
                                                    required
                                                >
                                            </td>
                                            <td>
                                                <input type="text" class="form-control item-line-total" value="Rs. 0" readonly>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-danger-outline remove-item-btn">&times;</button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-between align-items-center flex-wrap mb-3 p-3">
                            <button type="button" class="btn btn-inline btn-primary-outline" id="add-item-btn">Add Item</button>
                            <div class="invoice-total-strip d-flex ">
                                <div><strong>Subtotal:</strong> <span id="invoice-subtotal">Rs. 0</span></div>
                                <div><strong>Discount:</strong> <span id="invoice-discount-preview">Rs. 0</span></div>
                                <div><strong>Invoice Total:</strong> <span id="invoice-total">Rs. 0</span></div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6 col-lg-3">
                                <label class="form-label">Discount</label>
                                <input type="number" step="0.01" min="0" name="discount_amount" id="discount-amount-input" class="form-control" value="{{ old('discount_amount', 0) }}">
                            </div>
                            <div class="form-group col-md-6 col-lg-9">
                                <label class="form-label">Notes</label>
                                <input type="text" name="notes" class="form-control" value="{{ old('notes') }}" placeholder="Short note to appear on the invoice">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Terms & Conditions</label>
                            <textarea name="terms" class="form-control" rows="2" placeholder="Payment terms or billing conditions">{{ old('terms') }}</textarea>
                        </div>

                        <!-- <div class="form-group">
                            <label class="form-label">Internal Remarks</label>
                            <input type="text" name="remarks" class="form-control" value="{{ old('remarks') }}" placeholder="Internal invoice remarks">
                        </div> -->

                        <div class="text-right mt-3">
                            <button type="submit" class="btn btn-inline btn-primary-outline">Create Invoice</button>
                        </div>
                    </form>
                </div>
            </section>
        @endif

        @if($canViewReceivables)
            <section class="box-typical box-typical-dashboard panel panel-default finance-card mt-3">
                <header class="box-typical-header panel-heading finance-header">
                    <div>
                        <h3 class="panel-title form-label mb-1">Invoices</h3>
                        <!-- <div class="text-muted small">Track paid, unpaid, partial, and overdue invoices.</div> -->
                    </div>
                </header>
                <div class="box-typical-body panel-body">
                    <form class="mb-3" method="GET" action="{{ route('finance.receivables') }}">
                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label class="form-label">Campus</label>
                                <select class="form-control" name="campus_id">
                                    <option value="">All Campuses</option>
                                    @foreach($campuses as $campus)
                                        <option value="{{ $campus->id }}" @selected(($filters['campus_id'] ?? null) == $campus->id)>
                                            {{ $campus->code }} - {{ $campus->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-3">
                                <label class="form-label">Status</label>
                                <select class="form-control" name="status">
                                    <option value="">All</option>
                                    <option value="pending" @selected(($filters['status'] ?? '') === 'pending')>Pending</option>
                                    <option value="partial" @selected(($filters['status'] ?? '') === 'partial')>Partial</option>
                                    <option value="paid" @selected(($filters['status'] ?? '') === 'paid')>Paid</option>
                                    <option value="overdue" @selected(($filters['status'] ?? '') === 'overdue')>Overdue</option>
                                </select>
                            </div>
                            <div class="form-group col-md-3">
                                <label class="form-label">Search</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    name="search"
                                    value="{{ $filters['search'] ?? '' }}"
                                    placeholder="Invoice #, customer, phone"
                                >
                            </div>
                            <div class="form-group d-flex col-md-3 mt-4 p-2 align-items-end justify-content-end">
                                <button type="submit" class="btn btn-inline btn-primary-outline mr-2 ">Filter</button>
                                <a href="{{ route('finance.receivables') }}" class="btn btn-inline btn-danger-outline">Reset</a>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive receivables-list-table-wrap">
                        <table class="table table-bordered finance-table">
                            <thead>
                                <tr>
                                    <th>Invoice</th>
                                    <th>Customer / Source</th>
                                    <th>Campus</th>
                                    <th>Invoice Date</th>
                                    <th>Due Date</th>
                                    <th>Total</th>
                                    <th>Paid</th>
                                    <th>Balance</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($charges as $charge)
                                    @php
                                        $invoiceNumber = ($invoiceSchemaReady ?? true)
                                            ? ($charge->invoice_number ?: ($charge->voucher_number ?: 'N/A'))
                                            : ($charge->voucher_number ?: 'N/A');
                                        $paymentRefPreview = ($charge->campus->code ?? 'GEN') . '-RCV-' . now()->format('my') . '-AUTO';
                                    @endphp
                                    <tr>
                                        <td>
                                            <strong>{{ $invoiceNumber }}</strong>
                                            <div class="text-muted small">{{ $charge->chargeType->name ?? 'Invoice' }}</div>
                                        </td>
                                        <td>{{ $charge->student_name ?: 'N/A' }}</td>
                                        <td>{{ $charge->campus->code ?? 'N/A' }}</td>
                                        <td>{{ ($invoiceSchemaReady ?? true) ? (optional($charge->invoice_date)->format('Y-m-d') ?: 'N/A') : (optional($charge->created_at)->format('Y-m-d') ?: 'N/A') }}</td>
                                        <td>{{ ($invoiceSchemaReady ?? true) ? (optional($charge->due_date)->format('Y-m-d') ?: 'N/A') : '-' }}</td>
                                        <td>Rs. {{ number_format((float) $charge->net_amount, 0) }}</td>
                                        <td>Rs. {{ number_format((float) (($invoiceSchemaReady ?? true) ? $charge->paid_amount : ($charge->status === 'paid' ? $charge->net_amount : 0)), 0) }}</td>
                                        <td>Rs. {{ number_format((float) (($invoiceSchemaReady ?? true) ? $charge->balance_amount : ($charge->status === 'pending' ? $charge->net_amount : 0)), 0) }}</td>
                                        <td>
                                            <span class="badge {{ $statusColors[$charge->status] ?? 'badge-secondary' }}">{{ ucfirst($charge->status) }}</span>
                                        </td>
                                        <td>
                                            <div class="dropdown invoice-action-dropdown">
                                                <button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-toggle="dropdown">
                                                    Action
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-right">
                                                    @if($invoiceSchemaReady ?? true)
                                                        <a class="dropdown-item" href="{{ route('finance.receivables.show', $charge) }}">View Invoice</a>
                                                    @endif
                                                    @if(($invoiceSchemaReady ?? true) && $charge->latestPayment?->attachment_path)
                                                        <a class="dropdown-item" href="{{ asset('storage/' . $charge->latestPayment->attachment_path) }}" target="_blank">View Latest Proof</a>
                                                    @endif
                                                    @if(($invoiceSchemaReady ?? true) && $canUpdateReceivables && (float) $charge->balance_amount > 0)
                                                        <button
                                                            type="button"
                                                            class="dropdown-item text-primary"
                                                            data-payment-trigger="invoice"
                                                            data-charge-id="{{ $charge->id }}"
                                                            data-payment-action="{{ route('finance.receivables.collect', $charge) }}"
                                                            data-invoice-number="{{ $invoiceNumber }}"
                                                            data-customer-name="{{ $charge->student_name ?: 'N/A' }}"
                                                            data-balance-amount="{{ number_format((float) $charge->balance_amount, 2, '.', '') }}"
                                                            data-payment-ref-preview="{{ $paymentRefPreview }}"
                                                        >
                                                            Pay Now
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center text-muted">No invoice records found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $charges->links() }}
                </div>
            </section>
        @endif
    </div>

    <template id="invoice-item-template">
        <tr class="invoice-item-row">
            <td>
                <input type="text" class="form-control item-description" name="__NAME__[description]" placeholder="Item or service description" required>
            </td>
            <td>
                <input type="number" step="0.01" min="0.01" class="form-control item-quantity" name="__NAME__[quantity]" value="1" required>
            </td>
            <td>
                <input type="number" step="0.01" min="0" class="form-control item-unit-price" name="__NAME__[unit_price]" value="" required>
            </td>
            <td>
                <input type="text" class="form-control item-line-total" value="Rs. 0" readonly>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-danger-outline remove-item-btn">&times;</button>
            </td>
        </tr>
    </template>

    @if(($invoiceSchemaReady ?? true) && $canUpdateReceivables)
        @include('finance.receivables.partials.payment_modal', ['paymentCharge' => null])
    @endif
@endsection

@push('styles')
    <style>
        :root {
            --space-finance-receivables-index-1: 10px;
            --space-finance-receivables-index-2: 12px;
            --color-finance-receivables-index-1: #fff;
        }

        .finance-shell { padding: 8px 0 16px; }
        .finance-header { display: flex; justify-content: space-between; align-items: center; gap: var(--space-finance-receivables-index-2); flex-wrap: wrap; }
        .required::after { content: ' *'; color: #e53935; }
        .finance-table thead th { background: #1ea7ff; color: var(--color-finance-receivables-index-1); }
        .finance-summary-row { margin: 2px 0 10px; }
        .invoice-summary-card {
            min-height: 130px;
            border-radius: 10px;
            padding: 14px;
            color: var(--color-finance-receivables-index-1);
            margin-bottom: var(--space-finance-receivables-index-2);
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.12);
        }
        .invoice-summary-card .summary-value {
            font-size: 22px;
            font-weight: 700;
            margin-top: 20px;
            text-align: center;
        }
        .invoice-summary-card .summary-label {
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            text-align: center;
            margin-top: var(--space-finance-receivables-index-1);
            opacity: .9;
        }
        .tone-open { background: #2684ff; }
        .tone-overdue { background: #f35f62; }
        .tone-count { background: #975ce7; }
        .tone-collected { background: #1aa278; }
        .invoice-item-table-wrap { border: 1px solid #eef2f7; border-radius: 8px; overflow: hidden; }
        .receivables-list-table-wrap {
            overflow: visible;
        }
        .receivables-list-table-wrap .dropdown-menu {
            z-index: 1050;
        }
        .invoice-floating-menu {
            position: fixed !important;
            z-index: 99999 !important;
            min-width: 180px;
        }
        .receivables-list-table-wrap td:last-child,
        .receivables-list-table-wrap th:last-child {
            white-space: nowrap;
        }
        .invoice-total-strip {
            display: grid;
            gap: 4px;
            text-align: right;
            min-width: 220px;
        }
        @media (max-width: 760px) {
            .invoice-summary-card .summary-value { margin-top: 8px; font-size: 18px; }
            .invoice-total-strip { width: 100%; margin-top: var(--space-finance-receivables-index-1); }
            .receivables-list-table-wrap {
                overflow-x: auto;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var tableBody = document.querySelector('#invoice-items-table tbody');
            var addItemButton = document.getElementById('add-item-btn');
            var itemTemplate = document.getElementById('invoice-item-template');
            var chargeTypeSelect = document.getElementById('charge-type-select');
            var discountInput = document.getElementById('discount-amount-input');

            function money(value) {
                var amount = Number(value || 0);
                return 'Rs. ' + amount.toLocaleString(undefined, { maximumFractionDigits: 2, minimumFractionDigits: 0 });
            }

            function rowLineTotal(row) {
                var quantity = Number(row.querySelector('.item-quantity')?.value || 0);
                var unitPrice = Number(row.querySelector('.item-unit-price')?.value || 0);
                return quantity * unitPrice;
            }

            function recalculateTotals() {
                var subtotal = 0;

                tableBody.querySelectorAll('.invoice-item-row').forEach(function (row) {
                    var lineTotal = rowLineTotal(row);
                    subtotal += lineTotal;
                    var output = row.querySelector('.item-line-total');
                    if (output) {
                        output.value = money(lineTotal);
                    }
                });

                var discount = Number(discountInput?.value || 0);
                var total = Math.max(0, subtotal - discount);

                document.getElementById('invoice-subtotal').textContent = money(subtotal);
                document.getElementById('invoice-discount-preview').textContent = money(discount);
                document.getElementById('invoice-total').textContent = money(total);
            }

            function reindexRows() {
                tableBody.querySelectorAll('.invoice-item-row').forEach(function (row, index) {
                    row.querySelectorAll('input').forEach(function (input) {
                        var name = input.getAttribute('name');
                        if (!name) {
                            return;
                        }
                        input.setAttribute('name', name.replace(/items\[\d+\]/, 'items[' + index + ']'));
                    });
                });
            }

            function addRow(prefill) {
                if (!itemTemplate || !tableBody) {
                    return;
                }

                var rowIndex = tableBody.querySelectorAll('.invoice-item-row').length;
                var html = itemTemplate.innerHTML.replace(/__NAME__/g, 'items[' + rowIndex + ']');
                var wrapper = document.createElement('tbody');
                wrapper.innerHTML = html.trim();
                var row = wrapper.firstElementChild;

                if (prefill) {
                    var description = row.querySelector('.item-description');
                    var unitPrice = row.querySelector('.item-unit-price');
                    if (description && !description.value) {
                        description.value = prefill.description || '';
                    }
                    if (unitPrice && !unitPrice.value && prefill.unitPrice) {
                        unitPrice.value = prefill.unitPrice;
                    }
                }

                tableBody.appendChild(row);
                recalculateTotals();
            }

            if (addItemButton) {
                addItemButton.addEventListener('click', function () {
                    addRow();
                });
            }

            if (tableBody) {
                tableBody.addEventListener('input', function (event) {
                    if (event.target.matches('.item-quantity, .item-unit-price')) {
                        recalculateTotals();
                    }
                });

                tableBody.addEventListener('click', function (event) {
                    if (!event.target.closest('.remove-item-btn')) {
                        return;
                    }

                    var rows = tableBody.querySelectorAll('.invoice-item-row');
                    if (rows.length === 1) {
                        rows[0].querySelectorAll('input[type="text"], input[type="number"]').forEach(function (input) {
                            if (input.classList.contains('item-quantity')) {
                                input.value = '1';
                            } else {
                                input.value = '';
                            }
                        });
                        recalculateTotals();
                        return;
                    }

                    event.target.closest('.invoice-item-row').remove();
                    reindexRows();
                    recalculateTotals();
                });
            }

            if (chargeTypeSelect) {
                chargeTypeSelect.addEventListener('change', function () {
                    var option = this.options[this.selectedIndex];
                    var firstRow = tableBody.querySelector('.invoice-item-row');
                    if (!option || !firstRow) {
                        return;
                    }

                    var descriptionInput = firstRow.querySelector('.item-description');
                    var unitPriceInput = firstRow.querySelector('.item-unit-price');

                    if (descriptionInput && !descriptionInput.value) {
                        descriptionInput.value = option.dataset.name || '';
                    }

                    if (unitPriceInput && !unitPriceInput.value && Number(option.dataset.defaultAmount || 0) > 0) {
                        unitPriceInput.value = option.dataset.defaultAmount;
                    }

                    recalculateTotals();
                });
            }

            if (discountInput) {
                discountInput.addEventListener('input', recalculateTotals);
            }

            recalculateTotals();

            var activeInvoiceMenu = null;
            var activeInvoiceButton = null;

            function restoreInvoiceMenu() {
                if (!activeInvoiceMenu) {
                    return;
                }

                var parent = activeInvoiceMenu.__invoiceDropdownParent;
                if (parent) {
                    parent.appendChild(activeInvoiceMenu);
                }

                activeInvoiceMenu.classList.remove('invoice-floating-menu', 'show');
                activeInvoiceMenu.removeAttribute('style');

                if (activeInvoiceButton) {
                    activeInvoiceButton.setAttribute('aria-expanded', 'false');
                    var dropdown = activeInvoiceButton.closest('.invoice-action-dropdown');
                    if (dropdown) {
                        dropdown.classList.remove('show', 'open');
                    }
                }

                activeInvoiceMenu = null;
                activeInvoiceButton = null;
            }

            function positionInvoiceMenu(menu, button) {
                var rect = button.getBoundingClientRect();
                var menuWidth = menu.offsetWidth || 180;
                var menuHeight = menu.offsetHeight || 120;
                var left = Math.max(8, Math.min(rect.right - menuWidth, window.innerWidth - menuWidth - 8));
                var top = rect.bottom + 4;

                if (top + menuHeight > window.innerHeight - 8) {
                    top = Math.max(8, rect.top - menuHeight - 4);
                }

                menu.style.top = top + 'px';
                menu.style.left = left + 'px';
                menu.style.right = 'auto';
                menu.style.transform = 'none';
            }

            function floatInvoiceMenu(button) {
                var dropdown = button.closest('.invoice-action-dropdown');
                var menu = dropdown ? dropdown.querySelector('.dropdown-menu') : null;

                if (!dropdown || !menu) {
                    return;
                }

                if (activeInvoiceMenu && activeInvoiceMenu !== menu) {
                    restoreInvoiceMenu();
                }

                menu.__invoiceDropdownParent = dropdown;
                activeInvoiceMenu = menu;
                activeInvoiceButton = button;

                document.body.appendChild(menu);
                menu.classList.add('invoice-floating-menu', 'show');
                button.setAttribute('aria-expanded', 'true');
                dropdown.classList.add('show');
                positionInvoiceMenu(menu, button);
            }

            document.querySelectorAll('.invoice-action-dropdown .dropdown-toggle').forEach(function (button) {
                button.addEventListener('click', function (event) {
                    if (activeInvoiceButton === this && activeInvoiceMenu) {
                        event.preventDefault();
                        event.stopPropagation();
                        restoreInvoiceMenu();
                        return;
                    }

                    var clickedButton = this;

                    setTimeout(function () {
                        var dropdown = clickedButton.closest('.invoice-action-dropdown');
                        var menu = dropdown ? dropdown.querySelector('.dropdown-menu') : activeInvoiceMenu;

                        if (!menu || (activeInvoiceMenu === menu && !menu.classList.contains('show'))) {
                            restoreInvoiceMenu();
                            return;
                        }

                        floatInvoiceMenu(clickedButton);
                    }, 0);
                }, true);
            });

            document.addEventListener('click', function (event) {
                if (!activeInvoiceMenu) {
                    return;
                }

                if (activeInvoiceMenu.contains(event.target) && event.target.closest('.dropdown-item')) {
                    setTimeout(restoreInvoiceMenu, 0);
                    return;
                }

                if (activeInvoiceMenu.contains(event.target) || (activeInvoiceButton && activeInvoiceButton.contains(event.target))) {
                    return;
                }

                restoreInvoiceMenu();
            });

            window.addEventListener('resize', function () {
                if (activeInvoiceMenu && activeInvoiceButton) {
                    positionInvoiceMenu(activeInvoiceMenu, activeInvoiceButton);
                }
            });

            window.addEventListener('scroll', function () {
                if (activeInvoiceMenu && activeInvoiceButton) {
                    positionInvoiceMenu(activeInvoiceMenu, activeInvoiceButton);
                }
            }, true);
        });
    </script>
@endpush
