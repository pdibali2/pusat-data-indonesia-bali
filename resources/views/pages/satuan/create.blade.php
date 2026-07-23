@extends('layouts.main')

@section('title', 'Tambah Satuan')

@section('content')
<div class="max-w-2xl space-y-4">

    <div class="flex items-center gap-2 text-xs text-gray-500">
        <a href="{{ route('admin.satuan.index') }}" class="hover:text-green-500 transition">Kelola Satuan</a>
        <i class="fas fa-chevron-right text-[10px]"></i>
        <span class="text-gray-700">Tambah Satuan</span>
    </div>

    <div class="card-panel p-6">
        <h2 class="text-sm font-semibold text-gray-600 mb-5">
            <i class="fas fa-ruler text-green-400 mr-2"></i>Tambah Satuan Baru
        </h2>

        <form action="{{ route('admin.satuan.store') }}" method="POST">
            @csrf

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nama Satuan</label>
                    <input type="text" name="nama_satuan" value="{{ old('nama_satuan') }}"
                           class="mt-2 w-full rounded-xl border border-gray-200 px-4 py-2 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                    @error('nama_satuan')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Simbol</label>
                    <input type="text" name="simbol" value="{{ old('simbol') }}"
                           class="mt-2 w-full rounded-xl border border-gray-200 px-4 py-2 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                    @error('simbol')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Nilai Konversi</label>
                    <input type="text" name="nilai_konversi" value="{{ old('nilai_konversi') }}"
                           class="mt-2 w-full rounded-xl border border-gray-200 px-4 py-2 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                    <p class="text-xs text-gray-500 mt-1">Nilai relatif terhadap satuan dasar.</p>
                    @error('nilai_konversi')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex gap-3 mt-6 pt-5 border-t border-white/10">
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i> Simpan
                </button>
                <a href="{{ route('admin.satuan.index') }}"
                   class="flex items-center gap-2 bg-white/10 hover:bg-white/20 text-gray-400 text-xs px-5 py-2.5 rounded-lg transition">
                    <i class="fas fa-arrow-left"></i> Batal
                </a>
            </div>
        </form>
    </div>

</div>
@endsection
