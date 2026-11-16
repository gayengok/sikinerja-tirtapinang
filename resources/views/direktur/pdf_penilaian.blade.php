<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Hasil Penilaian KPI</title>
    <style>
        @page {
            margin: 28px 30px 34px;
        }

        body {
            color: #17314b;
            font-family: DejaVu Sans, sans-serif;
            font-size: 8px;
            line-height: 1.4;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .brand td {
            vertical-align: middle;
        }

        .brand-logo {
            width: 54px;
            height: 45px;
        }

        .brand-copy small,
        .brand-copy strong,
        .brand-copy span {
            display: block;
        }

        .brand-copy small {
            color: #1879a7;
            font-size: 7px;
            font-weight: bold;
        }

        .brand-copy strong {
            margin: 2px 0;
            font-size: 11px;
        }

        .brand-copy span,
        .report-subtitle,
        .section-note {
            color: #71859a;
            font-size: 7px;
        }

        .rule {
            margin: 10px 0 12px;
            border-top: 1px solid #26435e;
        }

        .report-title {
            margin: 0 0 3px;
            font-size: 10px;
            font-weight: bold;
        }

        .report-subtitle {
            margin: 0;
        }

        .identity {
            margin: 14px 0 12px;
            border: 1px solid #dce6ef;
        }

        .identity td {
            width: 33.33%;
            padding: 8px 9px;
            border-right: 1px solid #e6edf3;
        }

        .identity td:last-child {
            border-right: 0;
        }

        .label {
            display: block;
            margin-bottom: 3px;
            color: #7a8ea2;
            font-size: 6px;
            font-weight: bold;
        }

        .identity strong,
        .identity small {
            display: block;
        }

        .identity strong {
            font-size: 8px;
        }

        .identity small {
            margin-top: 2px;
            color: #71859a;
            font-size: 6px;
        }

        .section-heading {
            margin: 12px 0 5px;
            padding: 5px 7px;
            color: #174e72;
            background: #eaf4fb;
            border-left: 3px solid #1594c5;
            font-size: 8px;
            font-weight: bold;
        }

        .section-note {
            margin: -2px 0 6px;
        }

        .kpi-table {
            table-layout: fixed;
        }

        .kpi-table th {
            padding: 5px 4px;
            color: #38536e;
            background: #f0f5f9;
            border: 1px solid #dce6ef;
            font-size: 6px;
            text-align: left;
        }

        .kpi-table td {
            padding: 5px 4px;
            border: 1px solid #e1e9f0;
            vertical-align: top;
            word-wrap: break-word;
        }

        .kpi-table tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        .center,
        .number {
            text-align: center !important;
        }

        .right {
            text-align: right !important;
        }

        .kpi-table tfoot td {
            color: #174e72;
            background: #edf6fc;
            font-weight: bold;
        }

        .empty {
            padding: 10px !important;
            color: #71859a;
            text-align: center;
        }

        .tukin-table {
            margin-top: 2px;
        }

        .tukin-table td {
            padding: 5px 7px;
            border-bottom: 1px solid #e7edf3;
        }

        .tukin-table .amount {
            color: #17314b;
            font-weight: bold;
            text-align: right;
            white-space: nowrap;
        }

        .deduction-title {
            color: #b42338;
            background: #fff5f5;
            font-weight: bold;
        }

        .deduction-row td {
            color: #7a5260;
            background: #fffafa;
            font-size: 7px;
        }

        .net-row td {
            padding: 8px 7px;
            color: #0e4b70;
            background: #eaf4fb;
            font-weight: bold;
        }

        .net-row .amount {
            font-size: 11px;
        }

        .notice {
            padding: 8px;
            color: #7b5b13;
            background: #fff8e6;
            border: 1px solid #f2dfa9;
        }

        .printed-at {
            margin-top: 14px;
            color: #64788d;
            font-size: 7px;
            text-align: right;
        }

        .signatures {
            margin-top: 14px;
            page-break-inside: avoid;
        }

        .signatures td {
            width: 33.33%;
            padding: 5px 7px;
            text-align: center;
            vertical-align: top;
        }

        .signature-role {
            min-height: 12px;
            margin-top: 2px;
            font-weight: bold;
        }

        .signature-space {
            height: 42px;
        }

        .signature-name {
            padding-top: 3px;
            border-top: 1px solid #637990;
            font-weight: bold;
            text-decoration: underline;
        }

        .signature-nip {
            margin-top: 2px;
            color: #71859a;
            font-size: 6px;
        }
    </style>
</head>

<body>
    <table class="brand">
        <tr>
            <td style="width: 62px;">
                <img class="brand-logo" src="{{ $logo }}" alt="Logo Perumda Tirta Pinang">
            </td>
            <td class="brand-copy">
                <small>PERUSAHAAN UMUM DAERAH AIR MINUM TIRTA PINANG</small>
                <strong>LEMBAR RINCIAN PENILAIAN KPI &amp; PERHITUNGAN TUNJANGAN KINERJA</strong>
                <span>Dokumen hasil evaluasi kinerja dan perhitungan tunjangan pegawai.</span>
            </td>
        </tr>
    </table>
    <div class="rule"></div>
    <p class="report-title">Hasil Penilaian KPI Kepala Bagian</p>
    <p class="report-subtitle">
        Periode {{ $periode?->nama_periode ?? 'Belum tersedia' }}
        &bull; Status: {{ $hasilTunjangan?->status === 'dikirim' ? 'Menunggu Verifikasi Final' : ($hasilTunjangan ? 'Draft / Meninjau Ulang' : 'Belum dihitung') }}
    </p>

    <table class="identity">
        <tr>
            <td>
                <span class="label">Kepala Bagian yang Dinilai</span>
                <strong>{{ $pegawai->nama }}</strong>
                <small>{{ $pegawai->jabatan?->nama_jabatan ?? '-' }} &bull; NIP {{ $pegawai->nip ?? '-' }}</small>
            </td>
            <td>
                <span class="label">Pejabat Penilai</span>
                <strong>{{ $penilai->nama }}</strong>
                <small>{{ $penilai->jabatan?->nama_jabatan ?? 'Direktur' }}</small>
            </td>
            <td>
                <span class="label">Unit / Periode</span>
                <strong>{{ $pegawai->jabatan?->divisi?->nama_divisi ?? '-' }}</strong>
                <small>{{ $periode?->nama_periode ?? 'Periode belum tersedia' }}</small>
            </td>
        </tr>
    </table>

    <div class="section-heading">I. Rincian Capaian Hasil KPI</div>
    <p class="section-note">Rekap capaian berbobot dan skor setiap indikator kinerja yang dinilai.</p>
    <table class="kpi-table">
        <colgroup>
            <col style="width: 5%">
            <col style="width: 37%">
            <col style="width: 10%">
            <col style="width: 12%">
            <col style="width: 12%">
            <col style="width: 10%">
            <col style="width: 14%">
        </colgroup>
        <thead>
            <tr>
                <th class="center">No</th>
                <th>Indikator Kinerja Utama (IKU) / Sasaran</th>
                <th class="center">Bobot</th>
                <th class="right">Target</th>
                <th class="right">Realisasi</th>
                <th class="center">Skor (1-5)</th>
                <th class="right">Tertimbang</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($kpiRows as $row)
                @php
                    $masterKpi = $row['masterKpi'];
                    $penilaian = $row['penilaian'];
                @endphp
                <tr>
                    <td class="center">{{ $loop->iteration }}</td>
                    <td>{{ $masterKpi->indikator }}</td>
                    <td class="center">{{ number_format((float) $masterKpi->bobot, 0, ',', '.') }}%</td>
                    <td class="right">
                        {{ $penilaian?->target !== null
                            ? number_format((float) $penilaian->target, 2, ',', '.')
                            : ($masterKpi->target_default !== null ? number_format((float) $masterKpi->target_default, 2, ',', '.') : '-') }}
                    </td>
                    <td class="right">
                        {{ $penilaian?->realisasi_final !== null ? number_format((float) $penilaian->realisasi_final, 2, ',', '.') : '-' }}
                    </td>
                    <td class="center">{{ $penilaian?->skor !== null ? number_format((float) $penilaian->skor, 2, ',', '.') : '-' }}</td>
                    <td class="right">{{ number_format((float) $row['tertimbang'], 2, ',', '.') }}%</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="empty">Belum ada indikator KPI untuk ditampilkan.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" class="right">TOTAL / RATA-RATA:</td>
                <td class="center">{{ number_format((float) $totalBobotKpi, 0, ',', '.') }}%</td>
                <td colspan="3" class="right">Rata-rata skor KPI</td>
                <td class="right">{{ number_format((float) $rataSkorKpi, 2, ',', '.') }} / 5</td>
            </tr>
            <tr>
                <td colspan="6" class="right">TOTAL AKUMULASI NILAI KPI:</td>
                <td class="right">{{ number_format((float) $capaianKpi, 2, ',', '.') }}%</td>
            </tr>
        </tfoot>
    </table>

    <div class="section-heading">II. Rincian Tunjangan Kinerja (Tukin)</div>
    <p class="section-note">Kalkulasi berdasarkan hasil penilaian dan kedisiplinan presensi.</p>
    @if ($hasilTunjangan)
        @php
            $nilaiAkhir = min(100, max(0, (float) ($hasilTunjangan->nilai_akhir ?? 0)));
            $tukinBruto = (float) ($hasilTunjangan->tukin_bruto ?? 0);
            $totalPotongan = (float) ($hasilTunjangan->total_potongan_presensi ?? 0);
            $nilaiPotongan = $tukinBruto * $totalPotongan / 100;
        @endphp
        <table class="tukin-table">
            <tr>
                <td>Base Tukin Jabatan</td>
                <td class="amount">Rp {{ number_format((float) ($pegawai->jabatan?->base_tukin ?? 0), 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Tukin Bruto (Capaian {{ number_format($nilaiAkhir, 1, ',', '.') }}%)</td>
                <td class="amount">Rp {{ number_format($tukinBruto, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="deduction-title">Potongan Presensi ({{ number_format($totalPotongan, 2, ',', '.') }}%)</td>
                <td class="amount deduction-title">-Rp {{ number_format($nilaiPotongan, 0, ',', '.') }}</td>
            </tr>
            @forelse ($presensiPotongans as $potongan)
                <tr class="deduction-row">
                    <td>
                        {{ $potongan->jenisPotongan?->nama_potongan ?? 'Potongan Presensi' }}
                        ({{ $potongan->jumlah_kejadian }} kejadian)
                        &bull; Tarif {{ number_format((float) ($potongan->jenisPotongan?->persen_potongan ?? 0), 2, ',', '.') }}% per kejadian
                    </td>
                    <td class="amount">
                        -Rp {{ number_format($tukinBruto * (float) $potongan->persen_potongan / 100, 0, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr class="deduction-row">
                    <td>Tidak ada potongan presensi</td>
                    <td class="amount">Rp 0</td>
                </tr>
            @endforelse
            <tr class="net-row">
                <td>TUKIN NETO DITERIMA</td>
                <td class="amount">Rp {{ number_format((float) ($hasilTunjangan->tukin_neto ?? 0), 0, ',', '.') }}</td>
            </tr>
        </table>
    @else
        <div class="notice">Hasil tunjangan belum tersedia untuk periode ini.</div>
    @endif

    <p class="printed-at">
        Ditetapkan di Pangkalpinang, tanggal {{ $tanggalCetak->locale('id')->translatedFormat('d F Y') }}
    </p>

    <table class="signatures">
        <tr>
            <td>
                <span>KEPALA BAGIAN YANG DINILAI,</span>
                <div class="signature-space"></div>
                <div class="signature-name">{{ $pegawai->nama }}</div>
                <div class="signature-nip">NIP. {{ $pegawai->nip ?? '-' }}</div>
            </td>
            <td>
                <span>PEJABAT PENILAI,</span>
                <div class="signature-role">Direktur</div>
                <div class="signature-space"></div>
                <div class="signature-name">{{ $penilai->nama }}</div>
                <div class="signature-nip">NIP. {{ $penilai->nip ?? '-' }}</div>
            </td>
            <td>
                <span>MENGESAHKAN,</span>
                <div class="signature-role">Kepala Divisi SDM &amp; Remunerasi</div>
                <div class="signature-space"></div>
                <div class="signature-name">&nbsp;</div>
                <div class="signature-nip">NIP. __________________</div>
            </td>
        </tr>
    </table>
</body>

</html>