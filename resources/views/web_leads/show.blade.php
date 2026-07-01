@extends('layouts.theme')

@section('title', 'Website Lead Details')

@section('content')
    @php
        $canCreateLeadFromWebLead = auth()->user()?->hasAnyPermission(['lead.create', 'web-lead.view', 'web-lead.create']) ?? false;
        $canUpdateWebLead = auth()->user()?->hasAnyPermission(['web-lead.view', 'web-lead.update']) ?? false;
    @endphp

    <div class="box-typical box-typical-dashboard panel panel-default web-lead-detail-card">
        <header class="box-typical-header panel-heading d-flex justify-content-between">
            <div>
                <h3 class="panel-title mb-0 form-label">{{ $webLead->full_name }}</h3>
                <small class="text-muted">{{ $webLead->source_label }} from {{ $webLead->source_site }}</small>
            </div>
            <div class="web-lead-actions">
                <div class="dropdown d-inline-block">
                    <button class="btn btn-inline btn-primary dropdown-toggle"
                            type="button"
                            id="actionDropdown"
                            data-toggle="dropdown"
                            aria-haspopup="true"
                            aria-expanded="false">
                        Action
                    </button>

                    <div class="dropdown-menu" aria-labelledby="actionDropdown">
                        @if ($webLead->isActionable())
                            @if($canCreateLeadFromWebLead)
                                <a class="dropdown-item"
                                   href="{{ route('leads.create', ['web_lead' => $webLead->id]) }}">
                                    <i class="fa fa-plus-square-o mr-2 text-primary p-1"></i>Create Lead
                                </a>
                            @endif

                            @if($canUpdateWebLead)
                                <form method="POST"
                                      action="{{ route('web-leads.not-interested', $webLead) }}"
                                      style="margin:0;">
                                    @csrf
                                    <button type="submit"
                                            class="dropdown-item text-danger"
                                            style="cursor:pointer;">
                                        <i class="fa fa-times-circle-o mr-2 text-danger p-1"></i>Not Interested
                                    </button>
                                </form>
                            @endif
                        @elseif ($webLead->converted_to_lead_id)
                            <a class="dropdown-item"
                               href="{{ route('leads.show', $webLead->converted_to_lead_id) }}">
                                Open CRM Lead
                            </a>
                        @endif

                        <!-- <div class="dropdown-divider"></div> -->

                        <a class="dropdown-item"
                           href="{{ route('web-leads.index', array_filter([
                                'tab' => $webLead->source_type,
                            ], static fn ($value) => $value !== null && $value !== '')) }}">
                           <i class="fa-regular fa-circle-left text-black mr-2 p-1"></i> Back
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <div class="box-typical-body panel-body">
            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            <div class="row">
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tbody>
                            <tr><th>Submitted</th><td>{{ optional($webLead->submitted_at ?? $webLead->created_at)->format('d-M-Y h:i A') ?? 'N/A' }}</td></tr>
                            <tr><th>Phone</th><td>{{ $webLead->phone ?: 'N/A' }}</td></tr>
                            <tr><th>Email</th><td>{{ $webLead->email ?: 'N/A' }}</td></tr>
                            <tr><th>Country</th><td>{{ $webLead->country ?: 'N/A' }}</td></tr>
                            <tr><th>City</th><td>{{ $webLead->city ?: 'N/A' }}</td></tr>
                            <tr><th>Area</th><td>{{ $webLead->area ?: 'N/A' }}</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tbody>
                            <tr><th>Interested Program</th><td>{{ $webLead->interested_program ?: 'N/A' }}</td></tr>
                            <tr><th>Preferred Campus</th><td>{{ $webLead->preferred_campus ?: 'N/A' }}</td></tr>
                            <tr><th>Teaching Method</th><td>{{ $webLead->teaching_method ?: 'N/A' }}</td></tr>
                            <tr><th>Gender</th><td>{{ $webLead->gender ?: 'N/A' }}</td></tr>
                            <tr><th>Handled By</th><td>{{ $webLead->handledBy->name ?? 'N/A' }}</td></tr>
                            <tr><th>Handled At</th><td>{{ optional($webLead->handled_at)->format('d-M-Y h:i A') ?? 'N/A' }}</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-6">
                    <div class="web-lead-panel">
                        <h4 class="form-label">Website Message</h4>
                        <p class="mb-0">{{ $webLead->message ?: 'No message provided.' }}</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="web-lead-panel">
                        <h4 class="form-label">CRM Lead</h4>
                        @if ($webLead->convertedLead)
                            <p class="mb-1"><strong>Name:</strong> {{ $webLead->convertedLead->name }}</p>
                            <p class="mb-1"><strong>Program:</strong> {{ $webLead->convertedLead->program->title ?? $webLead->convertedLead->program->name ?? 'N/A' }}</p>
                            <a href="{{ route('leads.show', $webLead->convertedLead) }}" class="btn btn-xs btn-success-outline">Open Lead</a>
                        @else
                            <p class="mb-0 text-muted">No CRM lead has been created from this record yet.</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- <div class="web-lead-panel mt-3">
                <h4 class="form-label">Raw Payload</h4>
                <pre class="web-lead-payload">{{ json_encode($webLead->payload ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
            </div> -->
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .web-lead-detail-card .panel-body {
            padding: 14px;
        }

        .web-lead-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .web-lead-panel {
            border: 1px solid #dbe4ed;
            border-radius: 8px;
            padding: 12px;
            background: #fbfdff;
            height: 100%;
        }

        .web-lead-payload {
            margin: 0;
            max-height: 360px;
            overflow: auto;
            background: #0f172a;
            color: #e2e8f0;
            border-radius: 8px;
            padding: 12px;
        }
    </style>
@endpush
