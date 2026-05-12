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

    @include('pages.components.navbar')

    <main class="pb-20 min-h-screen">

        {{-- Page Header --}}
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
            <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-stikom-accent/15 border border-stikom-accent/30 mb-6">
                    <div class="w-1.5 h-1.5 rounded-full bg-stikom-accent"></div>
                    <span class="text-stikom-accent text-xs font-bold uppercase tracking-wider font-display">Semua Klasifikasi</span>
                </div>
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black text-white mb-4 font-display">
                    Jelajahi Data Berdasarkan Kategori
                </h1>
                <p class="text-white/60 text-lg max-w-2xl mx-auto">
                    Pilih kategori untuk melihat daftar metadata yang tersedia di dalam sistem.
                </p>
            </div>
        </div>

        {{-- Search bar --}}
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 -mt-6 relative z-10 mb-10">
            <div class="bg-white rounded-2xl shadow-lg shadow-stikom/10 px-5 py-4 flex items-center gap-3">
                <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                </svg>
                <input
                    id="search-klasifikasi"
                    type="search"
                    placeholder="Cari klasifikasi..."
                    class="flex-1 bg-transparent text-sm text-gray-700 placeholder-gray-400 outline-none"
                    autocomplete="off"
                />
            </div>
        </div>

        {{-- Grid klasifikasi --}}
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div id="klasifikasi-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($klasifikasiList as $item)
                    <a
                        href="{{ route('klasifikasi.show', $item['slug']) }}"
                        data-name="{{ strtolower($item['nama']) }}"
                        class="klasifikasi-card group flex items-center justify-between bg-white rounded-2xl px-5 py-4
                               border border-gray-100 shadow-sm
                               hover:border-stikom/30 hover:shadow-md hover:-translate-y-0.5
                               transition-all duration-200"
                    >
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 rounded-xl bg-stikom/5 group-hover:bg-stikom/10 flex items-center justify-center shrink-0 transition-colors">
                                <svg class="w-4 h-4 text-stikom" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                            </div>
                            <span class="text-sm font-semibold text-gray-800 group-hover:text-stikom truncate transition-colors">
                                {{ $item['nama'] }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2 shrink-0 ml-3">
                            @if($item['total'] > 0)
                                <span class="text-xs font-bold text-stikom bg-stikom/8 px-2.5 py-1 rounded-full">
                                    {{ $item['total'] }}
                                </span>
                            @else
                                <span class="text-xs text-gray-300 bg-gray-50 px-2.5 py-1 rounded-full font-medium">0</span>
                            @endif
                            <svg class="w-4 h-4 text-gray-300 group-hover:text-stikom group-hover:translate-x-0.5 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </a>
                @endforeach
            </div>

            {{-- Empty state --}}
            <div id="empty-state" class="hidden text-center py-16">
                <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <p class="text-gray-500 text-sm">Klasifikasi tidak ditemukan.</p>
            </div>
        </div>

    </main>

    @include('pages.components.footer')

    <script>
    // Filter klasifikasi by search
    (function () {
        const input  = document.getElementById('search-klasifikasi');
        const cards  = document.querySelectorAll('.klasifikasi-card');
        const empty  = document.getElementById('empty-state');

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