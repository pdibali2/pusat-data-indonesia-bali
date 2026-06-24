<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Klasifikasi - {{ $nama }}</title>
    <meta name="description" content="Daftar data kategori {{ $nama }} di Pusat Data Indonesia Bali."/>
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
                <nav class="flex items-center gap-2 text-[11px] text-white/35 mb-8 font-body" aria-label="Breadcrumb">
                    <a href="{{ route('landing') }}" class="hover:text-white/70 transition-colors">Beranda</a>
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    <a href="{{ route('klasifikasi.index') }}" class="hover:text-white/70 transition-colors">Klasifikasi</a>
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    <span class="text-white/60 truncate max-w-[200px]">{{ $nama }}</span>
                </nav>
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 mb-4 bg-stikom-accent/15 border border-stikom-accent/40">
                        <div class="w-1.5 h-1.5 bg-stikom-accent"></div>
                        <span class="text-stikom-accent text-[10px] font-bold uppercase tracking-[.12em] font-display">Klasifikasi</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl lg:text-4xl font-black text-white font-display leading-tight">{{ $nama }}</h1>
                </div>
            </div>
        </div>

        {{-- SEARCH BAR --}}
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 -mt-5 relative z-10 mb-10"
             x-data="klasifikasiSearch('{{ $nama }}')">
            <div class="bg-white border-l-4 border-stikom-red shadow-xl shadow-stikom/10 flex items-center gap-3 px-5 py-4">
                <svg x-show="!loading" class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                </svg>
                <svg x-show="loading" class="w-4 h-4 text-gray-400 shrink-0 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                </svg>
                <input
                    type="search"
                    x-model="query"
                    @input.debounce.350ms="search"
                    @keydown.escape="reset"
                    placeholder="Cari data dalam klasifikasi ini..."
                    class="flex-1 bg-transparent text-sm text-gray-700 placeholder-gray-400 outline-none font-body"
                    autocomplete="off"
                />
                <span x-show="query.length >= 2 && !loading"
                      @click="reset"
                      class="text-xs text-gray-300 hover:text-red-400 cursor-pointer transition-colors">✕</span>
                <span x-show="!query || query.length < 2"
                      class="text-[10px] font-bold text-gray-300 uppercase tracking-widest hidden sm:block">ENTER ↵</span>
            </div>
        </div>

        {{-- CONTENT --}}
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 mt-8">

            @if($metadataList->isEmpty())
                <div class="text-center py-24">
                    <div class="w-16 h-16 bg-gray-100 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <p class="text-gray-300 text-xl font-bold">Belum ada data dalam klasifikasi ini.</p>
                </div>
            @else

                {{-- Freemium banner --}}
                @if($isLimited)
                    @php $lockedCount = $metadataList->filter(fn($m) => !in_array($m->metadata_id, $freeIds))->count(); @endphp
                    @if($lockedCount > 0)
                        <div class="mb-6 flex items-start gap-3 px-4 py-3 bg-amber-50 border border-amber-200">
                            <svg class="w-4 h-4 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            <p class="text-xs text-amber-700 leading-relaxed">
                                Data bertanda premium memerlukan langganan.
                                <a href="{{ route('langganan') }}" class="font-bold underline hover:text-amber-900">Berlangganan</a> untuk akses penuh.
                            </p>
                        </div>
                    @endif
                @endif

                <div class="flex items-center gap-3 mb-5">
                    <div class="w-0.5 h-7 bg-stikom-blue shrink-0"></div>
                    <span class="text-[10px] font-bold text-stikom-blue uppercase tracking-[.12em] font-display">Daftar Data</span>
                </div>

                {{-- Container kartu (Alpine akan replace ini saat search) --}}
                <div id="metadata-container" class="space-y-2">
                    @foreach($metadataList as $meta)
                        @php $isLocked = $isLimited && !in_array($meta->metadata_id, $freeIds); @endphp

                        @if(!$isLocked)
                            @include('pages.landing.klasifikasi._card', ['meta' => $meta])
                        @else
                            <div class="relative bg-white border border-gray-100 border-l-4 border-l-stikom-blue shadow-sm overflow-hidden">
                                <div class="flex items-start sm:items-center gap-4 px-5 py-4">
                                    <div class="w-10 h-10 bg-stikom/5 flex items-center justify-center shrink-0">
                                        <svg class="w-5 h-5 text-stikom" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h2 class="text-sm font-bold text-gray-800 line-clamp-1 mb-1 font-body">{{ $meta->nama }}</h2>
                                        <div class="flex flex-wrap gap-x-4 gap-y-1 text-[11px] text-gray-400 font-body">
                                            @if($meta->satuan_data)
                                                <span class="flex items-center gap-1">
                                                    <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                                    {{ $meta->satuan_data }}
                                                </span>
                                            @endif
                                            @if($meta->frekuensi_penerbitan)
                                                <span class="flex items-center gap-1">
                                                    <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    {{ $meta->frekuensi_penerbitan }}
                                                </span>
                                            @endif
                                            @if($meta->tahun_mulai_data)
                                                <span class="flex items-center gap-1">
                                                    <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                    sejak {{ $meta->tahun_mulai ?? $meta->tahun_mulai_data ?? '—' }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <a href="{{ route('langganan') }}"
                                       class="shrink-0 flex items-center gap-1.5 px-3 py-1.5 bg-stikom text-stikom-accent hover:text-black text-xs font-bold hover:bg-stikom-accent transition-colors duration-200 font-display">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                        Langganan
                                    </a>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>

                {{-- Paywall CTA --}}
                @if($isLimited)
                    @php $lockedOnPage = $metadataList->filter(fn($m) => !in_array($m->metadata_id, $freeIds))->count(); @endphp
                    @if($lockedOnPage > 0)
                        <div class="mt-6 bg-white border border-gray-100 border-l-4 border-l-stikom-blue shadow-lg p-8 text-center">
                            <div class="w-12 h-12 bg-stikom flex items-center justify-center mx-auto mb-4">
                                <svg class="w-6 h-6 text-stikom-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-black text-stikom mb-2 font-display">Akses Semua Dataset</h3>
                            <p class="text-gray-500 text-sm mb-6 max-w-sm mx-auto font-body">
                                Berlangganan untuk akses penuh ke semua data.
                            </p>
                            <a href="{{ route('langganan') }}" class="px-6 py-3 bg-stikom-accent text-black font-black text-sm hover:bg-yellow-500 transition-colors">
                                Berlangganan Sekarang
                            </a>
                        </div>
                    @endif
                @endif

                @if($metadataList->hasPages())
                    <div id="pagination-wrapper" class="mt-8">
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                            <p class="text-sm text-gray-500 order-2 sm:order-1">
                                Halaman <span class="font-bold text-stikom">{{ $metadataList->currentPage() }}</span>
                                dari <span class="font-bold text-stikom">{{ $metadataList->lastPage() }}</span>
                            </p>
                            <nav class="flex items-center gap-1 order-1 sm:order-2" aria-label="Paginasi">
                                @if($metadataList->onFirstPage())
                                    <span class="w-9 h-9 flex items-center justify-center text-gray-300 cursor-not-allowed">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                    </span>
                                @else
                                    <a href="{{ $metadataList->previousPageUrl() }}" class="w-9 h-9 flex items-center justify-center border border-gray-200 text-gray-500 hover:border-stikom hover:text-stikom transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                    </a>
                                @endif

                                @php
                                    $window  = 2;
                                    $current = $metadataList->currentPage();
                                    $last    = $metadataList->lastPage();
                                    $start   = max(1, $current - $window);
                                    $end     = min($last, $current + $window);
                                @endphp

                                @if($start > 1)
                                    <a href="{{ $metadataList->url(1) }}" class="w-9 h-9 flex items-center justify-center border border-gray-200 text-sm text-gray-600 hover:border-stikom hover:text-stikom transition-colors">1</a>
                                    @if($start > 2)<span class="w-9 h-9 flex items-center justify-center text-gray-400 text-sm">…</span>@endif
                                @endif

                                @for($p = $start; $p <= $end; $p++)
                                    @if($p === $current)
                                        <span class="w-9 h-9 flex items-center justify-center bg-stikom text-white text-sm font-bold">{{ $p }}</span>
                                    @else
                                        <a href="{{ $metadataList->url($p) }}" class="w-9 h-9 flex items-center justify-center border border-gray-200 text-sm text-gray-600 hover:border-stikom hover:text-stikom transition-colors">{{ $p }}</a>
                                    @endif
                                @endfor

                                @if($end < $last)
                                    @if($end < $last - 1)<span class="w-9 h-9 flex items-center justify-center text-gray-400 text-sm">…</span>@endif
                                    <a href="{{ $metadataList->url($last) }}" class="w-9 h-9 flex items-center justify-center border border-gray-200 text-sm text-gray-600 hover:border-stikom hover:text-stikom transition-colors">{{ $last }}</a>
                                @endif

                                @if($metadataList->hasMorePages())
                                    <a href="{{ $metadataList->nextPageUrl() }}" class="w-9 h-9 flex items-center justify-center border border-gray-200 text-gray-500 hover:border-stikom hover:text-stikom transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </a>
                                @else
                                    <span class="w-9 h-9 flex items-center justify-center text-gray-300 cursor-not-allowed">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </span>
                                @endif
                            </nav>
                        </div>
                    </div>
                @endif

                {{-- Empty search result --}}
                <div id="search-empty" class="hidden text-center py-10">
                    <p class="text-sm text-gray-400 font-body">Tidak ada data yang sesuai pencarian.</p>
                </div>

            @endif
        </div>
    </main>

    @include('pages.landing.components.footer')

    <button id="back-to-top"
            onclick="window.scrollTo({ top: 0, behavior: 'smooth' })"
            class="fixed bottom-6 right-6 z-50 w-12 h-12 bg-stikom-red text-white shadow-xl flex items-center justify-center opacity-0 translate-y-4 pointer-events-none transition-all duration-300 hover:scale-110"
            aria-label="Kembali ke atas">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
        </svg>
    </button>

    <script>
    // Back to top
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

    // Alpine search component
    function klasifikasiSearch(klasifikasi) {
        return {
            query: '',
            loading: false,
            originalHTML: '',

            init() {
                this.originalHTML = document.getElementById('metadata-container').innerHTML;
            },

            async search() {
                const q         = this.query.trim();
                const container = document.getElementById('metadata-container');
                const emptyEl   = document.getElementById('search-empty');
                const paginationWrapper = document.getElementById('pagination-wrapper');

                if (q.length < 2) {
                    container.innerHTML = this.originalHTML;
                    if (emptyEl) emptyEl.classList.add('hidden');
                    if (paginationWrapper) paginationWrapper.classList.remove('hidden');
                    return;
                }

                this.loading = true;

                try {
                    const params = new URLSearchParams({ q, klasifikasi });
                    const res    = await fetch(`/search-metadata?${params}`, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    const items = await res.json();

                    // Sembunyikan pagination saat search aktif
                    if (paginationWrapper) paginationWrapper.classList.add('hidden');

                    if (items.length === 0) {
                        container.innerHTML = '';
                        if (emptyEl) emptyEl.classList.remove('hidden');
                        return;
                    }

                    if (emptyEl) emptyEl.classList.add('hidden');
                    this.renderResults(container, items);
                } catch (err) {
                    console.error('Search error:', err);
                } finally {
                    this.loading = false;
                }
            },

            reset() {
                this.query      = '';
                const container = document.getElementById('metadata-container');
                const emptyEl   = document.getElementById('search-empty');
                const paginationWrapper = document.getElementById('pagination-wrapper');

                container.innerHTML = this.originalHTML;
                if (emptyEl) emptyEl.classList.add('hidden');
                if (paginationWrapper) paginationWrapper.classList.remove('hidden');
            },

            renderResults(container, items) {
                container.innerHTML = '';

                const esc = str => String(str ?? '')
                    .replace(/&/g,'&amp;').replace(/</g,'&lt;')
                    .replace(/>/g,'&gt;').replace(/"/g,'&quot;');

                items.forEach(item => {
                    const card = document.createElement('div');

                    if (item.is_locked) {
                        // Locked card
                        card.className = 'relative bg-white border border-gray-100 border-l-4 border-l-stikom-blue shadow-sm overflow-hidden';
                        card.innerHTML = `
                            <div class="flex items-start sm:items-center gap-4 px-5 py-4">
                                <div class="w-10 h-10 bg-stikom/5 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5 text-stikom" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h2 class="text-sm font-bold text-gray-800 line-clamp-1 mb-1">${esc(item.nama)}</h2>
                                    <div class="flex flex-wrap gap-x-4 gap-y-1 text-[11px] text-gray-400">
                                        ${item.satuan_data ? `<span>${esc(item.satuan_data)}</span>` : ''}
                                        ${item.frekuensi_penerbitan ? `<span>${esc(item.frekuensi_penerbitan)}</span>` : ''}
                                    </div>
                                </div>
                                <a href="/langganan" class="shrink-0 flex items-center gap-1.5 px-3 py-1.5 bg-stikom text-stikom-accent hover:text-black text-xs font-bold hover:bg-stikom-accent transition-colors duration-200">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                    Langganan
                                </a>
                            </div>`;
                    } else {
                        // Free card
                        card.className = 'group bg-white border border-gray-100 border-l-4 border-l-transparent shadow-sm hover:border-l-stikom-blue hover:shadow-md transition-all duration-200 cursor-pointer overflow-hidden';
                        card.onclick = () => { window.location.href = `/statistik/${item.metadata_id}`; };
                        card.innerHTML = `
                            <div class="flex items-start sm:items-center gap-4 px-5 py-4">
                                <div class="w-10 h-10 bg-stikom/5 group-hover:bg-stikom flex items-center justify-center shrink-0 transition-colors duration-200">
                                    <svg class="w-5 h-5 text-stikom group-hover:text-stikom-accent transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h2 class="text-sm font-bold text-gray-800 group-hover:text-stikom transition-colors line-clamp-1 mb-1">${esc(item.nama)}</h2>
                                    <div class="flex flex-wrap gap-x-4 gap-y-1 text-[11px] text-gray-400">
                                        ${item.satuan_data ? `<span>${esc(item.satuan_data)}</span>` : ''}
                                        ${item.frekuensi_penerbitan ? `<span>${esc(item.frekuensi_penerbitan)}</span>` : ''}
                                        ${item.tahun_mulai_data ? `<span>Sejak ${esc(String(item.tahun_mulai_data))}</span>` : ''}
                                    </div>
                                </div>
                                <svg class="w-4 h-4 text-gray-300 group-hover:text-stikom-blue shrink-0 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </div>`;
                    }

                    container.appendChild(card);
                });
            }
        };
    }
    </script>

</body>
</html>