<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Hasil Penilaian KPI</title>
    <style>
        @page {
            margin: 24px 28px 30px;
        }

        body {
            margin: 0;
            color: #17314b;
            font-family: DejaVu Sans, sans-serif;
            font-size: 7px;
            line-height: 1.3;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .brand td {
            vertical-align: middle;
        }

        .brand-logo {
            width: 42px;
            height: 38px;
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
            margin: 2px 0 1px;
            font-size: 10px;
        }

        .brand-copy span,
        .report-subtitle,
        .section-note {
            color: #71859a;
            font-size: 7px;
        }

        .rule {
            margin: 7px 0 14px;
            border-top: 2px solid #26435e;
        }

        .section-heading {
            margin: 11px 0 5px;
            padding-left: 7px;
            color: #26394d;
            border-left: 4px solid #26394d;
            font-size: 8px;
            font-weight: bold;
        }

        .section-note {
            margin: -2px 0 5px;
        }

        .kpi-table {
            table-layout: fixed;
        }

        .kpi-table th {
            padding: 6px 4px;
            color: #38536e;
            background: #f0f5f9;
            border: 1px solid #dce6ef;
            font-size: 6px;
            text-align: center;
        }

        .kpi-table th:nth-child(2) {
            text-align: left;
        }

        .kpi-table td {
            padding: 6px 4px;
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
            margin-top: 3px;
            page-break-inside: avoid;
        }

        .tukin-table td {
            padding: 6px 7px;
            border: 1px solid #e1e9f0;
        }

        .tukin-table td:first-child {
            width: 72%;
        }

        .tukin-table .description {
            display: block;
            margin-top: 2px;
            color: #71859a;
            font-size: 6px;
            font-weight: normal;
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

        .net-row td {
            padding: 8px 7px;
            color: #0e4b70;
            background: #eaf4fb;
            font-weight: bold;
        }

        .net-row .description {
            color: #526b80;
            font-style: italic;
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
            margin-top: 12px;
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

    <div class="section-heading">I. Rincian Capaian Hasil KPI</div>
    <table class="kpi-table">
        <colgroup>
            <col style="width: 5%">
            <col style="width: 42%">
            <col style="width: 8%">
            <col style="width: 11%">
            <col style="width: 11%">
            <col style="width: 11%">
            <col style="width: 12%">
        </colgroup>
        <thead>
            <tr>
                <th class="center">No</th>
                <th>Indikator Kinerja Utama (IKU) / Sasaran</th>
                <th class="center">Bobot</th>
                <th>Target</th>
                <th>Realisasi</th>
                <th class="center">Skor (1-5)</th>
                <th>Tertimbang</th>
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
                    <td class="center">
                        {{ $penilaian?->target !== null
                            ? number_format((float) $penilaian->target, 2, ',', '.')
                            : ($masterKpi->target_default !== null
                                ? number_format((float) $masterKpi->target_default, 2, ',', '.')
                                : '-') }}{{ $penilaian?->target !== null || $masterKpi->target_default !== null ? '%' : '' }}
                    </td>
                    <td class="center">
                        {{ $penilaian?->realisasi_final !== null ? number_format((float) $penilaian->realisasi_final, 2, ',', '.') . '%' : '-' }}
                    </td>
                    <td class="center">
                        {{ $penilaian?->skor !== null ? number_format((float) $penilaian->skor, 2, ',', '.') : '-' }}
                    </td>
                    <td class="center">
                        {{ $penilaian?->skor !== null && (float) $totalBobotKpi > 0
                            ? number_format((float) $penilaian->skor * ((float) $masterKpi->bobot / (float) $totalBobotKpi), 2, ',', '.')
                            : '-' }}
                    </td>
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
    @if ($hasilTunjangan)
        @php
            $nilaiAkhir = min(100, max(0, (float) ($hasilTunjangan->nilai_akhir ?? 0)));
            $tukinBruto = (float) ($hasilTunjangan->tukin_bruto ?? 0);
            $totalPotongan = (float) ($hasilTunjangan->total_potongan_presensi ?? 0);
            $nilaiPotongan = ($tukinBruto * $totalPotongan) / 100;
            $tukinNeto = (int) round((float) ($hasilTunjangan->tukin_neto ?? 0));
            $terbilang = function (int $angka) use (&$terbilang): string {
                $kata = [
                    'nol',
                    'satu',
                    'dua',
                    'tiga',
                    'empat',
                    'lima',
                    'enam',
                    'tujuh',
                    'delapan',
                    'sembilan',
                    'sepuluh',
                    'sebelas',
                ];

                if ($angka < 12) {
                    return $kata[$angka];
                }
                if ($angka < 20) {
                    return $terbilang($angka - 10) . ' belas';
                }
                if ($angka < 100) {
                    return $terbilang(intdiv($angka, 10)) .
                        ' puluh' .
                        ($angka % 10 ? ' ' . $terbilang($angka % 10) : '');
                }
                if ($angka < 200) {
                    return 'seratus' . ($angka - 100 ? ' ' . $terbilang($angka - 100) : '');
                }
                if ($angka < 1000) {
                    return $terbilang(intdiv($angka, 100)) .
                        ' ratus' .
                        ($angka % 100 ? ' ' . $terbilang($angka % 100) : '');
                }
                if ($angka < 2000) {
                    return 'seribu' . ($angka - 1000 ? ' ' . $terbilang($angka - 1000) : '');
                }
                if ($angka < 1000000) {
                    return $terbilang(intdiv($angka, 1000)) .
                        ' ribu' .
                        ($angka % 1000 ? ' ' . $terbilang($angka % 1000) : '');
                }
                if ($angka < 1000000000) {
                    return $terbilang(intdiv($angka, 1000000)) .
                        ' juta' .
                        ($angka % 1000000 ? ' ' . $terbilang($angka % 1000000) : '');
                }

                return $terbilang(intdiv($angka, 1000000000)) .
                    ' miliar' .
                    ($angka % 1000000000 ? ' ' . $terbilang($angka % 1000000000) : '');
            };
        @endphp
        <table class="tukin-table">
            <tr>
                <td>
                    Base Tukin Jabatan (Plafon Struktural)
                    <span class="description">Standar honorarium kinerja maksimal posisi Kepala Bagian</span>
                </td>
                <td class="amount">Rp {{ number_format((float) ($pegawai->jabatan?->base_tukin ?? 0), 0, ',', '.') }}
                </td>
            </tr>
            <tr>
                <td>
                    Tukin Bruto (Capaian {{ number_format($nilaiAkhir, 2, ',', '.') }}%)
                    <span class="description">
                        Formula: {{ number_format($nilaiAkhir, 2, ',', '.') }}% x Rp
                        {{ number_format((float) ($pegawai->jabatan?->base_tukin ?? 0), 0, ',', '.') }}
                    </span>
                </td>
                <td class="amount">Rp {{ number_format($tukinBruto, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="deduction-title">
                    Potongan Presensi &amp; Disiplin Kerja ({{ number_format($totalPotongan, 2, ',', '.') }}%)
                    <span class="description">
                        @if ($presensiPotongans->isNotEmpty())
                            {{ $presensiPotongans->map(
                                    fn($potongan) => sprintf(
                                        '%s %s kejadian (%s%%)',
                                        $potongan->jenisPotongan?->nama_potongan ?? 'Potongan Presensi',
                                        $potongan->jumlah_kejadian,
                                        number_format((float) $potongan->persen_potongan, 0, ',', '.'),
                                    ),
                                )->implode(' + ') }}
                        @else
                            Tidak ada potongan presensi
                        @endif
                    </span>
                </td>
                <td class="amount deduction-title">-Rp {{ number_format($nilaiPotongan, 0, ',', '.') }}</td>
            </tr>
            <tr class="net-row">
                <td>
                    TUKIN NETO DITERIMA
                    <span class="description">Terbilang: "{{ ucfirst($terbilang($tukinNeto)) }} Rupiah"</span>
                </td>
                <td class="amount">Rp {{ number_format($tukinNeto, 0, ',', '.') }}</td>
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
