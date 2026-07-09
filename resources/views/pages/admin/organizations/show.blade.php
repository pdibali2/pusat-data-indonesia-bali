@extends('layouts.main')

@section('title', 'Detail Organisasi')

@section('content')
<div class="page-layout">
    <div class="page-header flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Detail Organisasi</h1>
            <p class="text-sm text-gray-500 mt-0.5">Kontrol lengkap untuk organisasi dan anggota.</p>
        </div>
        <a href="{{ route('admin.organizations.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 transition">
            <i class="fas fa-arrow-left text-xs"></i> Kembali
        </a>
    </div>

    <div class="grid gap-6 xl:grid-cols-[1.4fr_0.6fr]">
        <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-slate-800">{{ $organization->name }}</h2>
                <p class="text-sm text-slate-500">Owner: {{ $organization->owner?->name ?? '—' }} ({{ $organization->owner?->email ?? '—' }})</p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Total Anggota</p>
                    <p class="mt-2 text-2xl font-bold text-slate-800">{{ $organization->members->count() }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Member Aktif</p>
                    <p class="mt-2 text-2xl font-bold text-slate-800">{{ $organization->members->where('status', 'active')->count() }}</p>
                </div>
            </div>

            <div class="mt-6">
                <h3 class="text-sm font-semibold text-slate-700 uppercase tracking-[0.15em]">Anggota</h3>
                <div class="mt-3 space-y-3">
                    @foreach($organization->members as $member)
                        <div class="rounded-2xl border border-gray-200 bg-slate-50 p-4">
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="font-semibold text-slate-800">{{ $member->user?->name ?? '—' }}</p>
                                    <p class="text-sm text-slate-500">{{ $member->user?->email ?? '—' }}</p>
                                </div>
                                <div class="space-x-2">
                                    <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-600 border border-slate-200">{{ ucfirst($member->role) }}</span>
                                    <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-600 border border-slate-200">{{ ucfirst($member->status) }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="text-sm font-semibold text-slate-700 uppercase tracking-[0.15em]">Paket Organisasi</h3>
            <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-sm font-semibold text-slate-800">{{ $organization->subscription?->nama_layanan ?? 'Tidak tersedia' }}</p>
                <p class="text-sm text-slate-500 mt-2">Seat maksimal: {{ $organization->subscription?->max_seats ?? 0 }}</p>
                <p class="text-sm text-slate-500">Sesi bersamaan: {{ $organization->subscription?->max_concurrent_sessions ?? 0 }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
