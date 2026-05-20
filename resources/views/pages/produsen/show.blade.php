{{-- resources/views/pages/produsen/show.blade.php --}}
@extends('layouts.main')

@section('title', 'Detail Produsen')

@section('content')
<div class="max-w-2xl space-y-4">

    <div class="flex items-center gap-2 text-xs text-gray-500">
        <a href="{{ route('admin.produsen.index') }}" class="hover:text-green-500 transition">Kelola Produsen</a>
        <i class="fas fa-chevron-right text-[10px]"></i>
        <span class="text-gray-300">Detail</span>
    </div>

    <div class="card-panel p-6">
        <h2 class="text-sm font-semibold text-gray-200 mb-5">
            <i class="fas fa-industry text-green-400 mr-2"></i>Detail Produsen
        </h2>

        <dl class="space-y-3 text-xs">
            @foreach ([
                ['label' => 'Nama Produsen',    'value' => $produsen->nama_produsen],
                ['label' => 'Contact Person',   'value' => $produsen->nama_contact_person],
                ['label' => 'Email',            'value' => $produsen->email],
                ['label' => 'Kontak',           'value' => $produsen->kontak],
                ['label' => 'Alamat',           'value' => $produsen->alamat],
                ['label' => 'Dibuat',           'value' => $produsen->created_at?->format('d M Y H:i')],
                ['label' => 'Diperbarui',       'value' => $produsen->updated_at?->format('d M Y H:i')],
            ] as $item)
                <div class="flex gap-4 py-2.5 border-b border-white/5">
                    <dt class="w-36 text-gray-500 shrink-0">{{ $item['label'] }}</dt>
                    <dd class="text-gray-300">{{ $item['value'] ?? '-' }}</dd>
                </div>
            @endforeach
        </dl>

        <div class="flex gap-3 mt-6">
            <a href="{{ route('admin.produsen.edit', $produsen) }}"
               class="btn-primary">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="{{ route('admin.produsen.index') }}"
               class="flex items-center gap-2 bg-white/10 hover:bg-white/20 text-gray-400 text-xs px-4 py-2 rounded-lg transition">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

</div>
@endsection