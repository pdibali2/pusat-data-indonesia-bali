<section id="produk-unggulan" class="py-24 bg-white" aria-labelledby="produk-heading">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-12 fade-up">
            <div>
                <h2 id="produk-heading" class="text-3xl sm:text-4xl font-black font-poppins text-stikom-blue leading-tight">
                    Preview Dataset Terbaru
                </h2>
                <p class="text-gray-500 mt-2 text-base">Data yang siap Anda eksplorasi lebih lanjut.</p>
            </div>
        </div>

        {{-- Cards grid --}}
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">

            @forelse($produkUnggulan as $i => $meta)

                @php
                    $lastYear = now()->year - 1; // Ambil data hingga tahun lalu untuk memastikan data sudah tersedia
                    $startYear   = $lastYear - 4;

                    $dataPoints = $meta->data()
                        ->where('data.status', \App\Models\Data::STATUS_AVAILABLE)
                        ->join('time', 'data.time_id', '=', 'time.time_id')
                        ->whereBetween('time.year', [$startYear, $lastYear])
                        ->groupBy('time.year')
                        ->orderBy('time.year')
                        ->selectRaw('time.year, SUM(data.number_value) as nilai')
                        ->pluck('nilai', 'year');

                    $years = range($startYear, $lastYear);
                    $vals  = collect($years)
                        ->map(fn($y) => isset($dataPoints[$y]) ? (float) $dataPoints[$y] : null)
                        ->filter()
                        ->values();

                    $minV = $vals->min() ?: 0;
                    $maxV = $vals->max() ?: 1;
                    $n    = $vals->count();

                    $pts = $vals->map(function ($v, $i) use ($n, $minV, $maxV) {
                        $x = ($i / max($n - 1, 1)) * 300;
                        $y = 90 - 6 - (($v - $minV) / max($maxV - $minV, 1)) * 78;
                        return [round($x, 1), round($y, 1)];
                    });

                    $polyLine = $pts->map(fn($p) => "{$p[0]},{$p[1]}")->implode(' ');
                    $areaPath = 'M0,90 L' . $pts->map(fn($p) => "{$p[0]},{$p[1]}")->implode(' L') . ' L300,90 Z';

                    $firstVal = $vals->first() ?: 1;
                    $lastVal  = $vals->last()  ?: 1;
                    $diff     = $lastVal - $firstVal;
                    $pct      = round(abs($diff / $firstVal) * 100, 1);
                    $trend    = $diff >= 0 ? 'up' : 'down';
                    $lastPt   = $pts->last();
                @endphp

                <div class="group bg-white rounded-lg border border-gray-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden fade-up"
                     style="animation-delay: {{ $i * 0.07 }}s">

                    {{-- Sparkline area --}}
                    <div class="relative px-5 pt-5 pb-2 h-36 overflow-hidden" style="background:#0B2A52;">

                        <span class="absolute top-3 right-4 text-xs font-semibold" style="color:rgba(247,193,0,0.7);">
                            {{ $meta->satuan_data }}
                        </span>

                        <svg viewBox="0 0 300 90" class="w-full h-20 mt-1" preserveAspectRatio="none" aria-hidden="true">
                            <defs>
                                <linearGradient id="gf-{{ $meta->metadata_id }}" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%"   stop-color="#F7C100" stop-opacity="0.4"/>
                                    <stop offset="100%" stop-color="#F7C100" stop-opacity="0.03"/>
                                </linearGradient>
                            </defs>
                            @foreach([25, 50, 75] as $gy)
                                <line x1="0" y1="{{ $gy }}" x2="300" y2="{{ $gy }}"
                                      stroke="white" stroke-opacity="0.06" stroke-width="1"/>
                            @endforeach

                            @if($n >= 2)
                                <path d="{{ $areaPath }}" fill="url(#gf-{{ $meta->metadata_id }})"/>
                                <polyline points="{{ $polyLine }}" fill="none" stroke="#F7C100"
                                          stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                                @if($lastPt)
                                    <circle cx="{{ $lastPt[0] }}" cy="{{ $lastPt[1] }}" r="3.5" fill="#F7C100"/>
                                @endif
                            @else
                                <text x="150" y="48" text-anchor="middle"
                                      font-size="11" fill="rgba(255,255,255,0.35)">Belum ada data</text>
                            @endif
                        </svg>

                        {{-- Year labels --}}
                        <div class="flex justify-between px-0.5 mt-1">
                            @foreach($years as $y)
                                <span class="text-white/40" style="font-size:10px;">{{ $y }}</span>
                            @endforeach
                        </div>

                        {{-- Trend badge --}}
                        @if($n >= 2)
                            <div class="absolute bottom-3 right-4 flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold
                                {{ $trend === 'up' ? 'bg-blue-500/20 text-blue-300' : 'bg-red-500/20 text-red-300' }}">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    @if($trend === 'up')
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                              d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                    @else
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                              d="M13 17l5-5m0 0l-5-5m5 5H6"/>
                                    @endif
                                </svg>
                                {{ $trend === 'up' ? '+' : '-' }}{{ $pct }}%
                            </div>
                        @endif
                    </div>

                    {{-- Card body --}}
                    <div class="p-5">
                        <div class="flex items-start justify-between gap-2 mb-3">
                            <h3 class="text-sm font-bold text-stikom leading-snug line-clamp-2 group-hover:text-stikom-blue transition-colors duration-200">
                                {{ $meta->nama }}
                            </h3>
                            <span class="shrink-0 px-2 py-0.5 rounded-full bg-stikom/5 text-stikom text-xs font-semibold">
                                {{ $meta->frekuensi_penerbitan }}
                            </span>
                        </div>

                        <div class="flex items-center gap-2 mb-4">
                            <span class="px-2 py-0.5 rounded-full bg-stikom-blue/15 text-stikom text-xs font-semibold">
                                {{ $meta->klasifikasi->nama_klasifikasi }}
                            </span>
                            <span class="text-gray-400 text-xs">sejak {{ $meta->tahun_mulai_data }}</span>
                        </div>

                        <div class="border-t border-gray-50 pt-4">
                            <a href="{{ route('landing.data.show', $meta->metadata_id) }}"
                               class="flex items-center gap-1 text-xs font-bold text-stikom hover:text-stikom-accent transition-colors">
                                Lihat Detail
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

            @empty
                <div class="sm:col-span-2 lg:col-span-3 text-center py-16 text-gray-400">
                    <svg class="w-12 h-12 mx-auto mb-4 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="font-medium">Belum ada data yang dipublikasikan</p>
                </div>
            @endforelse

        </div>

        {{-- CTA bawah --}}
        <div class="text-center mt-12 fade-up">
            <a href="{{ route('langganan') }}"
               class="inline-flex items-center gap-2 px-7 py-3.5 bg-stikom-accent text-black hover:text-white font-black text-sm hover:bg-yellow-600 transition-all duration-200 shadow-lg hover:scale-105">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                          d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
                Berlangganan untuk Akses Penuh
            </a>
        </div>
    </div>
</section>