<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Rekap Kehadiran dan Tunjangan Pegawai</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 18mm 12mm 15mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: #15233b;
            font-family: DejaVu Sans, sans-serif;
            font-size: 8px;
        }

        .brand {
            width: 100%;
            border-bottom: 1px solid #d9e2ec;
            padding-bottom: 8px;
        }

        .brand-logo {
            display: block;
            width: 82px;
            height: 48px;
            object-fit: contain;
        }

        .brand-copy {
            padding-left: 10px;
            vertical-align: middle;
        }

        .brand-copy small {
            display: block;
            margin-bottom: 3px;
            color: #168278;
            font-size: 7px;
            letter-spacing: .8px;
        }

        .brand-copy strong {
            display: block;
            font-size: 12px;
        }

        .brand-copy span {
            color: #64748b;
            font-size: 7px;
        }

        .report-heading {
            padding: 10px 0 8px;
            border-bottom: 2px solid #8290a3;
            text-align: center;
        }

        .report-heading h1 {
            margin: 0 0 4px;
            font-size: 12px;
            font-weight: 700;
        }

        .report-heading p {
            margin: 0;
            color: #475569;
            font-size: 8px;
        }

        .report-table {
            width: 100%;
            margin-top: 10px;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .report-table .column-number {
            width: 4%;
        }

        .report-table .column-name {
            width: 17%;
        }

        .report-table .column-position {
            width: 16%;
        }

        .report-table .column-division {
            width: 16%;
        }

        .report-table .column-period {
            width: 19%;
        }

        .report-table .column-attendance {
            width: 13%;
        }

        .report-table .column-allowance {
            width: 15%;
        }

        .report-table th,
        .report-table td {
            padding: 6px 5px;
            border: 1px solid #d8e0e9;
            vertical-align: middle;
        }

        .report-table thead {
            display: table-header-group;
        }

        .report-table th {
            color: #334155;
            background: #f0f4f8;
            font-size: 8px;
            font-weight: 700;
            text-align: left;
            text-transform: uppercase;
        }

        .report-table td {
            font-size: 8px;
        }

        .report-table tbody tr {
            page-break-inside: avoid;
        }

        .report-table .number,
        .report-table .attendance {
            text-align: center;
        }

        .report-table .period {
            color: #475569;
            text-align: center;
            white-space: nowrap;
        }

        .report-table .allowance {
            text-align: right;
            white-space: nowrap;
        }

        .report-table .pending {
            color: #a16207;
        }

        .report-table tfoot th,
        .report-table tfoot td {
            background: #f0f4f8;
            font-weight: 700;
        }

        .report-table tfoot .total-label {
            text-align: right;
        }

        .signatures {
            width: 100%;
            margin-top: 22px;
            page-break-inside: avoid;
        }

        .signatures td {
            width: 50%;
            text-align: center;
            vertical-align: top;
        }

        .signature-intro {
            height: 28px;
        }

        .signature-place {
            text-align: center;
        }

        .signature-approval {
            margin-top: 2px;
        }

        .signature-role {
            height: 24px;
            margin: 0;
        }

        .signature-space {
            height: 52px;
        }

        .signature-line {
            width: 155px;
            margin: 0 auto;
            border-bottom: 1px solid #15233b;
        }

        .signature-nip {
            width: 155px;
            margin: 4px auto 0;
            color: #475569;
            font-size: 7px;
            text-align: left;
        }

        .signature-nip-line {
            display: inline-block;
            width: 118px;
            margin-left: 3px;
            border-bottom: 1px solid #8290a3;
            vertical-align: middle;
        }

        .signature-left .signature-intro,
        .signature-left .signature-role {
            text-align: center;
        }

        .signature-right .signature-role {
            text-align: center;
        }

        .signature-right .signature-place,
        .signature-right .signature-approval {
            text-align: center;
        }

        .empty {
            padding: 20px !important;
            color: #64748b;
            text-align: center;
        }
    </style>
</head>

<body>
    {{-- <table class="brand">
        <tr>
            <td style="width: 92px; vertical-align: middle;">
                <img class="brand-logo" src="{{ $logo }}" alt="Logo SIKINERJA">
            </td>
            <td class="brand-copy">
                <small>PERUMDA TIRTA PINANG</small>
                <strong>SIKINERJA</strong>
                <span>Sistem Informasi Kinerja dan Tunjangan Pegawai</span>
            </td>
        </tr>
    </table> --}}

    <header class="report-heading">
        <h1>REKAPITULASI DATA KEHADIRAN DAN TUNJANGAN PEGAWAI</h1>
        <p>
            Periode {{ \Carbon\Carbon::create($tahun, $bulan, 1)->locale('id')->translatedFormat('F Y') }}
            ({{ $tanggalMulai->format('d-m-Y') }} s/d {{ $tanggalSelesai->format('d-m-Y') }})
            &bull; Total terdata: {{ $pegawais->count() }} Personel
        </p>
    </header>

    <table class="report-table">
        <colgroup>
            <col style="width: 4%">
            <col style="width: 17%">
            <col style="width: 16%">
            <col style="width: 16%">
            <col style="width: 19%">
            <col style="width: 13%">
            <col style="width: 15%">
        </colgroup>
        <thead>
            <tr>
                <th class="number column-number">No</th>
                <th class="column-name">Nama Pegawai</th>
                <th class="column-position">Jabatan</th>
                <th class="column-division">Divisi</th>
                <th class="period column-period">Periode Tanggal</th>
                <th class="attendance column-attendance">Jumlah Kehadiran</th>
                <th class="allowance column-allowance">Tunjangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($pegawais as $pegawai)
                <tr>
                    <td class="number">{{ $loop->iteration }}</td>
                    <td>{{ $pegawai->nama }}</td>
                    <td>{{ $pegawai->jabatan?->nama_jabatan ?? '-' }}</td>
                    <td>{{ $pegawai->jabatan?->divisi?->nama_divisi ?? '-' }}</td>
                    <td class="period">
                        {{ $tanggalMulai->format('d-m-Y') }} s/d {{ $tanggalSelesai->format('d-m-Y') }}
                    </td>
                    <td class="attendance">{{ $pegawai->jumlah_kehadiran }} hari</td>
                    <td class="allowance {{ $pegawai->tukin_neto === null ? 'pending' : '' }}">
                        {{ $pegawai->tukin_neto !== null ? 'Rp ' . number_format((float) $pegawai->tukin_neto, 0, ',', '.') : 'Belum dihitung' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="empty">Belum ada data pegawai untuk ditampilkan.</td>
                </tr>
            @endforelse
        </tbody>
        @if ($pegawais->isNotEmpty())
            <tfoot>
                <tr>
                    <th colspan="5" class="total-label">TOTAL AKUMULASI (TERCATAT):</th>
                    <th class="attendance">{{ $totalKehadiran }} hari</th>
                    <th class="allowance">
                        {{ $tukinTersedia ? 'Rp ' . number_format($totalTukin, 0, ',', '.') : 'Belum dihitung' }}
                    </th>
                </tr>
            </tfoot>
        @endif
    </table>

    <table class="signatures">
        <tr>
            <td class="signature-left">
                <div class="signature-intro">
                    <div>DIBUAT OLEH,</div>
                    <div class="signature-approval">Staf Bagian Kepegawaian &amp; SDM</div>
                </div>
                <div class="signature-role"></div>
                <div class="signature-space"></div>
                <div class="signature-line"></div>
                <div class="signature-nip">NIP.<span class="signature-nip-line"></span></div>
            </td>
            <td class="signature-right">
                <div class="signature-intro">
                    <div class="signature-place">
                        Pangkalpinang, {{ $tanggalCetak->locale('id')->translatedFormat('d F Y') }}
                    </div>
                    <div class="signature-approval">MENGETAHUI &amp; MENYETUJUI,</div>
                </div>
                <div class="signature-role">Direktur Utama Perumda Air Minum</div>
                <div class="signature-space"></div>
                <div class="signature-line"></div>
                <div class="signature-nip">NIP.<span class="signature-nip-line"></span></div>
            </td>
        </tr>
    </table>
</body>

</html>
