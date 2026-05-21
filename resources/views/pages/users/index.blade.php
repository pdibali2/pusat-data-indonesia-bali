{{-- resources/views/pages/users/index.blade.php --}}
@extends('layouts.main')

@section('title', 'Kelola User')

@section('content')
<div class="page-layout">

    {{-- Header --}}
    <div class="page-header">
        <div>
            <h2 class="text-lg font-semibold text-gray-600">Kelola User</h2>
            <p class="text-xs text-gray-500 mt-0.5">Manajemen akun pengguna sistem</p>
        </div>
        <a href="{{ route('admin.users.create') }}"
           class="btn-primary">
            <i class="fas fa-plus"></i> Tambah User
        </a>
    </div>

    {{-- Flash --}}
    @include('layouts.alert')

    {{-- Filter & Search --}}
    <form method="GET" action="{{ route('admin.users.index') }}"
          class="card-panel p-3 flex flex-wrap gap-2">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Cari nama, email, username..."
               class="flex-1 min-w-48 bg-white/5 border border-white/10 text-gray-300 text-xs rounded-lg px-3 py-2 placeholder-gray-600 focus:outline-none focus:border-green-400/50">

        <select name="group_id"
                class="bg-white/5 border border-white/10 text-gray-300 text-xs rounded-lg px-3 py-2 focus:outline-none focus:border-green-400/50">
            <option value="">Semua Group</option>
            @foreach ($groups as $group)
                <option value="{{ $group->group_id }}" {{ request('group_id') == $group->group_id ? 'selected' : '' }}>
                    {{ $group->title }}
                </option>
            @endforeach
        </select>

        <button type="submit"
                class="btn-primary">
            <i class="fas fa-search"></i> Cari
        </button>

        @if(request('search') || request('group_id'))
            <a href="{{ route('admin.users.index') }}"
               class="bg-white/10 hover:bg-white/20 text-gray-400 text-xs px-4 py-2 rounded-lg transition">
                <i class="fas fa-times"></i> Reset
            </a>
        @endif
    </form>

    {{-- Table --}}
    <div class="card-panel">
        @if ($users->isEmpty())
            <div class="flex flex-col items-center justify-center py-16 text-gray-500">
                <i class="fas fa-users text-4xl mb-3 opacity-30"></i>
                <p class="text-sm font-medium">Belum ada user</p>
                <p class="text-xs mt-1">Klik "Tambah User" untuk menambahkan user baru</p>
            </div>
        @else
            <table class="w-full text-xs">
                <thead>
                    <tr class="border-b border-white/10 text-gray-400 bg-white/5">
                        <th class="text-left px-4 py-3 font-semibold">#</th>
                        <th class="text-left px-4 py-3 font-semibold">Nama</th>
                        <th class="text-left px-4 py-3 font-semibold">Username</th>
                        <th class="text-left px-4 py-3 font-semibold">Email</th>
                        <th class="text-left px-4 py-3 font-semibold">Group</th>
                        <th class="text-left px-4 py-3 font-semibold">Terdaftar</th>
                        <th class="text-center px-4 py-3 font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @foreach ($users as $i => $user)
                        <tr class="hover:bg-white/5 transition text-gray-300">
                            <td class="px-4 py-3 text-gray-500">{{ $users->firstItem() + $i }}</td>
                            <td class="px-4 py-3 font-medium text-gray-600">{{ $user->name }}</td>
                            <td class="px-4 py-3 text-gray-400">{{ $user->username }}</td>
                            <td class="px-4 py-3 text-gray-400">{{ $user->email }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded-full text-xs bg-green-500/10 text-green-400 border border-green-500/20">
                                    {{ $user->group->title ?? '-' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-500">
                                {{ $user->registerdate ? $user->registerdate->format('d M Y') : '-' }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.users.show', $user) }}"
                                       class="p-1.5 rounded-md bg-blue-500/10 text-blue-400 hover:bg-blue-500/20 transition"
                                       title="Detail">
                                        <i class="fas fa-eye text-xs"></i>
                                    </a>
                                    <a href="{{ route('admin.users.edit', $user) }}"
                                       class="p-1.5 rounded-md bg-green-500/10 text-green-400 hover:bg-green-500/20 transition"
                                       title="Edit">
                                        <i class="fas fa-edit text-xs"></i>
                                    </a>
                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                                          onsubmit="return confirm('Yakin ingin menghapus user {{ addslashes($user->name) }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="p-1.5 rounded-md bg-red-500/10 text-red-400 hover:bg-red-500/20 transition"
                                                title="Hapus">
                                            <i class="fas fa-trash text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Pagination --}}
            @if ($users->hasPages())
                <div class="px-4 py-3 border-t border-white/10">
                    {{ $users->links() }}
                </div>
            @endif
        @endif
    </div>

</div>
@endsection