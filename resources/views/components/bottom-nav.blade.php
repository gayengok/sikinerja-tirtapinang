@php
    $role = auth()->user()->role ?? null;
@endphp

@if ($role === 'pegawai')
    <div class="mobile-bottom-nav" id="mobileBottomNav">
        <div class="container-fluid px-2">
            <ul class="nav nav-justified align-items-center">
                <li class="nav-item">
                    <a href="{{ route('pegawai.dashboard') }}"
                        class="mobile-nav-link {{ request()->routeIs('pegawai.dashboard') ? 'active' : '' }}">
                        <div class="nav-icon-wrapper">
                            <i class="fa-solid fa-house"></i>
                        </div>
                        <span>Beranda</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('pegawai.laporan-harian.index') }}"
                        class="mobile-nav-link {{ request()->routeIs('pegawai.laporan-harian.*') ? 'active' : '' }}">
                        <div class="nav-icon-wrapper">
                            <i class="fa-solid fa-file-lines"></i>
                        </div>
                        <span>Laporan</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('pegawai.absensi.index') }}"
                        class="mobile-nav-link {{ request()->routeIs('pegawai.absensi.*') ? 'active' : '' }}">
                        <div class="nav-icon-wrapper">
                            <i class="fa-solid fa-calendar-check"></i>
                        </div>
                        <span>Absensi</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('pegawai.profile.index') }}"
                        class="mobile-nav-link {{ request()->routeIs('pegawai.profile.*') ? 'active' : '' }}">
                        <div class="nav-icon-wrapper">
                            <i class="fa-solid fa-user"></i>
                        </div>
                        <span>Profil</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    {{-- BAGIAN NAV ATASAN --}}
@elseif ($role === 'kepala_bagian')
    <div class="mobile-bottom-nav" id="mobileBottomNav">
        <div class="container-fluid px-2">
            <ul class="nav nav-justified align-items-center">
                <li class="nav-item">
                    <a href="{{ route('kepala_bagian.dashboard') }}"
                        class="mobile-nav-link {{ request()->routeIs('kepala_bagian.dashboard') ? 'active' : '' }}">
                        <div class="nav-icon-wrapper"><i class="fa-solid fa-house"></i></div>
                        <span>Beranda</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('kepala_bagian.penilaian-atasan.index') }}"
                        class="mobile-nav-link {{ request()->routeIs('kepala_bagian.penilaian-atasan.*') ? 'active' : '' }}">
                        <div class="nav-icon-wrapper"><i class="fa-solid fa-chart-line"></i></div>
                        <span>Penilaian</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    @elseif ($role === 'direktur')
        <div class="mobile-bottom-nav" id="mobileBottomNav">
            <div class="container-fluid px-2">
                <ul class="nav nav-justified align-items-center">
                    <li class="nav-item">
                        <a href="{{ route('direktur.dashboard') }}"
                            class="mobile-nav-link {{ request()->routeIs('direktur.dashboard') ? 'active' : '' }}">
                            <div class="nav-icon-wrapper"><i class="fa-solid fa-house"></i></div>
                            <span>Beranda</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('direktur.penilaian-kepala-bagian.index') }}"
                            class="mobile-nav-link {{ request()->routeIs('direktur.penilaian-kepala-bagian.*') ? 'active' : '' }}">
                            <div class="nav-icon-wrapper"><i class="fa-solid fa-chart-line"></i></div>
                            <span>Penilaian</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

    {{-- BAGIAN NAV ATASAN --}}
@elseif ($role === 'atasan')
    <div class="mobile-bottom-nav" id="mobileBottomNav">
        <div class="container-fluid px-2">
            <ul class="nav nav-justified align-items-center">
                <li class="nav-item">
                    <a href="{{ route('atasan.dashboard') }}"
                        class="mobile-nav-link {{ request()->routeIs('atasan.dashboard') ? 'active' : '' }}">
                        <div class="nav-icon-wrapper">
                            <i class="fa-solid fa-house"></i>
                        </div>
                        <span>Beranda</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('atasan.laporan.index') }}"
                        class="mobile-nav-link {{ request()->routeIs('atasan.laporan.*') ? 'active' : '' }}">
                        <div class="nav-icon-wrapper">
                            <i class="fa-solid fa-clipboard-check"></i>
                        </div>
                        <span>Approval</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('atasan.absensi.index') }}"
                        class="mobile-nav-link {{ request()->routeIs('atasan.absensi.*') ? 'active' : '' }}">
                        <div class="nav-icon-wrapper">
                            <i class="fa-solid fa-calendar-check"></i>
                        </div>
                        <span>Absen</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('atasan.pegawai.index') }}"
                        class="mobile-nav-link {{ request()->routeIs('atasan.pegawai.*') ? 'active' : '' }}">
                        <div class="nav-icon-wrapper">
                            <i class="fa-solid fa-users"></i>
                        </div>
                        <span>Bawahan</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('atasan.hasil-penilaian.index') }}"
                        class="mobile-nav-link {{ request()->routeIs('atasan.hasil-penilaian.*') ? 'active' : '' }}">
                        <div class="nav-icon-wrapper">
                            <i class="fa-solid fa-chart-pie"></i>
                        </div>
                        <span>Hasil</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>


    {{-- BAGIAN NAV MASTER --}}
