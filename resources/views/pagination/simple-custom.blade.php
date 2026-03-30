@if ($paginator->hasPages())
    <nav class="pagination" role="navigation" aria-label="Pagination">
        <div class="actions" style="align-items:center; justify-content:center; width:100%;">
            @if ($paginator->onFirstPage())
                <span class="btn-outline" style="opacity:.5;cursor:not-allowed;">Prev</span>
            @else
                <a class="btn-outline" href="{{ $paginator->previousPageUrl() }}" rel="prev">Prev</a>
            @endif

            @if ($paginator->hasMorePages())
                <a class="btn-outline" href="{{ $paginator->nextPageUrl() }}" rel="next">Next</a>
            @else
                <span class="btn-outline" style="opacity:.5;cursor:not-allowed;">Next</span>
            @endif
        </div>
    </nav>
@endif
