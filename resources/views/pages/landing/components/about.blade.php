<section id="tentang" class="py-24 bg-slate-50 border-t border-gray-100" aria-labelledby="tentang-heading">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-16 items-center">

            {{-- Illustration Panel --}}
            <div class="fade-up order-2 lg:order-1">
                <div class="relative">

                    {{-- Main mockup card --}}
                    <div class="relative bg-white border border-gray-200 p-0 shadow-xl overflow-hidden">

                        {{-- Card header bar --}}
                        <div class="bg-[#001734] px-6 py-3 flex items-center gap-2">
                            <div class="w-2.5 h-2.5 bg-red-400"></div>
                            <div class="w-2.5 h-2.5 bg-yellow-400"></div>
                            <div class="w-2.5 h-2.5 bg-[#3DB166]"></div>
                            <div class="ml-3 h-5 flex-1 bg-white/10"></div>
                            {{-- <span class="text-white/40 text-xs font-mono">pusat-data-bali.id</span> --}}
                        </div>

                        <div class="p-6 space-y-3">
                            {{-- Rows --}}
                            {{-- @foreach([
                                ['icon' => 'M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7', 'label' => 'Dataset Aktif', 'val' => '2,847', 'pct' => 78],
                                ['icon' => 'M9 19v-6a2 2 0 00-2-2H5', 'label' => 'Produsen Data', 'val' => '134', 'pct' => 55],
                                ['icon' => 'M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z', 'label' => 'Akses Bulanan', 'val' => '12.5K', 'pct' => 91],
                            ] as $row)
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-[#001734] flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4 text-[#3DB166]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $row['icon'] }}"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between mb-1">
                                            <span class="text-xs font-semibold text-gray-600">{{ $row['label'] }}</span>
                                            <span class="text-xs font-black text-[#001734]">{{ $row['val'] }}</span>
                                        </div>
                                        <div class="h-1.5 bg-gray-100">
                                            <div class="h-full bg-[#3DB166] transition-all duration-700"
                                                 style="width: {{ $row['pct'] }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach --}}

                            {{-- Mini bar chart --}}
                            <div class="mt-4 border border-gray-100 p-4 bg-gray-50">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Data Trend</span>
                                    <span class="text-xs font-black text-[#3DB166]">+12.5% ↑</span>
                                </div>
                                <div class="flex items-end gap-1.5 h-16">
                                    @foreach([40, 55, 45, 70, 60, 85, 65, 90, 55, 95, 72, 88] as $h)
                                        <div class="flex-1 transition-all duration-500 hover:opacity-100"
                                             style="height:{{ $h }}%; background: {{ $loop->last ? '#3DB166' : '#001734' }}; opacity: {{ $loop->last ? '1' : '0.7' }}"></div>
                                    @endforeach
                                </div>
                                <div class="mt-2 flex gap-1.5">
                                    @foreach(['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'] as $m)
                                        <span class="flex-1 text-[9px] text-gray-400 text-center">{{ $m }}</span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Badge floating top-right --}}
                    <div class="absolute -top-4 -right-4 bg-[#3DB166] text-white px-4 py-2 shadow-lg text-sm font-black">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Kustomisasi Tampilan
                        </div>
                    </div>

                    {{-- Badge floating bottom-left --}}
                    <div class="absolute -bottom-4 -left-4 bg-[#001734] border border-[#3DB166]/30 shadow-xl px-4 py-3">
                        <div class="text-[10px] text-[#3DB166]/70 mb-0.5 uppercase tracking-wider font-semibold">Status Data</div>
                        {{-- <div class="text-base font-black text-white flex items-center gap-1.5">
                            <span class="w-2 h-2 bg-[#3DB166] animate-pulse"></span>
                            Real-time
                        </div> --}}
                    </div>
                </div>
            </div>

            {{-- Text content --}}
            <div class="fade-up order-1 lg:order-2" style="animation-delay:0.15s">

                {{-- Section label --}}
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-1 h-8 bg-[#3DB166]"></div>
                    <span class="text-[#3DB166] text-xs font-bold uppercase tracking-widest">Tentang Platform</span>
                </div>

                <h2 id="tentang-heading" class="text-3xl sm:text-4xl font-black font-poppins text-[#001734] leading-tight mb-6">
                    Apa itu <br><span class="text-[#3DB166]">Pusat Data Indonesia?</span>
                </h2>

                <p class="text-gray-600 text-lg leading-relaxed mb-5">
                    Pusat Data Indonesia adalah layanan yang berfungsi untuk menyediakan dan menampilkan
                    data sesuai dengan kustomisasi penggunanya, sehingga pengguna dapat menemukan data
                    lebih mudah sesuai dengan kebutuhan.
                </p>

                <p class="text-gray-500 leading-relaxed mb-8">
                    Platform ini dirancang untuk menjadi jembatan antara produsen data dan pengguna data
                    dengan menyediakan akses mudah dan cepat.
                </p>

                {{-- Feature bullets --}}
                {{-- <div class="space-y-3 mb-8">
                    @foreach([
                        'Data dari berbagai instansi pemerintah & swasta',
                        'Akses mudah dengan antarmuka intuitif',
                        'Ekspor data ke berbagai format (Excel, PDF, JSON)',
                    ] as $feat)
                        <div class="flex items-start gap-3">
                            <div class="w-5 h-5 bg-[#3DB166] flex items-center justify-center shrink-0 mt-0.5">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <span class="text-gray-600 text-sm leading-relaxed">{{ $feat }}</span>
                        </div>
                    @endforeach
                </div> --}}

                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('data.index') }}"
                       class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-[#3DB166] text-white font-black text-sm hover:bg-[#2d9955] transition-colors shadow-sm">
                        Jelajahi Data
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                    <a href="#fitur"
                       class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-transparent text-[#001734] font-bold text-sm border border-[#001734]/30 hover:border-[#3DB166] hover:text-[#3DB166] transition-colors">
                        Lihat Fitur
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>