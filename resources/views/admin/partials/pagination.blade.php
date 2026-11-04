@if ($paginator->hasPages())
    <nav class="admin-pagination" role="navigation" aria-label="Phân trang">
        @if ($paginator instanceof \Illuminate\Pagination\LengthAwarePaginator && $paginator->total() > 0)
            <span class="admin-pagination-info">
                Hiển thị {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} / {{ $paginator->total() }} mục
            </span>
        @endif

        <div class="admin-pagination-links">
            @if ($paginator->onFirstPage())
                <span class="page-btn is-disabled" aria-disabled="true">‹ Trước</span>
            @else
                <a class="page-btn" href="{{ $paginator->previousPageUrl() }}" rel="prev">‹ Trước</a>
            @endif

            @foreach ($elements ?? [] as $element)
                @if (is_string($element))
                    <span class="page-dots">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="page-btn is-current" aria-current="page">{{ $page }}</span>
                        @else
                            <a class="page-btn" href="{{ $url }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a class="page-btn" href="{{ $paginator->nextPageUrl() }}" rel="next">Sau ›</a>
            @else
                <span class="page-btn is-disabled" aria-disabled="true">Sau ›</span>
            @endif
        </div>
    </nav>
@endif
