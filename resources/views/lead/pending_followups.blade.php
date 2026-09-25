@extends('layouts.theme')

@section('title', 'My Pending Follow-ups')

@section('content')
    <div class="follow-shell">
        <div class="box-typical box-typical-dashboard panel panel-default">
            <div class="panel-heading p-3">
                <h3 class="panel-title mb-0">My Pending Follow-ups</h3>
                <p class="text-muted mt-2 mb-0">Due and overdue follow-ups where you recorded the latest follow-up, within your campus access.</p>
            </div>
            <div class="box-typical-body panel-body follow-body">
                <form method="GET" class="follow-controls">
                    <div class="d-flex align-items-center" style="gap: 0.5rem;">
                        <label for="pending-followup-per-page">Show</label>
                        <select name="per_page" id="pending-followup-per-page" class="form-select form-select-sm" onchange="this.form.submit()">
                            @foreach([10, 25, 50, 100] as $option)
                                <option value="{{ $option }}" @selected($perPage === $option)>{{ $option }}</option>
                            @endforeach
                        </select>
                        <span>Entries</span>
                    </div>
                    <div class="follow-search">
                        <input type="text" name="q" class="form-control form-control-sm" placeholder="Search..." aria-label="Search pending follow-ups" value="{{ $search }}">
                        @if($search !== '')
                            <a href="{{ route('leads.pending-followups') }}" class="btn btn-default btn-sm">Reset</a>
                        @endif
                    </div>
                </form>
                <div class="table-responsive">
                    <table class="table table-bordered follow-table">
                        <thead>
                            <tr>
                                <th>Sr</th>
                                <th>Name</th>
                                <th>Program</th>
                                <th>Primary Contact</th>
                                <th>Campus</th>
                                <th>Follow-up Due</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($followups as $followup)
                                <tr>
                                    <td>{{ ($followups->firstItem() ?? 1) + $loop->index }}</td>
                                    <td>
                                        @if(auth()->user()?->hasAnyPermission(['lead.view']))
                                            <a href="{{ route('leads.show', $followup->lead_id) }}">{{ $followup->lead->name ?? 'N/A' }}</a>
                                        @else
                                            {{ $followup->lead->name ?? 'N/A' }}
                                        @endif
                                    </td>
                                    <td>{{ $followup->lead->program?->title ?? $followup->lead->program?->name ?? 'N/A' }}</td>
                                    <td>{{ $followup->lead->phone ?? 'N/A' }}</td>
                                    <td>{{ $followup->lead->campus?->code ?? $followup->lead->campus?->name ?? 'N/A' }}</td>
                                    <td>{{ $followup->notification_due_at->format('d-M-Y h:i A') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted">No pending follow-ups found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="follow-footer">
                    @include('partials.follow-pagination', ['paginator' => $followups])
                </div>
            </div>
        </div>
    </div>
@endsection
