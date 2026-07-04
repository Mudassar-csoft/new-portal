@extends('layouts.theme')

@section('title', 'Edit Role')

@section('content')
    @php
        $selectedPermissionIds = collect(old('permissions', $role->permissions->modelKeys()))->map(fn ($id) => (int) $id);
    @endphp

    <div class="role-shell">
        <div class="box-typical box-typical-dashboard panel panel-default role-card">
            <header class="box-typical-header panel-heading d-flex justify-content-between">
                <div>
                    <h3 class="panel-title mb-0 form-label-role">Edit Role</h3>
                </div>
                <a href="{{ route('roles.index') }}" class="btn btn-default">Back</a>
            </header>
            <div class="box-typical-body panel-body role-body">
                <form method="POST" action="{{ route('roles.update', $role) }}">
                    @csrf
                    @method('PUT')

                    <div class="form-row">
                        <div class="form-group col-md-2">
                            <label class="required form-label-role">Name</label>
                        </div>
                        <div class="form-group col-md-10">
                            <input
                                type="text"
                                name="name"
                                class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', $role->name) }}"
                                placeholder="Admin"
                                @if($role->is_system) readonly @endif
                                required
                            >
                            @if($role->is_system)
                                <small class="text-muted">System role names are fixed and cannot be changed.</small>
                            @endif
                            @error('name')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-2">
                            <label class="form-label-role">Slug</label>
                        </div>
                        <div class="form-group col-md-10">
                            <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug', $role->slug) }}" placeholder="admin" readonly>
                            <small class="text-muted">Slug stays fixed after the role is created.</small>
                            @error('slug')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-2">
                            <label class="form-label-role">Description</label>
                        </div>
                        <div class="form-group col-md-10">
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="2" placeholder="Optional description">{{ old('description', $role->description) }}</textarea>
                            @error('description')
                                <div class="field-error ">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    @include('user.partials.direct-permissions', [
                        'sectionTitle' => 'Role Permissions',
                        'helperText' => 'Assign access module by module for this role.',
                        'permissionGroups' => $permissionGroups,
                        'selectedPermissionIds' => $selectedPermissionIds,
                    ])

                    <div class="text-right mt-3">
                        <a href="{{ route('roles.index') }}" class="btn btn-danger-outline mr-2">Cancel</a>
                        <button type="submit" class="btn btn-primary-outline">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        :root {
            --space-role-edit-1: 6px;
        }

        .form-label-role {
            font-size: 14px !important;
            margin-bottom: var(--space-role-edit-1);
    margin-top: var(--space-role-edit-1);
    
    color: #343a40 !important;
    text-transform: uppercase;
    font-weight: 600;

        }
        .required::after {
            content: '*';
            color: #e74c3c;
            margin-left: 4px;
        }
        .role-shell {
            min-height: 100vh;
            padding: 10px;
        }
        .role-card {
            max-width: 100%;
            margin: 0 auto;
        }
        .role-body {
            padding: 20px;
        }
    </style>
@endpush
