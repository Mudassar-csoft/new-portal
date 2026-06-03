<style>
    .lead-shell {
        font-family: 'Proxima Nova', sans-serif;
        position: relative;
        min-height: 100vh;
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
        height: 100vh;
        background: rgba(245, 247, 251, 0.95);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        z-index: 10;
        gap: 12px;
    }

    .lead-spinner { display: inline-flex; align-items: center; gap: 8px; }

    .lead-spinner .dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #12a0ff;
        animation: bounce 0.9s ease-in-out infinite;
    }

    .lead-spinner .dot:nth-child(2) { animation-delay: 0.15s; background: #1f8ef1; }
    .lead-spinner .dot:nth-child(3) { animation-delay: 0.3s;  background: #36b1ff; }

    .lead-loader p { margin: 0; color: #54667a; font-weight: 600; }

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
        color: #1f2937;
        line-height: 1.4;
        margin: 0;
    }

    .lead-title span {
        font-size: 14px;
        font-weight: 400;
        color: #1f2937;
    }

    .lead-create-card .form-row { padding: 3px 10px; }
    .lead-create-card .form-group { margin-bottom: 8px; }

    .lead-create-card label,
    .lead-create-card .form-label {
        color: #343434;
        font-size: 12px;
        font-weight: 600;
        line-height: 1.2;
        margin-bottom: 6px;
    }

    .lead-create-card .form-control {
        font-size: 12px;
        height: 37px !important;
        min-height: 37px !important;
        padding: 0.375rem 0.625rem !important;
        border: 1px solid #ccc;
        border-radius: 0.25rem;
        color: #343434;
    }

    .lead-create-card textarea.form-control {
        height: 82px !important;
        min-height: 82px !important;
        resize: vertical;
    }

    .required::after {
        content: '*';
        color: #e53935;
        margin-left: 4px;
    }

    .campus-type-options {
        margin-top: 8px;
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
        border-color: #00a8ff;
    }

    .campus-type-option .form-check-input:checked::before {
        content: '';
        position: absolute;
        top: 2px;
        left: 2px;
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background-color: #00a8ff;
    }

    .campus-type-option .form-check-label {
        font-size: 14px;
        margin-bottom: 0;
        cursor: pointer;
        color: #343434;
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
        gap: 12px;
    }

    .tbl-cell.text-right { flex: 0 0 auto; text-align: right; }

    @media (max-width: 767px) {
        .tbl-row { flex-direction: column; align-items: stretch; }
        .tbl-cell.text-right { text-align: left; }
    }
</style>
