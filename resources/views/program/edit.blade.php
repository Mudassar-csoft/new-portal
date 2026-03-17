@extends('layouts.theme')

@section('title', 'Edit Programme')

@section('content')
    <div class="program-form-shell">
        <div class="box-typical box-typical-dashboard panel panel-default program-form-card">
            <header class="box-typical-header panel-heading d-flex align-items-center justify-content-between">
                <div>
                    <h3 class="panel-title mb-0">Edit Programme</h3>
                    <small class="text-muted">Update programme pricing, discounts, outline file, and status settings.</small>
                </div>
                <div class="d-flex" style="gap:10px;">
                    <a href="{{ route('program.index') }}" class="btn btn-default">Back to Programmes</a>
                    <a href="{{ route('batch.index', ['program_id' => $program->id]) }}" class="btn btn-primary">Related Batches</a>
                </div>
            </header>
            <div class="box-typical-body panel-body">
                <form method="POST" action="{{ route('program.update', $program) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    @include('program.partials.form')

                    <div class="text-right mt-3">
                        <button type="submit" class="btn btn-primary">Update Programme</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .program-form-shell {
            padding: 10px;
        }

        .program-form-card {
            max-width: 1250px;
            margin: 0 auto;
        }

        .required::after {
            content: ' *';
            color: #e53935;
        }

        .program-discount-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 14px;
        }

        .program-discount-row {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            margin-bottom: 12px;
            padding: 14px;
            border: 1px solid #dbe5f1;
            border-radius: 10px;
            background: #f8fbff;
        }

        .program-discount-col {
            flex: 1 1 220px;
            min-width: 180px;
        }

        .program-discount-action {
            display: flex;
            align-items: end;
        }
    </style>
@endpush

@push('scripts')
    @include('program.partials.discount-script')
@endpush
