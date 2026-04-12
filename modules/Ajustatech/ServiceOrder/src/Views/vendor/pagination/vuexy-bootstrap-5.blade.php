@if ($paginator->hasPages())
    <nav class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3" aria-label="Page navigation">
        <div class="small text-body-secondary">
            {{ trans('service-order::messages.pagination_showing_results', ['first' => $paginator->firstItem() ?? 0, 'last' => $paginator->lastItem() ?? 0, 'total' => $paginator->total()]) }}
        </div>

        <ul class="pagination mb-0 pagination-rounded pagination-outline-primary">
            <li class="page-item prev {{ $paginator->onFirstPage() ? 'disabled' : '' }}">
                @if ($paginator->onFirstPage())
                    <span class="page-link" aria-hidden="true">‹</span>
                @else
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="{{ __('pagination.previous') }}">‹</a>
                @endif
            </li>

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="page-item disabled" aria-disabled="true"><span class="page-link">{{ $element }}</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        <li class="page-item {{ $page == $paginator->currentPage() ? 'active' : '' }}" aria-current="{{ $page == $paginator->currentPage() ? 'page' : 'false' }}">
                            @if ($page == $paginator->currentPage())
                                <span class="page-link">{{ $page }}</span>
                            @else
                                <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                            @endif
                        </li>
                    @endforeach
                @endif
            @endforeach

            <li class="page-item next {{ $paginator->hasMorePages() ? '' : 'disabled' }}">
                @if ($paginator->hasMorePages())
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="{{ __('pagination.next') }}">›</a>
                @else
                    <span class="page-link" aria-hidden="true">›</span>
                @endif
            </li>
        </ul>
    </nav>
@endif
