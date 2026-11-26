<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersetujuanCuti extends Model
{
    use HasFactory;

    protected $table = 'persetujuan_cuti';

    public const STATUS_WAITING = 'waiting';

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_PARTIALLY_APPROVED = 'partially_approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_SKIPPED = 'skipped';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'pengajuan_cuti_id',
        'approver_id',
        'role_approver',
        'urutan',
        'status',
        'jumlah_hari_diterima',
        'jumlah_hari_disetujui',
        'jumlah_hari_ditolak',
        'tanggal_diterima',
        'tanggal_disetujui',
        'tanggal_ditolak',
        'catatan',
        'approver_snapshot',
        'tanda_tangan_path',
        'token_verifikasi',
        'hash_verifikasi',
        'diterima_pada',
        'diproses_pada',
    ];

    protected $hidden = [
        'token_verifikasi',
        'hash_verifikasi',
        'tanda_tangan_path',
    ];

    protected $appends = [
        'status_label',
        'status_color',
        'role_label',
    ];

    protected function casts(): array
    {
        return [
            'urutan' => 'integer',
            'jumlah_hari_diterima' => 'integer',
            'jumlah_hari_disetujui' => 'integer',
            'jumlah_hari_ditolak' => 'integer',
            'tanggal_diterima' => 'array',
            'tanggal_disetujui' => 'array',
            'tanggal_ditolak' => 'array',
            'approver_snapshot' => 'array',
            'diterima_pada' => 'datetime',
            'diproses_pada' => 'datetime',
        ];
    }

    public function pengajuanCuti()
    {
        return $this->belongsTo(
            PengajuanCuti::class,
            'pengajuan_cuti_id'
        );
    }

    public function approver()
    {
        return $this->belongsTo(
            Pegawai::class,
            'approver_id'
        );
    }

    public function mutasiKuotas()
    {
        return $this->hasMany(
            MutasiKuotaCuti::class,
            'persetujuan_cuti_id'
        )->terbaru();
    }

    public function suratPermohonans()
    {
        return $this->hasMany(
            SuratPermohonanCuti::class,
            'persetujuan_cuti_id'
        )->terbaru();
    }

    public function skCuti()
    {
        return $this->hasOne(
            SkCuti::class,
            'persetujuan_cuti_id'
        );
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_WAITING => 'Menunggu Tahapan Sebelumnya',
            self::STATUS_PENDING => 'Menunggu Persetujuan',
            self::STATUS_APPROVED => 'Disetujui',
            self::STATUS_PARTIALLY_APPROVED => 'Disetujui Sebagian',
            self::STATUS_REJECTED => 'Ditolak',
            self::STATUS_SKIPPED => 'Tidak Dilanjutkan',
            self::STATUS_CANCELLED => 'Dibatalkan',
            default => 'Tidak Diketahui',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_WAITING => 'secondary',
            self::STATUS_PENDING => 'warning',
            self::STATUS_APPROVED => 'success',
            self::STATUS_PARTIALLY_APPROVED => 'info',
            self::STATUS_REJECTED => 'danger',
            self::STATUS_SKIPPED,
            self::STATUS_CANCELLED => 'secondary',
            default => 'secondary',
        };
    }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role_approver) {
            'atasan' => 'Atasan',
            'kepala_bagian' => 'Kepala Bagian',
            'direktur' => 'Direktur',
            default => 'Tidak Diketahui',
        };
    }

    public function isWaiting(): bool
    {
        return $this->status === self::STATUS_WAITING;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isPartiallyApproved(): bool
    {
        return $this->status === self::STATUS_PARTIALLY_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isSkipped(): bool
    {
        return $this->status === self::STATUS_SKIPPED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isDisetujui(): bool
    {
        return in_array($this->status, [
            self::STATUS_APPROVED,
            self::STATUS_PARTIALLY_APPROVED,
        ], true);
    }

    public function sudahDiproses(): bool
    {
        return in_array($this->status, [
            self::STATUS_APPROVED,
            self::STATUS_PARTIALLY_APPROVED,
            self::STATUS_REJECTED,
        ], true);
    }

    public function isFinal(): bool
    {
        return in_array($this->status, [
            self::STATUS_APPROVED,
            self::STATUS_PARTIALLY_APPROVED,
            self::STATUS_REJECTED,
            self::STATUS_SKIPPED,
            self::STATUS_CANCELLED,
        ], true);
    }

    public function bisaDiproses(): bool
    {
        return $this->isPending()
            && $this->jumlah_hari_diterima > 0;
    }

    public function memilikiTandaTangan(): bool
    {
        return $this->isDisetujui()
            && filled($this->tanda_tangan_path);
    }

    public function memilikiDataVerifikasi(): bool
    {
        return $this->sudahDiproses()
            && filled($this->token_verifikasi)
            && filled($this->hash_verifikasi);
    }

    public function scopeWaiting($query)
    {
        return $query->where(
            'status',
            self::STATUS_WAITING
        );
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
        return $query->whereIn('status', [
            self::STATUS_APPROVED,
            self::STATUS_PARTIALLY_APPROVED,
        ]);
    }

    public function scopeRejected($query)
    {
        return $query->where(
            'status',
            self::STATUS_REJECTED
        );
    }

    public function scopeSudahDiproses($query)
    {
        return $query->whereIn('status', [
            self::STATUS_APPROVED,
            self::STATUS_PARTIALLY_APPROVED,
            self::STATUS_REJECTED,
        ]);
    }

    public function scopeApprover($query, int $pegawaiId)
    {
        return $query->where(
            'approver_id',
            $pegawaiId
        );
    }

    public function scopeRole($query, string $role)
    {
        return $query->where(
            'role_approver',
            $role
        );
    }

    public function scopeBerurutan($query)
    {
        return $query->orderBy('urutan');
    }
}
