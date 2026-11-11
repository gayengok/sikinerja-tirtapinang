document.addEventListener('DOMContentLoaded', function () {
    const body = document.body;
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const toggleDesktop = document.getElementById('sidebarToggleDesktop');
    const toggleMobile = document.getElementById('sidebarToggleMobile');

    // Toggle sidebar untuk layar desktop (collapse/expand)
    if (toggleDesktop) {
        toggleDesktop.addEventListener('click', function () {
            body.classList.toggle('sidebar-collapsed');
            const isExpanded = !body.classList.contains('sidebar-collapsed');
            toggleDesktop.setAttribute('aria-expanded', String(isExpanded));
            toggleDesktop.setAttribute('aria-label', isExpanded ? 'Ciutkan navigasi' : 'Buka navigasi');
            toggleDesktop.setAttribute('title', isExpanded ? 'Ciutkan navigasi' : 'Buka navigasi');
        });
    }

    // Toggle sidebar untuk layar mobile (slide in/out + overlay)
    if (toggleMobile && sidebar && overlay) {
        toggleMobile.addEventListener('click', function () {
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
        });

        overlay.addEventListener('click', function () {
            sidebar.classList.remove('show');
            overlay.classList.remove('show');
        });
    }

    // Auto close alert setelah beberapa detik
    document.querySelectorAll('.alert').forEach(function (alertEl) {
        setTimeout(function () {
            if (window.bootstrap && bootstrap.Alert) {
                const alertInstance = bootstrap.Alert.getOrCreateInstance(alertEl);
                alertInstance.close();
            } else {
                alertEl.classList.remove('show');
            }
        }, 6000);
    });

    // Konfirmasi sederhana untuk tombol/form hapus
    document.querySelectorAll('.btn-confirm-delete, form.form-delete').forEach(function (el) {
        el.addEventListener('submit', function (e) {
            const message = el.getAttribute('data-confirm-message') || 'Apakah Anda yakin ingin menghapus data ini?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });

        // Jika elemen adalah tombol (bukan form), tangani via click + cari form terdekat
        if (el.tagName === 'BUTTON' || el.tagName === 'A') {
            el.addEventListener('click', function (e) {
                const message = el.getAttribute('data-confirm-message') || 'Apakah Anda yakin ingin menghapus data ini?';
                if (!confirm(message)) {
                    e.preventDefault();
                }
            });
        }
    });
});