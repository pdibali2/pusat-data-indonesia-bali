<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>{{ $nama }} — Pusat Data Indonesia Bali</title>
    <meta name="description" content="Daftar metadata kategori {{ $nama }} di Pusat Data Indonesia Bali."/>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon"/>
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    @vite(['resources/css/app.css', 'resources/js/app.ts'])
</head>
<body class="bg-slate-50 text-gray-900 antialiased">

    @include('pages.components.navbar')

    <main class="pb-20 min-h-screen">

        {{-- Breadcrumb + Header --}}
        <div class="bg-stikom py-24 pb-16 relative overflow-hidden">
            <div class="absolute inset-0 opacity-10" aria-hidden="true">
                <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <pattern id="hdr-grid" width="40" height="40" patternUnits="userSpaceOnUse">
                            <path d="M 40 0 L 0 0 0 40" fill="none" stroke="white" stroke-width="0.5"/>
                        </pattern>
                    </defs>
                    <rect width="100%" height="100%" fill="url(#hdr-grid)"/>
                </svg>
            </div>
            <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                {{-- Breadcrumb --}}
                <nav class="flex items-center gap-2 text-xs text-white/40 mb-6" aria-label="Breadcrumb">
                    <a href="{{ route('landing') }}" class="hover:text-white/70 transition-colors">Beranda</a>
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                    <a href="{{ route('klasifikasi.index') }}" class="hover:text-white/70 transition-colors">Klasifikasi</a>
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                    <span class="text-white/70 truncate max-w-200">{{ $nama }}</span>
                </nav>

                <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
                    <div>
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-stikom-accent/15 border border-stikom-accent/30 mb-4">
                            <div class="w-1.5 h-1.5 rounded-full bg-stikom-accent"></div>
                            <span class="text-stikom-accent text-xs font-bold uppercase tracking-wider">Klasifikasi</span>
                        </div>
                        <h1 class="text-2xl sm:text-3xl lg:text-4xl font-black text-white font-display">{{ $nama }}</h1>
                    </div>
                    <div class="shrink-0 bg-white/10 rounded-2xl px-5 py-3 text-center">
                        <div class="text-2xl font-black text-stikom-accent font-display">{{ $metadataList->total() }}</div>
                        <div class="text-xs text-white/50 mt-0.5">Total Metadata</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Konten --}}
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 mt-8">

            @if($metadataList->isEmpty())
                <div class="text-center py-20">
                    <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <p class="text-gray-500 text-sm font-medium">Belum ada metadata untuk klasifikasi ini.</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($metadataList as $meta)
                        {{-- Setiap item bisa diklik tapi memunculkan modal subscription --}}
                        <div
                            class="metadata-item group bg-white rounded-2xl border border-gray-100 shadow-sm
                                   hover:border-stikom/20 hover:shadow-md
                                   transition-all duration-200 cursor-pointer overflow-hidden"
                            data-id="{{ $meta->metadata_id }}"
                            data-nama="{{ $meta->nama }}"
                            onclick="showSubscribeGate()"
                        >
                            <div class="flex items-start sm:items-center gap-4 px-5 py-4">
                                {{-- Icon --}}
                                <div class="w-10 h-10 rounded-xl bg-stikom/5 group-hover:bg-stikom/10 flex items-center justify-center shrink-0 transition-colors mt-0.5 sm:mt-0">
                                    <svg class="w-5 h-5 text-stikom" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                    </svg>
                                </div>

                                {{-- Info --}}
                                <div class="flex-1 min-w-0">
                                    <div class="flex flex-wrap items-center gap-2 mb-1">
                                        <h2 class="text-sm font-bold text-gray-800 group-hover:text-stikom transition-colors line-clamp-1">
                                            {{ $meta->nama }}
                                        </h2>
                                    </div>
                                    <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-400">
                                        @if($meta->satuan_data)
                                            <span class="flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                                {{ $meta->satuan_data }}
                                            </span>
                                        @endif
                                        @if($meta->frekuensi_penerbitan)
                                            <span class="flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                {{ $meta->frekuensi_penerbitan }}
                                            </span>
                                        @endif
                                        @if($meta->tahun_mulai_data)
                                            <span class="flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                Sejak {{ $meta->tahun_mulai_data }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Lock icon --}}
                                <div class="shrink-0 flex items-center gap-2">
                                    <div class="hidden sm:flex items-center gap-1.5 text-xs text-amber-600 bg-amber-50 border border-amber-100 px-2.5 py-1.5 rounded-lg font-semibold">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                        </svg>
                                        Berlangganan
                                    </div>
                                    <svg class="w-4 h-4 text-gray-300 group-hover:text-stikom group-hover:translate-x-0.5 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Pagination --}}
                @if($metadataList->hasPages())
                    <div class="mt-8 flex justify-center">
                        <div class="flex items-center gap-1">
                            {{-- Previous --}}
                            @if($metadataList->onFirstPage())
                                <span class="px-3 py-2 rounded-xl text-sm text-gray-300 cursor-not-allowed">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                </span>
                            @else
                                <a href="{{ $metadataList->previousPageUrl() }}" class="px-3 py-2 rounded-xl text-sm text-gray-600 hover:bg-white hover:shadow-sm transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                </a>
                            @endif

                            {{-- Page numbers --}}
                            @foreach($metadataList->getUrlRange(max(1, $metadataList->currentPage()-2), min($metadataList->lastPage(), $metadataList->currentPage()+2)) as $page => $url)
                                @if($page == $metadataList->currentPage())
                                    <span class="px-3.5 py-2 rounded-xl text-sm font-bold bg-stikom text-white">{{ $page }}</span>
                                @else
                                    <a href="{{ $url }}" class="px-3.5 py-2 rounded-xl text-sm text-gray-600 hover:bg-white hover:shadow-sm transition-all">{{ $page }}</a>
                                @endif
                            @endforeach

                            {{-- Next --}}
                            @if($metadataList->hasMorePages())
                                <a href="{{ $metadataList->nextPageUrl() }}" class="px-3 py-2 rounded-xl text-sm text-gray-600 hover:bg-white hover:shadow-sm transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            @else
                                <span class="px-3 py-2 rounded-xl text-sm text-gray-300 cursor-not-allowed">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </span>
                            @endif
                        </div>
                    </div>
                @endif
            @endif

        </div>
    </main>

    @include('pages.components.footer')

    {{-- ═══════════════════════════════════════════════════════════════
         MODAL: Subscription Gate
         Muncul saat pengguna klik salah satu metadata
    ════════════════════════════════════════════════════════════════ --}}
    <div
        id="subscribe-modal"
        class="fixed inset-0 z-200 flex items-center justify-center p-4
               opacity-0 pointer-events-none transition-opacity duration-200"
        role="dialog" aria-modal="true" aria-labelledby="modal-title"
    >
        {{-- Backdrop --}}
        <div
            id="modal-backdrop"
            class="absolute inset-0 bg-black/50 backdrop-blur-sm"
            onclick="hideSubscribeGate()"
        ></div>

        {{-- Panel --}}
        <div class="relative z-10 w-full max-w-md bg-white rounded-3xl shadow-2xl overflow-hidden
                    scale-95 transition-transform duration-200" id="modal-panel">

            {{-- Top accent bar --}}
            <div class="h-1.5 bg-linear-to-r from-stikom via-[#003a6b] to-stikom-accent"></div>

            <div class="px-8 py-8">
                {{-- Lock illustration --}}
                <div class="w-16 h-16 rounded-2xl bg-amber-50 border border-amber-100 flex items-center justify-center mx-auto mb-6">
                    <svg class="w-8 h-8 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>

                <h2 id="modal-title" class="text-xl font-black text-stikom text-center mb-2 font-display">
                    Akses Terbatas
                </h2>
                <p class="text-gray-500 text-sm text-center mb-7 leading-relaxed">
                    Untuk melihat detail data dan mengunduh dataset ini, kamu perlu memiliki paket berlangganan aktif.
                </p>

                {{-- Benefit list --}}
                <ul class="space-y-2.5 mb-8">
                    @php
                    $benefits = [
                        'Akses semua data',
                        'Ekspor data Excel dan PDF',
                        'Template tampilan data',
                    ];
                    @endphp
                    @foreach($benefits as $b)
                        <li class="flex items-start gap-3 text-sm text-gray-600">
                            <svg class="w-4 h-4 text-stikom shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                            {{ $b }}
                        </li>
                    @endforeach
                </ul>

                {{-- CTAs --}}
                <div class="flex flex-col gap-3">
                    <a
                        href="{{ route('langganan') }}"
                        class="w-full py-3.5 rounded-xl text-sm font-black text-center
                               bg-stikom text-white hover:bg-[#002a52]
                               transition-colors duration-200"
                    >
                        Lihat Paket Berlangganan
                    </a>
                    <button
                        onclick="hideSubscribeGate()"
                        class="w-full py-3 rounded-xl text-sm font-semibold text-gray-500
                               hover:text-gray-700 hover:bg-gray-50
                               transition-colors duration-200"
                    >
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Back to Top --}}
    <button id="back-to-top"
            onclick="window.scrollTo({ top: 0, behavior: 'smooth' })"
            class="fixed bottom-6 right-6 z-50 w-12 h-12 rounded-2xl bg-stikom text-stikom-accent shadow-xl
                   flex items-center justify-center opacity-0 translate-y-4 pointer-events-none
                   transition-all duration-300 hover:bg-[#002a52] hover:scale-110"
            aria-label="Kembali ke atas">
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
            btn.classList.toggle('opacity-0',           !past);
            btn.classList.toggle('translate-y-4',       !past);
            btn.classList.toggle('pointer-events-none', !past);
            btn.classList.toggle('opacity-100',         past);
            btn.classList.toggle('translate-y-0',       past);
        }, { passive: true });
    })();

    // ── Modal helpers ─────────────────────────────────────────────
    const modal   = document.getElementById('subscribe-modal');
    const panel   = document.getElementById('modal-panel');

    function showSubscribeGate() {
        modal.classList.remove('opacity-0', 'pointer-events-none');
        modal.classList.add('opacity-100');
        panel.classList.remove('scale-95');
        panel.classList.add('scale-100');
        document.body.style.overflow = 'hidden';
    }

    function hideSubscribeGate() {
        modal.classList.remove('opacity-100');
        modal.classList.add('opacity-0', 'pointer-events-none');
        panel.classList.remove('scale-100');
        panel.classList.add('scale-95');
        document.body.style.overflow = '';
    }

    // Close on Escape key
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') hideSubscribeGate();
    });
    </script>

</body>
</html>