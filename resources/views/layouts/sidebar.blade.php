@php
    $isCustomer = auth()->user()?->group_id === 3;

    $menus = [
        (object)[
            "title"        => "Data",
            "path"         => "/data",
            "icon"         => "fas fa-database",
            "active"       => request()->segment(1) === 'data',
            "children"     => [],
            "onlyCustomer" => false,
        ],
        (object)[
            "title"        => "Transaksi Saya",
            "path"         => "/transaksi/riwayat",
            "icon"         => "fas fa-file-invoice-dollar",
            "active"       => request()->is('transaksi/*'),
            "children"     => [],
            "onlyCustomer" => false,
        ],
        (object)[
            "title"        => "Metadata",
            "path"         => "/metadata",
            "icon"         => "fas fa-folder-open",
            "active"       => request()->is('metadata*'),
            "onlyCustomer" => true,
            "children" => [
                (object)["title" => "All Metadata", "path" => "/metadata",          "icon" => "fa-solid fa-floppy-disk",      "active" => request()->is('metadata')],
                (object)["title" => "Approval",     "path" => "/metadata/approval", "icon" => "fa-solid fa-clipboard-check", "active" => request()->is('metadata/approval')],
            ],
        ],
        (object)[
            "title"        => "Dimensi",
            "path"         => "/dimensi",
            "icon"         => "fas fa-cubes",
            "active"       => request()->is('dimensi_*'),
            "onlyCustomer" => true,
            "children" => [
                (object)["title" => "Dimensi Waktu",  "path" => "/dimensi_waktu",  "icon" => "fa-solid fa-clock",        "active" => request()->segment(1) === 'dimensi_waktu'],
                (object)["title" => "Dimensi Lokasi", "path" => "/dimensi_lokasi", "icon" => "fa-solid fa-location-dot", "active" => request()->segment(1) === 'dimensi_lokasi'],
            ],
        ],
        (object)[
            "title"        => "Kelola Master Data",
            "path"         => "#",
            "icon"         => "fas fa-users-cog",
            "onlyCustomer" => true,
            "active"       => request()->segment(1) === 'admin' && in_array(request()->segment(2), ['users','groups','klasifikasi','produsen','rujukan']),
            "children" => [
                (object)["title" => "Kelola User",        "path" => "/admin/users",       "icon" => "fas fa-user",     "active" => request()->segment(2) === 'users'],
                (object)["title" => "Kelola Group",       "path" => "/admin/groups",      "icon" => "fas fa-users",    "active" => request()->segment(2) === 'groups'],
                (object)["title" => "Kelola Klasifikasi", "path" => "/admin/klasifikasi", "icon" => "fas fa-tags",     "active" => request()->segment(2) === 'klasifikasi'],
                (object)["title" => "Kelola Produsen",    "path" => "/admin/produsen",    "icon" => "fas fa-industry", "active" => request()->segment(2) === 'produsen'],
                (object)["title" => "Kelola Rujukan",     "path" => "/admin/rujukan",     "icon" => "fas fa-file-alt", "active" => request()->segment(2) === 'rujukan'],
            ],
        ],
        (object)[
            "title"        => "Kelola Layanan",
            "path"         => "#",
            "icon"         => "fas fa-box-open",
            "onlyCustomer" => true,
            "active"       => request()->segment(1) === 'admin' && request()->segment(2) === 'layanan',
            "children" => [
                (object)["title" => "Layanan", "path" => "/admin/layanan", "icon" => "fas fa-store", "active" => request()->segment(2) === 'layanan'],
            ],
        ],
        (object)[
            "title"        => "Transaksi",
            "path"         => "#",
            "icon"         => "fas fa-receipt",
            "onlyCustomer" => true,
            "active"       => request()->segment(1) === 'admin' && in_array(request()->segment(2), ['transaksi-admin']),
            "children" => [
                (object)["title" => "Dashboard",        "path" => "/admin/transaksi-admin/dashboard", "icon" => "fas fa-chart-pie", "active" => request()->is('admin/transaksi-admin/dashboard')],
                (object)["title" => "Daftar Transaksi", "path" => "/admin/transaksi-admin",           "icon" => "fas fa-list-alt",  "active" => request()->is('admin/transaksi-admin') && !request()->is('admin/transaksi-admin/dashboard')],
            ],
        ],
    ];
