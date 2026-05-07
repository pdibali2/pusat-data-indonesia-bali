{{-- ═══════════════════════════════════════════════════════════════
     _template-panel.blade.php
     Include dari: pages/data/index.blade.php
═══════════════════════════════════════════════════════════════ --}}

@php
    $activeTemplateId = (int) request('template_id', 0);
    $tahunOpts        = range(2010, 2030);
    $semesterOpts     = [1 => 'Semester 1', 2 => 'Semester 2'];
    $kuartalOpts      = [1 => 'Q1', 2 => 'Q2', 3 => 'Q3', 4 => 'Q4'];
    $bulanOpts        = [
        1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April',
        5=>'Mei', 6=>'Juni', 7=>'Juli', 8=>'Agustus',
        9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember',
    ];
@endphp

{{-- ══════════════════════════════════════════════════════════
     STYLES — diletakkan di atas agar tidak FOUC
══════════════════════════════════════════════════════════ --}}
<style>
    /* ── Card utama tidak boleh memotong scroll ── */
    .tp-panel-card {
        overflow: visible !important;
    }

    /* ── Wrapper tabel: scroll horizontal BEBAS, vertikal max 70vh ── */
    #tableScrollOuter {
        width: 100%;
        overflow-x: auto;
        overflow-y: auto;
        max-height: 70vh;
        border: 1px solid #e5e7eb;
        border-radius: 0.75rem;
        -webkit-overflow-scrolling: touch;
        /* Pastikan tidak ada max-width yang memotong */
        position: relative;
    }

    /* ── Tabel: lebar min-content agar tidak di-squeeze ── */
    #pivotTable {
        border-collapse: collapse;
        width: max-content;   /* ← KUNCI: tabel melebar sesuai konten */
        min-width: 100%;
        table-layout: auto;
        font-size: 0.813rem;
    }

    /* ── Sticky kolom kiri ── */
    #pivotTable th.col-sticky,
    #pivotTable td.col-sticky {
        position: sticky;
        z-index: 10;
    }
    /* Kolom Nama Metadata */
    #pivotTable th.col-sticky-nama,
    #pivotTable td.col-sticky-nama {
        left: 0;
        min-width: 220px;
        max-width: 260px;
        background: inherit;
    }
    /* Freeze header baris */
    #pivotTable thead th {
        position: sticky;
        top: 0;
        z-index: 20;
        background: #f9fafb;
    }
    /* Sudut kiri-atas: sticky horizontal + vertikal */
    #pivotTable thead th.col-sticky-nama {
        z-index: 30;
        background: #f9fafb;
    }
    /* ── Garis kanan sticky kolom ── */
    #pivotTable th.col-sticky-nama,
    #pivotTable td.col-sticky-nama {
        border-right: 2px solid #e5e7eb;
        box-shadow: 2px 0 4px -2px rgba(0,0,0,0.08);
    }

    /* ── Warna baris zebra ── */
    #pivotTable tbody tr:nth-child(even) td {
        background-color: #f9fafb;
    }
    #pivotTable tbody tr:nth-child(even) td.col-sticky {
        background-color: #f9fafb;
    }
    #pivotTable tbody tr:nth-child(odd) td.col-sticky {
        background-color: #ffffff;
    }
    #pivotTable tbody tr:hover td {
        background-color: #f5f3ff !important;
    }

    /* ── Indentasi wilayah ── */
    .loc-indent-0 { padding-left: 0.5rem; }
    .loc-indent-1 { padding-left: 1.5rem; }
    .loc-indent-2 { padding-left: 2.5rem; }
    .loc-indent-3 { padding-left: 3.5rem; }
    .loc-indent-1::before { content: '└ '; color: #d1d5db; margin-right: 2px; }
    .loc-indent-2::before { content: '  └ '; color: #d1d5db; margin-right: 2px; white-space: pre; }
    .loc-indent-3::before { content: '    └ '; color: #d1d5db; margin-right: 2px; white-space: pre; }

    /* ── Separator antar metadata ── */
    tr.metadata-separator td {
        height: 2px;
        padding: 0;
        background: #e5e7eb;
    }

    /* ── Step badge ── */
    .tp-step-badge {
        width: 1.25rem; height: 1.25rem;
        border-radius: 9999px;
        background: #8b5cf6; color: #fff;
        font-size: 0.65rem; font-weight: 700;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }

    /* ── Select ── */
    .tp-select {
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
        background: #fff;
        outline: none;
        transition: box-shadow 0.15s, border-color 0.15s;
        cursor: pointer;
    }
    .tp-select:focus {
        box-shadow: 0 0 0 2px #a78bfa;
        border-color: #8b5cf6;
    }

    /* ── Frekuensi tombol ── */
    .freq-btn:disabled {
        pointer-events: none;
        opacity: 0.40;
        filter: grayscale(0.6);
        cursor: not-allowed;
    }
    .freq-count-badge {
        display: inline-block;
        vertical-align: middle;
    }

    /* ── Tombol Grafik di tabel ── */
    .btn-grafik {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.25rem 0.625rem;
        font-size: 0.7rem;
        font-weight: 500;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        color: #6b7280;
        background: #fff;
        white-space: nowrap;
        transition: border-color 0.15s, background 0.15s, color 0.15s;
        cursor: pointer;
        text-decoration: none;
    }
    .btn-grafik:hover {
        border-color: #a78bfa;
        background: #f5f3ff;
        color: #7c3aed;
    }
</style>

<div class="tp-panel-card mt-2 bg-white rounded-xl shadow p-6">

    {{-- ════ HEADER ════ --}}
    <div class="flex justify-between items-start mb-6">
        <div>
            <h2 class="text-lg font-bold text-gray-800">Tampilkan Data</h2>
            <p class="text-sm text-gray-400 mt-1">
                Pilih template → frekuensi → rentang periode → klik Tampilkan Data
            </p>
        </div>
        <a href="{{ route('template.create') }}"
           class="px-4 py-2 bg-sky-500 hover:bg-sky-600 text-white text-sm font-semibold rounded-lg
                  shadow-md shadow-sky-400/30 flex items-center gap-2 transition-colors">
            <i class="fas fa-plus"></i> Buat Template
        </a>
    </div>

    {{-- ════ ALERT ════ --}}
    @if(session('success'))
        <div class="mb-5 flex items-center gap-3 bg-green-50 border border-green-200
                    text-green-700 px-4 py-3 rounded-lg text-sm">
            <i class="fas fa-check-circle text-green-500 shrink-0"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- ════════════════════════════════════════
         STEP 1 — Pilih Template
    ════════════════════════════════════════ --}}
    <div class="mb-6">
        <div class="flex items-center gap-2 mb-3">
            <span class="tp-step-badge">1</span>
            <p class="text-sm font-semibold text-gray-700">Pilih Template</p>
        </div>

        @if($availableTemplates->isEmpty())
            <div class="flex flex-col w-full border border-gray-300 rounded-lg text-sm text-gray-500">
                <div class="border-b border-gray-300 px-3 py-2 text-xs font-medium text-gray-600">
                    Daftar Template
                </div>
                <div class="flex flex-col items-center gap-3 py-12 text-gray-400">
                    <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center">
                        <i class="fas fa-layer-group text-gray-300 text-xl"></i>
                    </div>
                    <p class="font-medium text-gray-500">Belum ada template</p>
                    <p class="text-xs text-gray-400">Buat template pertama Anda untuk memudahkan akses data</p>
                    <a href="{{ route('template.create') }}"
                       class="mt-1 px-4 py-2 bg-violet-500 hover:bg-violet-600 text-white
                              text-xs font-semibold rounded-lg transition-colors">
                        <i class="fas fa-plus mr-1"></i> Buat Template
                    </a>
                </div>
            </div>
        @else
            <div class="flex flex-col w-full border border-gray-300 rounded-lg">
                <div class="border-b border-gray-300 px-3 py-2 text-xs font-medium text-gray-600">
                    Daftar Template
                    <span class="text-gray-400">({{ $availableTemplates->count() }})</span>
                </div>
                <div class="flex flex-col gap-2 my-3 mx-3 max-h-52 overflow-y-auto pr-1
                            scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-transparent">
                    @foreach($availableTemplates as $tmpl)
                        @php
                            $fp         = $tmpl->filter_params ?? [];
                            $jenis      = $fp['jenis_template'] ?? 'metadata';
                            $jenisLabel = [
                                'metadata'    => 'Metadata',
                                'klasifikasi' => 'Klasifikasi',
                                'wilayah'     => 'Wilayah',
                            ][$jenis] ?? $jenis;
                            $isActive = $activeTemplateId === (int) $tmpl->tampilan_id;
                        @endphp
                        <div class="grid grid-cols-13 gap-5 w-full border-2 rounded-lg px-4 py-3
                                    text-xs font-semibold items-center cursor-pointer transition-all duration-150
                                    {{ $isActive
                                        ? 'border-violet-500 bg-violet-500 text-white'
                                        : 'border-violet-300 text-violet-700 hover:bg-violet-600 hover:text-white' }}"
                             onclick="selectTemplate({{ $tmpl->tampilan_id }})">

                            <div class="col-span-6">
                                <p class="font-semibold">{{ $tmpl->nama_tampilan }}</p>
                                <div class="mt-1 flex items-center gap-2 font-normal opacity-80">
                                    <span>{{ $jenisLabel }}</span>
                                    <span>•</span>
                                    <span>{{ $tmpl->isi_tampilan_count ?? 0 }} metadata</span>
                                </div>
                            </div>

                            <div class="col-span-3 font-normal opacity-70">
                                <span class="block">Dibuat</span>
                                <span>{{ $tmpl->created_at?->format('Y-m-d H:i') }}</span>
                            </div>

                            <div class="col-span-3 font-normal opacity-70">
                                <span class="block">Diubah</span>
                                <span>{{ $tmpl->updated_at?->format('Y-m-d H:i') }}</span>
                            </div>

                            <div class="col-span-1 flex gap-2 justify-end"
                                 onclick="event.stopPropagation()">
                                <a href="{{ route('template.edit', $tmpl->tampilan_id) }}"
                                   class="{{ $isActive ? 'text-white/70 hover:text-white' : 'text-violet-400 hover:text-violet-600' }} transition-colors"
                                   title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('template.destroy', $tmpl->tampilan_id) }}"
                                      method="POST"
                                      onsubmit="return confirm('Hapus template \'{{ addslashes($tmpl->nama_tampilan) }}\'?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="{{ $isActive ? 'text-white/70 hover:text-white' : 'text-violet-400 hover:text-red-500' }} transition-colors"
                                            title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    {{-- ════════════════════════════════════════
         STEP 2 — Pilih Frekuensi
    ════════════════════════════════════════ --}}
    <div id="stepFrekuensi" class="{{ $activeTemplateId ? '' : 'hidden' }} mb-6">
        <div class="flex items-center gap-2 mb-3">
            <span class="tp-step-badge">2</span>
            <p class="text-sm font-semibold text-gray-700">Pilih Frekuensi Rentang Waktu</p>
        </div>

        {{-- Loading indicator frekuensi --}}
        <div id="freqLoadingHint" class="mb-2 text-xs text-gray-400 hidden">
            <i class="fas fa-circle-notch fa-spin mr-1 text-violet-400"></i>
            Memeriksa ketersediaan data per frekuensi...
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2">
            @foreach([
                '10tahunan'  => ['label' => '10 Tahunan',  'sub' => 'Setiap 10 tahun'],
                'tahunan'    => ['label' => 'Tahunan',     'sub' => 'Per tahun'],
                'semesteran' => ['label' => 'Semesteran',  'sub' => 'S1 / S2'],
                'kuartal'    => ['label' => 'Kuartal',     'sub' => 'Q1 – Q4'],
                'bulanan'    => ['label' => 'Bulanan',     'sub' => 'Jan – Des'],
                'custom'     => ['label' => 'Custom',      'sub' => 'Rentang bebas'],
            ] as $key => $opt)
                <button type="button"
                        id="freq-btn-{{ $key }}"
                        onclick="selectFrekuensi('{{ $key }}')"
                        class="freq-btn border-2 border-gray-200 rounded-xl p-3 text-left
                               hover:border-violet-400 hover:bg-violet-50 transition-all duration-150
                               {{ $key === 'custom' ? 'border-dashed' : '' }}"
                        disabled>
                    <p class="text-xs font-semibold text-gray-700 flex items-center gap-1 flex-wrap">
                        {{ $opt['label'] }}
                        @if($key === 'custom')
                            <i class="fas fa-sliders-h text-gray-400"></i>
                        @endif
                        <span id="freq-badge-{{ $key }}"
                              class="freq-count-badge text-[10px] font-bold px-1.5 py-0.5 rounded-full
                                     bg-gray-100 text-gray-400">
                            …
                        </span>
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $opt['sub'] }}</p>
                </button>
            @endforeach
        </div>
    </div>

    {{-- ════════════════════════════════════════
         STEP 3 — Rentang Periode
    ════════════════════════════════════════ --}}
    <div id="stepPeriode" class="hidden mb-6">
        <div class="flex items-center gap-2 mb-3">
            <span class="tp-step-badge">3</span>
            <p class="text-sm font-semibold text-gray-700">Tentukan Rentang Periode</p>
        </div>

        {{-- Info helper --}}
        <div id="periodeHelperText" class="mb-3 text-xs text-violet-600 bg-violet-50 border border-violet-200 rounded-lg px-3 py-2 hidden">
            <i class="fas fa-info-circle mr-1"></i>
            <span id="periodeHelperMsg"></span>
        </div>

        {{-- ── 10 Tahunan ─────────────────────── --}}
        <div id="periode10tahunan" class="hidden">
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <p class="text-xs text-gray-500 mb-3">Pilih rentang dekade (setiap 10 tahun)</p>
                <div class="flex flex-wrap gap-2 mb-3" id="preset10tahunan"></div>
                <p class="text-xs text-gray-400 mt-2">Atau pilih manual:</p>
                <div class="flex flex-wrap items-end gap-3 mt-2">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Dari Dekade</label>
                        <select id="from10" onchange="checkPeriode10()" class="tp-select min-w-32">
                            <option value="">Pilih...</option>
                            @foreach([1950,1960,1970,1980,1990,2000,2010,2020,2030,2040] as $d)
                                <option value="{{ $d }}">{{ $d }}</option>
                            @endforeach
                        </select>
                    </div>
                    <span class="text-gray-400 mb-2">—</span>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Sampai Dekade</label>
                        <select id="to10" onchange="checkPeriode10()" class="tp-select min-w-32">
                            <option value="">Pilih...</option>
                            @foreach([1950,1960,1970,1980,1990,2000,2010,2020,2030,2040] as $d)
                                <option value="{{ $d }}">{{ $d }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div id="tahunanKolomInfo" class="hidden mt-2">
                    <span class="text-xs text-violet-600 font-medium" id="tahunanKolom"></span>
                </div>
            </div>
        </div>

        {{-- ── Tahunan ──────────────────────────── --}}
        <div id="periodeTahunan" class="hidden">
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <p class="text-xs text-gray-500 mb-3">Pilih rentang tahun</p>
                <div class="flex flex-wrap gap-2 mb-3">
                    @foreach(['5'=>'5 Tahun','10'=>'10 Tahun','15'=>'15 Tahun','20'=>'20 Tahun'] as $n => $label)
                        <button type="button" onclick="applyPresetTahunan({{ $n }})"
                                class="px-3 py-1.5 text-xs border border-violet-200 text-violet-600
                                       bg-violet-50 hover:bg-violet-100 rounded-lg transition-colors font-medium">
                            {{ $label }} Terakhir
                        </button>
                    @endforeach
                </div>
                <div class="flex flex-wrap items-end gap-3">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Dari Tahun</label>
                        <select id="fromTahunan" onchange="checkPeriodeTahunan()" class="tp-select min-w-32">
                            <option value="">Pilih...</option>
                            @foreach($tahunOpts as $y)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                    <span class="text-gray-400 mb-2">—</span>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Sampai Tahun</label>
                        <select id="toTahunan" onchange="checkPeriodeTahunan()" class="tp-select min-w-32">
                            <option value="">Pilih...</option>
                            @foreach($tahunOpts as $y)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div id="tahunanKolomInfoTahunan" class="hidden mt-2">
                    <span class="text-xs text-violet-600 font-medium" id="tahunanKolomTahunan"></span>
                </div>
            </div>
        </div>

        {{-- ── Complex: semesteran / kuartal / bulanan ─── --}}
        <div id="periodeComplex" class="hidden">
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 space-y-4">
                <div>
                    <p class="text-xs text-gray-500 mb-2">Pilih rentang tahun</p>
                    <div class="flex flex-wrap gap-2 mb-3">
                        @foreach(['3'=>'3 Tahun','5'=>'5 Tahun','10'=>'10 Tahun'] as $n => $label)
                            <button type="button" onclick="applyPresetComplex({{ $n }})"
                                    class="px-3 py-1.5 text-xs border border-violet-200 text-violet-600
                                           bg-violet-50 hover:bg-violet-100 rounded-lg transition-colors font-medium">
                                {{ $label }} Terakhir
                            </button>
                        @endforeach
                    </div>
                    <div class="flex flex-wrap items-end gap-3">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Tahun Dari</label>
                            <select id="yearFrom" onchange="checkPeriodeComplex()" class="tp-select min-w-32">
                                <option value="">Pilih...</option>
                                @foreach($tahunOpts as $y)
                                    <option value="{{ $y }}">{{ $y }}</option>
                                @endforeach
                            </select>
                        </div>
                        <span class="text-gray-400 mb-2">—</span>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Tahun Sampai</label>
                            <select id="yearTo" onchange="checkPeriodeComplex()" class="tp-select min-w-32">
                                <option value="">Pilih...</option>
                                @foreach($tahunOpts as $y)
                                    <option value="{{ $y }}">{{ $y }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-2" id="periodeComplexLabel">Periode</label>
                    <div class="flex flex-wrap items-end gap-3">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Dari</label>
                            <select id="periodFromComplex" onchange="checkPeriodeComplex()" class="tp-select min-w-36">
                                <option value="">Semua (opsional)</option>
                            </select>
                        </div>
                        <span class="text-gray-400 mb-2">—</span>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Sampai</label>
                            <select id="periodToComplex" onchange="checkPeriodeComplex()" class="tp-select min-w-36">
                                <option value="">Semua (opsional)</option>
                            </select>
                        </div>
                    </div>
                    <p class="text-xs text-gray-400 mt-2">
                        <i class="fas fa-info-circle mr-1"></i>
                        Kosongkan untuk mengambil semua periode dalam rentang tahun yang dipilih.
                    </p>
                </div>

                <div id="complexKolomInfo" class="hidden">
                    <span class="text-xs text-violet-600 font-medium" id="complexKolom"></span>
                </div>
            </div>
        </div>

        {{-- ── Custom ─────────────────────────────── --}}
        <div id="periodeCustom" class="hidden">
            <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 space-y-4">
                <p class="text-xs text-amber-700 font-medium">
                    <i class="fas fa-sliders-h mr-1"></i>
                    Rentang Custom — Pilih tahun dari–sampai secara bebas
                </p>
                <div class="flex flex-wrap items-end gap-3">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Dari Tahun</label>
                        <select id="customYearFrom" onchange="checkPeriodeCustom()" class="tp-select min-w-32">
                            <option value="">Pilih...</option>
                            @foreach($tahunOpts as $y)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                    <span class="text-gray-400 mb-2">—</span>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Sampai Tahun</label>
                        <select id="customYearTo" onchange="checkPeriodeCustom()" class="tp-select min-w-32">
                            <option value="">Pilih...</option>
                            @foreach($tahunOpts as $y)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-1">
                        <label class="block text-xs text-gray-500 mb-1">Satuan</label>
                        <select id="customUnit" onchange="checkPeriodeCustom()" class="tp-select">
                            <option value="tahunan">Per Tahun</option>
                            <option value="semesteran">Per Semester</option>
                            <option value="kuartal">Per Kuartal</option>
                            <option value="bulanan">Per Bulan</option>
                        </select>
                    </div>
                </div>
                <div id="customKolomInfo" class="hidden">
                    <span class="text-xs text-amber-700 font-medium" id="customKolom"></span>
                </div>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════
         STEP 4 — Tombol Tampilkan Data
    ════════════════════════════════════════ --}}
    <div id="stepTampilkan" class="hidden mb-6">
        <button type="button" onclick="tampilkanData()"
                class="px-6 py-2.5 bg-violet-600 hover:bg-violet-700 text-white text-sm
                       font-semibold rounded-lg shadow-md shadow-violet-400/30
                       flex items-center gap-2 transition-colors">
            <i class="fas fa-table"></i> Tampilkan Data
        </button>
    </div>

    {{-- ════════════════════════════════════════
         HASIL TABEL PIVOT
    ════════════════════════════════════════ --}}
    <div id="dataTableSection" class="hidden">
        <hr class="my-5 border-gray-100">

        {{-- Info bar --}}
        <div class="flex items-center justify-between mb-3 flex-wrap gap-2">
            <div>
                <p class="text-sm font-semibold text-gray-700" id="tableInfoText">Memuat data...</p>
                <p class="text-xs text-gray-400 mt-0.5" id="tableSubInfo"></p>
            </div>
            <div class="flex gap-2 flex-wrap">
                <button type="button" onclick="resetFilter()"
                        class="px-3 py-1.5 border border-gray-300 hover:bg-gray-50 text-gray-500
                               text-xs font-medium rounded-lg flex items-center gap-1.5 transition-colors">
                    <i class="fas fa-times text-xs"></i> Reset
                </button>
            </div>
        </div>

        {{-- Loading --}}
        <div id="tableLoading"
             class="hidden flex flex-col items-center gap-3 py-14 text-gray-400">
            <i class="fas fa-circle-notch fa-spin text-violet-400 text-3xl"></i>
            <p class="text-sm">Memuat data...</p>
        </div>

        {{-- Empty --}}
        <div id="tableEmpty"
             class="hidden flex flex-col items-center gap-3 py-14
                    border-2 border-dashed border-gray-200 rounded-xl text-gray-400">
            <i class="fas fa-inbox text-4xl text-gray-200"></i>
            <p class="text-sm font-medium text-gray-500" id="tableEmptyMsg">
                Tidak ada data ditemukan
            </p>
            <p class="text-xs">Coba ubah filter atau rentang periode</p>
        </div>

        {{-- ════ TABEL — wrapper scroll ════
             #tableScrollOuter : overflow-x auto + overflow-y auto (max-height 70vh)
             Tabel di dalamnya : width: max-content → melebar bebas
        --}}
        <div id="tableWrap" class="hidden">
            <div id="tableScrollOuter">
                <table id="pivotTable">
                    <thead id="pivotHead"></thead>
                    <tbody id="pivotBody"></tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        <div id="tablePagination"
             class="hidden mt-4 flex items-center justify-between text-xs text-gray-500 flex-wrap gap-2">
            <span id="paginationInfo"></span>
            <div id="paginationBtns" class="flex gap-1 flex-wrap"></div>
        </div>
    </div>

