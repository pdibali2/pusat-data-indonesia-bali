<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Checkout — Pusat Data Indonesia Bali</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon"/>
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    @vite(['resources/css/app.css', 'resources/js/app.ts'])
    {{-- Midtrans Snap JS (sandbox) --}}
    <script src="https://app.sandbox.midtrans.com/snap/snap.js"
            data-client-key="{{ config('midtrans.client_key') }}"></script>
</head>
<body class="bg-slate-50 text-gray-900 antialiased">

    @include('pages.landing.components.navbar')

    <main class="min-h-screen py-16">
        <div class="max-w-xl mx-auto px-4">

            {{-- Header --}}
            <div class="flex items-center gap-3 mb-8">
                <div class="w-0.5 h-7 bg-stikom-core shrink-0"></div>
                <div>
                    <h1 class="text-xl font-black text-stikom font-display">Konfirmasi Pesanan</h1>
                    <p class="text-xs text-gray-400 font-body mt-0.5">Periksa detail langganan sebelum membayar</p>
                </div>
            </div>

            {{-- Order Summary Card --}}
            <div class="bg-white border border-gray-100 shadow-sm mb-4 overflow-hidden">

                {{-- Top bar --}}
                <div class="bg-stikom px-5 py-3 flex items-center justify-between">
                    <span class="text-[10px] font-bold text-stikom-core uppercase tracking-widest font-display">
                        Ringkasan Pesanan
                    </span>
                    <span class="text-[10px] text-white/40 font-mono">{{ $transaksi->order_id }}</span>
                </div>

                <div class="p-6">
                    {{-- Layanan --}}
                    <div class="flex items-start gap-4 mb-6">
                        <div class="w-10 h-10 bg-stikom flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-stikom-core" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-black text-stikom font-display text-base">{{ $layanan->nama_layanan }}</p>
                            <p class="text-xs text-gray-400 mt-0.5 font-body">Durasi: {{ $layanan->durasi_label }}</p>
                        </div>
                    </div>

                    {{-- Fitur --}}
                    @if($layanan->fiturs->isNotEmpty())
                    <ul class="space-y-2 mb-6 border-t border-gray-50 pt-4">
                        @foreach($layanan->fiturs as $fitur)
                        <li class="flex items-center gap-2.5 text-sm font-body
                                   {{ $fitur->aktif ? 'text-gray-700' : 'text-gray-300' }}">
                            @if($fitur->aktif)
                                <div class="w-4 h-4 bg-stikom-core flex items-center justify-center shrink-0">
                                    <svg class="w-2.5 h-2.5" fill="none" stroke="white" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                            @else
                                <div class="w-4 h-4 border border-gray-200 flex items-center justify-center shrink-0">
                                    <svg class="w-2.5 h-2.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </div>
                            @endif
                            {{ $fitur->nama_fitur }}
                        </li>
                        @endforeach
                    </ul>
                    @endif

                    {{-- Total --}}
                    <div class="border-t border-gray-100 pt-4 flex items-center justify-between">
                        <span class="text-sm text-gray-500 font-body">Total Pembayaran</span>
                        <span class="text-2xl font-black text-stikom font-display">{{ $layanan->harga_format }}</span>
                    </div>
                </div>
            </div>

            {{-- Note --}}
            <p class="text-xs text-gray-400 text-center mb-6 font-body">
                Pembayaran diproses secara aman melalui Midtrans.<br>
                Data kartu kamu tidak disimpan di sistem kami.
            </p>

            {{-- Pay Button --}}
            <button id="pay-button"
                    class="w-full py-4 bg-stikom-core hover:bg-green-400 text-stikom font-black
                           text-sm tracking-wide transition-colors duration-200 font-display
                           flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
                Bayar Sekarang — {{ $layanan->harga_format }}
            </button>

            <a href="{{ route('langganan') }}"
               class="block text-center mt-4 text-xs text-gray-400 hover:text-gray-600 transition font-body">
                ← Kembali ke halaman langganan
            </a>

        </div>
    </main>

    @include('pages.landing.components.footer')

    <script>
    const snapToken = @json($snapToken);
    const statusUrl = @json(route('transaksi.status', $transaksi->transaksi_id));
    const riwayatUrl = @json(route('transaksi.riwayat'));

    document.getElementById('pay-button').addEventListener('click', function () {
        snap.pay(snapToken, {
            onSuccess: function(result) {
                // Midtrans sudah kirim webhook, arahkan ke riwayat
                window.location.href = riwayatUrl + '?paid=1';
            },
            onPending: function(result) {
                window.location.href = riwayatUrl + '?pending=1';
            },
            onError: function(result) {
                alert('Pembayaran gagal. Silakan coba lagi.');
                console.error(result);
            },
            onClose: function() {
                // User menutup popup tanpa bayar — biarkan saja di halaman ini
            }
        });
    });
    </script>

</body>
</html>