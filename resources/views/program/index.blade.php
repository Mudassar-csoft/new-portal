@extends('layouts.theme')

@section('title', $pageTitle)

@section('content')
    @php
        $filters = $filters ?? ['scope' => 'all', 'program_type' => null, 'status' => null, 'campus_id' => null, 'search' => null];
        $scopeCards = $scopeCards ?? [];
        $badgeClasses = [
            'active' => 'label-success',
            'inactive' => 'label-default',
        ];
    @endphp

    <div class="program-index-shell">
        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <div class="box-typical box-typical-dashboard panel panel-default program-index-card">
            <header class="box-typical-header panel-heading d-flex justify-content-between">
                <div>
                    <h3 class="panel-title mb-0">{{ $pageTitle }}</h3>
                    <!-- <small class="text-muted">{{ $pageDescription }}</small> -->
                </div>
                <a href="{{ route('program.create') }}" class="btn btn-primary">Create Programme</a>
            </header>
            <div class="box-typical-body panel-body">
                <div class="program-scope-grid">
                    @foreach($scopeCards as $card)
                        <a href="{{ route('program.index', array_filter(array_merge(request()->except('page', 'scope'), ['scope' => $card['scope'] !== 'all' ? $card['scope'] : null]))) }}"
                           class="program-scope-card {{ ($filters['scope'] ?? 'all') === $card['scope'] ? 'is-active' : '' }}">
                           <strong>{{ number_format((int) $card['count']) }}</strong>
                           <span class="program-scope-label">{{ $card['label'] }}</span>
                        </a>
                    @endforeach
                </div>

                <form method="GET" action="{{ route('program.index') }}" class="program-filter-form">
                    <input type="hidden" name="scope" value="{{ $filters['scope'] ?? 'all' }}">
                    <div class="form-row program-filter-row">
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label">Programme Type</label>
                            <select class="form-control" name="program_type">
                                <option value="">All Types</option>
                                @foreach($typeOptions as $type)
                                    <option value="{{ $type }}" @selected(($filters['program_type'] ?? '') === $type)>{{ ucwords($type) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label">Status</label>
                            <select class="form-control" name="status">
                                <option value="">All Statuses</option>
                                @foreach(['active' => 'Active', 'inactive' => 'Inactive'] as $key => $label)
                                    <option value="{{ $key }}" @selected(($filters['status'] ?? '') === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label">Discount Campus</label>
                            <select class="form-control" name="campus_id">
                                <option value="">All Campuses</option>
                                @foreach($campuses as $campus)
                                    <option value="{{ $campus->id }}" @selected(($filters['campus_id'] ?? null) == $campus->id)>
                                        {{ $campus->code }} - {{ $campus->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-lg-3 col-md-6 col-lg-3 col-md-6-wide">
                            <label class="form-label">Search</label>
                            <input type="text" class="form-control" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Programme title, code, type, remarks, or discount campus">
                        </div>
                        <div class="form-group program-filter-actions">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="{{ route('program.index', array_filter(['scope' => ($filters['scope'] ?? 'all') !== 'all' ? $filters['scope'] : null])) }}" class="btn btn-danger">Reset</a>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover table-bordered program-table">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Programme</th>
                                <th>Type</th>
                                <th>Fee</th>
                                <th>Duration</th>
                                <th>Installments</th>
                                <th>Batches</th>
                                <th>Students</th>
                                <th>Discounts</th>
                                <th>Outline</th>
                                <th>Status</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($programs as $program)
                                <tr>
                                    <td>{{ $program->code }}</td>
                                    <td>
                                        <strong>{{ $program->title ?? $program->name }}</strong>
                                        @if($program->remarks)
                                            <br>
                                            <span class="text-muted">{{ \Illuminate\Support\Str::limit($program->remarks, 80) }}</span>
                                        @endif
                                    </td>
                                    <td>{{ ucwords($program->program_type ?? 'n/a') }}</td>
                                    <td>{{ number_format((float) $program->fee, 2) }}</td>
                                    <td>
                                        {{ number_format((int) ($program->duration_weeks ?? 0)) }} weeks
                                        @if($program->discount_limit !== null)
                                            <br>
                                            <span class="text-muted">Limit {{ rtrim(rtrim(number_format((float) $program->discount_limit, 2), '0'), '.') }}%</span>
                                        @endif
                                    </td>
                                    <td>{{ number_format((int) ($program->installments ?? 0)) }}</td>
                                    <td>{{ number_format((int) ($program->batches_count ?? 0)) }}</td>
                                    <td>{{ number_format((int) ($program->admissions_count ?? 0)) }}</td>
                                    <td>
                                        @if($program->campusDiscounts->isEmpty())
                                            <span class="text-muted">No discounts</span>
                                        @else
                                            @foreach($program->campusDiscounts->sortBy(fn ($discount) => [$discount->campus_id === null ? 0 : 1, $discount->campus?->name ?? '']) as $discount)
                                                <div class="program-discount-line">
                                                    <strong>{{ rtrim(rtrim(number_format((float) $discount->discount_percent, 2), '0'), '.') }}%</strong>
                                                    <span class="text-muted">{{ $discount->campus?->name ?? 'All campuses' }}</span>
                                                    <span class="label {{ ($discount->status ?? 'active') === 'active' ? 'label-success' : 'label-default' }}">
                                                        {{ ucfirst($discount->status ?? 'active') }}
                                                    </span>
                                                </div>
                                            @endforeach
                                        @endif
                                    </td>
                                    <td>
                                        @if($program->outline_path)
                                            <a href="{{ route('program.outline', $program) }}" class="btn btn-xs btn-default">Download</a>
                                        @else
                                            <span class="text-muted">None</span>
                                        @endif
                                    </td>
                                    <td><span class="label {{ $badgeClasses[$program->status] ?? 'label-default' }}">{{ ucfirst($program->status ?? 'active') }}</span></td>
                                    <td class="text-right">
                                        @include('program.partials.action', ['actionId' => 'program-action-' . $program->id])
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="12" class="text-center text-muted">No programmes found for the selected filters.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $programs->links() }}
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .program-index-shell {
            padding: 10px;
        }

        .program-index-card {
            max-width: 1450px;
            margin: 0 auto;
        }

        .program-scope-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 14px;
            margin-bottom: 18px;
            padding:10px;
        }

        .program-scope-card {
            display: block;
            height:25vh;
            border: 1px solid #dbe5f1;
            border-radius: 12px;
            padding: 14px 16px;
            background: #16b3fb;
            color: white;
            text-decoration: none;
            text-align:center;
            transition: all .18s ease;
        }

        .program-scope-card:hover,
        .program-scope-card:focus {
            text-decoration: none;
            border-color: #40b56c;
            box-shadow: 0 8px 18px rgba(64, 181, 108, 0.12);
        }

        .program-scope-card.is-active {
            background: #078bec;
            border-color: #40b56c;
        }

        .program-scope-card strong {
            display: block;
            font-size: 22px;
            margin-top: 25px;
        }

        .program-scope-label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: white;
        }

        .program-filter-form {
            margin-bottom: 18px;
        }

        .program-filter-row {
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
            align-items: end;
        }

        /* .col-lg-3 col-md-6 {
            flex: 1 1 180px;
            min-width: 180px;
        }

        .col-lg-3 col-md-6-wide {
            flex: 2 1 280px;
            min-width: 260px;
        } */

        .program-filter-actions {
            display: flex;
            gap: 10px;
            margin-left: auto;
            padding-right: 16px;
        }

        .program-table thead th {
            background: #40b56c;
            color: #fff;
            border-color: #389f5e;
        }

        .program-table td,
        .program-table th {
            vertical-align: middle;
        }

        .program-discount-line {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 6px;
            margin-bottom: 6px;
        }

        .program-action-dropdown .dropdown-menu {
            z-index: 1050;
        }

        @media (max-width: 767px) {
            .program-filter-actions {
                width: 100%;
                margin-left: 0;
            }
        }
    </style>
@endpush
