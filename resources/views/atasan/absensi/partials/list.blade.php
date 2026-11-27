@php
    $statusOptions = [
        'hadir' => ['label' => 'Hadir', 'class' => 'is-present'],
        'izin' => ['label' => 'Izin', 'class' => 'is-permission'],
        'sakit' => ['label' => 'Sakit', 'class' => 'is-sick'],
        'cuti' => ['label' => 'Cuti', 'class' => 'is-leave'],
        'dinas_luar' => ['label' => 'Dinas Luar', 'class' => 'is-duty'],
        'shift_1' => ['label' => 'Shift 1 (Operator)', 'class' => 'is-present'],
        'shift_2' => ['label' => 'Shift 2 (Operator)', 'class' => 'is-present'],
        'shift_3' => ['label' => 'Shift 3 (Operator)', 'class' => 'is-present'],
        'alpa' => ['label' => 'Alpa', 'class' => 'is-absent'],
        'tidak_hadir' => ['label' => 'Tidak Hadir', 'class' => 'is-absent'],
    ];
@endphp

<div class="atasan-attendance-list-heading">
    <div>
        <h2>Rekap Presensi Pegawai</h2>
        <p>
            Catatan kehadiran pada
            <strong>{{ $tanggalDipilih->translatedFormat('l, d F Y') }}</strong>.
        </p>
    </div>

    <span>
        {{ $pegawais->firstItem() ?? 0 }}–{{ $pegawais->lastItem() ?? 0 }}
        dari {{ $pegawais->total() }} pegawai
    </span>
</div>

{{-- Tampilan mobile --}}
<div class="atasan-attendance-list atasan-attendance-mobile-list">
    @forelse ($pegawais as $pegawai)
        @php
            $absensi = $pegawai->absensis->first();

            $statusData = $absensi
                ? ($statusOptions[$absensi->status] ?? [
                    'label' => ucfirst(str_replace('_', ' ', $absensi->status)),
                    'class' => 'is-empty',
                ])
                : [
                    'label' => 'Belum ada data',
                    'class' => 'is-empty',
                ];

            $verificationStatus = $absensi?->status_verifikasi;

            $initials = collect(preg_split('/\s+/u', trim($pegawai->nama)))
                ->filter()
                ->take(2)
                ->map(fn ($word) => mb_strtoupper(mb_substr($word, 0, 1)))
                ->implode('');
        @endphp

        <article class="atasan-attendance-card">
            <div class="atasan-attendance-employee">
                <span class="atasan-attendance-avatar">
                    {{ $initials }}
                </span>

                <div class="atasan-attendance-employee-info">
                    <h3>{{ $pegawai->nama }}</h3>
                    <p>{{ $pegawai->jabatan?->nama_jabatan ?? 'Jabatan belum tersedia' }}</p>
                    <small>
                        {{ $pegawai->nip ?: 'NIP belum tersedia' }}
                    </small>
                </div>

                <span class="atasan-attendance-status {{ $statusData['class'] }}">
                    <i class="fa-solid fa-circle"></i>
                    {{ $statusData['label'] }}
                </span>
            </div>

            <div class="attendance-mobile-date">
                <i class="fa-regular fa-calendar"></i>
                {{ $tanggalDipilih->translatedFormat('l, d F Y') }}
            </div>

            <div class="atasan-attendance-data">
                <div>
                    <span>
                        <i class="fa-regular fa-clock"></i>
                        Jam Masuk
                    </span>

                    <strong>
                        {{ $absensi?->jam_masuk
                            ? \Carbon\Carbon::parse($absensi->jam_masuk)->format('H:i')
                            : '—' }}
                    </strong>
                </div>

                <div>
                    <span>
                        <i class="fa-solid fa-right-from-bracket"></i>
                        Jam Pulang
                    </span>

                    <strong>
                        {{ $absensi?->jam_pulang
                            ? \Carbon\Carbon::parse($absensi->jam_pulang)->format('H:i')
                            : '—' }}
                    </strong>
                </div>

                <div>
                    <span>
                        <i class="fa-solid fa-location-dot"></i>
                        Jarak GPS
                    </span>

                    <strong>
                        {{ $absensi?->jarak_lokasi !== null
                            ? number_format($absensi->jarak_lokasi, 0, ',', '.') . ' m'
                            : '—' }}
                    </strong>
                </div>
            </div>

            @if ($absensi)
                <p class="atasan-attendance-location {{ $absensi->di_luar_kantor ? 'is-outside' : 'is-inside' }}">
                    <i class="fa-solid {{ $absensi->di_luar_kantor ? 'fa-triangle-exclamation' : 'fa-circle-check' }}"></i>
                    {{ $absensi->di_luar_kantor
                        ? 'Presensi di luar area kantor'
                        : 'Presensi di dalam area kantor' }}
                </p>
            @endif

            <div class="attendance-mobile-verification">
                <span>Status Verifikasi</span>

                @if (!$absensi)
                    <span class="attendance-verification-badge is-pending">
                        Tidak ada data
                    </span>
                @elseif ($verificationStatus === 'terverifikasi')
                    <span class="attendance-verification-badge is-verified">
                        <i class="fa-solid fa-circle-check"></i>
                        Terverifikasi
                    </span>
                @elseif ($verificationStatus === 'perlu_klarifikasi')
                    <span class="attendance-verification-badge is-clarification">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        Perlu Klarifikasi
                    </span>
                @else
                    <span class="attendance-verification-badge is-pending">
                        Belum Diverifikasi
                    </span>
                @endif
            </div>

            <div class="atasan-attendance-footer">
                @if ($absensi)
                    <a
                        href="{{ route($routePrefix . '.absensi.show', $absensi) }}"
                        class="attendance-mobile-detail-button">
                        <i class="fa-regular fa-eye"></i>
                        Lihat Detail & Verifikasi
                        <i class="fa-solid fa-chevron-right"></i>
                    </a>
                @else
                    <span>
                        Belum ada catatan absensi pada tanggal ini.
                    </span>
                @endif
            </div>
        </article>
    @empty
        <div class="atasan-attendance-empty">
            <i class="fa-regular fa-calendar-xmark"></i>

            <strong>Data pegawai tidak ditemukan</strong>

            <span>
                Tidak ada pegawai yang sesuai dengan
                pencarian dan filter pada tanggal ini.
            </span>
        </div>
    @endforelse
