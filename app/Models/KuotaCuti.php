<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KuotaCuti extends Model
{
    use HasFactory;

    protected $table = 'kuota_cuti';

    protected $fillable = [
        'pegawai_id',
        'jenis_cuti_id',
        'kode_periode',
        'tahun',
        'tanggal_mulai_periode',
        'tanggal_akhir_periode',
        'kuota_dasar',
        'kuota_akumulasi',
        'kuota_penyesuaian',
        'kuota_terpakai',
        'kuota_direservasi',
        'kuota_hangus',
        'tahun_ke_siklus',
        'kebijakan_snapshot',
        'is_active',
    ];

    protected $appends = [
        'total_kuota',
        'sisa_kuota',
        'tanpa_batas',
    ];

    protected function casts(): array
    {
        return [
            'tahun' => 'integer',
            'tanggal_mulai_periode' => 'date',
            'tanggal_akhir_periode' => 'date',
            'kuota_dasar' => 'integer',
            'kuota_akumulasi' => 'integer',
            'kuota_penyesuaian' => 'integer',
            'kuota_terpakai' => 'integer',
            'kuota_direservasi' => 'integer',
            'kuota_hangus' => 'integer',
            'tahun_ke_siklus' => 'integer',
            'kebijakan_snapshot' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class);
    }

    public function jenisCuti()
    {
        return $this->belongsTo(JenisCuti::class, 'jenis_cuti_id');
    }

    public function detailPengajuanCutis()
    {
        return $this->hasMany(
            DetailPengajuanCuti::class,
            'kuota_cuti_id'
        );
    }

    public function mutasis()
    {
        return $this->hasMany(
            MutasiKuotaCuti::class,
            'kuota_cuti_id'
        )->terbaru();
    }

    public function mutasiPenambahan()
    {
        return $this->mutasis()->penambahan();
    }

    public function mutasiPengurangan()
    {
        return $this->mutasis()->pengurangan();
    }

    public function snapshotSaldo(): array
    {
        return [
            'kuota_dasar' => $this->kuota_dasar,
            'kuota_akumulasi' => (int) $this->kuota_akumulasi,
            'kuota_penyesuaian' => (int) $this->kuota_penyesuaian,
            'kuota_terpakai' => (int) $this->kuota_terpakai,
            'kuota_direservasi' => (int) $this->kuota_direservasi,
            'kuota_hangus' => (int) $this->kuota_hangus,
            'total_kuota' => $this->total_kuota,
            'sisa_kuota' => $this->sisa_kuota,
            'tanpa_batas' => $this->tanpa_batas,
        ];
    }

    public function getTanpaBatasAttribute(): bool
    {
        $attributes = $this->getAttributes();

        if (!array_key_exists('kuota_dasar', $attributes)) {
            return false;
        }

        if ($this->kuota_dasar !== null) {
            return false;
        }

        if ($this->kode_periode === 'TANPA-BATAS') {
            return true;
        }

        return ($this->kebijakan_snapshot['periode_kuota'] ?? null)
            === 'tanpa_batas';
    }

    public function getTotalKuotaAttribute(): ?int
    {
        if ($this->tanpa_batas) {
            return null;
        }

        return max(
            0,
            (int) $this->kuota_dasar
                + (int) $this->kuota_akumulasi
                + (int) $this->kuota_penyesuaian
        );
    }

    public function getSisaKuotaAttribute(): ?int
    {
        if ($this->tanpa_batas) {
            return null;
        }

        return max(
            0,
            $this->total_kuota
                - (int) $this->kuota_terpakai
                - (int) $this->kuota_direservasi
                - (int) $this->kuota_hangus
        );
    }

    public function scopeAktif($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeTahun($query, int $tahun)
    {
        return $query->where('tahun', $tahun);
    }

    public function scopePegawai($query, int $pegawaiId)
    {
        return $query->where('pegawai_id', $pegawaiId);
    }

    public function scopeJenisCuti($query, int $jenisCutiId)
    {
        return $query->where('jenis_cuti_id', $jenisCutiId);
    }

    public function scopeBerlakuPada($query, $tanggal)
    {
        return $query
            ->where('tanggal_mulai_periode', '<=', $tanggal)
            ->where(function ($query) use ($tanggal) {
                $query->whereNull('tanggal_akhir_periode')
                    ->orWhere('tanggal_akhir_periode', '>=', $tanggal);
            });
    }
}
