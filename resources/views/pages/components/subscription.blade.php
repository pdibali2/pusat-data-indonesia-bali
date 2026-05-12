{{-- resources/views/components/landing/subscription.blade.php --}}
<section id="langganan" class="py-24 bg-slate-50" aria-labelledby="langganan-heading">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="text-center max-w-2xl mx-auto mb-16 fade-up">
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-stikom/5 border border-stikom/10 mb-6">
                <div class="w-1.5 h-1.5 rounded-full bg-stikom-accent"></div>
                <span class="text-stikom text-xs font-bold uppercase tracking-wider">Paket Berlangganan</span>
            </div>
            <h2 id="langganan-heading" class="text-3xl sm:text-4xl font-black text-stikom leading-tight mb-4">
                Pilih Paket yang <span class="text-stikom-accent" >Sesuai Kebutuhan</span>
            </h2>
            <p class="text-gray-500 text-lg leading-relaxed">
                Akses penuh ke seluruh fitur platform dengan berlangganan.
            </p>
        </div>

        <div class="grid sm:grid-cols-3 gap-6 max-w-4xl mx-auto">

            @php
            $plans = [
                ['name' => '1 Bulan',  'price' => 'Rp50.000',  'period' => '/ bulan',   'popular' => true, 'features' => ['Akses semua data','Export data Excel dan PDF','Template tampilan data']],
                // ['name' => '6 Bulan',  'price' => 'Rp300.000', 'period' => '/ 6 bulan', 'popular' => false,  'features' => ['Semua fitur 1 Bulan','Template lebih banyak','Prioritas support','Notifikasi data baru']],
                // ['name' => '1 Tahun',  'price' => 'Rp600.000', 'period' => '/ tahun',   'popular' => false, 'features' => ['Semua fitur 6 Bulan','Akses API dasar','Laporan kustom','Dedicated support']],
            ];
            @endphp

            @foreach($plans as $i => $p)
                <a href="{{ route('langganan') }}"
                   class="group relative col-2 block rounded-3xl transition-all duration-300 fade-up
                          {{ $p['popular'] ? 'shadow-2xl scale-105 hover:scale-110' : 'bg-white border border-gray-100 shadow-sm hover:shadow-xl hover:-translate-y-1' }}"
                   style="animation-delay: {{ $i * 0.1 }}s;
                          {{ $p['popular'] ? 'background: linear-gradient(135deg, #001734 0%, #002a52 100%);' : '' }}"
                   aria-label="Paket {{ $p['name'] }}">

                    @if($p['popular'])
                        <div class="absolute -top-4 left-1/2 -translate-x-1/2 z-10">
                            <div class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full bg-stikom-accent text-stikom text-xs font-black shadow-lg">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                                Launching Price
                            </div>
                        </div>
                    @endif

                    <div class="p-8 {{ $p['popular'] ? 'pt-10' : '' }}">
                        <div class="text-sm font-bold uppercase tracking-wider mb-2 {{ $p['popular'] ? 'text-stikom-accent/70' : 'text-gray-400' }}">
                            {{ $p['name'] }}
                        </div>
                        <div class="text-3xl font-black mb-1 {{ $p['popular'] ? 'text-white' : 'text-stikom' }}">
                            {{ $p['price'] }}
                        </div>
                        <div class="text-sm mb-8 {{ $p['popular'] ? 'text-white/50' : 'text-gray-400' }}">
                            {{ $p['period'] }}
                        </div>
                        <ul class="space-y-3 mb-8">
                            @foreach($p['features'] as $feat)
                                <li class="flex items-center gap-3 text-sm {{ $p['popular'] ? 'text-white/80' : 'text-gray-600' }}">
                                    <svg class="w-4 h-4 shrink-0 {{ $p['popular'] ? 'text-stikom-accent' : 'text-stikom' }}"
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    {{ $feat }}
                                </li>
                            @endforeach
                        </ul>
                        <div class="w-full py-3 rounded-xl text-sm font-black text-center transition-all duration-200
                                    {{ $p['popular']
                                        ? 'bg-stikom-accent text-stikom group-hover:bg-yellow-400'
                                        : 'bg-stikom text-white group-hover:bg-[#002a52]' }}">
                            Berlangganan Sekarang
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>