@extends('layouts.theme')

@section('title', 'Add Expense')

@section('content')
    @php
        $statusColors = [
            'pending' => 'badge-warning',
            'approved' => 'badge-info',
            'paid' => 'badge-success',
            'rejected' => 'badge-danger',
            'reversed' => 'badge-secondary',
        ];
    @endphp

    <div class="finance-shell">
        @include('partials.session-status-alert')
        @include('partials.session-error-alert')
        @include('partials.validation-errors-alert')

        <section class="box-typical box-typical-dashboard panel panel-default finance-card">
            <header class="box-typical-header panel-heading finance-header">
                <div>
                    <h3 class="panel-title">Expense Management <span class="text-muted">|</span> Add Expense Request</h3>
                    <!-- <p class="text-muted mb-0">Select campus first, then choose expense type. Rent and utility bills auto-load their source records.</p> -->
                </div>
                <!-- <div class="finance-header-actions">
                    <a href="{{ route('finance.rent.index') }}" class="btn btn-danger btn-sm">Building Rent Setup</a>
                    <a href="{{ route('finance.utility.bills') }}" class="btn btn-primary btn-sm">Add Utility Bill</a>
                </div> -->
            </header>
            <div class="box-typical-body panel-body">
                <form method="POST" action="{{ route('finance.expense.store') }}" id="expenseRequestForm">
                    @csrf
                    <div class="form-row mt-2">
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label required">Campus / Franchise</label>
                            <select name="campus_id" id="campusSelect" class="form-control" required>
                                <option value="">- Select -</option>
                                @foreach($campuses as $campus)
                                    <option value="{{ $campus->id }}" @selected(old('campus_id') == $campus->id)>
                                        {{ $campus->code }} - {{ $campus->name }} ({{ ucfirst($campus->campus_type) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label"> Payee</label>
                            <select name="payee_id" class="form-control">
                                <option value="">- Select -</option>
                                @foreach($payees as $payee)
                                    <option value="{{ $payee->id }}">{{ $payee->full_name }} ({{ ucfirst($payee->type) }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label required">Expense Type</label>
                            <select name="expense_type_id" id="expenseTypeSelect" class="form-control" required>
                                <option value="">- Select -</option>
                                <option value="__add_new__" >+ Add Expense Type</option>
                                @foreach($expenseTypes as $type)
                                <option
                                value="{{ $type->id }}"
                                        data-category="{{ $type->category ?: 'general' }}"
                                        @selected(old('expense_type_id') == $type->id)
                                        >
                                        {{ $type->name }}
                                    </option>
                                    @endforeach
                            </select>
                            <!-- <small class="text-muted">If it is missing, add a new expense type from the popup.</small> -->
                        </div>
                        @include('finance.partials.payment-date-amount-fields')
                        @include('finance.partials.payment-method-field')
                         <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label">Payment Ref No</label>
                            <input type="text" name="payment_ref_no" class="form-control" value="{{ old('payment_ref_no') }}">
                        </div>
                     
                        <div class="form-group col-lg-3 col-md-6" id="amountFieldCol">
                            <label class="form-label required">Amount (PKR)</label>
                            <input type="number" step="0.01" min="1" name="amount" id="expenseAmount" class="form-control" value="{{ old('amount') }}" required>
                        </div>
                  
                    </div>

                    <input type="hidden" name="rent_id" id="rentIdField" value="{{ old('rent_id') }}">

                    <!-- <div class="finance-source-card alert alert-info" id="expenseTypeHint">
                        Select campus and expense type to load rent or utility bill details.
                    </div> -->

                    <section class="finance-source-section d-none" id="rentSourceSection">
                        <div class="source-section-title">Building Rent</div>
                        <div class="form-row" id="rentFieldsRow">
                            <div class="form-group col-lg-3 col-md-6">
                                <label class="form-label required">Rent Month</label>
                                <input type="month" name="expense_month" id="rentMonthSelect" class="form-control" value="{{ old('expense_month') }}">
                                <small class="text-danger d-none" id="rentMonthMessage"></small>
                            </div>
                        </div>
                    </section>

                    <section class="finance-source-section d-none" id="utilitySourceSection">
                        <div class="source-section-title">Utility Bill</div>
                        <div class="form-row">
                            <div class="form-group col-lg-4 col-md-6">
                                <label class="form-label required">Bill Type</label>
                                <select id="utilityBillTypeFilter" class="form-control">
                                    <option value="">- Select Bill Type -</option>
                                </select>
                            </div>
                            <div class="form-group col-lg-4 col-md-6">
                                <label class="form-label">Company Name</label>
                                <input type="text" id="utilityCompanyName" class="form-control" readonly>
                            </div>
                            <div class="form-group col-lg-4 col-md-6">
                                <label class="form-label required">Reference No</label>
                                <select name="bill_id" id="utilityBillSelect" class="form-control">
                                    <option value="">- Select Bill -</option>
                                </select>
                            </div>
                        </div>
                        <!-- <small class="text-muted">Reference numbers are loaded from the Utility Bill setup for the selected campus.</small> -->
                    </section>

                    

                    <div class="form-row">
                        <div class="form-group col-12">
                            <label class="form-label">Remarks</label>
                            <textarea name="remarks" class="form-control" rows="3" style="padding:10px;">{{ old('remarks') }}</textarea>
                        </div>
                    </div>

                    <div class="text-right mt-3 mb-2">
                        <button type="submit" class="btn btn-inline btn-primary-outline" id="expenseSubmitBtn" >Submit For Approval</button>
                        <button type="submit" class="btn btn-inline btn-danger-outline" id="expenseSubmitBtn">Cancel</button>
                    </div>
                </form>
            </div>
        </section>
        <section class="box-typical box-typical-dashboard panel panel-default finance-card mt-3">
            <header class="box-typical-header panel-heading finance-header">
                <h3 class="panel-title">Recent Expense Requests</h3>
                <a href="{{ route('finance.expense.all') }}" class="btn btn-danger btn-sm">View All</a>
            </header>
            <div class="box-typical-body panel-body">
                <div class="table-responsive">
                    <table class="table table-bordered finance-table">
                        <thead>
                        <tr>
                            <th>Voucher</th>
                            <th>Expense</th>
                            <th>Campus</th>
                            <th>Source</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($recentExpenses as $expense)
                            @php
                                $canManageExpense = auth()->user()?->hasAnyPermission(\App\Support\AccessMap::financeExpenseManagePermissions($expense->category)) ?? false;
                            @endphp
                            <tr>
                                <td>{{ $expense->voucher_no }}</td>
                                <td>{{ $expense->expenseType->name ?? ucfirst($expense->category ?? 'general') }}</td>
                                <td>{{ $expense->campus->code ?? 'N/A' }}</td>
                                <td>
                                    @if($expense->category === 'utility')
                                        {{ $expense->bill->billType->display_name ?? 'Utility Bill' }} / {{ $expense->bill->reference_number ?? 'N/A' }}
                                    @elseif($expense->category === 'rent')
                                        {{ $expense->expense_month?->format('M Y') ?? 'Rent' }}
                                    @else
                                        {{ $expense->payee->full_name ?? 'N/A' }}
                                    @endif
                                </td>
                                <td>Rs. {{ number_format((float) $expense->amount, 0) }}</td>
                                <td><span class="badge {{ $statusColors[$expense->status] ?? 'badge-secondary' }}">{{ ucfirst($expense->status) }}</span></td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-toggle="dropdown">
                                            Action
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            @if($canManageExpense && $expense->status === 'pending')
                                                <form method="POST" action="{{ route('finance.expense.approve', $expense) }}">
                                                    @csrf
                                                    <button class="dropdown-item text-success" type="submit">Approve</button>
                                                </form>
                                                <form method="POST" action="{{ route('finance.expense.reject', $expense) }}">
                                                    @csrf
                                                    <input type="hidden" name="reason" value="Rejected by admin">
                                                    <button class="dropdown-item text-danger" type="submit">Reject</button>
                                                </form>
                                            @endif
                                            @if($canManageExpense && $expense->status === 'approved')
                                                @include('finance.partials.pay_now_modal', ['expense' => $expense, 'paymentMethods' => $paymentMethods, 'canAdjustAmount' => $canManageExpense])
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">No expense requests yet.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>

    <div class="modal fade" id="quickExpenseTypeModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="quickExpenseTypeForm">
                    <div class="modal-header">
                        <h4 class="modal-title">Add Expense Type</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger d-none" id="quickExpenseTypeError"></div>
                        <div class="form-group">
                            <label class="form-label required">Expense Type Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label required">Category</label>
                            <select name="category" class="form-control" required>
                                <option value="general">General</option>
                                <option value="rent">Building Rent</option>
                                <option value="utility">Utility Bill</option>
                                <option value="marketing">Marketing</option>
                                <option value="asset">Asset Purchase</option>
                                <option value="payroll">Payroll</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-inline btn-danger-outline" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-inline btn-primary-outline">Save Type</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .finance-shell { padding: 8px 0 16px; }
        .finance-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }
        .finance-header-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .finance-table thead th { background: #1ea7ff; color: #fff; }
        .required::after { content: ' *'; color: #e53935; }
        .finance-source-card,
        .finance-source-section {
            border: 1px solid #d9e5ec;
            border-radius: 6px;
            padding: 14px;
            margin-bottom: 16px;
            background: #f7fbfe;
        }
        .source-section-title {
            font-size: 0.75rem;
            font-weight: 700;
            color: #2c3e50;
            text-transform: uppercase;
            margin-bottom: 10px;
        }
        .dropdown-menu form { margin: 0; }
        .dropdown-menu form .dropdown-item { width: 100%; text-align: left; background: transparent; border: 0; }
    </style>
@endpush

@push('scripts')
    @include('finance.partials.pay_now_modal_script')
    <script>
        (function ($) {
            const endpoints = {
                expenseTypeStore: @json(route('finance.expense.types.store')),
                rentMeta: @json(route('finance.expense.rentMeta')),
                utilityLookup: @json(route('finance.utility.lookup'))
            };
            let oldUtilityBillId = @json(old('bill_id'));
            const oldExpenseMonth = @json(old('expense_month'));

            let currentRentData = null;
            let blockedRentMonths = {};
            let currentUtilityPayload = { billTypes: [], bills: [] };

            function selectedCategory() {
                return $('#expenseTypeSelect option:selected').data('category') || '';
            }

            function formatCurrency(value) {
                if (value === null || value === undefined || value === '') {
                    return '';
                }

                return 'Rs. ' + Number(value).toLocaleString();
            }

            function resetUtilitySelection() {
                currentUtilityPayload = { billTypes: [], bills: [] };
                $('#utilityBillTypeFilter').html('<option value="">- Select Bill Type -</option>');
                $('#utilityBillSelect').html('<option value="">- Select Bill -</option>');
                $('#utilityCompanyName').val('');
            }

            function updateRentMonthState() {
                const selectedMonth = $('#rentMonthSelect').val();
                const messageBox = $('#rentMonthMessage');
                const submitButton = $('#expenseSubmitBtn');

                if (selectedMonth && blockedRentMonths[selectedMonth]) {
                    messageBox
                        .removeClass('d-none')
                        .text('Rent is already ' + blockedRentMonths[selectedMonth] + ' for ' + selectedMonth + '. Select another month.');
                    submitButton.prop('disabled', true);
                    $('#expenseAmount').val('');
                    return;
                }

                messageBox.addClass('d-none').text('');
                submitButton.prop('disabled', false);

                if (currentRentData) {
                    $('#expenseAmount').val(currentRentData.current_amount || '');
                }
            }

            function fillUtilityDetails() {
                const selectedId = $('#utilityBillSelect').val();
                const bill = currentUtilityPayload.bills.find(item => String(item.id) === String(selectedId));

                if (!bill) {
                    syncUtilityCompany();
                    if (selectedCategory() === 'utility') {
                        $('#expenseAmount').val('');
                    }
                    return;
                }

                $('#utilityCompanyName').val(bill.company_name || '');
                $('#expenseAmount').val(bill.balance > 0 ? bill.balance : (bill.amount > 0 ? bill.amount : ''));
            }

            function renderUtilityBills() {
                const filterValue = $('#utilityBillTypeFilter').val();
                const options = ['<option value="">- Select Bill -</option>'];

                if (!filterValue) {
                    $('#utilityBillSelect').html(options.join(''));
                    fillUtilityDetails();
                    return;
                }

                const bills = currentUtilityPayload.bills.filter(item => String(item.bill_type_id) === String(filterValue));

                bills.forEach(function (bill) {
                    options.push(
                        '<option value="' + bill.id + '">' +
                        bill.reference_number +
                        '</option>'
                    );
                });

                $('#utilityBillSelect').html(options.join(''));
                syncUtilityCompany();

                if (oldUtilityBillId) {
                    $('#utilityBillSelect').val(String(oldUtilityBillId));
                    oldUtilityBillId = null;
                }

                fillUtilityDetails();
            }

            function syncUtilityCompany() {
                const selectedType = currentUtilityPayload.billTypes.find(function (type) {
                    return String(type.id) === String($('#utilityBillTypeFilter').val());
                });

                $('#utilityCompanyName').val(selectedType ? (selectedType.company_name || '') : '');
            }

            function loadUtilityBills() {
                const campusId = $('#campusSelect').val();

                resetUtilitySelection();
                if (!campusId) {
                    $('#expenseTypeHint').removeClass('d-none alert-danger').addClass('alert-info').text('Select campus and expense type to load rent or utility bill details.');
                    return;
                }

                $.get(endpoints.utilityLookup, { campus_id: campusId })
                    .done(function (response) {
                        currentUtilityPayload = response;

                        const typeOptions = ['<option value="">- Select Bill Type -</option>'];
                        response.billTypes.forEach(function (type) {
                            typeOptions.push(
                                '<option value="' + type.id + '" data-company="' + (type.company_name || '') + '">' +
                                (type.service_name || type.name) +
                                '</option>'
                            );
                        });

                        $('#utilityBillTypeFilter').html(typeOptions.join(''));
                        if (response.bills.length === 0) {
                            $('#expenseTypeHint').removeClass('d-none alert-info').addClass('alert-danger').text('No utility bill reference found for the selected campus.');
                        } else {
                            $('#expenseTypeHint').addClass('d-none').text('');
                        }
                        renderUtilityBills();
                    });
            }

            function loadRentDetails() {
                const campusId = $('#campusSelect').val();

                currentRentData = null;
                blockedRentMonths = {};
                $('#rentIdField').val('');

                if (!campusId) {
                    $('#expenseTypeHint').removeClass('d-none alert-danger').addClass('alert-info').text('Select campus and expense type to load rent or utility bill details.');
                    return;
                }

                $.get(endpoints.rentMeta, { campus_id: campusId })
                    .done(function (response) {
                        if (!response.rent) {
                            $('#expenseTypeHint').removeClass('d-none alert-info').addClass('alert-danger').text(response.message || 'No building rent found for the selected campus.');
                            $('#expenseSubmitBtn').prop('disabled', true);
                            return;
                        }

                        currentRentData = response.rent;
                        blockedRentMonths = response.blockedMonths || {};
                        $('#expenseTypeHint').addClass('d-none').text('');
                        $('#rentIdField').val(response.rent.id);
                        $('#expenseAmount').val(response.rent.current_amount || '');

                        if (oldExpenseMonth) {
                            $('#rentMonthSelect').val(oldExpenseMonth);
                        }

                        updateRentMonthState();
                    });
            }

            function toggleExpenseSourceSections() {
                const category = selectedCategory();
                const amountField = $('#expenseAmount');
                const amountFieldCol = $('#amountFieldCol');

                $('#rentSourceSection, #utilitySourceSection').addClass('d-none');
                $('#expenseTypeHint').removeClass('d-none alert-danger').addClass('alert-info').text('Select campus and expense type to load rent or utility bill details.');
                $('#expenseSubmitBtn').prop('disabled', false);

                if (category === 'rent') {
                    amountFieldCol.appendTo('#rentFieldsRow');
                    $('#amountRow').addClass('d-none');
                    $('#rentSourceSection').removeClass('d-none');
                    amountField.prop('readonly', true);
                    loadRentDetails();
                    return;
                }

                amountFieldCol.appendTo('#amountRow');
                $('#amountRow').removeClass('d-none');

                if (category === 'utility') {
                    $('#utilitySourceSection').removeClass('d-none');
                    amountField.prop('readonly', false);
                    loadUtilityBills();
                    return;
                }

                amountField.prop('readonly', false);
                resetUtilitySelection();
                currentRentData = null;
                blockedRentMonths = {};
                $('#rentIdField').val('');
               / $('#expenseTypeHint').removeClass('d-none alert-danger').addClass('alert-info').text('This expense type uses a manual amount and then goes to approval.');
            }

            $('#campusSelect, #expenseTypeSelect').on('change', function () {
                if ($('#expenseTypeSelect').val() === '__add_new__') {
                    $('#quickExpenseTypeModal').modal('show');
                    $('#expenseTypeSelect').val('');
                    return;
                }

                toggleExpenseSourceSections();
            });
            $('#utilityBillTypeFilter').on('change', function () {
                syncUtilityCompany();
                renderUtilityBills();
            });
            $('#utilityBillSelect').on('change', fillUtilityDetails);
            $('#rentMonthSelect').on('change', updateRentMonthState);

            $('#quickExpenseTypeForm').on('submit', function (event) {
                event.preventDefault();

                const $form = $(this);
                const $error = $('#quickExpenseTypeError');

                $.ajax({
                    url: endpoints.expenseTypeStore,
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Accept': 'application/json'
                    },
                    data: $form.serialize()
                }).done(function (response) {
                    const option = $('<option></option>')
                        .val(response.type.id)
                        .attr('data-category', response.type.category)
                        .text(response.type.name);

                    $('#expenseTypeSelect').append(option).val(String(response.type.id)).trigger('change');
                    $('#quickExpenseTypeModal').modal('hide');
                    $form.trigger('reset');
                    $error.addClass('d-none').text('');
                }).fail(function (xhr) {
                    const errors = xhr.responseJSON && xhr.responseJSON.errors ? xhr.responseJSON.errors : {};
                    const message = Object.values(errors).flat().join(' ') || 'Unable to save expense type.';
                    $error.removeClass('d-none').text(message);
                });
            });

            toggleExpenseSourceSections();
        })(jQuery);
    </script>
@endpush
