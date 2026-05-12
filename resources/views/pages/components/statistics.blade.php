{{-- resources/views/components/landing/statistics.blade.php --}}
<section id="statistik" class="py-24 bg-stikom relative overflow-hidden" aria-labelledby="statistik-heading">

    <div class="absolute inset-0 opacity-10" aria-hidden="true">
        <svg class="absolute inset-0 w-full h-full" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <pattern id="stat-dots" width="30" height="30" patternUnits="userSpaceOnUse">
                    <circle cx="2" cy="2" r="1.5" fill="#F7C100"/>
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#stat-dots)"/>
        </svg>
    </div>

    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="text-center mb-16 fade-up">
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-stikom-accent/15 border border-stikom-accent/30 mb-6">
                <div class="w-1.5 h-1.5 rounded-full bg-stikom-accent"></div>
                <span class="text-stikom-accent text-xs font-bold uppercase tracking-wider">Statistik Platform</span>
            </div>
            <h2 id="statistik-heading" class="text-3xl sm:text-4xl font-black text-white leading-tight">
                Data Kami dalam Angka
            </h2>
        </div>

        <div class="grid sm:grid-cols-3 gap-6"
             x-data="statsCounter()"
             x-init="initObserver()"
        >
            @php
            $stats = [
                ['label' => 'Total Data',       'value' => $jumlahData,     'key' => 'data',     'icon' => 'M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4'],
                ['label' => 'Total Metadata',   'value' => $jumlahMetadata, 'key' => 'metadata', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                ['label' => 'Produsen Data',    'value' => $jumlahProdusen, 'key' => 'produsen', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
            ];
            @endphp

            @foreach($stats as $i => $s)
                <div class="group relative bg-white/5 backdrop-blur-sm border border-white/10 rounded-3xl p-8 hover:bg-stikom-accent/10 hover:border-stikom-accent/30 hover:-translate-y-1 transition-all duration-300 fade-up"
                     style="animation-delay: {{ $i * 0.12 }}s">
                    <div class="w-14 h-14 rounded-2xl bg-stikom-accent flex items-center justify-center mb-6 shadow-lg group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-7 h-7 text-stikom" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $s['icon'] }}"/>
                        </svg>
                    </div>
                    <div class="text-5xl font-black text-white mb-2 tabular-nums"
                         x-text="formatNum(counts['{{ $s['key'] }}'])"
                         data-target="{{ $s['value'] }}"
                         data-key="{{ $s['key'] }}">0</div>
                    <div class="text-white/50 font-semibold text-sm uppercase tracking-wider">{{ $s['label'] }}</div>
                    <div class="absolute bottom-0 left-8 right-8 h-0.5 bg-stikom-accent opacity-0 group-hover:opacity-60 transition-opacity duration-300 rounded-full"></div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<script>
function statsCounter() {
    return {
        counts: { data: 0, metadata: 0, produsen: 0 },
        animated: false,
        initObserver() {
            const observer = new IntersectionObserver((entries) => {
                if (entries[0].isIntersecting && !this.animated) {
                    this.animated = true;
                    document.querySelectorAll('[data-target]').forEach(el => {
                        this.animateCount(el.dataset.key, parseInt(el.dataset.target) || 0);
                    });
                }
            }, { threshold: 0.3 });
            observer.observe(this.$el);
        },
        animateCount(key, target) {
            const steps = 60, stepTime = 1800 / steps, inc = target / steps;
            let cur = 0;
            const t = setInterval(() => {
                cur += inc;
                if (cur >= target) { cur = target; clearInterval(t); }
                this.counts[key] = Math.floor(cur);
            }, stepTime);
        },
        formatNum(n) {
            if (n >= 1_000_000) return (n / 1_000_000).toFixed(1).replace('.0','') + 'JT';
            if (n >= 1_000)     return Math.floor(n / 1_000) + 'K';
            return n.toLocaleString('id-ID');
        }
    }
}
</script>