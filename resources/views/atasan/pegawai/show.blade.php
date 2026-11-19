
@extends('layouts.app')

@section('title', 'Detail Pegawai Bawahan')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/atasan/pegawai/show.css') }}?v=20261119-2">
@endpush

@section('content')
@php
    $inisial = mb_strtoupper(mb_substr($pegawai->nama ?: 'P', 0, 2));

    $foto = $pegawai->foto_profil
        ? \Illuminate\Support\Facades\Storage::disk('public')->url($pegawai->foto_profil)
        : null;

    $tanggalMasuk = $pegawai->tanggal_masuk
        ? \Carbon\Carbon::parse($pegawai->tanggal_masuk)->translatedFormat('d F Y')
        : '-';

    $informasiPegawai = [
        ['Nama Lengkap', $pegawai->nama],
        ['NIP', $pegawai->nip ?: '-'],
        ['Email', $pegawai->user->email ?? '-'],
        ['Divisi', $pegawai->jabatan->divisi->nama_divisi ?? '-'],
        ['Jabatan', $pegawai->jabatan->nama_jabatan ?? '-'],
        ['Grade', $pegawai->jabatan->grade ?? '-'],
        ['Tanggal Masuk', $tanggalMasuk],
        ['Atasan Langsung', $pegawai->atasan->nama ?? '-'],
        ['Status Kepegawaian', ucfirst($pegawai->status ?? '-')],
    ];
@endphp

