@extends('layouts.apps')

@section('title', 'Dashboard Saya')

@php
    $user = auth()->user();
    $pegawaiName = $user?->name ?? 'Pegawai Demo';
    $periodeNama = $periodeAktif?->nama_periode ?? 'Belum ada periode penilaian';

    $statusAbsensi = $statusAbsensiHariIni ?? 'belum';

    $labelAbsen = match ($statusAbsensi) {
        'hadir' => 'Hadir',
        'izin' => 'Izin',
        'sakit' => 'Sakit',
        'alpa' => 'Alpa',
        'cuti' => 'Cuti',
        'dinas_luar' => 'Dinas Luar',
        'shift_1' => 'Shift 1 (Operator)',
        'shift_2' => 'Shift 2 (Operator)',
        'shift_3' => 'Shift 3 (Operator)',
        default => 'Belum Absen',
    };

    $waktuAbsensi = $waktuAbsensiHariIni ?? null;

    if ($waktuAbsensi instanceof \DateTimeInterface) {
        $waktuAbsensi = $waktuAbsensi->format('H:i') . ' WIB';
    }

    if (is_string($waktuAbsensi)) {
        $waktuAbsensi = trim($waktuAbsensi);
    }

    if (in_array($waktuAbsensi, ['', '-', '(-)'], true)) {
        $waktuAbsensi = null;
    }

    $awalBulan = now()->startOfMonth();
    $akhirBulan = now()->endOfMonth();

    $totalHariKerja = 0;

    foreach (\Carbon\CarbonPeriod::create($awalBulan, $akhirBulan) as $tanggal) {
        if ($tanggal->isWeekday()) {
            $totalHariKerja++;
        }
    }

    $jumlahKehadiran = max(0, (int) ($jumlahKehadiranBulanIni ?? 0));
    $jumlahLaporan = max(0, (int) ($jumlahLaporanBulanIni ?? 0));

    $persentaseAbsensi = $totalHariKerja > 0
        ? round(min(100, $jumlahKehadiran / $totalHariKerja * 100), 2)
        : 0;

    $persentaseLaporan = $totalHariKerja > 0
        ? round(min(100, $jumlahLaporan / $totalHariKerja * 100), 2)
        : 0;

    $nilaiAkhir = $hasilTerakhir
        ? max(0, min(100, (float) ($hasilTerakhir->nilai_akhir ?? 0)))
        : 0;

    $skorKpiTerakhir = $hasilTerakhir
        ? max(0, min(100, (float) ($hasilTerakhir->rata_skor_kpi ?? 0)))
        : 0;

    $skorKompetensiTerakhir = $hasilTerakhir
        ? max(0, min(100, (float) ($hasilTerakhir->rata_skor_kompetensi ?? 0)))
        : 0;
@endphp

