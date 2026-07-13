{{--
    Laporan PDF Anomali Data — dikirim ke Produsen Data sebagai bahan pertimbangan.
    Di-render via barryvdh/laravel-dompdf.

    Variabel yang dibutuhkan (sama seperti show.blade.php):
    - $anomaly            : App\Models\Anomaly
    - $data               : App\Models\Data (dengan relasi metadata, location, produsen, rujukan, time, user)
    - $sourceComparison   : Collection perbandingan sumber (opsional, boleh kosong)
--}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Anomali Data #{{ $anomaly->anomalies_id }}</title>
    <style>
        @page {
            margin: 55px 50px 45px 50px;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #1f2937;
            line-height: 1.35;
        }

        /* ══════════ KOP SURAT ══════════ */
        .kop-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
        }
        .kop-table td {
            vertical-align: middle;
            padding: 0;
        }
        .kop-logo {
            width: 60px;
        }
        .kop-logo img {
            width: 52px;
            height: auto;
        }
        .kop-text {
            padding-left: 12px;
        }
        .kop-title {
            font-size: 15px;
            font-weight: bold;
            color: #001734;
            margin: 0;
            letter-spacing: 0.3px;
        }
        .kop-subtitle {
            font-size: 9px;
            color: #374151;
            margin: 1px 0 0 0;
        }
        .kop-address {
            font-size: 8px;
            color: #6b7280;
            margin: 2px 0 0 0;
        }
        .kop-rule-thick {
            border-top: 2.5px solid #001734;
            margin-top: 8px;
        }
        .kop-rule-thin {
            border-top: 1px solid #001734;
            margin-top: 2px;
            margin-bottom: 12px;
        }

        /* ══════════ JUDUL LAPORAN ══════════ */
        .report-title {
            text-align: center;
            margin-bottom: 12px;
        }
        .report-title h1 {
            font-size: 12px;
            text-decoration: underline;
            text-transform: uppercase;
            margin: 0 0 2px 0;
        }
        .report-title p {
            font-size: 9.5px;
            margin: 0;
        }

        /* ══════════ META SURAT (Nomor/Lampiran/Hal) ══════════ */
        .meta-surat {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 9.5px;
        }
        .meta-surat td { padding: 1px 0; }
        .meta-label { width: 70px; }
        .meta-colon { width: 12px; }

        /* ══════════ ISI SURAT ══════════ */
        .isi p {
            margin: 0 0 7px 0;
            text-align: justify;
        }

        /* ══════════ TABEL DETAIL ══════════ */
        .section-heading {
            font-size: 9.5px;
            font-weight: bold;
            text-transform: uppercase;
            color: #374151;
            margin: 9px 0 4px 0;
            letter-spacing: 0.3px;
        }
        table.detail-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
            font-size: 9.5px;
        }
        table.detail-table td {
            padding: 2px 6px;
            vertical-align: top;
        }
        table.detail-table td.label {
            width: 105px;
            color: #6b7280;
        }
        table.detail-table td.colon {
            width: 10px;
        }

        table.grid-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            font-size: 9px;
        }
        table.grid-table th,
        table.grid-table td {
            border: 1px solid #d1d5db;
            padding: 4px 7px;
        }
        table.grid-table th {
            background: #f3f4f6;
            text-align: left;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8px;
            color: #4b5563;
        }
        table.grid-table td.num { text-align: right; font-family: 'DejaVu Sans Mono', monospace; }
        table.grid-table td.center { text-align: center; }

        .highlight-box {
            background: #fef2f2;
            border: 1px solid #fecaca;
            padding: 6px 10px;
            margin-bottom: 8px;
            font-size: 9.5px;
        }
        .highlight-box .val {
            font-weight: bold;
            font-size: 11px;
            color: #b91c1c;
        }

        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 8.5px;
            font-weight: bold;
        }

        /* ══════════ TANDA TANGAN ══════════ */
        .ttd-wrap {
            width: 100%;
            margin-top: 56px;
        }
        .ttd-table {
            width: 100%;
            border-collapse: collapse;
        }
        .ttd-table td {
            width: 50%;
            vertical-align: top;
            font-size: 9.5px;
        }
        .ttd-right {
            text-align: center;
        }
        .ttd-space {
            height: 42px;
        }
        .ttd-name {
            font-weight: bold;
            text-decoration: underline;
        }

        .footer-note {
            margin-top: 12px;
            font-size: 7.5px;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
            padding-top: 4px;
        }
    </style>
