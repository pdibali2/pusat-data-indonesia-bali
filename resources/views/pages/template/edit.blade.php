@extends('layouts.main')

@section('content')

<style>
    .preview-badge        { background:#f0f9ff; color:#0284c7; border-color:#bae6fd; }
    #selectionBarPreview  { background:#f0fdf4; border-color:#bbf7d0; }
    #selectionBarPreview p { color:#15803d; }
    #selectionBarPreview button { color:#16a34a; }
</style>

<div class="mt-2 bg-white rounded-xl shadow p-6">

    {{-- BREADCRUMB --}}
    <div class="flex items-center gap-2 text-sm text-gray-400 mb-5">
        <a href="{{ route('data.index') }}" class="hover:text-sky-500 transition-colors">Data</a>
        <i class="fas fa-chevron-right text-xs"></i>
        <span class="text-gray-600 font-medium">Edit Template</span>
    </div>

    @php
        $jenisLabel = ['metadata' => 'Metadata', 'klasifikasi' => 'Klasifikasi', 'wilayah' => 'Wilayah'][$jenis] ?? $jenis;
        $jenisColor = ['metadata' => 'sky', 'klasifikasi' => 'violet', 'wilayah' => 'emerald'][$jenis] ?? 'sky';
        $jenisIcon  = ['metadata' => 'fa-database', 'klasifikasi' => 'fa-tags', 'wilayah' => 'fa-map-marker-alt'][$jenis] ?? 'fa-database';
    @endphp

    {{-- HEADER --}}
    <div class="mb-6 flex items-start justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Edit Template</h1>
            <p class="text-sm text-gray-400 mt-1">Ubah nama, wilayah, dan isi metadata template Anda</p>
        </div>
        <span class="hidden sm:block px-3 py-1.5 bg-{{ $jenisColor }}-50 text-{{ $jenisColor }}-600 border border-{{ $jenisColor }}-100 text-xs font-semibold rounded-full">
            <i class="fas {{ $jenisIcon }} mr-1"></i> Jenis: {{ $jenisLabel }}
        </span>
    </div>

    @if(session('success'))
        <div class="mb-4 flex items-center gap-3 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">
            <i class="fas fa-check-circle text-green-500 shrink-0"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- ── NAMA TEMPLATE ── --}}
    <div class="p-5 border border-gray-200 rounded-xl mb-2 bg-gray-50/40">
        <h2 class="text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
            <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-sky-500 text-white text-xs font-bold">1</span>
            Nama Template
        </h2>
        <input type="text"
               id="inputNamaTampilan"
               value="{{ old('nama_tampilan', $tampilan->nama_tampilan) }}"
               maxlength="100"
               required
               placeholder="Nama template..."
               class="w-full max-w-lg border border-gray-300 rounded-lg px-3 py-2.5 text-sm
                      focus:outline-none focus:ring-2 focus:ring-gray-400 bg-white">
        @error('nama_tampilan')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    {{-- ═══════════════════════════════════════════════════════════
         SECTION 2 — PILIH METADATA & WILAYAH (per jenis)
    ═══════════════════════════════════════════════════════════ --}}
    @if($jenis === 'metadata')
    <div class="p-5 border border-gray-200 rounded-xl mb-2">
        <h2 class="text-sm font-bold text-gray-700 mb-4">
            <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-sky-500 text-white text-xs font-bold mr-2">2</span>
            Metadata &amp; Wilayah
        </h2>

        <div class="space-y-4">

            {{-- ── METADATA: chips existing (read-only) + tambah baru ── --}}
            <div>
                <label class="block text-xs text-gray-500 font-medium mb-1">
                    <i class="fas fa-database mr-1 text-gray-400"></i> Metadata dalam template
                </label>

                {{-- Chips existing (read-only) --}}
                <div id="existingMetaChips" class="flex flex-wrap gap-1.5 mb-2"></div>

                {{-- Search untuk tambah metadata baru --}}
                <div class="relative" id="metaDropWrap">
                    <input type="text" id="metaSearch"
                            placeholder="Ketik untuk menambah metadata baru ke template..."
                            autocomplete="off"
                            oninput="onMetaSearchInput()"
                            onfocus="onMetaSearchFocus()"
                            class="w-full border border-gray-300 rounded-lg pl-8 pr-4 py-2.5 text-sm
                                    focus:outline-none focus:ring-2 focus:ring-sky-400 transition-shadow">
                    <i class="fas fa-search absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>

                    <div id="metaDropList"
                         class="hidden absolute z-20 w-full mt-1 bg-white border border-gray-200
                                rounded-xl shadow-xl max-h-72 overflow-y-auto">
                        <p class="px-4 py-3 text-xs text-gray-400 text-center">Mulai ketik untuk mencari metadata</p>
                    </div>
                </div>

                {{-- Chips metadata baru (bisa dihapus) --}}
                <div id="selectedMetaChips" class="flex flex-wrap gap-1.5 mt-2 min-h-6"></div>
            </div>

            {{-- ── INFO: lokasi tersimpan per metadata ── --}}
            <div id="savedLocationsInfo" class="hidden">
                <label class="block text-xs text-gray-500 font-medium mb-1.5">
                    <i class="fas fa-map-marker-alt mr-1 text-gray-400"></i> Lokasi tersimpan di template
                </label>
                <div id="savedLocationsChips" class="flex flex-wrap gap-1.5"></div>
            </div>

            {{-- ── WILAYAH cascade — untuk filter preview baru ── --}}
            <div>
                <label class="block text-xs text-gray-500 font-medium mb-2">
                    <i class="fas fa-filter mr-1 text-gray-400"></i> Filter wilayah untuk preview
                    <span class="text-gray-400 font-normal">(opsional — kosongkan untuk lihat semua lokasi tersimpan)</span>
                </label>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

                    {{-- PROVINSI --}}
                    <div>
                        <label class="block text-xs text-gray-500 font-semibold mb-1.5 uppercase tracking-wide">Provinsi</label>
                        <div class="relative" id="wrapProvinsi">
                            <input type="text" id="inputProvinsi" placeholder="Cari provinsi..."
                                autocomplete="off"
                                oninput="onWilInput('provinsi')"
                                onfocus="onWilFocus('provinsi')"
                                class="w-full border border-gray-300 rounded-lg pl-7 pr-7 py-2 text-sm
                                    focus:outline-none focus:ring-2 focus:ring-emerald-400 bg-white">
                            <i class="fas fa-search absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                            <button type="button" id="clearProvinsi" onclick="clearLevel('provinsi')"
                                class="hidden absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 text-sm leading-none">×</button>
                            <div id="dropProvinsi"
                                class="hidden absolute z-30 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-xl max-h-52 overflow-y-auto"></div>
                        </div>
                        <input type="hidden" id="valProvinsi">
                    </div>

                    {{-- KABUPATEN --}}
                    <div>
                        <label class="block text-xs text-gray-500 font-semibold mb-1.5 uppercase tracking-wide">Kabupaten / Kota</label>
                        <div class="relative" id="wrapKabupaten">
                            <input type="text" id="inputKabupaten" placeholder="Pilih provinsi dulu..."
                                autocomplete="off" disabled
                                oninput="onWilInput('kabupaten')"
                                onfocus="onWilFocus('kabupaten')"
                                class="w-full border border-gray-200 rounded-lg pl-7 pr-7 py-2 text-sm
                                    focus:outline-none focus:ring-2 focus:ring-emerald-400 bg-gray-100 text-gray-400 cursor-not-allowed">
                            <i class="fas fa-search absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-300 text-xs pointer-events-none"></i>
                            <button type="button" id="clearKabupaten" onclick="clearLevel('kabupaten')"
                                class="hidden absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 text-sm leading-none">×</button>
                            <div id="dropKabupaten"
                                class="hidden absolute z-30 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-xl max-h-52 overflow-y-auto"></div>
                        </div>
                        <input type="hidden" id="valKabupaten">
                    </div>

                    {{-- KECAMATAN --}}
                    <div>
                        <label class="block text-xs text-gray-500 font-semibold mb-1.5 uppercase tracking-wide">Kecamatan</label>
                        <div class="relative" id="wrapKecamatan">
                            <input type="text" id="inputKecamatan" placeholder="Pilih kabupaten dulu..."
                                autocomplete="off" disabled
                                oninput="onWilInput('kecamatan')"
                                onfocus="onWilFocus('kecamatan')"
                                class="w-full border border-gray-200 rounded-lg pl-7 pr-7 py-2 text-sm
                                    focus:outline-none focus:ring-2 focus:ring-emerald-400 bg-gray-100 text-gray-400 cursor-not-allowed">
                            <i class="fas fa-search absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-300 text-xs pointer-events-none"></i>
                            <button type="button" id="clearKecamatan" onclick="clearLevel('kecamatan')"
                                class="hidden absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 text-sm leading-none">×</button>
                            <div id="dropKecamatan"
                                class="hidden absolute z-30 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-xl max-h-52 overflow-y-auto"></div>
                        </div>
                        <input type="hidden" id="valKecamatan">
                    </div>

                    {{-- DESA --}}
                    <div>
                        <label class="block text-xs text-gray-500 font-semibold mb-1.5 uppercase tracking-wide">Desa / Kelurahan</label>
                        <div class="relative" id="wrapDesa">
                            <input type="text" id="inputDesa" placeholder="Pilih kecamatan dulu..."
                                autocomplete="off" disabled
                                oninput="onWilInput('desa')"
                                onfocus="onWilFocus('desa')"
                                class="w-full border border-gray-200 rounded-lg pl-7 pr-7 py-2 text-sm
                                    focus:outline-none focus:ring-2 focus:ring-emerald-400 bg-gray-100 text-gray-400 cursor-not-allowed">
                            <i class="fas fa-search absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-300 text-xs pointer-events-none"></i>
                            <button type="button" id="clearDesa" onclick="clearLevel('desa')"
                                class="hidden absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 text-sm leading-none">×</button>
                            <div id="dropDesa"
                                class="hidden absolute z-30 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-xl max-h-52 overflow-y-auto"></div>
                        </div>
                        <input type="hidden" id="valDesa">
                    </div>
                </div>

                {{-- Badge wilayah terpilih --}}
                <div id="m_selectedWilayahBadge"
                     class="hidden mt-3 flex items-center gap-2 p-2.5 bg-sky-50 border border-sky-200 rounded-lg w-fit">
                    <i class="fas fa-map-marker-alt text-sky-500 text-xs"></i>
                    <span class="text-xs text-sky-700 font-semibold" id="m_badgeNama">—</span>
                    <span class="text-xs text-sky-500" id="m_badgeLevel">—</span>
                </div>
            </div>

            <button type="button" id="btnPilih" onclick="loadMetadataPreview()"
                class="px-5 py-2.5 bg-sky-500 hover:bg-sky-600 active:bg-sky-700 text-white text-sm font-semibold rounded-lg
                       shadow-md shadow-sky-400/30 flex items-center gap-2 transition-all">
                <i class="fas fa-search"></i> Muat Ulang Preview
            </button>
        </div>
    </div>

    @elseif($jenis === 'klasifikasi')
    <div class="p-5 border border-gray-200 rounded-xl mb-2 bg-gray-50/40">
        <h2 class="text-sm font-bold text-gray-700 mb-4 flex items-center gap-2">
            <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-violet-500 text-white text-xs font-bold">2</span>
            Klasifikasi &amp; Wilayah
        </h2>

        {{-- KLASIFIKASI --}}
        <div class="mb-4">
            <label class="block text-xs text-gray-500 font-semibold mb-1.5 uppercase tracking-wide">
                Klasifikasi <span class="text-red-500">*</span>
            </label>

            <div class="relative" id="wrapKlasifikasi">
                <input
                    type="text"
                    id="inputKlasifikasi"
                    placeholder="Cari klasifikasi..."
                    autocomplete="off"
                    oninput="onKlasifikasiInput()"
                    onfocus="onKlasifikasiFocus()"
                    value="{{ $selectedKlasifikasi }}"
                    class="w-full border border-gray-300 rounded-lg pl-7 pr-7 py-2 text-sm
                        focus:outline-none focus:ring-2 focus:ring-violet-400 bg-white"
                >
                <i class="fas fa-search absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                <button
                    type="button"
                    id="clearKlasifikasi"
                    onclick="clearKlasifikasi()"
                    class="{{ $selectedKlasifikasi ? '' : 'hidden' }} absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 text-sm leading-none"
                >×</button>
                <div
                    id="dropKlasifikasi"
                    class="hidden absolute z-30 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-xl max-h-60 overflow-y-auto"
                ></div>
            </div>
            <input type="hidden" id="valKlasifikasi" value="{{ $selectedKlasifikasi }}">
        </div>

        {{-- INFO: lokasi tersimpan --}}
        <div id="savedLocationsInfo" class="hidden mb-4">
            <label class="block text-xs text-gray-500 font-medium mb-1.5">
                <i class="fas fa-map-marker-alt mr-1 text-gray-400"></i> Lokasi tersimpan di template
            </label>
            <div id="savedLocationsChips" class="flex flex-wrap gap-1.5"></div>
        </div>

        {{-- WILAYAH cascade --}}
        <div class="mb-4">
            <label class="block text-xs text-gray-500 font-medium mb-2">
                <i class="fas fa-filter mr-1 text-gray-400"></i> Filter wilayah untuk preview
                <span class="text-gray-400 font-normal">(opsional — kosongkan untuk lihat semua lokasi tersimpan)</span>
            </label>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

                {{-- PROVINSI --}}
                <div>
                    <label class="block text-xs text-gray-500 font-semibold mb-1.5 uppercase tracking-wide">Provinsi</label>
                    <div class="relative" id="wrapProvinsi">
                        <input type="text" id="inputProvinsi" placeholder="Cari provinsi..."
                            autocomplete="off"
                            oninput="onWilInput('provinsi')"
                            onfocus="onWilFocus('provinsi')"
                            class="w-full border border-gray-300 rounded-lg pl-7 pr-7 py-2 text-sm
                                focus:outline-none focus:ring-2 focus:ring-emerald-400 bg-white">
                        <i class="fas fa-search absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                        <button type="button" id="clearProvinsi" onclick="clearLevel('provinsi')"
                            class="hidden absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 text-sm leading-none">×</button>
                        <div id="dropProvinsi"
                            class="hidden absolute z-30 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-xl max-h-52 overflow-y-auto"></div>
                    </div>
                    <input type="hidden" id="valProvinsi">
                </div>

                {{-- KABUPATEN --}}
                <div>
                    <label class="block text-xs text-gray-500 font-semibold mb-1.5 uppercase tracking-wide">Kabupaten / Kota</label>
                    <div class="relative" id="wrapKabupaten">
                        <input type="text" id="inputKabupaten" placeholder="Pilih provinsi dulu..."
                            autocomplete="off" disabled
                            oninput="onWilInput('kabupaten')"
                            onfocus="onWilFocus('kabupaten')"
                            class="w-full border border-gray-200 rounded-lg pl-7 pr-7 py-2 text-sm
                                focus:outline-none focus:ring-2 focus:ring-emerald-400 bg-gray-100 text-gray-400 cursor-not-allowed">
                        <i class="fas fa-search absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-300 text-xs pointer-events-none"></i>
                        <button type="button" id="clearKabupaten" onclick="clearLevel('kabupaten')"
                            class="hidden absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 text-sm leading-none">×</button>
                        <div id="dropKabupaten"
                            class="hidden absolute z-30 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-xl max-h-52 overflow-y-auto"></div>
                    </div>
                    <input type="hidden" id="valKabupaten">
                </div>

                {{-- KECAMATAN --}}
                <div>
                    <label class="block text-xs text-gray-500 font-semibold mb-1.5 uppercase tracking-wide">Kecamatan</label>
                    <div class="relative" id="wrapKecamatan">
                        <input type="text" id="inputKecamatan" placeholder="Pilih kabupaten dulu..."
                            autocomplete="off" disabled
                            oninput="onWilInput('kecamatan')"
                            onfocus="onWilFocus('kecamatan')"
                            class="w-full border border-gray-200 rounded-lg pl-7 pr-7 py-2 text-sm
                                focus:outline-none focus:ring-2 focus:ring-emerald-400 bg-gray-100 text-gray-400 cursor-not-allowed">
                        <i class="fas fa-search absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-300 text-xs pointer-events-none"></i>
                        <button type="button" id="clearKecamatan" onclick="clearLevel('kecamatan')"
                            class="hidden absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 text-sm leading-none">×</button>
                        <div id="dropKecamatan"
                            class="hidden absolute z-30 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-xl max-h-52 overflow-y-auto"></div>
                    </div>
                    <input type="hidden" id="valKecamatan">
                </div>

                {{-- DESA --}}
                <div>
                    <label class="block text-xs text-gray-500 font-semibold mb-1.5 uppercase tracking-wide">Desa / Kelurahan</label>
                    <div class="relative" id="wrapDesa">
                        <input type="text" id="inputDesa" placeholder="Pilih kecamatan dulu..."
                            autocomplete="off" disabled
                            oninput="onWilInput('desa')"
                            onfocus="onWilFocus('desa')"
                            class="w-full border border-gray-200 rounded-lg pl-7 pr-7 py-2 text-sm
                                focus:outline-none focus:ring-2 focus:ring-emerald-400 bg-gray-100 text-gray-400 cursor-not-allowed">
                        <i class="fas fa-search absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-300 text-xs pointer-events-none"></i>
                        <button type="button" id="clearDesa" onclick="clearLevel('desa')"
                            class="hidden absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 text-sm leading-none">×</button>
                        <div id="dropDesa"
                            class="hidden absolute z-30 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-xl max-h-52 overflow-y-auto"></div>
                    </div>
                    <input type="hidden" id="valDesa">
                </div>
            </div>

            {{-- Badge wilayah terpilih --}}
            <div id="k_selectedWilayahBadge"
                 class="hidden mt-3 flex items-center gap-2.5 p-2.5 bg-violet-50 border border-violet-200 rounded-lg w-fit">
                <i class="fas fa-map-marker-alt text-violet-500 text-xs"></i>
                <span class="text-xs text-violet-700 font-semibold" id="k_badgeNama">—</span>
                <span class="text-xs text-violet-500" id="k_badgeLevel">—</span>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="button" id="btnPilih" onclick="loadKlasifikasiPreview()"
                class="px-5 py-2.5 bg-violet-500 text-white text-sm font-semibold rounded-lg
                       shadow-md flex items-center gap-2 transition-all
                       hover:bg-violet-600 active:scale-95">
                <i class="fas fa-search"></i> Muat Ulang Preview
            </button>
            <p class="text-xs text-gray-400" id="pilihHint"></p>
        </div>
    </div>

    @elseif($jenis === 'wilayah')
    <div class="p-5 border border-gray-200 rounded-xl mb-2 bg-gray-50/40">
        <h2 class="text-sm font-bold text-gray-700 mb-4 flex items-center gap-2">
            <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-emerald-500 text-white text-xs font-bold">2</span>
            Wilayah
            <span class="text-xs font-normal text-gray-400">(pilih sampai tingkat yang diinginkan)</span>
        </h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

            {{-- PROVINSI --}}
            <div>
                <label class="block text-xs text-gray-500 font-semibold mb-1.5 uppercase tracking-wide">Provinsi</label>
                <div class="relative" id="wrapProvinsi">
                    <input type="text" id="inputProvinsi" placeholder="Cari provinsi..."
                        autocomplete="off"
                        oninput="onWilInput('provinsi')"
                        onfocus="onWilFocus('provinsi')"
                        class="w-full border border-gray-300 rounded-lg pl-7 pr-7 py-2 text-sm
                               focus:outline-none focus:ring-2 focus:ring-emerald-400 bg-white">
                    <i class="fas fa-search absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                    <button type="button" id="clearProvinsi" onclick="clearLevel('provinsi')"
                        class="hidden absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 text-sm leading-none">×</button>
                    <div id="dropProvinsi"
                         class="hidden absolute z-30 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-xl max-h-52 overflow-y-auto"></div>
                </div>
                <input type="hidden" id="valProvinsi">
            </div>

            {{-- KABUPATEN --}}
            <div>
                <label class="block text-xs text-gray-500 font-semibold mb-1.5 uppercase tracking-wide">Kabupaten / Kota</label>
                <div class="relative" id="wrapKabupaten">
                    <input type="text" id="inputKabupaten" placeholder="Pilih provinsi dulu..."
                        autocomplete="off" disabled
                        oninput="onWilInput('kabupaten')"
                        onfocus="onWilFocus('kabupaten')"
                        class="w-full border border-gray-200 rounded-lg pl-7 pr-7 py-2 text-sm
                               focus:outline-none focus:ring-2 focus:ring-emerald-400 bg-gray-100 text-gray-400 cursor-not-allowed">
                    <i class="fas fa-search absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-300 text-xs pointer-events-none"></i>
                    <button type="button" id="clearKabupaten" onclick="clearLevel('kabupaten')"
                        class="hidden absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 text-sm leading-none">×</button>
                    <div id="dropKabupaten"
                         class="hidden absolute z-30 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-xl max-h-52 overflow-y-auto"></div>
                </div>
                <input type="hidden" id="valKabupaten">
            </div>

            {{-- KECAMATAN --}}
            <div>
                <label class="block text-xs text-gray-500 font-semibold mb-1.5 uppercase tracking-wide">Kecamatan</label>
                <div class="relative" id="wrapKecamatan">
                    <input type="text" id="inputKecamatan" placeholder="Pilih kabupaten dulu..."
                        autocomplete="off" disabled
                        oninput="onWilInput('kecamatan')"
                        onfocus="onWilFocus('kecamatan')"
                        class="w-full border border-gray-200 rounded-lg pl-7 pr-7 py-2 text-sm
                               focus:outline-none focus:ring-2 focus:ring-emerald-400 bg-gray-100 text-gray-400 cursor-not-allowed">
                    <i class="fas fa-search absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-300 text-xs pointer-events-none"></i>
                    <button type="button" id="clearKecamatan" onclick="clearLevel('kecamatan')"
                        class="hidden absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 text-sm leading-none">×</button>
                    <div id="dropKecamatan"
                         class="hidden absolute z-30 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-xl max-h-52 overflow-y-auto"></div>
                </div>
                <input type="hidden" id="valKecamatan">
            </div>

            {{-- DESA --}}
            <div>
                <label class="block text-xs text-gray-500 font-semibold mb-1.5 uppercase tracking-wide">Desa / Kelurahan</label>
                <div class="relative" id="wrapDesa">
                    <input type="text" id="inputDesa" placeholder="Pilih kecamatan dulu..."
                        autocomplete="off" disabled
                        oninput="onWilInput('desa')"
                        onfocus="onWilFocus('desa')"
                        class="w-full border border-gray-200 rounded-lg pl-7 pr-7 py-2 text-sm
                               focus:outline-none focus:ring-2 focus:ring-emerald-400 bg-gray-100 text-gray-400 cursor-not-allowed">
                    <i class="fas fa-search absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-300 text-xs pointer-events-none"></i>
                    <button type="button" id="clearDesa" onclick="clearLevel('desa')"
                        class="hidden absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 text-sm leading-none">×</button>
                    <div id="dropDesa"
                         class="hidden absolute z-30 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-xl max-h-52 overflow-y-auto"></div>
                </div>
                <input type="hidden" id="valDesa">
            </div>
        </div>

        {{-- Badge wilayah terpilih --}}
        <div id="w_selectedWilayahBadge" class="hidden mt-4 flex items-center gap-2.5 p-3 bg-emerald-50 border border-emerald-200 rounded-lg w-fit">
            <i class="fas fa-map-marker-alt text-emerald-500"></i>
            <div>
                <p class="text-xs text-emerald-700 font-semibold" id="w_badgeNama">—</p>
                <p class="text-xs text-emerald-500 mt-0.5" id="w_badgeLevel">—</p>
            </div>
        </div>

        <div class="mt-4 flex items-center gap-3">
            <button type="button" id="btnPilih" onclick="loadWilayahTable()"
                class="px-5 py-2.5 bg-emerald-500 text-white text-sm font-semibold rounded-lg
                       shadow-md flex items-center gap-2 transition-all
                       hover:bg-emerald-600">
                <i class="fas fa-search"></i> Muat Ulang Preview
            </button>
            <p class="text-xs text-gray-400" id="pilihHint"></p>
        </div>

        {{-- Info: wilayah tersimpan --}}
        @if($existingLocations->isNotEmpty())
            <div class="mt-3 flex flex-wrap gap-2">
                <span class="text-xs text-gray-400 self-center">Wilayah tersimpan:</span>
                @foreach($existingLocations as $loc)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-emerald-50 text-emerald-700 rounded-full border border-emerald-100 text-xs">
                        <i class="fas fa-map-marker-alt text-emerald-400 text-xs"></i>
                        {{ $loc->nama_wilayah }}
                    </span>
                @endforeach
            </div>
        @endif
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════
         SECTION 3 — PREVIEW TABEL (shared semua jenis)
    ═══════════════════════════════════════════════════════════ --}}
    <div id="sectionResult" class="hidden mt-6">

        {{-- TAB SWITCHER --}}
        <div class="border-b border-gray-200 mb-4">
            <div class="flex gap-1 overflow-x-auto" id="freqTabs">
                @foreach(['dekade' => 'Dekade', 'tahunan' => 'Tahunan', 'semester' => 'Semester', 'kuartal' => 'Kuartal', 'bulanan' => 'Bulanan'] as $key => $label)
                    <button type="button"
                        id="tab-{{ $key }}"
                        onclick="switchTab('{{ $key }}')"
                        class="tab-btn shrink-0 px-4 py-2.5 text-xs font-semibold border-b-2 transition-colors
                               border-transparent text-gray-400 cursor-not-allowed"
                        disabled>
                        {{ $label }}
                        <span id="tab-count-{{ $key }}"
                              class="ml-1.5 text-xs font-bold px-1.5 py-0.5 rounded-full bg-gray-100 text-gray-400">0</span>
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Info bar --}}
        <div class="flex items-center justify-between mb-3">
            <div>
                <h3 class="font-bold text-gray-700 text-sm">Hasil Metadata</h3>
                <p class="text-xs text-gray-400 mt-0.5" id="resultDesc">—</p>
            </div>
            <span id="totalBadge"
                  class="hidden sm:block text-xs bg-emerald-50 text-emerald-600 border border-emerald-100 px-2.5 py-1 rounded-full font-semibold">
                0 baris
            </span>
        </div>

        <div class="border border-gray-200 rounded-xl overflow-hidden">
            <table class="w-full table-auto text-sm text-left">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider border-b border-gray-200">
                    <tr>
                        <th class="w-10 px-2 py-3">
                            <input type="checkbox"
                                id="checkAll"
                                onchange="toggleAll(this)"
                                class="rounded border-gray-300 cursor-pointer">
                        </th>

                        <th class="px-4 py-3 font-semibold text-gray-600">
                            Metadata
                        </th>

                        <th class="w-14 px-3 py-3 text-center">
                            Info
                        </th>

                        <th class="w-16 px-3 py-3 text-center">
                            Detail
                        </th>
                    </tr>
                </thead>

                <tbody id="resultTbody"></tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div id="paginWrap" class="hidden mt-3 flex items-center justify-between text-xs text-gray-500">
            <span id="paginInfo" class="hidden sm:block"></span>
            <div id="paginBtns" class="flex gap-1 mx-auto sm:mx-0"></div>
        </div>

        {{-- Seleksi bar --}}
        <div id="selBar" class="mt-3 hidden items-center justify-between px-4 py-2.5 rounded-lg"
             style="background:#f0fdf4; border:1px solid #bbf7d0;">
            <p class="font-medium text-emerald-700 flex items-center gap-2 text-sm">
                <i class="fas fa-check-square"></i>
                <span id="selCount">0 metadata dipilih</span>
            </p>
            <button onclick="clearAllSel()" class="text-xs font-medium text-emerald-600 hover:underline">
                <i class="fas fa-times mr-1"></i> Batalkan Pilihan
            </button>
        </div>

        {{-- Pengaturan urutan --}}
        <div class="mt-5 p-4 bg-gray-50 border border-gray-200 rounded-xl">
            <p class="text-xs font-bold text-gray-600 mb-3 flex items-center gap-1.5">
                <i class="fas fa-sort-amount-down text-gray-400"></i> Pengaturan Urutan Tampilan
            </p>
            <div class="flex flex-col sm:flex-row sm:flex-wrap items-start gap-4">
                <label class="flex items-center gap-2 cursor-pointer select-none">
                    <input type="checkbox" id="chkKlasifikasi" class="rounded border-gray-300"
                           {{ ($fp['urutan_by'] ?? '') === 'klasifikasi' || ($fp['urutan_by'] ?? '') === 'keduanya' ? 'checked' : '' }}>
                    <span class="text-xs text-gray-600">Atur berdasarkan Klasifikasi</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer select-none">
                    <input type="checkbox" id="chkWilayah" class="rounded border-gray-300"
                           {{ ($fp['urutan_by'] ?? '') === 'wilayah' || ($fp['urutan_by'] ?? '') === 'keduanya' ? 'checked' : '' }}>
                    <span class="text-xs text-gray-600">Atur berdasarkan Wilayah</span>
                </label>
                <button type="button" onclick="terapkanUrutan()"
                    class="px-4 py-1.5 bg-gray-700 hover:bg-gray-800 text-white text-xs font-semibold rounded-lg transition-colors">
                    Terapkan
                </button>
            </div>
        </div>

        {{-- Tombol Simpan --}}
        <div class="mt-5 flex items-center justify-between gap-3">
            <a href="{{ route('data.index') }}"
               class="border border-gray-300 text-gray-500 hover:bg-gray-50 px-4 py-2.5 rounded-lg text-sm transition-colors">
                Batal
            </a>
            <button type="button" onclick="openSaveModal()"
                class="px-6 py-2.5 text-white text-sm font-semibold rounded-lg flex items-center gap-2 transition-colors"
                style="background:#8b5cf6;"
                onmouseover="this.style.background='#7c3aed'"
                onmouseout="this.style.background='#8b5cf6'">
                <i class="fas fa-save"></i> Simpan Perubahan
            </button>
        </div>
    </div>

