@php $isEdit = isset($layanan) && $layanan->exists; @endphp

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- ── Kolom Kiri (2/3) ── --}}
    <div class="lg:col-span-2 space-y-5">

        {{-- Informasi Dasar --}}
        <div class="card-panel">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-700">Informasi Dasar</h2>
            </div>
            <div class="px-6 py-5 space-y-4">

                {{-- Nama Layanan --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Nama Layanan <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nama_layanan"
                           value="{{ old('nama_layanan', $layanan->nama_layanan ?? '') }}"
                           placeholder="Contoh: Paket Starter"
                           class="w-full px-4 py-2.5 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                                  {{ $errors->has('nama_layanan') ? 'border-red-400 bg-red-50' : 'border-gray-200' }}">
                    @error('nama_layanan')
                    <p class="mt-1 text-xs text-red-500 flex items-center gap-1"><i class="fas fa-exclamation-circle"></i> {{ $message }}</p>
                    @enderror
                </div>

                {{-- Harga --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Harga (Rp) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-medium">Rp</span>
                        <input type="number" name="harga" min="0" step="1000"
                               value="{{ old('harga', $layanan->harga ?? '') }}"
                               placeholder="0"
                               class="w-full pl-10 pr-4 py-2.5 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                                      {{ $errors->has('harga') ? 'border-red-400 bg-red-50' : 'border-gray-200' }}">
                    </div>
                    @error('harga')
                    <p class="mt-1 text-xs text-red-500 flex items-center gap-1"><i class="fas fa-exclamation-circle"></i> {{ $message }}</p>
                    @enderror
                </div>

                {{-- Durasi --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Durasi <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="durasi" min="1"
                               value="{{ old('durasi', $layanan->durasi ?? 1) }}"
                               id="input-durasi"
                               class="w-full px-4 py-2.5 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                                      {{ $errors->has('durasi') ? 'border-red-400 bg-red-50' : 'border-gray-200' }}">
                        @error('durasi')
                        <p class="mt-1 text-xs text-red-500 flex items-center gap-1"><i class="fas fa-exclamation-circle"></i> {{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Tipe Durasi <span class="text-red-500">*</span>
                        </label>
                        <select name="durasi_type" id="select-durasi-type"
                                onchange="handleDurasiType(this.value)"
                                class="w-full px-4 py-2.5 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white
                                       {{ $errors->has('durasi_type') ? 'border-red-400 bg-red-50' : 'border-gray-200' }}">
                            @foreach(['harian','mingguan','bulanan','tahunan','selamanya'] as $type)
                            <option value="{{ $type }}"
                                {{ old('durasi_type', $layanan->durasi_type ?? 'bulanan') === $type ? 'selected' : '' }}>
                                {{ ucfirst($type) }}
                            </option>
                            @endforeach
                        </select>
                        @error('durasi_type')
                        <p class="mt-1 text-xs text-red-500 flex items-center gap-1"><i class="fas fa-exclamation-circle"></i> {{ $message }}</p>
                        @enderror
                    </div>
                </div>

            </div>
        </div>

        {{-- Fitur Layanan --}}
        <div class="card-panel">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-semibold text-gray-700">Fitur Layanan</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Tambahkan fitur-fitur yang disertakan dalam paket ini.</p>
                </div>
                <button type="button" onclick="addFitur()"
                        class="inline-flex items-center gap-1.5 text-xs text-blue-600 hover:text-blue-700 font-medium bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg transition">
                    <i class="fas fa-plus text-xs"></i> Tambah Fitur
                </button>
            </div>
            <div class="px-6 py-5">
                <div id="fitur-list" class="space-y-2">
                    @php
                        $fiturs = old('fiturs', $isEdit ? $layanan->fiturs->pluck('nama_fitur')->toArray() : []);
                        if (empty($fiturs)) $fiturs = [''];
                    @endphp
                    @foreach($fiturs as $i => $fiturNama)
                    <div class="fitur-item flex items-center gap-2">
                        <div class="flex-shrink-0 w-6 h-6 rounded-full bg-blue-50 flex items-center justify-center">
                            <i class="fas fa-grip-vertical text-gray-300 text-xs"></i>
                        </div>
                        <input type="text" name="fiturs[]"
                               value="{{ $fiturNama }}"
                               placeholder="Contoh: Akses 5 metadata"
                               class="flex-1 px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <button type="button" onclick="removeFitur(this)"
                                class="flex-shrink-0 p-1.5 text-gray-300 hover:text-red-500 hover:bg-red-50 rounded-lg transition">
                            <i class="fas fa-times text-xs"></i>
                        </button>
                    </div>
                    @endforeach
                </div>
                <p id="fitur-empty-note" class="text-xs text-gray-400 mt-3 {{ count(array_filter($fiturs)) > 0 ? 'hidden' : '' }}">
                    Belum ada fitur. Klik "Tambah Fitur" untuk menambahkan.
                </p>
            </div>
        </div>

    </div>

    {{-- ── Kolom Kanan (1/3) ── --}}
    <div class="space-y-5">

        {{-- Status & Pengaturan --}}
        <div class="card-panel">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-700">Status & Pengaturan</h2>
            </div>
            <div class="px-6 py-5 space-y-4">

                {{-- Status --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Status <span class="text-red-500">*</span>
                    </label>
                    <select name="status"
                            class="w-full px-4 py-2.5 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white
                                   {{ $errors->has('status') ? 'border-red-400' : 'border-gray-200' }}">
                        @foreach(['publish' => 'Publish', 'pending' => 'Pending (Draft)', 'takedown' => 'Takedown'] as $val => $label)
                        <option value="{{ $val }}" {{ old('status', $layanan->status ?? 'pending') === $val ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                        @endforeach
                    </select>
                    @error('status')
                    <p class="mt-1 text-xs text-red-500 flex items-center gap-1"><i class="fas fa-exclamation-circle"></i> {{ $message }}</p>
                    @enderror
                    <p class="mt-1.5 text-xs text-gray-400">
                        <strong class="text-gray-500">Publish</strong>: tampil di landing page.<br>
                        <strong class="text-gray-500">Pending</strong>: belum aktif.<br>
                        <strong class="text-gray-500">Takedown</strong>: disembunyikan.
                    </p>
                </div>

                {{-- Urutan --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Urutan Tampil</label>
                    <input type="number" name="urutan" min="0"
                           value="{{ old('urutan', $layanan->urutan ?? 0) }}"
                           class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="mt-1 text-xs text-gray-400">Angka kecil = tampil lebih awal.</p>
                </div>

                {{-- Is Popular --}}
                <div class="flex items-center justify-between py-1">
                    <div>
                        <p class="text-sm font-medium text-gray-700">Tandai Populer</p>
                        <p class="text-xs text-gray-400">Ditampilkan dengan badge "Populer".</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_popular" value="1"
                               class="sr-only peer"
                               {{ old('is_popular', $layanan->is_popular ?? false) ? 'checked' : '' }}>
                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-300 rounded-full peer
                                    peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-0.5 after:left-[2px]
                                    after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all
                                    peer-checked:bg-blue-600"></div>
                    </label>
                </div>

            </div>
        </div>

        {{-- Thumbnail --}}
        <div class="card-panel">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-700">Thumbnail</h2>
            </div>
            <div class="px-6 py-5">

                {{-- Preview --}}
                <div id="thumb-preview-wrap" class="{{ ($isEdit && $layanan->thumbnail) ? '' : 'hidden' }} mb-3">
                    <div class="relative inline-block">
                        <img id="thumb-preview"
                             src="{{ $isEdit && $layanan->thumbnail ? asset('storage/' . $layanan->thumbnail) : '' }}"
                             alt="Thumbnail Preview"
                             class="w-full h-36 object-cover rounded-lg border border-gray-200">
                        <button type="button" onclick="clearThumbnail()"
                                class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center hover:bg-red-600 transition">
                            <i class="fas fa-times text-xs"></i>
                        </button>
                    </div>
                </div>

                {{-- Upload Area --}}
                <div id="thumb-upload-area"
                     class="{{ ($isEdit && $layanan->thumbnail) ? 'hidden' : '' }} border-2 border-dashed border-gray-200 rounded-lg p-6 text-center hover:border-blue-400 transition cursor-pointer"
                     onclick="document.getElementById('thumbnail-input').click()">
                    <i class="fas fa-cloud-upload-alt text-gray-300 text-2xl mb-2"></i>
                    <p class="text-sm text-gray-500">Klik untuk upload gambar</p>
                    <p class="text-xs text-gray-400 mt-1">JPG, JPEG, PNG, WEBP — maks 2MB</p>
                </div>

                <input type="file" id="thumbnail-input" name="thumbnail" accept="image/*" class="hidden"
                       onchange="previewThumbnail(this)">

                @error('thumbnail')
                <p class="mt-2 text-xs text-red-500 flex items-center gap-1"><i class="fas fa-exclamation-circle"></i> {{ $message }}</p>
                @enderror
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
// ── Fitur ────────────────────────────────────────────────────
function addFitur() {
    const list = document.getElementById('fitur-list');
    const div  = document.createElement('div');
    div.className = 'fitur-item flex items-center gap-2';
    div.innerHTML = `
        <div class="flex-shrink-0 w-6 h-6 rounded-full bg-blue-50 flex items-center justify-center">
            <i class="fas fa-grip-vertical text-gray-300 text-xs"></i>
        </div>
        <input type="text" name="fiturs[]" placeholder="Contoh: Akses 5 metadata"
               class="flex-1 px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        <button type="button" onclick="removeFitur(this)"
                class="flex-shrink-0 p-1.5 text-gray-300 hover:text-red-500 hover:bg-red-50 rounded-lg transition">
            <i class="fas fa-times text-xs"></i>
        </button>`;
    list.appendChild(div);
    div.querySelector('input').focus();
    updateEmptyNote();
}

function removeFitur(btn) {
    btn.closest('.fitur-item').remove();
    updateEmptyNote();
}

function updateEmptyNote() {
    const items = document.querySelectorAll('.fitur-item').length;
    document.getElementById('fitur-empty-note').classList.toggle('hidden', items > 0);
}

// ── Thumbnail ────────────────────────────────────────────────
function previewThumbnail(input) {
    if (!input.files || !input.files[0]) return;
    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('thumb-preview').src = e.target.result;
        document.getElementById('thumb-preview-wrap').classList.remove('hidden');
        document.getElementById('thumb-upload-area').classList.add('hidden');
    };
    reader.readAsDataURL(input.files[0]);
}

function clearThumbnail() {
    document.getElementById('thumbnail-input').value = '';
    document.getElementById('thumb-preview').src = '';
    document.getElementById('thumb-preview-wrap').classList.add('hidden');
    document.getElementById('thumb-upload-area').classList.remove('hidden');
}

// ── Durasi Type ───────────────────────────────────────────────
function handleDurasiType(val) {
    const inputDurasi = document.getElementById('input-durasi');

    if (val === 'selamanya') {
        inputDurasi.value = 1;
        inputDurasi.readOnly = true;

        inputDurasi.classList.add(
            'opacity-50',
            'cursor-not-allowed',
            'bg-gray-100'
        );
    } else {
        inputDurasi.readOnly = false;

        inputDurasi.classList.remove(
            'opacity-50',
            'cursor-not-allowed',
            'bg-gray-100'
        );
    }
}

// Init on page load
document.addEventListener('DOMContentLoaded', function () {
    const sel = document.getElementById('select-durasi-type');
    if (sel) handleDurasiType(sel.value);
    updateEmptyNote();
});
</script>
@endpush