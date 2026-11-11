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
    <style>
        body.desktop-atasan-attendance .atasan-attendance-detail-page {
            width: min(1120px, 100%);
            margin: 0 auto;
            padding: 1.25rem clamp(1rem, 2vw, 2rem) 2rem;
            color: #17213b;
            font-size: 12px;
        }

        .attendance-detail-back {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            margin-bottom: 1rem;
            color: #2563eb;
            font-size: 12px;
            text-decoration: none;
        }

        .attendance-detail-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .attendance-detail-eyebrow {
            color: #2563eb;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .06em;
        }

        .attendance-detail-heading h1 {
            margin: .25rem 0;
            font-size: 22px;
            font-weight: 800;
        }

        .attendance-detail-heading p {
            margin: 0;
            color: #7789a4;
            font-size: 12px;
        }

        .attendance-detail-employee,
        .attendance-detail-card {
            background: #fff;
            border: 1px solid #e3eaf3;
            border-radius: 12px;
            box-shadow: 0 2px 8px #24345508;
        }

        .atasan-attendance-detail-page .atasan-attendance-avatar {
            display: grid;
            place-items: center;
            width: 42px;
            height: 42px;
            flex: 0 0 42px;
            color: #fff;
            background: linear-gradient(145deg, #2563eb, #0ea5e9);
            border-radius: 12px;
            font-size: 12px;
            font-weight: 800;
        }

        .atasan-attendance-detail-page .atasan-attendance-status {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            padding: .3rem .5rem;
            border-radius: 7px;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
        }

        .atasan-attendance-detail-page .atasan-attendance-status i {
            font-size: 7px;
        }

        .atasan-attendance-detail-page .is-present {
            color: #078653;
            background: #eafbf4;
        }

        .atasan-attendance-detail-page .is-permission {
            color: #a16207;
            background: #fff7df;
        }

        .atasan-attendance-detail-page .is-sick {
            color: #c47708;
            background: #fff3d6;
        }

        .atasan-attendance-detail-page .is-absent {
            color: #dc4969;
            background: #fff0f3;
        }

        .atasan-attendance-detail-page .is-leave,
        .atasan-attendance-detail-page .is-empty {
            color: #64748b;
            background: #f1f5f9;
        }

        .atasan-attendance-detail-page .is-duty {
            color: #2563eb;
            background: #edf4ff;
        }

        .attendance-detail-employee {
            display: flex;
            align-items: center;
            gap: .75rem;
            margin-bottom: .85rem;
            padding: .85rem 1rem;
            border-radius: 9px;
        }

        .attendance-detail-employee .atasan-attendance-avatar {
            width: 42px;
            height: 42px;
            flex: 0 0 42px;
        }

        .attendance-detail-employee h2 {
            margin: 0;
            font-size: 15px;
        }

        .attendance-detail-employee p {
            margin: .2rem 0 0;
            color: #71839e;
            font-size: 12px;
        }

        .attendance-detail-employee p span {
            padding: 0 .2rem;
        }

        .attendance-detail-date {
            display: grid;
            grid-template-columns: 2rem minmax(0, 1fr);
            align-items: center;
            gap: .5rem;
            margin-left: auto;
            padding: .45rem .6rem;
            background: #f8fafc;
            border: 1px solid #edf1f6;
            border-radius: 7px;
            text-align: left;
        }

        .attendance-detail-date>span {
            display: grid;
            place-items: center;
            width: 2rem;
            height: 2rem;
            color: #2563eb;
            background: #eef4ff;
            border-radius: 6px;
        }

        .attendance-detail-date>div {
            display: flex;
            flex-direction: column;
            gap: .1rem;
        }

        .attendance-detail-date small {
            color: #8090a7;
            font-size: 10px;
            font-weight: 700;
        }

        .attendance-detail-date strong {
            font-size: 12px;
        }

        .attendance-detail-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.15fr) minmax(0, .85fr);
            gap: .85rem;
        }

        .attendance-detail-card {
            min-width: 0;
            padding: .9rem 1rem;
        }

        .attendance-detail-card h3 {
            display: flex;
            align-items: center;
            gap: .45rem;
            margin: 0 0 .65rem;
            padding-bottom: .55rem;
            border-bottom: 1px solid #edf1f6;
            font-size: 13px;
            font-weight: 700;
        }

        .attendance-detail-card h3 i {
            color: #2563eb;
        }

        .attendance-detail-column {
            display: grid;
            align-content: start;
            gap: .85rem;
            min-width: 0;
        }

        .attendance-detail-card-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .5rem;
            margin-bottom: .65rem;
            padding-bottom: .55rem;
            border-bottom: 1px solid #edf1f6;
        }

        .attendance-detail-card-heading h3 {
            min-width: 0;
            margin: 0;
            padding: 0;
            border: 0;
        }

        .attendance-detail-card-heading h3>span {
            display: grid;
            place-items: center;
            width: 1.65rem;
            height: 1.65rem;
            flex: 0 0 1.65rem;
            color: #2563eb;
            background: #eef4ff;
            border-radius: 6px;
        }

        .attendance-detail-card-heading>small {
            color: #8391a6;
            font-size: 10px;
            text-align: right;
        }

        .attendance-verification-badge {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            padding: .25rem .4rem;
            border-radius: 6px;
            font-size: 10px;
            font-weight: 700;
            white-space: nowrap;
        }

        .attendance-verification-badge.is-verified {
            color: #078653;
            background: #eafbf4;
        }

        .attendance-verification-badge.is-clarification {
            color: #a16207;
            background: #fff7df;
        }

        .attendance-verification-badge.is-pending {
            color: #64748b;
            background: #f1f5f9;
        }

        .attendance-verification-history {
            display: grid;
            gap: .25rem;
            margin-bottom: .65rem;
            padding: .55rem .65rem;
            color: #667994;
            background: #f8fafc;
            border: 1px solid #edf1f6;
            border-radius: 7px;
            font-size: 11px;
        }

        .attendance-verification-history strong {
            color: #27344c;
        }


        .attendance-verification-history p {
            margin: .25rem 0 0;
            padding-top: .35rem;
            border-top: 1px solid #e8edf4;
            color: #526480;
            white-space: pre-line;
        }

        .attendance-verification-form label {
            display: block;
            margin-bottom: .3rem;
            color: #526480;
            font-size: 11px;
            font-weight: 700;
        }

        .attendance-verification-form label span {
            color: #8291a7;
            font-size: 10px;
            font-weight: 400;
        }

        .attendance-verification-form textarea {
            display: block;
            width: 100%;
            min-height: 70px;
            padding: .55rem .65rem;
            color: #27344c;
            background: #fff;
            border: 1px solid #dfe7f0;
            border-radius: 7px;
            font: inherit;
            font-size: 12px;
            resize: vertical;
        }

        .attendance-verification-form textarea:focus {
            border-color: #7ba7f7;
            outline: 3px solid #2563eb1a;
        }

        .attendance-verification-error {
            display: block;
            margin-top: .25rem;
            color: #dc3545;
            font-size: 11px;
        }

        .attendance-verification-actions {
            display: flex;
            justify-content: flex-end;
            flex-wrap: wrap;
            gap: .45rem;
            margin-top: .55rem;
        }

        .attendance-verification-actions button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .35rem;
            min-height: 2rem;
            padding: .4rem .65rem;
            border: 1px solid transparent;
            border-radius: 7px;
            font: inherit;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
        }

        .attendance-clarify-button {
            color: #a16207;
            background: #fff7df;
            border-color: #f7e7b5 !important;
        }

        .attendance-verify-button {
            color: #fff;
            background: #2563eb;
        }

        .attendance-verify-button:hover {
            background: #1d4ed8;
        }

        .attendance-detail-actions {
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        .attendance-detail-actions .attendance-print-button {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            min-height: 2rem;
            padding: .4rem .65rem;
            color: #33435e;
            background: #fff;
            border: 1px solid #dfe7f0;
            border-radius: 7px;
            font: inherit;
            font-size: 12px;
            font-weight: 600;
        }

        .attendance-detail-actions .attendance-print-button:hover {
            color: #1d4ed8;
            background: #f8fbff;
            border-color: #b8cef5;
        }

        .attendance-detail-employee-copy {
            min-width: 0;
            flex: 1;
        }

        .attendance-detail-employee-meta {
            display: flex;
            flex-wrap: wrap;
            gap: .3rem .8rem;
            margin-top: .35rem;
            color: #8291a7;
            font-size: 10px;
        }

        .attendance-detail-employee-meta i {
            margin-right: .18rem;
            color: #7b8ba3;
        }

        .attendance-detail-card dl {
            display: grid;
            gap: .45rem;
            margin: 0;
        }

        .attendance-detail-card dl>div {
            display: grid;
            grid-template-columns: minmax(120px, .75fr) minmax(0, 1.25fr);
            align-items: start;
            gap: .6rem;
        }

        .attendance-detail-card dt {
            color: #7789a4;
            font-size: 12px;
            font-weight: 400;
        }

        .attendance-detail-card dd {
            min-width: 0;
            margin: 0;
            color: #27344c;
            font-size: 12px;
            font-weight: 600;
            overflow-wrap: anywhere;
        }

        .attendance-detail-time dl>div {
            min-height: 1.8rem;
            padding: .3rem 0;
            border-bottom: 1px solid #f0f3f7;
        }

        .attendance-detail-time dl>div:last-child {
            border-bottom: 0;
        }

        .attendance-time-check {
            display: inline-flex;
            align-items: center;
            gap: .2rem;
            margin-left: .3rem;
            padding: .15rem .3rem;
            color: #078653;
            background: #eafbf4;
            border-radius: 4px;
            font-size: 10px;
        }

        .attendance-detail-shift {
            display: block;
            margin-top: .2rem;
            color: #687994;
            font-size: 10px !important;
            font-weight: 500 !important;
        }

        .attendance-detail-card .atasan-attendance-status {
            display: inline-block;
            font-size: 11px;
        }

        .attendance-location-badge {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            padding: .25rem .4rem;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
        }

        .attendance-location-badge.is-inside {
            color: #078653;
            background: #eafbf4;
        }

        .attendance-location-badge.is-outside {
            color: #a16207;
            background: #fff7df;
        }

        .attendance-location-stats {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .5rem;
        }

        .attendance-location-stats>div {
            display: flex;
            min-width: 0;
            flex-direction: column;
            gap: .22rem;
            padding: .55rem;
            background: #f8fafc;
            border: 1px solid #edf1f6;
            border-radius: 7px;
        }

        .attendance-location-stats small,
        .attendance-proof-meta small,
        .attendance-note-block>small {
            color: #7b8ca6;
            font-size: 10px;
        }

        .attendance-location-stats strong {
            color: #27344c;
            font-size: 12px;
            font-weight: 700;
        }

        .attendance-location-stats strong.is-inside {
            color: #078653;
        }

        .attendance-location-stats strong.is-outside {
            color: #a16207;
        }

        .attendance-location-stats .attendance-coordinate {
            overflow-wrap: anywhere;
            font-size: 11px;
        }

        .attendance-map-preview {
            height: 220px;
            margin-top: .55rem;
            overflow: hidden;
            background: #e9eef5;
            border: 1px solid #e3eaf3;
            border-radius: 8px;
        }

        .attendance-map-preview iframe {
            display: block;
            width: 100%;
            height: 100%;
            border: 0;
        }

        .attendance-map-link {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .35rem;
            margin-top: .55rem;
            padding: .45rem .6rem;
            color: #2563eb;
            background: #eef4ff;
            border-radius: 7px;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
        }

        .attendance-map-link .fa-arrow-up-right-from-square {
            margin-left: auto;
            font-size: 10px;
        }



        .attendance-map-link:hover {
            color: #1d4ed8;
            background: #e1ecff;
        }

        .attendance-map-unavailable {
            margin-top: .55rem;
            padding: .55rem;
            color: #75849a;
            background: #f8fafc;
            border-radius: 7px;
            font-size: 11px;
        }

        .attendance-proof-valid {
            flex: 0 0 auto;
            padding: .2rem .35rem;
            color: #078653;
            background: #eafbf4;
            border-radius: 5px;
            font-size: 10px;
            font-weight: 700;
        }

        .attendance-proof-image-link {
            position: relative;
            display: block;
            width: 100% !important;
            overflow: hidden;
            background: #e9eef5;
            border: 1px solid #e3eaf3;
            border-radius: 8px;
        }

        .attendance-proof-image-link img {
            width: 100%;
            height: clamp(220px, 26vw, 320px);
            max-height: 320px;
            object-fit: contain;
            background: #e9eef5;
            border: 0;
            border-radius: 0;
        }

        .attendance-proof-image-link>span {
            position: absolute;
            right: .45rem;
            bottom: .45rem;
            padding: .25rem .4rem;
            color: #fff;
            background: #17213bd9;
            border-radius: 5px;
            font-size: 10px;
            opacity: 0;
            transition: opacity .15s ease;
        }

        .attendance-proof-image-link:hover>span,
        .attendance-proof-image-link:focus-visible>span {
            opacity: 1;
        }

        .attendance-proof-meta {
            display: flex;
            justify-content: space-between;
            gap: .65rem;
            margin-top: .5rem;
            padding: .45rem .55rem;
            background: #f8fafc;
            border-radius: 7px;
        }

        .attendance-proof-meta>span {
            display: flex;
            min-width: 0;
            flex-direction: column;
            gap: .15rem;
        }

        .attendance-proof-meta strong {
            color: #34435b;
            font-size: 11px;
        }

        .attendance-detail-note p {
            margin: 0;
            color: #526480;
            font-size: 12px;
            line-height: 1.6;
            white-space: pre-line;
        }

        .attendance-note-block {
            padding: .55rem .65rem;
            background: #f8fafc;
            border: 1px solid #edf1f6;
            border-radius: 7px;
        }

        .attendance-note-block>small {
            display: block;
            margin-bottom: .25rem;
            font-weight: 700;
        }

        .attendance-note-disclaimer {
            display: flex;
            gap: .4rem;
            margin-top: .5rem;
            color: #74839a;
            font-size: 10px;
            line-height: 1.45;
        }

        .attendance-proof-empty {
            display: flex;
            align-items: center;
            gap: .55rem;
            min-height: 100px;
            padding: 1rem;
            color: #8291a7;
            background: #f8fafc;
            border: 1px dashed #d4deea;
            border-radius: 8px;
            font-size: 12px;
        }

        .attendance-proof-empty i {
            color: #9aabc1;
        }

        .attendance-proof-empty i {
            font-size: 18px;
        }

        @media print {

            body.desktop-atasan-attendance .navbar-sikinerja,
            body.desktop-atasan-attendance .sidebar,
            body.desktop-atasan-attendance .mobile-bottom-nav,
            body.desktop-atasan-attendance .footer-sikinerja,
            body.desktop-atasan-attendance .attendance-detail-back,
            body.desktop-atasan-attendance .attendance-detail-actions .attendance-print-button {
                display: none !important;
            }

            body.desktop-atasan-attendance .main-content {
                margin: 0 !important;
                padding: 0 !important;
            }

            body.desktop-atasan-attendance .atasan-attendance-detail-page {
                width: 100%;
                max-width: none;
                padding: 0;
            }

            body.desktop-atasan-attendance .attendance-detail-card,
            body.desktop-atasan-attendance .attendance-detail-employee {
                box-shadow: none;
                break-inside: avoid;
            }
        }

        @media (max-width: 767.98px) {
            body.desktop-atasan-attendance .atasan-attendance-detail-page {
                padding: 1rem;
            }

            .attendance-detail-heading {
                align-items: flex-start;
                flex-direction: column;
            }

            .attendance-detail-actions {
                width: 100%;
                justify-content: space-between;
            }

            .attendance-detail-employee {
                align-items: flex-start;
            }

            .attendance-detail-grid {
                grid-template-columns: 1fr;
            }

            .attendance-detail-date {
                width: 100%;
                margin: .25rem 0 0;
                padding-top: .65rem;
                border-top: 1px solid #edf1f6;
            }

            .attendance-detail-shift {
                margin-top: 0;
            }

            .attendance-proof-image-link img {
                height: min(60vw, 260px);
            }

            .attendance-location-stats {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 420px) {
            .attendance-detail-employee-meta {
                flex-direction: column;
                gap: .2rem;
            }

            .attendance-detail-card-heading {
                align-items: flex-start;
            }

            .attendance-detail-card-heading>small {
                max-width: 45%;
            }

            .attendance-location-stats {
                grid-template-columns: 1fr;
            }

            .attendance-detail-card dl>div {
                grid-template-columns: minmax(95px, .75fr) minmax(0, 1.25fr);
            }
        }
    </style>
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
