@if ($paginator->hasPages())
    <nav class="flex flex-col sm:flex-row items-center justify-between gap-4 mt-8" aria-label="Pagination">

        {{-- Info halaman --}}
        <p class="text-sm text-gray-500 order-2 sm:order-1">
            Halaman
            <span class="font-bold text-[#001734]">{{ $paginator->currentPage() }}</span>
            dari
            <span class="font-bold text-[#001734]">{{ $paginator->lastPage() }}</span>
            <span class="text-gray-400 ml-1">({{ number_format($paginator->total()) }} total)</span>
        </p>

        {{-- Tombol halaman --}}
        <div class="flex items-center gap-1 order-1 sm:order-2">

            {{-- Prev --}}
            @if ($paginator->onFirstPage())
                <span class="w-9 h-9 flex items-center justify-center rounded-xl text-gray-300 cursor-not-allowed" aria-disabled="true">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}"
                   class="w-9 h-9 flex items-center justify-center rounded-xl border border-gray-200 text-gray-500
                          hover:border-[#001734] hover:text-[#001734] transition-colors"
                   aria-label="Halaman sebelumnya">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
            @endif

            {{-- Window halaman --}}
            @php
                $window  = 2;
                $current = $paginator->currentPage();
                $last    = $paginator->lastPage();
                $start   = max(1, $current - $window);
                $end     = min($last, $current + $window);
            @endphp

            @if ($start > 1)
                <a href="{{ $paginator->url(1) }}"
                   class="w-9 h-9 flex items-center justify-center rounded-xl border border-gray-200 text-sm
                          text-gray-600 hover:border-[#001734] hover:text-[#001734] transition-colors">1</a>
                @if ($start > 2)
                    <span class="w-9 h-9 flex items-center justify-center text-gray-400 text-sm">…</span>
                @endif
            @endif

            @for ($p = $start; $p <= $end; $p++)
                @if ($p === $current)
                    <span class="w-9 h-9 flex items-center justify-center rounded-xl bg-[#001734] text-white text-sm font-bold"
                          aria-current="page">{{ $p }}</span>
                @else
                    <a href="{{ $paginator->url($p) }}"
                       class="w-9 h-9 flex items-center justify-center rounded-xl border border-gray-200 text-sm
                              text-gray-600 hover:border-[#001734] hover:text-[#001734] transition-colors">{{ $p }}</a>
                @endif
            @endfor

            @if ($end < $last)
                @if ($end < $last - 1)
                    <span class="w-9 h-9 flex items-center justify-center text-gray-400 text-sm">…</span>
                @endif
                <a href="{{ $paginator->url($last) }}"
                   class="w-9 h-9 flex items-center justify-center rounded-xl border border-gray-200 text-sm
                          text-gray-600 hover:border-[#001734] hover:text-[#001734] transition-colors">{{ $last }}</a>
            @endif

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}"
                   class="w-9 h-9 flex items-center justify-center rounded-xl border border-gray-200 text-gray-500
                          hover:border-[#001734] hover:text-[#001734] transition-colors"
                   aria-label="Halaman berikutnya">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            @else
                <span class="w-9 h-9 flex items-center justify-center rounded-xl text-gray-300 cursor-not-allowed" aria-disabled="true">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </span>
            @endif

        </div>

    </nav>
@endif