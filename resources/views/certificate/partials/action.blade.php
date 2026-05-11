@php
    $actionId = $actionId ?? ('cert-action-' . $cert->id);
    $status = $cert->status ?? 'requested';
@endphp

@once
    @push('styles')
        <style>
            .follow-action-dropdown .dropdown-menu.lead-action-menu {
                min-width: 220px;
                padding: 1px 0;
                border: 1px solid #dfe5eb;
                border-radius: 6px;
                background: #fff;
                box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
                text-align: left !important;
            }
            .follow-action-dropdown .dropdown-item.lead-action-item,
            .follow-action-dropdown form button.dropdown-item.lead-action-item {
                display: flex !important; align-items: center; gap: 8px; width: 100%;
                text-align: left !important; padding: 6px 18px !important;
                color: #303740 !important; font-size: 15px !important; font-weight: 500;
                background: transparent !important; border: 0; transition: background-color 0.18s ease;
            }
            .follow-action-dropdown .dropdown-item.lead-action-item:hover,
            .follow-action-dropdown form button.dropdown-item.lead-action-item:hover {
                background: #f7fafc !important; text-decoration: none;
            }
            .follow-action-dropdown .lead-action-icon { width: 22px; min-width: 22px; height: 22px; display: inline-flex; align-items: center; justify-content: center; }
            .follow-action-dropdown .lead-action-icon.lead-icon-blue { color: #19b6e6; }
            .follow-action-dropdown .lead-action-icon.lead-icon-black { color: #303740; }
            .follow-action-dropdown .lead-action-icon.lead-icon-green { color: #2db853; }
            .follow-action-dropdown .lead-action-icon.lead-icon-yellow { color: #f5b400; }
            .follow-action-dropdown .lead-action-icon.lead-icon-red { color: #ef4e4e; }
            .follow-action-dropdown form { margin: 0; }
        </style>
    @endpush
@endonce

<div class="dropdown follow-action-dropdown">
    <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="{{ $actionId }}" data-display="static" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        Actions
    </button>
    <div class="dropdown-menu dropdown-menu-right lead-action-menu" aria-labelledby="{{ $actionId }}">
        <a class="dropdown-item lead-action-item" href="{{ route('certificate.edit', $cert) }}">
            <span class="lead-action-icon lead-icon-blue"><i class="fa fa-pencil"></i></span>
            <span class="lead-action-label">Edit Remarks</span>
        </a>

        @if($status === 'requested')
            <form method="POST" action="{{ route('certificate.approve', $cert) }}" onsubmit="return confirm('Approve this certificate request?');">
                @csrf
                @method('PATCH')
                <button type="submit" class="dropdown-item lead-action-item">
                    <span class="lead-action-icon lead-icon-green"><i class="fa fa-check"></i></span>
                    <span class="lead-action-label">Approve</span>
                </button>
            </form>
        @endif

        @if(in_array($status, ['requested', 'approved'], true))
            <form method="POST" action="{{ route('certificate.reject', $cert) }}" onsubmit="return confirm('Reject this certificate?');">
                @csrf
                @method('PATCH')
                <button type="submit" class="dropdown-item lead-action-item">
                    <span class="lead-action-icon lead-icon-red"><i class="fa fa-ban"></i></span>
                    <span class="lead-action-label">Reject</span>
                </button>
            </form>
        @endif

        @if($status === 'approved')
            <form method="POST" action="{{ route('certificate.send-to-printing', $cert) }}">
                @csrf
                @method('PATCH')
                <button type="submit" class="dropdown-item lead-action-item">
                    <span class="lead-action-icon lead-icon-yellow"><i class="fa fa-print"></i></span>
                    <span class="lead-action-label">Send to Printing</span>
                </button>
            </form>
        @endif

        @if($status === 'printing')
            <form method="POST" action="{{ route('certificate.mark-ready', $cert) }}">
                @csrf
                @method('PATCH')
                <button type="submit" class="dropdown-item lead-action-item">
                    <span class="lead-action-icon lead-icon-green"><i class="fa fa-flag-checkered"></i></span>
                    <span class="lead-action-label">Mark Ready</span>
                </button>
            </form>
        @endif

        @if($status === 'ready')
            <form method="POST" action="{{ route('certificate.mark-delivered', $cert) }}" onsubmit="return promptDelivery(this, '{{ addslashes($cert->admission?->student_name ?? '') }}');">
                @csrf
                @method('PATCH')
                <input type="hidden" name="delivered_to" value="">
                <button type="submit" class="dropdown-item lead-action-item">
                    <span class="lead-action-icon lead-icon-green"><i class="fa fa-handshake-o"></i></span>
                    <span class="lead-action-label">Mark Delivered</span>
                </button>
            </form>
        @endif

        @if($status !== 'delivered')
            <form method="POST" action="{{ route('certificate.destroy', $cert) }}" onsubmit="return confirm('Delete this certificate record?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="dropdown-item lead-action-item">
                    <span class="lead-action-icon lead-icon-red"><i class="fa fa-trash"></i></span>
                    <span class="lead-action-label">Delete</span>
                </button>
            </form>
        @endif
    </div>
</div>

@once
    @push('scripts')
        <script>
            function promptDelivery(form, defaultName) {
                var to = window.prompt('Delivered to (name)?', defaultName || '');
                if (to === null) return false;
                form.querySelector('input[name=delivered_to]').value = to;
                return true;
            }
        </script>
    @endpush
@endonce
