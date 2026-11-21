{{-- Navigasi halaman untuk {{ $items->links() }}, menimpa tampilan bawaan Laravel agar memakai token tema. --}}
@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi halaman" class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-muted">
            Menampilkan <span class="num">{{ $paginator->firstItem() }}</span> sampai <span class="num">{{ $paginator->lastItem() }}</span> dari <span class="num">{{ $paginator->total() }}</span> data
        </p>

        <ul class="flex flex-wrap items-center gap-1">
            <li>
                @if ($paginator->onFirstPage())
                    <span class="tab opacity-55" aria-disabled="true">Sebelumnya</span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="tab">Sebelumnya</a>
                @endif
            </li>

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li><span class="tab" aria-hidden="true">{{ $element }}</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        <li class="hidden sm:block">
                            @if ($page == $paginator->currentPage())
                                <span class="tab num" aria-current="page">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="tab num" aria-label="Halaman {{ $page }}">{{ $page }}</a>
                            @endif
                        </li>
                    @endforeach
                @endif
            @endforeach

            <li>
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="tab">Berikutnya</a>
                @else
                    <span class="tab opacity-55" aria-disabled="true">Berikutnya</span>
                @endif
            </li>
        </ul>
    </nav>
@endif
