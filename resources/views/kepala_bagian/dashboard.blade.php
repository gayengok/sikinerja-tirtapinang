@extends('layouts.app')

@section('title', 'Dashboard Kepala Bagian')

@section('content')
    @php
        $totalLaporan = $laporanMenunggu + $laporanDisetujui + $laporanDitolak;
        $persentaseDisetujui = $totalLaporan > 0 ? round(($laporanDisetujui / $totalLaporan) * 100) : 0;
    @endphp

    <main class="kepala-bagian-dashboard">
        <section class="kepala-bagian-welcome">
            <div>
                <span class="kepala-bagian-eyebrow"><i class="fa-solid fa-circle"></i> Kepala Bagian / Penilai</span>
                <h1>Selamat Datang, {{ auth()->user()->name }}</h1>
                <p>Ringkasan kinerja atasan dan progres laporan tim untuk periode berjalan.</p>
            </div>
            <div class="kepala-bagian-period">
                <i class="fa-regular fa-calendar-check"></i>
                <span><small>PERIODE BERJALAN</small><strong>{{ $periodeAktif?->nama_periode ?? 'Belum tersedia' }}</strong></span>
            </div>
        </section>

        <section class="kepala-bagian-stats" aria-label="Ringkasan statistik">
            <article class="is-team"><span><i class="fa-solid fa-user-tie"></i></span><strong>{{ $jumlahAtasan }}</strong><b>Atasan yang Dinilai</b><small>Atasan aktif di bawah tanggung jawab Anda</small></article>
            <article class="is-approved"><span><i class="fa-solid fa-file-circle-check"></i></span><strong>{{ $laporanDisetujui }}</strong><b>Laporan Disetujui</b><small>Dari tim atasan yang ditugaskan</small></article>
            <article class="is-pending"><span><i class="fa-regular fa-clock"></i></span><strong>{{ $laporanMenunggu }}</strong><b>Laporan Menunggu</b><small>{{ $laporanDitolak }} laporan perlu revisi</small></article>
        </section>

        <section class="kepala-bagian-progress">
            <div><span>Progres laporan tim</span><strong>{{ $persentaseDisetujui }}% disetujui</strong></div>
            <div class="kepala-bagian-progress-track"><span style="width: {{ $persentaseDisetujui }}%"></span></div>
            <small>{{ $laporanDisetujui }} dari {{ $totalLaporan }} laporan telah disetujui.</small>
        </section>

        <section class="kepala-bagian-tools" aria-label="Fitur pengelolaan tim">
            <a href="{{ route('kepala_bagian.pegawai.index') }}">
                <i class="fa-solid fa-users"></i>
                <span><strong>Pegawai Atasan</strong><small>Lihat dan nilai Atasan di bawah tanggung jawab Anda.</small></span>
                <i class="fa-solid fa-arrow-right"></i>
            </a>
            <a href="{{ route('kepala_bagian.absensi.index') }}">
                <i class="fa-solid fa-calendar-check"></i>
                <span><strong>Data Absensi</strong><small>Pantau presensi pegawai dalam tim Atasan.</small></span>
                <i class="fa-solid fa-arrow-right"></i>
            </a>
            <a href="{{ route('kepala_bagian.laporan.index', ['status' => 'diajukan']) }}">
                <i class="fa-solid fa-file-circle-check"></i>
                <span><strong>Approval Laporan</strong><small>Tinjau, setujui, atau tolak laporan yang diajukan.</small></span>
                <i class="fa-solid fa-arrow-right"></i>
            </a>
        </section>

        <section class="kepala-bagian-action">
            <div class="kepala-bagian-action-icon"><i class="fa-solid fa-chart-line"></i></div>
            <div>
                <h2>Penilaian Kinerja Atasan</h2>
                <p>Tinjau dan berikan nilai KPI kepada atasan yang menjadi tanggung jawab Anda.</p>
            </div>
            <a href="{{ route('kepala_bagian.penilaian-atasan.index') }}">Buka Penilaian <i class="fa-solid fa-arrow-right"></i></a>
        </section>
    </main>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/kepala-bagian/dashboard.css') }}">
@endpush