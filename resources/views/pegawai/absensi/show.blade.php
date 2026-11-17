@extends('layouts.apps')

@section('title', 'Detail Absensi')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pegawai/absensi/responsive.css') }}">
@endpush

@section('content')
    @php
        $labelStatus = match ($absensi->status) {
            'hadir' => 'Hadir',
            'izin' => 'Izin',
            'sakit' => 'Sakit',
            'alpa', 'tidak_hadir' => 'Tidak Hadir',
            'cuti' => 'Cuti',
            'dinas_luar' => 'Dinas Luar',
            default => ucfirst($absensi->status),
        };
        $badgeStatus = match ($absensi->status) {
            'hadir' => 'bg-success',
            'izin' => 'bg-info',
            'sakit' => 'bg-warning text-dark',
            'alpa', 'tidak_hadir' => 'bg-danger',
            'cuti' => 'bg-secondary',
            'dinas_luar' => 'bg-primary',
            default => 'bg-light text-dark border',
        };
        $statusTone = match ($absensi->status) {
            'hadir' => 'success',
            'izin', 'dinas_luar' => 'info',
            'sakit' => 'warning',
            'alpa', 'tidak_hadir' => 'danger',
            default => 'neutral',
        };
        $tanggalAbsensi = \Carbon\Carbon::parse($absensi->tanggal);
    @endphp

    <div class="attendance-detail-page">
        <nav class="attendance-breadcrumb" aria-label="breadcrumb">
            <a href="{{ route($dashboardRoute) }}">Dashboard</a>
            <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
            <a href="{{ route($attendanceRoute . '.index') }}">Absensi Saya</a>
            <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
            <span aria-current="page">Detail</span>
        </nav>

        <header class="attendance-detail-hero">
            <div class="attendance-detail-hero-content">
                <span class="attendance-detail-eyebrow">
                    <i class="fa-solid fa-calendar-check"></i>
                    RIWAYAT PRESENSI
                </span>
                <h1>Detail Absensi</h1>
                <p>Informasi kehadiran dan verifikasi lokasi pada tanggal yang dipilih.</p>
            </div>
            <a href="{{ route($attendanceRoute . '.index') }}" class="attendance-detail-back">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Kembali ke Absensi</span>
            </a>
        </header>

        <div class="attendance-detail-grid">
            <section class="attendance-detail-card attendance-detail-overview" aria-labelledby="attendance-overview-title">
                <div class="attendance-detail-card-heading">
                    <span class="attendance-detail-card-icon"><i class="fa-regular fa-calendar"></i></span>
                    <div>
                        <h2 id="attendance-overview-title">Ringkasan Kehadiran</h2>
                        <p>Data presensi yang tersimpan</p>
                    </div>
                </div>

                <div class="attendance-detail-date-block">
                    <div class="attendance-detail-date-icon">
                        <strong>{{ $tanggalAbsensi->format('d') }}</strong>
                        <span>{{ $tanggalAbsensi->translatedFormat('M') }}</span>
                    </div>
                    <div class="attendance-detail-date-copy">
                        <small>Tanggal Absensi</small>
                        <strong>{{ $tanggalAbsensi->translatedFormat('l, d F Y') }}</strong>
                    </div>
                    <span class="attendance-detail-status is-{{ $statusTone }}">
                        <i class="fa-solid fa-circle" aria-hidden="true"></i>
                        {{ $labelStatus }}
                    </span>
                </div>

                <div class="attendance-detail-time-grid">
                    <div class="attendance-detail-time-item">
                        <span class="attendance-detail-time-icon is-in"><i class="fa-solid fa-arrow-right-to-bracket"></i></span>
                        <div>
                            <small>Jam Masuk</small>
                            <strong>{{ $absensi->jam_masuk ? \Carbon\Carbon::parse($absensi->jam_masuk)->format('H:i') : '-' }}</strong>
                            <span>WIB</span>
                        </div>
                    </div>
                    <div class="attendance-detail-time-item">
                        <span class="attendance-detail-time-icon is-out"><i class="fa-solid fa-arrow-right-from-bracket"></i></span>
                        <div>
                            <small>Jam Pulang</small>
                            <strong>{{ $absensi->jam_pulang ? \Carbon\Carbon::parse($absensi->jam_pulang)->format('H:i') : '-' }}</strong>
                            <span>WIB</span>
                        </div>
                    </div>
                </div>

                <dl class="attendance-detail-data">
                    <div>
                        <dt><i class="fa-solid fa-circle-check"></i> Status</dt>
                        <dd><span class="badge {{ $badgeStatus }}">{{ $labelStatus }}</span></dd>
                    </div>
                    <div>
                        <dt><i class="fa-solid fa-database"></i> Sumber</dt>
                        <dd>{{ ucfirst($absensi->sumber) }}</dd>
                    </div>
                    <div>
                        <dt><i class="fa-regular fa-message"></i> Keterangan</dt>
                        <dd class="attendance-detail-description">{{ $absensi->keterangan ?: '-' }}</dd>
                    </div>
                </dl>
            </section>

            <section class="attendance-detail-card attendance-detail-location" aria-labelledby="attendance-location-title">
                <div class="attendance-detail-card-heading">
                    <span class="attendance-detail-card-icon"><i class="fa-solid fa-location-dot"></i></span>
                    <div>
                        <h2 id="attendance-location-title">Informasi Lokasi</h2>
                        <p>Hasil pencatatan GPS saat presensi</p>
                    </div>
                </div>

                @if ($absensi->latitude !== null && $absensi->longitude !== null)
                    <div class="attendance-detail-location-status {{ $absensi->di_luar_kantor ? 'is-outside' : 'is-inside' }}">
                        <span class="attendance-detail-location-status-icon">
                            <i class="fa-solid {{ $absensi->di_luar_kantor ? 'fa-triangle-exclamation' : 'fa-shield-halved' }}"></i>
                        </span>
                        <div>
                            <strong>{{ $absensi->di_luar_kantor ? 'Terdeteksi di luar kantor' : 'Dalam area kantor' }}</strong>
                            <small>Status lokasi berdasarkan data absensi tersimpan.</small>
                        </div>
                    </div>
                    <dl class="attendance-detail-data">
                        <div>
                            <dt><i class="fa-solid fa-location-crosshairs"></i> Lokasi GPS</dt>
                            <dd class="attendance-detail-coordinates">
                                {{ number_format($absensi->latitude, 7, ',', '.') }},
                                {{ number_format($absensi->longitude, 7, ',', '.') }}
                            </dd>
                        </div>
                        <div>
                            <dt><i class="fa-solid fa-ruler"></i> Jarak dari kantor</dt>
                            <dd>{{ $absensi->jarak_lokasi !== null ? number_format($absensi->jarak_lokasi, 0, ',', '.') . ' m' : '-' }}</dd>
                        </div>
                    </dl>
                @else
                    <div class="attendance-detail-location-empty">
                        <i class="fa-solid fa-location-dot"></i>
                        <strong>Data GPS tidak tersedia</strong>
                        <p>Presensi ini tidak memiliki koordinat lokasi yang tercatat.</p>
                    </div>
                @endif
            </section>
        </div>
    </div>
@endsection
