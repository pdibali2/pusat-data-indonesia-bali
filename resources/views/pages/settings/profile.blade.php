{{-- resources/views/pages/settings/profile.blade.php --}}
@extends('layouts.main')

@section('title', 'Edit Profil')

@section('content')
<div class="max-w-2xl space-y-4">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-xs text-gray-500">
        <a href="{{ url()->previous() }}" class="hover:text-sky-600 transition">Kembali</a>
        <i class="fas fa-chevron-right text-[10px]"></i>
        <span class="text-gray-300">Edit Profil</span>
    </div>

    <div class="card-panel p-6">
        <h2 class="text-sm font-semibold text-gray-700 mb-5">
            <i class="fas fa-user-edit text-sky-400 mr-2"></i>Edit Profil
        </h2>

        @if(session('success'))
            <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('profile.update') }}" method="POST">
            @csrf
            @method('PATCH')

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="text-xs font-medium text-gray-400">Nama lengkap <span class="text-red-400">*</span></label>
                    <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}"
                           class="mt-2 w-full rounded-xl border bg-white/90 px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition focus:border-sky-400"
                           placeholder="Nama lengkap">
                    @error('name') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="text-xs font-medium text-gray-400">Username <span class="text-red-400">*</span></label>
                    <input type="text" name="username" value="{{ old('username', auth()->user()->username) }}"
                           class="mt-2 w-full rounded-xl border bg-white/90 px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition focus:border-sky-400"
                           placeholder="Username">
                    @error('username') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="text-xs font-medium text-gray-400">Email <span class="text-red-400">*</span></label>
                    <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}"
                           class="mt-2 w-full rounded-xl border bg-white/90 px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition focus:border-sky-400"
                           placeholder="Email">
                    @error('email') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mt-6 grid grid-cols md:grid-cols-2 gap-3 border-t border-white/10 pt-5">
                <button type="submit" class="btn-primary md:col-1">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
                <a href="{{ route('user-password.edit') }}"
                   class="md:col-2 inline-flex items-center gap-2 rounded-xl border border-slate-200 px-4 py-2 text-sm text-slate-700 transition hover:bg-slate-50">
                    <i class="fas fa-key"></i> Ubah Password
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
