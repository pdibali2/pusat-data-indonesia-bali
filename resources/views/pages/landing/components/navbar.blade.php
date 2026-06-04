@php
use Illuminate\Support\Str;
$half = (int) ceil($allKlasifikasi->count() / 2);
$col1 = $allKlasifikasi->slice(0, $half);
$col2 = $allKlasifikasi->slice($half);
@endphp

<nav
    x-data="navbar()"
    x-init="init()"
    :class="scrolled ? 'bg-white shadow-lg border-b border-gray-100' : 'bg-transparent'"
    class="fixed top-0 left-0 right-0 z-50 transition-all duration-300"
    role="navigation"
    aria-label="Navigasi Utama"
>
    {{-- Top accent bar --}}
    <div class="h-0.5 w-full"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 lg:h-18">

            {{-- Logo --}}
            <a href="{{ route('landing') }}"
               class="flex items-center gap-3 group shrink-0"
               aria-label="Pusat Data Indonesia Bali">
                <div class="w-9 h-9 bg-stikom-accent flex items-center justify-center transition-transform duration-200 group-hover:scale-110">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                    </svg>
                </div>
                <div class="leading-tight hidden sm:block">
                    <div class="text-sm font-bold tracking-tight transition-colors duration-300 font-poppins"
                         :class="scrolled ? 'text-stikom' : 'text-white'">
                        Pusat Data Indonesia Bali
                    </div>
                    {{-- <div class="text-[10px] font-semibold tracking-widest uppercase transition-colors duration-300"
                         :class="scrolled ? 'text-stikom-accent' : 'text-stikom-accent'">
                        Provinsi Bali
                    </div> --}}
                </div>
            </a>

            {{-- Desktop menu --}}
            <div class="hidden lg:flex items-center gap-0">

                {{-- Klasifikasi mega-dropdown --}}
                <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                    <button
                        class="flex items-center gap-1.5 px-4 py-5 text-sm font-semibold transition-all duration-200 border-b-4 border-transparent"
                        :class="scrolled
                            ? 'text-stikom hover:text-stikom-accent hover:border-stikom-accent'
                            : 'text-white/90 hover:text-white hover:border-stikom-accent/70'"
                        aria-haspopup="true" :aria-expanded="open"
                    >
                        Klasifikasi
                        <svg class="w-3.5 h-3.5 transition-transform duration-200"
                             :class="open ? 'rotate-180' : ''"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <div x-show="open"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 translate-y-1"
                         class="absolute top-full left-0 mt-0 w-96 bg-white shadow-2xl z-50 overflow-hidden border-t-2 border-stikom-accent"
                         role="menu">
                        <div class="px-5 py-3 bg-stikom flex items-center justify-between">
                            <span class="text-white text-xs font-bold uppercase tracking-widest">Semua Klasifikasi</span>
                            <a href="{{ route('klasifikasi.index') }}"
                               class="text-stikom-accent text-xs font-semibold hover:underline flex items-center gap-1">
                                Lihat semua
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                </svg>
                            </a>
                        </div>
                        <div class="grid grid-cols-2 max-h-72 overflow-y-auto">
                            <div class="border-r border-gray-100 py-2">
                                @foreach($col1 as $k)
                                    <a href="{{ route('klasifikasi.show', ['klasifikasi' => Str::slug($k)]) }}"
                                       class="block px-4 py-2 text-xs text-gray-600 hover:bg-stikom-accent/10 hover:text-stikom font-medium transition-colors border-l-2 border-transparent hover:border-stikom-accent"
                                       role="menuitem">{{ $k }}</a>
                                @endforeach
                            </div>
                            <div class="py-2">
                                @foreach($col2 as $k)
                                    <a href="{{ route('klasifikasi.show', ['klasifikasi' => Str::slug($k)]) }}"
                                       class="block px-4 py-2 text-xs text-gray-600 hover:bg-stikom-accent/10 hover:text-stikom font-medium transition-colors border-l-2 border-transparent hover:border-stikom-accent"
                                       role="menuitem">{{ $k }}</a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Produk dropdown --}}
                <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                    <button
                        class="flex items-center gap-1.5 px-4 py-5 text-sm font-semibold transition-all duration-200 border-b-4 border-transparent"
                        :class="scrolled
                            ? 'text-stikom hover:text-stikom-accent hover:border-stikom-accent'
                            : 'text-white/90 hover:text-white hover:border-stikom-accent/70'"
                        aria-haspopup="true" :aria-expanded="open"
                    >
                        Produk
                        <svg class="w-3.5 h-3.5 transition-transform duration-200"
                             :class="open ? 'rotate-180' : ''"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="open"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 translate-y-1"
                         class="absolute top-full left-0 mt-0 w-52 bg-white shadow-xl border border-gray-100 border-t-2 border-t-stikom-accent py-2 z-50"
                         role="menu">
                        <a href="{{ route('landing.data.series') }}"
                           class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-stikom-accent/10 hover:text-stikom transition-colors border-l-2 border-transparent hover:border-stikom-accent"
                           role="menuitem">
                            <svg class="w-4 h-4 text-stikom-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            Data Series
                        </a>
                        <a href="{{ route('data.index') }}"
                           class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-stikom-accent/10 hover:text-stikom transition-colors border-l-2 border-transparent hover:border-stikom-accent"
                           role="menuitem">
                            <svg class="w-4 h-4 text-stikom-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                            </svg>
                            Multiple Data
                        </a>
                    </div>
                </div>

                <a href="{{ route('langganan') }}"
                   class="px-4 py-5 text-sm font-semibold transition-all duration-200 border-b-4 border-transparent"
                   :class="scrolled
                       ? 'text-stikom hover:text-stikom-accent hover:border-stikom-accent'
                       : 'text-white/90 hover:text-white hover:border-stikom-accent/70'">
                    Langganan
                </a>

                {{-- CTA Auth --}}
                @auth
                    <a href="{{ route('data.index') }}"
                    class="ml-4 px-6 py-4 bg-stikom-accent hover:bg-yellow-600 text-stikom hover:text-white text-sm font-bold transition-all duration-200 shadow-md hover:shadow-lg hover:-translate-y-0.5">
                        Halaman Data
                    </a>
                @endauth

                @guest
                    <a href="{{ route('login') }}"
                    class="ml-4 px-6 py-4 bg-stikom-accent text-black text-sm font-bold transition-all duration-200 hover:bg-stikom-accent/90 shadow-md hover:shadow-lg hover:-translate-y-0.5">
                        Login
                    </a>
                @endguest
            </div>

            {{-- Mobile hamburger --}}
            <button
                @click="mobileOpen = !mobileOpen"
                class="lg:hidden p-2 transition-colors duration-200"
                :class="scrolled ? 'text-stikom' : 'text-white'"
                aria-label="Toggle menu" :aria-expanded="mobileOpen"
            >
                <svg x-show="!mobileOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                <svg x-show="mobileOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- Mobile menu --}}
    <div x-show="mobileOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="lg:hidden bg-white border-t-2 border-stikom-accent shadow-xl max-h-[80vh] overflow-y-auto">
        <div class="max-w-7xl mx-auto px-4 py-4 space-y-0.5">

            <div x-data="{ open: false }">
                <button @click="open = !open"
                        class="w-full flex items-center justify-between px-4 py-3 text-sm font-semibold text-stikom hover:bg-stikom-accent/10 hover:text-stikom-accent transition-colors border-l-2 border-transparent hover:border-stikom-accent">
                    Klasifikasi
                    <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-180' : ''"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open" class="mt-0.5 ml-4 space-y-0 max-h-56 overflow-y-auto pr-2">
                    @foreach($allKlasifikasi as $k)
                        <a href="{{ route('klasifikasi.show', ['klasifikasi' => Str::slug($k)]) }}"
                           @click="mobileOpen = false"
                           class="block px-4 py-2 text-sm text-gray-600 hover:text-stikom hover:bg-stikom-accent/10 border-l-2 border-transparent hover:border-stikom-accent transition-colors">{{ $k }}</a>
                    @endforeach
                </div>
            </div>

            <div x-data="{ open: false }">
                <button @click="open = !open"
                        class="w-full flex items-center justify-between px-4 py-3 text-sm font-semibold text-stikom hover:bg-stikom-accent/10 hover:text-stikom-accent transition-colors border-l-2 border-transparent hover:border-stikom-accent">
                    Produk
                    <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-180' : ''"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open" class="mt-0.5 ml-4 space-y-0">
                    <a href="{{ route('data.index') }}" @click="mobileOpen = false"
                       class="block px-4 py-2 text-sm text-gray-600 hover:text-stikom border-l-2 border-transparent hover:border-stikom-accent hover:bg-stikom-accent/10 transition-colors">Data Series</a>
                    <a href="{{ route('data.index') }}" @click="mobileOpen = false"
                       class="block px-4 py-2 text-sm text-gray-600 hover:text-stikom border-l-2 border-transparent hover:border-stikom-accent hover:bg-stikom-accent/10 transition-colors">Multiple Data</a>
                </div>
            </div>

            <a href="{{ route('langganan') }}" @click="mobileOpen = false"
               class="block px-4 py-3 text-sm font-semibold text-stikom hover:bg-stikom-accent/10 hover:text-stikom-accent border-l-2 border-transparent hover:border-stikom-accent transition-colors">
                Langganan
            </a>

            <div class="pt-3 border-t border-gray-100">
                @auth
                    <a href="{{ route('data.index') }}"
                    @click="mobileOpen = false"
                    class="block w-full text-center px-4 py-3 text-sm font-bold bg-stikom-accent text-white hover:bg-[#2d9955] transition-colors">
                        Halaman Data
                    </a>
                @endauth

                @guest
                    <a href="{{ route('login') }}"
                    class="block w-full text-center px-4 py-3 text-sm font-bold bg-stikom-accent text-black hover:bg-[#2d9955] transition-colors">
                        Login
                    </a>
                @endguest

            </div>
        </div>
    </div>
</nav>

<script>
function navbar() {
    return {
        scrolled: false,
        mobileOpen: false,
        init() {
            this.onScroll();
            window.addEventListener('scroll', () => this.onScroll(), { passive: true });
        },
        onScroll() {
            this.scrolled = window.scrollY > 20;
        }
    }
}
</script>