@endphp

<aside class="w-56 h-full flex flex-col" style="background: linear-gradient(180deg, #0c4a6e 0%, #0369a1 100%);">

    {{-- Logo --}}
    <div class="px-5 py-4 flex items-center gap-3 border-b border-white/10">
        <div class="w-9 h-9 rounded-lg bg-white/15 border border-white/30 flex items-center justify-center text-white">
            <i class="fas fa-database text-sm"></i>
        </div>
        <div class="leading-tight">
            <p class="text-[10px] font-semibold text-white/80 tracking-wide uppercase">Pusat Data</p>
            <p class="text-[10px] font-bold text-white tracking-wide uppercase">Indonesia Bali</p>
        </div>
    </div>

    {{-- Nav --}}
    <nav class="flex-1 overflow-y-auto py-3 px-3 scrollbar-none">
        <ul class="flex flex-col gap-0.5">
            @foreach ($menus as $menu)
                @if($isCustomer && $menu->onlyCustomer)
                    @continue
                @endif

                <li class="relative group">

                    {{-- Section label heuristic: group menu atas (Data, Transaksi Saya) vs bawah --}}
                    @if($menu->onlyCustomer && $loop->first)
                        <p class="text-[9px] font-semibold uppercase tracking-widest text-sky-300/50 px-2 pt-3 pb-1">Pengelolaan</p>
                    @endif

                    {{-- Parent --}}
                    <a href="{{ $menu->children ? '#' : url($menu->path) }}"
                       class="flex items-center justify-between px-2.5 py-2 rounded-md transition-colors text-xs
                              {{ $menu->active
                                  ? 'bg-white/15 text-white font-semibold'
                                  : 'text-white/70 hover:bg-white/8 hover:text-white' }}
                              relative">

                        {{-- Active indicator --}}
                        @if($menu->active)
                            <span class="absolute left-0 top-1/2 -translate-y-1/2 w-0.5 h-3/5 bg-sky-300 rounded-r-full"></span>
                        @endif

                        <div class="flex items-center gap-2.5">
                            <i class="{{ $menu->icon }} w-4 text-center text-[11px]"></i>
                            <span>{{ $menu->title }}</span>
                        </div>

                        @if($menu->children)
                            <i class="fas fa-chevron-down text-[8px] opacity-50 {{ $menu->active ? 'rotate-180' : '' }} transition-transform"></i>
                        @endif
                    </a>

                    {{-- Submenu --}}
                    @if($menu->children)
                        <ul class="{{ $menu->active ? 'block' : 'hidden group-hover:block' }} mt-0.5 ml-3 pl-3 border-l border-sky-400/25 space-y-0.5">
                            @foreach($menu->children as $child)
                                <li>
                                    <a href="{{ url($child->path) }}"
                                       class="flex items-center gap-2.5 px-2 py-1.5 rounded-md text-[11px] transition-colors
                                              {{ $child->active
                                                  ? 'text-sky-300 font-semibold'
                                                  : 'text-white/55 hover:text-white hover:bg-white/6' }}">
                                        <i class="{{ $child->icon }} text-[10px]"></i>
                                        <span>{{ $child->title }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif

                </li>
            @endforeach
        </ul>
    </nav>

    {{-- Logout --}}
    <div class="p-4 border-t border-white/10">
        <form action="/logout" method="POST">
            @csrf
            <button type="submit"
                    class="w-full flex items-center justify-center gap-2 text-[11px] font-semibold
                           text-white/90 hover:text-white bg-red-500/40 hover:bg-red-600 border border-red-400/30
                           hover:border-red-400/55 py-2 rounded-lg transition-colors">
                <i class="fa-solid fa-arrow-right-from-bracket rotate-180"></i>
                Logout
            </button>
        </form>
    </div>

</aside>