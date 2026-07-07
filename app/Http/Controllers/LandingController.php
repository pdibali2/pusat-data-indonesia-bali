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
        // Produk unggulan: hanya metadata yang is_free = 1
        $produkUnggulan = Metadata::query()
            ->where('status', 2)
            ->where('is_free', 1)  // ← ganti ini
            ->whereHas('data', function ($q) {
                $q->where('status', 1)->where('location_id', 0);
            })
            ->with([
                'klasifikasi',
                'data' => function ($q) {
                    $q->where('status', 1)->where('location_id', 0)->with('location', 'rujukan.produsen')->limit(1);
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

    public function bantuan()
    {
        $topics = [
            [
                'title' => 'Mencari Data',
                'icon'  => '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z" /></svg>',
                'intro' => 'Gunakan fitur pencarian untuk menemukan data statistik yang Anda butuhkan dengan cepat.',
                'steps' => [
                    'Klik kolom pencarian.',
                    'Ketikkan kata kunci, misalnya nama topik data atau klasifikasi data.',
                    'Pilih hasil pada daftar saran otomatis, atau tekan <b>Enter</b> untuk melihat semua hasil.',
                ],
                'tip' => 'Gunakan kata kunci yang spesifik agar hasil pencarian lebih akurat.',
            ],
            [
                'title' => 'Template Tampilan Data',
                'icon'  => '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg>',
                'intro' => 'Template Tampilan Data memungkinkan Anda menyusun beberapa data sekaligus ke dalam satu tabel pivot yang bisa disesuaikan rentang waktunya, lalu diekspor kapan saja tanpa perlu mengulang filter dari awal.',
                'steps' => [
                    'Buka menu <b>Produk → Template Tampilan Data</b> dari navigasi utama.',
                    'Klik <b>Buat Template</b>, lalu pilih salah satu jenis template: <b>Metadata</b> (pilih data secara manual satu per satu), <b>Klasifikasi</b> (ambil semua data dalam satu klasifikasi topik), atau <b>Wilayah</b> (ambil semua data yang tersedia untuk wilayah tertentu).',
                    'Pilih data dan/atau wilayah sesuai kebutuhan, lalu beri nama template dan simpan.',
                    'Setelah tersimpan, Anda akan diarahkan ke halaman <b>Template Tampilan Data</b>. Pilih template yang baru dibuat pada <b>Langkah 1: Pilih Template</b>.',
                    'Pilih <b>Frekuensi Rentang Waktu</b> (Tahunan, Semesteran, Kuartal, Bulanan, dsb) sesuai frekuensi data yang tersedia — frekuensi yang tidak memiliki data akan otomatis dinonaktifkan.',
                    'Tentukan <b>Rentang Periode</b> (misalnya dari tahun 2020 sampai 2024), lalu klik <b>Tampilkan Data</b> untuk melihat hasilnya dalam bentuk tabel.',
                    'Gunakan tombol <b>Export</b> di atas tabel untuk mengunduh hasil dalam format Excel, PDF, atau JSON.',
                ],
                'tip' => 'Template yang Anda buat tanpa login akan tersimpan sementara dan bisa hilang jika cache dibersihkan. Login terlebih dahulu agar template tersimpan permanen di akun Anda dan bisa diakses dari perangkat lain.',
            ],
            [
                'title' => 'Melihat Detail Data',
                'icon'  => '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2a4 4 0 014-4h4m0 0l-4-4m4 4l-4 4M3 7h4v4H3V7z" /></svg>',
                'intro' => 'Setiap data memiliki halaman detail berisi tabel, grafik, dan metadata lengkap.',
                'steps' => [
                    'Buka menu <b>Data Series</b>.',
                    'Klik data yang ingin dilihat untuk membuka halaman detail dari data yang dipilih.',
                    'Tabel dan grafik akan menampilkan data berdasarkan rentang tahun yang tersedia.',
                ],
                'tip' => 'Beberapa data memerlukan langganan aktif untuk dapat diakses secara penuh.',
            ],
            [
                'title' => 'Klasifikasi Data',
                'icon'  => '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7" /></svg>',
                'intro' => 'Data dikelompokkan berdasarkan klasifikasi agar lebih mudah dijelajahi.',
                'steps' => [
                    'Buka menu <b>Klasifikasi</b> dari navigasi utama.',
                    'Pilih salah satu klasifikasi untuk melihat daftar data di dalamnya.',
                    'Pilih data yang ingin dilihat.',
                ],
                'tip' => null,
            ],
            [
                'title' => 'Langganan & Akses Premium',
                'icon'  => '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>',
                'intro' => 'Sebagian data bersifat premium dan membutuhkan langganan aktif untuk diakses.',
                'steps' => [
                    'Buka menu <b>Langganan</b> untuk melihat paket yang tersedia.',
                    'Pilih paket sesuai kebutuhan, lalu lakukan pembayaran.',
                    'Setelah pembayaran berhasil, akses data premium akan otomatis terbuka.',
                ],
                'tip' => 'Anda bisa memeriksa status transaksi di halaman Riwayat Transaksi setelah login.',
            ],
            [
                'title' => 'Akun & Login',
                'icon'  => '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>',
                'intro' => 'Buat akun untuk mengakses fitur langganan dan menyimpan preferensi Anda.',
                'steps' => [
                    'Klik tombol <b>Daftar</b> pada navigasi untuk membuat akun baru.',
                    'Isi formulir pendaftaran dan verifikasi email Anda melalui tautan yang dikirimkan.',
                    'Setelah terverifikasi, login menggunakan email dan kata sandi Anda.',
                    'Jika lupa kata sandi, gunakan fitur <b>Lupa Kata Sandi</b> di halaman login.',
                ],
                'tip' => 'Periksa folder spam jika email verifikasi tidak muncul di kotak masuk.',
            ],
        ];

        return view('pages.landing.bantuan', compact('topics'));
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

        $klasifikasiList = Klasifikasi::whereHas('metadata', function ($q) {
                $q->where('status', 2)
                  ->whereHas('data', fn($qd) => $qd->where('status', 1)->where('location_id', 0));
            })
            ->orderBy('nama_klasifikasi')
            ->get(['nama_klasifikasi', 'icon'])
            ->map(function ($item) use ($counts) {
                $slug = Str::slug($item->nama_klasifikasi);
                if (!$slug) return null;
                return [
                    'nama'  => $item->nama_klasifikasi,
                    'slug'  => $slug,
                    'total' => $counts->get($item->nama_klasifikasi, 0),
                    'icon'  => $item->icon,
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

        $isLimited   = $this->isLimitedUser();
        $freeIds     = $this->getFreeIds();
        $maxFreeBait = 2; // maksimal data gratis yang ditampilkan sebagai "pemancing"

        // ── Data gratis yang benar-benar milik klasifikasi ini ──────────────
        $freeIdsInKlasifikasi = [];
        if ($isLimited && !empty($freeIds)) {
            $freeIdsInKlasifikasi = Metadata::where('status', 2)
                ->where('is_free', 1)
                ->whereHas('data', fn($q) => $q->where('status', 1)->where('location_id', 0))
                ->whereHas('klasifikasi', fn($q) => $q->where('nama_klasifikasi', $nama))
                ->orderBy('nama')
                ->pluck('metadata_id')
                ->all();
        }

        $recommendedFree = collect();
        $allowedFreeIds  = [];

        if ($isLimited) {
            if (!empty($freeIdsInKlasifikasi)) {
                // Ada data gratis di klasifikasi ini → batasi maksimal $maxFreeBait sebagai pemancing
                $allowedFreeIds = array_slice($freeIdsInKlasifikasi, 0, $maxFreeBait);
            } elseif (!empty($freeIds)) {
                // Tidak ada data gratis di klasifikasi ini → pinjam dari klasifikasi lain
                $recommendedFree = Metadata::with([
                        'klasifikasi',
                        'data' => fn($q) => $q->where('status', 1)
                            ->where('location_id', 0)
                            ->with('rujukan.produsen')
                            ->limit(1),
                    ])
                    ->where('status', 2)
                    ->where('is_free', 1)
                    ->whereIn('metadata_id', $freeIds)
                    ->whereHas('data', fn($q) => $q->where('status', 1)->where('location_id', 0))
                    ->inRandomOrder()
                    ->limit($maxFreeBait)
                    ->get();

                $allowedFreeIds = $recommendedFree->pluck('metadata_id')->all();
            }
        }

        $query = Metadata::with(['klasifikasi', 'produsen', 'data' => function ($q) {
                $q->where('status', 1)
                ->where('location_id', 0)
                ->with('rujukan.produsen')
                ->limit(1);
            },])
            ->where('status', 2)
            ->whereHas('data', fn($q) => $q->where('status', 1)->where('location_id', 0))
            ->whereHas('klasifikasi', function ($q) use ($nama) {
                $q->where('nama_klasifikasi', $nama);
            });

        // Pemancing (maks 2) ditaruh di atas jika limited
        if ($isLimited && !empty($allowedFreeIds)) {
            $ids = implode(',', array_map('intval', $allowedFreeIds));
            $query->orderByRaw("FIELD(metadata_id, {$ids}) = 0 ASC")
                ->orderByRaw("FIELD(metadata_id, {$ids}) ASC");
        }

        $query->orderBy('nama');

        $metadataList = $query->paginate(10);

        return view('pages.landing.klasifikasi.show', compact(
            'nama', 'metadataList', 'freeIds', 'isLimited', 'allowedFreeIds', 'recommendedFree'
        ));
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
                // Coba exact phrase dulu
                $qb->where('nama', 'like', "%{$q}%")
                ->orWhere(function ($inner) use ($keywords) {
                    // Fallback: semua kata harus ada (AND, bukan OR)
                    foreach ($keywords as $kw) {
                        $inner->where('nama', 'like', "%{$kw}%");
                    }
                });
            })
            ->when($klasifikasi, function ($query) use ($klasifikasi) {
                $query->whereHas('klasifikasi', fn($q) =>
                    $q->where('nama_klasifikasi', $klasifikasi)
                );
            })
            ->with(['klasifikasi', 'produsen'])
            ->select('metadata_id', 'nama', 'klasifikasi_id', 'produsen_id', 'satuan_data', 'frekuensi_penerbitan', 'tahun_mulai_data')
            ->limit(100)
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

        return response()->json(
            $sorted->map(fn($item) => [
                'metadata_id'          => $item->metadata_id,
                'nama'                 => $item->nama,
                'klasifikasi'          => $item->klasifikasi?->nama_klasifikasi,
                'klasifikasi_slug'     => Str::slug($item->klasifikasi?->nama_klasifikasi),
                'satuan_data'          => $item->satuan_data,
                'frekuensi_penerbitan' => $item->frekuensi_penerbitan,
                'tahun_mulai_data'     => $item->tahun_mulai_data,
                'is_locked'            => $isLimited && !in_array($item->metadata_id, $freeIds), // pakai $freeIds murni
                'produsen'             => $item->produsen?->nama ?? $item->produsen?->nama_produsen,
            ])
        );
    }

    public function autocomplete(Request $request)
    {
        $q = trim($request->input('q', ''));

        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $keywords = collect(explode(' ', $q))
            ->map(fn($k) => trim(strtolower($k)))
            ->filter(fn($k) => strlen($k) >= 2)
            ->values();

        $results = Metadata::where('status', 2)
            ->whereHas('data', fn($query) =>
                $query->where('status', 1)->where('location_id', 0)
            )
            ->where(function ($qb) use ($q, $keywords) {
                $qb->where('nama', 'like', "%{$q}%")
                ->orWhere(function ($inner) use ($keywords) {
                    foreach ($keywords as $kw) {
                        $inner->where('nama', 'like', "%{$kw}%");
                    }
                })
                ->orWhere('konsep',      'like', "%{$q}%")
                ->orWhere('definisi',    'like', "%{$q}%")
                ->orWhere('satuan_data', 'like', "%{$q}%")
                ->orWhere('tag',         'like', "%{$q}%");
            })
            ->select('metadata_id', 'nama', 'klasifikasi_id', 'konsep', 'definisi', 'satuan_data', 'tag')
            ->with('klasifikasi:klasifikasi_id,nama_klasifikasi')
            ->limit(80)
            ->get();

        $qLower = strtolower($q);

        $scored = $results->map(function ($item) use ($q, $qLower, $keywords) {
            $nama  = strtolower($item->nama);
            $score = 0;

            if ($nama === $qLower)               $score += 1000;
            if (str_starts_with($nama, $qLower)) $score += 500;
            if (str_contains($nama, $qLower))    $score += 300;

            $allMatch = $keywords->every(fn($kw) => str_contains($nama, $kw));
            if ($allMatch) $score += 200;

            $matchCount = $keywords->filter(fn($kw) => str_contains($nama, $kw))->count();
            $score += $matchCount * 50;

            similar_text($qLower, $nama, $similarity);
            $score += (int) $similarity;
            $score -= (int) (strlen($nama) / 10);

            // Deteksi "ditemukan di" field mana
            $foundIn = null;
            if (!str_contains($nama, $qLower) && !$allMatch) {
                if ($item->konsep   && str_contains(strtolower($item->konsep),      $qLower)) $foundIn = 'konsep';
                elseif ($item->definisi  && str_contains(strtolower($item->definisi),   $qLower)) $foundIn = 'definisi';
                elseif ($item->satuan_data && str_contains(strtolower($item->satuan_data), $qLower)) $foundIn = 'satuan data';
                elseif ($item->tag   && str_contains(strtolower($item->tag),         $qLower)) $foundIn = 'tag';
            }

            $item->_score    = $score;
            $item->_found_in = $foundIn;
            return $item;
        });

        $sorted = $scored->sortByDesc('_score')->take(8)->values();

        return response()->json(
            $sorted->map(fn($item) => [
                'label'      => $item->nama,
                'klasifikasi'=> $item->klasifikasi?->nama_klasifikasi,
                'found_in'   => $item->_found_in,   // null kalau match di nama
                'q'          => $item->nama,
            ])
        );
    }

    public function searchResults(Request $request)
    {
        $q = trim($request->input('q', ''));

        $isLimited = $this->isLimitedUser();
        $freeIds   = $this->getFreeIds();

        if (strlen($q) < 2) {
            return view('pages.landing.search', [
                'q'          => $q,
                'sorted'     => collect(),
                'freeIds'    => $freeIds,
                'isLimited'  => $isLimited,
                'totalFound' => 0,
            ]);
        }

        $keywords = collect(explode(' ', $q))
            ->map(fn($k) => trim(strtolower($k)))
            ->filter(fn($k) => strlen($k) >= 2)
            ->values();

        $results = Metadata::where('status', 2)
            ->whereHas('data', fn($query) =>
                $query->where('status', 1)->where('location_id', 0)
            )
            ->where(function ($qb) use ($q, $keywords) {
                $qb->where('nama', 'like', "%{$q}%")
                ->orWhere(function ($inner) use ($keywords) {
                    foreach ($keywords as $kw) {
                        $inner->where('nama', 'like', "%{$kw}%");
                    }
                })
                ->orWhere('konsep',      'like', "%{$q}%")
                ->orWhere('definisi',    'like', "%{$q}%")
                ->orWhere('satuan_data', 'like', "%{$q}%")
                ->orWhere('tag',         'like', "%{$q}%");
            })
            ->with([
            'klasifikasi:klasifikasi_id,nama_klasifikasi',
                'data' => fn($q) => $q->where('status', 1)
                    ->where('location_id', 0)
                    ->with('rujukan.produsen')
                    ->limit(1),
            ])
            ->select('metadata_id', 'nama', 'klasifikasi_id', 'satuan_data',
                    'frekuensi_penerbitan', 'tahun_mulai_data', 'is_free',
                    'konsep', 'definisi', 'tag')
            ->limit(200)
            ->get();

        $qLower = strtolower($q);

        $scored = $results->map(function ($item) use ($q, $qLower, $keywords, $freeIds, $isLimited) {
            $nama  = strtolower($item->nama);
            $score = 0;

            if ($nama === $qLower)               $score += 1000;
            if (str_starts_with($nama, $qLower)) $score += 500;
            if (str_contains($nama, $qLower))    $score += 300;

            $allMatch = $keywords->every(fn($kw) => str_contains($nama, $kw));
            if ($allMatch) $score += 200;

            $matchCount = $keywords->filter(fn($kw) => str_contains($nama, $kw))->count();
            $score += $matchCount * 50;

            similar_text($qLower, $nama, $similarity);
            $score += (int) $similarity;
            $score -= (int) (strlen($nama) / 10);

            if ($item->is_free) $score += 30;

            // is_locked: murni berdasarkan is_free, tanpa paksa topThree
            $item->is_locked = $isLimited && !in_array($item->metadata_id, $freeIds);
            $item->_score    = $score;
            return $item;
        });
        
        // Pisahkan free dan premium dari hasil search
        if ($isLimited) {
            $freeFromSearch    = $scored->filter(fn($i) => !$i->is_locked)->sortByDesc('_score')->values();
            $premiumFromSearch = $scored->filter(fn($i) =>  $i->is_locked)->sortByDesc('_score')->values();

            // Terapkan logika slot free (hanya untuk user terbatas):
            if ($freeFromSearch->count() >= 3) {
                // Kasus 1: ada ≥3 free → batasi 3 saja
                $freeSlot = $freeFromSearch->take(3);
            } elseif ($freeFromSearch->count() >= 1) {
                // Kasus 2: ada 1–2 free → tampilkan semua yang ada
                $freeSlot = $freeFromSearch;
            } else {
                // Kasus 3: tidak ada free dari search → ambil 2 random dari DB
                $randomFree = Metadata::where('status', 2)
                    ->where('is_free', 1)
                    ->whereIn('metadata_id', $freeIds)
                    ->whereHas('data', fn($q) => $q->where('status', 1)->where('location_id', 0))
                    ->inRandomOrder()
                    ->limit(2)
                    ->get();

                $randomFree->each(function ($item) {
                    $item->is_locked  = false;
                    $item->_score     = 0;
                    $item->_is_random = true;
                });

                $freeSlot = $randomFree;
            }

            // Gabungkan: free slot di atas, premium di bawah
            $sorted     = $freeSlot->values()->concat($premiumFromSearch->values())->values();
            $totalFound = $sorted->count();
        } else {
            // User sudah punya akses penuh (login + subscribe aktif, atau admin/pengelola)
            // → tampilkan semua hasil apa adanya, tanpa pembatasan slot gratis
            $sorted     = $scored->sortByDesc('_score')->values();
            $totalFound = $sorted->count();
        }

        $perPage     = 10;
        $currentPage = max(1, (int) $request->input('page', 1));
        $offset      = ($currentPage - 1) * $perPage;

        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $sorted->slice($offset, $perPage)->values(),
            $totalFound,
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('pages.landing.search', [
            'q'          => $q,
            'sorted'     => $paginated,
            'isLimited'  => $isLimited,
            'freeIds'    => $freeIds,
            'totalFound' => $totalFound,
        ]);
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
     * Semua metadata_id yang ditandai is_free = 1.
     */
    private function getFreeIds(): array
    {
        return Metadata::where('status', 2)
            ->where('is_free', 1)
            ->pluck('metadata_id')
            ->all();
    }

    /**
     * Rekomendasi data gratis: minimal 3, maksimal 6 item.
     * Maksimal 3 item boleh berasal dari rujukan (sumber) yang sama.
     */
    private function getFreeRecommendations(int $min = 3, int $max = 20, int $maxPerSource = 20)
    {
        $pool = Metadata::with([
                'klasifikasi',
                'data' => fn($q) => $q->where('status', 1)
                    ->where('location_id', 0)
                    ->with('location', 'rujukan.produsen')
                    ->limit(1),
            ])
            ->where('status', 2)
            ->where('is_free', 1)
            ->whereHas('data', fn($q) => $q->where('status', 1)->where('location_id', 0))
            ->orderBy('date_inputed', 'desc')
            ->orderBy('metadata_id', 'desc')
            ->get();

        $perSource = [];
        $selected  = collect();

        foreach ($pool as $m) {
            if ($selected->count() >= $max) {
                break;
            }

            $sourceKey = $m->data->first()?->rujukan_id ?? 'meta-' . $m->metadata_id;
            $perSource[$sourceKey] ??= 0;

            if ($perSource[$sourceKey] >= $maxPerSource) {
                continue;
            }

            $perSource[$sourceKey]++;
            $selected->push($m);
        }

        return $selected->count() >= $min ? $selected : collect();
    }

    public function dataSeries(Request $request)
    {
        $perPage = (int) $request->input('per_page', 12);
        $perPage = in_array($perPage, [12, 24, 48]) ? $perPage : 12;

        $freeIds   = $this->getFreeIds();
        $isLimited = $this->isLimitedUser();

        // ── Rekomendasi data gratis: min 3, max 6, max 3 per sumber sama ──
        $rekomendasiGratis = $isLimited ? $this->getFreeRecommendations(3, 6, 3) : collect();
        $rekomendasiIds    = $rekomendasiGratis->pluck('metadata_id')->all();

        // Base query — "Semua Data" (exclude yang sudah masuk rekomendasi)
        $query = Metadata::with([
            'klasifikasi',
            'produsen',
            'data' => function ($q) {           
                $q->where('status', 1)
                ->where('location_id', 0)
                ->with('rujukan.produsen')
                ->limit(1);
            },
        ])
        ->where('status', 2)
        ->whereHas('data', fn($q) => $q->where('status', 1)->where('location_id', 0));

        if (!empty($rekomendasiIds)) {
            $query->whereNotIn('metadata_id', $rekomendasiIds);
        }

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

        // "Semua Data": premium dulu, gratis ditaruh paling belakang
        match ($request->input('sort', 'terbaru')) {
            'az'    => $query->orderByRaw('metadata.is_free ASC')->orderBy('nama', 'asc'),
            'za'    => $query->orderByRaw('metadata.is_free ASC')->orderBy('nama', 'desc'),
            default => $query->orderByRaw('metadata.is_free ASC')
                            ->orderBy('date_inputed', 'desc')
                            ->orderBy('metadata_id', 'desc'),
        };

        $metadataList = $query->paginate($perPage)->withQueryString();

        // Year ranges (batch) — gabungkan id "Semua Data" + id rekomendasi
        $metadataIds = array_merge(
            $metadataList->pluck('metadata_id')->all(),
            $rekomendasiIds
        );

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
            'rekomendasiGratis',
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