</div>{{-- /tp-panel-card --}}

{{-- ══════════════════════════════════════════════════════════
     JAVASCRIPT
══════════════════════════════════════════════════════════ --}}
<script>
// ─── Konstanta ────────────────────────────────────────────────
const TP_PERIODE_OPTS = {
    semesteran : [[1,'Semester 1'],[2,'Semester 2']],
    kuartal    : [[1,'Q1'],[2,'Q2'],[3,'Q3'],[4,'Q4']],
    bulanan    : [
        [1,'Januari'],[2,'Februari'],[3,'Maret'],[4,'April'],
        [5,'Mei'],[6,'Juni'],[7,'Juli'],[8,'Agustus'],
        [9,'September'],[10,'Oktober'],[11,'November'],[12,'Desember'],
    ],
};

const TP_FREK_LABEL = {
    '10tahunan':'10 Tahunan','5tahunan':'5 Tahunan','tahunan':'Tahunan',
    'semesteran':'Semesteran','kuartal':'Kuartal','bulanan':'Bulanan','custom':'Custom',
};

const TMPL_URLS = {
    base       : '{{ route("data.index") }}',
    tableData  : '{{ route("template.table_data") }}',
    freqCounts : '{{ route("template.freq_counts") }}',
    grafik     : '{{ route("template.grafik") }}',
    csrf       : document.querySelector('meta[name="csrf-token"]')?.content ?? '',
};

