@if($sourceComparison->isEmpty())
    <p class="text-xs text-gray-400 text-center py-4">Tidak ada data pembanding.</p>
@else
    @php $unitsConsistent = $sourceComparison->first()['units_consistent'] ?? true; @endphp
    @unless($unitsConsistent)
    <div class="mb-2 px-3 py-2 rounded-lg text-xs font-medium bg-pink-50 text-pink-700 border border-pink-200 flex items-center gap-2">
        <i class="fas fa-scale-unbalanced"></i>
        Satuan rujukan berbeda antar sumber untuk periode ini, periksa kemungkinan kesalahan normalisasi selain konflik nilainya.
    </div>
    @endunless

@php
    $refUnits = $sourceComparison->pluck('satuan_rujukan')->filter()->unique()->values();
    $unitMismatch = $refUnits->count() > 1;
    $primaryUnit = $refUnits->first();
@endphp
<div class="mb-3 text-xs text-gray-500">
    @if($unitMismatch)
        <span class="font-semibold text-amber-700">Terdapat perbedaan satuan rujukan antar sumber.</span>
        Periksa apakah konflik terjadi karena nilai berbeda atau karena perbedaan unit.
    @elseif($primaryUnit)
        <span class="font-semibold text-slate-700">Semua sumber melaporkan satuan rujukan: {{ $primaryUnit }}.</span>
    @endif
</div>
<div class="overflow-x-auto">
    <table class="w-full text-xs">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-2 py-2 text-left text-gray-500 font-medium">Rujukan</th>
                <th class="px-2 py-2 text-right text-gray-500 font-medium">Nilai</th>
                <th class="px-2 py-2 text-left text-gray-500 font-medium">Satuan Rujukan</th>
                <th class="px-2 py-2 text-right text-gray-500 font-medium">Selisih</th>
                <th class="px-2 py-2 text-right text-gray-500 font-medium">% Diff</th>
                <th class="px-2 py-2 text-center text-gray-500 font-medium">Status Nilai</th>
                <th class="px-2 py-2 text-center text-gray-500 font-medium">Status Satuan</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @php
                $sourceComparison = $sourceComparison->sortByDesc(fn($s) => $s['data_id'] === ($data->id ?? null));
            @endphp
            @foreach($sourceComparison as $src)
            <tr class="{{ $src['conflict'] ? 'bg-amber-50/40' : '' }}">
                <td class="px-2 py-2 font-medium text-gray-700">
                    {{ $src['rujukan'] }}
                    @if($src['has_unit_note'] ?? false)
                    <div class="text-[10px] text-pink-500 mt-0.5">
                        <i class="fas fa-scale-unbalanced mr-0.5"></i>lapor dalam {{ $src['satuan_rujukan'] }}
                    </div>
                    @endif
                </td>
                <td class="px-2 py-2 text-right font-mono text-gray-800">
                    {{ number_format($src['value'], 2) }}
                </td>
                <td class="px-2 py-2 text-gray-500">{{ $src['satuan_rujukan'] ?? $src['satuan'] ?? '—' }}</td>
                <td class="px-2 py-2 text-right font-mono {{ $src['selisih'] >= 0 ? 'text-red-600' : 'text-blue-600' }}">
                    {{ $src['selisih'] >= 0 ? '+' : '' }}{{ number_format($src['selisih'], 2) }}
                </td>
                <td class="px-2 py-2 text-right font-mono {{ $src['conflict'] ? 'text-amber-600 font-semibold' : 'text-gray-500' }}">
                    {{ $src['pct_diff'] }}%
                </td>
                <td class="px-2 py-2 text-center">
                    @if($src['conflict'])
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold"
                            style="background:#fef9c3; color:#a16207;">Konflik</span>
                    @else
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold"
                            style="background:#dcfce7; color:#15803d;">OK</span>
                    @endif
                </td>
                <td class="px-2 py-2 text-center">
                    @if($src['unit_conflict'] ?? false)
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold"
                            style="background:#fce7f3; color:#be185d;">Konflik</span>
                    @else
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold"
                            style="background:#dcfce7; color:#15803d;">OK</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <p class="text-xs text-gray-400 mt-2 px-2">
        Baseline rata-rata: <span class="font-semibold">{{ number_format($sourceComparison->first()['avg_baseline'], 2) }}</span>
        — Konflik jika selisih &gt; 5% dari rata-rata.
    </p>
</div>
@endif