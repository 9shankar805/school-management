@if ($paginator->hasPages())
<nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex items-center justify-between gap-4 flex-wrap">

    {{-- Summary --}}
    <p class="text-xs" style="color:var(--text-muted)">
        Showing
        <span class="font-medium" style="color:var(--text-body)">{{ $paginator->firstItem() }}</span>
        –
        <span class="font-medium" style="color:var(--text-body)">{{ $paginator->lastItem() }}</span>
        of
        <span class="font-medium" style="color:var(--text-body)">{{ $paginator->total() }}</span>
        results
    </p>

    {{-- Page buttons --}}
    <div class="pagination-modern">
        {{-- Previous --}}
        @if($paginator->onFirstPage())
            <span class="disabled" aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                <i class="bi bi-chevron-left" style="font-size:.7rem"></i>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="{{ __('pagination.previous') }}">
                <i class="bi bi-chevron-left" style="font-size:.7rem"></i>
            </a>
        @endif

        {{-- Page numbers --}}
        @foreach($elements as $element)
            @if(is_string($element))
                <span class="disabled px-1" aria-disabled="true">{{ $element }}</span>
            @endif
            @if(is_array($element))
                @foreach($element as $page => $url)
                    @if($page == $paginator->currentPage())
                        <span class="current" aria-current="page" aria-label="{{ __('pagination.page', ['page' => $page]) }}">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" aria-label="{{ __('pagination.page', ['page' => $page]) }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next --}}
        @if($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="{{ __('pagination.next') }}">
                <i class="bi bi-chevron-right" style="font-size:.7rem"></i>
            </a>
        @else
            <span class="disabled" aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                <i class="bi bi-chevron-right" style="font-size:.7rem"></i>
            </span>
        @endif
    </div>
</nav>
@endif
