<header class="bg-white border-b border-slate-200 px-6 h-13.5 flex items-center justify-between sticky top-0 z-40">

    {{-- Breadcrumb / halaman aktif --}}
    <div class="flex items-center gap-2 text-xs text-slate-400">
        <i class="fas fa-home text-[10px]"></i>
        <span>/</span>
        <span class="text-sky-600 font-semibold capitalize">
            {{ ucfirst(request()->segment(1) ?? 'Dashboard') }}
        </span>
    </div>

    {{-- Kanan: aksi + user --}}
    <div class="flex items-center gap-3">

        {{-- Notifikasi --}}
        {{-- <button class="relative w-8 h-8 rounded-lg bg-sky-50 border border-sky-100 hover:bg-sky-100
                        flex items-center justify-center text-sky-600 text-sm transition-colors">
            <i class="fas fa-bell"></i>
            <span class="absolute top-1 right-1 w-1.5 h-1.5 bg-red-500 rounded-full border border-white"></span>
        </button> --}}

        {{-- Divider --}}
        <div class="w-px h-6 bg-slate-200"></div>

        {{-- User pill --}}
        <div class="flex items-center gap-2.5 pl-1 pr-3 py-1 rounded-xl bg-slate-50
                    border border-slate-200 hover:border-sky-200 transition-colors cursor-default">
            <div class="w-7 h-7 rounded-full bg-linear-to-br from-sky-500 to-sky-700
                         flex items-center justify-center text-white text-[10px] font-bold tracking-wide">
                {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
            </div>
            <div class="leading-tight">
                <p class="text-xs font-semibold text-slate-800">{{ Auth::user()->name }}</p>
                <p class="text-[10px] text-slate-400">{{ Auth::user()->group->title }}</p>
            </div>
        </div>

    </div>
</header>