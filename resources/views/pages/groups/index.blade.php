{{-- resources/views/pages/groups/index.blade.php --}}
@extends('layouts.main')

@section('title', 'Kelola Group')

@section('content')
<div class="page-layout">

    <div class="page-header">
        <div>
            <h2 class="text-lg font-semibold text-gray-200">Kelola Group</h2>
            <p class="text-xs text-gray-500 mt-0.5">Manajemen group / role pengguna</p>
        </div>
        <a href="{{ route('admin.groups.create') }}"
           class="btn-primary">
            <i class="fas fa-plus"></i> Tambah Group
        </a>
    </div>

    @include('layouts.alert')

    <form method="GET" action="{{ route('admin.groups.index') }}"
          class="card-panel p-3 flex gap-2">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Cari nama group..."
               class="flex-1 bg-white/5 border border-white/10 text-gray-300 text-xs rounded-lg px-3 py-2
                      placeholder-gray-600 focus:outline-none focus:border-green-400/50">
        <button type="submit"
                class="btn-primary">
            <i class="fas fa-search"></i> Cari
        </button>
        @if(request('search'))
            <a href="{{ route('admin.groups.index') }}"
               class="bg-white/10 hover:bg-white/20 text-gray-400 text-xs px-4 py-2 rounded-lg transition">
                <i class="fas fa-times"></i> Reset
            </a>
        @endif
    </form>

    <div class="card-panel">
        @if ($groups->isEmpty())
            <div class="flex flex-col items-center justify-center py-16 text-gray-500">
                <i class="fas fa-users text-4xl mb-3 opacity-30"></i>
                <p class="text-sm font-medium">Belum ada group</p>
                <p class="text-xs mt-1">Klik "Tambah Group" untuk menambahkan group baru</p>
            </div>
        @else
            <table class="w-full text-xs">
                <thead>
                    <tr class="border-b border-white/10 text-gray-400 bg-white/5">
                        <th class="text-left px-4 py-3 font-semibold">#</th>
                        <th class="text-left px-4 py-3 font-semibold">Nama Group</th>
                        <th class="text-left px-4 py-3 font-semibold">Jumlah User</th>
                        <th class="text-center px-4 py-3 font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @foreach ($groups as $i => $group)
                        <tr class="hover:bg-white/5 transition text-gray-300">
                            <td class="px-4 py-3 text-gray-500">{{ $groups->firstItem() + $i }}</td>
                            <td class="px-4 py-3 font-medium text-gray-200">{{ $group->title }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded-full bg-blue-500/10 text-blue-400 border border-blue-500/20">
                                    {{ $group->user_count }} user
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.groups.show', $group) }}"
                                       class="p-1.5 rounded-md bg-blue-500/10 text-blue-400 hover:bg-blue-500/20 transition">
                                        <i class="fas fa-eye text-xs"></i>
                                    </a>
                                    <a href="{{ route('admin.groups.edit', $group) }}"
                                       class="p-1.5 rounded-md bg-green-500/10 text-green-400 hover:bg-green-500/20 transition">
                                        <i class="fas fa-edit text-xs"></i>
                                    </a>
                                    <form action="{{ route('admin.groups.destroy', $group) }}" method="POST"
                                          onsubmit="return confirm('Yakin ingin menghapus group {{ addslashes($group->title) }}?')">
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
            @if ($groups->hasPages())
                <div class="px-4 py-3 border-t border-white/10">
                    {{ $groups->links() }}
                </div>
            @endif
        @endif
    </div>

</div>
@endsection