@section('content')
<div class="pegawai-dashboard-shell">

    <section class="pegawai-hero-panel">
        <div class="hero-topline">
            <span class="hero-chip">
                Tahun Buku {{ now()->year }}
            </span>
        </div>

        <h1>Selamat Datang, {{ $pegawaiName }}</h1>

        <p>
            Ringkasan aktivitas laporan harian &amp; evaluasi kinerja Anda hari ini.
        </p>

        <div class="hero-bottom-row">
            <span class="hero-status-label">Absensi Hari Ini:</span>

            <span class="hero-status-pill">
                @if (in_array($statusAbsensi, ['hadir', 'shift_1', 'shift_2', 'shift_3'], true))
                    <i class="fa-solid fa-circle-check"></i>
                @else
                    <i class="fa-regular fa-clock"></i>
                @endif

                <span>{{ $labelAbsen }}</span>

                @if (in_array($statusAbsensi, ['hadir', 'shift_1', 'shift_2', 'shift_3'], true) && $waktuAbsensi)
                    <span class="hero-timer">({{ $waktuAbsensi }})</span>
                @endif
            </span>
        </div>
    </section>

    <div class="pegawai-dashboard-content">

        <section class="pegawai-summary-panel">
            <div class="summary-header">
                <div>
                    <h3>Ringkasan Aktivitas</h3>
                    <p>Monitoring aktivitas pegawai bulan berjalan</p>
                </div>
            </div>

            <div class="summary-visual-card">
                <div class="summary-card-topbar">
                    <span class="summary-month">
                        <i class="fa-regular fa-calendar"></i>
                        {{ now()->translatedFormat('F Y') }}
                    </span>
                </div>

                <div class="activity-chart-grid" id="activityChartCarousel">

                    <article class="activity-gauge-card gauge-attendance">
                        <div class="gauge-card-heading">
                            <span class="gauge-card-icon">
                                <i class="fa-solid fa-calendar-check"></i>
                            </span>

                            <div>
                                <h4>Absensi Bulanan</h4>
                                <p>Persentase kehadiran</p>
                            </div>
                        </div>

                        <div
                            class="gauge-chart"
                            role="img"
                            aria-label="Absensi {{ number_format($persentaseAbsensi, 2, ',', '.') }} persen"
                        >
                            <svg viewBox="0 0 240 190" aria-hidden="true">
                                <path
                                    class="gauge-track"
                                    d="M 25 155 A 95 95 0 1 1 215 155"
                                    pathLength="100"
                                />

                                <path
                                    class="gauge-progress"
                                    d="M 25 155 A 95 95 0 1 1 215 155"
                                    pathLength="100"
                                    stroke-dasharray="{{ $persentaseAbsensi }} 100"
                                />
                            </svg>

                            <div class="gauge-chart-value">
                                <strong>{{ number_format($persentaseAbsensi, 2, ',', '.') }}%</strong>
                                <span>{{ $jumlahKehadiran }} dari {{ $totalHariKerja }} hari</span>
                            </div>
                        </div>

                        <div class="gauge-card-footer">
                            <div>
                                <span>Hari Hadir</span>
                                <strong>{{ $jumlahKehadiran }} Hari</strong>
                            </div>

                            <div>
                                <span>Target Bulanan</span>
                                <strong>{{ $totalHariKerja }} Hari Kerja</strong>
                            </div>
                        </div>
                    </article>

                    <article class="activity-gauge-card gauge-report">
                        <div class="gauge-card-heading">
                            <span class="gauge-card-icon">
                                <i class="fa-solid fa-file-circle-check"></i>
                            </span>

                            <div>
                                <h4>Laporan Harian</h4>
                                <p>Persentase laporan bulanan</p>
                            </div>
                        </div>

                        <div
                            class="gauge-chart"
                            role="img"
                            aria-label="Laporan harian {{ number_format($persentaseLaporan, 2, ',', '.') }} persen"
                        >
                            <svg viewBox="0 0 240 190" aria-hidden="true">
                                <path
                                    class="gauge-track"
                                    d="M 25 155 A 95 95 0 1 1 215 155"
                                    pathLength="100"
                                />

                                <path
                                    class="gauge-progress"
                                    d="M 25 155 A 95 95 0 1 1 215 155"
                                    pathLength="100"
                                    stroke-dasharray="{{ $persentaseLaporan }} 100"
                                />
                            </svg>

                            <div class="gauge-chart-value">
                                <strong>{{ number_format($persentaseLaporan, 2, ',', '.') }}%</strong>
                                <span>{{ $jumlahLaporan }} dari {{ $totalHariKerja }} hari</span>
                            </div>
                        </div>

                        <div class="gauge-card-footer">
                            <div>
                                <span>Laporan Dibuat</span>
                                <strong>{{ $jumlahLaporan }} Laporan</strong>
                            </div>

                            <div>
                                <span>Target Bulanan</span>
                                <strong>{{ $totalHariKerja }} Hari Kerja</strong>
                            </div>
                        </div>
                    </article>

                </div>

                <div class="activity-carousel-pagination" aria-label="Navigasi grafik">
                    <button
                        type="button"
                        class="activity-carousel-dot is-active"
                        aria-label="Tampilkan grafik Absensi Bulanan"
                        aria-current="true"
                    ></button>

                    <button
                        type="button"
                        class="activity-carousel-dot"
                        aria-label="Tampilkan grafik Laporan Harian"
                    ></button>
                </div>

                <div class="summary-workday-note">
                    <i class="fa-solid fa-circle-info"></i>
                    <span>
                        Target dihitung berdasarkan hari Senin–Jumat pada bulan
                        {{ now()->translatedFormat('F Y') }}.
                    </span>
                </div>
            </div>
        </section>

        <div class="pegawai-dashboard-right">

            <section class="pegawai-period-panel">
                <div class="period-header-row">
                    <div class="panel-title-wrap">
                        <span class="panel-icon">
                            <i class="fa-regular fa-clipboard"></i>
                        </span>
                        <h3>Periode Penilaian Aktif</h3>
                    </div>

                    <span class="period-running-badge">
                        {{ $periodeStatusLabel ?? 'Belum Aktif' }}
                    </span>
                </div>

                <h2>{{ $periodeNama }}</h2>

                <div class="period-date-range">
                    <i class="fa-regular fa-calendar"></i>

                    @if (($periodeMulai ?? null) && ($periodeSelesai ?? null))
                        <span>
                            {{ $periodeMulai->translatedFormat('d F Y') }}
                            &ndash;
                            {{ $periodeSelesai->translatedFormat('d F Y') }}
                        </span>
                    @else
                        <span>Tanggal periode belum tersedia</span>
                    @endif
                </div>

                <div class="progress-track">
                    <span
                        class="progress-fill"
                        style="width: {{ max(0, min(100, (float) ($periodeProgress ?? 0))) }}%;"
                    ></span>
                </div>

                <div class="progress-meta">
                    @if (($periodeTotalHari ?? 0) > 0)
                        <span>
                            Hari ke-{{ $periodeHariBerjalan }}
                            dari {{ $periodeTotalHari }} hari
                        </span>
                        <span>{{ $periodeHariTersisa }} hari tersisa</span>
                    @else
                        <span>Data progres belum tersedia</span>
                    @endif
                </div>
            </section>

            <section class="pegawai-evaluation-panel">
                @if ($hasilTerakhir)
                    <div class="evaluation-result-card">
                        <div class="evaluation-result-heading">
                            <div class="evaluation-title">
                                <span class="evaluation-title-icon">
                                    <i class="fa-solid fa-circle-info"></i>
                                </span>

                                <div>
                                    <strong>Penilaian Terakhir</strong>
                                    <small>
                                        {{ $hasilTerakhir->periode?->nama_periode ?? 'Periode tidak tersedia' }}
                                    </small>
                                </div>
                            </div>

                            <span class="evaluation-status-badge">
                                {{ $hasilTerakhir->status === 'dikirim' ? 'Dikirim' : 'Draft' }}
                            </span>
                        </div>

                        <div class="evaluation-score-panel">
                            <div
                                class="evaluation-score-ring"
                                style="--score-progress: {{ $nilaiAkhir * 3.6 }}deg;"
                            >
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
                                    <div>
                                        <span>Skor KPI</span>
                                        <strong>{{ number_format($skorKpiTerakhir, 1) }}</strong>
                                    </div>
                                    <i><b style="width: {{ $skorKpiTerakhir }}%;"></b></i>
                                </div>

                                <div class="evaluation-metric">
                                    <div>
                                        <span>Kompetensi</span>
                                        <strong>{{ number_format($skorKompetensiTerakhir, 1) }}</strong>
                                    </div>
                                    <i><b style="width: {{ $skorKompetensiTerakhir }}%;"></b></i>
                                </div>
                            </div>
                        </div>

                        <div class="evaluation-tukin">
                            <span class="evaluation-tukin-icon">
                                <i class="fa-regular fa-money-bill-1"></i>
                            </span>

                            <div>
                                <small>ESTIMASI TUKIN</small>
                                <strong>Tukin Neto</strong>
                            </div>

                            <b>
                                Rp {{ number_format((float) ($hasilTerakhir->tukin_neto ?? 0), 0, ',', '.') }}
                            </b>
                        </div>
                    </div>
                @else
                    <div class="panel-title-wrap">
                        <span class="panel-icon">
                            <i class="fa-solid fa-circle-info"></i>
                        </span>
                        <h3>Penilaian Terakhir</h3>
                    </div>

                    <div class="evaluation-empty-state">
                        <p>Belum ada penilaian.</p>
                        <small>
                            Penilaian akan diverifikasi oleh Tim Penilai BKD setelah akhir periode.
                        </small>
                    </div>
                @endif
            </section>

        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/pegawai/dashboard.css') }}?v={{ filemtime(public_path('css/pegawai/dashboard.css')) }}">
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const carousel = document.getElementById('activityChartCarousel');
    const dots = Array.from(document.querySelectorAll('.activity-carousel-dot'));

    if (!carousel || !dots.length) {
        return;
    }

    const slides = Array.from(carousel.querySelectorAll('.activity-gauge-card'));

    function updateCarousel() {
        if (!slides.length) {
            return;
        }

        const carouselLeft = carousel.getBoundingClientRect().left;
        let activeIndex = 0;
        let nearestDistance = Infinity;

        slides.forEach((slide, index) => {
            const distance = Math.abs(
                slide.getBoundingClientRect().left - carouselLeft
            );

            if (distance < nearestDistance) {
                nearestDistance = distance;
                activeIndex = index;
            }
        });

        dots.forEach((dot, index) => {
            const active = index === activeIndex;
            dot.classList.toggle('is-active', active);

            if (active) {
                dot.setAttribute('aria-current', 'true');
            } else {
                dot.removeAttribute('aria-current');
            }
        });
    }

    dots.forEach((dot, index) => {
        dot.addEventListener('click', function () {
            if (!slides[index]) {
                return;
            }

            carousel.scrollTo({
                left: slides[index].offsetLeft - carousel.offsetLeft,
                behavior: 'smooth'
            });
        });
    });

    carousel.addEventListener('scroll', updateCarousel, { passive: true });
    window.addEventListener('resize', updateCarousel);

    updateCarousel();
});
</script>
@endpush
