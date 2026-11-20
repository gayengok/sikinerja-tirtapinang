
@php
    $role = auth()->user()?->role;
@endphp

<aside class="sidebar sikinerja-sidebar" id="sidebar">

    <div class="sidebar-brand">
        <a href="{{ match ($role) {
            'master' => route('master.dashboard'),
            'kepala_bagian' => route('kepala_bagian.dashboard'),
            'direktur' => route('direktur.dashboard'),
            'atasan' => route('atasan.dashboard'),
            'pegawai' => route('pegawai.dashboard'),
            default => url('/'),
        } }}" class="sidebar-brand-link">

            <span class="sidebar-logo-container">
                <img src="{{ asset('img/logo.png') }}"
                    alt="Logo SIKINERJA"
                    class="sidebar-logo-mark">
            </span>

            <span class="sidebar-brand-text">
                <strong>SIKINERJA</strong>
                <small>PDAM TIRTA PINANG</small>
            </span>
        </a>

        <button type="button"
            class="sidebar-close-button"
            id="sidebarCloseBtn"
            aria-label="Tutup navigasi">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <nav class="sidebar-nav" aria-label="Menu utama">

        <div class="sidebar-menu-heading">
            <span>MENU UTAMA</span>
            <span class="sidebar-menu-heading-line"></span>
        </div>

        @if ($role === 'master')

            <a href="{{ route('master.dashboard') }}"
                class="sidebar-link {{ request()->routeIs('master.dashboard') ? 'active' : '' }}">
                <span class="sidebar-link-icon"><i class="fa-solid fa-gauge-high"></i></span>
                <span class="sidebar-link-text">Dashboard</span>
                @if(request()->routeIs('master.dashboard'))
                    <span class="sidebar-active-indicator"></span>
                @endif
            </a>

            <div class="sidebar-section-title">Pengelolaan Data</div>

            <a href="{{ route('master.divisi.index') }}"
                class="sidebar-link {{ request()->routeIs('master.divisi.*') ? 'active' : '' }}">
                <span class="sidebar-link-icon"><i class="fa-solid fa-sitemap"></i></span>
                <span class="sidebar-link-text">Divisi</span>
            </a>

            <a href="{{ route('master.jabatan.index') }}"
                class="sidebar-link {{ request()->routeIs('master.jabatan.*') ? 'active' : '' }}">
                <span class="sidebar-link-icon"><i class="fa-solid fa-id-badge"></i></span>
                <span class="sidebar-link-text">Jabatan</span>
            </a>

            <a href="{{ route('master.pegawai.index') }}"
                class="sidebar-link {{ request()->routeIs('master.pegawai.*') ? 'active' : '' }}">
                <span class="sidebar-link-icon"><i class="fa-solid fa-users"></i></span>
                <span class="sidebar-link-text">Pegawai</span>
            </a>

            <a href="{{ route('master.periode-penilaian.index') }}"
                class="sidebar-link {{ request()->routeIs('master.periode-penilaian.*') ? 'active' : '' }}">
                <span class="sidebar-link-icon"><i class="fa-solid fa-calendar-days"></i></span>
                <span class="sidebar-link-text">Periode Penilaian</span>
            </a>

            <a href="{{ route('master.master-kpi.index') }}"
                class="sidebar-link {{ request()->routeIs('master.master-kpi.*') ? 'active' : '' }}">
                <span class="sidebar-link-icon"><i class="fa-solid fa-bullseye"></i></span>
                <span class="sidebar-link-text">Master KPI</span>
            </a>

            <a href="{{ route('master.master-kompetensi.index') }}"
                class="sidebar-link {{ request()->routeIs('master.master-kompetensi.*') ? 'active' : '' }}">
                <span class="sidebar-link-icon"><i class="fa-solid fa-star"></i></span>
                <span class="sidebar-link-text">Master Kompetensi</span>
            </a>

            <a href="{{ route('master.template-laporan.index') }}"
                class="sidebar-link {{ request()->routeIs('master.template-laporan.*') ? 'active' : '' }}">
                <span class="sidebar-link-icon"><i class="fa-solid fa-file-lines"></i></span>
                <span class="sidebar-link-text">Template Laporan</span>
            </a>

            <a href="{{ route('master.jam-kerja.index') }}"
                class="sidebar-link {{ request()->routeIs('master.jam-kerja.*') ? 'active' : '' }}">
                <span class="sidebar-link-icon"><i class="fa-solid fa-clock"></i></span>
                <span class="sidebar-link-text">Jam Kerja</span>
            </a>

            <a href="{{ route('master.jenis-potongan.index') }}"
                class="sidebar-link {{ request()->routeIs('master.jenis-potongan.*') ? 'active' : '' }}">
                <span class="sidebar-link-icon"><i class="fa-solid fa-scissors"></i></span>
                <span class="sidebar-link-text">Jenis Potongan</span>
            </a>

        @elseif ($role === 'kepala_bagian')

            <a href="{{ route('kepala_bagian.dashboard') }}"
                class="sidebar-link {{ request()->routeIs('kepala_bagian.dashboard') ? 'active' : '' }}">
                <span class="sidebar-link-icon"><i class="fa-solid fa-gauge-high"></i></span>
                <span class="sidebar-link-text">Dashboard</span>
                @if(request()->routeIs('kepala_bagian.dashboard'))
                    <span class="sidebar-active-indicator"></span>
                @endif
            </a>

            <a href="{{ route('kepala_bagian.penilaian-atasan.index') }}"
                class="sidebar-link {{ request()->routeIs('kepala_bagian.penilaian-atasan.*') ? 'active' : '' }}">
                <span class="sidebar-link-icon"><i class="fa-solid fa-chart-line"></i></span>
                <span class="sidebar-link-text">Penilaian</span>
            </a>

            <a href="{{ route('kepala_bagian.rekap-data.index') }}"
                class="sidebar-link {{ request()->routeIs('kepala_bagian.rekap-data.*') ? 'active' : '' }}">
                <span class="sidebar-link-icon"><i class="fa-solid fa-table-list"></i></span>
                <span class="sidebar-link-text">Rekap Data</span>
            </a>

        @elseif ($role === 'direktur')

            <a href="{{ route('direktur.dashboard') }}"
                class="sidebar-link {{ request()->routeIs('direktur.dashboard') ? 'active' : '' }}">
                <span class="sidebar-link-icon"><i class="fa-solid fa-gauge-high"></i></span>
                <span class="sidebar-link-text">Dashboard</span>
                @if(request()->routeIs('direktur.dashboard'))
                    <span class="sidebar-active-indicator"></span>
                @endif
            </a>

            <a href="{{ route('direktur.penilaian-kepala-bagian.index') }}"
                class="sidebar-link {{ request()->routeIs('direktur.penilaian-kepala-bagian.*') ? 'active' : '' }}">
                <span class="sidebar-link-icon"><i class="fa-solid fa-chart-line"></i></span>
                <span class="sidebar-link-text">Penilaian</span>
            </a>

            <a href="{{ route('direktur.rekap-data.index') }}"
                class="sidebar-link {{ request()->routeIs('direktur.rekap-data.*') ? 'active' : '' }}">
                <span class="sidebar-link-icon"><i class="fa-solid fa-table-list"></i></span>
                <span class="sidebar-link-text">Rekap Data</span>
            </a>

        @elseif ($role === 'atasan')

            <a href="{{ route('atasan.dashboard') }}"
                class="sidebar-link {{ request()->routeIs('atasan.dashboard') ? 'active' : '' }}">
                <span class="sidebar-link-icon"><i class="fa-solid fa-gauge-high"></i></span>
                <span class="sidebar-link-text">Dashboard</span>
                @if(request()->routeIs('atasan.dashboard'))
                    <span class="sidebar-active-indicator"></span>
                @endif
            </a>

            <a href="{{ route('atasan.pegawai.index') }}"
                class="sidebar-link {{ request()->routeIs('atasan.pegawai.*') ? 'active' : '' }}">
                <span class="sidebar-link-icon"><i class="fa-solid fa-users"></i></span>
                <span class="sidebar-link-text">Pegawai</span>
            </a>

            <a href="{{ route('atasan.absensi.index') }}"
                class="sidebar-link {{ request()->routeIs('atasan.absensi.*') ? 'active' : '' }}">
                <span class="sidebar-link-icon"><i class="fa-solid fa-calendar-check"></i></span>
                <span class="sidebar-link-text">Data Absensi</span>
            </a>

            <a href="{{ route('atasan.laporan.index') }}"
                class="sidebar-link {{ request()->routeIs('atasan.laporan.*') ? 'active' : '' }}">
                <span class="sidebar-link-icon"><i class="fa-solid fa-file-circle-check"></i></span>
                <span class="sidebar-link-text">Approval</span>
            </a>

            <a href="{{ route('atasan.hasil-penilaian.index') }}"
                class="sidebar-link {{ request()->routeIs('atasan.hasil-penilaian.*') ? 'active' : '' }}">
                <span class="sidebar-link-icon"><i class="fa-solid fa-chart-line"></i></span>
                <span class="sidebar-link-text">Hasil Penilaian</span>
            </a>

        @elseif ($role === 'pegawai')

            <a href="{{ route('pegawai.dashboard') }}"
                class="sidebar-link {{ request()->routeIs('pegawai.dashboard') ? 'active' : '' }}">
                <span class="sidebar-link-icon"><i class="fa-solid fa-gauge-high"></i></span>
                <span class="sidebar-link-text">Dashboard Saya</span>
            </a>

            <a href="{{ route('pegawai.laporan-harian.create') }}"
                class="sidebar-link {{ request()->routeIs('pegawai.laporan-harian.*') ? 'active' : '' }}">
                <span class="sidebar-link-icon"><i class="fa-solid fa-file-lines"></i></span>
                <span class="sidebar-link-text">Laporan Harian</span>
            </a>

            <a href="{{ route('pegawai.absensi.index') }}"
                class="sidebar-link {{ request()->routeIs('pegawai.absensi.*') ? 'active' : '' }}">
                <span class="sidebar-link-icon"><i class="fa-solid fa-calendar-check"></i></span>
                <span class="sidebar-link-text">Absensi Saya</span>
            </a>

            <a href="{{ route('pegawai.profile.index') }}"
                class="sidebar-link {{ request()->routeIs('pegawai.profile.*') ? 'active' : '' }}">
                <span class="sidebar-link-icon"><i class="fa-solid fa-user"></i></span>
                <span class="sidebar-link-text">Profil Saya</span>
            </a>

        @endif

    </nav>

</aside>
