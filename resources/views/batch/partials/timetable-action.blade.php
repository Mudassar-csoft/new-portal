@extends('layouts.theme')
@php
    $query = request()->query();
    $query['edit'] = $entry->id;
@endphp

<div class="d-flex justify-content-end align-items-center" style="gap:8px;">
    <a href="{{ route('batch.timetable.index', $query) }}" class="btn btn-sm btn-primary-outline" style="padding: 0.3rem 0.65rem;">Edit</a>
    <form method="POST" action="{{ route('batch.timetable.destroy', $entry) }}" onsubmit="return confirm('Delete this timetable entry?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-sm btn-danger-outline" style="padding: 0.3rem 0.65rem;">Delete</button>
    </form>
</div>
