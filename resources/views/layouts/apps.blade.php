
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="theme-color" content="#0875ad">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">

        <title>@yield('title', 'Dashboard') - {{ config('app.name', 'SIKINERJA') }}</title>

        <link rel="icon" type="image/png" href="{{ asset('img/logo.png') }}">

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

        <link href="{{ asset('css/pegawai/dashboard.css') }}" rel="stylesheet">
        <link href="{{ asset('css/pegawai/navigation.css') }}" rel="stylesheet">
        <link href="{{ asset('css/pegawai/theme.css') }}" rel="stylesheet">

        @stack('styles')
    </head>

    @php
        $user = auth()->user();

        $navigation = [
            [
                'label' => 'Beranda',
                'icon' => 'fa-solid fa-house',
                'routes' => ['pegawai.dashboard'],
                'active' => request()->routeIs('pegawai.dashboard'),
            ],
            [
                'label' => 'Laporan',
                'icon' => 'fa-solid fa-file-lines',
                'routes' => [
                    'pegawai.laporan-harian.create',
                ],
                'active' => request()->routeIs(
                    'pegawai.laporan.*',
                    'pegawai.laporan-harian.*',
                    'pegawai.laporan_harian.*'
                ),
            ],
            [
                'label' => 'Absensi',
                'icon' => 'fa-solid fa-calendar-check',
                'routes' => [
                    'pegawai.absensi.index',
                    'pegawai.absensi',
                ],
                'active' => request()->routeIs('pegawai.absensi.*', 'pegawai.absensi'),
            ],
        ];

        foreach ($navigation as &$item) {
            $item['url'] = null;

            foreach ($item['routes'] as $routeName) {
                if (\Illuminate\Support\Facades\Route::has($routeName)) {
                    $item['url'] = route($routeName);
                    break;
                }
            }
        }

        unset($item);

        $profileRoutes = [
            'pegawai.profil.index',
            'pegawai.profile.index',
            'profile.edit',
            'pegawai.profil',
        ];

        $profileUrl = null;

        foreach ($profileRoutes as $routeName) {
            if (\Illuminate\Support\Facades\Route::has($routeName)) {
                $profileUrl = route($routeName);
                break;
            }
        }

        $logoutUrl = \Illuminate\Support\Facades\Route::has('logout')
            ? route('logout')
            : null;

        $profileActive = request()->routeIs(
            'pegawai.profil.*',
            'pegawai.profile.*',
            'pegawai.profil',
            'profile.*'
        );
    @endphp

    <body class="pegawai-app-body {{ request()->routeIs('pegawai.laporan-harian.create') ? 'role-pegawai' : '' }}">
        <div class="pegawai-app-layout" id="pegawaiAppLayout">

            <nav class="pegawai-navigation" aria-label="Navigasi utama">
                <div class="pegawai-navigation-brand">
                    <img
                        src="{{ asset('img/logo.png') }}"
                        alt="Logo PDAM Tirta Pinang"
                        class="pegawai-navigation-logo"
                    >

                    <div class="pegawai-navigation-brand-text">
                        <strong>SIKINERJA</strong>
                        <small>PDAM Tirta Pinang</small>
                    </div>
                </div>

                <div class="pegawai-navigation-links">
                    @foreach ($navigation as $item)
                        @if ($item['url'])
                            <a
                                href="{{ $item['url'] }}"
                                class="pegawai-navigation-link {{ $item['active'] ? 'is-active' : '' }}"
                                @if ($item['active']) aria-current="page" @endif
                            >
                                <i class="{{ $item['icon'] }}"></i>
                                <span>{{ $item['label'] }}</span>
                            </a>
                        @else
                            <span
                                class="pegawai-navigation-link is-unavailable"
                                aria-disabled="true"
                                title="Route {{ $item['label'] }} belum ditemukan"
                            >
                                <i class="{{ $item['icon'] }}"></i>
                                <span>{{ $item['label'] }}</span>
                            </span>
                        @endif
                    @endforeach

                    <div class="pegawai-profile-menu" id="pegawaiProfileMenu">
                        <button
                            type="button"
                            class="pegawai-navigation-link pegawai-profile-trigger {{ $profileActive ? 'is-active' : '' }}"
                            id="pegawaiProfileTrigger"
                            aria-expanded="false"
                            aria-controls="pegawaiProfileDropdown"
                            aria-haspopup="true"
                        >
                            <i class="fa-solid fa-user"></i>
                            <span>Profil</span>
                        </button>

                        <div
                            class="pegawai-profile-dropdown"
                            id="pegawaiProfileDropdown"
                            hidden
                        >
                            <div class="pegawai-profile-dropdown-header">
                                <span class="pegawai-profile-avatar">
                                    {{ mb_strtoupper(mb_substr($user?->name ?? 'P', 0, 1)) }}
                                </span>

                                <div>
                                    <strong>{{ $user?->name ?? 'Pegawai' }}</strong>
                                    <small>{{ $user?->email ?? 'Akun Pegawai' }}</small>
                                </div>
                            </div>

                            <div class="pegawai-profile-dropdown-divider"></div>

                            @if ($profileUrl)
                                <a href="{{ $profileUrl }}" class="pegawai-profile-dropdown-item">
                                    <i class="fa-regular fa-user"></i>
                                    <span>Lihat Profil</span>
                                    <i class="fa-solid fa-chevron-right"></i>
                                </a>
                            @else
                                <span class="pegawai-profile-dropdown-item is-unavailable" aria-disabled="true">
                                    <i class="fa-regular fa-user"></i>
                                    <span>Lihat Profil</span>
                                </span>
                            @endif

                            @if ($logoutUrl)
                                <form method="POST" action="{{ $logoutUrl }}">
                                    @csrf

                                    <button
                                        type="submit"
                                        class="pegawai-profile-dropdown-item pegawai-profile-logout"
                                    >
                                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                                        <span>Logout</span>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>

                <button
                    type="button"
                    class="pegawai-notification-trigger"
                    id="pegawaiNotificationTrigger"
                    aria-label="Buka notifikasi"
                    aria-haspopup="dialog"
                    aria-controls="pegawaiNotificationModal"
                >
                    <i class="fa-regular fa-bell"></i>
                    <span class="pegawai-notification-indicator" @if ($pegawaiNotificationUnreadCount === 0) hidden @endif></span>
                </button>
            </nav>

            <main class="pegawai-app-main" id="mainContent">
                @include('components.alerts')
                @yield('content')
            </main>
        </div>

        <div
            class="pegawai-notification-modal"
            id="pegawaiNotificationModal"
            role="dialog"
            aria-modal="true"
            aria-labelledby="pegawaiNotificationTitle"
            aria-hidden="true"
            hidden
        >
            <div class="pegawai-notification-backdrop" id="pegawaiNotificationBackdrop"></div>

            <div class="pegawai-notification-panel" tabindex="-1">
                <div class="pegawai-notification-header">
                    <div class="pegawai-notification-heading">
                        <span class="pegawai-notification-heading-icon">
                            <i class="fa-regular fa-bell"></i>
                        </span>

                        <div>
                            <h2 id="pegawaiNotificationTitle">Notifikasi</h2>
                            <p>Informasi dan pemberitahuan terbaru Anda</p>
                        </div>
                    </div>

                    <button
                        type="button"
                        class="pegawai-notification-close"
                        id="pegawaiNotificationClose"
                        aria-label="Tutup notifikasi"
                    >
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="pegawai-notification-content">
                    @hasSection('pegawai_notifications')
                        @yield('pegawai_notifications')
                    @elseif ($pegawaiNotifications->isNotEmpty())
                        <div class="pegawai-notification-list">
                            @foreach ($pegawaiNotifications as $notification)
                                <a href="{{ route('pegawai.notifikasi.open', $notification) }}"
                                    class="pegawai-notification-item {{ $notification->dibaca_pada ? '' : 'is-unread' }}">
                                    <span class="pegawai-notification-item-icon">
                                        <i
                                            class="fa-solid {{ $notification->jenis === 'absensi' ? 'fa-calendar-check' : 'fa-file-lines' }}"></i>
                                    </span>

                                    <span class="pegawai-notification-item-copy">
                                        <strong>{{ $notification->judul }}</strong>
                                        <span>{{ $notification->pesan }}</span>
                                        <small>{{ $notification->created_at->translatedFormat('d M Y, H:i') }}</small>
                                        <small class="pegawai-notification-item-read-status">
                                            {{ $notification->dibaca_pada ? 'Sudah dilihat' : 'Belum dilihat' }}
                                        </small>
                                    </span>

                                    @if (!$notification->dibaca_pada)
                                        <span class="pegawai-notification-item-unread" aria-label="Belum dibaca"></span>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="pegawai-notification-empty">
                            <span class="pegawai-notification-empty-icon">
                                <i class="fa-regular fa-bell-slash"></i>
                            </span>

                            <h3>Belum ada notifikasi</h3>

                            <p>
                                Pemberitahuan terbaru terkait absensi, laporan harian,
                                dan penilaian kinerja akan ditampilkan di sini.
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const profileMenu = document.getElementById('pegawaiProfileMenu');
                const profileTrigger = document.getElementById('pegawaiProfileTrigger');
                const profileDropdown = document.getElementById('pegawaiProfileDropdown');

                const notificationTrigger = document.getElementById('pegawaiNotificationTrigger');
                const notificationModal = document.getElementById('pegawaiNotificationModal');
                const notificationBackdrop = document.getElementById('pegawaiNotificationBackdrop');
                const notificationClose = document.getElementById('pegawaiNotificationClose');
                const notificationPanel = notificationModal?.querySelector('.pegawai-notification-panel');

                let lastFocusedElement = null;

                function closeProfileDropdown() {
                    if (!profileDropdown || !profileTrigger) {
                        return;
                    }

                    profileDropdown.hidden = true;
                    profileTrigger.setAttribute('aria-expanded', 'false');
                    profileMenu.classList.remove('is-open');
                }

                function openProfileDropdown() {
                    if (!profileDropdown || !profileTrigger) {
                        return;
                    }

                    profileDropdown.hidden = false;
                    profileTrigger.setAttribute('aria-expanded', 'true');
                    profileMenu.classList.add('is-open');
                }

                if (profileTrigger && profileDropdown) {
                    profileTrigger.addEventListener('click', function (event) {
                        event.stopPropagation();

                        if (profileDropdown.hidden) {
                            openProfileDropdown();
                        } else {
                            closeProfileDropdown();
                        }
                    });

                    document.addEventListener('click', function (event) {
                        if (!profileMenu.contains(event.target)) {
                            closeProfileDropdown();
                        }
                    });
                }

                function openNotification() {
                    if (!notificationModal) {
                        return;
                    }

                    closeProfileDropdown();

                    lastFocusedElement = document.activeElement;

                    notificationModal.hidden = false;
                    notificationModal.setAttribute('aria-hidden', 'false');

                    document.body.classList.add('pegawai-notification-open');

                    requestAnimationFrame(function () {
                        notificationModal.classList.add('is-visible');
                        notificationClose?.focus();
                    });
                }

                function closeNotification() {
                    if (!notificationModal || notificationModal.hidden) {
                        return;
                    }

                    notificationModal.classList.remove('is-visible');
                    notificationModal.setAttribute('aria-hidden', 'true');
                    notificationModal.hidden = true;

                    document.body.classList.remove('pegawai-notification-open');

                    if (lastFocusedElement instanceof HTMLElement) {
                        lastFocusedElement.focus();
                    }
                }

                notificationTrigger?.addEventListener('click', openNotification);
                notificationClose?.addEventListener('click', closeNotification);
                notificationBackdrop?.addEventListener('click', closeNotification);

                notificationModal?.addEventListener('keydown', function (event) {
                    if (event.key !== 'Tab') {
                        return;
                    }

                    const focusable = Array.from(
                        notificationPanel.querySelectorAll(
                            'button:not([disabled]), a[href], input:not([disabled]), [tabindex]:not([tabindex="-1"])'
                        )
                    ).filter(function (element) {
                        return element.getClientRects().length > 0;
                    });

                    if (!focusable.length) {
                        event.preventDefault();
                        notificationPanel.focus();
                        return;
                    }

                    const first = focusable[0];
                    const last = focusable[focusable.length - 1];

                    if (event.shiftKey && document.activeElement === first) {
                        event.preventDefault();
                        last.focus();
                    } else if (!event.shiftKey && document.activeElement === last) {
                        event.preventDefault();
                        first.focus();
                    }
                });

                document.addEventListener('keydown', function (event) {
                    if (event.key === 'Escape') {
                        if (notificationModal && !notificationModal.hidden) {
                            closeNotification();
                        } else {
                            closeProfileDropdown();
                        }
                    }
                });
            });
        </script>

        @stack('scripts')
    </body>
    </html>
