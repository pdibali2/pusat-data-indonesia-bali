<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Hasil Pencarian "{{ $q }}"</title>
    <meta name="description" content="Hasil pencarian data untuk kata kunci {{ $q }}."/>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon"/>
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    @vite(['resources/css/app.css', 'resources/js/app.ts'])
</head>
<body class="bg-slate-50 text-gray-900 antialiased">

    @include('pages.landing.components.navbar')

    <main class="pb-20 min-h-screen">

        {{-- PAGE HEADER --}}
        <div class="bg-stikom py-20 pb-14 relative overflow-hidden border-l-4 border-stikom-blue">
            <div class="absolute inset-0 opacity-[.06]" aria-hidden="true"
                 style="background-image:repeating-linear-gradient(0deg,rgba(255,255,255,.4) 0,rgba(255,255,255,.4) 1px,transparent 1px,transparent 40px),repeating-linear-gradient(90deg,rgba(255,255,255,.4) 0,rgba(255,255,255,.4) 1px,transparent 1px,transparent 40px)">
            </div>
            <div class="absolute inset-0 opacity-[.07]"
                 style="background-image:radial-gradient(circle,#3DB166 1px,transparent 1px);background-size:24px 24px"
                 aria-hidden="true"></div>

            <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                {{-- Breadcrumb --}}
                <nav class="flex items-center gap-2 text-[11px] text-white/35 mb-8 font-body" aria-label="Breadcrumb">
                    <a href="{{ route('landing') }}" class="hover:text-white/70 transition-colors">Beranda</a>
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                    <span class="text-white/60">Hasil Pencarian</span>
                </nav>

                <div class="inline-flex items-center gap-2 px-3 py-1.5 mb-4 bg-stikom-accent/15 border border-stikom-accent/40">
                    <div class="w-1.5 h-1.5 bg-stikom-accent"></div>
                    <span class="text-stikom-accent text-[10px] font-bold uppercase tracking-[.12em] font-display">Pencarian</span>
                </div>

                <h1 class="text-2xl sm:text-3xl font-black text-white font-display leading-tight mb-2">
                    Hasil untuk <span class="text-stikom-accent">"{{ $q }}"</span>
                </h1>
                @if($totalFound > 0)
                    <p class="text-white/50 text-sm font-body">{{ $totalFound }} data ditemukan</p>
                @endif
            </div>
        </div>

        {{-- SEARCH BAR dengan autocomplete --}}
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 -mt-5 relative z-10 mb-10"
            x-data="searchPageBar()">
            <div class="relative">
                <div class="bg-white border-l-4 border-stikom-red shadow-xl shadow-stikom/10 flex items-center gap-3 px-5 py-4">
                    <svg x-show="!loading" class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                    </svg>
                    <svg x-show="loading" class="w-4 h-4 text-gray-400 animate-spin shrink-0" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                    </svg>
                    <input
                        type="search"
                        x-model="query"
                        @input.debounce.350ms="search"
                        @keydown.enter="goToSearch"
                        @keydown.escape="close"
                        @focus="query.length >= 2 && search()"
                        placeholder="Cari data lainnya..."
                        class="flex-1 bg-transparent text-sm text-gray-700 placeholder-gray-400 outline-none font-body"
                        autocomplete="off"
                    />
                    <button @click="goToSearch"
                            class="shrink-0 px-4 py-1.5 bg-stikom text-white text-xs font-bold hover:bg-stikom-red transition-colors">
                        Cari
                    </button>
                </div>

                {{-- Dropdown suggestion --}}
                <div x-show="showSuggestions"
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    @click.outside="close"
                    class="absolute top-full left-0 right-0 bg-white shadow-2xl border border-gray-100
                            border-t-2 border-t-stikom-accent overflow-hidden overflow-y-auto max-h-72 z-50">

                    <template x-if="suggestions.length === 0 && !loading && query.length >= 2">
                        <div class="px-5 py-4 text-xs text-gray-400 flex items-center gap-3">
                            <svg class="w-5 h-5 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Tidak ada data yang sesuai
                        </div>
                    </template>

                    <template x-for="(item, index) in suggestions" :key="index">
                        <button
                            @click="selectSuggestion(item)"
                            class="w-full flex items-center gap-3 px-5 py-3 border-b border-gray-50
                                last:border-0 hover:bg-stikom-accent/8 text-left min-h-[44px] transition-colors">
                            <svg class="w-4 h-4 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
                            </svg>
                            <div class="flex flex-col min-w-0 flex-1">
                                <span class="text-sm text-gray-800 truncate" x-text="item.label"></span>
                                <span class="text-xs truncate"
                                    :class="item.found_in ? 'text-stikom-accent/80' : 'text-gray-400'"
                                    x-text="item.found_in
                                        ? 'ditemukan di: ' + item.found_in + (item.klasifikasi ? ' · ' + item.klasifikasi : '')
                                        : (item.klasifikasi ?? '')">
                                </span>
                            </div>
                            <svg class="w-3.5 h-3.5 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                        </button>
                    </template>
                </div>
            </div>
        </div>

        {{-- RESULTS --}}
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            @if(strlen($q) < 2)
                {{-- Query terlalu pendek --}}
                <div class="text-center py-24">
                    <div class="w-16 h-16 bg-gray-100 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                        </svg>
                    </div>
                    <p class="text-gray-400 text-base font-semibold mb-1">Ketik minimal 2 karakter</p>
                    <p class="text-gray-300 text-sm">untuk memulai pencarian data.</p>
                </div>

            @elseif($totalFound === 0)
                {{-- Tidak ada hasil --}}
                <div class="text-center py-24">
                    <div class="w-16 h-16 bg-gray-100 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <p class="text-gray-400 text-base font-semibold mb-1">Tidak ada data ditemukan</p>
                    <p class="text-gray-300 text-sm mb-6">Coba kata kunci yang berbeda atau lebih umum.</p>
                    <a href="{{ route('klasifikasi.index') }}"
                       class="inline-flex items-center gap-2 px-5 py-2.5 bg-stikom text-white text-sm font-bold hover:bg-stikom-red transition-colors">
                        Jelajahi Klasifikasi
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                </div>

            @else

                {{-- Section label --}}
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-0.5 h-7 bg-stikom-blue shrink-0"></div>
                    <span class="text-[10px] font-bold text-stikom-blue uppercase tracking-[.12em] font-display">Daftar Hasil</span>
                </div>

                {{-- Cards: gratis (is_locked=false) di atas, berbayar di bawah --}}
                <div class="space-y-2">
                    @foreach($sorted as $meta)
                        @if(!$meta->is_locked)
                            @include('pages.landing.klasifikasi._card', ['meta' => $meta])
                        @else
                            @include('pages.landing.search._card_locked', ['meta' => $meta])
                        @endif
                    @endforeach
                </div>

                {{-- Paywall CTA --}}
                @if($isLimited && $sorted->where('is_locked', true)->count() > 0)
                    <div class="mt-6 bg-white border border-gray-100 border-l-4 border-l-stikom-blue shadow-lg p-8 text-center">
                        <div class="w-12 h-12 bg-stikom flex items-center justify-center mx-auto mb-4">
                            <svg class="w-6 h-6 text-stikom-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-black text-stikom mb-2 font-display">Akses Semua Hasil Pencarian</h3>
                        <p class="text-gray-500 text-sm mb-6 max-w-sm mx-auto font-body">
                            Berlangganan untuk membuka semua data yang terkunci.
                        </p>
                        <a href="{{ route('langganan') }}"
                        class="px-6 py-3 bg-stikom-accent text-black font-black text-sm hover:bg-yellow-500 transition-colors">
                            Berlangganan Sekarang
                        </a>
                    </div>
                @endif

                {{-- Pagination --}}
                @if($sorted->hasPages())
                    <div class="mt-10 flex flex-col sm:flex-row items-center justify-between gap-4">
                        <p class="text-sm text-gray-500 order-2 sm:order-1">
                            Halaman <span class="font-bold text-stikom">{{ $sorted->currentPage() }}</span>
                            dari <span class="font-bold text-stikom">{{ $sorted->lastPage() }}</span>
                        </p>
                        <nav class="flex items-center gap-1 order-1 sm:order-2" aria-label="Paginasi">
                            @if($sorted->onFirstPage())
                                <span class="w-9 h-9 flex items-center justify-center text-gray-300 cursor-not-allowed">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                </span>
                            @else
                                <a href="{{ $sorted->previousPageUrl() }}" class="w-9 h-9 flex items-center justify-center border border-gray-200 text-gray-500 hover:border-stikom hover:text-stikom transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                </a>
                            @endif

                            @php
                                $window = 2; $current = $sorted->currentPage();
                                $last = $sorted->lastPage();
                                $start = max(1, $current - $window);
                                $end = min($last, $current + $window);
                            @endphp

                            @if($start > 1)
                                <a href="{{ $sorted->url(1) }}" class="w-9 h-9 flex items-center justify-center border border-gray-200 text-sm text-gray-600 hover:border-stikom hover:text-stikom transition-colors">1</a>
                                @if($start > 2)<span class="w-9 h-9 flex items-center justify-center text-gray-400 text-sm">…</span>@endif
                            @endif

                            @for($p = $start; $p <= $end; $p++)
                                @if($p === $current)
                                    <span class="w-9 h-9 flex items-center justify-center bg-stikom text-white text-sm font-bold">{{ $p }}</span>
                                @else
                                    <a href="{{ $sorted->url($p) }}" class="w-9 h-9 flex items-center justify-center border border-gray-200 text-sm text-gray-600 hover:border-stikom hover:text-stikom transition-colors">{{ $p }}</a>
                                @endif
                            @endfor

                            @if($end < $last)
                                @if($end < $last - 1)<span class="w-9 h-9 flex items-center justify-center text-gray-400 text-sm">…</span>@endif
                                <a href="{{ $sorted->url($last) }}" class="w-9 h-9 flex items-center justify-center border border-gray-200 text-sm text-gray-600 hover:border-stikom hover:text-stikom transition-colors">{{ $last }}</a>
                            @endif

                            @if($sorted->hasMorePages())
                                <a href="{{ $sorted->nextPageUrl() }}" class="w-9 h-9 flex items-center justify-center border border-gray-200 text-gray-500 hover:border-stikom hover:text-stikom transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            @else
                                <span class="w-9 h-9 flex items-center justify-center text-gray-300 cursor-not-allowed">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </span>
                            @endif
                        </nav>
                    </div>
                @endif

            @endif
        </div>
    </main>

    @include('pages.landing.components.footer')

    {{-- Back to top --}}
    <button id="back-to-top"
            onclick="window.scrollTo({ top: 0, behavior: 'smooth' })"
            class="fixed bottom-6 right-6 z-50 w-12 h-12 bg-stikom-red text-white shadow-xl
                   flex items-center justify-center opacity-0 translate-y-4 pointer-events-none
                   transition-all duration-300 hover:scale-110"
            aria-label="Kembali ke atas">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
        </svg>
    </button>

    <script>
    (function () {
        const btn = document.getElementById('back-to-top');
        window.addEventListener('scroll', () => {
            const past = window.scrollY > 300;
            btn.classList.toggle('opacity-0',           !past);
            btn.classList.toggle('translate-y-4',       !past);
            btn.classList.toggle('pointer-events-none', !past);
            btn.classList.toggle('opacity-100',          past);
            btn.classList.toggle('translate-y-0',        past);
        }, { passive: true });
    })();

    function searchPageBar() {
        return {
            query: '{{ addslashes($q) }}',
            suggestions: [],
            showSuggestions: false,
            loading: false,

            async search() {
                if (this.query.length < 2) {
                    this.suggestions = [];
                    this.showSuggestions = false;
                    return;
                }
                this.loading = true;
                try {
                    const res = await fetch(`/autocomplete?q=${encodeURIComponent(this.query)}`);
                    this.suggestions = await res.json();
                    this.showSuggestions = this.suggestions.length > 0;
                } catch(e) {
                    this.suggestions = [];
                } finally {
                    this.loading = false;
                }
            },

            selectSuggestion(item) {
                this.query = item.label;
                this.showSuggestions = false;
                window.location.href = `/search?q=${encodeURIComponent(item.q)}`;
            },

            goToSearch() {
                const q = this.query.trim();
                if (!q) return;
                this.showSuggestions = false;
                window.location.href = `/search?q=${encodeURIComponent(q)}`;
            },

            close() { this.showSuggestions = false; }
        }
    }
    </script>

</body>
</html>