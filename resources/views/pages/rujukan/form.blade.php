{{-- resources/views/pages/rujukan/form.blade.php --}}
<div class="page-layout">

    <div>
        <label class="block text-xs font-semibold text-gray-400 mb-1.5">
            Nama Rujukan <span class="text-red-400">*</span>
        </label>
        <input type="text" name="nama_rujukan" value="{{ old('nama_rujukan', $rujukan->nama_rujukan ?? '') }}"
               class="w-full bg-white/5 border @error('nama_rujukan') border-red-500/50 @else border-gray-600 @enderror
                      text-gray-600 text-sm rounded-lg px-3 py-2.5 placeholder-gray-600
                      focus:outline-none focus:border-green-400/50 transition"
               placeholder="Masukkan nama rujukan">
        @error('nama_rujukan')
            <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-xs font-semibold text-gray-400 mb-1.5">
            Produsen <span class="text-red-400">*</span>
        </label>
        <select name="produsen_id"
                class="w-full bg-white/5 border @error('produsen_id') border-red-500/50 @else border-gray-600 @enderror
                       text-gray-600 text-sm rounded-lg px-3 py-2.5
                       focus:outline-none focus:border-green-400/50 transition">
            <option value="">-- Pilih Produsen --</option>
            @foreach ($produsen as $p)
                <option value="{{ $p->produsen_id }}"
                    {{ old('produsen_id', $rujukan->produsen_id ?? '') == $p->produsen_id ? 'selected' : '' }}>
                    {{ $p->nama_produsen }}
                </option>
            @endforeach
        </select>
        @error('produsen_id')
            <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-xs font-semibold text-gray-400 mb-1.5">Link Rujukan</label>
        <input type="url" name="link_rujukan" value="{{ old('link_rujukan', $rujukan->link_rujukan ?? '') }}"
               class="w-full bg-white/5 border @error('link_rujukan') border-red-500/50 @else border-gray-600 @enderror
                      text-gray-600 text-sm rounded-lg px-3 py-2.5 placeholder-gray-600
                      focus:outline-none focus:border-green-400/50 transition"
               placeholder="https://...">
        @error('link_rujukan')
            <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-xs font-semibold text-gray-400 mb-1.5">Gambar Rujukan</label>

        @isset($rujukan)
            @if ($rujukan->gambar_rujukan)
                <div class="mb-2">
                    <img src="{{ Storage::url($rujukan->gambar_rujukan) }}"
                         alt="Gambar saat ini"
                         class="w-24 h-24 object-cover rounded-lg border border-gray-600">
                    <p class="text-xs text-gray-600 mt-1">Gambar saat ini. Unggah baru untuk mengganti.</p>
                </div>
            @endif
        @endisset

        <input type="file" name="gambar_rujukan" accept="image/*"
               class="w-full bg-white/5 border @error('gambar_rujukan') border-red-500/50 @else border-gray-600 @enderror
                      text-gray-400 text-sm rounded-lg px-3 py-2.5
                      file:mr-3 file:py-1 file:px-3 file:rounded-md file:border-0
                      file:text-xs file:font-semibold file:bg-green-500/20 file:text-green-400
                      hover:file:bg-green-500/30 focus:outline-none focus:border-green-400/50 transition">
        <p class="mt-1 text-xs text-gray-600">Format: JPG, JPEG, PNG, WEBP. Maks: 2MB</p>
        @error('gambar_rujukan')
            <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
        @enderror
    </div>

</div>