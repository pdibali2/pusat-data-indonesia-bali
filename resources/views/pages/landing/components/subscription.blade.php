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
            <div class="flex flex-wrap gap-4 items-stretch justify-center fade-up">

                @foreach($pricings->sortBy('urutan') as $plan)
                    @php $isPopular = $plan->is_popular; @endphp

                    <div class="relative flex flex-col overflow-hidden group bg-white
                                w-full sm:w-[calc(50%-0.5rem)] md:w-[calc(33.333%-0.667rem)]
                                xl:w-[calc(25%-0.75rem)] 2xl:w-[calc(20%-0.8rem)]
                                {{ $isPopular
                                    ? 'ring-2 ring-sky-400 shadow-2xl shadow-sky-500/15 -my-3 z-10'
                                    : 'border border-gray-100 shadow-sm' }}"
                        style="{{ $isPopular ? '' : 'border-top: 3px solid transparent;' }}"
                        @if(!$isPopular)
                            x-data
                            @mouseenter="$el.style.borderTopColor='#A32D2D'"
                            @mouseleave="$el.style.borderTopColor='transparent'"
                        @endif>

                        {{-- Top accent bar (popular only) --}}
                        @if($isPopular)
                            <div class="h-[3px] bg-sky-400 w-full shrink-0"></div>
                        @endif

                        {{-- Badge row --}}
                        @if($isPopular)
                            <div class="bg-sky-50 border-b border-sky-100 px-4 py-2 flex items-center justify-between shrink-0">
                                <span class="text-sky-700 text-[9px] font-black uppercase tracking-widest font-poppins flex items-center gap-1">
                                    <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                    Paling Populer
                                </span>
                                <span class="text-sky-500 text-[8px] font-bold uppercase tracking-widest font-poppins">Terbaik</span>
                            </div>
                        @else
                            <div class="px-4 py-1.5 border-b border-gray-100 shrink-0">
                                <span class="text-stikom-red text-[9px] font-bold uppercase tracking-widest font-poppins">Paket</span>
                            </div>
                        @endif

                        {{-- Card body — flex column, tumbuh mengisi tinggi card --}}
                        <div class="flex flex-col flex-1 p-[14px] {{ $isPopular ? 'pt-4' : '' }}">

                            <div class="text-[9px] font-bold uppercase tracking-widest text-gray-400 mb-2 font-poppins">
                                {{ $plan->durasi_label }}
                            </div>

                            <div class="text-xs font-bold text-stikom mb-1 font-poppins">
                                {{ $plan->nama_layanan ?? 'Paket' }}
                            </div>

                            <div class="font-black font-poppins leading-tight text-stikom {{ $isPopular ? 'text-xl' : 'text-lg' }}">
                                {{ $plan->harga_format }}
                            </div>

                            <div class="text-[10px] text-gray-400 mt-0.5 mb-3">
                                / {{ strtolower($plan->durasi_label) }}
                            </div>

                            <div class="mb-3 border-t {{ $isPopular ? 'border-sky-100' : 'border-gray-100' }}"></div>

                            @if(!empty($plan->deskripsi))
                                <p class="text-[10.5px] mb-3 leading-relaxed shrink-0 text-gray-500">
                                    {{ $plan->deskripsi }}
                                </p>
                            @endif

                            <ul class="flex-1 overflow-y-auto space-y-2 mb-12 pr-0.5"
                                style="max-height: 140px; scrollbar-width: thin; scrollbar-color: {{ $isPopular ? 'rgba(16,185,129,.25) transparent' : 'rgba(0,0,0,.1) transparent' }};">

                                @forelse($plan->fiturs->sortBy('urutan') as $fitur)
                                    <li class="flex items-start gap-2 text-[11px] leading-snug {{ !$fitur->aktif ? 'opacity-30' : '' }}">

                                        @if($fitur->aktif)
                                            <div class="w-[14px] h-[14px] flex items-center justify-center shrink-0 mt-px
                                                        {{ $isPopular ? 'bg-stikom-blue' : 'bg-stikom-blue' }}">
                                                <svg class="w-[7px] h-[7px] text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3.5" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </div>
                                        @else
                                            <div class="w-[14px] h-[14px] flex items-center justify-center shrink-0 mt-px border border-gray-200">
                                                <svg class="w-[7px] h-[7px] text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </div>
                                        @endif

                                        <span class="text-gray-600">{{ $fitur->nama_fitur }}</span>
                                    </li>
                                @empty
                                    <li class="text-[10px] italic text-gray-300">Belum ada fitur.</li>
                                @endforelse
                            </ul>

                            <a href="{{ route('langganan') }}"
                            class="block w-full py-3 text-[10px] font-black text-center font-poppins uppercase tracking-wide transition-all duration-200 shrink-0
                                    {{ $isPopular
                                        ? 'bg-stikom-accent text-stikom hover:bg-yellow-600 hover:text-white'
                                        : 'bg-stikom-accent text-stikom hover:bg-yellow-600 hover:text-white' }}">
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