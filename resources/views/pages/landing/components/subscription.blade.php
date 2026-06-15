{{-- resources/views/pages/landing/components/subscription.blade.php --}}

<section id="langganan" class="py-24 bg-slate-50 border-t border-gray-100" aria-labelledby="langganan-heading">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Section header --}}
        <div class="mb-12 fade-up">
            <h2 id="langganan-heading" class="text-3xl sm:text-4xl font-black font-poppins text-stikom leading-tight">
                Pilih Paket <span class="text-stikom-blue">Sesuai Kebutuhan</span>
            </h2>
        </div>

        {{-- Grid layout --}}
        @if($pricings->isNotEmpty())
            {{-- items-stretch agar semua card sama tinggi dalam satu baris --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-4 items-stretch fade-up">

                @foreach($pricings->sortBy('urutan') as $plan)
                    @php $isPopular = $plan->is_popular; @endphp

                    {{--
                        Card wrapper:
                        - Regular  : border abu, border-top transparan → merah saat hover
                        - Featured : dark navy + ring merah + sedikit lebih tinggi lewat negative margin atas-bawah
                    --}}
                    <div class="relative flex flex-col overflow-hidden group
                                {{ $isPopular
                                    ? 'ring-2 ring-stikom-red shadow-2xl shadow-stikom-red/20 -my-3 z-10'
                                    : 'border border-gray-100 shadow-sm' }}"
                         style="{{ $isPopular
                             ? 'background: linear-gradient(170deg, #001020 0%, #001734 55%, #002348 100%);'
                             : 'background: #ffffff; border-top: 3px solid transparent;' }}"
                         @if(!$isPopular)
                             x-data
                             @mouseenter="$el.style.borderTopColor='#A32D2D'"
                             @mouseleave="$el.style.borderTopColor='transparent'"
                         @endif>

                        {{-- Top accent bar (popular only) --}}
                        @if($isPopular)
                            <div class="h-[3px] bg-stikom-red w-full shrink-0"></div>
                        @endif

                        {{-- Badge row --}}
                        @if($isPopular)
                            <div class="bg-stikom-red px-4 py-2 flex items-center justify-between shrink-0">
                                <span class="text-white text-[9px] font-black uppercase tracking-widest font-poppins flex items-center gap-1">
                                    <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                    Paling Populer
                                </span>
                                <span class="text-white/60 text-[8px] font-bold uppercase tracking-widest font-poppins">Terbaik</span>
                            </div>
                        @else
                            <div class="px-4 py-1.5 border-b border-gray-100 shrink-0">
                                <span class="text-stikom-red text-[9px] font-bold uppercase tracking-widest font-poppins">Paket</span>
                            </div>
                        @endif

                        {{-- Card body — flex column, tumbuh mengisi tinggi card --}}
                        <div class="flex flex-col flex-1 p-[14px] {{ $isPopular ? 'pt-4' : '' }}">

                            {{-- Duration label --}}
                            <div class="text-[9px] font-bold uppercase tracking-widest mb-2 font-poppins
                                        {{ $isPopular ? 'text-white/35' : 'text-gray-400' }}">
                                {{ $plan->durasi_label }}
                            </div>

                            {{-- Plan name --}}
                            <div class="text-xs font-bold mb-1 font-poppins
                                        {{ $isPopular ? 'text-white' : 'text-stikom' }}">
                                {{ $plan->nama_layanan ?? 'Paket' }}
                            </div>

                            {{-- Price --}}
                            <div class="font-black font-poppins leading-tight
                                        {{ $isPopular ? 'text-white text-xl' : 'text-stikom text-lg' }}">
                                {{ $plan->harga_format }}
                            </div>

                            {{-- Period --}}
                            <div class="text-[10px] mt-0.5 mb-3
                                        {{ $isPopular ? 'text-white/30' : 'text-gray-400' }}">
                                / {{ strtolower($plan->durasi_label) }}
                            </div>

                            {{-- Divider --}}
                            <div class="mb-3 {{ $isPopular ? 'border-t border-white/[.07]' : 'border-t border-gray-100' }}"></div>

                            {{-- Description --}}
                            @if(!empty($plan->deskripsi))
                                <p class="text-[10.5px] mb-3 leading-relaxed shrink-0
                                          {{ $isPopular ? 'text-white/40' : 'text-gray-500' }}">
                                    {{ $plan->deskripsi }}
                                </p>
                            @endif

                            {{--
                                Features — area scroll:
                                - flex-1 agar mengisi sisa ruang
                                - overflow-y-auto dengan max-height supaya card tetap seragam
                                - scrollbar tipis via inline style
                            --}}
                            <ul class="flex-1 overflow-y-auto space-y-2 mb-12 pr-0.5"
                                style="max-height: 140px; scrollbar-width: thin; scrollbar-color: {{ $isPopular ? 'rgba(255,255,255,.15) transparent' : 'rgba(0,0,0,.1) transparent' }};">

                                @forelse($plan->fiturs->sortBy('urutan') as $fitur)
                                    <li class="flex items-start gap-2 text-[11px] leading-snug
                                               {{ !$fitur->aktif ? 'opacity-30' : '' }}">

                                        @if($fitur->aktif)
                                            <div class="w-[14px] h-[14px] flex items-center justify-center shrink-0 mt-px
                                                        {{ $isPopular ? 'bg-stikom-blue' : 'bg-stikom-blue' }}">
                                                <svg class="w-[7px] h-[7px] text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3.5" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </div>
                                        @else
                                            <div class="w-[14px] h-[14px] flex items-center justify-center shrink-0 mt-px
                                                        {{ $isPopular ? 'border border-white/15' : 'border border-gray-200' }}">
                                                <svg class="w-[7px] h-[7px] {{ $isPopular ? 'text-white/30' : 'text-gray-300' }}"
                                                     fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </div>
                                        @endif

                                        <span class="{{ $isPopular ? 'text-white/75' : 'text-gray-600' }}">
                                            {{ $fitur->nama_fitur }}
                                        </span>
                                    </li>
                                @empty
                                    <li class="text-[10px] italic {{ $isPopular ? 'text-white/25' : 'text-gray-300' }}">
                                        Belum ada fitur.
                                    </li>
                                @endforelse
                            </ul>

                            {{-- CTA — selalu di bawah (mt-auto tidak diperlukan karena flex-1 sudah di ul) --}}
                            <a href="{{ route('langganan') }}"
                               class="block w-full py-3 text-[10px] font-black text-center font-poppins uppercase tracking-wide transition-all duration-200 shrink-0
                                      bg-stikom-accent text-stikom hover:bg-yellow-600 hover:text-white">
                                Berlangganan Sekarang
                            </a>

                        </div>
                    </div>
                @endforeach

            </div>
        @else
            <div class="text-center py-20 text-gray-400 text-sm font-body">
                Belum ada paket berlangganan tersedia.
            </div>
        @endif

    </div>
</section>