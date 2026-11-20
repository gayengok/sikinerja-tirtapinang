
<div
    class="sikinerja-notification-modal"
    id="sikinerjaNotificationModal"
    role="dialog"
    aria-modal="true"
    aria-labelledby="sikinerjaNotificationTitle"
    aria-hidden="true"
    hidden
>
    <div
        class="sikinerja-notification-backdrop"
        id="sikinerjaNotificationBackdrop"
    ></div>

    <div class="sikinerja-notification-panel" tabindex="-1">
        <div class="sikinerja-notification-header">
            <div class="sikinerja-notification-heading">
                <span class="sikinerja-notification-heading-icon">
                    <i class="fa-regular fa-bell"></i>
                </span>

                <div class="sikinerja-notification-heading-text">
                    <h2 id="sikinerjaNotificationTitle">Notifikasi</h2>
                    <p>Informasi dan pemberitahuan terbaru Anda</p>
                </div>
            </div>

            <button
                type="button"
                class="sikinerja-notification-close"
                id="sikinerjaNotificationClose"
                aria-label="Tutup notifikasi"
            >
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="sikinerja-notification-content">
            <div class="sikinerja-notification-empty">
                <span class="sikinerja-notification-empty-icon">
                    <i class="fa-regular fa-bell-slash"></i>
                </span>

                <h3>Belum ada notifikasi</h3>

                <p>
                    Pemberitahuan terbaru terkait aktivitas,
                    absensi, laporan harian, dan penilaian kinerja
                    akan ditampilkan di sini.
                </p>
            </div>
        </div>
    </div>
</div>
