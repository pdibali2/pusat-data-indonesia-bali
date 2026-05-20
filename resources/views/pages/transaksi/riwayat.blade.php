@extends('layouts.main')

@section('title', 'Riwayat Transaksi')

@section('content')
<div class="page-layout">

    <div class="page-header">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Riwayat Transaksi</h1>
            <p class="text-sm text-gray-500 mt-0.5">Semua transaksi dan langganan kamu</p>
        </div>
        <a href="{{ route('langganan') }}" class="btn-primary">
            <i class="fas fa-plus text-xs"></i> Berlangganan
        </a>
    </div>

    {{-- Flash --}}
    @if(session('error'))
    <div class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-lg">
        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
    </div>
    @endif

    {{-- Statistik User --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="card-panel px-5 py-4">
            <p class="text-xs text-gray-500 mb-1">Total Transaksi</p>
            <p class="text-2xl font-bold text-gray-800">{{ $stats['total'] }}</p>
        </div>
        <div class="card-panel px-5 py-4">
            <p class="text-xs text-gray-500 mb-1">Langganan Aktif</p>
            <p class="text-2xl font-bold text-green-600">{{ $stats['aktif'] }}</p>
        </div>
        <div class="card-panel px-5 py-4">
            <p class="text-xs text-gray-500 mb-1">Menunggu Bayar</p>
            <p class="text-2xl font-bold text-yellow-600">{{ $stats['pending'] }}</p>
        </div>
        <div class="card-panel px-5 py-4">
            <p class="text-xs text-gray-500 mb-1">Total Dibayar</p>
            <p class="text-lg font-bold text-blue-600">Rp {{ number_format($stats['total_bayar'], 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- Filter --}}
    <div class="card-panel">
        <div class="p-4 border-b border-gray-100">
            <form method="GET" action="{{ route('transaksi.riwayat') }}" class="flex flex-wrap gap-2">
                <select name="status"
                        class="px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    <option value="">Semua Status</option>
                    <option value="success"   {{ request('status') === 'success'   ? 'selected' : '' }}>Berhasil</option>
                    <option value="pending"   {{ request('status') === 'pending'   ? 'selected' : '' }}>Menunggu</option>
                    <option value="failed"    {{ request('status') === 'failed'    ? 'selected' : '' }}>Gagal</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                </select>
                <button type="submit" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm rounded-lg transition">
                    Filter
                </button>
                @if(request()->filled('status'))
                <a href="{{ route('transaksi.riwayat') }}" class="px-4 py-2 text-sm text-gray-500 hover:text-gray-700 rounded-lg transition">
                    Reset
                </a>
                @endif
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
                        <th class="px-5 py-3 text-left font-medium">Status</th>
                        <th class="px-5 py-3 text-left font-medium">Masa Aktif</th>
                        <th class="px-5 py-3 text-left font-medium">Tanggal</th>
                        <th class="px-5 py-3 text-right font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($transaksis as $item)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-5 py-3.5 font-mono text-xs text-gray-500">
                            {{ $item->order_id }}
                        </td>
                        <td class="px-5 py-3.5">
                            <p class="font-medium text-gray-800">{{ $item->nama_layanan }}</p>
                            <p class="text-xs text-gray-400">{{ $item->durasi_label }}</p>
                        </td>
                        <td class="px-5 py-3.5 font-medium text-gray-700">
                            {{ $item->harga_format }}
                        </td>
                        <td class="px-5 py-3.5">
                            {!! $item->status_badge !!}
                        </td>
                        <td class="px-5 py-3.5 text-xs text-gray-500">
                            @if($item->isSuccess())
                                @if($item->aktif_sampai)
                                    {{ $item->aktif_mulai?->format('d M Y') }} –
                                    {{ $item->aktif_sampai->format('d M Y') }}
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
                        <td class="px-5 py-3.5 text-xs text-gray-500">
                            {{ $item->created_at->format('d M Y, H:i') }}
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <a href="{{ route('transaksi.detail', $item) }}"
                               class="text-xs text-blue-600 hover:underline">Detail</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>

        {{-- Pagination --}}
        @if($transaksis->hasPages())
        <div class="px-5 py-3 border-t border-gray-100 flex items-center justify-between text-sm text-gray-500">
            <span>Menampilkan {{ $transaksis->firstItem() }}–{{ $transaksis->lastItem() }} dari {{ $transaksis->total() }} data</span>
            <div>{{ $transaksis->links() }}</div>
        </div>
        @endif
    </div>
</div>
@endsection