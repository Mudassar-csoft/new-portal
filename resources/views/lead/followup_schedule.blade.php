@extends('layouts.theme')

@section('title', $pageTitle ?? 'Follow-up Schedule')

@section('content')
    @php
        $selectedWindow = $selectedWindow ?? 'today';
        $search = $search ?? '';
        $perPage = (int) ($perPage ?? 25);
        $lastFollowupFrom = $lastFollowupFrom ?? '';
        $lastFollowupTo = $lastFollowupTo ?? '';
        $currentPage = $followups->currentPage();
        $lastPage = $followups->lastPage();
        $pageStart = max(1, $currentPage - 2);
        $pageEnd = min($lastPage, $currentPage + 2);

        if ($pageStart <= 3) {
            $pageStart = 1;
            $pageEnd = min($lastPage, 5);
        }

        if ($pageEnd >= $lastPage - 2) {
            $pageEnd = $lastPage;
            $pageStart = max(1, $lastPage - 4);
        }

        $pageItems = [];

        if ($lastPage > 0) {
            if ($pageStart > 1) {
                $pageItems[] = 1;

                if ($pageStart > 2) {
                    $pageItems[] = 'ellipsis-left';
                }
            }

            foreach (range($pageStart, $pageEnd) as $pageNumber) {
                $pageItems[] = $pageNumber;
            }

            if ($pageEnd < $lastPage) {
                if ($pageEnd < $lastPage - 1) {
                    $pageItems[] = 'ellipsis-right';
                }

                $pageItems[] = $lastPage;
            }
        }
    @endphp

    <div class="follow-shell">
        <div id="follow-loader" class="follow-loader">
            <div class="follow-spinner">
                <div class="dot"></div>
                <div class="dot"></div>
                <div class="dot"></div>
            </div>
            <p>Loading follow-up schedule...</p>
        </div>

        <div id="follow-content" class="follow-content p-0 m-0">
            <div class="follow-card box-typical box-typical-dashboard panel panel-default">
                <div class="panel-heading p-3">
                    <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap: 10px;">
                        <h3 class="panel-title mb-0">Lead Management | <span class="text-muted">{{ $moduleTitle ?? 'Follow-up Schedule' }}</span></h3>
                        @if(!empty($stageRoute ?? null))
                            <a href="{{ $stageRoute }}" class="btn btn-default btn-sm">All Follow-up Stages</a>
                        @endif
                    </div>
                </div>

                <div class="follow-tab-bar m-0 pt-3 small" style="gap: 2px;">
                    @foreach ($tabs as $key => $label)
                        @php
                            $tabQuery = request()->query();
                            unset($tabQuery['page']);
                            $tabQuery['window'] = $key;
                            $tabUrl = request()->url() . (count($tabQuery) ? '?' . http_build_query($tabQuery) : '');
                        @endphp
                        <a href="{{ $tabUrl }}" class="follow-tab {{ $selectedWindow === $key ? 'active' : '' }}" data-window="{{ $key }}" style="display: flex; align-items: center; gap: 3px;">
                            <span class="label-text">{{ $label }}</span>
                            <span class="badge {{ $badgeColors[$key] ?? 'badge-secondary' }}">{{ $tabCounts[$key] ?? 0 }}</span>
                        </a>
                    @endforeach
                </div>

                <div class="box-typical-body panel-body follow-body">
                    <form method="GET" class="follow-controls" id="follow-filter-form">
                        <input type="hidden" name="window" value="{{ $selectedWindow }}">

                        <div class="d-flex control-flow-show-bar" style="gap: 0.5rem; align-items: center;">
                            <label>Show</label>
                            <select class="form-select form-select-sm" name="per_page" id="follow-per-page" onchange="this.form.submit()">
                                @foreach([10, 25, 50, 100] as $option)
                                    <option value="{{ $option }}" {{ $perPage === $option ? 'selected' : '' }}>{{ $option }}</option>
                                @endforeach
                            </select>
                            <label>Entries</label>
                        </div>

                        <div class="d-flex follow-date-range-field" style="gap: 0.5rem; align-items: center;">
                            <label class="mb-0">Last Follow-up</label>
                            <input type="date" name="last_followup_from" class="form-control form-control-sm follow-auto-submit" value="{{ $lastFollowupFrom }}" onchange="this.form.submit()">
                            <span>to</span>
                            <input type="date" name="last_followup_to" class="form-control form-control-sm follow-auto-submit" value="{{ $lastFollowupTo }}" onchange="this.form.submit()">
                        </div>

                        <div class="follow-search">
                            <input type="text" name="q" id="follow-search" class="form-control form-control-sm" placeholder="Search..." value="{{ $search }}">
                            <!-- <button type="submit" class="btn btn-primary btn-sm">Search</button> -->
                            @if($search !== '' || $selectedWindow !== 'today' || $lastFollowupFrom !== '' || $lastFollowupTo !== '')
                                <a href="{{ request()->url() }}" class="btn btn-default btn-sm">Reset</a>
                            @endif
                            <i class="fa fa-search"></i>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-bordered follow-table" id="follow-table">
                            <thead>
                                <tr>
                                    <th>Sr</th>
                                    <th>Name</th>
                                    <th>{{ $interestHeading ?? 'Program' }}</th>
                                    <th>Contact</th>
                                    <th>Origin</th>
                                    <th>Campus Code</th>
                                    <th>Create Date</th>
                                    <th>Last Follower</th>
                                    <th>Followups</th>
                                    <th class="text-left">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($followups as $row)
                                    @php
                                        $actionId = 'schedule-action-' . \Illuminate\Support\Str::slug($row->lead->name ?? 'lead') . '-' . $loop->iteration;
                                        $nameUrl = !empty($row->lead?->id)
                                            ? route('leads.show', $row->lead->id)
                                            : null;
                                        $labelClass = match ($row->stage_label) {
                                            'New' => 'label-primary',
                                            'Contacted' => 'label-success',
                                            'Need Analysis' => 'label-warning',
                                            'Branch Visited' => 'label-default',
                                            'Proposal & Negotiation', 'Proposal or Negotiation' => 'label-info',
                                            'Not Interesting' => 'label-default',
                                            'Registered' => 'label-success',
                                            'Enrolled' => 'label-success',
                                            default => 'label-default',
                                        };

                                        if (($type ?? 'training') === 'coworking' && $row->lead?->coworkingRegistration) {
                                            $nameUrl = route('coworking-registrations.show', $row->lead->coworkingRegistration);
                                        }
                                    @endphp
                                    <tr>
                                        <td class="text-start">{{ ($followups->firstItem() ?? 1) + $loop->index }}</td>
                                        <td>
                                            @if($nameUrl)
                                                <a href="{{ $nameUrl }}" class="lead-link">
                                                    {{ $row->lead->name ?? 'N/A' }}
                                                </a>
                                            @else
                                                {{ $row->lead->name ?? 'N/A' }}
                                            @endif
                                            <div class="follow-stage-meta mt-1">
                                                <span class="label {{ $labelClass }}">{{ $row->stage_label }}</span>
                                            </div>
                                        </td>
                                        <td>{{ $row->lead->interest_summary ?? 'N/A' }}</td>
                                        <td>{{ $row->lead->phone ?? 'N/A' }}</td>
                                        <td>{{ $row->lead->origin ?? 'N/A' }}</td>
                                        <td>
                                            @if(($type ?? 'training') === 'coworking')
                                                {{ $row->branch_code ?? 'N/A' }}
                                            @else
                                                {{ $row->lead->campus->code ?? $row->campus->code ?? $row->campus->name ?? 'N/A' }}
                                            @endif
                                        </td>
                                        <td>
                                            @if($row->lead?->created_at)
                                                {{ $row->lead->created_at->format('d-m-Y') }}
                                                ({{ $row->lead->created_at->copy()->startOfDay()->diffInDays(now()->startOfDay()) }} days)
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td class="last-follower-cell">{{ $row->last_follower_name ?? 'System' }}</td>
                                        <td class="followup-count text-center">{{ (int) ($row->followups_count ?? 0) }}</td>
                                        <td class="action-cell">
                                            @include('lead.partials.action', ['actionId' => $actionId, 'lead' => $row->lead])
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted">No scheduled follow-ups found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="follow-footer">
                        @include('partials.follow-pagination', ['paginator' => $followups, 'countId' => 'follow-count'])
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="lead-modal" id="lead-form-modal" aria-hidden="true">
        <div class="modal-card" role="dialog" aria-modal="true">
            <div class="modal-header">
                <h5 class="modal-title" id="lead-form-modal-title">Form</h5>
                <button type="button" class="modal-close" id="lead-form-modal-close" aria-label="Close">&times;</button>
            </div>
            <iframe id="lead-form-modal-frame" title="Lead Form"></iframe>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        :root {
            --space-lead-followup-schedule-1: 12px;
            --typo-lead-followup-schedule-line-height-1: 1;
        }

        body.lead-modal-open {
            overflow: hidden;
        }

        .lead-modal {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(4px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1055;
            padding: 18px;
        }

        .lead-modal.show {
            display: flex;
        }

        .lead-modal .modal-card {
            background: #fff;
            width: min(1320px, 98vw);
            height: min(900px, 94vh);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.72);
            box-shadow: 0 28px 80px rgba(15, 23, 42, 0.35);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .lead-modal .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 20px;
            border-bottom: 1px solid #e2e8f0;
            background: linear-gradient(180deg, #fbfdff 0%, #f3f8ff 100%);
        }

        .lead-modal .modal-title {
            font-size: 1.625rem !important;
            font-weight: 500 !important;
            text-wrap: auto;
        }

        .lead-modal .modal-close {
            border: 0;
            background: transparent;
            font-size: 1.75rem;
            line-height: var(--typo-lead-followup-schedule-line-height-1);
            color: #5b6b80;
            cursor: pointer;
        }

        .lead-modal iframe {
            flex: 1;
            border: 0;
            width: 100%;
            background: #f3f8fd;
        }

        .follow-stage-meta {
            line-height: var(--typo-lead-followup-schedule-line-height-1);
        }

        .follow-tab {
            text-decoration: none;
        }

        .follow-controls {
            gap: var(--space-lead-followup-schedule-1);
            flex-wrap: wrap;
        }

        .follow-search {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-left: auto;
        }

        .follow-search i {
            display: none;
        }

        .follow-search .form-control {
            width: min(280px, 100%);
        }

        .follow-date-range-field .form-control {
            width: 150px;
            max-width: 100%;
        }

        .follow-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: var(--space-lead-followup-schedule-1);
            flex-wrap: wrap;
        }

    </style>
