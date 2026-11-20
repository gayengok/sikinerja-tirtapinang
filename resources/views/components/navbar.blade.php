
@php
    $user = auth()->user();
    $role = $user?->role;

    $labelRole = match ($role) {
        'master' => 'Admin',
        'kepala_bagian' => 'Kepala Bagian',
        'direktur' => 'Direktur',
        'atasan' => 'Atasan',
        'pegawai' => 'Pegawai',
        default => 'Pengguna',
    };

    $homeRoute = match ($role) {
        'master' => 'master.dashboard',
        'kepala_bagian' => 'kepala_bagian.dashboard',
        'direktur' => 'direktur.dashboard',
        'atasan' => 'atasan.dashboard',
        'pegawai' => 'pegawai.dashboard',
        default => null,
    };

    $hasSidebar = in_array($role, [
        'master',
        'atasan',
        'kepala_bagian',
        'direktur',
    ], true);

    $profileRoutes = match ($role) {
        'pegawai' => [
            'pegawai.profil.index',
            'pegawai.profile.index',
            'pegawai.profil',
            'profile.edit',
        ],
        'master', 'atasan', 'kepala_bagian', 'direktur' => [
            'profile.index',
        ],
        default => [],
    };

    $profileRoute = null;

    foreach ($profileRoutes as $routeName) {
        if (Route::has($routeName)) {
            $profileRoute = $routeName;
            break;
        }
    }

    $profileActive = $role === 'pegawai'
        ? request()->routeIs(
            'pegawai.profil.*',
            'pegawai.profile.*',
            'pegawai.profil',
            'profile.edit'
        )
        : request()->routeIs('profile.index');

    $homeUrl = $homeRoute && Route::has($homeRoute)
        ? route($homeRoute)
        : url('/');

    $logoutUrl = Route::has('logout')
        ? route('logout')
        : null;
@endphp

<nav class="navbar navbar-sikinerja sikinerja-capsule-navbar"
     aria-label="Navigasi atas">

    {{-- BRAND --}}
    <div class="sikinerja-capsule-start">

        @if ($hasSidebar)
            <button
                type="button"
                class="sikinerja-brand-toggle"
                id="sikinerjaBrandToggle"
                aria-label="Tutup sidebar"
                aria-expanded="true"
                aria-controls="sidebar"
                title="Tutup sidebar"
            >
                <img src="{{ asset('img/logo.png') }}" alt="SIKINERJA">
            </button>
        @else
            <a href="{{ $homeUrl }}"
               class="sikinerja-brand-toggle"
               aria-label="Beranda SIKINERJA">
                <img src="{{ asset('img/logo.png') }}" alt="SIKINERJA">
            </a>
        @endif

        <a href="{{ $homeUrl }}" class="sikinerja-capsule-brand-text">
            <strong>SIKINERJA</strong>
            <small>PDAM TIRTA PINANG</small>
        </a>

        @if ($hasSidebar)
            <button
                type="button"
                class="sidebar-toggle-button sidebar-toggle-mobile"
                id="sidebarToggleMobile"
                aria-label="Buka navigasi"
                aria-expanded="false"
                aria-controls="sidebar"
                title="Buka navigasi"
            >
                <i class="fa-solid fa-bars"></i>
            </button>
        @endif
    </div>

    {{-- ACTIONS --}}
    <div class="sikinerja-capsule-end">

        <button
            type="button"
            class="sikinerja-action-button sikinerja-notification-trigger"
            id="navNotifBtn"
            aria-label="Buka notifikasi"
            aria-haspopup="dialog"
            aria-controls="sikinerjaNotificationModal"
            title="Notifikasi"
        >
            <i class="fa-regular fa-bell"></i>
            <span class="sikinerja-notification-dot"></span>
        </button>

        {{-- PROFILE --}}
        <div class="navbar-user dropdown">

            <button
                type="button"
                class="sikinerja-profile-trigger {{ $profileActive ? 'is-active' : '' }}"
                id="navbarProfileTrigger"
                data-bs-toggle="dropdown"
                data-bs-auto-close="outside"
                aria-expanded="false"
                aria-label="Menu profil"
            >
                <span class="user-avatar">
                    {{ mb_strtoupper(mb_substr($user?->name ?? '?', 0, 1)) }}
                </span>
                <i class="fa-solid fa-chevron-down profile-chevron"></i>
            </button>

            <div
                class="dropdown-menu dropdown-menu-end sikinerja-profile-dropdown"
                aria-labelledby="navbarProfileTrigger"
            >
                <div class="sikinerja-profile-header">
                    <span class="sikinerja-profile-avatar">
                        {{ mb_strtoupper(mb_substr($user?->name ?? '?', 0, 1)) }}
                    </span>

                    <div class="sikinerja-profile-info">
                        <strong>{{ $user?->name ?? 'Pengguna' }}</strong>
                        <span>{{ $labelRole }}</span>
                        @if ($user?->email)
                            <small>{{ $user->email }}</small>
                        @endif
                    </div>
                </div>

                <div class="sikinerja-profile-divider"></div>

                @if ($profileRoute)
                    <a href="{{ route($profileRoute) }}"
                       class="sikinerja-profile-item {{ $profileActive ? 'is-active' : '' }}">
                        <span class="sikinerja-profile-item-icon">
                            <i class="fa-regular fa-user"></i>
                        </span>
                        <span>Lihat Profil</span>
                        <i class="fa-solid fa-chevron-right ms-auto"></i>
                    </a>
                @endif

                @if ($logoutUrl)
                    <form method="POST" action="{{ $logoutUrl }}">
                        @csrf
                        <button type="submit"
                                class="sikinerja-profile-item is-logout">
                            <span class="sikinerja-profile-item-icon">
                                <i class="fa-solid fa-arrow-right-from-bracket"></i>
                            </span>
                            <span>Keluar</span>
                            <i class="fa-solid fa-chevron-right ms-auto"></i>
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</nav>
