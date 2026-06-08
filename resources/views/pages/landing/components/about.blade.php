<section id="tentang" class="py-24 bg-slate-50 border-t border-gray-100" aria-labelledby="tentang-heading">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-16 items-center">

            {{-- Illustration Panel --}}
<div class="fade-up order-2 lg:order-1">
    <div class="relative">

        {{-- Badge floating top-right --}}
        <div class="absolute -top-4 -right-4 z-10 bg-stikom-accent text-stikom px-4 py-2 shadow-lg text-sm font-black">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Kustomisasi Tampilan
            </div>
        </div>

        {{-- Main mockup card --}}
        <div class="bg-white border border-gray-200 shadow-xl overflow-hidden rounded-sm">

            {{-- Browser bar --}}
            <div class="bg-[#001734] px-5 py-2.5 flex items-center gap-2">
                <div class="w-2.5 h-2.5 rounded-full bg-red-400"></div>
                <div class="w-2.5 h-2.5 rounded-full bg-yellow-400"></div>
                <div class="w-2.5 h-2.5 rounded-full bg-green-400"></div>
                <div class="ml-3 h-5 flex-1 bg-white/10 rounded-sm"></div>
            </div>

            {{-- Page header --}}
            <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <div class="text-sm font-bold text-[#001734]">Tampilkan Data</div>
                    <div class="text-[10px] text-gray-400 mt-0.5">Pilih template → frekuensi → rentang periode → klik Tampilkan Data</div>
                </div>
                <div class="bg-stikom-blue text-white text-[10px] font-bold px-3 py-1.5">+ Buat Template</div>
            </div>

            {{-- Step 1: Pilih Template --}}
            <div class="px-5 py-3 border-b border-gray-100">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-5 h-5 rounded-full bg-stikom-blue text-white text-[10px] font-bold flex items-center justify-center flex-shrink-0">1</div>
                    <span class="text-xs font-bold text-[#001734]">Pilih Template</span>
                </div>
                <div class="text-[10px] text-gray-400 mb-2">Daftar Template (1)</div>
                <div class="bg-stikom-blue text-white px-3 py-2.5 flex items-center justify-between">
                    <div>
                        <div class="text-xs font-bold">Template Tampilan 1</div>
                    </div>
                </div>
            </div>

            {{-- Step 2: Frekuensi --}}
            <div class="px-5 py-3 border-b border-gray-100">
                <div class="flex items-center gap-2 mb-2.5">
                    <div class="w-5 h-5 rounded-full bg-stikom-blue text-white text-[10px] font-bold flex items-center justify-center flex-shrink-0">2</div>
                    <span class="text-xs font-bold text-[#001734]">Pilih Frekuensi Rentang Waktu</span>
                </div>
                <div class="grid grid-cols-6 gap-1.5">
                    @foreach([
                        ['10 Tahunan','0','Setiap 10 tahun', false],
                        ['Tahunan','1','Per tahun', true],
                        ['Semesteran','0','S1 / S2', false],
                        ['Kuartal','0','Q1 – Q4', false],
                        ['Bulanan','0','Jan – Des', false],
                        ['Custom','1','Rentang bebas', false],
                    ] as [$name, $count, $desc, $active])
                    <div class="border {{ $active ? 'border-2 border-stikom-blue' : 'border-gray-200' }} p-1.5 text-center">
                        <div class="text-[9px] font-bold {{ $active ? 'text-stikom-blue' : 'text-gray-600' }}">{{ $name }}</div>
                        <div class="text-[9px] text-gray-400">{{ $desc }}</div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Step 3: Rentang Periode --}}
            <div class="px-5 py-3 border-b border-gray-100">
                <div class="flex items-center gap-2 mb-2.5">
                    <div class="w-5 h-5 rounded-full bg-stikom-blue text-white text-[10px] font-bold flex items-center justify-center flex-shrink-0">3</div>
                    <span class="text-xs font-bold text-[#001734]">Tentukan Rentang Periode</span>
                </div>
                {{-- <div class="flex flex-wrap gap-1.5 mb-2.5">
                    @foreach(['5 Tahun Terakhir','10 Tahun Terakhir','15 Tahun Terakhir','20 Tahun Terakhir'] as $p)
                    <span class="border border-stikom-blue text-stikom-blue text-[9px] px-2.5 py-1 rounded-full">{{ $p }}</span>
                    @endforeach
                </div> --}}
                <div class="flex items-center gap-2">
                    <div>
                        <div class="text-[9px] text-gray-400 mb-1">Dari Tahun</div>
                        <div class="border border-gray-200 text-[10px] px-2 py-1 text-gray-700">2021 ▾</div>
                    </div>
                    <span class="text-gray-400 mt-3">—</span>
                    <div>
                        <div class="text-[9px] text-gray-400 mb-1">Sampai Tahun</div>
                        <div class="border border-gray-200 text-[10px] px-2 py-1 text-gray-700">2025 ▾</div>
                    </div>
                </div>
            </div>

            {{-- Submit --}}
            <div class="px-5 py-3 border-b border-gray-100">
                <div class="inline-flex items-center gap-1.5 bg-stikom-blue text-white text-xs font-bold px-4 py-2">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 6h18M3 14h18M3 18h18"/></svg>
                    Tampilkan Data
                </div>
            </div>

            {{-- Result table --}}
            <div class="px-5 py-3">
                <div class="flex items-end justify-between mb-2">
                    <div>
                        <div class="text-xs font-bold text-[#001734]">Menampilkan 1 baris data</div>
                        
                    </div>
                    <div class="border border-gray-200 text-[9px] text-gray-400 px-2 py-1">✕ Reset</div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-[9px] border-collapse">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="text-left text-gray-500 font-semibold px-2 py-1.5 border border-gray-100" rowspan="2">Nama Metadata / Wilayah</th>
                                <th class="text-center text-gray-500 font-semibold px-2 py-1 border border-gray-100" colspan="5">Tahun</th>
                                <th class="text-center text-gray-500 font-semibold px-2 py-1.5 border border-gray-100" rowspan="2">Satuan</th>
                                <th class="text-center text-gray-500 font-semibold px-2 py-1.5 border border-gray-100" rowspan="2">Sumber</th>
                                <th class="text-center text-gray-500 font-semibold px-2 py-1.5 border border-gray-100" rowspan="2">Aksi</th>
                            </tr>
                            <tr class="bg-gray-50">
                                @foreach(['2021','2022','2023','2024','2025'] as $y)
                                <th class="text-center text-gray-500 font-semibold px-2 py-1 border border-gray-100">{{ $y }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="px-2 py-2 border border-gray-100">
                                    <div class="font-bold text-stikom">Angka Partisipasi Kasar Laki-laki </div>
                                    <div class="text-gray-400 mt-0.5">Kependudukan</div>
                                    <div class="text-gray-400 mt-0.5">Provinsi Bali</div>
                                </td>
                                <td class="text-center px-2 py-2 border border-gray-100 font-semibold text-stikom">105</td>
                                <td class="text-center px-2 py-2 border border-gray-100 font-semibold text-stikom">120</td>
                                <td class="text-center px-2 py-2 border border-gray-100 font-semibold text-stikom">165</td>
                                <td class="text-center px-2 py-2 border border-gray-100 font-semibold text-stikom">200</td>
                                <td class="text-center px-2 py-2 border border-gray-100 font-semibold text-stikom">260</td>
                                <td class="text-center px-2 py-2 border border-gray-100 text-gray-400">Jiwa/km2</td>
                                <td class="text-center px-2 py-2 border border-gray-100 text-gray-400">Prov. Bali</td>
                                <td class="text-center px-2 py-2 border border-gray-100">
                                    <span class="border border-gray-200 text-gray-400 px-1.5 py-0.5">Grafik</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Badge floating bottom-left --}}
        {{-- <div class="absolute -bottom-4 -left-4 bg-stikom border border-stikom-accent/30 shadow-xl px-4 py-3">
            <div class="text-[10px] text-white/80 mb-0.5 uppercase tracking-wider font-semibold">Status Data</div>
        </div> --}}
    </div>