<div class="atasan-employee-detail-page">
    <nav class="atasan-detail-breadcrumb" aria-label="Breadcrumb">
        <a href="{{ route('atasan.dashboard') }}">Dashboard</a>
        <i class="fa-solid fa-chevron-right"></i>
        <a href="{{ route('atasan.pegawai.index') }}">Pegawai Bawahan</a>
        <i class="fa-solid fa-chevron-right"></i>
        <span>Detail Pegawai</span>
    </nav>

    <section class="atasan-detail-hero">
        <div class="atasan-detail-hero-content">
            <span class="atasan-detail-avatar">
                @if($foto)
                    <img
                        src="{{ $foto }}"
                        alt=""
                        onerror="this.remove()"
                    >
                @endif
                <span>{{ $inisial }}</span>
            </span>

            <div class="atasan-detail-hero-identity">
                <span class="atasan-detail-eyebrow">
                    <i class="fa-solid fa-user"></i>
                    PROFIL PEGAWAI BAWAHAN
                </span>

                <h1>{{ $pegawai->nama }}</h1>

                <p>{{ $pegawai->jabatan->nama_jabatan ?? 'Jabatan belum tersedia' }}</p>

                <div class="atasan-detail-hero-meta">
                    <span>
                        <i class="fa-regular fa-building"></i>
                        {{ $pegawai->jabatan->divisi->nama_divisi ?? '-' }}
                    </span>

                    <span class="atasan-detail-status {{ $pegawai->status === 'aktif' ? 'is-active' : '' }}">
                        <i class="fa-solid fa-circle"></i>
                        {{ ucfirst($pegawai->status ?? '-') }}
                    </span>
                </div>
            </div>
        </div>

        <a href="{{ route('atasan.pegawai.index') }}" class="atasan-detail-back">
            <i class="fa-solid fa-arrow-left"></i>
            Kembali ke Daftar
        </a>
    </section>

    <section class="atasan-detail-stats">
        <article class="atasan-detail-stat">
            <span class="atasan-detail-stat-icon">
                <i class="fa-regular fa-file-lines"></i>
            </span>
            <strong>{{ $jumlahLaporan }}</strong>
            <small>Total Laporan</small>
        </article>

        <article class="atasan-detail-stat">
            <span class="atasan-detail-stat-icon">
                <i class="fa-regular fa-clock"></i>
            </span>
            <strong>{{ $laporanMenunggu }}</strong>
            <small>Menunggu Approval</small>
        </article>

        <article class="atasan-detail-stat">
            <span class="atasan-detail-stat-icon">
                <i class="fa-regular fa-calendar-check"></i>
            </span>
            <strong>{{ $jumlahKehadiran }}</strong>
            <small>Catatan Hadir</small>
        </article>

        <article class="atasan-detail-stat">
            <span class="atasan-detail-stat-icon">
                <i class="fa-solid fa-chart-line"></i>
            </span>
            <strong>
                {{ $hasilTerakhir && $hasilTerakhir->nilai_akhir !== null
                    ? number_format($hasilTerakhir->nilai_akhir, 1, ',', '.')
                    : '-' }}
            </strong>
            <small>Nilai Terakhir</small>
        </article>
    </section>

    <div class="atasan-detail-content-grid">
        <section class="atasan-detail-card">
            <div class="atasan-detail-card-heading">
                <span class="atasan-detail-card-icon">
                    <i class="fa-regular fa-id-card"></i>
                </span>

                <div>
                    <span class="atasan-detail-kicker">DATA KEPEGAWAIAN</span>
                    <h2>Informasi Pegawai</h2>
                </div>
            </div>

            <div class="atasan-detail-info-list">
                @foreach($informasiPegawai as $item)
                    <div class="atasan-detail-row">
                        <span>{{ $item[0] }}</span>
                        <strong>{{ $item[1] }}</strong>
                    </div>
                @endforeach
            </div>
        </section>

        <div class="atasan-detail-side">
            <section class="atasan-detail-card">
                <div class="atasan-detail-card-heading">
                    <span class="atasan-detail-card-icon">
                        <i class="fa-solid fa-chart-line"></i>
                    </span>

                    <div>
                        <span class="atasan-detail-kicker">EVALUASI KINERJA</span>
                        <h2>Penilaian Terakhir</h2>
                    </div>
                </div>

                @if($hasilTerakhir)
                    <div class="atasan-detail-info-list">
                        <div class="atasan-detail-row">
                            <span>Nilai Akhir</span>
                            <strong>
                                {{ $hasilTerakhir->nilai_akhir !== null
                                    ? number_format($hasilTerakhir->nilai_akhir, 1, ',', '.')
                                    : 'Belum tersedia' }}
                            </strong>
                        </div>

                        <div class="atasan-detail-row">
                            <span>Predikat</span>
                            <strong>{{ $hasilTerakhir->predikat ?: '-' }}</strong>
                        </div>

                        <div class="atasan-detail-row">
                            <span>Tukin Neto</span>
                            <strong>
                                {{ $hasilTerakhir->tukin_neto !== null
                                    ? 'Rp ' . number_format($hasilTerakhir->tukin_neto, 0, ',', '.')
                                    : 'Belum tersedia' }}
                            </strong>
                        </div>
                    </div>
                @else
                    <div class="atasan-detail-empty">
                        <i class="fa-regular fa-chart-bar"></i>
                        <strong>Belum ada penilaian</strong>
                        <p>Hasil penilaian pegawai belum tersedia di database.</p>
                    </div>
                @endif
            </section>

            <section class="atasan-detail-card">
                <div class="atasan-detail-card-heading">
                    <span class="atasan-detail-card-icon">
                        <i class="fa-solid fa-list-check"></i>
                    </span>

                    <div>
                        <span class="atasan-detail-kicker">AKTIVITAS LAPORAN</span>
                        <h2>Ringkasan Laporan</h2>
                    </div>
                </div>

                <div class="atasan-detail-info-list">
                    <div class="atasan-detail-row">
                        <span>Total Laporan</span>
                        <strong>{{ $jumlahLaporan }}</strong>
                    </div>

                    <div class="atasan-detail-row">
                        <span>Disetujui</span>
                        <strong>{{ $laporanDisetujui }}</strong>
                    </div>

                    <div class="atasan-detail-row">
                        <span>Menunggu Approval</span>
                        <strong>{{ $laporanMenunggu }}</strong>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <section class="atasan-detail-card atasan-detail-navigation">
        <div class="atasan-detail-card-heading">
            <span class="atasan-detail-card-icon">
                <i class="fa-solid fa-arrow-up-right-from-square"></i>
            </span>

            <div>
                <span class="atasan-detail-kicker">AKSI PEGAWAI</span>
                <h2>Akses Data Terkait</h2>
            </div>
        </div>

        <div class="atasan-detail-actions">
            <a href="{{ route('atasan.absensi.index', ['search' => $pegawai->nama]) }}">
                <i class="fa-regular fa-calendar-check"></i>
                <span>Data Absensi</span>
                <i class="fa-solid fa-chevron-right"></i>
            </a>

            <a href="{{ route('atasan.laporan.index') }}">
                <i class="fa-regular fa-file-lines"></i>
                <span>Daftar Laporan</span>
                <i class="fa-solid fa-chevron-right"></i>
            </a>

            <a href="{{ route('atasan.hasil-penilaian.show', $pegawai) }}">
                <i class="fa-solid fa-chart-line"></i>
                <span>Hasil Penilaian</span>
                <i class="fa-solid fa-chevron-right"></i>
            </a>

            <a
                href="{{ route('atasan.kompetensi.edit', $pegawai) }}"
                class="is-primary"
            >
                <i class="fa-solid fa-pen-to-square"></i>
                <span>Beri Nilai Kompetensi</span>
                <i class="fa-solid fa-chevron-right"></i>
            </a>
        </div>
    </section>
</div>
@endsection