</div>

{{-- ═══ MODAL KONFIRMASI SIMPAN ═══ --}}
<div id="modalSave"
     style="position:fixed;inset:0;z-index:50;background:rgba(0,0,0,0.45);display:none;align-items:center;justify-content:center;padding:1rem;">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md">
        <div class="px-6 py-4 rounded-t-xl" style="background:linear-gradient(135deg,#8b5cf6,#6d28d9);">
            <div class="flex items-center justify-between">
                <h3 class="text-white font-bold text-base flex items-center gap-2">
                    <i class="fas fa-save"></i> Konfirmasi Perubahan
                </h3>
                <button onclick="closeModal()" class="text-purple-200 hover:text-white text-2xl leading-none">×</button>
            </div>
        </div>
        <div class="p-6 space-y-3">
            <div class="p-3 bg-gray-50 border border-gray-200 rounded-lg">
                <p class="text-xs text-gray-500 font-semibold uppercase tracking-wide mb-1">Nama Template</p>
                <p class="text-sm text-gray-800 font-bold" id="confirmNama">—</p>
            </div>
            <div class="p-3 bg-purple-50 rounded-lg border border-purple-100 text-xs text-purple-700">
                <i class="fas fa-list-check mr-1"></i>
                <span id="modalMetaCount">0</span> metadata akan disimpan.
            </div>
            <p class="text-xs text-gray-400">
                <i class="fas fa-exclamation-triangle text-amber-400 mr-1"></i>
                Metadata/wilayah yang tidak dicentang akan dihapus dari template ini.
            </p>
        </div>
        <div class="px-6 py-4 border-t bg-gray-50 rounded-b-xl flex justify-end gap-2">
            <button onclick="closeModal()"
                class="border border-gray-300 text-gray-500 hover:bg-gray-100 px-4 py-2 rounded-lg text-sm">Batal</button>
            <button onclick="submitTemplate()"
                class="px-5 py-2 text-white text-sm font-semibold rounded-lg flex items-center gap-2"
                style="background:#8b5cf6;"
                onmouseover="this.style.background='#7c3aed'"
                onmouseout="this.style.background='#8b5cf6'">
                <i class="fas fa-save"></i> Ya, Simpan
            </button>
        </div>
    </div>
