{{-- resources/views/pages/rujukan/show.blade.php --}}
@extends('layouts.main')

@section('title', 'Detail Rujukan')

@section('content')
<div class="max-w-2xl space-y-4">

    <div class="flex items-center gap-2 text-xs text-gray-500">
        <a href="{{ route('admin.rujukan.index') }}" class="hover:text-green-500 transition">Kelola Rujukan</a>
        <i class="fas fa-chevron-right text-[10px]"></i>
        <span class="text-gray-300">Detail</span>
    </div>

    <div class="card-panel p-6">
        <h2 class="text-sm font-semibold text-gray-200 mb-5">
            <i class="fas fa-file-alt text-green-400 mr-2"></i>Detail Rujukan
        </h2>

        @if ($rujukan->gambar_rujukan)
            <div class="mb-5">
                <img src="{{ Storage::url($rujukan->gambar_rujukan) }}"
                     alt="{{ $rujukan->nama_rujukan }}"
                     class="w-40 h-40 object-cover rounded-lg border border-white/10">
            </div>
        @endif

        <dl class="space-y-3 text-xs">
            @foreach ([
                ['label' => 'Nama Rujukan', 'value' => $rujukan->nama_rujukan],
                ['label' => 'Produsen',     'value' => $rujukan->produsen->nama_produsen ?? '-'],
                ['label' => 'Dibuat',       'value' => $rujukan->created_at?->format('d M Y H:i')],
                ['label' => 'Diperbarui',   'value' => $rujukan->updated_at?->format('d M Y H:i')],
            ] as $item)
                <div class="flex gap-4 py-2.5 border-b border-white/5">
                    <dt class="w-32 text-gray-500 shrink-0">{{ $item['label'] }}</dt>
                    <dd class="text-gray-300">{{ $item['value'] ?? '-' }}</dd>
                </div>
            @endforeach

            @if ($rujukan->link_rujukan)
                <div class="flex gap-4 py-2.5 border-b border-white/5">
                    <dt class="w-32 text-gray-500 shrink-0">Link</dt>
                    <dd>
                        <a href="{{ $rujukan->link_rujukan }}" target="_blank"
                           class="text-blue-400 hover:text-blue-300 transition">
                            <i class="fas fa-external-link-alt mr-1"></i>{{ $rujukan->link_rujukan }}
                        </a>
                    </dd>
                </div>
            @endif
        </dl>

        <div class="flex gap-3 mt-6">
            <a href="{{ route('admin.rujukan.edit', $rujukan) }}"
               class="btn-primary">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="{{ route('admin.rujukan.index') }}"
               class="flex items-center gap-2 bg-white/10 hover:bg-white/20 text-gray-400 text-xs px-4 py-2 rounded-lg transition">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

</div>
@endsection