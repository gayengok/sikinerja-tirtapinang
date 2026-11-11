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
    <style>
        /* ------------------------------------------------------------------
                                                                                   Design tokens
                                                                                ------------------------------------------------------------------ */
        :root {
            --pw-bg: #edf3f5;
            --pw-surface: #ffffff;
            --pw-surface-muted: #f5f7fa;
            --pw-border: #dce5ef;
            --pw-border-soft: #ebeff4;
            --pw-ink: #1f2a37;
            --pw-ink-soft: #5a697d;
            --pw-ink-faint: #64748b;

            --pw-primary: #0b8db3;
            --pw-primary-dark: #1687c8;
            --pw-link: #117dc6;

            --pw-icon-blue-bg: #e4f1ff;
            --pw-icon-blue-fg: #1d78ff;
            --pw-icon-cyan-bg: #dff8ff;
            --pw-icon-cyan-fg: #0ea5e9;
            --pw-icon-emerald-bg: #defae8;
            --pw-icon-emerald-fg: #1aa26a;
            --pw-icon-orange-bg: #fff0d9;
            --pw-icon-orange-fg: #ef9f18;

            --pw-radius-lg: 1.1rem;
            --pw-radius-md: 0.9rem;
            --pw-radius-sm: 0.65rem;
            --pw-shadow-card: 0 3px 10px rgba(37, 62, 90, 0.08);
        }

        body {
            background: var(--pw-bg);

        }

        /* ------------------------------------------------------------------
                                                                                   Shell
                                                                                   The pegawai dashboard always renders the compact, phone-width
                                                                                   composition — regardless of viewport — since it lives inside the
                                                                                   mobile portal shell.
                                                                                ------------------------------------------------------------------ */
        .pegawai-dashboard-shell {
            width: 100%;
            max-width: 560px;
            margin: 0 auto;
            padding: 1rem 1rem 1.5rem;
            color: var(--pw-ink);
        }

        .pegawai-breadcrumb {
            padding: 0.75rem 1rem 0.65rem;
            background: #f4f7fb;
            color: var(--pw-ink-soft);
            font-size: 0.7rem;
        }

        .pegawai-breadcrumb strong {
            font-weight: 700;
            color: var(--pw-ink);
        }

        /* ------------------------------------------------------------------
                                                                                   Hero
                                                                                ------------------------------------------------------------------ */
        .pegawai-hero-panel {
            background: #0875ad;
            border-radius: var(--pw-radius-lg);
            margin: 0 0 1.1rem;
            padding: 1rem 1rem 0.85rem;
            color: #fff;
            box-shadow: 0 12px 26px rgba(10, 126, 160, 0.2);
        }

        .hero-topline {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.8rem;
        }

        .hero-chip {
            display: inline-flex;
            align-items: center;
            padding: 0.35rem 0.55rem;
            border-radius: 999px;
            font-size: 0.62rem;
            font-weight: 700;
            background: rgba(255, 255, 255, 0.16);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }

        .hero-chip-soft {
            background: rgba(255, 255, 255, 0.14);
            color: rgba(255, 255, 255, 0.9);
        }

        .pegawai-hero-panel h1 {
            margin: 0;
            font-size: 1.18rem;
            font-weight: 800;
            line-height: 1.25;
            letter-spacing: -0.03em;
        }

        .pegawai-hero-panel p {
            margin: 0.55rem 0 0.85rem;
            font-size: 0.72rem;
            line-height: 1.5;
            color: rgba(255, 255, 255, 0.9);
        }

        .hero-bottom-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .hero-status-label {
            font-size: 0.68rem;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.92);
        }

        .hero-status-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background: rgba(255, 255, 255, 0.18);
            border: 1px solid rgba(255, 255, 255, 0.22);
            padding: 0.35rem 0.6rem;
            border-radius: 999px;
            font-size: 0.68rem;
            font-weight: 700;
        }

        .hero-status-pill i {
            color: #d9ffec;
            font-size: 0.75rem;
        }

        .hero-timer {
            opacity: 0.92;
            font-weight: 600;
        }

        /* ------------------------------------------------------------------
                                                                                   Summary cards
                                                                                ------------------------------------------------------------------ */
        .pegawai-summary-panel {
            padding: 0;
        }

        .summary-header {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            margin-bottom: 0.65rem;
        }

        .summary-header h3 {
            margin: 0;
            font-size: 0.78rem;
            font-weight: 800;
            color: #243447;
        }

        .summary-header span {
            font-size: 0.68rem;
            color: var(--pw-ink-faint);
            font-weight: 600;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.65rem;
        }

        .summary-card {
            display: flex;
            flex-direction: column;
            min-height: 126px;
            padding: 0.8rem 0.75rem 0.7rem;
            background: var(--pw-surface);
            border: 1px solid var(--pw-border);
            border-radius: var(--pw-radius-md);
            box-shadow: var(--pw-shadow-card);
            color: inherit;
            text-decoration: none;
        }

        .summary-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 2.15rem;
            height: 2.15rem;
            margin-bottom: 0.55rem;
            border-radius: 0.7rem;
            font-size: 0.9rem;
        }

        .summary-icon-primary {
            background: var(--pw-icon-blue-bg);
            color: var(--pw-icon-blue-fg);
        }

        .summary-icon-cyan {
            background: var(--pw-icon-cyan-bg);
            color: var(--pw-icon-cyan-fg);
        }

        .summary-icon-emerald {
            background: var(--pw-icon-emerald-bg);
            color: var(--pw-icon-emerald-fg);
        }

        .summary-icon-orange {
            background: var(--pw-icon-orange-bg);
            color: var(--pw-icon-orange-fg);
        }

        .summary-number {
            font-size: 1.25rem;
            line-height: 1;
            font-weight: 800;
            letter-spacing: -0.03em;
            margin-bottom: 0.25rem;
            color: var(--pw-ink);
        }

        .summary-label {
            font-size: 0.66rem;
            font-weight: 600;
            line-height: 1.35;
            color: #5f6d80;
        }

        .summary-link,
        .summary-subtext {
            margin-top: auto;
            padding-top: 0.5rem;
            font-size: 0.66rem;
            font-weight: 700;
        }

        .summary-link {
            display: flex;
            align-items: center;
            gap: 0.3rem;
            color: var(--pw-link);
        }

        .summary-subtext {
            color: var(--pw-ink-soft);
            font-weight: 600;
        }

        .summary-visual-card {
            min-height: 355px;
            padding: 1rem 0.75rem;
            background: var(--pw-surface);
            border: 1px solid var(--pw-border);
            border-radius: var(--pw-radius-md);
            box-shadow: var(--pw-shadow-card);
        }

        .summary-chart {
            position: relative;
            width: min(100%, 340px);
            height: 330px;
            margin: 0 auto;
        }

        .summary-chart::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 212px;
            height: 212px;
            transform: translate(-50%, -50%);
            border-radius: 50%;
            background: conic-gradient(#0b91b2 0deg 90deg,
                    #f2365a 90deg 180deg,
                    #27bd8c 180deg 270deg,
                    #ff9f12 270deg 360deg);
            box-shadow: 0 8px 18px rgba(23, 70, 102, 0.12);
        }

        .summary-chart-center {
            position: absolute;
            z-index: 1;
            top: 50%;
            left: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            width: 72px;
            height: 72px;
            transform: translate(-50%, -50%);
            border: 8px solid #243447;
            border-radius: 50%;
            background: #fff;
            color: #34465b;
            line-height: 1.1;
            text-align: center;
        }

        .summary-chart-center strong {
            font-size: 0.68rem;
            font-weight: 800;
        }

        .summary-chart-center span {
            margin-top: 0.12rem;
            font-size: 0.55rem;
            color: #7890a6;
        }

        .summary-orbit-label {
            position: absolute;
            z-index: 2;
            display: flex;
            flex-direction: column;
            gap: 0.08rem;
            min-width: 62px;
            color: #334155;
            font-size: 0.58rem;
            line-height: 1.15;
        }

        .summary-orbit-label strong {
            font-size: 0.72rem;
            font-weight: 800;
        }

        .summary-orbit-label span {
            color: #708399;
            font-weight: 600;
        }

        .summary-orbit-label::before {
            content: '';
            position: absolute;
            width: 7px;
            height: 7px;
            border: 2px solid #fff;
            border-radius: 50%;
            box-shadow: 0 0 0 1px currentColor;
        }

        .summary-orbit-report {
            top: 58px;
            left: 0;
            align-items: flex-end;
            text-align: right;
            color: #ff9f12;
        }

        .summary-orbit-report::after,
        .summary-orbit-presence::after {
            content: '';
            position: absolute;
            width: 78px;
            height: 24px;
            border-top: 2px solid currentColor;
            border-radius: 50%;
        }

        .summary-orbit-report::after {
            top: 10px;
            right: -83px;
            transform: rotate(12deg);
            transform-origin: right;
        }

        .summary-orbit-report::before {
            top: 19px;
            right: -90px;
        }

        .summary-orbit-total {
            top: 72px;
            right: 0;
            color: #0b91b2;
        }

        .summary-orbit-total::after,
        .summary-orbit-attendance::after {
            content: '';
            position: absolute;
            width: 74px;
            height: 24px;
            border-top: 2px solid currentColor;
            border-radius: 50%;
        }

        .summary-orbit-total::after {
            top: 9px;
            left: -79px;
            transform: rotate(-12deg);
            transform-origin: left;
        }

        .summary-orbit-total::before {
            top: 19px;
            left: -86px;
        }

        .summary-orbit-attendance {
            right: 0;
            bottom: 62px;
            color: #f2365a;
        }

        .summary-orbit-attendance::after {
            top: -22px;
            left: -78px;
            transform: rotate(12deg);
            transform-origin: left;
        }

        .summary-orbit-attendance::before {
            top: -10px;
            left: -85px;
        }

        .summary-orbit-presence {
            bottom: 55px;
            left: 0;
            align-items: flex-end;
            text-align: right;
            color: #27bd8c;
        }

        .summary-orbit-presence::after {
            top: -22px;
            right: -83px;
            transform: rotate(-12deg);
            transform-origin: right;
        }

        .summary-orbit-presence::before {
            top: -10px;
            right: -90px;
        }

        @media (max-width: 340px) {
            .summary-grid {
                grid-template-columns: 1fr;
            }

            .summary-visual-card {
                padding-inline: 0.35rem;
            }

            .summary-chart {
                transform: scale(0.92);
                transform-origin: center;
            }
        }

        /* ------------------------------------------------------------------
                                                                                   Period & evaluation panels
                                                                                ------------------------------------------------------------------ */
        .pegawai-period-panel,
        .pegawai-evaluation-panel {
            margin: 0.9rem 0 0;
            padding: 0.9rem 0.85rem;
            background: #fff;
            border: 1px solid var(--pw-border-soft);
            border-radius: var(--pw-radius-lg);
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.02);
        }

        .period-header-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            margin-bottom: 0.7rem;
        }

        .pegawai-evaluation-panel .panel-title-wrap {
            margin-bottom: 0.9rem;
        }

        .panel-title-wrap {
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        .panel-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 1.8rem;
            height: 1.8rem;
            border-radius: var(--pw-radius-sm);
            background: #edf6ff;
            color: var(--pw-primary-dark);
            font-size: 0.75rem;
        }

        .panel-title-wrap h3 {
            margin: 0;
            font-size: 0.82rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            color: var(--pw-ink);
        }

        .period-running-badge {
            padding: 0.3rem 0.6rem;
            border-radius: 999px;
            background: #d9f4eb;
            color: #177b5d;
            font-size: 0.62rem;
            font-weight: 800;
        }

        .pegawai-period-panel h2 {
            margin: 0 0 0.55rem;
            font-size: 1.05rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            color: #1d2a38;
        }

        .period-date-range {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            margin-bottom: 0.8rem;
            font-size: 0.68rem;
            color: var(--pw-ink-soft);
        }

        .progress-track {
            height: 0.4rem;
            margin-bottom: 0.6rem;
            background: #dfe7ee;
            border-radius: 999px;
            overflow: hidden;
        }

        .progress-fill {
            display: block;
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, #25aed9 0%, #1d7ef7 100%);
        }

        .progress-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
            margin-bottom: 0.8rem;
            font-size: 0.68rem;
            color: var(--pw-ink-soft);
        }

        .detail-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            padding: 0.7rem;
            background: #edf2f5;
            border: 1px solid #e2e9ef;
            border-radius: var(--pw-radius-sm);
            font-size: 0.68rem;
            font-weight: 600;
            color: #2b3a4a;
            text-decoration: none;
        }

        .detail-row-cta {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            color: var(--pw-link);
            font-weight: 700;
        }

        .evaluation-empty-state {
            padding: 1.1rem 0.9rem;
            text-align: center;
            background: rgba(255, 255, 255, 0.5);
            border: 1px dashed #d0d9e4;
            border-radius: var(--pw-radius-md);
            color: #4c5d73;
        }

        .evaluation-empty-state p {
            margin: 0 0 0.5rem;
            font-size: 0.82rem;
            font-weight: 700;
            color: #243447;
        }

        .evaluation-empty-state small {
            font-size: 0.68rem;
            line-height: 1.5;
            color: #687889;
        }

        .evaluation-result-card {
            padding: 0.85rem;
            background: #fff;
            border: 1px solid #edf1f6;
            border-radius: var(--pw-radius-lg);
        }

        .evaluation-result-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid #edf1f6;
        }

        .evaluation-title {
            display: flex;
            align-items: center;
            gap: 0.55rem;
            min-width: 0;
        }

        .evaluation-title-icon,
        .evaluation-tukin-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
            width: 1.8rem;
            height: 1.8rem;
            border-radius: 0.55rem;
            background: #edf5ff;
            color: #236fe5;
            font-size: 0.7rem;
        }

        .evaluation-title strong,
        .evaluation-title small {
            display: block;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .evaluation-title strong {
            font-size: 0.76rem;
            color: #243447;
        }

        .evaluation-title small {
            margin-top: 0.15rem;
            font-size: 0.56rem;
            color: #7b8da1;
        }

        .evaluation-status-badge {
            flex: 0 0 auto;
            padding: 0.3rem 0.55rem;
            border-radius: 999px;
            background: #e1f5eb;
            color: #187957;
            font-size: 0.58rem;
            font-weight: 800;
        }

        .evaluation-score-panel {
            padding: 0.9rem 0.1rem 0.8rem;
            border-bottom: 1px solid #e6eef5;
        }

        .evaluation-score-ring {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 142px;
            height: 142px;
            margin: 0 auto 0.9rem;
            border-radius: 50%;
            background: conic-gradient(#f23b5b var(--score-progress), #e1e8f0 0deg);
            transform: rotate(-18deg);
        }

        .evaluation-score-ring-inner {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            width: 116px;
            height: 116px;
            border-radius: 50%;
            background: #fff;
            transform: rotate(18deg);
        }

        .evaluation-score-ring-inner small {
            font-size: 0.56rem;
            font-weight: 800;
            color: #93a3b8;
        }

        .evaluation-score-ring-inner div {
            display: flex;
            align-items: baseline;
            margin: 0.1rem 0 0.25rem;
        }

        .evaluation-score-ring-inner div strong {
            font-size: 1.8rem;
            line-height: 1;
            color: #17243a;
        }

        .evaluation-score-ring-inner div span {
            margin-left: 0.12rem;
            font-size: 0.66rem;
            color: #8a9bb0;
        }

        .evaluation-score-ring-inner b {
            padding: 0.22rem 0.55rem;
            border: 1px solid #ffc5cf;
            border-radius: 999px;
            background: #fff1f3;
            color: #f23b5b;
            font-size: 0.58rem;
        }

        .evaluation-meta-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.55rem;
        }

        .evaluation-metric {
            padding: 0.65rem 0.55rem;
            border: 1px solid #edf1f6;
            border-radius: 0.65rem;
            background: #fff;
        }

        .evaluation-metric div {
            display: flex;
            justify-content: space-between;
            gap: 0.35rem;
        }

        .evaluation-metric span,
        .evaluation-metric strong {
            font-size: 0.58rem;
        }

        .evaluation-metric span {
            color: #718399;
        }

        .evaluation-metric strong {
            color: #2d4054;
        }

        .evaluation-metric i {
            display: block;
            height: 0.28rem;
            margin-top: 0.5rem;
            overflow: hidden;
            border-radius: 999px;
            background: #edf1f6;
        }

        .evaluation-metric i b {
            display: block;
            height: 100%;
            border-radius: inherit;
            background: #159dbd;
        }

        .evaluation-metric:nth-child(2) i b {
            background: #356ee8;
        }

        .evaluation-tukin {
            display: flex;
            align-items: center;
            gap: 0.55rem;
            margin-top: 0.8rem;
            padding: 0.7rem;
            border-radius: 0.7rem;
            background: #eef7ff;
        }

        .evaluation-tukin-icon {
            background: #fff;
            color: #159dbd;
        }

        .evaluation-tukin div {
            display: flex;
            flex-direction: column;
            gap: 0.18rem;
            min-width: 0;
        }

        .evaluation-tukin small {
            font-size: 0.54rem;
            font-weight: 800;
            letter-spacing: 0.04em;
            color: #718399;
        }

        .evaluation-tukin div strong {
            font-size: 0.68rem;
            color: #243447;
        }

        .evaluation-tukin>b {
            margin-left: auto;
            font-size: 0.82rem;
            color: #17243a;
        }

        @media (max-width: 340px) {
            .evaluation-meta-grid {
                grid-template-columns: 1fr 1fr;
            }
        }
    </style>
@endpush
