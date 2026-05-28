@if($reviews->isEmpty())
<div class="text-center py-8">
    <i class="fa-regular fa-clock text-3xl text-slate-200 mb-2 block"></i>
    <p class="text-sm text-slate-400">Belum ada keputusan</p>
    <p class="text-xs text-slate-300 mt-0.5">Menunggu review dari administrator</p>
</div>
@else
<div class="relative">
    {{-- Garis vertikal --}}
    <div class="absolute left-4 top-0 bottom-0 w-px bg-slate-100"></div>

    <div class="space-y-5">
        @foreach($reviews as $review)
        @php
        $decisionMap = [
            'approved'           => ['emerald', 'fa-check',        'Approved'],
            'approved_with_note' => ['teal',    'fa-note-sticky',  'Approved dengan Catatan'],
            'rejected'           => ['red',     'fa-xmark',        'Rejected'],
            'revised'            => ['orange',  'fa-rotate',       'Minta Revisi'],
            'under_review'       => ['indigo',  'fa-magnifying-glass','Under Review'],
        ];
        [$color, $icon, $label] = $decisionMap[$review->decision] ?? ['slate', 'fa-circle', ucfirst($review->decision)];
        @endphp

        <div class="relative flex gap-4 pl-0">
            {{-- Dot + Icon --}}
            <div class="shrink-0 w-8 h-8 rounded-full bg-{{ $color }}-100 border-2 border-{{ $color }}-300
                        flex items-center justify-center z-10">
                <i class="fa-solid {{ $icon }} text-{{ $color }}-600 text-xs"></i>
            </div>

            {{-- Konten --}}
            <div class="flex-1 pb-1">
                <div class="flex items-start justify-between gap-2 mb-1">
                    <div>
                        <span class="text-sm font-semibold text-slate-800">{{ $label }}</span>
                        <span class="mx-1.5 text-slate-300">·</span>
                        <span class="text-xs text-slate-500">{{ $review->reviewer->name ?? 'System' }}</span>
                    </div>
                    <time class="text-xs text-slate-400 whitespace-nowrap shrink-0">
                        {{ \Carbon\Carbon::parse($review->created_at)->format('d M Y') }}
                        <br>
                        <span class="text-slate-300">{{ \Carbon\Carbon::parse($review->created_at)->format('H:i') }} WITA</span>
                    </time>
                </div>

                {{-- Reviewer Badge --}}
                <div class="flex items-center gap-1.5 mb-2">
                    <div class="w-5 h-5 rounded-full bg-slate-200 flex items-center justify-center text-slate-600 font-bold text-[10px] shrink-0">
                        {{ strtoupper(substr($review->reviewer->name ?? 'S', 0, 1)) }}
                    </div>
                    <span class="text-xs text-slate-500">{{ $review->reviewer->role ?? 'Administrator' }}</span>
                </div>

                {{-- Justifikasi --}}
                <div class="bg-slate-50 border border-slate-100 rounded-xl px-3 py-2.5">
                    <p class="text-xs text-slate-400 mb-1 flex items-center gap-1">
                        <i class="fa-solid fa-quote-left text-[10px]"></i> Justifikasi
                    </p>
                    <p class="text-sm text-slate-700 leading-relaxed">{{ $review->justification }}</p>
                </div>

                {{-- Relative time --}}
                <p class="text-xs text-slate-300 mt-1.5">
                    {{ \Carbon\Carbon::parse($review->created_at)->diffForHumans() }}
                </p>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif