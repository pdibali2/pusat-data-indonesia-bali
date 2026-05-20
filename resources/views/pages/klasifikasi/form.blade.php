<div class="space-y-4">
    <div>
        <label for="nama_klasifikasi" class="block text-sm font-medium text-gray-700 mb-1">
            Nama Klasifikasi <span class="text-red-500">*</span>
        </label>
        <input type="text" id="nama_klasifikasi" name="nama_klasifikasi"
               value="{{ old('nama_klasifikasi', $klasifikasi->nama_klasifikasi ?? '') }}"
               placeholder="Contoh: Kependudukan"
               class="w-full px-4 py-2.5 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                      {{ $errors->has('nama_klasifikasi') ? 'border-red-400 bg-red-50' : 'border-gray-200' }}">
        @error('nama_klasifikasi')
        <p class="mt-1 text-xs text-red-500 flex items-center gap-1">
            <i class="fas fa-exclamation-circle"></i> {{ $message }}
        </p>
        @enderror
    </div>
</div>