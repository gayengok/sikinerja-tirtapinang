
@extends('layouts.app')

@section('title', 'Dashboard Atasan')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/atasan/dashboard.css') }}?v=20261119-5">
@endpush

@section('content')
@php
    $namaAtasan = $atasan->nama ?? auth()->user()->name ?? 'Atasan';
    $namaPeriode = $periodeAktif->nama_periode ?? $periodeAktif->nama ?? 'Belum Ada Periode Aktif';

    $tanggalMulai = $periodeAktif?->tanggal_mulai
        ? \Carbon\Carbon::parse($periodeAktif->tanggal_mulai)->translatedFormat('d M Y')
        : '-';

    $tanggalSelesai = $periodeAktif?->tanggal_selesai
        ? \Carbon\Carbon::parse($periodeAktif->tanggal_selesai)->translatedFormat('d M Y')
        : '-';

    $routeLaporan = \Illuminate\Support\Facades\Route::has('atasan.laporan-harian.create')
        ? route('atasan.laporan-harian.create')
        : (\Illuminate\Support\Facades\Route::has('atasan.laporan-saya.create')
            ? route('atasan.laporan-saya.create')
            : null);

    $routeAbsensi = \Illuminate\Support\Facades\Route::has('atasan.absensi-saya.index')
        ? route('atasan.absensi-saya.index')
        : (\Illuminate\Support\Facades\Route::has('atasan.absensi.index')
            ? route('atasan.absensi.index')
            : null);

    $menuCepat = [
        ['judul' => 'Pegawai Bawahan', 'deskripsi' => 'Lihat daftar dan informasi staf.', 'icon' => 'fa-users', 'route' => 'atasan.pegawai.index'],
        ['judul' => 'Presensi Bawahan', 'deskripsi' => 'Pantau catatan kehadiran pegawai.', 'icon' => 'fa-calendar-check', 'route' => 'atasan.absensi.index'],
        ['judul' => 'Approval Laporan', 'deskripsi' => 'Periksa laporan yang diajukan.', 'icon' => 'fa-clipboard-check', 'route' => 'atasan.approval.index'],
        ['judul' => 'Evaluasi Performa', 'deskripsi' => 'Lihat hasil penilaian bawahan.', 'icon' => 'fa-chart-line', 'route' => 'atasan.penilaian.index'],
    ];

    $laporanAwal = collect($grafikLaporan)->firstWhere('tanggal', $tanggalHariIni) ?? end($grafikLaporan);
    $absensiAwal = collect($grafikAbsensi)->firstWhere('tanggal', $tanggalHariIni) ?? end($grafikAbsensi);

    $laporanAwal = $laporanAwal ?: [
        'total' => 0, 'disetujui' => 0, 'menunggu' => 0,
        'ditolak' => 0, 'terkirim' => 0,
        'belum_terkirim' => $jumlahBawahan,
        'persen_disetujui' => 0,
        'persen_menunggu' => 0,
        'persen_ditolak' => 0,
    ];

    $absensiAwal = $absensiAwal ?: [
        'tercatat' => 0,
        'belum_tercatat' => $jumlahBawahan,
        'label' => '-',
    ];
@endphp

