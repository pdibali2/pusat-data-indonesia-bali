@if (session('success'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
         class="flex items-center gap-3 bg-green-500/10 border border-green-500/30 text-green-400 text-sm px-4 py-3 rounded-lg mb-4">
        <i class="fas fa-check-circle text-green-400"></i>
        <span>{{ session('success') }}</span>
        <button @click="show = false" class="ml-auto text-green-400 hover:text-green-200">
            <i class="fas fa-times text-xs"></i>
        </button>
    </div>
@endif

@if (session('error'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
         class="flex items-center gap-3 bg-red-500/10 border border-red-500/30 text-red-400 text-sm px-4 py-3 rounded-lg mb-4">
        <i class="fas fa-exclamation-circle text-red-400"></i>
        <span>{{ session('error') }}</span>
        <button @click="show = false" class="ml-auto text-red-400 hover:text-red-200">
            <i class="fas fa-times text-xs"></i>
        </button>
    </div>
@endif