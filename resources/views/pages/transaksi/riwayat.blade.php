@extends('layouts.main')

@section('title', 'Riwayat Transaksi')

@section('content')
<div class="page-layout">

    <div class="page-header">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Riwayat Berlangganan</h1>
            <p class="text-sm text-gray-500 mt-0.5">Semua transaksi dan langganan kamu</p>
        </div>
    </div>

    @if(session('error'))
    <div class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-lg">
        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
    </div>
    @endif

    {{-- Cari transaksi aktif milik user --}}
    @php
        $aktifNow = $transaksis->first(fn($t) => $t->isSuccess() && $t->isAktif());
        // fallback: cari dari semua transaksi jika halaman bukan 1
        if (! $aktifNow) {
            $aktifNow = \App\Models\Transaksi::where('user_id', Auth::id())
                ->where('status', 'success')
                ->where(function ($q) {
                    $q->whereNull('aktif_sampai')
                    ->orWhere('aktif_sampai', '>=', now());
                })
                ->latest('aktif_mulai')
                ->first();
        }
    @endphp

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Paket Aktif --}}
        <div class="card-panel px-5 py-4">
            <p class="text-xs text-gray-500 mb-1">Paket Aktif Saat Ini</p>
            @if($aktifNow)
                <p class="text-base font-bold text-green-600">{{ $aktifNow->nama_layanan }}</p>
            @else
                <p class="text-base font-bold text-gray-400">Tidak ada</p>
            @endif
        </div>

        {{-- Masa Berlaku --}}
        <div class="card-panel px-5 py-4">
            <p class="text-xs font-semibold text-gray-500 mb-2">Masa Berlaku Paket</p>
            @if($aktifNow)
                <p class="text-xs text-gray-500 mb-0.5">Mulai</p>
                <p class="text-sm font-bold text-green-600 mb-1">
                    {{ $aktifNow->aktif_mulai?->translatedFormat('l, d F Y') ?? '—' }}
                </p>
                <p class="text-xs text-gray-500 mb-0.5">Sampai</p>
                <p class="text-sm font-bold text-green-600">
                    {{ $aktifNow->aktif_sampai
                        ? $aktifNow->aktif_sampai->translatedFormat('l, d F Y')
                        : 'Selamanya' }}
                </p>
            @else
                <p class="text-sm text-gray-400">—</p>
            @endif
        </div>

        {{-- Status Paket --}}
        <div class="card-panel px-5 py-4">
            <p class="text-xs text-gray-500 mb-1">Status Paket</p>
            @if($aktifNow)
                <span class="inline-flex items-center gap-1.5 text-sm font-bold text-green-600">
                    <span class="w-2 h-2 rounded-full bg-green-500 inline-block"></span>
                    Aktif
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 text-sm font-bold text-gray-400">
                    <span class="w-2 h-2 rounded-full bg-gray-300 inline-block"></span>
                    Tidak Aktif
                </span>
            @endif
        </div>

        {{-- Sisa Waktu --}}
        <div class="card-panel px-5 py-4">
            <p class="text-xs text-gray-500 mb-1">Sisa Waktu</p>
            @if($aktifNow && $aktifNow->aktif_sampai)
                @php $sisaHari = (int) now()->diffInDays($aktifNow->aktif_sampai, false); @endphp
                <p class="text-base font-bold {{ $sisaHari <= 3 ? 'text-red-500' : 'text-green-600' }}">
                    {{ $sisaHari > 0 ? $sisaHari . ' hari lagi' : 'Hari ini berakhir' }}
                </p>
            @elseif($aktifNow)
                <p class="text-base font-bold text-green-600">Selamanya</p>
            @else
                <p class="text-base font-bold text-gray-400">—</p>
            @endif
        </div>
    </div>

    <div class="card-panel">
        {{-- Filter --}}
        <div class="card-panel">
        <div class="p-4 border-b border-gray-100">
            <form id="form-riwayat" method="GET" action="{{ route('transaksi.riwayat') }}">
                <div class="flex flex-wrap items-center gap-2">
                    <select name="status" onchange="this.form.submit()"
                            class="px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <option value="">Semua Status</option>
                        <option value="success"   {{ request('status') === 'success'   ? 'selected' : '' }}>Berhasil</option>
                        <option value="pending"   {{ request('status') === 'pending'   ? 'selected' : '' }}>Menunggu</option>
                        <option value="failed"    {{ request('status') === 'failed'    ? 'selected' : '' }}>Gagal</option>
                    </select>

                    @if(request()->filled('status'))
                        <a href="{{ route('transaksi.riwayat') }}"
                        class="inline-flex items-center gap-1 text-xs text-red-400 hover:text-red-600 transition">
                            <i class="fas fa-times-circle"></i> Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Tabel --}}
        <div class="overflow-x-auto">
            @if($transaksis->isEmpty())
            <div class="py-16 text-center">
                <div class="w-14 h-14 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-receipt text-gray-400 text-xl"></i>
                </div>
                <p class="text-gray-500 text-sm font-medium">Belum ada transaksi</p>
                <p class="text-gray-400 text-xs mt-1">Mulai berlangganan untuk mengakses layanan.</p>
                <a href="{{ route('langganan') }}" class="btn-link mt-3 inline-block">
                    <i class="fas fa-store text-xs"></i> Lihat Layanan
                </a>
            </div>
            @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                        <th class="px-5 py-3 text-left font-medium">Order ID</th>
                        <th class="px-5 py-3 text-left font-medium">Layanan</th>
                        <th class="px-5 py-3 text-left font-medium">Harga</th>
                        <th class="px-5 py-3 text-left font-medium">Menunggu Bayar</th>
                        <th class="px-5 py-3 text-left font-medium">Masa Aktif</th>
                        <th class="px-5 py-3 text-left font-medium">Tanggal</th>
                        <th class="px-5 py-3 text-right font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($transaksis as $item)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-5 py-3.5 font-mono text-xs text-gray-500">{{ $item->order_id }}</td>
                        <td class="px-5 py-3.5">
                            <p class="font-medium text-gray-800">{{ $item->nama_layanan }}</p>
                            <p class="text-xs text-gray-400">{{ $item->durasi_label }}</p>
                        </td>
                        <td class="px-5 py-3.5 font-medium text-gray-700">{{ $item->harga_format }}</td>
                        <td class="px-5 py-3.5">
                            @if($item->status === 'pending')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-50 text-yellow-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-yellow-400 inline-block"></span>
                                    Ya
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-500">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-400 inline-block"></span>
                                    Tidak
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-xs text-gray-500">
                            @if($item->isSuccess())
                                @if($item->aktif_sampai)
                                    {{ $item->aktif_mulai?->format('d M Y') }} – {{ $item->aktif_sampai->format('d M Y') }}
                                    @if($item->isAktif())
                                        <span class="ml-1 text-green-600 font-medium">Aktif</span>
                                    @else
                                        <span class="ml-1 text-red-500 font-medium">Berakhir</span>
                                    @endif
                                @else
                                    <span class="text-green-600 font-medium">Selamanya</span>
                                @endif
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-xs text-gray-500">{{ $item->created_at->format('d M Y, H:i') }}</td>
                        <td class="px-5 py-3.5 text-right">
                            {{-- Tombol Detail → buka modal --}}
                            <button type="button"
                                    onclick="showDetail({{ $item->transaksi_id }})"
                                    class="text-xs text-blue-600 hover:text-blue-800 hover:underline">
                                Detail
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>

        @if($transaksis->hasPages())
        <div class="px-5 py-3 border-t border-gray-100 flex items-center justify-between text-sm text-gray-500">
            <span>Menampilkan {{ $transaksis->firstItem() }}–{{ $transaksis->lastItem() }} dari {{ $transaksis->total() }} data</span>
            <div>{{ $transaksis->links() }}</div>
        </div>
        @endif
    </div>
