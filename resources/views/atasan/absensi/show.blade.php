@extends('layouts.app')

@section('title', 'Detail Absensi Pegawai')

@section('content')
    @php
        $pegawai = $absensi->pegawai;
        $labelStatus = match ($absensi->status) {
            'hadir' => 'Hadir',
            'izin' => 'Izin',
            'sakit' => 'Sakit',
            'alpa', 'tidak_hadir' => 'Tidak Hadir',
            'cuti' => 'Cuti',
            'dinas_luar' => 'Dinas Luar',
            default => ucfirst($absensi->status),
        };
        $statusClass = match ($absensi->status) {
            'hadir' => 'is-present',
            'izin' => 'is-permission',
            'sakit' => 'is-sick',
            'alpa', 'tidak_hadir' => 'is-absent',
            'cuti' => 'is-leave',
            'dinas_luar' => 'is-duty',
            default => 'is-empty',
        };
        $hasLocation = $absensi->latitude !== null && $absensi->longitude !== null;
        $lastUpdated = $absensi->updated_at?->translatedFormat('H:i d M Y');
        $statusVerifikasi = $absensi->status_verifikasi;
    @endphp

    <div class="atasan-attendance-detail-page">
        <header class="attendance-detail-heading">
            <div>
                <a class="attendance-detail-back" href="{{ route($routePrefix . '.absensi.index', request()->query()) }}">
                    <i class="fa-solid fa-arrow-left"></i> Kembali ke Data Absensi
                </a>
                <h1>Detail Absensi Pegawai</h1>
                <p>Periksa waktu, lokasi, serta bukti presensi untuk membantu proses verifikasi.</p>
            </div>
            <div class="attendance-detail-actions">
                <span class="atasan-attendance-status {{ $statusClass }}">
                    <i class="fa-solid fa-circle"></i> {{ $labelStatus }}
                </span>
                <button type="button" class="attendance-print-button" id="attendancePrint">
                    <i class="fa-solid fa-print"></i> Cetak Bukti Presensi
                </button>
            </div>
        </header>

        <section class="attendance-detail-employee">
            <span class="atasan-attendance-avatar">{{ strtoupper(substr($pegawai->nama, 0, 2)) }}</span>
            <div class="attendance-detail-employee-copy">
                <h2>{{ $pegawai->nama }}</h2>
                <p>{{ $pegawai->jabatan->nama_jabatan ?? 'Pegawai' }}
                    @if ($pegawai->jabatan?->divisi?->nama_divisi)
                        <span>&middot;</span> {{ $pegawai->jabatan->divisi->nama_divisi }}
                    @endif
                </p>
                <div class="attendance-detail-employee-meta">
                    <span><i class="fa-regular fa-id-card"></i> Unit
                        {{ $pegawai->jabatan?->divisi?->nama_divisi ?? 'Tidak diketahui' }}</span>
                    <span><i class="fa-solid fa-location-dot"></i> Status kantor:
                        {{ $absensi->di_luar_kantor ? 'Di luar area' : 'Di dalam area' }}</span>
                </div>
            </div>
            <div class="attendance-detail-date">
                <span><i class="fa-regular fa-calendar"></i></span>
                <div>
                    <small>TANGGAL PRESENSI</small>
                    <strong>{{ $absensi->tanggal->translatedFormat('D, d F Y') }}</strong>
                    @if ($pegawai->jabatan?->jamKerjas?->isNotEmpty())
                        @php
                            $namaHari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                            $jadwalHariIni =
                                $pegawai->jabatan->jamKerjas->firstWhere(
                                    'hari',
                                    $namaHari[$absensi->tanggal->dayOfWeek],
                                ) ?? $pegawai->jabatan->jamKerjas->firstWhere('hari', 'Kerja');
                        @endphp
                        @if ($jadwalHariIni)
                            <small class="attendance-detail-shift">Shift reguler:
                                {{ \Carbon\Carbon::parse($jadwalHariIni->jam_masuk)->format('H:i') }}
                                - {{ \Carbon\Carbon::parse($jadwalHariIni->jam_pulang)->format('H:i') }}</small>
                        @endif
                    @endif
                </div>
            </div>
        </section>

        <div class="attendance-detail-grid">
            <div class="attendance-detail-column">
                <section class="attendance-detail-card attendance-detail-time">
                    <div class="attendance-detail-card-heading">
                        <h3><span><i class="fa-regular fa-clock"></i></span> Waktu &amp; Status Presensi</h3>
                        @if ($lastUpdated)
                            <small>Data diperbarui: {{ $lastUpdated }}</small>
                        @endif
                    </div>
                    <dl>
                        <div>
                            <dt>Jam masuk</dt>
                            <dd>
                                {{ $absensi->jam_masuk ? \Carbon\Carbon::parse($absensi->jam_masuk)->format('H:i') . ' WIB' : 'Belum tercatat' }}
                                @if ($absensi->jam_masuk && $absensi->status === 'hadir')
                                    <span class="attendance-time-check"><i class="fa-solid fa-check"></i> Tercatat</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt>Jam pulang</dt>
                            <dd>{{ $absensi->jam_pulang ? \Carbon\Carbon::parse($absensi->jam_pulang)->format('H:i') . ' WIB' : 'Belum tercatat' }}
                            </dd>
                        </div>
                        <div>
                            <dt>Jenis presensi terakhir</dt>
                            <dd>{{ $absensi->jenis_presensi ? ucfirst($absensi->jenis_presensi) : '-' }}</dd>
                        </div>
                        <div>
                            <dt>Status kehadiran</dt>
                            <dd><span class="atasan-attendance-status {{ $statusClass }}">{{ $labelStatus }}</span></dd>
                        </div>
                        <div>
                            <dt>Sumber data</dt>
                            <dd>{{ ucfirst($absensi->sumber ?? 'Tidak diketahui') }}</dd>
                        </div>
                    </dl>
                </section>

                <section class="attendance-detail-card attendance-detail-location">
                    <div class="attendance-detail-card-heading">
                        <h3><span><i class="fa-solid fa-location-dot"></i></span> Verifikasi Lokasi &amp; GPS</h3>
                        <span
                            class="attendance-location-badge {{ $absensi->di_luar_kantor ? 'is-outside' : 'is-inside' }}">
                            {{ $absensi->di_luar_kantor ? 'Perlu diperiksa' : 'Dalam geofence' }}
                        </span>
                    </div>
                    <div class="attendance-location-stats">
                        <div>
                            <small>Status area kantor</small>
                            <strong class="{{ $absensi->di_luar_kantor ? 'is-outside' : 'is-inside' }}">
                                <i
                                    class="fa-solid {{ $absensi->di_luar_kantor ? 'fa-triangle-exclamation' : 'fa-circle-check' }}"></i>
                                {{ $absensi->di_luar_kantor ? 'Di luar area kantor' : 'Di dalam area kantor' }}
                            </strong>
                        </div>
                        <div>
                            <small>Jarak dari kantor</small>
                            <strong>{{ $absensi->jarak_lokasi !== null ? number_format($absensi->jarak_lokasi, 0, ',', '.') . ' m' : 'Tidak tersedia' }}</strong>
                        </div>
                        <div>
                            <small>Akurasi GPS perangkat</small>
                            <strong>{{ $absensi->akurasi_gps !== null ? number_format($absensi->akurasi_gps, 1, ',', '.') . ' m' : 'Tidak tersedia' }}</strong>
                        </div>
                        <div>
                            <small>Koordinat presensi</small>
                            <strong class="attendance-coordinate">
                                @if ($hasLocation)
                                    {{ number_format($absensi->latitude, 7, ',', '.') }},
                                    {{ number_format($absensi->longitude, 7, ',', '.') }}
                                @else
                                    Tidak tersedia
                                @endif
                            </strong>
                        </div>
                    </div>
                    @if ($hasLocation)
                        <div class="attendance-map-preview">
                            <iframe
                                src="https://maps.google.com/maps?q={{ $absensi->latitude }},{{ $absensi->longitude }}&amp;z=16&amp;output=embed"
                                title="Peta lokasi presensi {{ $pegawai->nama }}"
                                loading="lazy"
                                referrerpolicy="no-referrer"
                                allowfullscreen></iframe>
                        </div>
                        <a class="attendance-map-link"
                            href="https://www.google.com/maps?q={{ $absensi->latitude }},{{ $absensi->longitude }}"
                            target="_blank" rel="noopener noreferrer">
                            <i class="fa-solid fa-map-location-dot"></i> Lihat titik presensi di Google Maps
                            <i class="fa-solid fa-arrow-up-right-from-square"></i>
                        </a>
                    @else
                        <div class="attendance-map-unavailable"><i class="fa-solid fa-circle-info"></i> Data koordinat tidak
                            tersedia untuk presensi ini.</div>
                    @endif
                </section>
            </div>

            <div class="attendance-detail-column">
                <section class="attendance-detail-card attendance-detail-proof">
                    <div class="attendance-detail-card-heading">
                        <h3><span><i class="fa-regular fa-image"></i></span> Bukti Foto Presensi</h3>
                        @if ($absensi->foto_path)
                            <span class="attendance-proof-valid"><i class="fa-solid fa-shield-check"></i> Bukti
                                tersedia</span>
                        @endif
                    </div>
                    @if ($fotoTersedia)
                        <a class="attendance-proof-image-link" href="{{ route($routePrefix . '.absensi.foto', $absensi) }}"
                            target="_blank" rel="noopener noreferrer" aria-label="Buka foto bukti presensi ukuran penuh">
                            <img src="{{ route($routePrefix . '.absensi.foto', $absensi) }}"
                                alt="Bukti foto presensi {{ $pegawai->nama }}">
                            <span><i class="fa-solid fa-up-right-and-down-left-from-center"></i> Buka foto ukuran
                                penuh</span>
                        </a>
                        <div class="attendance-proof-meta">
                            <span><small>Waktu pembaruan</small><strong>{{ $lastUpdated ?? '-' }}</strong></span>
                            <span><small>Metode</small><strong>{{ ucfirst($absensi->sumber ?? 'Tidak diketahui') }}</strong></span>
                        </div>
                    @else
                        <div class="attendance-proof-empty">
                            <i class="fa-regular fa-image"></i>
                            <span>{{ $absensi->foto_path ? 'File foto bukti presensi tidak ditemukan.' : 'Tidak ada foto bukti presensi.' }}</span>
                        </div>
                    @endif
                </section>

                <section class="attendance-detail-card attendance-detail-note">
                    <div class="attendance-detail-card-heading">
                        <h3><span><i class="fa-regular fa-note-sticky"></i></span> Keterangan &amp; Catatan</h3>
                    </div>
                    <div class="attendance-note-block">
                        <small>KETERANGAN DARI PEGAWAI</small>
                        <p>{{ $absensi->keterangan ?: 'Tidak ada keterangan yang dicantumkan.' }}</p>
                    </div>
                    <div class="attendance-note-disclaimer">
                        <i class="fa-solid fa-circle-info"></i>
                        <span>Gunakan informasi waktu, lokasi, dan bukti foto sebagai bahan pemeriksaan. Data lokasi berasal
                            dari perangkat pegawai.</span>
                    </div>
                </section>

                <section class="attendance-detail-card attendance-verification-card">
                    <div class="attendance-detail-card-heading">
                        <h3><span><i class="fa-solid fa-clipboard-check"></i></span> Keputusan Verifikasi</h3>
                        @if ($statusVerifikasi)
                            <span
                                class="attendance-verification-badge {{ $statusVerifikasi === 'terverifikasi' ? 'is-verified' : 'is-clarification' }}">
                                <i
                                    class="fa-solid {{ $statusVerifikasi === 'terverifikasi' ? 'fa-circle-check' : 'fa-triangle-exclamation' }}"></i>
                                {{ $statusVerifikasi === 'terverifikasi' ? 'Terverifikasi' : 'Perlu Klarifikasi' }}
                            </span>
                        @else
                            <span class="attendance-verification-badge is-pending">
                                <i class="fa-regular fa-clock"></i> Belum diverifikasi
                            </span>
                        @endif
                    </div>

                    @if ($statusVerifikasi)
                        <div class="attendance-verification-history">
                            <span>Keputusan oleh
                                <strong>{{ $absensi->diverifikasiOleh?->nama ?? 'Atasan' }}</strong></span>
                            <span>{{ $absensi->diverifikasi_pada?->translatedFormat('d F Y, H:i') ?? '-' }} WIB</span>
                            @if ($absensi->catatan_verifikasi)
                                <p>{{ $absensi->catatan_verifikasi }}</p>
                            @endif
                        </div>
                    @endif

                    <form class="attendance-verification-form" method="POST"
                        action="{{ route($routePrefix . '.absensi.verifikasi', $absensi) }}"
                        id="attendanceVerificationForm">
                        @csrf
                        <label for="catatan_verifikasi">Catatan pemeriksaan
                            <span id="verificationNoteHint">(wajib untuk klarifikasi)</span>
                        </label>
                        <textarea id="catatan_verifikasi" name="catatan_verifikasi" rows="3" maxlength="1000"
                            placeholder="Tuliskan alasan jika data perlu diklarifikasi...">{{ old('catatan_verifikasi', $statusVerifikasi === 'perlu_klarifikasi' ? $absensi->catatan_verifikasi : '') }}</textarea>
                        @error('catatan_verifikasi')
                            <small class="attendance-verification-error">{{ $message }}</small>
                        @enderror
                        @error('keputusan')
                            <small class="attendance-verification-error">{{ $message }}</small>
                        @enderror
                        <div class="attendance-verification-actions">
                            <button type="submit" name="keputusan" value="perlu_klarifikasi"
                                class="attendance-clarify-button">
                                <i class="fa-solid fa-rotate"></i> Perlu Klarifikasi
                            </button>
                            <button type="submit" name="keputusan" value="terverifikasi" class="attendance-verify-button">
                                <i class="fa-solid fa-check"></i>
                                {{ $statusVerifikasi === 'terverifikasi' ? 'Simpan Verifikasi' : 'Verifikasi Data' }}
                            </button>
                        </div>
                    </form>
                </section>
            </div>
        </div>
    </div>
@endsection



@push('styles')
    <link rel="stylesheet" href="{{ asset('css/atasan/absensi/show.css') }}">
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const printButton = document.getElementById('attendancePrint');
            const verificationForm = document.getElementById('attendanceVerificationForm');

            if (printButton) {
                printButton.addEventListener('click', function() {
                    window.print();
                });
            }

            if (verificationForm) {
                verificationForm.addEventListener('submit', function(event) {
                    const note = verificationForm.querySelector('[name="catatan_verifikasi"]');
                    note.required = event.submitter?.value === 'perlu_klarifikasi';
                });
            }
        });
    </script>
@endpush
