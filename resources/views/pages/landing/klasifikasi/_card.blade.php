<div
    class="metadata-item group bg-white border border-gray-100 border-l-4 border-l-transparent
           shadow-sm hover:border-l-stikom-blue hover:shadow-md
           transition-all duration-200 cursor-pointer overflow-hidden"
    data-id="{{ $meta->metadata_id }}"
    data-nama="{{ $meta->nama }}"
    onclick="window.location.href='{{ route('landing.data.show', $meta->metadata_id) }}'"
>
    <div class="flex items-start sm:items-center gap-4 px-5 py-4">

        {{-- Icon --}}
        <div class="w-10 h-10 bg-stikom/5 group-hover:bg-stikom flex items-center justify-center shrink-0 transition-colors duration-200 mt-0.5 sm:mt-0">
            <svg class="w-5 h-5 text-stikom group-hover:text-stikom-blue transition-colors duration-200"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                      d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
        </div>

        {{-- Info --}}
        <div class="flex-1 min-w-0">
            <h2 class="text-sm font-bold text-gray-800 group-hover:text-stikom transition-colors duration-200 line-clamp-1 mb-1 font-body">
                {{ $meta->nama }}
            </h2>
            <div class="flex flex-wrap gap-x-4 gap-y-1 text-[11px] text-gray-400 font-body">

                @if($meta->satuan_data)
                    <span class="flex items-center gap-1">
                        <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                        {{ $meta->satuan_data }}
                    </span>
                @endif

                @if($meta->frekuensi_penerbitan)
                    <span class="flex items-center gap-1">
                        <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ $meta->frekuensi_penerbitan }}
                    </span>
                @endif

                @if($meta->tahun_mulai_data)
                    <span class="flex items-center gap-1">
                        <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Sejak {{ $meta->tahun_mulai_data }}
                    </span>
                @endif

            </div>
        </div>

        {{-- Lock badge + arrow --}}
        <div class="shrink-0 flex items-center gap-2">
            <div class="hidden sm:flex items-center gap-1.5 text-[11px] text-stikom-blue
                        bg-stikom-blue/10 border border-stikom-blue/25
                        px-2.5 py-1.5 font-bold font-body">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                Berlangganan
            </div>
            <svg class="w-4 h-4 text-gray-300 group-hover:text-stikom-blue group-hover:translate-x-0.5 transition-all duration-200"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </div>

    </div>
</div>