</div>

{{-- ══════════════════════════════════════════════════════
     MODAL DETAIL TRANSAKSI — Portrait style
═══════════════════════════════════════════════════════ --}}
<div id="modal-detail"
     class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm px-4"
     onclick="closeDetail(event)">

    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm overflow-hidden"
         id="modal-card"
         onclick="event.stopPropagation()">

        {{-- Loading state --}}
        <div id="modal-loading" class="flex flex-col items-center justify-center py-16">
            <div class="w-8 h-8 border-4 border-blue-200 border-t-blue-600 rounded-full animate-spin"></div>
            <p class="text-sm text-gray-500 mt-3">Memuat detail...</p>
        </div>

        {{-- Content (diisi JS) --}}
        <div id="modal-content" class="hidden"></div>
    </div>
</div>

{{-- Data transaksi (JSON, untuk JS) --}}
<script>
const transaksiData = {
    @foreach($transaksis as $item)
    {{ $item->transaksi_id }}: {
        id:           {{ $item->transaksi_id }},
        order_id:     "{{ $item->order_id }}",
        nama_layanan: "{{ e($item->nama_layanan) }}",
        harga_format: "{{ $item->harga_format }}",
        durasi_label: "{{ $item->durasi_label }}",
        status:       "{{ $item->status }}",
        status_label: "{{ match($item->status) { 'success'=>'Berhasil','pending'=>'Menunggu','failed'=>'Gagal', default=>$item->status } }}",
        payment_type: "{{ $item->payment_type ? strtoupper(str_replace('_',' ',$item->payment_type)) : '—' }}",
        midtrans_id:  "{{ $item->midtrans_transaction_id ?? '—' }}",
        aktif_mulai:  "{{ $item->aktif_mulai ? $item->aktif_mulai->format('d M Y') : '—' }}",
        aktif_sampai: "{{ $item->aktif_sampai ? $item->aktif_sampai->format('d M Y') : ($item->isSuccess() ? 'Selamanya' : '—') }}",
        is_aktif:     {{ $item->isAktif() ? 'true' : 'false' }},
        is_success:   {{ $item->isSuccess() ? 'true' : 'false' }},
        created_at:   "{{ $item->created_at->format('d M Y, H:i') }}",
        updated_at:   "{{ $item->updated_at->format('d M Y, H:i') }}",
        snap_token:   "{{ $item->snap_token ? 'Ada' : '—' }}",
    },
    @endforeach
};
</script>

