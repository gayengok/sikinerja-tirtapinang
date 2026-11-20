
@extends('layouts.app')

@section('title', 'Dashboard Direktur')

@section('content')
    @php
        $totalKepalaBagian = $kepalaBagians->count();
        $sudahDinilai = $sudahDinilaiIds->count();
        $belumDinilai = max(0, $totalKepalaBagian - $sudahDinilai);
        $persentaseDinilai = $totalKepalaBagian > 0
            ? round(($sudahDinilai / $totalKepalaBagian) * 100)
            : 0;
    @endphp

    <main class="direktur-dashboard">
        <section class="direktur-dashboard-hero">
            <div class="direktur-dashboard-hero-top">
                <div class="direktur-dashboard-hero-content">
                    <span class="direktur-dashboard-eyebrow">
                        <i class="fa-solid fa-circle"></i>
                        Direktur / Penilai
                    </span>

                    <h1>Selamat Datang, {{ auth()->user()->name }}</h1>

                    <p>
                        Pantau progres penilaian kinerja Kepala Bagian,
                        kelola laporan, dan lihat ringkasan kehadiran tim
                        dalam satu dashboard.
                    </p>
                </div>

                <div class="direktur-dashboard-period">
                    <span class="direktur-dashboard-period-icon">
                        <i class="fa-regular fa-calendar-check"></i>
                    </span>

                    <div class="direktur-dashboard-period-copy">
                        <small>PERIODE PENILAIAN</small>
                        <strong>{{ $periodeAktif?->nama_periode ?? 'Belum tersedia' }}</strong>
                    </div>

                    <span class="direktur-dashboard-period-badge {{ $periodeAktif ? 'is-active' : 'is-inactive' }}">
                        <i class="fa-solid fa-circle"></i>
                        {{ $periodeAktif ? 'Aktif' : 'Belum Aktif' }}
                    </span>
                </div>
            </div>

            <div class="direktur-dashboard-hero-divider"></div>

            <div class="direktur-dashboard-quick-heading">
                <h2>
                    <i class="fa-solid fa-bolt"></i>
                    Akses Cepat
                </h2>
                <span>Kelola penilaian dan laporan Kepala Bagian</span>
            </div>

            <div class="direktur-dashboard-quick-links">
                <a class="direktur-dashboard-quick-link"
                    href="{{ route('direktur.rekap-data.index') }}">
                    <span class="direktur-dashboard-quick-icon">
                        <i class="fa-solid fa-calendar-check"></i>
                    </span>

                    <span class="direktur-dashboard-quick-copy">
                        <strong>Data Absensi</strong>
                        <small>Pantau rekap kehadiran pegawai seluruh divisi.</small>
                    </span>

                    <span class="direktur-dashboard-quick-arrow">
                        <i class="fa-solid fa-arrow-right"></i>
                    </span>
                </a>

                <a class="direktur-dashboard-quick-link"
                    href="{{ route('direktur.laporan.index', ['status' => 'diajukan']) }}">
                    <span class="direktur-dashboard-quick-icon">
                        <i class="fa-solid fa-file-circle-check"></i>
                    </span>

                    <span class="direktur-dashboard-quick-copy">
                        <strong>Approval Laporan</strong>
                        <small>Tinjau, setujui, atau tolak laporan yang diajukan.</small>
                    </span>

                    <span class="direktur-dashboard-quick-arrow">
                        <i class="fa-solid fa-arrow-right"></i>
                    </span>
                </a>

                <a class="direktur-dashboard-quick-link"
                    href="{{ route('direktur.penilaian-kepala-bagian.index') }}">
                    <span class="direktur-dashboard-quick-icon">
                        <i class="fa-solid fa-chart-line"></i>
                    </span>

                    <span class="direktur-dashboard-quick-copy">
                        <strong>Penilaian KPI</strong>
                        <small>Berikan penilaian kinerja kepada Kepala Bagian.</small>
                    </span>

                    <span class="direktur-dashboard-quick-arrow">
                        <i class="fa-solid fa-arrow-right"></i>
                    </span>
                </a>
            </div>
        </section>

        <section class="direktur-dashboard-stats" aria-label="Ringkasan statistik penilaian">
            <article class="direktur-dashboard-stat is-team">
                <div class="direktur-dashboard-stat-top">
                    <span class="direktur-dashboard-stat-label">TOTAL KEPALA BAGIAN</span>
                    <span class="direktur-dashboard-stat-icon">
                        <i class="fa-solid fa-users"></i>
                    </span>
                </div>

                <strong>{{ $totalKepalaBagian }}</strong>

                <p>Kepala Bagian aktif yang ditugaskan kepada Anda</p>
            </article>

            <article class="direktur-dashboard-stat is-complete">
                <div class="direktur-dashboard-stat-top">
                    <span class="direktur-dashboard-stat-label">SUDAH DINILAI</span>
                    <span class="direktur-dashboard-stat-icon">
                        <i class="fa-solid fa-circle-check"></i>
                    </span>
                </div>

                <strong>{{ $sudahDinilai }}</strong>

                <p>
                    <i class="fa-solid fa-circle"></i>
                    {{ $persentaseDinilai }}% penilaian telah diselesaikan
                </p>
            </article>

            <article class="direktur-dashboard-stat is-pending">
                <div class="direktur-dashboard-stat-top">
                    <span class="direktur-dashboard-stat-label">BELUM DINILAI</span>
                    <span class="direktur-dashboard-stat-icon">
                        <i class="fa-regular fa-clock"></i>
                    </span>
                </div>

                <strong>{{ $belumDinilai }}</strong>

                <p>
                    @if ($belumDinilai > 0)
                        <span class="direktur-dashboard-pending-label">Perlu tindakan</span>
                        Menunggu penilaian KPI
                    @else
                        Semua Kepala Bagian telah dinilai
                    @endif
                </p>
            </article>
        </section>

        <section class="direktur-dashboard-progress">
            <div class="direktur-dashboard-section-heading">
                <div class="direktur-dashboard-section-title">
                    <span class="direktur-dashboard-section-icon">
                        <i class="fa-solid fa-chart-line"></i>
                    </span>

                    <div>
                        <span class="direktur-dashboard-section-eyebrow">PERIODE AKTIF</span>
                        <h2>Progres Penilaian Kepala Bagian</h2>
                        <p>{{ $periodeAktif?->nama_periode ?? 'Belum ada periode aktif' }}</p>
                    </div>
                </div>

                <span class="direktur-dashboard-section-period">
                    <i class="fa-regular fa-calendar"></i>
                    {{ $periodeAktif?->nama_periode ?? 'Belum tersedia' }}
                </span>
            </div>

            <div class="direktur-dashboard-progress-panel">
                <div class="direktur-dashboard-progress-heading">
                    <div>
                        <strong>PROGRES PENILAIAN TIM</strong>
                        <p>Persentase Kepala Bagian yang telah mendapatkan penilaian KPI.</p>
                    </div>

                    <span class="direktur-dashboard-progress-percentage">
                        {{ $persentaseDinilai }}%
                    </span>
                </div>

                <div class="direktur-dashboard-progress-track"
                    role="progressbar"
                    aria-label="Progres penilaian Kepala Bagian"
                    aria-valuenow="{{ $persentaseDinilai }}"
                    aria-valuemin="0"
                    aria-valuemax="100">
                    <span style="width: {{ $persentaseDinilai }}%"></span>
                </div>

                <div class="direktur-dashboard-progress-footer">
                    <span>{{ $sudahDinilai }} dari {{ $totalKepalaBagian }} Kepala Bagian telah dinilai</span>
                    <span>{{ $belumDinilai }} penilaian tersisa</span>
                </div>
            </div>
        </section>

        <section class="direktur-dashboard-action">
            <span class="direktur-dashboard-action-icon">
                <i class="fa-solid fa-clipboard-check"></i>
            </span>

            <div class="direktur-dashboard-action-copy">
                <h2>Penilaian Kinerja Kepala Bagian</h2>
                <p>
                    Tinjau dan berikan nilai KPI kepada Kepala Bagian
                    yang menjadi tanggung jawab Anda.
                </p>
            </div>

            <a href="{{ route('direktur.penilaian-kepala-bagian.index') }}">
                Buka Penilaian
                <i class="fa-solid fa-arrow-right"></i>
            </a>
        </section>
    </main>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/direktur/index.css') }}">
@endpush
