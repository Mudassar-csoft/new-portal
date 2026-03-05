@php $status = strtolower((string) ($transfer->status ?? 'pending')); @endphp

@if($status === 'pending')
    <form method="POST" action="{{ route('lead_transfers.approve', $transfer) }}" class="d-inline">
        @csrf
        <button type="submit" class="btn btn-xs btn-primary">Approve</button>
    </form>
@else
    <span class="text-muted">-</span>
@endif
