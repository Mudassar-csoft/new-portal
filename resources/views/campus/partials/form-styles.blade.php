<style>
        :root {
            --dimension-campus-partials-form-styles-1: 100vh;
            --dimension-campus-partials-form-styles-2: 12px;
            --dimension-campus-partials-form-styles-3: 37px;
            --dimension-campus-partials-form-styles-4: 7px;
            --dimension-campus-partials-form-styles-5: 82px;
            --space-campus-partials-form-styles-1: 12px;
            --space-campus-partials-form-styles-2: 8px;
            --color-campus-partials-form-styles-1: #00a8ff;
            --color-campus-partials-form-styles-2: #1f2937;
            --color-campus-partials-form-styles-3: #343434;
            --typo-campus-partials-form-styles-font-weight-1: 600;
            --typo-campus-partials-form-styles-font-size-2: 14px;
            --typo-campus-partials-form-styles-font-size-3: 12px;
        }

    .lead-shell {
        font-family: 'Proxima Nova', sans-serif;
        position: relative;
        min-height: var(--dimension-campus-partials-form-styles-1);
        width: 100%;
        overflow: visible;
        padding: 0;
        margin: 0;
    }

    .lead-loader {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: var(--dimension-campus-partials-form-styles-1);
        background: rgba(245, 247, 251, 0.95);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        z-index: 10;
        gap: var(--space-campus-partials-form-styles-1);
    }

    .lead-spinner { display: inline-flex; align-items: center; gap: var(--space-campus-partials-form-styles-2); }

    .lead-spinner .dot {
        width: var(--dimension-campus-partials-form-styles-2);
        height: var(--dimension-campus-partials-form-styles-2);
        border-radius: 50%;
        background: #12a0ff;
        animation: bounce 0.9s ease-in-out infinite;
    }

    .lead-spinner .dot:nth-child(2) { animation-delay: 0.15s; background: #1f8ef1; }
    .lead-spinner .dot:nth-child(3) { animation-delay: 0.3s;  background: #36b1ff; }

    .lead-loader p { margin: 0; color: #54667a; font-weight: var(--typo-campus-partials-form-styles-font-weight-1); }

    .lead-content {
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.4s ease;
        position: relative;
        min-height: 400px;
    }

    body.campus-form-ready .lead-content { opacity: 1; visibility: visible; }
    body.campus-form-ready #campus-form-loader { display: none; }

    @keyframes bounce {
        0%, 80%, 100% { transform: translateY(0); opacity: 0.6; }
        40% { transform: translateY(-12px); opacity: 1; }
    }

    .lead-create-card { overflow: visible !important; max-height: none !important; }
    .lead-create-card .panel-heading { padding: 10px 20px; }
    .lead-body { padding: 10px 10px 5px; overflow: visible !important; }

    .lead-title {
        font-size: 18px;
        font-weight: 500;
        color: var(--color-campus-partials-form-styles-2);
        line-height: 1.4;
        margin: 0;
    }

    .lead-title span {
        font-size: var(--typo-campus-partials-form-styles-font-size-2);
        font-weight: 400;
        color: var(--color-campus-partials-form-styles-2);
    }

    .lead-create-card .form-row { padding: 3px 10px; }
    .lead-create-card .form-group { margin-bottom: var(--space-campus-partials-form-styles-2); }

    .lead-create-card label,
    .lead-create-card .form-label {
        color: var(--color-campus-partials-form-styles-3);
        font-size: var(--typo-campus-partials-form-styles-font-size-3);
        font-weight: var(--typo-campus-partials-form-styles-font-weight-1);
        line-height: 1.2;
        margin-bottom: 6px;
    }

    .lead-create-card .form-control {
        font-size: var(--typo-campus-partials-form-styles-font-size-3);
        height: var(--dimension-campus-partials-form-styles-3) !important;
        min-height: var(--dimension-campus-partials-form-styles-3) !important;
        padding: 0.375rem 0.625rem !important;
        border: 1px solid #ccc;
        border-radius: 0.25rem;
        color: var(--color-campus-partials-form-styles-3);
    }

    .lead-create-card textarea.form-control {
        height: var(--dimension-campus-partials-form-styles-5) !important;
        min-height: var(--dimension-campus-partials-form-styles-5) !important;
        resize: vertical;
    }

    .required::after {
        content: '*';
        color: #e53935;
        margin-left: 4px;
    }

    .campus-type-options {
        margin-top: var(--space-campus-partials-form-styles-2);
    }

    .campus-type-option {
        display: inline-flex;
        align-items: center;
        gap: 0;
        cursor: pointer;
        padding: 0;
        background: transparent;
        border: 0;
    }

    .campus-type-option .form-check-input {
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        width: 14px;
        height: 14px !important;
        border: 2px solid grey;
        border-radius: 50%;
        outline: none;
        cursor: pointer;
        position: relative;
        background-color: #fff;
        transition: background 0.2s, box-shadow 0.2s;
        margin: 0;
    }

    .campus-type-option .form-check-input:checked {
        border-color: var(--color-campus-partials-form-styles-1);
    }

    .campus-type-option .form-check-input:checked::before {
        content: '';
        position: absolute;
        top: 2px;
        left: 2px;
        width: var(--dimension-campus-partials-form-styles-4);
        height: var(--dimension-campus-partials-form-styles-4);
        border-radius: 50%;
        background-color: var(--color-campus-partials-form-styles-1);
    }

    .campus-type-option .form-check-label {
        font-size: var(--typo-campus-partials-form-styles-font-size-2);
        margin-bottom: 0;
        cursor: pointer;
        color: var(--color-campus-partials-form-styles-3);
    }

    .lead-body .alert {
        margin: 8px 12px 12px;
    }

    .js-royalty-field[hidden] {
        display: none !important;
    }

    .tbl-row {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: var(--space-campus-partials-form-styles-1);
    }

    .tbl-cell.text-right { flex: 0 0 auto; text-align: right; }

    @media (max-width: 767px) {
        .tbl-row { flex-direction: column; align-items: stretch; }
        .tbl-cell.text-right { text-align: left; }
    }
</style>
