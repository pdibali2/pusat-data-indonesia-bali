<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/js/app.ts', 'resources/css/app.css'])
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Menunggu Konfirmasi Login</title>
</head>
<body class="min-h-screen bg-slate-50 flex items-center justify-center px-4">
    <div class="max-w-xl w-full rounded-3xl bg-white shadow-xl p-8">
        <div class="text-center">
            <h1 class="text-2xl font-semibold text-slate-900">Menunggu konfirmasi dari perangkat aktif lainnya...</h1>
            <p class="mt-3 text-slate-600">Permintaan login Anda sedang menunggu keputusan. Silakan tunggu beberapa saat.</p>
        </div>

        <div class="mt-8 rounded-2xl border border-slate-200 bg-slate-50 p-6">
            <div class="space-y-3 text-sm text-slate-700">
                <div>
                    <span class="font-semibold">Perangkat:</span>
                    <span>{{ $pendingLogin->device_info }}</span>
                </div>
                <div>
                    <span class="font-semibold">Lokasi:</span>
                    <span>{{ $pendingLogin->estimated_location ?? 'Lokasi tidak diketahui' }}</span>
                </div>
                <div>
                    <span class="font-semibold">Waktu:</span>
                    <span>{{ $pendingLogin->created_at?->format('Y-m-d H:i:s') }}</span>
                </div>
            </div>
        </div>

        <div class="mt-8 text-center text-slate-500">
            <p id="statusMessage">Menunggu konfirmasi ...</p>
        </div>
    </div>

    <script>
        const pendingId = {{ $pendingLogin->id }};
        const polling = setInterval(async () => {
            try {
                const response = await fetch(`/session/pending-login/${pendingId}/status`, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await response.json();

                document.getElementById('statusMessage').textContent = data.message;

                if (data.status === 'approved') {
                    clearInterval(polling);
                    window.location.href = data.redirect || '/data';
                }

                if (data.status === 'rejected' || data.status === 'expired') {
                    clearInterval(polling);
                    setTimeout(() => {
                        window.location.href = '/login';
                    }, 2200);
                }
            } catch (error) {
                console.error(error);
            }
        }, 3500);
    </script>
</body>
</html>
