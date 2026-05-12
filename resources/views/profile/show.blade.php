@extends('layouts.theme')

@section('title', 'My Profile')

@section('content')
    <div class="lead-shell">
        <div id="profile-loader" class="lead-loader">
            <div class="lead-spinner">
                <div class="dot"></div>
                <div class="dot"></div>
                <div class="dot"></div>
            </div>
            <p>Loading profile...</p>
        </div>

        <div id="profile-content" class="lead-content">
            <div class="box-typical box-typical-dashboard panel panel-default lead-create-card">
                <header class="box-typical-header panel-heading lead-header">
                    <div class="tbl w-100">
                        <div class="tbl-row">
                            <div class="tbl-cell tbl-cell-title p-0 m-0">
                                <h2 class="panel-title lead-title">My Profile</h2>
                            </div>
                            <div class="tbl-cell text-right" style="width: 260px;">
                                <a href="{{ route('profile.change-password') }}" class="btn btn-inline btn-primary-outline" style="padding: 0.4rem; padding-left:10px;">
                                    <i class="fa fa-key mr-1"></i> Change Password
                                </a>
                            </div>
                        </div>
                    </div>
                </header>

                <div class="box-typical-body panel-body lead-body">
                    @if (session('status'))
                        <div class="alert alert-success">{{ session('status') }}</div>
                    @endif

                    <div class="profile-summary">
                        <div><span class="label-mute">Campus:</span> {{ $user->campus?->code ?? $user->campus?->name ?? 'N/A' }}</div>
                        <div><span class="label-mute">Role(s):</span>
                            @if($user->roles->isEmpty())
                                <span class="text-muted">No roles</span>
                            @else
                                @foreach($user->roles as $r)
                                    <span class="label label-info" style="margin-right:4px;">{{ $r->name }}</span>
                                @endforeach
                            @endif
                        </div>
                        <div><span class="label-mute">Member since:</span> {{ optional($user->created_at)->format('d M Y') ?? 'N/A' }}</div>
                    </div>

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('profile.update') }}">
                        @csrf
                        @method('PUT')
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label class="form-label required">Full Name</label>
                                <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label class="form-label required">Email</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                            </div>
                        </div>

                        <div class="form-actions mb-2 mt-3 text-right mr-0">
                            <button type="submit" class="btn btn-inline btn-primary-outline" style="padding: 0.4rem; padding-left:10px;">Save Changes</button>
                            <a href="{{ route('dashboard') }}" class="btn btn-inline btn-danger-outline" style="padding: 0.4rem; padding-left:10px;">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .lead-shell { font-family: 'Proxima Nova', sans-serif; position: relative; min-height: 100vh; width: 100%; overflow: visible; padding: 0; margin: 0; }
        .lead-loader { position: absolute; top: 0; left: 0; right: 0; height: 100vh; background: rgba(245,247,251,0.95); display: flex; align-items: center; justify-content: center; flex-direction: column; z-index: 10; gap: 12px; }
        .lead-spinner { display: inline-flex; align-items: center; gap: 8px; }
        .lead-spinner .dot { width: 12px; height: 12px; border-radius: 50%; background: #12a0ff; animation: bounce 0.9s ease-in-out infinite; }
        .lead-spinner .dot:nth-child(2) { animation-delay: 0.15s; background: #1f8ef1; }
        .lead-spinner .dot:nth-child(3) { animation-delay: 0.3s; background: #36b1ff; }
        .lead-loader p { margin: 0; color: #54667a; font-weight: 600; }
        @keyframes bounce { 0%, 80%, 100% { transform: translateY(0); opacity: 0.6; } 40% { transform: translateY(-12px); opacity: 1; } }
        .lead-content { opacity: 0; visibility: hidden; transition: opacity 0.4s ease; position: relative; min-height: 400px; }
        body.profile-ready .lead-content { opacity: 1; visibility: visible; }
        body.profile-ready #profile-loader { display: none; }

        .lead-create-card { overflow: visible !important; max-height: none !important; }
        .lead-create-card .panel-heading { padding: 10px 20px; }
        .lead-body { padding: 14px 18px; overflow: visible !important; }
        .lead-title { font-size: 18px; font-weight: 500; color: #1f2937; line-height: 1.4; }
        .lead-create-card .form-row { padding: 3px 10px; }
        .lead-create-card .form-group { margin-bottom: 8px; }
        .lead-create-card label, .lead-create-card .form-label { color: #343434; font-size: 12px; font-weight: 600; line-height: 1.2; margin-bottom: 6px; }
        .lead-create-card .form-control { font-size: 13px; height: 37px !important; min-height: 37px !important; padding: 0.375rem 0.625rem !important; border: 1px solid #ccc; border-radius: 0.25rem; color: #343434; }
        .required::after { content: '*'; color: #e53935; margin-left: 4px; }

        .profile-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 8px 18px;
            padding: 12px 14px;
            margin-bottom: 14px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: #f8fbff;
            font-size: 13px;
        }
        .label-mute { color: #54667a; font-weight: 600; margin-right: 4px; }
        .tbl-row { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; }
        .tbl-cell.text-right { flex: 0 0 auto; text-align: right; }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            setTimeout(function () { document.body.classList.add('profile-ready'); }, 200);
        });
    </script>
@endpush