</div>

@include('pages.template.metadata-detail-modal')

{{-- HIDDEN FORM — submit ke update --}}
<form id="formSaveTemplateHidden" action="{{ route('template.update', $tampilan->tampilan_id) }}" method="POST" class="hidden">
    @csrf
    @method('PUT')
    <input type="hidden" name="nama_tampilan" id="hidNama">
    <input type="hidden" name="klasifikasi" id="hidKlasifikasi">
    <div id="hidMetadataIds"></div>
    <div id="hidLocationIds"></div>
    <div id="hidUrutanBy"></div>
</form>

<script>
// ═══════════════════════════════════════════════════════════════
// CONFIG & DATA AWAL DARI SERVER
// ═══════════════════════════════════════════════════════════════
const JENIS                 = @json($jenis);
const CSRF                   = '{{ csrf_token() }}';
const FETCH_PREVIEW_URL      = '{{ route("template.fetch_preview") }}';
const FETCH_KLASIFIKASI_URL  = '{{ route("template.fetch_klasifikasi") }}';
const FETCH_WILAYAH_URL      = '{{ route("template.fetch_wilayah") }}';
const URL_CHILDREN           = '{{ route("template.child_locations") }}';

// Klasifikasi tersimpan (jenis = klasifikasi)
const SELECTED_KLASIFIKASI = @json($selectedKlasifikasi ?? '');
const KLASIFIKASI_LIST     = @json($klasifikasiList ?? []);

