<div class="relative bg-white border border-gray-100 border-l-4 border-l-stikom-blue shadow-sm overflow-hidden">
    <div class="flex items-start sm:items-center gap-4 px-5 py-4">

        {{-- Icon --}}
        <div class="w-10 h-10 bg-stikom/5 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-stikom" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                      d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
        </div>

        {{-- Info --}}
        <div class="flex-1 min-w-0">
            <h2 class="text-sm font-bold text-gray-800 line-clamp-1 mb-1 font-body">{{ $meta->nama }}</h2>
            <div class="flex flex-wrap gap-x-4 gap-y-1 text-[11px] text-gray-400 font-body">
                @if($meta->klasifikasi)
                    <span class="flex items-center gap-1">
                        <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                        {{ $meta->klasifikasi->nama_klasifikasi }}
                    </span>
                @endif
                @if($meta->satuan_data)
                    <span class="flex items-center gap-1">
                        <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                        {{ $meta->satuan_data }}
                    </span>
                @endif
                @if($meta->frekuensi_penerbitan)
                    <span class="flex items-center gap-1">
                        <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ $meta->frekuensi_penerbitan }}
                    </span>
                @endif
                @if($meta->tahun_mulai_data)
                    <span class="flex items-center gap-1">
                        <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        sejak {{ $meta->tahun_mulai_data }}
                    </span>
                @endif
                @php
                    $firstData    = $meta->data->where('location_id', 0)->first();
                    $namaProdusen = $firstData?->rujukan?->produsen?->nama_produsen
                                    ?? $meta->produsen?->nama_produsen
                                    ?? $meta->produsen?->nama
                                    ?? null;
                @endphp
                @if($namaProdusen ?? null)
                    <span class="flex items-center gap-1">
                        <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        {{ $namaProdusen }}
                    </span>
                @endif
            </div>
        </div>

        {{-- Tombol langganan --}}
        <a href="{{ route('langganan') }}"
           class="shrink-0 flex items-center gap-1.5 px-3 py-1.5
                  bg-stikom text-stikom-accent hover:text-black text-xs font-bold
                  hover:bg-stikom-accent transition-colors duration-200 font-display">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
            Langganan
        </a>
    </div>
</div>