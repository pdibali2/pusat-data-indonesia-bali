{{-- resources/views/pages/groups/edit.blade.php --}}
@extends('layouts.main')

@section('title', 'Edit Group')

@section('content')
<div class="max-w-lg space-y-4">

    <div class="flex items-center gap-2 text-xs text-gray-500">
        <a href="{{ route('admin.groups.index') }}" class="hover:text-green-500 transition">Kelola Group</a>
        <i class="fas fa-chevron-right text-[10px]"></i>
        <span class="text-gray-300">Edit: {{ $group->title }}</span>
    </div>

    <div class="card-panel p-6">
        <h2 class="text-sm font-semibold text-gray-700 mb-5">
            <i class="fas fa-users text-green-400 mr-2"></i>Edit Group
        </h2>

        <form action="{{ route('admin.groups.update', $group) }}" method="POST">
            @csrf
            @method('PUT')
            @include('pages.groups.form')
            <div class="flex gap-3 mt-6 pt-5 border-t border-white/10">
                <button type="submit"
                        class="btn-primary">
                    <i class="fas fa-save"></i> Perbarui
                </button>
                <a href="{{ route('admin.groups.index') }}"
                   class="flex items-center gap-2 bg-white/10 hover:bg-white/20 text-gray-400 text-xs px-5 py-2.5 rounded-lg transition">
                    <i class="fas fa-arrow-left"></i> Batal
                </a>
            </div>
        </form>
    </div>

</div>
@endsection