// Metadata yang sudah ada di template (read-only chips) — jenis metadata
const EXISTING_METADATA = @json($existingMetadata->values());

// Map metadata_id (string) => [location_id, ...] tersimpan (semua jenis)
const META_LOC_MAP = @json($metaLocMap);

// Semua location_id unik yang tersimpan di template
const SAVED_LOCATION_IDS = @json(
    collect($metaLocMap ?? [])->flatten()->unique()->filter()->map(fn($v) => (string) $v)->values()
);

// Semua metadata aktif (untuk dropdown tambah metadata) — jenis metadata
const metaCache = @json($allMetadata);

// ═══════════════════════════════════════════════════════════════
// DATA LOKASI — di-embed dari server
// ═══════════════════════════════════════════════════════════════
const ALL_LOCATIONS = @json(
    \App\Models\Location::select('location_id', 'nama_wilayah')
        ->orderBy('nama_wilayah')
        ->get()
        ->map(fn($l) => ['id' => (string)$l->location_id, 'nama' => $l->nama_wilayah])
        ->values()
);

const LOC_PROVINSI = ALL_LOCATIONS.filter(l => l.id.slice(-8) === '00000000');
const idxKab = {};
const idxKec = {};
const idxDes = {};
const locById = {};

ALL_LOCATIONS.forEach(l => {
    locById[l.id] = l;
    const len = l.id.length;
    if (len < 10) return;
    const isProvinsi  = l.id.slice(-8) === '00000000';
    const isKabupaten = !isProvinsi && l.id.slice(-6) === '000000';
    const isKecamatan = !isProvinsi && !isKabupaten && l.id.slice(-4) === '0000';
    const isDesa      = !isProvinsi && !isKabupaten && !isKecamatan;
    if (isKabupaten) {
        const p = l.id.slice(0, 2);
        if (!idxKab[p]) idxKab[p] = [];
        idxKab[p].push(l);
    } else if (isKecamatan) {
        const p = l.id.slice(0, 4);
        if (!idxKec[p]) idxKec[p] = [];
        idxKec[p].push(l);
    } else if (isDesa) {
        const p = l.id.slice(0, 6);
        if (!idxDes[p]) idxDes[p] = [];
        idxDes[p].push(l);
    }
});

// ═══════════════════════════════════════════════════════════════
// STATE CASCADE WILAYAH
// ═══════════════════════════════════════════════════════════════
const M_LEVELS = ['provinsi', 'kabupaten', 'kecamatan', 'desa'];
const M_LEVEL_LABEL = {
    provinsi: 'Provinsi', kabupaten: 'Kabupaten/Kota',
    kecamatan: 'Kecamatan', desa: 'Desa/Kelurahan',
};
const mSelLoc = { provinsi: null, kabupaten: null, kecamatan: null, desa: null };

function mCap(s) { return s.charAt(0).toUpperCase() + s.slice(1); }

function onWilFocus(level) {
    const drop = document.getElementById('drop' + mCap(level));
    if (!drop.classList.contains('hidden')) return;
    showDropForLevel(level, '');
}

function onWilInput(level) {
    const q = document.getElementById('input' + mCap(level)).value.trim();
    showDropForLevel(level, q);
}

function getLocationsForLevel(level) {
    if (level === 'provinsi') return LOC_PROVINSI;
    if (level === 'kabupaten') {
        if (!mSelLoc.provinsi) return [];
        return idxKab[mSelLoc.provinsi.id.slice(0, 2)] || [];
    }
    if (level === 'kecamatan') {
        if (!mSelLoc.kabupaten) return [];
        return idxKec[mSelLoc.kabupaten.id.slice(0, 4)] || [];
    }
    if (level === 'desa') {
        if (!mSelLoc.kecamatan) return [];
        return idxDes[mSelLoc.kecamatan.id.slice(0, 6)] || [];
    }
    return [];
}

function showDropForLevel(level, q) {
    const drop = document.getElementById('drop' + mCap(level));
    let items = getLocationsForLevel(level);
    if (q) items = items.filter(x => x.nama.toLowerCase().includes(q.toLowerCase()));
    if (!items.length) {
        drop.innerHTML = '<p class="px-4 py-3 text-xs text-gray-400 text-center">Tidak ada hasil</p>';
        drop.classList.remove('hidden');
        return;
    }
    drop.innerHTML = items.map(x => {
        const isSel = mSelLoc[level] && mSelLoc[level].id === x.id;
        return `<button type="button"
            onclick="selectLevel('${level}', '${x.id}', '${escH(x.nama).replace(/'/g, "\\'")}')"
            class="w-full text-left px-4 py-2.5 flex items-center gap-2.5 border-b border-gray-50 last:border-0 transition-colors
                   ${isSel ? 'bg-emerald-50' : 'hover:bg-gray-50'}">
            ${isSel
                ? '<i class="fas fa-check-circle text-emerald-500 text-xs shrink-0"></i>'
                : '<i class="far fa-circle text-gray-300 text-xs shrink-0"></i>'}
            <span class="text-xs ${isSel ? 'font-semibold text-emerald-700' : 'text-gray-700'}">${escH(x.nama)}</span>
        </button>`;
    }).join('');
    drop.classList.remove('hidden');
}

function selectLevel(level, id, nama, opts = {}) {
    mSelLoc[level] = { id, nama };
    document.getElementById('input' + mCap(level)).value = nama;
    document.getElementById('val'   + mCap(level)).value = id;
    document.getElementById('clear' + mCap(level)).classList.remove('hidden');
    document.getElementById('drop'  + mCap(level)).classList.add('hidden');

    const idx = M_LEVELS.indexOf(level);
    if (!opts.keepDeeper) {
        M_LEVELS.slice(idx + 1).forEach(l => {
            mSelLoc[l] = null;
            document.getElementById('input' + mCap(l)).value = '';
            document.getElementById('val'   + mCap(l)).value = '';
            document.getElementById('clear' + mCap(l)).classList.add('hidden');
            document.getElementById('drop'  + mCap(l)).classList.add('hidden');
        });
    }
    const next = M_LEVELS[idx + 1];
    if (next) {
        const ni = document.getElementById('input' + mCap(next));
        ni.disabled = false;
        ni.classList.remove('bg-gray-100', 'text-gray-400', 'cursor-not-allowed');
        ni.classList.add('bg-white', 'text-gray-700');
        if (!opts.keepDeeper) ni.placeholder = 'Cari ' + M_LEVEL_LABEL[next] + '...';
    }
    mUpdateBadge();
}

function clearLevel(level) {
    const idx = M_LEVELS.indexOf(level);
    M_LEVELS.slice(idx).forEach(l => {
        mSelLoc[l] = null;
        document.getElementById('input' + mCap(l)).value = '';
        document.getElementById('val'   + mCap(l)).value = '';
        document.getElementById('clear' + mCap(l)).classList.add('hidden');
        document.getElementById('drop'  + mCap(l)).classList.add('hidden');
        if (l !== 'provinsi') {
            const ni = document.getElementById('input' + mCap(l));
            ni.disabled = true;
            ni.classList.add('bg-gray-100', 'text-gray-400', 'cursor-not-allowed');
            ni.classList.remove('bg-white', 'text-gray-700');
            ni.placeholder = 'Pilih ' + M_LEVEL_LABEL[M_LEVELS[M_LEVELS.indexOf(l) - 1]] + ' dulu...';
        }
    });
    mUpdateBadge();
}

function mUpdateBadge() {
    let deepest = null;
    for (let i = M_LEVELS.length-1; i >= 0; i--) {
        if (mSelLoc[M_LEVELS[i]]) { deepest = { level:M_LEVELS[i], ...mSelLoc[M_LEVELS[i]] }; break; }
    }
    const prefix = JENIS === 'klasifikasi' ? 'k_' : (JENIS === 'wilayah' ? 'w_' : 'm_');
    const badge  = document.getElementById(prefix + 'selectedWilayahBadge');
    if (!badge) return;
    if (deepest) {
        badge.classList.remove('hidden');
        const namaEl  = document.getElementById(prefix + 'badgeNama');
        const levelEl = document.getElementById(prefix + 'badgeLevel');
        if (namaEl)  namaEl.textContent  = deepest.nama;
        if (levelEl) levelEl.textContent = (JENIS === 'wilayah' ? 'Level: ' : '(') + M_LEVEL_LABEL[deepest.level] + (JENIS === 'wilayah' ? '' : ')');
    } else { badge.classList.add('hidden'); }
}

