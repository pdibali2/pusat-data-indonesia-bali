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

    @include('pages.landing.components.navbar')

    <main class="pb-20 min-h-screen">

        {{-- ═══ PAGE HEADER ════════════════════════════════════════════ --}}
        <div class="bg-stikom py-20 pb-14 relative overflow-hidden border-l-4 border-stikom-blue">
            <div class="absolute inset-0 opacity-[.06]" aria-hidden="true"
                 style="background-image:repeating-linear-gradient(0deg,rgba(255,255,255,.4) 0,rgba(255,255,255,.4) 1px,transparent 1px,transparent 40px),repeating-linear-gradient(90deg,rgba(255,255,255,.4) 0,rgba(255,255,255,.4) 1px,transparent 1px,transparent 40px)">
            </div>
            <div class="absolute inset-0 opacity-[.07]"
                 style="background-image:radial-gradient(circle,#3d6db1 1px,transparent 1px);background-size:24px 24px"
                 aria-hidden="true"></div>

            <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black font-poppins text-white mb-4 leading-tight">
                    Pilih Paket yang<br>
                    <span class="text-stikom-accent">Sesuai Kebutuhan</span>
                </h1>
            </div>
        </div>

        {{-- ═══ FLASH MESSAGES ═════════════════════════════════════════ --}}
        @if(session('error'))
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
                <div class="flex items-center gap-3 px-4 py-3 bg-red-50 border border-red-200 text-red-700 text-sm font-body">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ session('error') }}
                </div>
            </div>
        @endif

        {{-- ═══ LOGIN NOTICE ════════════════════════════════════════════ --}}
        @guest
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
                <div class="flex items-center gap-3 px-4 py-3
                            bg-stikom-blue/10 border border-stikom-blue/30 text-sm font-body">
                    <div class="w-1.5 h-1.5 bg-stikom-blue shrink-0"></div>
                    <span class="text-stikom-blue/80">
                        Kamu harus
                        <a href="{{ route('login') }}" class="font-bold text-stikom-blue underline">login</a>
                        terlebih dahulu untuk berlangganan.
                    </span>
                </div>
            </div>
        @endguest

        {{-- ═══ PRICING SECTION ════════════════════════════════════════ --}}
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-10">

            <div class="flex items-center gap-3 mb-10">
                <div class="w-0.5 h-7 bg-stikom-blue shrink-0"></div>
                <span class="text-[10px] font-bold text-stikom-blue uppercase tracking-[.12em] font-poppins">
                    Pilih Paket Berlangganan
                </span>
            </div>

            @if($layanans->isEmpty())
                <div class="text-center py-20 text-gray-400 font-body text-sm">
                    Belum ada paket langganan yang tersedia. Silakan cek kembali nanti.
                </div>
            @else

                {{-- items-stretch agar semua card sama tinggi --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-4 items-stretch">

                    @foreach($layanans as $layanan)

                        @php
                            $sudahAktif = auth()->check()
                                ? \App\Models\Transaksi::where('user_id', auth()->user()->user_id)
                                    ->where('layanan_id', $layanan->layanan_id)
                                    ->where('status', 'success')
                                    ->where(function ($q) { $q->whereNull('aktif_sampai')->orWhere('aktif_sampai', '>=', now()); })
                                    ->exists()
                                : false;
                        @endphp

                        @if($layanan->is_popular)
                        {{-- ── FEATURED CARD ─────────────────────────── --}}
                        <div class="relative flex flex-col overflow-hidden ring-2 ring-stikom-red shadow-2xl shadow-stikom-red/20 -my-3 z-10"
                             style="background: linear-gradient(170deg, #001020 0%, #001734 55%, #002348 100%);">

                            <div class="h-[3px] bg-stikom-red w-full shrink-0"></div>

                            <div class="bg-stikom-red px-4 py-2 flex items-center justify-between shrink-0">
                                <span class="flex items-center gap-1 text-[9px] font-black text-white uppercase tracking-widest font-poppins">
                                    <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                    Paling Populer
                                </span>
                                <span class="text-[8px] font-bold text-white/60 uppercase tracking-widest font-poppins">Terbaik</span>
                            </div>

                            <div class="flex flex-col flex-1 p-[14px] pt-4">

                                <div class="text-[9px] font-bold uppercase tracking-widest text-white/35 mb-2 font-poppins">
                                    {{ $layanan->durasi_label }}
                                </div>
                                <div class="text-xs font-bold text-white mb-1 font-poppins">{{ $layanan->nama_layanan }}</div>
                                <div class="text-xl font-black text-white font-poppins leading-tight">{{ $layanan->harga_format }}</div>
                                <div class="text-[10px] text-white/30 mt-0.5 mb-3 font-body">/ {{ strtolower($layanan->durasi_label) }}</div>

                                <div class="border-t border-white/[.07] mb-3"></div>

                                {{-- Features scrollable --}}
                                <ul class="flex-1 overflow-y-auto space-y-2 mb-10 pr-0.5"
                                    style="max-height: 140px; scrollbar-width: thin; scrollbar-color: rgba(255,255,255,.15) transparent;">
                                    @forelse($layanan->fiturs->sortBy('urutan') as $fitur)
                                        <li class="flex items-start gap-2 text-[11px] leading-snug
                                                   {{ $fitur->aktif ? 'text-white/75' : 'text-white/25 opacity-30' }}">
                                            @if($fitur->aktif)
                                                <div class="w-[14px] h-[14px] bg-stikom-blue flex items-center justify-center shrink-0 mt-px">
                                                    <svg class="w-[7px] h-[7px] text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3.5" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                </div>
                                            @else
                                                <div class="w-[14px] h-[14px] border border-white/15 flex items-center justify-center shrink-0 mt-px">
                                                    <svg class="w-[7px] h-[7px] text-white/25" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </div>
                                            @endif
                                            {{ $fitur->nama_fitur }}
                                        </li>
                                    @empty
                                        <li class="text-white/25 text-[10px] italic font-body">Fitur belum diatur.</li>
                                    @endforelse
                                </ul>

                                {{-- CTA --}}
                                @if($sudahAktif)
                                    <div class="w-full py-3 text-[10px] font-black text-center shrink-0
                                                bg-white/10 text-white/50 font-poppins cursor-default uppercase tracking-wide">
                                        ✓ Langganan Aktif
                                    </div>
                                @elseauth
                                    <form method="POST" action="{{ route('transaksi.checkout') }}">
                                        @csrf
                                        <input type="hidden" name="layanan_id" value="{{ $layanan->layanan_id }}">
                                        <button type="submit"
                                                class="w-full py-3 text-[10px] font-black text-stikom bg-stikom-accent
                                                       hover:bg-yellow-600 hover:text-white transition-colors duration-200 font-poppins
                                                       uppercase tracking-wide shrink-0">
                                            Berlangganan Sekarang
                                        </button>
                                    </form>
                                @else
                                    <a href="{{ route('login') }}?redirect={{ urlencode(route('langganan')) }}"
                                       class="block w-full py-3 text-[10px] font-black text-center text-stikom
                                              bg-stikom-accent hover:bg-yellow-600 hover:text-white transition-colors duration-200
                                              font-poppins uppercase tracking-wide shrink-0">
                                        <p class="px-2">Login untuk Berlangganan</p>
                                    </a>
                                @endauth

                            </div>
                        </div>

                        @else
                        {{-- ── REGULAR CARD ──────────────────────────── --}}
                        <div class="bg-white border border-gray-100 flex flex-col overflow-hidden shadow-sm
                                    transition-all duration-200
                                    hover:shadow-md"
                             style="border-top: 3px solid transparent;"
                             onmouseenter="this.style.borderTopColor='#A32D2D'"
                             onmouseleave="this.style.borderTopColor='transparent'">

                            <div class="px-4 py-1.5 border-b border-gray-100 shrink-0">
                                <span class="text-stikom-red text-[9px] font-bold uppercase tracking-widest font-poppins">Paket</span>
                            </div>

                            <div class="flex flex-col flex-1 p-[14px]">

                                <div class="text-[9px] font-bold uppercase tracking-widest text-gray-400 mb-2 font-poppins">
                                    {{ $layanan->durasi_label }}
                                </div>
                                <div class="text-xs font-bold text-stikom mb-1 font-poppins">{{ $layanan->nama_layanan }}</div>
                                <div class="text-lg font-black text-stikom font-poppins leading-tight">{{ $layanan->harga_format }}</div>
                                <div class="text-[10px] text-gray-400 mt-0.5 mb-3 font-body">/ {{ strtolower($layanan->durasi_label) }}</div>

                                <div class="border-t border-gray-100 mb-3"></div>

                                {{-- Features scrollable --}}
                                <ul class="flex-1 overflow-y-auto space-y-2 mb-10 pr-0.5"
                                    style="max-height: 140px; scrollbar-width: thin; scrollbar-color: rgba(0,0,0,.1) transparent;">
                                    @forelse($layanan->fiturs->sortBy('urutan') as $fitur)
                                        <li class="flex items-start gap-2 text-[11px] leading-snug
                                                   {{ $fitur->aktif ? 'text-gray-600' : 'text-gray-300 opacity-40' }}">
                                            @if($fitur->aktif)
                                                <div class="w-[14px] h-[14px] bg-stikom-blue flex items-center justify-center shrink-0 mt-px">
                                                    <svg class="w-[7px] h-[7px] text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3.5" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                </div>
                                            @else
                                                <div class="w-[14px] h-[14px] border border-gray-200 flex items-center justify-center shrink-0 mt-px">
                                                    <svg class="w-[7px] h-[7px] text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </div>
                                            @endif
                                            {{ $fitur->nama_fitur }}
                                        </li>
                                    @empty
                                        <li class="text-gray-300 text-[10px] italic font-body">Fitur belum diatur.</li>
                                    @endforelse
                                </ul>

                                {{-- CTA --}}
                                @if($sudahAktif)
                                    <div class="w-full py-3 text-[10px] font-black text-center shrink-0
                                                bg-gray-50 text-gray-400 font-poppins cursor-default
                                                border border-gray-200 uppercase tracking-wide">
                                        ✓ Langganan Aktif
                                    </div>
                                @elseauth
                                    <form method="POST" action="{{ route('transaksi.checkout') }}">
                                        @csrf
                                        <input type="hidden" name="layanan_id" value="{{ $layanan->layanan_id }}">
                                        <button type="submit"
                                                class="w-full py-3 text-[10px] font-black text-stikom shrink-0
                                                       bg-stikom-accent hover:bg-yellow-600 hover:text-white
                                                       transition-all duration-200 font-poppins uppercase tracking-wide">
                                            Pilih {{ $layanan->nama_layanan }}
                                        </button>
                                    </form>
                                @else
                                    <a href="{{ route('login') }}?redirect={{ urlencode(route('langganan')) }}"
                                       class="block w-full py-3 text-[10px] font-black text-center text-stikom shrink-0
                                              bg-stikom-accent hover:bg-yellow-600 hover:text-white
                                              transition-all duration-200 font-poppins uppercase tracking-wide">
                                        <p class="px-2">Login untuk Berlangganan</p>
                                    </a>
                                @endauth

                            </div>
                        </div>
                        @endif

                    @endforeach

                </div>
            @endif

        </div>
    </main>

    @include('pages.landing.components.footer')

    <button
        id="back-to-top"
        onclick="window.scrollTo({ top: 0, behavior: 'smooth' })"
        class="fixed bottom-6 right-6 z-50 w-12 h-12 bg-stikom-red text-white
               shadow-xl shadow-stikom-red/30 flex items-center justify-center
               opacity-0 translate-y-4 pointer-events-none
               transition-all duration-300 hover:bg-red-800 hover:scale-110
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

</body>
</html>