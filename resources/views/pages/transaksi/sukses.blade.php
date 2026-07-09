@extends('layouts.main')

@section('title', 'Pembayaran Berhasil')

@section('content')
@php
    $transaksi->loadMissing('layanan');
    $redirectUrl = route('transaksi.riwayat');
@endphp
<style>
@keyframes scaleIn {
    from { transform: scale(0.5); opacity: 0; }
    to   { transform: scale(1);   opacity: 1; }
}
@keyframes fadeUp {
    from { transform: translateY(20px); opacity: 0; }
    to   { transform: translateY(0);    opacity: 1; }
}
@keyframes ripple {
    0%   { transform: scale(1);   opacity: 0.6; }
    100% { transform: scale(2.8); opacity: 0; }
}
@keyframes checkDraw {
    from { stroke-dashoffset: 60; }
    to   { stroke-dashoffset: 0; }
}
@keyframes confettiFall {
    0%   { transform: translateY(-10px) rotate(0deg);   opacity: 1; }
    100% { transform: translateY(120px) rotate(360deg); opacity: 0; }
}

.ripple-ring {
    animation: ripple 1.8s ease-out infinite;
}
.ripple-ring:nth-child(2) { animation-delay: 0.6s; }
.ripple-ring:nth-child(3) { animation-delay: 1.2s; }

.check-icon  { animation: scaleIn 0.5s cubic-bezier(0.34,1.56,0.64,1) 0.3s both; }
.check-path  { stroke-dasharray: 60; animation: checkDraw 0.5s ease 0.7s both; }
.card-anim   { animation: fadeUp 0.5s ease 0.5s both; }
.detail-anim { animation: fadeUp 0.5s ease 0.7s both; }
.btn-anim    { animation: fadeUp 0.5s ease 0.9s both; }

.confetti-item {
    position: absolute;
    width: 8px; height: 8px;
    border-radius: 2px;
    animation: confettiFall 1.5s ease-in forwards;
}
</style>

