{{-- resources/views/components/landing/features.blade.php --}}
<section id="fitur" class="py-24 bg-white" aria-labelledby="fitur-heading">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="text-center max-w-2xl mx-auto mb-16 fade-up">
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-stikom/5 border border-stikom/10 mb-6">
                <div class="w-1.5 h-1.5 rounded-full bg-stikom-accent"></div>
                <span class="text-stikom text-xs font-bold uppercase tracking-wider">Keunggulan Platform</span>
            </div>
            <h2 id="fitur-heading" class="text-3xl sm:text-4xl font-black text-stikom leading-tight mb-4">
                Kenapa Memilih <span class="text-stikom-accent">Pusat Data Indonesia</span>?
            </h2>
            <p class="text-gray-500 text-lg leading-relaxed">
                Solusi lengkap untuk pencarian hingga visualisasi data hanya dalam satu platform.
            </p>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">

            @php
            $features = [
                ['icon' => 'M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4',       'title' => 'Data Terpusat',       'desc' => 'Semua data dari berbagai produsen data terintegrasi dalam satu platform yang terstruktur dan mudah diakses.'],
                ['icon' => 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z',                'title' => 'Mudah Diakses',       'desc' => 'Antarmuka yang intuitif memungkinkan siapa saja menemukan data tanpa hambatan teknis.'],
                ['icon' => 'M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z', 'title' => 'Tampilan Personal',   'desc' => 'Kustomisasi tampilan data sesuai kebutuhan Anda.'],
            ];
            @endphp

            @foreach($features as $i => $f)
                <div class="group relative bg-white rounded-2xl p-7 border border-gray-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 cursor-default fade-up"
                     style="animation-delay: {{ $i * 0.08 }}s">
                    <div class="w-12 h-12 rounded-xl bg-stikom flex items-center justify-center mb-5 transition-all duration-300 group-hover:bg-stikom-accent">
                        <svg class="w-6 h-6 text-stikom-accent group-hover:text-stikom transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $f['icon'] }}"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-black text-stikom mb-3 group-hover:text-stikom-accent transition-colors duration-200" style="-webkit-text-stroke: 0px;">
                        {{ $f['title'] }}
                    </h3>
                    <p class="text-gray-500 text-sm leading-relaxed">{{ $f['desc'] }}</p>
                </div>
            @endforeach

            {{-- CTA card --}}
            <div class="sm:col-span-2 lg:col-span-1 fade-up" style="animation-delay:0.45s">
                <div class="h-full bg-stikom-accent rounded-2xl p-7 flex flex-col justify-between min-h-48">
                    <div>
                        <div class="w-12 h-12 rounded-xl bg-stikom/15 flex items-center justify-center mb-5">
                            <svg class="w-6 h-6 text-stikom" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-black text-stikom mb-2">Mulai Sekarang</h3>
                        <p class="text-stikom/70 text-sm leading-relaxed">Bergabunglah dan nikmati akses penuh ke seluruh data yang tersedia.</p>
                    </div>
                    <a href="{{ route('langganan') }}"
                       class="mt-6 inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-stikom text-white font-bold text-sm hover:bg-[#002a52] transition-colors w-fit">
                        Berlangganan
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                </div>
            </div>

        </div>
    </div>
</section>