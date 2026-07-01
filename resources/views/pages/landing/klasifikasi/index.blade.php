@php
$iconSvg = function (?string $key) {
    $svgMap = config('klasifikasi_icons.svg', []);
    $default = config('klasifikasi_icons.default', 'tag');
    $iconPath = $svgMap[$key] ?? $svgMap[$default] ?? '';
    return '<svg class="w-5 h-5 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">' . $iconPath . '</svg>';
};
@endphp

<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Klasifikasi</title>
    <meta name="description" content="Jelajahi semua kategori data yang tersedia di Pusat Data Indonesia Bali."/>
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
            {{-- Dot accent --}}
            <div class="absolute inset-0 opacity-[.07]"
                 style="background-image:radial-gradient(circle,#3d6db1 1px,transparent 1px);background-size:24px 24px"
                 aria-hidden="true"></div>

            <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                

                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black font-poppins text-white mb-4 leading-tight">
                    Jelajahi Data<br>
                    <span class="text-stikom-accent">Berdasarkan Klasifikasi</span>
                </h1>
                <p class="text-white/50 text-base max-w-xl mx-auto">
                    Pilih klasifikasi untuk melihat data yang tersedia.
                </p>
            </div>
        </div>

        {{-- ═══ SEARCH BAR ════════════════════════════════════════════ --}}
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 -mt-5 relative z-10 mb-10">
            <div class="bg-white border-l-4 border-stikom-red shadow-xl shadow-stikom/10 flex items-center gap-3 px-5 py-4">
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
                <div class="w-0.5 h-7 bg-stikom-blue shrink-0"></div>
                <span class="text-[10px] font-bold text-stikom-blue uppercase tracking-[.12em] font-display">
                    Daftar Klasifikasi
                </span>
            </div>

            <div id="klasifikasi-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                @forelse($klasifikasiList as $item)
                    <a
                        href="{{ route('klasifikasi.show', $item['slug']) }}"
                        data-name="{{ strtolower($item['nama']) }}"
                        class="klasifikasi-card group flex items-center justify-between
                               bg-white border border-gray-100 border-l-4 border-l-transparent
                               px-5 py-4 shadow-sm
                               hover:border-l-stikom-blue hover:shadow-md hover:-translate-y-0.5
                               transition-all duration-200"
                    >
                        <div class="flex items-center gap-3 min-w-0">
                            {{-- Icon box --}}
                            <div class="w-9 h-9 bg-stikom/5 group-hover:bg-stikom flex items-center justify-center shrink-0 transition-colors duration-200">
                                {!! $iconSvg($item['icon'] ?? null) !!}
                            </div>
                            <span class="text-sm font-semibold text-gray-800 group-hover:text-stikom truncate transition-colors duration-200 font-body">
                                {{ $item['nama'] }}
                            </span>
                        </div>

                        <div class="flex items-center gap-2 shrink-0 ml-3">
                            {{-- @if($item['total'] > 0)
                                <span class="text-[11px] font-black text-stikom-blue bg-stikom-blue/10
                                             border border-stikom-blue/20 px-2.5 py-1 font-display">
                                    {{ $item['total'] }}
                                </span>
                            @else
                                <span class="text-[11px] text-gray-300 bg-gray-50 px-2.5 py-1 font-medium">0</span>
                            @endif --}}
                            <svg class="w-4 h-4 text-gray-300 group-hover:text-stikom-blue group-hover:translate-x-0.5 transition-all duration-200"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </a>
                    @empty
                    <div class="col-span-3 text-center py-20">
                        <p class="text-gray-300 text-xl font-bold">Klasifikasi tidak tersedia.</p>
                    </div>
                @endforelse
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
    </script>

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