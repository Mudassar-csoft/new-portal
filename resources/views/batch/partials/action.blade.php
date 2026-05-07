@php
    $actionId = $actionId ?? ('batch-action-' . $batch->id);
@endphp

<div class="dropdown batch-action-dropdown">
    <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="{{ $actionId }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        Actions
    </button>
    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="{{ $actionId }}">
        <a class="dropdown-item" href="{{ route('batch.edit', $batch) }}">
            <!-- <i class="fa fa-pencil mr-2 text-info"></i> -->
            Edit 
        </a>
        <a class="dropdown-item" href="{{ route('batch.timetable.index', ['batch_id' => $batch->id]) }}">
            <!-- <i class="fa fa-calendar mr-2 text-primary"></i> -->
            Manage Time Table
        </a>
    </div>
</div>
