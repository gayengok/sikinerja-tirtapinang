
@extends('layouts.app')

@section('title', 'Dashboard Kepala Bagian')

@section('content')
    @php
        $totalLaporan = $laporanMenunggu + $laporanDisetujui + $laporanDitolak;
        $persentaseDisetujui = $totalLaporan > 0
            ? round(($laporanDisetujui / $totalLaporan) * 100)
            : 0;
    @endphp

    <div class="kepala-bagian-dashboard">
        <section class="kb-hero">
            <div class="kb-hero-top">
                <div class="kb-hero-intro">
                    <div class="kb-hero-eyebrow">
                        <span class="kb-status-dot"></span>
                        <span>Kepala Bagian / Penilai</span>
                        <span class="kb-eyebrow-divider"></span>
                        <i class="fa-solid fa-chart-line"></i>
                        <span>Dashboard Tim</span>
                    </div>

                    <h1>Selamat Datang, {{ auth()->user()->name }}</h1>

                    <p>
                        Pantau kinerja atasan, progres laporan tim,
                        dan proses penilaian KPI dalam satu dashboard.
                    </p>
                </div>

                <div class="kb-hero-period">
                    <div class="kb-period-icon">
                        <i class="fa-regular fa-calendar-check"></i>
                    </div>

                    <div class="kb-period-info">
                        <small>PERIODE PENILAIAN</small>
                        <strong>
                            {{ $periodeAktif?->nama_periode ?? 'Belum tersedia' }}
                        </strong>
                    </div>

                    <span class="kb-period-status">
                        <span></span>
                        {{ $periodeAktif ? 'Aktif' : 'Belum Ada' }}
                    </span>
                </div>
            </div>

            <div class="kb-hero-activities">
                <div class="kb-activities-heading">
                    <h2>
                        <i class="fa-solid fa-bolt"></i>
                        Akses Cepat
                    </h2>

                    <span>Kelola tim dan penilaian atasan</span>
                </div>

                <div class="kb-quick-grid">
                    <a href="{{ route('kepala_bagian.pegawai.index') }}" class="kb-quick-card">
                        <span class="kb-quick-icon">
                            <i class="fa-solid fa-users"></i>
                        </span>

                        <span class="kb-quick-content">
                            <strong>Pegawai Atasan</strong>
                            <small>Lihat dan kelola atasan dalam tanggung jawab Anda.</small>
                        </span>

                        <span class="kb-quick-arrow">
                            <i class="fa-solid fa-arrow-right"></i>
                        </span>
                    </a>

                    <a href="{{ route('kepala_bagian.absensi.index') }}" class="kb-quick-card">
                        <span class="kb-quick-icon">
                            <i class="fa-solid fa-calendar-check"></i>
                        </span>

                        <span class="kb-quick-content">
                            <strong>Data Absensi</strong>
                            <small>Pantau kehadiran pegawai dalam tim atasan.</small>
                        </span>

                        <span class="kb-quick-arrow">
                            <i class="fa-solid fa-arrow-right"></i>
                        </span>
                    </a>

                    <a href="{{ route('kepala_bagian.laporan.index', ['status' => 'diajukan']) }}" class="kb-quick-card">
                        <span class="kb-quick-icon">
                            <i class="fa-solid fa-file-circle-check"></i>
                        </span>

                        <span class="kb-quick-content">
                            <strong>Approval Laporan</strong>
                            <small>Tinjau dan verifikasi laporan yang diajukan.</small>
                        </span>

                        <span class="kb-quick-arrow">
                            <i class="fa-solid fa-arrow-right"></i>
                        </span>
                    </a>

                    <a href="{{ route('kepala_bagian.penilaian-atasan.index') }}" class="kb-quick-card">
                        <span class="kb-quick-icon">
                            <i class="fa-solid fa-chart-line"></i>
                        </span>

                        <span class="kb-quick-content">
                            <strong>Penilaian KPI</strong>
                            <small>Berikan penilaian kinerja kepada atasan.</small>
                        </span>

                        <span class="kb-quick-arrow">
                            <i class="fa-solid fa-arrow-right"></i>
                        </span>
                    </a>
                </div>
            </div>
        </section>

        <section class="kb-period-panel">
            <div class="kb-panel-heading">
                <div class="kb-panel-title-group">
                    <span class="kb-panel-icon">
                        <i class="fa-regular fa-calendar-check"></i>
                    </span>

                    <div>
                        <span class="kb-section-eyebrow">PERIODE AKTIF</span>
                        <h2>Periode Penilaian</h2>
                        <p>
                            {{ $periodeAktif?->nama_periode ?? 'Belum ada periode penilaian aktif.' }}
                        </p>
                    </div>
                </div>

                <span class="kb-panel-chip">
                    <i class="fa-regular fa-calendar"></i>
                    {{ $periodeAktif?->nama_periode ?? 'Belum tersedia' }}
                </span>
            </div>

            <div class="kb-period-summary">
                <div class="kb-period-summary-head">
                    <div>
                        <span class="kb-summary-label">PROGRES PERSETUJUAN LAPORAN</span>
                        <p>Persentase laporan yang telah disetujui pada periode berjalan.</p>
                    </div>

                    <strong>{{ $persentaseDisetujui }}%</strong>
                </div>

                <div
                    class="kb-progress-track"
                    role="progressbar"
                    aria-label="Progres persetujuan laporan"
                    aria-valuenow="{{ $persentaseDisetujui }}"
                    aria-valuemin="0"
                    aria-valuemax="100"
                >
                    <span style="width: {{ $persentaseDisetujui }}%"></span>
                </div>

                <div class="kb-progress-footer">
                    <span>{{ $laporanDisetujui }} dari {{ $totalLaporan }} laporan disetujui</span>
                    <span>{{ $totalLaporan - $laporanDisetujui }} laporan lainnya</span>
                </div>
            </div>
        </section>

        <section class="kb-overview">
            <div class="kb-overview-heading">
                <div>
                    <span class="kb-section-eyebrow">OVERVIEW</span>
                    <h2>Ringkasan Kinerja Tim</h2>
                    <p>Statistik atasan dan progres laporan dalam tanggung jawab Anda.</p>
                </div>

                <span class="kb-overview-note">
                    <i class="fa-solid fa-database"></i>
                    Data saat halaman dimuat
                </span>
            </div>

            <div class="kb-stats-grid">
                <article class="kb-stat-card">
                    <div class="kb-stat-top">
                        <span class="kb-stat-icon">
                            <i class="fa-solid fa-users"></i>
                        </span>
                        <span class="kb-stat-tag">Tim</span>
                    </div>

                    <strong class="kb-stat-number">{{ $jumlahAtasan }}</strong>
                    <h3>Atasan yang Dinilai</h3>
                    <p>Jumlah atasan dalam tanggung jawab Anda.</p>
                </article>

                <article class="kb-stat-card">
                    <div class="kb-stat-top">
                        <span class="kb-stat-icon">
                            <i class="fa-solid fa-circle-check"></i>
                        </span>
                        <span class="kb-stat-tag">{{ $persentaseDisetujui }}%</span>
                    </div>

                    <strong class="kb-stat-number">{{ $laporanDisetujui }}</strong>
                    <h3>Laporan Disetujui</h3>
                    <p>Laporan yang telah mendapatkan persetujuan.</p>
                </article>

                <article class="kb-stat-card">
                    <div class="kb-stat-top">
                        <span class="kb-stat-icon">
                            <i class="fa-regular fa-clock"></i>
                        </span>
                        <span class="kb-stat-tag">Review</span>
                    </div>

                    <strong class="kb-stat-number">{{ $laporanMenunggu }}</strong>
                    <h3>Menunggu Persetujuan</h3>
                    <p>Laporan yang masih memerlukan peninjauan.</p>
                </article>

                <article class="kb-stat-card">
                    <div class="kb-stat-top">
                        <span class="kb-stat-icon">
                            <i class="fa-solid fa-rotate-left"></i>
                        </span>
                        <span class="kb-stat-tag">Revisi</span>
                    </div>

                    <strong class="kb-stat-number">{{ $laporanDitolak }}</strong>
                    <h3>Laporan Ditolak</h3>
                    <p>Laporan yang memerlukan perbaikan.</p>
                </article>
            </div>
        </section>

        <section class="kb-analysis-grid">
            <article class="kb-analysis-card">
                <div class="kb-analysis-heading">
                    <div>
                        <span class="kb-section-eyebrow">ANALISIS LAPORAN</span>
                        <h2>Status Laporan Atasan</h2>
                        <p>Distribusi keputusan laporan dalam tim.</p>
                    </div>

                    <span class="kb-analysis-icon">
                        <i class="fa-solid fa-chart-pie"></i>
                    </span>
                </div>

                <div class="kb-analysis-content">
                    <div
                        class="kb-donut"
                        style="--approved: {{ $totalLaporan > 0 ? ($laporanDisetujui / $totalLaporan) * 100 : 0 }}%; --pending: {{ $totalLaporan > 0 ? ($laporanMenunggu / $totalLaporan) * 100 : 0 }}%;"
                    >
                        <div class="kb-donut-center">
                            <strong>{{ $totalLaporan }}</strong>
                            <span>Total Laporan</span>
                        </div>
                    </div>

                    <div class="kb-analysis-legend">
                        <div class="kb-legend-row kb-legend-total">
                            <span>Total Laporan</span>
                            <strong>{{ $totalLaporan }}</strong>
                        </div>

                        <div class="kb-legend-row">
                            <span><i class="kb-legend-dot is-approved"></i> Disetujui</span>
                            <strong>
                                {{ $laporanDisetujui }}
                                <small>{{ $totalLaporan > 0 ? round($laporanDisetujui / $totalLaporan * 100) : 0 }}%</small>
                            </strong>
                        </div>

                        <div class="kb-legend-row">
                            <span><i class="kb-legend-dot is-pending"></i> Menunggu</span>
                            <strong>
                                {{ $laporanMenunggu }}
                                <small>{{ $totalLaporan > 0 ? round($laporanMenunggu / $totalLaporan * 100) : 0 }}%</small>
                            </strong>
                        </div>

                        <div class="kb-legend-row">
                            <span><i class="kb-legend-dot is-rejected"></i> Ditolak / Revisi</span>
                            <strong>
                                {{ $laporanDitolak }}
                                <small>{{ $totalLaporan > 0 ? round($laporanDitolak / $totalLaporan * 100) : 0 }}%</small>
                            </strong>
                        </div>
                    </div>
                </div>

                <div class="kb-analysis-footer">
                    <span>
                        <i class="fa-solid fa-circle-info"></i>
                        Ringkasan status laporan tim
                    </span>

                    <a href="{{ route('kepala_bagian.laporan.index', ['status' => 'diajukan']) }}">
                        Lihat Laporan
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </article>

            <article class="kb-analysis-card">
                <div class="kb-analysis-heading">
                    <div>
                        <span class="kb-section-eyebrow">MONITORING PENILAIAN</span>
                        <h2>Penilaian Kinerja Atasan</h2>
                        <p>Ringkasan atasan yang menjadi tanggung jawab Anda.</p>
                    </div>

                    <span class="kb-analysis-icon">
                        <i class="fa-solid fa-chart-line"></i>
                    </span>
                </div>

                <div class="kb-assessment-content">
                    <div class="kb-assessment-highlight">
                        <span class="kb-assessment-highlight-icon">
                            <i class="fa-solid fa-user-tie"></i>
                        </span>

                        <div>
                            <span>Total Atasan yang Dinilai</span>
                            <strong>{{ $jumlahAtasan }}</strong>
                            <small>Atasan di bawah tanggung jawab Kepala Bagian</small>
                        </div>
                    </div>

                    <div class="kb-assessment-description">
                        <h3>Kelola Penilaian KPI</h3>
                        <p>
                            Tinjau data kinerja dan lakukan penilaian terhadap
                            atasan sesuai periode penilaian yang berlaku.
                        </p>
                    </div>

                    <a href="{{ route('kepala_bagian.penilaian-atasan.index') }}" class="kb-assessment-button">
                        Buka Penilaian Atasan
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </article>
        </section>
    </div>
@endsection

@push('styles')
    <link
        rel="stylesheet"
        href="{{ asset('css/kepala-bagian/dashboard.css') }}?v={{ file_exists(public_path('css/kepala-bagian/dashboard.css')) ? filemtime(public_path('css/kepala-bagian/dashboard.css')) : '1' }}"
    >
@endpush
