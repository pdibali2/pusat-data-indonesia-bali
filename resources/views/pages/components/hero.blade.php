{{-- resources/views/components/landing/hero.blade.php --}}
<section
    id="beranda"
    class="relative min-h-screen flex items-center justify-center overflow-visible bg-linear-to-br from-stikom via-[#002a52] to-[#001020]"
    aria-label="Beranda"
>
    {{-- Grid pattern --}}
    <div class="absolute inset-0 opacity-10" aria-hidden="true">
        <svg class="absolute inset-0 w-full h-full" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <pattern id="hero-grid" width="40" height="40" patternUnits="userSpaceOnUse">
                    <path d="M 40 0 L 0 0 0 40" fill="none" stroke="white" stroke-width="0.5"/>
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#hero-grid)"/>
        </svg>
    </div>

    {{-- Animated blobs --}}
    <div class="absolute inset-0 overflow-hidden pointer-events-none" aria-hidden="true">
        <div class="blob-1 absolute -top-40 -left-40 w-96 h-96 rounded-full bg-stikom-accent/20 blur-3xl"></div>
        <div class="blob-2 absolute -bottom-40 -right-40 w-96 h-96 rounded-full bg-stikom-accent/10 blur-3xl"></div>
        <div class="blob-3 absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-80 h-80 rounded-full bg-blue-400/10 blur-3xl"></div>
    </div>

    <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center pt-28 pb-20">

        {{-- Badge --}}
        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-stikom-accent/15 border border-stikom-accent/30 mb-8 fade-up" style="animation-delay:0.1s">
            <span class="w-2 h-2 rounded-full bg-stikom-accent animate-pulse"></span>
            <span class="text-stikom-accent text-xs font-semibold tracking-wider uppercase">Platform Layanan Satu Data</span>
        </div>

        {{-- Heading --}}
        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-white leading-tight tracking-tight mb-6 fade-up" style="animation-delay:0.2s">
            Pusat Data
            <span class="block text-stikom-accent">Indonesia Bali</span>
        </h1>

        <p class="text-sm sm:text-md text-white/70 max-w-2xl mx-auto mb-10 leading-relaxed fade-up" style="animation-delay:0.3s">
            Layanan penyedia data terpusat untuk memudahkan pengguna menemukan dan mengakses data sesuai kebutuhan.
        </p>

        {{-- Search --}}
        <div class="relative z-60 max-w-2xl mx-auto mb-8 fade-up"
            style="animation-delay:0.4s"
            x-data="heroSearch()">
            <div class="relative">
                <div class="flex items-center bg-white rounded-2xl shadow-2xl shadow-black/40 overflow-visible">
                    <div class="pl-5 pr-3 shrink-0">
                        <svg x-show="!loading" class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
                        </svg>
                        <svg x-show="loading" class="w-5 h-5 text-stikom animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true">
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
                        @focus="query.length >= 2 && search()""
                        placeholder="Cari metadata..."
                        class="flex-1 py-4 pr-4 bg-transparent text-gray-800 placeholder-gray-400 text-base outline-none"
                        aria-label="Cari metadata"
                        autocomplete="off"
                    />
                    <button
                        @click="goToSearch"
                        class="m-2 px-5 py-2.5 bg-stikom hover:bg-[#002a52] text-white text-sm font-bold rounded-xl transition-colors duration-200 shrink-0"
                    >
                        Cari
                    </button>
                </div>

                {{-- Autocomplete dropdown --}}
                <div
                    x-show="showSuggestions"
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    @click.outside="close"
                    class="absolute top-full left-0 right-0 mt-2 bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden overflow-y-auto max-h-80 z-999"
                    role="listbox"
                >
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
                           class="flex items-center gap-4 px-5 py-3.5 hover:bg-yellow-50 border-b border-gray-50 last:border-0 transition-colors cursor-pointer"
                           role="option">
                            <div class="w-8 h-8 rounded-lg bg-stikom flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 text-stikom-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div class="items-start flex flex-col min-w-0 w-full">
                                <div
                                    class="text-sm font-semibold text-gray-800 truncate"
                                    x-text="item.nama">
                                </div>

                                <div
                                    class="text-xs text-gray-400 leading-normal"
                                    x-text="item.klasifikasi">
                                </div>
                            </div>
                        </a>
                    </template>
                </div>
            </div>
        </div>

        <div class="relative z-10 flex flex-wrap justify-center gap-2 mb-12 fade-up" style="animation-delay:0.5s">
            @foreach($klasifikasiAktif as $k)
                <a href="{{ route('klasifikasi.show', ['klasifikasi' => \Illuminate\Support\Str::slug($k)]) }}"
                   class="px-3.5 py-1.5 rounded-full bg-white/10 border border-stikom-accent/30 text-white/80 text-xs font-medium hover:bg-stikom-accent/20 hover:text-stikom-accent transition-all duration-200 hover:scale-105 backdrop-blur-sm">
                    {{ $k }}
                </a>
            @endforeach
        </div>

        {{-- CTA --}}
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4 fade-up" style="animation-delay:0.6s">
            <a href="{{ route('data.index') }}"
               class="group inline-flex items-center gap-2 px-7 py-3.5 rounded-2xl bg-stikom-accent text-stikom font-black text-sm hover:bg-yellow-400 transition-all duration-200 shadow-xl shadow-yellow-500/20 hover:scale-105">
                <svg class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                </svg>
                Mulai Eksplorasi Data
            </a>
        </div>
    </div>

    {{-- Wave divider --}}
    {{-- <div class="absolute bottom-0 left-0 right-0" aria-hidden="true">
        <svg viewBox="0 0 1440 80" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full">
            <path d="M0,40 C360,80 1080,0 1440,40 L1440,80 L0,80 Z" fill="#f8fafc"/>
        </svg>
    </div> --}}
</section>

<script>
function heroSearch() {
    return {
        query: '',
        suggestions: [],
        showSuggestions: false,
        loading: false,

        slugify(text) {
            return text
                .toString()
                .toLowerCase()
                .trim()
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
                const res = await fetch(`/landing/search-metadata?q=${encodeURIComponent(this.query)}`);
                this.suggestions = await res.json();
                this.showSuggestions = true;
            } catch(e) {
                this.suggestions = [];
            } finally {
                this.loading = false;
            }
        },

        goToSearch() {
            if (this.query.trim()) {
                window.location.href = `/metadata?q=${encodeURIComponent(this.query.trim())}`;
            }
        },

        close() {
            this.showSuggestions = false;
        }
    }
}
</script>
{{-- blob animations defined in resources/css/app.css --}}