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
            ->whereHas('metadata', function ($q) {
                $q->where('status', 2)
                ->whereHas('data', fn($qd) => $qd->where('status', 1)->where('location_id', 0));
            })
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
            $q->where('status', 2)
            ->whereHas('data', fn($qd) => $qd->where('status', 1)->where('location_id', 0));
        })
        ->orderBy('nama_klasifikasi')
        ->take(10)
        ->get();

        // Statistik
        $jumlahData     = Data::where('status', 1)->count();
        $jumlahMetadata = Metadata::where('status', 2)->count();
        $jumlahProdusen = ProdusenData::count();

        // Produk unggulan: hanya metadata yang benar-benar punya data
        $produkUnggulan = Metadata::query()
            ->where('status', 2)

            // hanya metadata yang memiliki data aktif
            ->whereHas('data', function ($q) {
                $q->where('status', 1)->where('location_id', 0);
            })

            ->with([
                'klasifikasi',
                'data' => function ($q) {
                    $q->where('status', 1)->where('location_id', 0)->with('location')->limit(1);
                }
            ])

            ->orderBy('date_inputed', 'desc')
            ->orderBy('metadata_id', 'desc')
            ->limit(3)
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
        $counts = Metadata::with('klasifikasi')
            ->where('status', 2)
            ->whereHas('data', fn($q) => $q->where('status', 1)->where('location_id', 0))
            ->get()
            ->groupBy(fn ($m) => $m->klasifikasi?->nama_klasifikasi)
            ->map(fn ($items) => $items->count());

        $klasifikasiList = collect($this->allKlasifikasi)
            ->map(function ($k) use ($counts) {
                $slug = Str::slug($k);
                if (!$slug) return null;
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
        $nama = Klasifikasi::orderBy('nama_klasifikasi')
            ->pluck('nama_klasifikasi')
            ->map(fn($k) => trim((string) $k))
            ->filter(fn($k) => $k !== '' && $k !== '-' && Str::slug($k) !== '')
            ->first(fn($k) => Str::slug($k) === $klasifikasi);

        abort_if(is_null($nama), 404);

        $metadataList = Metadata::with('klasifikasi')
            ->where('status', 2)
            ->whereHas('data', fn($q) => $q->where('status', 1)->where('location_id', 0))  // ← tambahan
            ->whereHas('klasifikasi', function ($q) use ($nama) {
                $q->where('nama_klasifikasi', $nama);
            })
            ->orderBy('nama')
            ->paginate(10);

            $freeLimit       = 3;
            $pageStartIndex  = ($metadataList->currentPage() - 1) * 10 + 1;
            $freeCountOnPage = max(0, min(10, $freeLimit - $pageStartIndex + 1));

        return view('pages.landing.klasifikasi.show', compact('nama', 'metadataList', 'freeLimit', 'freeCountOnPage'));
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

    public function dataSeries(Request $request)
    {
        $perPage = (int) $request->input('per_page', 12);
        $perPage = in_array($perPage, [12, 24, 48]) ? $perPage : 12;

        $query = Metadata::with([
            'klasifikasi',
            'data' => fn($q) => $q->where('status', 1)->where('location_id', 0)->with('location')->limit(1),
        ])
        ->where('status', 2)
        ->whereHas('data', fn($q) => $q->where('status', 1)->where('location_id', 0));

        if ($q = trim($request->input('q', ''))) {
            $query->where(function ($qb) use ($q) {
                $qb->where('nama', 'like', "%{$q}%")
                ->orWhere('alias', 'like', "%{$q}%")
                ->orWhere('tag', 'like', "%{$q}%");
            });
        }

        if ($klas = trim($request->input('klasifikasi', ''))) {
            $query->whereHas('klasifikasi', fn($qb) =>
                $qb->where('nama_klasifikasi', $klas)
            );
        }

        if ($freq = trim($request->input('frekuensi', ''))) {
            $query->where('frekuensi_penerbitan', $freq);
        }

        if ($tipe = trim($request->input('tipe', ''))) {
            $query->where('tipe_data', $tipe);
        }

        match ($request->input('sort', 'terbaru')) {
            'az'    => $query->orderBy('nama', 'asc'),
            'za'    => $query->orderBy('nama', 'desc'),
            default => $query->orderBy('date_inputed', 'desc')->orderBy('metadata_id', 'desc'),
        };

        $metadataList = $query->paginate($perPage)->withQueryString();

        // ── Freemium: hitung batas 30% dari TOTAL keseluruhan ──────────────────
        // Pakai total query yang sama (tanpa paginate) agar konsisten lintas halaman
        $totalAll = (clone $query)->count(); // total setelah filter diterapkan
        // Kalau tidak ada filter aktif, pakai total semua metadata aktif
        $totalForLimit = Metadata::where('status', 2)->count();
        $freeLimit = 3;

        // Index global item pertama di halaman ini (1-based)
        $pageStartIndex = ($metadataList->currentPage() - 1) * $perPage + 1;

        // Berapa item di halaman ini yang masih dalam zona free?
        // freeCountOnPage = max(0, min(perPage, freeLimit - pageStartIndex + 1))
        $freeCountOnPage = max(0, min($perPage, $freeLimit - $pageStartIndex + 1));

        $klasifikasiList = Klasifikasi::orderBy('nama_klasifikasi')
            ->pluck('nama_klasifikasi')->filter()->values();

        $frekuensiList = Metadata::where('status', 2)
            ->whereNotNull('frekuensi_penerbitan')->distinct()
            ->orderBy('frekuensi_penerbitan')->pluck('frekuensi_penerbitan')->filter();

        $tipeList = Metadata::where('status', 2)
            ->whereNotNull('tipe_data')->distinct()
            ->orderBy('tipe_data')->pluck('tipe_data')->filter();

        $totalMetadata    = Metadata::where('status', 2)->count();
        $totalKlasifikasi = Klasifikasi::count();
        $totalProdusen    = ProdusenData::count();

        return view('pages.landing.data_series', compact(
            'metadataList',
            'klasifikasiList',
            'frekuensiList',
            'tipeList',
            'totalMetadata',
            'totalKlasifikasi',
            'totalProdusen',
            'freeLimit',         // ← baru
            'freeCountOnPage',   // ← baru
        ));
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
        $rawData = Data::with(['time', 'location', 'rujukan.produsen'])
            ->where('metadata_id', $metadataId)
            ->where('status', 1)
            ->where('location_id', 0)
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
    
        $firstData = $rawData->first();
        $rujukan   = $firstData?->rujukan;

        return view('pages.landing.data_show', compact(
            'metadata',
            'tableRows',
            'chartLabels',
            'chartValues',
            'yearStart',
            'yearEnd',
            'rujukan',
        ));
    }
}