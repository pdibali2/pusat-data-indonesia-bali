@extends('layouts.main')

@section('title', 'Detail Klasifikasi')

@section('content')
<div class="p-6 max-w-lg space-y-5">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-gray-500">
        <a href="{{ route('admin.klasifikasi.index') }}" class="hover:text-blue-600 transition">Klasifikasi</a>
        <i class="fas fa-chevron-right text-xs text-gray-300"></i>
        <span class="text-gray-700">Detail</span>
    </div>

    {{-- Flash --}}
    @if(session('success'))
    <div class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif

    <div class="card-panel">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h1 class="text-base font-bold text-gray-800">{{ $klasifikasi->nama_klasifikasi }}</h1>
                <p class="text-xs text-gray-400 mt-0.5">Dibuat {{ $klasifikasi->created_at->format('d M Y, H:i') }}</p>
            </div>
            <span class="inline-flex items-center gap-1 bg-blue-50 text-blue-700 text-xs font-medium px-3 py-1 rounded-full">
                <i class="fas fa-tags text-xs"></i> Klasifikasi
            </span>
        </div>

        <div class="px-6 py-5 space-y-3 text-sm">
            <div class="flex justify-between">
                <span class="text-gray-500">ID</span>
                <span class="font-medium text-gray-700">{{ $klasifikasi->klasifikasi_id }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Nama Klasifikasi</span>
                <span class="font-medium text-gray-700">{{ $klasifikasi->nama_klasifikasi }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Terakhir diperbarui</span>
                <span class="text-gray-600">{{ $klasifikasi->updated_at->format('d M Y, H:i') }}</span>
            </div>
        </div>

        <div class="px-6 py-4 border-t border-gray-100 flex gap-3">
            <a href="{{ route('admin.klasifikasi.edit', $klasifikasi) }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white text-sm font-medium rounded-lg transition">
                <i class="fas fa-pen text-xs"></i> Edit
            </a>
            <a href="{{ route('admin.klasifikasi.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2 border border-gray-200 text-gray-600 text-sm rounded-lg hover:bg-gray-50 transition">
                <i class="fas fa-arrow-left text-xs"></i> Kembali
            </a>
        </div>
    </div>

</div>
@endsection