@extends('layouts.theme')

@section('title', 'Building Rent Setup')

@section('content')
    @php($canEditRent = $canUpdateRent && auth()->user()?->isAdmin())
    <div class="finance-shell">
        @include('partials.session-status-alert')
        @include('partials.validation-errors-alert')

        @if($canCreateRent)
            <section class="box-typical box-typical-dashboard panel panel-default finance-card">
                <header class="box-typical-header panel-heading finance-header">
                    <div>
                        <h3 class="panel-title">Building Rent Setup</h3>
                        <!-- <p class="text-muted mb-0">Add the active rent record for each campus so expense requests can fetch the correct monthly amount.</p> -->
                    </div>
                    <div class="finance-header-actions">
                        <a href="{{ route('finance.expense.add') }}" class="btn btn-primary-outline btn-sm">Create Expense Request</a>
                        <a href="{{ route('finance.expense.rent') }}" class="btn btn-danger-outline btn-sm">Rent Expense List</a>
                    </div>
                </header>
                <div class="box-typical-body panel-body">
                    <form method="POST" action="{{ route('finance.rent.store') }}" data-rent-calculator>
                        @csrf
                        <div class="form-row mt-2">
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
                                <label class="form-label required">Rent Agreement Date</label>
                                <input type="date" name="agreement_date" class="form-control" value="{{ old('agreement_date', now()->toDateString()) }}" required>
                            </div>
                            <div class="form-group col-lg-6 col-md-12">
                                <label class="form-label required">Address</label>
                                <input type="text" name="address" class="form-control" value="{{ old('address') }}" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-lg-3 col-md-6">
                                <label class="form-label required">Rent Amount</label>
                                <input type="number" step="0.01" min="0" name="rent_amount" class="form-control" value="{{ old('rent_amount') }}" data-rent-amount required>
                            </div>
                            <div class="form-group col-lg-3 col-md-6">
                                <label class="form-label">Increment Percentage</label>
                                <input type="number" step="0.01" min="0" name="increment_percentage" class="form-control" value="{{ old('increment_percentage', 0) }}" data-rent-increment>
                            </div>
                            <div class="form-group col-lg-3 col-md-6">
                                <label class="form-label">Actual Amount</label>
                                <input type="text" class="form-control" data-rent-actual readonly>
                            </div>
                            <div class="form-group col-lg-3 col-md-6">
                                <label class="form-label">Advance Payment</label>
                                <input type="number" step="0.01" min="0" name="advance_payment" class="form-control" value="{{ old('advance_payment', 0) }}">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-12">
                                <label class="form-label">Remarks</label>
                                <textarea name="remarks" class="form-control" rows="3" style="padding:10px;">{{ old('remarks') }}</textarea>
                            </div>
                        </div>

                        <div class="text-right">
                            <button type="submit" class="btn btn-inline btn-primary-outline">Save Building Rent</button>
                        </div>
                    </form>
                </div>
            </section>
        @endif

        @if($canViewRent)
            <section class="box-typical box-typical-dashboard panel panel-default finance-card mt-3">
                <div class="box-typical-body panel-body">
                <div class="table-responsive">
                    <table class="table table-bordered finance-table">
                        <thead>
                        <tr>
                            <th>Campus</th>
                            <th>Agreement Date</th>
                            <th>Address</th>
                            <th>Rent</th>
                            <th>Increment %</th>
                            <th>Actual Amount</th>
                            <th>Advance</th>
                            <th>Status</th>
                            @if($canEditRent)
                                <th>Action</th>
                            @endif
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($rents as $rent)
                            <tr>
                                <td>{{ $rent->campus->code ?? 'N/A' }}</td>
                                <td>{{ optional($rent->agreement_date)->format('d-M-Y') }}</td>
                                <td>{{ $rent->address }}</td>
                                <td>Rs. {{ number_format((float) $rent->rent_amount, 0) }}</td>
                                <td>{{ number_format((float) $rent->increment_percentage, 2) }}%</td>
                                <td>Rs. {{ number_format((float) $rent->current_amount, 0) }}</td>
                                <td>Rs. {{ number_format((float) $rent->advance_payment, 0) }}</td>
                                <td>
                                    <span class="badge {{ $rent->is_active ? 'badge-success' : 'badge-secondary' }}">
                                        {{ $rent->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                @if($canEditRent)
                                    <td class="rent-action-cell">
                                        <div class="dropdown rent-action-dropdown">
                                            <button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-toggle="dropdown">
                                                Action
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right action-key">
                                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#editRentModal-{{ $rent->id }}">Edit</a>
                                            </div>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $canEditRent ? 9 : 8 }}" class="text-center text-muted">No building rent record found.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $rents->links() }}
                </div>
            </section>
        @endif
    </div>
