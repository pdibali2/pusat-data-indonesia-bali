<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Layanan;
use App\Models\Klasifikasi;
use App\Models\Metadata;
use App\Models\Data;
use App\Models\Transaksi;
use App\Models\ProdusenData;
use Illuminate\Support\Facades\DB;
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

        // ── Rentang tahun untuk produk unggulan ────────────────────────────────
        $unggulanIds = $produkUnggulan->pluck('metadata_id')->all();

        $yearRangesUnggulan = DB::table('data')
            ->join('time', 'data.time_id', '=', 'time.time_id')
            ->whereIn('data.metadata_id', $unggulanIds)
            ->where('data.status', 1)
            ->whereNotNull('data.number_value')
            ->groupBy('data.metadata_id')
            ->selectRaw('data.metadata_id, MIN(time.year) as min_year, MAX(time.year) as max_year')
            ->get()
            ->keyBy('metadata_id')
            ->map(fn($r) => $r->min_year . '-' . $r->max_year);
            
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
            'yearRangesUnggulan',   // ← baru
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
            $isLimited = $this->isLimitedUser();

        return view('pages.landing.klasifikasi.show', compact('nama', 'metadataList', 'freeLimit', 'freeCountOnPage', 'isLimited'));
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

        $isLimited = $this->isLimitedUser();
        $freeIds   = $this->getFreeIds();

        // ── Pecah keyword jadi kata-kata ────────────────────────────────
        $keywords = collect(explode(' ', $q))
            ->map(fn($k) => trim(strtolower($k)))
            ->filter(fn($k) => strlen($k) >= 2)
            ->values();

        // ── Query: ambil semua yang mengandung setidaknya 1 kata ────────
        $results = Metadata::where('status', 2)
            ->whereHas('data', function ($query) {
                $query->where('status', 1)->where('location_id', 0);
            })
            ->where(function ($qb) use ($keywords, $q) {
                // Match exact phrase dulu
                $qb->where('nama', 'like', "%{$q}%");
                // Atau mengandung salah satu kata
                foreach ($keywords as $kw) {
                    $qb->orWhere('nama', 'like', "%{$kw}%");
                }
            })
            ->when($klasifikasi, function ($query) use ($klasifikasi) {
                $query->whereHas('klasifikasi', fn($q) =>
                    $q->where('nama_klasifikasi', $klasifikasi)
                );
            })
            ->with('klasifikasi')
            ->select('metadata_id', 'nama', 'klasifikasi_id', 'satuan_data', 'frekuensi_penerbitan', 'tahun_mulai_data')
            ->limit(100) // ambil lebih banyak dulu, nanti di-score & dipotong
            ->get();

        // ── Scoring: hitung kemiripan tiap hasil dengan keyword ─────────
        $qLower = strtolower($q);

        $scored = $results->map(function ($item) use ($q, $qLower, $keywords) {
            $nama      = strtolower($item->nama);
            $score     = 0;

            // 1. Exact match penuh → skor tertinggi
            if ($nama === $qLower) {
                $score += 1000;
            }

            // 2. Nama dimulai dengan keyword
            if (str_starts_with($nama, $qLower)) {
                $score += 500;
            }

            // 3. Mengandung exact phrase
            if (str_contains($nama, $qLower)) {
                $score += 300;
            }

            // 4. Semua kata keyword ada di nama
            $allMatch = $keywords->every(fn($kw) => str_contains($nama, $kw));
            if ($allMatch) {
                $score += 200;
            }

            // 5. Hitung berapa kata keyword yang cocok
            $matchCount = $keywords->filter(fn($kw) => str_contains($nama, $kw))->count();
            $score += $matchCount * 50;

            // 6. Skor similar_text (0–100) untuk kemiripan karakter
            similar_text($qLower, $nama, $similarity);
            $score += (int) $similarity;

            // 7. Bonus: nama lebih pendek → lebih relevan (hindari nama super panjang naik)
            $score -= (int) (strlen($nama) / 10);

            $item->_score = $score;
            return $item;
        });

        // Sort descending by score, ambil 50 teratas
        $sorted = $scored->sortByDesc('_score')->take(50)->values();

        // ── 3 teratas hasil search selalu free di dropdown ───────────────
        $topThreeIds      = $sorted->take(3)->pluck('metadata_id')->all();
        $effectiveFreeIds = array_unique(array_merge($freeIds, $topThreeIds));

        return response()->json(
            $sorted->map(fn($item) => [
                'metadata_id'          => $item->metadata_id,
                'nama'                 => $item->nama,
                'klasifikasi'          => $item->klasifikasi?->nama_klasifikasi,
                'klasifikasi_slug'     => Str::slug($item->klasifikasi?->nama_klasifikasi),
                'satuan_data'          => $item->satuan_data,
                'frekuensi_penerbitan' => $item->frekuensi_penerbitan,
                'tahun_mulai_data'     => $item->tahun_mulai_data,
                'is_locked'            => $isLimited && !in_array($item->metadata_id, $effectiveFreeIds),
            ])
        );
    }

    private function isLimitedUser(): bool
    {
        if (!auth()->check()) {
            return true;
        }

        $user = auth()->user();

        if ($user->group_id != 3) {
            return false; // bukan customer (admin/pengelola) → tidak dibatasi
        }

        $hasActiveSubscription = Transaksi::where('user_id', $user->user_id)
            ->where('status', 'success')
            ->where(function ($q) {
                $q->whereNull('aktif_sampai')
                ->orWhere('aktif_sampai', '>=', now());
            })
            ->exists();

        return !$hasActiveSubscription;
    }

    /**
     * 3 metadata_id pertama per rujukan (order: date_inputed desc).
     */
    private function getFreeIds(): array
    {
        // Tambahkan kolom order ke SELECT agar compatible dengan DISTINCT
        $rows = DB::table('metadata')
            ->join('data', 'metadata.metadata_id', '=', 'data.metadata_id')
            ->where('metadata.status', 2)
            ->where('data.status', 1)
            ->where('data.location_id', 0)
            ->whereNotNull('data.rujukan_id')
            ->orderBy('metadata.date_inputed', 'desc')
            ->orderBy('metadata.metadata_id', 'desc')
            ->select('metadata.metadata_id', 'data.rujukan_id', 'metadata.date_inputed') // ← tambah date_inputed
            ->get()
            ->unique('metadata_id'); // ← ganti distinct() dengan unique() di collection

        $freeIds         = [];
        $countPerRujukan = [];

        foreach ($rows as $row) {
            $rid = $row->rujukan_id;
            $countPerRujukan[$rid] = $countPerRujukan[$rid] ?? 0;
            if ($countPerRujukan[$rid] < 3) {
                $freeIds[] = $row->metadata_id;
                $countPerRujukan[$rid]++;
            }
        }

        $freeIds = array_unique($freeIds);

        // Fallback kalau kurang dari 3
        if (count($freeIds) < 3) {
            $extra = Metadata::where('status', 2)
                ->whereHas('data', fn($q) => $q->where('status', 1)->where('location_id', 0))
                ->whereNotIn('metadata_id', $freeIds)
                ->orderBy('date_inputed', 'desc')
                ->orderBy('metadata_id', 'desc')
                ->limit(3 - count($freeIds))
                ->pluck('metadata_id')
                ->all();

            $freeIds = array_unique(array_merge($freeIds, $extra));
        }

        return $freeIds;
    }

    public function dataSeries(Request $request)
    {
        $perPage = (int) $request->input('per_page', 12);
        $perPage = in_array($perPage, [12, 24, 48]) ? $perPage : 12;

        $freeIds   = $this->getFreeIds();
        $isLimited = $this->isLimitedUser();

        // Base query
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

        // Sort: kalau limited → free selalu di atas, locked di bawah
        // Kalau tidak limited → pakai sort pilihan user
        if ($isLimited) {
            // FIELD(metadata_id, id1, id2, ...) → free dapat urutan terkecil (= teratas)
            if (!empty($freeIds)) {
                $ids      = implode(',', array_map('intval', $freeIds));
                $query->orderByRaw("FIELD(metadata_id, {$ids}) = 0 ASC") // 0 = tidak ada di list → bawah
                    ->orderByRaw("FIELD(metadata_id, {$ids}) ASC");    // urutan dalam list free
            }
            // Setelah free, sort terbaru
            $query->orderBy('date_inputed', 'desc')->orderBy('metadata_id', 'desc');
        } else {
            match ($request->input('sort', 'terbaru')) {
                'az'    => $query->orderBy('nama', 'asc'),
                'za'    => $query->orderBy('nama', 'desc'),
                default => $query->orderBy('date_inputed', 'desc')->orderBy('metadata_id', 'desc'),
            };
        }

        $metadataList = $query->paginate($perPage)->withQueryString();

        // Year ranges (batch)
        $metadataIds = $metadataList->pluck('metadata_id')->all();

        $yearRanges = DB::table('data')
            ->join('time', 'data.time_id', '=', 'time.time_id')
            ->whereIn('data.metadata_id', $metadataIds)
            ->where('data.status', 1)
            ->whereNotNull('data.number_value')
            ->groupBy('data.metadata_id')
            ->selectRaw('data.metadata_id, MIN(time.year) as min_year, MAX(time.year) as max_year')
            ->get()
            ->keyBy('metadata_id')
            ->map(fn($r) => $r->min_year . '-' . $r->max_year);

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
            'freeIds',
            'yearRanges',
            'isLimited',
        ));
    }

    public function dataShow(int $metadataId)
    {
        $metadata = Metadata::with(['klasifikasi', 'produsen'])
            ->where('status', 2)
            ->findOrFail($metadataId);

        // Gate
        if ($this->isLimitedUser()) {
            $freeIds = $this->getFreeIds();
            if (!in_array($metadataId, $freeIds)) {
                return redirect()->route('langganan')
                    ->with('info', 'Akses data ini memerlukan langganan aktif.');
            }
        }

        $yearRange = DB::table('data')
            ->join('time', 'data.time_id', '=', 'time.time_id')
            ->where('data.metadata_id', $metadataId)
            ->where('data.status', 1)
            ->where('data.location_id', 0)
            ->whereNotNull('data.number_value')
            ->selectRaw('MIN(time.year) as min_year, MAX(time.year) as max_year')
            ->first();

        $yearStart = $yearRange?->min_year ?? (now()->year - 5);
        $yearEnd   = $yearRange?->max_year ?? (now()->year - 1);

        $rawData = Data::with(['time', 'location', 'rujukan.produsen'])
            ->where('metadata_id', $metadataId)
            ->where('status', 1)
            ->where('location_id', 0)
            ->whereHas('time', fn($q) => $q->whereBetween('year', [$yearStart, $yearEnd]))
            ->get();

        $grouped = $rawData
            ->sortBy(fn($d) => $d->time?->year)
            ->groupBy(fn($d) => $d->time?->year);

        $tableRows   = [];
        $chartLabels = [];
        $chartValues = [];

        foreach ($grouped as $year => $items) {
            $val = $items->pluck('number_value')->filter(fn($v) => !is_null($v))->first();
            $tableRows[]   = ['period' => (string) $year, 'value' => $val, 'year' => $year];
            $chartLabels[] = (string) $year;
            $chartValues[] = $val !== null ? (float) $val : null;
        }

        $yearsWithData = array_column($tableRows, 'year');
        for ($y = $yearStart; $y <= $yearEnd; $y++) {
            if (!in_array($y, $yearsWithData)) {
                $tableRows[]   = ['period' => (string)$y, 'value' => null, 'year' => $y];
                $chartLabels[] = (string)$y;
                $chartValues[] = null;
            }
        }

        usort($tableRows, fn($a, $b) => $a['year'] <=> $b['year']);
        array_multisort(array_column($tableRows, 'year'), SORT_ASC, $tableRows);

        $chartLabels = array_column($tableRows, 'period');
        $chartValues = array_map(fn($r) => $r['value'] !== null ? (float)$r['value'] : null, $tableRows);
        $tableRows   = collect($tableRows);

        $firstData = $rawData->first();
        $rujukan   = $firstData?->rujukan;

        return view('pages.landing.data_show', compact(
            'metadata', 'tableRows', 'chartLabels', 'chartValues',
            'yearStart', 'yearEnd', 'rujukan',
        ));
    }
}