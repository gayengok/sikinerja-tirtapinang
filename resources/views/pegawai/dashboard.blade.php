@extends('layouts.app')

@section('title', 'Dashboard Saya')

@php
    $user = auth()->user();
    $pegawaiName = $user?->name ?? 'Pegawai Demo';
    $pegawaiNip = $user?->pegawai?->nip ?? '19890926';
    $periodeNama = $periodeAktif?->nama_periode ?? 'Belum ada periode penilaian';
    $statusLaporanLabel = ($statusLaporanHariIni ?? 'belum') === 'sudah' ? 'Sudah Lapor' : 'Belum Lapor';
    $statusAbsensi = $statusAbsensiHariIni ?? 'hadir';
    $labelAbsen = match ($statusAbsensi) {
        'hadir' => 'Hadir',
        'izin' => 'Izin',
        'sakit' => 'Sakit',
        'alpa' => 'Alpa',
        'cuti' => 'Cuti',
        'dinas_luar' => 'Dinas Luar',
        default => 'Belum Absen',
    };
    $waktuAbsensi = $waktuAbsensiHariIni ?? '07:52 WIB';
@endphp

@section('content')
    <div class="pegawai-dashboard-shell">
        <section class="pegawai-hero-panel">
            <div class="hero-topline">
                <span class="hero-chip hero-chip-primary">PDAM Tirta Pinang</span>
                <span class="hero-chip hero-chip-soft">Tahun Buku 2026</span>
            </div>

            <h1>Selamat Datang, {{ $pegawaiName }}</h1>
            <p>Ringkasan aktivitas laporan harian &amp; evaluasi kinerja Anda hari ini.</p>

            <div class="hero-bottom-row">
                <span class="hero-status-label">Absensi Hari Ini:</span>
                <span class="hero-status-pill">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>{{ $labelAbsen }}</span>
                    <span class="hero-timer">({{ $waktuAbsensi }})</span>
                </span>
            </div>
        </section>

        <section class="pegawai-summary-panel">
            <div class="summary-header">
                <h3>Ringkasan Aktivitas</h3>
                <span>Bulan September</span>
            </div>

            <div class="summary-visual-card">
                <div class="summary-chart" aria-label="Ringkasan aktivitas bulan September">
                    <div class="summary-chart-center">

                    </div>

                    <div class="summary-orbit-label summary-orbit-report">
                        <strong>{{ $statusLaporanLabel === 'Sudah Lapor' ? '1' : 'Belum' }}</strong>
                        <span>Laporan</span>
                    </div>
                    <div class="summary-orbit-label summary-orbit-total">
                        <strong>{{ $jumlahLaporanBulanIni ?? 1 }}</strong>
                        <span>Bulan Ini</span>
                    </div>
                    <div class="summary-orbit-label summary-orbit-attendance">
                        <strong>{{ $labelAbsen }}</strong>
                        <span>Absen</span>
                    </div>
                    <div class="summary-orbit-label summary-orbit-presence">
                        <strong>{{ $jumlahKehadiranBulanIni ?? 3 }}</strong>
                        <span>Kehadiran</span>
                    </div>
                </div>
            </div>
        </section>

        <section class="pegawai-period-panel">
            <div class="period-header-row">
                <div class="panel-title-wrap">
                    <span class="panel-icon"><i class="fa-regular fa-clipboard"></i></span>
                    <h3>Periode Penilaian Aktif</h3>
                </div>
                <span class="period-running-badge">{{ $periodeStatusLabel }}</span>
            </div>

            <h2>{{ $periodeNama }}</h2>
            <div class="period-date-range">
                <i class="fa-regular fa-calendar"></i>
                @if ($periodeMulai && $periodeSelesai)
                    <span>{{ $periodeMulai->translatedFormat('d F Y') }} &ndash;
                        {{ $periodeSelesai->translatedFormat('d F Y') }}</span>
                @else
                    <span>Tanggal periode belum tersedia</span>
                @endif
            </div>

            <div class="progress-track">
                <span class="progress-fill" style="width: {{ $periodeProgress }}%;"></span>
            </div>

            <div class="progress-meta">
                @if ($periodeTotalHari > 0)
                    <span>Hari ke-{{ $periodeHariBerjalan }} dari {{ $periodeTotalHari }} hari</span>
                    <span>{{ $periodeHariTersisa }} hari tersisa</span>
                @else
                    <span>Data progres belum tersedia</span>
                @endif
            </div>

            {{-- <a href="#" class="detail-row">
                <span>Capaian SKP Pegawai</span>
                <span class="detail-row-cta">Lihat Detail SKP <i class="fa-solid fa-chevron-right"></i></span>
            </a> --}}
        </section>

        <section class="pegawai-evaluation-panel">
            @if ($hasilTerakhir)
                <div class="evaluation-result-card">
                    @php
                        $nilaiAkhir = max(0, min(100, (float) ($hasilTerakhir->nilai_akhir ?? 0)));
                        $skorKpiTerakhir = max(0, min(100, (float) ($hasilTerakhir->rata_skor_kpi ?? 0)));
                        $skorKompetensiTerakhir = max(0, min(100, (float) ($hasilTerakhir->rata_skor_kompetensi ?? 0)));
                    @endphp

                    <div class="evaluation-result-heading">
                        <div class="evaluation-title">
                            <span class="evaluation-title-icon"><i class="fa-solid fa-circle-info"></i></span>
                            <div>
                                <strong>Penilaian Terakhir</strong>
                                <small>{{ $hasilTerakhir->periode?->nama_periode ?? 'Periode tidak tersedia' }}</small>
                            </div>
                        </div>
                        <span class="evaluation-status-badge">
                            {{ $hasilTerakhir->status === 'dikirim' ? 'Dikirim' : 'Draft' }}
                        </span>
                    </div>

                    <div class="evaluation-score-panel">
                        <div class="evaluation-score-ring" style="--score-progress: {{ $nilaiAkhir * 3.6 }}deg;">
                            <div class="evaluation-score-ring-inner">
                                <small>TOTAL SKOR</small>
                                <div>
                                    <strong>{{ number_format($nilaiAkhir, 1) }}</strong>
                                    <span>/100</span>
                                </div>
                                <b>{{ $hasilTerakhir->predikat ?? '-' }}</b>
                            </div>
                        </div>

                        <div class="evaluation-meta-grid">
                            <div class="evaluation-metric">
                                <div><span>Skor KPI</span><strong>{{ number_format($skorKpiTerakhir, 1) }}</strong></div>
                                <i><b style="width: {{ $skorKpiTerakhir }}%;"></b></i>
                            </div>
                            <div class="evaluation-metric">
                                <div>
                                    <span>Kompetensi</span><strong>{{ number_format($skorKompetensiTerakhir, 1) }}</strong>
                                </div>
                                <i><b style="width: {{ $skorKompetensiTerakhir }}%;"></b></i>
                            </div>
                        </div>
                    </div>

                    <div class="evaluation-tukin">
                        <span class="evaluation-tukin-icon"><i class="fa-regular fa-money-bill-1"></i></span>
                        <div>
                            <small>ESTIMASI TUKIN</small>
                            <strong>Tukin Neto</strong>
                        </div>
                        <b>Rp {{ number_format((float) ($hasilTerakhir->tukin_neto ?? 0), 0, ',', '.') }}</b>
                    </div>
                </div>
            @else
                <div class="panel-title-wrap">
                    <span class="panel-icon"><i class="fa-solid fa-circle-info"></i></span>
                    <h3>Penilaian Terakhir</h3>
                </div>
                <div class="evaluation-empty-state">
                    <p>Belum ada penilaian.</p>
                    <small>Penilaian akan diverifikasi oleh Tim Penilai BKD setelah akhir periode.</small>
                </div>
            @endif
        </section>
    </div>

@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pegawai/dashboard.css') }}">
@endpush
