@if ($paginator->hasPages())
    <nav class="pagination" role="navigation" aria-label="Pagination">
        <div class="actions" style="align-items:center; justify-content:center; width:100%;">
            @if ($paginator->onFirstPage())
                <span class="btn-outline" style="opacity:.5;cursor:not-allowed;">Prev</span>
            @else
                <a class="btn-outline" href="{{ $paginator->previousPageUrl() }}" rel="prev">Prev</a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="muted" style="padding:.2rem .4rem;">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="btn" style="cursor:default;">{{ $page }}</span>
                        @else
                            <a class="btn-outline" href="{{ $url }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a class="btn-outline" href="{{ $paginator->nextPageUrl() }}" rel="next">Next</a>
            @else
                <span class="btn-outline" style="opacity:.5;cursor:not-allowed;">Next</span>
            @endif
        </div>
    </nav>
@endif
