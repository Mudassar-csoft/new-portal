@extends('layouts.theme')

@section('title', 'Create Role')

@section('content')
    @php
        $selectedPermissionIds = collect(old('permissions', []))->map(fn ($id) => (int) $id);
    @endphp

    <div class="user-shell">
        <div class="box-typical box-typical-dashboard panel panel-default user-card">
            <header class="box-typical-header panel-heading d-flex justify-content-between">
                <div>
                    <h3 class="panel-title mb-0 form-label">Create Role</h3>
                </div>
            </header>
            <div class="box-typical-body panel-body user-body">
                <form method="POST" action="{{ route('roles.store') }}">
                    @csrf

                    <div class="form-row">
                        <div class="form-group col-md-2">
                            <label class="required form-label">Name</label>
                        </div>
                        <div class="form-group col-md-10">
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Admin" required>
                            @error('name')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-2">
                            <label class="form-label">Slug</label>
                        </div>
                        <div class="form-group col-md-10">
                            <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug') }}" placeholder="admin">
                            @error('slug')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-2">
                            <label class="form-label">Description</label>
                        </div>
                        <div class="form-group col-md-10">
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="2" placeholder="Optional description">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    @include('user.partials.direct-permissions', [
                        'sectionTitle' => 'Role Permissions',
                        'helperText' => 'Assign access module by module for this role.',
                        'permissionGroups' => $permissionGroups,
                        'selectedPermissionIds' => $selectedPermissionIds,
                    ])

                    <div class="text-right mt-3 mb-2">
                        <button type="submit" class="btn btn-inline btn-primary-outline">Create Role</button>
                        <a href="{{ route('roles.index') }}" class="btn btn-inline btn-danger-outline p-2">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .required::after {
            content: '*';
            color: #e74c3c;
            margin-left: 4px;
        }
        .user-shell {
            min-height: 100vh;
            padding: 20px;
            background: linear-gradient(160deg, #f6f8fc 0%, #eef3fb 100%);
        }
        .user-card {
            margin: 0 auto;
            border-radius: 14px;
            box-shadow: 0 18px 40px rgba(25, 45, 85, 0.12);
        }
        .user-body {
            padding: 16px 24px 12px;
        }
        .form-control:focus {
            border-color: #2b78ff;
            box-shadow: 0 0 0 3px rgba(43, 120, 255, 0.12);
        }
    </style>
@endpush
