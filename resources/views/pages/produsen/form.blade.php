{{-- resources/views/pages/produsen/form.blade.php --}}
<div class="page-layout">

    <div>
        <label class="block text-xs font-semibold text-gray-400 mb-1.5">
            Nama Produsen <span class="text-red-400">*</span>
        </label>
        <input type="text" name="nama_produsen" value="{{ old('nama_produsen', $produsen->nama_produsen ?? '') }}"
               class="w-full bg-white/5 border @error('nama_produsen') border-red-500/50 @else text-gray-700 @enderror
                      text-gray-200 text-sm rounded-lg px-3 py-2.5 placeholder-gray-600
                      focus:outline-none focus:border-green-400/50 transition"
               placeholder="Masukkan nama produsen">
        @error('nama_produsen')
            <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-xs font-semibold text-gray-400 mb-1.5">Nama Contact Person</label>
        <input type="text" name="nama_contact_person" value="{{ old('nama_contact_person', $produsen->nama_contact_person ?? '') }}"
               class="w-full bg-white/5 border @error('nama_contact_person') border-red-500/50 @else text-gray-700 @enderror
                      text-gray-200 text-sm rounded-lg px-3 py-2.5 placeholder-gray-600
                      focus:outline-none focus:border-green-400/50 transition"
               placeholder="Nama narahubung">
        @error('nama_contact_person')
            <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-xs font-semibold text-gray-400 mb-1.5">Email</label>
        <input type="email" name="email" value="{{ old('email', $produsen->email ?? '') }}"
               class="w-full bg-white/5 border @error('email') border-red-500/50 @else text-gray-700 @enderror
                      text-gray-200 text-sm rounded-lg px-3 py-2.5 placeholder-gray-600
                      focus:outline-none focus:border-green-400/50 transition"
               placeholder="email@contoh.com">
        @error('email')
            <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-xs font-semibold text-gray-400 mb-1.5">Nomor Kontak</label>
        <input type="text" name="kontak" value="{{ old('kontak', $produsen->kontak ?? '') }}"
               class="w-full bg-white/5 border @error('kontak') border-red-500/50 @else text-gray-700 @enderror
                      text-gray-200 text-sm rounded-lg px-3 py-2.5 placeholder-gray-600
                      focus:outline-none focus:border-green-400/50 transition"
               placeholder="Nomor telepon / HP">
        @error('kontak')
            <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-xs font-semibold text-gray-400 mb-1.5">Alamat</label>
        <textarea name="alamat" rows="3"
                  class="w-full bg-white/5 border @error('alamat') border-red-500/50 @else text-gray-700 @enderror
                         text-gray-200 text-sm rounded-lg px-3 py-2.5 placeholder-gray-600
                         focus:outline-none focus:border-green-400/50 transition resize-none"
                  placeholder="Masukkan alamat lengkap">{{ old('alamat', $produsen->alamat ?? '') }}</textarea>
        @error('alamat')
            <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
        @enderror
    </div>

</div>