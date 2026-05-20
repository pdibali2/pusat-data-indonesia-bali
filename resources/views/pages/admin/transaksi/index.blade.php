@extends('layouts.main')

@section('title', 'Daftar Transaksi')

@section('content')
<div class="page-layout">

    <div class="page-header">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Daftar Transaksi</h1>
            <p class="text-sm text-gray-500 mt-0.5">Semua transaksi pembayaran pengguna</p>
        </div>
        <a href="{{ route('admin.transaksi.dashboard') }}"
           class="px-4 py-2 text-sm bg-white border border-gray-200 rounded-lg hover:bg-gray-50 text-gray-600 transition flex items-center gap-2">
            <i class="fas fa-chart-bar text-xs"></i> Dashboard
        </a>
    </div>

    <div class="card-panel">

        {{-- Filter --}}
        <div class="p-4 border-b border-gray-100">
            <form method="GET" action="{{ route('admin.transaksi.index') }}" class="flex flex-wrap gap-2">

                <div class="relative flex-1 min-w-[180px] max-w-xs">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Order ID, nama user, layanan..."
                           class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <select name="status"
                        class="px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    <option value="">Semua Status</option>
                    <option value="success"   {{ request('status') === 'success'   ? 'selected' : '' }}>Berhasil</option>
                    <option value="pending"   {{ request('status') === 'pending'   ? 'selected' : '' }}>Menunggu</option>
                    <option value="failed"    {{ request('status') === 'failed'    ? 'selected' : '' }}>Gagal</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                </select>

                <select name="layanan_id"
                        class="px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    <option value="">Semua Layanan</option>
                    @foreach($layanans as $lay)
                    <option value="{{ $lay->layanan_id }}" {{ request('layanan_id') == $lay->layanan_id ? 'selected' : '' }}>
                        {{ $lay->nama_layanan }}
                    </option>
                    @endforeach
                </select>

                <input type="date" name="dari" value="{{ request('dari') }}"
                       class="px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
                       title="Dari tanggal">

                <input type="date" name="sampai" value="{{ request('sampai') }}"
                       class="px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
                       title="Sampai tanggal">

                <button type="submit"
                        class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm rounded-lg transition">
                    Filter
                </button>
                @if(request()->hasAny(['search','status','layanan_id','dari','sampai']))
                <a href="{{ route('admin.transaksi.index') }}"
                   class="px-4 py-2 text-sm text-gray-500 hover:text-gray-700 rounded-lg transition">
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
                <p class="text-gray-500 text-sm font-medium">Tidak ada transaksi ditemukan</p>
            </div>
            @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                        <th class="px-5 py-3 text-left font-medium">#</th>
                        <th class="px-5 py-3 text-left font-medium">Order ID</th>
                        <th class="px-5 py-3 text-left font-medium">Pengguna</th>
                        <th class="px-5 py-3 text-left font-medium">Layanan</th>
                        <th class="px-5 py-3 text-left font-medium">Harga</th>
                        <th class="px-5 py-3 text-left font-medium">Metode</th>
                        <th class="px-5 py-3 text-left font-medium">Status</th>
                        <th class="px-5 py-3 text-left font-medium">Tanggal</th>
                        <th class="px-5 py-3 text-right font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($transaksis as $item)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-5 py-3.5 text-gray-400 text-xs">
                            {{ ($transaksis->currentPage() - 1) * $transaksis->perPage() + $loop->iteration }}
                        </td>
                        <td class="px-5 py-3.5 font-mono text-xs text-gray-500">
                            {{ $item->order_id }}
                        </td>
                        <td class="px-5 py-3.5">
                            <p class="font-medium text-gray-800">{{ $item->user?->name ?? '—' }}</p>
                            <p class="text-xs text-gray-400">{{ $item->user?->email ?? '' }}</p>
                        </td>
                        <td class="px-5 py-3.5">
                            <p class="text-gray-800">{{ $item->nama_layanan }}</p>
                            <p class="text-xs text-gray-400">{{ $item->durasi_label }}</p>
                        </td>
                        <td class="px-5 py-3.5 font-semibold text-gray-700">
                            {{ $item->harga_format }}
                        </td>
                        <td class="px-5 py-3.5 text-xs text-gray-500">
                            {{ $item->payment_type ? strtoupper(str_replace('_', ' ', $item->payment_type)) : '—' }}
                        </td>
                        <td class="px-5 py-3.5">
                            {!! $item->status_badge !!}
                        </td>
                        <td class="px-5 py-3.5 text-xs text-gray-500">
                            {{ $item->created_at->format('d M Y') }}<br>
                            <span class="text-gray-400">{{ $item->created_at->format('H:i') }}</span>
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <a href="{{ route('admin.transaksi.show', $item) }}"
                               class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition inline-flex" title="Detail">
                                <i class="fas fa-eye text-xs"></i>
                            </a>
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