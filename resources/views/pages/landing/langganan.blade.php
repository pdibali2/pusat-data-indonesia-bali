<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Langganan</title>
    <meta name="description" content="Pilih paket berlangganan Pusat Data Indonesia Bali untuk akses penuh ke seluruh data dan fitur platform."/>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon"/>
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    @vite(['resources/css/app.css', 'resources/js/app.ts'])
</head>
<body class="bg-slate-50 text-gray-900 antialiased">

    @include('pages.landing.components.navbar')

    <main class="pb-20 min-h-screen">

        {{-- ═══ PAGE HEADER ════════════════════════════════════════════ --}}
        <div class="bg-stikom py-30 pb-14 relative overflow-hidden border-l-4 border-stikom-blue">
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

            <div class="flex items-center gap-3 mb-8">
                <div class="w-0.5 h-7 bg-stikom-blue shrink-0"></div>
                <span class="text-[10px] font-bold text-stikom-blue uppercase tracking-[.12em] font-poppins">
                    Pilih Paket Berlangganan
                </span>
            </div>

            @php
                $personalPlans = $layanans->filter(fn ($layanan) => (string) ($layanan->audience_type ?? 'personal') !== 'organization');
                $organizationPlans = $layanans->filter(fn ($layanan) => (string) ($layanan->audience_type ?? 'personal') === 'organization');
            @endphp

            <div x-data="{ activeTab: 'personal' }">

                {{-- Tab switcher --}}
                <div class="mb-8 flex flex-wrap gap-2">
                    <button type="button" @click="activeTab = 'personal'"
                        :class="activeTab === 'personal' ? 'bg-stikom text-white' : 'bg-white text-slate-700 border border-slate-200'"
                        class="rounded-full px-4 py-2 text-sm font-semibold transition">
                        Personal
                    </button>
                    <button type="button" @click="activeTab = 'organization'"
                        :class="activeTab === 'organization' ? 'bg-stikom text-white' : 'bg-white text-slate-700 border border-slate-200'"
                        class="rounded-full px-4 py-2 text-sm font-semibold transition">
                        Organization
                    </button>
                </div>

                @if($layanans->isEmpty())
                    <div class="text-center py-20 text-gray-400 font-body text-sm">
                        Belum ada paket langganan yang tersedia. Silakan cek kembali nanti.
                    </div>
                @else
                    <div x-show="activeTab === 'personal'" x-cloak x-transition>
                        @include('pages.landing.components.plan-card-list', ['plans' => $personalPlans])
                    </div>

                    <div x-show="activeTab === 'organization'" x-cloak x-transition>
                        @if(auth()->check())
                            <div class="mb-4 rounded-2xl border border-sky-200 bg-sky-50/70 px-4 py-3 mb-10 text-sm text-sky-700">
                                Paket Organization cocok untuk tim. Setelah checkout, Anda bisa melanjutkan ke halaman pembuatan organisasi.
                            </div>
                        @endif
                        @include('pages.landing.components.plan-card-list', ['plans' => $organizationPlans])
                    </div>
                @endif

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