// State filter aktif
const TS = {
    tampilan_id : {{ $activeTemplateId ?: 'null' }},
    frekuensi   : null,
    year_from   : null,
    year_to     : null,
    period_from : null,
    period_to   : null,
    custom_unit : null,
    page        : 1,
};

// ─────────────────────────────────────────────────────────────
// STEP 1 — Pilih template
// ─────────────────────────────────────────────────────────────
function selectTemplate(id) {
    const url = (TS.tampilan_id === id)
        ? TMPL_URLS.base
        : TMPL_URLS.base + '?template_id=' + id;
    window.location.href = url;
}

// ─────────────────────────────────────────────────────────────
// STEP 2 — Load & render freq-counts badges
// ─────────────────────────────────────────────────────────────
async function loadFreqCounts(tampilanId) {
    if (!tampilanId) return;

    const freqKeys = ['10tahunan', 'tahunan', 'semesteran', 'kuartal', 'bulanan', 'custom'];

    // Tampilkan hint loading
    const hint = document.getElementById('freqLoadingHint');
    if (hint) hint.classList.remove('hidden');

    // Set semua badge ke "…" dan disabled sementara
    freqKeys.forEach(freq => {
        const btn   = document.getElementById('freq-btn-' + freq);
        const badge = document.getElementById('freq-badge-' + freq);
        if (btn)   { btn.disabled = true; }
        if (badge) { badge.textContent = '…'; badge.className = 'freq-count-badge text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-gray-100 text-gray-400'; }
    });

    try {
        const res  = await fetch(`${TMPL_URLS.freqCounts}?tampilan_id=${tampilanId}`);
        const data = await res.json();

        freqKeys.forEach(freq => {
            const btn   = document.getElementById('freq-btn-' + freq);
            const badge = document.getElementById('freq-badge-' + freq);
            if (!btn) return;

            const count = data[freq] ?? 0;

            if (count > 0) {
                // ── Ada data → aktifkan tombol ──
                btn.disabled = false;
                btn.removeAttribute('disabled');
                // Styling tombol aktif (enabled)
                btn.classList.remove('opacity-40', 'cursor-not-allowed');
                btn.classList.add('cursor-pointer');

                if (badge) {
                    badge.className = 'freq-count-badge text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-violet-100 text-violet-700';
                    badge.textContent = count;
                    badge.title = `${count} metadata tersedia`;
                }
                btn.title = `${count} metadata tersedia`;
            } else {
                // ── Tidak ada data → disable ──
                btn.disabled = true;
                // Reset border/background jika sebelumnya aktif
                btn.classList.remove('border-violet-500','bg-violet-50','border-amber-400','bg-amber-50');
                btn.classList.add('border-gray-200');

                if (badge) {
                    badge.className = 'freq-count-badge text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-gray-100 text-gray-400';
                    badge.textContent = '0';
                }
                btn.title = 'Tidak ada data untuk frekuensi ini';
            }
        });

    } catch (e) {
        console.warn('Gagal memuat freq counts:', e);
        // Saat error → enable semua agar tidak memblokir user
        freqKeys.forEach(freq => {
            const btn   = document.getElementById('freq-btn-' + freq);
            const badge = document.getElementById('freq-badge-' + freq);
            if (btn)   btn.disabled = false;
            if (badge) { badge.textContent = '?'; }
        });
    } finally {
        if (hint) hint.classList.add('hidden');
    }
}

