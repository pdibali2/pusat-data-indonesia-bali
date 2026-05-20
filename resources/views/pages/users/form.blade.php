<div class="page-layout">

    {{-- Nama --}}
    <div>
        <label class="block text-xs font-semibold text-gray-400 mb-1.5">
            Nama Lengkap <span class="text-red-400">*</span>
        </label>
        <input type="text" name="name" value="{{ old('name', $user->name ?? '') }}"
               class="w-full bg-white/5 border @error('name') border-red-500/50 @else border-white/10 @enderror
                      text-gray-200 text-sm rounded-lg px-3 py-2.5 placeholder-gray-600
                      focus:outline-none focus:border-green-400/50 transition"
               placeholder="Masukkan nama lengkap">
        @error('name')
            <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
        @enderror
    </div>

    {{-- Username --}}
    <div>
        <label class="block text-xs font-semibold text-gray-400 mb-1.5">
            Username <span class="text-red-400">*</span>
        </label>
        <input type="text" name="username" value="{{ old('username', $user->username ?? '') }}"
               class="w-full bg-white/5 border @error('username') border-red-500/50 @else border-white/10 @enderror
                      text-gray-200 text-sm rounded-lg px-3 py-2.5 placeholder-gray-600
                      focus:outline-none focus:border-green-400/50 transition"
               placeholder="Masukkan username">
        @error('username')
            <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
        @enderror
    </div>

    {{-- Email --}}
    <div>
        <label class="block text-xs font-semibold text-gray-400 mb-1.5">
            Email <span class="text-red-400">*</span>
        </label>
        <input type="email" name="email" value="{{ old('email', $user->email ?? '') }}"
               class="w-full bg-white/5 border @error('email') border-red-500/50 @else border-white/10 @enderror
                      text-gray-200 text-sm rounded-lg px-3 py-2.5 placeholder-gray-600
                      focus:outline-none focus:border-green-400/50 transition"
               placeholder="Masukkan email">
        @error('email')
            <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
        @enderror
    </div>

    {{-- Group --}}
    <div>
        <label class="block text-xs font-semibold text-gray-400 mb-1.5">
            Group <span class="text-red-400">*</span>
        </label>
        <select name="group_id"
                class="w-full bg-white/5 border @error('group_id') border-red-500/50 @else border-white/10 @enderror
                       text-gray-200 text-sm rounded-lg px-3 py-2.5
                       focus:outline-none focus:border-green-400/50 transition">
            <option value="">-- Pilih Group --</option>
            @foreach ($groups as $group)
                <option value="{{ $group->group_id }}"
                    {{ old('group_id', $user->group_id ?? '') == $group->group_id ? 'selected' : '' }}>
                    {{ $group->title }}
                </option>
            @endforeach
        </select>
        @error('group_id')
            <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
        @enderror
    </div>

    {{-- Password --}}
    <div>
        <label class="block text-xs font-semibold text-gray-400 mb-1.5">
            Password
            @isset($user)
                <span class="text-gray-600 font-normal">(kosongkan jika tidak diubah)</span>
            @else
                <span class="text-red-400">*</span>
            @endisset
        </label>
        <input type="password" name="password"
               class="w-full bg-white/5 border @error('password') border-red-500/50 @else border-white/10 @enderror
                      text-gray-200 text-sm rounded-lg px-3 py-2.5 placeholder-gray-600
                      focus:outline-none focus:border-green-400/50 transition"
               placeholder="{{ isset($user) ? 'Biarkan kosong jika tidak diubah' : 'Masukkan password' }}">
        @error('password')
            <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
        @enderror
    </div>

    {{-- Konfirmasi Password --}}
    <div>
        <label class="block text-xs font-semibold text-gray-400 mb-1.5">
            Konfirmasi Password
            @unless(isset($user))
                <span class="text-red-400">*</span>
            @endunless
        </label>
        <input type="password" name="password_confirmation"
               class="w-full bg-white/5 border border-white/10 text-gray-200 text-sm rounded-lg px-3 py-2.5
                      placeholder-gray-600 focus:outline-none focus:border-green-400/50 transition"
               placeholder="Ulangi password">
    </div>

</div>