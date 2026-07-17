@extends('layouts.theme')

@section('title', 'Payables')

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
            <header class="box-typical-header panel-heading finance-header d-flex">
                <h3 class="panel-title">{{ (($filters['scope'] ?? '') === 'open') ? 'Open Payables' : 'Payables' }}</h3>
                <!-- <a href="{{ route('finance.expense.add') }}" class="btn btn-primary btn-sm">Add Expense</a> -->
            </header>
            <div class="box-typical-body panel-body">
                <form class="mb-3" method="GET" action="{{ route('finance.payables') }}">
                    @if(($filters['scope'] ?? '') === 'open')
                        <input type="hidden" name="scope" value="open">
                    @endif
                    <div class="form-row mt-3">
                        <div class="form-group col-md-4">
                            <label class="form-label required">Campus</label>
                            <select class="form-control" name="campus_id">
                                <option value="">All Campuses</option>
                                @foreach($campuses as $campus)
                                    <option value="{{ $campus->id }}" @selected(($filters['campus_id'] ?? null) == $campus->id)>
                                        {{ $campus->code }} - {{ $campus->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label class="form-label required">Status</label>
                            <select class="form-control" name="status">
                                <option value="">All</option>
                                @foreach(['pending', 'approved', 'paid', 'rejected', 'reversed'] as $status)
                                    <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>
                                        {{ ucfirst($status) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-3 d-flex align-items-end mt-4 pt-2 justify-content-end ">
                            <button type="submit" class="btn btn-inline btn-primary-outline ">Filter</button>
                            <a
                                href="{{ route('finance.payables', (($filters['scope'] ?? '') === 'open') ? ['scope' => 'open'] : []) }}"
                                class="btn btn-inline btn-danger-outline"
                            >
                                Reset
                            </a>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered finance-table">
                        <thead>
                            <tr>
                                <th>Voucher</th>
                                <th>Expense Type</th>
                                <th>Payee</th>
                                <th>Campus</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payables as $expense)
                                @php
                                    $canManageExpense = auth()->user()?->hasAnyPermission(\App\Support\AccessMap::financeExpenseManagePermissions($expense->category)) ?? false;
                                @endphp
                                <tr>
                                    <td>{{ $expense->voucher_no ?? 'N/A' }}</td>
                                    <td>{{ $expense->expenseType->name ?? ucfirst($expense->category ?? 'expense') }}</td>
                                    <td>{{ $expense->payee->full_name ?? 'N/A' }}</td>
                                    <td>{{ $expense->campus->code ?? 'N/A' }}</td>
                                    <td>{{ optional($expense->payment_date)->format('d-M-Y') }}</td>
                                    <td>Rs. {{ number_format((float) $expense->amount, 0) }}</td>
                                    <td><span class="badge {{ $statusColors[$expense->status] ?? 'badge-secondary' }}">{{ ucfirst($expense->status) }}</span></td>
                                    <td>
                                        <div class="dropdown payable-action-dropdown btn-group">
                                            <button class="btn btn-primary btn-sm dropdown-toggle" type="button" aria-haspopup="true" aria-expanded="false">
                                                Action
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                @if($expense->attachment_path)
                                                    <a class="dropdown-item" href="{{ asset('storage/' . $expense->attachment_path) }}" target="_blank">View Image</a>
                                                @endif
                                                @if($canManageExpense && $expense->status === 'pending')
                                                    <form method="POST" action="{{ route('finance.expense.approve', $expense) }}">
                                                        @csrf
                                                        <button class="dropdown-item text-success" type="submit">✅ Approve</button>
                                                    </form>
                                                    <form method="POST" action="{{ route('finance.expense.reject', $expense) }}">
                                                        @csrf
                                                        <input type="hidden" name="reason" value="Rejected by admin">
                                                        <button class="dropdown-item text-danger" type="submit">❌  Reject</button>
                                                    </form>
                                                @endif
                                                @if($canManageExpense && $expense->status === 'approved')
                                                    @include('finance.partials.pay_now_modal', ['expense' => $expense, 'paymentMethods' => $paymentMethods, 'canAdjustAmount' => $canManageExpense])
                                                @endif
                                                @if(!$canManageExpense && $expense->status !== 'approved')
                                                    <span class="dropdown-item text-muted">Permission required</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">No payable records found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $payables->links() }}
            </div>
        </section>
    </div>
@endsection

@push('styles')
    <style>
         

        .finance-shell { padding: 8px 0 16px; }
        .finance-header { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; }
        .finance-table thead th { background: #1ea7ff; color: #fff; }
        .dropdown-menu form { margin: 0; }
        .dropdown-menu form .dropdown-item { width: 100%; text-align: left; background: transparent; border: 0; }

        .payable-action-dropdown .dropdown-menu {
            display: none;
        }

        .payable-action-dropdown.show .dropdown-menu,
        .payable-action-dropdown .dropdown-menu.show {
            display: block !important;
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(function () {
            function closeAllPayableDropdowns() {
                $('.payable-action-dropdown.show').each(function () {
                    var $d = $(this);
                    $d.removeClass('show');
                    $d.children('.dropdown-toggle').attr('aria-expanded', 'false');
                    $d.children('.dropdown-menu').removeClass('show').removeAttr('style');
                });
            }

            $(document).on('click.financePayables', '.payable-action-dropdown .dropdown-toggle', function (event) {
                event.preventDefault();
                event.stopPropagation();
                event.stopImmediatePropagation();

                var $toggle = $(this);
                var $dropdown = $toggle.closest('.payable-action-dropdown');
                var $menu = $dropdown.children('.dropdown-menu');

                if (!$menu.length) {
                    return;
                }

                var wasOpen = $dropdown.hasClass('show');
                closeAllPayableDropdowns();

                if (wasOpen) {
                    return;
                }

                var rect = $toggle.get(0).getBoundingClientRect();
                var menuEl = $menu.get(0);
                var viewportHeight = window.innerHeight || document.documentElement.clientHeight || 0;
                var rightOffset = Math.max(0, window.innerWidth - Math.round(rect.right));
                var menuHeight = $menu.outerHeight() || (menuEl ? menuEl.scrollHeight : 0) || 260;
                var belowTop = Math.round(rect.bottom) + 4;
                var aboveTop = Math.round(rect.top) - menuHeight - 4;
                var topPos = belowTop;
                var maxHeight = Math.max(180, viewportHeight - 24);

                if ((belowTop + menuHeight) > (viewportHeight - 12)) {
                    topPos = aboveTop >= 12 ? aboveTop : 12;
                    maxHeight = Math.max(180, Math.min(menuHeight, viewportHeight - topPos - 12));
                }

                $menu.attr('style',
                    'position:fixed !important;' +
                    'top:' + (topPos -5) + 'px !important;' +
                    'right:' + (rightOffset + 66) + 'px !important;' +
                    'left:auto !important;' +
                    'bottom:auto !important;' +
                    'margin:0 !important;' +
                    'transform:none !important;' +
                    'min-width:220px !important;' +
                    'max-height:' + maxHeight + 'px !important;' +
                    'overflow-y:auto !important;' +
                    'display:block !important;' +
                    'z-index:99999 !important;'
                );

                $dropdown.addClass('show');
                $menu.addClass('show');
                $toggle.attr('aria-expanded', 'true');
            });

            $(document).on('click.financePayables', function (event) {
                if ($(event.target).closest('.payable-action-dropdown').length) {
                    return;
                }
                closeAllPayableDropdowns();
            });

            $(window).on('resize.financePayables scroll.financePayables', function () {
                closeAllPayableDropdowns();
            });
        });
    </script>
    @include('finance.partials.pay_now_modal_script')
@endpush
