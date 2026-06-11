@extends('layouts.main')

@section('title', $layanan->nama_layanan)

@section('content')
<div class="page-layout">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-gray-500">
        <a href="{{ route('admin.layanan.index') }}" class="hover:text-blue-600 transition">Layanan</a>
        <i class="fas fa-chevron-right text-xs text-gray-300"></i>
        <span class="text-gray-700">{{ $layanan->nama_layanan }}</span>
    </div>

    {{-- Flash --}}
    @if(session('success'))
    <div class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Detail Utama --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Header Card --}}
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                {{-- Thumbnail Banner --}}
                @if($layanan->thumbnail)
                <div class="h-48 bg-gray-100 overflow-hidden">
                    <img src="{{ asset('storage/' . $layanan->thumbnail) }}"
                         alt="{{ $layanan->nama_layanan }}"
                         class="w-full h-full object-cover">
                </div>
                @endif

                <div class="px-6 py-5">
                    <div class="flex flex-wrap items-start gap-3">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h1 class="text-xl font-bold text-gray-800">{{ $layanan->nama_layanan }}</h1>
                                @if($layanan->is_popular)
                                <span class="inline-flex items-center gap-1 bg-yellow-50 text-yellow-700 text-xs font-medium px-2.5 py-1 rounded-full border border-yellow-200">
                                    <i class="fas fa-star text-xs"></i> Populer
                                </span>
                                @endif
                            </div>
                            <p class="text-2xl font-bold text-blue-600 mt-2">{{ $layanan->harga_format }}</p>
                            <p class="text-sm text-gray-500">/ {{ $layanan->durasi_label }}</p>
                        </div>
                        <div>
                            @if($layanan->status === 'publish')
                            <span class="inline-flex items-center gap-1.5 bg-green-50 text-green-700 text-xs font-semibold px-3 py-1.5 rounded-full border border-green-200">
                                <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span> Publish
                            </span>
                            @elseif($layanan->status === 'pending')
                            <span class="inline-flex items-center gap-1.5 bg-yellow-50 text-yellow-700 text-xs font-semibold px-3 py-1.5 rounded-full border border-yellow-200">
                                <span class="w-1.5 h-1.5 bg-yellow-500 rounded-full"></span> Pending
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1.5 bg-gray-100 text-gray-600 text-xs font-semibold px-3 py-1.5 rounded-full border border-gray-200">
                                <span class="w-1.5 h-1.5 bg-gray-400 rounded-full"></span> Takedown
                            </span>
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-5 pt-5 border-t border-gray-100 text-center">
                        <div>
                            <p class="text-xs text-gray-400 mb-1">Urutan</p>
                            <p class="text-sm font-semibold text-gray-700">{{ $layanan->urutan }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 mb-1">Fitur</p>
                            <p class="text-sm font-semibold text-gray-700">{{ $layanan->fiturs->count() }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 mb-1">Populer</p>
                            <p class="text-sm font-semibold {{ $layanan->is_popular ? 'text-yellow-500' : 'text-gray-400' }}">
                                {{ $layanan->is_popular ? 'Ya' : 'Tidak' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 mb-1">ID</p>
                            <p class="text-sm font-semibold text-gray-700">{{ $layanan->layanan_id }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Fitur --}}
            <div class="card-panel">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="text-sm font-semibold text-gray-700">Fitur Layanan</h2>
                </div>
                <div class="px-6 py-5">
                    @if($layanan->fiturs->isEmpty())
                    <p class="text-sm text-gray-400 text-center py-4">Belum ada fitur yang ditambahkan.</p>
                    @else
                    <ul class="space-y-2.5">
                        @foreach($layanan->fiturs as $fitur)
                        <li class="flex items-center gap-3 text-sm">
                            <div class="flex-shrink-0 w-5 h-5 bg-blue-50 rounded-full flex items-center justify-center">
                                <i class="fas fa-check text-blue-500 text-xs"></i>
                            </div>
                            <span class="{{ $fitur->aktif ? 'text-gray-700' : 'text-gray-400 line-through' }}">
                                {{ $fitur->nama_fitur }}
                            </span>
                        </li>
                        @endforeach
                    </ul>
                    @endif
                </div>
            </div>

        </div>

        {{-- Sidebar Aksi --}}
        <div class="space-y-4">

            {{-- Aksi --}}
            <div class="card-panel">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="text-sm font-semibold text-gray-700">Aksi</h2>
                </div>
                <div class="px-6 py-5 space-y-2">
                    <a href="{{ route('admin.layanan.edit', $layanan) }}"
                       class="btn-primary w-full flex items-center justify-center gap-2">
                        <i class="fas fa-pen text-xs"></i> Edit Layanan
                    </a>

                    @if($layanan->status !== 'publish')
                    <form action="{{ route('admin.layanan.publish', $layanan) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-2 px-4 py-2.5 bg-green-500 hover:bg-green-600 text-white text-sm font-medium rounded-lg transition">
                            <i class="fas fa-globe text-xs"></i> Publish
                        </button>
                    </form>
                    @endif

                    @if($layanan->status !== 'takedown')
                    <form action="{{ route('admin.layanan.takedown', $layanan) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-2 px-4 py-2.5 bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium rounded-lg transition">
                            <i class="fas fa-eye-slash text-xs"></i> Takedown
                        </button>
                    </form>
                    @endif

                    @if($layanan->status !== 'pending')
                    <form action="{{ route('admin.layanan.draft', $layanan) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-2 px-4 py-2.5 bg-indigo-100 hover:bg-indigo-200 text-indigo-700 text-sm font-medium rounded-lg transition">
                            <i class="fas fa-file-alt text-xs"></i> Jadikan Draft
                        </button>
                    </form>
                    @endif

                    <form action="{{ route('admin.layanan.toggle_popular', $layanan) }}" method="POST">
                        @csrf
                        <button type="submit"
                                class="w-full flex items-center gap-2 px-4 py-2.5 text-sm font-medium rounded-lg transition
                                       {{ $layanan->is_popular
                                            ? 'bg-yellow-50 hover:bg-yellow-100 text-yellow-700 border border-yellow-200'
                                            : 'bg-gray-50 hover:bg-gray-100 text-gray-600 border border-gray-200' }}">
                            <i class="fas fa-star text-xs"></i>
                            {{ $layanan->is_popular ? 'Hapus dari Populer' : 'Tandai Populer' }}
                        </button>
                    </form>

                    <hr class="border-gray-100 my-1">

                    <button type="button"
                            onclick="confirmDelete('{{ route('admin.layanan.destroy', $layanan) }}', '{{ $layanan->nama_layanan }}')"
                            class="w-full flex items-center gap-2 px-4 py-2.5 bg-red-50 hover:bg-red-100 text-red-600 text-sm font-medium rounded-lg transition border border-red-200">
                        <i class="fas fa-trash text-xs"></i> Hapus Layanan
                    </button>
                </div>
            </div>

            {{-- Info --}}
            <div class="card-panel">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="text-sm font-semibold text-gray-700">Informasi</h2>
                </div>
                <div class="px-6 py-5 space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-400">Dibuat</span>
                        <span class="text-gray-600">{{ $layanan->created_at->format('d M Y') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Diperbarui</span>
                        <span class="text-gray-600">{{ $layanan->updated_at->format('d M Y') }}</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- Modal Hapus --}}
<div id="modal-delete" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm mx-4 p-6">
        <div class="flex items-center justify-center w-12 h-12 bg-red-100 rounded-full mx-auto mb-4">
            <i class="fas fa-trash text-red-500"></i>
        </div>
        <h3 class="text-center text-gray-800 font-semibold text-base">Hapus Layanan?</h3>
        <p class="text-center text-gray-500 text-sm mt-1">
            Anda akan menghapus <strong id="modal-nama" class="text-gray-700"></strong>. Tindakan ini tidak dapat dibatalkan.
        </p>
        <form id="modal-form" method="POST" class="mt-5 flex gap-3">
            @csrf @method('DELETE')
            <button type="button" onclick="closeModal()"
                    class="flex-1 py-2 border border-gray-200 text-gray-600 text-sm rounded-lg hover:bg-gray-50 transition">Batal</button>
            <button type="submit"
                    class="flex-1 py-2 bg-red-600 hover:bg-red-700 text-white text-sm rounded-lg transition">Ya, Hapus</button>
        </form>
    </div>
</div>

@push('scripts')
<script>
function confirmDelete(url, nama) {
    document.getElementById('modal-form').action = url;
    document.getElementById('modal-nama').textContent = nama;
    document.getElementById('modal-delete').classList.remove('hidden');
}
function closeModal() {
    document.getElementById('modal-delete').classList.add('hidden');
}
document.getElementById('modal-delete').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>
@endpush
@endsection