@push('scripts')
<script>
    const statusCfg = {
        success:   { bg: 'bg-emerald-50',  text: 'text-emerald-700', dot: 'bg-emerald-500', label: 'Berhasil'   },
        pending:   { bg: 'bg-yellow-50',   text: 'text-yellow-700',  dot: 'bg-yellow-400',  label: 'Menunggu'   },
        failed:    { bg: 'bg-red-50',      text: 'text-red-700',     dot: 'bg-red-500',      label: 'Gagal'      },
        cancelled: { bg: 'bg-gray-100',    text: 'text-gray-600',    dot: 'bg-gray-400',     label: 'Dibatalkan' },
    };
    
    function showDetail(id) {
        const modal   = document.getElementById('modal-detail');
        const loading = document.getElementById('modal-loading');
        const content = document.getElementById('modal-content');
    
        loading.classList.remove('hidden');
        content.classList.add('hidden');
        content.innerHTML = '';
        modal.classList.remove('hidden');
    
        setTimeout(() => {
            const d = transaksiData[id];
            if (!d) {
                loading.innerHTML = '<p class="text-red-500 text-sm py-8 text-center px-6">Data tidak ditemukan.</p>';
                return;
            }
    
            const cfg = statusCfg[d.status] || statusCfg.pending;
    
            // Header gradient berdasarkan status
            const headerBg = d.status === 'success'   ? 'from-emerald-500 to-teal-600'
                        : d.status === 'pending'   ? 'from-amber-400 to-yellow-500'
                        : d.status === 'failed'    ? 'from-red-500 to-rose-600'
                        : 'from-gray-400 to-gray-500';
    
            // Icon berdasarkan status
            const headerIcon = d.status === 'success'
                ? '<i class="fas fa-check-circle text-white text-3xl"></i>'
                : d.status === 'pending'
                ? '<i class="fas fa-clock text-white text-3xl"></i>'
                : '<i class="fas fa-times-circle text-white text-3xl"></i>';
    
            // Footer berdasarkan status
            const footerHtml = d.status === 'success'
                ? `<div class="bg-emerald-50 border border-emerald-100 rounded-xl px-4 py-3 text-center">
                    <p class="text-xs text-emerald-700 font-medium">
                        <i class="fas fa-shield-alt mr-1"></i>
                        Langganan ${d.is_aktif ? 'sedang aktif' : 'sudah berakhir'}
                    </p>
                </div>`
                : d.status === 'pending'
                ? `<div class="bg-yellow-50 border border-yellow-100 rounded-xl px-4 py-3 text-center">
                    <p class="text-xs text-yellow-700 font-medium">
                        <i class="fas fa-clock mr-1"></i>
                        Menunggu konfirmasi pembayaran
                    </p>
                </div>`
                : `<div class="bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-center">
                    <p class="text-xs text-gray-500">
                        <i class="fas fa-info-circle mr-1"></i>
                        Transaksi ini tidak aktif
                    </p>
                </div>`;
    
            content.innerHTML = `
                <div>
                    <div class="bg-gradient-to-br ${headerBg} px-6 pt-6 pb-8 relative">
                        <button onclick="closeModal()"
                                class="absolute top-4 right-4 w-7 h-7 rounded-full bg-white/20 hover:bg-white/30 flex items-center justify-center transition">
                            <i class="fas fa-times text-white text-xs"></i>
                        </button>
                        <div class="flex flex-col items-center text-center">
                            ${headerIcon}
                            <p class="text-white font-bold text-lg mt-2">${cfg.label}</p>
                            <p class="text-white/80 text-xs mt-0.5 font-mono">${d.order_id}</p>
                            <p class="text-white font-bold text-2xl mt-3">${d.harga_format}</p>
                        </div>
                    </div>
    
                    <div class="-mt-4 relative z-10">
                        <svg viewBox="0 0 400 24" class="w-full" preserveAspectRatio="none" style="height:24px">
                            <path d="M0,0 Q200,24 400,0 L400,24 L0,24 Z" fill="white"/>
                        </svg>
                    </div>
    
                    <div class="px-5 -mt-2 pb-2 space-y-0 divide-y divide-gray-100">
                        ${row('Layanan',       d.nama_layanan)}
                        ${row('Durasi',        d.durasi_label)}
                        ${row('Metode Bayar',  d.payment_type)}
                        ${row('ID Midtrans',   d.midtrans_id, 'mono')}
                        ${row('Mulai Aktif',   d.aktif_mulai)}
                        ${row('Aktif Hingga',  d.aktif_sampai, d.is_aktif ? 'green' : '')}
                        ${row('Tgl Transaksi', d.created_at)}
                        ${row('Tgl Update',    d.updated_at)}
                    </div>
    
                    <div class="px-5 py-4 mt-1">
                        ${footerHtml}
                    </div>
    
                    <div class="px-5 pb-5">
                        <button onclick="closeModal()"
                                class="w-full py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-600 text-sm font-medium rounded-xl transition">
                            Tutup
                        </button>
                    </div>
                </div>`;
    
            loading.classList.add('hidden');
            content.classList.remove('hidden');
        }, 200);
    }
    
    function row(label, val, type = '') {
        const valClass = type === 'mono'  ? 'font-mono text-xs text-gray-600 break-all'
                    : type === 'green' ? 'font-semibold text-emerald-600'
                    : 'text-gray-700';
        return `
            <div class="flex justify-between items-center py-2.5 gap-4">
                <span class="text-xs text-gray-500 flex-shrink-0">${label}</span>
                <span class="text-xs ${valClass} text-right">${val || '—'}</span>
            </div>`;
    }
    
    function closeModal() {
        document.getElementById('modal-detail').classList.add('hidden');
    }
    function closeDetail(e) {
        if (e.target === document.getElementById('modal-detail')) closeModal();
    }
    
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });
</script>
@endpush

@endsection