</div>

            {{-- Text content --}}
            <div class="fade-up order-1 lg:order-2" style="animation-delay:0.15s">

                <h2 id="tentang-heading" class="text-3xl sm:text-4xl font-black font-poppins text-[#001734] leading-tight mb-6">
                    Apa itu <br><span class="text-stikom-blue">Pusat Data Indonesia Bali?</span>
                </h2>

                <p class="text-gray-600 text-lg text-justify leading-relaxed mb-5">
                    Pusat Data Indonesia Bali adalah layanan penyedia data dalam berbagai klasifikasi dan berbasis kewilayahan yang menyediakan dan menyajikan data sesuai dengan preferensi pengguna, sehingga pengguna dapat menemukan data yang diinginkan dengan lebih mudah sesuai dengan kebutuhan.
                </p>

                <p class="text-gray-500 text-justify leading-relaxed mb-8">
                    Platform ini dirancang untuk menjadi jembatan antara produsen data dan pengguna data
                    dengan menyediakan akses yang lebih mudah dan cepat.
                </p>

                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('data.index') }}"
                       class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-stikom-accent text-black hover:text-white font-black text-sm hover:bg-yellow-600 transition-colors shadow-sm">
                        Jelajahi Data
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                    <a href="#fitur"
                       class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-transparent text-[#001734] font-bold text-sm border border-[#001734]/30 hover:border-stikom-accent hover:text-stikom-accent transition-colors">
                        Lihat Fitur
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>