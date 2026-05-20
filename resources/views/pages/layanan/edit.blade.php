@extends('layouts.main')

@section('title', 'Edit Layanan')

@section('content')
<div class="page-layout">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-gray-500">
        <a href="{{ route('admin.layanan.index') }}" class="hover:text-blue-600 transition">Layanan</a>
        <i class="fas fa-chevron-right text-xs text-gray-300"></i>
        <a href="{{ route('admin.layanan.show', $layanan) }}" class="hover:text-blue-600 transition">{{ $layanan->nama_layanan }}</a>
        <i class="fas fa-chevron-right text-xs text-gray-300"></i>
        <span class="text-gray-700">Edit</span>
    </div>

    <div class="page-header">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Edit Layanan</h1>
            <p class="text-sm text-gray-500 mt-0.5">Perbarui informasi paket layanan.</p>
        </div>
        {{-- Status Badge --}}
        @if($layanan->status === 'publish')
        <span class="inline-flex items-center gap-1.5 bg-green-50 text-green-700 text-xs font-medium px-3 py-1.5 rounded-full border border-green-200">
            <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span> Publish
        </span>
        @elseif($layanan->status === 'pending')
        <span class="inline-flex items-center gap-1.5 bg-yellow-50 text-yellow-700 text-xs font-medium px-3 py-1.5 rounded-full border border-yellow-200">
            <span class="w-1.5 h-1.5 bg-yellow-500 rounded-full"></span> Pending
        </span>
        @else
        <span class="inline-flex items-center gap-1.5 bg-gray-100 text-gray-600 text-xs font-medium px-3 py-1.5 rounded-full border border-gray-200">
            <span class="w-1.5 h-1.5 bg-gray-400 rounded-full"></span> Takedown
        </span>
        @endif
    </div>

    <form action="{{ route('admin.layanan.update', $layanan) }}" method="POST" enctype="multipart/form-data">
        @csrf @method('PUT')
        @include('pages.layanan.form')

        <div class="flex items-center justify-end gap-3 mt-6 pt-5 border-t border-gray-200">
            <a href="{{ route('admin.layanan.show', $layanan) }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                <i class="fas fa-arrow-left"></i> Batal
            </a>
            <button type="submit"
                    class="btn-primary">
                <i class="fas fa-save mr-1.5 text-xs"></i> Perbarui Layanan
            </button>
        </div>
    </form>

</div>
@endsection