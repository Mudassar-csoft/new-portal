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
                                <a href="{{ route('profile.change-password') }}" class="btn btn-inline btn-primary-outline ci-inline-pad-04 ci-inline-pl-10">
                                    <i class="fa fa-key mr-1"></i> Change Password
                                </a>
                            </div>
                        </div>
                    </div>
                </header>

                <div class="box-typical-body panel-body lead-body">
        @include('partials.session-status-alert')

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

        @include('partials.validation-errors-alert')

                    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="avatar-row">
                            <div class="avatar-preview">
                                @if($user->avatar_path)
                                    <img id="avatar-preview-img" src="{{ asset('storage/' . $user->avatar_path) }}" alt="Avatar">
                                @else
                                    <img id="avatar-preview-img" src="{{ asset('theme/img/avatar-2-64.png') }}" alt="Avatar">
                                @endif
                            </div>
                            <div class="avatar-actions">
                                <label class="form-label">Profile Picture</label>
                                <input type="file" name="avatar" id="avatar-input" accept="image/jpeg,image/png,image/webp" class="form-control" style="padding:4px;">
                                <small class="text-muted">JPG, PNG, or WEBP up to 2 MB.</small>
                                @if($user->avatar_path)
                                    <label class="d-block mt-2">
                                        <input type="checkbox" name="remove_avatar" value="1">
                                        Remove current picture
                                    </label>
                                @endif
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <label class="form-label required">Full Name</label>
                                <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                            </div>
                            <div class="form-group col-md-12">
                                <label class="form-label required">Email</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                            </div>
                        </div>

                        <div class="form-actions mb-2 mt-3 text-right mr-0">
                            <button type="submit" class="btn btn-inline btn-primary-outline ci-inline-pad-04 ci-inline-pl-10">Save Changes</button>
                            <a href="{{ route('dashboard') }}" class="btn btn-inline btn-danger-outline ci-inline-pad-04 ci-inline-pl-10">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        :root {
            --dimension-profile-show-1: 100vh;
            --dimension-profile-show-2: 12px;
            --dimension-profile-show-3: 37px;
            --dimension-profile-show-4: 88px;
            --space-profile-show-1: 12px;
            --space-profile-show-2: 12px 14px;
            --space-profile-show-3: 14px;
            --space-profile-show-4: 4px;
            --space-profile-show-5: 8px;
            --color-profile-show-1: #343434;
            --color-profile-show-2: #54667a;
            --color-profile-show-3: #e2e8f0;
            --typo-profile-show-font-weight-1: 600;
            --typo-profile-show-font-size-2: 12px;
            --typo-profile-show-font-size-3: 13px;
        }

        .lead-shell { font-family: 'Proxima Nova', sans-serif; position: relative; min-height: var(--dimension-profile-show-1); width: 100%; overflow: visible; padding: 0; margin: 0; }
        .lead-loader { position: absolute; top: 0; left: 0; right: 0; height: var(--dimension-profile-show-1); background: rgba(245,247,251,0.95); display: flex; align-items: center; justify-content: center; flex-direction: column; z-index: 10; gap: var(--space-profile-show-1); }
        .lead-spinner { display: inline-flex; align-items: center; gap: var(--space-profile-show-5); }
        .lead-spinner .dot { width: var(--dimension-profile-show-2); height: var(--dimension-profile-show-2); border-radius: 50%; background: #12a0ff; animation: bounce 0.9s ease-in-out infinite; }
        .lead-spinner .dot:nth-child(2) { animation-delay: 0.15s; background: #1f8ef1; }
        .lead-spinner .dot:nth-child(3) { animation-delay: 0.3s; background: #36b1ff; }
        .lead-loader p { margin: 0; color: var(--color-profile-show-2); font-weight: var(--typo-profile-show-font-weight-1); }
        @keyframes bounce { 0%, 80%, 100% { transform: translateY(0); opacity: 0.6; } 40% { transform: translateY(-12px); opacity: 1; } }
        .lead-content { opacity: 0; visibility: hidden; transition: opacity 0.4s ease; position: relative; min-height: 400px; }
        body.profile-ready .lead-content { opacity: 1; visibility: visible; }
        body.profile-ready #profile-loader { display: none; }

        .lead-create-card { overflow: visible !important; max-height: none !important; }
        .lead-create-card .panel-heading { padding: 10px 20px; }
        .lead-body { padding: 14px 18px; overflow: visible !important; }
        .lead-title { font-size: 18px; font-weight: 500; color: #1f2937; line-height: 1.4; }
        .lead-create-card .form-row { padding: 3px 10px; }
        .lead-create-card .form-group { margin-bottom: var(--space-profile-show-5); }
        .lead-create-card label, .lead-create-card .form-label { color: var(--color-profile-show-1); font-size: var(--typo-profile-show-font-size-2); font-weight: var(--typo-profile-show-font-weight-1); line-height: 1.2; margin-bottom: 6px; }
        .lead-create-card .form-control { font-size: var(--typo-profile-show-font-size-3); height: var(--dimension-profile-show-3) !important; min-height: var(--dimension-profile-show-3) !important; padding: 0.375rem 0.625rem !important; border: 1px solid #ccc; border-radius: 0.25rem; color: var(--color-profile-show-1); }
        .required::after { content: '*'; color: #e53935; margin-left: var(--space-profile-show-4); }

        .profile-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 8px 18px;
            padding: var(--space-profile-show-2);
            margin-bottom: var(--space-profile-show-3);
            border: 1px solid var(--color-profile-show-3);
            border-radius: 8px;
            background: #f8fbff;
            font-size: var(--typo-profile-show-font-size-3);
        }
        .label-mute { color: var(--color-profile-show-2); font-weight: var(--typo-profile-show-font-weight-1); margin-right: var(--space-profile-show-4); }
        .tbl-row { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: var(--space-profile-show-1); }
        .tbl-cell.text-right { flex: 0 0 auto; text-align: right; }

        .avatar-row {
            display: flex;
            gap: 18px;
            align-items: center;
            padding: var(--space-profile-show-2);
            margin-bottom: var(--space-profile-show-3);
            border: 1px solid var(--color-profile-show-3);
            border-radius: 8px;
            background: #fff;
        }
        .avatar-preview img {
            width: var(--dimension-profile-show-4);
            height: var(--dimension-profile-show-4);
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--color-profile-show-3);
            display: block;
        }
        .avatar-actions { flex: 1 1 auto; }
        .avatar-actions .form-label { font-size: var(--typo-profile-show-font-size-2); font-weight: var(--typo-profile-show-font-weight-1); }
        @media (max-width: 600px) {
            .avatar-row { flex-direction: column; align-items: flex-start; }
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            setTimeout(function () { document.body.classList.add('profile-ready'); }, 200);

            var avatarInput = document.getElementById('avatar-input');
            var avatarImg = document.getElementById('avatar-preview-img');
            if (avatarInput && avatarImg) {
                avatarInput.addEventListener('change', function (e) {
                    var file = e.target.files && e.target.files[0];
                    if (!file) return;
                    var reader = new FileReader();
                    reader.onload = function (evt) { avatarImg.src = evt.target.result; };
                    reader.readAsDataURL(file);
                });
            }
        });
    </script>
@endpush
