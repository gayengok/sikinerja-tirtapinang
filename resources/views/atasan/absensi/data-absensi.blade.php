@extends('layouts.app')

@section('title', 'Data Absensi Bawahan')

@section('content')
    @php
        $namaBulan = \Carbon\Carbon::create($tahun, $bulan, 1)->translatedFormat('F Y');
        $tanggal = (int) ($tanggal ?? now()->day);
        $jumlahHari = (int) ($jumlahHari ?? \Carbon\Carbon::create($tahun, $bulan, 1)->daysInMonth);
        $tanggal = max(1, min($tanggal, $jumlahHari));
        $tanggalDipilih = $tanggalDipilih ?? \Carbon\Carbon::create($tahun, $bulan, $tanggal);
        $chartStats = [
            ['label' => 'Hadir', 'count' => $ringkasan['hadir'] ?? 0, 'class' => 'is-present', 'icon' => 'fa-circle-check', 'description' => 'Tepat Waktu'],
            ['label' => 'Izin', 'count' => $ringkasan['izin'] ?? 0, 'class' => 'is-permission', 'icon' => 'fa-file-circle-check', 'description' => 'Tercatat Izin'],
            ['label' => 'Sakit', 'count' => $ringkasan['sakit'] ?? 0, 'class' => 'is-sick', 'icon' => 'fa-file-medical', 'description' => 'Tercatat Sakit'],
            ['label' => 'Alpa', 'count' => $ringkasan['alpa'] ?? 0, 'class' => 'is-absent', 'icon' => 'fa-circle-exclamation', 'description' => 'Tanpa Keterangan'],
            ['label' => 'Telat', 'count' => $ringkasan['telat'] ?? 0, 'class' => 'is-late', 'icon' => 'fa-clock', 'description' => 'Lewat Batas Jam'],
        ];
        $chartMax = max(1, max(array_column($chartStats, 'count')));
    @endphp

    <div class="atasan-attendance-page">
        <header class="atasan-attendance-heading">
            <div class="atasan-attendance-heading-copy">
                <span class="atasan-attendance-eyebrow"><i class="fa-solid fa-circle"></i> MONITORING KEHADIRAN</span>
                <h1>Data Absensi {{ $jenisPegawai }}</h1>
                <p>Pantau kehadiran {{ strtolower($jenisPegawai) }} yang berada di bawah tanggung jawab Anda.</p>
            </div>
            <div class="atasan-attendance-heading-actions">
                <span class="atasan-attendance-period"><i class="fa-regular fa-calendar"></i> {{ $namaBulan }}</span>
            </div>
        </header>

        <section class="atasan-attendance-summary" aria-labelledby="attendance-summary-title">
            <div class="atasan-attendance-summary-head">
                <div>
                    <span class="atasan-attendance-section-label"><i class="fa-solid fa-chart-simple"></i> RINGKASAN KEHADIRAN</span>
                    <h2 id="attendance-summary-title">Statistik Presensi Tim</h2>
                    <p>Akumulasi data kehadiran pada periode {{ $namaBulan }}.</p>
                </div>
                <span class="atasan-attendance-employee-count"><i class="fa-solid fa-users"></i> {{ $jumlahPegawai }} {{ $jenisPegawai }} Terdaftar</span>
            </div>
            <div class="attendance-summary-cards">
                @foreach ($chartStats as $stat)
                    <div class="atasan-attendance-summary-stat {{ $stat['class'] }}">
                        <span class="atasan-attendance-stat-icon"><i class="fa-solid {{ $stat['icon'] }}"></i></span>
                        <div class="attendance-stat-copy">
                            <span class="atasan-attendance-stat-name">{{ $stat['label'] }}</span>
                            <strong>{{ number_format($stat['count'], 0, ',', '.') }}</strong>
                            <small>{{ $stat['description'] }}</small>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="attendance-month-chart">
                <div class="attendance-month-chart-heading">
                    <div>
                        <strong><i class="fa-solid fa-chart-column"></i> Grafik Kehadiran Harian</strong>
                        <p>Klik batang atau tanggal untuk membuka rekap presensi hari tersebut.</p>
                    </div>
                    <span class="atasan-attendance-total-record">{{ $hariKerja }} Hari Kerja</span>
                </div>
                <div class="attendance-chart-legend" aria-label="Keterangan grafik">
                    @foreach ($chartStats as $stat)
                        <span class="{{ $stat['class'] }}"><i></i>{{ $stat['label'] }}</span>
                    @endforeach
                </div>
                @php
                    $dailyMax = max(1, collect($grafikHarian ?? [])->max(fn ($item) => array_sum($item['counts'] ?? [])) ?? 0);
                    $dailyColors = ['hadir' => '#10a779', 'izin' => '#0b94c6', 'sakit' => '#e9a62b', 'alpa' => '#e85d75', 'telat' => '#f28a45'];
                @endphp
                <div class="attendance-chart-scroll" id="attendanceChartScroll" tabindex="0" aria-label="Grafik presensi harian, geser horizontal untuk tanggal lainnya">
                    <div class="attendance-chart-days">
                        @foreach (($grafikHarian ?? []) as $dayData)
                            @php
                                $dayNumber = (int) $dayData['day'];
                                $counts = $dayData['counts'];
                                $dailyTotal = array_sum($counts);
                                $dayDate = \Carbon\Carbon::create($tahun, $bulan, $dayNumber);
                            @endphp
                            <button type="button" class="attendance-chart-day {{ $dayNumber === $tanggal ? 'is-active' : '' }}" data-chart-day="{{ $dayNumber }}" aria-label="{{ $dayDate->translatedFormat('d F Y') }}, {{ $dailyTotal }} catatan presensi" aria-pressed="{{ $dayNumber === $tanggal ? 'true' : 'false' }}">
                                <span class="attendance-chart-total">{{ $dailyTotal }}</span>
                                <span class="attendance-chart-track">
                                    @foreach (['hadir', 'izin', 'sakit', 'alpa', 'telat'] as $key)
                                        @if (($counts[$key] ?? 0) > 0)
                                            <span class="attendance-chart-segment" style="height: {{ (($counts[$key] ?? 0) / $dailyMax) * 100 }}%; background: {{ $dailyColors[$key] }}" title="{{ ucfirst($key) }}: {{ $counts[$key] }}"></span>
                                        @endif
                                    @endforeach
                                </span>
                                <span class="attendance-chart-weekday">{{ $dayDate->translatedFormat('D') }}</span>
                                <strong>{{ $dayNumber }}</strong>
                            </button>
                        @endforeach
                    </div>
                </div>
                <div class="attendance-chart-footer"><span><i class="fa-solid fa-hand-pointer"></i> Pilih tanggal pada grafik untuk melihat detailnya</span><span>{{ $namaBulan }}</span></div>
            </div>
            @if (($ringkasan['cuti'] ?? 0) > 0 || ($ringkasan['dinas_luar'] ?? 0) > 0)
                <div class="atasan-attendance-additional">
                    @if (($ringkasan['cuti'] ?? 0) > 0)<span><i class="fa-solid fa-calendar-check"></i> Cuti: {{ $ringkasan['cuti'] }}</span>@endif
                    @if (($ringkasan['dinas_luar'] ?? 0) > 0)<span><i class="fa-solid fa-briefcase"></i> Dinas Luar: {{ $ringkasan['dinas_luar'] }}</span>@endif
                </div>
            @endif
        </section>

        <section class="atasan-attendance-directory" aria-labelledby="attendance-directory-title">
            <div class="atasan-attendance-directory-head">
                <div>
                    <span class="atasan-attendance-section-label"><i class="fa-solid fa-users-viewfinder"></i> DATA PEGAWAI</span>
                    <h2 id="attendance-directory-title">Pantau Kehadiran Pegawai</h2>
                    <p>Pilih tanggal untuk melihat jam masuk, jam pulang, status kehadiran, dan verifikasi.</p>
                </div>
            </div>
            <form class="atasan-attendance-filter" id="attendanceFilterForm" method="GET" action="{{ route($routePrefix . '.absensi.index') }}">
                <input type="hidden" name="tanggal" id="attendanceTanggalInput" value="{{ $tanggal }}">
                <label class="atasan-attendance-search">
                    <span>Cari Pegawai</span>
                    <div class="atasan-attendance-search-field">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="search" name="search" id="attendanceSearchInput" value="{{ $search }}" maxlength="150" autocomplete="off" placeholder="Nama, NIP, jabatan, atau divisi...">
                    </div>
                </label>
                <div class="atasan-attendance-filter-row">
                    <label><span>Bulan</span>
                        <select name="bulan" id="attendanceBulanInput">
                            @for ($month = 1; $month <= 12; $month++)
                                <option value="{{ $month }}" @selected($bulan === $month)>{{ \Carbon\Carbon::create(2026, $month, 1)->translatedFormat('F') }}</option>
                            @endfor
                        </select>
                    </label>
                    <label><span>Tahun</span>
                        <select name="tahun" id="attendanceTahunInput">
                            @for ($year = min(now()->year - 5, $tahun); $year <= max(now()->year + 1, $tahun); $year++)
                                <option value="{{ $year }}" @selected($tahun === $year)>{{ $year }}</option>
                            @endfor
                        </select>
                    </label>
                    <label><span>Status</span>
                        <select name="status" id="attendanceStatusInput">
                            <option value="">Semua Status</option>
                            @foreach (['hadir'=>'Hadir','shift_1'=>'Shift 1 (Operator)','shift_2'=>'Shift 2 (Operator)','shift_3'=>'Shift 3 (Operator)','izin'=>'Izin','sakit'=>'Sakit','alpa'=>'Alpa','tidak_hadir'=>'Tidak Hadir','cuti'=>'Cuti','dinas_luar'=>'Dinas Luar'] as $statusValue => $statusLabel)
                                <option value="{{ $statusValue }}" @selected($status === $statusValue)>{{ $statusLabel }}</option>
                            @endforeach
                        </select>
                    </label>
                    <button type="submit" class="atasan-attendance-filter-button"><i class="fa-solid fa-filter"></i> Terapkan</button>
                    <a class="atasan-attendance-filter-reset" id="attendanceResetButton" href="{{ route($routePrefix . '.absensi.index') }}" aria-label="Reset filter" title="Reset filter"><i class="fa-solid fa-rotate-left"></i></a>
                </div>
            </form>

            <section class="attendance-daily-navigation" aria-labelledby="attendanceDailyTitle">
                <div class="attendance-daily-heading">
                    <div>
                        <span class="attendance-daily-eyebrow"><i class="fa-regular fa-calendar-days"></i> NAVIGASI PRESENSI</span>
                        <h2 id="attendanceDailyTitle">Presensi Harian</h2>
                        <p>Pilih tanggal atau geser untuk melihat presensi hari lainnya.</p>
                    </div>
                    <div class="attendance-daily-controls">
                        <button type="button" id="attendancePreviousDay" class="attendance-day-arrow" aria-label="Hari sebelumnya"><i class="fa-solid fa-chevron-left"></i></button>
                        <span class="attendance-current-date" id="attendanceCurrentDate">{{ $tanggalDipilih->translatedFormat('d F Y') }}</span>
                        <button type="button" id="attendanceNextDay" class="attendance-day-arrow" aria-label="Hari berikutnya"><i class="fa-solid fa-chevron-right"></i></button>
                    </div>
                </div>
                <div class="attendance-date-strip" id="attendanceDateStrip" aria-label="Pilih tanggal absensi">
                    @for ($day = 1; $day <= $jumlahHari; $day++)
                        @php $dateItem = \Carbon\Carbon::create($tahun, $bulan, $day); @endphp
                        <button type="button" class="attendance-date-item {{ $day === $tanggal ? 'is-active' : '' }} {{ $dateItem->isToday() ? 'is-today' : '' }}" data-day="{{ $day }}" aria-pressed="{{ $day === $tanggal ? 'true' : 'false' }}">
                            <span class="attendance-date-weekday">{{ $dateItem->translatedFormat('D') }}</span>
                            <strong>{{ $day }}</strong>
                            <small>{{ $dateItem->isToday() ? 'Hari ini' : $dateItem->translatedFormat('M') }}</small>
                        </button>
                    @endfor
                </div>
                <div class="attendance-daily-bottom">
                    <span><i class="fa-solid fa-circle-info"></i> Data berdasarkan tanggal yang dipilih</span>
                    <button type="button" class="attendance-today-button" id="attendanceTodayButton"><i class="fa-solid fa-calendar-day"></i> Hari Ini</button>
                </div>
            </section>

            <div id="attendanceResults" class="attendance-results" aria-live="polite">
                @include('atasan.absensi.partials.list')
            </div>
        </section>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/atasan/absensi/data-absensi.css') }}?v=6">
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('attendanceFilterForm');
            const results = document.getElementById('attendanceResults');
            const searchInput = document.getElementById('attendanceSearchInput');
            const bulanInput = document.getElementById('attendanceBulanInput');
            const tahunInput = document.getElementById('attendanceTahunInput');
            const statusInput = document.getElementById('attendanceStatusInput');
            const tanggalInput = document.getElementById('attendanceTanggalInput');

            const strip = document.getElementById('attendanceDateStrip');
            const previousButton = document.getElementById('attendancePreviousDay');
            const nextButton = document.getElementById('attendanceNextDay');
            const todayButton = document.getElementById('attendanceTodayButton');
            const currentDate = document.getElementById('attendanceCurrentDate');

            if (!form || !results || !strip) {
                return;
            }

            const endpoint = form.action;
            const today = {
                day: {{ now()->day }},
                month: {{ now()->month }},
                year: {{ now()->year }}
            };

            const monthNames = [
                'Januari', 'Februari', 'Maret', 'April',
                'Mei', 'Juni', 'Juli', 'Agustus',
                'September', 'Oktober', 'November', 'Desember'
            ];

            const weekdayNames = [
                'Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'
            ];

            let debounceTimer = null;
            let activeController = null;
            let requestNumber = 0;

            function daysInMonth() {
                return new Date(
                    Number(tahunInput.value),
                    Number(bulanInput.value),
                    0
                ).getDate();
            }

            function selectedDay() {
                return Number(tanggalInput.value);
            }

            function updateDateLabel() {
                const day = selectedDay();
                const month = Number(bulanInput.value);
                const year = Number(tahunInput.value);

                currentDate.textContent =
                    `${day} ${monthNames[month - 1]} ${year}`;

                previousButton.disabled = day <= 1;
                nextButton.disabled = day >= daysInMonth();
            }

            function scrollChartToActive(active) {
                const container = document.getElementById('attendanceChartScroll');
                if (!container || !active) return;
                const containerRect = container.getBoundingClientRect();
                const activeRect = active.getBoundingClientRect();
                const offset = activeRect.left - containerRect.left;
                const left = container.scrollLeft + offset - (container.clientWidth - activeRect.width) / 2;
                container.scrollTo({ left: Math.max(0, left), behavior: 'smooth' });
            }

            function renderDateStrip() {
                const month = Number(bulanInput.value);
                const year = Number(tahunInput.value);
                const totalDays = daysInMonth();
                const currentDay = selectedDay();

                const fragment = document.createDocumentFragment();

                for (let day = 1; day <= totalDays; day++) {
                    const date = new Date(year, month - 1, day);

                    const isToday =
                        day === today.day &&
                        month === today.month &&
                        year === today.year;

                    const button = document.createElement('button');

                    button.type = 'button';
                    button.dataset.day = String(day);

                    button.className =
                        'attendance-date-item' +
                        (day === currentDay ? ' is-active' : '') +
                        (isToday ? ' is-today' : '');

                    button.setAttribute(
                        'aria-pressed',
                        day === currentDay ? 'true' : 'false'
                    );

                    const weekday = document.createElement('span');
                    weekday.className = 'attendance-date-weekday';
                    weekday.textContent = weekdayNames[date.getDay()];

                    const number = document.createElement('strong');
                    number.textContent = String(day);

                    const subtitle = document.createElement('small');
                    subtitle.textContent = isToday
                        ? 'Hari ini'
                        : monthNames[month - 1].slice(0, 3);

                    button.append(weekday, number, subtitle);
                    fragment.appendChild(button);
                }

                strip.replaceChildren(fragment);
                updateDateLabel();
                document.querySelectorAll('[data-chart-day]').forEach(button => {
                    const active = Number(button.dataset.chartDay) === selectedDay();
                    button.classList.toggle('is-active', active);
                    button.setAttribute('aria-pressed', active ? 'true' : 'false');
                });
                const chartActive = document.querySelector('.attendance-chart-day.is-active');
                if (chartActive) scrollChartToActive(chartActive);
                scrollToActiveDate();
            }

            function scrollToActiveDate() {
                const active = strip.querySelector('.attendance-date-item.is-active');

                if (!active) {
                    return;
                }

                const stripRect = strip.getBoundingClientRect();
                const activeRect = active.getBoundingClientRect();
                const targetLeft = strip.scrollLeft + activeRect.left - stripRect.left -
                    (strip.clientWidth - activeRect.width) / 2;

                strip.scrollTo({
                    left: Math.max(0, targetLeft),
                    behavior: 'smooth'
                });
            }

            function syncSelectedDate() {
                const day = selectedDay();

                strip.querySelectorAll('.attendance-date-item').forEach(button => {
                    const active = Number(button.dataset.day) === day;

                    button.classList.toggle('is-active', active);
                    button.setAttribute('aria-pressed', active ? 'true' : 'false');
                });

                updateDateLabel();
                document.querySelectorAll('[data-chart-day]').forEach(button => {
                    const active = Number(button.dataset.chartDay) === day;
                    button.classList.toggle('is-active', active);
                    button.setAttribute('aria-pressed', active ? 'true' : 'false');
                });
                const chartActive = document.querySelector('.attendance-chart-day.is-active');
                if (chartActive) scrollChartToActive(chartActive);
                scrollToActiveDate();
            }

            function buildUrl(page = null) {
                const url = new URL(endpoint, window.location.origin);
                const params = new URLSearchParams(new FormData(form));

                if (!params.get('status')) {
                    params.delete('status');
                }

                if (!params.get('search')) {
                    params.delete('search');
                }

                if (page !== null) {
                    params.set('page', String(page));
                }

                url.search = params.toString();

                return url;
            }

            function setLoading(loading) {
                results.classList.toggle('is-loading', loading);
                results.setAttribute('aria-busy', loading ? 'true' : 'false');
            }

            async function loadResults(page = null) {
                if (activeController) {
                    activeController.abort();
                }

                activeController = new AbortController();

                const currentRequest = ++requestNumber;
                const url = buildUrl(page);

                setLoading(true);

                try {
                    const response = await fetch(url.toString(), {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        credentials: 'same-origin',
                        signal: activeController.signal
                    });

                    if (!response.ok) {
                        throw new Error('Gagal memuat data absensi.');
                    }

                    const data = await response.json();

                    if (currentRequest !== requestNumber) {
                        return;
                    }

                    if (!data.success) {
                        throw new Error('Respons data absensi tidak valid.');
                    }

                    results.innerHTML = data.html;

                    window.history.replaceState(
                        {},
                        '',
                        url.pathname + url.search
                    );

                } catch (error) {
                    if (error.name === 'AbortError') {
                        return;
                    }

                    if (currentRequest === requestNumber) {
                        results.innerHTML = `
                            <div class="attendance-ajax-error">
                                <i class="fa-solid fa-circle-exclamation"></i>
                                <strong>Data absensi gagal dimuat</strong>
                                <span>Periksa koneksi lalu coba kembali.</span>
                                <button type="button" id="attendanceRetry">
                                    Coba Lagi
                                </button>
                            </div>
                        `;
                    }
                } finally {
                    if (currentRequest === requestNumber) {
                        setLoading(false);
                    }
                }
            }

            function changeDay(day) {
                const validDay = Math.max(
                    1,
                    Math.min(Number(day), daysInMonth())
                );

                if (validDay === selectedDay()) {
                    return;
                }

                tanggalInput.value = String(validDay);

                syncSelectedDate();
                loadResults();
            }

            function resetDayForPeriod() {
                const month = Number(bulanInput.value);
                const year = Number(tahunInput.value);

                tanggalInput.value = (
                    month === today.month && year === today.year
                ) ? String(today.day) : '1';

                renderDateStrip();
            }

            strip.addEventListener('click', function (event) {
                const button = event.target.closest('[data-day]');

                if (button) {
                    changeDay(Number(button.dataset.day));
                }
            });

            const chartScroll = document.getElementById('attendanceChartScroll');
            if (chartScroll) {
                chartScroll.addEventListener('click', function (event) {
                    const button = event.target.closest('[data-chart-day]');
                    if (button) changeDay(Number(button.dataset.chartDay));
                });
                const initialChartDay = chartScroll.querySelector('.attendance-chart-day.is-active');
                if (initialChartDay) {
                    requestAnimationFrame(() => scrollChartToActive(initialChartDay));
                }
            }

            previousButton.addEventListener('click', function () {
                changeDay(selectedDay() - 1);
            });

            nextButton.addEventListener('click', function () {
                changeDay(selectedDay() + 1);
            });

            todayButton.addEventListener('click', function () {
                const monthOption = bulanInput.querySelector(
                    `option[value="${today.month}"]`
                );

                const yearOption = tahunInput.querySelector(
                    `option[value="${today.year}"]`
                );

                if (!monthOption || !yearOption) {
                    return;
                }

                const periodChanged = Number(bulanInput.value) !== today.month ||
                    Number(tahunInput.value) !== today.year;
                bulanInput.value = String(today.month);
                tahunInput.value = String(today.year);
                tanggalInput.value = String(today.day);

                if (periodChanged) {
                    window.location.assign(buildUrl().toString());
                    return;
                }
                renderDateStrip();
                loadResults();
            });

            searchInput.addEventListener('input', function () {
                clearTimeout(debounceTimer);

                debounceTimer = setTimeout(function () {
                    loadResults();
                }, 350);
            });

            statusInput.addEventListener('change', function () {
                loadResults();
            });

            function reloadPeriod() {
                resetDayForPeriod();
                window.location.assign(buildUrl().toString());
            }

            bulanInput.addEventListener('change', reloadPeriod);
            tahunInput.addEventListener('change', reloadPeriod);

            form.addEventListener('submit', function (event) {
                event.preventDefault();
                clearTimeout(debounceTimer);
                loadResults();
            });

            results.addEventListener('click', function (event) {
                const paginationLink = event.target.closest(
                    '.atasan-attendance-pagination a[href]'
                );

                if (paginationLink) {
                    event.preventDefault();

                    const url = new URL(paginationLink.href);
                    const page = Number(url.searchParams.get('page') || 1);

                    loadResults(page);
                    return;
                }

                if (event.target.closest('#attendanceRetry')) {
                    loadResults();
                }
            });
