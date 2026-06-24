<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>{{ $metadata->nama }}</title>
    <meta name="description" content="Data statistik {{ $metadata->nama }} periode {{ $yearStart }}–{{ $yearEnd }}."/>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon"/>
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    @vite(['resources/css/app.css', 'resources/js/app.ts'])
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }

        /* ── Custom scrollbar ── */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #1a56db; }

        /* ── Hero: tetap dark navy ── */
        .hero-bg {
            background: linear-gradient(135deg, #060d1f 0%, #0d1a35 50%, #0a1628 100%);
            position: relative;
        }
        .hero-bg::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(ellipse 55% 70% at 85% 30%, rgba(234,179,8,.07) 0%, transparent 60%),
                radial-gradient(ellipse 40% 50% at 15% 80%, rgba(26,86,219,.10) 0%, transparent 55%);
            pointer-events: none;
        }
        .hero-bg::after {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,.025) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.025) 1px, transparent 1px);
            background-size: 48px 48px;
            pointer-events: none;
        }

        /* ── Stat cards — light ── */
        .stat-card {
            border-left: 3px solid #1a56db;
            transition: transform .2s, box-shadow .2s;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(26,86,219,.10);
        }

        /* ── Table row hover ── */
        .data-row { transition: background .15s; }
        .data-row:hover { background: #eff6ff; }

        /* ── Tab active indicator ── */
        .tab-btn {
            position: relative;
            transition: color .2s;
        }
        .tab-btn::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 3px;
            background: #eab308;
            transform: scaleX(0);
            transition: transform .25s;
        }
        .tab-btn.active::after { transform: scaleX(1); }

        /* ── Chart toggle ── */
        .chart-btn { transition: background .15s, color .15s, border-color .15s; }
        .chart-btn.active {
            background: #1a56db;
            border-color: #1a56db;
            color: #fff;
        }

        /* ── Animations ── */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .fade-up { animation: fadeUp .45s ease both; }
        .d1 { animation-delay: .06s; }
        .d2 { animation-delay: .12s; }
        .d3 { animation-delay: .18s; }
        .d4 { animation-delay: .24s; }

        /* ── Gate modal ── */
        .gate-overlay {
            position: fixed; inset: 0;
            background: rgba(6,13,31,.75);
            backdrop-filter: blur(6px);
            z-index: 9000;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            opacity: 0;
            pointer-events: none;
            transition: opacity .25s;
        }
        .gate-overlay.open { opacity: 1; pointer-events: all; }
        .gate-modal {
            transform: translateY(20px);
            transition: transform .3s;
        }
        .gate-overlay.open .gate-modal { transform: translateY(0); }

        /* ── Period chip ── */
        .period-chip {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: .7rem;
            font-weight: 600;
            background: #eff6ff;
            color: #1a56db;
            border: 1px solid #bfdbfe;
            padding: 2px 8px;
            border-radius: 3px;
            white-space: nowrap;
        }

        /* ── Info card ── */
        .info-card { border-top: 2px solid #eab308; }

        /* ── Badge ── */
        .klasifikasi-badge {
            background: #eab308;
            color: #060d1f;
            font-size: .65rem;
            font-weight: 800;
            letter-spacing: .1em;
            text-transform: uppercase;
            padding: .28rem .7rem;
            border-radius: 2px;
        }

        /* ── Section divider accent ── */
        .section-label {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .section-label::before {
            content: '';
            width: 4px;
            height: 16px;
            background: #1a56db;
            border-radius: 2px;
            display: inline-block;
            flex-shrink: 0;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800">

    @include('pages.landing.components.navbar')

    {{-- ════════════════ HERO (tetap dark) ════════════════ --}}
    <section class="hero-bg relative z-0">
        <div class="max-w-6xl mx-auto px-6 pt-24 pb-0 relative z-10">

            {{-- Breadcrumb --}}
            <nav class="flex items-center gap-1.5 text-xs text-slate-500 mb-5 flex-wrap fade-up">
                <a href="{{ route('landing') }}" class="hover:text-slate-300 transition-colors">Beranda</a>
                <span class="text-slate-700">›</span>
                <a href="{{ route('klasifikasi.index') }}" class="hover:text-slate-300 transition-colors">Klasifikasi</a>
                <span class="text-slate-700">›</span>
                <a href="{{ route('klasifikasi.show', Str::slug($metadata->klasifikasi?->nama_klasifikasi ?? '')) }}"
                   class="hover:text-slate-300 transition-colors">
                    {{ $metadata->klasifikasi?->nama_klasifikasi ?? 'Klasifikasi' }}
                </a>
                <span class="text-slate-700">›</span>
                <span class="text-slate-400">{{ Str::limit($metadata->nama, 50) }}</span>
            </nav>

            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
                <div class="flex-1 min-w-0">
                    <div class="mb-3 fade-up d1">
                        <span class="klasifikasi-badge">
                            ■ {{ $metadata->klasifikasi?->nama_klasifikasi ?? 'Data Statistik' }}
                        </span>
                    </div>
                    @php $wilayah = $metadata->data->where('location_id', 0)->first()?->location?->nama_wilayah; @endphp
                    <h1 class="text-3xl lg:text-4xl font-extrabold text-white leading-tight mb-4 max-w-2xl fade-up d2">
                        {{ $metadata->nama }}
                        @if($wilayah)
                            di {{ $wilayah }}
                        @endif
                    </h1>
                    <div class="flex flex-wrap gap-2 mb-6 fade-up d3">
                        @if($metadata->satuan_data)
                        <span class="inline-flex items-center gap-1.5 text-xs text-slate-400 bg-white/5 border border-white/10 px-3 py-1.5 rounded-sm">
                            <svg class="w-3 h-3 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                            Satuan: {{ $metadata->satuan_data }}
                        </span>
                        @endif
                        @if($metadata->frekuensi_penerbitan)
                        <span class="inline-flex items-center gap-1.5 text-xs text-slate-400 bg-white/5 border border-white/10 px-3 py-1.5 rounded-sm">
                            <svg class="w-3 h-3 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ $metadata->frekuensi_penerbitan }}
                        </span>
                        @endif
                        @if($yearStart)
                        <span class="inline-flex items-center gap-1.5 text-xs text-slate-400 bg-white/5 border border-white/10 px-3 py-1.5 rounded-sm">
                            <svg class="w-3 h-3 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            Sejak {{ $yearStart }}
                        </span>
                        @endif
                        <span class="inline-flex items-center gap-1.5 text-xs text-slate-400 bg-white/5 border border-white/10 px-3 py-1.5 rounded-sm">
                            <svg class="w-3 h-3 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            {{ $rujukan?->nama_rujukan ?? $metadata->produsen?->nama_produsen ?? 'Sumber tidak tersedia' }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Tabs --}}
            <nav class="flex mt-2 border-b border-white/10 fade-up d4" role="tablist">
                <button class="tab-btn active text-white text-sm font-700 uppercase tracking-wider px-5 py-3.5"
                        data-tab="grafik" role="tab">Grafik &amp; Tabel</button>
                <button class="tab-btn text-slate-500 text-sm font-700 uppercase tracking-wider px-5 py-3.5 hover:text-slate-300"
                        data-tab="info" role="tab">Informasi Tentang Data</button>
            </nav>
        </div>
    </section>

    {{-- ════════════════ BODY (light) ════════════════ --}}
    <div class="max-w-6xl mx-auto px-6 py-8 pb-16">

        {{-- ── TAB: GRAFIK & TABEL ── --}}
        <div class="tab-panel" id="panel-grafik">

            @php
                $values    = $tableRows->pluck('value')->filter(fn($v) => !is_null($v));
                $latestRow = $tableRows->sortByDesc('year')->first();
                $dec       = $metadata->flag_desimal ?? 0;
            @endphp

            {{-- Chart Card --}}
            <div class="bg-white border border-slate-200 rounded mb-6 p-6 shadow-sm fade-up d2">
                <div class="flex items-start justify-between flex-wrap gap-3 mb-5">
                    <div>
                        <div class="section-label mb-1">
                            <span class="text-xs font-800 uppercase tracking-widest text-slate-700">{{ $metadata->nama }}</span>
                        </div>
                        <div class="text-xs text-slate-400 pl-4">
                            Periode {{ $yearStart }}–{{ $yearEnd }} &nbsp;·&nbsp; Satuan: {{ $metadata->satuan_data }}
                        </div>
                    </div>
                    <div class="flex gap-1">
                        <button id="btn-bar" onclick="setChartType('bar')"
                                class="chart-btn active inline-flex items-center gap-1.5 text-xs font-600 border border-slate-200 text-slate-500 px-3 py-1.5 rounded-sm">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            Bar
                        </button>
                        <button id="btn-line" onclick="setChartType('line')"
                                class="chart-btn inline-flex items-center gap-1.5 text-xs font-600 border border-slate-200 text-slate-500 px-3 py-1.5 rounded-sm">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
                            Line
                        </button>
                    </div>
                </div>
                <div class="relative h-72">
                    <canvas id="mainChart"></canvas>
                </div>
            </div>

            {{-- Data Table --}}
            <div class="grid grid-cols-1 gap-3">
                <div class="bg-white border border-slate-200 rounded overflow-hidden shadow-sm fade-up d3">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-slate-50 flex-wrap gap-3">
                        <div class="section-label">
                            <span class="text-xs font-800 uppercase tracking-widest text-slate-700">
                                Data Periode {{ $yearStart }}–{{ $yearEnd }}
                            </span>
                        </div>
                        <span class="inline-flex items-center gap-1.5 text-xs text-slate-400 border border-slate-200 bg-white px-3 py-1 rounded-sm">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            Sumber: {{ $rujukan?->produsen?->nama_produsen ?? $metadata->produsen?->nama_produsen ?? 'Tidak diketahui' }}
                        </span>
                    </div>
                    <div class="hidden md:block">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-[#0d1a35]">
                                    <th class="text-left px-6 py-3 text-xs font-700 uppercase tracking-widest text-slate-300">Nama Data</th>
                                    <th class="text-left px-6 py-3 text-xs font-700 uppercase tracking-widest text-slate-300">Periode</th>
                                    <th class="text-right px-6 py-3 text-xs font-700 uppercase tracking-widest text-yellow-400">{{ $metadata->satuan_data }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tableRows as $row)
                                <tr class="data-row border-b border-slate-100 last:border-b-0">
                                    <td class="px-6 py-4 text-slate-700 font-500">{{ $metadata->nama }}</td>
                                    <td class="px-6 py-4">
                                        <span class="period-chip">
                                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            {{ $row['period'] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right font-700 font-mono text-slate-800 tabular-nums">
                                        @if(!is_null($row['value']))
                                            {{ number_format((float)$row['value'], $dec, ',', '.') }}
                                        @else
                                            <span class="text-slate-300 font-400">—</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-12 text-center">
                                        <svg class="w-10 h-10 mx-auto mb-3 opacity-25 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.4" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <p class="text-slate-400 text-sm">Data untuk periode {{ $yearStart }}–{{ $yearEnd }} belum tersedia.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="space-y-4 p-5 md:hidden">
                        @foreach($tableRows as $row)
                        <div class="bg-white rounded-xl border p-5">
                            <div class="text-slate-500 text-xs uppercase">
                                Nama Data
                            </div>
                            <div class="font-semibold text-slate-700">
                                {{ $metadata->nama }}
                            </div>
                            <div class="mt-4 flex justify-between">
                                <div>
                                    <div class="text-xs text-slate-400">
                                        Periode
                                    </div>
                                    <span class="period-chip">
                                        {{ $row['period'] }}
                                    </span>
                                </div>
                                <div class="text-right">
                                    <div class="text-xs text-slate-400">
                                        {{ $metadata->satuan_data }}
                                    </div>
                                    <div class="font-bold font-mono text-lg">
                                        {{ number_format((float)$row['value'], $dec, ',', '.') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div class="flex items-center justify-between px-6 py-3.5 border-t border-slate-100 bg-slate-50 flex-wrap gap-3">
                        <span class="text-xs text-slate-400">
                            Menampilkan {{ count($tableRows) }} baris · Data lengkap tersedia untuk pelanggan
                        </span>
                        <button onclick="showGate()"
                                class="inline-flex items-center gap-1.5 text-xs font-700 text-blue-600 hover:text-blue-800 transition-colors uppercase tracking-wider">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            Akses Data Lengkap →
                        </button>
                    </div>
                </div>
            </div>

        </div>{{-- /panel-grafik --}}

        {{-- ── TAB: INFORMASI METADATA ── --}}
        <div class="tab-panel hidden" id="panel-info">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 fade-up">

                <div class="info-card bg-white border border-slate-200 rounded p-5 shadow-sm md:col-span-2">
                    <div class="text-xs font-800 uppercase tracking-widest text-slate-400 mb-2">Konsep</div>
                    <div class="text-sm text-slate-700 leading-relaxed">{{ $metadata->konsep }}</div>
                </div>

                <div class="info-card bg-white border border-slate-200 rounded p-5 shadow-sm md:col-span-2">
                    <div class="text-xs font-800 uppercase tracking-widest text-slate-400 mb-2">Definisi</div>
                    <div class="text-sm text-slate-700 leading-relaxed">{{ $metadata->definisi }}</div>
                </div>

                <div class="info-card bg-white border border-slate-200 rounded p-5 shadow-sm">
                    <div class="text-xs font-800 uppercase tracking-widest text-slate-400 mb-2">Metodologi</div>
                    <div class="text-sm text-slate-700 leading-relaxed">{{ $metadata->metodologi }}</div>
                </div>

                <div class="info-card bg-white border border-slate-200 rounded p-5 shadow-sm">
                    <div class="text-xs font-800 uppercase tracking-widest text-slate-400 mb-2">Penjelasan Metodologi</div>
                    <div class="text-sm text-slate-700 leading-relaxed">{{ $metadata->penjelasan_metodologi }}</div>
                </div>

                @if($metadata->asumsi)
                <div class="info-card bg-white border border-slate-200 rounded p-5 shadow-sm md:col-span-2">
                    <div class="text-xs font-800 uppercase tracking-widest text-slate-400 mb-2">Asumsi</div>
                    <div class="text-sm text-slate-700 leading-relaxed">{{ $metadata->asumsi }}</div>
                </div>
                @endif

                <div class="info-card bg-white border border-slate-200 rounded p-5 shadow-sm">
                    <div class="text-xs font-800 uppercase tracking-widest text-slate-400 mb-2">Tipe Data</div>
                    <div class="text-sm text-slate-700">{{ $metadata->tipe_data }}</div>
                </div>

                <div class="info-card bg-white border border-slate-200 rounded p-5 shadow-sm">
                    <div class="text-xs font-800 uppercase tracking-widest text-slate-400 mb-2">Satuan Data</div>
                    <div class="text-sm text-slate-700">{{ $metadata->satuan_data }}</div>
                </div>

                <div class="info-card bg-white border border-slate-200 rounded p-5 shadow-sm">
                    <div class="text-xs font-800 uppercase tracking-widest text-slate-400 mb-2">Frekuensi Penerbitan</div>
                    <div class="text-sm text-slate-700">{{ $metadata->frekuensi_penerbitan }}</div>
                </div>

                <div class="info-card bg-white border border-slate-200 rounded p-5 shadow-sm">
                    <div class="text-xs font-800 uppercase tracking-widest text-slate-400 mb-2">Tahun Mulai Data</div>
                    <div class="text-sm text-slate-700">{{ $yearStart ?? '-' }}</div>
                </div>

                <div class="info-card bg-white border border-slate-200 rounded p-5 shadow-sm">
                    <div class="text-xs font-800 uppercase tracking-widest text-slate-400 mb-2">Tahun Data Tersedia</div>
                    <div class="text-sm text-slate-700">
                        @if($yearStart && $yearEnd)
                            {{ $yearStart == $yearEnd ? $yearStart : $yearStart . '–' . $yearEnd }}
                        @else
                            —
                        @endif
                    </div>
                </div>

                <div class="info-card bg-white border border-slate-200 rounded p-5 shadow-sm">
                    <div class="text-xs font-800 uppercase tracking-widest text-slate-400 mb-2">Sumber Data</div>
                    <div class="text-sm text-slate-700">
                        {{ $rujukan?->nama_rujukan ?? '—' }}
                        ({{ $rujukan?->produsen?->nama_produsen ?? $metadata->produsen?->nama_produsen ?? '—' }})
                    </div>
                </div>

            </div>
        </div>

    </div>

    {{-- ════════════════ GATE MODAL ════════════════ --}}
    <div class="gate-overlay" id="subscribeGate" role="dialog" aria-modal="true" aria-labelledby="gateTitle">
        <div class="gate-modal bg-white rounded-lg w-full max-w-md p-8 text-center shadow-2xl border border-slate-100">
            <div class="w-14 h-14 rounded-full bg-blue-50 border border-blue-100 flex items-center justify-center mx-auto mb-4">
                <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <h2 class="text-xl font-800 text-slate-800 mb-2" id="gateTitle">Data Lengkap Tersedia</h2>
            <p class="text-sm text-slate-500 leading-relaxed mb-6">
                Anda sedang melihat pratinjau data publik.<br/>
                Berlangganan untuk mengakses seluruh dataset.
            </p>
            <a href="{{ route('langganan') }}"
               class="block w-full bg-[#0d1a35] hover:bg-[#1a56db] text-white font-700 text-sm py-3 rounded transition-colors mb-2">
                Lihat Paket Langganan
            </a>
            <button onclick="closeGate()" class="text-xs text-slate-400 hover:text-slate-600 transition-colors">
                <- Lanjutkan lihat pratinjau
            </button>
        </div>
    </div>

    {{-- ════════════════ SCRIPTS ════════════════ --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
    <script>
    const chartLabels = @json($chartLabels);
    const chartValues = @json($chartValues);
    const satuan      = @json($metadata->satuan_data);
    const flagDesimal = {{ $metadata->flag_desimal ?? 0 }};

    const C_BLUE      = '#1a56db';
    const C_BLUE_BAR  = 'rgba(26,86,219,.75)';
    const C_BLUE_FILL = 'rgba(26,86,219,.08)';
    const C_YELLOW    = '#eab308';
    const GRID_COLOR  = 'rgba(0,0,0,.05)';
    const TICK_COLOR  = '#94a3b8';

    Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";

    let mainChart;

    function fmt(v) {
        if (v === null || v === undefined) return '—';
        return new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: flagDesimal,
            maximumFractionDigits: flagDesimal,
        }).format(v);
    }

    function buildDataset(type) {
        const lastIdx = chartValues.map((v,i) => v !== null ? i : -1).filter(i => i >= 0).pop();
        if (type === 'bar') return {
            label: satuan,
            data: chartValues,
            backgroundColor: chartValues.map((_, i) => i === lastIdx ? C_YELLOW : C_BLUE_BAR),
            borderColor: 'transparent',
            borderWidth: 0,
            borderRadius: 5,
            borderSkipped: false,
        };
        return {
            label: satuan,
            data: chartValues,
            borderColor: C_BLUE,
            backgroundColor: C_BLUE_FILL,
            borderWidth: 2.5,
            pointBackgroundColor: C_BLUE,
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 5,
            pointHoverRadius: 7,
            tension: 0.35,
            fill: true,
        };
    }

    function initChart(type) {
        const ctx = document.getElementById('mainChart').getContext('2d');
        if (mainChart) mainChart.destroy();
        mainChart = new Chart(ctx, {
            type,
            data: { labels: chartLabels, datasets: [buildDataset(type)] },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0d1a35',
                        borderColor: 'rgba(255,255,255,.1)',
                        borderWidth: 1,
                        titleColor: '#94a3b8',
                        bodyColor: '#fff',
                        padding: 12,
                        cornerRadius: 4,
                        displayColors: false,
                        callbacks: {
                            title: items => items[0].label,
                            label: c => '  ' + fmt(c.raw) + ' ' + satuan,
                        },
                    },
                },
                scales: {
                    x: {
                        grid: { display: false },
                        border: { color: '#e2e8f0' },
                        ticks: { color: TICK_COLOR, font: { size: 11, weight: '600' } },
                    },
                    y: {
                        grid: { color: GRID_COLOR },
                        border: { dash: [4,4], color: 'transparent' },
                        ticks: { color: TICK_COLOR, font: { size: 11 }, callback: v => fmt(v) },
                    },
                },
                animation: { duration: 500, easing: 'easeOutQuart' },
            },
        });
    }

    function setChartType(type) {
        document.getElementById('btn-bar').classList.toggle('active', type === 'bar');
        document.getElementById('btn-line').classList.toggle('active', type === 'line');
        initChart(type);
    }

    document.addEventListener('DOMContentLoaded', () => initChart('bar'));

    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.tab-btn').forEach(b => {
                b.classList.remove('active', 'text-white');
                b.classList.add('text-slate-500');
            });
            document.querySelectorAll('.tab-panel').forEach(p => {
                p.classList.add('hidden');
                p.classList.remove('block');
            });
            btn.classList.add('active', 'text-white');
            btn.classList.remove('text-slate-500');
            const panel = document.getElementById('panel-' + btn.dataset.tab);
            panel.classList.remove('hidden');
            panel.classList.add('block');
            if (btn.dataset.tab === 'grafik' && mainChart) setTimeout(() => mainChart.resize(), 50);
        });
    });

    document.getElementById('panel-grafik').classList.remove('hidden');
    document.getElementById('panel-grafik').classList.add('block');

    function showGate()  { document.getElementById('subscribeGate').classList.add('open'); }
    function closeGate() { document.getElementById('subscribeGate').classList.remove('open'); }
    document.getElementById('subscribeGate').addEventListener('click', function(e) { if (e.target === this) closeGate(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeGate(); });
    </script>

</body>
</html>