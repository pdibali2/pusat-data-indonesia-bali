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
        <div class="bg-stikom py-20 pb-14 relative overflow-hidden border-l-4 border-stikom-core">
            <div class="absolute inset-0 opacity-[.06]" aria-hidden="true"
                 style="background-image:repeating-linear-gradient(0deg,rgba(255,255,255,.4) 0,rgba(255,255,255,.4) 1px,transparent 1px,transparent 40px),repeating-linear-gradient(90deg,rgba(255,255,255,.4) 0,rgba(255,255,255,.4) 1px,transparent 1px,transparent 40px)">
            </div>
            <div class="absolute inset-0 opacity-[.07]"
                 style="background-image:radial-gradient(circle,#3DB166 1px,transparent 1px);background-size:24px 24px"
                 aria-hidden="true"></div>

            <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                {{-- <div class="inline-flex items-center gap-2 px-3 py-1.5 mb-6
                            bg-stikom-core/15 border border-stikom-core/40">
                    <div class="w-1.5 h-1.5 bg-stikom-core"></div>
                    <span class="text-stikom-core text-[10px] font-bold uppercase tracking-[.12em] font-poppins">
                        Paket Berlangganan
                    </span>
                </div> --}}
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black font-poppins text-white mb-4 leading-tight">
                    Pilih Paket yang<br>
                    <span class="text-stikom-core">Sesuai Kebutuhan</span>
                </h1>
                <p class="text-white/50 text-base max-w-xl mx-auto font-body">
                    Akses penuh ke seluruh data, metadata, dan fitur ekspor data dengan harga terjangkau.
                </p>
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

        {{-- ═══ LOGIN NOTICE (jika belum login) ════════════════════════ --}}
        @guest
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
            <div class="flex items-center gap-3 px-4 py-3
                        bg-stikom-core/10 border border-stikom-core/30 text-sm font-body">
                <div class="w-1.5 h-1.5 bg-stikom-core shrink-0"></div>
                <span class="text-stikom-core/80">
                    Kamu harus
                    <a href="{{ route('login') }}" class="font-bold text-stikom-core underline">login</a>
                    terlebih dahulu untuk berlangganan.
                </span>
            </div>
        </div>
        @endguest

        {{-- ═══ PRICING SECTION ════════════════════════════════════════ --}}
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 mt-10">

            <div class="flex items-center gap-3 mb-8">
                <div class="w-0.5 h-7 bg-stikom-core shrink-0"></div>
                <span class="text-[10px] font-bold text-stikom-core uppercase tracking-[.12em] font-display">
                    Harga & Fitur
                </span>
            </div>

            @if($layanans->isEmpty())
            {{-- Belum ada layanan aktif --}}
            <div class="text-center py-20 text-gray-400 font-body text-sm">
                Belum ada paket langganan yang tersedia. Silakan cek kembali nanti.
            </div>
            @else

            <div class="grid sm:grid-cols-{{ min($layanans->count(), 3) }} gap-4 items-start">

                @foreach($layanans as $layanan)

                {{-- Cek apakah user punya langganan aktif untuk layanan ini --}}
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
                {{-- ── FEATURED CARD ─────────────────────────────────────── --}}
                <div class="relative overflow-hidden shadow-2xl shadow-stikom/40 order-first sm:order-none"
                     style="background:linear-gradient(160deg,#001020 0%,#001734 55%,#002a52 100%)">

                    <div class="bg-stikom-core px-4 py-2 flex items-center justify-between">
                        <span class="flex items-center gap-1.5 text-[10px] font-black text-stikom uppercase tracking-widest font-display">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                            Paling Populer
                        </span>
                        <span class="text-[9px] font-black text-stikom uppercase tracking-widest font-display">Terbaik</span>
                    </div>

                    <div class="p-7">
                        <div class="text-[10px] font-bold uppercase tracking-widest text-stikom-core/60 mb-3 font-display">
                            {{ $layanan->durasi_label }}
                        </div>
                        <div class="text-xl font-black text-stikom-core mb-1 font-display">{{ $layanan->nama_layanan }}</div>
                        <div class="text-4xl font-black text-white mb-1 font-display">{{ $layanan->harga_format }}</div>
                        <div class="text-[11px] text-white/35 mb-7 font-body">/ {{ strtolower($layanan->durasi_label) }}</div>

                        <ul class="space-y-3 mb-7">
                            @forelse($layanan->fiturs as $fitur)
                                <li class="flex items-start gap-3 text-sm font-body
                                           {{ $fitur->aktif ? 'text-white/80' : 'text-white/25' }}">
                                    @if($fitur->aktif)
                                        <div class="w-4 h-4 bg-stikom-core flex items-center justify-center shrink-0 mt-0.5">
                                            <svg class="w-2.5 h-2.5" fill="none" stroke="white" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </div>
                                    @else
                                        <div class="w-4 h-4 border border-white/15 flex items-center justify-center shrink-0 mt-0.5">
                                            <svg class="w-2.5 h-2.5 text-white/25" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </div>
                                    @endif
                                    {{ $fitur->nama_fitur }}
                                </li>
                            @empty
                                <li class="text-white/40 text-xs font-body italic">Fitur belum diatur.</li>
                            @endforelse
                        </ul>

                        @if($sudahAktif)
                            {{-- Sudah berlangganan --}}
                            <div class="w-full py-3.5 text-sm font-black text-center
                                        bg-white/10 text-white/50 font-display cursor-default">
                                ✓ Langganan Aktif
                            </div>
                        @elseauth
                            {{-- Login & belum berlangganan → tombol checkout --}}
                            <form method="POST" action="{{ route('transaksi.checkout') }}">
                                @csrf
                                <input type="hidden" name="layanan_id" value="{{ $layanan->layanan_id }}">
                                <button type="submit"
                                        class="w-full py-3.5 text-sm font-black text-stikom bg-stikom-core
                                               hover:bg-green-400 transition-colors duration-200 font-display">
                                    Berlangganan Sekarang →
                                </button>
                            </form>
                        @else
                            {{-- Belum login --}}
                            <a href="{{ route('login') }}?redirect={{ urlencode(route('langganan')) }}"
                               class="block w-full py-3.5 text-sm font-black text-center text-stikom
                                      bg-stikom-core hover:bg-green-400 transition-colors duration-200 font-display">
                                Login untuk Berlangganan →
                            </a>
                        @endauth
                    </div>
                </div>

                @else
                {{-- ── REGULAR CARD ──────────────────────────────────────── --}}
                <div class="bg-white border border-gray-100 border-t-4 border-t-transparent
                            shadow-sm hover:border-t-stikom-core hover:shadow-md
                            transition-all duration-200 overflow-hidden">

                    <div class="p-7">
                        <div class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-3 font-display">
                            {{ $layanan->durasi_label }}
                        </div>
                        <div class="text-base font-black text-stikom mb-1 font-display">{{ $layanan->nama_layanan }}</div>
                        <div class="text-3xl font-black text-stikom mb-1 font-display">{{ $layanan->harga_format }}</div>
                        <div class="text-[11px] text-gray-400 mb-7 font-body">/ {{ strtolower($layanan->durasi_label) }}</div>

                        <ul class="space-y-3 mb-7">
                            @forelse($layanan->fiturs as $fitur)
                                <li class="flex items-start gap-3 text-sm font-body
                                           {{ $fitur->aktif ? 'text-gray-600' : 'text-gray-300' }}">
                                    @if($fitur->aktif)
                                        <div class="w-4 h-4 bg-stikom-core flex items-center justify-center shrink-0 mt-0.5">
                                            <svg class="w-2.5 h-2.5" fill="none" stroke="white" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </div>
                                    @else
                                        <div class="w-4 h-4 border border-gray-200 flex items-center justify-center shrink-0 mt-0.5">
                                            <svg class="w-2.5 h-2.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </div>
                                    @endif
                                    {{ $fitur->nama_fitur }}
                                </li>
                            @empty
                                <li class="text-gray-300 text-xs font-body italic">Fitur belum diatur.</li>
                            @endforelse
                        </ul>

                        @if($sudahAktif)
                            <div class="w-full py-3.5 text-sm font-black text-center
                                        bg-gray-50 text-gray-400 font-display cursor-default border border-gray-200">
                                ✓ Langganan Aktif
                            </div>
                        @elseauth
                            <form method="POST" action="{{ route('transaksi.checkout') }}">
                                @csrf
                                <input type="hidden" name="layanan_id" value="{{ $layanan->layanan_id }}">
                                <button type="submit"
                                        class="w-full py-3.5 text-sm font-black text-white bg-stikom
                                               hover:bg-stikom-core transition-colors duration-200 font-display">
                                    Pilih {{ $layanan->nama_layanan }} →
                                </button>
                            </form>
                        @else
                            <a href="{{ route('login') }}?redirect={{ urlencode(route('langganan')) }}"
                               class="block w-full py-3.5 text-sm font-black text-center text-white
                                      bg-stikom hover:bg-stikom-core transition-colors duration-200 font-display">
                                Login untuk Berlangganan →
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

    <button id="back-to-top"
            onclick="window.scrollTo({ top: 0, behavior: 'smooth' })"
            class="fixed bottom-6 right-6 z-50 w-12 h-12 bg-stikom text-stikom-core shadow-xl
                   flex items-center justify-center opacity-0 translate-y-4 pointer-events-none
                   transition-all duration-300 hover:bg-stikom-core hover:text-white hover:scale-110"
            aria-label="Kembali ke atas">
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