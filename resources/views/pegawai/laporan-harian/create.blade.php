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


                        <div class="daily-report-submit-bar">
                            @if ($routePrefix === 'atasan')
                                @if ($laporanHariIni?->status === 'diajukan')
                                    <button type="button" disabled><i class="fa-solid fa-clock"></i> Menunggu verifikasi Kepala Bagian</button>
                                @elseif (in_array($laporanHariIni?->status, ['disetujui', 'diverifikasi']))
                                    <button type="button" disabled><i class="fa-solid fa-circle-check"></i> Laporan sudah diverifikasi</button>
                                @elseif (in_array($laporanHariIni?->status, ['draft', 'ditolak']))
                                    <a href="{{ route($routePrefix . '.laporan-harian.edit', $laporanHariIni) }}">
                                        {{ $laporanHariIni->status === 'ditolak' ? 'Perbaiki Laporan' : 'Lanjutkan Laporan' }}
                                    </a>
                                @else
                                    <button type="submit"><i class="fa-solid fa-paper-plane"></i> Kirim Laporan Harian</button>
                                @endif
                            @else
                                <a href="{{ route($routePrefix . '.laporan-harian.index') }}">Batal</a>
                                <button type="submit"><i class="fa-solid fa-floppy-disk"></i> Simpan</button>
                            @endif
                        </div>
                    @endif
                </section>
            </div>
        </form>
    </div>
@endsection




