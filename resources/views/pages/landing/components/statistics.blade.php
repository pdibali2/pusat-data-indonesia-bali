<section id="statistik" class="py-24 bg-stikom relative overflow-hidden" aria-labelledby="statistik-heading">

    {{-- Dot pattern overlay --}}
    <div class="absolute inset-0 opacity-[0.08]" aria-hidden="true">
        <svg class="absolute inset-0 w-full h-full" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <pattern id="stat-dots" width="28" height="28" patternUnits="userSpaceOnUse">
                    <circle cx="2" cy="2" r="1.5" fill="#3DB166"/>
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#stat-dots)"/>
        </svg>
    </div>

    {{-- Left accent bar --}}
    <div class="absolute left-0 top-0 bottom-0 w-1 bg-stikom-blue" aria-hidden="true"></div>

    {{-- Subtle glow --}}
    <div class="absolute inset-0 overflow-hidden pointer-events-none" aria-hidden="true">
        <div class="absolute top-0 left-1/4 w-72 h-72 bg-stikom-blue/10 blur-[80px]"></div>
        <div class="absolute bottom-0 right-1/4 w-96 h-72 bg-stikom-blue/8 blur-[100px]"></div>
    </div>

    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Section header --}}
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-8 mb-16 fade-up">
            <div>
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-1 h-8 bg-stikom-blue"></div>
                    <span class="text-stikom-blue text-xs font-bold uppercase tracking-widest">Statistik Platform</span>
                </div>
                <h2 id="statistik-heading" class="text-3xl sm:text-4xl font-black font-poppins text-white leading-tight">
                    Data Kami <span class="text-stikom-blue">dalam Angka</span>
                </h2>
            </div>
        </div>

        {{-- Stats grid --}}
        <div class="grid sm:grid-cols-3 gap-0 border border-white/10"
             x-data="statsCounter()"
             x-init="initObserver()">

            @php
            $stats = [
                [
                    'label'    => 'Total Data',
                    'value'    => $jumlahData,
                    'key'      => 'data',
                    'suffix'   => '',
                    'icon'     => 'M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4',
                ],
                [
                    'label'    => 'Total Metadata',
                    'value'    => $jumlahMetadata,
                    'key'      => 'metadata',
                    'suffix'   => '',
                    'icon'     => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                ],
                [
                    'label'    => 'Produsen Data',
                    'value'    => $jumlahProdusen,
                    'key'      => 'produsen',
                    'suffix'   => '',
                    'icon'     => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
                ],
            ];
            @endphp

            @foreach($stats as $i => $s)
                <div class="group relative p-8 border-r border-white/10 last:border-r-0 hover:bg-white/5 transition-all duration-300 fade-up"
                     style="animation-delay: {{ $i * 0.12 }}s">

                    {{-- Top accent line on hover --}}
                    <div class="absolute top-0 left-0 right-0 h-0.5 bg-stikom-blue scale-x-0 group-hover:scale-x-100 transition-transform duration-300 origin-left"></div>

                    {{-- Icon --}}
                    <div class="w-12 h-12 bg-stikom-blue/15 border border-stikom-blue/30 flex items-center justify-center mb-6 group-hover:bg-stikom-blue group-hover:border-stikom-blue transition-all duration-300">
                        <svg class="w-6 h-6 text-stikom-blue group-hover:text-white transition-colors duration-300"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $s['icon'] }}"/>
                        </svg>
                    </div>

                    {{-- Number --}}
                    <div class="text-5xl font-black text-white mb-2 tabular-nums font-poppins"
                         x-text="formatNum(counts['{{ $s['key'] }}'])"
                         data-target="{{ $s['value'] }}"
                         data-key="{{ $s['key'] }}">0</div>

                    {{-- Label --}}
                    <div class="text-stikom-blue font-bold text-sm uppercase tracking-widest mb-1">{{ $s['label'] }}</div>
                    

                    {{-- Bottom bar --}}
                    <div class="absolute bottom-0 left-8 right-8 h-0.5 bg-stikom-blue opacity-0 group-hover:opacity-50 transition-opacity duration-300"></div>
                </div>
            @endforeach
        </div>

        {{-- CTA row below stats --}}
        {{-- <div class="mt-12 flex flex-col sm:flex-row items-center justify-between gap-6 border-t border-white/10 pt-10 fade-up">
            
            <a href="{{ route('data.index') }}"
               class="inline-flex items-center gap-2 px-6 py-3 border border-white/20 text-white text-sm font-bold hover:border-stikom-blue hover:text-stikom-blue transition-all duration-200">
                Lihat Semua Data
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                </svg>
            </a>
        </div> --}}
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
            const steps = 60, stepTime = 1800 / steps;
            const inc = target / steps;
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