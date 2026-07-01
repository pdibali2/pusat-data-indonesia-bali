<div class="space-y-5">
    {{-- Nama Klasifikasi --}}
    <div>
        <input type="text" id="nama_klasifikasi" name="nama_klasifikasi"
               value="{{ old('nama_klasifikasi', $klasifikasi->nama_klasifikasi ?? '') }}"
               placeholder="Nama klasifikasi"
               class="w-full px-4 py-3 text-sm border rounded-xl focus:outline-none focus:ring-2 focus:ring-stikom-blue/40 focus:border-stikom-blue transition
                      {{ $errors->has('nama_klasifikasi') ? 'border-red-400 bg-red-50' : 'border-gray-200' }}">
        @error('nama_klasifikasi')
        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    {{-- Icon Picker (grid visual) --}}
    <div>
        <input type="hidden" id="icon" name="icon" value="{{ old('icon', $klasifikasi->icon ?? '') }}">

        <div id="icon-grid" class="grid grid-cols-6 sm:grid-cols-8 gap-2">
            {{-- Default option --}}
            <button type="button" data-icon=""
                    class="icon-option aspect-square rounded-xl border-2 flex items-center justify-center transition
                           border-stikom-blue bg-stikom-blue/10 text-stikom-blue">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    {!! config('klasifikasi_icons.svg')[config('klasifikasi_icons.default')] !!}
                </svg>
            </button>

            @foreach(config('klasifikasi_icons.whitelist') as $key => $label)
                <button type="button" data-icon="{{ $key }}" title="{{ $label }}"
                        class="icon-option aspect-square rounded-xl border-2 flex items-center justify-center transition
                               border-gray-200 text-gray-400 hover:border-stikom-blue/50 hover:text-stikom-blue">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        {!! config('klasifikasi_icons.svg')[$key] !!}
                    </svg>
                </button>
            @endforeach
        </div>

        @error('icon')
        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>
</div>

<style>
    .icon-option svg { width: 20px; height: 20px; }
</style>

<script>
(function () {
    const hiddenInput = document.getElementById('icon');
    const buttons = document.querySelectorAll('.icon-option');
    const activeClasses = ['border-stikom-blue', 'bg-stikom-blue/10', 'text-stikom-blue'];
    const inactiveClasses = ['border-gray-200', 'text-gray-400'];

    const setActive = (btn) => {
        buttons.forEach(b => {
            b.classList.remove(...activeClasses);
            b.classList.add(...inactiveClasses);
        });
        btn.classList.remove(...inactiveClasses);
        btn.classList.add(...activeClasses);
    };

    buttons.forEach(btn => {
        if (btn.dataset.icon === hiddenInput.value) setActive(btn);
        btn.addEventListener('click', () => {
            hiddenInput.value = btn.dataset.icon;
            setActive(btn);
        });
    });
})();
</script>