@extends('layouts.app')
@section('title', 'Dashboard Admin')
@section('content')
    @php
        $masterOverview = [
            [
                'label' => 'Divisi',
                'count' => $totalDivisi,
                'route' => 'master.divisi.index',
                'icon' => 'fa-sitemap',
                'tone' => 'blue',
            ],
            [
                'label' => 'Jabatan',
                'count' => $totalJabatan,
                'route' => 'master.jabatan.index',
                'icon' => 'fa-id-badge',
                'tone' => 'cyan',
            ],
            [
                'label' => 'Pegawai aktif',
                'count' => $totalPegawaiAktif,
                'route' => 'master.pegawai.index',
                'icon' => 'fa-users',
                'tone' => 'green',
            ],
            [
                'label' => 'Master KPI',
                'count' => $totalKpi,
                'route' => 'master.master-kpi.index',
                'icon' => 'fa-bullseye',
                'tone' => 'violet',
            ],
            [
                'label' => 'Kompetensi',
                'count' => $totalKompetensi,
                'route' => 'master.master-kompetensi.index',
                'icon' => 'fa-star',
                'tone' => 'orange',
            ],
            [
                'label' => 'Periode penilaian',
                'count' => $totalPeriode,
                'route' => 'master.periode-penilaian.index',
                'icon' => 'fa-calendar-days',
                'tone' => 'mint',
            ],
        ];
        $largestMasterCount = max(1, ...array_column($masterOverview, 'count'));
    @endphp

    <div class="master-dashboard-desktop">
        <header class="master-desktop-heading">
            <div>
                <span class="master-desktop-eyebrow">SIKINERJA / ADMIN</span>
                <h1>Dashboard</h1>
                <p>Ringkasan pengelolaan data Perumdam Tirta Pinang.</p>
            </div>
            <a href="{{ route('master.pegawai.index') }}" class="master-desktop-primary">
                <i class="fa-solid fa-users"></i> Kelola Pegawai
            </a>
        </header>

        <div class="master-desktop-greeting">
            <div>
                <span>PORTAL ADMINISTRATOR</span>
                <h2>Selamat datang, {{ auth()->user()->name }}</h2>
                <p>Perumdam Air Minum Tirta Pinang</p>
            </div>
            <div class="master-greeting-date"><i class="fa-regular fa-calendar"></i> {{ now()->translatedFormat('l, d F Y') }}
            </div>
        </div>

        <section class="master-desktop-stats" aria-label="Ringkasan dashboard">
            <article class="master-desktop-stat">
                <span class="master-desktop-stat-icon blue"><i class="fa-solid fa-sitemap"></i></span>
                <div><small>Total Divisi</small><strong>{{ $totalDivisi }}</strong><span>Data terdaftar</span></div>
                <i class="master-stat-watermark fa-solid fa-sitemap"></i>
            </article>
            <article class="master-desktop-stat">
                <span class="master-desktop-stat-icon cyan"><i class="fa-solid fa-id-badge"></i></span>
                <div><small>Total Jabatan</small><strong>{{ $totalJabatan }}</strong><span>Data terdaftar</span></div>
                <i class="master-stat-watermark fa-solid fa-id-badge"></i>
            </article>
            <article class="master-desktop-stat">
                <span class="master-desktop-stat-icon green"><i class="fa-solid fa-users"></i></span>
                <div><small>Pegawai Aktif</small><strong>{{ $totalPegawaiAktif }}</strong><span>Status aktif</span></div>
                <i class="master-stat-watermark fa-solid fa-users"></i>
            </article>
            <article class="master-desktop-stat">
                <span class="master-desktop-stat-icon orange"><i class="fa-solid fa-file-lines"></i></span>
                <div><small>Laporan Hari Ini</small><strong>{{ $laporanHariIni }}</strong><span>Berdasarkan tanggal
                        laporan</span></div>
                <i class="master-stat-watermark fa-solid fa-file-lines"></i>
            </article>
        </section>

        <section class="master-desktop-overview">
            <article class="master-desktop-panel master-data-overview">
                <div class="master-desktop-panel-heading">
                    <div>
                        <h2>Ringkasan Data Utama</h2>
                        <p>Jumlah data pada setiap kategori</p>
                    </div>
                    <i class="fa-solid fa-chart-column"></i>
                </div>
                <div class="master-data-bars">
                    @foreach ($masterOverview as $item)
                        <a href="{{ route($item['route']) }}" class="master-data-row">
                            <span class="master-desktop-stat-icon {{ $item['tone'] }}"><i
                                    class="fa-solid {{ $item['icon'] }}"></i></span>
                            <span class="master-data-label">{{ $item['label'] }}</span>
                            <span class="master-data-track"><span
                                    style="width: {{ round(($item['count'] / $largestMasterCount) * 100, 1) }}%"></span></span>
                            <strong>{{ $item['count'] }}</strong>
                            <i class="fa-solid fa-chevron-right"></i>
                        </a>
                    @endforeach
                </div>
            </article>

            <article class="master-desktop-panel master-desktop-period">
                <div class="master-desktop-panel-heading">
                    <div>
                        <h2>Periode Penilaian</h2>
                        <p>Status periode saat ini</p>
                    </div>
                    <i class="fa-regular fa-calendar-check"></i>
                </div>
                @if ($periodeAktif)
                    <span class="master-period-status is-active"><i class="fa-solid fa-circle"></i> BERJALAN</span>
                    <h3>{{ $periodeAktif->nama_periode }}</h3>
                    <p class="master-period-dates">
                        <i class="fa-regular fa-calendar"></i>
                        {{ \Carbon\Carbon::parse($periodeAktif->tanggal_mulai)->translatedFormat('d F Y') }}
                        <span>sampai</span>
                        {{ \Carbon\Carbon::parse($periodeAktif->tanggal_selesai)->translatedFormat('d F Y') }}
                    </p>
                @else
                    <span class="master-period-status is-inactive"><i class="fa-solid fa-circle"></i> BELUM ADA</span>
                    <h3>Belum ada periode aktif</h3>
                    <p class="master-period-dates">Atur periode penilaian untuk memulai siklus evaluasi.</p>
                @endif
                <a href="{{ route('master.periode-penilaian.index') }}" class="master-period-link">
                    Kelola periode <i class="fa-solid fa-arrow-right"></i>
                </a>
            </article>
        </section>

        <section class="master-desktop-modules">
            <div class="master-desktop-panel-heading">
                <div>
                    <h2>Akses Pengelolaan Data</h2>
                    <p>Pilih modul untuk melihat atau mengelola data.</p>
                </div>
                <a href="{{ route('master.divisi.index') }}">Lihat semua <i class="fa-solid fa-arrow-right"></i></a>
            </div>
            <div class="master-desktop-module-grid">
                <a href="{{ route('master.divisi.index') }}"><span class="blue"><i
                            class="fa-solid fa-sitemap"></i></span><strong>Divisi</strong><small>{{ $totalDivisi }}
                        data</small><i class="fa-solid fa-arrow-up-right-from-square"></i></a>
                <a href="{{ route('master.jabatan.index') }}"><span class="cyan"><i
                            class="fa-solid fa-id-badge"></i></span><strong>Jabatan</strong><small>{{ $totalJabatan }}
                        data</small><i class="fa-solid fa-arrow-up-right-from-square"></i></a>
                <a href="{{ route('master.pegawai.index') }}"><span class="green"><i
                            class="fa-solid fa-users"></i></span><strong>Pegawai</strong><small>{{ $totalPegawaiAktif }}
                        aktif</small><i class="fa-solid fa-arrow-up-right-from-square"></i></a>
                <a href="{{ route('master.master-kpi.index') }}"><span class="violet"><i
                            class="fa-solid fa-bullseye"></i></span><strong>Master KPI</strong><small>{{ $totalKpi }}
                        indikator</small><i class="fa-solid fa-arrow-up-right-from-square"></i></a>
                <a href="{{ route('master.master-kompetensi.index') }}"><span class="orange"><i
                            class="fa-solid fa-star"></i></span><strong>Kompetensi</strong><small>{{ $totalKompetensi }}
                        data</small><i class="fa-solid fa-arrow-up-right-from-square"></i></a>
                <a href="{{ route('master.periode-penilaian.index') }}"><span class="mint"><i
                            class="fa-regular fa-calendar"></i></span><strong>Periode
                        Penilaian</strong><small>{{ $totalPeriode }} periode</small><i
                        class="fa-solid fa-arrow-up-right-from-square"></i></a>
                <a href="{{ route('master.template-laporan.index') }}"><span class="teal"><i
                            class="fa-solid fa-file-lines"></i></span><strong>Template Laporan</strong><small>Kelola
                        template</small><i class="fa-solid fa-arrow-up-right-from-square"></i></a>
                <a href="{{ route('master.jam-kerja.index') }}"><span class="blue"><i
                            class="fa-regular fa-clock"></i></span><strong>Jam Kerja</strong><small>Atur jadwal</small><i
                        class="fa-solid fa-arrow-up-right-from-square"></i></a>
                <a href="{{ route('master.jenis-potongan.index') }}"><span class="red"><i
                            class="fa-solid fa-scissors"></i></span><strong>Jenis Potongan</strong><small>Atur
                        presensi</small><i class="fa-solid fa-arrow-up-right-from-square"></i></a>
            </div>
        </section>
    </div>

    <div class="master-dashboard-mobile">
        <div class="master-dashboard-page">
            <section class="master-welcome">
                <span><i class="fa-solid fa-shield-halved"></i> PORTAL UTAMA ADMINISTRATOR</span>
                <h1>Selamat Datang, {{ auth()->user()->name }}</h1>
                <p>Administrator SIKINERJA &mdash; Perumdam Air Minum Tirta Pinang</p>
            </section>

            <section class="master-stat-grid">
                <div><span class="master-stat-icon blue"><i
                            class="fa-solid fa-sitemap"></i></span><strong>{{ $totalDivisi }}</strong><small>Total
                        Divisi</small></div>
                <div><span class="master-stat-icon cyan"><i
                            class="fa-solid fa-id-badge"></i></span><strong>{{ $totalJabatan }}</strong><small>Total
                        Jabatan</small></div>
                <div><span class="master-stat-icon green"><i
                            class="fa-solid fa-users"></i></span><strong>{{ $totalPegawaiAktif }}</strong><small>Pegawai
                        Aktif</small></div>
                <div><span class="master-stat-icon mint"><i
                            class="fa-solid fa-file-lines"></i></span><strong>{{ $laporanHariIni }}</strong><small>Laporan
                        Hari
                        Ini</small></div>
            </section>

            <section class="master-period-card">
                <div class="master-card-heading"><span><i class="fa-regular fa-calendar-check"></i> Periode Penilaian
                        Aktif</span><b><i class="fa-solid fa-circle"></i>
                        {{ $periodeAktif ? 'BERJALAN' : 'BELUM ADA' }}</b>
                </div>
                @if ($periodeAktif)
                    <h2>{{ $periodeAktif->nama_periode }}</h2>
                    <p><i class="fa-regular fa-calendar"></i>
                        {{ \Carbon\Carbon::parse($periodeAktif->tanggal_mulai)->translatedFormat('d F Y') }} &ndash;
                        {{ \Carbon\Carbon::parse($periodeAktif->tanggal_selesai)->translatedFormat('d F Y') }}</p>
                @else
                    <h2>Belum ada periode aktif</h2>
                    <p>Silakan atur periode penilaian melalui menu Pengelolaan Data.</p>
                @endif
            </section>

            <div class="master-section-heading">
                <div>
                    <h2>Pengelolaan Data SIKINERJA</h2>
                    <p>Kelola struktur dan konfigurasi aplikasi</p>
                </div><a href="{{ route('master.divisi.index') }}"><i class="fa-solid fa-plus"></i> Tambah</a>
            </div>
            <div class="master-search"><i class="fa-solid fa-magnifying-glass"></i><input type="search"
                    placeholder="Cari menu master data..."></div>
            <section class="master-menu-grid">
                <a href="{{ route('master.divisi.index') }}"><span class="blue"><i
                            class="fa-solid fa-sitemap"></i></span><strong>Divisi</strong><small>{{ $totalDivisi }}
                        data</small></a>
                <a href="{{ route('master.jabatan.index') }}"><span class="cyan"><i
                            class="fa-solid fa-id-badge"></i></span><strong>Jabatan</strong><small>{{ $totalJabatan }}
                        data</small></a>
                <a href="{{ route('master.pegawai.index') }}"><span class="green"><i
                            class="fa-solid fa-users"></i></span><strong>Pegawai</strong><small>{{ $totalPegawaiAktif }}
                        aktif</small></a>
                <a href="{{ route('master.master-kpi.index') }}"><span class="violet"><i
                            class="fa-solid fa-bullseye"></i></span><strong>Master KPI</strong><small>{{ $totalKpi }}
                        indikator</small></a>
                <a href="{{ route('master.master-kompetensi.index') }}"><span class="orange"><i
                            class="fa-solid fa-star"></i></span><strong>Kompetensi</strong><small>{{ $totalKompetensi }}
                        data</small></a>
                <a href="{{ route('master.periode-penilaian.index') }}"><span class="mint"><i
                            class="fa-regular fa-calendar"></i></span><strong>Periode</strong><small>{{ $totalPeriode }}
                        periode</small></a>
                <a href="{{ route('master.jam-kerja.index') }}"><span class="teal"><i
                            class="fa-regular fa-clock"></i></span><strong>Jam Kerja</strong><small>Atur jadwal</small></a>
                <a href="{{ route('master.jenis-potongan.index') }}"><span class="red"><i
                            class="fa-solid fa-scissors"></i></span><strong>Potongan</strong><small>Atur
                        presensi</small></a>
            </section>
            <div class="master-dashboard-note"><i class="fa-solid fa-circle-info"></i><span>Gunakan menu di atas untuk
                    mengelola
                    data utama dan konfigurasi SIKINERJA.</span></div>

        </div>
    </div>
