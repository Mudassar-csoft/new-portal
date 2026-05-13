@php
    $actionId = $actionId ?? ('student-action-' . $admission->id);
@endphp

@once
    @push('styles')
        <style>
            .follow-action-dropdown .dropdown-menu.lead-action-menu {
                min-width: 240px;
                padding: 1px 0;
                border: 1px solid #dfe5eb;
                border-radius: 6px;
                background: #fff;
                box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
                text-align: left !important;
            }

            .follow-action-dropdown .dropdown-item.lead-action-item,
            .follow-action-dropdown form button.dropdown-item.lead-action-item {
                display: flex !important;
                align-items: center;
                justify-content: flex-start;
                gap: 8px;
                width: 100%;
                text-align: left !important;
                padding: 5px 18px !important;
                color: #303740 !important;
                font-size: 16px !important;
                font-weight: 500;
                line-height: 1.35;
                background: transparent !important;
                border: 0;
                transition: background-color 0.18s ease, color 0.18s ease;
            }

            .follow-action-dropdown .dropdown-item.lead-action-item:hover,
            .follow-action-dropdown .dropdown-item.lead-action-item:focus,
            .follow-action-dropdown form button.dropdown-item.lead-action-item:hover,
            .follow-action-dropdown form button.dropdown-item.lead-action-item:focus {
                background: #f7fafc !important;
                color: #222b33 !important;
                text-decoration: none;
            }

            .follow-action-dropdown .dropdown-item.lead-action-item[disabled],
            .follow-action-dropdown form button.dropdown-item.lead-action-item[disabled] {
                opacity: 0.5;
                cursor: not-allowed;
            }

            .follow-action-dropdown .lead-action-icon {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 22px;
                min-width: 22px;
                height: 22px;
                font-size: 16px !important;
                line-height: 1;
                text-align: center;
                margin-right: 0 !important;
                padding: 0 !important;
            }

            .follow-action-dropdown .lead-action-label {
                display: inline-block;
                font-size: 16px !important;
                font-weight: 500;
                letter-spacing: 0.01em;
            }

            .follow-action-dropdown .lead-action-icon.lead-icon-blue { color: #19b6e6; }
            .follow-action-dropdown .lead-action-icon.lead-icon-black { color: #303740; }
            .follow-action-dropdown .lead-action-icon.lead-icon-green { color: #2db853; }
            .follow-action-dropdown .lead-action-icon.lead-icon-yellow { color: #f5b400; }

            .follow-action-dropdown .dropdown-item-text {
                padding: 6px 18px !important;
                font-size: 14px;
                color: #2db853;
            }

            .follow-action-dropdown form { margin: 0; }
        </style>
    @endpush
@endonce

<div class="dropdown follow-action-dropdown student-action-dropdown">
    <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="{{ $actionId }}" aria-haspopup="true" aria-expanded="false">
        Actions
    </button>
    <div class="dropdown-menu dropdown-menu-right lead-action-menu" aria-labelledby="{{ $actionId }}">
        @foreach($statusOptions as $statusKey => $statusLabel)
            <form method="POST" action="{{ route('student.records.status', $admission) }}">
                @csrf
                <input type="hidden" name="status" value="{{ $statusKey }}">
                <button type="submit" class="dropdown-item lead-action-item" @disabled($admission->student_status === $statusKey)>
                    <span class="lead-action-icon lead-icon-blue" aria-hidden="true">
                        <i class="fa fa-exchange"></i>
                    </span>
                    <span class="lead-action-label">Mark {{ $statusLabel }}</span>
                </button>
            </form>
        @endforeach

        <div class="dropdown-divider"></div>

        @if($admission->certificate_delivered_at)
            <div class="dropdown-item-text">
                <i class="fa fa-check mr-2"></i>Certificate Delivered
            </div>
        @else
            <form method="POST" action="{{ route('student.records.certificate-delivered', $admission) }}">
                @csrf
                <button type="submit" class="dropdown-item lead-action-item">
                    <span class="lead-action-icon lead-icon-yellow" aria-hidden="true">
                        <i class="fa fa-certificate"></i>
                    </span>
                    <span class="lead-action-label">Mark Certificate Delivered</span>
                </button>
            </form>
        @endif
    </div>
</div>
