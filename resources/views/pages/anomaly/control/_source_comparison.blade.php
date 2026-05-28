@if($sourceComparison->isEmpty())
    <p class="text-xs text-gray-400 text-center py-4">Tidak ada data pembanding.</p>
@else
<div class="overflow-x-auto">
    <table class="w-full text-xs">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-2 py-2 text-left text-gray-500 font-medium">Produsen</th>
                <th class="px-2 py-2 text-right text-gray-500 font-medium">Nilai</th>
                <th class="px-2 py-2 text-right text-gray-500 font-medium">Selisih</th>
                <th class="px-2 py-2 text-right text-gray-500 font-medium">% Diff</th>
                <th class="px-2 py-2 text-center text-gray-500 font-medium">Status</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @foreach($sourceComparison as $src)
            <tr class="{{ $src['conflict'] ? 'bg-amber-50/40' : '' }}">
                <td class="px-2 py-2 font-medium text-gray-700">{{ $src['produsen'] }}</td>
                <td class="px-2 py-2 text-right font-mono text-gray-800">
                    {{ number_format($src['value'], 2) }}
                </td>
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