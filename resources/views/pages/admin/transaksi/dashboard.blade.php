@extends('layouts.main')

@section('title', 'Dashboard Berlangganan')

@section('content')
<div class="page-layout">

    <div class="page-header">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Dashboard Berlangganan</h1>
            <p class="text-sm text-gray-500 mt-0.5">Statistik & ringkasan semua data berlangganan</p>
        </div>
        <a href="{{ route('admin.transaksi.index') }}" class="btn-primary">
            <i class="fas fa-list text-xs"></i> Lihat Semua Transaksi
        </a>
    </div>

    {{-- Statistik Utama --}}
    <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">

        <div class="card-panel px-5 py-4 flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-receipt text-blue-500"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500">Total Transaksi Berlangganan</p>
                <p class="text-2xl font-bold text-gray-800">{{ number_format($stats['total_transaksi']) }}</p>
            </div>
        </div>

        <div class="card-panel px-5 py-4 flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-green-50 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-coins text-green-500"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500">Total Pendapatan</p>
                <p class="text-xl font-bold text-green-700">Rp {{ number_format($stats['total_pendapatan'], 0, ',', '.') }}</p>
            </div>
        </div>

        <div class="card-panel px-5 py-4 flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-purple-50 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-users text-purple-500"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500">Pelanggan Aktif</p>
                <p class="text-2xl font-bold text-purple-700">{{ number_format($stats['pelanggan_aktif']) }}</p>
            </div>
        </div>

        <div class="card-panel px-5 py-4 flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-yellow-50 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-clock text-yellow-500"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500">Menunggu Bayar</p>
                <p class="text-2xl font-bold text-yellow-600">{{ number_format($stats['transaksi_pending']) }}</p>
            </div>
        </div>

        <div class="card-panel px-5 py-4 flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-check-circle text-emerald-500"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500">Transaksi Sukses</p>
                <p class="text-2xl font-bold text-emerald-700">{{ number_format($stats['transaksi_sukses']) }}</p>
            </div>
        </div>

        <div class="card-panel px-5 py-4 flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-red-50 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-times-circle text-red-400"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500">Gagal / Batal</p>
                <p class="text-2xl font-bold text-red-500">{{ number_format($stats['transaksi_gagal']) }}</p>
            </div>
        </div>

    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Grafik Pendapatan Bulanan --}}
        <div class="lg:col-span-2 card-panel">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-700">Pendapatan Bulanan (12 Bulan Terakhir)</h2>
            </div>
            <div class="px-6 py-5">
                <canvas id="chartPendapatan" height="200"></canvas>
            </div>
        </div>

        {{-- Distribusi per Layanan --}}
        <div class="card-panel">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-700">Distribusi per Layanan</h2>
            </div>
            <div class="px-6 py-5">
                @forelse($perLayanan as $item)
                <div class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
                    <span class="text-xs text-gray-600 truncate max-w-[60%]">{{ $item->nama_layanan }}</span>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-semibold text-gray-800">{{ $item->jumlah }}x</span>
                        <span class="text-xs text-gray-400">Rp {{ number_format($item->total, 0, ',', '.') }}</span>
                    </div>
                </div>
                @empty
                <p class="text-xs text-gray-400 text-center py-4">Belum ada data</p>
                @endforelse
            </div>
        </div>

    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Metode Pembayaran --}}
        <div class="card-panel">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-700">Metode Pembayaran</h2>
            </div>
            <div class="px-6 py-5">
                <canvas id="chartMetode" height="200"></canvas>
            </div>
        </div>

        {{-- Transaksi Terbaru --}}
        <div class="card-panel">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-700">Transaksi Terbaru</h2>
                <a href="{{ route('admin.transaksi.index') }}" class="text-xs text-blue-600 hover:underline">Lihat semua →</a>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($transaksiTerbaru as $item)
                <div class="px-6 py-3 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-800">{{ $item->user?->name ?? '—' }}</p>
                        <p class="text-xs text-gray-400">{{ $item->nama_layanan }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs font-semibold text-gray-700">{{ $item->harga_format }}</p>
                        {!! $item->status_badge !!}
                    </div>
                </div>
                @empty
                <p class="text-xs text-gray-400 text-center py-8">Belum ada transaksi</p>
                @endforelse
            </div>
        </div>

    </div>

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// ── Grafik Pendapatan Bulanan ────────────────────────────────
const pendapatanData = @json($pendapatanBulanan);
const labels = pendapatanData.map(d => d.bulan);
const values = pendapatanData.map(d => parseFloat(d.total));

new Chart(document.getElementById('chartPendapatan'), {
    type: 'bar',
    data: {
        labels,
        datasets: [{
            label: 'Pendapatan (Rp)',
            data: values,
            backgroundColor: 'rgba(59, 130, 246, 0.7)',
            borderColor: 'rgba(59, 130, 246, 1)',
            borderWidth: 1,
            borderRadius: 6,
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: ctx => 'Rp ' + ctx.raw.toLocaleString('id-ID')
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: val => 'Rp ' + (val / 1000000).toFixed(1) + 'jt'
                }
            }
        }
    }
});

// ── Grafik Metode Pembayaran ─────────────────────────────────
const metodeData = @json($perMetode);
const metodeLabels = metodeData.map(d => d.payment_type ? d.payment_type.replace(/_/g, ' ').toUpperCase() : 'Lainnya');
const metodeValues = metodeData.map(d => d.jumlah);

if (metodeLabels.length > 0) {
    new Chart(document.getElementById('chartMetode'), {
        type: 'doughnut',
        data: {
            labels: metodeLabels,
            datasets: [{
                data: metodeValues,
                backgroundColor: [
                    '#3B82F6', '#10B981', '#F59E0B', '#EF4444',
                    '#8B5CF6', '#EC4899', '#14B8A6'
                ],
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom', labels: { font: { size: 11 } } }
            }
        }
    });
} else {
    document.getElementById('chartMetode').parentElement.innerHTML =
        '<p class="text-xs text-gray-400 text-center py-12">Belum ada data metode pembayaran</p>';
}
</script>
@endpush

@endsection