// ─────────────────────────────────────────────────────────────
// STEP 2 — Pilih frekuensi
// ─────────────────────────────────────────────────────────────
function selectFrekuensi(freq) {
    TS.frekuensi = freq;
    TS.year_from = TS.year_to = TS.period_from = TS.period_to = null;
    TS.page      = 1;

    // Highlight tombol aktif
    document.querySelectorAll('.freq-btn').forEach(btn => {
        btn.classList.remove('border-violet-500','bg-violet-50','border-amber-400','bg-amber-50');
        btn.classList.add('border-gray-200');
    });
    const ab = document.getElementById('freq-btn-' + freq);
    if (ab) {
        ab.classList.remove('border-gray-200');
        if (freq === 'custom') {
            ab.classList.add('border-amber-400','bg-amber-50');
        } else {
            ab.classList.add('border-violet-500','bg-violet-50');
        }
    }

    _resetDataTable();
    document.getElementById('stepTampilkan').classList.add('hidden');
    document.getElementById('stepPeriode').classList.remove('hidden');

    // Helper text
    const helperMap = {
        '10tahunan'  : '10 Tahunan: setiap titik = 1 dekade (10 tahun). Jumlah kolom = jumlah dekade dalam rentang.',
        'tahunan'    : 'Tahunan: 1 kolom per tahun. Misal 2020–2025 = 6 kolom.',
        'semesteran' : 'Semesteran: 1 kolom per semester (S1/S2). Misal 2021–2022 = 4 kolom.',
        'kuartal'    : 'Kuartal: 1 kolom per kuartal (Q1–Q4). Misal 2021–2022 = 8 kolom.',
        'bulanan'    : 'Bulanan: 1 kolom per bulan. Misal 2021–2022 = 24 kolom.',
        'custom'     : 'Custom: pilih tahun bebas dan satuan tampilan kolom.',
    };
    const helperEl  = document.getElementById('periodeHelperText');
    const helperMsg = document.getElementById('periodeHelperMsg');
    if (helperMap[freq]) {
        helperEl.classList.remove('hidden');
        helperMsg.textContent = helperMap[freq];
    } else {
        helperEl.classList.add('hidden');
    }

    // Sembunyikan semua panel periode
    ['periode10tahunan','periodeTahunan','periodeComplex','periodeCustom']
        .forEach(id => document.getElementById(id)?.classList.add('hidden'));

    if (freq === '10tahunan') {
        document.getElementById('periode10tahunan').classList.remove('hidden');
        _buildPreset10();
    } else if (freq === 'tahunan') {
        document.getElementById('periodeTahunan').classList.remove('hidden');
    } else if (freq === 'custom') {
        document.getElementById('periodeCustom').classList.remove('hidden');
    } else {
        // semesteran, kuartal, bulanan
        document.getElementById('periodeComplex').classList.remove('hidden');
        const labelMap = { 'semesteran':'Semester', 'kuartal':'Kuartal', 'bulanan':'Bulan' };
        document.getElementById('periodeComplexLabel').textContent = labelMap[freq] ?? 'Periode';

        const opts = TP_PERIODE_OPTS[freq] ?? [];
        ['periodFromComplex','periodToComplex'].forEach(selId => {
            const el = document.getElementById(selId);
            if (!el) return;
            el.innerHTML = '<option value="">Semua (opsional)</option>';
            opts.forEach(([v, label]) => {
                const o = document.createElement('option');
                o.value = v; o.textContent = label;
                el.appendChild(o);
            });
        });
        ['yearFrom','yearTo','periodFromComplex','periodToComplex']
            .forEach(id => { const el = document.getElementById(id); if (el) el.value = ''; });
    }
}

