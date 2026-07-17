@extends('layouts.theme')

@section('title', $pageTitle)

@section('content')
    @php
        $filters = $filters ?? ['scope' => 'all', 'campus_id' => null, 'status' => null, 'search' => null];
        $scopeCards = $scopeCards ?? [];
        $activeScope = $filters['scope'] ?? 'all';
        $perPage = (int) ($filters['per_page'] ?? 25);
        $statusLabelClasses = ['active' => 'label-success', 'inactive' => 'label-default'];
        $scopeBadgeColors = ['all' => 'badge-secondary', 'active' => 'badge-success', 'inactive' => 'badge-warning'];
    @endphp

    <div class="lead-status-shell">
        @include('partials.status-loader', ['id' => 'news-status-loader', 'message' => 'Loading news...'])

        <div id="news-status-content" class="follow-content">
            @include('partials.session-status-alert')

            <div class="follow-card box-typical box-typical-dashboard panel panel-default">
                <div class="follow-tab-bar">
                    @foreach ($scopeCards as $card)
                        @php
                            $isActive = $activeScope === $card['scope'];
                            $url = route('news.index', array_filter(array_merge(
                                request()->except('page', 'scope'),
                                ['scope' => $card['scope'] !== 'all' ? $card['scope'] : null]
                            )));
                        @endphp
                        <a href="{{ $url }}" class="follow-tab {{ $isActive ? 'active' : '' }}" data-scope="{{ $card['scope'] }}">
                            <span class="label-text">{{ $card['label'] }}</span>
                            <span class="badge {{ $scopeBadgeColors[$card['scope']] ?? 'badge-secondary' }}">{{ number_format((int) $card['count']) }}</span>
                        </a>
                    @endforeach
                </div>

                <div class="box-typical-body panel-body follow-body">
                    <form method="GET" action="{{ route('news.index') }}" id="news-filter-form">
                        <input type="hidden" name="scope" value="{{ $activeScope }}">

                        <div class="follow-controls">
                            <div class="d-flex ci-inline-gap-05-center">
                                <label>Show</label>
                                <select class="form-select form-select-sm" name="per_page">
                                    @foreach([10, 25, 50, 100] as $option)
                                        <option value="{{ $option }}" @selected($perPage === $option)>{{ $option }}</option>
                                    @endforeach
                                </select>
                                <label>Entries</label>
                            </div>
                            <div class="follow-search">
                                <input type="text" name="search" id="news-status-search" class="form-control form-control-sm"
                                       placeholder="Search..." value="{{ $filters['search'] ?? '' }}">
                                <i class="fa fa-search"></i>
                            </div>
                        </div>

                        <div class="news-filter-row">
                            <div class="news-filter-field">
                                <label class="form-label">Campus</label>
                                <select class="form-control form-control-sm" name="campus_id">
                                    <option value="">All Campuses</option>
                                    @foreach($campuses as $campus)
                                        <option value="{{ $campus->id }}" @selected(($filters['campus_id'] ?? null) == $campus->id)>
                                            {{ $campus->code }} - {{ $campus->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @include('partials.filter-active-status-select')
                            <div class="news-filter-actions">
                                @if(auth()->user()?->hasAnyPermission(['news.create']))
                                    <!-- <a href="{{ route('news.create') }}" class="btn btn-primary btn-sm">Add News</a> -->
                                @endif
                                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                                <a href="{{ route('news.index', array_filter(['scope' => $activeScope !== 'all' ? $activeScope : null])) }}" class="btn btn-danger-outline">Reset</a>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-bordered follow-table" id="news-status-table">
                            <thead>
                                <tr>
                                    <th>Sr</th>
                                    <th>Image</th>
                                    <th>Title</th>
                                    <th>Campus</th>
                                    <th>News Date</th>
                                    <th>Status</th>
                                    <th class="text-left">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($newsItems as $idx => $news)
                                    @php
                                        $rowIndex = ($newsItems->firstItem() ?? 0) + $idx;
                                        $statusKey = $news->status ?? 'active';
                                    @endphp
                                    <tr>
                                        <td class="text-center">{{ $rowIndex }}</td>
                                        <td>
                                            @if($news->featured_image_path)
                                                <img class="news-thumb" src="{{ asset('storage/' . $news->featured_image_path) }}" alt="{{ $news->title }}">
                                            @else
                                                <span class="news-thumb news-thumb-empty"><i class="fa fa-image"></i></span>
                                            @endif
                                        </td>
                                        <td>
                                            <strong>{{ $news->title }}</strong>
                                            <br>
                                            <span class="text-muted">{{ \Illuminate\Support\Str::limit($news->short_description, 90) }}</span>
                                        </td>
                                        <td>{{ $news->campus?->code }} - {{ $news->campus?->name }}</td>
                                        <td>{{ optional($news->news_date)->format('d-m-Y') }}</td>
                                        <td>
                                            <span class="label {{ $statusLabelClasses[$statusKey] ?? 'label-default' }}">{{ ucfirst($statusKey) }}</span>
                                        </td>
                                        <td class="action-cell">
                                            @include('news.partials.action', ['actionId' => 'news-action-' . $news->id])
                                        </td>
                                    </tr>
                                @empty
                                    <tr data-empty-row>
                                        <td colspan="7" class="text-center text-muted">No news found for the selected filters.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="follow-footer">
                        @include('partials.follow-pagination', ['paginator' => $newsItems, 'countId' => 'news-status-count'])
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        :root {
            --dimension-news-index-1: 100%;
            --dimension-news-index-2: 100vh;
            --dimension-news-index-3: 56px;
            --dimension-news-index-4: 180px;
            --space-news-index-1: 14px;
            --space-news-index-2: 8px;
            --color-news-index-1: #54667a;
            --typo-news-index-font-weight-1: 600;
        }

        .ci-inline-gap-05-center { gap: 0.5rem; align-items: center; }
        .lead-status-shell { position: relative; min-height: var(--dimension-news-index-2); width: var(--dimension-news-index-1); overflow: hidden; }
        .follow-content { opacity: 0; visibility: hidden; transition: opacity 0.4s ease; position: relative; min-height: 400px; }
        body.news-ready .follow-content { opacity: 1; visibility: visible; }
        body.news-ready #news-status-loader { display: none; }
        .table-responsive { overflow: visible !important; }
        .follow-table .action-cell { min-width: 110px; white-space: nowrap; }
        .news-filter-row { display: flex; gap: var(--space-news-index-1); flex-wrap: wrap; align-items: end; margin-bottom: var(--space-news-index-1); }
        .news-filter-field { flex: 1 1 220px; min-width: var(--dimension-news-index-4); }
        .news-filter-field .form-label { font-size: 0.8125rem; font-weight: var(--typo-news-index-font-weight-1); color: var(--color-news-index-1); margin-bottom: 4px; }
        .news-filter-actions { display: flex; gap: var(--space-news-index-2); margin-left: auto; align-items: center; flex-wrap: wrap; }
        .news-thumb { width: var(--dimension-news-index-3); height: 40px; object-fit: cover; border-radius: 6px; border: 1px solid #dbe5f1; display: inline-flex; align-items: center; justify-content: center; background: #f4f8fb; color: #8a99a8; }

        @media (max-width: 767px) {
            .news-filter-actions { width: var(--dimension-news-index-1); margin-left: 0; }
        }
    </style>
@endpush

@push('scripts')
    <script>
        (function () {
            document.addEventListener('DOMContentLoaded', function () {
                setTimeout(function () {
                    document.body.classList.add('news-ready');
                }, 150);
            });
        })();
    </script>
@endpush
