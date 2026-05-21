{{-- resources/views/pages/landing/components/subscription.blade.php --}}

<section id="langganan" class="py-24 bg-slate-50 border-t border-gray-100" aria-labelledby="langganan-heading">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Section header --}}
        <div class="grid lg:grid-cols-2 gap-12 items-end mb-16 fade-up">
            <div>
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-1 h-8 bg-stikom-core"></div>
                    <span class="text-stikom-core text-xs font-bold uppercase tracking-widest">Paket Berlangganan</span>
                </div>
                <h2 id="langganan-heading" class="text-3xl sm:text-4xl font-black font-poppins text-stikom leading-tight">
                    Pilih Paket yang <br><span class="text-stikom-core">Sesuai Kebutuhan</span>
                </h2>
            </div>
        </div>

        {{-- Grid layout: menyesuaikan jumlah paket --}}
        <div class="grid lg:grid-cols-5 gap-8 items-start">

            {{-- Pricing cards dari database --}}
            <div class="{{ $pricings->count() > 1 ? 'lg:col-span-5' : 'lg:col-span-2' }} fade-up">
                <div class="{{ $pricings->count() > 1 ? 'grid sm:grid-cols-2 lg:grid-cols-' . min($pricings->count(), 3) . ' gap-6' : '' }}">

                    @forelse($pricings as $plan)
                        @php $isPopular = $plan->is_popular; @endphp

                        <div class="relative overflow-hidden shadow-2xl
                                    {{ $isPopular
                                        ? 'ring-2 ring-stikom-core shadow-stikom/30'
                                        : 'border border-white/10 shadow-gray-200/50' }}"
                            style="{{ $isPopular
                                ? 'background: linear-gradient(160deg, #001020 0%, #001734 50%, #002a52 100%);'
                                : 'background: #ffffff;' }}">

                            {{-- Top badge --}}
                            @if($isPopular)
                                <div class="bg-stikom-core py-2 px-6 flex items-center justify-between">
                                    <span class="text-white text-xs font-black uppercase tracking-widest flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                        Launching Price
                                    </span>
                                    <span class="text-white text-[10px] font-bold uppercase tracking-wider">Terbaik</span>
                                </div>
                            @else
                                <div class="border-b border-gray-100 py-2 px-6">
                                    <span class="text-gray-400 text-xs font-bold uppercase tracking-widest">Paket</span>
                                </div>
                            @endif

                            <div class="p-8">
                                {{-- Nama paket --}}
                                <div class="text-xs font-bold uppercase tracking-widest mb-3
                                            {{ $isPopular ? 'text-stikom-core/60' : 'text-gray-400' }}">
                                    {{ $plan->nama_layanan ?? 'Paket' }}
                                </div>

                                {{-- Harga --}}
                                <div class="flex items-end gap-2 mb-1">
                                    <span class="text-5xl font-black font-poppins {{ $isPopular ? 'text-white' : 'text-stikom' }}">
                                        {{ $plan->harga_format }}
                                    </span>
                                </div>

                                {{-- Periode — dinamis dari durasi + durasi_type --}}
                                <div class="text-sm mb-8 {{ $isPopular ? 'text-white/35' : 'text-gray-400' }}">
                                    / {{ $plan->durasi_label }}
                                </div>

                                {{-- Deskripsi --}}
                                @if(!empty($plan->deskripsi))
                                    <p class="text-xs mb-6 leading-relaxed {{ $isPopular ? 'text-white/50' : 'text-gray-500' }}">
                                        {{ $plan->deskripsi }}
                                    </p>
                                @endif

                                {{-- Fitur --}}
                                <ul class="space-y-3 mb-8">
                                    @forelse($plan->fiturs->sortBy('urutan') as $fitur)
                                        <li class="flex items-center gap-3 text-sm {{ !$fitur->aktif ? 'opacity-35' : '' }}">
                                            <div class="w-5 h-5 flex items-center justify-center shrink-0
                                                        {{ $fitur->aktif
                                                            ? ($isPopular ? 'bg-stikom-core' : 'bg-stikom/10')
                                                            : 'bg-white/10' }}">
                                                <svg class="w-3 h-3 {{ $fitur->aktif
                                                                ? ($isPopular ? 'text-white' : 'text-stikom')
                                                                : 'text-gray-400' }}"
                                                    fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                    @if($fitur->aktif)
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                                    @else
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    @endif
                                                </svg>
                                            </div>
                                            <span class="{{ $isPopular ? 'text-white/75' : 'text-gray-600' }}">
                                                {{ $fitur->nama_fitur }}
                                            </span>
                                        </li>
                                    @empty
                                        <li class="text-sm italic {{ $isPopular ? 'text-white/30' : 'text-gray-400' }}">
                                            Tidak ada fitur terdaftar.
                                        </li>
                                    @endforelse
                                </ul>

                                <a href="{{ route('langganan') }}"
                                class="block w-full py-3.5 text-sm font-black text-center transition-all duration-200
                                        {{ $isPopular
                                            ? 'bg-stikom-core hover:bg-[#2d9955] text-white'
                                            : 'bg-stikom/10 hover:bg-stikom/20 text-stikom' }}">
                                    Berlangganan Sekarang
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-16 text-gray-400 lg:col-span-full">
                            <p class="text-sm">Belum ada paket berlangganan tersedia.</p>
                        </div>
                    @endforelse

                </div>
            </div>

            {{-- Benefits / Why subscribe — hanya tampil jika hanya 1 paket --}}
            @if($pricings->count() === 1)
                <div class="lg:col-span-3 fade-up" style="animation-delay:0.15s">
                    <div class="mb-8">
                        <h3 class="text-xl font-black text-stikom font-poppins mb-2">Kenapa berlangganan?</h3>
                        <p class="text-gray-500 text-sm">Dapatkan akses penuh ke semua fitur premium yang dirancang untuk mempermudah pekerjaan Anda.</p>
                    </div>

                    <div class="mt-6 flex flex-col sm:flex-row gap-3">
                        <a href="{{ route('langganan') }}"
                           class="inline-flex items-center justify-center gap-2 px-7 py-3.5 bg-stikom-core text-white font-black text-sm hover:bg-[#2d9955] transition-all duration-200 shadow-lg">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                            Mulai Berlangganan
                        </a>
                    </div>
                </div>
            @endif

        </div>
    </div>
</section>