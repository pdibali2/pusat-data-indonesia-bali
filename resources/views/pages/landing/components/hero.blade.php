{{-- resources/views/pages/landing/components/hero.blade.php --}}
{{-- Mobile-first refactor: full responsive 320px → desktop --}}

<section
    id="beranda"
    class="relative min-h-screen flex items-center justify-center overflow-hidden"
    style="background: linear-gradient(135deg, #001a34 0%, #00316d 45%, #013d76 100%);"
    aria-label="Beranda"
>
    {{-- Grid pattern overlay --}}
    <div class="absolute inset-0 opacity-[0.07]" aria-hidden="true">
        <svg class="absolute inset-0 w-full h-full" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <pattern id="hero-grid" width="48" height="48" patternUnits="userSpaceOnUse">
                    <path d="M 48 0 L 0 0 0 48" fill="none" stroke="white" stroke-width="0.8"/>
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#hero-grid)"/>
        </svg>
    </div>

    {{-- Subtle glow blobs --}}
    <div class="absolute inset-0 overflow-hidden pointer-events-none" aria-hidden="true">
        <div class="absolute top-1/4 -left-32 w-64 h-64 md:w-80 md:h-80 bg-stikom-accent/10 blur-[80px]"></div>
        <div class="absolute bottom-1/4 -right-32 w-72 h-72 md:w-96 md:h-96 bg-stikom-accent/8 blur-[100px]"></div>
    </div>

    {{-- Decorative data lines (desktop only) --}}
    <div class="absolute right-0 top-0 bottom-0 w-64 hidden xl:block opacity-[0.06]" aria-hidden="true">
        <svg class="w-full h-full" viewBox="0 0 256 800" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
            @foreach([20, 60, 100, 140, 180, 220] as $x)
                <line x1="{{ $x }}" y1="0" x2="{{ $x }}" y2="800" stroke="white" stroke-width="1"/>
            @endforeach
            @foreach([80, 200, 320, 440, 560, 680] as $y)
                <line x1="0" y1="{{ $y }}" x2="256" y2="{{ $y }}" stroke="white" stroke-width="1"/>
            @endforeach
        </svg>
    </div>

    {{-- ─── Content ─────────────────────────────────────────────── --}}
    <div class="relative z-10 w-full max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-left lg:text-center pt-28 pb-16 sm:pt-32 sm:pb-20 md:pt-36 md:pb-24">

        {{-- Heading --}}
        <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-poppins font-bold tracking-normal text-stikom-accent leading-tight mb-3 sm:mb-4"
            style="animation-delay:0.2s">
            Pusat Data Indonesia Bali
        </h1>

        <p class="text-white/80 text-sm sm:text-base md:text-lg max-w-xs sm:max-w-md md:max-w-2xl lg:mx-auto mb-8 sm:mb-10 leading-relaxed sm:px-0">
            Layanan penyedia data terpusat untuk memudahkan pengguna menemukan
            dan mengakses data sesuai kebutuhan.
        </p>

        {{-- ─── Search bar ─────────────────────────────────────────── --}}
        {{--
            MOBILE FIX:
            - Input font-size text-[16px] → cegah auto-zoom iOS
            - Touch target tombol "Cari" min-h-[44px]
            - Autocomplete max-h-60 dengan overflow-y-auto
            - border-l-4 hanya di sm keatas agar tidak terlalu mencolok di 320px
        --}}
        <div class="relative z-30 w-full max-w md:max-w-2xl lg:mx-auto mb-7 sm:mb-8"
             x-data="heroSearch()">
            <div class="relative">
                <div class="flex items-stretch bg-white shadow-2xl shadow-black/50 overflow-visible border-l-0 sm:border-l-4 border-stikom-red">

                    {{-- Search icon --}}
                    <div class="pl-3 sm:pl-5 pr-2 sm:pr-3 shrink-0 flex items-center">
                        <svg x-show="!loading" class="w-4 h-4 sm:w-5 sm:h-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
                        </svg>
                        <svg x-show="loading" class="w-4 h-4 sm:w-5 sm:h-5 text-gray-400 animate-spin shrink-0" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                        </svg>
                    </div>

                    {{-- Input — text-[16px] wajib agar iOS tidak zoom --}}
                    <input
                        type="search"
                        x-model="query"
                        @input.debounce.350ms="search"
                        @keydown.enter="goToSearch"
                        @keydown.escape="close"
                        @focus="query.length >= 2 && search()"
                        placeholder="Cari data..."
                        class="flex-1 min-w-0 py-3 sm:py-4 pr-2 bg-transparent text-gray-800 placeholder-gray-400
                               text-[16px] outline-none font-poppins"
                        aria-label="Cari data"
                        autocomplete="off"
                    />

                    {{-- Tombol Cari — min touch target 44px --}}
                    <button
                        @click="goToSearch"
                        class="shrink-0 px-4 sm:px-6 min-h-[44px] bg-stikom text-white text-sm font-bold
                               transition-colors duration-200 hover:bg-stikom-red self-stretch flex items-center">
                        Cari
                    </button>
                </div>

                {{-- Autocomplete dropdown --}}
                <div x-show="showSuggestions"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     @click.outside="close"
                     class="absolute top-full left-0 right-0 mt-0 bg-white shadow-2xl border border-gray-100
                            border-t-2 border-t-stikom-accent overflow-hidden overflow-y-auto max-h-60 sm:max-h-80 z-50"
                     role="listbox">

                    <template x-if="suggestions.length === 0 && !loading && query.length >= 2">
                        <div class="px-4 sm:px-5 py-4 text-xs text-gray-400 flex items-center gap-3">
                            <svg class="w-5 h-5 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Belum ada data yang tersedia
                        </div>
                    </template>

                    <template x-for="item in suggestions" :key="item.metadata_id">
                        {{-- Touch target min 44px via py-3 --}}
                        <a :href="`/statistik/${item.metadata_id}`"
                           class="flex items-center gap-3 sm:gap-4 px-4 sm:px-5 py-3 hover:bg-stikom-accent/8
                                  border-b border-gray-50 last:border-0 transition-colors cursor-pointer
                                  border-l-2 border-l-transparent hover:border-l-stikom-accent min-h-[44px]"
                           role="option">
                            <div class="w-8 h-8 bg-stikom flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 text-stikom-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div class="flex flex-col min-w-0 w-full">
                                <div class="text-sm font-semibold text-gray-800 truncate" x-text="item.nama"></div>
                                <div class="text-xs text-gray-400 leading-normal truncate" x-text="item.klasifikasi"></div>
                            </div>
                        </a>
                    </template>
                </div>
            </div>
        </div>

        {{-- ─── Klasifikasi tags ────────────────────────────────────── --}}
        {{--
            Mobile: flex-wrap, scroll horizontal tidak diperlukan karena wrap
            Limit tampilan di mobile agar tidak memenuhi layar
        --}}
        <div class="relative z-10 flex flex-wrap lg:justify-center gap-1.5 sm:gap-2 mb-10 sm:mb-12 sm:px-0"
             style="animation-delay:0.5s">
            @foreach($klasifikasiAktif as $k)
                <a href="{{ route('klasifikasi.show', ['klasifikasi' => \Illuminate\Support\Str::slug($k->nama_klasifikasi)]) }}"
                   class="px-3 py-1.5 bg-white/8 border border-white/15 text-white/70 text-xs font-medium
                          hover:bg-stikom-accent/20 hover:text-stikom-accent hover:border-stikom-accent/50
                          transition-all duration-200 backdrop-blur-sm
                          min-h-[36px] flex items-center">
                    {{ $k->nama_klasifikasi }}
                </a>
            @endforeach
        </div>

        {{-- ─── CTA Buttons ─────────────────────────────────────────── --}}
        {{--
            Mobile: stack vertikal (flex-col), lebar penuh
            sm+: sejajar horizontal (sm:flex-row)
        --}}
        <div class="flex flex-col sm:flex-row items-center justify-center gap-3 sm:gap-4 sm:px-0">
            <a href="{{ route('data.index') }}"
               class="group w-full sm:w-auto inline-flex items-center justify-center gap-2
                      px-6 sm:px-8 py-3.5 bg-stikom-accent text-black hover:text-white font-black
                      text-sm transition-all duration-200 shadow-xl hover:bg-stikom-accent
                      hover:shadow-stikom-accent/30 hover:-translate-y-0.5 min-h-[44px]">
                <svg class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                </svg>
                Mulai Eksplorasi Data
            </a>
            <a href="#tentang"
               class="w-full sm:w-auto inline-flex items-center justify-center gap-2
                      px-6 sm:px-8 py-3.5 bg-transparent text-white/80 font-semibold
                      text-sm border border-white/25 hover:border-white/50 hover:text-white
                      transition-all duration-200 min-h-[44px]">
                Pelajari Lebih Lanjut
            </a>
        </div>
    </div>

    {{-- Bottom accent --}}
    <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-linear-to-r from-transparent via-stikom-accent to-transparent" aria-hidden="true"></div>
</section>

<script>
function heroSearch() {
    return {
        query: '',
        suggestions: [],
        showSuggestions: false,
        loading: false,
        async search() {
            if (this.query.length < 2) {
                this.suggestions = [];
                this.showSuggestions = false;
                return;
            }
            this.loading = true;
            try {
                const res = await fetch(`{{ route('search_metadata') }}?q=${encodeURIComponent(this.query)}`);
                this.suggestions = await res.json();
                this.showSuggestions = true;
            } catch(e) {
                this.suggestions = [];
                this.showSuggestions = true;
            } finally {
                this.loading = false;
            }
        },
        goToSearch() {
            if (!this.query.trim()) return;
            if (this.suggestions.length > 0) {
                const first = this.suggestions[0];
                window.location.href = `/klasifikasi/${first.klasifikasi_slug}`;
            }
        },
        close() { this.showSuggestions = false; }
    }
}
</script>