<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailPengajuanCuti extends Model
{
    use HasFactory;

    protected $table = 'detail_pengajuan_cuti';

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_EXCLUDED = 'excluded';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'pengajuan_cuti_id',
        'kuota_cuti_id',
        'tanggal',
        'is_hari_kerja',
        'dihitung_kuota',
        'status',
        'ditolak_oleh',
        'ditolak_pada',
        'disetujui_pada',
    ];

    protected $appends = [
        'status_label',
        'status_color',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'is_hari_kerja' => 'boolean',
            'dihitung_kuota' => 'boolean',
            'ditolak_pada' => 'datetime',
            'disetujui_pada' => 'datetime',
        ];
    }

    public function pengajuanCuti()
    {
        return $this->belongsTo(
            PengajuanCuti::class,
            'pengajuan_cuti_id'
        );
    }

    public function kuotaCuti()
    {
        return $this->belongsTo(
            KuotaCuti::class,
            'kuota_cuti_id'
        );
    }

    public function penolak()
    {
        return $this->belongsTo(
            Pegawai::class,
            'ditolak_oleh'
        );
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Dalam Pengajuan',
            self::STATUS_APPROVED => 'Disetujui',
            self::STATUS_REJECTED => 'Ditolak',
            self::STATUS_EXCLUDED => 'Tidak Dihitung',
            self::STATUS_CANCELLED => 'Dibatalkan',
            default => 'Tidak Diketahui',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'warning',
            self::STATUS_APPROVED => 'success',
            self::STATUS_REJECTED => 'danger',
            self::STATUS_EXCLUDED,
            self::STATUS_CANCELLED => 'secondary',
            default => 'secondary',
        };
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isExcluded(): bool
    {
        return $this->status === self::STATUS_EXCLUDED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isDihitungKuota(): bool
    {
        return (bool) $this->dihitung_kuota;
    }

    public function bisaDiproses(): bool
    {
        return $this->isPending()
            && $this->isDihitungKuota();
    }

    public function scopePending($query)
    {
        return $query->where(
            'status',
            self::STATUS_PENDING
        );
    }

    public function scopeApproved($query)
    {
        return $query->where(
            'status',
            self::STATUS_APPROVED
        );
    }

    public function scopeRejected($query)
    {
        return $query->where(
            'status',
            self::STATUS_REJECTED
        );
    }

    public function scopeExcluded($query)
    {
        return $query->where(
            'status',
            self::STATUS_EXCLUDED
        );
    }

    public function scopeDihitungKuota($query)
    {
        return $query->where(
            'dihitung_kuota',
            true
        );
    }

    public function scopeBisaDiproses($query)
    {
        return $query
            ->pending()
            ->dihitungKuota();
    }

    public function scopeTanggal($query, $tanggal)
    {
        return $query->whereDate(
            'tanggal',
            $tanggal
        );
    }

    public function scopeRentangTanggal(
        $query,
        $tanggalMulai,
        $tanggalSelesai
    ) {
        return $query->whereBetween('tanggal', [
            $tanggalMulai,
            $tanggalSelesai,
        ]);
    }
}