function mGetDeepestLocId() {
    for (let i = M_LEVELS.length-1; i >= 0; i--) {
        if (mSelLoc[M_LEVELS[i]]) return mSelLoc[M_LEVELS[i]].id;
    }
    return null;
}

function prefillCascadeFromLocationId(locId) {
    const loc = locById[String(locId)];
    if (!loc) return;
    const id = loc.id;
    const isProvinsi  = id.slice(-8) === '00000000';
    const isKabupaten = !isProvinsi && id.slice(-6) === '000000';
    const isKecamatan = !isProvinsi && !isKabupaten && id.slice(-4) === '0000';
    const provId = id.slice(0, 2) + '00000000';
    const prov = locById[provId];
    if (prov) selectLevel('provinsi', prov.id, prov.nama, { keepDeeper: true });
    if (isProvinsi) { mUpdateBadge(); return; }
    const kabId = id.slice(0, 4) + '000000';
    const kab = locById[kabId];
    if (kab) selectLevel('kabupaten', kab.id, kab.nama, { keepDeeper: true });
    if (isKabupaten) { mUpdateBadge(); return; }
    const kecId = id.slice(0, 6) + '0000';
    const kec = locById[kecId];
    if (kec) selectLevel('kecamatan', kec.id, kec.nama, { keepDeeper: true });
    if (isKecamatan) { mUpdateBadge(); return; }
    selectLevel('desa', loc.id, loc.nama, { keepDeeper: true });
}

// ═══════════════════════════════════════════════════════════════
// RENDER CHIPS: lokasi tersimpan per template
// Menampilkan badge nama wilayah untuk setiap location_id tersimpan
// ═══════════════════════════════════════════════════════════════
function renderSavedLocationsChips() {
    const wrap  = document.getElementById('savedLocationsInfo');
    const chips = document.getElementById('savedLocationsChips');
    if (!wrap || !chips) return;

    if (!SAVED_LOCATION_IDS.length) {
        wrap.classList.add('hidden');
        return;
    }

    chips.innerHTML = SAVED_LOCATION_IDS.map(lid => {
        const loc  = locById[String(lid)];
        const nama = loc ? loc.nama : ('Lokasi #' + lid);
        const colorClass = JENIS === 'klasifikasi'
            ? 'bg-violet-50 border-violet-200 text-violet-700'
            : 'bg-sky-50 border-sky-200 text-sky-700';
        const iconColor = JENIS === 'klasifikasi' ? 'text-violet-400' : 'text-sky-400';
        return `<span class="inline-flex items-center gap-1.5 px-2.5 py-1 ${colorClass} rounded-full border text-xs">
            <i class="fas fa-map-marker-alt ${iconColor} text-xs"></i>
            ${escH(nama)}
        </span>`;
    }).join('');

    wrap.classList.remove('hidden');
}

// ═══════════════════════════════════════════════════════════════
// METADATA — chips existing (read-only) + chips baru (selectedMeta)
// ═══════════════════════════════════════════════════════════════
let selectedMeta = {};
let metaTimeout  = null;
const EXISTING_META_IDS = new Set(EXISTING_METADATA.map(m => m.metadata_id));

function renderExistingChips() {
    const el = document.getElementById('existingMetaChips');
    if (!el) return;
    el.innerHTML =
        EXISTING_METADATA.map(m =>
            `<span class="inline-flex items-center gap-1 bg-gray-100 border border-gray-200 text-gray-600 text-xs px-2.5 py-1 rounded-full">
                <i class="fas fa-database text-gray-400 text-xs"></i>
                ${escH(m.nama)}
            </span>`
        ).join('');
}

function onMetaSearchFocus() {
    const box = document.getElementById('metaDropList');
    if (!box.classList.contains('hidden')) return;
    if (!metaCache) return;
    renderMetaDrop(metaCache);
}

function onMetaSearchInput() {
    clearTimeout(metaTimeout);
    const q = document.getElementById('metaSearch').value.trim();
    metaTimeout = setTimeout(() => {
        if (!metaCache) return;
        const filtered = q
            ? metaCache.filter(m => (m.nama || '').toLowerCase().includes(q.toLowerCase()))
            : metaCache;
        renderMetaDrop(filtered);
    }, 150);
}

function renderMetaDrop(results) {
    const box = document.getElementById('metaDropList');
    if (!results.length) {
        box.innerHTML = '<p class="px-4 py-3 text-xs text-gray-400 text-center">Tidak ada hasil</p>';
        box.classList.remove('hidden');
        return;
    }
    box.innerHTML = results.map(m => {
        const already = EXISTING_META_IDS.has(m.metadata_id);
        const sel     = !!selectedMeta[m.metadata_id];
        return `<button type="button"
            data-meta-id="${m.metadata_id}"
            onclick="${already ? '' : `toggleMetaById(this.dataset.metaId)`}"
            class="w-full text-left px-4 py-2.5 flex items-start gap-2.5 border-b border-gray-50 last:border-0 transition-colors
                   ${already ? 'opacity-50 cursor-not-allowed bg-gray-50' : (sel ? 'bg-sky-50' : 'hover:bg-gray-50 cursor-pointer')}">
            <span class="mt-0.5 shrink-0">
                ${already
                    ? '<i class="fas fa-check-circle text-emerald-400 text-xs"></i>'
                    : (sel ? '<i class="fas fa-check-circle text-sky-500 text-xs"></i>' : '<i class="far fa-circle text-gray-300 text-xs"></i>')}
            </span>
            <span class="flex flex-col gap-0.5 min-w-0">
                <span class="font-medium text-gray-800 text-xs">${escH(m.nama)}</span>
                <span class="text-gray-400 text-xs truncate">
                    ${escH(m.klasifikasi||'')}${m.satuan_data ? ' · ' + escH(m.satuan_data) : ''}${m.frekuensi_penerbitan ? ' · ' + escH(m.frekuensi_penerbitan) : ''}
                </span>
            </span>
            ${already ? '<span class="shrink-0 text-xs text-emerald-500 font-medium">Sudah ada</span>' : ''}
        </button>`;
    }).join('');
    box.classList.remove('hidden');
}

function toggleMetaById(idStr) {
    const id = parseInt(idStr);
    const m  = metaCache.find(x => x.metadata_id === id);
    if (!m) return;
    toggleMeta(m.metadata_id, m.nama, m.klasifikasi || '', m.satuan_data || '', m.frekuensi_penerbitan || '');
}

function toggleMeta(id, nama, klasifikasi, satuan, frekuensi) {
    if (selectedMeta[id]) { delete selectedMeta[id]; }
    else { selectedMeta[id] = { metadata_id: id, nama, klasifikasi, satuan_data: satuan, frekuensi_penerbitan: frekuensi }; }
    renderMetaChips();
    const q = document.getElementById('metaSearch').value.trim();
    const list = metaCache ? (q ? metaCache.filter(m => m.nama.toLowerCase().includes(q.toLowerCase())) : metaCache) : null;
    if (list) renderMetaDrop(list);
}

function renderMetaChips() {
    const el = document.getElementById('selectedMetaChips');
    if (!el) return;
    el.innerHTML =
        Object.values(selectedMeta).map(m =>
            `<span class="inline-flex items-center gap-1 bg-sky-50 border border-sky-200 text-sky-700 text-xs px-2.5 py-1 rounded-full">
                <i class="fas fa-plus-circle text-sky-300 text-xs"></i>
                ${escH(m.nama)}
                <button type="button" onclick="removeMeta(${m.metadata_id})"
                        class="text-sky-400 hover:text-sky-600 ml-1 leading-none">×</button>
            </span>`
        ).join('');
}

function removeMeta(id) { delete selectedMeta[id]; renderMetaChips(); }

// ═══════════════════════════════════════════════════════════════
// KLASIFIKASI — search & select
// ═══════════════════════════════════════════════════════════════
function onKlasifikasiFocus() {
    const box = document.getElementById('dropKlasifikasi');
    if (!box.classList.contains('hidden')) return;
    renderKlasifikasiDrop(KLASIFIKASI_LIST);
}

function onKlasifikasiInput() {
    const q = document.getElementById('inputKlasifikasi').value.trim();
    const filtered = q
        ? KLASIFIKASI_LIST.filter(k => k.toLowerCase().includes(q.toLowerCase()))
        : KLASIFIKASI_LIST;
    renderKlasifikasiDrop(filtered);
}

function renderKlasifikasiDrop(items) {
    const box = document.getElementById('dropKlasifikasi');
    if (!items.length) {
        box.innerHTML = '<p class="px-4 py-3 text-xs text-gray-400 text-center">Tidak ada hasil</p>';
        box.classList.remove('hidden');
        return;
    }
    box.innerHTML = items.map(k => {
        const isSel = document.getElementById('valKlasifikasi').value === k;
        return `<button type="button"
            onclick="selectKlasifikasi('${k.replace(/'/g, "\\'")}')"
            class="w-full text-left px-4 py-2.5 flex items-center gap-2.5 text-xs border-b border-gray-50 last:border-0 transition-colors
                   ${isSel ? 'bg-violet-50 font-semibold text-violet-700' : 'text-gray-700 hover:bg-gray-50'}">
            ${isSel
                ? '<i class="fas fa-check-circle text-violet-500 text-xs"></i>'
                : '<i class="far fa-circle text-gray-300 text-xs"></i>'}
            ${escH(k)}
        </button>`;
    }).join('');
    box.classList.remove('hidden');
}

function selectKlasifikasi(nama) {
    document.getElementById('inputKlasifikasi').value = nama;
    document.getElementById('valKlasifikasi').value   = nama;
    document.getElementById('clearKlasifikasi').classList.remove('hidden');
    document.getElementById('dropKlasifikasi').classList.add('hidden');
}

function clearKlasifikasi() {
    document.getElementById('inputKlasifikasi').value = '';
    document.getElementById('valKlasifikasi').value   = '';
    document.getElementById('clearKlasifikasi').classList.add('hidden');
    document.getElementById('dropKlasifikasi').classList.add('hidden');
}

