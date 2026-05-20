<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use Illuminate\Http\Request;

class AdminTransaksiController extends Controller
{
    // ── Dashboard Transaksi Admin ──────────────────────────────
    public function dashboard()
    {
        // Statistik utama
        $stats = [
            'total_transaksi' => Transaksi::count(),
            'total_pendapatan' => Transaksi::success()->sum('harga'),
            'transaksi_pending' => Transaksi::pending()->count(),
            'transaksi_sukses'  => Transaksi::success()->count(),
            'transaksi_gagal'   => Transaksi::failed()->orWhere('status', 'cancelled')->count(),
            'pelanggan_aktif'   => Transaksi::success()
                ->where(function ($q) {
                    $q->whereNull('aktif_sampai')
                      ->orWhere('aktif_sampai', '>=', now());
                })->distinct('user_id')->count('user_id'),
        ];

        // Pendapatan per bulan (12 bulan terakhir)
        $pendapatanBulanan = Transaksi::success()
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as bulan, SUM(harga) as total")
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get();

        // Transaksi terbaru (10)
        $transaksiTerbaru = Transaksi::with(['user', 'layanan'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        // Distribusi per layanan
        $perLayanan = Transaksi::success()
            ->selectRaw('nama_layanan, COUNT(*) as jumlah, SUM(harga) as total')
            ->groupBy('nama_layanan')
            ->orderByDesc('jumlah')
            ->get();

        // Distribusi per metode pembayaran
        $perMetode = Transaksi::success()
            ->selectRaw('payment_type, COUNT(*) as jumlah')
            ->whereNotNull('payment_type')
            ->groupBy('payment_type')
            ->orderByDesc('jumlah')
            ->get();

        return view('pages.admin.transaksi.dashboard', compact(
            'stats',
            'pendapatanBulanan',
            'transaksiTerbaru',
            'perLayanan',
            'perMetode'
        ));
    }

    // ── Index: Daftar Semua Transaksi ─────────────────────────
    public function index(Request $request)
    {
        $query = Transaksi::with(['user', 'layanan'])
                          ->orderByDesc('created_at');

        // Filter pencarian
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('order_id', 'like', "%{$s}%")
                  ->orWhere('nama_layanan', 'like', "%{$s}%")
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$s}%")
                                                    ->orWhere('email', 'like', "%{$s}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('layanan_id')) {
            $query->where('layanan_id', $request->layanan_id);
        }

        if ($request->filled('dari')) {
            $query->whereDate('created_at', '>=', $request->dari);
        }

        if ($request->filled('sampai')) {
            $query->whereDate('created_at', '<=', $request->sampai);
        }

        $transaksis = $query->paginate(15)->withQueryString();

        // Untuk dropdown filter layanan
        $layanans = \App\Models\Layanan::orderBy('nama_layanan')->get(['layanan_id', 'nama_layanan']);

        return view('pages.admin.transaksi.index', compact('transaksis', 'layanans'));
    }

    // ── Show: Detail satu transaksi ───────────────────────────
    public function show(Transaksi $transaksi)
    {
        $transaksi->load(['user', 'layanan']);

        return view('pages.admin.transaksi.show', compact('transaksi'));
    }
}