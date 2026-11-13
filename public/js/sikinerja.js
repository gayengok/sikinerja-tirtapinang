document.addEventListener('DOMContentLoaded', function () {
    const body = document.body;
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const toggleDesktop = document.getElementById('sidebarToggleDesktop');
    const toggleMobile = document.getElementById('sidebarToggleMobile');
    const closeMobile = document.getElementById('sidebarCloseBtn');

    // Toggle sidebar untuk layar desktop (collapse/expand)
    if (toggleDesktop) {
        toggleDesktop.addEventListener('click', function () {
            body.classList.toggle('sidebar-collapsed');
            const isExpanded = !body.classList.contains('sidebar-collapsed');
            const collapsedLabel = body.classList.contains('role-atasan')
                ? 'Perluas navigasi'
                : 'Buka navigasi';
            toggleDesktop.setAttribute('aria-expanded', String(isExpanded));
            toggleDesktop.setAttribute('aria-label', isExpanded ? 'Ciutkan navigasi' : collapsedLabel);
            toggleDesktop.setAttribute('title', isExpanded ? 'Ciutkan navigasi' : collapsedLabel);
        });
    }

    // Toggle sidebar untuk layar mobile (slide in/out + overlay)
    if (toggleMobile && sidebar && overlay) {
        const setMobileSidebar = function (isOpen) {
            sidebar.classList.toggle('show', isOpen);
            overlay.classList.toggle('show', isOpen);
            toggleMobile.setAttribute('aria-expanded', String(isOpen));
        };

        toggleMobile.addEventListener('click', function () {
            setMobileSidebar(!sidebar.classList.contains('show'));
        });

        overlay.addEventListener('click', function () {
            setMobileSidebar(false);
        });

        if (closeMobile) {
            closeMobile.addEventListener('click', function () {
                setMobileSidebar(false);
            });
        }

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && sidebar.classList.contains('show')) {
                setMobileSidebar(false);
                toggleMobile.focus();
            }
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