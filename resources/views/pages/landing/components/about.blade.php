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
                            <div class="w-2.5 h-2.5 bg-green-400"></div>
                            <div class="ml-3 h-5 flex-1 bg-white/10"></div>
                            {{-- <span class="text-white/40 text-xs font-mono">pusat-data-bali.id</span> --}}
                        </div>

                        <div class="p-6 space-y-3">

                            {{-- Mini bar chart --}}
                            <div class="mt-4 border border-gray-100 p-4 bg-gray-50">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Data Trend</span>
                                    <span class="text-xs font-black text-stikom-core">+12.5% ↑</span>
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
                    <div class="absolute -top-4 -right-4 bg-stikom-blue text-white px-4 py-2 shadow-lg text-sm font-black">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Kustomisasi Tampilan
                        </div>
                    </div>

                    {{-- Badge floating bottom-left --}}
                    <div class="absolute -bottom-4 -left-4 bg-stikom border border-stikom-accent/30 shadow-xl px-4 py-3">
                        <div class="text-[10px] text-white/80 mb-0.5 uppercase tracking-wider font-semibold">Status Data</div>
                        {{-- <div class="text-base font-black text-white flex items-center gap-1.5">
                            <span class="w-2 h-2 bg-stikom-accent animate-pulse"></span>
                            Real-time
                        </div> --}}
                    </div>
                </div>
            </div>

            {{-- Text content --}}
            <div class="fade-up order-1 lg:order-2" style="animation-delay:0.15s">

                <h2 id="tentang-heading" class="text-3xl sm:text-4xl font-black font-poppins text-[#001734] leading-tight mb-6">
                    Apa itu <br><span class="text-stikom-blue">Pusat Data Indonesia?</span>
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