// ─── Preset tombol cepat 10tahunan ────────────────────────────
function _buildPreset10() {
    const container = document.getElementById('preset10tahunan');
    container.innerHTML = '';
    const curYear   = new Date().getFullYear();
    const curDecade = Math.floor(curYear / 10) * 10;
    const presets   = [
        { from: curDecade,      to: curDecade,      label: 'Dekade Ini' },
        { from: curDecade - 10, to: curDecade,       label: '2 Dekade Terakhir' },
        { from: curDecade - 20, to: curDecade,       label: '3 Dekade Terakhir' },
        { from: 1990,           to: curDecade,       label: 'Sejak 1990' },
    ];
    presets.forEach(p => {
        const btn = document.createElement('button');
        btn.type  = 'button';
        btn.textContent = p.label;
        btn.className   = 'px-3 py-1.5 text-xs border border-violet-200 text-violet-600 bg-violet-50 hover:bg-violet-100 rounded-lg transition-colors font-medium';
        btn.onclick = () => {
            document.getElementById('from10').value = p.from;
            document.getElementById('to10').value   = p.to;
            checkPeriode10();
        };
        container.appendChild(btn);
    });
}

// ─── Preset "N tahun terakhir" ────────────────────────────────
function applyPresetTahunan(n) {
    const curYear = new Date().getFullYear();
    document.getElementById('fromTahunan').value = curYear - n + 1;
    document.getElementById('toTahunan').value   = curYear;
    checkPeriodeTahunan();
}

