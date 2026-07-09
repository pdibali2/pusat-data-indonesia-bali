@extends('layouts.main')

@section('title', 'Kelola Organisasi')

@section('content')
<div class="page-layout">
    <div class="page-header">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Kelola Organisasi</h1>
            <p class="text-sm text-gray-500 mt-0.5">Daftar organisasi dan kontrol akses tim.</p>
        </div>
    </div>

    <div class="card-panel">
        <div class="p-4 border-b border-gray-100">
            <form method="GET" class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex-1 min-w-0">
                    <label class="block text-[11px] font-medium text-gray-400 mb-1">Cari Organisasi</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama organisasi atau owner"
                        class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"/>
                </div>
                <div class="flex items-center gap-2">
                    <button type="submit" class="inline-flex items-center rounded-xl bg-sky-600 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-700 transition">Cari</button>
                    <a href="{{ route('admin.organizations.index') }}" class="inline-flex items-center rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 transition">Reset</a>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            @if($organizations->isEmpty())
                <div class="py-16 text-center text-gray-500">Belum ada organisasi yang ditemukan.</div>
            @else
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 text-gray-500 uppercase text-xs tracking-wider">
                        <tr>
                            <th class="px-4 py-3">Nama</th>
                            <th class="px-4 py-3">Owner</th>
                            <th class="px-4 py-3">Member</th>
                            <th class="px-4 py-3">Dibuat</th>
                            <th class="px-4 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($organizations as $organization)
                            <tr>
                                <td class="px-4 py-4 font-semibold text-slate-800">{{ $organization->name }}</td>
                                <td class="px-4 py-4 text-slate-600">{{ $organization->owner?->name ?? '—' }}</td>
                                <td class="px-4 py-4 text-slate-600">{{ $organization->members_count }}</td>
                                <td class="px-4 py-4 text-slate-500">{{ $organization->created_at->format('d M Y') }}</td>
                                <td class="px-4 py-4">
                                    <a href="{{ route('admin.organizations.show', $organization) }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                                        <i class="fas fa-eye text-[10px]"></i> Detail
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <div class="mt-4">
            {{ $organizations->links() }}
        </div>
    </div>
</div>
@endsection
