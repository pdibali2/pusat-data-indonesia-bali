<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Layanan;
use App\Models\Klasifikasi;
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
        $this->allKlasifikasi = Klasifikasi::query()
            ->orderBy('nama_klasifikasi')
            ->pluck('nama_klasifikasi')
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
        $klasifikasiAktif = Klasifikasi::whereHas('metadata', function ($q) {
            $q->where('status', 2);
        })
        ->orderBy('nama_klasifikasi')
        ->take(10)
        ->get();

        // Statistik
        $jumlahData     = Data::where('status', 1)->count();
        $jumlahMetadata = Metadata::where('status', 2)->count();
        $jumlahProdusen = ProdusenData::count();

        // Produk unggulan: 6 metadata terbaru yang sudah aktif
        $produkUnggulan = Metadata::where('status', 2)
            ->latest('date_inputed')
            ->limit(6)
            ->with('klasifikasi')
            ->get([
                'metadata_id',
                'nama',
                'klasifikasi_id',
                'satuan_data',
                'frekuensi_penerbitan',
                'tahun_mulai_data'
            ]);

        // Paket layanan untuk section pricing di landing
        $pricings = Layanan::with('fiturs')
            ->where('status', 'publish')
            ->orderBy('urutan')
            ->orderBy('layanan_id')
            ->get();

        return view('pages.landing.index', compact(
            'klasifikasiAktif',
            'jumlahData',
            'jumlahMetadata',
            'jumlahProdusen',
            'produkUnggulan',
            'pricings',
        ));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HALAMAN LANGGANAN
    // ─────────────────────────────────────────────────────────────────────────

    public function langganan()
    {
        $layanans = Layanan::where('status', 'publish')
            ->with(['fiturs' => fn($q) => $q->orderBy('urutan')])
            ->orderBy('urutan')
            ->orderBy('harga')
            ->get();

        return view('pages.landing.langganan', compact('layanans'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HALAMAN DAFTAR KLASIFIKASI
    // ─────────────────────────────────────────────────────────────────────────

    public function klasifikasiIndex()
    {
        // Hitung jumlah metadata per klasifikasi
        $counts = Metadata::with('klasifikasi')
            ->where('status', 2)
            ->get()
            ->groupBy(fn ($m) => $m->klasifikasi?->nama_klasifikasi)
            ->map(fn ($items) => $items->count());

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

        $metadataList = Metadata::with('klasifikasi')
            ->where('status', 2)
            ->whereHas('klasifikasi', function ($q) use ($nama) {
                $q->where('nama_klasifikasi', $nama);
            })
            ->orderBy('nama')
            ->paginate(20);

        return view('pages.landing.klasifikasi.show', compact('nama', 'metadataList'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // AJAX: autocomplete / search metadata
    // - Mendukung filter per klasifikasi via ?klasifikasi=
    // - Mencari di seluruh DB, bukan hanya halaman saat ini
    // ─────────────────────────────────────────────────────────────────────────

    public function searchMetadata(Request $request)
    {
        $q           = trim($request->input('q', ''));
        $klasifikasi = trim($request->input('klasifikasi', ''));

        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $results = Metadata::where('status', 2)
            ->where('nama', 'like', "%{$q}%")
            ->when($klasifikasi, function ($query) use ($klasifikasi) {
                $query->whereHas('klasifikasi', fn($q) =>
                    $q->where('nama_klasifikasi', $klasifikasi)
                );
            })
            ->with('klasifikasi')
            ->select('metadata_id', 'nama', 'klasifikasi_id', 'satuan_data', 'frekuensi_penerbitan', 'tahun_mulai_data')
            ->limit(50)
            ->get();

        return response()->json(
            $results->map(fn($item) => [
                'metadata_id'          => $item->metadata_id,
                'nama'                 => $item->nama,
                'klasifikasi'          => $item->klasifikasi?->nama_klasifikasi,
                'satuan_data'          => $item->satuan_data,
                'frekuensi_penerbitan' => $item->frekuensi_penerbitan,
                'tahun_mulai_data'     => $item->tahun_mulai_data,
            ])
        );
    }

    public function dataShow(int $metadataId)
    {
        // ── 1. Load metadata (hanya yang sudah publish) ──────────────────────
        $metadata = Metadata::with(['klasifikasi', 'produsen'])
            ->where('status', 2)
            ->findOrFail($metadataId);
    
        // ── 2. Hitung rentang tahun ──────────────────────────────────────────
        //    5 tahun terakhir s.d. tahun lalu  (mis. 2026 → 2021–2025)
        $yearEnd   = now()->year - 1;          // 2025
        $yearStart = $yearEnd - 4;             // 2021
    
        // ── 3. Ambil data dalam rentang ──────────────────────────────────────
        //    Join ke tabel `time` untuk filter tahun
        $rawData = Data::with('time')
            ->where('metadata_id', $metadataId)
            ->where('status', 1)
            ->whereHas('time', fn($q) =>
                $q->whereBetween('year', [$yearStart, $yearEnd])
            )
            ->get();
    
        // ── 4. Bentuk baris tabel & series chart ─────────────────────────────
        //
        //    Strategi pengelompokan:
        //    - Jika ada kolom `period` / `label` di tabel time  → pakai itu
        //    - Kalau tidak, pakai "year" saja
        //    - Urutkan ascending lalu ambil nilai pertama per periode
        //    (sesuaikan groupBy-key dengan struktur model Time Anda)
    
        $grouped = $rawData
            ->sortBy(fn($d) => $d->time?->year)
            ->groupBy(fn($d) => $d->time?->year);  // group per year
    
        $tableRows   = [];   // untuk blade tabel
        $chartLabels = [];   // label sumbu-X
        $chartValues = [];   // nilai numeric
    
        foreach ($grouped as $year => $items) {
            // Ambil nilai pertama yang tidak null pada year itu
            $val = $items
                ->pluck('number_value')
                ->filter(fn($v) => !is_null($v))
                ->first();
    
            $period = (string) $year;
    
            $tableRows[]   = ['period' => $period, 'value' => $val, 'year' => $year];
            $chartLabels[] = $period;
            $chartValues[] = $val !== null ? (float) $val : null;
        }
    
        // Pastikan ada semua year (isi null kalau tidak ada data)
        $yearsWithData = array_column($tableRows, 'year');
        for ($y = $yearStart; $y <= $yearEnd; $y++) {
            if (!in_array($y, $yearsWithData)) {
                $tableRows[]   = ['period' => (string)$y, 'value' => null, 'year' => $y];
                $chartLabels[] = (string)$y;
                $chartValues[] = null;
            }
        }
    
        // Sort ulang ascending
        usort($tableRows, fn($a, $b) => $a['year'] <=> $b['year']);
        array_multisort(array_column($tableRows, 'year'), SORT_ASC, $tableRows);
    
        // Rebuild labels & values sesuai urutan akhir
        $chartLabels = array_column($tableRows, 'period');
        $chartValues = array_map(fn($r) => $r['value'] !== null ? (float)$r['value'] : null, $tableRows);
    
        // Wrap ke Collection biar bisa pakai ->pluck(), ->max(), dll. di blade
        $tableRows = collect($tableRows);
    
        return view('pages.landing.data_show', compact(
            'metadata',
            'tableRows',
            'chartLabels',
            'chartValues',
            'yearStart',
            'yearEnd',
        ));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PRIVATE: produk unggulan berdasarkan year terakhir yang ada datanya
    // ─────────────────────────────────────────────────────────────────────────

    private function getProdukUnggulan(): array
    {
        $targetYear = now()->year - 1;
        $minYear    = 2000;

        while ($targetYear >= $minYear) {
            $metadata = \App\Models\Metadata::query()
                ->with(['klasifikasi'])
                ->whereHas('data', function ($q) use ($targetYear) {
                    $q->available()
                      ->whereHas('time', function ($qt) use ($targetYear) {
                          $qt->where('year', $targetYear);
                      });
                })
                ->take(6)
                ->get();

            if ($metadata->isNotEmpty()) {
                return [
                    'produkUnggulan' => $metadata,
                    'tahunData'      => $targetYear,
                ];
            }

            $targetYear--;
        }

        return [
            'produkUnggulan' => collect(),
            'tahunData'      => null,
        ];
    }
}