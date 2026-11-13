<section class="atasan-dashboard-action-buttons" aria-label="Pengiriman laporan dan presensi Atasan">
    @php
        $laporanAtasanHref = route('atasan.laporan-harian.create');
        $laporanAtasanLabel = $laporanAtasanHariIni ? 'Sudah Isi Laporan' : 'Buat Laporan';
        $laporanAtasanDescription = $laporanAtasanHariIni
            ? 'Laporan hari ini sudah dikirim ke Kepala Bagian.'
            : 'Isi dan ajukan laporan kegiatan Anda untuk diverifikasi Kepala Bagian.';

        if ($laporanAtasanHariIni?->status === 'draft') {
            $laporanAtasanHref = route('atasan.laporan-harian.edit', $laporanAtasanHariIni);
            $laporanAtasanLabel = 'Lanjutkan Laporan';
        } elseif ($laporanAtasanHariIni?->status === 'ditolak') {
            $laporanAtasanHref = route('atasan.laporan-harian.edit', $laporanAtasanHariIni);
            $laporanAtasanLabel = 'Perbaiki Laporan';
            $laporanAtasanDescription = 'Laporan perlu diperbaiki sebelum dikirim kembali ke Kepala Bagian.';
        }
    @endphp

    <a class="atasan-dashboard-action-button {{ $laporanAtasanHariIni ? 'is-completed' : '' }}"
        href="{{ $laporanAtasanHref }}">
        <span class="atasan-dashboard-action-icon" aria-hidden="true">
            <i class="fa-solid fa-clipboard-question"></i>
        </span>
        <span class="atasan-dashboard-action-copy">
            <strong>Laporan Harian ke Kepala Bagian</strong>
            <small>{{ $laporanAtasanDescription }}</small>
        </span>
        <span class="atasan-dashboard-action-cta">{{ $laporanAtasanLabel }} <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>
    </a>

    @php
        $presensiAtasanSelesai = filled($presensiAtasanHariIni?->jam_masuk)
            && filled($presensiAtasanHariIni?->jam_pulang);
        $presensiAtasanLabel = $presensiAtasanSelesai
            ? 'Sudah Absensi'
            : (filled($presensiAtasanHariIni?->jam_masuk) ? 'Isi Absensi Pulang' : 'Isi Absensi');
        $presensiAtasanDescription = $presensiAtasanSelesai
            ? 'Presensi masuk dan pulang hari ini sudah tercatat.'
            : (filled($presensiAtasanHariIni?->jam_masuk)
                ? 'Presensi masuk tercatat. Lengkapi dengan presensi pulang.'
                : 'Catat kehadiran Anda dengan verifikasi lokasi dan foto.');
    @endphp

    <a class="atasan-dashboard-action-button {{ $presensiAtasanSelesai ? 'is-attendance-complete' : '' }}"
        href="{{ route('atasan.absensi-saya.index') }}">
        <span class="atasan-dashboard-action-icon" aria-hidden="true">
            <i class="fa-regular fa-calendar-check"></i>
        </span>
        <span class="atasan-dashboard-action-copy">
            <strong>Presensi Atasan</strong>
            <small>{{ $presensiAtasanDescription }}</small>
        </span>
        <span class="atasan-dashboard-action-cta">{{ $presensiAtasanLabel }} <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>
    </a>
</section>
