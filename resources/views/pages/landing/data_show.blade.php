<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Data Statistik {{ $metadata->nama }}</title>
    <meta name="description" content="Data statistik {{ $metadata->nama }} periode {{ $yearStart }}–{{ $yearEnd }}."/>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon"/>
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    @vite(['resources/css/app.css', 'resources/js/app.ts'])
    <style>
        @import url('https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,300&display=swap');

        :root {
            --blue:       #1a5cff;
            --blue-dark:  #0f3db5;
            --blue-light: #e8eeff;
            --slate:      #1e2535;
            --slate-mid:  #3d4a63;
            --slate-soft: #8391a8;
            --border:     #dde3f0;
            --surface:    #f7f9fc;
            --white:      #ffffff;
        }

        *, *::before, *::after { box-sizing: border-box; }
        body { font-family: 'DM Sans', sans-serif; margin: 0; background: var(--surface); }

        /* ── Hero ───────────────────────────────────────────────── */
        .dshow-hero {
            background: var(--slate);
            padding: 3rem 0 0;
            position: relative;
            overflow: hidden;
        }
        .dshow-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(ellipse 60% 80% at 80% 40%, rgba(26,92,255,.18) 0%, transparent 65%),
                radial-gradient(ellipse 40% 60% at 10% 90%, rgba(26,92,255,.10) 0%, transparent 60%);
            pointer-events: none;
        }
        .dshow-hero__inner {
            max-width: 1140px;
            margin: 0 auto;
            padding: 0 1.5rem;
            position: relative;
            z-index: 1;
        }

        /* breadcrumb */
        .breadcrumb {
            display: flex;
            align-items: center;
            gap: .4rem;
            font-size: .75rem;
            color: rgba(255,255,255,.5);
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }
        .breadcrumb a { color: rgba(255,255,255,.6); text-decoration: none; }
        .breadcrumb a:hover { color: #fff; }
        .breadcrumb .sep { color: rgba(255,255,255,.25); }

        /* badge */
        .dshow-hero__badge {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            font-size: .7rem;
            font-weight: 600;
            letter-spacing: .08em;
            text-transform: uppercase;
            background: rgba(26,92,255,.25);
            border: 1px solid rgba(26,92,255,.5);
            color: #7ab0ff;
            padding: .3rem .75rem;
            border-radius: 2px;
            margin-bottom: 1rem;
        }

        /* title */
        .dshow-hero__title {
            font-family: 'DM Serif Display', serif;
            font-size: clamp(1.55rem, 3vw + .5rem, 2.4rem);
            line-height: 1.2;
            color: #fff;
            max-width: 740px;
            margin: 0 0 1.25rem;
        }

        /* pills */
        .meta-pills {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
            margin-bottom: 2rem;
        }
        .meta-pill {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            font-size: .72rem;
            font-weight: 500;
            color: rgba(255,255,255,.65);
            background: rgba(255,255,255,.07);
            border: 1px solid rgba(255,255,255,.1);
            padding: .35rem .75rem;
            border-radius: 2px;
            white-space: nowrap;
        }
        .meta-pill svg { opacity: .7; flex-shrink: 0; }

        /* tab bar */
        .hero-tabs {
            display: flex;
            margin-top: .5rem;
        }
        .hero-tab {
            font-size: .8rem;
            font-weight: 600;
            letter-spacing: .03em;
            color: rgba(255,255,255,.45);
            padding: .75rem 1.25rem;
            cursor: pointer;
            border: none;
            border-bottom: 3px solid transparent;
            background: none;
            transition: color .2s, border-color .2s;
        }
        .hero-tab:hover { color: rgba(255,255,255,.75); }
        .hero-tab.active { color: #fff; border-bottom-color: var(--blue); }

        /* ── Body ───────────────────────────────────────────────── */
        .dshow-body {
            max-width: 1140px;
            margin: 0 auto;
            padding: 2rem 1.5rem 4rem;
        }

        /* tab panels */
        .tab-panel { display: none; }
        .tab-panel.active { display: block; }

        /* ── Stat bar ────────────────────────────────────────────── */
        .stat-bar {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: .75rem;
            margin-bottom: 1.5rem;
        }
        .stat-item {
            background: var(--white);
            border: 1px solid var(--border);
            border-top: 3px solid var(--blue);
            padding: 1rem 1.25rem;
            border-radius: 3px;
            box-shadow: 0 1px 3px rgba(0,0,0,.04);
        }
        .stat-item__label {
            font-size: .68rem;
            font-weight: 600;
            letter-spacing: .07em;
            text-transform: uppercase;
            color: var(--slate-soft);
            margin-bottom: .35rem;
        }
        .stat-item__value {
            font-family: 'DM Serif Display', serif;
            font-size: 1.6rem;
            color: var(--slate);
            line-height: 1;
        }
        .stat-item__unit {
            font-size: .7rem;
            color: var(--slate-soft);
            margin-top: .25rem;
        }

        /* ── Chart card ─────────────────────────────────────────── */
        .chart-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 4px;
            padding: 1.5rem 1.75rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 1px 4px rgba(0,0,0,.04);
        }
        .chart-card__header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 1.25rem;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .chart-card__title {
            font-size: .82rem;
            font-weight: 700;
            color: var(--slate);
            letter-spacing: .04em;
            text-transform: uppercase;
        }
        .chart-card__sub {
            font-size: .75rem;
            color: var(--slate-soft);
            margin-top: .2rem;
        }
        .chart-toggles { display: flex; gap: .25rem; }
        .chart-toggle-btn {
            padding: .35rem .6rem;
            border: 1px solid var(--border);
            background: var(--white);
            cursor: pointer;
            border-radius: 2px;
            color: var(--slate-soft);
            transition: background .15s, border-color .15s;
        }
        .chart-toggle-btn:hover { background: var(--surface); }
        .chart-toggle-btn.active { background: var(--blue); border-color: var(--blue); color: #fff; }
        .chart-toggle-btn svg { display: block; }
        .chart-wrap { position: relative; height: 320px; }

        /* ── Data table ─────────────────────────────────────────── */
        .data-table-wrap {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 4px;
            box-shadow: 0 1px 4px rgba(0,0,0,.04);
            overflow: hidden;
        }
        .data-table-wrap__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border);
            background: var(--surface);
            flex-wrap: wrap;
            gap: .75rem;
        }
        .data-table-wrap__title {
            font-size: .78rem;
            font-weight: 700;
            color: var(--slate);
            text-transform: uppercase;
            letter-spacing: .06em;
        }
        .data-source-badge {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            font-size: .7rem;
            color: var(--slate-soft);
            background: var(--white);
            border: 1px solid var(--border);
            padding: .3rem .7rem;
            border-radius: 2px;
        }

        table.dshow-table {
            width: 100%;
            border-collapse: collapse;
            font-size: .82rem;
        }
        table.dshow-table thead tr { background: var(--blue); color: #fff; }
        table.dshow-table thead th {
            padding: .75rem 1.25rem;
            text-align: left;
            font-size: .72rem;
            font-weight: 600;
            letter-spacing: .06em;
            text-transform: uppercase;
            white-space: nowrap;
        }
        table.dshow-table thead th:last-child { text-align: right; }
        table.dshow-table tbody tr {
            border-bottom: 1px solid var(--border);
            transition: background .15s;
        }
        table.dshow-table tbody tr:last-child { border-bottom: none; }
        table.dshow-table tbody tr:hover { background: var(--blue-light); }
        table.dshow-table tbody td {
            padding: .85rem 1.25rem;
            color: var(--slate);
            vertical-align: middle;
        }
        table.dshow-table tbody td:last-child {
            text-align: right;
            font-weight: 600;
            font-variant-numeric: tabular-nums;
            color: var(--blue-dark);
        }

        .period-chip {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            font-size: .7rem;
            font-weight: 600;
            background: var(--blue-light);
            color: var(--blue-dark);
            padding: .2rem .55rem;
            border-radius: 2px;
            white-space: nowrap;
        }
        .no-data-row td {
            text-align: center;
            color: var(--slate-soft);
            padding: 3rem;
            font-size: .85rem;
        }
        .table-footer {
            padding: .85rem 1.25rem;
            border-top: 1px solid var(--border);
            background: var(--surface);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: .5rem;
        }
        .table-footer span { font-size: .72rem; color: var(--slate-soft); }
        .table-footer button {
            font-size: .72rem;
            font-weight: 600;
            color: var(--blue);
            background: none;
            border: none;
            cursor: pointer;
            padding: 0;
        }
        .table-footer button:hover { text-decoration: underline; }

        /* ── Info tab ────────────────────────────────────────────── */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.25rem;
        }
        .info-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 4px;
            padding: 1.25rem 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,.04);
        }
        .info-card.full { grid-column: 1 / -1; }
        .info-card__label {
            font-size: .67rem;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: var(--slate-soft);
            margin-bottom: .5rem;
        }
        .info-card__value {
            font-size: .9rem;
            color: var(--slate);
            line-height: 1.65;
        }

        /* ── Gate modal ──────────────────────────────────────────── */
        .gate-overlay {
            position: fixed; inset: 0;
            background: rgba(15,20,35,.72);
            backdrop-filter: blur(4px);
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
            background: var(--white);
            border-radius: 8px;
            width: 100%;
            max-width: 420px;
            padding: 2rem;
            box-shadow: 0 24px 64px rgba(0,0,0,.28);
            transform: translateY(18px);
            transition: transform .3s;
            text-align: center;
        }
        .gate-overlay.open .gate-modal { transform: translateY(0); }
        .gate-icon {
            width: 56px; height: 56px;
            background: var(--blue-light);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1rem;
        }
        .gate-title {
            font-family: 'DM Serif Display', serif;
            font-size: 1.35rem;
            color: var(--slate);
            margin: 0 0 .5rem;
        }
        .gate-sub {
            font-size: .84rem;
            color: var(--slate-soft);
            line-height: 1.65;
            margin-bottom: 1.5rem;
        }
        .btn-gate-primary {
            display: block;
            background: var(--blue);
            color: #fff;
            font-weight: 600;
            font-size: .85rem;
            padding: .8rem;
            border-radius: 3px;
            text-decoration: none;
            margin-bottom: .6rem;
            transition: background .2s;
        }
        .btn-gate-primary:hover { background: var(--blue-dark); }
        .btn-gate-secondary {
            display: block;
            background: transparent;
            color: var(--slate-mid);
            font-size: .82rem;
            font-weight: 500;
            padding: .7rem;
            border: 1px solid var(--border);
            border-radius: 3px;
            text-decoration: none;
            margin-bottom: .5rem;
            transition: background .2s;
        }
        .btn-gate-secondary:hover { background: var(--surface); }
        .gate-dismiss {
            font-size: .75rem;
            color: var(--slate-soft);
            cursor: pointer;
            background: none;
            border: none;
            margin-top: .25rem;
            display: inline-block;
        }
        .gate-dismiss:hover { color: var(--slate); }

        /* ── Animations ──────────────────────────────────────────── */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(14px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .fade-up   { animation: fadeUp .4s ease both; }
        .delay-1   { animation-delay: .07s; }
        .delay-2   { animation-delay: .14s; }
        .delay-3   { animation-delay: .21s; }
        .delay-4   { animation-delay: .28s; }

        /* ── Responsive ──────────────────────────────────────────── */
        @media (max-width: 680px) {
            .info-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 640px) {
            .stat-bar { grid-template-columns: repeat(2, 1fr); }
            .chart-card { padding: 1.25rem 1rem; }
            .chart-wrap { height: 240px; }
            table.dshow-table thead th,
            table.dshow-table tbody td { padding: .6rem .75rem; }
            .dshow-hero { padding-top: 1.75rem; }
        }
    </style>
</head>
<body>

    @include('pages.landing.components.navbar')

    {{-- ══════════════════════════════════════════════════════ --}}
    {{-- HERO                                                   --}}
    {{-- ══════════════════════════════════════════════════════ --}}
    <section class="dshow-hero">
        <div class="dshow-hero__inner">

            {{-- Breadcrumb --}}
            <nav class="breadcrumb" aria-label="Breadcrumb">
                <a href="{{ route('landing') }}">Beranda</a>
                <span class="sep">›</span>
                <a href="{{ route('klasifikasi.index') }}">Klasifikasi</a>
                <span class="sep">›</span>
                <a href="{{ route('klasifikasi.show', Str::slug($metadata->klasifikasi?->nama_klasifikasi ?? '')) }}">
                    {{ $metadata->klasifikasi?->nama_klasifikasi ?? 'Klasifikasi' }}
                </a>
                <span class="sep">›</span>
                <span style="color:rgba(255,255,255,.8)">{{ Str::limit($metadata->nama, 45) }}</span>
            </nav>

            {{-- Classification badge --}}
            <div class="dshow-hero__badge">
                <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                {{ $metadata->klasifikasi?->nama_klasifikasi ?? 'Data Statistik' }}
            </div>

            {{-- Title --}}
            <h1 class="dshow-hero__title fade-up">{{ $metadata->nama }}</h1>

            {{-- Info pills --}}
            <div class="meta-pills fade-up delay-1">
                @if($metadata->satuan_data)
                <span class="meta-pill">
                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                    Satuan: {{ $metadata->satuan_data }}
                </span>
                @endif
                @if($metadata->frekuensi_penerbitan)
                <span class="meta-pill">
                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ $metadata->frekuensi_penerbitan }}
                </span>
                @endif
                @if($metadata->tahun_mulai_data)
                <span class="meta-pill">
                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Data sejak {{ $metadata->tahun_mulai_data }}
                </span>
                @endif
                <span class="meta-pill">
                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    {{ $metadata->produsen?->nama_produsen ?? 'Sumber tidak tersedia' }}
                </span>
            </div>

            {{-- Tabs --}}
            <nav class="hero-tabs" role="tablist">
                <button class="hero-tab active" role="tab" data-tab="grafik">Grafik &amp; Tabel</button>
                <button class="hero-tab" role="tab" data-tab="info">Informasi Metadata</button>
            </nav>

        </div>
    </section>

    {{-- ══════════════════════════════════════════════════════ --}}
    {{-- BODY                                                   --}}
    {{-- ══════════════════════════════════════════════════════ --}}
    <div class="dshow-body">

        {{-- ── TAB: GRAFIK & TABEL ──────────────────────────── --}}
        <div class="tab-panel active" id="panel-grafik">

            @php
                $values    = $tableRows->pluck('value')->filter(fn($v) => !is_null($v));
                $latestRow = $tableRows->sortByDesc('year')->first();
                $dec       = $metadata->flag_desimal ?? 0;
            @endphp

            {{-- Stat cards --}}
            <div class="stat-bar fade-up delay-2">
                <div class="stat-item">
                    <div class="stat-item__label">Data Terbaru</div>
                    <div class="stat-item__value">
                        @if($latestRow && !is_null($latestRow['value']))
                            {{ number_format((float)$latestRow['value'], $dec, ',', '.') }}
                        @else —
                        @endif
                    </div>
                    <div class="stat-item__unit">{{ $metadata->satuan_data }} · {{ $latestRow['period'] ?? '—' }}</div>
                </div>
                <div class="stat-item">
                    <div class="stat-item__label">Tertinggi (5 Thn)</div>
                    <div class="stat-item__value">
                        {{ $values->isNotEmpty() ? number_format((float)$values->max(), $dec, ',', '.') : '—' }}
                    </div>
                    <div class="stat-item__unit">{{ $metadata->satuan_data }}</div>
                </div>
                <div class="stat-item">
                    <div class="stat-item__label">Terendah (5 Thn)</div>
                    <div class="stat-item__value">
                        {{ $values->isNotEmpty() ? number_format((float)$values->min(), $dec, ',', '.') : '—' }}
                    </div>
                    <div class="stat-item__unit">{{ $metadata->satuan_data }}</div>
                </div>
                <div class="stat-item">
                    <div class="stat-item__label">Rata-rata (5 Thn)</div>
                    <div class="stat-item__value">
                        {{ $values->isNotEmpty() ? number_format((float)$values->avg(), $dec, ',', '.') : '—' }}
                    </div>
                    <div class="stat-item__unit">{{ $metadata->satuan_data }}</div>
                </div>
            </div>

            {{-- Chart --}}
            <div class="chart-card fade-up delay-3">
                <div class="chart-card__header">
                    <div>
                        <div class="chart-card__title">{{ $metadata->nama }}</div>
                        <div class="chart-card__sub">
                            Periode {{ $yearStart }}–{{ $yearEnd }} &nbsp;·&nbsp; Satuan: {{ $metadata->satuan_data }}
                        </div>
                    </div>
                    <div class="chart-toggles">
                        <button class="chart-toggle-btn active" id="btn-bar" title="Bar" onclick="setChartType('bar')">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </button>
                        <button class="chart-toggle-btn" id="btn-line" title="Line" onclick="setChartType('line')">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="chart-wrap">
                    <canvas id="mainChart"></canvas>
                </div>
            </div>

            {{-- Table --}}
            <div class="data-table-wrap fade-up delay-4">
                <div class="data-table-wrap__header">
                    <span class="data-table-wrap__title">Data Periode {{ $yearStart }}–{{ $yearEnd }}</span>
                    <span class="data-source-badge">
                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        Sumber: {{ $metadata->produsen?->nama_produsen ?? 'Tidak diketahui' }}
                    </span>
                </div>

                <div style="overflow-x:auto">
                    <table class="dshow-table">
                        <thead>
                            <tr>
                                <th>Indikator</th>
                                <th>Periode</th>
                                <th>{{ $metadata->satuan_data }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tableRows as $row)
                            <tr>
                                <td>
                                    <span style="font-weight:500">{{ $metadata->nama }}</span>
                                </td>
                                <td>
                                    <span class="period-chip">
                                        <svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        {{ $row['period'] }}
                                    </span>
                                </td>
                                <td>
                                    @if(!is_null($row['value']))
                                        {{ number_format((float)$row['value'], $dec, ',', '.') }}
                                    @else
                                        <span style="color:var(--slate-soft);font-weight:400">—</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr class="no-data-row">
                                <td colspan="3">
                                    <svg width="38" height="38" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                         style="display:block;margin:0 auto .6rem;opacity:.35">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.4"
                                              d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Data untuk periode {{ $yearStart }}–{{ $yearEnd }} belum tersedia.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="table-footer">
                    <span>Menampilkan {{ count($tableRows) }} baris · Data lengkap tersedia untuk pelanggan</span>
                    <button onclick="showGate()">Akses Data Lengkap →</button>
                </div>
            </div>

        </div>{{-- /panel-grafik --}}

        {{-- ── TAB: INFORMASI METADATA ──────────────────────── --}}
        <div class="tab-panel" id="panel-info">
            <div class="info-grid fade-up">

                <div class="info-card full">
                    <div class="info-card__label">Konsep</div>
                    <div class="info-card__value">{{ $metadata->konsep }}</div>
                </div>

                <div class="info-card full">
                    <div class="info-card__label">Definisi</div>
                    <div class="info-card__value">{{ $metadata->definisi }}</div>
                </div>

                <div class="info-card">
                    <div class="info-card__label">Metodologi</div>
                    <div class="info-card__value">{{ $metadata->metodologi }}</div>
                </div>

                <div class="info-card">
                    <div class="info-card__label">Penjelasan Metodologi</div>
                    <div class="info-card__value">{{ $metadata->penjelasan_metodologi }}</div>
                </div>

                @if($metadata->asumsi)
                <div class="info-card full">
                    <div class="info-card__label">Asumsi</div>
                    <div class="info-card__value">{{ $metadata->asumsi }}</div>
                </div>
                @endif

                <div class="info-card">
                    <div class="info-card__label">Tipe Data</div>
                    <div class="info-card__value">{{ $metadata->tipe_data }}</div>
                </div>

                <div class="info-card">
                    <div class="info-card__label">Satuan Data</div>
                    <div class="info-card__value">{{ $metadata->satuan_data }}</div>
                </div>

                <div class="info-card">
                    <div class="info-card__label">Frekuensi Penerbitan</div>
                    <div class="info-card__value">{{ $metadata->frekuensi_penerbitan }}</div>
                </div>

                <div class="info-card">
                    <div class="info-card__label">Tahun Mulai Data</div>
                    <div class="info-card__value">{{ $metadata->tahun_mulai_data }}</div>
                </div>

                <div class="info-card full">
                    <div class="info-card__label">Produsen / Sumber Data</div>
                    <div class="info-card__value">{{ $metadata->produsen?->nama_produsen ?? '—' }}</div>
                </div>

            </div>
        </div>{{-- /panel-info --}}

    </div>{{-- /dshow-body --}}

    {{-- ══════════════════════════════════════════════════════ --}}
    {{-- SUBSCRIBE GATE MODAL                                   --}}
    {{-- ══════════════════════════════════════════════════════ --}}
    <div class="gate-overlay" id="subscribeGate" role="dialog" aria-modal="true" aria-labelledby="gateTitle">
        <div class="gate-modal">
            <div class="gate-icon">
                <svg width="26" height="26" fill="none" stroke="#1a5cff" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <h2 class="gate-title" id="gateTitle">Data Lengkap Tersedia</h2>
            <p class="gate-sub">
                Anda sedang melihat pratinjau data publik.<br>
                Berlangganan untuk mengakses seluruh dataset,
                unduh CSV / Excel, dan visualisasi interaktif lengkap.
            </p>
            <a href="{{ route('langganan') }}" class="btn-gate-primary">Lihat Paket Langganan</a>
            <a href="{{ route('login') }}" class="btn-gate-secondary">Masuk / Daftar Akun</a>
            <br>
            <button class="gate-dismiss" onclick="closeGate()">Lanjutkan lihat pratinjau</button>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════ --}}
    {{-- SCRIPTS                                                --}}
    {{-- ══════════════════════════════════════════════════════ --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
    <script>
    // ── Data from PHP ────────────────────────────────────────
    const chartLabels = @json($chartLabels);
    const chartValues = @json($chartValues);
    const satuan      = @json($metadata->satuan_data);
    const flagDesimal = {{ $metadata->flag_desimal ?? 0 }};

    // ── Chart ────────────────────────────────────────────────
    const C_BLUE      = '#1a5cff';
    const C_BLUE_A    = 'rgba(26,92,255,.12)';
    const C_SLATE     = '#1e2535';
    const C_SOFT      = '#8391a8';

    Chart.defaults.font.family = "'DM Sans', sans-serif";

    let mainChart;

    function fmt(v) {
        if (v === null || v === undefined) return '—';
        return new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: flagDesimal,
            maximumFractionDigits: flagDesimal,
        }).format(v);
    }

    function buildDataset(type) {
        if (type === 'bar') return {
            label: satuan,
            data: chartValues,
            backgroundColor: C_BLUE,
            borderColor: C_BLUE,
            borderWidth: 0,
            borderRadius: 4,
            borderSkipped: false,
        };
        return {
            label: satuan,
            data: chartValues,
            borderColor: C_BLUE,
            backgroundColor: C_BLUE_A,
            borderWidth: 2.5,
            pointBackgroundColor: C_BLUE,
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
                        backgroundColor: C_SLATE,
                        titleColor: 'rgba(255,255,255,.65)',
                        bodyColor: '#fff',
                        padding: 12,
                        cornerRadius: 4,
                        callbacks: { label: c => '  ' + fmt(c.raw) + ' ' + satuan },
                    },
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: C_SOFT, font: { size: 11, weight: '500' } },
                    },
                    y: {
                        grid: { color: 'rgba(0,0,0,.05)' },
                        border: { dash: [4,4] },
                        ticks: { color: C_SOFT, font: { size: 11 }, callback: v => fmt(v) },
                    },
                },
                animation: { duration: 480, easing: 'easeOutQuart' },
            },
        });
    }

    function setChartType(type) {
        document.getElementById('btn-bar').classList.toggle('active', type === 'bar');
        document.getElementById('btn-line').classList.toggle('active', type === 'line');
        initChart(type);
    }

    document.addEventListener('DOMContentLoaded', () => initChart('bar'));

    // ── Tabs ─────────────────────────────────────────────────
    document.querySelectorAll('.hero-tab').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.hero-tab').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
            btn.classList.add('active');
            document.getElementById('panel-' + btn.dataset.tab).classList.add('active');
            if (btn.dataset.tab === 'grafik' && mainChart) setTimeout(() => mainChart.resize(), 50);
        });
    });

    // ── Gate modal ────────────────────────────────────────────
    function showGate()  { document.getElementById('subscribeGate').classList.add('open'); }
    function closeGate() { document.getElementById('subscribeGate').classList.remove('open'); }

    document.getElementById('subscribeGate').addEventListener('click', function(e) {
        if (e.target === this) closeGate();
    });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeGate(); });
    </script>

</body>
</html>