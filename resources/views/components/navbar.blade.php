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
    $profileRoute = $role === 'pegawai' ? 'pegawai.profile.index' : null;
@endphp
<nav class="navbar navbar-sikinerja navbar-expand" aria-label="Navigasi atas">
    <div class="navbar-start">
        <a href="{{ $homeRoute && Route::has($homeRoute) ? route($homeRoute) : url('/') }}" class="navbar-brand" aria-label="Beranda SIKINERJA">
            <span class="brand-badge-icon"><img src="{{ asset('img/logo.png') }}" alt="Logo SIKINERJA"></span>
            <span class="brand-caption">
                <span class="brand-caption-title">SIKINERJA</span>
                <span class="brand-caption-subtitle">TIRTA PINANG</span>
            </span>
        </a>
        @if (in_array($role, ['master', 'atasan', 'kepala_bagian', 'direktur'], true))
            <button type="button" class="sidebar-toggle-button sidebar-toggle-desktop" id="sidebarToggleDesktop"
                aria-label="Ciutkan navigasi" aria-expanded="true" aria-controls="sidebar" title="Ciutkan navigasi">
                <i class="fa-solid fa-bars"></i>
            </button>
            <button type="button" class="sidebar-toggle-button sidebar-toggle-mobile" id="sidebarToggleMobile"
                aria-label="Buka navigasi" aria-expanded="false" aria-controls="sidebar" title="Buka navigasi">
                <i class="fa-solid fa-bars"></i>
            </button>
        @endif
    </div>
    <div class="navbar-end">
        <div class="dropdown">
            <button type="button" class="btn btn-icon-circle" id="navNotifBtn" data-bs-toggle="dropdown"
                aria-expanded="false" aria-label="Buka notifikasi" title="Notifikasi">
                <i class="fa-regular fa-bell"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-end notification-menu" aria-labelledby="navNotifBtn">
                <div class="px-2 py-2 border-bottom fw-bold small">Aktivitas &amp; Notifikasi</div>
                <div class="notification-empty">
                    <i class="fa-regular fa-bell-slash"></i>
                    <strong class="d-block text-dark mb-1">Belum ada notifikasi</strong>
                    <span>Notifikasi akan tampil di sini saat fitur tersedia.</span>
                </div>
            </div>
        </div>
        <div class="navbar-user dropdown">
            <button type="button" class="navbar-user-toggle" data-bs-toggle="dropdown" aria-expanded="false"
                aria-label="Buka menu profil">
                <span class="user-avatar">{{ mb_strtoupper(mb_substr($user?->name ?? '?', 0, 1)) }}</span>
            </button>
            <div class="dropdown-menu dropdown-menu-end shadow-sm p-2" style="width:220px;border-radius:14px;border:1px solid #e2e8f0">
                <div class="px-2 py-2 border-bottom mb-1">
                    <div class="fw-semibold text-dark text-truncate">{{ $user?->name ?? 'Pengguna' }}</div>
                    <div class="text-muted small">{{ $labelRole }}</div>
                </div>
                @if ($profileRoute && Route::has($profileRoute))
                    <a class="dropdown-item rounded-3 py-2 px-2 small" href="{{ route($profileRoute) }}">
                        <i class="fa-solid fa-user me-2"></i>Profil
                    </a>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="dropdown-item rounded-3 py-2 px-2 small text-danger">
                        <i class="fa-solid fa-right-from-bracket me-2"></i>Keluar
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>