function applyPresetComplex(n) {
    const curYear = new Date().getFullYear();
    document.getElementById('yearFrom').value = curYear - n + 1;
    document.getElementById('yearTo').value   = curYear;
    checkPeriodeComplex();
}

// ─── STEP 3 — Cek validitas & preview kolom ──────────────────

function checkPeriode10() {
    const f = +document.getElementById('from10').value;
    const t = +document.getElementById('to10').value;
    if (f && t) {
        TS.period_from = f; TS.period_to = t;
        TS.year_from   = f; TS.year_to   = t;
        const count = Math.floor((t - f) / 10) + 1;
        showKolomPreview('tahunanKolom', 'tahunanKolomInfo', `→ ${count} kolom dekade`);
        document.getElementById('stepTampilkan').classList.remove('hidden');
    } else {
        document.getElementById('stepTampilkan').classList.add('hidden');
    }
}

function checkPeriodeTahunan() {
    const f = +document.getElementById('fromTahunan').value;
    const t = +document.getElementById('toTahunan').value;
    if (f && t) {
        TS.year_from   = f; TS.year_to   = t;
        TS.period_from = f; TS.period_to = t;
        const count = t - f + 1;
        showKolomPreview('tahunanKolomTahunan', 'tahunanKolomInfoTahunan', `→ ${count} kolom tahun`);
        document.getElementById('stepTampilkan').classList.remove('hidden');
    } else {
        document.getElementById('stepTampilkan').classList.add('hidden');
    }
}

function checkPeriodeComplex() {
    const yFrom = +document.getElementById('yearFrom').value;
    const yTo   = +document.getElementById('yearTo').value;
    const pFrom = document.getElementById('periodFromComplex').value;
    const pTo   = document.getElementById('periodToComplex').value;

    if (yFrom && yTo) {
        TS.year_from   = yFrom;
        TS.year_to     = yTo;
        TS.period_from = pFrom ? +pFrom : null;
        TS.period_to   = pTo   ? +pTo   : null;

        const years   = yTo - yFrom + 1;
        const perYear = { semesteran: 2, kuartal: 4, bulanan: 12 }[TS.frekuensi] ?? 1;
        let count     = years * perYear;
        if (pFrom && pTo) count = years * (pTo - pFrom + 1);
        const unit = { semesteran:'semester', kuartal:'kuartal', bulanan:'bulan' }[TS.frekuensi] ?? 'periode';
        showKolomPreview('complexKolom', 'complexKolomInfo', `→ estimasi ${count} kolom ${unit}`);
        document.getElementById('stepTampilkan').classList.remove('hidden');
    } else {
        document.getElementById('stepTampilkan').classList.add('hidden');
    }
}

function checkPeriodeCustom() {
    const f    = +document.getElementById('customYearFrom').value;
    const t    = +document.getElementById('customYearTo').value;
    const unit = document.getElementById('customUnit').value;

    if (f && t) {
        TS.year_from   = f;
        TS.year_to     = t;
        TS.period_from = null;
        TS.period_to   = null;
        TS.custom_unit = unit;
        TS.frekuensi   = unit;

        const years   = t - f + 1;
        const perYear = { tahunan: 1, semesteran: 2, kuartal: 4, bulanan: 12 }[unit] ?? 1;
        const count   = years * perYear;
        const label   = { tahunan:'tahun', semesteran:'semester', kuartal:'kuartal', bulanan:'bulan' }[unit];
        showKolomPreview('customKolom', 'customKolomInfo', `→ ${count} kolom ${label} (${f}–${t})`);
        document.getElementById('stepTampilkan').classList.remove('hidden');
    } else {
        document.getElementById('stepTampilkan').classList.add('hidden');
    }
}

function showKolomPreview(textId, wrapId, text) {
    const el = document.getElementById(textId);
    const wr = document.getElementById(wrapId);
    if (el) el.textContent = text;
    if (wr) wr.classList.remove('hidden');
}

