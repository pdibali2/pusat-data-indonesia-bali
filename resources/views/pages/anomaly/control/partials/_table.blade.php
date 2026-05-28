{{-- Bulk Action Bar --}}
<div id="bulk-bar" class="hidden bg-sky-700 text-white rounded-xl px-4 py-2.5 mb-3 flex items-center gap-3 text-sm">
    <i class="fa-solid fa-check-double"></i>
    <span><strong id="bulk-count">0</strong> item dipilih</span>
    <div class="flex gap-2 ml-auto">
        <button onclick="bulkAction('approved')"
                class="bg-emerald-500 hover:bg-emerald-600 px-3 py-1 rounded-lg text-xs font-medium transition">
            <i class="fa-solid fa-check mr-1"></i> Approve Semua
        </button>
        <button onclick="bulkAction('rejected')"
                class="bg-red-500 hover:bg-red-600 px-3 py-1 rounded-lg text-xs font-medium transition">
            <i class="fa-solid fa-xmark mr-1"></i> Reject Semua
        </button>
    </div>
</div>

{{-- Table --}}
<div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-100 text-slate-500 text-xs uppercase tracking-wide">
                    <th class="px-4 py-3 text-left w-8">
                        <input type="checkbox" onchange="toggleSelectAll(this)"
                               class="rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                    </th>
                    <th class="px-4 py-3 text-left">Anomali</th>
                    <th class="px-4 py-3 text-left">Tabel / Tipe</th>
                    <th class="px-4 py-3 text-center">Severity</th>
                    <th class="px-4 py-3 text-center">Status</th>
                    <th class="px-4 py-3 text-right">Perubahan</th>
                    <th class="px-4 py-3 text-left">Terdeteksi</th>
                    <th class="px-4 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($anomalies as $anomaly)
                <tr class="hover:bg-slate-50/60 transition group">
                    <td class="px-4 py-3">
                        <input type="checkbox" value="{{ $anomaly->id }}"
                            class="row-checkbox rounded border-slate-300 text-sky-600 focus:ring-sky-500"
                            onchange="toggleSelect({{ $anomaly->id }})">
                    </td>
                    <td class="px-4 py-3">
                        <p class="font-medium text-slate-800 line-clamp-2 max-w-xs">{{ $anomaly->message }}</p>
                        @if($anomaly->reviews_count > 0)
                        <span class="text-xs text-slate-400">
                            <i class="fa-solid fa-clock-rotate-left mr-1"></i>{{ $anomaly->reviews_count }} review
                        </span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <p class="font-mono text-xs bg-slate-100 text-slate-600 px-2 py-0.5 rounded inline-block">
                            {{ $anomaly->table_name }}
                        </p>
                        <p class="text-xs text-slate-400 mt-1">{{ ucwords(str_replace('_',' ',$anomaly->anomaly_type)) }}</p>
                    </td>
                    <td class="px-4 py-3 text-center">
                        @include('pages.anomaly.control.components._badge-severity', ['severity' => $anomaly->severity])
                    </td>
                    <td class="px-4 py-3 text-center">
                        @include('pages.anomaly.control.components._badge-status', ['status' => $anomaly->status])
                    </td>
                    <td class="px-4 py-3 text-right">
                        @if($anomaly->percentage_change !== null)
                        <span class="font-semibold {{ $anomaly->percentage_change > 0 ? 'text-red-600' : 'text-emerald-600' }}">
                            {{ $anomaly->percentage_change > 0 ? '+' : '' }}{{ number_format($anomaly->percentage_change, 1) }}%
                        </span>
                        <div class="text-xs text-slate-400 mt-0.5">
                            {{ $anomaly->previous_value ?? '-' }} → {{ $anomaly->current_value }}
                        </div>
                        @else
                        <span class="text-slate-400">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-xs text-slate-500">
                        {{ \Carbon\Carbon::parse($anomaly->detected_at)->diffForHumans() }}
                        <div class="text-slate-400">{{ \Carbon\Carbon::parse($anomaly->detected_at)->format('d M Y') }}</div>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-1.5">
                            <a href="{{ route('anomaly.control.show', $anomaly->anomalies_id) }}"
                            class="p-1.5 rounded-lg text-slate-400 hover:text-sky-600 hover:bg-sky-50 transition"
                            title="Detail">
                                <i class="fa-solid fa-eye text-xs"></i>
                            </a>
                            @if(in_array($anomaly->status, ['warning','under_review']))
                            <button onclick="openReviewModal({{ $anomaly->id }}, '{{ $anomaly->status }}')"
                                    class="p-1.5 rounded-lg text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 transition"
                                    title="Review">
                                <i class="fa-solid fa-gavel text-xs"></i>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-16 text-center text-slate-400">
                        <i class="fa-solid fa-circle-check text-4xl text-emerald-300 mb-3 block"></i>
                        <p class="font-medium">Tidak ada anomali ditemukan</p>
                        <p class="text-xs mt-1">Semua data dalam kondisi normal</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($anomalies->hasPages())
    <div class="px-4 py-3 border-t border-slate-100 flex items-center justify-between">
        <p class="text-xs text-slate-500">
            Menampilkan {{ $anomalies->firstItem() }}–{{ $anomalies->lastItem() }}
            dari {{ $anomalies->total() }} anomali
        </p>
        {{ $anomalies->withQueryString()->links('vendor.pagination.simple-tailwind') }}
    </div>
    @endif
</div>