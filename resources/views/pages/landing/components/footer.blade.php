<footer class="bg-blue-950 text-white" role="contentinfo">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Main grid --}}
        <div class="py-16 grid sm:grid-cols-2 lg:grid-cols-4 gap-12">

            {{-- Brand --}}
            <div class="lg:col-span-1">
                <div class="flex items-center gap-3 mb-5">
                    
                    <div>
                        <div class="text-sm font-bold text-white leading-tight">Pusat Data Indonesia Bali</div>
                        {{-- <div class="text-xs text-stikom-accent font-bold">Bali</div> --}}
                    </div>
                </div>
                <p class="text-white/50 text-sm leading-relaxed mb-6">
                    Menyediakan dan menampilkan data sesuai dengan kustomisasi pengguna.
                </p>
                <a href="{{ route('langganan') }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 bg-stikom-accent hover:bg-yellow-600 text-stikom  hover:text-white text-sm font-black transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                    Berlangganan
                </a>
            </div>

            {{-- Klasifikasi populer --}}
            <div>
                <h3 class="text-xs font-bold text-stikom-accent uppercase tracking-widest mb-5">
                    Klasifikasi Data
                </h3>

                <ul class="space-y-2.5">
                    @foreach($allKlasifikasi->take(10) as $klasifikasi)
                        <li>
                            <a href="{{ route('klasifikasi.show', [
                                    'klasifikasi' => Str::slug($klasifikasi)
                                ]) }}"
                            class="text-white/50 hover:text-stikom-accent text-sm transition-colors duration-150 flex items-center gap-2 group">

                                <span class="w-1 h-1 rounded-full bg-white/20 group-hover:bg-stikom-accent transition-colors shrink-0"></span>

                                {{ $klasifikasi }}
                            </a>
                        </li>
                    @endforeach
                    <li>
                        <a href="{{ route('klasifikasi.index') }}"
                           class="text-xs text-white/30 hover:text-stikom-accent transition-colors flex items-center gap-2 group">
                            Lihat Semua Klasifikasi
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Produk --}}
            <div>
                <h3 class="text-xs font-bold text-stikom-accent uppercase tracking-widest mb-5">Produk</h3>
                <ul class="space-y-2.5">
                    @foreach([
                        ['label' => 'Data Series',   'route' => 'template.index'],
                        ['label' => 'Template Tampilan Data', 'route' => 'data.index'],
                        ['label' => 'Langganan',     'route' => 'langganan'],
                    ] as $item)
                        <li>
                            <a href="{{ route($item['route']) }}"
                               class="text-white/50 hover:text-stikom-accent text-sm transition-colors flex items-center gap-2 group">
                                <span class="w-1 h-1 rounded-full bg-white/20 group-hover:bg-stikom-accent transition-colors shrink-0"></span>
                                {{ $item['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Kontak --}}
            <div>
                <h3 class="text-xs font-bold text-stikom-accent uppercase tracking-widest mb-5">Kontak</h3>
                <ul class="space-y-4">
                    <li>
                        <a href="tel:+6281234567890" class="flex items-start gap-3 group">
                            <div class="w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center shrink-0 group-hover:bg-stikom-accent/20 transition-colors">
                                <svg class="w-4 h-4 text-stikom-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="text-xs text-white/30 mb-0.5">Telepon</div>
                                <div class="text-sm text-white/70 group-hover:text-white transition-colors">(0361) 244445</div>
                            </div>
                        </a>
                    </li>
                    <li>
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 text-stikom-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="text-xs text-white/30 mb-0.5">Alamat</div>
                                <div class="text-sm text-white/70 leading-relaxed">Jl. Raya Puputan No.86, Dangin Puri Klod, Kec. Denpasar Tim., Kota Denpasar, Bali 80234</div>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>

        {{-- Bottom bar --}}
        <div class="border-t border-white/10 py-6 flex flex-col sm:flex-row items-center justify-between gap-4">
            <p class="text-white/30 text-sm">
                Copyright &copy; {{ date('Y') }} Pusat Data Indonesia Bali. Hak cipta dilindungi.
            </p>
        </div>
    </div>
</footer>