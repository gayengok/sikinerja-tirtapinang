<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Rekap Kehadiran dan Tunjangan Pegawai</title>
    <link rel="stylesheet" href="{{ public_path('css/kepala-bagian/rekap-data/pdf.css') }}">
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
