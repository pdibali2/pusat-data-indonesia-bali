<div class="bg-white rounded-xl border border-slate-100 shadow-sm p-4 mb-4">
    <form method="GET" action="{{ route('anomaly.control.index') }}" class="flex flex-wrap gap-3 items-end">

        {{-- Search --}}
        <div class="flex-1 min-w-48">
            <label class="text-xs text-slate-500 mb-1 block">Cari</label>
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari pesan anomali, tabel..."
                       class="w-full pl-8 pr-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none">
            </div>
        </div>

        {{-- Severity --}}
        <div class="min-w-36">
            <label class="text-xs text-slate-500 mb-1 block">Severity</label>
            <select name="severity" class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-sky-500">
                <option value="">Semua</option>
                @foreach(['critical','high','medium','low'] as $s)
                <option value="{{ $s }}" @selected(request('severity') === $s)>
                    {{ ucfirst($s) }}
                </option>
                @endforeach
            </select>
        </div>

        {{-- Status --}}
        <div class="min-w-40">
            <label class="text-xs text-slate-500 mb-1 block">Status</label>
            <select name="status" class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-sky-500">
                <option value="">Semua</option>
                @foreach(['warning','under_review','approved','approved_with_note','rejected'] as $st)
                <option value="{{ $st }}" @selected(request('status') === $st)>
                    {{ ucwords(str_replace('_',' ',$st)) }}
                </option>
                @endforeach
            </select>
        </div>

        {{-- Type --}}
        <div class="min-w-40">
            <label class="text-xs text-slate-500 mb-1 block">Tipe Anomali</label>
            <select name="type" class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-sky-500">
                <option value="">Semua</option>
                @foreach(['extreme_increase','extreme_decrease','source_conflict','unusual_value'] as $t)
                <option value="{{ $t }}" @selected(request('type') === $t)>
                    {{ ucwords(str_replace('_',' ',$t)) }}
                </option>
                @endforeach
            </select>
        </div>

        {{-- Buttons --}}
        <div class="flex gap-2">
            <button type="submit"
                    class="bg-sky-600 hover:bg-sky-700 text-white text-sm px-4 py-2 rounded-lg transition inline-flex items-center gap-2">
                <i class="fa-solid fa-filter text-xs"></i> Filter
            </button>
            <a href="{{ route('anomaly.control.index') }}"
               class="bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm px-4 py-2 rounded-lg transition inline-flex items-center gap-2">
                <i class="fa-solid fa-rotate-left text-xs"></i> Reset
            </a>
        </div>

    </form>
</div>