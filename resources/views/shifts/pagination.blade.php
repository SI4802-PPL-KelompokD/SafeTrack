@if ($paginator->hasPages())
<nav class="wa-pager" aria-label="Navigasi halaman daftar shift">
    <p class="wa-pager-summary">Menampilkan {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} dari {{ $paginator->total() }} data</p>
    <div class="wa-pager-buttons">
        @if ($paginator->onFirstPage())
            <span class="wa-page disabled" aria-disabled="true" aria-label="Halaman sebelumnya">‹ Previous</span>
        @else
            <a class="wa-page" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Halaman sebelumnya">‹ Previous</a>
        @endif
        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="wa-page disabled">{{ $element }}</span>
            @else
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="wa-page current" aria-current="page" aria-label="Halaman {{ $page }}">{{ $page }}</span>
                    @else
                        <a class="wa-page" href="{{ $url }}" aria-label="Halaman {{ $page }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach
        @if ($paginator->hasMorePages())
            <a class="wa-page" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Halaman berikutnya">Next ›</a>
        @else
            <span class="wa-page disabled" aria-disabled="true" aria-label="Halaman berikutnya">Next ›</span>
        @endif
    </div>
</nav>
@endif