let touchStartX = 0;
            let touchStartY = 0;

            results.addEventListener('touchstart', function (event) {
                if (event.touches.length !== 1) {
                    return;
                }

                touchStartX = event.touches[0].clientX;
                touchStartY = event.touches[0].clientY;
            }, {
                passive: true
            });

            results.addEventListener('touchend', function (event) {
                if (event.changedTouches.length !== 1) {
                    return;
                }

                if (event.target.closest('a, button, input, select')) {
                    return;
                }

                const deltaX =
                    event.changedTouches[0].clientX - touchStartX;

                const deltaY =
                    event.changedTouches[0].clientY - touchStartY;

                if (
                    Math.abs(deltaX) < 70 ||
                    Math.abs(deltaX) <= Math.abs(deltaY) * 1.4
                ) {
                    return;
                }

                if (deltaX > 0) {
                    changeDay(selectedDay() - 1);
                } else {
                    changeDay(selectedDay() + 1);
                }
            }, {
                passive: true
            });
window.addEventListener('popstate', function () {
                const params = new URLSearchParams(window.location.search);

                bulanInput.value = params.get('bulan') || String(today.month);
                tahunInput.value = params.get('tahun') || String(today.year);
                statusInput.value = params.get('status') || '';
                searchInput.value = params.get('search') || '';

                tanggalInput.value = params.get('tanggal') ||
                    (
                        Number(bulanInput.value) === today.month &&
                        Number(tahunInput.value) === today.year
                            ? String(today.day)
                            : '1'
                    );

                tanggalInput.value = String(
                    Math.max(1, Math.min(selectedDay(), daysInMonth()))
                );

                renderDateStrip();

                loadResults(Number(params.get('page') || 1));
            });

            renderDateStrip();
        });
    </script>
@endpush