@endsection
@push('styles')
    <style>
        body.role-master .master-dashboard-page {
            box-sizing: border-box;
            width: 100%;
            max-width: 560px;
            margin: auto;
            padding: 1rem;
            color: #14223d;
            padding: 1rem 1rem 0;
        }

        .master-welcome {
            padding: 1rem 1.1rem;
            color: #fff;
            background: #0875ad;
            border-radius: 19px;
            box-shadow: 0 8px 20px #087fc044
        }

        .master-welcome>span {
            display: inline-block;
            padding: .3rem .55rem;
            color: #dff5ff;
            background: #ffffff2b;
            border-radius: 7px;
            font-size: .62rem;
            font-weight: 700
        }

        .master-welcome h1 {
            margin: .8rem 0 .2rem;
            font-size: 1.15rem;
            overflow-wrap: anywhere
        }

        .master-welcome p {
            margin: 0;
            color: #d9f2ff;
            font-size: .72rem
        }

        .master-stat-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .65rem;
            margin: 1rem 0
        }

        .master-stat-grid>div {
            display: grid;
            grid-template-columns: 38px 1fr;
            column-gap: .65rem;
            align-items: center;
            padding: .8rem;
            background: #fff;
            border: 1px solid #e1e8f0;
            border-radius: 16px;
            box-shadow: 0 3px 12px #2334550a
        }

        .master-stat-icon,
        .master-menu-grid span {
            display: grid;
            place-items: center
        }

        .master-stat-icon {
            grid-row: span 2;
            width: 38px;
            height: 38px;
            border-radius: 11px;
            font-size: .9rem
        }

        .master-stat-grid strong {
            font-size: 1.3rem;
            line-height: 1
        }

        .master-stat-grid small {
            color: #71829c;
            font-size: .66rem
        }

        .blue {
            color: #1267da;
            background: #e7f0ff
        }

        .cyan {
            color: #008da9;
            background: #e5fbff
        }

        .green {
            color: #078653;
            background: #e9fbf4
        }

        .mint,
        .teal {
            color: #078c73;
            background: #e8fbf5
        }

        .violet {
            color: #6857d8;
            background: #f0edff
        }

        .orange {
            color: #c87500;
            background: #fff5df
        }

        .red {
            color: #d33755;
            background: #fff0f3
        }

        .master-period-card {
            padding: 1rem;
            background: #fff;
            border: 1px solid #e1e8f0;
            border-radius: 18px;
            box-shadow: 0 3px 12px #2334550a
        }

        .master-card-heading {
            display: flex;
            justify-content: space-between;
            gap: .5rem;
            padding-bottom: .75rem;
            border-bottom: 1px solid #edf1f6;
            color: #0875ad;
            font-size: .72rem
        }

        .master-card-heading b {
            padding: .3rem .5rem;
            color: #078653;
            background: #dff9ed;
            border-radius: 12px;
            font-size: .58rem;
            white-space: nowrap
        }

        .master-card-heading b i {
            font-size: .4rem
        }

        .master-period-card h2 {
            margin: 1rem 0 .35rem;
            font-size: .95rem
        }

        .master-period-card p {
            margin: 0;
            color: #71829c;
            font-size: .7rem
        }

        .master-period-card p i {
            margin-right: .3rem;
            color: #91a1b8
        }

        .master-section-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: .6rem;
            margin: 1.2rem 0 .7rem
        }

        .master-section-heading h2 {
            margin: 0;
            font-size: .86rem
        }

        .master-section-heading p {
            margin: .2rem 0 0;
            color: #71829c;
            font-size: .65rem
        }

        .master-section-heading a {
            padding: .6rem .7rem;
            color: #fff;
            background: #078ac8;
            border-radius: 10px;
            text-decoration: none;
            font-size: .68rem;
            white-space: nowrap
        }

        .master-search {
            display: flex;
            align-items: center;
            gap: .55rem;
            padding: .7rem .8rem;
            margin-bottom: .75rem;
            background: #fff;
            border: 1px solid #dfe7f0;
            border-radius: 13px;
            color: #91a1b8
        }

        .master-search input {
            width: 100%;
            min-width: 0;
            border: 0;
            outline: 0;
            font-size: .72rem
        }

        .master-menu-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: .55rem
        }

        .master-menu-grid a {
            display: flex;
            flex-direction: column;
            align-items: center;
            min-width: 0;
            padding: .7rem .25rem;
            background: #fff;
            border: 1px solid #e1e8f0;
            border-radius: 14px;
            text-align: center;
            text-decoration: none;
            box-shadow: 0 3px 10px #23345508
        }

        .master-menu-grid span {
            width: 35px;
            height: 35px;
            margin-bottom: .45rem;
            border-radius: 10px;
            font-size: .85rem
        }

        .master-menu-grid strong {
            color: #263653;
            font-size: .65rem;
            overflow-wrap: anywhere
        }

        .master-menu-grid small {
            margin-top: .2rem;
            color: #91a1b8;
            font-size: .56rem;
            overflow-wrap: anywhere
        }

        .master-dashboard-note {
            display: flex;
            gap: .55rem;
            margin-top: 1rem;
            padding: .8rem;
            color: #0875ad;
            background: #eff9ff;
            border: 1px solid #bde5fb;
            border-radius: 13px;
            font-size: .68rem;
            line-height: 1.4
        }

        .master-dashboard-note i {
            margin-top: .1rem
        }

        @media(max-width:380px) {
            body.role-master .master-dashboard-page {
                padding: .75rem;
                padding-bottom: calc(var(--bottom-nav-height) + 1rem)
            }

            .master-welcome {
                padding: .9rem
            }

            .master-welcome h1 {
                font-size: 1rem
            }

            .master-stat-grid {
                gap: .45rem
            }

            .master-stat-grid>div {
                padding: .65rem;
                grid-template-columns: 32px 1fr;
                gap: .45rem
            }

            .master-stat-icon {
                width: 32px;
                height: 32px;
                font-size: .75rem
            }

            .master-stat-grid strong {
                font-size: 1.1rem
            }

            .master-stat-grid small {
                font-size: .58rem
            }

            .master-menu-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr))
            }

            .master-menu-grid a {
                padding: .8rem .4rem
            }

            .master-section-heading h2 {
                font-size: .78rem
            }

            .master-section-heading p {
                font-size: .59rem
            }
        }
    </style>