// ═══════════════════════════════════════════════════════════════
// STATE PREVIEW (shared)
// ═══════════════════════════════════════════════════════════════
let allGrouped        = {};
let activeTab         = '';
let sortedRows        = [];
let selectedMap       = {};
let expandedMap       = {};
const PAGE_SIZE       = 15;
let currentPage       = 1;
let hasUserInteracted = false;

// ═══════════════════════════════════════════════════════════════
// LOAD PREVIEW — JENIS: METADATA
// Saat initial load: kirim SEMUA location_ids tersimpan supaya
// semua baris tersimpan muncul di preview dan bisa di-precheck.
// Saat user klik "Muat Ulang Preview": kirim location_id dari cascade.
// ═══════════════════════════════════════════════════════════════
async function loadMetadataPreview(forceLocIds = null) {
    const metaIds = [
        ...EXISTING_METADATA.map(m => m.metadata_id),
        ...Object.keys(selectedMeta).map(id => parseInt(id)),
    ];

    if (!metaIds.length) { alert('Template tidak memiliki metadata.'); return; }

    // Tentukan location_ids yang akan dikirim ke server
    let locationIds;
    if (forceLocIds !== null) {
        // Dipaksa dari luar (initial load)
        locationIds = forceLocIds;
    } else {
        // Dipanggil manual oleh user: pakai cascade picker
        const cascadeLocId = mGetDeepestLocId();
        locationIds = cascadeLocId ? [cascadeLocId] : [];
    }

    const body = new URLSearchParams();
    body.append('_token', CSRF);
    metaIds.forEach(id => body.append('metadata_ids[]', id));
    locationIds.forEach(id => body.append('location_ids[]', id));

    const btn = document.getElementById('btnPilih');
    if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-circle-notch fa-spin mr-2"></i> Memuat...'; }

    const prevSelectedKeys = new Set(Object.keys(selectedMap));

    try {
        const r = await fetch(FETCH_PREVIEW_URL, {
            method: 'POST',
            body,
            headers: { 'Accept': 'application/json' }
        });
        const d = await r.json();
        if (!d.success) { alert('Server error: ' + (d.message || JSON.stringify(d))); return; }

        allGrouped  = flattenGrouped(d.grouped || {});
        expandedMap = {};
        currentPage = 1;

        applySelectionStrategy(prevSelectedKeys);
        activateTabs(allGrouped);
        document.getElementById('sectionResult').classList.remove('hidden');

        const allNames = metaIds
            .map(id => {
                const m = EXISTING_METADATA.find(x => x.metadata_id === id) || selectedMeta[id];
                return m ? m.nama : '';
            })
            .filter(Boolean).join(', ');

        document.getElementById('resultDesc').textContent =
            (allNames.length > 60 ? allNames.slice(0, 60) + '…' : allNames) +
            (locationIds.length ? ' · ' + locationIds.length + ' wilayah' : '');

    } catch (e) {
        alert('Network error: ' + e.message);
    } finally {
        if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-search mr-2"></i> Muat Ulang Preview'; }
    }
}

// ═══════════════════════════════════════════════════════════════
// LOAD PREVIEW — JENIS: KLASIFIKASI
// Sama: initial load kirim semua saved location_ids,
// user reload pakai cascade picker.
// ═══════════════════════════════════════════════════════════════
async function loadKlasifikasiPreview(forceLocIds = null) {
    const klasifikasi = document.getElementById('valKlasifikasi').value.trim();
    if (!klasifikasi) { alert('Pilih klasifikasi terlebih dahulu.'); return; }

    let locationIds;
    if (forceLocIds !== null) {
        locationIds = forceLocIds;
    } else {
        const cascadeLocId = mGetDeepestLocId();
        locationIds = cascadeLocId ? [cascadeLocId] : [];
    }

    const body = new URLSearchParams();
    body.append('_token', CSRF);
    body.append('klasifikasi', klasifikasi);
    locationIds.forEach(id => body.append('location_ids[]', id));

    const btn = document.getElementById('btnPilih');
    if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-circle-notch fa-spin mr-2"></i> Memuat...'; }

    const prevSelectedKeys = new Set(Object.keys(selectedMap));

    try {
        const r = await fetch(FETCH_KLASIFIKASI_URL, {
            method: 'POST',
            body,
            headers: { 'Accept': 'application/json' }
        });
        const d = await r.json();
        if (!d.success) { alert('Server error: ' + (d.message || JSON.stringify(d))); return; }

        allGrouped  = flattenGrouped(d.grouped || {});
        expandedMap = {};
        currentPage = 1;

        applySelectionStrategy(prevSelectedKeys);
        activateTabs(allGrouped);
        document.getElementById('sectionResult').classList.remove('hidden');

        document.getElementById('resultDesc').textContent =
            klasifikasi + (locationIds.length ? ' · ' + locationIds.length + ' wilayah' : '');

    } catch (e) {
        alert('Network error: ' + e.message);
    } finally {
        if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-search mr-2"></i> Muat Ulang Preview'; }
    }
}

// ═══════════════════════════════════════════════════════════════
// LOAD PREVIEW — JENIS: WILAYAH
// ═══════════════════════════════════════════════════════════════
async function loadWilayahTable() {
    let locId = mGetDeepestLocId();
    let locationIds;

    if (locId) {
        locationIds = [locId];
    } else if (SAVED_LOCATION_IDS.length) {
        locationIds = SAVED_LOCATION_IDS;
    } else {
        alert('Pilih wilayah terlebih dahulu.');
        return;
    }

    const body = new URLSearchParams();
    body.append('_token', CSRF);
    locationIds.forEach(id => body.append('location_ids[]', id));

    const btn = document.getElementById('btnPilih');
    if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-circle-notch fa-spin mr-2"></i> Memuat...'; }

    const prevSelectedKeys = new Set(Object.keys(selectedMap));

    try {
        const r = await fetch(FETCH_WILAYAH_URL, {
            method: 'POST',
            body,
            headers: { 'Accept': 'application/json' }
        });
        const d = await r.json();
        if (!d.success) { alert('Server error: ' + (d.message || JSON.stringify(d))); return; }

        allGrouped  = flattenGrouped(d.grouped || {});
        expandedMap = {};
        currentPage = 1;

        applySelectionStrategy(prevSelectedKeys);
        activateTabs(allGrouped);
        document.getElementById('sectionResult').classList.remove('hidden');

        const namaWil = (mSelLoc.desa||mSelLoc.kecamatan||mSelLoc.kabupaten||mSelLoc.provinsi)?.nama
            || (locationIds.length > 1 ? locationIds.length + ' wilayah tersimpan' : '');
        document.getElementById('resultDesc').textContent = namaWil;

    } catch (e) {
        alert('Network error: ' + e.message);
    } finally {
        if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-search mr-2"></i> Muat Ulang Preview'; }
    }
}

// ═══════════════════════════════════════════════════════════════
// SELEKSI STRATEGY
// ═══════════════════════════════════════════════════════════════
function applySelectionStrategy(prevSelectedKeys) {
    if (!hasUserInteracted) {
        preselectFromMetaLocMap();
    } else {
        mergeSelectedMapAfterReload(prevSelectedKeys);
    }
}

/**
 * Pre-check semua baris yang cocok dengan META_LOC_MAP tersimpan.
 *
 * Logika matching:
 * - Jika metadata tersimpan DENGAN location_ids → centang baris yang
 *   location_id-nya ada di daftar tersebut.
 * - Jika metadata tersimpan TANPA location_ids (array kosong) → centang
 *   baris "Semua Wilayah" (location_id falsy / 0 / null).
 */
function preselectFromMetaLocMap() {
    selectedMap = {};

    Object.keys(allGrouped).forEach(freq => {
        (allGrouped[freq] || []).forEach(row => {
            const mid = String(row.metadata_id);
            if (!(mid in META_LOC_MAP)) return; // metadata baru, skip

            const savedLocs = (META_LOC_MAP[mid] || []).map(String);
            const lid = row.location_id;

            let match = false;
            if (savedLocs.length === 0) {
                // tersimpan tanpa lokasi → cocokkan "Semua Wilayah"
                match = !lid || lid === 0 || lid === '0' || lid === null;
            } else {
                match = savedLocs.includes(String(lid));
            }

            if (match) selectedMap[rowKey(row)] = row;
        });
    });
}

function mergeSelectedMapAfterReload(prevSelectedKeys) {
    const newSelectedMap = {};
    const allNewRows = {};
    Object.keys(allGrouped).forEach(freq => {
        (allGrouped[freq] || []).forEach(row => { allNewRows[rowKey(row)] = row; });
    });

    prevSelectedKeys.forEach(key => {
        if (allNewRows[key]) newSelectedMap[key] = allNewRows[key];
    });

    const prevSelectedMetaIds = new Set([...prevSelectedKeys].map(k => k.split('_')[0]));

    Object.keys(allGrouped).forEach(freq => {
        (allGrouped[freq] || []).forEach(row => {
            const mid = String(row.metadata_id);
            if (prevSelectedMetaIds.has(mid)) return;
            if (!(mid in META_LOC_MAP)) return;

            const savedLocs = (META_LOC_MAP[mid] || []).map(String);
            const lid = row.location_id;
            let match = false;
            if (savedLocs.length === 0) {
                match = !lid || lid === 0 || lid === '0' || lid === null;
            } else {
                match = savedLocs.includes(String(lid));
            }
            if (match) newSelectedMap[rowKey(row)] = row;
        });
    });

    selectedMap = newSelectedMap;
}

// ═══════════════════════════════════════════════════════════════
// AUTO-LOAD SAAT HALAMAN DIBUKA
// ═══════════════════════════════════════════════════════════════
function autoLoadPreview() {
    hasUserInteracted = false;

    if (JENIS === 'metadata') {
        renderExistingChips();
        renderSavedLocationsChips();
        const savedLocs = SAVED_LOCATION_IDS.length ? SAVED_LOCATION_IDS : [];
        loadMetadataPreview(savedLocs);

    } else if (JENIS === 'klasifikasi') {
        const inputEl = document.getElementById('inputKlasifikasi');
        const valEl   = document.getElementById('valKlasifikasi');
        const clearEl = document.getElementById('clearKlasifikasi');

        // FIX: Blade sudah render value="{{ $selectedKlasifikasi }}" di input,
        // baca dari sana sebagai fallback jika SELECTED_KLASIFIKASI kosong
        const klasifikasiFinal = (SELECTED_KLASIFIKASI && SELECTED_KLASIFIKASI.trim())
            ? SELECTED_KLASIFIKASI.trim()
            : (inputEl ? inputEl.value.trim() : '');

        if (klasifikasiFinal) {
            if (inputEl) inputEl.value = klasifikasiFinal;
            if (valEl)   valEl.value   = klasifikasiFinal;
            if (clearEl) clearEl.classList.remove('hidden');
        }

        renderSavedLocationsChips();

        const savedLocs = SAVED_LOCATION_IDS.length ? SAVED_LOCATION_IDS : [];
        if (klasifikasiFinal) {
            loadKlasifikasiPreview(savedLocs);
        }

    } else if (JENIS === 'wilayah') {
        if (SAVED_LOCATION_IDS.length === 1) {
            prefillCascadeFromLocationId(SAVED_LOCATION_IDS[0]);
        }
        loadWilayahTable();
    }
}

