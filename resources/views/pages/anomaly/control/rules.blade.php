@extends('layouts.main')

@section('content')
<div class="py-6 px-4 space-y-5">

    <div class="flex items-center gap-2 text-xs text-gray-400">
        <a href="{{ route('anomaly.control.index') }}"
           class="hover:text-sky-600 transition-colors font-semibold text-sky-600">
            <i class="fas fa-angle-left mr-1"></i>Control Anomali
        </a>
        <span>/</span>
        <span class="text-gray-600">Atur Threshold</span>
    </div>

    <div>
        <h1 class="text-xl font-bold text-gray-800">Anomaly Rules</h1>
        <p class="text-xs text-gray-400 mt-0.5">
            Konfigurasi threshold severity untuk deteksi anomali otomatis
        </p>
    </div>

    {{-- Info box --}}
    <div class="bg-sky-50 border border-sky-200 rounded-xl p-4 text-xs text-sky-800 space-y-1">
        <p class="font-semibold"><i class="fas fa-info-circle mr-1"></i>Cara Kerja Threshold</p>
        <p>Sistem menghitung persentase perubahan antar periode. Jika perubahan ≥ threshold, anomali dibuat dengan severity tersebut.</p>
        <p class="mt-1">
            <span class="font-semibold">Prioritas rule:</span>
            Rule spesifik (metadata + frekuensi) →
            Rule per metadata →
            Rule global (berlaku semua) →
            Default hardcoded (Low 20%, Medium 100%, High 500%, Critical 5000%)
        </p>
    </div>

    @if(session('success'))
    <div class="flex items-start gap-3 px-4 py-3 rounded-lg text-sm bg-green-50
                border border-green-200 text-green-800">
        <i class="fas fa-check-circle mt-0.5 text-green-500"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    {{-- Rules table --}}
    <div class="space-y-3">
        @forelse($rules as $rule)
        <div class="bg-white rounded-xl border {{ $rule->is_active ? 'border-gray-200' : 'border-gray-100 opacity-60' }} p-5">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <div class="flex items-center gap-2">
                        <p class="font-semibold text-gray-800 text-sm">{{ $rule->name }}</p>
                        @if(!$rule->is_active)
                        <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-500">
                            Nonaktif
                        </span>
                        @endif
                        @if(!$rule->metadata_id)
                        <span class="text-xs px-2 py-0.5 rounded-full bg-blue-50 text-blue-600 border border-blue-100">
                            Global
                        </span>
                        @endif
                    </div>
                    <p class="text-xs text-gray-400 mt-0.5">
                        Kode: <code class="bg-gray-100 px-1 rounded">{{ $rule->code }}</code>
                        @if($rule->metadata)
                         • Metadata: <strong>{{ $rule->metadata->nama }}</strong>
                        @endif
                        @if($rule->frekuensi)
                         • Frekuensi: <strong>{{ ucfirst($rule->frekuensi) }}</strong>
                        @endif
                    </p>
                    @if($rule->description)
                    <p class="text-xs text-gray-400 mt-1">{{ $rule->description }}</p>
                    @endif
                </div>
                <button onclick="toggleEdit({{ $rule->anomaly_rules_id }})"
                        class="text-xs text-sky-600 hover:text-sky-800 transition-colors flex items-center gap-1">
                    <i class="fas fa-edit"></i> Edit
                </button>
            </div>

            {{-- Threshold display --}}
            <div class="grid grid-cols-4 gap-3 mb-4">
                @php
                $thresholds = [
                    ['label'=>'Low',      'value'=>$rule->threshold_low,      'style'=>'background:#dbeafe;color:#1d4ed8;'],
                    ['label'=>'Medium',   'value'=>$rule->threshold_medium,   'style'=>'background:#fef9c3;color:#a16207;'],
                    ['label'=>'High',     'value'=>$rule->threshold_high,     'style'=>'background:#ffedd5;color:#c2410c;'],
                    ['label'=>'Critical', 'value'=>$rule->threshold_critical, 'style'=>'background:#fee2e2;color:#b91c1c;'],
                ];
                @endphp
                @foreach($thresholds as $t)
                <div class="text-center p-3 rounded-lg" style="{{ $t['style'] }}">
                    <p class="text-xs font-semibold opacity-80 mb-0.5">{{ $t['label'] }}</p>
                    <p class="text-lg font-bold font-mono">≥{{ number_format($t['value'], 0) }}%</p>
                </div>
                @endforeach
            </div>

            {{-- Edit form (hidden by default) --}}
            <form id="editForm-{{ $rule->anomaly_rules_id }}"
                  method="POST"
                  action="{{ route('anomaly.control.rules.update', $rule->anomaly_rules_id) }}"
                  class="hidden border-t border-gray-100 pt-4">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-3">
                    @foreach(['low'=>'Low','medium'=>'Medium','high'=>'High','critical'=>'Critical'] as $key=>$label)
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">
                            Threshold {{ $label }} (%)
                        </label>
                        <input type="number" name="threshold_{{ $key }}"
                               value="{{ $rule->{'threshold_'.$key} }}"
                               step="0.01" min="0"
                               class="w-full text-xs border border-gray-200 rounded-lg px-2.5 py-2
                                      focus:outline-none focus:ring-2 focus:ring-sky-400">
                    </div>
                    @endforeach
                </div>

                <div class="flex items-center gap-4 mb-3">
                    <label class="flex items-center gap-2 text-xs text-gray-700 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1"
                               {{ $rule->is_active ? 'checked' : '' }}
                               class="rounded border-gray-300 text-sky-600 focus:ring-sky-400">
                        Rule aktif
                    </label>
                </div>

                <div class="flex gap-2">
                    <button type="submit"
                            class="text-xs bg-sky-600 hover:bg-sky-700 text-white
                                   px-4 py-2 rounded-lg font-semibold transition-colors">
                        <i class="fas fa-save mr-1"></i>Simpan
                    </button>
                    <button type="button" onclick="toggleEdit({{ $rule->anomaly_rules_id }})"
                            class="text-xs border border-gray-200 text-gray-500
                                   hover:bg-gray-50 px-4 py-2 rounded-lg transition-colors">
                        Batal
                    </button>
                </div>
            </form>
        </div>
        @empty
        <div class="bg-white rounded-xl border border-gray-200 p-8 text-center text-gray-400">
            <i class="fas fa-sliders-h text-3xl text-gray-200 mb-2 block"></i>
            Belum ada rule. Jalankan seeder terlebih dahulu.
        </div>
        @endforelse
    </div>
</div>

<script>
function toggleEdit(id) {
    const form = document.getElementById(`editForm-${id}`);
    form.classList.toggle('hidden');
}
</script>
@endsection