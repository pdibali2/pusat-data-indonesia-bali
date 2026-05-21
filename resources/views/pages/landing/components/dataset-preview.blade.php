<section id="produk-unggulan" class="py-24 bg-white" aria-labelledby="produk-heading">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-12 fade-up">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-stikom/5 border border-stikom/10 mb-4">
                    <div class="w-1.5 h-1.5 rounded-full bg-stikom-core"></div>
                    <span class="text-stikom text-xs font-bold uppercase tracking-wider">Produk Unggulan</span>
                </div>
                <h2 id="produk-heading" class="text-3xl sm:text-4xl font-black font-poppins text-stikom leading-tight">
                    Preview <span class="text-stikom-core">Dataset Terbaru</span>
                </h2>
                <p class="text-gray-500 mt-2 text-base">Data terpilih yang siap Anda eksplorasi lebih lanjut.</p>
            </div>
            <a href="{{ route('metadata.index') }}"
               class="shrink-0 inline-flex items-center gap-2 px-5 py-2.5 bg-stikom-core hover:bg-emerald-700 text-white text-sm font-bold transition-colors">
                Lihat Semua
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                </svg>
            </a>
        </div>

        {{-- Cards grid --}}
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">

            @forelse($produkUnggulan as $i => $meta)
                @php
                    // Generate deterministic-looking fake sparkline heights from metadata_id
                    $seed = $meta->metadata_id;
                    $bars = [];
                    for ($b = 0; $b < 12; $b++) {
                        $bars[] = 25 + (($seed * ($b + 3) * 17) % 65);
                        $seed = ($seed * 6364136223846793005 + 1) % PHP_INT_MAX;
                    }
                    $trend = $bars[11] > $bars[0] ? 'up' : 'down';
                    $diff  = abs($bars[11] - $bars[0]);
                @endphp

                <div class="group bg-white rounded-lg border border-gray-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden fade-up"
                     style="animation-delay: {{ $i * 0.07 }}s">

                    {{-- Sparkline area --}}
                    <div class="relative bg-linear-to-br from-stikom to-[#002a52] px-5 pt-5 pb-2 h-36 overflow-hidden">

                        {{-- Watermark label --}}
                        <div class="absolute top-3 right-4 flex items-center gap-1.5">
                            <span class="text-stikom-accent/60 text-xs font-semibold">{{ $meta->satuan_data }}</span>
                        </div>

                        {{-- SVG sparkline --}}
                        <svg viewBox="0 0 220 70" class="w-full h-20 mt-2" preserveAspectRatio="none" aria-hidden="true">
                            {{-- Grid lines --}}
                            @foreach([17, 35, 52] as $gy)
                                <line x1="0" y1="{{ $gy }}" x2="220" y2="{{ $gy }}" stroke="white" stroke-opacity="0.06" stroke-width="1"/>
                            @endforeach

                            {{-- Area fill --}}
                            @php
                                $pts = [];
                                $n = count($bars);
                                foreach ($bars as $bi => $bv) {
                                    $x = ($bi / ($n - 1)) * 220;
                                    $y = 70 - (($bv / 90) * 65);
                                    $pts[] = "{$x},{$y}";
                                }
                                $polyLine = implode(' ', $pts);
                                $areaPath = "M0,70 L" . implode(' L', $pts) . " L220,70 Z";
                            @endphp
                            <defs>
                                <linearGradient id="spark-fill-{{ $meta->metadata_id }}" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="#F7C100" stop-opacity="0.35"/>
                                    <stop offset="100%" stop-color="#F7C100" stop-opacity="0.02"/>
                                </linearGradient>
                            </defs>
                            <path d="{{ $areaPath }}" fill="url(#spark-fill-{{ $meta->metadata_id }})"/>
                            <polyline points="{{ $polyLine }}" fill="none" stroke="#F7C100" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>

                            {{-- Last dot --}}
                            @php [$lx, $ly] = explode(',', end($pts)); @endphp
                            <circle cx="{{ $lx }}" cy="{{ $ly }}" r="3" fill="#F7C100"/>
                        </svg>

                        {{-- Trend badge --}}
                        <div class="absolute bottom-3 right-4 flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold
                                    {{ $trend === 'up' ? 'bg-emerald-500/20 text-emerald-300' : 'bg-red-500/20 text-red-300' }}">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                @if($trend === 'up')
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                @else
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 17l5-5m0 0l-5-5m5 5H6"/>
                                @endif
                            </svg>
                            {{ $trend === 'up' ? '+' : '-' }}{{ round(($diff / max($bars[0], 1)) * 100, 1) }}%
                        </div>
                    </div>

                    {{-- Card body --}}
                    <div class="p-5">
                        <div class="flex items-start justify-between gap-2 mb-3">
                            <h3 class="text-sm font-bold text-stikom leading-snug line-clamp-2 group-hover:text-stikom-core transition-colors duration-200"
                                style="-webkit-text-stroke: 0px;">
                                {{ $meta->nama }}
                            </h3>
                            <span class="shrink-0 px-2 py-0.5 rounded-full bg-stikom/5 text-stikom text-xs font-semibold">
                                {{ $meta->frekuensi_penerbitan }}
                            </span>
                        </div>

                        <div class="flex items-center gap-2 mb-4">
                            <span class="px-2 py-0.5 rounded-full bg-stikom-core/15 text-stikom text-xs font-semibold">
                                {{ $meta->klasifikasi->nama_klasifikasi }}
                            </span>
                            <span class="text-gray-400 text-xs">sejak {{ $meta->tahun_mulai_data }}</span>
                        </div>

                        {{-- Pilihan unduh format --}}
                        <div class="border-t border-gray-50 pt-4" x-data="{ showFormat: false }">
                            <div class="flex items-center justify-between">
                                <button
                                    @click="showFormat = !showFormat"
                                    class="flex items-center gap-1.5 text-xs font-semibold text-stikom hover:text-stikom-core transition-colors group/dl"
                                    aria-label="Pilih format unduh"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                    </svg>
                                    Unduh Data
                                    <svg class="w-3 h-3 transition-transform" :class="showFormat ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                                <a href="{{ route('langganan') }}"
                                   class="flex items-center gap-1 text-xs font-bold text-stikom hover:text-stikom-core transition-colors">
                                    Lihat Detail
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            </div>

                            {{-- Format picker --}}
                            <div x-show="showFormat"
                                 x-transition:enter="transition ease-out duration-150"
                                 x-transition:enter-start="opacity-0 -translate-y-1"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 class="mt-3 flex items-center gap-2 flex-wrap">
                                @foreach([
                                    ['label' => 'Excel', 'icon' => 'M3 10h18M3 14h18M10 3v18M14 3v18M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z', 'color' => 'text-emerald-600 bg-emerald-50 hover:bg-emerald-100'],
                                    ['label' => 'PDF',   'icon' => 'M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z',               'color' => 'text-red-600 bg-red-50 hover:bg-red-100'],
                                    ['label' => 'JSON',  'icon' => 'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4',                                                                                     'color' => 'text-blue-600 bg-blue-50 hover:bg-blue-100'],
                                ] as $fmt)
                                    <a href="{{ route('langganan') }}"
                                       title="Unduh format {{ $fmt['label'] }} (perlu berlangganan)"
                                       class="flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold {{ $fmt['color'] }} transition-colors"
                                       aria-label="Unduh {{ $fmt['label'] }}">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $fmt['icon'] }}"/>
                                        </svg>
                                        {{ $fmt['label'] }}
                                    </a>
                                @endforeach
                                <span class="text-xs text-gray-400 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                    Perlu berlangganan
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="sm:col-span-2 lg:col-span-3 text-center py-16 text-gray-400">
                    <svg class="w-12 h-12 mx-auto mb-4 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="font-medium">Belum ada data yang dipublikasikan</p>
                </div>
            @endforelse
        </div>

        {{-- CTA bawah --}}
        <div class="text-center mt-12 fade-up">
            <a href="{{ route('langganan') }}"
               class="inline-flex items-center gap-2 px-7 py-3.5 bg-stikom-core text-white font-black text-sm hover:bg-emerald-700 transition-all duration-200 shadow-lg hover:scale-105">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
                Berlangganan untuk Akses Penuh
            </a>
        </div>
    </div>
</section>