// ─────────────────────────────────────────────────────────────
// STEP 4 — Fetch & render tabel pivot
// ─────────────────────────────────────────────────────────────
async function tampilkanData(page = 1) {
    TS.page = page;

    const section    = document.getElementById('dataTableSection');
    const loading    = document.getElementById('tableLoading');
    const empty      = document.getElementById('tableEmpty');
    const tableWrap  = document.getElementById('tableWrap');
    const pagination = document.getElementById('tablePagination');

    section.classList.remove('hidden');
    loading.classList.remove('hidden');
    empty.classList.add('hidden');
    tableWrap.classList.add('hidden');
    pagination.classList.add('hidden');
    section.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

    try {
        const payload = {
            tampilan_id : TS.tampilan_id,
            frekuensi   : TS.frekuensi,
            year_from   : TS.year_from,
            year_to     : TS.year_to,
            period_from : TS.period_from,
            period_to   : TS.period_to,
            page        : page,
        };

        const res = await fetch(TMPL_URLS.tableData, {
            method  : 'POST',
            headers : {
                'Content-Type' : 'application/json',
                'X-CSRF-TOKEN' : TMPL_URLS.csrf,
                'Accept'       : 'application/json',
            },
            body: JSON.stringify(payload),
        });

        const d = await res.json();
        loading.classList.add('hidden');

        if (!d.success || !d.rows?.length) {
            empty.classList.remove('hidden');
            document.getElementById('tableEmptyMsg').textContent =
                d.message ?? 'Tidak ada data ditemukan pada rentang periode ini.';
            return;
        }

        _renderTable(d);

    } catch (e) {
        loading.classList.add('hidden');
        empty.classList.remove('hidden');
        document.getElementById('tableEmptyMsg').textContent =
            'Terjadi kesalahan saat memuat data. Silakan coba lagi.';
        console.error(e);
    }
}

// ─── Deteksi level wilayah ────────────────────────────────────
function _getLokasiLevel(row) {
    if (row.lokasi_level !== undefined && row.lokasi_level !== null) {
        return row.lokasi_level;
    }
    const locId = String(row.location_id ?? '');
    if (locId.endsWith('00000000')) return 0;
    if (locId.endsWith('000000'))   return 1;
    if (locId.endsWith('0000'))     return 2;
    return 3;
}

