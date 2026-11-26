<div
    class="master-jam-window-preview"
    data-attendance-window-preview
    aria-live="polite"
>
    <div class="master-jam-window-preview-heading">
        <i class="fa-solid fa-eye" aria-hidden="true"></i>
        Pratinjau rentang absen
        <span>Presensi di luar rentang akan ditolak</span>
    </div>
    <div class="master-jam-window-preview-grid">
        <div class="master-jam-window-preview-item">
            <strong>Rentang Absen Masuk</strong>
            <span data-entry-schedule>Jam Masuk: --:--</span>
            <span data-entry-range>Rentang: --:-- – --:--</span>
            <small data-entry-details>Atur jam buka dan batas absen masuk.</small>
        </div>
        <div class="master-jam-window-preview-item">
            <strong>Rentang Absen Pulang</strong>
            <span data-exit-schedule>Jam Pulang: --:--</span>
            <span data-exit-range>Rentang: --:-- – --:--</span>
            <small data-exit-details>Atur jam buka dan tutup absen pulang.</small>
        </div>
    </div>
</div>

<script>
    (function () {
        const preview = document.currentScript.previousElementSibling;
        const form = preview.closest('form');
        const jamMasukInput = form.querySelector('[name="jam_masuk"]');
        const jamPulangInput = form.querySelector('[name="jam_pulang"]');
        const entryOpenInput = form.querySelector('[name="jam_buka_absen_masuk"]');
        const entryLimitInput = form.querySelector('[name="batas_absen_masuk"]');
        const exitOpenInput = form.querySelector('[name="jam_buka_absen_pulang"]');
        const exitCloseInput = form.querySelector('[name="jam_tutup_absen_pulang"]');

        function parseMinutes(value) {
            if (!value || !/^\d{2}:\d{2}$/.test(value)) {
                return null;
            }

            const parts = value.split(':').map(Number);
            return parts[0] * 60 + parts[1];
        }

        function formatMinutes(minutes) {
            const normalized = ((minutes % 1440) + 1440) % 1440;
            const hours = String(Math.floor(normalized / 60)).padStart(2, '0');
            const remainingMinutes = String(normalized % 60).padStart(2, '0');

            return hours + ':' + remainingMinutes;
        }

        function updateEntryWindow() {
            const jamMasuk = parseMinutes(jamMasukInput.value);
            const open = parseMinutes(entryOpenInput.value);
            const deadline = parseMinutes(entryLimitInput.value);
            const schedule = preview.querySelector('[data-entry-schedule]');
            const range = preview.querySelector('[data-entry-range]');
            const details = preview.querySelector('[data-entry-details]');

            if (jamMasuk === null || open === null || deadline === null) {
                schedule.textContent = 'Jam Masuk: --:--';
                range.textContent = 'Rentang: --:-- – --:--';
                details.textContent = 'Atur jam buka dan batas absen masuk.';
                return;
            }

            const openTime = formatMinutes(open);
            const deadlineTime = formatMinutes(deadline);
            const selisihBatas = deadline - jamMasuk;

            schedule.textContent = 'Jam Masuk: ' + formatMinutes(jamMasuk);
            range.textContent = 'Rentang: ' + openTime + ' – ' + deadlineTime;
            details.textContent = 'Buka: ' + openTime + ' · Batas: ' + deadlineTime
                + ' (' + (selisihBatas >= 0 ? '+' : '') + selisihBatas + 'm dari jam masuk)';
        }

        function updateExitWindow() {
            const jamPulang = parseMinutes(jamPulangInput.value);
            const open = parseMinutes(exitOpenInput.value);
            const close = parseMinutes(exitCloseInput.value);
            const schedule = preview.querySelector('[data-exit-schedule]');
            const range = preview.querySelector('[data-exit-range]');
            const details = preview.querySelector('[data-exit-details]');

            if (jamPulang === null || open === null || close === null) {
                schedule.textContent = 'Jam Pulang: --:--';
                range.textContent = 'Rentang: --:-- – --:--';
                details.textContent = 'Atur jam buka dan tutup absen pulang.';
                return;
            }

            const openTime = formatMinutes(open);
            const closeTime = formatMinutes(close);
            schedule.textContent = 'Jam Pulang: ' + formatMinutes(jamPulang);
            range.textContent = 'Rentang: ' + openTime + ' – ' + closeTime;
            details.textContent = 'Buka: ' + openTime + ' · Tutup: ' + closeTime;
        }

        [jamMasukInput, entryOpenInput, entryLimitInput].forEach(function (input) {
            input.addEventListener('input', updateEntryWindow);
        });
        [jamPulangInput, exitOpenInput, exitCloseInput].forEach(function (input) {
            input.addEventListener('input', updateExitWindow);
        });

        updateEntryWindow();
        updateExitWindow();
    })();
</script>
