@php
    $statusLaporanKepalaBagian = $laporanKepalaBagianHariIni?->status;
    $laporanKepalaBagianLabel = match ($statusLaporanKepalaBagian) {
        'draft' => 'Lanjutkan Laporan',
        'diajukan' => 'Laporan Terkirim',
        'disetujui', 'diverifikasi' => 'Sudah Diverifikasi',
        'ditolak' => 'Perbaiki Laporan',
        default => 'Isi Laporan Harian',
    };
    $laporanKepalaBagianDescription = match ($statusLaporanKepalaBagian) {
        'draft' => 'Draf laporan belum dikirim. Lanjutkan pengisian untuk mengajukannya.',
        'diajukan' => 'Laporan sudah dikirim dan sedang menunggu verifikasi Direktur.',
        'disetujui', 'diverifikasi' => 'Laporan sudah diverifikasi oleh Direktur.',
        'ditolak' => 'Laporan perlu diperbaiki sebelum dikirim kembali ke Direktur.',
        default => 'Isi laporan kegiatan harian Anda untuk diverifikasi Direktur.',
    };
    $laporanKepalaBagianUrl = in_array($statusLaporanKepalaBagian, ['draft', 'ditolak'], true)
        ? route('kepala_bagian.laporan-harian.edit', $laporanKepalaBagianHariIni)
        : route('kepala_bagian.laporan-harian.create');

    $absensiKepalaBagianLengkap = filled($absensiKepalaBagianHariIni?->jam_masuk)
        && filled($absensiKepalaBagianHariIni?->jam_pulang);
    $absensiKepalaBagianLabel = $absensiKepalaBagianLengkap
        ? ($absensiKepalaBagianHariIni?->status_verifikasi === 'terverifikasi'
            ? 'Sudah Diverifikasi'
            : 'Absensi Lengkap')
        : (filled($absensiKepalaBagianHariIni?->jam_masuk)
            ? 'Isi Absensi Pulang'
            : 'Isi Absensi');
    $absensiKepalaBagianDescription = $absensiKepalaBagianLengkap
        ? ($absensiKepalaBagianHariIni?->status_verifikasi === 'terverifikasi'
            ? 'Absensi harian sudah diverifikasi Direktur.'
            : 'Presensi masuk dan pulang tercatat, menunggu verifikasi Direktur.')
        : (filled($absensiKepalaBagianHariIni?->jam_masuk)
            ? 'Presensi masuk tercatat. Lengkapi dengan presensi pulang.'
            : 'Catat kehadiran harian menggunakan lokasi dan foto.');
@endphp

<section class="kb-daily-entry" aria-label="Input data harian Kepala Bagian">
    <div class="kb-daily-entry-heading">
        <h3><i class="fa-solid fa-pen-to-square"></i> Input Data Harian</h3>
        <span>Laporan dan absensi Anda akan diverifikasi Direktur</span>
    </div>

    <div class="kb-daily-entry-grid">
        <a href="{{ $laporanKepalaBagianUrl }}" class="kb-daily-entry-card">
            <span class="kb-daily-entry-icon">
                <i class="fa-solid fa-file-lines"></i>
            </span>
            <span class="kb-daily-entry-copy">
                <strong>Laporan Harian</strong>
                <small>{{ $laporanKepalaBagianDescription }}</small>
            </span>
            <span class="kb-daily-entry-cta">
                {{ $laporanKepalaBagianLabel }}
                <i class="fa-solid fa-arrow-right"></i>
            </span>
        </a>

        <a href="{{ route('kepala_bagian.absensi-saya.index') }}" class="kb-daily-entry-card">
            <span class="kb-daily-entry-icon">
                <i class="fa-solid fa-calendar-check"></i>
            </span>
            <span class="kb-daily-entry-copy">
                <strong>Absensi Harian</strong>
                <small>{{ $absensiKepalaBagianDescription }}</small>
            </span>
            <span class="kb-daily-entry-cta">
                {{ $absensiKepalaBagianLabel }}
                <i class="fa-solid fa-arrow-right"></i>
            </span>
        </a>
    </div>
</section>
