<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Data Series</title>
    <meta name="description" content="Jelajahi seluruh dataset yang tersedia di Pusat Data Indonesia Bali. Filter berdasarkan klasifikasi, frekuensi, dan kata kunci."/>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon"/>
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet"/>
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    @vite(['resources/css/app.css', 'resources/js/app.ts'])

    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
</head>
<body class="bg-[#f8fafc] text-gray-900 antialiased">

    @include('pages.landing.components.navbar')

    {{-- ── Page Header ──────────────────────────────────────────────────────── --}}
    <div class="bg-[#001734] pt-24 pb-10 relative overflow-hidden">
        {{-- grid bg --}}
        <div class="absolute inset-0 opacity-10" aria-hidden="true">
            <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
                <defs><pattern id="ds-grid" width="40" height="40" patternUnits="userSpaceOnUse">
                    <path d="M 40 0 L 0 0 0 40" fill="none" stroke="white" stroke-width="0.5"/>
                </pattern></defs>
                <rect width="100%" height="100%" fill="url(#ds-grid)"/>
            </svg>
        </div>

        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Breadcrumb --}}
            <nav class="flex items-center gap-2 text-xs text-white/40 mb-6" aria-label="Breadcrumb">
                <a href="{{ route('landing') }}" class="hover:text-[#F7C100] transition-colors">Beranda</a>
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <span class="text-white/70">Data Series</span>
            </nav>

            <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
                <div>
                    <h1 class="text-3xl sm:text-4xl font-black font-poppins text-white leading-tight mb-2">
                        Data Series
                    </h1>
                    <p class="text-white/50 text-base max-w-xl">
                        Jelajahi seluruh dataset yang tersedia. Klik dataset untuk melihat detail dan grafik.
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Main Content ──────────────────────────────────────────────────────── --}}
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

        {{-- ── Filter Bar ────────────────────────────────────────────────── --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 mb-8"
             x-data="filterBar()"
             x-init="syncFromUrl()">

            <form method="GET" action="{{ route('landing.data.series') }}" id="filter-form">
                <div class="flex flex-col lg:flex-row gap-4">

                    {{-- Search --}}
                    <div class="relative flex-1 min-w-0">
                        <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
                        </svg>
                        <input
                            type="search"
                            name="q"
                            value="{{ request('q') }}"
                            placeholder="Cari nama metadata, tag..."
                            class="w-full pl-11 pr-4 py-2.5 rounded-xl border border-gray-200 text-sm text-gray-800 placeholder-gray-400 outline-none focus:border-[#001734] focus:ring-2 focus:ring-[#001734]/10 transition-colors"
                            aria-label="Cari metadata"
                        />
                    </div>

                    {{-- Klasifikasi --}}
                    <div class="lg:w-56">
                        <select id="select-klasifikasi"
                                name="klasifikasi"
                                placeholder="Pilih klasifikasi..."
                                class="tom-select w-full border
                                rounded-sm focus:outline-none focus:ring-2 focus:ring-sky-400 text-xs"
                                aria-label="Filter klasifikasi">
                            <option value="">Semua Klasifikasi</option>
                            @foreach($klasifikasiList as $kl)
                                <option value="{{ $kl }}" {{ request('klasifikasi') === $kl ? 'selected' : '' }}>
                                    {{ $kl }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Search button --}}
                    <button type="submit"
                            class="px-6 py-2.5 rounded-xl bg-[#001734] text-white text-sm font-bold hover:bg-[#002a52] transition-colors flex-shrink-0 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
                        </svg>
                        Cari
                    </button>

                    {{-- Reset --}}
                    @if(request()->hasAny(['q','klasifikasi','frekuensi','tipe','sort']))
                        <a href="{{ route('landing.data.series') }}"
                           class="px-4 py-2.5 rounded-xl border border-gray-200 text-sm text-gray-500 hover:border-red-300 hover:text-red-500 transition-colors flex-shrink-0 flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Reset
                        </a>
                    @endif
                </div>

                {{-- Active filter chips --}}
                @if(request()->hasAny(['q','klasifikasi','frekuensi','tipe']))
                    <div class="flex flex-wrap gap-2 mt-4 pt-4 border-t border-gray-50">
                        <span class="text-xs text-gray-400 self-center">Filter aktif:</span>
                        @foreach(['q' => 'Kata kunci', 'klasifikasi' => 'Klasifikasi', 'frekuensi' => 'Frekuensi', 'tipe' => 'Tipe'] as $key => $label)
                            @if(request($key))
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#001734]/5 border border-[#001734]/10 text-xs font-semibold text-[#001734]">
                                    {{ $label }}: {{ request($key) }}
                                    <a href="{{ request()->fullUrlWithQuery([$key => null]) }}" class="hover:text-red-500 transition-colors" aria-label="Hapus filter {{ $label }}">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </a>
                                </span>
                            @endif
                        @endforeach
                    </div>
                @endif
            </form>
        </div>

        {{-- ── Result info ──────────────────────────────────────────────── --}}
        <div class="flex items-center justify-between mb-6">
            <p class="text-sm text-gray-500">
                Menampilkan
                <span class="font-bold text-[#001734]">{{ $metadataList->firstItem() }}–{{ $metadataList->lastItem() }}</span>
                dari
                <span class="font-bold text-[#001734]">{{ number_format($metadataList->total()) }}</span>
                dataset
                @if(request('q'))<span> untuk "<span class="font-semibold text-[#001734]">{{ request('q') }}</span>"</span>@endif
            </p>

            {{-- View toggle --}}
            <div class="flex items-center gap-1 bg-gray-100 rounded-xl p-1" x-data="{ view: localStorage.getItem('ds_view') || 'grid' }"
                 x-init="$watch('view', v => localStorage.setItem('ds_view', v))">
                <button @click="view = 'grid'"
                        :class="view === 'grid' ? 'bg-white shadow-sm text-[#001734]' : 'text-gray-400 hover:text-gray-600'"
                        class="p-1.5 rounded-lg transition-all duration-150"
                        aria-label="Tampilan grid">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                    </svg>
                </button>
                <button @click="view = 'list'"
                        :class="view === 'list' ? 'bg-white shadow-sm text-[#001734]' : 'text-gray-400 hover:text-gray-600'"
                        class="p-1.5 rounded-lg transition-all duration-150"
                        aria-label="Tampilan list">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <input type="hidden" id="ds-view-state" :value="view"/>
            </div>
        </div>

        {{-- ── Cards ──────────────────────────────────────────────────────── --}}
        @if($metadataList->isEmpty())
            <div class="text-center py-24">
                <div class="w-20 h-20 rounded-3xl bg-gray-100 flex items-center justify-center mx-auto mb-5">
                    <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-black text-[#001734] mb-2">Dataset tidak ditemukan</h3>
                <p class="text-gray-400 text-sm mb-6">Coba ubah kata kunci atau filter yang digunakan.</p>
                <a href="{{ route('landing.data.series') }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#001734] text-white text-sm font-bold hover:bg-[#002a52] transition-colors">
                    Tampilkan semua dataset
                </a>
            </div>
        @else

            {{-- ── Freemium banner (guest only, hanya tampil jika ada data terkunci di halaman ini) ── --}}
            @guest
                @if($freeCountOnPage < $metadataList->count())
                    <div class="mb-6 flex items-center gap-3 px-4 py-3 rounded-xl bg-amber-50 border border-amber-200">
                        <svg class="w-4 h-4 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        <p class="text-xs text-amber-700">
                            Anda melihat <strong>{{ $freeCountOnPage }} dari {{ $metadataList->count() }}</strong> dataset di halaman ini secara gratis.
                            <a href="{{ route('langganan') }}" class="font-bold underline hover:text-amber-900 transition-colors">Berlangganan</a> untuk akses penuh ke semua <strong>{{ number_format($metadataList->total()) }}</strong> dataset.
                        </p>
                    </div>
                @endif
            @endguest

            {{-- ── Grid + List wrapper ──────────────────────────────────────── --}}
            <div x-data="{ view: localStorage.getItem('ds_view') || 'grid' }"
                 x-init="window.addEventListener('storage', () => { view = localStorage.getItem('ds_view') || 'grid'; })">

                {{-- ──────────────────────────────────────────────────────────── --}}
                {{-- GRID VIEW                                                    --}}
                {{-- ──────────────────────────────────────────────────────────── --}}
                <div x-show="view === 'grid'" class="relative">

                    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($metadataList as $i => $meta)
                            @php
                                // Locked jika guest DAN item ini melewati batas free di halaman ini
                                $isLocked = auth()->guest() && ($i + 1) > $freeCountOnPage;

                                // ── Sparkline (hanya hitung untuk card yang tidak terkunci) ──
                                $lastYear  = now()->year - 1;
                                $startYear = $lastYear - 4;
                                $years     = range($startYear, $lastYear);

                                if (!$isLocked) {
                                    $dataPoints = $meta->data()
                                        ->where('data.status', 1)
                                        ->join('time', 'data.time_id', '=', 'time.time_id')
                                        ->whereBetween('time.year', [$startYear, $lastYear])
                                        ->groupBy('time.year')
                                        ->orderBy('time.year')
                                        ->selectRaw('time.year, SUM(data.number_value) as nilai')
                                        ->pluck('nilai', 'year');

                                    $vals = collect($years)
                                        ->map(fn($y) => isset($dataPoints[$y]) ? (float) $dataPoints[$y] : null)
                                        ->filter()->values();

                                    $minV = $vals->min() ?: 0;
                                    $maxV = $vals->max() ?: 1;
                                    $n    = $vals->count();

                                    $pts = $vals->map(function ($v, $idx) use ($n, $minV, $maxV) {
                                        $x = ($idx / max($n - 1, 1)) * 300;
                                        $y = 84 - (($v - $minV) / max($maxV - $minV, 1)) * 72;
                                        return [round($x, 1), round($y, 1)];
                                    });

                                    $polyLine = $pts->map(fn($p) => "{$p[0]},{$p[1]}")->implode(' ');
                                    $areaPath = 'M0,90 L' . $pts->map(fn($p) => "{$p[0]},{$p[1]}")->implode(' L') . ' L300,90 Z';

                                    $firstVal = $vals->first() ?: 1;
                                    $lastVal  = $vals->last()  ?: 1;
                                    $diff     = $lastVal - $firstVal;
                                    $pct      = round(abs($diff / max(abs($firstVal), 1)) * 100, 1);
                                    $trend    = $diff >= 0 ? 'up' : 'down';
                                    $lastPt   = $pts->last();
                                } else {
                                    $n = 0;
                                }

                                $namaKlasifikasi = $meta->klasifikasi?->nama_klasifikasi ?? $meta->klasifikasi ?? '—';
                            @endphp

                            @if(!$isLocked)
                                {{-- ── FREE card ─────────────────────────────────────────── --}}
                                <div class="group bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden">

                                    {{-- Sparkline header --}}
                                    <div class="relative px-5 pt-5 pb-2 h-36 overflow-hidden" style="background: linear-gradient(135deg, #001734 0%, #002a52 100%);">
                                        <span class="absolute top-3 right-4 text-xs font-semibold" style="color: rgba(247,193,0,0.65);">
                                            {{ $meta->satuan_data }}
                                        </span>
                                        <svg viewBox="0 0 300 90" class="w-full h-20 mt-1" preserveAspectRatio="none" aria-hidden="true">
                                            <defs>
                                                <linearGradient id="gf-{{ $meta->metadata_id }}" x1="0" y1="0" x2="0" y2="1">
                                                    <stop offset="0%"   stop-color="#F7C100" stop-opacity="0.40"/>
                                                    <stop offset="100%" stop-color="#F7C100" stop-opacity="0.03"/>
                                                </linearGradient>
                                            </defs>
                                            @foreach([22, 45, 67] as $gy)
                                                <line x1="0" y1="{{ $gy }}" x2="300" y2="{{ $gy }}" stroke="white" stroke-opacity="0.06" stroke-width="1"/>
                                            @endforeach
                                            @if($n >= 2)
                                                <path d="{{ $areaPath }}" fill="url(#gf-{{ $meta->metadata_id }})"/>
                                                <polyline points="{{ $polyLine }}" fill="none" stroke="#F7C100" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                                                @if($lastPt)
                                                    <circle cx="{{ $lastPt[0] }}" cy="{{ $lastPt[1] }}" r="3.5" fill="#F7C100"/>
                                                @endif
                                            @else
                                                <text x="150" y="48" text-anchor="middle" font-size="11" fill="rgba(255,255,255,0.30)">Belum ada data</text>
                                            @endif
                                        </svg>
                                        <div class="flex justify-between px-0.5 mt-1">
                                            @foreach($years as $y)
                                                <span class="text-white/35" style="font-size:10px;">{{ $y }}</span>
                                            @endforeach
                                        </div>
                                        @if($n >= 2)
                                            <div class="absolute bottom-3 right-4 flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold
                                                        {{ $trend === 'up' ? 'bg-emerald-500/20 text-emerald-300' : 'bg-red-500/20 text-red-300' }}">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                    @if($trend === 'up')
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/>
                                                    @else
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                                                    @endif
                                                </svg>
                                                {{ $trend === 'up' ? '+' : '-' }}{{ $pct }}%
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Card body --}}
                                    <div class="p-5">
                                        <div class="flex items-start justify-between gap-2 mb-2">
                                            <h3 class="text-sm font-bold text-[#001734] leading-snug line-clamp-2 group-hover:text-[#F7C100] transition-colors duration-200">
                                                {{ $meta->nama }}
                                            </h3>
                                            <span class="flex-shrink-0 px-2 py-0.5 rounded-full bg-[#001734]/5 text-[#001734] text-xs font-semibold whitespace-nowrap">
                                                {{ $meta->frekuensi_penerbitan }}
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-2 mb-4">
                                            <span class="px-2 py-0.5 rounded-full bg-[#F7C100]/15 text-[#001734] text-xs font-semibold max-w-[140px] truncate">
                                                {{ $namaKlasifikasi }}
                                            </span>
                                            <span class="text-gray-400 text-xs whitespace-nowrap">sejak {{ $meta->tahun_mulai_data }}</span>
                                        </div>
                                        <div class="border-t border-gray-50 pt-4">
                                            <div class="flex items-center justify-between">
                                                <a href="{{ route('landing.data.show', $meta->metadata_id) }}"
                                                   class="flex items-center gap-1 text-xs font-bold text-[#001734] hover:text-[#F7C100] transition-colors">
                                                    Lihat Detail
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                    </svg>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            @else
                                {{-- ── LOCKED card ─────────────────────────────────────────── --}}
                                <div class="relative bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden select-none">

                                    {{-- Blurred sparkline header --}}
                                    <div class="relative px-5 pt-5 pb-2 h-36 overflow-hidden blur-sm" style="background: linear-gradient(135deg, #001734 0%, #002a52 100%);">
                                        <svg viewBox="0 0 300 90" class="w-full h-20 mt-1" preserveAspectRatio="none" aria-hidden="true">
                                            {{-- placeholder random-looking flat line --}}
                                            <polyline points="0,60 60,45 120,55 180,38 240,50 300,42"
                                                      fill="none" stroke="#F7C100" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" opacity="0.4"/>
                                        </svg>
                                        <div class="flex justify-between px-0.5 mt-1">
                                            @foreach($years as $y)
                                                <span class="text-white/35" style="font-size:10px;">{{ $y }}</span>
                                            @endforeach
                                        </div>
                                    </div>

                                    {{-- Blurred card body --}}
                                    <div class="p-5 blur-sm">
                                        <div class="flex items-start justify-between gap-2 mb-2">
                                            <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                                            <div class="h-4 bg-gray-100 rounded w-12 flex-shrink-0"></div>
                                        </div>
                                        <div class="flex items-center gap-2 mb-4">
                                            <div class="h-3.5 bg-amber-100 rounded-full w-20"></div>
                                            <div class="h-3.5 bg-gray-100 rounded w-16"></div>
                                        </div>
                                        <div class="border-t border-gray-50 pt-4">
                                            <div class="h-3.5 bg-gray-200 rounded w-20"></div>
                                        </div>
                                    </div>

                                    {{-- Lock overlay --}}
                                    <div class="absolute inset-0 flex flex-col items-center justify-center bg-white/60 backdrop-blur-[2px]">
                                        <div class="w-10 h-10 rounded-xl bg-[#001734] flex items-center justify-center mb-2 shadow-lg">
                                            <svg class="w-5 h-5 text-[#F7C100]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                            </svg>
                                        </div>
                                        <p class="text-xs font-bold text-[#001734]">Dataset Premium</p>
                                    </div>
                                </div>
                            @endif

                        @endforeach
                    </div>

                    {{-- ── PAYWALL OVERLAY — muncul di atas grid jika ada card terkunci ── --}}
                    @guest
                        @if($freeCountOnPage < $metadataList->count())
                            <div class="absolute bottom-0 left-0 right-0 z-10"
                                 style="height: 72%;">
                                {{-- fade gradient --}}
                                <div class="absolute inset-0 pointer-events-none"
                                     style="background: linear-gradient(to bottom, transparent 0%, #f8fafc 32%, #f8fafc 100%);"></div>

                                {{-- CTA card --}}
                                <div class="absolute bottom-0 left-0 right-0 px-4 pb-2">
                                    <div class="max-w-xl mx-auto bg-white rounded-3xl border border-gray-100 shadow-2xl overflow-hidden">

                                        {{-- Top accent bar --}}
                                        <div class="h-1.5 bg-gradient-to-r from-[#001734] via-[#F7C100] to-[#001734]"></div>

                                        <div class="p-7 sm:p-9 text-center">
                                            {{-- Icon --}}
                                            <div class="w-16 h-16 rounded-2xl bg-[#001734] flex items-center justify-center mx-auto mb-5 shadow-lg">
                                                <svg class="w-8 h-8 text-[#F7C100]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                                </svg>
                                            </div>

                                            <h3 class="text-xl sm:text-2xl font-black text-[#001734] mb-2 leading-tight">
                                                Akses Penuh ke Semua Dataset
                                            </h3>
                                            <p class="text-gray-500 text-sm mb-7 max-w-sm mx-auto leading-relaxed">
                                                Anda sedang melihat <strong class="text-[#001734]">30% data gratis</strong>.
                                                Berlangganan untuk menjelajahi seluruh
                                                <strong class="text-[#001734]">{{ number_format($metadataList->total()) }} dataset</strong>.
                                            </p>

                                            {{-- CTA buttons --}}
                                            <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                                                <a href="{{ route('langganan') }}"
                                                   class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-3.5 rounded-2xl bg-[#001734] text-white font-black text-sm hover:bg-[#002a52] transition-colors shadow-lg">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                              d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                                    </svg>
                                                    Berlangganan Sekarang
                                                </a>
                                                <a href="{{ route('login') }}"
                                                   class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-2xl border border-gray-200 text-gray-600 font-bold text-sm hover:border-[#001734] hover:text-[#001734] transition-colors">
                                                    Sudah punya akun? Masuk
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endguest

                </div>{{-- /grid --}}

                {{-- ──────────────────────────────────────────────────────────── --}}
                {{-- LIST VIEW                                                    --}}
                {{-- ──────────────────────────────────────────────────────────── --}}
                <div x-show="view === 'list'" class="relative space-y-3">

                    @foreach($metadataList as $i => $meta)
                        @php
                            $isLocked        = auth()->guest() && ($i + 1) > $freeCountOnPage;
                            $namaKlasifikasi = $meta->klasifikasi?->nama_klasifikasi ?? $meta->klasifikasi ?? '—';
                        @endphp

                        @if(!$isLocked)
                            {{-- ── FREE list row ──────────────────────────────────────── --}}
                            <div class="group bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md hover:border-[#F7C100]/30 transition-all duration-200 overflow-hidden">
                                <div class="flex items-center gap-4 p-4 sm:p-5">
                                    <div class="w-12 h-12 rounded-xl bg-[#001734] flex items-center justify-center flex-shrink-0 group-hover:bg-[#F7C100] transition-colors duration-300">
                                        <svg class="w-6 h-6 text-[#F7C100] group-hover:text-[#001734] transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-start gap-3 flex-wrap">
                                            <h3 class="text-sm font-bold text-[#001734] group-hover:text-[#F7C100] transition-colors line-clamp-1">
                                                {{ $meta->nama }}
                                            </h3>
                                            <div class="flex items-center gap-2 flex-wrap mt-0.5">
                                                <span class="px-2 py-0.5 rounded-full bg-[#F7C100]/15 text-[#001734] text-xs font-semibold">
                                                    {{ $namaKlasifikasi }}
                                                </span>
                                                <span class="px-2 py-0.5 rounded-full bg-[#001734]/5 text-[#001734] text-xs font-semibold">
                                                    {{ $meta->frekuensi_penerbitan }}
                                                </span>
                                                <span class="text-gray-400 text-xs">{{ $meta->satuan_data }}</span>
                                                <span class="text-gray-300 text-xs">·</span>
                                                <span class="text-gray-400 text-xs">sejak {{ $meta->tahun_mulai_data }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 shrink-0">
                                        <a href="{{ route('landing.data.show', $meta->metadata_id) }}"
                                           class="flex items-center gap-1.5 px-4 py-2 rounded-xl bg-[#001734] text-white text-xs font-bold hover:bg-[#002a52] transition-colors">
                                            Detail
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            </div>

                        @else
                            {{-- ── LOCKED list row ─────────────────────────────────────── --}}
                            <div class="relative bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden select-none">
                                {{-- Blurred row --}}
                                <div class="flex items-center gap-4 p-4 sm:p-5 blur-sm pointer-events-none">
                                    <div class="w-12 h-12 rounded-xl bg-gray-200 flex-shrink-0"></div>
                                    <div class="flex-1 min-w-0 space-y-2">
                                        <div class="h-4 bg-gray-200 rounded w-2/3"></div>
                                        <div class="flex gap-2">
                                            <div class="h-3 bg-amber-100 rounded-full w-20"></div>
                                            <div class="h-3 bg-gray-100 rounded-full w-16"></div>
                                            <div class="h-3 bg-gray-100 rounded w-12"></div>
                                        </div>
                                    </div>
                                    <div class="h-8 w-16 bg-gray-200 rounded-xl flex-shrink-0"></div>
                                </div>
                                {{-- Lock badge --}}
                                <div class="absolute inset-0 flex items-center justify-center bg-white/50 backdrop-blur-[1px]">
                                    <div class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-[#001734] shadow-md">
                                        <svg class="w-3.5 h-3.5 text-[#F7C100]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                        </svg>
                                        <span class="text-xs font-bold text-white">Premium</span>
                                    </div>
                                </div>
                            </div>
                        @endif

                    @endforeach

                    {{-- ── PAYWALL OVERLAY untuk list view ── --}}
                    @guest
                        @if($freeCountOnPage < $metadataList->count())
                            <div class="absolute bottom-0 left-0 right-0 z-10"
                                 style="height: 72%;">
                                <div class="absolute inset-0 pointer-events-none"
                                     style="background: linear-gradient(to bottom, transparent 0%, #f8fafc 32%, #f8fafc 100%);"></div>
                                <div class="absolute bottom-0 left-0 right-0 px-4 pb-2">
                                    <div class="max-w-xl mx-auto bg-white rounded-3xl border border-gray-100 shadow-2xl overflow-hidden">
                                        <div class="h-1.5 bg-gradient-to-r from-[#001734] via-[#F7C100] to-[#001734]"></div>
                                        <div class="p-7 sm:p-9 text-center">
                                            <div class="w-16 h-16 rounded-2xl bg-[#001734] flex items-center justify-center mx-auto mb-5 shadow-lg">
                                                <svg class="w-8 h-8 text-[#F7C100]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                                </svg>
                                            </div>
                                            <h3 class="text-xl sm:text-2xl font-black text-[#001734] mb-2 leading-tight">
                                                Akses Penuh ke Semua Dataset
                                            </h3>
                                            <p class="text-gray-500 text-sm mb-7 max-w-sm mx-auto leading-relaxed">
                                                Anda sedang melihat <strong class="text-[#001734]">30% data gratis</strong>.
                                                Berlangganan untuk menjelajahi seluruh
                                                <strong class="text-[#001734]">{{ number_format($metadataList->total()) }} dataset</strong>
                                                dan mengunduh dalam format Excel, PDF, dan JSON.
                                            </p>
                                            <div class="flex items-center justify-center gap-4 sm:gap-8 mb-7">
                                                <div class="text-center">
                                                    <div class="text-2xl font-black text-[#001734]">{{ number_format($freeLimit) }}</div>
                                                    <div class="text-xs text-gray-400 mt-0.5">Dataset gratis</div>
                                                </div>
                                                <div class="w-px h-10 bg-gray-100"></div>
                                                <div class="text-center">
                                                    <div class="text-2xl font-black text-[#F7C100]">{{ number_format(max(0, $metadataList->total() - $freeLimit)) }}</div>
                                                    <div class="text-xs text-gray-400 mt-0.5">Dataset premium</div>
                                                </div>
                                                <div class="w-px h-10 bg-gray-100"></div>
                                                <div class="text-center">
                                                    <div class="text-2xl font-black text-[#001734]">3</div>
                                                    <div class="text-xs text-gray-400 mt-0.5">Format ekspor</div>
                                                </div>
                                            </div>
                                            <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                                                <a href="{{ route('langganan') }}"
                                                   class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-3.5 rounded-2xl bg-[#001734] text-white font-black text-sm hover:bg-[#002a52] transition-colors shadow-lg">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                              d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                                    </svg>
                                                    Berlangganan Sekarang
                                                </a>
                                                <a href="{{ route('login') }}"
                                                   class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-2xl border border-gray-200 text-gray-600 font-bold text-sm hover:border-[#001734] hover:text-[#001734] transition-colors">
                                                    Sudah punya akun? Masuk
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endguest

                </div>{{-- /list --}}

            </div>{{-- /x-data view wrapper --}}

            {{-- ── Pagination ──────────────────────────────────────────────────── --}}
            {{--
                Guest di halaman 1 dengan paywall aktif: sembunyikan pagination
                agar user tidak bisa loncat ke halaman 2 (semua terkunci di sana).
                Di semua kondisi lain (login, atau halaman berikutnya): tampilkan.
            --}}
            @php
                $showPagination = auth()->check()
                    || $metadataList->currentPage() > 1
                    || $freeCountOnPage >= $metadataList->count();
            @endphp

            @if($metadataList->hasPages() && $showPagination)
                <div class="mt-10 flex flex-col sm:flex-row items-center justify-between gap-4">

                    <p class="text-sm text-gray-500 order-2 sm:order-1">
                        Halaman <span class="font-bold text-[#001734]">{{ $metadataList->currentPage() }}</span>
                        dari <span class="font-bold text-[#001734]">{{ $metadataList->lastPage() }}</span>
                    </p>

                    <nav class="flex items-center gap-1 order-1 sm:order-2" aria-label="Paginasi">

                        @if($metadataList->onFirstPage())
                            <span class="w-9 h-9 flex items-center justify-center rounded-xl text-gray-300 cursor-not-allowed" aria-disabled="true">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                </svg>
                            </span>
                        @else
                            <a href="{{ $metadataList->previousPageUrl() }}&{{ http_build_query(request()->except('page')) }}"
                               class="w-9 h-9 flex items-center justify-center rounded-xl border border-gray-200 text-gray-500 hover:border-[#001734] hover:text-[#001734] transition-colors"
                               aria-label="Halaman sebelumnya">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                </svg>
                            </a>
                        @endif

                        @php
                            $window  = 2;
                            $current = $metadataList->currentPage();
                            $last    = $metadataList->lastPage();
                            $start   = max(1, $current - $window);
                            $end     = min($last, $current + $window);
                        @endphp

                        @if($start > 1)
                            <a href="{{ $metadataList->url(1) }}&{{ http_build_query(request()->except('page')) }}"
                               class="w-9 h-9 flex items-center justify-center rounded-xl border border-gray-200 text-sm text-gray-600 hover:border-[#001734] hover:text-[#001734] transition-colors">1</a>
                            @if($start > 2)
                                <span class="w-9 h-9 flex items-center justify-center text-gray-400 text-sm">…</span>
                            @endif
                        @endif

                        @for($p = $start; $p <= $end; $p++)
                            @if($p === $current)
                                <span class="w-9 h-9 flex items-center justify-center rounded-xl bg-[#001734] text-white text-sm font-bold" aria-current="page">{{ $p }}</span>
                            @else
                                <a href="{{ $metadataList->url($p) }}&{{ http_build_query(request()->except('page')) }}"
                                   class="w-9 h-9 flex items-center justify-center rounded-xl border border-gray-200 text-sm text-gray-600 hover:border-[#001734] hover:text-[#001734] transition-colors">{{ $p }}</a>
                            @endif
                        @endfor

                        @if($end < $last)
                            @if($end < $last - 1)
                                <span class="w-9 h-9 flex items-center justify-center text-gray-400 text-sm">…</span>
                            @endif
                            <a href="{{ $metadataList->url($last) }}&{{ http_build_query(request()->except('page')) }}"
                               class="w-9 h-9 flex items-center justify-center rounded-xl border border-gray-200 text-sm text-gray-600 hover:border-[#001734] hover:text-[#001734] transition-colors">{{ $last }}</a>
                        @endif

                        @if($metadataList->hasMorePages())
                            <a href="{{ $metadataList->nextPageUrl() }}&{{ http_build_query(request()->except('page')) }}"
                               class="w-9 h-9 flex items-center justify-center rounded-xl border border-gray-200 text-gray-500 hover:border-[#001734] hover:text-[#001734] transition-colors"
                               aria-label="Halaman berikutnya">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        @else
                            <span class="w-9 h-9 flex items-center justify-center rounded-xl text-gray-300 cursor-not-allowed" aria-disabled="true">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </span>
                        @endif

                    </nav>

                    <div class="flex items-center gap-2 order-3 text-sm text-gray-500">
                        <span>Tampilkan</span>
                        <select onchange="window.location.href = '{{ route('landing.data.series') }}?' + new URLSearchParams({...Object.fromEntries(new URLSearchParams(window.location.search)), per_page: this.value, page: 1}).toString()"
                                class="px-3 py-1.5 rounded-xl border border-gray-200 text-sm text-gray-700 outline-none focus:border-[#001734] bg-white cursor-pointer"
                                aria-label="Jumlah per halaman">
                            @foreach([12, 24, 48] as $pp)
                                <option value="{{ $pp }}" {{ request('per_page', 12) == $pp ? 'selected' : '' }}>
                                    {{ $pp }}
                                </option>
                            @endforeach
                        </select>
                        <span>per halaman</span>
                    </div>

                </div>
            @endif

        @endif

    </main>

    @include('pages.landing.components.footer')

    {{-- Back to top --}}
    <button id="back-to-top"
            onclick="window.scrollTo({ top: 0, behavior: 'smooth' })"
            class="fixed bottom-6 right-6 z-50 w-12 h-12 rounded-2xl bg-[#001734] text-[#F7C100] shadow-xl flex items-center justify-center opacity-0 translate-y-4 pointer-events-none transition-all duration-300 hover:bg-[#002a52] hover:scale-110 focus:outline-none focus:ring-2 focus:ring-[#F7C100] focus:ring-offset-2"
            aria-label="Kembali ke atas">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
        </svg>
    </button>

    <script>
        new TomSelect('#select-klasifikasi', {
            placeholder: 'Semua Klasifikasi',
            allowEmptyOption: true,
            onchange: function() {
                document.getElementById('filter-form').submit();
            }
        });
        </script>
    <script>
    // Back to top
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

    function filterBar() {
        return {
            syncFromUrl() {
                // no-op: values come from server-rendered value= attrs
            }
        }
    }
    </script>

</body>
</html>