<?php

namespace App\Http\Controllers;

use App\Models\Data;
use App\Models\Metadata;
use App\Models\Rujukan;
use App\Models\Location;
use App\Models\Waktu;
use App\Models\Tampilan;
use App\Models\IsiTampilan;
use App\Models\Transaksi;
use App\Imports\DataImport;
use App\Services\AnomalyDetectionService;
use App\Services\AuditTrailService;
use App\Services\WorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DataController extends Controller
{
    const STATUS_AVAILABLE = 1;
    const STATUS_PENDING   = 0;

    public function __construct(
        private readonly AnomalyDetectionService $detector,
        private readonly AuditTrailService       $auditTrail,
        private readonly WorkflowService         $workflow,
    ) {}

    // ═══════════════════════════════════════════════════════════
    // INDEX
    // ═══════════════════════════════════════════════════════════

    public function index(Request $request)
    {
        $hasFilter = $request->hasAny([
            'metadata_id', 'filter_wilayah_id', 'year', 'search', 'template_id'
        ]);

        if ($request->filled('template_id')) {
            $tampilan = Tampilan::where('tampilan_id', $request->template_id)
                ->where('user_id', Auth::user()->user_id)
                ->first();

            if ($tampilan && $tampilan->filter_params) {
                $fp = $tampilan->filter_params;
                $request->merge(array_filter([
                    'metadata_id'       => $fp['metadata_id']       ?? null,
                    'filter_wilayah_id' => $fp['filter_wilayah_id'] ?? null,
                    'year'              => $fp['year']               ?? null,
                ]));
            }
        }

        $data = null;
        if ($hasFilter) {
            $query = Data::with(['metadata', 'location', 'time', 'user', 'latestAnomaly'])
                ->where('status', Data::STATUS_AVAILABLE);

            if ($request->filled('metadata_id')) {
                $query->where('metadata_id', $request->metadata_id);
            }
            if ($request->filled('filter_wilayah_id')) {
                $query->where('location_id', $request->filter_wilayah_id);
            }
            if ($request->filled('year')) {
                $query->whereHas('time', fn($q) => $q->where('year', $request->year));
            }
            if ($request->filled('search')) {
                $s = $request->search;
                $query->where(fn($q) =>
                    $q->whereHas('metadata', fn($m) => $m->where('nama', 'like', "%$s%"))
                      ->orWhere('text_value', 'like', "%$s%")
                );
            }

            $data = $query->orderBy('date_inputed', 'desc')->paginate(15)->withQueryString();
        }

        $activeTemplateId   = (int) $request->input('template_id', 0);
        $metadataList       = Metadata::where('status', 2)->orderBy('nama')->get(['metadata_id', 'nama']);
        $wilayahList        = Location::select('nama_wilayah')->distinct()->orderBy('nama_wilayah')->pluck('nama_wilayah');
        $availableTemplates = Tampilan::where('user_id', Auth::user()->user_id)
                                ->withCount('isiTampilan')
                                ->orderBy('created_at', 'desc')
                                ->get();

        $hasAccess = true;
        if (Auth::check()) {
            $user = Auth::user();
            if ((int) $user->group_id === 3) {
                $hasAccess = Transaksi::where('user_id', $user->user_id)
                    ->where('status', 'success')
                    ->where(function ($q) {
                        $q->whereNull('aktif_sampai')
                          ->orWhere('aktif_sampai', '>=', now());
                    })
                    ->exists();
            }
        }

        $pendingCount  = Data::where('status', Data::STATUS_PENDING)->count();

        // Jumlah anomali aktif untuk badge di sidebar
        $anomalyCount  = Data::needsReview()->count();

        return view('pages.data.index', compact(
            'data', 'metadataList', 'wilayahList',
            'availableTemplates', 'pendingCount', 'anomalyCount',
            'hasFilter', 'activeTemplateId', 'hasAccess'
        ));
    }

    // ═══════════════════════════════════════════════════════════
    // AJAX HELPERS (tidak berubah)
    // ═══════════════════════════════════════════════════════════

    public function searchWilayah(Request $request)
    {
        $q = $request->input('q', '');
        $query = Location::select('location_id', 'nama_wilayah')->orderBy('nama_wilayah');
        if ($q !== '') $query->where('nama_wilayah', 'like', "%{$q}%");

        return response()->json(
            $query->get()->map(fn($loc) => ['id' => $loc->location_id, 'path' => $loc->nama_wilayah])
        );
    }

    public function getKecamatan(Request $request)
    {
        $request->validate(['kabupaten' => 'required|string']);
        return response()->json(
            Location::where('kabupaten', $request->kabupaten)
                ->select('kecamatan')->distinct()->orderBy('kecamatan')->pluck('kecamatan')
        );
    }

    public function getDesa(Request $request)
    {
        $request->validate(['kecamatan' => 'required|string']);
        return response()->json(
            Location::where('kecamatan', $request->kecamatan)
                ->select('desa')->distinct()->orderBy('desa')->pluck('desa')
        );
    }

    public function searchMetadata(Request $request)
    {
        $q     = $request->input('q', '');
        $limit = $q === '' ? 200 : 30;
        $query = Metadata::with('klasifikasi')->where('status', 2)->orderBy('nama');
        if ($q !== '') $query->where('nama', 'like', "%{$q}%");

        return response()->json(
            $query->limit($limit)->get(['metadata_id', 'nama', 'klasifikasi_id', 'satuan_data'])
                ->map(fn($item) => [
                    'metadata_id'    => $item->metadata_id,
                    'nama'           => $item->nama,
                    'satuan_data'    => $item->satuan_data,
                    'klasifikasi_id' => $item->klasifikasi_id,
                    'klasifikasi'    => $item->klasifikasi?->nama_klasifikasi,
                ])
        );
    }

    public function searchYear(Request $request)
    {
        $q = $request->input('q', '');
        $query = Waktu::select('year')->distinct();
        if ($q !== '') $query->where('year', 'like', "{$q}%");
        return response()->json($query->orderByDesc('year')->pluck('year'));
    }

    private static function detectLocationLevel(string $nama): string
    {
        $lower = strtolower($nama);
        if (str_contains($lower, 'provinsi'))                                   return 'provinsi';
        if (str_contains($lower, 'kabupaten') || str_contains($lower, 'kota')) return 'kabupaten';
        if (str_contains($lower, 'kecamatan'))                                  return 'kecamatan';
        return 'desa';
    }

    public function getProdusenByRujukan(Request $request)
    {
        $rujukan = Rujukan::select('rujukan_id', 'nama_rujukan', 'produsen_id')
            ->with('produsen:produsen_id,nama_produsen')
            ->where('rujukan_id', $request->rujukan_id)
            ->first();

        if (!$rujukan) {
            return response()->json(['success' => false, 'message' => 'Rujukan tidak ditemukan'], 404);
        }

        return response()->json([
            'success'       => true,
            'produsen_id'   => $rujukan->produsen_id,
            'nama_produsen' => $rujukan->produsen?->nama_produsen ?? '-',
        ]);
    }

    // ═══════════════════════════════════════════════════════════
    // CREATE
    // ═══════════════════════════════════════════════════════════

    public function create()
    {
        $metadataList = Metadata::select(
            'metadata_id', 'nama', 'tipe_data', 'satuan_data', 'frekuensi_penerbitan', 'flag_desimal'
        )->where('status', 2)->orderBy('nama')->get();

        $rujukanList  = Rujukan::select('rujukan_id', 'nama_rujukan')->orderBy('nama_rujukan')->get();
        $locationList = Location::select('location_id', 'nama_wilayah')->orderBy('nama_wilayah')->get();

        $provinsiList  = $locationList->filter(fn($l) => str_contains(strtolower($l->nama_wilayah), 'provinsi'));
        $kabupatenList = $locationList->filter(fn($l) =>
            str_contains(strtolower($l->nama_wilayah), 'kabupaten') ||
            str_contains(strtolower($l->nama_wilayah), 'kota')
        );
        $kecamatanList = $locationList->filter(fn($l) => str_contains(strtolower($l->nama_wilayah), 'kecamatan'));
        $desaList      = $locationList->filter(fn($l) =>
            !str_contains(strtolower($l->nama_wilayah), 'provinsi') &&
            !str_contains(strtolower($l->nama_wilayah), 'kabupaten') &&
            !str_contains(strtolower($l->nama_wilayah), 'kota') &&
            !str_contains(strtolower($l->nama_wilayah), 'kecamatan')
        );

        $timeList = Waktu::select('time_id', 'decade', 'year', 'semester', 'quarter', 'month')
            ->orderBy('decade', 'desc')->orderBy('year', 'desc')->orderBy('month')->get();

        $timeListJs = $timeList->map(fn($t) => [
            'time_id'  => $t->time_id, 'decade'   => $t->decade,
            'year'     => $t->year,    'semester' => $t->semester,
            'quarter'  => $t->quarter, 'month'    => $t->month,
        ])->values()->toArray();

        $locationListJs = $locationList->map(fn($l) => [
            'location_id'  => (string) $l->location_id,
            'nama_wilayah' => $l->nama_wilayah,
            'level'        => self::detectLocationLevel($l->nama_wilayah),
        ])->values()->toArray();

        return view('pages.data.create', compact(
            'metadataList', 'rujukanList', 'locationList',
            'provinsiList', 'kabupatenList', 'kecamatanList', 'desaList',
            'timeList', 'timeListJs', 'locationListJs'
        ));
    }

    // ═══════════════════════════════════════════════════════════
    // STORE — dengan screening anomali otomatis
    // ═══════════════════════════════════════════════════════════

    public function store(Request $request)
    {
        // Resolve location_id dari level terdalam
        $locationId = $request->desa_id
            ?? $request->kecamatan_id
            ?? $request->kabupaten_id
            ?? $request->provinsi_id;

        $request->merge(['location_id' => $locationId]);

        // Validasi metadata aktif
        $metadata = Metadata::where('metadata_id', $request->metadata_id)
            ->where('status', 2)->first();
        if (!$metadata) {
            return back()->withInput()->withErrors([
                'metadata_id' => 'Metadata tidak aktif atau tidak valid.',
            ]);
        }

        // Validasi rujukan
        if (!Rujukan::where('rujukan_id', $request->rujukan_id)->exists()) {
            return back()->withInput()->withErrors([
                'rujukan_id' => 'Rujukan tidak tersedia.',
            ]);
        }

        $request->validate([
            'metadata_id'  => 'required|integer|exists:metadata,metadata_id',
            'location_id'  => 'required|integer|exists:location,location_id',
            'time_id'      => 'required|integer|exists:time,time_id',
            'rujukan_id'   => 'required|integer|exists:rujukan,rujukan_id',
            'number_value' => 'nullable|numeric',
        ]);

        // Cek duplikat
        $duplicate = Data::where('metadata_id', $request->metadata_id)
            ->where('location_id', $request->location_id)
            ->where('time_id', $request->time_id)
            ->where('rujukan_id', $request->rujukan_id)
            ->first();

        if ($duplicate) {
            return redirect()->back()->withInput()->with('duplicate_warning', [
                'message'         => 'Data dengan kombinasi Metadata, Lokasi, dan Waktu yang sama sudah terdaftar.',
                'existing_id'     => $duplicate->id,
                'existing_status' => $duplicate->status_label,
            ]);
        }

        // ── Simpan data ───────────────────────────────────────
        $data = Data::create([
            'user_id'         => Auth::user()->user_id,
            'metadata_id'     => $request->metadata_id,
            'location_id'     => $request->location_id,
            'time_id'         => $request->time_id,
            'rujukan_id'      => $request->rujukan_id,
            'produsen_id'     => $request->produsen_id ? : null,
            'number_value'    => $request->number_value,
            'status'          => Data::STATUS_PENDING,
            'workflow_status' => Data::WORKFLOW_DRAFT,
            'date_inputed'    => Carbon::now(),
        ]);

        // ── Audit trail: data dibuat ──────────────────────────
        $this->auditTrail->recordCreated('data', $data->id, [
            'metadata_id'  => $data->metadata_id,
            'location_id'  => $data->location_id,
            'time_id'      => $data->time_id,
            'number_value' => $data->number_value,
        ]);

        // ── Screening anomali otomatis ────────────────────────
        $screenResult = null;
        if ($request->number_value !== null) {
            $data->loadMissing(['metadata', 'time', 'location']);
            $screenResult = $this->detector->screenData($data);
        }

        // ── Redirect dengan feedback ──────────────────────────
        if ($screenResult && $screenResult['anomalies_found']) {
            $count    = count($screenResult['anomalies']);
            $severities = collect($screenResult['anomalies'])
                ->pluck('severity')
                ->unique()
                ->implode(', ');

            return redirect()
                ->route('anomaly.control.index')
                ->with('warning', "Data disimpan. Ditemukan {$count} anomali ({$severities}). Silakan lakukan review.");
        }

        return redirect()
            ->route('data.index')
            ->with('success', 'Data berhasil disimpan dan menunggu verifikasi admin.');
    }

    // ═══════════════════════════════════════════════════════════
    // IMPORT EXCEL — PREVIEW (tidak berubah)
    // ═══════════════════════════════════════════════════════════

    public function previewExcel(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|file|mimetypes:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel,application/octet-stream,application/zip|max:10240',
        ], [
            'file_excel.required' => 'File Excel wajib diupload.',
            'file_excel.mimes'    => 'File harus berformat .xlsx atau .xls.',
            'file_excel.max'      => 'Ukuran file maksimal 10MB.',
        ]);
 
        try {
            $path   = $request->file('file_excel')->getRealPath();
            $import = new \App\Imports\DataImport();
            $result = $import->preview($path);
 
            // result sudah mengandung 'outliers' dan 'outlier_count' dari DataImport
            return response()->json($result);
 
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membaca file Excel: ' . $e->getMessage(),
            ], 422);
        }
    }

    // ═══════════════════════════════════════════════════════════
    // IMPORT EXCEL — SIMPAN + screening anomali batch
    // ═══════════════════════════════════════════════════════════

    public function importExcel(Request $request)
    {
        $request->validate([
            'file_excel'      => 'required|file|mimetypes:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel,application/octet-stream,application/zip|max:10240',
            'skip_duplicates' => 'nullable|boolean',
            // excluded_keys: array of record keys yang user pilih untuk TIDAK diimport
            'excluded_keys'   => 'nullable|array',
            'excluded_keys.*' => 'string',
        ]);
 
        try {
            $path = $request->file('file_excel')->getRealPath();
 
            // Bangun set key yang dikecualikan dari pilihan user di UI
            $excludedKeys = [];
            foreach ($request->input('excluded_keys', []) as $key) {
                $excludedKeys[$key] = true;
            }
 
            $import = new DataImport(
                userId:         Auth::user()->user_id,
                skipDuplicates: $request->boolean('skip_duplicates', true)
            );
 
            $result = $import->import($path, $excludedKeys);
 
            // Screening anomali untuk data yang baru diimport
            $anomalyStats = ['scanned' => 0, 'anomaliesFound' => 0];
            if ($result['imported'] > 0) {
                $anomalyStats = $this->detector->scanExistingData(batchSize: 100);
            }
 
            $message = $result['message'];
            if ($anomalyStats['anomaliesFound'] > 0) {
                $message .= " {$anomalyStats['anomaliesFound']} anomali terdeteksi — silakan cek halaman Control.";
            }
 
            if ($request->wantsJson()) {
                return response()->json([
                    'success'       => true,
                    'message'       => $message,
                    'anomaly_count' => $anomalyStats['anomaliesFound'],
                    'redirect'      => $anomalyStats['anomaliesFound'] > 0
                        ? route('anomaly.control.index')
                        : route('data.index'),
                ]);
            }
 
            return $anomalyStats['anomaliesFound'] > 0
                ? redirect()->route('anomaly.control.index')->with('warning', $message)
                : redirect()->route('data.index')->with('success', $message);
 
        } catch (\Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengimpor data: ' . $e->getMessage(),
                ], 500);
            }
            return redirect()->back()->withErrors(['file_excel' => 'Gagal mengimpor: ' . $e->getMessage()]);
        }
    }

    // ═══════════════════════════════════════════════════════════
    // TEMPLATE EXCEL (tidak berubah)
    // ═══════════════════════════════════════════════════════════

    public function downloadTemplateExcel()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $headers     = [
            'A1' => 'metadata_id', 'B1' => 'location_id',
            'C1' => 'time_id',     'D1' => 'number_value', 'E1' => 'rujukan_id',
        ];
        foreach ($headers as $cell => $val) $sheet->setCellValue($cell, $val);
        $sheet->getStyle('A1:E1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '0284C7']],
            'alignment' => ['horizontal' => 'center'],
        ]);
        $sheet->setCellValue('A2', 1); $sheet->setCellValue('B2', 1);
        $sheet->setCellValue('C2', 1); $sheet->setCellValue('D2', 100.50);
        $sheet->setCellValue('E2', 1);
        foreach (range('A', 'E') as $col) $sheet->getColumnDimension($col)->setAutoSize(true);
        $sheet->setTitle('Template Data');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="template_import_data.xlsx"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    // ═══════════════════════════════════════════════════════════
    // APPROVAL — diperbarui pakai WorkflowService
    // ═══════════════════════════════════════════════════════════

    public function approval(Request $request)
    {
        $status = $request->input('status', 0);
        $query  = Data::with(['metadata', 'location', 'time', 'user', 'latestAnomaly'])
            ->where('status', $status);

        if ($request->filled('metadata_id')) {
            $query->where('metadata_id', $request->metadata_id);
        }

        $data          = $query->orderBy('date_inputed', 'desc')->paginate(20)->withQueryString();
        $metadataList  = Metadata::select('metadata_id', 'nama')->orderBy('nama')->get();
        $pendingCount  = Data::where('status', Data::STATUS_PENDING)->count();
        $approvedCount = Data::where('status', Data::STATUS_AVAILABLE)->count();
        $rejectedCount = Data::where('status', Data::STATUS_REJECTED)->count();

        return view('pages.data.approval', compact(
            'data', 'metadataList', 'pendingCount', 'approvedCount', 'rejectedCount'
        ));
    }

    public function bulkApprove(Request $request)
    {
        $query = Data::where('status', Data::STATUS_PENDING);
        if ($request->filled('metadata_id')) {
            $query->where('metadata_id', $request->metadata_id);
        }

        $dataItems = $query->get();
        $count     = 0;

        foreach ($dataItems as $item) {
            $this->workflow->approveData($item);
            $count++;
        }

        return redirect()
            ->route('data.approval', $request->only('metadata_id'))
            ->with('success', "{$count} data berhasil disetujui.");
    }

    public function approve(Data $datum)
    {
        $this->workflow->approveData($datum);
        return redirect()->back()->with('success',
            "Data #{$datum->id} dari metadata {$datum->metadata->nama} berhasil diverifikasi."
        );
    }

    public function reject(Data $datum)
    {
        $reason = request('reason', 'Ditolak oleh administrator.');
        $this->workflow->rejectData($datum, $reason);
        return redirect()->back()->with('success',
            "Data #{$datum->id} dari metadata {$datum->metadata->nama} ditolak."
        );
    }

    // ═══════════════════════════════════════════════════════════
    // SHOW
    // ═══════════════════════════════════════════════════════════

    public function show(Data $datum)
    {
        $datum->load([
            'metadata', 'location', 'time', 'user',
            'anomalies.reviews.reviewer',
            'produsen',
        ]);

        // Audit trail histori untuk tab timeline
        $auditHistory = $this->auditTrail->getHistory('data', $datum->id);

        return view('pages.data.show', compact('datum', 'auditHistory'));
    }

    // ═══════════════════════════════════════════════════════════
    // TEMPLATE TAMPILAN (tidak berubah)
    // ═══════════════════════════════════════════════════════════

    public function storeTemplate(Request $request)
    {
        $request->validate([
            'nama_tampilan'      => 'required|max:100',
            'filter_metadata_id' => 'nullable|exists:metadata,metadata_id',
            'filter_wilayah_id'  => 'nullable|exists:location,location_id',
            'filter_year'        => 'nullable|integer|min:1900|max:2100',
            'data_ids'           => 'nullable|array',
            'data_ids.*'         => 'exists:data,id',
        ]);

        $filterParams = array_filter([
            'metadata_id'       => $request->filter_metadata_id,
            'filter_wilayah_id' => $request->filter_wilayah_id,
            'year'              => $request->filter_year,
        ]);

        $tampilan = Tampilan::create([
            'nama_tampilan' => $request->nama_tampilan,
            'user_id'       => Auth::user()->user_id,
            'filter_params' => $filterParams ?: null,
        ]);

        if ($request->filled('filter_metadata_id')) {
            IsiTampilan::create([
                'tampilan_id' => $tampilan->tampilan_id,
                'metadata_id' => $request->filter_metadata_id,
            ]);
        }
        if ($request->filled('data_ids')) {
            $tampilan->dataItems()->sync($request->data_ids);
        }

        return redirect()
            ->route('data.index', ['template_id' => $tampilan->tampilan_id])
            ->with('success', "Template \"{$request->nama_tampilan}\" berhasil disimpan.");
    }

    public function deleteTemplate(Tampilan $tampilan)
    {
        if ($tampilan->user_id !== Auth::user()->user_id) abort(403);
        $tampilan->delete();
        return redirect()->route('data.index')->with('success', 'Template berhasil dihapus.');
    }
}