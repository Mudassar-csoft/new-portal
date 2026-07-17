<div class="lead-shell">
    <div id="review-form-loader" class="lead-loader">
        <div class="lead-spinner">
            <div class="dot"></div>
            <div class="dot"></div>
            <div class="dot"></div>
        </div>
        <p>Preparing review form...</p>
    </div>

    <div id="review-form-content" class="lead-content">
        <div class="box-typical box-typical-dashboard panel panel-default lead-create-card">
            <header class="box-typical-header panel-heading lead-header">
                <div class="tbl w-100">
                    <div class="tbl-row">
                        <div class="tbl-cell tbl-cell-title p-0 m-0">
                            <h2 class="panel-title lead-title">
                                {{ $title }} <span class="ml-2">(All fields marked with <span class="text-danger semibold">*</span> are required)</span>
                            </h2>
                        </div>
                    </div>
                </div>
            </header>

            <div class="box-typical-body panel-body lead-body">
                <form method="POST" action="{{ $action }}" enctype="multipart/form-data">
                    @csrf
                    @if(($mode ?? 'create') === 'edit')
                        @method('PUT')
                    @endif

                    @include('reviews.partials.form')

                    <div class="form-actions mb-2 mt-3 text-right mr-0">
                        <button type="submit" class="btn btn-inline btn-primary-outline ci-inline-pad-04 ci-inline-pl-10 ci-inline-ml-5">{{ $submitLabel }}</button>
                        <a href="{{ route('reviews.index') }}" class="btn btn-inline btn-danger-outline ci-inline-pad-04 ci-inline-pl-10">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('styles')
    <style>
        :root {
            --dimension-review-form-1: 100%;
            --dimension-review-form-2: 100vh;
            --dimension-review-form-3: 12px;
            --dimension-review-form-4: 1px;
            --dimension-review-form-5: 25px;
            --dimension-review-form-6: 37px;
            --dimension-review-form-7: 92px;
            --space-review-form-1: 10px;
            --space-review-form-2: 12px;
            --space-review-form-3: 8px;
            --color-review-form-1: #00a8ff;
            --color-review-form-2: #1f2937;
            --color-review-form-3: #343434;
            --color-review-form-4: #566a7f;
            --typo-review-form-font-weight-1: 600;
            --typo-review-form-font-weight-2: 500;
            --typo-review-form-font-size-3: 14px;
            --typo-review-form-font-size-4: 12px;
        }

        .lead-shell { font-family: 'Proxima Nova', sans-serif; position: relative; min-height: var(--dimension-review-form-2); width: var(--dimension-review-form-1); overflow: visible; padding: 0; margin: 0; }
        .lead-loader { position: absolute; top: 0; left: 0; right: 0; height: var(--dimension-review-form-2); background: rgba(245,247,251,0.95); display: flex; align-items: center; justify-content: center; flex-direction: column; z-index: 10; gap: var(--space-review-form-2); }
        .lead-spinner { display: inline-flex; align-items: center; gap: var(--space-review-form-3); }
        .lead-spinner .dot { width: var(--dimension-review-form-3); height: var(--dimension-review-form-3); border-radius: 50%; background: #12a0ff; animation: bounce 0.9s ease-in-out infinite; }
        .lead-spinner .dot:nth-child(2) { animation-delay: 0.15s; background: #1f8ef1; }
        .lead-spinner .dot:nth-child(3) { animation-delay: 0.3s; background: #36b1ff; }
        .lead-loader p { margin: 0; color: #54667a; font-weight: var(--typo-review-form-font-weight-1); }
        .lead-content { opacity: 0; visibility: hidden; transition: opacity 0.4s ease; position: relative; min-height: 400px; }
        body.review-form-ready .lead-content { opacity: 1; visibility: visible; }
        body.review-form-ready #review-form-loader { display: none; }
        @keyframes bounce { 0%, 80%, 100% { transform: translateY(0); opacity: 0.6; } 40% { transform: translateY(-12px); opacity: 1; } }
        .lead-create-card { overflow: visible !important; max-height: none !important; }
        .lead-create-card .panel-heading { padding: 10px 20px; }
        .lead-body { padding: 10px 10px 5px; overflow: visible !important; }
        .lead-title { font-size: 1.125rem; font-weight: var(--typo-review-form-font-weight-2); color: var(--color-review-form-2); line-height: 1.4; }
        .lead-title span { font-size: var(--typo-review-form-font-size-3); font-weight: 400; color: var(--color-review-form-2); }
        .lead-create-card .form-row { padding: 3px 10px; }
        .lead-create-card .form-group { margin-bottom: var(--space-review-form-3); }
        .lead-create-card .form-row > .form-group.col-lg-2 { flex: 0 0 16.666667%; max-width: 16.666667%; }
        .lead-create-card .form-row > .form-group.col-lg-3 { flex: 0 0 25%; max-width: 25%; }
        .lead-create-card .form-row > .form-group.col-lg-6 { flex: 0 0 50%; max-width: 50%; }
        .lead-create-card label,
        .lead-create-card .form-label { color: var(--color-review-form-3); font-size: var(--typo-review-form-font-size-4); font-weight: var(--typo-review-form-font-weight-1); line-height: 1.2; margin-bottom: 6px; }
        .lead-create-card .form-control,
        .lead-create-card .form-control-file { font-size: var(--typo-review-form-font-size-4); }
        .lead-create-card .form-control { height: var(--dimension-review-form-6) !important; min-height: var(--dimension-review-form-6) !important; padding: 0.375rem 0.625rem !important; border: 1px solid #ccc; border-radius: 0.25rem; color: var(--color-review-form-3); }
        .lead-create-card textarea.form-control { height: var(--dimension-review-form-7) !important; min-height: var(--dimension-review-form-7) !important; resize: vertical; }
        .lead-create-card .alert { margin: 6px 10px 10px; }
        .required::after { content: '*'; color: #e53935; margin-left: 4px; }
        .review-upload { display: flex !important; align-items: center; gap: var(--space-review-form-1); width: var(--dimension-review-form-1); min-height: 37px; padding: 6px 10px; margin: 0; border: 1px dashed #b8d7ea; border-radius: 7px; background: linear-gradient(180deg, #fbfdff 0%, #f3faff 100%); color: var(--color-review-form-4) !important; cursor: pointer; transition: border-color 0.18s ease, box-shadow 0.18s ease, background 0.18s ease; }
        .review-upload:hover,
        .review-upload:focus-within { border-color: var(--color-review-form-1); box-shadow: 0 0 0 3px rgba(0,168,255,0.10); background: #f8fcff; }
        .review-upload input[type="file"] { position: absolute; width: var(--dimension-review-form-4); height: var(--dimension-review-form-4); opacity: 0; pointer-events: none; }
        .review-upload-icon { display: inline-flex; align-items: center; justify-content: center; width: var(--dimension-review-form-5); min-width: var(--dimension-review-form-5); height: var(--dimension-review-form-5); border-radius: 999px; background: rgba(0,168,255,0.12); color: var(--color-review-form-1); font-size: 0.8125rem; }
        .review-upload-copy { display: flex; flex-direction: column; min-width: 0; line-height: 1.15; }
        .review-upload-title { max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: var(--color-review-form-3); font-size: var(--typo-review-form-font-size-4); font-weight: var(--typo-review-form-font-weight-1); }
        .review-upload-hint { margin-top: 2px; color: #8a99a8; font-size: 0.5625rem; font-weight: var(--typo-review-form-font-weight-2); }
        .review-current-image { display: inline-block; width: 76px; height: 76px; object-fit: cover; border: 1px solid #dbe5f1; border-radius: 6px; margin-top: 8px; }
        .review-checkbox { min-height: 37px; display: flex; align-items: center; margin-top: 0; }

        @media (max-width: 1199px) {
            .lead-create-card .form-row > .form-group.col-lg-2,
            .lead-create-card .form-row > .form-group.col-lg-3,
            .lead-create-card .form-row > .form-group.col-lg-6 { flex: 0 0 50%; max-width: 50%; }
        }

        @media (max-width: 767px) {
            .lead-create-card .form-row > .form-group.col-lg-2,
            .lead-create-card .form-row > .form-group.col-lg-3,
            .lead-create-card .form-row > .form-group.col-lg-6 { flex: 0 0 100%; max-width: var(--dimension-review-form-1); }
        }
    </style>
@endpush

@push('scripts')
    <script>
        (function () {
            document.addEventListener('DOMContentLoaded', function () {
                setTimeout(function () {
                    document.body.classList.add('review-form-ready');
                }, 200);

                var input = document.getElementById('review-profile-image-upload');
                var label = document.querySelector('[data-review-upload-label]');
                if (input && label) {
                    input.addEventListener('change', function () {
                        label.textContent = this.files && this.files.length ? this.files[0].name : 'Choose profile image';
                    });
                }
            });
        })();
    </script>
@endpush
