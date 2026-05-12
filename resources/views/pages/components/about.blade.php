{{-- resources/views/components/landing/about.blade.php --}}
<section id="tentang" class="py-24 bg-slate-50" aria-labelledby="tentang-heading">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-16 items-center">

            {{-- Illustration --}}
            <div class="fade-up order-2 lg:order-1">
                <div class="relative">
                    <div class="relative bg-white rounded-3xl p-8 shadow-xl border border-gray-100">
                        <div class="flex items-center gap-2 mb-6">
                            <div class="w-3 h-3 rounded-full bg-red-400"></div>
                            <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
                            <div class="w-3 h-3 rounded-full bg-green-400"></div>
                            <div class="ml-2 h-6 flex-1 bg-gray-100 rounded-lg"></div>
                        </div>

                        <div class="space-y-3">
                            @foreach([
                                ['icon' => 'M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7', 'bg' => 'bg-[#001734]', 'bar' => 'bg-stikom-accent/30', 'w' => 'w-16'],
                                ['icon' => 'M9 19v-6a2 2 0 00-2-2H5', 'bg' => 'bg-[#001734]', 'bar' => 'bg-stikom-accent/20', 'w' => 'w-20'],
                                ['icon' => 'M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z', 'bg' => 'bg-[#001734]', 'bar' => 'bg-stikom-accent/25', 'w' => 'w-12'],
                            ] as $row)
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg {{ $row['bg'] }} flex items-center justify-center">
                                        <svg class="w-4 h-4 text-stikom-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $row['icon'] }}"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1 h-4 bg-gray-100 rounded-full"></div>
                                    <div class="{{ $row['w'] }} h-4 {{ $row['bar'] }} rounded-full"></div>
                                </div>
                            @endforeach

                            {{-- Mini bar chart --}}
                            <div class="mt-4 p-4 bg-linear-to-br from-stikom/5 to-stikom/10 rounded-2xl">
                                <div class="flex items-end gap-2 h-20">
                                    @foreach([40, 60, 45, 80, 65, 90, 70, 85, 55, 95] as $h)
                                        <div class="flex-1 rounded-t-md transition-all duration-500 hover:opacity-100"
                                             style="height:{{ $h }}%; background: linear-gradient(to top, #001734, #002a52); opacity:0.8"></div>
                                    @endforeach
                                </div>
                                <div class="mt-3 flex items-center justify-between">
                                    <span class="text-xs text-gray-500 font-medium">Data Overview</span>
                                    <span class="text-xs font-bold text-stikom-accent">+12.5%</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="absolute -top-4 -right-4 bg-stikom-accent text-stikom px-4 py-2 rounded-2xl shadow-lg text-sm font-black">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Kustomisasi Tampilan Data
                        </div>
                    </div>

                    {{-- <div class="absolute -bottom-4 -left-4 bg-white border border-gray-100 shadow-xl rounded-2xl px-4 py-3">
                        <div class="text-xs text-gray-400 mb-0.5">Data Tersedia</div>
                        <div class="text-lg font-black text-stikom">Real-time</div>
                    </div> --}}
                </div>
            </div>

            {{-- Text --}}
            <div class="fade-up order-1 lg:order-2" style="animation-delay:0.15s">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-stikom/5 border border-stikom/10 mb-6">
                    <div class="w-1.5 h-1.5 rounded-full bg-stikom-accent"></div>
                    <span class="text-stikom text-xs font-bold uppercase tracking-wider">Tentang Platform</span>
                </div>

                <h2 id="tentang-heading" class="text-3xl sm:text-4xl font-black text-stikom leading-tight mb-6">
                    Apa itu <span class="text-stikom-accent">Pusat Data Indonesia</span>?
                </h2>

                <p class="text-gray-600 text-lg leading-relaxed mb-6">
                    Pusat Data Indonesia adalah layanan yang berfungsi untuk menyediakan dan menampilkan data sesuai dengan kustomisasi penggunanya, sehingga pengguna dapat menemukan data lebih mudah sesuai dengan kebutuhan pengguna.
                </p>

                <p class="text-gray-500 leading-relaxed mb-8">
                    Platform ini dirancang untuk menjadi jembatan antara produsen data dan pengguna data dengan menyediakan akses mudah dan cepat.
                </p>

                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('data.index') }}"
                       class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-stikom text-white font-bold text-sm hover:bg-[#002a52] transition-colors shadow-sm">
                        Jelajahi Data
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                </div>
            </div>

        </div>
    </div>
</section>