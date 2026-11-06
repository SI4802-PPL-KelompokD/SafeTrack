{{-- Paginasi sederhana tanpa Tailwind. Pakai: @include('partials.pager', ['paginator' => $reports]) --}}
@if ($paginator->hasPages())
    <div class="pager">
        @if ($paginator->onFirstPage())
            <span class="btn btn-ghost btn-sm" style="opacity:.5;">&larr; Sebelumnya</span>
        @else
            <a class="btn btn-ghost btn-sm" href="{{ $paginator->previousPageUrl() }}">&larr; Sebelumnya</a>
        @endif
        <span class="muted">Halaman {{ $paginator->currentPage() }} dari {{ $paginator->lastPage() }}</span>
        @if ($paginator->hasMorePages())
            <a class="btn btn-ghost btn-sm" href="{{ $paginator->nextPageUrl() }}">Berikutnya &rarr;</a>
        @else
            <span class="btn btn-ghost btn-sm" style="opacity:.5;">Berikutnya &rarr;</span>
        @endif
    </div>
@endif
