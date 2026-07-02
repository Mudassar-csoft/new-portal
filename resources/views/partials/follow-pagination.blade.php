@php
    $paginator = $paginator ?? null;
    $countId = $countId ?? null;
    $label = $label ?? 'entries';

    $currentPage = $paginator?->currentPage() ?? 1;
    $lastPage = $paginator?->lastPage() ?? 1;
    $startPage = max(1, $currentPage - 2);
    $endPage = min($lastPage, $currentPage + 2);
@endphp

<div @if($countId) id="{{ $countId }}" @endif>
    Showing {{ $paginator?->firstItem() ?? 0 }} to {{ $paginator?->lastItem() ?? 0 }} of {{ $paginator?->total() ?? 0 }} {{ $label }}
</div>

<ul class="pagination pagination-sm mb-0">
    <li class="page-item {{ $paginator?->onFirstPage() ? 'disabled' : '' }}">
        <a class="page-link" href="{{ $paginator && ! $paginator->onFirstPage() ? $paginator->previousPageUrl() : '#' }}">Previous</a>
    </li>

    @if ($startPage > 1)
        <li class="page-item"><a class="page-link" href="{{ $paginator->url(1) }}">1</a></li>
        @if ($startPage > 2)
            <li class="page-item disabled"><span class="page-link">...</span></li>
        @endif
    @endif

    @for ($page = $startPage; $page <= $endPage; $page++)
        <li class="page-item {{ $page === $currentPage ? 'active' : '' }}">
            <a class="page-link" href="{{ $paginator?->url($page) ?? '#' }}">{{ $page }}</a>
        </li>
    @endfor

    @if ($endPage < $lastPage)
        @if ($endPage < $lastPage - 1)
            <li class="page-item disabled"><span class="page-link">...</span></li>
        @endif
        <li class="page-item"><a class="page-link" href="{{ $paginator->url($lastPage) }}">{{ $lastPage }}</a></li>
    @endif

    <li class="page-item {{ $paginator?->hasMorePages() ? '' : 'disabled' }}">
        <a class="page-link" href="{{ $paginator && $paginator->hasMorePages() ? $paginator->nextPageUrl() : '#' }}">Next</a>
    </li>
</ul>
