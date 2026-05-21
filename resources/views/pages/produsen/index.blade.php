{{-- resources/views/pages/produsen/index.blade.php --}}
@extends('layouts.main')

@section('title', 'Kelola Produsen')

@section('content')
<div class="page-layout">

    <div class="page-header">
        <div>
            <h2 class="text-lg font-semibold text-gray-600">Kelola Produsen</h2>
            <p class="text-xs text-gray-500 mt-0.5">Manajemen data produsen / publisher data</p>
        </div>
        <a href="{{ route('admin.produsen.create') }}"
           class="btn-primary">
            <i class="fas fa-plus"></i> Tambah Produsen
        </a>
    </div>

    @include('layouts.alert')

    <form method="GET" action="{{ route('admin.produsen.index') }}"
          class="card-panel p-3 flex gap-2">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Cari nama, email, contact person..."
               class="flex-1 bg-white/5 border border-gray-600 text-gray-700 text-xs rounded-lg px-3 py-2
                      placeholder-gray-600 focus:outline-none focus:border-green-400/50">
        <button type="submit"
                class="btn-primary">
            <i class="fas fa-search"></i> Cari
        </button>
        @if(request('search'))
            <a href="{{ route('admin.produsen.index') }}"
               class="bg-white/10 hover:bg-white/20 text-gray-400 text-xs px-4 py-2 rounded-lg transition">
                <i class="fas fa-times"></i> Reset
            </a>
        @endif
    </form>

    <div class="card-panel">
        @if ($produsen->isEmpty())
            <div class="flex flex-col items-center justify-center py-16 text-gray-500">
                <i class="fas fa-industry text-4xl mb-3 opacity-30"></i>
                <p class="text-sm font-medium">Belum ada produsen</p>
                <p class="text-xs mt-1">Klik "Tambah Produsen" untuk menambahkan data baru</p>
            </div>
        @else
            <table class="w-full text-xs">
                <thead>
                    <tr class="border-b border-gray-300 text-gray-400 bg-white/5">
                        <th class="text-left px-4 py-3 font-semibold">#</th>
                        <th class="text-left px-4 py-3 font-semibold">Nama Produsen</th>
                        <th class="text-left px-4 py-3 font-semibold">Contact Person</th>
                        <th class="text-left px-4 py-3 font-semibold">Kontak</th>
                        <th class="text-left px-4 py-3 font-semibold">Email</th>
                        <th class="text-center px-4 py-3 font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @foreach ($produsen as $i => $p)
                        <tr class="hover:bg-white/5 transition text-gray-300">
                            <td class="px-4 py-3 text-gray-500">{{ $produsen->firstItem() + $i }}</td>
                            <td class="px-4 py-3 font-medium text-gray-600">{{ $p->nama_produsen }}</td>
                            <td class="px-4 py-3 text-gray-400">{{ $p->nama_contact_person ?? '-' }}</td>
                            <td class="px-4 py-3 text-gray-400">{{ $p->kontak ?? '-' }}</td>
                            <td class="px-4 py-3 text-gray-400">{{ $p->email ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.produsen.show', $p) }}"
                                       class="p-1.5 rounded-md bg-blue-500/10 text-blue-400 hover:bg-blue-500/20 transition">
                                        <i class="fas fa-eye text-xs"></i>
                                    </a>
                                    <a href="{{ route('admin.produsen.edit', $p) }}"
                                       class="p-1.5 rounded-md bg-green-500/10 text-green-400 hover:bg-green-500/20 transition">
                                        <i class="fas fa-edit text-xs"></i>
                                    </a>
                                    <form action="{{ route('admin.produsen.destroy', $p) }}" method="POST"
                                          onsubmit="return confirm('Yakin ingin menghapus produsen {{ addslashes($p->nama_produsen) }}?')">
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
            @if ($produsen->hasPages())
                <div class="px-4 py-3 border-t border-white/10">
                    {{ $produsen->links() }}
                </div>
            @endif
        @endif
    </div>

</div>
@endsection