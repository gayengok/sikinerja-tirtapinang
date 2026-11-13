@if ($laporanMenunggu > 0)
    <a class="atasan-pending-reports-notice" href="{{ route('atasan.laporan.index', ['status' => 'diajukan']) }}">
        <span class="atasan-pending-reports-icon" aria-hidden="true">
            <i class="fa-regular fa-clock"></i>
        </span>
        <span class="atasan-pending-reports-copy">
            <strong>Menunggu Konfirmasi Laporan</strong>
            <span>{{ $laporanMenunggu }} laporan harian bawahan telah dikirim dan belum dikonfirmasi.</span>
        </span>
        <i class="fa-solid fa-chevron-right atasan-pending-reports-arrow" aria-hidden="true"></i>
    </a>
@endif