@endsection

@if($canEditRent)
    @push('modals')
        @foreach($rents as $rent)
            <div class="modal fade" id="editRentModal-{{ $rent->id }}" tabindex="-1" role="dialog" aria-labelledby="editRentModalLabel-{{ $rent->id }}" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <form method="POST" action="{{ route('finance.rent.update', $rent) }}" data-rent-calculator>
                            @csrf
                            @method('PATCH')
                            <div class="modal-header">
                                <h5 class="modal-title" id="editRentModalLabel-{{ $rent->id }}">Edit Building Rent</h5>
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
                                                <option value="{{ $campus->id }}" @selected((int) $rent->campus_id === (int) $campus->id)>
                                                    {{ $campus->code }} - {{ $campus->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group col-lg-4 col-md-6">
                                        <label class="form-label required">Rent Agreement Date</label>
                                        <input type="date" name="agreement_date" class="form-control" value="{{ optional($rent->agreement_date)->toDateString() }}" required>
                                    </div>
                                    <div class="form-group col-lg-4 col-md-6">
                                        <label class="form-label required">Status</label>
                                        <select name="is_active" class="form-control" required>
                                            <option value="1" @selected($rent->is_active)>Active</option>
                                            <option value="0" @selected(!$rent->is_active)>Inactive</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-12">
                                        <label class="form-label required">Address</label>
                                        <input type="text" name="address" class="form-control" value="{{ $rent->address }}" required>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-lg-3 col-md-6">
                                        <label class="form-label required">Rent Amount</label>
                                        <input type="number" step="0.01" min="0" name="rent_amount" class="form-control" value="{{ (float) $rent->rent_amount }}" data-rent-amount required>
                                    </div>
                                    <div class="form-group col-lg-3 col-md-6">
                                        <label class="form-label">Increment Percentage</label>
                                        <input type="number" step="0.01" min="0" name="increment_percentage" class="form-control" value="{{ (float) $rent->increment_percentage }}" data-rent-increment>
                                    </div>
                                    <div class="form-group col-lg-3 col-md-6">
                                        <label class="form-label">Actual Amount</label>
                                        <input type="text" class="form-control" data-rent-actual readonly>
                                    </div>
                                    <div class="form-group col-lg-3 col-md-6">
                                        <label class="form-label">Advance Payment</label>
                                        <input type="number" step="0.01" min="0" name="advance_payment" class="form-control" value="{{ (float) $rent->advance_payment }}">
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-12">
                                        <label class="form-label">Remarks</label>
                                        <textarea name="remarks" class="form-control" rows="3" style="padding:10px;">{{ $rent->remarks }}</textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-inline btn-secondary-outline" data-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-inline btn-primary-outline">Update Building Rent</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    @endpush
@endif

@push('styles')
    <style>
        .finance-shell { padding: 8px 0 16px; }
        .finance-header { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; }
        .finance-header-actions { display: flex; gap: 8px; flex-wrap: wrap; }
        .required::after { content: ' *'; color: #e53935; }
        .finance-table thead th { background: #1ea7ff; color: #fff; }
        .rent-action-cell { min-width: 100px; }
        .rent-action-dropdown .dropdown-menu { min-width: 140px; }
    </style>
@endpush

@push('scripts')
    <script>
        (function ($) {
            function updateActualRent($scope) {
                const amount = parseFloat($scope.find('[data-rent-amount]').val() || 0);
                const increment = parseFloat($scope.find('[data-rent-increment]').val() || 0);
                const actual = amount + ((amount * increment) / 100);

                $scope.find('[data-rent-actual]').val('Rs. ' + actual.toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
            }

            $('[data-rent-calculator]').each(function () {
                updateActualRent($(this));
            });

            $(document).on('input', '[data-rent-amount], [data-rent-increment]', function () {
                updateActualRent($(this).closest('[data-rent-calculator]'));
            });
        })(jQuery);
    </script>
@endpush
