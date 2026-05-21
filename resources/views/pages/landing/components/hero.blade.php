{{-- resources/views/pages/landing/components/hero.blade.php --}}
{{-- Redesign: STIKOM-style hero — dark navy gradient, grid pattern, sharp corners, bold typography --}}

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

    {{-- Left accent bar --}}
    <div class="absolute left-0 top-0 bottom-0 w-1" aria-hidden="true"></div>

    {{-- Subtle glow blobs --}}
    <div class="absolute inset-0 overflow-hidden pointer-events-none" aria-hidden="true">
        <div class="absolute top-1/4 -left-32 w-80 h-80 bg-stikom-core/10 blur-[80px]"></div>
        <div class="absolute bottom-1/4 -right-32 w-96 h-96 bg-stikom-core/8 blur-[100px]"></div>
    </div>

    {{-- Decorative data lines (right side) --}}
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

    <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center pt-32 pb-24">

        {{-- Label badge --}}
        {{-- <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-stikom-core/15 border border-stikom-core/40 mb-8 fade-up" style="animation-delay:0.1s">
            <span class="w-1.5 h-1.5 bg-stikom-core animate-pulse"></span>
            <span class="text-stikom-core text-xs font-bold tracking-widest uppercase">Platform Layanan Satu Data</span>
        </div> --}}

        {{-- Heading --}}
        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-poppins font-bold tracking-normal text-white leading-tight mb-2 fade-up"
            style="animation-delay:0.2s">
            Pusat Data Indonesia Bali
        </h1>
        

        <p class="text-white/55 text-base sm:text-lg max-w-2xl mx-auto mb-10 leading-relaxed fade-up"
           style="animation-delay:0.3s">
            Layanan penyedia data terpusat untuk memudahkan pengguna menemukan
            dan mengakses data sesuai kebutuhan.
        </p>

        {{-- Search bar --}}
        <div class="relative z-60 max-w-2xl mx-auto mb-8 fade-up" style="animation-delay:0.4s"
             x-data="heroSearch()">
            <div class="relative">
                <div class="flex items-center bg-white shadow-2xl shadow-black/50 overflow-visible border-l-4 border-stikom-core">
                    <div class="pl-5 pr-3 shrink-0">
                        <svg x-show="!loading" class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
                        </svg>
                        <svg x-show="loading" class="w-5 h-5 text-stikom-core animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                        </svg>
                    </div>
                    <input
                        type="search"
                        x-model="query"
                        @input.debounce.350ms="search"
                        @keydown.enter="goToSearch"
                        @keydown.escape="close"
                        @focus="query.length >= 2 && search()"
                        placeholder="Cari metadata, dataset, indikator..."
                        class="flex-1 py-4 pr-4 bg-transparent text-gray-800 placeholder-gray-400 text-base outline-none font-poppins"
                        aria-label="Cari metadata"
                        autocomplete="off"
                    />
                    <button
                        @click="goToSearch"
                        class="m-0 px-6 py-4 bg-stikom text-white text-sm font-bold transition-colors duration-200 shrink-0 hover:bg-stikom-core self-stretch flex items-center">
                        Cari
                    </button>
                </div>

                {{-- Autocomplete dropdown --}}
                <div x-show="showSuggestions"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     @click.outside="close"
                     class="absolute top-full left-0 right-0 mt-0 bg-white shadow-2xl border border-gray-100 border-t-2 border-t-stikom-core overflow-hidden overflow-y-auto max-h-80 z-999"
                     role="listbox">
                    <template x-if="suggestions.length === 0 && !loading && query.length >= 2">
                        <div class="px-5 py-4 text-xs text-gray-400 flex items-center gap-3">
                            <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Metadata tidak ditemukan
                        </div>
                    </template>
                    <template x-for="item in suggestions" :key="item.metadata_id">
                        <a :href="`/klasifikasi/${slugify(item.klasifikasi)}`"
                           class="flex items-center gap-4 px-5 py-3.5 hover:bg-stikom-core/8 border-b border-gray-50 last:border-0 transition-colors cursor-pointer border-l-2 border-l-transparent hover:border-l-stikom-core"
                           role="option">
                            <div class="w-8 h-8 bg-stikom flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 text-stikom-core" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div class="items-start flex flex-col min-w-0 w-full">
                                <div class="text-sm font-semibold text-gray-800 truncate" x-text="item.nama"></div>
                                <div class="text-xs text-gray-400 leading-normal" x-text="item.klasifikasi"></div>
                            </div>
                        </a>
                    </template>
                </div>
            </div>
        </div>

        {{-- Klasifikasi tags --}}
        <div class="relative z-10 flex flex-wrap justify-center gap-2 mb-12 fade-up" style="animation-delay:0.5s">
            @foreach($klasifikasiAktif as $k)
                <a href="{{ route('klasifikasi.show', ['klasifikasi' => \Illuminate\Support\Str::slug($k->nama_klasifikasi)]) }}"
                   class="px-3.5 py-1.5 bg-white/8 border border-white/15 text-white/70 text-xs font-medium hover:bg-stikom-core/20 hover:text-stikom-core hover:border-stikom-core/50 transition-all duration-200 backdrop-blur-sm">
                    {{ $k->nama_klasifikasi }}
                </a>
            @endforeach
        </div>

        {{-- CTA --}}
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4 fade-up" style="animation-delay:0.6s">
            <a href="{{ route('data.index') }}"
               class="group inline-flex items-center gap-2 px-8 py-3.5 bg-stikom-core text-white font-black text-sm transition-all duration-200 shadow-xl hover:bg-[#2d9955] hover:shadow-stikom-core/30 hover:-translate-y-0.5">
                <svg class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                </svg>
                Mulai Eksplorasi Data
            </a>
            <a href="#tentang"
               class="inline-flex items-center gap-2 px-8 py-3.5 bg-transparent text-white/80 font-semibold text-sm border border-white/25 hover:border-white/50 hover:text-white transition-all duration-200">
                Pelajari Lebih Lanjut
            </a>
        </div>

        {{-- Stats mini strip --}}
        {{-- <div class="mt-16 grid grid-cols-3 gap-0 max-w-lg mx-auto fade-up border border-white/10 divide-x divide-white/10" style="animation-delay:0.7s">
            <div class="px-6 py-4 text-center">
                <div class="text-2xl font-black text-white font-poppins">{{ number_format($jumlahData ?? 0) }}</div>
                <div class="text-[10px] font-semibold uppercase tracking-wider text-white/40 mt-0.5">Total Data</div>
            </div>
            <div class="px-6 py-4 text-center">
                <div class="text-2xl font-black text-stikom-core font-poppins">{{ number_format($jumlahMetadata ?? 0) }}</div>
                <div class="text-[10px] font-semibold uppercase tracking-wider text-white/40 mt-0.5">Metadata</div>
            </div>
            <div class="px-6 py-4 text-center">
                <div class="text-2xl font-black text-white font-poppins">{{ number_format($jumlahProdusen ?? 0) }}</div>
                <div class="text-[10px] font-semibold uppercase tracking-wider text-white/40 mt-0.5">Produsen Data</div>
            </div>
        </div> --}}
    </div>

    {{-- Bottom border accent --}}
    <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-linear-to-r from-transparent via-stikom-core to-transparent" aria-hidden="true"></div>
</section>

<script>
function heroSearch() {
    return {
        query: '',
        suggestions: [],
        showSuggestions: false,
        loading: false,
        slugify(text) {
            return text.toString().toLowerCase().trim()
                .replace(/\s+/g, '-')
                .replace(/[^\w\-]+/g, '')
                .replace(/\-\-+/g, '-');
        },
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
            if (this.query.trim()) {
                window.location.href = `/metadata?q=${encodeURIComponent(this.query.trim())}`;
            }
        },
        close() { this.showSuggestions = false; }
    }
}
</script>