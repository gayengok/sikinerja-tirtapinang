<?php

namespace App\Services;

use App\Models\MasterJenisPotongan;
use App\Models\MasterJamKerja;
use App\Models\Pegawai;
use App\Models\PeriodePenilaian;
use App\Models\PresensiPotongan;
use App\Support\HariLibur;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PresensiPotonganService
{
    private const RULES = [
        'late_light' => 'Terlambat Ringan',
        'late_medium' => 'Terlambat Sedang',
        'late_heavy' => 'Terlambat Berat',
        'missing_punch' => 'Tidak Absen',
        'unexcused' => 'Tanpa Keterangan',
        'early_light' => 'Pulang Sebelum Waktu (Ringan)',
        'early_heavy' => 'Pulang Sebelum Waktu (Berat)',
    ];

    private const EXEMPT_STATUSES = ['izin', 'sakit', 'cuti', 'dinas_luar'];

    public function syncForPegawai(Pegawai $pegawai, PeriodePenilaian $periode): void
    {
        $pegawai->loadMissing('jabatan');

        $tanggalMulai = Carbon::parse($periode->getRawOriginal('tanggal_mulai'))->startOfDay();
        $tanggalAkhir = Carbon::parse($periode->getRawOriginal('tanggal_selesai'))
            ->min(now()->startOfDay());

        if ($tanggalAkhir->lt($tanggalMulai)) {
            $this->replacePenalties($pegawai, $periode, []);

            return;
        }

        $jadwalKerja = MasterJamKerja::where(function ($query) use ($pegawai) {
            $query->whereNull('jabatan_id')
                ->orWhere('jabatan_id', $pegawai->jabatan_id);
        })->get()->groupBy('hari')->map(function (Collection $jadwals) use ($pegawai) {
            return $jadwals->firstWhere('jabatan_id', $pegawai->jabatan_id)
                ?? $jadwals->firstWhere('jabatan_id', null);
        });
        $absensis = $pegawai->absensis()
            ->whereDate('tanggal', '>=', $tanggalMulai->toDateString())
            ->whereDate('tanggal', '<=', $tanggalAkhir->toDateString())
            ->get()
            ->keyBy(fn ($absensi) => substr((string) $absensi->getRawOriginal('tanggal'), 0, 10));
        $rules = MasterJenisPotongan::all()->keyBy('nama_potongan');
        $events = [];

        for ($tanggal = $tanggalMulai->copy(); $tanggal->lte($tanggalAkhir); $tanggal->addDay()) {
            $jadwal = $this->jadwalUntukTanggal($jadwalKerja, $tanggal);

            if (! $this->hariKerja($jadwal, $tanggal)) {
                continue;
            }

            if ($tanggal->isToday() && (! $jadwal || now()->lt($this->waktuJadwal($tanggal, $jadwal->jam_pulang)))) {
                continue;
            }

            $absensi = $absensis->get($tanggal->toDateString());
            if (! $absensi) {
                $this->catatKejadian($events, $rules, 'unexcused');

                continue;
            }

            if (in_array($absensi->status, self::EXEMPT_STATUSES, true)) {
                continue;
            }

            if (in_array($absensi->status, ['alpa', 'tidak_hadir'], true)) {
                $this->catatKejadian($events, $rules, 'unexcused');

                continue;
            }

            if ($absensi->status !== 'hadir') {
                continue;
            }

            if (! $absensi->jam_masuk) {
                $this->catatKejadian($events, $rules, 'missing_punch');
            } elseif ($jadwal) {
                $batasMasuk = $this->waktuJadwal($tanggal, $jadwal->jam_masuk)
                    ->addMinutes((int) $jadwal->toleransi_menit);
                $menitTerlambat = $this->menitSelisih($batasMasuk, $this->waktuJadwal($tanggal, $absensi->jam_masuk));

                if ($menitTerlambat > 0) {
                    $kategori = match (true) {
                        $menitTerlambat <= 10 => 'late_light',
                        $menitTerlambat <= 20 => 'late_medium',
                        default => 'late_heavy',
                    };
                    $this->catatKejadian($events, $rules, $kategori);
                }
            }

            if (! $absensi->jam_pulang) {
                $this->catatKejadian($events, $rules, 'missing_punch');
            } elseif ($jadwal) {
                $menitPulangAwal = $this->menitSelisih(
                    $this->waktuJadwal($tanggal, $absensi->jam_pulang),
                    $this->waktuJadwal($tanggal, $jadwal->jam_pulang)
                );

                if ($menitPulangAwal > 0) {
                    $this->catatKejadian($events, $rules, $menitPulangAwal <= 30 ? 'early_light' : 'early_heavy');
                }
            }
        }

        $this->replacePenalties($pegawai, $periode, $events);
    }

    private function jadwalUntukTanggal(Collection $jadwalKerja, Carbon $tanggal): mixed
    {
        $namaHari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

        return $jadwalKerja->get($namaHari[$tanggal->dayOfWeek])
            ?? $jadwalKerja->get('Kerja');
    }

    private function hariKerja(mixed $jadwal, Carbon $tanggal): bool
    {
        if (HariLibur::detail($tanggal->toDateString())) {
            return false;
        }

        if ($jadwal !== null) {
            return true;
        }

        return $tanggal->isWeekday();
    }

    private function waktuJadwal(Carbon $tanggal, string $waktu): Carbon
    {
        return Carbon::parse($tanggal->toDateString() . ' ' . $waktu);
    }

    private function menitSelisih(Carbon $batas, Carbon $aktual): int
    {
        $selisihDetik = $aktual->getTimestamp() - $batas->getTimestamp();

        return $selisihDetik > 0 ? (int) ceil($selisihDetik / 60) : 0;
    }

    private function catatKejadian(array &$events, Collection $rules, string $kategori): void
    {
        $namaAturan = self::RULES[$kategori];
        $aturan = $rules->get($namaAturan);

        if (! $aturan && $kategori === 'unexcused') {
            $aturan = $rules->get('Alpa');
        }

        if (! $aturan) {
            throw new RuntimeException("Aturan potongan presensi '{$namaAturan}' belum dikonfigurasi.");
        }

        $aturanId = $aturan->id;
        $events[$aturanId] ??= [
            'jenis_potongan_id' => $aturanId,
            'jumlah_kejadian' => 0,
            'persen_potongan' => 0,
        ];
        $events[$aturanId]['jumlah_kejadian']++;
        $events[$aturanId]['persen_potongan'] += (float) $aturan->persen_potongan;
    }

    private function replacePenalties(Pegawai $pegawai, PeriodePenilaian $periode, array $events): void
    {
        DB::transaction(function () use ($pegawai, $periode, $events) {
            PresensiPotongan::where('pegawai_id', $pegawai->id)
                ->where('periode_id', $periode->id)
                ->delete();

            foreach ($events as $event) {
                $event['persen_potongan'] = round($event['persen_potongan']);

                PresensiPotongan::create([
                    'pegawai_id' => $pegawai->id,
                    'periode_id' => $periode->id,
                    ...$event,
                ]);
            }
        });
    }
}
