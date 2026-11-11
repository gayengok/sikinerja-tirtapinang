@php
    $role = auth()->user()->role ?? null;
@endphp

<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            @if ($role === 'master')
                <img class="sidebar-logo-mark" src="{{ asset('img/logo.png') }}" alt="Logo SIKINERJA">
            @else
                <i class="fa-solid fa-droplet text-info"></i>
            @endif
            <div class="sidebar-brand-text">
                <span class="fw-bold">SIKINERJA</span>
                <small>Tirta Pinang</small>
            </div>
        </div>
        <button type="button" class="btn-close btn-close-white btn-sm" id="sidebarCloseBtn"
            aria-label="Close"></button>
    </div>

    <nav class="sidebar-nav">
        @if ($role === 'master')
            <a href="{{ route('master.dashboard') }}" title="Dashboard"
                class="sidebar-link {{ request()->routeIs('master.dashboard') ? 'active' : '' }}">
                <i class="fa-solid fa-gauge"></i> <span>Dashboard</span>
            </a>

            <div class="sidebar-section-title">Pengelolaan Data</div>

            <a href="{{ route('master.divisi.index') }}" title="Divisi"
                class="sidebar-link {{ request()->routeIs('master.divisi.*') ? 'active' : '' }}">
                <i class="fa-solid fa-sitemap"></i> <span>Divisi</span>
            </a>
            <a href="{{ route('master.jabatan.index') }}" title="Jabatan"
                class="sidebar-link {{ request()->routeIs('master.jabatan.*') ? 'active' : '' }}">
                <i class="fa-solid fa-id-badge"></i> <span>Jabatan</span>
            </a>
            <a href="{{ route('master.pegawai.index') }}" title="Pegawai"
                class="sidebar-link {{ request()->routeIs('master.pegawai.*') ? 'active' : '' }}">
                <i class="fa-solid fa-users"></i> <span>Pegawai</span>
            </a>
            <a href="{{ route('master.periode-penilaian.index') }}" title="Periode Penilaian"
                class="sidebar-link {{ request()->routeIs('master.periode-penilaian.*') ? 'active' : '' }}">
                <i class="fa-solid fa-calendar-days"></i> <span>Periode Penilaian</span>
            </a>
            <a href="{{ route('master.master-kpi.index') }}" title="Master KPI"
                class="sidebar-link {{ request()->routeIs('master.master-kpi.*') ? 'active' : '' }}">
                <i class="fa-solid fa-bullseye"></i> <span>Master KPI</span>
            </a>
            <a href="{{ route('master.master-kompetensi.index') }}" title="Master Kompetensi"
                class="sidebar-link {{ request()->routeIs('master.master-kompetensi.*') ? 'active' : '' }}">
                <i class="fa-solid fa-star"></i> <span>Master Kompetensi</span>
            </a>
            <a href="{{ route('master.template-laporan.index') }}" title="Template Laporan"
                class="sidebar-link {{ request()->routeIs('master.template-laporan.*') ? 'active' : '' }}">
                <i class="fa-solid fa-file-lines"></i> <span>Template Laporan</span>
            </a>
            <a href="{{ route('master.jam-kerja.index') }}" title="Jam Kerja"
                class="sidebar-link {{ request()->routeIs('master.jam-kerja.*') ? 'active' : '' }}">
                <i class="fa-solid fa-clock"></i> <span>Jam Kerja</span>
            </a>
            <a href="{{ route('master.jenis-potongan.index') }}" title="Jenis Potongan"
                class="sidebar-link {{ request()->routeIs('master.jenis-potongan.*') ? 'active' : '' }}">
                <i class="fa-solid fa-scissors"></i> <span>Jenis Potongan</span>
            </a>
        @elseif ($role === 'kepala_bagian')
            <a href="{{ route('kepala_bagian.dashboard') }}" title="Dashboard"
                class="sidebar-link {{ request()->routeIs('kepala_bagian.dashboard') ? 'active' : '' }}">
                <i class="fa-solid fa-gauge"></i> <span>Dashboard</span>
            </a>
            <a href="{{ route('kepala_bagian.penilaian-atasan.index') }}" title="Penilaian Atasan"
                class="sidebar-link {{ request()->routeIs('kepala_bagian.penilaian-atasan.*') ? 'active' : '' }}">
                <i class="fa-solid fa-chart-line"></i> <span>Penilaian Atasan</span>
            </a>
        @elseif ($role === 'atasan')
            <a href="{{ route('atasan.dashboard') }}"
                title="Dashboard"
                class="sidebar-link {{ request()->routeIs('atasan.dashboard') ? 'active' : '' }}">
                <i class="fa-solid fa-gauge"></i> <span>Dashboard</span>
            </a>
            <a href="{{ route('atasan.pegawai.index') }}"
                title="Pegawai Bawahan"
                class="sidebar-link {{ request()->routeIs('atasan.pegawai.*') ? 'active' : '' }}">
                <i class="fa-solid fa-users"></i> <span>Pegawai Bawahan</span>
            </a>
            <a href="{{ route('atasan.absensi.index') }}"
                title="Data Absensi"
                class="sidebar-link {{ request()->routeIs('atasan.absensi.*') ? 'active' : '' }}">
                <i class="fa-solid fa-calendar-check"></i> <span>Data Absensi</span>
            </a>
            <a href="{{ route('atasan.laporan.index') }}"
                title="Approval Laporan"
                class="sidebar-link {{ request()->routeIs('atasan.laporan.*') ? 'active' : '' }}">
                <i class="fa-solid fa-file-circle-check"></i> <span>Approval Laporan</span>
            </a>
            <a href="{{ route('atasan.hasil-penilaian.index') }}"
                title="Hasil Penilaian"
                class="sidebar-link {{ request()->routeIs('atasan.hasil-penilaian.*') ? 'active' : '' }}">
                <i class="fa-solid fa-chart-line"></i> <span>Hasil Penilaian</span>
            </a>
        @elseif ($role === 'pegawai')
            <a href="{{ route('pegawai.dashboard') }}"
                class="sidebar-link {{ request()->routeIs('pegawai.dashboard') ? 'active' : '' }}">
                <i class="fa-solid fa-gauge"></i> <span>Dashboard Saya</span>
            </a>
            <a href="{{ route('pegawai.laporan-harian.index') }}"
                class="sidebar-link {{ request()->routeIs('pegawai.laporan-harian.*') ? 'active' : '' }}">
                <i class="fa-solid fa-file-lines"></i> <span>Laporan Harian</span>
            </a>
            <a href="{{ route('pegawai.absensi.index') }}"
                class="sidebar-link {{ request()->routeIs('pegawai.absensi.*') ? 'active' : '' }}">
                <i class="fa-solid fa-calendar-check"></i> <span>Absensi Saya</span>
            </a>
            <a href="{{ route('pegawai.profile.index') }}"
                class="sidebar-link {{ request()->routeIs('pegawai.profile.*') ? 'active' : '' }}">
                <i class="fa-solid fa-user"></i> <span>Profil Saya</span>
            </a>
        @endif
    </nav>
</aside>
