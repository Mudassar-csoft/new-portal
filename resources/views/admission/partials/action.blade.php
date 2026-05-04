@php
	$actionId = $actionId ?? ('adm-action-' . uniqid());
	$admission = $admission ?? null;
	$leadId = $leadId ?? ($admission->lead_id ?? null);
@endphp

@once
	@push('styles')
		<style>
			.admission-action-dropdown .dropdown-menu.lead-action-menu {
				min-width: 292px;
				padding: 8px 0;
				border: 1px solid #dfe5eb;
				border-radius: 6px;
				background: #fff;
				box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
				text-align: left !important;
			}

			.admission-action-dropdown .dropdown-item.lead-action-item {
				display: flex !important;
				align-items: center;
				justify-content: flex-start;
				gap: 14px;
				padding: 5px 18px !important;
				color: #303740 !important;
				font-size: 17px !important;
				font-weight: 500;
				line-height: 1.35;
				background: transparent !important;
				border: 0;
				text-align: left !important;
			}

			.admission-action-dropdown .dropdown-item.lead-action-item:hover,
			.admission-action-dropdown .dropdown-item.lead-action-item:focus {
				background: #f7fafc !important;
				color: #222b33 !important;
				text-decoration: none;
			}

			.admission-action-dropdown .lead-action-icon {
				display: inline-flex;
				align-items: center;
				justify-content: center;
				width: 24px;
				min-width: 24px;
				height: 24px;
				font-size: 18px !important;
				line-height: 1;
				margin-right: 0 !important;
				padding: 0 !important;
			}

			.admission-action-dropdown .lead-action-label {
				display: inline-block;
				font-size: 17px !important;
				font-weight: 500;
				letter-spacing: 0.01em;
			}

			.admission-action-dropdown .lead-action-icon svg {
				display: block;
				width: 24px;
				height: 24px;
			}

			.admission-action-dropdown .lead-action-icon--whatsapp svg {
				width: 22px;
				height: 22px;
			}

			.admission-action-dropdown .lead-icon-blue { color: #1677ff; }
			.admission-action-dropdown .lead-icon-cyan { color: #19b6e6; }
			.admission-action-dropdown .lead-icon-black { color: #303740; }
			.admission-action-dropdown .lead-icon-green { color: #2db853; }
		</style>
	@endpush
@endonce

<div class="dropdown admission-action-dropdown">
	<button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="{{ $actionId }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
		Actions
	</button>
	<div class="dropdown-menu dropdown-menu-right lead-action-menu" aria-labelledby="{{ $actionId }}">
		<a class="dropdown-item lead-action-item" href="#">
			<span class="lead-action-icon lead-icon-blue" aria-hidden="true">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
					<rect x="3" y="6.5" width="18" height="11" rx="2"></rect>
					<path d="M7 10.5h10"></path>
					<path d="M8 6.5V5a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v1.5"></path>
				</svg>
			</span><span class="lead-action-label">Collect Fee Installment</span>
		</a>

		<a class="dropdown-item lead-action-item" href="{{ route('admission.create', ['lead_id' => $leadId]) }}">
			<span class="lead-action-icon lead-icon-cyan" aria-hidden="true">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
					<path d="M8 3.75h5.5L18.25 8.5V19a1.25 1.25 0 0 1-1.25 1.25h-9.5A1.25 1.25 0 0 1 6.25 19V5A1.25 1.25 0 0 1 7.5 3.75Z"/>
					<path d="M13.5 3.75V8.5h4.75"/>
					<path d="m9 12 2 2 4-4"/>
				</svg>
			</span><span class="lead-action-label">Enroll To Another Course</span>
		</a>

		<a class="dropdown-item lead-action-item" href="#">
			<span class="lead-action-icon lead-icon-black" aria-hidden="true">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
					<path d="M4.75 5.75h14.5A1.75 1.75 0 0 1 21 7.5v9A1.75 1.75 0 0 1 19.25 18.25H4.75A1.75 1.75 0 0 1 3 16.5v-9a1.75 1.75 0 0 1 1.75-1.75Z"/>
					<path d="M7 9.5h10"/>
					<path d="M7 13h7"/>
					<path d="m8 18.25-2.75 2.25"/>
				</svg>
			</span><span class="lead-action-label">Send SMS</span>
		</a>

		<a class="dropdown-item lead-action-item" href="#">
			<span class="lead-action-icon lead-icon-black" aria-hidden="true">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
					<path d="M3.75 6.25h16.5A1.25 1.25 0 0 1 21.5 7.5v9a1.25 1.25 0 0 1-1.25 1.25H3.75A1.25 1.25 0 0 1 2.5 16.5v-9a1.25 1.25 0 0 1 1.25-1.25Z"/>
					<path d="m3 7 9 7 9-7"/>
				</svg>
			</span><span class="lead-action-label">Send Email</span>
		</a>

		<a class="dropdown-item lead-action-item" href="#">
			<span class="lead-action-icon lead-action-icon--whatsapp lead-icon-green" aria-hidden="true">
				<svg viewBox="0 0 24 24" fill="currentColor">
					<path d="M20.52 3.48A11.8 11.8 0 0 0 12.12 0C5.58 0 .24 5.34.24 11.88c0 2.1.54 4.14 1.62 5.94L0 24l6.36-1.8a11.86 11.86 0 0 0 5.76 1.5H12.12c6.54 0 11.88-5.34 11.88-11.88 0-3.18-1.26-6.18-3.48-8.34zm-8.4 18.24h-.06a9.8 9.8 0 0 1-4.98-1.38l-.36-.24-3.78 1.08 1.02-3.66-.24-.36a9.88 9.88 0 0 1-1.56-5.28c0-5.46 4.44-9.9 9.9-9.9 2.64 0 5.1 1.02 6.96 2.88a9.8 9.8 0 0 1 2.88 7.02c0 5.46-4.44 9.9-9.84 9.9zm5.46-7.38c-.3-.18-1.8-.9-2.1-1.02-.24-.06-.48-.12-.72.18-.18.3-.72 1.02-.9 1.2-.18.18-.36.24-.66.06a8.1 8.1 0 0 1-2.4-1.5 8.98 8.98 0 0 1-1.68-2.1c-.18-.3 0-.42.12-.6.12-.12.3-.36.42-.54.12-.18.18-.3.3-.48.06-.18 0-.36 0-.54 0-.12-.72-1.74-.96-2.34-.24-.6-.54-.48-.72-.48h-.6c-.24 0-.54.06-.84.36-.3.3-1.08 1.08-1.08 2.64s1.14 3.06 1.26 3.24c.18.24 2.22 3.42 5.4 4.74.72.3 1.32.48 1.8.6.72.24 1.38.18 1.92.12.6-.06 1.8-.72 2.1-1.44.24-.66.24-1.32.18-1.44-.12-.12-.3-.18-.6-.36z"/>
				</svg>
			</span><span class="lead-action-label">Whatsapp</span>
		</a>

		<a class="dropdown-item lead-action-item" href="#">
			<span class="lead-action-icon lead-icon-black" aria-hidden="true">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
					<path d="M3.75 20.25h4.5l11-11a1.6 1.6 0 0 0 0-2.25l-2.25-2.25a1.6 1.6 0 0 0-2.25 0l-11 11v4.5Z"/>
					<path d="m13.5 6.5 4 4"/>
				</svg>
			</span><span class="lead-action-label">Edit</span>
		</a>
	</div>
</div>
