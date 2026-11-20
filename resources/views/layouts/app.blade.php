<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#026aa7">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <title>@yield('title', 'Dashboard') - {{ config('app.name', 'SIKINERJA') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="{{ asset('css/sikinerja.css') }}?v={{ file_exists(public_path('css/sikinerja.css')) ? filemtime(public_path('css/sikinerja.css')) : '1' }}" rel="stylesheet">
    @stack('styles')
</head>
<body class="mobile-only-mode role-{{ auth()->user()->role ?? 'guest' }} {{ in_array(auth()->user()?->role, ['kepala_bagian', 'direktur'], true) ? 'role-atasan' : '' }} {{ request()->routeIs('atasan.absensi.*', 'atasan.absensi-saya.*', 'kepala_bagian.absensi.*', 'kepala_bagian.absensi-saya.*', 'direktur.absensi.*') ? 'desktop-atasan-attendance' : '' }}">
    <div class="mobile-app-shell">
        @include('components.navbar')
        <main class="main-content" id="mainContent">
            @include('components.alerts')
            @yield('content')
            @include('components.footer')
        </main>
        @include('components.bottom-nav')
    </div>
    @include('components.sidebar')
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/sikinerja.js') }}"></script>
    @stack('scripts')
</body>
</html>
