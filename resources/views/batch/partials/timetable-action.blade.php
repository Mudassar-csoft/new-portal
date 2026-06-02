@php
    $query = request()->query();
    $query['edit'] = $entry->id;
@endphp

<div class="d-flex justify-content-end align-items-center" style="gap:8px;">
    <a href="{{ route('batch.timetable.index', $query) }}" class="btn btn-primary-outline mt-1">Edit</a>
    <form method="POST" action="{{ route('batch.timetable.destroy', $entry) }}" onsubmit="return confirm('Delete this timetable entry?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-sm btn-danger-outline">Delete</button>
    </form>
</div>