@push('styles')
    <style>
        body.role-atasan .daily-report-form-page {
            display: grid;
            gap: 18px;
            width: min(1040px, 100%);
            margin: 0 auto;
            padding: 22px;
            color: #17213b;
        }

        body.role-atasan .daily-report-banner {
            position: relative;
            overflow: hidden;
            margin: 0;
            padding: 22px 24px;
            color: #17213b;
            background: #fff;
            border: 1px solid #e3eaf2;
            border-radius: 14px;
            box-shadow: 0 2px 8px rgba(35, 52, 85, .025);
        }

        body.role-atasan .daily-report-banner > span {
            position: relative;
            z-index: 1;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 9px;
            color: #41617d;
            background: #f1f6fa;
            border-radius: 999px;
            font-size: .64rem;
            font-weight: 700;
            letter-spacing: .035em;
        }

        body.role-atasan .daily-report-banner > span > i {
            color: #078ac8;
            font-size: .38rem;
        }

        body.role-atasan .daily-report-banner > span > b {
            color: #9aabba;
            font-weight: 500;
        }

        body.role-atasan .daily-report-banner h1 {
            position: relative;
            z-index: 1;
            margin: 8px 0 4px;
            color: #17213b;
            font-size: clamp(1.35rem, 3vw, 1.8rem);
            font-weight: 800;
        }

        body.role-atasan .daily-report-banner p {
            position: relative;
            z-index: 1;
            max-width: 560px;
            margin: 0;
            color: #71829c;
            font-size: .78rem;
            line-height: 1.6;
        }

        body.role-atasan .daily-report-banner-icon {
            position: absolute;
            top: 50%;
            right: 20px;
            display: grid;
            width: 42px;
            height: 42px;
            place-items: center;
            color: #078ac8;
            background: #f2f8fc;
            border-radius: 11px;
            font-size: 1.1rem;
            transform: translateY(-50%);
        }

        body.role-atasan .daily-report-form-page > form {
            display: grid;
            gap: 18px;
        }

        body.role-atasan .daily-report-steps {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 18px;
            background: #fff;
            border: 1px solid #dfe7f0;
            border-radius: 14px;
        }

        body.role-atasan .daily-report-steps a {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            min-width: 0;
            color: #64748b;
            text-decoration: none;
        }

        body.role-atasan .daily-report-steps a > span {
            display: grid;
            place-items: center;
            width: 30px;
            height: 30px;
            flex: 0 0 30px;
            color: #71829c;
            background: #f1f5f9;
            border-radius: 50%;
            font-size: .68rem;
            font-weight: 800;
        }

        body.role-atasan .daily-report-steps a strong {
            font-size: .78rem;
        }

        body.role-atasan .daily-report-steps a.is-current {
            color: #075985;
        }

        body.role-atasan .daily-report-steps a.is-current > span {
            color: #fff;
            background: #078ac8;
            box-shadow: 0 0 0 4px #e8f6fc;
        }

        body.role-atasan .daily-report-steps > i {
            color: #b3c0cf;
            font-size: .72rem;
        }

        body.role-atasan .daily-report-form-card {
            min-width: 0;
            margin: 0;
            padding: 22px;
            background: #fff;
            border: 1px solid #dfe7f0;
            border-radius: 16px;
            box-shadow: 0 5px 16px rgba(35, 52, 85, .045);
        }

        body.role-atasan .daily-report-card-title {
            display: flex;
            align-items: center;
            gap: 11px;
            min-height: 44px;
            padding-bottom: 14px;
            border-bottom: 1px solid #edf1f5;
        }

        body.role-atasan .daily-report-card-title > i {
            display: grid;
            flex: 0 0 38px;
            width: 38px;
            height: 38px;
            place-items: center;
            color: #078ac8;
            background: #e8f6fc;
            border-radius: 11px;
        }

        body.role-atasan .daily-report-card-title strong {
            color: #17213b;
            font-size: .95rem;
        }

        body.role-atasan .daily-report-card-title span {
            margin-left: auto;
            color: #71829c;
            font-size: .75rem;
            white-space: nowrap;
        }

        body.role-atasan #laporan-informasi {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
            align-items: start;
            gap: 18px 22px;
        }

        body.role-atasan #laporan-informasi > .daily-report-card-title {
            grid-column: 1 / -1;
        }

        body.role-atasan #laporan-realisasi {
            display: block;
        }

        body.role-atasan .daily-report-field {
            min-width: 0;
            margin: 0;
        }

        body.role-atasan #laporan-informasi > .daily-report-field {
            grid-column: 1 / -1;
        }

        body.role-atasan .daily-report-field label {
            display: block;
            margin-bottom: 8px;
            color: #26344d;
            font-size: .82rem;
            font-weight: 700;
        }

        body.role-atasan .daily-report-field label b {
            color: #e33f56;
        }

        body.role-atasan .daily-report-field input:not([type="hidden"]),
        body.role-atasan .daily-report-field textarea,
        body.role-atasan .daily-report-indicator input:not([type="hidden"]),
        body.role-atasan .daily-report-indicator select {
            box-sizing: border-box;
            width: 100%;
            min-width: 0;
            min-height: 44px;
            padding: 10px 12px;
            color: #27344c;
            background: #f8fafc;
            border: 1px solid #d5e0eb;
            border-radius: 10px;
            outline: 0;
            font: inherit;
            font-size: .84rem;
        }

        body.role-atasan .daily-report-field textarea {
            min-height: 128px;
            resize: vertical;
            line-height: 1.6;
        }

        body.role-atasan .daily-report-field input:focus,
        body.role-atasan .daily-report-field textarea:focus,
        body.role-atasan .daily-report-indicator input:focus,
        body.role-atasan .daily-report-indicator select:focus {
            border-color: #42a8d1;
            box-shadow: 0 0 0 3px rgba(8, 138, 200, .12);
        }

        body.role-atasan .daily-report-field input[type="file"] {
            padding: 7px;
            background: #fff;
        }

        body.role-atasan .daily-report-field input[type="file"]::file-selector-button {
            margin-right: 10px;
            padding: 7px 10px;
            color: #075985;
            background: #e8f6fc;
            border: 0;
            border-radius: 7px;
            font-weight: 700;
        }

        body.role-atasan .daily-report-field small {
            display: block;
            margin-top: 7px;
            color: #71829c;
            font-size: .72rem;
            line-height: 1.5;
        }

        body.role-atasan .daily-report-form-card .report-calendar {
            width: 100%;
            border-radius: 12px;
        }

        body.role-atasan .daily-report-form-card .report-calendar-day {
            min-height: 42px;
        }

        body.role-atasan .daily-report-hint {
            margin: 14px 0 16px;
            padding: 11px 13px;
            color: #64748b;
            background: #f3f9fc;
            border: 1px solid #e4f0f5;
            border-radius: 10px;
            font-size: .82rem;
            line-height: 1.55;
        }

        body.role-atasan .daily-report-indicators {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        body.role-atasan .daily-report-indicator {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(130px, .7fr);
            align-items: center;
            gap: 14px;
            min-width: 0;
            padding: 14px;
            background: #f8fafc;
            border: 1px solid #e1e9f1;
            border-radius: 12px;
        }

        body.role-atasan .daily-report-indicator-label {
            min-width: 0;
        }

        body.role-atasan .daily-report-indicator-label strong,
        body.role-atasan .daily-report-indicator-label span {
            display: block;
            overflow-wrap: anywhere;
        }

        body.role-atasan .daily-report-indicator-label strong {
            color: #26344d;
            font-size: .84rem;
            line-height: 1.45;
        }

        body.role-atasan .daily-report-indicator-label span {
            margin-top: 4px;
            color: #71829c;
            font-size: .72rem;
        }

        body.role-atasan .daily-report-submit-bar {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 18px;
            padding-top: 16px;
            border-top: 1px solid #edf1f5;
        }

        body.role-atasan .daily-report-submit-bar a {
            padding: 11px 16px;
            color: #526480;
            background: #f1f5f9;
            border-radius: 9px;
            font-size: .82rem;
            font-weight: 700;
            text-decoration: none;
        }

        body.role-atasan .daily-report-submit-bar button {
            min-height: 42px;
            padding: 10px 18px;
            color: #fff;
            background: #078ac8;
            border: 0;
            border-radius: 9px;
            font-size: .82rem;
            font-weight: 700;
        }

        body.role-atasan .daily-report-submit-bar button:disabled {
            background: #64748b;
            cursor: not-allowed;
            opacity: .9;
        }

        body.role-atasan .daily-report-status-notice {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 13px 15px;
            color: #07527c;
            background: #eaf7ff;
            border: 1px solid #c8eaff;
            border-radius: 10px;
            font-size: .82rem;
            line-height: 1.5;
        }

        body.role-atasan .daily-report-status-notice > i {
            margin-top: 2px;
            color: #078ac8;
        }

        body.role-atasan .report-calendar-toolbar button:disabled {
            cursor: not-allowed;
            opacity: .45;
        }

        body.role-atasan .daily-report-alert,
        body.role-atasan .daily-report-holiday-notice {
            margin: 0;
        }

        @media (min-width: 721px) and (max-width: 991.98px) {
            body.role-atasan #laporan-informasi {
                grid-template-columns: minmax(0, 1fr);
            }

            body.role-atasan #laporan-informasi > .daily-report-card-title {
                grid-column: auto;
            }
        }

        @media (max-width: 720px) {
            body.role-atasan .daily-report-form-page {
                gap: 14px;
                padding: 14px;
            }

            body.role-atasan .daily-report-banner {
                padding: 18px;
                border-radius: 14px;
            }

            body.role-atasan .daily-report-banner-icon {
                top: 18px;
                right: 16px;
                width: 36px;
                height: 36px;
                font-size: .95rem;
                transform: none;
            }

            body.role-atasan .daily-report-form-page > form {
                gap: 14px;
            }

            body.role-atasan .daily-report-steps {
                justify-content: space-between;
                gap: 8px;
                padding: 11px 12px;
            }

            body.role-atasan .daily-report-steps a {
                gap: 7px;
            }

            body.role-atasan .daily-report-steps a > span {
                width: 27px;
                height: 27px;
                flex-basis: 27px;
            }

            body.role-atasan .daily-report-steps a strong {
                font-size: .68rem;
            }

            body.role-atasan .daily-report-steps > i {
                font-size: .6rem;
            }

            body.role-atasan .daily-report-form-card {
                padding: 16px;
                border-radius: 13px;
            }

            body.role-atasan #laporan-informasi,
            body.role-atasan .daily-report-indicators {
                grid-template-columns: minmax(0, 1fr);
            }

            body.role-atasan #laporan-informasi > .daily-report-card-title {
                grid-column: auto;
            }

            body.role-atasan .daily-report-indicator {
                grid-template-columns: minmax(0, 1fr) minmax(100px, .7fr);
                gap: 10px;
                padding: 11px;
            }

            body.role-atasan .daily-report-form-card .report-calendar-day {
                min-height: 38px;
            }
        }

        .report-calendar {
            overflow: hidden;
            border: 1px solid #dce5ef;
            border-radius: 15px;
            background: #fff;
        }

        .daily-report-holiday-notice {
            position: relative;
            display: flex;
            align-items: flex-start;
            gap: .75rem;
            margin-bottom: 1rem;
            padding: 1rem;
            border: 1px solid #dce5ef;
            border-radius: 15px;
            background: #fff;
            color: #17345d;
        }

        .daily-report-holiday-icon {
            display: grid;
            width: 2.2rem;
            height: 2.2rem;
            flex: 0 0 auto;
            place-items: center;
            border-radius: 10px;
            background: #eef1f5;
            color: #59708d;
        }

        .daily-report-holiday-notice strong,
        .daily-report-holiday-notice span,
        .daily-report-holiday-notice p {
            display: block;
        }

        .daily-report-holiday-notice strong {
            padding-right: 5rem;
            font-size: .9rem;
        }

        .daily-report-holiday-notice span:not(.daily-report-holiday-badge) {
            margin-top: .3rem;
            color: #607694;
            font-size: .75rem;
        }

        .daily-report-holiday-notice p {
            margin: .8rem 0 0;
            color: #17345d;
            font-size: .82rem;
        }

        .daily-report-holiday-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            padding: .35rem .65rem;
            border-radius: 999px;
            background: #e9edf2;
            color: #17345d;
            font-size: .68rem;
            font-weight: 800;
        }

        .report-calendar-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: .75rem .9rem;
            background: #f7fafd;
            border-bottom: 1px solid #e4ebf3;
            color: #17345d;
        }

        .report-calendar-toolbar strong {
            font-size: .88rem;
            text-transform: capitalize;
        }

        .report-calendar-toolbar button {
            display: grid;
            width: 2rem;
            height: 2rem;
            place-items: center;
            border: 0;
            border-radius: 8px;
            background: transparent;
            color: #58708f;
            cursor: pointer;
        }

        .report-calendar-toolbar button:hover {
            background: #e8f2fb;
            color: #168fd4;
        }

        .report-calendar-weekdays,
        .report-calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
        }

        .report-calendar-weekdays {
            padding: .7rem .35rem .45rem;
            color: #607694;
            font-size: .72rem;
            font-weight: 700;
            text-align: center;
        }

        .report-calendar-grid {
            gap: 1px;
            padding: 0 .35rem .35rem;
            background: #e7edf4;
        }

        .report-calendar-day {
            position: relative;
            min-height: 3.35rem;
            padding: .45rem .25rem .25rem;
            border: 0;
            background: #fff;
            color: #526b8b;
            font: inherit;
            font-size: .78rem;
            cursor: pointer;
        }

        .report-calendar-day.is-empty {
            cursor: default;
            background: #f8fafc;
        }

        .report-calendar-day.is-holiday {
            background: #fff5f5;
            color: #d95353;
        }

        .report-calendar-day.is-disabled {
            cursor: not-allowed;
        }

        .report-calendar-lock {
            position: absolute;
            top: .25rem;
            right: .3rem;
            display: grid;
            width: .85rem;
            height: .85rem;
            place-items: center;
            border-radius: 50%;
            background: #e7edf4;
            color: #7b8da1;
            font-size: .48rem;
        }

        .report-calendar-day.is-national-holiday {
            background: #fff0f0;
            color: #cf3f50;
            font-weight: 700;
        }

        .report-calendar-day:not(.is-empty):hover {
            background: #eef7ff;
        }

        .report-calendar-day.is-today::before {
            position: absolute;
            top: .25rem;
            right: .3rem;
            width: .32rem;
            height: .32rem;
            border-radius: 50%;
            background: #168fd4;
            content: "";
        }

        .report-calendar-day.is-selected {
            outline: 2px solid #75b9f5;
            outline-offset: -2px;
            background: #edf6ff;
            color: #0e87d2;
            font-weight: 700;
        }

        .report-calendar-day.is-selected .report-calendar-number {
            display: inline-grid;
            width: 2rem;
            height: 2rem;
            place-items: center;
            border-radius: 50%;
            background: #2297e2;
            color: #fff;
        }

        .report-calendar-mark {
            display: grid;
            width: .42rem;
            height: .42rem;
            margin: .2rem auto 0;
            border-radius: 50%;
            place-items: center;
        }

        .report-calendar-mark.is-draft {
            background: #ef5350;
        }

        .report-calendar-mark.is-approved {
            width: .85rem;
            height: .85rem;
            border-radius: 50%;
            background: #25a66f;
            color: #fff;
            font-size: .55rem;
            line-height: 1;
        }

        .report-calendar-mark.is-permission {
            width: .85rem;
            height: .85rem;
            border-radius: 50%;
            background: #25a66f;
            color: #fff;
            font-size: .55rem;
            line-height: 1;
        }

        .report-calendar-mark.is-absent,
        .report-calendar-mark.is-rejected {
            background: #ef5350;
            color: #fff;
            font-size: .45rem;
        }

        .report-calendar-mark.is-rejected {
            width: .85rem;
            height: .85rem;
            font-size: .55rem;
        }

        .report-calendar-mark.is-missing {
            background: #2f8fe5;
        }

        .report-calendar-legend {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: .45rem .8rem;
            margin-top: .7rem;
            padding: .55rem .1rem .1rem;
            color: #17345d;
            font-size: .67rem;
            font-weight: 700;
        }

        .report-calendar-legend-item {
            display: inline-flex;
            align-items: center;
            gap: .25rem;
            white-space: nowrap;
        }

        .report-calendar-legend-item i {
            display: inline-grid;
            width: .9rem;
            height: .9rem;
            place-items: center;
            font-size: .72rem;
        }

        .report-calendar-legend-item.is-approved i {
            color: #25a66f;
        }

        .report-calendar-legend-item.is-not-approved i {
            color: #d92f4b;
            font-size: .58rem;
        }

        .report-calendar-legend-item.is-missing i {
            color: #2f8fe5;
            font-size: .58rem;
        }
    </style>
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