@elseif ($role === 'master')
    <div class="mobile-bottom-nav" id="mobileBottomNav">
        <div class="container-fluid px-2">
            <ul class="nav nav-justified align-items-center">
                <li class="nav-item">
                    <a href="{{ route('master.dashboard') }}"
                        class="mobile-nav-link {{ request()->routeIs('master.dashboard') ? 'active' : '' }}">
                        <div class="nav-icon-wrapper">
                            <i class="fa-solid fa-house"></i>
                        </div>
                        <span>Beranda</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('master.pegawai.index') }}"
                        class="mobile-nav-link {{ request()->routeIs('master.pegawai.*') ? 'active' : '' }}">
                        <div class="nav-icon-wrapper">
                            <i class="fa-solid fa-users"></i>
                        </div>
                        <span>Pegawai</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('master.divisi.index') }}"
                        class="mobile-nav-link {{ request()->routeIs('master.divisi.*') ? 'active' : '' }}">
                        <div class="nav-icon-wrapper">
                            <i class="fa-solid fa-sitemap"></i>
                        </div>
                        <span>Divisi</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('master.master-kpi.index') }}"
                        class="mobile-nav-link {{ request()->routeIs('master.master-kpi.*') ? 'active' : '' }}">
                        <div class="nav-icon-wrapper">
                            <i class="fa-solid fa-bullseye"></i>
                        </div>
                        <span>KPI</span>
                    </a>
                </li>
                <li class="nav-item">
                    <button type="button" class="mobile-nav-link border-0 bg-transparent w-100" id="bottomNavMenuBtn"
                        aria-expanded="false" aria-controls="masterMobileMenu">
                        <div class="nav-icon-wrapper">
                            <i class="fa-solid fa-bars"></i>
                        </div>
                        <span>Menu</span>
                    </button>
                </li>
            </ul>
        </div>
    </div>
    <div class="master-mobile-menu" id="masterMobileMenu" aria-hidden="true">
        <div class="master-mobile-menu-header">
            <strong>Menu Pengelolaan Data</strong>
            <button type="button" id="masterMobileMenuClose" aria-label="Tutup"><i
                    class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="master-mobile-menu-links">
            <a href="{{ route('master.periode-penilaian.index') }}"><i class="fa-regular fa-calendar"></i> Periode
                Penilaian</a>
            <a href="{{ route('master.master-kompetensi.index') }}"><i class="fa-solid fa-star"></i> Master
                Kompetensi</a>
            <a href="{{ route('master.template-laporan.index') }}"><i class="fa-solid fa-file-lines"></i> Template
                Laporan</a>
            <a href="{{ route('master.jam-kerja.index') }}"><i class="fa-regular fa-clock"></i> Jam Kerja</a>
            <a href="{{ route('master.jenis-potongan.index') }}"><i class="fa-solid fa-scissors"></i> Jenis
                Potongan</a>
            <a href="#">
                <i class="fa-solid fa-chart-line"></i> Hasil Laporan Kinerja
            </a>
        </div>
    </div>
    <script>
        (function() {
            const button = document.getElementById('bottomNavMenuBtn');
            const menu = document.getElementById('masterMobileMenu');
            const close = document.getElementById('masterMobileMenuClose');
            if (!button || !menu) return;
            const setMenu = (open) => {
                menu.classList.toggle('show', open);
                menu.setAttribute('aria-hidden', open ? 'false' : 'true');
                button.setAttribute('aria-expanded', open ? 'true' : 'false');
            };
            button.addEventListener('click', () => setMenu(!menu.classList.contains('show')));
            close?.addEventListener('click', () => setMenu(false));
        })();
    </script>
@endif
