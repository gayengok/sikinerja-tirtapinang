@extends('layouts.app')

@section('title', 'Buat Laporan Harian')

@section('content')
    <div class="pegawai-page daily-report-form-page">

        <div class="daily-report-banner">
            <span><i class="fa-solid fa-circle" aria-hidden="true"></i> INPUT HARIAN <b aria-hidden="true">·</b>
                {{ auth()->user()->pegawai->jabatan->divisi->nama_divisi ?? (auth()->user()->role === 'atasan' ? 'Laporan Atasan' : 'Laporan Pegawai') }}</span>
            <h1>Buat Laporan Harian</h1>
            <p>Isi catatan kegiatan tugas dan realisasi laporan harian secara akurat.</p>
            <i class="fa-regular fa-file-lines daily-report-banner-icon"></i>
        </div>

        @if ($routePrefix === 'atasan')
            <nav class="daily-report-steps" aria-label="Tahapan pengisian laporan">
                <a href="#laporan-informasi" class="is-current">
                    <span>01</span>
                    <strong>Informasi laporan</strong>
                </a>
                <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                <a href="#laporan-realisasi">
                    <span>02</span>
                    <strong>Realisasi aktivitas</strong>
                </a>
            </nav>
        @endif

        @if (session('error'))
            <div class="alert alert-danger daily-report-alert">{{ session('error') }}</div>
        @endif
        @if ($routePrefix === 'atasan' && $laporanHariIni)
            <div class="daily-report-status-notice" role="status">
                <i class="fa-solid {{ $laporanHariIni->status === 'diajukan' ? 'fa-clock' : ($laporanHariIni->status === 'disetujui' ? 'fa-circle-check' : 'fa-circle-exclamation') }}"
                    aria-hidden="true"></i>
                <span>
                    @if ($laporanHariIni->status === 'diajukan')
                        Laporan sudah dikirim dan sedang menunggu verifikasi Kepala Bagian.
                    @elseif (in_array($laporanHariIni->status, ['disetujui', 'diverifikasi']))
                        Laporan sudah diverifikasi oleh Kepala Bagian.
                    @elseif ($laporanHariIni->status === 'ditolak')
                        Laporan perlu diperbaiki sebelum dikirim kembali.
                    @else
                        Laporan hari ini sudah tersimpan sebagai draft.
                    @endif
                </span>
            </div>
        @endif
        <form action="{{ route($routePrefix . '.laporan-harian.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <section class="daily-report-form-card" id="laporan-informasi">
                <div class="daily-report-card-title"><i class="fa-regular fa-file-lines"></i><strong>Informasi
                        Pelaporan</strong><span>Langkah 1 dari 2</span></div>
                <div class="daily-report-field daily-report-date-field">
                    <label for="tanggal">Tanggal Pelaporan <b>*</b></label>
                    <input type="hidden" name="tanggal" id="tanggal" value="{{ old('tanggal', $tanggalTerpilih) }}">
                    <div class="report-calendar" data-calendar
                        data-readonly="{{ $laporanHariIni ? 'true' : 'false' }}" data-reports='@json($laporanTanggal)'>
                        <div class="report-calendar-toolbar">
                            <button type="button" data-calendar-prev aria-label="Bulan sebelumnya"
                                @disabled($laporanHariIni)><i
                                    class="fa-solid fa-chevron-left"></i></button>
                            <strong data-calendar-label></strong>
                            <button type="button" data-calendar-next aria-label="Bulan berikutnya"
                                @disabled($laporanHariIni)><i
                                    class="fa-solid fa-chevron-right"></i></button>
                        </div>
                        <div class="report-calendar-weekdays" aria-hidden="true">
                            <span>Sen</span><span>Sel</span><span>Rab</span><span>Kam</span><span>Jum</span><span>Sab</span><span>Min</span>
                        </div>
                        <div class="report-calendar-grid" data-calendar-grid></div>
                    </div>
                    <div class="report-calendar-legend" aria-label="Keterangan tanda kalender">
                        <span class="report-calendar-legend-item is-approved">
                            <i class="fa-solid fa-check" aria-hidden="true"></i>
                            <span>Laporan disetujui</span>
                        </span>
                        <span class="report-calendar-legend-item is-not-approved">
                            <i class="fa-solid fa-circle" aria-hidden="true"></i>
                            <span>Laporan belum disetujui</span>
                        </span>
                        <span class="report-calendar-legend-item is-missing">
                            <i class="fa-solid fa-circle" aria-hidden="true"></i>
                            <span>Tidak ada laporan</span>
                        </span>
                    </div>
                    <small><i class="fa-regular fa-calendar"></i> Sesuai tanggal jadwal kehadiran shift kerja aktif.</small>
                    @error('tanggal')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>




                <div class="daily-report-field daily-report-note-field">
                    <label for="catatan">Catatan & Uraian Aktivitas</label>
                    <textarea name="catatan" id="catatan" rows="4" maxlength="1000"
                        placeholder="Jelaskan ringkas kegiatan lapangan, kendala jaringan, atau progres tugas hari ini..."
                        @readonly($laporanHariIni)>{{ old('catatan', $laporanHariIni?->catatan) }}</textarea>
                    <small>Contoh: Pemeriksaan debit air sambungan blok B. <em>0/1000 karakter</em></small>
                    @error('catatan')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>



                <div class="daily-report-field daily-report-evidence-field">
                    <label for="bukti_gambar">Bukti Kegiatan <span>(Opsional)</span></label>
                    <input type="file" name="bukti_gambar" id="bukti_gambar" accept="image/jpeg,image/png,image/webp"
                        class="@error('bukti_gambar') is-invalid @enderror" @disabled($laporanHariIni)>
                    @if ($laporanHariIni?->bukti_gambar)
                        <small>Bukti kegiatan sudah dilampirkan pada laporan ini.</small>
                    @else
                        <small><i class="fa-regular fa-image"></i> Upload JPG, PNG, atau WEBP maksimal 5 MB.</small>
                    @endif
                    @error('bukti_gambar')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>
            </section>

            <div class="daily-report-holiday-notice" data-holiday-notice @if (!$hariLiburTerpilih) hidden @endif>
                <div class="daily-report-holiday-icon"><i class="fa-regular fa-calendar-xmark"></i></div>
                <div>
                    <strong data-holiday-label>{{ $hariLiburTerpilih['label'] ?? 'Hari libur' }}</strong>
                    <span data-holiday-source>{{ $hariLiburTerpilih['source'] ?? 'Jadwal libur posisi' }}</span>
                    <p>Pengisian kehadiran dan laporan harian tidak perlu dilakukan pada tanggal ini.</p>
                </div>
                <span class="daily-report-holiday-badge">Hari Libur</span>
            </div>

            <div data-report-fields @if ($hariLiburTerpilih) hidden @endif>
                <section class="daily-report-form-card" id="laporan-realisasi">
                    <div class="daily-report-card-title"><i class="fa-solid fa-chart-simple"></i><strong>Realisasi Laporan
                            Harian</strong><span>{{ $templateLaporans->count() }} indikator</span></div>
                    <p class="daily-report-hint">Gunakan kolom input untuk mengisi realisasi pekerjaan Anda hari ini.</p>
                    @if ($templateLaporans->isEmpty())
                        <div class="alert alert-warning">Belum ada template laporan untuk divisi Anda. Hubungi Master untuk
                            mengaturnya.</div>
                    @else
                        <div class="daily-report-indicators">
                            @foreach ($templateLaporans as $index => $template)
                                <div class="daily-report-indicator">
                                    <div class="daily-report-indicator-label">
                                        <strong>{{ $template->nama_field }}</strong><span>{{ $template->satuan ?: 'Realisasi' }}</span>
                                    </div>
                                    @if ($template->tipe_field === 'dropdown')
                                        <select name="detail[{{ $index }}][nilai]"
                                            class="@error('detail.' . $index . '.nilai') is-invalid @enderror"
                                            @disabled($laporanHariIni)>
                                            <option value="">-- Pilih --</option>
                                            @foreach ($template->options as $option)
                                                <option value="{{ $option->label }}"
                                                    {{ old('detail.' . $index . '.nilai', $nilaiTersimpan->get($template->id)?->nilai) === $option->label ? 'selected' : '' }}>
                                                    {{ $option->label }}</option>
                                            @endforeach
                                        </select>
                                    @else
                                        <input type="{{ $template->tipe_field === 'angka' ? 'number' : 'text' }}"
                                            {{ $template->tipe_field === 'angka' ? 'step=0.01' : '' }}
                                            name="detail[{{ $index }}][nilai]"
                                            value="{{ old('detail.' . $index . '.nilai', $nilaiTersimpan->get($template->id)?->nilai) }}" placeholder="Masukkan nilai"
                                            @readonly($laporanHariIni)
                                            class="@error('detail.' . $index . '.nilai') is-invalid @enderror">
                                    @endif
                                    <input type="hidden" name="detail[{{ $index }}][master_template_laporan_id]"
                                        value="{{ $template->id }}">
                                    @error('detail.' . $index . '.nilai')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endforeach
                        </div>
                        @if ($routePrefix !== 'atasan')
                            <div class="daily-report-submit-bar">
                                <a href="{{ route($routePrefix . '.laporan-harian.index') }}">Batal</a>
                                <button type="submit"><i class="fa-solid fa-floppy-disk"></i> Simpan</button>
                            </div>
                        @endif
                    @endif
                </section>
                @if ($routePrefix === 'atasan' && $templateLaporans->isNotEmpty() && !in_array($laporanHariIni?->status, ['disetujui', 'diverifikasi']))
                    <div class="daily-report-submit-bar">
                        @if ($laporanHariIni?->status === 'diajukan')
                            <button type="button" disabled><i class="fa-solid fa-clock"></i> Menunggu verifikasi Kepala Bagian</button>
                        @elseif (in_array($laporanHariIni?->status, ['draft', 'ditolak']))
                            <a href="{{ route($routePrefix . '.laporan-harian.edit', $laporanHariIni) }}">
                                {{ $laporanHariIni->status === 'ditolak' ? 'Perbaiki Laporan' : 'Lanjutkan Laporan' }}
                            </a>
                        @else
                            <button type="submit"><i class="fa-solid fa-paper-plane"></i> Kirim Laporan Harian</button>
                        @endif
                    </div>
                @endif
            </div>
        </form>
    </div>