@endpush

@push('styles')
    <style>
        .master-dashboard-desktop {
            display: none;
        }

        @media (min-width: 768px) {
            .master-dashboard-mobile {
                display: none !important;
            }

            .master-dashboard-desktop {
                display: block;
                width: 100%;
                max-width: 1480px;
                margin: 0 auto;
                padding: 1.7rem 2rem 2.5rem;
                color: #1b2938;
            }

            .master-desktop-heading,
            .master-desktop-panel-heading {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
            }

            .master-desktop-heading {
                margin-bottom: 1.25rem;
            }

            .master-desktop-eyebrow {
                color: #607286;
                font-size: .68rem;
                font-weight: 700;
                letter-spacing: .08em;
            }

            .master-desktop-heading h1 {
                margin: .25rem 0 .2rem;
                font-size: 1.6rem;
                font-weight: 700;
            }

            .master-desktop-heading p,
            .master-desktop-panel-heading p {
                margin: 0;
                color: #788797;
                font-size: .82rem;
            }

            .master-desktop-primary,
            .master-period-link {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: .55rem;
                padding: .7rem .9rem;
                color: #fff;
                background: #1769d2;
                border-radius: 6px;
                font-size: .78rem;
                font-weight: 700;
                text-decoration: none;
                white-space: nowrap;
            }

            .master-desktop-primary:hover,
            .master-period-link:hover {
                color: #fff;
                background: #1056b0;
            }

            .master-desktop-greeting {
                display: flex;
                align-items: center;
                gap: .9rem;
                min-height: 84px;
                margin-bottom: 1rem;
                padding: 1rem 1.1rem;
                background: #fff;
                border: 1px solid #e2e8ef;
                border-left: 4px solid #1769d2;
                border-radius: 6px;
            }

            .master-desktop-greeting span {
                color: #778697;
                font-size: .61rem;
                font-weight: 700;
                letter-spacing: .07em;
            }

            .master-desktop-greeting h2 {
                margin: .1rem 0;
                font-size: 1rem;
                font-weight: 700;
            }

            .master-desktop-greeting p {
                margin: 0;
                color: #788797;
                font-size: .75rem;
            }

            .master-greeting-date {
                display: flex;
                align-items: center;
                gap: .45rem;
                margin-left: auto;
                color: #627285;
                font-size: .75rem;
                white-space: nowrap;
            }

            .master-desktop-stats {
                display: grid;
                grid-template-columns: repeat(4, minmax(0, 1fr));
                gap: .9rem;
                margin-bottom: 1rem;
            }

            .master-desktop-stat,
            .master-desktop-panel {
                background: #fff;
                border: 1px solid #e2e8ef;
                border-radius: 6px;
            }

            .master-desktop-stat {
                position: relative;
                display: flex;
                align-items: center;
                gap: .85rem;
                min-width: 0;
                min-height: 116px;
                overflow: hidden;
                padding: 1rem;
            }

            .master-desktop-stat-icon {
                display: grid;
                place-items: center;
                width: 42px;
                height: 42px;
                flex: 0 0 42px;
                border-radius: 6px;
                font-size: .95rem;
            }

            .master-desktop-stat>div {
                position: relative;
                z-index: 1;
                min-width: 0;
            }

            .master-desktop-stat small,
            .master-desktop-stat strong,
            .master-desktop-stat>div>span {
                display: block;
            }

            .master-desktop-stat small {
                color: #718092;
                font-size: .72rem;
            }

            .master-desktop-stat strong {
                margin: .12rem 0;
                color: #1b2938;
                font-size: 1.55rem;
                line-height: 1.2;
            }

            .master-desktop-stat>div>span {
                color: #8b98a6;
                font-size: .65rem;
            }

            .master-stat-watermark {
                position: absolute;
                right: .8rem;
                bottom: .45rem;
                color: #f0f3f6;
                font-size: 2.9rem;
            }

            .master-desktop-overview {
                display: grid;
                grid-template-columns: minmax(0, 1.55fr) minmax(300px, .9fr);
                gap: 1rem;
                margin-bottom: 1rem;
            }

            .master-desktop-panel {
                min-width: 0;
                padding: 1.1rem;
            }

            .master-desktop-panel-heading {
                margin-bottom: .95rem;
            }

            .master-desktop-panel-heading h2 {
                margin: 0 0 .2rem;
                color: #1b2938;
                font-size: .95rem;
                font-weight: 700;
            }

            .master-desktop-panel-heading>i {
                color: #8291a1;
                font-size: 1rem;
            }

            .master-data-bars {
                display: grid;
                gap: .25rem;
            }

            .master-data-row {
                display: grid;
                grid-template-columns: 32px minmax(105px, .8fr) minmax(80px, 1.35fr) 35px 10px;
                align-items: center;
                gap: .65rem;
                min-height: 43px;
                padding: .25rem .4rem;
                color: inherit;
                border-radius: 4px;
                text-decoration: none;
            }

            .master-data-row:hover {
                background: #f6f8fa;
            }

            .master-data-row .master-desktop-stat-icon {
                width: 30px;
                height: 30px;
                font-size: .72rem;
            }

            .master-data-label {
                color: #465668;
                font-size: .75rem;
            }

            .master-data-track {
                height: 7px;
                overflow: hidden;
                background: #edf1f5;
                border-radius: 8px;
            }

            .master-data-track>span {
                display: block;
                height: 100%;
                min-width: 2px;
                background: #2875df;
                border-radius: inherit;
            }

            .master-data-row strong {
                color: #25364a;
                font-size: .75rem;
                text-align: right;
            }

            .master-data-row>i {
                color: #a0acb9;
                font-size: .62rem;
            }

            .master-period-status {
                display: inline-flex;
                align-items: center;
                gap: .4rem;
                padding: .38rem .55rem;
                border-radius: 4px;
                font-size: .62rem;
                font-weight: 700;
            }

            .master-period-status i {
                font-size: .43rem;
            }

            .master-period-status.is-active {
                color: #087c50;
                background: #e6f7ef;
            }

            .master-period-status.is-inactive {
                color: #94620a;
                background: #fff5dc;
            }

            .master-desktop-period h3 {
                margin: 1rem 0 .55rem;
                color: #1b2938;
                font-size: 1.08rem;
                font-weight: 700;
            }

            .master-period-dates {
                display: flex;
                align-items: center;
                flex-wrap: wrap;
                gap: .35rem;
                min-height: 40px;
                margin: 0 0 1.1rem;
                color: #718092;
                font-size: .74rem;
                line-height: 1.5;
            }

            .master-period-dates>i {
                margin-right: .15rem;
                color: #1769d2;
            }

            .master-period-link {
                width: 100%;
                justify-content: space-between;
                padding: .65rem .75rem;
            }

            .master-desktop-modules {
                padding: 1.1rem;
                background: #fff;
                border: 1px solid #e2e8ef;
                border-radius: 6px;
            }

            .master-desktop-modules>.master-desktop-panel-heading {
                margin-bottom: 1rem;
            }

            .master-desktop-modules>.master-desktop-panel-heading>a {
                color: #1769d2;
                font-size: .73rem;
                font-weight: 700;
                text-decoration: none;
                white-space: nowrap;
            }

            .master-desktop-module-grid {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: .65rem;
            }

            .master-desktop-module-grid>a {
                position: relative;
                display: grid;
                grid-template-columns: 38px minmax(0, 1fr) 15px;
                grid-template-rows: auto auto;
                align-items: center;
                column-gap: .7rem;
                min-width: 0;
                padding: .8rem;
                color: inherit;
                background: #fff;
                border: 1px solid #e7ebf0;
                border-radius: 5px;
                text-decoration: none;
                transition: border-color .15s ease, background-color .15s ease;
            }

            .master-desktop-module-grid>a:hover {
                background: #f8fafc;
                border-color: #b7cdea;
            }

            .master-desktop-module-grid>a>span {
                display: grid;
                place-items: center;
                grid-row: span 2;
                width: 38px;
                height: 38px;
                border-radius: 5px;
                font-size: .82rem;
            }

            .master-desktop-module-grid strong {
                min-width: 0;
                overflow-wrap: anywhere;
                color: #29394b;
                font-size: .75rem;
            }

            .master-desktop-module-grid small {
                color: #8290a0;
                font-size: .66rem;
            }

            .master-desktop-module-grid>a>i {
                grid-column: 3;
                grid-row: 1 / span 2;
                color: #99a5b2;
                font-size: .65rem;
            }
        }

        @media (min-width: 768px) and (max-width: 1199.98px) {
            .master-dashboard-desktop {
                padding-right: 1.25rem;
                padding-left: 1.25rem;
            }

            .master-desktop-stats {
                gap: .65rem;
            }

            .master-desktop-stat {
                gap: .6rem;
                padding: .8rem;
            }

            .master-desktop-module-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (min-width: 768px) {
            body.role-master .sidebar {
                display: block !important;
            }

            body.role-master .sidebar-brand {
                display: none !important;
            }

            body.role-master .mobile-app-shell {
                width: 100%;
                max-width: none;
                margin: 0;
                overflow-x: hidden;
            }

            body.role-master .navbar-sikinerja {
                top: 0;
                left: 0;
                right: 0;
                width: 100%;
                max-width: none;
                margin: 0;
                transform: none;
                border-top: 0;
                background-color: #fff;
            }

            body.role-master .main-content {
                width: auto;
                margin-left: var(--sidebar-width);
                margin-top: 64px;
                padding-top: 0;
                padding-bottom: 2rem;
            }

            body.role-master.sidebar-collapsed .sidebar {
                width: 72px;
                transform: translateX(0);
            }

            body.role-master.sidebar-collapsed .main-content {
                margin-left: 72px;
            }

            body.role-master .sidebar-overlay,
            body.role-master #sidebarCloseBtn {
                display: none !important;
            }

            body.role-master .mobile-bottom-nav {
                display: none !important;
            }

            body.role-master .master-mobile-menu {
                display: none !important;
            }
        }
    </style>
