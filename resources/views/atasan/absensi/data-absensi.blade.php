@extends('layouts.app')

@section('title', 'Data Absensi Bawahan')

@section('content')
    <div class="atasan-attendance-page">

        <header class="atasan-attendance-heading">
            <div>
                <h1>
                    <span class="atasan-attendance-heading-icon"><i class="fa-solid fa-clipboard-check"></i></span>
                    Data Absensi {{ $jenisPegawai }}
                </h1>
                <p>Pantau kehadiran {{ strtolower($jenisPegawai) }} yang berada di bawah tanggung jawab Anda.</p>
            </div>
        </header>




        @php
            $chartStats = [
                ['label' => 'Hadir', 'count' => $ringkasan['hadir'], 'class' => 'chart-present'],
                ['label' => 'Izin', 'count' => $ringkasan['izin'], 'class' => 'chart-permission'],
                ['label' => 'Sakit', 'count' => $ringkasan['sakit'], 'class' => 'chart-sick'],
                ['label' => 'Alpa', 'count' => $ringkasan['alpa'], 'class' => 'chart-absent'],
                ['label' => 'Telat', 'count' => $ringkasan['telat'], 'class' => 'chart-late'],
            ];
            $chartMax = max(5, (int) (ceil(max(array_column($chartStats, 'count')) / 5) * 5));
            $chartMid = (int) ceil($chartMax / 2);
        @endphp
        <section class="atasan-attendance-summary" aria-labelledby="attendance-summary-title">
            <div class="atasan-attendance-summary-head">
                <span id="attendance-summary-title"><i class="fa-solid fa-chart-simple"></i> RINGKASAN KEHADIRAN</span>
                <span class="atasan-attendance-employee-count"><i class="fa-solid fa-users"></i>
                    {{ $jumlahPegawai }} {{ $jenisPegawai }} Terdaftar</span>
            </div>
            <div class="atasan-attendance-summary-grid">
                <div class="atasan-attendance-chart">
                    <div class="atasan-attendance-chart-head">
                        <strong><i class="fa-solid fa-circle"></i> Statistik Presensi</strong>
                        <span>Total: {{ $hariKerja }} Hari Kerja</span>
                    </div>
                    <small class="atasan-attendance-chart-period">Periode
                        {{ \Carbon\Carbon::create($tahun, $bulan, 1)->translatedFormat('F Y') }}</small>
                    <div class="atasan-attendance-chart-body" role="img"
                        aria-label="Grafik presensi: Hadir {{ $ringkasan['hadir'] }}, Izin {{ $ringkasan['izin'] }}, Sakit {{ $ringkasan['sakit'] }}, Alpa {{ $ringkasan['alpa'] }}, Telat {{ $ringkasan['telat'] }}">
                        <div class="atasan-attendance-chart-axis" aria-hidden="true">
                            <span>{{ $chartMax }}</span>
                            <span>{{ $chartMid }}</span>
                            <span>0</span>
                        </div>
                        <div class="atasan-attendance-chart-bars">
                            <span class="chart-gridline chart-gridline-top" aria-hidden="true"></span>
                            <span class="chart-gridline chart-gridline-middle" aria-hidden="true"></span>
                            <span class="chart-gridline chart-gridline-bottom" aria-hidden="true"></span>
                            @foreach ($chartStats as $stat)
                                @php($barHeight = $stat['count'] > 0 ? ($stat['count'] / $chartMax) * 100 : 0)
                                <div class="atasan-attendance-chart-column">
                                    <div class="atasan-attendance-chart-bar-area">
                                        <strong style="--bar-height: {{ $barHeight }}%">{{ $stat['count'] }}</strong>
                                        <span class="atasan-attendance-chart-bar {{ $stat['class'] }}"
                                            style="--bar-height: {{ $barHeight }}%"></span>
                                    </div>
                                    <span class="atasan-attendance-chart-label">{{ $stat['label'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @foreach ($chartStats as $stat)
                    <div class="atasan-attendance-summary-stat {{ $stat['class'] }}">
                        <strong>{{ $stat['count'] }}</strong>
                        <span>{{ $stat['label'] }}</span>
                        <small>
                            @switch($stat['label'])
                                @case('Hadir')
                                    <i class="fa-solid fa-circle-check"></i> Tepat Waktu
                                @break

                                @case('Izin')
                                    <i class="fa-solid fa-file-circle-check"></i> Formulir Disetujui
                                @break

                                @case('Sakit')
                                    <i class="fa-solid fa-file-medical"></i> Surat Dokter
                                @break

                                @case('Alpa')
                                    <i class="fa-solid fa-circle-exclamation"></i> Tanpa Keterangan
                                @break

                                @case('Telat')
                                    <i class="fa-regular fa-clock"></i> Lewat Batas Jam
                                @break
                            @endswitch
                        </small>
                    </div>
                @endforeach
            </div>
        </section>

        <aside class="atasan-attendance-reminder">
            <span class="atasan-attendance-reminder-icon"><i class="fa-solid fa-bell"></i></span>
            <div>
                <strong>Pantau Kehadiran Pegawai</strong>
                <p>Periksa jam masuk, jam pulang, dan lokasi presensi untuk periode yang dipilih.</p>
            </div>
            <span class="atasan-attendance-reminder-period">
                {{ \Carbon\Carbon::create($tahun, $bulan, 1)->translatedFormat('F Y') }}
            </span>
        </aside>

        <form class="atasan-attendance-filter" method="GET" action="{{ route($routePrefix . '.absensi.index') }}">
            <label class="atasan-attendance-search">
                <span>Pencarian Pegawai</span>
                <div><i class="fa-solid fa-magnifying-glass"></i>
                    <input type="search" name="search" value="{{ $search }}"
                        placeholder="Cari nama, NIK, atau jabatan...">
                </div>
            </label>
            <div class="atasan-attendance-filter-row">
                <label><span>Bulan</span><select name="bulan">
                        @for ($month = 1; $month <= 12; $month++)
                            <option value="{{ $month }}" @selected($bulan === $month)>
                                {{ \Carbon\Carbon::create()->month($month)->translatedFormat('F') }}</option>
                        @endfor
                    </select></label>
                <label><span>Tahun</span><select name="tahun">
                        @for ($year = now()->year - 2; $year <= now()->year + 1; $year++)
                            <option value="{{ $year }}" @selected($tahun === $year)>{{ $year }}</option>
                        @endfor
                    </select></label>
                <label><span>Status</span><select name="status">
                        <option value="">Semua</option>
                        <option value="hadir" @selected($status === 'hadir')>Hadir</option>
                        <option value="izin" @selected($status === 'izin')>Izin</option>
                        <option value="sakit" @selected($status === 'sakit')>Sakit</option>
                        <option value="alpa" @selected($status === 'alpa')>Alpa</option>
                        <option value="tidak_hadir" @selected($status === 'tidak_hadir')>Tidak hadir</option>
                        <option value="cuti" @selected($status === 'cuti')>Cuti</option>
                        <option value="dinas_luar" @selected($status === 'dinas_luar')>Dinas luar</option>
                    </select></label>
                <button type="submit"><i class="fa-solid fa-filter"></i> Terapkan</button>
                @if ($search || $status)
                    <a class="atasan-attendance-filter-reset"
                        href="{{ route($routePrefix . '.absensi.index', ['bulan' => $bulan, 'tahun' => $tahun]) }}"
                        aria-label="Reset pencarian dan status"><i class="fa-solid fa-rotate-left"></i></a>
                @endif
            </div>
        </form>

        <div class="atasan-attendance-list-heading">
            <div>
                <h2>Rekap Presensi Harian Pegawai</h2>
                <p>Daftar presensi {{ strtolower($jenisPegawai) }} terdaftar pada unit kerja Anda.</p>
            </div>
            <span>Menampilkan {{ $pegawais->firstItem() ?? 0 }}–{{ $pegawais->lastItem() ?? 0 }}
                dari {{ $pegawais->total() }} pegawai</span>
        </div>

        <div class="atasan-attendance-list atasan-attendance-mobile-list">
            @forelse ($pegawais as $pegawai)
                @php($absensi = $pegawai->absensis->first())
                @php($statusCounts = $pegawai->absensis->groupBy('status')->map->count())
                @php(
    $statusLabel = $absensi
        ? match ($absensi->status) {
            'hadir' => 'Hadir',
            'izin' => 'Izin',
            'cuti' => 'Cuti',
            'sakit' => 'Sakit',
            'dinas_luar' => 'Dinas Luar',
            'alpa', 'tidak_hadir' => 'Tidak Hadir',
            default => 'Tidak Hadir'
        }
        : 'Belum ada data'
)
                @php(
    $statusClass = $absensi
        ? match ($absensi->status) {
            'hadir' => 'is-present',
            'izin' => 'is-permission',
            'cuti' => 'is-leave',
            'sakit' => 'is-sick',
            'dinas_luar' => 'is-duty',
            default => 'is-absent'
        }
        : 'is-empty'
)
                <article class="atasan-attendance-card">
                    <div class="atasan-attendance-employee">
                        <span class="atasan-attendance-avatar">{{ strtoupper(substr($pegawai->nama, 0, 2)) }}</span>
                        <div>
                            <h3>{{ $pegawai->nama }}</h3>
                            <p>{{ $pegawai->jabatan->nama_jabatan ?? 'Pegawai' }}</p>
                        </div>
                        <span class="atasan-attendance-status {{ $statusClass }}"><i class="fa-solid fa-circle"></i>
                            {{ $statusLabel }}</span>
                    </div>
                    <div class="atasan-attendance-data">
                        <div><span><i class="fa-regular fa-clock"></i> Jam
                                masuk</span><strong>{{ $absensi?->jam_masuk ? \Carbon\Carbon::parse($absensi->jam_masuk)->format('H:i') : '-' }}</strong>
                        </div>
                        <div><span><i class="fa-solid fa-right-from-bracket"></i> Jam
                                pulang</span><strong>{{ $absensi?->jam_pulang ? \Carbon\Carbon::parse($absensi->jam_pulang)->format('H:i') : '-' }}</strong>
                        </div>
                        <div><span><i class="fa-solid fa-location-dot"></i> Jarak
                                GPS</span><strong>{{ $absensi?->jarak_lokasi !== null ? number_format($absensi->jarak_lokasi, 0, ',', '.') . ' m' : '-' }}</strong>
                        </div>
                    </div>
                    @if ($absensi)
                        <p class="atasan-attendance-location {{ $absensi->di_luar_kantor ? 'is-outside' : 'is-inside' }}">
                            <i
                                class="fa-solid {{ $absensi->di_luar_kantor ? 'fa-triangle-exclamation' : 'fa-circle-check' }}"></i>
                            Lokasi terdeteksi {{ $absensi->di_luar_kantor ? 'di luar' : 'di dalam' }} area kantor
                        </p>
                    @endif
                    <div class="atasan-attendance-statuses">
                        <span class="atasan-attendance-statuses-label">Rekap status periode ini</span>
                        <div class="atasan-attendance-statuses-list">
                            @foreach ([
            'hadir' => ['Hadir', 'is-present'],
            'izin' => ['Izin', 'is-permission'],
            'sakit' => ['Sakit', 'is-sick'],
            'cuti' => ['Cuti', 'is-leave'],
            'dinas_luar' => ['Dinas luar', 'is-duty'],
            'alpa' => ['Alpa', 'is-absent'],
            'tidak_hadir' => ['Tidak hadir', 'is-absent'],
        ] as $statusKey => [$label, $class])
                                @if (($statusCounts[$statusKey] ?? 0) > 0)
                                    <span class="atasan-status-count {{ $class }}">{{ $label }}
                                        <b>{{ $statusCounts[$statusKey] }}</b></span>
                                @endif
                            @endforeach
                            @if ($statusCounts->isEmpty())
                                <span class="atasan-status-none">Belum ada status absensi pada periode ini</span>
                            @endif
                        </div>
                    </div>
                    <div class="atasan-attendance-footer">
                        <span><i class="fa-regular fa-calendar"></i>
                            {{ $absensi?->tanggal?->translatedFormat('d F Y') ?? 'Belum melakukan absensi pada periode ini' }}</span>
                    </div>
                </article>
            @empty
                <div class="atasan-attendance-empty"><i class="fa-regular fa-calendar-xmark"></i><strong>Data pegawai
                        tidak
                        ditemukan</strong><span>Coba ubah kata kunci atau filter periode absensi.</span></div>
            @endforelse
        </div>

        <div class="atasan-attendance-table-wrap">
            <table class="atasan-attendance-table" id="attendanceTable">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>{{ $jenisPegawai }} &amp; Jabatan</th>
                        <th>Tanggal</th>
                        <th>Jam Masuk</th>
                        <th>Jam Pulang</th>
                        <th>Status Kehadiran</th>
                        <th>Rekap Periode Ini</th>
                        <th>Verifikasi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($pegawais as $pegawai)
                        @php($absensi = $pegawai->absensis->first())
                        @php($statusCounts = $pegawai->absensis->groupBy('status')->map->count())
                        @php(
    $statusLabel = $absensi
        ? match ($absensi->status) {
            'hadir' => 'Hadir',
            'izin' => 'Izin',
            'cuti' => 'Cuti',
            'sakit' => 'Sakit',
            'dinas_luar' => 'Dinas Luar',
            'alpa', 'tidak_hadir' => 'Tidak Hadir',
            default => 'Tidak Hadir'
        }
        : 'Belum ada data'
)
                        @php(
    $statusClass = $absensi
        ? match ($absensi->status) {
            'hadir' => 'is-present',
            'izin' => 'is-permission',
            'cuti' => 'is-leave',
            'sakit' => 'is-sick',
            'dinas_luar' => 'is-duty',
            default => 'is-absent'
        }
        : 'is-empty'
)
                        <tr>
                            <td class="attendance-row-number">{{ ($pegawais->firstItem() ?? 1) + $loop->index }}</td>
                            <td>
                                <div class="attendance-table-employee">
                                    <span
                                        class="atasan-attendance-avatar">{{ strtoupper(substr($pegawai->nama, 0, 2)) }}</span>
                                    <span><strong>{{ $pegawai->nama }}</strong><small>{{ $pegawai->jabatan->nama_jabatan ?? 'Pegawai' }}</small></span>
                                </div>
                            </td>
                            <td>{{ $absensi?->tanggal?->translatedFormat('d M Y') ?? '-' }}</td>
                            <td><span
                                    class="attendance-time">{{ $absensi?->jam_masuk ? \Carbon\Carbon::parse($absensi->jam_masuk)->format('H:i') : '-' }}</span>
                            </td>
                            <td><span
                                    class="attendance-time">{{ $absensi?->jam_pulang ? \Carbon\Carbon::parse($absensi->jam_pulang)->format('H:i') : '-' }}</span>
                            </td>
                            <td><span class="atasan-attendance-status {{ $statusClass }}"><i
                                        class="fa-solid fa-circle"></i> {{ $statusLabel }}</span></td>
                            <td>
                                <div class="attendance-period-counts">
                                    @foreach ([
            'hadir' => ['Hadir', 'is-present'],
            'izin' => ['Izin', 'is-permission'],
            'sakit' => ['Sakit', 'is-sick'],
            'cuti' => ['Cuti', 'is-leave'],
            'dinas_luar' => ['Dinas luar', 'is-duty'],
            'alpa' => ['Alpa', 'is-absent'],
            'tidak_hadir' => ['Tidak hadir', 'is-absent'],
        ] as $statusKey => [$label, $class])
                                        @if (($statusCounts[$statusKey] ?? 0) > 0)
                                            <span class="atasan-status-count {{ $class }}">{{ $label }}:
                                                {{ $statusCounts[$statusKey] }}</span>
                                        @endif
                                    @endforeach
                                    @if ($statusCounts->sum() === 0)
                                        <span class="attendance-no-record">Belum ada data periode ini</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @php($verificationStatus = $absensi?->status_verifikasi)
                                @if ($verificationStatus === 'terverifikasi')
                                    <span class="attendance-verification-badge is-verified">Terverifikasi</span>
                                @elseif ($verificationStatus === 'perlu_klarifikasi')
                                    <span class="attendance-verification-badge is-clarification">Perlu klarifikasi</span>
                                @else
                                    <span class="attendance-verification-badge is-pending">Belum diverifikasi</span>
                                @endif
                            </td>
                            <td>
                                @if ($absensi)
                                    <a class="attendance-view-employee"
                                        href="{{ route($routePrefix . '.absensi.show', $absensi) }}"
                                        aria-label="Verifikasi absensi {{ $pegawai->nama }}"
                                        title="Lihat detail absensi">
                                        <i class="fa-regular fa-eye"></i>
                                    </a>
                                @else
                                    <span class="attendance-view-employee is-disabled" title="Belum ada data absensi"
                                        aria-label="Belum ada data absensi untuk {{ $pegawai->nama }}">
                                        <i class="fa-regular fa-eye-slash"></i>
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="attendance-table-empty" colspan="9">Tidak ada data pegawai yang cocok dengan
                                filter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="atasan-attendance-pagination">{{ $pegawais->links('pagination::bootstrap-5') }}</div>
    </div>
@endsection


@push('styles')
    <link rel="stylesheet" href="{{ asset('css/atasan/absensi/data-absensi.css') }}">
@endpush
