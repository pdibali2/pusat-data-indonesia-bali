@extends('layouts.main')

@section('title', 'Tambah Layanan')

@section('content')
<div class="page-layout">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-gray-500">
        <a href="{{ route('admin.layanan.index') }}" class="hover:text-blue-600 transition">Layanan</a>
        <i class="fas fa-chevron-right text-xs text-gray-300"></i>
        <span class="text-gray-700">Tambah</span>
    </div>

    <div class="page-header">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Tambah Layanan Baru</h1>
            <p class="text-sm text-gray-500 mt-0.5">Buat paket layanan baru untuk ditampilkan di landing page.</p>
        </div>
    </div>

    <form action="{{ route('admin.layanan.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @include('pages.layanan.form')

        <div class="flex items-center justify-end gap-3 mt-6 pt-5 border-t border-gray-200">
            <a href="{{ route('admin.layanan.index') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                <i class="fas fa-arrow-left"></i> Batal
            </a>
            <button type="submit"
                    class="btn-primary">
                <i class="fas fa-save mr-1.5 text-xs"></i> Simpan Layanan
            </button>
        </div>
    </form>

</div>
@endsection