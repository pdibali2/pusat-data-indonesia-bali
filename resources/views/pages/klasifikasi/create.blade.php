@extends('layouts.main')

@section('title', 'Tambah Klasifikasi')

@section('content')
<div class="page-layout max-w-lg mx-auto">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-5">
        <a href="{{ route('admin.klasifikasi.index') }}"
           class="hover:text-blue-600 transition">
            Klasifikasi
        </a>

        <i class="fas fa-chevron-right text-xs text-gray-300"></i>

        <span class="text-gray-700">
            Tambah
        </span>
    </div>

    <div class="card-panel">

        <div class="px-6 py-4 border-b border-gray-100">
            <h1 class="text-base font-bold text-gray-800">
                Tambah Klasifikasi Baru
            </h1>
        </div>

        <form action="{{ route('admin.klasifikasi.store') }}"
              method="POST"
              class="px-6 py-5 space-y-5">

            @csrf

            @include('pages.klasifikasi.form')

            <div class="flex items-center justify-end gap-3 pt-2">

                <a href="{{ route('admin.klasifikasi.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                    <i class="fas fa-arrow-left"></i> Batal
                </a>

                <button type="submit"
                        class="btn-primary">
                    <i class="fas fa-save"></i> Simpan
                </button>

            </div>
        </form>
    </div>

</div>
@endsection