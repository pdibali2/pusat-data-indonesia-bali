<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @if(app()->environment('testing'))
    @else
        @vite(['resources/js/app.ts', 'resources/css/app.css'])
    @endif

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.5.2/dist/css/tom-select.css" rel="stylesheet">
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.5.2/dist/js/tom-select.complete.min.js"></script>

    <title>Pusat Data Indonesia Bali</title>
</head>
<body class="bg-sky-50/60 text-slate-900 font-poppins">

{{-- ══════════════════════════════════════════
     ROOT ALPINE: manages sidebar open/close
══════════════════════════════════════════ --}}
<div class="flex h-screen overflow-hidden"
     x-data="{ sidebarOpen: false }"
     @keydown.escape.window="sidebarOpen = false">

    @auth
        {{-- ── BACKDROP (mobile only) ──────────────────── --}}
        <div x-show="sidebarOpen"
             x-transition:enter="transition-opacity duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="sidebarOpen = false"
             class="fixed inset-0 z-40 bg-black/50 lg:hidden"
             style="display: none;"></div>

        {{-- ── SIDEBAR ──────────────────────────────────── --}}
        {{-- Desktop: always visible, fixed left
             Mobile: drawer, slides in from left --}}
        <div class="fixed inset-y-0 left-0 z-50 w-56 transform transition-transform duration-300 ease-in-out
                    lg:translate-x-0"
             :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
            @include('layouts.sidebar')
        </div>
    @endauth

    {{-- ── MAIN CONTENT ─────────────────────────────── --}}
    <div class="flex flex-col flex-1 min-w-0 {{ Auth::check() ? 'lg:ml-56' : '' }}">

        {{-- NAVBAR (sticky) --}}
        <div class="sticky top-0 z-40">
            @include('layouts.navbar')
        </div>

        {{-- SCROLLABLE CONTENT --}}
        <main class="overflow-y-auto flex-1 px-3 py-4 lg:px-4 lg:py-5" style="overflow-x: hidden; min-width: 0;">
            <div class="page-view-shell min-h-full max-w-full">
                @yield('content')
            </div>
        </main>
    </div>

</div>

@if (session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: @json(session('success')),
        showConfirmButton: false,
        timer: 1800
    });
</script>
@endif

<script>
    function autoSubmitDebounce(input, delay = 400) {
        clearTimeout(input._timer);
        input._timer = setTimeout(() => input.form.submit(), delay);
    }

    function updateDateTime() {
        const now = new Date();
        const optionsDate = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        const optionsTime = { hour: '2-digit', minute: '2-digit', hour12: false };
        const dateEl = document.getElementById('current-date');
        const timeEl = document.getElementById('current-time');
        if (dateEl) dateEl.textContent = now.toLocaleDateString('id', optionsDate);
        if (timeEl) timeEl.textContent = now.toLocaleTimeString('id', optionsTime) + ' WITA';
    }
    setInterval(updateDateTime, 1000);
    updateDateTime();
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">

