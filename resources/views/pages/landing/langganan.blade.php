<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Langganan Pusat Data Indonesia Bali</title>
    <meta name="description" content="Pilih paket berlangganan Pusat Data Indonesia Bali untuk akses penuh ke seluruh data dan fitur platform."/>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon"/>
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    @vite(['resources/css/app.css', 'resources/js/app.ts'])
</head>
<body class="bg-slate-50 text-gray-900 antialiased">

    @include('pages.components.navbar')

    <main class="pb-20 min-h-screen">

        {{-- Page header --}}
        <div class="bg-stikom py-24 pb-16 relative overflow-hidden">
            <div class="absolute inset-0 opacity-10" aria-hidden="true">
                <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
                    <defs><pattern id="hdr-grid" width="40" height="40" patternUnits="userSpaceOnUse">
                        <path d="M 40 0 L 0 0 0 40" fill="none" stroke="white" stroke-width="0.5"/>
                    </pattern></defs>
                    <rect width="100%" height="100%" fill="url(#hdr-grid)"/>
                </svg>
            </div>
            <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-stikom-accent/15 border border-stikom-accent/30 mb-6">
                    <div class="w-1.5 h-1.5 rounded-full bg-stikom-accent"></div>
                    <span class="text-stikom-accent text-xs font-bold uppercase tracking-wider">Paket Berlangganan</span>
                </div>
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black text-white mb-4">
                    Berlangganan Sekarang
                </h1>
                <p class="text-white/60 text-lg max-w-2xl mx-auto">
                    Akses penuh ke seluruh data, metadata, dan fitur ekspor data.
                </p>
            </div>
        </div>

        {{-- Pricing cards --}}
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">

            @php
            $plans = [
                [
                    'name'     => '1 Bulan',
                    'price'    => 'Rp50.000',
                    'per'      => 'per bulan',
                    'popular'  => true,
                    'features' => [
                        ['ok' => true,  'text' => 'Akses semua data'],
                        ['ok' => true,  'text' => 'Export data Excel dan PDF'],
                        ['ok' => true,  'text' => 'Template tampilan data'],
                    ],
                ],
                // [
                //     'name'     => '6 Bulan',
                //     'price'    => 'Rp300.000',
                //     'per'      => 'total (Rp50.000/bulan)',
                //     'popular'  => false,
                //     'features' => [
                //         ['ok' => true,  'text' => 'Semua fitur 1 Bulan'],
                //         ['ok' => true,  'text' => 'Template tampilan lebih banyak'],
                //         ['ok' => true,  'text' => 'Notifikasi data baru'],
                //         ['ok' => true,  'text' => 'Prioritas support'],
                //         ['ok' => false, 'text' => 'Akses API'],
                //         ['ok' => false, 'text' => 'Laporan kustom'],
                //         ['ok' => false, 'text' => 'Dedicated support'],
                //     ],
                // ],
                // [
                //     'name'     => '1 Tahun',
                //     'price'    => 'Rp600.000',
                //     'per'      => 'total (Rp50.000/bulan)',
                //     'popular'  => false,
                //     'features' => [
                //         ['ok' => true, 'text' => 'Semua fitur 6 Bulan'],
                //         ['ok' => true, 'text' => 'Akses API dasar'],
                //         ['ok' => true, 'text' => 'Laporan kustom'],
                //         ['ok' => true, 'text' => 'Dedicated support'],
                //         ['ok' => true, 'text' => 'Prioritas update fitur'],
                //         ['ok' => true, 'text' => 'Konsultasi data 1x/bulan'],
                //         ['ok' => true, 'text' => 'Badge "Subscriber Tahunan"'],
                //     ],
                // ],
            ];
            @endphp

            <div class="grid sm:grid-cols-3 gap-6 items-start">
                @foreach($plans as $i => $p)
                    <div class="relative col-2 rounded-2xl overflow-hidden transition-all duration-300 hover:-translate-y-1
                                {{ $p['popular'] ? 'shadow-2xl shadow-stikom/30 ' : 'shadow-md hover:shadow-xl bg-white' }}"
                        style="{{ $p['popular'] ? 'background: linear-gradient(160deg, #001734 0%, #002a52 100%);' : '' }}">

                        @if($p['popular'])
                            <div class="text-center py-2 bg-stikom-accent">
                                <span class="text-stikom text-xs font-black uppercase tracking-widest flex items-center justify-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                    Launching Price
                                </span>
                            </div>
                        @endif

                        <div class="p-8">
                            <div class="text-xs font-bold uppercase tracking-widest mb-3 {{ $p['popular'] ? 'text-stikom-accent/60' : 'text-gray-400' }}">
                                {{ $p['name'] }}
                            </div>
                            <div class="text-4xl font-black mb-1 {{ $p['popular'] ? 'text-white' : 'text-stikom' }}">
                                {{ $p['price'] }}
                            </div>
                            <div class="text-sm mb-8 {{ $p['popular'] ? 'text-white/40' : 'text-gray-400' }}">
                                {{ $p['per'] }}
                            </div>

                            <ul class="space-y-3 mb-8">
                                @foreach($p['features'] as $feat)
                                    <li class="flex items-start gap-3 text-sm {{ $p['popular'] ? 'text-white/80' : 'text-gray-600' }} {{ !$feat['ok'] ? 'opacity-40' : '' }}">
                                        <svg class="w-4 h-4 shrink-0 mt-0.5 {{ $feat['ok'] ? ($p['popular'] ? 'text-stikom-accent' : 'text-stikom') : 'text-gray-300' }}"
                                             fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            @if($feat['ok'])
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                            @else
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            @endif
                                        </svg>
                                        {{ $feat['text'] }}
                                    </li>
                                @endforeach
                            </ul>

                            <button
                                class="w-full py-3.5 rounded-xl text-sm font-black transition-all duration-200
                                       {{ $p['popular']
                                           ? 'bg-stikom-accent text-stikom hover:bg-yellow-400'
                                           : 'bg-stikom text-white hover:bg-[#002a52]' }}"
                                onclick="alert('Fitur pembayaran akan segera hadir!')"
                            >
                                Pilih Paket {{ $p['name'] }}
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
                
            </div>

        </div>
    </main>

    @include('pages.components.footer')

    <button id="back-to-top"
            onclick="window.scrollTo({ top: 0, behavior: 'smooth' })"
            class="fixed bottom-6 right-6 z-50 w-12 h-12 rounded-2xl bg-stikom text-stikom-accent shadow-xl flex items-center justify-center opacity-0 translate-y-4 pointer-events-none transition-all duration-300 hover:bg-[#002a52] hover:scale-110"
            aria-label="Kembali ke atas">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
        </svg>
    </button>
    <script>
    (function () {
        const btn = document.getElementById('back-to-top');
        window.addEventListener('scroll', () => {
            btn.classList.toggle('opacity-0',           window.scrollY <= 300);
            btn.classList.toggle('translate-y-4',       window.scrollY <= 300);
            btn.classList.toggle('pointer-events-none', window.scrollY <= 300);
            btn.classList.toggle('opacity-100',         window.scrollY > 300);
            btn.classList.toggle('translate-y-0',       window.scrollY > 300);
        }, { passive: true });
    })();
    
    </script>
</body>
</html>