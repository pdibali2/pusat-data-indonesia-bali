{{-- resources/views/pages/groups/show.blade.php --}}
@extends('layouts.main')

@section('title', 'Detail Group')

@section('content')
<div class="max-w-lg space-y-4">

    <div class="flex items-center gap-2 text-xs text-gray-500">
        <a href="{{ route('admin.groups.index') }}" class="hover:text-green-500 transition">Kelola Group</a>
        <i class="fas fa-chevron-right text-[10px]"></i>
        <span class="text-gray-300">Detail</span>
    </div>

    <div class="card-panel p-6">
        <h2 class="text-sm font-semibold text-gray-200 mb-5">
            <i class="fas fa-users text-green-400 mr-2"></i>Detail Group
        </h2>

        <dl class="space-y-3 text-xs">
            <div class="flex gap-4 py-2.5 border-b border-white/5">
                <dt class="w-32 text-gray-500 shrink-0">ID</dt>
                <dd class="text-gray-300">{{ $group->group_id }}</dd>
            </div>
            <div class="flex gap-4 py-2.5 border-b border-white/5">
                <dt class="w-32 text-gray-500 shrink-0">Nama Group</dt>
                <dd class="text-gray-200 font-medium">{{ $group->title }}</dd>
            </div>
            <div class="flex gap-4 py-2.5 border-b border-white/5">
                <dt class="w-32 text-gray-500 shrink-0">Jumlah User</dt>
                <dd>
                    <span class="px-2 py-0.5 rounded-full bg-blue-500/10 text-blue-400 border border-blue-500/20">
                        {{ $group->user_count }} user
                    </span>
                </dd>
            </div>
        </dl>

        <div class="flex gap-3 mt-6">
            <a href="{{ route('admin.groups.edit', $group) }}"
               class="btn-primary">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="{{ route('admin.groups.index') }}"
               class="flex items-center gap-2 bg-white/10 hover:bg-white/20 text-gray-400 text-xs px-4 py-2 rounded-lg transition">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

</div>
@endsection