@endpush

@push('scripts')
    <script>
        (function () {
            function showAlert(title, text, type) {
                if (window.swal) {
                    swal({ title: title, text: text, type: type });
                    return;
                }

                alert(text);
            }

            function openUrls(urls) {
                (urls || []).forEach(function (url) {
                    if (!url) {
                        return;
                    }

                    try {
                        window.open(url, '_blank');
                    } catch (error) {
                        console.error('Unable to open voucher url', error);
                    }
                });
            }

            function openLeadModal(url, title) {
                var modal = document.getElementById('lead-form-modal');
                var frame = document.getElementById('lead-form-modal-frame');
                var titleNode = document.getElementById('lead-form-modal-title');

                if (!modal || !frame) {
                    window.location.href = url;
                    return;
                }

                frame.src = url;
                modal.classList.add('show');
                modal.setAttribute('aria-hidden', 'false');
                document.body.classList.add('lead-modal-open');
                if (titleNode) titleNode.textContent = title || 'Form';
            }

            function closeLeadModal() {
                var modal = document.getElementById('lead-form-modal');
                var frame = document.getElementById('lead-form-modal-frame');

                if (!modal || !frame) return;

                frame.src = 'about:blank';
                modal.classList.remove('show');
                modal.setAttribute('aria-hidden', 'true');
                document.body.classList.remove('lead-modal-open');
            }

            function initLeadModal() {
                var modal = document.getElementById('lead-form-modal');
                var closeButton = document.getElementById('lead-form-modal-close');

                if (!modal) return;

                if (closeButton) {
                    closeButton.addEventListener('click', closeLeadModal);
                }

                modal.addEventListener('click', function (event) {
                    if (event.target === modal) {
                        closeLeadModal();
                    }
                });

                document.addEventListener('keydown', function (event) {
                    if (event.key === 'Escape' && modal.classList.contains('show')) {
                        closeLeadModal();
                    }
                });

                document.addEventListener('click', function (event) {
                    var trigger = event.target.closest('.js-lead-modal-link');
                    if (!trigger) return;

                    var url = trigger.getAttribute('data-lead-modal-url');
                    if (!url) return;

                    event.preventDefault();
                    openLeadModal(url, trigger.getAttribute('data-lead-modal-title') || 'Form');
                });

                window.addEventListener('message', function (event) {
                    if (event.data && event.data.type === 'lead-modal-close') {
                        closeLeadModal();

                        if (event.data.openUrls) {
                            openUrls(event.data.openUrls);
                        }

                        if (event.data.status) {
                            showAlert('Success', event.data.status, 'success');
                        }

                        if (event.data.reload) {
                            setTimeout(function () {
                                window.location.reload();
                            }, 500);
                        }
                    }
                });
            }

            function revealFollowPage() {
                setTimeout(function () {
                    document.body.classList.add('follow-ready');
                }, 150);
            }

            document.addEventListener('DOMContentLoaded', function () {
                initLeadModal();
                revealFollowPage();

            });
        })();
    </script>
    @if(session('status'))
        <script>
            (function () {
                if (!window.swal) return;
                swal({
                    title: 'Success',
                    text: @json(session('status')),
                    type: 'success'
                });
            })();
        </script>
    @endif
@endpush
