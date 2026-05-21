{{-- resources/views/pages/rujukan/create.blade.php --}}
@extends('layouts.main')

@section('title', 'Tambah Rujukan')

@section('content')
<div class="max-w-2xl space-y-4">

    <div class="flex items-center gap-2 text-xs text-gray-500">
        <a href="{{ route('admin.rujukan.index') }}" class="hover:text-green-500 transition">Kelola Rujukan</a>
        <i class="fas fa-chevron-right text-[10px]"></i>
        <span class="text-gray-300">Tambah Rujukan</span>
    </div>

    <div class="card-panel p-6">
        <h2 class="text-sm font-semibold text-gray-600 mb-5">
            <i class="fas fa-file-alt text-green-400 mr-2"></i>Tambah Rujukan Baru
        </h2>

        <form action="{{ route('admin.rujukan.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @include('pages.rujukan.form')
            <div class="flex gap-3 mt-6 pt-5 border-t border-white/10">
                <button type="submit"
                        class="btn-primary">
                    <i class="fas fa-save"></i> Simpan
                </button>
                <a href="{{ route('admin.rujukan.index') }}"
                   class="flex items-center gap-2 bg-white/10 hover:bg-white/20 text-gray-400 text-xs px-5 py-2.5 rounded-lg transition">
                    <i class="fas fa-arrow-left"></i> Batal
                </a>
            </div>
        </form>
    </div>

</div>
@endsection