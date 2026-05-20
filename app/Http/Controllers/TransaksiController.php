<?php

namespace App\Http\Controllers;

use App\Models\Layanan;
use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TransaksiController extends Controller
{
    // ── Landing: Halaman Langganan (daftar layanan) ────────────
    // Sudah ada di LandingController->langganan(), tidak perlu duplikasi.
    // Controller ini fokus pada proses checkout & riwayat user.

    // ── Checkout: Buat transaksi & ambil Snap Token ────────────
    public function checkout(Request $request)
    {
        // Pastikan user sudah login
        if (! Auth::check()) {
            return redirect()->route('login')
                ->with('error', 'Silakan login terlebih dahulu untuk berlangganan.');
        }

        $request->validate([
            'layanan_id' => 'required|exists:layanan,layanan_id',
        ]);

        $user    = Auth::user();
        $layanan = Layanan::where('layanan_id', $request->layanan_id)
                          ->where('status', 'publish')
                          ->firstOrFail();

        // Cek apakah user sudah punya transaksi aktif untuk layanan ini
        $existing = Transaksi::where('user_id', $user->user_id)
                              ->where('layanan_id', $layanan->layanan_id)
                              ->where('status', 'success')
                              ->where(function ($q) {
                                  $q->whereNull('aktif_sampai')
                                    ->orWhere('aktif_sampai', '>=', now());
                              })->first();

        if ($existing) {
            return redirect()->route('transaksi.riwayat')
                ->with('error', 'Kamu sudah memiliki langganan aktif untuk layanan ini.');
        }

        // Buat order_id unik
        $orderId = Transaksi::generateOrderId($user->user_id);

        // Simpan transaksi dengan status pending
        $transaksi = Transaksi::create([
            'user_id'      => $user->user_id,
            'layanan_id'   => $layanan->layanan_id,
            'nama_layanan' => $layanan->nama_layanan,
            'harga'        => $layanan->harga,
            'durasi'       => $layanan->durasi,
            'durasi_type'  => $layanan->durasi_type,
            'order_id'     => $orderId,
            'status'       => 'pending',
        ]);

        // Request Snap Token ke Midtrans
        $snapToken = $this->getSnapToken($transaksi, $user, $layanan);

        if (! $snapToken) {
            $transaksi->update(['status' => 'failed']);
            return back()->with('error', 'Gagal menghubungi payment gateway. Coba lagi.');
        }

        $transaksi->update(['snap_token' => $snapToken]);

        // Tampilkan halaman checkout dengan Snap popup
        return view('pages.transaksi.checkout', compact('transaksi', 'layanan', 'snapToken'));
    }

    // ── Notification: Webhook dari Midtrans ───────────────────
    // URL ini harus bisa diakses publik (daftarkan di dashboard Midtrans)
    // Tidak perlu CSRF karena request dari server Midtrans
    public function notification(Request $request)
    {
        $payload = $request->all();

        Log::info('Midtrans Notification', $payload);

        // Verifikasi signature key untuk keamanan
        $orderId           = $payload['order_id'] ?? '';
        $statusCode        = $payload['status_code'] ?? '';
        $grossAmount       = $payload['gross_amount'] ?? '';
        $serverKey         = config('midtrans.server_key');
        $signatureKey      = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        if ($signatureKey !== ($payload['signature_key'] ?? '')) {
            Log::warning('Midtrans: Invalid signature', ['order_id' => $orderId]);
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $transaksi = Transaksi::where('order_id', $orderId)->first();

        if (! $transaksi) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        // Update payload mentah
        $transaksi->update(['midtrans_payload' => $payload]);

        $transactionStatus = $payload['transaction_status'] ?? '';
        $fraudStatus       = $payload['fraud_status'] ?? '';
        $paymentType       = $payload['payment_type'] ?? null;

        // Logika status Midtrans → status kita
        if ($transactionStatus === 'capture') {
            // Credit card: capture + fraud check
            $newStatus = ($fraudStatus === 'accept') ? 'success' : 'failed';
        } elseif ($transactionStatus === 'settlement') {
            $newStatus = 'success';
        } elseif (in_array($transactionStatus, ['deny', 'expire', 'failure'])) {
            $newStatus = 'failed';
        } elseif ($transactionStatus === 'cancel') {
            $newStatus = 'cancelled';
        } elseif ($transactionStatus === 'pending') {
            $newStatus = 'pending';
        } else {
            $newStatus = $transaksi->status; // tidak berubah
        }

        // Update transaksi
        $updateData = [
            'status'                   => $newStatus,
            'payment_type'             => $paymentType,
            'midtrans_transaction_id'  => $payload['transaction_id'] ?? null,
        ];

        // Jika berhasil, hitung masa aktif
        if ($newStatus === 'success' && ! $transaksi->isSuccess()) {
            $mulai = Carbon::now();
            $updateData['aktif_mulai']  = $mulai;
            $updateData['aktif_sampai'] = Transaksi::hitungAktifSampai(
                $transaksi->durasi_type,
                $transaksi->durasi,
                $mulai
            );
        }

        $transaksi->update($updateData);

        return response()->json(['message' => 'OK']);
    }

    // ── Status: Cek status transaksi (AJAX polling) ────────────
    public function status(Transaksi $transaksi)
    {
        // Pastikan hanya pemilik yang bisa cek
        if ($transaksi->user_id !== Auth::id()) {
            abort(403);
        }

        return response()->json([
            'status'       => $transaksi->status,
            'status_label' => match($transaksi->status) {
                'success'   => 'Pembayaran Berhasil',
                'pending'   => 'Menunggu Pembayaran',
                'failed'    => 'Pembayaran Gagal',
                'cancelled' => 'Dibatalkan',
                default     => $transaksi->status,
            },
        ]);
    }

    // ── Riwayat: Daftar transaksi milik user yang login ────────
    public function riwayat(Request $request)
    {
        $user = Auth::user();

        $query = Transaksi::where('user_id', $user->user_id)
                          ->with('layanan')
                          ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $transaksis = $query->paginate(10)->withQueryString();

        // Statistik ringkas untuk user
        $stats = [
            'total'    => Transaksi::where('user_id', $user->user_id)->count(),
            'aktif'    => Transaksi::where('user_id', $user->user_id)->success()
                            ->where(function ($q) {
                                $q->whereNull('aktif_sampai')
                                  ->orWhere('aktif_sampai', '>=', now());
                            })->count(),
            'pending'  => Transaksi::where('user_id', $user->user_id)->pending()->count(),
            'total_bayar' => Transaksi::where('user_id', $user->user_id)
                                ->success()->sum('harga'),
        ];

        return view('pages.transaksi.riwayat', compact('transaksis', 'stats'));
    }

    // ── Detail: Detail satu transaksi milik user ───────────────
    public function detail(Transaksi $transaksi)
    {
        if ($transaksi->user_id !== Auth::id()) {
            abort(403);
        }

        $transaksi->load('layanan');

        return view('pages.transaksi.detail', compact('transaksi'));
    }

    // ── Private: Request Snap Token ke Midtrans ────────────────
    private function getSnapToken(Transaksi $transaksi, $user, Layanan $layanan): ?string
    {
        $serverKey = config('midtrans.server_key');
        $isProduction = config('midtrans.is_production');

        $snapUrl = $isProduction
            ? 'https://app.midtrans.com/snap/v1/transactions'
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';

        $payload = [
            'transaction_details' => [
                'order_id'     => $transaksi->order_id,
                'gross_amount' => (int) $transaksi->harga,
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email'      => $user->email,
            ],
            'item_details' => [
                [
                    'id'       => 'LAYANAN-' . $layanan->layanan_id,
                    'price'    => (int) $layanan->harga,
                    'quantity' => 1,
                    'name'     => $layanan->nama_layanan . ' (' . $layanan->durasi_label . ')',
                ],
            ],
            'callbacks' => [
                'finish' => route('transaksi.riwayat'),
            ],
        ];

        try {
            $response = Http::withBasicAuth($serverKey, '')
                ->timeout(30)
                ->post($snapUrl, $payload);

            if ($response->successful()) {
                return $response->json('token');
            }

            Log::error('Midtrans Snap Error', [
                'status'   => $response->status(),
                'response' => $response->json(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Midtrans HTTP Exception: ' . $e->getMessage());
            return null;
        }
    }
}