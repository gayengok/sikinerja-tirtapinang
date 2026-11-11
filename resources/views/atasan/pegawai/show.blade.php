@extends('layouts.app')

@section('title', 'Detail Pegawai Bawahan')

@section('content')
    <div class="atasan-employee-detail-page">
        <div class="atasan-mobile-breadcrumb">
            <a href="{{ route('atasan.pegawai.index') }}" aria-label="Kembali ke pegawai bawahan"><i
                    class="fa-solid fa-arrow-left"></i></a>
            <div><span>Pegawai Bawahan</span><strong>Detail Pegawai</strong></div>
        </div>
        <section class="atasan-detail-hero">
            <span class="atasan-detail-avatar">{{ strtoupper(substr($pegawai->nama, 0, 2)) }}</span>
            <h1>{{ $pegawai->nama }}</h1>
            <p>{{ $pegawai->jabatan->nama_jabatan ?? '-' }}</p>
            <span class="atasan-detail-status">{{ ucfirst($pegawai->status) }}</span>
        </section>
        <section class="atasan-detail-card">
            <h2><i class="fa-regular fa-id-card"></i> Informasi Pegawai</h2>
            @foreach ([['Nama', $pegawai->nama], ['Email', $pegawai->user->email ?? '-'], ['Divisi', $pegawai->jabatan->divisi->nama_divisi ?? '-'], ['Jabatan', $pegawai->jabatan->nama_jabatan ?? '-'], ['Grade', $pegawai->jabatan->grade ?? '-'], ['Tanggal Masuk', $pegawai->tanggal_masuk ? \Carbon\Carbon::parse($pegawai->tanggal_masuk)->translatedFormat('d F Y') : '-'], ['Atasan', $pegawai->atasan->nama ?? '-']] as $item)
                <div class="atasan-detail-row"><span>{{ $item[0] }}</span><strong>{{ $item[1] }}</strong></div>
            @endforeach
        </section>
        <div class="atasan-detail-stats">
            <div><i class="fa-regular fa-file-lines"></i><strong>{{ $jumlahLaporan }}</strong><span>Total Laporan</span>
            </div>
            <div><i class="fa-solid fa-hourglass-half"></i><strong>{{ $laporanMenunggu }}</strong><span>Menunggu
                    Approval</span></div>
            <div><i class="fa-regular fa-calendar-check"></i><strong>{{ $jumlahKehadiran }}</strong><span>Kehadiran</span>
            </div>
            <div><i class="fa-solid fa-chart-line"></i><strong>{{ $hasilTerakhir->nilai_akhir ?? '-' }}</strong><span>Nilai
                    Terakhir</span></div>
        </div>
        <section class="atasan-detail-card">
            <h2><i class="fa-solid fa-chart-line"></i> Penilaian Terakhir</h2>
            @if ($hasilTerakhir)
                <div class="atasan-detail-row"><span>Nilai Akhir</span><strong>{{ $hasilTerakhir->nilai_akhir }}</strong>
                </div>
                <div class="atasan-detail-row"><span>Predikat</span><strong>{{ $hasilTerakhir->predikat }}</strong></div>
                <div class="atasan-detail-row"><span>Tukin Neto</span><strong>Rp
                        {{ number_format($hasilTerakhir->tukin_neto ?? 0, 0, ',', '.') }}</strong></div>
            @else
                <p class="atasan-detail-empty">Belum ada data penilaian.</p>
            @endif
        </section>
        <div class="atasan-detail-actions"><a href="{{ route('atasan.absensi.index', ['search' => $pegawai->nama]) }}"><i
                    class="fa-regular fa-calendar-check"></i> Lihat Data Absensi</a><a
                href="{{ route('atasan.laporan.index') }}"><i class="fa-regular fa-file-lines"></i> Lihat Laporan</a><a
                href="{{ route('atasan.hasil-penilaian.show', $pegawai) }}"><i class="fa-solid fa-chart-line"></i> Lihat
                Hasil Penilaian</a></div>
    </div>
@endsection

@push('styles')
    <style>
        body.role-atasan .atasan-employee-detail-page {
            padding: 1rem;
            color: #17213b
        }

        .atasan-detail-hero {
            padding: 1.2rem;
            text-align: center;
            color: #fff;
            background: linear-gradient(145deg, #08405f, #146b91);
            border-radius: 20px
        }

        .atasan-detail-avatar {
            display: grid;
            place-items: center;
            width: 68px;
            height: 68px;
            margin: 0 auto .65rem;
            background: #ffffff2b;
            border: 2px solid #ffffff66;
            border-radius: 20px;
            font-size: 1.25rem;
            font-weight: 800
        }

        .atasan-detail-hero h1 {
            margin: 0;
            font-size: 1.2rem
        }

        .atasan-detail-hero p {
            margin: .25rem 0 .55rem;
            color: #cce4f0;
            font-size: .75rem
        }

        .atasan-detail-status {
            padding: .3rem .65rem;
            background: #ffffff22;
            border-radius: 10px;
            font-size: .68rem
        }

        .atasan-detail-card {
            margin-top: 1rem;
            padding: 1rem;
            background: #fff;
            border: 1px solid #dfe7f0;
            border-radius: 18px
        }

        .atasan-detail-card h2 {
            margin: 0 0 .8rem;
            color: #273b58;
            font-size: .82rem
        }

        .atasan-detail-card h2 i {
            margin-right: .35rem;
            color: #078ac8
        }

        .atasan-detail-row {
            display: flex;
            justify-content: space-between;
            gap: .7rem;
            padding: .65rem 0;
            border-top: 1px solid #edf1f6;
            font-size: .72rem
        }

        .atasan-detail-row span {
            color: #8a9ab4
        }

        .atasan-detail-row strong {
            color: #34435b;
            text-align: right
        }

        .atasan-detail-stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: .65rem;
            margin-top: 1rem
        }

        .atasan-detail-stats div {
            padding: .8rem;
            background: #fff;
            border: 1px solid #dfe7f0;
            border-radius: 15px
        }

        .atasan-detail-stats i {
            display: block;
            margin-bottom: .4rem;
            color: #078ac8
        }

        .atasan-detail-stats strong,
        .atasan-detail-stats span {
            display: block
        }

        .atasan-detail-stats strong {
            font-size: 1.2rem
        }

        .atasan-detail-stats span {
            margin-top: .15rem;
            color: #7789a4;
            font-size: .65rem
        }

        .atasan-detail-empty {
            margin: 0;
            color: #8a9ab4;
            font-size: .75rem
        }

        .atasan-detail-actions {
            display: grid;
            gap: .6rem;
            margin-top: 1rem
        }

        .atasan-detail-actions a {
            padding: .8rem;
            color: #078ac8;
            background: #eef9ff;
            border: 1px solid #cbeeff;
            border-radius: 13px;
            text-decoration: none;
            font-size: .74rem;
            font-weight: 700
        }

        .atasan-detail-actions i {
            width: 20px;
            margin-right: .35rem
        }
    </style>
@endpush
