{{-- resources/views/pages/groups/form.blade.php --}}
<div>
    <label class="block text-xs font-semibold text-gray-400 mb-1.5">
        Nama Group <span class="text-red-400">*</span>
    </label>
    <input type="text" name="title" value="{{ old('title', $group->title ?? '') }}"
           class="w-full bg-white/5 border @error('title') border-red-500/50 @else border-white/10 @enderror
                  text-gray-200 text-sm rounded-lg px-3 py-2.5 placeholder-gray-600
                  focus:outline-none focus:border-green-400/50 transition"
           placeholder="Masukkan nama group">
    @error('title')
        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
    @enderror
</div>