document.addEventListener('DOMContentLoaded', autoLoadPreview);

// ═══════════════════════════════════════════════════════════════
// TAB FREKUENSI
// ═══════════════════════════════════════════════════════════════
const FREQ_KEYS = ['dekade', 'tahunan', 'semester', 'kuartal', 'bulanan'];

function activateTabs(grouped) {
    let firstActive = '';
    FREQ_KEYS.forEach(freq => {
        const count  = (grouped[freq] || []).length;
        const tabBtn = document.getElementById('tab-' + freq);
        const tabCnt = document.getElementById('tab-count-' + freq);
        tabCnt.textContent = count;
        if (count > 0) {
            tabBtn.disabled = false;
            tabBtn.classList.remove('cursor-not-allowed', 'text-gray-400');
            tabBtn.classList.add('cursor-pointer', 'text-gray-600', 'hover:text-gray-800');
            tabCnt.classList.remove('bg-gray-100', 'text-gray-400');
            tabCnt.classList.add('bg-sky-100', 'text-sky-600');
            if (!firstActive) firstActive = freq;
        } else {
            tabBtn.disabled = true;
            tabBtn.classList.add('cursor-not-allowed', 'text-gray-400');
            tabBtn.classList.remove('cursor-pointer', 'text-gray-600', 'hover:text-gray-800');
            tabCnt.classList.add('bg-gray-100', 'text-gray-400');
            tabCnt.classList.remove('bg-sky-100', 'text-sky-600');
        }
    });
    const total = FREQ_KEYS.reduce((s, f) => s + (grouped[f] || []).length, 0);
    document.getElementById('totalBadge').textContent = total + ' baris';

    if (firstActive) {
        switchTab(firstActive);
    } else {
        activeTab  = '';
        sortedRows = [];
        renderTable();
    }
}

/**
 * Untuk semua jenis (khususnya wilayah & klasifikasi): saat initial load,
 * urutkan sortedRows agar baris tercentang (tersimpan) muncul di atas.
 */
function sortCheckedFirst() {
    sortedRows.sort((a, b) => {
        const aChecked = selectedMap[rowKey(a)] ? 0 : 1;
        const bChecked = selectedMap[rowKey(b)] ? 0 : 1;
        if (aChecked !== bChecked) return aChecked - bChecked;
        const nameCmp = (a.nama || '').localeCompare(b.nama || '');
        if (nameCmp !== 0) return nameCmp;
        return (a.nama_wilayah || '').localeCompare(b.nama_wilayah || '');
    });
}

function switchTab(freq) {
    activeTab = freq;
    FREQ_KEYS.forEach(f => {
        const btn = document.getElementById('tab-' + f);
        if (f === freq) {
            btn.classList.add('border-sky-500', 'text-sky-600');
            btn.classList.remove('border-transparent', 'text-gray-600');
        } else {
            btn.classList.remove('border-sky-500', 'text-sky-600');
            if (!btn.disabled) btn.classList.add('border-transparent', 'text-gray-600');
        }
    });

    sortedRows  = [...(allGrouped[freq] || [])];
    expandedMap = {};
    currentPage = 1;

    // Saat initial load: tampilkan baris tersimpan (tercentang) di atas
    if (!hasUserInteracted) {
        sortCheckedFirst();
    }

    renderTable();
}

function flattenGrouped(grouped) {
    const result = {};
    Object.keys(grouped).forEach(freq => {
        result[freq] = [];
        (grouped[freq] || []).forEach(m => {
            if (Array.isArray(m.locations)) {
                const locs = m.locations.length
                    ? m.locations
                    : [{ location_id: 0, nama_wilayah: 'Semua Wilayah', has_children: false }];
                locs.forEach(loc => {
                    result[freq].push({
                        metadata_id:          m.metadata_id,
                        nama:                 m.nama,
                        klasifikasi:          m.klasifikasi || '',
                        satuan_data:          m.satuan_data || '',
                        frekuensi_penerbitan: m.frekuensi_penerbitan || '',
                        location_id:          loc.location_id,
                        nama_wilayah:         loc.nama_wilayah,
                        has_children:         loc.has_children,
                        depth:                0,
                    });
                });
            } else {
                result[freq].push({
                    metadata_id:          m.metadata_id,
                    nama:                 m.nama,
                    klasifikasi:          (typeof m.klasifikasi === 'object' && m.klasifikasi)
                                              ? (m.klasifikasi.nama_klasifikasi || '')
                                              : (m.klasifikasi || ''),
                    satuan_data:          m.satuan_data || m.satuan || '',
                    frekuensi_penerbitan: m.frekuensi_penerbitan || '',
                    location_id:          m.location_id ?? 0,
                    nama_wilayah:         m.nama_wilayah || 'Semua Wilayah',
                    has_children:         !!m.has_children,
                    depth:                m.depth || 0,
                });
            }
        });
    });
    return result;
}

// ═══════════════════════════════════════════════════════════════
// RENDER TABEL
// ═══════════════════════════════════════════════════════════════
function rowKey(row) { return `${row.metadata_id}_${row.location_id}_${row.depth || 0}`; }

function buildFlat(baseRows) {
    const result = [];
    function addRows(rows) {
        rows.forEach(row => {
            result.push(row);
            const key = rowKey(row);
            if (expandedMap[key]) addRows(expandedMap[key]);
        });
    }
    addRows(baseRows);
    return result;
}

function renderTable() {
    const tbody = document.getElementById('resultTbody');
    const flat  = buildFlat(sortedRows);
    const total = flat.length;

    if (!total) {
        tbody.innerHTML = `<tr><td colspan="4" class="px-4 py-10 text-center text-gray-400 text-sm">
            <i class="fas fa-inbox text-3xl text-gray-200 block mb-2"></i>
            Tidak ada metadata untuk filter ini.
        </td></tr>`;
        document.getElementById('paginWrap').classList.add('hidden');
        document.getElementById('checkAll').checked = false;
        return;
    }

    const start = (currentPage - 1) * PAGE_SIZE;
    const end   = Math.min(start + PAGE_SIZE, total);
    tbody.innerHTML = flat.slice(start, end).map(row => buildRow(row)).join('');

    const visibleRows = flat.slice(start, end);
    const allChecked  = visibleRows.length > 0 && visibleRows.every(row => selectedMap[rowKey(row)]);
    document.getElementById('checkAll').checked = allChecked;

    const totalPages = Math.ceil(total / PAGE_SIZE);
    const pw = document.getElementById('paginWrap');
    if (totalPages > 1) {
        pw.classList.remove('hidden');
        document.getElementById('paginInfo').textContent = `Menampilkan ${start+1}–${end} dari ${total}`;
        function buildPaginBtns(totalPages, cur) {
            let pages = [];
            if (totalPages <= 7) {
                for (let p = 1; p <= totalPages; p++) pages.push(p);
            } else {
                pages = [1];
                if (cur > 3) pages.push('...');
                const s = Math.max(2, cur - 1), e = Math.min(totalPages - 1, cur + 1);
                for (let p = s; p <= e; p++) pages.push(p);
                if (cur < totalPages - 2) pages.push('...');
                pages.push(totalPages);
            }
            const prev = cur > 1 ? `<button onclick="goPage(${cur-1})" class="w-7 h-7 text-xs rounded-md font-medium border border-gray-200 text-gray-500 hover:bg-gray-50">&lt;</button>` : '';
            const next = cur < totalPages ? `<button onclick="goPage(${cur+1})" class="w-7 h-7 text-xs rounded-md font-medium border border-gray-200 text-gray-500 hover:bg-gray-50">&gt;</button>` : '';
            const mid = pages.map(p => p === '...'
                ? `<span class="px-1 text-gray-400 text-xs self-center">…</span>`
                : `<button onclick="goPage(${p})" class="w-7 h-7 text-xs rounded-md font-medium transition-colors ${p===cur?'bg-sky-500 text-white':'border border-gray-200 text-gray-500 hover:bg-gray-50'}">${p}</button>`
            ).join('');
            return prev + mid + next;
        }
        document.getElementById('paginBtns').innerHTML = buildPaginBtns(totalPages, currentPage);
    } else {
        pw.classList.add('hidden');
    }

    updateSelBar();
}

function buildRow(row) {
    const key        = rowKey(row);
    const depth      = row.depth || 0;
    const checked    = !!selectedMap[key];
    const indent     = depth * 24;
    const isExpanded = !!expandedMap[key];

    const displayName = `<span class="font-${depth === 0 ? 'semibold' : 'medium'} text-gray-800">${escH(row.nama)} di ${escH(row.nama_wilayah)}</span>`;

    let detailBtn = `<span class="text-gray-300 text-xs">—</span>`;
    if (row.has_children) {
        detailBtn = `<button type="button"
            onclick="toggleExpand('${key}')"
            title="${isExpanded ? 'Sembunyikan turunan' : 'Tampilkan 1 level wilayah di bawah'}"
            class="inline-flex items-center justify-center w-7 h-7 rounded border font-bold text-xs select-none transition-colors
                   ${isExpanded
                       ? 'bg-sky-100 border-sky-300 text-sky-700 hover:bg-sky-200'
                       : 'bg-white border-gray-300 text-gray-600 hover:border-sky-400 hover:text-sky-600'}">
            ${isExpanded ? '<i class="fa-solid fa-angle-left"></i>' : '<i class="fa-solid fa-angle-down"></i>'}
        </button>`;
    }

    const rowBg = depth === 0
        ? (checked ? 'bg-emerald-50/60 hover:bg-emerald-50' : 'hover:bg-sky-50')
        : (depth === 1 ? 'bg-sky-50/50 hover:bg-sky-100/60' : 'bg-violet-50/40 hover:bg-violet-100/50');
    const borderStyle = depth > 0
        ? `border-left: 3px solid ${depth === 1 ? '#7dd3fc' : '#c4b5fd'};` : '';

    return `<tr class="${rowBg} transition-colors">
        <td class="py-3 pr-1" style="padding-left:${8 + indent}px; ${borderStyle}">
            <input type="checkbox" class="row-chk rounded border-gray-300 cursor-pointer"
                value="${escH(key)}"
                onchange="onRowCheck(this, ${row.metadata_id}, ${row.location_id}, ${depth})"
                ${checked ? 'checked' : ''}>
        </td>
        <td class="px-4 py-3 text-xs min-w-0 break-words" style="${depth > 0 ? 'padding-left:' + (16 + indent) + 'px' : ''}">
            ${depth > 0 ? '<span class="text-gray-400 mr-1">↳</span>' : ''}
            ${displayName}
            ${row.frekuensi_penerbitan ? `<span class="ml-1 text-gray-400 font-normal">(${escH(row.frekuensi_penerbitan)})</span>` : ''}
            ${checked && depth === 0 ? '<span class="mt-1 flex items-center gap-1 text-emerald-600 text-xs font-medium"><i class="fas fa-bookmark text-xs"></i> Tersimpan</span>' : ''}
        </td>
        <td class="px-3 py-3 text-center">
            <button type="button"
                    onclick="openMetadataModal(${row.metadata_id})"
                    title="Lihat detail metadata"
                    class="inline-flex items-center justify-center bg-transparent border-0 p-0 cursor-pointer">
                <i class="fas fa-circle-info text-sky-500 hover:text-sky-600 transition-colors"></i>
            </button>
        </td>
        <td class="px-3 py-3 text-center">${detailBtn}</td>
    </tr>`;
}