</head>
<body>

    {{-- ═══════════════════════ KOP SURAT ═══════════════════════ --}}
    <table class="kop-table">
        <tr>
            <td class="kop-logo">
                <img src="{{ public_path('images/logo/PDIB-transparan-B.png') }}">
            </td>
            <td class="kop-text">
                <p class="kop-title">PUSAT DATA INDONESIA BALI</p>
                {{-- <p class="kop-subtitle">Institut Teknologi dan Bisnis (ITB) STIKOM Bali</p> --}}
                <p class="kop-address">
                    Jl. Raya Puputan No. 86, Dangin Puri Klod, Kec. Denpasar Timur, Kota Denpasar, Bali 80234
                    &nbsp;·&nbsp; Telp. (0361) 244445 &nbsp;·&nbsp; pusatdataindonesiabali@gmail.com
                </p>
            </td>
        </tr>
    </table>
    <div class="kop-rule-thick"></div>
    <div class="kop-rule-thin"></div>

    {{-- ═══════════════════════ JUDUL LAPORAN ═══════════════════════ --}}
    <div class="report-title">
        <h1>Laporan Anomali Data</h1>
        <p>Nomor: LAP-ANM/{{ $anomaly->anomalies_id }}/PDIB/{{ now()->format('m/Y') }}</p>
    </div>

    {{-- ═══════════════════════ META SURAT ═══════════════════════ --}}
    <table class="meta-surat">
        <tr>
            <td class="meta-label">Kepada Yth.</td>
            <td class="meta-colon">:</td>
            <td><strong>{{ $data->produsen?->nama_produsen ?? $data->rujukan?->produsen?->nama_produsen ?? '-' }}</strong></td>
        </tr>
        <tr>
            <td class="meta-label">Perihal</td>
            <td class="meta-colon">:</td>
            <td>Pemberitahuan Anomali pada Data Statistik yang Diinput</td>
        </tr>
    </table>

    {{-- ═══════════════════════ ISI SURAT ═══════════════════════ --}}
    <div class="isi">
        <p>Dengan hormat,</p>
        <p>
            Sehubungan dengan proses validasi data pada sistem Pusat Data Indonesia Bali (PDIB), sistem kami
            telah mendeteksi adanya indikasi anomali pada salah satu data yang bersumber dari instansi Bapak/Ibu.
            Berikut kami sampaikan rincian temuan sebagai bahan pertimbangan dan tindak lanjut.
        </p>
    </div>

    @if($anomaly->anomaly_type === \App\Models\Anomaly::TYPE_SOURCE_CONFLICT)
        <div class="highlight-box">
            Nilai dari sumber ini tercatat sebesar
            <span class="val">{{ number_format($data->number_value, 2, ',', '.') }}</span>,
            namun terindikasi berbeda signifikan dengan sumber data lain untuk
            lokasi dan periode yang sama. Rincian perbandingan
            dapat dilihat pada tabel di bawah.
        </div>
    @else
        <div class="highlight-box">
            Nilai berubah dari <strong>{{ $anomaly->previous_value !== null ? number_format($anomaly->previous_value, 2, ',', '.') : '—' }}</strong>
            menjadi <strong>{{ $anomaly->current_value !== null ? number_format($anomaly->current_value, 2, ',', '.') : '—' }}</strong>
            &nbsp;dengan&nbsp; perubahan sebesar <span class="val">{{ $anomaly->formatted_percentage_change }}</span>
        </div>
    @endif

    <div class="section-heading">Detail Data</div>
    <table class="detail-table">
        <tr>
            <td class="label">Judul Data</td>
            <td class="colon">:</td>
            <td>{{ $data->metadata?->nama ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Satuan</td>
            <td class="colon">:</td>
            <td>{{ $data->metadata?->satuan_data ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Lokasi</td>
            <td class="colon">:</td>
            <td>{{ $data->location?->nama_wilayah ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Periode</td>
            <td class="colon">:</td>
            <td>
                {{ $data->time
                    ? ($data->time->year
                        . ($data->time->month   ? '/Bln-'.$data->time->month   : '')
                        . ($data->time->quarter  ? '/Q'.$data->time->quarter    : '')
                        . ($data->time->semester ? '/S'.$data->time->semester   : ''))
                    : '-' }}
            </td>
        </tr>
        <tr>
            <td class="label">Sumber/Produsen</td>
            <td class="colon">:</td>
            <td>{{ $data->produsen?->nama_produsen ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Rujukan</td>
            <td class="colon">:</td>
            <td>{{ $data->rujukan?->nama_rujukan ?? '-' }}</td>
        </tr>
    </table>

    @if(isset($sourceComparison) && $sourceComparison->isNotEmpty())
        <div class="section-heading">Perbandingan Antar Sumber Data</div>
        <table class="grid-table">
            <thead>
                <tr>
                    <th>Rujukan</th>
                    <th style="text-align:right">Nilai</th>
                    <th style="text-align:right">Selisih</th>
                    <th style="text-align:right">% Diff</th>
                    <th style="text-align:center">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sourceComparison as $row)
                    <tr>
                        <td>{{ $row->rujukan ?? $row['rujukan'] ?? '-' }}</td>
                        <td class="num">{{ number_format($row->value ?? $row['value'] ?? 0, 2, ',', '.') }}</td>
                        <td class="num">{{ number_format($row->selisih ?? $row['selisih'] ?? 0, 2, ',', '.') }}</td>
                        <td class="num">{{ $row->pct_diff ?? $row['pct_diff'] ?? 0 }}%</td>
                        <td class="center">
                            @if($row->conflict ?? $row['conflict'] ?? false)
                                <span class="badge" style="background:#fef9c3;color:#a16207;">Konflik</span>
                            @else
                                <span class="badge" style="background:#dcfce7;color:#15803d;">OK</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="isi">
        <p>
            Kami mohon kesediaan Bapak/Ibu untuk meninjau kembali data yang telah disampaikan, mengingat
            adanya indikasi perubahan nilai yang signifikan di luar pola normal data periode sebelumnya.
            Konfirmasi atau klarifikasi dari pihak Bapak/Ibu akan sangat membantu proses validasi data pada
            sistem kami.
        </p>
        <p>
            Demikian laporan ini kami sampaikan. Atas perhatian dan kerja samanya, kami ucapkan terima kasih.
        </p>
    </div>

    {{-- ═══════════════════════ TANDA TANGAN ═══════════════════════ --}}
    <div class="ttd-wrap">
        <table class="ttd-table">
            <tr>
                <td></td>
                <td class="ttd-right">
                    Denpasar, {{ now()->translatedFormat('d F Y') }}
                    <div class="ttd-space"></div>
                    <div class="ttd-name">Kepala Pusat Data Indonesia Bali</div>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>