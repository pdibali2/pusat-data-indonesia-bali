@extends('layouts.main')

@section('title', 'Klasifikasi')

@section('content')
<div class="page-layout">

    {{-- Header --}}
    <div class="page-header">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Kelola Klasifikasi</h1>
            <p class="text-sm text-gray-500 mt-0.5">Daftar kategori klasifikasi metadata</p>
        </div>
        <a href="{{ route('admin.klasifikasi.create') }}"
           class="btn-primary text-xs">
            <i class="fas fa-plus text-xs"></i> Tambah Klasifikasi
        </a>
    </div>

    {{-- Flash --}}
    @if(session('success'))
    <div class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg">
        <i class="fas fa-check-circle"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif
    @if(session('error'))
    <div class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-lg">
        <i class="fas fa-exclamation-circle"></i>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    {{-- Search --}}
    <div class="card-panel">
        <div class="p-4 border-b border-gray-100">
            <form method="GET" action="{{ route('admin.klasifikasi.index') }}" class="flex gap-2">
                <div class="relative flex-1 max-w-xs">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Cari klasifikasi..."
                           class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <button type="submit"
                        class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm rounded-lg transition">
                    Cari
                </button>
                @if(request('search'))
                <a href="{{ route('admin.klasifikasi.index') }}"
                   class="px-4 py-2 text-sm text-gray-500 hover:text-gray-700 rounded-lg transition">
                    Reset
                </a>
                @endif
            </form>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            @if($klasifikasis->isEmpty())
            <div class="py-16 text-center">
                <div class="w-14 h-14 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-tags text-gray-400 text-xl"></i>
                </div>
                <p class="text-gray-500 text-sm font-medium">Belum ada klasifikasi</p>
                <p class="text-gray-400 text-xs mt-1">Mulai dengan menambahkan klasifikasi baru.</p>
                <a href="{{ route('admin.klasifikasi.create') }}"
                   class="btn-link">
                    <i class="fas fa-plus text-xs"></i> Tambah sekarang
                </a>
            </div>
            @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                        <th class="px-5 py-3 text-left font-medium">#</th>
                        <th class="px-5 py-3 text-left font-medium">Nama Klasifikasi</th>
                        <th class="px-5 py-3 text-left font-medium">Ikon</th>
                        <th class="px-5 py-3 text-left font-medium">Status</th>
                        <th class="px-5 py-3 text-left font-medium">Dibuat</th>
                        <th class="px-5 py-3 text-center font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($klasifikasis as $item)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-5 py-3.5 text-gray-400">
                            {{ ($klasifikasis->currentPage() - 1) * $klasifikasis->perPage() + $loop->iteration }}
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="font-medium text-gray-800">{{ $item->nama_klasifikasi }}</span>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="inline-flex items-center gap-2 px-2 py-1 rounded-full bg-slate-50 border border-gray-200 text-slate-600 text-xs">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    {!! config('klasifikasi_icons.svg')[$item->icon] ?? config('klasifikasi_icons.svg')[config('klasifikasi_icons.default')] !!}
                                </svg>
                                <span>{{ $item->icon ?? 'default' }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-3.5">
                            @if ($item->status === 1)
                                <span class="px-2 py-0.5 rounded-full text-xs bg-emerald-500/10 text-emerald-600 border border-emerald-500/20">
                                    <i class="fas fa-check-circle text-xs"></i> Aktif
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-xs bg-red-500/10 text-red-600 border border-red-500/20">
                                    <i class="fas fa-circle-xmark text-xs"></i> Nonaktif
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-gray-500">
                            {{ $item->created_at->format('d M Y') }}
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('admin.klasifikasi.show', $item) }}"
                                   class="p-1.5 rounded-md bg-blue-500/10 text-blue-400 hover:bg-blue-500/20 transition"
                                   title="Detail">
                                    <i class="fas fa-eye text-xs"></i>
                                </a>
                                <a href="{{ route('admin.klasifikasi.edit', $item) }}"
                                   class="p-1.5 rounded-md bg-green-500/10 text-green-400 hover:bg-green-500/20 transition"
                                   title="Edit">
                                    <i class="fas fa-edit text-xs"></i>
                                </a>
                                <form action="{{ route('admin.klasifikasi.toggle_status', $item) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @if ($item->status === 1)
                                        <button type="submit"
                                                class="p-1.5 rounded-md bg-yellow-500/10 text-yellow-400 hover:bg-yellow-500/20 transition"
                                                title="Nonaktifkan"
                                                onclick="return confirm('Yakin ingin menonaktifkan klasifikasi {{ addslashes($item->nama_klasifikasi) }}?')">
                                            <i class="fas fa-ban text-xs"></i>
                                        </button>
                                    @else
                                        <button type="submit"
                                                class="p-1.5 rounded-md bg-emerald-500/10 text-emerald-400 hover:bg-emerald-500/20 transition"
                                                title="Aktifkan">
                                            <i class="fas fa-check text-xs"></i>
                                        </button>
                                    @endif
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>

        {{-- Pagination --}}
        @if($klasifikasis->hasPages())
        <div class="px-5 py-3 border-t border-gray-100 flex items-center justify-between text-sm text-gray-500">
            <span>Menampilkan {{ $klasifikasis->firstItem() }}–{{ $klasifikasis->lastItem() }} dari {{ $klasifikasis->total() }} data</span>
            <div>{{ $klasifikasis->links() }}</div>
        </div>
        @endif
    </div>
</div>

{{-- Modal Konfirmasi Hapus --}}
<div id="modal-delete" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm mx-4 p-6">
        <div class="flex items-center justify-center w-12 h-12 bg-red-100 rounded-full mx-auto mb-4">
            <i class="fas fa-trash text-red-500"></i>
        </div>
        <h3 class="text-center text-gray-800 font-semibold text-base">Hapus Klasifikasi?</h3>
        <p class="text-center text-gray-500 text-sm mt-1">
            Anda akan menghapus <strong id="modal-nama" class="text-gray-700"></strong>. Tindakan ini tidak dapat dibatalkan.
        </p>
        <form id="modal-form" method="POST" class="mt-5 flex gap-3">
            @csrf @method('DELETE')
            <button type="button" onclick="closeModal()"
                    class="flex-1 py-2 border border-gray-200 text-gray-600 text-sm rounded-lg hover:bg-gray-50 transition">
                Batal
            </button>
            <button type="submit"
                    class="flex-1 py-2 bg-red-600 hover:bg-red-700 text-white text-sm rounded-lg transition">
                Ya, Hapus
            </button>
        </form>
    </div>
</div>
<script>
function confirmDelete(url, nama) {
    document.getElementById('modal-form').action = url;
    document.getElementById('modal-nama').textContent = nama;
    document.getElementById('modal-delete').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('modal-delete').classList.add('hidden');
}

document.getElementById('modal-delete').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>
@endsection