// ═══════════════════════════════════════════════════════════════
// EXPAND / COLLAPSE
// ═══════════════════════════════════════════════════════════════
async function toggleExpand(key) {
    if (expandedMap[key]) { collapseKey(key); renderTable(); return; }

    const parts      = key.split('_');
    const metadataId = parseInt(parts[0]);
    const locationId = parseInt(parts[1]);
    const depth      = parseInt(parts[2] || '0');
    const parentRow  = findRowByKey(key);

    try {
        const r = await fetch(`${URL_CHILDREN}?metadata_id=${metadataId}&location_id=${locationId}`);
        const d = await r.json();
        if (!d.children || !d.children.length) {
            if (parentRow) parentRow.has_children = false;
            renderTable(); return;
        }
        expandedMap[key] = d.children.map(c => ({
            metadata_id:          metadataId,
            nama:                 parentRow ? parentRow.nama : '',
            klasifikasi:          parentRow ? parentRow.klasifikasi : '',
            satuan_data:          parentRow ? parentRow.satuan_data : '',
            frekuensi_penerbitan: parentRow ? parentRow.frekuensi_penerbitan : '',
            location_id:          c.location_id,
            nama_wilayah:         c.nama_wilayah,
            has_children:         c.has_children,
            depth:                depth + 1,
        }));
        renderTable();
    } catch(e) { console.error('Expand error:', e); }
}

function collapseKey(key) {
    if (expandedMap[key]) {
        expandedMap[key].forEach(child => collapseKey(rowKey(child)));
        delete expandedMap[key];
    }
}

function findRowByKey(key) {
    for (const row of sortedRows) { if (rowKey(row) === key) return row; }
    for (const children of Object.values(expandedMap)) {
        for (const child of children) { if (rowKey(child) === key) return child; }
    }
    return null;
}

// ═══════════════════════════════════════════════════════════════
// SELECTION
// ═══════════════════════════════════════════════════════════════
function onRowCheck(cb, metadataId, locationId, depth) {
    hasUserInteracted = true;
    const key = cb.value;
    if (cb.checked) {
        const flat = buildFlat(sortedRows);
        const row  = flat.find(r => rowKey(r) === key);
        if (row) selectedMap[key] = row;
    } else { delete selectedMap[key]; }

    // Re-render baris ini agar badge "Tersimpan" update
    renderTable();

    updateSelBar();
    const flat = buildFlat(sortedRows);
    const start = (currentPage - 1) * PAGE_SIZE;
    const end   = Math.min(start + PAGE_SIZE, flat.length);
    const visibleRows = flat.slice(start, end);
    const allChecked = visibleRows.length > 0 && visibleRows.every(row => selectedMap[rowKey(row)]);
    document.getElementById('checkAll').checked = allChecked;
}

function toggleAll(masterCb) {
    hasUserInteracted = true;
    const flat = buildFlat(sortedRows);
    const start = (currentPage - 1) * PAGE_SIZE;
    const end   = Math.min(start + PAGE_SIZE, flat.length);
    const visibleRows = flat.slice(start, end);
    if (masterCb.checked) {
        visibleRows.forEach(row => { selectedMap[rowKey(row)] = row; });
    } else {
        visibleRows.forEach(row => { delete selectedMap[rowKey(row)]; });
    }
    renderTable();
}

function clearAllSel() {
    hasUserInteracted = true;
    selectedMap = {};
    document.querySelectorAll('.row-chk').forEach(cb => cb.checked = false);
    const ca = document.getElementById('checkAll'); if (ca) ca.checked = false;
    updateSelBar();
}

function updateSelBar() {
    const count = Object.keys(selectedMap).length;
    const bar = document.getElementById('selBar');
    if (count > 0) { bar.classList.remove('hidden'); bar.style.display = 'flex'; }
    else { bar.classList.add('hidden'); bar.style.display = 'none'; }
    document.getElementById('selCount').textContent = count + ' metadata dipilih';
}

// ═══════════════════════════════════════════════════════════════
// PENGATURAN URUTAN
// ═══════════════════════════════════════════════════════════════
function terapkanUrutan() {
    const byKlas = document.getElementById('chkKlasifikasi').checked;
    const byWil  = document.getElementById('chkWilayah').checked;
    const base   = allGrouped[activeTab] || [];
    if (!byKlas && !byWil) {
        sortedRows = [...base];
    } else {
        sortedRows = [...base].sort((a, b) => {
            if (byKlas) { const k = (a.klasifikasi||'').localeCompare(b.klasifikasi||''); if (k !== 0) return k; }
            if (byWil)  { const w = (a.nama_wilayah||'').localeCompare(b.nama_wilayah||''); if (w !== 0) return w; }
            return (a.nama||'').localeCompare(b.nama||'');
        });
    }
    expandedMap = {};
    currentPage = 1;
    renderTable();
}

// ═══════════════════════════════════════════════════════════════
// PAGINATION
// ═══════════════════════════════════════════════════════════════
function goPage(p) { currentPage = p; renderTable(); }

// ═══════════════════════════════════════════════════════════════
// MODAL SIMPAN
// ═══════════════════════════════════════════════════════════════
function openSaveModal() {
    const metaCountFromMap = Object.keys(selectedMap || {}).length
        ? new Set(Object.values(selectedMap).map(r => r.metadata_id)).size
        : 0;

    if (!metaCountFromMap) { alert('Pilih minimal 1 metadata-wilayah untuk disimpan.'); return; }

    const nama = document.getElementById('inputNamaTampilan').value.trim();
    if (!nama) {
        document.getElementById('inputNamaTampilan').focus();
        alert('Nama template tidak boleh kosong.');
        return;
    }

    if (JENIS === 'klasifikasi') {
        const klas = document.getElementById('valKlasifikasi').value.trim();
        if (!klas) { alert('Klasifikasi tidak boleh kosong.'); return; }
    }

    document.getElementById('confirmNama').textContent    = nama;
    document.getElementById('modalMetaCount').textContent = metaCountFromMap;
    document.getElementById('modalSave').style.display    = 'flex';
}

function closeModal() { document.getElementById('modalSave').style.display = 'none'; }

function submitTemplate() {
    const nama = document.getElementById('inputNamaTampilan').value.trim();
    if (!nama) return;

    const metaSet = new Set(Object.values(selectedMap).map(r => r.metadata_id));
    if (!metaSet.size) { alert('Pilih minimal 1 metadata.'); return; }

    const metaLocMap = {};
    Object.values(selectedMap).forEach(row => {
        const mid = String(row.metadata_id);
        const lid = row.location_id;
        if (!metaLocMap[mid]) metaLocMap[mid] = [];
        if (lid && !metaLocMap[mid].includes(lid)) metaLocMap[mid].push(lid);
    });
    metaSet.forEach(mid => {
        if (!metaLocMap[String(mid)]) metaLocMap[String(mid)] = [];
    });

    document.getElementById('hidNama').value = nama;
    document.getElementById('hidMetadataIds').innerHTML =
        [...metaSet].map(id => `<input type="hidden" name="metadata_ids[]" value="${id}">`).join('');

    let mapInput = document.getElementById('hidMetaLocMap');
    if (!mapInput) {
        mapInput = document.createElement('input');
        mapInput.type = 'hidden';
        mapInput.name = 'metadata_location_ids';
        mapInput.id   = 'hidMetaLocMap';
        document.getElementById('formSaveTemplateHidden').appendChild(mapInput);
    }
    mapInput.value = JSON.stringify(metaLocMap);

    document.getElementById('hidLocationIds').innerHTML = '';

    if (JENIS === 'klasifikasi') {
        document.getElementById('hidKlasifikasi').value =
            document.getElementById('valKlasifikasi').value.trim();
    } else {
        document.getElementById('hidKlasifikasi').value = '';
    }

    const urutanBy = [];
    if (document.getElementById('chkKlasifikasi').checked) urutanBy.push('klasifikasi');
    if (document.getElementById('chkWilayah').checked)     urutanBy.push('wilayah');
    document.getElementById('hidUrutanBy').innerHTML =
        urutanBy.map(v => `<input type="hidden" name="urutan_by[]" value="${v}">`).join('');

    closeModal();
    document.getElementById('formSaveTemplateHidden').submit();
}

// ═══════════════════════════════════════════════════════════════
// UTILS
// ═══════════════════════════════════════════════════════════════
function escH(s) {
    const d = document.createElement('div');
    d.innerText = s || '';
    return d.innerHTML;
}

document.addEventListener('click', e => {
    const wrap = document.getElementById('metaDropWrap');
    const drop = document.getElementById('metaDropList');
    if (wrap && drop && !wrap.contains(e.target)) drop.classList.add('hidden');

    const kWrap = document.getElementById('wrapKlasifikasi');
    const kDrop = document.getElementById('dropKlasifikasi');
    if (kWrap && kDrop && !kWrap.contains(e.target)) kDrop.classList.add('hidden');

    if (e.target === document.getElementById('modalSave')) closeModal();
});
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });
</script>

@endsection