@php
    $absenMasuk = $jenisJadwal === 'masuk';
    [$waktuBuka, $waktuTutup] = $absenMasuk
        ? $jamKerja->rentangAbsenMasuk()
        : $jamKerja->rentangAbsenPulang();
    $jamJadwal = \Carbon\Carbon::parse($absenMasuk ? $jamKerja->jam_masuk : $jamKerja->jam_pulang);
    $selisihBatasMasuk = $absenMasuk
        ? \Carbon\Carbon::parse($jamKerja->jam_masuk)->diffInMinutes($waktuTutup, false)
        : null;
@endphp

<div class="master-jam-window">
    <span class="master-jam-window-schedule">
        <i class="fa-regular fa-clock" aria-hidden="true"></i>
        Jam {{ $absenMasuk ? 'Masuk' : 'Pulang' }}:
        <strong>{{ $jamJadwal->format('H:i') }}</strong>
    </span>
    <span class="master-jam-window-range">
        Rentang: {{ $waktuBuka->format('H:i') }} – {{ $waktuTutup->format('H:i') }}
    </span>
    <small>
        Buka: {{ $waktuBuka->format('H:i') }}
        @if ($absenMasuk)
            · Batas: {{ $waktuTutup->format('H:i') }}
            ({{ $selisihBatasMasuk >= 0 ? '+' : '' }}{{ (int) $selisihBatasMasuk }}m)
        @else
            · Tutup: {{ $waktuTutup->format('H:i') }} (Maks)
        @endif
    </small>
</div>
