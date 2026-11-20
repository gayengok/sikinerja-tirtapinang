@extends(in_array($attendanceRoute, ['atasan.absensi-saya', 'kepala_bagian.absensi-saya'], true) ? 'layouts.app' : 'layouts.apps')

@section('title', 'Presensi ' . $attendanceLabel)

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <link rel="stylesheet" href="{{ asset('css/pegawai/absensi/index.css') }}">
@endpush

@section('content')
    <div class="manual-page attendance-dashboard-page">
        @php($attendanceLocked = $cutiHariIni || $hariLiburHariIni !== null)
        @php($jenisPresensiAwal = old('jenis_presensi', $absensiMasukHariIni && !$absensiPulangHariIni ? 'pulang' : 'masuk'))

        <header class="manual-header">
            <button class="manual-back" type="button" onclick="history.back()" aria-label="Kembali">
                <i class="fa-solid fa-arrow-left"></i>
            </button>
            <div>
                <h1>Presensi {{ $attendanceLabel }}</h1>
                <p>Rekam kehadiran dengan verifikasi GPS dan Foto</p>
            </div>
            <div class="attendance-header-clock" id="attendanceClock"
                data-server-timestamp="{{ now()->timestamp }}">
                <span class="attendance-header-clock-icon"><i class="fa-regular fa-clock"></i></span>
                <div class="attendance-header-clock-text">
                    <small id="attendanceClockDate">{{ now()->translatedFormat('l, d F Y') }}</small>
                    <strong><span id="attendanceClockTime">{{ now()->format('H:i:s') }}</span> <em>WIB</em></strong>
                </div>
            </div>
        </header>

        @if ($hariLiburHariIni)
            <dialog class="manual-holiday-dialog" id="holidayDialog" aria-labelledby="holidayDialogTitle">
                <div class="manual-holiday-dialog-content">
                    <span class="manual-holiday-dialog-icon">
                        <i class="fa-solid fa-calendar-xmark"></i>
                    </span>
                    <h2 id="holidayDialogTitle">Hari Libur</h2>
                    <p>{{ $hariLiburHariIni['label'] }}. Presensi tidak diperlukan dan dinonaktifkan otomatis.</p>
                    <button type="button" id="closeHolidayDialog">Mengerti</button>
                </div>
            </dialog>
        @endif

        <dialog class="manual-holiday-dialog" id="attendanceWarningDialog"
            data-show-on-load="{{ session('attendance_warning') ? 'true' : 'false' }}"
            aria-labelledby="attendanceWarningDialogTitle">
            <div class="manual-holiday-dialog-content">
                <span class="manual-holiday-dialog-icon">
                    <i class="fa-solid fa-circle-exclamation"></i>
                </span>
                <h2 id="attendanceWarningDialogTitle">Presensi Sudah Tercatat</h2>
                <p>{{ session('attendance_warning', 'Setiap jenis presensi hanya dapat dilakukan satu kali sehari.') }}</p>
                <button type="button" class="close-attendance-warning">Mengerti</button>
            </div>
        </dialog>

        <div class="manual-body">
            <div class="manual-card manual-banner manual-banner-info manual-system-notice">
                <i class="fa-solid fa-circle-info"></i>
                <div>
                    <strong>Perhatian Sistem</strong>
                    Presensi hanya diterima ketika koordinat perangkat berada di dalam area kantor.
                    Aktifkan izin lokasi akurat dan kamera. Batas akurasi GPS maksimal 25 meter.
                </div>
            </div>



            @if ($cutiHariIni)
                <div class="manual-card manual-banner manual-banner-warning">
                    <i class="fa-solid fa-umbrella-beach"></i>
                    <div>
                        <strong>Anda sedang cuti hari ini</strong>
                        Presensi dinonaktifkan karena status Anda tercatat sebagai cuti.
                    </div>
                </div>
            @endif

            @if ($hariLiburHariIni)
                <div class="manual-card manual-banner manual-banner-warning">
                    <i class="fa-solid fa-calendar-xmark"></i>
                    <div>
                        <strong>Hari ini hari libur</strong>
                        {{ $hariLiburHariIni['label'] }}.
                    </div>
                </div>
            @endif

            <fieldset class="{{ $attendanceLocked ? 'manual-disabled' : '' }}" @disabled($attendanceLocked)>
                <form id="attendanceForm"
                    action="{{ route($attendanceRoute . '.store') }}"
                    method="POST"
                    enctype="multipart/form-data">
                    @csrf

                    <input type="hidden" id="jenisPresensi" name="jenis_presensi" value="{{ $jenisPresensiAwal }}">
                    <input type="hidden" id="latitude" name="latitude">
                    <input type="hidden" id="longitude" name="longitude">
                    <input type="hidden" id="accuracy" name="accuracy">

                    @if ($errors->any())
                        <div class="manual-card manual-banner manual-banner-warning" role="alert">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                            <div>{{ $errors->first() }}</div>
                        </div>
                    @endif

                    @if (session('success') || session('error'))
                        <div class="manual-card manual-banner manual-banner-info" role="status">
                            <i class="fa-solid fa-circle-info"></i>
                            <div>{{ session('success') ?: session('error') }}</div>
                        </div>
                    @endif

                    <div class="manual-attendance-layout attendance-flow-layout">
                        <div class="attendance-primary-grid">
                            <div class="attendance-gps-column">
                                <section class="manual-card manual-gps-card">
                                <div class="manual-section-title">
                                    <span>
                                        <i class="fa-solid fa-location-crosshairs"></i>
                                        Validasi Geolokasi GPS
                                    </span>
                                    <span class="manual-chip">GPS Real-time</span>
                                </div>

                                <p class="manual-gps-status">
                                    GPS diperbarui otomatis selama halaman aktif.
                                    Presensi hanya diterima di dalam area kantor.
                                </p>

                                <div class="manual-map">
                                    <div class="manual-map-canvas" id="attendanceMap"></div>

                                    <div class="manual-map-overlay">
                                        <strong id="gpsLocationTitle">
                                            MENUNGGU LOKASI GPS
                                        </strong>
                                        <span id="gpsCoordinates">
                                            Mengaktifkan pembacaan lokasi perangkat...
                                        </span>
                                    </div>
                                </div>

                                <div class="manual-gps">
                                    <div>
                                        <strong>Wajib berada di dalam area kantor</strong>
                                        <small class="manual-gps-status" id="gpsStatus">
                                            Memulai GPS otomatis...
                                        </small>
                                    </div>

                                    <button type="button" class="manual-refresh" id="refreshGpsButton">
                                        <i class="fa-solid fa-rotate"></i>
                                        Cari Ulang
                                    </button>
                                </div>
                            </section>
                            </div>
                            <div class="attendance-camera-column">
                                <section class="manual-card manual-photo-card">
                                <div class="manual-section-title">
                                    <span>
                                        <svg class="manual-photo-icon" viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M4 7h3l1.5-2h5L15 7h3a2 2 0 0 1 2 2v9a2 2 0 0 1 2-2Z" />
                                            <circle cx="12" cy="13" r="3.5" />
                                        </svg>
                                        Foto Absensi
                                    </span>
                                    <span class="manual-chip manual-photo-status" id="manualPhotoStatus">
                                        Belum diambil
                                    </span>
                                </div>

                                <div class="manual-photo" id="manualPhotoPreview">
                                    <video class="manual-camera-video" id="manualCameraVideo" autoplay playsinline muted hidden></video>
                                    <img class="manual-photo-preview"
                                        id="manualPhotoImage"
                                        alt="Pratinjau foto absensi">

                                    <div class="manual-photo-guide" aria-hidden="true">
                                        <span class="manual-photo-corner manual-photo-corner-tl"></span>
                                        <span class="manual-photo-corner manual-photo-corner-tr"></span>
                                        <span class="manual-photo-corner manual-photo-corner-bl"></span>
                                        <span class="manual-photo-corner manual-photo-corner-br"></span>
                                        <span class="manual-photo-frame"></span>
                                        <span class="manual-photo-scan"></span>
                                    </div>

                                    <div class="manual-photo-body">
                                        <span class="manual-photo-body-icon">
                                            <i class="fa-solid fa-camera"></i>
                                        </span>
                                        <strong>Siap mengambil foto</strong>
                                        <span class="manual-photo-hint">
                                            Pastikan wajah terlihat jelas dan pencahayaan cukup
                                        </span>
                                    </div>

                                    <span class="manual-photo-label">
                                        <i class="fa-solid fa-shield-halved"></i>
                                        Foto digunakan untuk verifikasi absensi
                                    </span>

                                    <span class="manual-photo-badge" aria-hidden="true">
                                        <i class="fa-solid fa-check"></i>
                                    </span>

                                    <button type="button"
                                        class="manual-photo-floating-retake"
                                        id="manualFloatingRetake"
                                        aria-label="Ambil ulang foto">
                                        <i class="fa-solid fa-rotate"></i>
                                    </button>
                                </div>

                                <input class="manual-photo-input"
                                    id="manualPhotoInput"
                                    name="foto"
                                    type="file"
                                    accept="image/jpeg,image/png,image/webp"
                                    capture="user">

                                <div class="manual-actions">
                                    <button type="button"
                                        class="manual-action manual-action-primary"
                                        id="manualPhotoButton">
                                        <i class="fa-solid fa-camera"></i>
                                        Buka Kamera
                                    </button>

                                    <button type="button"
                                        class="manual-action"
                                        id="manualRetakeButton"
                                        disabled>
                                        <i class="fa-solid fa-rotate"></i>
                                        Ambil Ulang
                                    </button>
                                </div>
                            </section>
                            </div>
                        </div>
                        <div class="attendance-secondary-grid">
                            <div class="attendance-presence-column">
                                <section class="manual-card manual-presence-card">
                                <div class="manual-section-title">
                                    <span>Jenis Presensi</span>
                                    <span class="manual-sync">
                                        <i class="fa-solid fa-circle"></i>
                                        Sinkron Server
                                    </span>
                                </div>

                                <div class="manual-types" role="group" aria-label="Jenis presensi">
                                    <button type="button"
                                        class="{{ $jenisPresensiAwal === 'masuk' ? 'selected' : '' }}"
                                        data-presensi="masuk"
                                        data-completed="{{ $absensiMasukHariIni ? 'true' : 'false' }}">
                                        <i class="fa-solid fa-arrow-right-to-bracket"></i>
                                        Masuk
                                        @if ($absensiMasukHariIni)
                                            <small>Sudah tercatat</small>
                                        @endif
                                    </button>

                                    <button type="button"
                                        class="{{ $jenisPresensiAwal === 'pulang' ? 'selected' : '' }}"
                                        data-presensi="pulang"
                                        data-completed="{{ $absensiPulangHariIni ? 'true' : 'false' }}">
                                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                                        Pulang
                                        @if ($absensiPulangHariIni)
                                            <small>Sudah tercatat</small>
                                        @endif
                                    </button>
                                </div>

                                <label class="manual-label" for="status">Status Absensi</label>

                                <select class="manual-select manual-field" id="status" name="status" required>
                                    <option value="hadir" @selected(old('status', 'hadir') === 'hadir')>
                                        Hadir (Bekerja Normal)
                                    </option>
                                    <option value="izin" @selected(old('status') === 'izin')>
                                        Izin
                                    </option>
                                    <option value="alpa" @selected(old('status') === 'alpa')>
                                        Alpa
                                    </option>
                                </select>

                                <button type="submit" form="attendanceForm"
                                    class="manual-submit" id="submitAttendanceButton">
                                    <i class="fa-solid fa-user-check"></i>
                                    Kirim Presensi
                                </button>

                                <div class="manual-note">
                                    <i class="fa-solid fa-shield-halved"></i>
                                    <span>
                                        Koordinat GPS dan foto dicatat untuk verifikasi.
                                        Lokasi di luar area tidak dapat melakukan presensi.
                                    </span>
                                </div>
                            </section>
                            </div>
                        </div>
                    </div>
                </form>
            </fieldset>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        (() => {
            const clock = document.getElementById('attendanceClock');
            const dateElement = document.getElementById('attendanceClockDate');
            const timeElement = document.getElementById('attendanceClockTime');

            if (!clock || !dateElement || !timeElement) return;

            const serverTimestamp = Number(clock.dataset.serverTimestamp);
            if (!Number.isFinite(serverTimestamp)) return;

            const serverTime = serverTimestamp * 1000;
            const pageOpenedAt = performance.now();

            const dateFormatter = new Intl.DateTimeFormat('id-ID', {
                timeZone: 'Asia/Jakarta',
                weekday: 'long',
                day: '2-digit',
                month: 'long',
                year: 'numeric'
            });

            const timeFormatter = new Intl.DateTimeFormat('id-ID', {
                timeZone: 'Asia/Jakarta',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hourCycle: 'h23'
            });

            const updateClock = () => {
                const currentTime = new Date(
                    serverTime + performance.now() - pageOpenedAt
                );

                dateElement.textContent = dateFormatter.format(currentTime);
                timeElement.textContent = timeFormatter.format(currentTime);
            };

            updateClock();
            window.setInterval(updateClock, 250);
        })();

        (() => {
            const openDialog = (dialog) => {
                if (!dialog) return;

                if (typeof dialog.showModal === 'function') {
                    if (!dialog.open) dialog.showModal();
                } else {
                    dialog.setAttribute('open', '');
                }
            };

            const closeDialog = (dialog) => {
                if (typeof dialog?.close === 'function') {
                    dialog.close();
                } else {
                    dialog?.removeAttribute('open');
                }
            };

            const holidayDialog = document.getElementById('holidayDialog');
            const warningDialog = document.getElementById('attendanceWarningDialog');

            openDialog(holidayDialog);

            if (warningDialog?.dataset.showOnLoad === 'true') {
                openDialog(warningDialog);
            }

            document.getElementById('closeHolidayDialog')
                ?.addEventListener('click', () => closeDialog(holidayDialog));

            document.querySelectorAll('.close-attendance-warning')
                .forEach((button) => {
                    button.addEventListener('click', () => closeDialog(warningDialog));
                });
        })();

        (() => {
            const typeInput = document.getElementById('jenisPresensi');
            const typeButtons = document.querySelectorAll('[data-presensi]');

            typeButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    if (button.dataset.completed === 'true') {
                        const dialog = document.getElementById('attendanceWarningDialog');
                        const message = document.querySelector('#attendanceWarningDialog p');

                        if (message) {
                            message.textContent =
                                `Presensi ${button.dataset.presensi} hari ini sudah tercatat.`;
                        }

                        if (dialog && !dialog.open) {
                            if (typeof dialog.showModal === 'function') {
                                dialog.showModal();
                            } else {
                                dialog.setAttribute('open', '');
                            }
                        }

                        return;
                    }

                    typeButtons.forEach((item) => item.classList.remove('selected'));
                    button.classList.add('selected');
                    typeInput.value = button.dataset.presensi;
                });
            });
        })();

        (() => {
            const input = document.getElementById('manualPhotoInput');
            const preview = document.getElementById('manualPhotoPreview');
            const image = document.getElementById('manualPhotoImage');
            const video = document.getElementById('manualCameraVideo');
            const takeButton = document.getElementById('manualPhotoButton');
            const retakeButton = document.getElementById('manualRetakeButton');
            const floatingButton = document.getElementById('manualFloatingRetake');
            const photoStatus = document.getElementById('manualPhotoStatus');
            if (!input || !preview || !image || !video || !takeButton || !retakeButton) return;

            let stream = null;
            let previewUrl = null;
            let opening = false;
            const stopCamera = () => {
                if (stream) stream.getTracks().forEach(track => track.stop());
                stream = null;
                video.srcObject = null;
                video.hidden = true;
                preview.classList.remove('is-camera-live');
            };

            const openCamera = async () => {
                if (opening) return;
                if (!navigator.mediaDevices?.getUserMedia) {
                    alert('Kamera langsung membutuhkan HTTPS atau localhost dan browser yang mendukung akses kamera.');
                    return;
                }
                opening = true;
                takeButton.disabled = true;
                try {
                    stopCamera();
                    stream = await navigator.mediaDevices.getUserMedia({
                        audio: false,
                        video: { facingMode: 'user', width: { ideal: 1280 }, height: { ideal: 960 } }
                    });
                    video.srcObject = stream;
                    await video.play();
                    preview.classList.remove('has-preview');
                    preview.classList.add('is-camera-live');
                    video.hidden = false;
                    takeButton.innerHTML = '<i class="fa-solid fa-camera"></i> Jepret Foto';
                    photoStatus.textContent = 'Kamera aktif';
                    photoStatus.classList.remove('is-ready');
                } catch (error) {
                    stopCamera();
                    const message = error.name === 'NotAllowedError'
                        ? 'Izin kamera ditolak. Aktifkan izin kamera di pengaturan browser.'
                        : error.name === 'NotFoundError'
                            ? 'Kamera tidak ditemukan pada perangkat ini.'
                            : 'Kamera tidak dapat dibuka: ' + error.message;
                    alert(message);
                } finally {
                    opening = false;
                    takeButton.disabled = false;
                }
            };

            const capturePhoto = async () => {
                if (!stream || video.readyState < 2) return;
                const width = Math.min(video.videoWidth || 1280, 1280);
                const height = Math.round(width * (video.videoHeight || 960) / (video.videoWidth || 1280));
                const canvas = document.createElement('canvas');
                canvas.width = width;
                canvas.height = height;
                canvas.getContext('2d').drawImage(video, 0, 0, width, height);
                const blob = await new Promise(resolve => canvas.toBlob(resolve, 'image/jpeg', .85));
                if (!blob) {
                    alert('Gagal mengambil foto. Silakan coba kembali.');
                    return;
                }
                if (blob.size > 5 * 1024 * 1024) {
                    alert('Foto melebihi batas 5 MB.');
                    return;
                }
                const file = new File([blob], 'absensi-' + Date.now() + '.jpg', { type: 'image/jpeg' });
                const transfer = new DataTransfer();
                transfer.items.add(file);
                input.files = transfer.files;
                if (previewUrl) URL.revokeObjectURL(previewUrl);
                previewUrl = URL.createObjectURL(file);
                image.src = previewUrl;
                stopCamera();
                preview.classList.add('has-preview');
                photoStatus.textContent = 'Sudah diambil';
                photoStatus.classList.add('is-ready');
                takeButton.innerHTML = '<i class="fa-solid fa-camera"></i> Ambil Ulang Foto';
                retakeButton.disabled = false;
            };

            takeButton.addEventListener('click', () => stream ? capturePhoto() : openCamera());
            retakeButton.addEventListener('click', openCamera);
            floatingButton?.addEventListener('click', openCamera);
            window.addEventListener('pagehide', () => {
                stopCamera();
                if (previewUrl) URL.revokeObjectURL(previewUrl);
            });
            document.addEventListener('visibilitychange', () => {
                if (document.hidden && stream) {
                    stopCamera();
                    takeButton.innerHTML = '<i class="fa-solid fa-camera"></i> Buka Kamera';
                }
            });
        })();

        (() => {
            const form = document.getElementById('attendanceForm');
            const typeInput = document.getElementById('jenisPresensi');
            const refreshButton = document.getElementById('refreshGpsButton');
            const latitudeInput = document.getElementById('latitude');
            const longitudeInput = document.getElementById('longitude');
            const accuracyInput = document.getElementById('accuracy');
            const locationTitle = document.getElementById('gpsLocationTitle');
            const coordinates = document.getElementById('gpsCoordinates');
            const status = document.getElementById('gpsStatus');
            const mapElement = document.getElementById('attendanceMap');
            const submitButton = document.getElementById('submitAttendanceButton');
            const photoInput = document.getElementById('manualPhotoInput');

            if (
                !form || !typeInput || !refreshButton ||
                !latitudeInput || !longitudeInput || !accuracyInput ||
                !locationTitle || !coordinates || !status || !mapElement
            ) {
                return;
            }

            const geofenceData = @json($geofence);

            const MAX_ACCURACY = 25;
            const TARGET_ACCURACY = 10;
            const MAX_LOCATION_AGE = 15000;
            const GPS_TIMEOUT = 30000;

            let lastPosition = null;
            let watchId = null;
            let userMarker = null;
            let accuracyCircle = null;
            let submitting = false;
            let gpsStartedAt = 0;

            const map = window.L
                ? L.map(mapElement).setView([-2.146627, 106.101188], 18)
                : null;

            if (map) {
                L.tileLayer(
                    'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
                    {
                        maxZoom: 19,
                        attribution: '&copy; OpenStreetMap contributors'
                    }
                ).addTo(map);

                const geofenceLayer = L.geoJSON(geofenceData, {
                    style: {
                        color: '#0875ad',
                        weight: 3,
                        fillColor: '#0875ad',
                        fillOpacity: 0.16
                    }
                }).addTo(map);

                map.fitBounds(geofenceLayer.getBounds(), {
                    padding: [20, 20]
                });
            }

            const pointInRing = (longitude, latitude, ring) => {
                let inside = false;

                for (
                    let i = 0, j = ring.length - 1;
                    i < ring.length;
                    j = i++
                ) {
                    const [xi, yi] = ring[i];
                    const [xj, yj] = ring[j];

                    const cross =
                        (longitude - xi) * (yj - yi) -
                        (latitude - yi) * (xj - xi);

                    if (
                        Math.abs(cross) < 1e-12 &&
                        longitude >= Math.min(xi, xj) &&
                        longitude <= Math.max(xi, xj) &&
                        latitude >= Math.min(yi, yj) &&
                        latitude <= Math.max(yi, yj)
                    ) {
                        return true;
                    }

                    if ((yi > latitude) !== (yj > latitude)) {
                        const intersectionX =
                            xi + (latitude - yi) *
                            (xj - xi) / (yj - yi);

                        if (longitude < intersectionX) {
                            inside = !inside;
                        }
                    }
                }

                return inside;
            };

            const insideGeofence = (latitude, longitude) => {
                const rings =
                    geofenceData.features[0].geometry.coordinates;

                if (!pointInRing(longitude, latitude, rings[0])) {
                    return false;
                }

                for (let i = 1; i < rings.length; i++) {
                    if (pointInRing(longitude, latitude, rings[i])) {
                        return false;
                    }
                }

                return true;
            };

            const setGpsStatus = (message, valid = false) => {
                status.textContent = message;
                status.style.color = valid
                    ? 'var(--pr-success, #198754)'
                    : '';
            };

            const clearLocation = () => {
                lastPosition = null;
                latitudeInput.value = '';
                longitudeInput.value = '';
                accuracyInput.value = '';
            };

            const isFresh = (position) => {
                return position &&
                    Number.isFinite(position.timestamp) &&
                    Date.now() >= position.timestamp &&
                    Date.now() - position.timestamp <= MAX_LOCATION_AGE;
            };

            const isValidPosition = (position) => {
                if (!isFresh(position)) return false;

                const { latitude, longitude, accuracy } = position.coords;

                return (
                    Number.isFinite(latitude) &&
                    Number.isFinite(longitude) &&
                    Number.isFinite(accuracy) &&
                    accuracy >= 0 &&
                    accuracy <= MAX_ACCURACY &&
                    insideGeofence(latitude, longitude)
                );
            };

            const updateMap = (position) => {
                if (!map) return;

                const { latitude, longitude, accuracy } = position.coords;
                const point = [latitude, longitude];

                if (!userMarker) {
                    userMarker = L.circleMarker(point, {
                        radius: 8,
                        color: '#ffffff',
                        weight: 2,
                        fillColor: '#2563eb',
                        fillOpacity: 1
                    }).addTo(map);
                } else {
                    userMarker.setLatLng(point);
                }

                if (!accuracyCircle) {
                    accuracyCircle = L.circle(point, {
                        radius: accuracy,
                        color: '#f59e0b',
                        fillColor: '#f59e0b',
                        fillOpacity: 0.08,
                        weight: 1
                    }).addTo(map);
                } else {
                    accuracyCircle.setLatLng(point);
                    accuracyCircle.setRadius(accuracy);
                }

                if (!map.getBounds().contains(point)) {
                    map.panTo(point);
                }
            };

            const displayPosition = (position) => {
                const { latitude, longitude, accuracy } = position.coords;

                const inside = insideGeofence(latitude, longitude);
                const accurate = accuracy <= MAX_ACCURACY;
                const fresh = isFresh(position);
                const valid = inside && accurate && fresh;

                coordinates.textContent =
                    `${latitude.toFixed(7)}, ${longitude.toFixed(7)}`;

                updateMap(position);

                if (!fresh) {
                    locationTitle.textContent = 'GPS BELUM DIPERBARUI';
                } else if (!accurate) {
                    locationTitle.textContent = 'AKURASI GPS BELUM CUKUP';
                } else if (!inside) {
                    locationTitle.textContent = 'LOKASI DI LUAR AREA KANTOR';
                } else {
                    locationTitle.textContent = 'LOKASI VALID DI DALAM AREA KANTOR';
                }

                const accuracyText = `Akurasi lokasi ±${Math.round(accuracy)} meter.`;

                if (valid) {
                    lastPosition = position;
                    latitudeInput.value = latitude.toFixed(8);
                    longitudeInput.value = longitude.toFixed(8);
                    accuracyInput.value = accuracy.toFixed(2);

                    setGpsStatus(
                        `${accuracyText} Lokasi valid dan diperbarui otomatis.`,
                        true
                    );
                } else {
                    clearLocation();

                    const locationIssue = !inside
                        ? 'Titik di luar area kantor. Periksa Layanan lokasi Windows dan Wi-Fi di laptop, lalu klik Cari Ulang.'
                        : !accurate
                            ? 'Akurasi lebih dari 25 meter. Periksa izin lokasi Windows/browser dan Wi-Fi.'
                            : 'Menunggu koordinat terbaru.';

                    setGpsStatus(`${accuracyText} ${locationIssue}`);
                }
            };

            const stopGps = () => {
                if (watchId !== null && navigator.geolocation) {
                    navigator.geolocation.clearWatch(watchId);
                    watchId = null;
                }
            };

            const startGps = () => {
                if (!window.isSecureContext) {
                    clearLocation();
                    locationTitle.textContent = 'HTTPS DIPERLUKAN';
                    setGpsStatus('GPS memerlukan HTTPS atau localhost.');
                    return;
                }

                if (!navigator.geolocation) {
                    clearLocation();
                    locationTitle.textContent = 'GPS TIDAK TERSEDIA';
                    setGpsStatus('Browser tidak mendukung geolokasi.');
                    return;
                }

                stopGps();
                clearLocation();

                gpsStartedAt = Date.now();
                locationTitle.textContent = 'MEMBACA GPS SECARA REAL-TIME';
                coordinates.textContent = 'Menunggu koordinat perangkat...';

                setGpsStatus('GPS aktif. Mencari lokasi dengan akurasi tinggi...');

                watchId = navigator.geolocation.watchPosition(
                    (position) => {
                        if (submitting) return;

                        if (
                            !Number.isFinite(position.coords.latitude) ||
                            !Number.isFinite(position.coords.longitude) ||
                            !Number.isFinite(position.coords.accuracy) ||
                            !isFresh(position)
                        ) {
                            return;
                        }

                        displayPosition(position);
                    },
                    (error) => {
                        if (submitting) return;

                        clearLocation();

                        const messages = {
                            1: 'Izin lokasi ditolak. Aktifkan izin lokasi akurat untuk browser.',
                            2: 'Lokasi tidak tersedia. Pastikan layanan lokasi perangkat dan Wi-Fi aktif.',
                            3: 'GPS belum mendapat posisi. Sistem tetap mencoba membaca lokasi.'
                        };

                        locationTitle.textContent = 'MENUNGGU GPS';

                        setGpsStatus(
                            messages[error.code] ||
                            'Terjadi kesalahan saat membaca GPS.'
                        );

                        if (error.code === 1) {
                            stopGps();
                        }
                    },
                    {
                        enableHighAccuracy: true,
                        maximumAge: 0,
                        timeout: GPS_TIMEOUT
                    }
                );
            };

            refreshButton.addEventListener('click', startGps);

            window.setInterval(() => {
                if (submitting) return;

                if (lastPosition && !isFresh(lastPosition)) {
                    clearLocation();
                    locationTitle.textContent = 'MENUNGGU GPS TERBARU';
                    setGpsStatus('Koordinat kedaluwarsa. Menunggu pembaruan otomatis.');
                }

                if (
                    watchId !== null &&
                    Date.now() - gpsStartedAt > GPS_TIMEOUT &&
                    !lastPosition &&
                    !document.hidden
                ) {
                    setGpsStatus(
                        'GPS masih mencari lokasi yang memenuhi syarat. ' +
                        'Di laptop, aktifkan Layanan lokasi Windows dan Wi-Fi. ' +
                        'Laptop tanpa GPS fisik mungkin tetap kurang akurat.'
                    );
                }
            }, 1000);

            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    stopGps();
                    clearLocation();
                    locationTitle.textContent = 'GPS DIJEDA';
                    setGpsStatus('GPS dijeda saat halaman tidak aktif.');
                } else if (!submitting) {
                    startGps();
                }
            });

            form.addEventListener('submit', (event) => {
                const selectedButton = document.querySelector(
                    `[data-presensi="${typeInput.value}"]`
                );

                if (selectedButton?.dataset.completed === 'true') {
                    event.preventDefault();
                    selectedButton.click();
                    return;
                }

                if (!lastPosition || !isValidPosition(lastPosition)) {
                    event.preventDefault();
                    clearLocation();
                    locationTitle.textContent = 'GPS BELUM VALID';

                    setGpsStatus(
                        'Presensi belum dapat dikirim. ' +
                        'Tunggu GPS akurat dan berada di dalam polygon.'
                    );
                    return;
                }

                if (!photoInput?.files?.length) {
                    event.preventDefault();
                    alert('Ambil foto terlebih dahulu.');
                    return;
                }

                const { latitude, longitude, accuracy } = lastPosition.coords;

                latitudeInput.value = latitude.toFixed(8);
                longitudeInput.value = longitude.toFixed(8);
                accuracyInput.value = accuracy.toFixed(2);

                submitting = true;
                stopGps();

                if (submitButton) {
                    submitButton.disabled = true;
                    submitButton.textContent = 'Mengirim Presensi...';
                }
            });

            window.addEventListener('pagehide', stopGps);

            if (!form.closest('fieldset')?.disabled) {
                startGps();
            } else {
                locationTitle.textContent = 'PRESENSI DINONAKTIFKAN';
                setGpsStatus('GPS tidak dijalankan karena presensi sedang dinonaktifkan.');
            }
        })();
    </script>
@endpush