// ─────────────────────────────────────────────────────────────
// RENDER TABEL PIVOT
// ─────────────────────────────────────────────────────────────
function _renderTable(d) {
    const head      = document.getElementById('pivotHead');
    const body      = document.getElementById('pivotBody');
    const wrap      = document.getElementById('tableWrap');
    const pag       = document.getElementById('tablePagination');
    const infoText  = document.getElementById('tableInfoText');
    const subInfo   = document.getElementById('tableSubInfo');
    const cols      = d.columns;   // [{label, meta}]
    const rows      = d.rows;

    const periodLabel = {
        '10tahunan':'Dekade','5tahunan':'Periode (5 Tahunan)','tahunan':'Tahun',
        'semesteran':'Semester','kuartal':'Kuartal','bulanan':'Bulan','custom':'Periode Custom',
    }[d.frekuensi] ?? 'Periode';

    // ── Baris header 1: Nama | Periode (colspan) | Satuan | Sumber | Aksi
    // ── Baris header 2: [kosong] | kolom per periode | [rowspan dari baris 1]
    head.innerHTML = `
        <tr class="border-b border-gray-200">
            <th class="px-4 py-3 text-left font-semibold text-gray-600 text-xs
                       col-sticky col-sticky-nama whitespace-nowrap"
                rowspan="2">
                Nama Metadata / Wilayah
            </th>
            <th class="px-4 py-3 text-center font-semibold text-gray-600 text-xs
                       whitespace-nowrap border-l border-gray-200"
                colspan="${cols.length}">
                ${_esc(periodLabel)}
            </th>
            <th class="px-4 py-3 text-center font-semibold text-gray-600 text-xs
                       whitespace-nowrap border-l border-gray-200"
                rowspan="2" style="min-width:70px">
                Satuan
            </th>
            <th class="px-4 py-3 text-left font-semibold text-gray-600 text-xs
                       whitespace-nowrap border-l border-gray-200"
                rowspan="2" style="min-width:150px">
                Sumber
            </th>
            <th class="px-4 py-3 text-center font-semibold text-gray-600 text-xs
                       whitespace-nowrap border-l border-gray-200"
                rowspan="2" style="min-width:80px">
                Aksi
            </th>
        </tr>
        <tr class="border-b border-gray-200">
            ${cols.map((c, i) =>
                `<th class="px-3 py-2 text-center font-semibold text-gray-600 text-xs
                             whitespace-nowrap border-r border-gray-100
                             ${i === 0 ? 'border-l border-gray-200' : ''}"
                     style="min-width:90px">
                    ${_esc(c.label)}
                </th>`
            ).join('')}
        </tr>`;

    // ── Group rows per metadata ────────────────────────────────
    const grouped   = {};
    const metaOrder = [];
    rows.forEach(row => {
        const key = String(row.metadata_id);
        if (!grouped[key]) {
            grouped[key] = { nama: row.nama, klasifikasi: row.klasifikasi, rows: [] };
            metaOrder.push(key);
        }
        grouped[key].rows.push(row);
    });

    let html   = '';
    let rowIdx = 0;

    metaOrder.forEach((key, mIdx) => {
        const group = grouped[key];

        // Urutkan per level wilayah
        group.rows.sort((a, b) => {
            const la = _getLokasiLevel(a);
            const lb = _getLokasiLevel(b);
            if (la !== lb) return la - lb;
            return (a.lokasi ?? '').localeCompare(b.lokasi ?? '');
        });

        group.rows.forEach((row, ri) => {
            const level      = _getLokasiLevel(row);
            const isFirstRow = ri === 0;
            const span       = group.rows.length;
            rowIdx++;

            const levelIcons = ['🏛️','🏙️','🏘️','🏠'];
            const levelNames = ['Provinsi','Kabupaten','Kecamatan','Desa'];
            const locIcon    = levelIcons[level] ?? '';
            const locTitle   = levelNames[level] ?? '';

            // Nilai per kolom
            const cells = cols.map((c, ci) => {
                const val = row.values?.[c.label];
                const fmt = (val !== null && val !== undefined && val !== '')
                    ? parseFloat(val).toLocaleString('id-ID', {
                          minimumFractionDigits: 0, maximumFractionDigits: 2,
                      })
                    : '<span class="text-gray-200 select-none">—</span>';
                return `<td class="px-3 py-2.5 text-right font-mono text-xs
                                   border-r border-gray-100 whitespace-nowrap
                                   ${ci === 0 ? 'border-l border-gray-200' : ''}">
                            ${fmt}
                        </td>`;
            }).join('');

            html += `<tr>`;

            // ── Kolom sticky: Nama metadata + wilayah ──
            html += `
                <td class="px-3 py-2.5 col-sticky col-sticky-nama align-top">
                    <div class="flex flex-col gap-0.5">
                        ${isFirstRow ? `
                            <p class="text-xs font-bold text-gray-800 leading-tight">
                                ${_esc(group.nama)}
                            </p>
                            ${group.klasifikasi
                                ? `<p class="text-gray-400 text-[10px] mb-1">${_esc(group.klasifikasi)}</p>`
                                : ''}
                        ` : ''}
                        <span class="loc-indent-${level} flex items-center gap-1 text-xs
                                     ${level === 0 ? 'font-semibold text-gray-700' : ''}
                                     ${level === 1 ? 'text-gray-600' : ''}
                                     ${level === 2 ? 'text-gray-500' : ''}
                                     ${level === 3 ? 'text-gray-400 text-[10px]' : ''}"
                             title="${locTitle}">
                            <span class="text-gray-300">${locIcon}</span>
                            ${_esc(row.lokasi ?? '-')}
                        </span>
                    </div>
                </td>`;

            html += cells;

            // ── Satuan + Sumber: rowspan hanya di baris pertama (sama untuk semua lokasi) ──
            if (isFirstRow) {
                html += `
                    <td class="px-3 py-2.5 text-xs text-gray-500 text-center
                               border-l border-gray-200 whitespace-nowrap align-middle"
                        rowspan="${span}">
                        ${_esc(row.satuan ?? '-')}
                    </td>
                    <td class="px-3 py-2.5 text-xs text-gray-400 align-top border-l border-gray-200"
                        style="max-width:180px" title="${_esc(row.sumber ?? '')}"
                        rowspan="${span}">
                        <span class="line-clamp-3">${_esc(row.sumber ?? '-')}</span>
                    </td>`;
            }

            // ── Aksi/Grafik: muncul di SETIAP baris (per lokasi) ──
            const grafikUrl = `${TMPL_URLS.grafik}?metadata_id=${row.metadata_id}&location_id=${row.location_id ?? ''}`;
            const locLabel  = row.lokasi ? _esc(row.lokasi) : 'Semua Wilayah';
            html += `
                <td class="px-3 py-2.5 text-center align-middle border-l border-gray-200 whitespace-nowrap">
                    <a href="${grafikUrl}"
                       class="btn-grafik"
                       title="Grafik: ${_esc(group.nama)} — ${locLabel}">
                        <i class="fas fa-chart-bar"></i>
                        Grafik
                    </a>
                </td>`;

            html += `</tr>`;
        });

        // Separator antar metadata
        if (mIdx < metaOrder.length - 1) {
            const totalCols = 1 + cols.length + 3; // sticky + data cols + satuan+sumber+aksi
            html += `<tr class="metadata-separator"><td colspan="${totalCols}"></td></tr>`;
        }
    });

    body.innerHTML = html;
    wrap.classList.remove('hidden');

    // ── Info bar ─────────────────────────────────────────────
    const start = (d.current_page - 1) * d.per_page + 1;
    const end   = Math.min(d.current_page * d.per_page, d.total);
    infoText.textContent = `Menampilkan ${start}–${end} dari ${d.total} baris`;
    const tmplName = document.querySelector('[class*="border-violet-500"] p.font-semibold')?.textContent?.trim() ?? '';
    subInfo.textContent = [
        tmplName ? `Template: ${tmplName}` : '',
        `Frekuensi: ${TP_FREK_LABEL[d.frekuensi] ?? d.frekuensi}`,
        `${cols.length} kolom periode`,
    ].filter(Boolean).join(' · ');

    // ── Pagination ────────────────────────────────────────────
    if (d.last_page > 1) {
        pag.classList.remove('hidden');
        document.getElementById('paginationInfo').textContent =
            `Halaman ${d.current_page} dari ${d.last_page}`;

        let pages = [];
        for (let p = 1; p <= d.last_page; p++) {
            if (p === 1 || p === d.last_page || Math.abs(p - d.current_page) <= 2)
                pages.push(p);
            else if (pages[pages.length - 1] !== '...') pages.push('...');
        }
        document.getElementById('paginationBtns').innerHTML = pages.map(p =>
            p === '...'
            ? `<span class="w-7 h-7 flex items-center justify-center text-gray-400 text-xs">…</span>`
            : `<button onclick="tampilkanData(${p})"
                       class="w-7 h-7 rounded-md text-xs font-medium transition-colors
                              ${p === d.current_page
                                ? 'bg-violet-500 text-white'
                                : 'border border-gray-300 text-gray-500 hover:bg-gray-50'}">
                   ${p}</button>`
        ).join('');
    }
}

// ─── Export CSV ───────────────────────────────────────────────
function exportCsv() {
    const table = document.getElementById('pivotTable');
    if (!table) return;
    const rows = [];
    table.querySelectorAll('tr').forEach(tr => {
        const cols = [];
        tr.querySelectorAll('th, td').forEach(td => {
            cols.push('"' + td.innerText.replace(/"/g,'""').replace(/\n/g,' ').trim() + '"');
        });
        if (cols.length) rows.push(cols.join(','));
    });
    const blob = new Blob(['\uFEFF' + rows.join('\n')], {type:'text/csv;charset=utf-8;'});
    const url  = URL.createObjectURL(blob);
    const a    = document.createElement('a');
    a.href = url; a.download = `data-template-${TS.tampilan_id ?? 'export'}.csv`;
    a.click(); URL.revokeObjectURL(url);
}

// ─── Reset ────────────────────────────────────────────────────
function resetFilter() { window.location.href = TMPL_URLS.base; }

function _resetDataTable() {
    ['dataTableSection','tableLoading','tableEmpty','tableWrap','tablePagination']
        .forEach(id => document.getElementById(id)?.classList.add('hidden'));
}

function _esc(str) {
    const d = document.createElement('div');
    d.innerText = str ?? '';
    return d.innerHTML;
}

// ─── Init ─────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    if (TS.tampilan_id) {
        loadFreqCounts(TS.tampilan_id);
    }
});
</script>