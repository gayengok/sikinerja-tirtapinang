<?php

namespace App\Services;

use App\Models\HasilPenilaian;
use App\Models\LaporanHarian;
use App\Models\MasterKompetensi;
use App\Models\MasterKpi;
use App\Models\Pegawai;
use App\Models\PenilaianKompetensi;
use App\Models\PenilaianKpi;
use App\Models\PeriodePenilaian;
use App\Models\PresensiPotongan;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class HasilPenilaianService
{
    public function __construct(private readonly PresensiPotonganService $presensiPotonganService)
    {
    }

    public function syncForPegawai(Pegawai $pegawai, ?PeriodePenilaian $periode): ?HasilPenilaian
    {
        if (! $periode) {
            return null;
        }

        $pegawai->loadMissing('jabatan');

        $masterKpis = MasterKpi::where('jabatan_id', $pegawai->jabatan_id)
            ->orderBy('urutan')
            ->get();

        $this->syncAutomaticKpisFromApprovedReports($pegawai, $periode, $masterKpis);

        $penilaianKpis = PenilaianKpi::where('pegawai_id', $pegawai->id)
            ->where('periode_id', $periode->id)
            ->get();

        $penilaianKpiByMasterId = $penilaianKpis->keyBy('master_kpi_id');

        $totalBobotKpi = (float) $masterKpis->sum('bobot');

        // The weighted KPI score uses achievement percentage, not the 1-5 display score.
        $rataSkorKpi = $totalBobotKpi > 0
            ? round($masterKpis->sum(function (MasterKpi $masterKpi) use ($penilaianKpiByMasterId, $totalBobotKpi) {
                $penilaian = $penilaianKpiByMasterId->get($masterKpi->id);
                $nilaiKpi = min(100, max(0, (float) ($penilaian->persen_capaian ?? 0)));

                return $nilaiKpi * ((float) $masterKpi->bobot / $totalBobotKpi);
            }), 2)
            : 0;

        $masterKompetensiIds = MasterKompetensi::untukJabatan($pegawai->jabatan_id)->pluck('id');
        $jumlahKompetensi = $masterKompetensiIds->count();
        $penilaianKompetensi = PenilaianKompetensi::where('pegawai_id', $pegawai->id)
            ->where('periode_id', $periode->id)
            ->whereIn('master_kompetensi_id', $masterKompetensiIds)
            ->whereNotNull('skor')
            ->get();

        $this->presensiPotonganService->syncForPegawai($pegawai, $periode);

        $rataSkorKompetensi = $jumlahKompetensi > 0
            ? round(($penilaianKompetensi->sum('skor') / ($jumlahKompetensi * 5)) * 100, 2)
            : 0;
        $totalSkorA = round($rataSkorKpi * 0.6, 2);
        $totalSkorB = round($rataSkorKompetensi * 0.4, 2);
        $nilaiAkhir = round($totalSkorA + $totalSkorB, 2);
        $predikat = $this->tentukanPredikat($nilaiAkhir);

        $totalPotonganPersen = min(100, (float) PresensiPotongan::where('pegawai_id', $pegawai->id)
            ->where('periode_id', $periode->id)
            ->sum('persen_potongan'));

        $baseTukin = (float) ($pegawai->jabatan->base_tukin ?? 0);
        $tukinBruto = round($baseTukin * $nilaiAkhir / 100, 2);
        $potonganNominal = round($tukinBruto * $totalPotonganPersen / 100, 2);
        $tukinNeto = round($tukinBruto - $potonganNominal, 2);

        return DB::transaction(function () use (
            $pegawai,
            $periode,
            $rataSkorKpi,
            $rataSkorKompetensi,
            $totalSkorA,
            $totalSkorB,
            $nilaiAkhir,
            $predikat,
            $totalPotonganPersen,
            $tukinBruto,
            $tukinNeto
        ) {
            return HasilPenilaian::updateOrCreate(
                [
                    'pegawai_id' => $pegawai->id,
                    'periode_id' => $periode->id,
                ],
                [
                    'rata_skor_kpi' => $rataSkorKpi,
                    'rata_skor_kompetensi' => $rataSkorKompetensi,
                    'total_skor_a' => $totalSkorA,
                    'total_skor_b' => $totalSkorB,
                    'nilai_akhir' => $nilaiAkhir,
                    'predikat' => $predikat,
                    'total_potongan_presensi' => $totalPotonganPersen,
                    'tukin_bruto' => $tukinBruto,
                    'tukin_neto' => $tukinNeto,
                    'status' => 'draft',
                ]
            );
        });
    }

    private function syncAutomaticKpisFromApprovedReports(
        Pegawai $pegawai,
        PeriodePenilaian $periode,
        Collection $masterKpis
    ): void {
        $laporans = LaporanHarian::where('pegawai_id', $pegawai->id)
            ->where('status', 'disetujui')
            ->whereBetween('tanggal', [$periode->tanggal_mulai, $periode->tanggal_selesai])
            ->with('details.templateLaporan')
            ->get();

        if ($laporans->isEmpty()) {
            return;
        }

        $realisasiPerKpi = [];
        foreach ($laporans as $laporan) {
            foreach ($laporan->details as $detail) {
                $kpiId = $detail->templateLaporan?->terhubung_ke_kpi_id;
                $nilai = str_replace(',', '.', trim((string) $detail->nilai));

                if (! $kpiId || $nilai === '' || ! is_numeric($nilai)) {
                    continue;
                }

                $realisasiPerKpi[$kpiId] = ($realisasiPerKpi[$kpiId] ?? 0) + (float) $nilai;
            }
        }

        foreach ($masterKpis as $masterKpi) {
            if (! array_key_exists($masterKpi->id, $realisasiPerKpi)) {
                continue;
            }

            $target = (float) ($masterKpi->target_default ?? 0);
            $realisasi = (float) $realisasiPerKpi[$masterKpi->id];
            $persenCapaian = $this->hitungPersenCapaian($masterKpi->arah_capaian, $target, $realisasi);

            PenilaianKpi::updateOrCreate(
                [
                    'pegawai_id' => $pegawai->id,
                    'periode_id' => $periode->id,
                    'master_kpi_id' => $masterKpi->id,
                ],
                [
                    'target' => $target,
                    'realisasi_otomatis' => $realisasi,
                    'realisasi_final' => $realisasi,
                    'persen_capaian' => $persenCapaian,
                    'skor' => $this->skorDariCapaian($persenCapaian),
                    'keterangan' => 'Dihitung otomatis dari laporan harian yang disetujui.',
                ]
            );
        }
    }

    private function hitungPersenCapaian(string $arahCapaian, float $target, float $realisasi): float
    {
        if ($target <= 0) {
            return 0;
        }

        if ($arahCapaian === 'minimal') {
            return $realisasi <= 0 ? 0 : round(($target / $realisasi) * 100, 2);
        }

        return round(($realisasi / $target) * 100, 2);
    }

    private function skorDariCapaian(float $persenCapaian): int
    {
        return match (true) {
            $persenCapaian >= 100 => 5,
            $persenCapaian >= 90 => 4,
            $persenCapaian >= 75 => 3,
            $persenCapaian >= 60 => 2,
            default => 1,
        };
    }

    private function tentukanPredikat(float $nilaiAkhir): string
    {
        if ($nilaiAkhir >= 90) {
            return 'Sangat Baik';
        }
        if ($nilaiAkhir >= 80) {
            return 'Baik';
        }
        if ($nilaiAkhir >= 70) {
            return 'Cukup';
        }

        return 'Kurang';
    }
}
