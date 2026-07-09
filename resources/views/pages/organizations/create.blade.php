@extends('layouts.main')

@section('title', 'Buat Organisasi')

@section('content')
<div class="max-w-4xl mx-auto py-4">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-800">Buat Organisasi</h1>
            <p class="text-sm text-slate-500 mt-1">Siapkan tim Anda dan mulai gunakan paket organisasi.</p>
        </div>
        <a href="{{ route('langganan') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Kembali
        </a>
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <form action="{{ route('organizations.store') }}" method="POST" class="space-y-6">
            @csrf
            @if(isset($selectedPlan) && $selectedPlan)
                <input type="hidden" name="layanan_id" value="{{ $selectedPlan->layanan_id }}">
            @endif

            <div>
                <label for="name" class="mb-2 block text-sm font-semibold text-slate-700">Nama Organisasi</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required
                    class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none ring-0 transition focus:border-sky-400 focus:bg-white"
                    placeholder="Nama organisasi Anda">
                @error('name')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="rounded-2xl border border-sky-100 bg-sky-50/70 p-4 text-sm text-slate-600">
                <p class="font-semibold text-sky-700">Paket yang dipilih</p>
                @if(isset($selectedPlan) && $selectedPlan)
                    <p class="mt-2 font-semibold text-slate-800">{{ $selectedPlan->nama_layanan }} • {{ $selectedPlan->harga_format }}</p>
                    <p class="mt-1">Seat maksimal: {{ $selectedPlan->max_seats ?? 1 }} • Sesi bersamaan: {{ $selectedPlan->max_concurrent_sessions ?? 1 }}</p>
                @else
                    <p class="mt-2">Silakan kembali ke halaman langganan dan pilih paket organization terlebih dahulu.</p>
                @endif
            </div>

            <div class="flex justify-end">
                <button type="submit" class="inline-flex items-center rounded-2xl bg-sky-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-sky-700">
                    Lanjut Checkout
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
