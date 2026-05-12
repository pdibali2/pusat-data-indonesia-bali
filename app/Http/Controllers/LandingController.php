<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Metadata;
use App\Models\Data;
use App\Models\ProdusenData;
use Illuminate\Support\Str;

class LandingController extends Controller
{
    /** Daftar semua klasifikasi yang diakui sistem */
    private $allKlasifikasi;

    public function __construct()
    {
        $this->allKlasifikasi = Metadata::query()
            ->whereNotNull('klasifikasi')
            ->distinct()
            ->orderBy('klasifikasi')
            ->pluck('klasifikasi')
            ->map(fn ($k) => trim((string) $k))
            ->filter(function ($k) {
                return $k !== ''
                    && $k !== '-'
                    && Str::slug($k) !== '';
            })
            ->values();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // LANDING PAGE
    // ─────────────────────────────────────────────────────────────────────────

    public function index()
    {
        // Klasifikasi yang benar-benar ada data-nya (maks 10 untuk badges di hero)
        $klasifikasiAktif = Metadata::where('status', 2)
            ->select('klasifikasi')
            ->distinct()
            ->pluck('klasifikasi')
            ->map(fn ($k) => trim((string) $k))
            ->filter(function ($k) {
                return $k !== ''
                    && $k !== '-'
                    && Str::slug($k) !== '';
            })
            ->take(10)
            ->values();

        // Statistik
        $jumlahData     = Data::where('status', 1)->count();
        $jumlahMetadata = Metadata::where('status', 2)->count();
        $jumlahProdusen = ProdusenData::count();

        // Produk unggulan: 6 metadata terbaru yang sudah aktif
        $produkUnggulan = Metadata::where('status', 2)
            ->latest('date_inputed')
            ->limit(6)
            ->get(['metadata_id', 'nama', 'klasifikasi', 'satuan_data', 'frekuensi_penerbitan', 'tahun_mulai_data']);

        return view('pages.landing.index', compact(
            'klasifikasiAktif',
            'jumlahData',
            'jumlahMetadata',
            'jumlahProdusen',
            'produkUnggulan',
        ));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HALAMAN LANGGANAN
    // ─────────────────────────────────────────────────────────────────────────

    public function langganan()
    {
        return view('pages.landing.langganan');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HALAMAN DAFTAR KLASIFIKASI
    // ─────────────────────────────────────────────────────────────────────────

    public function klasifikasiIndex()
    {
        // Hitung jumlah metadata per klasifikasi
        $counts = Metadata::where('status', 2)
            ->selectRaw('klasifikasi, count(*) as total')
            ->groupBy('klasifikasi')
            ->pluck('total', 'klasifikasi');

        $klasifikasiList = collect($this->allKlasifikasi)
            ->map(function ($k) use ($counts) {

                $slug = Str::slug($k);

                if (!$slug) {
                    return null;
                }

                return [
                    'nama'  => $k,
                    'slug'  => $slug,
                    'total' => $counts->get($k, 0),
                ];
            })
            ->filter()
            ->values();

        return view('pages.landing.klasifikasi.index', compact('klasifikasiList'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HALAMAN DETAIL KLASIFIKASI → daftar metadata
    // ─────────────────────────────────────────────────────────────────────────

    public function klasifikasiShow(string $klasifikasi)
    {
        // Cocokkan slug ke nama asli
        $nama = collect($this->allKlasifikasi)
            ->first(fn($k) => Str::slug($k) === $klasifikasi);

        abort_if(is_null($nama), 404);

        $metadataList = Metadata::where('status', 2)
            ->where('klasifikasi', $nama)
            ->orderBy('nama')
            ->paginate(20);

        return view('pages.landing.klasifikasi.show', compact('nama', 'metadataList'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // AJAX: autocomplete metadata (status = 2, maks 5)
    // ─────────────────────────────────────────────────────────────────────────

    public function searchMetadata(Request $request)
    {
        $q = trim($request->input('q', ''));

        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $results = Metadata::where('status', 2)
            ->where(function ($query) use ($q) {
                $query->where('nama', 'like', "{$q}%");
            })
            ->select('metadata_id', 'nama', 'klasifikasi')
            ->limit(20)
            ->get();

        return response()->json($results);
    }
}