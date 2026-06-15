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

@stack('scripts')
</body>
</html>