</div>

{{-- Tampilan desktop --}}
<div class="atasan-attendance-table-wrap">
    <table class="atasan-attendance-table" id="attendanceTable">
        <thead>
            <tr>
                <th>No.</th>
                <th>Pegawai</th>
                <th>Tanggal</th>
                <th>Masuk</th>
                <th>Pulang</th>
                <th>Status</th>
                <th>Lokasi</th>
                <th>Verifikasi</th>
                <th>Aksi</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($pegawais as $pegawai)
                @php
                    $absensi = $pegawai->absensis->first();

                    $statusData = $absensi
                        ? ($statusOptions[$absensi->status] ?? [
                            'label' => ucfirst(str_replace('_', ' ', $absensi->status)),
                            'class' => 'is-empty',
                        ])
                        : [
                            'label' => 'Belum ada data',
                            'class' => 'is-empty',
                        ];

                    $verificationStatus = $absensi?->status_verifikasi;

                    $initials = collect(preg_split('/\s+/u', trim($pegawai->nama)))
                        ->filter()
                        ->take(2)
                        ->map(fn ($word) => mb_strtoupper(mb_substr($word, 0, 1)))
                        ->implode('');
                @endphp

                <tr>
                    <td class="attendance-row-number">
                        {{ ($pegawais->firstItem() ?? 1) + $loop->index }}
                    </td>

                    <td>
                        <div class="attendance-table-employee">
                            <span class="atasan-attendance-avatar">
                                {{ $initials }}
                            </span>

                            <span>
                                <strong>{{ $pegawai->nama }}</strong>

                                <small>
                                    {{ $pegawai->jabatan?->nama_jabatan ?? 'Jabatan belum tersedia' }}
                                </small>

                                <small>
                                    {{ $pegawai->nip ?: 'NIP belum tersedia' }}
                                </small>
                            </span>
                        </div>
                    </td>

                    <td>
                        {{ $tanggalDipilih->translatedFormat('d M Y') }}
                    </td>

                    <td>
                        <span class="attendance-time">
                            {{ $absensi?->jam_masuk
                                ? \Carbon\Carbon::parse($absensi->jam_masuk)->format('H:i')
                                : '—' }}
                        </span>
                    </td>

                    <td>
                        <span class="attendance-time">
                            {{ $absensi?->jam_pulang
                                ? \Carbon\Carbon::parse($absensi->jam_pulang)->format('H:i')
                                : '—' }}
                        </span>
                    </td>

                    <td>
                        <span class="atasan-attendance-status {{ $statusData['class'] }}">
                            <i class="fa-solid fa-circle"></i>
                            {{ $statusData['label'] }}
                        </span>
                    </td>

                    <td>
                        @if ($absensi)
                            <span class="attendance-location-indicator {{ $absensi->di_luar_kantor ? 'is-outside' : 'is-inside' }}">
                                <i class="fa-solid {{ $absensi->di_luar_kantor ? 'fa-triangle-exclamation' : 'fa-circle-check' }}"></i>
                                {{ $absensi->di_luar_kantor ? 'Luar Area' : 'Dalam Area' }}
                            </span>
                        @else
                            <span class="attendance-no-record">—</span>
                        @endif
                    </td>

                    <td>
                        @if (!$absensi)
                            <span class="attendance-verification-badge is-pending">
                                Tidak ada data
                            </span>
                        @elseif ($verificationStatus === 'terverifikasi')
                            <span class="attendance-verification-badge is-verified">
                                Terverifikasi
                            </span>
                        @elseif ($verificationStatus === 'perlu_klarifikasi')
                            <span class="attendance-verification-badge is-clarification">
                                Perlu Klarifikasi
                            </span>
                        @else
                            <span class="attendance-verification-badge is-pending">
                                Belum Diverifikasi
                            </span>
                        @endif
                    </td>

                    <td>
                        @if ($absensi)
                            <a
                                class="attendance-view-employee"
                                href="{{ route($routePrefix . '.absensi.show', $absensi) }}"
                                aria-label="Lihat detail absensi {{ $pegawai->nama }}"
                                title="Lihat detail dan verifikasi">
                                <i class="fa-regular fa-eye"></i>
                            </a>
                        @else
                            <span
                                class="attendance-view-employee is-disabled"
                                title="Tidak ada data absensi">
                                <i class="fa-regular fa-eye-slash"></i>
                            </span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td class="attendance-table-empty" colspan="9">
                        <i class="fa-regular fa-calendar-xmark"></i>
                        Tidak ada pegawai yang cocok dengan filter.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($pegawais->hasPages())
    <div class="atasan-attendance-pagination">
        {{ $pegawais->links('pagination::bootstrap-5') }}
    </div>
@endif
