@extends('layouts.app')

@section('title', 'Dashboard Atasan')

@section('content')
    @php
        $desktopReportTotal = $laporanMenunggu + $laporanDisetujui + $laporanDitolak;
        $desktopApprovalPercent = $desktopReportTotal > 0 ? round(($laporanDisetujui / $desktopReportTotal) * 100) : 0;
        $desktopPeriodProgress = 0;
        if ($periodeAktif?->tanggal_mulai && $periodeAktif?->tanggal_selesai) {
            $desktopPeriodStart = \Carbon\Carbon::parse($periodeAktif->tanggal_mulai);
            $desktopPeriodEnd = \Carbon\Carbon::parse($periodeAktif->tanggal_selesai);
            $desktopPeriodDays = max(1, $desktopPeriodStart->diffInDays($desktopPeriodEnd) + 1);
            $desktopElapsedDays = min(
                $desktopPeriodDays,
                max(0, $desktopPeriodStart->diffInDays(now()->startOfDay(), false) + 1),
            );
            $desktopPeriodProgress = (int) round(($desktopElapsedDays / $desktopPeriodDays) * 100);
        }
        $desktopApprovedShare = $desktopReportTotal > 0 ? round(($laporanDisetujui / $desktopReportTotal) * 100) : 0;
        $desktopPendingShare = $desktopReportTotal > 0 ? round(($laporanMenunggu / $desktopReportTotal) * 100) : 0;
        $desktopRejectedShare = max(0, 100 - $desktopApprovedShare - $desktopPendingShare);
    @endphp

    <div class="atasan-dashboard-desktop">
        <section class="atasan-desktop-welcome">
            <div class="atasan-desktop-welcome-copy">
                <div class="atasan-welcome-meta">
                    <span><i class="fa-solid fa-circle"></i> Atasan / Penilai</span>
                    <strong>DASHBOARD TIM</strong>
                </div>
                <h1>Selamat Datang, {{ auth()->user()->name }}</h1>
                <p>Ringkasan efektivitas tim, pemantauan presensi kerja, serta verifikasi laporan bawahan untuk periode
                    aktif saat ini.</p>
            </div>
            <div class="atasan-desktop-period-chip">
                <span><i class="fa-regular fa-calendar-check"></i></span>
                <div><small>PERIODE BERJALAN</small><strong>{{ $periodeAktif?->nama_periode ?? 'Belum tersedia' }}</strong>
                </div>
                @if ($periodeAktif)
                    <b>Berjalan</b>
                @endif
            </div>
        </section>

        <div class="atasan-desktop-section-heading">
            <h2>Ringkasan Statistik</h2>
            <span>Terakhir diperbarui: Realtime</span>
        </div>

        <section class="atasan-desktop-completion">
            <div class="atasan-desktop-completion-heading">
                <div>
                    <h2>Tingkat Penyelesaian Laporan Tim</h2>
                    <p>Efektivitas &amp; verifikasi seluruh bawahan bulan berjalan</p>
                </div>
                <span><i class="fa-solid fa-circle"></i> {{ $desktopApprovalPercent }}% Selesai</span>
            </div>
            <div class="atasan-desktop-completion-body">
                <div class="atasan-summary-ring" style="--progress: {{ $desktopApprovalPercent * 3.6 }}deg">
                    <strong>{{ $desktopApprovalPercent }}%</strong>
                    <small>{{ $desktopApprovalPercent >= 90 ? 'Tuntas' : 'Berjalan' }}</small>
                </div>
                <div class="atasan-desktop-distribution">
                    <div class="atasan-desktop-distribution-title"><strong>Distribusi Status Dokumen</strong><span>Total:
                            {{ $desktopReportTotal }} Laporan</span></div>
                    <div class="atasan-desktop-distribution-bar"
                        aria-label="Distribusi laporan disetujui, menunggu, dan ditolak">
                        <span class="is-approved" style="width: {{ $desktopApprovedShare }}%"></span>
                        <span class="is-pending" style="width: {{ $desktopPendingShare }}%"></span>
                        <span class="is-rejected" style="width: {{ $desktopRejectedShare }}%"></span>
                    </div>
                    <div class="atasan-desktop-distribution-legend">
                        <span><i class="is-approved"></i><b>{{ $laporanDisetujui }} Laporan</b><small>Disetujui
                                ({{ $desktopApprovedShare }}%)</small></span>
                        <span><i class="is-pending"></i><b>{{ $laporanMenunggu }} Laporan</b><small>Menunggu
                                ({{ $desktopPendingShare }}%)</small></span>
                        <span><i class="is-rejected"></i><b>{{ $laporanDitolak }} Laporan</b><small>Revisi
                                ({{ $desktopRejectedShare }}%)</small></span>
                    </div>
                </div>
            </div>
        </section>

        <section class="atasan-desktop-stats">
            <article class="summary-blue"><span><i class="fa-solid fa-users"></i></span>
                <div><strong>{{ $jumlahBawahan }}</strong><b>Orang Staf</b><small>Bawahan Langsung Aktif</small></div>
                <em>{{ $jumlahBawahan > 0 ? '100% Terbina' : 'Belum ada' }}</em>
            </article>
            <article class="summary-green"><span><i class="fa-solid fa-circle-check"></i></span>
                <div><strong>{{ $laporanDisetujui }}</strong><b>Laporan Tuntas</b><small>Tervalidasi &amp; Masuk
                        KPI</small></div><em>{{ $desktopApprovalPercent }}% Akurat</em>
            </article>
            <article class="summary-orange"><span><i class="fa-regular fa-clock"></i></span>
                <div><strong>{{ $laporanMenunggu }}</strong><b>Menunggu Review</b><small>Antrean Masuk Hari Ini</small>
                </div><em>{{ $laporanDitolak > 0 ? $laporanDitolak . ' Perlu Revisi' : 'Nihil Revisi' }}</em>
            </article>
        </section>

        <a class="atasan-desktop-pending" href="{{ route('atasan.laporan.index', ['status' => 'diajukan']) }}">
            <span><i class="fa-solid fa-clipboard-question"></i></span>
            <span><strong>Laporan Menunggu Approval Atasan</strong><small>Ada {{ $laporanMenunggu }} laporan bawahan yang
                    perlu diperiksa dan diverifikasi.</small></span>
            <b>Buka Approval <i class="fa-solid fa-arrow-right"></i></b>
        </a>

        <div class="atasan-desktop-lower">
            <section class="atasan-desktop-period-card">
                <div class="atasan-desktop-panel-heading">
                    <h2><i class="fa-regular fa-calendar-check"></i> Periode Penilaian Aktif</h2>
                    @if ($periodeAktif)
                        <span>Aktif Berjalan</span>
                    @endif
                </div>
                @if ($periodeAktif)
                    <small class="atasan-desktop-period-label">NAMA SIKLUS</small>
                    <h3>{{ $periodeAktif->nama_periode }}</h3>
                    <div class="atasan-desktop-period-dates">
                        <div><small>Batas Waktu
                                Pengisian</small><strong>{{ \Carbon\Carbon::parse($periodeAktif->tanggal_selesai)->translatedFormat('d F Y') }}</strong>
                        </div>
                        <div><small>Target Evaluasi</small><strong>{{ $jumlahBawahan }} Bawahan Langsung</strong></div>
                    </div>
                    <div class="atasan-desktop-period-progress"><span style="width: {{ $desktopPeriodProgress }}%"></span>
                    </div>
                    <div class="atasan-desktop-period-progress-meta"><span>Kelengkapan Evaluasi
                            Tim</span><strong>{{ $desktopPeriodProgress }}% Lengkap</strong></div>
                    <small class="atasan-desktop-period-caption">Data periode mengikuti jadwal yang ditetapkan
                        administrator.</small>
                @else
                    <p class="atasan-empty-text">Belum ada periode aktif.</p>
                @endif
            </section>
            <section class="atasan-desktop-quick-card">
                <div class="atasan-desktop-panel-heading">
                    <h2>AKSI CEPAT ATASAN</h2><span>Menu Utama</span>
                </div>
                <div class="atasan-desktop-quick-grid">
                    <a href="{{ route('atasan.pegawai.index') }}"><i class="fa-solid fa-users"></i><span><b>Pegawai
                                Bawahan</b><small>Kelola daftar staf, target KPI, dan rincian spesifikasi.</small></span><i
                            class="fa-solid fa-chevron-right"></i></a>
                    <a href="{{ route('atasan.absensi.index') }}"><i class="fa-regular fa-calendar-check"></i><span><b>Data
                                Presensi &amp; Shift</b><small>Pantau presensi harian, lembur, dan lokasi
                                produksi.</small></span><i class="fa-solid fa-chevron-right"></i></a>
                    <a href="{{ route('atasan.laporan.index', ['status' => 'diajukan']) }}"><i
                            class="fa-solid fa-clipboard-check"></i><span><b>Approval Laporan</b><small>Tinjau dan setujui
                                laporan harian staf.</small></span><i class="fa-solid fa-chevron-right"></i></a>
                    <a href="{{ route('atasan.hasil-penilaian.index') }}"><i
                            class="fa-solid fa-chart-line"></i><span><b>Evaluasi Performa</b><small>Periksa capaian dan
                                hasil penilaian bawahan.</small></span><i class="fa-solid fa-chevron-right"></i></a>
                </div>
            </section>
        </div>
    </div>

    <div class="atasan-dashboard-page atasan-dashboard-mobile">
        <section class="atasan-welcome-card">
            <div class="atasan-welcome-meta">
                <span><i class="fa-solid fa-circle"></i> Atasan / Penilai</span>
                <strong>Dashboard Tim</strong>
            </div>
            <h1>Selamat Datang, {{ auth()->user()->name }}</h1>
            <p>Ringkasan tim dan verifikasi laporan yang menjadi tanggung jawab Anda.</p>
        </section>

        @php
            $totalLaporan = $laporanMenunggu + $laporanDisetujui + $laporanDitolak;
            $approvalPercent = $totalLaporan > 0 ? round(($laporanDisetujui / $totalLaporan) * 100) : 0;
            $pendingRejected = $laporanMenunggu + $laporanDitolak;
        @endphp

        <div class="atasan-section-heading">
            <h2>Ringkasan Statistik</h2>
            <span>Terakhir diperbarui</span>
        </div>

        <section class="atasan-summary-card">
            <div class="atasan-summary-heading">
                <div>
                    <h2>Tingkat Penyelesaian Laporan Tim</h2>
                    <p>Efektivitas & verifikasi bulan berjalan</p>
                </div>
                <span><i class="fa-solid fa-circle"></i> {{ $approvalPercent }}% Selesai</span>
            </div>

            <div class="atasan-summary-progress">
                <div class="atasan-summary-ring" style="--progress: {{ $approvalPercent * 3.6 }}deg">
                    <strong>{{ $approvalPercent }}%</strong>
                    <small>{{ $approvalPercent >= 90 ? 'Optimal' : 'Berjalan' }}</small>
                </div>
                <div class="atasan-summary-breakdown">
                    <strong>Total Laporan: <b>{{ $totalLaporan }} Masuk</b></strong>
                    <div><i></i><span>Disetujui</span><b>{{ $laporanDisetujui }} ({{ $approvalPercent }}%)</b></div>
                    <div class="atasan-summary-line"><i style="width: {{ $approvalPercent }}%"></i></div>
                    <div class="muted"><span>Menunggu / Ditolak</span><b>{{ $pendingRejected }} Laporan</b></div>
                </div>
            </div>

            <div class="atasan-summary-stats">
                <article class="summary-blue">
                    <span><i class="fa-solid fa-users"></i></span>
                    <strong>{{ $jumlahBawahan }}</strong>
                    <b>Bawahan Aktif</b>
                    <small>{{ $jumlahBawahan > 0 ? '100% Terbina' : 'Belum ada bawahan' }}</small>
                </article>
                <article class="summary-green">
                    <span><i class="fa-solid fa-circle-check"></i></span>
                    <strong>{{ $laporanDisetujui }}</strong>
                    <b>Approval Tuntas</b>
                    <small>{{ $approvalPercent }}% Disetujui</small>
                </article>
                <article class="summary-gray">
                    <span><i class="fa-solid fa-clock"></i></span>
                    <strong>{{ $pendingRejected }}</strong>
                    <b>Antrean / Revisi</b>
                    <small>{{ $pendingRejected > 0 ? 'Perlu ditindaklanjuti' : 'Nihil menunggu' }}</small>
                </article>
            </div>
        </section>

        <article class="atasan-wide-stat">
            <span class="atasan-wide-icon"><i class="fa-solid fa-clipboard-question"></i></span>
            <div><strong>Laporan Menunggu Approval</strong><small>Laporan bawahan yang perlu diperiksa</small></div>
            <b>{{ $laporanMenunggu }}<small>Laporan</small></b>
        </article>

        <div class="atasan-dashboard-lower">
            <article class="atasan-panel atasan-period-card">
                <div class="atasan-panel-heading">
                    <h2><i class="fa-regular fa-calendar-check"></i> Periode Penilaian Aktif</h2>
                    @if ($periodeAktif)
                        <span class="atasan-status">Sedang Berjalan</span>
                    @endif
                </div>
                @if ($periodeAktif)
                    <h3>{{ $periodeAktif->nama_periode }}</h3>
                    <p><i class="fa-regular fa-clock"></i>
                        {{ \Carbon\Carbon::parse($periodeAktif->tanggal_mulai)->translatedFormat('d F Y') }}
                        &ndash; {{ \Carbon\Carbon::parse($periodeAktif->tanggal_selesai)->translatedFormat('d F Y') }}
                    </p>
                    <div class="atasan-period-footer"><span>Status Penutupan Form:</span><strong>Aktif</strong></div>
                @else
                    <p class="atasan-empty-text">Belum ada periode aktif.</p>
                @endif
            </article>

            {{-- <article class="atasan-panel atasan-action-card">
            <div class="atasan-panel-heading">
                <h2><i class="fa-regular fa-clipboard"></i> Aktivitas Memerlukan Tindakan</h2>
                <span>{{ $laporanMenunggu }} Tugas</span>
            </div>
            @if ($laporanMenunggu > 0)
                <div class="atasan-task-list">
                    @if ($laporanMenunggu > 0)
                        <a href="{{ route('atasan.laporan.index', ['status' => 'diajukan']) }}"><i class="fa-solid fa-file-circle-check"></i><span><b>{{ $laporanMenunggu }} laporan menunggu approval</b><small>Periksa dan berikan persetujuan</small></span><i class="fa-solid fa-chevron-right"></i></a>
                    @endif
                </div>
            @else
                <div class="atasan-no-task"><i class="fa-solid fa-check"></i><strong>Tidak ada laporan yang menunggu approval saat ini.</strong><span>Semua kewajiban verifikasi sudah terselesaikan.</span></div>
            @endif
        </article> --}}

            <div class="atasan-section-heading atasan-quick-heading">
                <h2>Aksi Cepat Atasan</h2>
            </div>
            <div class="atasan-quick-links">
                <a href="{{ route('atasan.pegawai.index') }}"><i class="fa-solid fa-users"></i><span><b>Pegawai
                            Bawahan</b><small>Kelola daftar & status target staf</small></span><i
                        class="fa-solid fa-chevron-right"></i></a>
                <a href="{{ route('atasan.absensi.index') }}"><i class="fa-regular fa-calendar-check"></i><span><b>Data
                            Absensi
                            Bawahan</b><small>Pantau kehadiran dan lokasi pegawai</small></span><i
                        class="fa-solid fa-chevron-right"></i></a>
                <a href="{{ route('atasan.laporan.index', ['status' => 'diajukan']) }}"><i
                        class="fa-solid fa-chart-column"></i><span><b>Approval Laporan</b><small>Setujui laporan untuk
                            menghitung KPI otomatis</small></span><i class="fa-solid fa-chevron-right"></i></a>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        body.role-atasan {
            background: #f6f9fc;
        }

        body.role-atasan .main-content {
            width: min(560px, 100%);
            margin: 0 auto;
            padding-bottom: calc(var(--bottom-nav-height) + 1rem);
        }

        body.role-atasan .footer-sikinerja {
            display: none;
        }

        .atasan-dashboard-page {
            padding: 1rem;
            color: #17213b;
        }

        .atasan-welcome-card {
            padding: 1.1rem 1.15rem;
            color: #fff;
            background: #0875ad;
            border-radius: 20px;
            box-shadow: 0 10px 20px rgba(12, 55, 95, .16);
        }

        .atasan-welcome-meta,
        .atasan-panel-heading,
        .atasan-period-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .6rem;
        }

        .atasan-welcome-meta {
            color: #c8e6f7;
            font-size: .66rem;
            font-weight: 700;
        }

        .atasan-welcome-meta i {
            color: #34d399;
            font-size: .45rem;
        }

        .atasan-welcome-meta strong {
            font-size: .7rem;
        }

        .atasan-welcome-card h1 {
            margin: 1rem 0 .35rem;
            font-size: 1.22rem;
            font-weight: 800;
        }

        .atasan-welcome-card p {
            margin: 0;
            color: #d5e6f6;
            font-size: .78rem;
            line-height: 1.55;
        }

        .atasan-section-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 1.25rem .15rem .7rem;
        }

        .atasan-section-heading h2 {
            margin: 0;
            color: #62738d;
            font-size: .78rem;
            font-weight: 800;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .atasan-section-heading span {
            color: #078ac8;
            font-size: .68rem;
        }

        .atasan-stat-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .7rem;
        }

        .atasan-stat-card {
            min-height: 145px;
            padding: 1rem;
            background: #fff;
            border: 1px solid #dfE7f0;
            border-radius: 18px;
            box-shadow: 0 3px 10px rgba(30, 50, 80, .04);
        }

        .atasan-stat-icon,
        .atasan-wide-icon {
            display: grid;
            place-items: center;
            width: 38px;
            height: 38px;
            margin-bottom: .65rem;
            border-radius: 12px;
        }

        .atasan-stat-card small,
        .atasan-stat-card em {
            display: block;
            color: #687b98;
            font-size: .68rem;
            font-style: normal;
        }

        .atasan-stat-card strong {
            display: block;
            margin: .45rem 0 .25rem;
            font-size: 1.55rem;
            line-height: 1;
        }

        .stat-blue .atasan-stat-icon {
            color: #2563eb;
            background: #edf4ff;
        }

        .stat-yellow {
            border-color: #f5d36b;
        }

        .stat-yellow .atasan-stat-icon {
            color: #d97706;
            background: #fff4ca;
        }

        .stat-yellow strong {
            color: #d97706;
        }

        .stat-green .atasan-stat-icon {
            color: #059669;
            background: #e9fbf3;
        }

        .stat-green strong {
            color: #059669;
        }

        .stat-red .atasan-stat-icon {
            color: #ef476f;
            background: #fff0f3;
        }

        .atasan-summary-card {
            padding: 1.1rem 1.15rem;
            background: #fff;
            border: 1px solid #dfe7f0;
            border-radius: 20px;
            box-shadow: 0 4px 14px rgba(30, 50, 80, .05);
        }

        .atasan-summary-heading {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: .6rem;
            padding-bottom: .85rem;
            border-bottom: 1px solid #edf1f5;
        }

        .atasan-summary-heading h2 {
            margin: 0;
            color: #26344d;
            font-size: .92rem;
        }

        .atasan-summary-heading p {
            margin: .25rem 0 0;
            color: #91a1b8;
            font-size: .7rem;
        }

        .atasan-summary-heading>span {
            padding: .45rem .65rem;
            color: #08a77f;
            background: #effcf7;
            border: 1px solid #9de7c4;
            border-radius: 999px;
            font-size: .65rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .atasan-summary-heading>span i {
            font-size: .42rem;
        }

        .atasan-summary-progress {
            display: flex;
            align-items: center;
            gap: 1.2rem;
            margin-top: 1rem;
            padding: 1rem;
            background: #f8fafc;
            border: 1px solid #edf1f5;
            border-radius: 17px;
        }

        .atasan-summary-ring {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 116px;
            height: 116px;
            flex: 0 0 116px;
            background: conic-gradient(#08b985 var(--progress), #e4f2ed 0);
            border-radius: 50%;
            position: relative;
        }

        .atasan-summary-ring::after {
            position: absolute;
            width: 82px;
            height: 82px;
            content: "";
            background: #fff;
            border-radius: 50%;
        }

        .atasan-summary-ring strong,
        .atasan-summary-ring small {
            position: relative;
            z-index: 1;
        }

        .atasan-summary-ring strong {
            color: #26344d;
            font-size: 1.55rem;
            line-height: 1;
        }

        .atasan-summary-ring small {
            margin-top: .25rem;
            color: #08a77f;
            font-size: .68rem;
            font-weight: 700;
        }

        .atasan-summary-breakdown {
            min-width: 0;
            flex: 1;
            color: #71829c;
            font-size: .72rem;
        }

        .atasan-summary-breakdown>strong {
            display: flex;
            justify-content: space-between;
            gap: .4rem;
            color: #71829c;
            font-size: .78rem;
        }

        .atasan-summary-breakdown>strong b,
        .atasan-summary-breakdown div b {
            color: #26344d;
            white-space: nowrap;
        }

        .atasan-summary-breakdown>div {
            display: flex;
            align-items: center;
            gap: .45rem;
            margin-top: .65rem;
        }

        .atasan-summary-breakdown>div i {
            width: 10px;
            height: 10px;
            background: #08b985;
            border-radius: 50%;
        }

        .atasan-summary-breakdown .muted {
            justify-content: space-between;
            color: #91a1b8;
        }

        .atasan-summary-line {
            height: 8px;
            margin-top: .35rem !important;
            overflow: hidden;
            background: #e1f2eb;
            border-radius: 99px;
        }

        .atasan-summary-line i {
            display: block;
            width: 100% !important;
            height: 100%;
            border-radius: inherit;
        }

        .atasan-summary-stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: .65rem;
            margin-top: 1rem;
        }

        .atasan-summary-stats article {
            min-width: 0;
            padding: .75rem .7rem;
            border: 1px solid #dfe7f0;
            border-radius: 16px;
        }

        .atasan-summary-stats article>span {
            display: grid;
            place-items: center;
            width: 34px;
            height: 34px;
            margin-bottom: .45rem;
            border-radius: 10px;
        }

        .atasan-summary-stats strong,
        .atasan-summary-stats b,
        .atasan-summary-stats small {
            display: block;
        }

        .atasan-summary-stats strong {
            margin-bottom: .15rem;
            color: #26344d;
            font-size: 1.35rem;
            line-height: 1;
        }

        .atasan-summary-stats b {
            color: #526480;
            font-size: .7rem;
        }

        .atasan-summary-stats small {
            margin-top: .2rem;
            font-size: .62rem;
        }

        .summary-blue {
            background: #f3fbff;
            border-color: #bdeaff !important;
        }

        .summary-blue>span {
            color: #078dcc;
            background: #dff4ff;
        }

        .summary-blue small {
            color: #078dcc;
        }

        .summary-green {
            background: #f2fff9;
            border-color: #b8f1d8 !important;
        }

        .summary-green>span {
            color: #08a77f;
            background: #d9f9e9;
        }

        .summary-green strong,
        .summary-green small {
            color: #08a77f;
        }

        .summary-gray {
            background: #f8fafc;
        }

        .summary-gray>span {
            color: #71829c;
            background: #edf2f6;
        }

        .summary-gray small {
            color: #91a1b8;
        }

        .atasan-wide-stat {
            display: flex;
            align-items: center;
            gap: .7rem;
            margin-top: .7rem;
            padding: 1rem;
            background: #fff;
            border: 1px solid #dfe7f0;
            border-radius: 18px;
        }

        .atasan-wide-icon {
            flex: 0 0 40px;
            margin: 0;
            color: #0891b2;
            background: #e8fbff;
        }

        .atasan-wide-stat div {
            min-width: 0;
            flex: 1;
        }

        .atasan-wide-stat strong,
        .atasan-wide-stat small,
        .atasan-wide-stat b small {
            display: block;
        }

        .atasan-wide-stat strong {
            font-size: .78rem;
        }

        .atasan-wide-stat small {
            margin-top: .2rem;
            color: #8a9ab4;
            font-size: .68rem;
        }

        .atasan-wide-stat b {
            text-align: right;
            font-size: 1.4rem;
        }

        .atasan-wide-stat b small {
            font-size: .6rem;
            font-weight: 500;
        }

        .atasan-panel {
            margin-top: 1rem;
            padding: 1rem;
            background: #fff;
            border: 1px solid #dfe7f0;
            border-radius: 20px;
            box-shadow: 0 3px 10px rgba(30, 50, 80, .04);
        }

        .atasan-panel-heading h2 {
            margin: 0;
            font-size: .8rem;
        }

        .atasan-panel-heading h2 i {
            margin-right: .35rem;
            color: #078ac8;
        }

        .atasan-panel-heading>span {
            color: #8a9ab4;
            font-size: .65rem;
        }

        .atasan-status {
            padding: .35rem .6rem;
            color: #078653 !important;
            background: #effcf7;
            border: 1px solid #9de7c4;
            border-radius: 8px;
            font-weight: 700;
        }

        .atasan-period-card h3 {
            margin: 1rem 0 .3rem;
            font-size: 1.05rem;
        }

        .atasan-period-card p {
            margin: 0;
            color: #70819c;
            font-size: .75rem;
        }

        .atasan-period-card p i {
            margin-right: .3rem;
        }

        .atasan-period-footer {
            margin-top: 1rem;
            padding-top: .75rem;
            border-top: 1px solid #edf0f5;
            color: #70819c;
            font-size: .72rem;
        }

        .atasan-period-footer strong {
            color: #078ac8;
        }

        .atasan-empty-text {
            margin-top: 1rem !important;
        }

        .atasan-action-card {
            padding-bottom: 1.1rem;
        }

        .atasan-task-list {
            display: grid;
            gap: .6rem;
            margin-top: 1rem;
        }

        .atasan-task-list a,
        .atasan-quick-links a {
            display: flex;
            align-items: center;
            gap: .7rem;
            color: #26344d;
            text-decoration: none;
        }

        .atasan-task-list a {
            padding: .7rem;
            background: #f8fafc;
            border-radius: 12px;
        }

        .atasan-task-list a>i:first-child,
        .atasan-quick-links a>i:first-child {
            color: #078ac8;
        }

        .atasan-task-list span,
        .atasan-quick-links span {
            min-width: 0;
            flex: 1;
        }

        .atasan-task-list b,
        .atasan-task-list small,
        .atasan-quick-links b,
        .atasan-quick-links small {
            display: block;
        }

        .atasan-task-list b,
        .atasan-quick-links b {
            font-size: .74rem;
        }

        .atasan-task-list small,
        .atasan-quick-links small {
            margin-top: .2rem;
            color: #8a9ab4;
            font-size: .65rem;
        }

        .atasan-task-list a>i:last-child,
        .atasan-quick-links a>i:last-child {
            color: #9aabc1;
            font-size: .7rem;
        }

        .atasan-no-task {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: .4rem;
            padding: 1.6rem .5rem .5rem;
            color: #526480;
            text-align: center;
        }

        .atasan-no-task>i {
            display: grid;
            place-items: center;
            width: 58px;
            height: 58px;
            margin-bottom: .5rem;
            color: #08a77f;
            background: #ecfcf5;
            border-radius: 50%;
            font-size: 1.3rem;
        }

        .atasan-no-task strong {
            font-size: .78rem;
        }

        .atasan-no-task span {
            color: #8a9ab4;
            font-size: .7rem;
        }

        .atasan-quick-heading {
            margin-top: 1.35rem;
        }

        .atasan-quick-links {
            overflow: hidden;
            background: #fff;
            border: 1px solid #dfe7f0;
            border-radius: 18px;
        }

        .atasan-quick-links a {
            padding: .9rem 1rem;
            border-bottom: 1px solid #edf0f5;
        }

        .atasan-quick-links a:last-child {
            border-bottom: 0;
        }

        .atasan-quick-links a>i:first-child {
            display: grid;
            place-items: center;
            width: 36px;
            height: 36px;
            background: #eaf4ff;
            border-radius: 11px;
        }

        .atasan-logout-card {
            display: flex;
            align-items: center;
            gap: .7rem;
            margin-top: 1rem;
            padding: .9rem 1rem;
            background: #fff;
            border: 1px solid #dfe7f0;
            border-radius: 18px;
        }

        .atasan-logout-icon {
            display: grid;
            place-items: center;
            width: 38px;
            height: 38px;
            color: #ef476f;
            background: #fff0f3;
            border-radius: 11px;
        }

        .atasan-logout-card>span:nth-child(2) {
            min-width: 0;
            flex: 1;
        }

        .atasan-logout-card strong,
        .atasan-logout-card small {
            display: block;
        }

        .atasan-logout-card strong {
            color: #26344d;
            font-size: .75rem;
        }

        .atasan-logout-card small {
            margin-top: .2rem;
            color: #8a9ab4;
            font-size: .64rem;
        }

        .atasan-logout-card button {
            padding: .5rem .8rem;
            color: #e83954;
            background: #fff0f3;
            border: 0;
            border-radius: 10px;
            font-size: .7rem;
            font-weight: 700;
        }

        @media (max-width:380px) {
            .atasan-dashboard-page {
                padding: .8rem;
            }

            .atasan-stat-card {
                padding: .8rem .7rem;
            }

            .atasan-welcome-card h1 {
                font-size: 1.08rem;
            }

            .atasan-summary-card {
                padding: .9rem .8rem;
            }

            .atasan-summary-heading h2 {
                font-size: .82rem;
            }

            .atasan-summary-heading p {
                font-size: .64rem;
            }

            .atasan-summary-heading>span {
                padding: .38rem .45rem;
                font-size: .58rem;
            }

            .atasan-summary-progress {
                gap: .7rem;
                padding: .7rem;
            }

            .atasan-summary-ring {
                width: 92px;
                height: 92px;
                flex-basis: 92px;
            }

            .atasan-summary-ring::after {
                width: 66px;
                height: 66px;
            }

            .atasan-summary-ring strong {
                font-size: 1.2rem;
            }

            .atasan-summary-ring small {
                font-size: .58rem;
            }

            .atasan-summary-breakdown {
                font-size: .62rem;
            }

            .atasan-summary-breakdown>strong {
                font-size: .66rem;
            }

            .atasan-summary-breakdown>div {
                margin-top: .45rem;
            }

            .atasan-summary-stats {
                gap: .4rem;
            }

            .atasan-summary-stats article {
                padding: .6rem .45rem;
            }

            .atasan-summary-stats strong {
                font-size: 1.15rem;
            }

            .atasan-summary-stats b {
                font-size: .62rem;
            }

            .atasan-summary-stats small {
                font-size: .55rem;
            }
        }

        @media (min-width: 992px) {
            body.role-atasan .mobile-app-shell {
                width: 100%;
                max-width: none;
                margin: 0;
                overflow-x: hidden;
            }

            body.role-atasan .navbar-sikinerja {
                top: 0;
                left: 0;
                right: 0;
                width: 100%;
                max-width: none;
                margin: 0;
                transform: none;
                background: #fff;
            }

            body.role-atasan .sidebar {
                display: block !important;
                transform: translateX(0);
            }

            body.role-atasan .sidebar-brand {
                display: none !important;
            }

            body.role-atasan .sidebar-overlay,
            body.role-atasan #sidebarCloseBtn,
            body.role-atasan .mobile-bottom-nav {
                display: none !important;
            }

            body.role-atasan .main-content {
                width: auto;
                max-width: none;
                margin-top: 64px;
                margin-left: var(--sidebar-width);
                padding-top: 0;
                padding-bottom: 2rem !important;
            }

            .atasan-dashboard-page {
                display: grid;
                grid-template-columns: minmax(0, 1fr);
                gap: .9rem;
                width: 100%;
                max-width: 1480px;
                margin: 0 auto;
                padding: 1.5rem 2rem 2rem;
                color: #1b2938;
            }

            .atasan-welcome-card {
                padding: 1.25rem 1.4rem;
                border-radius: 8px;
                box-shadow: none;
            }

            .atasan-welcome-card h1 {
                margin-top: .75rem;
                font-size: 1.45rem;
            }

            .atasan-welcome-card p {
                margin-top: .3rem;
                font-size: .78rem;
            }

            .atasan-section-heading {
                margin: .1rem 0 -.3rem;
            }

            .atasan-section-heading h2 {
                color: #34445e;
                font-size: .8rem;
                letter-spacing: .02em;
            }

            .atasan-summary-card {
                padding: 1.1rem;
                border-color: #e2e8ef;
                border-radius: 8px;
                box-shadow: none;
            }

            .atasan-summary-heading h2 {
                color: #25364a;
                font-size: .9rem;
            }

            .atasan-summary-heading p {
                font-size: .68rem;
            }

            .atasan-summary-progress {
                gap: 1.1rem;
                margin-top: .85rem;
                padding: .8rem;
                border-radius: 6px;
            }

            .atasan-summary-ring {
                width: 104px;
                height: 104px;
                flex-basis: 104px;
            }

            .atasan-summary-ring::after {
                width: 74px;
                height: 74px;
            }

            .atasan-summary-stats {
                gap: .7rem;
                margin-top: .8rem;
            }

            .atasan-summary-stats article {
                min-height: 100px;
                padding: .8rem;
                border-radius: 6px;
            }

            .atasan-summary-stats article>span {
                width: 30px;
                height: 30px;
                border-radius: 6px;
            }

            .atasan-summary-stats strong {
                font-size: 1.2rem;
            }

            .atasan-summary-stats b {
                font-size: .67rem;
            }

            .atasan-summary-stats small {
                font-size: .59rem;
            }

            .atasan-wide-stat {
                gap: .65rem;
                margin: 0;
                padding: .75rem .9rem;
                border-radius: 6px;
                box-shadow: none;
            }

            .atasan-wide-icon {
                width: 34px;
                height: 34px;
                flex-basis: 34px;
                border-radius: 6px;
            }

            .atasan-wide-stat strong {
                font-size: .72rem;
            }

            .atasan-wide-stat small {
                font-size: .61rem;
            }

            .atasan-wide-stat b {
                color: #25364a;
                font-size: 1.1rem;
            }

            .atasan-dashboard-lower {
                display: grid;
                grid-template-columns: minmax(0, 1fr) minmax(0, 1.15fr);
                align-items: start;
                gap: .8rem;
            }

            .atasan-dashboard-lower .atasan-period-card {
                grid-column: 1;
                grid-row: 1 / span 2;
                min-height: 100%;
                margin: 0;
                padding: .9rem 1rem;
                border-radius: 8px;
                box-shadow: none;
            }

            .atasan-dashboard-lower .atasan-quick-heading {
                grid-column: 2;
                grid-row: 1;
                margin: .1rem 0 -.3rem;
            }

            .atasan-dashboard-lower .atasan-quick-links {
                grid-column: 2;
                grid-row: 2;
                border-color: #e2e8ef;
                border-radius: 8px;
            }

            .atasan-dashboard-lower .atasan-quick-links a {
                min-height: 58px;
                padding: .7rem .8rem;
            }

            .atasan-dashboard-lower .atasan-quick-links a>i:first-child {
                width: 32px;
                height: 32px;
                border-radius: 6px;
            }

            .atasan-dashboard-lower .atasan-quick-links b {
                font-size: .7rem;
            }

            .atasan-dashboard-lower .atasan-quick-links small {
                font-size: .61rem;
            }

            .atasan-period-card h3 {
                margin-top: 1.1rem;
                color: #25364a;
                font-size: .95rem;
            }

            .atasan-period-card p,
            .atasan-period-footer {
                font-size: .67rem;
            }
        }

        @media (min-width: 992px) and (max-width: 1199.98px) {
            .atasan-dashboard-page {
                padding-right: 1.2rem;
                padding-left: 1.2rem;
            }

            .atasan-summary-progress {
                gap: .7rem;
            }

            .atasan-summary-ring {
                width: 88px;
                height: 88px;
                flex-basis: 88px;
            }

            .atasan-summary-ring::after {
                width: 64px;
                height: 64px;
            }
        }

        .atasan-dashboard-desktop {
            display: none;
        }

        @media (min-width: 768px) {
            .atasan-dashboard-mobile {
                display: none !important;
            }

            .atasan-dashboard-desktop {
                display: grid;
                grid-template-columns: minmax(0, 1fr);
                gap: .75rem;
                width: 100%;
                max-width: 1480px;
                margin: 0 auto;
                padding: 1.1rem clamp(1.25rem, 2vw, 2rem) 1.5rem;
                color: #1b2938;
            }

            .atasan-desktop-welcome {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1.2rem;
                min-height: 100px;
                padding: 1rem 1.25rem;
                color: #fff;
                background: linear-gradient(110deg, #087b9e, #0b7294 68%, #128c91);
                border-radius: 7px;
            }

            .atasan-desktop-welcome-copy {
                min-width: 0;
            }

            .atasan-desktop-welcome .atasan-welcome-meta {
                justify-content: flex-start;
                gap: .7rem;
                color: #c9eff4;
                font-size: .61rem;
            }

            .atasan-desktop-welcome .atasan-welcome-meta strong {
                padding-left: .65rem;
                border-left: 1px solid #ffffff50;
                font-size: .58rem;
            }

            .atasan-desktop-welcome h1 {
                margin: .55rem 0 .2rem;
                font-size: 1.35rem;
                font-weight: 750;
                overflow-wrap: anywhere;
            }

            .atasan-desktop-welcome p {
                max-width: 670px;
                margin: 0;
                color: #ddf2f6;
                font-size: .68rem;
                line-height: 1.45;
            }

            .atasan-desktop-period-chip {
                display: flex;
                align-items: center;
                gap: .55rem;
                flex: 0 0 auto;
                min-width: 220px;
                padding: .6rem .7rem;
                color: #fff;
                background: #ffffff20;
                border: 1px solid #ffffff30;
                border-radius: 6px;
            }

            .atasan-desktop-period-chip>span {
                display: grid;
                place-items: center;
                width: 32px;
                height: 32px;
                color: #fff;
                background: #ffffff25;
                border-radius: 5px;
            }

            .atasan-desktop-period-chip>div {
                min-width: 0;
                flex: 1;
            }

            .atasan-desktop-period-chip small,
            .atasan-desktop-period-chip strong {
                display: block;
            }

            .atasan-desktop-period-chip small {
                color: #c8e6ee;
                font-size: .51rem;
                font-weight: 700;
            }

            .atasan-desktop-period-chip strong {
                max-width: 180px;
                overflow: hidden;
                margin-top: .12rem;
                font-size: .65rem;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .atasan-desktop-period-chip>b {
                padding: .22rem .32rem;
                color: #087c50;
                background: #d7f9e9;
                border-radius: 4px;
                font-size: .52rem;
                white-space: nowrap;
            }

            .atasan-desktop-section-heading {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: .75rem;
                margin: .05rem .1rem -.25rem;
            }

            .atasan-desktop-section-heading h2 {
                margin: 0;
                color: #526480;
                font-size: .63rem;
                font-weight: 800;
                letter-spacing: .04em;
                text-transform: uppercase;
            }

            .atasan-desktop-section-heading span {
                color: #718092;
                font-size: .57rem;
            }

            .atasan-desktop-completion {
                padding: .85rem 1rem;
                background: #fff;
                border: 1px solid #e2e8ef;
                border-radius: 7px;
            }

            .atasan-desktop-completion-heading {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: .75rem;
                padding-bottom: .6rem;
                border-bottom: 1px solid #edf1f5;
            }

            .atasan-desktop-completion-heading h2 {
                margin: 0;
                color: #25364a;
                font-size: .75rem;
            }

            .atasan-desktop-completion-heading p {
                margin: .15rem 0 0;
                color: #8290a0;
                font-size: .59rem;
            }

            .atasan-desktop-completion-heading>span {
                padding: .28rem .42rem;
                color: #087c50;
                background: #e6f7ef;
                border-radius: 999px;
                font-size: .56rem;
                font-weight: 700;
                white-space: nowrap;
            }

            .atasan-desktop-completion-heading>span i {
                font-size: .38rem;
            }

            .atasan-desktop-completion-body {
                display: grid;
                grid-template-columns: 125px minmax(0, 1fr);
                align-items: center;
                gap: 1rem;
                padding-top: .75rem;
            }

            .atasan-desktop-completion-body .atasan-summary-ring {
                width: 88px;
                height: 88px;
                flex-basis: 88px;
                margin: 0 auto;
            }

            .atasan-desktop-completion-body .atasan-summary-ring::after {
                width: 64px;
                height: 64px;
            }

            .atasan-desktop-completion-body .atasan-summary-ring strong {
                font-size: 1.15rem;
            }

            .atasan-desktop-completion-body .atasan-summary-ring small {
                font-size: .55rem;
            }

            .atasan-desktop-distribution {
                min-width: 0;
            }

            .atasan-desktop-distribution-title {
                display: flex;
                justify-content: space-between;
                gap: .5rem;
                margin-bottom: .35rem;
                color: #526480;
                font-size: .57rem;
            }

            .atasan-desktop-distribution-title span {
                color: #8290a0;
                white-space: nowrap;
            }

            .atasan-desktop-distribution-bar {
                display: flex;
                height: 7px;
                overflow: hidden;
                background: #edf1f5;
                border-radius: 999px;
            }

            .atasan-desktop-distribution-bar span {
                display: block;
                min-width: 0;
                height: 100%;
            }

            .atasan-desktop-distribution-bar .is-approved,
            .atasan-desktop-distribution-legend .is-approved {
                background: #12b981;
            }

            .atasan-desktop-distribution-bar .is-pending,
            .atasan-desktop-distribution-legend .is-pending {
                background: #f5b82e;
            }

            .atasan-desktop-distribution-bar .is-rejected,
            .atasan-desktop-distribution-legend .is-rejected {
                background: #f16b7b;
            }

            .atasan-desktop-distribution-legend {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: .65rem;
                margin-top: .5rem;
            }

            .atasan-desktop-distribution-legend>span {
                display: grid;
                grid-template-columns: 7px minmax(0, auto);
                align-items: center;
                column-gap: .3rem;
                min-width: 0;
            }

            .atasan-desktop-distribution-legend i {
                width: 7px;
                height: 7px;
                border-radius: 50%;
            }

            .atasan-desktop-distribution-legend b,
            .atasan-desktop-distribution-legend small {
                grid-column: 2;
                overflow: hidden;
                color: #526480;
                font-size: .56rem;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .atasan-desktop-distribution-legend small {
                color: #8290a0;
                font-size: .52rem;
            }

            .atasan-desktop-stats {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: .7rem;
            }

            .atasan-desktop-stats article {
                position: relative;
                display: grid;
                grid-template-columns: 32px minmax(0, 1fr) auto;
                align-items: center;
                column-gap: .55rem;
                min-width: 0;
                min-height: 82px;
                padding: .65rem .75rem;
                background: #fff;
                border: 1px solid #e2e8ef;
                border-radius: 7px;
            }

            .atasan-desktop-stats article>span {
                display: grid;
                place-items: center;
                grid-row: 1 / span 3;
                width: 30px;
                height: 30px;
                border-radius: 5px;
            }

            .atasan-desktop-stats article>div {
                display: contents;
            }

            .atasan-desktop-stats article strong {
                color: #1b2938;
                font-size: 1.15rem;
                line-height: 1;
            }

            .atasan-desktop-stats article b {
                color: #526480;
                font-size: .63rem;
            }

            .atasan-desktop-stats article small {
                color: #8290a0;
                font-size: .54rem;
            }

            .atasan-desktop-stats article em {
                grid-column: 3;
                grid-row: 1 / span 3;
                padding: .25rem .35rem;
                color: #087c50;
                background: #e6f7ef;
                border-radius: 4px;
                font-size: .53rem;
                font-style: normal;
                white-space: nowrap;
            }

            .atasan-desktop-stats .summary-blue>span {
                color: #078dcc;
                background: #eaf5ff;
            }

            .atasan-desktop-stats .summary-green>span {
                color: #087c50;
                background: #e6f7ef;
            }

            .atasan-desktop-stats .summary-orange>span {
                color: #b77500;
                background: #fff5df;
            }

            .atasan-desktop-pending {
                display: flex;
                align-items: center;
                gap: .55rem;
                padding: .65rem .8rem;
                color: #07527c;
                background: #eaf7ff;
                border: 1px solid #c8eaff;
                border-radius: 6px;
                text-decoration: none;
            }

            .atasan-desktop-pending>span:first-child {
                display: grid;
                place-items: center;
                width: 30px;
                height: 30px;
                flex: 0 0 30px;
                color: #fff;
                background: #078dcc;
                border-radius: 5px;
            }

            .atasan-desktop-pending>span:nth-child(2) {
                display: grid;
                min-width: 0;
                flex: 1;
            }

            .atasan-desktop-pending strong {
                font-size: .65rem;
            }

            .atasan-desktop-pending small {
                margin-top: .1rem;
                color: #526b8c;
                font-size: .56rem;
            }

            .atasan-desktop-pending>b {
                display: inline-flex;
                align-items: center;
                gap: .35rem;
                padding: .4rem .55rem;
                color: #fff;
                background: #078dcc;
                border-radius: 4px;
                font-size: .57rem;
                white-space: nowrap;
            }

            .atasan-desktop-lower {
                display: grid;
                grid-template-columns: minmax(0, .85fr) minmax(0, 1.4fr);
                align-items: stretch;
                gap: .75rem;
            }

            .atasan-desktop-period-card,
            .atasan-desktop-quick-card {
                min-width: 0;
                padding: .8rem .9rem;
                background: #fff;
                border: 1px solid #e2e8ef;
                border-radius: 7px;
            }

            .atasan-desktop-panel-heading {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: .5rem;
                padding-bottom: .55rem;
                border-bottom: 1px solid #edf1f5;
            }

            .atasan-desktop-panel-heading h2 {
                margin: 0;
                color: #25364a;
                font-size: .7rem;
            }

            .atasan-desktop-panel-heading h2 i {
                margin-right: .25rem;
                color: #078dcc;
            }

            .atasan-desktop-panel-heading>span {
                color: #8290a0;
                font-size: .53rem;
                white-space: nowrap;
            }

            .atasan-desktop-panel-heading>span.atasan-status {
                padding: .22rem .35rem;
                color: #087c50 !important;
                background: #e6f7ef;
                border: 0;
                border-radius: 4px;
            }

            .atasan-desktop-period-label {
                display: block;
                margin-top: .65rem;
                color: #8290a0;
                font-size: .52rem;
                font-weight: 700;
            }

            .atasan-desktop-period-card h3 {
                margin: .2rem 0 .55rem;
                color: #25364a;
                font-size: .76rem;
            }

            .atasan-desktop-period-dates {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: .45rem;
            }

            .atasan-desktop-period-dates>div {
                padding: .45rem;
                background: #f8fafc;
                border-radius: 4px;
            }

            .atasan-desktop-period-dates small,
            .atasan-desktop-period-dates strong {
                display: block;
            }

            .atasan-desktop-period-dates small {
                color: #8290a0;
                font-size: .5rem;
            }

            .atasan-desktop-period-dates strong {
                margin-top: .1rem;
                color: #34445e;
                font-size: .57rem;
            }

            .atasan-desktop-period-progress {
                height: 5px;
                overflow: hidden;
                margin-top: .6rem;
                background: #e8eef4;
                border-radius: 999px;
            }

            .atasan-desktop-period-progress span {
                display: block;
                height: 100%;
                background: #078dcc;
                border-radius: inherit;
            }

            .atasan-desktop-period-progress-meta {
                display: flex;
                justify-content: space-between;
                gap: .3rem;
                margin-top: .25rem;
                color: #718092;
                font-size: .54rem;
            }

            .atasan-desktop-period-caption {
                display: block;
                margin-top: .6rem;
                padding-top: .5rem;
                color: #718092;
                border-top: 1px solid #edf1f5;
                font-size: .52rem;
            }

            .atasan-desktop-quick-grid {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: .45rem;
                padding-top: .55rem;
            }

            .atasan-desktop-quick-grid>a {
                display: flex;
                align-items: center;
                gap: .45rem;
                min-width: 0;
                padding: .5rem;
                color: #26344d;
                background: #fff;
                border: 1px solid #e8edf3;
                border-radius: 5px;
                text-decoration: none;
            }

            .atasan-desktop-quick-grid>a>i:first-child {
                display: grid;
                place-items: center;
                width: 28px;
                height: 28px;
                flex: 0 0 28px;
                color: #078dcc;
                background: #eaf5ff;
                border-radius: 5px;
                font-size: .72rem;
            }

            .atasan-desktop-quick-grid>a>span {
                display: block;
                min-width: 0;
                flex: 1;
            }

            .atasan-desktop-quick-grid b,
            .atasan-desktop-quick-grid small {
                display: block;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .atasan-desktop-quick-grid b {
                color: #25364a;
                font-size: .57rem;
                white-space: nowrap;
            }

            .atasan-desktop-quick-grid small {
                margin-top: .1rem;
                color: #8290a0;
                font-size: .5rem;
                line-height: 1.3;
            }

            .atasan-desktop-quick-grid>a>i:last-child {
                color: #a0acb9;
                font-size: .55rem;
            }
        }

        @media (min-width: 768px) and (max-width: 1199.98px) {
            .atasan-desktop-welcome {
                align-items: flex-start;
            }

            .atasan-desktop-period-chip {
                min-width: 180px;
            }

            .atasan-desktop-lower {
                grid-template-columns: minmax(0, 1fr);
            }

            .atasan-desktop-quick-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .atasan-dashboard-desktop {
                padding-right: 1rem;
                padding-left: 1rem;
            }
        }
    </style>
@endpush
