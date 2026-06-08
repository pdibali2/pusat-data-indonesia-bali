@extends('layouts.main')

@section('content')
<div class="mt-2 bg-white rounded-md shadow p-6">

    <!-- HEADER -->
    <div class="flex justify-between items-start">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Dimensi Lokasi</h1>
        </div>

        <div class="text-right text-sm text-gray-500">
            <div class="text-right text-sm text-gray-500">
                <p id="current-date">Loading date...</p>
                <p id="current-time" class="font-mono text-sky-600 font-semibold"></p>
            </div>
        </div>
    </div>


    <!-- ACTION BAR -->
    <div class="flex justify-between items-center mt-6">

        <a href="{{ route('dimensi_lokasi.create') }}"
        class="bg-sky-600 hover:bg-sky-700 text-white px-4 py-2 rounded-md text-sm shadow">
            + Tambah Lokasi
        </a>

        <form method="GET" class="w-1/3">
            <input 
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Cari lokasi..."
                class="border rounded-md px-3 py-2 w-full text-sm focus:outline-none focus:ring-2 focus:ring-sky-400">
        </form>

    </div>


    <!-- TABLE -->
    <div class="mt-6 border rounded-md overflow-hidden">

        <table class="w-full text-xs">

            <thead class="bg-gray-200 text-gray-700">

                <tr>
                    <th class="p-3 text-left">
                        No.
                    </th>
                    <th class="p-3 text-left">
                        Location Id
                    </th>
                    <th class="p-3 text-left">
                        Nama Wilayah
                    </th>
                    <th class="p-3 text-left">
                        Status
                    </th>
                </tr>

            </thead>


            <tbody class="divide-y">

                @forelse($data as $row)

                <tr class="hover:bg-gray-50">

                    <td class="p-3 bg-gray-50">
                        {{ ($data->currentPage() - 1) * $data->perPage() + $loop->iteration }}
                    </td>
                    
                    <td class="p-3">
                        {{ $row->location_id }}
                    </td>

                    <td class="p-3 bg-gray-50">
                        {{ $row->nama_wilayah }}
                    </td>

                    <td class="p-3">
                        @if ($row->status === 1)
                            <span class="px-2 py-0.5 rounded-full text-xs bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                <i class="fas fa-check-circle text-xs"></i> Aktif
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-xs bg-red-500/10 text-red-400 border border-red-500/20">
                                <i class="fas fa-circle-xmark text-xs"></i> Nonaktif
                            </span>
                        @endif
                    </td>

                    <td class="p-3">
                        <form action="{{ route('dimensi_lokasi.toggle_status', $row->location_id) }}" method="POST" class="inline">
                            @csrf
                            @if ($row->status === 1)
                                <button type="submit" onclick="return confirm('Nonaktifkan data ini?')" class="text-yellow-500 hover:text-yellow-700 hover:bg-yellow-50 p-2 rounded transition-colors">
                                    <i class="fas fa-ban text-sm"></i>
                                </button>
                            @else
                                <button type="submit" class="text-emerald-500 hover:text-emerald-700 hover:bg-emerald-50 p-2 rounded transition-colors">
                                    <i class="fas fa-check text-sm"></i>
                                </button>
                            @endif
                        </form>
                    </td>

                </tr>

                @empty

                <tr>
                    <td colspan="5" class="text-center p-5 text-gray-500">
                        Data lokasi tidak ditemukan
                    </td>
                </tr>

                @endforelse

            </tbody>

        </table>

    </div>


    <!-- PAGINATION -->
    @if(isset($data))
    <div class="mt-5">
        {{ $data->links() }}
    </div>
    @endif

    <script>
        // Live clock & date
        function updateDateTime() {
            const now = new Date();
            const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            const timeOptions = { hour: '2-digit', minute: '2-digit', second: '2-digit' };
            document.getElementById('current-date').textContent =
                now.toLocaleDateString('id-ID', dateOptions);
            document.getElementById('current-time').textContent =
                now.toLocaleTimeString('id-ID', timeOptions);
        }
        updateDateTime();
        setInterval(updateDateTime, 1000);
    </script>

</div>
@endsection