<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#026aa7">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <title>@yield('title', 'Dashboard') - {{ config('app.name', 'SIKINERJA') }}</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <!-- Google Font (Inter) -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="{{ asset('css/sikinerja.css') }}" rel="stylesheet">

    @stack('styles')
</head>

<body class="mobile-only-mode role-{{ auth()->user()->role ?? 'guest' }} {{ auth()->user()?->role === 'kepala_bagian' ? 'role-atasan' : '' }} {{ request()->routeIs('atasan.absensi.*', 'atasan.absensi-saya.*', 'kepala_bagian.absensi.*') ? 'desktop-atasan-attendance' : '' }}">

    <!-- SMARTPHONE / MOBILE APP CONTAINER -->
    <div class="mobile-app-shell">
        @include('components.navbar')

        <main class="main-content" id="mainContent">
            @include('components.alerts')
            @yield('content')
            @include('components.footer')
        </main>

        @include('components.bottom-nav')
    </div>

    <!-- Mobile slide-over drawer navigation -->
    @include('components.sidebar')

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="{{ asset('js/sikinerja.js') }}"></script>

    @stack('scripts')
</body>

</html>
