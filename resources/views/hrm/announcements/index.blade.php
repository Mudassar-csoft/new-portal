@extends('layouts.theme')

@section('title', 'Announcements')

@section('content')
    <div class="hrm-shell">
        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="box-typical box-typical-dashboard panel panel-default hrm-card">
            <header class="box-typical-header panel-heading">
                <h3 class="panel-title form-label">Announcements &amp; Notifications</h3>
            </header>
            <div class="box-typical-body panel-body">
                <form method="POST" action="{{ route('hrm.announcements.store') }}" class="mb-3 hrm-box">
                    @csrf
                    <div class="form-row">
                        <div class="form-group col-md-6 col-lg-3">
                            <label class="form-label required" >Campus</label>
                            <select name="campus_id" class="form-control">
                                <option value="">All</option>
                                @foreach($campuses as $campus)
                                    <option value="{{ $campus->id }}">{{ $campus->code }} - {{ $campus->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6 col-lg-3">
                            <label class="form-label required" >Department</label>
                            <select name="department_id" class="form-control">
                                <option value="">All</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6 col-lg-3">
                            <label class="form-label required" >Audience Scope</label>
                            <select name="audience_scope" class="form-control">
                                <option value="all">All</option>
                                <option value="campus">Campus</option>
                                <option value="department">Department</option>
                                <option value="role">Role</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6 col-lg-3">
                            <label class="form-label required" >Status</label>
                            <select name="status" class="form-control">
                                <option value="draft">Draft</option>
                                <option value="published">Published</option>
                                <option value="archived">Archived</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row" >
                        <div class="form-group col-md-6 col-lg-3">
                            <label class="form-label required" >Title</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="form-group col-md-6 col-lg-3">
                            <label class="form-label required" >Publish At</label>
                            <input type="datetime-local" name="publish_at" class="form-control">
                        </div>
                        <div class="form-group col-md-6 col-lg-3">
                            <label class="form-label required" >Expire At</label>
                            <input type="datetime-local" name="expire_at" class="form-control">
                        </div>
                        <div class="form-group col-md-6 col-lg-3">
                            <label class="form-label required" >Channels</label>
                            <div class="d-flex" style="gap:2%;margin-top: 8px;">
                                <div class="d-flex " style = "align-items: center;">
                                    <input type="checkbox" name="channel_in_app" value="1" checked>
                                    <label class="ml-1" > In-app</label>
                                </div>
                                <div class="d-flex" style = "align-items: center;">
                                    <input type="checkbox" name="channel_email" value="1">
                                    <label class="ml-1" > Email</label>
                                </div>
                                <div class="d-flex" style = "align-items: center;">
                                    <input type="checkbox" name="channel_sms" value="1">
                                    <label class="ml-1" > SMS</label>
                                </div>
                                <div class="d-flex" style = "align-items: center;">
                                    <input type="checkbox" name="channel_whatsapp" value="1">
                                    <label class="ml-1" > WhatsApp</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group" style= "padding-left:15px;">
                        <label class="form-label required" >Message / Policy Update</label>
                        <textarea name="message" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="text-right">
                    <button class="btn btn-inline btn-primary-outline" type="submit">Save Announcement</button>
               </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered hrm-table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Audience</th>
                                <th>Campus</th>
                                <th>Department</th>
                                <th>Publish</th>
                                <th>Expire</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($announcements as $announcement)
                                <tr>
                                    <td>{{ $announcement->title }}</td>
                                    <td>{{ ucfirst($announcement->audience_scope) }}</td>
                                    <td>{{ $announcement->campus->code ?? 'All' }}</td>
                                    <td>{{ $announcement->department->name ?? 'All' }}</td>
                                    <td>{{ optional($announcement->publish_at)->format('Y-m-d H:i') ?: '-' }}</td>
                                    <td>{{ optional($announcement->expire_at)->format('Y-m-d H:i') ?: '-' }}</td>
                                    <td>{{ ucfirst($announcement->status) }}</td>
                                    <td>
                                        @if($announcement->status !== 'published')
                                            <form method="POST" action="{{ route('hrm.announcements.publish', $announcement) }}">
                                                @csrf
                                                <button class="btn btn-sm btn-outline-success" type="submit">Publish</button>
                                            </form>
                                        @else
                                            <span class="text-success">Live</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center text-muted">No announcements found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $announcements->links() }}
            </div>
        </section>
    </div>
@endsection

@push('styles')
    <style>
     .col-md-6 col-lg-3 { flex: 0 0 26% !important; max-width: 28% !important; }
        .hrm-shell { padding: 8px 0 16px; }
        .hrm-table thead th { background: #eef2f7; color: #334155; }
        .hrm-box { border: 1px solid #e6ebf1; border-radius: 8px; padding: 10px; }
    </style>
@endpush