<div class="atasan-dashboard">

    <section class="atasan-hero">
        <div class="atasan-hero-top">
            <div class="atasan-hero-intro">
                <div class="atasan-hero-eyebrow">
                    <span class="atasan-online-dot"></span>
                    <span>Atasan / Penilai</span>
                    <span class="atasan-eyebrow-divider"></span>
                    <i class="fa-solid fa-chart-line"></i>
                    <span>Dashboard Tim</span>
                </div>

                <h1>Selamat Datang, {{ $namaAtasan }}</h1>

                <p>Pantau kinerja tim, presensi pegawai, dan proses verifikasi laporan bawahan dalam satu dashboard.</p>
            </div>

            <div class="atasan-hero-period">
                <span class="atasan-hero-period-icon">
                    <i class="fa-regular fa-calendar-check"></i>
                </span>

                <div class="atasan-hero-period-copy">
                    <small>PERIODE PENILAIAN</small>
                    <strong>{{ $namaPeriode }}</strong>
                </div>

                <span class="atasan-period-badge">
                    <span></span>
                    {{ $periodeAktif ? 'Aktif' : 'Tidak Aktif' }}
                </span>
            </div>
        </div>

        <div class="atasan-hero-actions">
            <div class="atasan-hero-actions-title">
                <strong>
                    <i class="fa-solid fa-bolt"></i>
                    Aktivitas Saya
                </strong>
                <span>Laporan harian dan presensi pribadi</span>
            </div>

            <div class="atasan-hero-action-grid">
                @if($routeLaporan)
                    <a href="{{ $routeLaporan }}" class="atasan-hero-action">
                        <span class="atasan-hero-action-icon">
                            <i class="fa-solid fa-clipboard-question"></i>
                        </span>

                        <span class="atasan-hero-action-copy">
                            <strong>Laporan Harian ke Kepala Bagian</strong>
                            <small>Isi dan ajukan laporan kegiatan Anda untuk diverifikasi Kepala Bagian.</small>
                        </span>

                        <span class="atasan-hero-action-button">
                            Buat Laporan
                            <i class="fa-solid fa-arrow-right"></i>
                        </span>
                    </a>
                @endif

                @if($routeAbsensi)
                    <a href="{{ $routeAbsensi }}" class="atasan-hero-action">
                        <span class="atasan-hero-action-icon">
                            <i class="fa-regular fa-calendar-check"></i>
                        </span>

                        <span class="atasan-hero-action-copy">
                            <strong>Presensi Atasan</strong>
                            <small>Catat kehadiran Anda dengan verifikasi lokasi dan foto.</small>
                        </span>

                        <span class="atasan-hero-action-button">
                            Isi Absensi
                            <i class="fa-solid fa-arrow-right"></i>
                        </span>
                    </a>
                @endif
            </div>
        </div>
    </section>

    <section class="atasan-period-section">
        <div class="atasan-period-heading">
            <div class="atasan-period-heading-left">
                <span class="atasan-section-icon">
                    <i class="fa-regular fa-calendar-check"></i>
                </span>

                <div>
                    <span class="atasan-kicker">PERIODE AKTIF</span>
                    <h2>Periode Penilaian</h2>
                </div>
            </div>

            <div class="atasan-period-dates">
                <div>
                    <small>Mulai</small>
                    <strong>{{ $tanggalMulai }}</strong>
                </div>

                <i class="fa-solid fa-arrow-right"></i>

                <div>
                    <small>Selesai</small>
                    <strong>{{ $tanggalSelesai }}</strong>
                </div>
            </div>
        </div>

        <div class="atasan-period-progress">
            <div class="atasan-period-progress-top">
                <span>PROGRES WAKTU PERIODE</span>
                <strong>{{ $progresPeriode }}%</strong>
            </div>

            <div class="atasan-progress-track">
                <div style="width: {{ min(100, max(0, $progresPeriode)) }}%"></div>
            </div>
        </div>
    </section>

    <section class="atasan-overview-section">
        <div class="atasan-section-title">
            <div>
                <span class="atasan-kicker">OVERVIEW</span>
                <h2>Ringkasan Kinerja Tim</h2>
                <p>Statistik laporan dan aktivitas bawahan bulan {{ $namaBulan }}.</p>
            </div>

            <span class="atasan-update-note">
                <i class="fa-solid fa-database"></i>
                Data saat halaman dimuat
            </span>
        </div>

        <div class="atasan-stat-grid">
            <article class="atasan-stat-card">
                <span class="atasan-stat-icon">
                    <i class="fa-solid fa-users"></i>
                </span>

                <div class="atasan-stat-content">
                    <strong class="atasan-stat-number">{{ $jumlahBawahan }}</strong>
                    <h3>Bawahan Langsung</h3>
                    <p>Jumlah pegawai dalam tim Anda.</p>
                </div>

                <span class="atasan-stat-tag">Tim</span>
            </article>

            <article class="atasan-stat-card">
                <span class="atasan-stat-icon">
                    <i class="fa-solid fa-circle-check"></i>
                </span>

                <div class="atasan-stat-content">
                    <strong class="atasan-stat-number">{{ $laporanDisetujui }}</strong>
                    <h3>Laporan Disetujui</h3>
                    <p>Laporan yang telah diverifikasi.</p>
                </div>

                <span class="atasan-stat-tag">{{ $persenDisetujui }}%</span>
            </article>

            <article class="atasan-stat-card">
                <span class="atasan-stat-icon">
                    <i class="fa-regular fa-clock"></i>
                </span>

                <div class="atasan-stat-content">
                    <strong class="atasan-stat-number">{{ $laporanMenunggu }}</strong>
                    <h3>Menunggu Persetujuan</h3>
                    <p>Laporan yang perlu ditinjau.</p>
                </div>

                <span class="atasan-stat-tag">Review</span>
            </article>

            <article class="atasan-stat-card">
                <span class="atasan-stat-icon">
                    <i class="fa-solid fa-rotate-left"></i>
                </span>

                <div class="atasan-stat-content">
                    <strong class="atasan-stat-number">{{ $laporanDitolak }}</strong>
                    <h3>Laporan Ditolak</h3>
                    <p>Laporan yang perlu diperbaiki.</p>
                </div>

                <span class="atasan-stat-tag">Revisi</span>
            </article>
        </div>
    </section>

    <section class="atasan-chart-grid">
        <article class="atasan-panel atasan-report-panel">
            <div class="atasan-panel-heading">
                <div>
                    <span class="atasan-kicker">ANALISIS LAPORAN</span>
                    <h2>Status Laporan Bawahan</h2>
                    <p>Distribusi laporan pada tanggal yang dipilih.</p>
                </div>

                <span class="atasan-section-icon">
                    <i class="fa-solid fa-chart-pie"></i>
                </span>
            </div>

            <div class="atasan-chart-toolbar">
                <label for="atasan-laporan-tanggal">Tanggal laporan</label>

                <select id="atasan-laporan-tanggal" class="atasan-date-select">
                    @foreach($grafikLaporan as $item)
                        <option value="{{ $item['tanggal'] }}" @selected($item['tanggal'] === $tanggalHariIni)>
                            {{ $item['label'] }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="atasan-chart-summary-grid">
                <div class="atasan-chart-summary">
                    <span class="atasan-dot atasan-dot-green"></span>
                    <div>
                        <small id="atasan-laporan-terkirim-label">Terkirim Hari Ini</small>
                        <strong id="atasan-laporan-terkirim">{{ $laporanAwal['terkirim'] }}</strong>
                    </div>
                </div>

                <div class="atasan-chart-summary">
                    <span class="atasan-dot atasan-dot-gray"></span>
                    <div>
                        <small id="atasan-laporan-belum-label">Belum Terkirim Hari Ini</small>
                        <strong id="atasan-laporan-belum">{{ $laporanAwal['belum_terkirim'] }}</strong>
                    </div>
                </div>
            </div>

            <div class="atasan-report-chart-content">
                <div class="atasan-donut-container">
                    <div id="atasan-laporan-donut" class="atasan-donut">
                        <div class="atasan-donut-center">
                            <strong id="atasan-laporan-total">{{ $laporanAwal['total'] }}</strong>
                            <span>Total Laporan</span>
                        </div>
                    </div>
                </div>

                <div class="atasan-report-legend">
                    <div class="atasan-report-legend-row">
                        <span>Total Laporan</span>
                        <strong id="atasan-legend-total">{{ $laporanAwal['total'] }}</strong>
                    </div>

                    <div class="atasan-report-legend-row">
                        <span>
                            <i class="atasan-legend-dot is-green"></i>
                            Disetujui
                        </span>
                        <strong>
                            <span id="atasan-legend-disetujui">{{ $laporanAwal['disetujui'] }}</span>
                            <small id="atasan-persen-disetujui">{{ $laporanAwal['persen_disetujui'] }}%</small>
                        </strong>
                    </div>

                    <div class="atasan-report-legend-row">
                        <span>
                            <i class="atasan-legend-dot is-orange"></i>
                            Menunggu
                        </span>
                        <strong>
                            <span id="atasan-legend-menunggu">{{ $laporanAwal['menunggu'] }}</span>
                            <small id="atasan-persen-menunggu">{{ $laporanAwal['persen_menunggu'] }}%</small>
                        </strong>
                    </div>

                    <div class="atasan-report-legend-row">
                        <span>
                            <i class="atasan-legend-dot is-red"></i>
                            Ditolak / Revisi
                        </span>
                        <strong>
                            <span id="atasan-legend-ditolak">{{ $laporanAwal['ditolak'] }}</span>
                            <small id="atasan-persen-ditolak">{{ $laporanAwal['persen_ditolak'] }}%</small>
                        </strong>
                    </div>
                </div>
            </div>

            <div class="atasan-panel-footer">
                <i class="fa-solid fa-circle-info"></i>
                Data laporan berdasarkan tanggal yang dipilih.
            </div>
        </article>

        <article class="atasan-panel atasan-attendance-panel">
            <div class="atasan-panel-heading">
                <div>
                    <span class="atasan-kicker">MONITORING PRESENSI</span>
                    <h2>Absensi Bawahan</h2>
                    <p>Catatan absensi selama {{ $namaBulan }}.</p>
                </div>

                <span class="atasan-section-icon">
                    <i class="fa-regular fa-calendar-check"></i>
                </span>
            </div>

            <div class="atasan-chart-toolbar">
                <div class="atasan-selected-date">
                    <i class="fa-regular fa-calendar"></i>
                    <span id="atasan-absensi-tanggal-label">{{ $absensiAwal['label'] }}</span>
                </div>

                <div class="atasan-scroll-buttons">
                    <button type="button" id="atasan-absensi-prev" aria-label="Tanggal sebelumnya">
                        <i class="fa-solid fa-chevron-left"></i>
                    </button>

                    <button type="button" id="atasan-absensi-next" aria-label="Tanggal berikutnya">
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                </div>
            </div>

            <div class="atasan-chart-summary-grid">
                <div class="atasan-chart-summary">
                    <span class="atasan-dot atasan-dot-blue"></span>
                    <div>
                        <small id="atasan-absensi-tercatat-label">Tercatat Hari Ini</small>
                        <strong id="atasan-absensi-tercatat">{{ $absensiAwal['tercatat'] }}</strong>
                    </div>
                </div>

                <div class="atasan-chart-summary">
                    <span class="atasan-dot atasan-dot-gray"></span>
                    <div>
                        <small id="atasan-absensi-belum-label">Belum Tercatat Hari Ini</small>
                        <strong id="atasan-absensi-belum">{{ $absensiAwal['belum_tercatat'] }}</strong>
                    </div>
                </div>
            </div>

            <div class="atasan-attendance-chart-area">
                <div class="atasan-attendance-scroll" id="atasan-absensi-scroll">
                    <div class="atasan-attendance-bars">
                        @foreach($grafikAbsensi as $index => $item)
                            @php
                                $persenTercatat = $jumlahBawahan > 0
                                    ? $item['tercatat'] / $jumlahBawahan * 100
                                    : 0;
                            @endphp

                            <button
                                type="button"
                                class="atasan-attendance-day {{ $item['tanggal'] === $tanggalHariIni ? 'is-active' : '' }}"
                                data-index="{{ $index }}"
                                data-tanggal="{{ $item['tanggal'] }}"
                                aria-label="{{ $item['label'] }}, {{ $item['tercatat'] }} tercatat"
                                aria-pressed="{{ $item['tanggal'] === $tanggalHariIni ? 'true' : 'false' }}"
                            >
                                <span class="atasan-attendance-bar-track">
                                    <span class="atasan-attendance-bar-fill" style="height: {{ $persenTercatat }}%"></span>
                                </span>
                                <span class="atasan-attendance-day-label">{{ $item['label_pendek'] }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="atasan-attendance-legend">
                <span><i class="atasan-legend-dot is-blue"></i> Tercatat</span>
                <span><i class="atasan-legend-dot is-gray"></i> Belum Tercatat</span>
            </div>

            <div class="atasan-panel-footer">
                <i class="fa-solid fa-circle-info"></i>
                Geser grafik dan pilih tanggal untuk melihat rincian. Belum tercatat bukan berarti tidak hadir.
            </div>
        </article>
    </section>

    <section class="atasan-quick-section">
        <div class="atasan-section-title">
            <div>
                <span class="atasan-kicker">NAVIGASI</span>
                <h2>Aksi Cepat Atasan</h2>
                <p>Akses fitur pengelolaan tim.</p>
            </div>
        </div>

        <div class="atasan-quick-grid">
            @foreach($menuCepat as $menu)
                @if(\Illuminate\Support\Facades\Route::has($menu['route']))
                    <a href="{{ route($menu['route']) }}" class="atasan-quick-link">
                        <span class="atasan-quick-icon">
                            <i class="fa-solid {{ $menu['icon'] }}"></i>
                        </span>

                        <span class="atasan-quick-copy">
                            <strong>{{ $menu['judul'] }}</strong>
                            <small>{{ $menu['deskripsi'] }}</small>
                        </span>

                        <i class="fa-solid fa-chevron-right"></i>
                    </a>
                @endif
            @endforeach
        </div>
    </section>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const dataLaporan = @json($grafikLaporan);
    const dataAbsensi = @json($grafikAbsensi);
    const tanggalHariIni = @json($tanggalHariIni);

    const elemen = id => document.getElementById(id);

    function isi(id, nilai) {
        const target = elemen(id);

        if (target) {
            target.textContent = nilai;
        }
    }

    function persen(nilai) {
        return Number(nilai || 0).toLocaleString('id-ID', {
            maximumFractionDigits: 1
        }) + '%';
    }

    function tampilkanLaporan(tanggal) {
        const data = dataLaporan.find(item => item.tanggal === tanggal);

        if (!data) {
            return;
        }

        const hariIni = tanggal === tanggalHariIni;

        isi('atasan-laporan-terkirim', data.terkirim);
        isi('atasan-laporan-belum', data.belum_terkirim);

        isi('atasan-laporan-terkirim-label',
            hariIni ? 'Terkirim Hari Ini' : 'Terkirim');

        isi('atasan-laporan-belum-label',
            hariIni ? 'Belum Terkirim Hari Ini' : 'Belum Terkirim');

        isi('atasan-laporan-total', data.total);
        isi('atasan-legend-total', data.total);
        isi('atasan-legend-disetujui', data.disetujui);
        isi('atasan-legend-menunggu', data.menunggu);
        isi('atasan-legend-ditolak', data.ditolak);

        isi('atasan-persen-disetujui', persen(data.persen_disetujui));
        isi('atasan-persen-menunggu', persen(data.persen_menunggu));
        isi('atasan-persen-ditolak', persen(data.persen_ditolak));

        const donut = elemen('atasan-laporan-donut');

        if (!donut) {
            return;
        }

        const total = Number(data.total) || 0;

        if (total === 0) {
            donut.style.background = '#dbe5ef';
            return;
        }

        const hijau = Number(data.disetujui) / total * 100;
        const oranye = Number(data.menunggu) / total * 100;
        const batasOranye = hijau + oranye;

        donut.style.background = `conic-gradient(
            #10b981 0% ${hijau}%,
            #f59e0b ${hijau}% ${batasOranye}%,
            #fb7185 ${batasOranye}% 100%
        )`;
    }

    const dropdownLaporan = elemen('atasan-laporan-tanggal');

    if (dropdownLaporan) {
        dropdownLaporan.addEventListener('change', function () {
            tampilkanLaporan(this.value);
        });

        tampilkanLaporan(dropdownLaporan.value);
    }

    const tombolTanggal = Array.from(
        document.querySelectorAll('.atasan-attendance-day')
    );

    let indexAbsensiAktif = dataAbsensi.findIndex(
        item => item.tanggal === tanggalHariIni
    );

    if (indexAbsensiAktif < 0) {
        indexAbsensiAktif = Math.max(0, dataAbsensi.length - 1);
    }

    function tampilkanAbsensi(index, geser = true) {
        const data = dataAbsensi[index];

        if (!data) {
            return;
        }

        indexAbsensiAktif = index;

        isi('atasan-absensi-tanggal-label', data.label);
        isi('atasan-absensi-tercatat', data.tercatat);
        isi('atasan-absensi-belum', data.belum_tercatat);

        const hariIni = data.tanggal === tanggalHariIni;

        isi('atasan-absensi-tercatat-label',
            hariIni ? 'Tercatat Hari Ini' : 'Tercatat');

        isi('atasan-absensi-belum-label',
            hariIni ? 'Belum Tercatat Hari Ini' : 'Belum Tercatat');

        tombolTanggal.forEach(function (tombol, posisi) {
            const aktif = posisi === index;

            tombol.classList.toggle('is-active', aktif);
            tombol.setAttribute('aria-pressed', aktif ? 'true' : 'false');
        });

        const prev = elemen('atasan-absensi-prev');
        const next = elemen('atasan-absensi-next');

        if (prev) {
            prev.disabled = index <= 0;
        }

        if (next) {
            next.disabled = index >= dataAbsensi.length - 1;
        }

        if (geser && tombolTanggal[index]) {
            tombolTanggal[index].scrollIntoView({
                behavior: 'smooth',
                block: 'nearest',
                inline: 'center'
            });
        }
    }

    tombolTanggal.forEach(function (tombol, index) {
        tombol.addEventListener('click', function () {
            tampilkanAbsensi(index);
        });
    });

    elemen('atasan-absensi-prev')?.addEventListener('click', function () {
        tampilkanAbsensi(Math.max(0, indexAbsensiAktif - 1));
    });

    elemen('atasan-absensi-next')?.addEventListener('click', function () {
        tampilkanAbsensi(Math.min(
            dataAbsensi.length - 1,
            indexAbsensiAktif + 1
        ));
    });

    if (dataAbsensi.length) {
        tampilkanAbsensi(indexAbsensiAktif, false);

        requestAnimationFrame(function () {
            tombolTanggal[indexAbsensiAktif]?.scrollIntoView({
                behavior: 'instant',
                block: 'nearest',
                inline: 'center'
            });
        });
    }
});
</script>
@endpush
