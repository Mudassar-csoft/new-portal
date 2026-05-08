@php
    $actionId = $actionId ?? ('program-action-' . $program->id);
@endphp

<div class="dropdown program-action-dropdown">
    <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="{{ $actionId }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        Actions
    </button>
    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="{{ $actionId }}">
        <a class="dropdown-item" href="{{ route('program.edit', $program) }}">
            <!-- <i class="fa fa-pencil mr-2 text-info"></i> -->
            Edit
        </a>
        <a class="dropdown-item" href="{{ route('batch.index', ['program_id' => $program->id]) }}">
            <!-- <i class="fa fa-users mr-2 text-primary"></i> -->
            View Batches
        </a>
        <a class="dropdown-item" href="{{ route('batch.timetable.index', ['program_id' => $program->id]) }}">
            <!-- <i class="fa fa-calendar mr-2 text-success"></i> -->
            View Time Table
        </a>
        @if($program->outline_path)
            <a class="dropdown-item" href="{{ route('program.outline', $program) }}">
                <i class="fa fa-download mr-2 text-muted"></i>Download Outline
            </a>
        @endif
    </div>
</div>
