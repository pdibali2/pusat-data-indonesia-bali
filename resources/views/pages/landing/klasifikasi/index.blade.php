<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Klasifikasi Data — Pusat Data Indonesia Bali</title>
    <meta name="description" content="Jelajahi semua kategori data yang tersedia di Pusat Data Indonesia Bali."/>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon"/>
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    @vite(['resources/css/app.css', 'resources/js/app.ts'])
</head>
<body class="bg-slate-50 text-gray-900 antialiased">

    @include('pages.landing.components.navbar')

    <main class="pb-20 min-h-screen">

        {{-- ═══ PAGE HEADER ════════════════════════════════════════════ --}}
        <div class="bg-stikom py-20 pb-14 relative overflow-hidden border-l-4 border-stikom-core">
            {{-- Grid pattern --}}
            <div class="absolute inset-0 opacity-[.06]" aria-hidden="true"
                 style="background-image:repeating-linear-gradient(0deg,rgba(255,255,255,.4) 0,rgba(255,255,255,.4) 1px,transparent 1px,transparent 40px),repeating-linear-gradient(90deg,rgba(255,255,255,.4) 0,rgba(255,255,255,.4) 1px,transparent 1px,transparent 40px)">
            </div>
            {{-- Dot accent --}}
            <div class="absolute inset-0 opacity-[.07]"
                 style="background-image:radial-gradient(circle,#3DB166 1px,transparent 1px);background-size:24px 24px"
                 aria-hidden="true"></div>

            <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                {{-- Badge --}}
                <div class="inline-flex items-center gap-2 px-3 py-1.5 mb-6
                            bg-stikom-core/15 border border-stikom-core/40">
                    <div class="w-1.5 h-1.5 bg-stikom-core"></div>
                    <span class="text-stikom-core text-[10px] font-bold uppercase tracking-[.12em] font-display">
                        Semua Klasifikasi
                    </span>
                </div>

                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black text-white mb-4 font-display leading-tight">
                    Jelajahi Data<br>
                    <span class="text-stikom-core">Berdasarkan Kategori</span>
                </h1>
                <p class="text-white/50 text-base max-w-xl mx-auto">
                    Pilih kategori untuk melihat daftar metadata yang tersedia di dalam sistem.
                </p>
            </div>
        </div>

        {{-- ═══ SEARCH BAR ════════════════════════════════════════════ --}}
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 -mt-5 relative z-10 mb-10">
            <div class="bg-white border-l-4 border-stikom-core shadow-xl shadow-stikom/10 flex items-center gap-3 px-5 py-4">
                <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                </svg>
                <input
                    id="search-klasifikasi"
                    type="search"
                    placeholder="Cari klasifikasi..."
                    class="flex-1 bg-transparent text-sm text-gray-700 placeholder-gray-400 outline-none font-body"
                    autocomplete="off"
                />
                <span class="text-[10px] font-bold text-gray-300 uppercase tracking-widest hidden sm:block">ENTER ↵</span>
            </div>
        </div>

        {{-- ═══ KLASIFIKASI GRID ══════════════════════════════════════ --}}
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Section label --}}
            <div class="flex items-center gap-3 mb-6">
                <div class="w-0.5 h-7 bg-stikom-core shrink-0"></div>
                <span class="text-[10px] font-bold text-stikom-core uppercase tracking-[.12em] font-display">
                    Daftar Klasifikasi
                </span>
            </div>

            <div id="klasifikasi-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($klasifikasiList as $item)
                    <a
                        href="{{ route('klasifikasi.show', $item['slug']) }}"
                        data-name="{{ strtolower($item['nama']) }}"
                        class="klasifikasi-card group flex items-center justify-between
                               bg-white border border-gray-100 border-l-4 border-l-transparent
                               px-5 py-4 shadow-sm
                               hover:border-l-stikom-core hover:shadow-md hover:-translate-y-0.5
                               transition-all duration-200"
                    >
                        <div class="flex items-center gap-3 min-w-0">
                            {{-- Icon box --}}
                            <div class="w-9 h-9 bg-stikom/5 group-hover:bg-stikom flex items-center justify-center shrink-0 transition-colors duration-200">
                                <svg class="w-4 h-4 text-stikom group-hover:text-stikom-core transition-colors duration-200"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                            </div>
                            <span class="text-sm font-semibold text-gray-800 group-hover:text-stikom truncate transition-colors duration-200 font-body">
                                {{ $item['nama'] }}
                            </span>
                        </div>

                        <div class="flex items-center gap-2 shrink-0 ml-3">
                            @if($item['total'] > 0)
                                <span class="text-[11px] font-black text-stikom-core bg-stikom-core/10
                                             border border-stikom-core/20 px-2.5 py-1 font-display">
                                    {{ $item['total'] }}
                                </span>
                            @else
                                <span class="text-[11px] text-gray-300 bg-gray-50 px-2.5 py-1 font-medium">0</span>
                            @endif
                            <svg class="w-4 h-4 text-gray-300 group-hover:text-stikom-core group-hover:translate-x-0.5 transition-all duration-200"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </a>
                @endforeach
            </div>

            {{-- Empty state --}}
            <div id="empty-state" class="hidden text-center py-20">
                <div class="w-14 h-14 bg-gray-100 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <p class="text-gray-500 text-sm font-body">Klasifikasi tidak ditemukan.</p>
            </div>
        </div>

    </main>

    @include('pages.landing.components.footer')

    <script>
    (function () {
        const input = document.getElementById('search-klasifikasi');
        const cards = document.querySelectorAll('.klasifikasi-card');
        const empty = document.getElementById('empty-state');

        input.addEventListener('input', () => {
            const q = input.value.toLowerCase().trim();
            let visible = 0;
            cards.forEach(card => {
                const match = card.dataset.name.includes(q);
                card.style.display = match ? '' : 'none';
                if (match) visible++;
            });
            empty.classList.toggle('hidden', visible > 0);
        });
    })();
    </script>

</body>
</html>