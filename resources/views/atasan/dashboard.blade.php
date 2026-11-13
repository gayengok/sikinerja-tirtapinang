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
        @include('atasan.partials.pending-reports-notice')

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

        @include('atasan.partials.dashboard-actions')

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
        @include('atasan.partials.pending-reports-notice')

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

        @include('atasan.partials.dashboard-actions')

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
    <link rel="stylesheet" href="{{ asset('css/atasan/dashboard.css') }}">
@endpush
