{{-- resources/views/pages/rujukan/index.blade.php --}}
@extends('layouts.main')

@section('title', 'Kelola Rujukan')

@section('content')
<div class="page-layout">

    <div class="page-header">
        <div>
            <h2 class="text-lg font-semibold text-gray-700">Kelola Rujukan</h2>
            <p class="text-xs text-gray-500 mt-0.5">Manajemen data referensi / rujukan</p>
        </div>
        <a href="{{ route('admin.rujukan.create') }}"
           class="btn-primary">
            <i class="fas fa-plus"></i> Tambah Rujukan
        </a>
    </div>

    @include('layouts.alert')

    <form method="GET" action="{{ route('admin.rujukan.index') }}"
          class="card-panel p-3 flex flex-wrap gap-2">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Cari nama rujukan..."
               class="flex-1 min-w-48 bg-white/5 border border-gray-700 text-gray-700 text-xs rounded-lg px-3 py-2
                      placeholder-gray-600 focus:outline-none focus:border-green-400/50">

        <select name="produsen_id"
                class="bg-white/5 border border-gray-700 text-gray-700 text-xs rounded-lg px-3 py-2
                       focus:outline-none focus:border-green-400/50">
            <option value="">Semua Produsen</option>
            @foreach ($produsen as $p)
                <option value="{{ $p->produsen_id }}" {{ request('produsen_id') == $p->produsen_id ? 'selected' : '' }}>
                    {{ $p->nama_produsen }}
                </option>
            @endforeach
        </select>

        <button type="submit"
                class="btn-primary">
            <i class="fas fa-search"></i> Cari
        </button>
        @if(request('search') || request('produsen_id'))
            <a href="{{ route('admin.rujukan.index') }}"
               class="bg-white/10 hover:bg-white/20 text-gray-400 text-xs px-4 py-2 rounded-lg transition">
                <i class="fas fa-times"></i> Reset
            </a>
        @endif
    </form>

    <div class="card-panel">
        @if ($rujukans->isEmpty())
            <div class="flex flex-col items-center justify-center py-16 text-gray-500">
                <i class="fas fa-file-alt text-4xl mb-3 opacity-30"></i>
                <p class="text-sm font-medium">Belum ada rujukan</p>
                <p class="text-xs mt-1">Klik "Tambah Rujukan" untuk menambahkan data baru</p>
            </div>
        @else
            <table class="w-full text-xs">
                <thead>
                    <tr class="border-b border-gray-300 text-gray-400 bg-white/5">
                        <th class="text-left px-4 py-3 font-semibold">#</th>
                        <th class="text-left px-4 py-3 font-semibold">Gambar</th>
                        <th class="text-left px-4 py-3 font-semibold">Nama Rujukan</th>
                        <th class="text-left px-4 py-3 font-semibold">Produsen</th>
                        <th class="text-left px-4 py-3 font-semibold">Link</th>
                        <th class="text-center px-4 py-3 font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @foreach ($rujukans as $i => $rujukan)
                        <tr class="hover:bg-white/5 transition text-gray-700">
                            <td class="px-4 py-3 text-gray-500">{{ $rujukans->firstItem() + $i }}</td>
                            <td class="px-4 py-3">
                                @if ($rujukan->gambar_rujukan)
                                    <img src="{{ Storage::url($rujukan->gambar_rujukan) }}"
                                         alt="{{ $rujukan->nama_rujukan }}"
                                         class="w-10 h-10 object-cover rounded-md border border-gray-700">
                                @else
                                    <div class="w-10 h-10 rounded-md bg-white/5 border border-gray-700 flex items-center justify-center text-gray-600">
                                        <i class="fas fa-image"></i>
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3 font-medium text-gray-700">{{ $rujukan->nama_rujukan }}</td>
                            <td class="px-4 py-3 text-gray-400">{{ $rujukan->produsen->nama_produsen ?? '-' }}</td>
                            <td class="px-4 py-3">
                                @if ($rujukan->link_rujukan)
                                    <a href="{{ $rujukan->link_rujukan }}" target="_blank"
                                       class="text-blue-400 hover:text-blue-300 transition truncate max-w-32 block">
                                        <i class="fas fa-external-link-alt mr-1"></i>Buka Link
                                    </a>
                                @else
                                    <span class="text-gray-600">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.rujukan.show', $rujukan) }}"
                                       class="p-1.5 rounded-md bg-blue-500/10 text-blue-400 hover:bg-blue-500/20 transition">
                                        <i class="fas fa-eye text-xs"></i>
                                    </a>
                                    <a href="{{ route('admin.rujukan.edit', $rujukan) }}"
                                       class="p-1.5 rounded-md bg-green-500/10 text-green-400 hover:bg-green-500/20 transition">
                                        <i class="fas fa-edit text-xs"></i>
                                    </a>
                                    <form action="{{ route('admin.rujukan.destroy', $rujukan) }}" method="POST"
                                          onsubmit="return confirm('Yakin ingin menghapus rujukan ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="p-1.5 rounded-md bg-red-500/10 text-red-400 hover:bg-red-500/20 transition">
                                            <i class="fas fa-trash text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @if ($rujukans->hasPages())
                <div class="px-4 py-3 border-t border-gray-700">
                    {{ $rujukans->links() }}
                </div>
            @endif
        @endif
    </div>

</div>
@endsection