<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MutasiKuotaCuti extends Model
{
    use HasFactory;

    protected $table = 'mutasi_kuota_cuti';

    public const JENIS_PEMBERIAN_KUOTA = 'pemberian_kuota';

    public const JENIS_AKUMULASI = 'akumulasi';

    public const JENIS_RESERVASI = 'reservasi';

    public const JENIS_PELEPASAN_RESERVASI = 'pelepasan_reservasi';

    public const JENIS_PENGGUNAAN = 'penggunaan';

    public const JENIS_PENYESUAIAN_TAMBAH = 'penyesuaian_tambah';

    public const JENIS_PENYESUAIAN_KURANG = 'penyesuaian_kurang';

    public const JENIS_KEDALUWARSA = 'kedaluwarsa';

    public const JENIS_RESET_AKUMULASI = 'reset_akumulasi';

    public const JENIS_KOREKSI = 'koreksi';

    protected $fillable = [
        'kuota_cuti_id',
        'pengajuan_cuti_id',
        'persetujuan_cuti_id',
        'user_id',
        'kode_mutasi',
        'jenis_mutasi',
        'jumlah_hari',
        'perubahan_sisa',
        'saldo_sebelum',
        'saldo_sesudah',
        'keterangan',
        'metadata',
        'terjadi_pada',
    ];

    protected $appends = [
        'jenis_label',
        'jenis_color',
        'perubahan_label',
    ];

    protected function casts(): array
    {
        return [
            'jumlah_hari' => 'integer',
            'perubahan_sisa' => 'integer',
            'saldo_sebelum' => 'array',
            'saldo_sesudah' => 'array',
            'metadata' => 'array',
            'terjadi_pada' => 'datetime',
        ];
    }

    public function kuotaCuti()
    {
        return $this->belongsTo(
            KuotaCuti::class,
            'kuota_cuti_id'
        );
    }

    public function pengajuanCuti()
    {
        return $this->belongsTo(
            PengajuanCuti::class,
            'pengajuan_cuti_id'
        );
    }

    public function persetujuanCuti()
    {
        return $this->belongsTo(
            PersetujuanCuti::class,
            'persetujuan_cuti_id'
        );
    }

    public function user()
    {
        return $this->belongsTo(
            User::class,
            'user_id'
        );
    }

    public function getJenisLabelAttribute(): string
    {
        return match ($this->jenis_mutasi) {
            self::JENIS_PEMBERIAN_KUOTA => 'Pemberian Kuota',
            self::JENIS_AKUMULASI => 'Tambahan Akumulasi',
            self::JENIS_RESERVASI => 'Reservasi Kuota',
            self::JENIS_PELEPASAN_RESERVASI => 'Pengembalian Reservasi',
            self::JENIS_PENGGUNAAN => 'Penggunaan Kuota',
            self::JENIS_PENYESUAIAN_TAMBAH => 'Penambahan Kuota',
            self::JENIS_PENYESUAIAN_KURANG => 'Pengurangan Kuota',
            self::JENIS_KEDALUWARSA => 'Kuota Kedaluwarsa',
            self::JENIS_RESET_AKUMULASI => 'Reset Akumulasi',
            self::JENIS_KOREKSI => 'Koreksi Kuota',
            default => 'Tidak Diketahui',
        };
    }

    public function getJenisColorAttribute(): string
    {
        return match ($this->jenis_mutasi) {
            self::JENIS_PEMBERIAN_KUOTA,
            self::JENIS_AKUMULASI,
            self::JENIS_PELEPASAN_RESERVASI,
            self::JENIS_PENYESUAIAN_TAMBAH => 'success',

            self::JENIS_RESERVASI => 'warning',

            self::JENIS_PENGGUNAAN => 'primary',

            self::JENIS_PENYESUAIAN_KURANG,
            self::JENIS_KEDALUWARSA,
            self::JENIS_RESET_AKUMULASI => 'danger',

            self::JENIS_KOREKSI => 'info',

            default => 'secondary',
        };
    }

    public function getPerubahanLabelAttribute(): string
    {
        if ($this->perubahan_sisa === null) {
            return 'Tanpa Batas';
        }

        if ($this->perubahan_sisa > 0) {
            return '+' . $this->perubahan_sisa . ' hari';
        }

        return $this->perubahan_sisa . ' hari';
    }

    public function isPenambahan(): bool
    {
        return $this->perubahan_sisa !== null
            && $this->perubahan_sisa > 0;
    }

    public function isPengurangan(): bool
    {
        return $this->perubahan_sisa !== null
            && $this->perubahan_sisa < 0;
    }

    public function isTanpaPerubahan(): bool
    {
        return $this->perubahan_sisa === 0;
    }

    public function isTanpaBatas(): bool
    {
        return $this->perubahan_sisa === null;
    }

    public function isOtomatis(): bool
    {
        return $this->user_id === null;
    }

    public function scopeKuota($query, int $kuotaId)
    {
        return $query->where(
            'kuota_cuti_id',
            $kuotaId
        );
    }

    public function scopePengajuan($query, int $pengajuanId)
    {
        return $query->where(
            'pengajuan_cuti_id',
            $pengajuanId
        );
    }

    public function scopeJenis($query, string $jenis)
    {
        return $query->where(
            'jenis_mutasi',
            $jenis
        );
    }

    public function scopePenambahan($query)
    {
        return $query->where(
            'perubahan_sisa',
            '>',
            0
        );
    }

    public function scopePengurangan($query)
    {
        return $query->where(
            'perubahan_sisa',
            '<',
            0
        );
    }

    public function scopeOtomatis($query)
    {
        return $query->whereNull('user_id');
    }

    public function scopeTahun($query, int $tahun)
    {
        return $query->whereYear(
            'terjadi_pada',
            $tahun
        );
    }

    public function scopeTerbaru($query)
    {
        return $query
            ->orderByDesc('terjadi_pada')
            ->orderByDesc('id');
    }
}
