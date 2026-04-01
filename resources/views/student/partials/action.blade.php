@php
    $actionId = $actionId ?? ('student-action-' . $admission->id);
@endphp

<div class="dropdown student-action-dropdown">
    <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="{{ $actionId }}" aria-haspopup="true" aria-expanded="false">
        Actions
    </button>
    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="{{ $actionId }}">
        @foreach($statusOptions as $statusKey => $statusLabel)
            <form method="POST" action="{{ route('student.records.status', $admission) }}">
                @csrf
                <input type="hidden" name="status" value="{{ $statusKey }}">
                <button type="submit" class="dropdown-item" @disabled($admission->student_status === $statusKey)>
                    <i class="fa fa-exchange mr-2 text-info"></i>Mark {{ $statusLabel }}
                </button>
            </form>
        @endforeach

        <div class="dropdown-divider"></div>

        @if($admission->certificate_delivered_at)
            <div class="dropdown-item-text text-success">
                <i class="fa fa-check mr-2"></i>Certificate Delivered
            </div>
        @else
            <form method="POST" action="{{ route('student.records.certificate-delivered', $admission) }}">
                @csrf
                <button type="submit" class="dropdown-item">
                    <i class="fa fa-certificate mr-2 text-warning"></i>Mark Certificate Delivered
                </button>
            </form>
        @endif
    </div>
</div>