@endpush

@push('styles')
    <style>
        .master-logout-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .7rem;
            margin-top: .8rem;
            padding: .8rem;
            background: #fff;
            border: 1px solid #f2d9de;
            border-radius: 14px;
            box-shadow: 0 3px 12px #2334550a
        }

        .master-logout-card>div {
            display: flex;
            align-items: center;
            gap: .65rem;
            min-width: 0
        }

        .master-logout-icon {
            display: grid;
            place-items: center;
            width: 34px;
            height: 34px;
            flex: 0 0 34px;
            color: #d33755;
            background: #fff0f3;
            border-radius: 10px
        }

        .master-logout-card strong,
        .master-logout-card small {
            display: block
        }

        .master-logout-card strong {
            color: #263653;
            font-size: .7rem
        }

        .master-logout-card small {
            margin-top: .2rem;
            color: #91a1b8;
            font-size: .58rem
        }

        .master-logout-card button {
            flex: 0 0 auto;
            padding: .55rem .7rem;
            color: #d33755;
            background: #fff0f3;
            border: 1px solid #f5cbd3;
            border-radius: 9px;
            font: inherit;
            font-size: .65rem;
            font-weight: 700;
            cursor: pointer
        }

        @media(max-width:380px) {
            .master-logout-card {
                align-items: flex-start
            }

            .master-logout-card small {
                max-width: 145px;
                line-height: 1.35
            }
        }
    </style>
@endpush
