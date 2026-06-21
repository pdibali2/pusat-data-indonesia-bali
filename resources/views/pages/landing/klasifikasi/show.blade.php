<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>{{ $nama }} Pusat Data Indonesia Bali</title>
    <meta name="description" content="Daftar data kategori {{ $nama }} di Pusat Data Indonesia Bali."/>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon"/>
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    @vite(['resources/css/app.css', 'resources/js/app.ts'])
</head>
<body class="bg-slate-50 text-gray-900 antialiased">

    @include('pages.landing.components.navbar')

    <main class="pb-20 min-h-screen">

        {{-- ═══ PAGE HEADER ════════════════════════════════════════════ --}}
        <div class="bg-stikom py-20 pb-14 relative overflow-hidden border-l-4 border-stikom-blue">
            {{-- Grid pattern --}}
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
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                    <a href="{{ route('klasifikasi.index') }}" class="hover:text-white/70 transition-colors">Klasifikasi</a>
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                    <span class="text-white/60 truncate max-w-[200px]">{{ $nama }}</span>
                </nav>

                <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-6">
                    <div>
                        {{-- Badge --}}
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 mb-4
                                    bg-stikom-accent/15 border border-stikom-accent/40">
                            <div class="w-1.5 h-1.5 bg-stikom-accent"></div>
                            <span class="text-stikom-accent text-[10px] font-bold uppercase tracking-[.12em] font-display">
                                Klasifikasi
                            </span>
                        </div>
                        <h1 class="text-2xl sm:text-3xl lg:text-4xl font-black text-white font-display leading-tight">
                            {{ $nama }}
                        </h1>
                    </div>

                    {{-- Total count card --}}
                    {{-- <div class="shrink-0 bg-white/[.08] border border-white/10 px-6 py-4 text-center">
                        <div class="text-3xl font-black text-stikom-accent font-display">
                            {{ $metadataList->total() }}
                        </div>
                        <div class="text-[10px] text-white/40 uppercase tracking-widest mt-1 font-body">
                            Total Data
                        </div>
                    </div> --}}
                </div>
            </div>
        </div>

        {{-- ═══ SEARCH BAR ════════════════════════════════════════════ --}}
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 -mt-5 relative z-10 mb-10">
            <div class="bg-white border-l-4 border-stikom-red shadow-xl shadow-stikom/10 flex items-center gap-3 px-5 py-4">
                <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                </svg>
                <input
                    id="search-metadata"
                    type="search"
                    placeholder="Cari data..."
                    data-klasifikasi="{{ $nama }}"
                    class="flex-1 bg-transparent text-sm text-gray-700 placeholder-gray-400 outline-none font-body"
                    autocomplete="off"
                />
                {{-- Spinner saat loading --}}
                <span id="search-loading" class="hidden" aria-label="Memuat...">
                    <svg class="w-4 h-4 text-gray-300 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                    </svg>
                </span>
                <span id="search-hint" class="text-[10px] font-bold text-gray-300 uppercase tracking-widest hidden sm:block">ENTER ↵</span>
            </div>
        </div>

        {{-- ═══ CONTENT ═════════════════════════════════════════════════ --}}
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 mt-8">

            @if($metadataList->isEmpty())
                <div class="text-center py-24">
                    <div class="w-16 h-16 bg-gray-100 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <p class="text-gray-300 text-xl font-bold">Belum ada data dalam klasifikasi ini.</p>
                </div>
            @else
                {{-- Section label --}}
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-0.5 h-7 bg-stikom-blue shrink-0"></div>
                    <span class="text-[10px] font-bold text-stikom-blue uppercase tracking-[.12em] font-display">
                        Daftar Data
                    </span>
                </div>

                {{-- Container kartu --}}
                <div id="metadata-container" class="space-y-2">
                    @foreach($metadataList as $i => $meta)
                        @php
                            $isLocked = $isLimited && ($i + 1) > $freeCountOnPage;
                        @endphp

                        @if(!$isLocked)
                            @include('pages.landing.klasifikasi._card', ['meta' => $meta])
                        @else
                            {{-- LOCKED card — tampil normal, tidak bisa diklik --}}
                            <div class="relative bg-white border border-gray-100 border-l-4 border-l-stikom-blue shadow-sm overflow-hidden">
                                <div class="flex items-start sm:items-center gap-4 px-5 py-4">
                                    {{-- Icon --}}
                                    <div class="w-10 h-10 bg-stikom/5 flex items-center justify-center shrink-0">
                                        <svg class="w-5 h-5 text-stikom" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                        </svg>
                                    </div>

                                    {{-- Info --}}
                                    <div class="flex-1 min-w-0">
                                        <h2 class="text-sm font-bold text-gray-800 line-clamp-1 mb-1 font-body">
                                            {{ $meta->nama }}
                                        </h2>
                                        <div class="flex flex-wrap gap-x-4 gap-y-1 text-[11px] text-gray-400 font-body">
                                            @if($meta->satuan_data)
                                                <span class="flex items-center gap-1">
                                                    <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                                    </svg>
                                                    {{ $meta->satuan_data }}
                                                </span>
                                            @endif
                                            @if($meta->frekuensi_penerbitan)
                                                <span class="flex items-center gap-1">
                                                    <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                    {{ $meta->frekuensi_penerbitan }}
                                                </span>
                                            @endif
                                            @if($meta->tahun_mulai_data)
                                                <span class="flex items-center gap-1">
                                                    <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                    </svg>
                                                    sejak {{ $meta->tahun_mulai ?? $meta->tahun_mulai_data ?? '—' }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Tombol Langganan --}}
                                    <a href="{{ route('langganan') }}"
                                    class="shrink-0 flex items-center gap-1.5 px-3 py-1.5
                                            bg-stikom text-stikom-accent hover:text-black text-xs font-bold
                                            hover:bg-stikom-accent transition-colors duration-200 font-display">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                        </svg>
                                        Langganan
                                    </a>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>

                {{-- Paywall CTA (guest + ada yang terkunci) --}}
                
                    @if($isLimited && $freeCountOnPage < $metadataList->count())
                        <div class="mt-6 bg-white border border-gray-100 border-l-4 border-l-stikom-blue shadow-lg p-8 text-center">
                            <div class="w-12 h-12 bg-stikom flex items-center justify-center mx-auto mb-4">
                                <svg class="w-6 h-6 text-stikom-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-black text-stikom mb-2 font-display">Akses Semua Dataset</h3>
                            <p class="text-gray-500 text-sm mb-6 max-w-sm mx-auto font-body">
                                Anda hanya dapat mengakses beberapa data secara gratis.
                                Berlangganan untuk akses penuh.
                            </p>
                            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                                <a href="{{ route('langganan') }}"
                                class="px-6 py-3 bg-stikom-accent text-black font-black text-sm hover:bg-yellow-500 transition-colors">
                                    Berlangganan Sekarang
                                </a>
                            </div>
                        </div>
                    @endif
                

                {{-- Pesan tidak ditemukan (di luar loop) --}}
                <div id="search-empty" class="hidden text-center py-10">
                    <p class="text-sm text-gray-400 font-body">Belum ada data yang tersedia dalam klasifikasi ini.</p>
                </div>

            @endif

        </div>
    </main>

    @include('pages.landing.components.footer')

    {{-- ═══════════════════════════════════════════════════════════════
         MODAL: Subscription Gate
    ════════════════════════════════════════════════════════════════ --}}
    <div
        id="subscribe-modal"
        class="fixed inset-0 z-[200] flex items-center justify-center p-4
               opacity-0 pointer-events-none transition-opacity duration-200"
        role="dialog" aria-modal="true" aria-labelledby="modal-title"
    >
        {{-- Backdrop --}}
        <div id="modal-backdrop"
             class="absolute inset-0 bg-black/60 backdrop-blur-sm"
             onclick="hideSubscribeGate()"></div>

        {{-- Panel --}}
        <div class="relative z-10 w-full max-w-md bg-white overflow-hidden shadow-2xl
                    scale-95 transition-transform duration-200 border-l-4 border-stikom-blue"
             id="modal-panel">

            {{-- Top accent bar --}}
            <div class="h-1 bg-stikom-blue"></div>

            <div class="px-8 py-8">
                {{-- Lock icon --}}
                <div class="w-14 h-14 bg-stikom-accent/10 border border-stikom-accent/25
                            flex items-center justify-center mx-auto mb-6">
                    <svg class="w-7 h-7 text-stikom-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>

                <h2 id="modal-title" class="text-xl font-black text-stikom text-center mb-2 font-display">
                    Akses Terbatas
                </h2>
                <p class="text-gray-500 text-sm text-center mb-7 leading-relaxed font-body">
                    Untuk melihat detail data dan mengunduh dataset ini, kamu perlu memiliki paket berlangganan aktif.
                </p>

                {{-- Benefits --}}
                <ul class="space-y-2.5 mb-8">
                    @php $benefits = ['Akses semua data', 'Template tampilan data']; @endphp
                    @foreach($benefits as $b)
                        <li class="flex items-start gap-3 text-sm text-gray-600 font-body">
                            <div class="w-4 h-4 bg-stikom-blue flex items-center justify-center shrink-0 mt-0.5">
                                <svg class="w-2.5 h-2.5" fill="none" stroke="white" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            {{ $b }}
                        </li>
                    @endforeach
                </ul>

                {{-- CTAs --}}
                <div class="flex flex-col gap-3">
                    <a href="{{ route('langganan') }}"
                       class="w-full py-3.5 text-sm font-black text-center
                              bg-stikom-accent text-black hover:text-white hover:bg-yellow-600
                              transition-colors duration-200 font-display">
                        Lihat Paket Berlangganan
                    </a>
                    <button onclick="hideSubscribeGate()"
                            class="w-full py-3 text-sm font-semibold text-gray-400
                                   hover:text-gray-600 hover:bg-gray-50
                                   transition-colors duration-200 font-body">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Back to Top --}}
    <button
        id="back-to-top"
        onclick="window.scrollTo({ top: 0, behavior: 'smooth' })"
        class="fixed bottom-6 right-6 z-50 w-12 h-12 bg-stikom-red text-white
               shadow-xl shadow-stikom-red/30 flex items-center justify-center
               opacity-0 translate-y-4 pointer-events-none
               transition-all duration-300 hover:bg-stikom-red hover:scale-110
               focus:outline-none focus:ring-2 focus:ring-stikom-red focus:ring-offset-2"
        aria-label="Kembali ke atas"
    >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
        </svg>
    </button>

    <script>
    // ── Back to top ───────────────────────────────────────────────
    (function () {
        const btn = document.getElementById('back-to-top');
        window.addEventListener('scroll', () => {
            const past = window.scrollY > 300;
            btn.classList.toggle('opacity-0',            !past);
            btn.classList.toggle('translate-y-4',        !past);
            btn.classList.toggle('pointer-events-none',  !past);
            btn.classList.toggle('opacity-100',           past);
            btn.classList.toggle('translate-y-0',         past);
        }, { passive: true });
    })();

    // ── Modal ─────────────────────────────────────────────────────
    const modal = document.getElementById('subscribe-modal');
    const panel = document.getElementById('modal-panel');

    function hideSubscribeGate() {
        modal.classList.remove('opacity-100');
        modal.classList.add('opacity-0', 'pointer-events-none');
        panel.classList.remove('scale-100');
        panel.classList.add('scale-95');
        document.body.style.overflow = '';
    }

    document.addEventListener('keydown', e => { if (e.key === 'Escape') hideSubscribeGate(); });

    // ── AJAX Search ───────────────────────────────────────────────
    (function () {
        const input      = document.getElementById('search-metadata');
        const container  = document.getElementById('metadata-container');
        const noResult   = document.getElementById('search-empty');
        const loading    = document.getElementById('search-loading');
        const hint       = document.getElementById('search-hint');
        const klasifikasi = input.dataset.klasifikasi ?? '';

        // Simpan HTML awal (server-side render) untuk reset
        const originalHTML = container.innerHTML;
        let debounceTimer  = null;
        let isFetching     = false;

        input.addEventListener('input', () => {
            clearTimeout(debounceTimer);

            const q = input.value.trim();

            // Kurang dari 2 karakter → kembalikan ke kondisi awal
            if (q.length < 2) {
                container.innerHTML = originalHTML;
                pagination.classList.remove('hidden');
                noResult.classList.add('hidden');
                return;
            }

            debounceTimer = setTimeout(() => fetchResults(q), 350);
        });

        async function fetchResults(q) {
            if (isFetching) return;
            isFetching = true;

            loading.classList.remove('hidden');
            hint.classList.add('hidden');
            pagination.classList.add('hidden');

            try {
                const params   = new URLSearchParams({ q, klasifikasi });
                const response = await fetch(`/search-metadata?${params}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });

                if (!response.ok) throw new Error(`HTTP ${response.status}`);

                const items = await response.json();
                renderResults(items);
            } catch (err) {
                console.error('Search error:', err);
            } finally {
                isFetching = false;
                loading.classList.add('hidden');
                hint.classList.remove('hidden');
            }
        }

        function renderResults(items) {
            container.innerHTML = '';

            if (items.length === 0) {
                noResult.classList.remove('hidden');
                return;
            }

            noResult.classList.add('hidden');

            const iconBar = `<svg class="w-5 h-5 text-stikom group-hover:text-stikom-blue transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>`;
            const iconTag  = `<svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>`;
            const iconClock = `<svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`;
            const iconCal  = `<svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>`;
            const iconLock = `<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>`;
            const iconArrow = `<svg class="w-4 h-4 text-gray-300 group-hover:text-stikom-blue group-hover:translate-x-0.5 transition-all duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>`;

            items.forEach(item => {
                const satuan    = item.satuan_data
                    ? `<span class="flex items-center gap-1">${iconTag}${esc(item.satuan_data)}</span>` : '';
                const frekuensi = item.frekuensi_penerbitan
                    ? `<span class="flex items-center gap-1">${iconClock}${esc(item.frekuensi_penerbitan)}</span>` : '';
                const tahun     = item.tahun_mulai_data
                    ? `<span class="flex items-center gap-1">${iconCal}Sejak ${esc(String(item.tahun_mulai_data))}</span>` : '';

                const card = document.createElement('div');
                card.className = [
                    'metadata-item group bg-white border border-gray-100 border-l-4',
                    'border-l-transparent shadow-sm hover:border-l-stikom-blue hover:shadow-md',
                    'transition-all duration-200 cursor-pointer overflow-hidden',
                ].join(' ');
                card.dataset.id   = item.metadata_id;
                card.dataset.nama = item.nama;
                card.onclick = () => {
                    window.location.href = `/statistik/${item.metadata_id}`;
                };

                card.innerHTML = `
                    <div class="flex items-start sm:items-center gap-4 px-5 py-4">
                        <div class="w-10 h-10 bg-stikom/5 group-hover:bg-stikom flex items-center justify-center shrink-0 transition-colors duration-200 mt-0.5 sm:mt-0">
                            ${iconBar}
                        </div>
                        <div class="flex-1 min-w-0">
                            <h2 class="text-sm font-bold text-gray-800 group-hover:text-stikom transition-colors duration-200 line-clamp-1 mb-1 font-body">
                                ${esc(item.nama)}
                            </h2>
                            <div class="flex flex-wrap gap-x-4 gap-y-1 text-[11px] text-gray-400 font-body">
                                ${satuan}${frekuensi}${tahun}
                            </div>
                        </div>
                    </div>`;

                container.appendChild(card);
            });
        }

        /** Escape HTML untuk mencegah XSS dari data server */
        function esc(str) {
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }
    })();
    </script>

</body>
</html>