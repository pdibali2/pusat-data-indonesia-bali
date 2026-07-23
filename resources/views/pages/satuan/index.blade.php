@extends('layouts.main')

@section('title', 'Satuan')

@section('content')
<div class="page-layout">

    {{-- Header --}}
    <div class="page-header">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Kelola Satuan</h1>
            <p class="text-sm text-gray-500 mt-0.5">Daftar satuan untuk konversi dan metadata.</p>
        </div>
        <a href="{{ route('admin.satuan.create') }}"
           class="btn-primary text-xs">
            <i class="fas fa-plus text-xs"></i> Tambah Satuan
        </a>
    </div>

    @if(session('success'))
    <div class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg">
        <i class="fas fa-check-circle"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    <div class="card-panel">
        <div class="p-4 border-b border-gray-100">
            <form method="GET" action="{{ route('admin.satuan.index') }}" class="flex gap-2 flex-wrap">
                <div class="relative flex-1 min-w-[220px] max-w-sm">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Cari nama atau simbol satuan..."
                           class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <button type="submit"
                        class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm rounded-lg transition">
                    Cari
                </button>
                @if(request('search'))
                <a href="{{ route('admin.satuan.index') }}"
                   class="px-4 py-2 text-sm text-gray-500 hover:text-gray-700 rounded-lg transition">
                    Reset
                </a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            @if($satuans->isEmpty())
            <div class="py-16 text-center">
                <div class="w-14 h-14 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-ruler text-gray-400 text-xl"></i>
                </div>
                <p class="text-gray-500 text-sm font-medium">Belum ada satuan.</p>
                <p class="text-gray-400 text-xs mt-1">Tambahkan satuan baru untuk mempermudah konversi.</p>
                <a href="{{ route('admin.satuan.create') }}"
                   class="btn-link">
                    <i class="fas fa-plus text-xs"></i> Tambah sekarang
                </a>
            </div>
            @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                        <th class="px-5 py-3 text-left font-medium">#</th>
                        <th class="px-5 py-3 text-left font-medium">Nama Satuan</th>
                        <th class="px-5 py-3 text-left font-medium">Simbol</th>
                        <th class="px-5 py-3 text-left font-medium">Nilai Konversi</th>
                        <th class="px-5 py-3 text-left font-medium">Dibuat</th>
                        <th class="px-5 py-3 text-center font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($satuans as $item)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-5 py-3.5 text-gray-400">
                            {{ ($satuans->currentPage() - 1) * $satuans->perPage() + $loop->iteration }}
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="font-medium text-gray-800">{{ $item->nama_satuan }}</span>
                        </td>
                        <td class="px-5 py-3.5 text-gray-700">{{ $item->simbol ?? '-' }}</td>
                        <td class="px-5 py-3.5 text-gray-700">{{ number_format($item->nilai_konversi, 8, ',', '.') }}</td>
                        <td class="px-5 py-3.5 text-gray-500">{{ $item->created_at?->format('d M Y') ?? '-' }}</td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('admin.satuan.edit', $item) }}"
                                   class="p-1.5 rounded-md bg-green-500/10 text-green-400 hover:bg-green-500/20 transition"
                                   title="Edit">
                                    <i class="fas fa-edit text-xs"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>

        @if($satuans->hasPages())
        <div class="px-5 py-3 border-t border-gray-100 flex items-center justify-between text-sm text-gray-500">
            <span>Menampilkan {{ $satuans->firstItem() }}–{{ $satuans->lastItem() }} dari {{ $satuans->total() }} data</span>
            <div>{{ $satuans->links() }}</div>
        </div>
        @endif
    </div>
</div>
@endsection
