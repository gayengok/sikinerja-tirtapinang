<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class MasterJamKerja extends Model
{
    use HasFactory;

    protected $fillable = [
        'jabatan_id',
        'hari',
        'jenis_jadwal',
        'nomor_shift',
        'jam_masuk',
        'jam_pulang',
        'toleransi_menit',
        'jam_buka_absen_masuk',
        'batas_absen_masuk',
        'jam_buka_absen_pulang',
        'jam_tutup_absen_pulang',
    ];

    protected function casts(): array
    {
        return [
            'nomor_shift' => 'integer',
        ];
    }

    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class);
    }

    public function rentangAbsenMasuk(): array
    {
        return [
            Carbon::parse($this->jam_buka_absen_masuk),
            Carbon::parse($this->batas_absen_masuk),
        ];
    }

    public function rentangAbsenPulang(): array
    {
        return [
            Carbon::parse($this->jam_buka_absen_pulang),
            Carbon::parse($this->jam_tutup_absen_pulang),
        ];
    }

    public function isOvernight(): bool
    {
        return $this->jam_pulang <= $this->jam_masuk;
    }

    public static function untukPegawaiPadaTanggal(
        Pegawai $pegawai,
        Carbon $tanggal,
        string $status = 'hadir'
    ): ?self {
        $namaHari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $hari = $namaHari[$tanggal->dayOfWeek];
        $jadwals = static::query()
            ->where(function ($query) use ($pegawai) {
                $query->whereNull('jabatan_id')
                    ->orWhere('jabatan_id', $pegawai->jabatan_id);
            })
            ->whereIn('hari', array_unique([$hari, 'Kerja']))
            ->get();

        return static::pilihUntukPegawai($jadwals, $pegawai, $hari, $status);
    }

    public static function pilihUntukPegawai(
        Collection $jadwals,
        Pegawai $pegawai,
        string $hari,
        string $status = 'hadir'
    ): ?self {
        preg_match('/^shift_([1-3])$/', $status, $shiftMatch);
        $jenisJadwal = isset($shiftMatch[1]) ? 'shift' : 'normal';
        $nomorShift = isset($shiftMatch[1]) ? (int) $shiftMatch[1] : null;

        foreach (array_unique([$hari, 'Kerja']) as $hariJadwal) {
            $jadwalHari = $jadwals
                ->where('hari', $hariJadwal)
                ->where('jenis_jadwal', $jenisJadwal)
                ->filter(fn (self $jadwal) => $jadwal->nomor_shift === $nomorShift);

            $jadwal = $jadwalHari->firstWhere('jabatan_id', $pegawai->jabatan_id)
                ?? $jadwalHari->firstWhere('jabatan_id', null);

            if ($jadwal) {
                return $jadwal;
            }
        }

        return null;
    }
}
