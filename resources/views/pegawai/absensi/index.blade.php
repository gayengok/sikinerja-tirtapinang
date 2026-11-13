@extends('layouts.app')

@section('title', $attendanceRoute === 'atasan.absensi-saya' ? 'Presensi Atasan' : 'Presensi Pegawai')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <link rel="stylesheet" href="{{ asset('css/pegawai/absensi/index.css') }}">
@endpush

@section('content')
    <div class="manual-page {{ $attendanceRoute === 'atasan.absensi-saya' ? 'manual-atasan-page' : '' }}">
        @php($attendanceLocked = $cutiHariIni || $hariLiburHariIni !== null)
        @php($jenisPresensiAwal = old('jenis_presensi', $absensiMasukHariIni && !$absensiPulangHariIni ? 'pulang' : 'masuk'))

        <header class="manual-header">
            <button class="manual-back" type="button" onclick="history.back()" aria-label="Kembali">
                <i class="fa-solid fa-arrow-left"></i>
            </button>
            <div>
                <h1>{{ $attendanceRoute === 'atasan.absensi-saya' ? 'Presensi Atasan' : 'Presensi Pegawai' }}</h1>
                <p>Rekam kehadiran Anda dengan verifikasi lokasi &amp; foto</p>
            </div>
        </header>

        @if ($hariLiburHariIni)
            <dialog class="manual-holiday-dialog" id="holidayDialog" aria-labelledby="holidayDialogTitle">
                <div class="manual-holiday-dialog-content">
                    <span class="manual-holiday-dialog-icon"><i class="fa-solid fa-calendar-xmark"></i></span>
                    <h2 id="holidayDialogTitle">Hari Libur</h2>
                    <p>Keterangan: {{ $hariLiburHariIni['label'] }}. Anda tidak perlu melakukan presensi. Fitur presensi
                        dikunci otomatis dan akan terbuka kembali pada hari kerja.</p>
                    <button type="button" id="closeHolidayDialog">Mengerti</button>
                </div>
            </dialog>
        @endif
        <dialog class="manual-holiday-dialog" id="attendanceWarningDialog"
            data-show-on-load="{{ session('attendance_warning') ? 'true' : 'false' }}"
            aria-labelledby="attendanceWarningDialogTitle">
            <div class="manual-holiday-dialog-content">
                <span class="manual-holiday-dialog-icon"><i class="fa-solid fa-circle-exclamation"></i></span>
                <h2 id="attendanceWarningDialogTitle">Presensi Sudah Tercatat</h2>
                <p>{{ session('attendance_warning', 'Setiap jenis presensi hanya dapat dilakukan satu kali dalam sehari.') }}</p>
                <button type="button" class="close-attendance-warning">Mengerti</button>
            </div>
        </dialog>

        <div class="manual-body">
            <div class="manual-card manual-banner manual-banner-info manual-system-notice">
                <i aria-hidden="true">
                    <svg class="manual-banner-icon" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="9"></circle>
                        <path d="M12 11v5"></path>
                        <path d="M12 8h.01"></path>
                    </svg>
                </i>
                <div>
                    <strong>Perhatian Sistem</strong>
                    Pengajuan presensi memerlukan verifikasi foto selfie langsung di lokasi kerja serta koordinat
                    GPS akurat bertempel waktu. Pastikan izin browser untuk Kamera dan Lokasi aktif.
                </div>
            </div>

            <div class="manual-card manual-identity">
                <div class="manual-avatar">{{ strtoupper(substr($pegawai->nama, 0, 1)) }}</div>
                <div class="manual-identity-copy">
                    <h2>{{ $pegawai->nama }}</h2>
                    <a href="#"><i class="fa-regular fa-id-badge"></i>
                        {{ $pegawai->jabatan->nama_jabatan ?? 'Pegawai' }}</a>
                </div>
                <span class="manual-active">Aktif</span>
            </div>

            @if ($cutiHariIni)
                <div class="manual-card manual-banner manual-banner-warning" style="margin-bottom: 0.9rem;">
                    <i class="fa-solid fa-umbrella-beach"></i>
                    <div>
                        <strong>Anda sedang cuti hari ini</strong>
                        Presensi dinonaktifkan karena status kehadiran Anda tercatat sebagai cuti. Presensi dapat
                        dilakukan kembali pada hari kerja berikutnya.
                    </div>
                </div>
            @endif

            @if ($hariLiburHariIni)
                <div class="manual-card manual-banner manual-banner-warning" style="margin-bottom: 0.9rem;">
                    <i class="fa-solid fa-calendar-xmark"></i>
                    <div>
                        <strong>Hari ini hari libur</strong>
                        {{ $hariLiburHariIni['label'] }}. Presensi tidak diperlukan dan dinonaktifkan otomatis.
                    </div>
                </div>
            @endif

            <fieldset class="{{ $attendanceLocked ? 'manual-disabled' : '' }}" @disabled($attendanceLocked)>
                <form id="attendanceForm" action="{{ route($attendanceRoute . '.store') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="jenisPresensi" name="jenis_presensi"
                        value="{{ $jenisPresensiAwal }}">
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

                    <div class="manual-attendance-layout">
                        <div class="manual-attendance-left">
                            @if ($attendanceRoute === 'atasan.absensi-saya')
                                @include('pegawai.absensi.partials.attendance-calendar')
                            @endif

                            <section class="manual-card manual-clock-card">
                                <div class="manual-section-title">
                                    <span>Waktu Sistem Server</span>
                                </div>
                                <div class="manual-clock" id="attendanceClock" data-server-timestamp="{{ now()->timestamp }}">
                                    <div class="manual-clock-icon"><i class="fa-regular fa-clock"></i></div>
                                    <div class="manual-clock-time">
                                        <small id="attendanceClockDate">{{ now()->translatedFormat('l, d F Y') }}</small>
                                        <strong><span id="attendanceClockTime">{{ now()->format('H:i:s') }}</span> <em>WIB</em></strong>
                                    </div>
                                    <div class="manual-clock-meta">
                                        <span class="manual-lock"><i class="fa-solid fa-lock"></i> Waktu Terkunci</span>
                                    </div>
                                </div>
                            </section>

                            <section class="manual-card manual-presence-card">
                                <div class="manual-section-title">
                                    <span>Jenis Presensi</span>
                                    <span class="manual-sync"><i class="fa-solid fa-circle"></i> Sinkron Server</span>
                                </div>

                                <div class="manual-types" role="group" aria-label="Jenis presensi">
                                    <button type="button"
                                        class="{{ $jenisPresensiAwal === 'masuk' ? 'selected' : '' }}"
                                        data-presensi="masuk" data-completed="{{ $absensiMasukHariIni ? 'true' : 'false' }}">
                                        <i class="fa-solid fa-arrow-right-to-bracket"></i> Masuk
                                        @if ($absensiMasukHariIni)<small>Sudah tercatat</small>@endif
                                    </button>
                                    <button type="button"
                                        class="{{ $jenisPresensiAwal === 'pulang' ? 'selected' : '' }}"
                                        data-presensi="pulang" data-completed="{{ $absensiPulangHariIni ? 'true' : 'false' }}">
                                        <i class="fa-solid fa-arrow-right-from-bracket"></i> Pulang
                                        @if ($absensiPulangHariIni)<small>Sudah tercatat</small>@endif
                                    </button>
                                </div>

                                <label class="manual-label" for="status">Status Absensi</label>
                                <select class="manual-select manual-field" id="status" name="status" required>
                                    <option value="hadir" @selected(old('status', 'hadir') === 'hadir')>Hadir (Bekerja Normal)</option>
                                    <option value="izin" @selected(old('status') === 'izin')>Izin</option>
                                    <option value="cuti" @selected(old('status') === 'cuti')>Cuti</option>
                                </select>
                                <button type="submit" form="attendanceForm" class="manual-submit">
                                    <i class="fa-solid fa-user-check"></i> Kirim Presensi
                                </button>
                                <div class="manual-note">
                                    <i aria-hidden="true">
                                        <svg class="manual-note-icon" viewBox="0 0 24 24">
                                            <path d="M12 3 20 6v5c0 5-3.4 8.6-8 10-4.6-1.4-8-5-8-10V6l8-3Z"></path>
                                            <path d="m9 12 2 2 4-4"></path>
                                        </svg>
                                    </i>
                                    <span>Data presensi, titik koordinat GPS, dan foto akan diverifikasi oleh atasan langsung dan Subbagian Kepegawaian sesuai Peraturan Disiplin.</span>
                                </div>
                            </section>
                        </div>

                        <div class="manual-attendance-right">
                            <section class="manual-card manual-gps-card">
                                <div class="manual-section-title">
                                    <span><i class="fa-solid fa-location-crosshairs"></i> Validasi Geolokasi GPS</span>
                                    <span class="manual-chip">GPS presisi</span>
                                </div>
                                <p class="manual-gps-status">
                                    Presensi tetap dapat dikirim dari luar area kantor. Lokasi dan jaraknya akan ditandai untuk
                                    verifikasi atasan.
                                </p>

                                <div class="manual-map">
                                    <div class="manual-map-canvas" id="attendanceMap"></div>
                                    <div class="manual-map-overlay">
                                        <strong id="gpsLocationTitle">LOKASI BELUM DIVERIFIKASI</strong>
                                        <span id="gpsCoordinates">Tekan Perbarui untuk membaca beberapa pembacaan GPS terbaik</span>
                                    </div>
                                </div>

                                <div class="manual-gps">
                                    <div>
                                        <strong>Wajib berada di area kantor</strong>
                                        <small class="manual-gps-status" id="gpsStatus">GPS belum dibaca</small>
                                    </div>
                                    <button type="button" class="manual-refresh" id="refreshGpsButton">
                                        <i class="fa-solid fa-rotate"></i> Perbarui
                                    </button>
                                </div>
                            </section>

                            <section class="manual-card manual-photo-card">
                                <div class="manual-section-title">
                                    <span>
                                        <svg class="manual-photo-icon" viewBox="0 0 24 24" aria-hidden="true">
                                            <path
                                                d="M4 7h3l1.5-2h5L15 7h3a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2Z" />
                                            <circle cx="12" cy="13" r="3.5" />
                                        </svg>
                                        Foto Absensi
                                    </span>
                                    <span class="manual-chip manual-photo-status" id="manualPhotoStatus">Belum diambil</span>
                                </div>

                                <div class="manual-photo" id="manualPhotoPreview">
                                    <img class="manual-photo-preview" id="manualPhotoImage" alt="Pratinjau foto absensi">

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
                                            <svg class="manual-photo-icon" viewBox="0 0 24 24" aria-hidden="true">
                                                <path
                                                    d="M4 7h3l1.5-2h5L15 7h3a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2Z" />
                                                <circle cx="12" cy="13" r="3.5" />
                                            </svg>
                                        </span>
                                        <strong>Siap mengambil foto</strong>
                                        <span class="manual-photo-hint">Pastikan wajah terlihat jelas dan pencahayaan cukup</span>
                                    </div>

                                    <span class="manual-photo-label"><i class="fa-solid fa-shield-halved"></i> Foto digunakan
                                        untuk verifikasi absensi</span>

                                    <span class="manual-photo-badge" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" class="manual-photo-badge-icon">
                                            <path d="M20 6 9 17l-5-5" />
                                        </svg>
                                    </span>

                                    <button type="button" class="manual-photo-floating-retake" id="manualFloatingRetake"
                                        aria-label="Ambil ulang foto">
                                        <svg viewBox="0 0 24 24" class="manual-photo-icon">
                                            <path d="M20 11a8 8 0 0 0-14.8-4L3 9" />
                                            <path d="M3 4v5h5" />
                                            <path d="M4 13a8 8 0 0 0 14.8 4L21 15" />
                                            <path d="M21 20v-5h-5" />
                                        </svg>
                                    </button>
                                </div>

                                <input class="manual-photo-input" id="manualPhotoInput" name="foto" type="file" accept="image/*"
                                    capture="user">

                                <div class="manual-actions">
                                    <button type="button" class="manual-action manual-action-primary" id="manualPhotoButton">
                                        <svg class="manual-photo-icon" viewBox="0 0 24 24" aria-hidden="true">
                                            <path
                                                d="M4 7h3l1.5-2h5L15 7h3a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2Z" />
                                            <circle cx="12" cy="13" r="3.5" />
                                        </svg>
                                        Ambil Foto
                                    </button>
                                    <button type="button" class="manual-action" id="manualRetakeButton" disabled>
                                        <svg class="manual-photo-icon" viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M20 11a8 8 0 0 0-14.8-4L3 9" />
                                            <path d="M3 4v5h5" />
                                            <path d="M4 13a8 8 0 0 0 14.8 4L21 15" />
                                            <path d="M21 20v-5h-5" />
                                        </svg>
                                        Ambil Ulang
                                    </button>
                                </div>
                            </section>
                        </div>
                    </div>
                </form>
            </fieldset>
        </div>
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
            const serverTimestamp = Number(clock?.dataset.serverTimestamp);

            if (!clock || !dateElement || !timeElement || !Number.isFinite(serverTimestamp)) {
                return;
            }

            const serverTime = serverTimestamp * 1000;
            const pageOpenedAt = performance.now();
            const dateFormatter = new Intl.DateTimeFormat('id-ID', {
                timeZone: 'Asia/Jakarta',
                weekday: 'long',
                day: '2-digit',
                month: 'long',
                year: 'numeric',
            });
            const timeFormatter = new Intl.DateTimeFormat('id-ID', {
                timeZone: 'Asia/Jakarta',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hourCycle: 'h23',
            });

            const updateClock = () => {
                const currentTime = new Date(serverTime + performance.now() - pageOpenedAt);
                dateElement.textContent = dateFormatter.format(currentTime);
                timeElement.textContent = timeFormatter.format(currentTime);
            };

            updateClock();
            window.setInterval(updateClock, 250);
        })();

        (() => {
            const openDialog = (dialog) => {
                if (!dialog) {
                    return;
                }

                if (typeof dialog.showModal === 'function') {
                    dialog.showModal();
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
            const attendanceWarningDialog = document.getElementById('attendanceWarningDialog');

            openDialog(holidayDialog);
            if (attendanceWarningDialog?.dataset.showOnLoad === 'true') {
                openDialog(attendanceWarningDialog);
            }

            document.getElementById('closeHolidayDialog')?.addEventListener('click', () => {
                closeDialog(holidayDialog);
            });
            document.querySelectorAll('.close-attendance-warning').forEach((button) => {
                button.addEventListener('click', () => closeDialog(attendanceWarningDialog));
            });
        })();

        (() => {
            const typeInput = document.getElementById('jenisPresensi');
            const typeButtons = document.querySelectorAll('[data-presensi]');

            typeButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    if (button.dataset.completed === 'true') {
                        const warningDialog = document.getElementById('attendanceWarningDialog');
                        const warningText = document.querySelector('#attendanceWarningDialog p');
                        const label = button.dataset.presensi === 'masuk' ? 'masuk' : 'pulang';

                        if (warningText) {
                            warningText.textContent =
                                `Presensi ${label} hari ini sudah tercatat. Setiap jenis presensi hanya dapat dilakukan satu kali dalam sehari.`;
                        }
                        if (warningDialog) {
                            if (warningDialog.open) {
                                return;
                            }
                            if (typeof warningDialog.showModal === 'function') {
                                warningDialog.showModal();
                            } else {
                                warningDialog.setAttribute('open', '');
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
            const takePhotoButton = document.getElementById('manualPhotoButton');
            const retakeButton = document.getElementById('manualRetakeButton');
            const floatingRetakeButton = document.getElementById('manualFloatingRetake');
            const photoStatus = document.getElementById('manualPhotoStatus');

            if (!input || !preview || !image || !takePhotoButton || !retakeButton || !photoStatus) {
                return;
            }

            const openCameraInput = () => input.click();

            takePhotoButton.addEventListener('click', openCameraInput);
            retakeButton.addEventListener('click', openCameraInput);
            floatingRetakeButton?.addEventListener('click', openCameraInput);

            input.addEventListener('change', () => {
                const [photo] = input.files;

                if (!photo) {
                    return;
                }

                if (!photo.type.startsWith('image/')) {
                    input.value = '';
                    return;
                }

                image.src = URL.createObjectURL(photo);
                preview.classList.add('has-preview');
                photoStatus.textContent = 'Sudah diambil';
                photoStatus.classList.add('is-ready');
                takePhotoButton.innerHTML = `
                    <svg class="manual-photo-icon" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M4 7h3l1.5-2h5L15 7h3a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2Z" />
                        <circle cx="12" cy="13" r="3.5" />
                    </svg>
                    Ganti Foto`;
                retakeButton.disabled = false;
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
            const officeLatitude = -2.146435;
            const officeLongitude = 106.101327;
            const maximumDistance = 50;

            if (!form || !typeInput || !refreshButton || !latitudeInput || !longitudeInput || !accuracyInput || !mapElement) {
                return;
            }

            const map = window.L ? L.map(mapElement).setView([officeLatitude, officeLongitude], 17) : null;
            if (map) {
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap contributors',
                }).addTo(map);
                L.marker([officeLatitude, officeLongitude])
                    .addTo(map)
                    .bindPopup('<strong>PDAM Kota Pangkal Pinang</strong><br>Jl. Bandes No. 200');
                L.circle([officeLatitude, officeLongitude], {
                    radius: maximumDistance,
                    color: '#0875ad',
                    fillColor: '#0875ad',
                    fillOpacity: 0.14,
                }).addTo(map);
            }
            let userMarker = null;

            const distanceInMeters = (latitude, longitude) => {
                const earthRadius = 6371000;
                const latitudeDifference = (latitude - officeLatitude) * Math.PI / 180;
                const longitudeDifference = (longitude - officeLongitude) * Math.PI / 180;
                const a = Math.sin(latitudeDifference / 2) ** 2 +
                    Math.cos(officeLatitude * Math.PI / 180) *
                    Math.cos(latitude * Math.PI / 180) *
                    Math.sin(longitudeDifference / 2) ** 2;

                return earthRadius * 2 * Math.asin(Math.sqrt(a));
            };

            const setGpsStatus = (message, isValid = false) => {
                status.textContent = message;
                status.style.color = isValid ? 'var(--pr-success)' : '';
            };

            const readBestLocation = (attempt = 1, bestPosition = null) => new Promise((resolve, reject) => {
                navigator.geolocation.getCurrentPosition((position) => {
                    const betterPosition = !bestPosition ||
                        position.coords.accuracy < bestPosition.coords.accuracy ?
                        position :
                        bestPosition;

                    if (attempt >= 3 || betterPosition.coords.accuracy <= 30) {
                        resolve(betterPosition);
                        return;
                    }

                    window.setTimeout(() => {
                        readBestLocation(attempt + 1, betterPosition).then(resolve, reject);
                    }, 700);
                }, reject, {
                    enableHighAccuracy: true,
                    timeout: 12000,
                    maximumAge: 0,
                });
            });

            const updateLocation = () => {
                if (!navigator.geolocation) {
                    setGpsStatus('Perangkat tidak mendukung GPS.');
                    return;
                }

                refreshButton.disabled = true;
                setGpsStatus('Membaca lokasi perangkat...');

                readBestLocation().then((position) => {
                    const {
                        latitude,
                        longitude,
                        accuracy
                    } = position.coords;
                    const distance = distanceInMeters(latitude, longitude);
                    const gpsUncertainty = Math.min(accuracy, 50);
                    const effectiveDistance = Math.max(0, distance - gpsUncertainty);

                    latitudeInput.value = latitude;
                    longitudeInput.value = longitude;
                    accuracyInput.value = accuracy;
                    coordinates.textContent = `${latitude.toFixed(6)}, ${longitude.toFixed(6)}`;
                    locationTitle.textContent = effectiveDistance <= maximumDistance ?
                        'LOKASI SESUAI AREA KANTOR' :
                        'LOKASI DI LUAR AREA KANTOR';
                    setGpsStatus(
                        `${Math.round(distance)} m dari titik kantor (akurasi ±${Math.round(accuracy)} m)`,
                        effectiveDistance <= maximumDistance
                    );
                    if (map && !userMarker) {
                        userMarker = L.marker([latitude, longitude])
                            .addTo(map)
                            .bindPopup('Lokasi perangkat Anda');
                    } else if (map) {
                        userMarker.setLatLng([latitude, longitude]);
                    }
                    if (map) {
                        map.setView([latitude, longitude], 17);
                    }
                    refreshButton.disabled = false;
                }).catch(() => {
                    latitudeInput.value = '';
                    longitudeInput.value = '';
                    accuracyInput.value = '';
                    setGpsStatus('Lokasi tidak dapat dibaca. Izinkan akses GPS lalu coba lagi.');
                    refreshButton.disabled = false;
                });
            };

            refreshButton.addEventListener('click', updateLocation);
            form.addEventListener('submit', (event) => {
                const selectedButton = document.querySelector(
                    `[data-presensi="${typeInput.value}"]`
                );
                if (selectedButton?.dataset.completed === 'true') {
                    event.preventDefault();
                    selectedButton.click();
                    return;
                }

                if (!latitudeInput.value || !longitudeInput.value || !accuracyInput.value) {
                    event.preventDefault();
                    setGpsStatus('Perbarui lokasi GPS sebelum mengirim presensi.');
                }
            });
        })();
    </script>
@endpush
