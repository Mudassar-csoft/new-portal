@extends('layouts.theme')

@section('title', 'Add Utility Bill')

@section('content')
    @php($canEditBills = $canUpdateBills && auth()->user()?->isAdmin())
    <div class="finance-shell">
        @include('partials.session-status-alert')
        @include('partials.validation-errors-alert')

        @if($canCreateBills)
            <section class="box-typical box-typical-dashboard panel panel-default finance-card">
                <header class="box-typical-header panel-heading finance-header">
                    <div>
                        <h3 class="panel-title">Bill Management <span class="text-muted">|</span> Add Utility Bill</h3>
                        <!-- <p class="text-muted mb-0">Add campus utility bill master details only. Payment amount will be entered later from the expense request form.</p> -->
                    </div>
                </header>
                <div class="box-typical-body panel-body">
                    <form method="POST" action="{{ route('finance.utility.bills.store') }}">
                        @csrf
                        <div class="form-row mt-3">
                            <div class="form-group col-lg-3 col-md-6">
                                <label class="form-label required">Campus / Franchise</label>
                                <select name="campus_id" class="form-control" required>
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
                                <div class="input-group">
                                    <select name="bill_type_id" id="billTypeSelect" class="form-control utility-bill-type-select" data-company-target="#billCompanyName" required>
                                        <option value="">- Select -</option>
                                        @foreach($billTypes as $type)
                                            <option
                                                value="{{ $type->id }}"
                                                data-company="{{ $type->company_name }}"
                                                data-service="{{ $type->service_name }}"
                                                @selected(old('bill_type_id') == $type->id)
                                            >
                                                {{ $type->service_name ?: $type->display_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @if($canCreateBillTypes)
                                        <span class="input-group-btn">
                                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#quickBillTypeModal">+</button>
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group col-lg-3 col-md-6">
                                <label class="form-label">Company Name</label>
                                <input type="text" id="billCompanyName" class="form-control" readonly>
                            </div>
                            <div class="form-group col-lg-3 col-md-6">
                                <label class="form-label required">Reference Number</label>
                                <input type="text" name="reference_number" class="form-control" value="{{ old('reference_number') }}" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-12">
                                <label class="form-label">Remarks</label>
                                <textarea name="remarks" class="form-control" rows="3" style="padding:10px;">{{ old('remarks') }}</textarea>
                            </div>
                        </div>

                        <div class="text-right mt-3">
                            <button type="submit" class="btn btn-inline btn-primary-outline">Save Bill</button>
                            <!-- <button  class="btn btn-inline btn-danger-outline">Cancel</button> -->
                        </div>
                    </form>
                </div>
            </section>
        @endif

        @if($canViewBills)
            <section class="box-typical box-typical-dashboard panel panel-default finance-card mt-3">
                <div class="box-typical-body panel-body">
                <div class="table-responsive">
                    <table class="table table-bordered finance-table">
                        <thead>
                            <tr>
                                <th>Sr#</th>
                                <th>Reference Number</th>
                                <th>Campus</th>
                                <!-- <th>Company</th> -->
                                <th>Bill Type</th>
                                <!-- <th>Remarks</th> -->
                                <!-- @if($canEditBills)
                                    <th>Action</th>
                                @endif -->
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($bills as $index => $bill)
                                <tr>
                                    <td>{{ $bills->firstItem() + $index }}</td>
                                    <td>{{ $bill->reference_number }}</td>
                                    <td>{{ $bill->campus->code ?? 'N/A' }}</td>
                                    <!-- <td>{{ $bill->billType->company_name ?? '-' }}</td> -->
                                    <td>{{ $bill->billType->service_name ?? $bill->billType->display_name ?? $bill->billType->name ?? 'N/A' }}</td>
                                    <!-- <td>{{ $bill->remarks ?: '-' }}</td> -->
                                    @if($canEditBills)
                                        <!-- <td class="utility-action-cell">
                                            <div class="dropdown utility-action-dropdown">
                                                <button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-toggle="dropdown">
                                                    Action
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-right action-key">
                                                    <a class="dropdown-item" href="#" data-toggle="modal" data-target="#editUtilityBillModal-{{ $bill->id }}">Edit</a>
                                                </div>
                                            </div>
                                        </td> -->
                                    @endif
                                    <td>{{ optional($bill->created_at)->format('d-M-Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $canEditBills ? 7 : 6 }}" class="text-center text-muted">No utility bills found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $bills->links() }}
                </div>
            </section>
        @endif
    </div>

    @if($canEditBills)
        @push('modals')
            @foreach($bills as $bill)
                <div class="modal fade" id="editUtilityBillModal-{{ $bill->id }}" tabindex="-1" role="dialog" aria-labelledby="editUtilityBillModalLabel-{{ $bill->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <form method="POST" action="{{ route('finance.utility.bills.update', $bill) }}">
                                @csrf
                                @method('PATCH')
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editUtilityBillModalLabel-{{ $bill->id }}">Edit Utility Bill</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-row">
                                        <div class="form-group col-lg-4 col-md-6">
                                            <label class="form-label required">Campus / Franchise</label>
                                            <select name="campus_id" class="form-control" required>
                                                @foreach($campuses as $campus)
                                                    <option value="{{ $campus->id }}" @selected((int) $bill->campus_id === (int) $campus->id)>
                                                        {{ $campus->code }} - {{ $campus->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group col-lg-4 col-md-6">
                                            <label class="form-label required">Bill Type</label>
                                            <select
                                                name="bill_type_id"
                                                class="form-control utility-bill-type-select"
                                                data-company-target="#editBillCompanyName-{{ $bill->id }}"
                                                required
                                            >
                                                @foreach($billTypes as $type)
                                                    <option
                                                        value="{{ $type->id }}"
                                                        data-company="{{ $type->company_name }}"
                                                        @selected((int) $bill->bill_type_id === (int) $type->id)
                                                    >
                                                        {{ $type->service_name ?: $type->display_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group col-lg-4 col-md-6">
                                            <label class="form-label">Company Name</label>
                                            <input type="text" id="editBillCompanyName-{{ $bill->id }}" class="form-control" readonly>
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group col-lg-6 col-md-6">
                                            <label class="form-label required">Reference Number</label>
                                            <input type="text" name="reference_number" class="form-control" value="{{ $bill->reference_number }}" required>
                                        </div>
                                        <div class="form-group col-lg-6 col-md-6">
                                            <label class="form-label">Remarks</label>
                                            <textarea name="remarks" class="form-control" rows="3" style="padding:10px;">{{ $bill->remarks }}</textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-inline btn-secondary-outline" data-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-inline btn-primary-outline">Update Utility Bill</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        @endpush
    @endif

    @if($canCreateBillTypes)
        <div class="modal fade" id="quickBillTypeModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form id="quickBillTypeForm">
                        <div class="modal-header">
                            <h4 class="modal-title">Add Bill Type</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <hr>
                        <div class="modal-body">
                            <div class="alert alert-danger d-none" id="quickBillTypeError"></div>
                            <div class="form-row">
                                <div class="form-group col-md-12">
                                    <label class="form-label">Company Name</label>
                                    <input type="text" name="company_name" class="form-control" placeholder="FESCO">
                                </div>
                                <div class="form-group col-md-12">
                                    <label class="form-label required">Bill Type</label>
                                    <input type="text" name="service_name" class="form-control" placeholder="Electricity" required>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-12">

                                <label class="form-label">Default Payee</label>
                                <select name="payee_id" class="form-control">
                                    <option value="">- Select -</option>
                                    @foreach($payees as $payee)
                                        <option value="{{ $payee->id }}">{{ $payee->full_name }} ({{ ucfirst($payee->type) }})</option>
                                    @endforeach
                                </select>
                            </div>
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
    @endif
@endsection

@push('styles')
    <style>
        .finance-shell { padding: 8px 0 16px; }
        .finance-header { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; }
        .required::after { content: ' *'; color: #e53935; }
        .finance-table thead th { background: #1ea7ff; color: #fff; }
        .input-group .btn { height: 32px; line-height: 1; }
        .utility-action-cell { min-width: 100px; }
        .utility-action-dropdown .dropdown-menu { min-width: 140px; }
        .modal-title{
            padding: 10px 25px 0px 25px;
        }
    </style>
@endpush

@push('scripts')
    <script>
        (function ($) {
            function syncBillCompany($select) {
                const target = $select.data('companyTarget');
                if (!target) {
                    return;
                }

                $(target).val($select.find('option:selected').data('company') || '');
            }

            $('#quickBillTypeForm').on('submit', function (event) {
                event.preventDefault();

                const $form = $(this);
                const $error = $('#quickBillTypeError');

                $.ajax({
                    url: @json(route('finance.utility.types.store')),
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Accept': 'application/json'
                    },
                    data: $form.serialize()
                }).done(function (response) {
                    const option = $('<option></option>')
                        .val(response.type.id)
                        .attr('data-company', response.type.company_name || '')
                        .attr('data-service', response.type.service_name || response.type.name || '')
                        .text(response.type.service_name || response.type.display_name || response.type.name);

                    $('#billTypeSelect').append(option).val(String(response.type.id));
                    syncBillCompany($('#billTypeSelect'));
                    $('#quickBillTypeModal').modal('hide');
                    $form.trigger('reset');
                    $error.addClass('d-none').text('');
                }).fail(function (xhr) {
                    const errors = xhr.responseJSON && xhr.responseJSON.errors ? xhr.responseJSON.errors : {};
                    const message = Object.values(errors).flat().join(' ') || 'Unable to save bill type.';
                    $error.removeClass('d-none').text(message);
                });
            });

            $(document).on('change', '.utility-bill-type-select', function () {
                syncBillCompany($(this));
            });

            $('.utility-bill-type-select').each(function () {
                syncBillCompany($(this));
            });
        })(jQuery);
    </script>
@endpush
