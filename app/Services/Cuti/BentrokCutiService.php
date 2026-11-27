<?php

namespace App\Services\Cuti;

use App\Models\DetailPengajuanCuti;
use App\Models\PengajuanCuti;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use LogicException;

class BentrokCutiService
{
    private const STATUS_PENGAJUAN_AKTIF = [
        PengajuanCuti::STATUS_PENDING_ATASAN,
        PengajuanCuti::STATUS_PENDING_KEPALA_BAGIAN,
        PengajuanCuti::STATUS_PENDING_DIREKTUR,
        PengajuanCuti::STATUS_APPROVED,
        PengajuanCuti::STATUS_PARTIALLY_APPROVED,
    ];

    private const STATUS_DETAIL_AKTIF = [
        DetailPengajuanCuti::STATUS_PENDING,
        DetailPengajuanCuti::STATUS_APPROVED,
    ];

    public function cariBentrok(
        int $pegawaiId,
        array $tanggal,
        ?int $kecualiPengajuanId = null,
        bool $gunakanLock = false
    ): array {
        if ($gunakanLock && DB::transactionLevel() === 0) {
            throw new LogicException('Pemeriksaan bentrok dengan lock wajib berada dalam transaksi.');
        }

        $tanggal = $this->normalisasiTanggal($tanggal);

        if (empty($tanggal)) {
            return [];
        }

        $query = DetailPengajuanCuti::query()
            ->join('pengajuan_cuti', 'pengajuan_cuti.id', '=', 'detail_pengajuan_cuti.pengajuan_cuti_id')
            ->where('pengajuan_cuti.pegawai_id', $pegawaiId)
            ->whereIn('pengajuan_cuti.status', self::STATUS_PENGAJUAN_AKTIF)
            ->whereIn('detail_pengajuan_cuti.status', self::STATUS_DETAIL_AKTIF)
            ->where('detail_pengajuan_cuti.dihitung_kuota', true)
            ->whereIn('detail_pengajuan_cuti.tanggal', $tanggal);

        if ($kecualiPengajuanId !== null) {
            $query->where('pengajuan_cuti.id', '!=', $kecualiPengajuanId);
        }

        if ($gunakanLock) {
            $query->lockForUpdate();
        }

        return $query
            ->orderBy('detail_pengajuan_cuti.tanggal')
            ->orderBy('pengajuan_cuti.id')
            ->get([
                'detail_pengajuan_cuti.id',
                'detail_pengajuan_cuti.pengajuan_cuti_id',
                'detail_pengajuan_cuti.tanggal',
                'detail_pengajuan_cuti.status',
                'pengajuan_cuti.jenis_cuti_id',
                'pengajuan_cuti.nomor_pengajuan',
                'pengajuan_cuti.status as status_pengajuan',
            ])
            ->map(fn($detail) => [
                'detail_id' => (int) $detail->id,
                'pengajuan_cuti_id' => (int) $detail->pengajuan_cuti_id,
                'jenis_cuti_id' => (int) $detail->jenis_cuti_id,
                'nomor_pengajuan' => $detail->nomor_pengajuan,
                'tanggal' => $detail->tanggal->toDateString(),
                'status_detail' => $detail->status,
                'status_pengajuan' => $detail->status_pengajuan,
            ])->all();
    }

    public function validasiTidakBentrok(
        int $pegawaiId,
        array $tanggal,
        ?int $kecualiPengajuanId = null,
        bool $gunakanLock = false
    ): void {
        $bentrok = $this->cariBentrok($pegawaiId, $tanggal, $kecualiPengajuanId, $gunakanLock);

        if (empty($bentrok)) {
            return;
        }

        $tanggalBentrok = array_values(array_unique(array_column($bentrok, 'tanggal')));

        throw ValidationException::withMessages([
            'tanggal_mulai' => 'Terdapat pengajuan cuti aktif pada tanggal: ' . implode(', ', $tanggalBentrok) . '.',
        ]);
    }

    public function ambilTanggalDihitung(array $details): array
    {
        $tanggal = [];

        foreach ($details as $detail) {
            if (!($detail['dihitung_kuota'] ?? false)) {
                continue;
            }

            $tanggal[] = $detail['tanggal'] ?? null;
        }

        return $this->normalisasiTanggal($tanggal);
    }

    private function normalisasiTanggal(array $tanggal): array
    {
        $hasil = [];

        foreach ($tanggal as $item) {
            if (!is_string($item) || !preg_match('/^\d{4}-\d{2}-\d{2}$/D', $item)) {
                throw ValidationException::withMessages([
                    'tanggal' => 'Format tanggal pemeriksaan bentrok tidak valid.',
                ]);
            }

            [$tahun, $bulan, $hari] = array_map('intval', explode('-', $item));

            if (!checkdate($bulan, $hari, $tahun)) {
                throw ValidationException::withMessages([
                    'tanggal' => 'Tanggal pemeriksaan bentrok tidak valid.',
                ]);
            }

            $hasil[] = CarbonImmutable::parse($item, 'Asia/Jakarta')->toDateString();
        }

        $hasil = array_values(array_unique($hasil));
        sort($hasil);

        return $hasil;
    }
}