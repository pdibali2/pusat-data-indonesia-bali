@extends('layouts.main')

@section('title', 'Kelola Tim')

@section('content')
<div class="max-w-6xl mx-auto py-4" x-data="{
    open: false,
    email: '',
    submitting: false,
    submit() {
        this.submitting = true;
        this.$el.querySelector('form').submit();
    }
}">
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-800">Kelola Tim</h1>
            <p class="mt-1 text-sm text-slate-500">Pantau anggota, seat yang tersedia, dan undang tim baru ke organisasi Anda.</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Seat terpakai</p>
            <p class="mt-1 text-lg font-bold text-slate-800">{{ $organization->activeMembers->count() }} dari {{ $organization->subscription?->max_seats ?? 1 }} seat</p>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600">
            <ul class="list-disc space-y-1 pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-5 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-slate-800">Daftar Anggota</h2>
                    <p class="text-sm text-slate-500">Owner dan member yang sedang aktif dalam organisasi.</p>
                </div>
                <button type="button" @click="open = true" class="inline-flex items-center rounded-2xl bg-sky-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-sky-700">
                    Invite Anggota
                </button>
            </div>

            <div class="space-y-3">
                @forelse($organization->members->sortByDesc('role') as $member)
                    <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-3">
                        <div>
                            <p class="font-semibold text-slate-800">{{ $member->user->name ?? 'Anggota' }}</p>
                            <p class="text-sm text-slate-500">{{ $member->user->email ?? '-' }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">
                                {{ $member->role === 'owner' ? 'Owner' : ucfirst($member->status) }}
                            </span>
                            @if($member->role !== 'owner')
                                <form action="{{ route('organizations.remove-member', ['organization' => $organization->organization_id, 'member' => $member->organization_member_id]) }}" method="POST" class="inline-block" onsubmit="return confirm('Hapus anggota ini dari organisasi?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-xl border border-red-200 px-3 py-2 text-sm font-semibold text-red-600 hover:bg-red-50">
                                        Remove
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50/70 px-5 py-8 text-center text-sm text-slate-500">
                        Belum ada anggota yang ditambahkan.
                    </div>
                @endforelse
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-slate-50/70 p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-800">Detail Paket</h2>
            <p class="mt-2 text-sm text-slate-500">Informasi paket organisasi yang sedang aktif.</p>
            <div class="mt-5 rounded-2xl border border-slate-200 bg-white p-4">
                <p class="text-sm font-semibold text-slate-700">{{ $organization->subscription?->nama_layanan ?? 'Paket organisasi belum dipilih' }}</p>
                <p class="mt-2 text-sm text-slate-500">Seat maksimal: {{ $organization->subscription?->max_seats ?? 1 }}</p>
                <p class="mt-1 text-sm text-slate-500">Sesi bersamaan: {{ $organization->subscription?->max_concurrent_sessions ?? 1 }}</p>
            </div>
        </div>
    </div>

    <div x-show="open" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4" style="display: none;">
        <div class="w-full max-w-md rounded-3xl border border-slate-200 bg-white p-6 shadow-xl">
            <div class="mb-5 flex items-start justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-slate-800">Invite Anggota</h3>
                    <p class="mt-1 text-sm text-slate-500">Masukkan email anggota yang ingin Anda undang ke organisasi.</p>
                </div>
                <button type="button" @click="open = false" class="rounded-full p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ route('organizations.invite-member', $organization->organization_id) }}" method="POST" class="space-y-4" @submit.prevent="submit">
                @csrf
                <div>
                    <label for="invite-email" class="mb-2 block text-sm font-semibold text-slate-700">Alamat Email</label>
                    <input id="invite-email" name="email" type="email" x-model="email" required class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-sky-400 focus:bg-white" placeholder="contoh@email.com">
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" @click="open = false" class="rounded-2xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50">Batal</button>
                    <button type="submit" :disabled="submitting" class="rounded-2xl bg-sky-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-sky-700 disabled:opacity-70">
                        <span x-show="!submitting">Kirim Undangan</span>
                        <span x-show="submitting">Mengirim...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
