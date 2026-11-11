@php
    $role = auth()->user()->role ?? null;

    $labelRole = match ($role) {
        'master' => 'Admin',
        'kepala_bagian' => 'Kepala Bagian',
        'atasan' => 'Atasan',
        'pegawai' => 'Pegawai',
        default => '-',
    };

    $profileRoute = match ($role) {
        'pegawai' => 'pegawai.profile.index',
        default => null,
    };

@endphp

<nav class="navbar navbar-sikinerja navbar-expand {{ request()->routeIs('atasan.absensi.*', 'atasan.absensi-saya.*', 'kepala_bagian.absensi.*') ? 'attendance-wide-navbar' : '' }}">
    <div class="navbar-start">
        <!-- Brand Logo & Title -->
        <a href="#" class="navbar-brand">
            <div class="brand-badge-icon">
                <img src="{{ asset('img/logo.png') }}" alt="Logo SIKINERJA">
            </div>
            <div class="brand-caption">
                <span class="brand-caption-title">SIKINERJA</span>
                <span class="brand-caption-subtitle">TIRTA PINANG</span>
            </div>
        </a>
        @if ($role === 'master' || $role === 'atasan' || $role === 'kepala_bagian')
            <button type="button" class="sidebar-toggle-button" id="sidebarToggleDesktop" aria-label="Ciutkan navigasi"
                aria-expanded="true" title="Ciutkan navigasi">
                <i class="fa-solid fa-bars"></i>
            </button>
        @endif
    </div>

    <style>
        .brand-badge-icon {
            width: 82px;
            height: 34px;
            background: rgba(255, 255, 255, .92);
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 3px;
            box-shadow: 0 2px 6px rgba(2, 132, 199, .18);
        }

        .brand-badge-icon img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: 7px;
        }

        .brand-caption {
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-width: 0;
            max-width: 112px;
            overflow: hidden;
            line-height: 1.05;
            transition: max-width .22s ease, opacity .18s ease, transform .22s ease;
        }

        .brand-caption-title {
            color: #026aa7;
            font-size: .95rem;
            font-weight: 700;
            letter-spacing: .01em;
        }

        .brand-caption-subtitle {
            margin-top: .18rem;
            color: #64748b;
            font-size: .55rem;
            font-weight: 600;
            letter-spacing: .12em;
        }

        .sidebar-toggle-button {
            display: none;
            place-items: center;
            width: 36px;
            height: 36px;
            padding: 0;
            color: #526174;
            background: transparent;
            border: 1px solid transparent;
            border-radius: 6px;
            cursor: pointer;
        }

        .sidebar-toggle-button:hover,
        .sidebar-toggle-button:focus-visible {
            color: #1769d2;
            background: #f0f5fb;
            border-color: #e2e8ef;
            outline: none;
        }

        @media (min-width: 768px) {
            .sidebar-toggle-button {
                display: grid;
            }
        }

        @media (min-width: 992px) {
            body.desktop-atasan-attendance.role-atasan .mobile-app-shell {
                width: 100% !important;
                max-width: none !important;
                margin: 0 !important;
            }

            body.desktop-atasan-attendance.role-atasan .navbar-sikinerja.attendance-wide-navbar {
                position: fixed !important;
                top: 0 !important;
                right: 0 !important;
                left: 0 !important;
                width: 100% !important;
                max-width: none !important;
                margin: 0 !important;
                padding-right: clamp(1rem, 2vw, 2rem) !important;
                padding-left: clamp(1rem, 2vw, 2rem) !important;
                transform: none !important;
                box-sizing: border-box !important;
                justify-content: space-between !important;
            }

            body.desktop-atasan-attendance.role-atasan .attendance-wide-navbar .navbar-start {
                flex: 0 0 auto !important;
                justify-content: flex-start !important;
                gap: 0.75rem !important;
            }

            body.desktop-atasan-attendance.role-atasan .attendance-wide-navbar .navbar-brand {
                flex: 0 0 auto !important;
                gap: 0.65rem !important;
            }

            body.desktop-atasan-attendance.role-atasan .attendance-wide-navbar .brand-badge-icon {
                width: 100px !important;
                height: 46px !important;
                flex: 0 0 100px !important;
                padding: 2px !important;
                overflow: hidden;
            }

            body.desktop-atasan-attendance.role-atasan .attendance-wide-navbar .brand-caption {
                width: auto !important;
                max-width: 140px !important;
                opacity: 1 !important;
                transform: none !important;
            }

            body.desktop-atasan-attendance.role-atasan .attendance-wide-navbar .navbar-end {
                flex: 0 0 auto !important;
                margin-left: auto !important;
            }
        }

        @media (min-width: 768px) {

            body.role-master .navbar-start,
            body.role-master .navbar-brand,
            body.role-master .brand-badge-icon {
                transition: gap .22s ease, width .22s ease, flex-basis .22s ease;
            }

            body.desktop-atasan-attendance .navbar-start,
            body.desktop-atasan-attendance .navbar-brand,
            body.desktop-atasan-attendance .brand-badge-icon,
            body.role-atasan .navbar-start,
            body.role-atasan .navbar-brand,
            body.role-atasan .brand-badge-icon {
                transition: gap .22s ease, width .22s ease, flex-basis .22s ease;
            }

            body.role-master.sidebar-collapsed .navbar-start {
                gap: .2rem;
            }

            body.desktop-atasan-attendance.sidebar-collapsed .navbar-start {
                gap: .2rem;
            }

            body.role-atasan.sidebar-collapsed .navbar-start {
                gap: .2rem;
            }

            body.role-master.sidebar-collapsed .navbar-brand {
                gap: 0;
            }

            body.desktop-atasan-attendance.sidebar-collapsed .navbar-brand {
                gap: 0;
            }

            body.role-atasan.sidebar-collapsed .navbar-brand {
                gap: 0;
            }

            body.role-master.sidebar-collapsed .brand-caption {
                max-width: 0;
                opacity: 0;
                transform: translateX(-8px);
            }

            body.desktop-atasan-attendance.sidebar-collapsed .brand-caption {
                max-width: 0;
                opacity: 0;
                transform: translateX(-8px);
            }

            body.role-atasan.sidebar-collapsed .brand-caption {
                max-width: 0;
                opacity: 0;
                transform: translateX(-8px);
            }

            body.role-master.sidebar-collapsed .brand-badge-icon {
                width: 34px;
                height: 30px;
                flex-basis: 34px;
                padding: 2px;
            }

            body.desktop-atasan-attendance.sidebar-collapsed .brand-badge-icon {
                width: 34px;
                height: 30px;
                flex-basis: 34px;
                padding: 2px;
            }

            body.role-atasan.sidebar-collapsed .brand-badge-icon {
                width: 34px;
                height: 30px;
                flex-basis: 34px;
                padding: 2px;
            }

            body.role-master.sidebar-collapsed .sidebar-toggle-button {
                width: 32px;
                height: 32px;
            }

            body.desktop-atasan-attendance.sidebar-collapsed .sidebar-toggle-button {
                width: 32px;
                height: 32px;
            }

            body.role-atasan.sidebar-collapsed .sidebar-toggle-button {
                width: 32px;
                height: 32px;
            }
        }
    </style>

    <!-- Right Controls: Notification & User Menu -->
    <div class="navbar-end">
        <!-- Notification Button with Red Indicator -->
        <div class="dropdown">
            <button type="button" class="btn btn-icon-circle" id="navNotifBtn" title="Notifikasi"
                data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa-regular fa-bell"></i>
                <span class="notif-dot-badge"></span>
            </button>

            <!-- Notification Dropdown Menu -->
            <ul class="dropdown-menu dropdown-menu-end shadow-sm p-2"
                style="width: 280px; border-radius: 14px; border: 1px solid #e2e8f0;">
                <li class="px-2 py-1 d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                    <span class="fw-bold small">Aktivitas & Notifikasi</span>
                    <span class="badge bg-danger rounded-pill" style="font-size: 0.65rem;">3 Baru</span>
                </li>
                <li>
                    <a class="dropdown-item rounded-3 py-2 px-2 small text-wrap d-flex gap-2"
                        href="{{ route('pegawai.laporan-harian.index') }}">
                        <i class="fa-solid fa-circle-check text-success mt-1"></i>
                        <div>
                            <div class="fw-semibold">Laporan harian telah diverifikasi</div>
                            <div class="text-muted" style="font-size: 0.72rem;">3 jam lalu</div>
                        </div>
                    </a>
                </li>
            </ul>
        </div>

        <!-- User Profile -->
        <div class="navbar-user dropdown">
            <button type="button" class="navbar-user-toggle" data-bs-toggle="dropdown" aria-expanded="false"
                aria-label="Buka menu profil">
                <span class="user-avatar">
                    {{ strtoupper(substr(auth()->user()->name ?? '?', 0, 1)) }}
                </span>
            </button>

            <div class="dropdown-menu dropdown-menu-end shadow-sm p-2"
                style="width: 220px; border-radius: 14px; border: 1px solid #e2e8f0;">
                <div class="px-2 py-2 border-bottom mb-1">
                    <div class="fw-semibold text-dark text-truncate">{{ auth()->user()->name }}</div>
                    <div class="text-muted small">{{ $labelRole }}</div>
                </div>
                @if ($profileRoute)
                    <a class="dropdown-item rounded-3 py-2 px-2 small" href="{{ route($profileRoute) }}">
                        <i class="fa-solid fa-user me-2"></i> Profil
                    </a>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="dropdown-item rounded-3 py-2 px-2 small text-danger">
                        <i class="fa-solid fa-right-from-bracket me-2"></i> Keluar
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>
