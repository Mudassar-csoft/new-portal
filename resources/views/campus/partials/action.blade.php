 @extends('layouts.theme')

 
 @php
    $actionId = $actionId ?? ('campus-action-' . $campus->id);
@endphp

<div class="dropdown campus-action-dropdown">
    <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="{{ $actionId }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        Actions
    </button>
    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="{{ $actionId }}">
        <a class="dropdown-item" href="{{ route('campus.edit', $campus) }}">
            <i class="fa fa-pencil mr-2 text-info"></i>Edit Campus
        </a>
        <a class="dropdown-item" href="{{ route('batch.index', ['campus_id' => $campus->id]) }}">
            <i class="fa fa-users mr-2 text-primary"></i>View Batches
        </a>
        <a class="dropdown-item" href="{{ route('inventory.index', ['campus_id' => $campus->id]) }}">
            <i class="fa fa-archive mr-2 text-success"></i>View Inventory
        </a>
    </div>
</div>