@auth
<div x-data="sessionSecurityPopup()" x-init="init()">
    <div x-show="open"
         class="fixed inset-0 z-50 bg-slate-900/70 flex items-end md:items-center justify-center px-4 py-6"
         style="display: none;"
         @keydown.escape.window.prevent>
        <div class="w-full max-w-2xl rounded-t-3xl bg-white shadow-2xl md:rounded-3xl overflow-hidden">
            <div class="bg-slate-950 px-6 py-5">
                <h2 class="text-lg font-semibold text-white" x-text="title"></h2>
                <p class="mt-1 text-sm text-slate-300" x-text="subtitle"></p>
            </div>
            <div class="p-6 space-y-5 text-slate-700">
                <template x-if="type === 'security_verification'">
                    <div class="space-y-3">
                        <div class="text-sm text-slate-500">Ada login baru pada akun Anda.</div>
                        <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                            <div class="text-sm"><span class="font-semibold">Perangkat:</span> <span x-text="payload.device_type"></span></div>
                            <div class="text-sm"><span class="font-semibold">Browser:</span> <span x-text="payload.browser"></span></div>
                            <div class="text-sm"><span class="font-semibold">Lokasi:</span> <span x-text="payload.estimated_location"></span></div>
                            <div class="text-sm"><span class="font-semibold">Waktu:</span> <span x-text="payload.created_at"></span></div>
                        </div>
                    </div>
                </template>
                <template x-if="type === 'pending_login'">
                    <div class="space-y-3">
                        <div class="text-sm text-slate-500">Akun Anda sedang mencoba login pada perangkat lain.</div>
                        <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                            <div class="text-sm"><span class="font-semibold">Perangkat:</span> <span x-text="payload.device_info"></span></div>
                            <div class="text-sm"><span class="font-semibold">Lokasi:</span> <span x-text="payload.estimated_location"></span></div>
                            <div class="text-sm"><span class="font-semibold">Waktu:</span> <span x-text="payload.created_at"></span></div>
                        </div>
                    </div>
                </template>
            </div>
            <div class="grid gap-3 bg-slate-100 p-6 md:grid-cols-2">
                <button type="button"
                        x-on:click="handleReject()"
                        x-bind:disabled="loading"
                        class="rounded-3xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 disabled:opacity-50">
                    <span x-text="rejectLabel"></span>
                </button>
                <button type="button"
                        x-on:click="handleApprove()"
                        x-bind:disabled="loading"
                        class="rounded-3xl bg-slate-950 px-4 py-3 text-sm font-semibold text-white hover:bg-slate-800 disabled:opacity-50">
                    <span x-text="approveLabel"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function sessionSecurityPopup() {
        return {
            open: false,
            type: null,
            payload: {},
            loading: false,
            intervalId: null,
            title: '',
            subtitle: '',
            approveLabel: '',
            rejectLabel: '',

            init() {
                this.poll();
                this.intervalId = setInterval(() => this.poll(), 12000);
            },

            async poll() {
                if (this.open) {
                    return;
                }

                try {
                    const response = await fetch('/session/security-notifications', {
                        headers: { 'Accept': 'application/json' },
                        credentials: 'same-origin',
                    });

                    if (! response.ok) {
                        return;
                    }

                    const data = await response.json();

                    if (data.pending) {
                        this.openModal(data);
                    }
                } catch (error) {
                    console.error(error);
                }
            },

            openModal(data) {
                this.type = data.type;
                this.payload = data;
                this.open = true;
                this.loading = false;
                if (data.type === 'security_verification') {
                    this.title = 'Terdeteksi Login Baru';
                    this.subtitle = 'Silakan konfirmasi apakah login ini berasal dari Anda.';
                    this.approveLabel = 'Ya, Ini Saya';
                    this.rejectLabel = 'Bukan Saya';
                } else {
                    this.title = 'Permintaan Login Baru';
                    this.subtitle = 'Silakan konfirmasi apakah perangkat baru ini milik Anda.';
                    this.approveLabel = 'Ya, Itu Saya';
                    this.rejectLabel = 'Bukan Saya';
                }
            },

            async handleApprove() {
                if (! this.payload.id) {
                    return;
                }

                this.loading = true;
                const url = this.type === 'pending_login'
                    ? `/session/pending-login/${this.payload.id}/approve`
                    : `/session/verification/${this.payload.id}/approve`;

                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin',
                });

                const data = await response.json();

                this.loading = false;
                if (data.redirect) {
                    window.location.href = data.redirect;
                    return;
                }

                if (data.status === 'approved') {
                    this.open = false;
                }
            },

            async handleReject() {
                if (! this.payload.id) {
                    return;
                }

                this.loading = true;
                const url = this.type === 'pending_login'
                    ? `/session/pending-login/${this.payload.id}/reject`
                    : `/session/verification/${this.payload.id}/reject`;

                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin',
                });

                const data = await response.json();
                this.loading = false;

                if (data.redirect) {
                    window.location.href = data.redirect;
                    return;
                }

                this.open = false;
            },
        };
    }
</script>
@endauth

@stack('scripts')
</body>
</html>