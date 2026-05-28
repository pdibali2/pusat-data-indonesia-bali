@php
    $user       = auth()->user();
    $isCustomer = $user?->group_id === 3;
    $isAdmin    = in_array($user?->group_id, [1, 2]); // sesuaikan group_id admin

    $menus = [
        (object)[
            "title"        => "Data",
            "path"         => "/data",
            "icon"         => "fas fa-database",
            "active"       => request()->segment(1) === 'data',
            "children"     => [],
            "onlyCustomer" => false,
            "onlyAdmin"    => false,
        ],
        (object)[
            "title"        => "Transaksi Saya",
            "path"         => "/transaksi/riwayat",
            "icon"         => "fas fa-file-invoice-dollar",
            "active"       => request()->is('transaksi/*'),
            "children"     => [],
            "onlyCustomer" => false,
            "onlyAdmin"    => false,
        ],
        (object)[
            "title"        => "Manajemen Data",
            "path"         => "/data",
            "icon"         => "fas fa-cogs",
            "active"       => request()->is('anomaly/control*'),
            "onlyCustomer" => true,
            "onlyAdmin"    => false,
            "children"     => array_filter([
                $isAdmin ? (object)[
                    "title"     => "Control Anomali",
                    "path"      => "/anomaly/control",
                    "icon"      => "fa-solid fa-shield-halved",
                    "active"    => request()->is('anomaly/control*'),
                    "onlyAdmin" => true,
                ] : null,
            ]),
        ],
        (object)[
            "title"        => "Metadata",
            "path"         => "/metadata",
            "icon"         => "fas fa-folder-open",
            "active"       => request()->is('metadata*'),
            "onlyCustomer" => true,
            "onlyAdmin"    => false,
            "children" => [
                (object)["title" => "All Metadata", "path" => "/metadata",          "icon" => "fa-solid fa-floppy-disk",     "active" => request()->is('metadata'),          "onlyAdmin" => false],
                (object)["title" => "Approval",     "path" => "/metadata/approval", "icon" => "fa-solid fa-clipboard-check", "active" => request()->is('metadata/approval'), "onlyAdmin" => false],
            ],
        ],
        (object)[
            "title"        => "Dimensi",
            "path"         => "/dimensi",
            "icon"         => "fas fa-cubes",
            "active"       => request()->is('dimensi_*'),
            "onlyCustomer" => true,
            "onlyAdmin"    => false,
            "children" => [
                (object)["title" => "Dimensi Waktu",  "path" => "/dimensi_waktu",  "icon" => "fa-solid fa-clock",        "active" => request()->segment(1) === 'dimensi_waktu',  "onlyAdmin" => false],
                (object)["title" => "Dimensi Lokasi", "path" => "/dimensi_lokasi", "icon" => "fa-solid fa-location-dot", "active" => request()->segment(1) === 'dimensi_lokasi', "onlyAdmin" => false],
            ],
        ],
        (object)[
            "title"        => "Kelola Master Data",
            "path"         => "#",
            "icon"         => "fas fa-users-cog",
            "onlyCustomer" => true,
            "onlyAdmin"    => false,
            "active"       => request()->segment(1) === 'admin' && in_array(request()->segment(2), ['users','groups','klasifikasi','produsen','rujukan']),
            "children" => [
                (object)["title" => "Kelola User",        "path" => "/admin/users",       "icon" => "fas fa-user",     "active" => request()->segment(2) === 'users',        "onlyAdmin" => false],
                (object)["title" => "Kelola Group",       "path" => "/admin/groups",      "icon" => "fas fa-users",    "active" => request()->segment(2) === 'groups',       "onlyAdmin" => false],
                (object)["title" => "Kelola Klasifikasi", "path" => "/admin/klasifikasi", "icon" => "fas fa-tags",     "active" => request()->segment(2) === 'klasifikasi',  "onlyAdmin" => false],
                (object)["title" => "Kelola Produsen",    "path" => "/admin/produsen",    "icon" => "fas fa-industry", "active" => request()->segment(2) === 'produsen',      "onlyAdmin" => false],
                (object)["title" => "Kelola Rujukan",     "path" => "/admin/rujukan",     "icon" => "fas fa-file-alt", "active" => request()->segment(2) === 'rujukan',       "onlyAdmin" => false],
            ],
        ],
        (object)[
            "title"        => "Kelola Layanan",
            "path"         => "#",
            "icon"         => "fas fa-box-open",
            "onlyCustomer" => true,
            "onlyAdmin"    => false,
            "active"       => request()->segment(1) === 'admin' && request()->segment(2) === 'layanan',
            "children" => [
                (object)["title" => "Layanan", "path" => "/admin/layanan", "icon" => "fas fa-store", "active" => request()->segment(2) === 'layanan', "onlyAdmin" => false],
            ],
        ],
        (object)[
            "title"        => "Transaksi",
            "path"         => "#",
            "icon"         => "fas fa-receipt",
            "onlyCustomer" => true,
            "onlyAdmin"    => false,
            "active"       => request()->segment(1) === 'admin' && in_array(request()->segment(2), ['transaksi-admin']),
            "children" => [
                (object)["title" => "Dashboard",        "path" => "/admin/transaksi-admin/dashboard", "icon" => "fas fa-chart-pie", "active" => request()->is('admin/transaksi-admin/dashboard'),                                              "onlyAdmin" => false],
                (object)["title" => "Daftar Transaksi", "path" => "/admin/transaksi-admin",           "icon" => "fas fa-list-alt",  "active" => request()->is('admin/transaksi-admin') && !request()->is('admin/transaksi-admin/dashboard'), "onlyAdmin" => false],
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
                {{-- Skip jika customer dan menu onlyCustomer --}}
                @if($isCustomer && $menu->onlyCustomer)
                    @continue
                @endif

                @php
                    $hasChildren = !empty($menu->children);
                    $isOpen      = $menu->active;
                @endphp

                <li x-data="{ open: {{ $isOpen ? 'true' : 'false' }} }">

                    {{-- Parent --}}
                    @if($hasChildren)
                    <button @click="open = !open"
                            class="w-full flex items-center justify-between px-2.5 py-2 rounded-md transition-colors text-xs
                                   {{ $menu->active
                                       ? 'bg-white/15 text-white font-semibold'
                                       : 'text-white/70 hover:bg-white/8 hover:text-white' }}
                                   relative">
                        @if($menu->active)
                            <span class="absolute left-0 top-1/2 -translate-y-1/2 w-0.5 h-3/5 bg-sky-300 rounded-r-full"></span>
                        @endif
                        <div class="flex items-center gap-2.5">
                            <i class="{{ $menu->icon }} w-4 text-center text-[11px]"></i>
                            <span>{{ $menu->title }}</span>
                        </div>
                        <i class="fas fa-chevron-down text-[8px] opacity-50 transition-transform duration-200"
                           :class="open ? 'rotate-180' : ''"></i>
                    </button>
                    @else
                    <a href="{{ url($menu->path) }}"
                       class="flex items-center justify-between px-2.5 py-2 rounded-md transition-colors text-xs
                              {{ $menu->active
                                  ? 'bg-white/15 text-white font-semibold'
                                  : 'text-white/70 hover:bg-white/8 hover:text-white' }}
                              relative">
                        @if($menu->active)
                            <span class="absolute left-0 top-1/2 -translate-y-1/2 w-0.5 h-3/5 bg-sky-300 rounded-r-full"></span>
                        @endif
                        <div class="flex items-center gap-2.5">
                            <i class="{{ $menu->icon }} w-4 text-center text-[11px]"></i>
                            <span>{{ $menu->title }}</span>
                        </div>
                    </a>
                    @endif

                    {{-- Submenu --}}
                    @if($hasChildren)
                    <ul x-show="open"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 -translate-y-1"
                        class="mt-0.5 ml-3 pl-3 border-l border-sky-400/25 space-y-0.5">
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
                           text-white bg-red-500/80 hover:bg-red-500 border border-red-400/30
                           hover:border-red-400/55 py-2 rounded-lg transition-colors">
                <i class="fa-solid fa-arrow-right-from-bracket rotate-180"></i>
                Logout
            </button>
        </form>
    </div>

</aside>