<div class="min-h-[80vh] flex items-center justify-center px-4 py-10">
    <div class="w-full max-w-md">

        {{-- Confetti --}}
        <div id="confetti-container" class="relative pointer-events-none" style="height:0">
            {{-- JS akan inject confetti di sini --}}
        </div>

        {{-- Card Sukses --}}
        <div class="card-anim bg-white rounded-3xl shadow-xl overflow-hidden">

            {{-- Header hijau --}}
            <div class="relative bg-gradient-to-br from-emerald-500 to-teal-600 pt-10 pb-16 flex flex-col items-center">

                {{-- Ripple rings --}}
                <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                    <div class="ripple-ring absolute w-24 h-24 rounded-full border-2 border-white/30"></div>
                    <div class="ripple-ring absolute w-24 h-24 rounded-full border-2 border-white/20"></div>
                    <div class="ripple-ring absolute w-24 h-24 rounded-full border-2 border-white/10"></div>
                </div>

                {{-- Check icon --}}
                <div class="check-icon relative z-10 w-20 h-20 rounded-full bg-white/20 border-4 border-white flex items-center justify-center shadow-lg">
                    <svg width="36" height="36" viewBox="0 0 36 36" fill="none">
                        <path class="check-path"
                              d="M8 18 L15 25 L28 11"
                              stroke="white" stroke-width="3.5"
                              stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>

                <h2 class="mt-4 text-white font-bold text-xl tracking-tight relative z-10">Pembayaran Berhasil!</h2>
                <p class="text-white/80 text-sm mt-1 relative z-10">Langgananmu sudah aktif</p>
            </div>

            {{-- Curved separator --}}
            <div class="relative -mt-6">
                <svg viewBox="0 0 400 30" class="w-full" preserveAspectRatio="none" style="height:30px">
                    <path d="M0,0 Q200,30 400,0 L400,30 L0,30 Z" fill="white"/>
                </svg>
            </div>

            {{-- Detail transaksi --}}
            <div class="detail-anim px-6 pb-2 -mt-2 space-y-3">

                <div class="bg-gray-50 rounded-2xl p-4 space-y-2.5 text-sm">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500">Order ID</span>
                        <span class="font-mono text-xs text-gray-700 bg-gray-200 px-2 py-0.5 rounded">
                            {{ $transaksi->order_id }}
                        </span>
                    </div>
                    <div class="border-t border-gray-200"></div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500">Layanan</span>
                        <span class="font-semibold text-gray-800">{{ $transaksi->nama_layanan }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500">Durasi</span>
                        <span class="text-gray-700">{{ $transaksi->durasi_label }}</span>
                    </div>
                    @if($transaksi->payment_type)
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500">Metode</span>
                        <span class="text-gray-700">{{ strtoupper(str_replace('_', ' ', $transaksi->payment_type)) }}</span>
                    </div>
                    @endif
                    @if($transaksi->aktif_sampai)
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500">Aktif Hingga</span>
                        <span class="font-semibold text-emerald-600">{{ $transaksi->aktif_sampai->format('d M Y') }}</span>
                    </div>
                    @else
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500">Masa Aktif</span>
                        <span class="font-semibold text-emerald-600">Selamanya</span>
                    </div>
                    @endif
                    <div class="border-t border-gray-200"></div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500 font-medium">Total Dibayar</span>
                        <span class="text-lg font-bold text-emerald-600">{{ $transaksi->harga_format }}</span>
                    </div>
                </div>

                {{-- Tanggal --}}
                <p class="text-center text-xs text-gray-400">
                    {{ $transaksi->updated_at->format('d M Y, H:i') }} WITA
                </p>
            </div>

            {{-- Tombol --}}
            <div class="btn-anim px-6 py-5 flex flex-col gap-2.5">
                <a href="{{ $redirectUrl }}"
                   class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl transition text-center shadow-md shadow-emerald-500/30">
                    <i class="fas fa-list-alt mr-1.5"></i> Lihat Riwayat Transaksi
                </a>
                <a href="{{ route('langganan') }}"
                   class="w-full py-3 bg-gray-100 hover:bg-gray-200 text-gray-600 text-sm font-medium rounded-xl transition text-center">
                    <i class="fas fa-store mr-1.5"></i> Kembali ke Layanan
                </a>
            </div>

        </div>

        {{-- Auto redirect note --}}
        <p class="text-center text-xs text-gray-400 mt-4">
            Otomatis dialihkan ke riwayat dalam <span id="countdown" class="font-semibold text-gray-600">10</span> detik
        </p>

    </div>
</div>

@push('scripts')
<script>
// ── Confetti ──────────────────────────────────────────────────
(function() {
    const colors = ['#10b981','#3b82f6','#f59e0b','#ef4444','#8b5cf6','#ec4899','#06b6d4'];
    const container = document.getElementById('confetti-container');

    for (let i = 0; i < 30; i++) {
        const el = document.createElement('div');
        el.className = 'confetti-item';
        el.style.cssText = `
            left: ${Math.random() * 100}%;
            top: 0;
            background: ${colors[Math.floor(Math.random() * colors.length)]};
            animation-delay: ${Math.random() * 1.5}s;
            animation-duration: ${1 + Math.random()}s;
            transform: rotate(${Math.random() * 360}deg);
            width: ${6 + Math.random() * 6}px;
            height: ${6 + Math.random() * 6}px;
        `;
        container.appendChild(el);
    }
})();

// ── Countdown redirect ────────────────────────────────────────
let count = 10;
const countEl = document.getElementById('countdown');
const timer = setInterval(() => {
    count--;
    countEl.textContent = count;
    if (count <= 0) {
        clearInterval(timer);
        window.location.href = @json($redirectUrl);
    }
}, 1000);
</script>
@endpush

@endsection