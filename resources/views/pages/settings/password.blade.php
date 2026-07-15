{{-- resources/views/pages/settings/password.blade.php --}}
@extends('layouts.main')

@section('title', 'Ubah Password')

@section('content')
<div class="max-w-2xl space-y-4">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-xs text-gray-500">
        <a href="{{ route('profile.edit') }}" class="hover:text-sky-600 transition">Profil</a>
        <i class="fas fa-chevron-right text-[10px]"></i>
        <span class="text-gray-300">Ubah Password</span>
    </div>

    <div class="card-panel p-6">
        <h2 class="text-sm font-semibold text-gray-700 mb-5">
            <i class="fas fa-key text-sky-400 mr-2"></i>Ubah Password
        </h2>

        @if(session('success'))
            <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('user-password.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid gap-4 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label class="text-xs font-medium text-gray-400">Password Saat Ini <span class="text-red-400">*</span></label>
                    <div class="relative mt-2">
                        <input type="password" id="current_password" name="current_password"
                               class="w-full rounded-xl border bg-white/90 px-3 py-2 pr-10 text-sm text-slate-900 shadow-sm outline-none transition focus:border-sky-400"
                               placeholder="Password saat ini">
                        <button type="button" data-toggle="current_password"
                                class="password-toggle absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    @error('current_password') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="text-xs font-medium text-gray-400">Password Baru <span class="text-red-400">*</span></label>
                    <div class="relative mt-2">
                        <input type="password" id="password" name="password"
                               class="w-full rounded-xl border bg-white/90 px-3 py-2 pr-10 text-sm text-slate-900 shadow-sm outline-none transition focus:border-sky-400"
                               placeholder="Password baru">
                        <button type="button" data-toggle="password"
                                class="password-toggle absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    @error('password') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="text-xs font-medium text-gray-400">Konfirmasi Password Baru <span class="text-red-400">*</span></label>
                    <div class="relative mt-2">
                        <input type="password" id="password_confirmation" name="password_confirmation"
                               class="w-full rounded-xl border bg-white/90 px-3 py-2 pr-10 text-sm text-slate-900 shadow-sm outline-none transition focus:border-sky-400"
                               placeholder="Ulangi password baru">
                        <button type="button" data-toggle="password_confirmation"
                                class="password-toggle absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="mt-6 grid grid-cols md:grid-cols-2 gap-3 border-t border-white/10 pt-5">
                <button type="submit" class="btn-primary md:col-1">
                    <i class="fas fa-save"></i> Simpan Password
                </button>
                <a href="{{ route('profile.edit') }}"
                   class="md:col-2 inline-flex items-center gap-2 rounded-xl border border-slate-200 px-4 py-2 text-sm text-slate-700 transition hover:bg-slate-50">
                    <i class="fas fa-user"></i> Kembali ke Profil
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.password-toggle').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const input = document.getElementById(btn.dataset.toggle);
            const icon = btn.querySelector('i');

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });
});
</script>
@endsection