@endsection




@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pegawai/laporan-harian/create.css') }}">
@endpush

@push('scripts')
    <script>
        (() => {
            const calendar = document.querySelector('[data-calendar]');
            if (!calendar) return;

            const input = document.getElementById('tanggal');
            const grid = calendar.querySelector('[data-calendar-grid]');
            const label = calendar.querySelector('[data-calendar-label]');
            const holidayNotice = document.querySelector('[data-holiday-notice]');
            const reportFields = document.querySelector('[data-report-fields]');
            const holidayLabel = holidayNotice?.querySelector('[data-holiday-label]');
            const holidaySource = holidayNotice?.querySelector('[data-holiday-source]');
            const reports = JSON.parse(calendar.dataset.reports || '{}');
            const reportIsReadOnly = calendar.dataset.readonly === 'true';
            const monthNames = [
                'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
            ];
            const selectedValue = input.value || new Date().toISOString().slice(0, 10);
            const selectedDate = new Date(`${selectedValue}T00:00:00`);
            let visibleYear = selectedDate.getFullYear();
            let visibleMonth = selectedDate.getMonth();

            const pad = (value) => String(value).padStart(2, '0');
            const formatDate = (year, month, day) => `${year}-${pad(month + 1)}-${pad(day)}`;
            const weekdayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

            function easterDate(year) {
                const date = new Date(Date.UTC(year, 2, 22));
                const days = (() => {
                    const a = year % 19;
                    const b = Math.floor(year / 100);
                    const c = year % 100;
                    const d = Math.floor(b / 4);
                    const e = b % 4;
                    const f = Math.floor((b + 8) / 25);
                    const g = Math.floor((b - f + 1) / 3);
                    const h = (19 * a + b - d - g + 15) % 30;
                    const i = Math.floor(c / 4);
                    const k = c % 4;
                    const l = (32 + 2 * e + 2 * i - h - k) % 7;
                    const m = Math.floor((a + 11 * h + 22 * l) / 451);
                    const month = Math.floor((h + l - 7 * m + 114) / 31);
                    const day = ((h + l - 7 * m + 114) % 31) + 1;
                    return Date.UTC(year, month - 1, day) - date.getTime();
                })();
                return new Date(date.getTime() + days);
            }

            function holidaysForYear(year) {
                const holidays = {};
                const add = (month, day, name) => {
                    holidays[formatDate(year, month - 1, day)] = name;
                };
                const addDate = (date, name) => {
                    holidays[formatDate(date.getUTCFullYear(), date.getUTCMonth(), date.getUTCDate())] = name;
                };

                [
                    [1, 1, 'Tahun Baru Masehi'],
                    [1, 16, 'Isra Mikraj Nabi Muhammad SAW'],
                    [2, 17, 'Tahun Baru Imlek'],
                    [3, 19, 'Hari Suci Nyepi'],
                    [3, 20, 'Idulfitri'],
                    [3, 21, 'Idulfitri'],
                    [5, 1, 'Hari Buruh Internasional'],
                    [5, 14, 'Kenaikan Yesus Kristus'],
                    [5, 27, 'Iduladha'],
                    [5, 31, 'Hari Waisak'],
                    [6, 1, 'Hari Lahir Pancasila'],
                    [6, 16, 'Tahun Baru Islam'],
                    [8, 17, 'Hari Kemerdekaan Republik Indonesia'],
                    [8, 25, 'Maulid Nabi Muhammad SAW'],
                    [12, 25, 'Hari Natal']
                ].forEach(([month, day, name]) => add(month, day, name));

                const easter = easterDate(year);
                const goodFriday = new Date(easter);
                goodFriday.setUTCDate(goodFriday.getUTCDate() - 2);
                const ascension = new Date(easter);
                ascension.setUTCDate(ascension.getUTCDate() - 39);
                if (year !== 2026) addDate(goodFriday, 'Wafat Yesus Kristus');
                addDate(easter, 'Kebangkitan Yesus Kristus');
                if (year !== 2026) addDate(ascension, 'Kenaikan Yesus Kristus');

                try {
                    const islamic = new Intl.DateTimeFormat('en-u-ca-islamic-umalqura', {
                        year: 'numeric',
                        month: 'numeric',
                        day: 'numeric'
                    });
                    const islamicDays = {};
                    const cursor = new Date(Date.UTC(year, 0, 1));
                    const end = new Date(Date.UTC(year, 11, 31));

                    while (cursor <= end) {
                        const parts = islamic.formatToParts(cursor);
                        const values = Object.fromEntries(parts
                            .filter((part) => ['year', 'month', 'day'].includes(part.type))
                            .map((part) => [part.type, Number(part.value)]));
                        const key = `${values.month}-${values.day}`;
                        islamicDays[key] = new Date(cursor);
                        cursor.setUTCDate(cursor.getUTCDate() + 1);
                    }

                    const islamicHolidays = {
                        '1-1': 'Tahun Baru Islam',
                        '7-27': 'Isra Mikraj Nabi Muhammad SAW',
                        '9-1': 'Awal Ramadan',
                        '10-1': 'Idulfitri',
                        '10-2': 'Idulfitri',
                        '12-10': 'Iduladha',
                        '3-12': 'Maulid Nabi Muhammad SAW'
                    };
                    Object.entries(islamicHolidays).forEach(([key, name]) => {
                        if (islamicDays[key]) addDate(islamicDays[key], name);
                    });
                } catch (error) {
                    // Browser lama tanpa kalender Umm al-Qura tetap menampilkan hari libur tetap.
                }

                return holidays;
            }

            function updateHolidayState(holidays) {
                if (!holidayNotice || !reportFields) return;

                const selected = new Date(`${input.value}T00:00:00`);
                const weekend = selected.getDay() === 0 || selected.getDay() === 6;
                const holidayName = holidays[input.value];
                const isHoliday = weekend || Boolean(holidayName);

                reportFields.hidden = isHoliday;
                holidayNotice.hidden = !isHoliday;

                if (!isHoliday) return;

                holidayLabel.textContent = holidayName ?
                    `Hari libur: ${holidayName}` :
                    `Hari libur akhir pekan (${weekdayNames[selected.getDay()]})`;
                holidaySource.textContent = holidayName ?
                    'Kalender libur nasional' :
                    'Jadwal libur posisi';
            }

            function render() {
                label.textContent = `${monthNames[visibleMonth]} ${visibleYear}`;
                grid.innerHTML = '';
                const holidays = holidaysForYear(visibleYear);
                updateHolidayState(holidays);

                const firstDay = new Date(visibleYear, visibleMonth, 1);
                const daysInMonth = new Date(visibleYear, visibleMonth + 1, 0).getDate();
                const mondayIndex = (firstDay.getDay() + 6) % 7;
                const today = new Date();
                const todayValue = formatDate(today.getFullYear(), today.getMonth(), today.getDate());

                for (let index = 0; index < mondayIndex; index += 1) {
                    const empty = document.createElement('span');
                    empty.className = 'report-calendar-day is-empty';
                    grid.appendChild(empty);
                }

                for (let day = 1; day <= daysInMonth; day += 1) {
                    const value = formatDate(visibleYear, visibleMonth, day);
                    const dayOfWeek = new Date(visibleYear, visibleMonth, day).getDay();
                    const button = document.createElement('button');
                    const marker = reports[value];
                    const isToday = value === todayValue;
                    const isHoliday = Boolean(holidays[value]) || dayOfWeek === 0 || dayOfWeek === 6;
                    const isSelectable = isToday || isHoliday;
                    button.type = 'button';
                    button.className = 'report-calendar-day';
                    button.innerHTML = `<span class="report-calendar-number">${day}</span>`;

                    if (isHoliday) button.classList.add('is-holiday');
                    if (reportIsReadOnly || !isSelectable) {
                        button.disabled = true;
                        button.classList.add('is-disabled');
                        button.setAttribute('aria-disabled', 'true');
                        button.setAttribute('title', 'Tanggal tidak dapat dipilih');

                        if (!reportIsReadOnly && !isHoliday) {
                            const lock = document.createElement('span');
                            lock.className = 'report-calendar-lock';
                            lock.innerHTML = '<i class="fa-solid fa-lock" aria-hidden="true"></i>';
                            lock.setAttribute('aria-label', 'Tanggal terkunci');
                            button.appendChild(lock);
                        }
                    }
                    if (holidays[value]) {
                        button.classList.add('is-national-holiday');
                        button.title = holidays[value];
                        button.setAttribute('aria-label',
                            `${day} ${monthNames[visibleMonth]} ${visibleYear}, ${holidays[value]}`);
                    }
                    if (value === input.value) button.classList.add('is-selected');
                    if (isToday) button.classList.add('is-today');

                    if (marker) {
                        const dot = document.createElement('span');
                        const isApproved = ['disetujui', 'diverifikasi'].includes(
                            String(marker).toLowerCase()
                        );
                        const markerClass = isApproved ? 'approved' : 'draft';
                        dot.className = `report-calendar-mark is-${markerClass}`;
                        dot.setAttribute(
                            'aria-label',
                            isApproved ? 'Laporan disetujui' : 'Laporan belum disetujui'
                        );
                        dot.title = isApproved ? 'Laporan disetujui' : 'Laporan belum disetujui';
                        if (isApproved) {
                            dot.innerHTML = '<i class="fa-solid fa-check" aria-hidden="true"></i>';
                        }
                        button.appendChild(dot);
                    } else if (!holidays[value] && dayOfWeek !== 0 && dayOfWeek !== 6 && value <= todayValue) {
                        const dot = document.createElement('span');
                        dot.className = 'report-calendar-mark is-missing';
                        dot.setAttribute('aria-label', 'Belum ada laporan');
                        dot.title = 'Belum ada laporan';
                        button.appendChild(dot);
                    }

                    button.addEventListener('click', () => {
                        if (!isSelectable) return;
                        input.value = value;
                        render();
                    });
                    grid.appendChild(button);
                }
            }

            calendar.querySelector('[data-calendar-prev]').addEventListener('click', () => {
                visibleMonth -= 1;
                if (visibleMonth < 0) {
                    visibleMonth = 11;
                    visibleYear -= 1;
                }
                render();
            });

            calendar.querySelector('[data-calendar-next]').addEventListener('click', () => {
                visibleMonth += 1;
                if (visibleMonth > 11) {
                    visibleMonth = 0;
                    visibleYear += 1;
                }
                render();
            });

            render();
        })();
    </script>
@endpush
