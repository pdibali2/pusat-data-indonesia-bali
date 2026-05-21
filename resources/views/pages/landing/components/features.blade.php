{{-- resources/views/pages/landing/components/features.blade.php --}}
{{-- Redesign: STIKOM-style features — sharp cards, left-border accents, icon squares, navy/emerald --}}

<section id="fitur" class="py-6 bg-white border-t border-gray-100" aria-labelledby="fitur-heading">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Section header --}}
        <div class="text-center max-w-2xl mx-auto mb-16 fade-up">
            <div class="flex items-center justify-center gap-3 mb-6">
                {{-- <div class="h-px flex-1 max-w-16 bg-stikom-core/30"></div>
                <span class="text-stikom-core text-xs font-bold uppercase tracking-widest">Keunggulan Platform</span>
                <div class="h-px flex-1 max-w-16 bg-stikom-core/30"></div> --}}
            </div>
            <h2 id="fitur-heading" class="text-3xl sm:text-4xl font-black font-poppins text-[#001734] leading-tight mb-4">
                Kenapa Memilih <br><span class="text-stikom-core">Pusat Data Indonesia?</span>
            </h2>
        </div>

        @php
        $features = [
            [
                'icon'  => 'M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4',
                'title' => 'Data Terpusat',
                'desc'  => 'Semua data dari berbagai produsen terintegrasi dalam satu platform yang terstruktur, dapat diakses kapan saja dan di mana saja.',
                'tag'   => 'Integrasi',
            ],
            [
                'icon'  => 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z',
                'title' => 'Mudah Diakses',
                'desc'  => 'Antarmuka yang intuitif memungkinkan siapa saja menemukan dan menggunakan data tanpa hambatan teknis yang berarti.',
                'tag'   => 'Aksesibilitas',
            ],
            [
                'icon'  => 'M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z',
                'title' => 'Tampilan Personal',
                'desc'  => 'Kustomisasi tampilan data sesuai kebutuhan. Pilih template, atur kolom, dan simpan preferensi tampilan Anda.',
                'tag'   => 'Kustomisasi',
            ],
            [
                'icon'  => 'M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                'title' => 'Ekspor Mudah',
                'desc'  => 'Unduh data dalam berbagai format — Excel, PDF, atau JSON — dengan satu klik. Cocok untuk analisis lebih lanjut.',
                'tag'   => 'Ekspor',
            ],
            [
                'icon'  => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                'title' => 'Visualisasi Data',
                'desc'  => 'Tampilkan data dalam bentuk grafik, tabel interaktif, dan sparkline langsung di platform tanpa perlu tools tambahan.',
                'tag'   => 'Visualisasi',
            ],
        ];
        @endphp

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-0 border border-gray-200">

            @foreach($features as $i => $f)
                <div class="group relative p-7 border-b border-r border-gray-200 hover:bg-[#001734] transition-all duration-300 cursor-default fade-up
                            {{ $i >= 3 ? 'border-b-0' : '' }}
                            {{ ($i + 1) % 3 === 0 ? 'border-r-0' : '' }}
                            {{ $i === 2 ? 'sm:border-r-0 lg:border-r border-gray-200' : '' }}"
                     style="animation-delay: {{ $i * 0.08 }}s">

                    {{-- Top accent line on hover --}}
                    <div class="absolute top-0 left-0 right-0 h-0.5 bg-stikom-core scale-x-0 group-hover:scale-x-100 transition-transform duration-300 origin-left"></div>

                    <div class="w-11 h-11 bg-[#001734] group-hover:bg-stikom-core flex items-center justify-center mb-5 transition-colors duration-300">
                        <svg class="w-5 h-5 text-stikom-core group-hover:text-white transition-colors duration-300"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $f['icon'] }}"/>
                        </svg>
                    </div>

                    <div class="text-[10px] font-bold uppercase tracking-widest text-stikom-core mb-2 group-hover:text-stikom-core">
                        {{ $f['tag'] }}
                    </div>
                    <h3 class="text-base font-black text-[#001734] group-hover:text-white mb-3 transition-colors duration-300 font-poppins">
                        {{ $f['title'] }}
                    </h3>
                    <p class="text-gray-500 group-hover:text-white/60 text-sm leading-relaxed transition-colors duration-300">
                        {{ $f['desc'] }}
                    </p>
                </div>
            @endforeach

            {{-- CTA card (last cell) --}}
            <div class="relative p-7 bg-stikom-core border-b-0 border-r-0 fade-up flex flex-col justify-between min-h-48"
                 style="animation-delay: 0.45s">
                <div>
                    <div class="text-[10px] font-bold uppercase tracking-widest text-white/60 mb-2">Mulai Sekarang</div>
                    <h3 class="text-base font-black text-white mb-3 font-poppins">Akses Penuh ke Semua Data</h3>
                    <p class="text-white/70 text-sm leading-relaxed">
                        Bergabunglah dan nikmati akses penuh ke seluruh dataset yang tersedia di platform.
                    </p>
                </div>
                <a href="{{ route('langganan') }}"
                   class="mt-6 inline-flex items-center gap-2 px-5 py-2.5 bg-[#001734] text-white font-bold text-sm hover:bg-[#002a52] transition-colors w-fit">
                    Berlangganan
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>
</section>