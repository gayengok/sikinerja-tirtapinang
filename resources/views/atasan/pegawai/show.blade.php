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
    <link rel="stylesheet" href="{{ asset('css/atasan/pegawai/show.css') }}">
@endpush
