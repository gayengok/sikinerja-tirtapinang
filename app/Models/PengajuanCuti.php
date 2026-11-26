<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengajuanCuti extends Model
{
    use HasFactory;

    protected $table = 'pengajuan_cuti';

    public const STATUS_DRAFT = 'draft';

    public const STATUS_PENDING_ATASAN = 'pending_atasan';

    public const STATUS_PENDING_KEPALA_BAGIAN = 'pending_kepala_bagian';

    public const STATUS_PENDING_DIREKTUR = 'pending_direktur';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_PARTIALLY_APPROVED = 'partially_approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'nomor_pengajuan',
        'pegawai_id',
        'jenis_cuti_id',
        'role_pemohon',
        'tanggal_pengajuan',
        'tanggal_mulai',
        'tanggal_selesai',
        'jumlah_hari_diajukan',
        'jumlah_hari_disetujui',
        'alasan',
        'lampiran',
        'status',
        'current_approver_id',
        'kebijakan_snapshot',
        'pemohon_snapshot',
        'diajukan_pada',
        'diselesaikan_pada',
        'dibatalkan_pada',
        'alasan_pembatalan',
    ];

    protected $appends = [
        'status_label',
        'status_color',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_pengajuan' => 'date',
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'jumlah_hari_diajukan' => 'integer',
            'jumlah_hari_disetujui' => 'integer',
            'kebijakan_snapshot' => 'array',
            'pemohon_snapshot' => 'array',
            'diajukan_pada' => 'datetime',
            'diselesaikan_pada' => 'datetime',
            'dibatalkan_pada' => 'datetime',
        ];
    }

    public function pegawai()
    {
        return $this->belongsTo(
            Pegawai::class,
            'pegawai_id'
        );
    }

    public function jenisCuti()
    {
        return $this->belongsTo(
            JenisCuti::class,
            'jenis_cuti_id'
        );
    }

    public function currentApprover()
    {
        return $this->belongsTo(
            Pegawai::class,
            'current_approver_id'
        );
    }

    public function details()
    {
        return $this->hasMany(
            DetailPengajuanCuti::class,
            'pengajuan_cuti_id'
        )->orderBy('tanggal');
    }

    public function detailPending()
    {
        return $this->details()->pending();
    }

    public function detailDisetujui()
    {
        return $this->details()->approved();
    }

    public function detailDitolak()
    {
        return $this->details()->rejected();
    }

    public function detailDikecualikan()
    {
        return $this->details()->excluded();
    }

    public function detailBisaDiproses()
    {
        return $this->details()->bisaDiproses();
    }

    public function persetujuans()
    {
        return $this->hasMany(
            PersetujuanCuti::class,
            'pengajuan_cuti_id'
        )->orderBy('urutan');
    }

    public function persetujuanAktif()
    {
        return $this->hasOne(
            PersetujuanCuti::class,
            'pengajuan_cuti_id'
        )->pending()->orderBy('urutan');
    }

    public function persetujuanAtasan()
    {
        return $this->hasOne(
            PersetujuanCuti::class,
            'pengajuan_cuti_id'
        )->where('role_approver', 'atasan');
    }

    public function persetujuanKepalaBagian()
    {
        return $this->hasOne(
            PersetujuanCuti::class,
            'pengajuan_cuti_id'
        )->where('role_approver', 'kepala_bagian');
    }

    public function persetujuanDirektur()
    {
        return $this->hasOne(
            PersetujuanCuti::class,
            'pengajuan_cuti_id'
        )->where('role_approver', 'direktur');
    }

    public function persetujuanSelesai()
    {
        return $this->persetujuans()->sudahDiproses();
    }

    public function mutasiKuotas()
    {
        return $this->hasMany(
            MutasiKuotaCuti::class,
            'pengajuan_cuti_id'
        )->terbaru();
    }

    public function suratPermohonans()
    {
        return $this->hasMany(
            SuratPermohonanCuti::class,
            'pengajuan_cuti_id'
        )->terbaru();
    }

    public function suratPermohonanTerbaru()
    {
        return $this->hasOne(
            SuratPermohonanCuti::class,
            'pengajuan_cuti_id'
        )->versiAktif();
    }

    public function suratPermohonanFinal()
    {
        return $this->hasOne(
            SuratPermohonanCuti::class,
            'pengajuan_cuti_id'
        )->versiAktif()->final();
    }

    public function memilikiSuratPermohonan(): bool
    {
        return $this->suratPermohonanTerbaru()->exists();
    }

    public function memilikiSuratPermohonanFinal(): bool
    {
        return $this->suratPermohonanFinal()->exists();
    }

    public function skCuti()
    {
        return $this->hasOne(
            SkCuti::class,
            'pengajuan_cuti_id'
        );
    }

    public function skCutiTerbit()
    {
        return $this->hasOne(
            SkCuti::class,
            'pengajuan_cuti_id'
        )->diterbitkan();
    }

    public function memilikiSkCuti(): bool
    {
        return $this->skCuti()->exists();
    }

    public function memilikiSkCutiTerbit(): bool
    {
        return $this->skCutiTerbit()->exists();
    }

    public function terkunciOlehSk(): bool
    {
        return $this->memilikiSkCutiTerbit();
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_PENDING_ATASAN => 'Menunggu Atasan',
            self::STATUS_PENDING_KEPALA_BAGIAN => 'Menunggu Kepala Bagian',
            self::STATUS_PENDING_DIREKTUR => 'Menunggu Direktur',
            self::STATUS_APPROVED => 'Disetujui',
            self::STATUS_PARTIALLY_APPROVED => 'Disetujui Sebagian',
            self::STATUS_REJECTED => 'Ditolak',
            self::STATUS_CANCELLED => 'Dibatalkan',
            default => 'Tidak Diketahui',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'secondary',
            self::STATUS_PENDING_ATASAN,
            self::STATUS_PENDING_KEPALA_BAGIAN,
            self::STATUS_PENDING_DIREKTUR => 'warning',
            self::STATUS_APPROVED => 'success',
            self::STATUS_PARTIALLY_APPROVED => 'info',
            self::STATUS_REJECTED => 'danger',
            self::STATUS_CANCELLED => 'secondary',
            default => 'secondary',
        };
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isPending(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING_ATASAN,
            self::STATUS_PENDING_KEPALA_BAGIAN,
            self::STATUS_PENDING_DIREKTUR,
        ], true);
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

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isFinal(): bool
    {
        return in_array($this->status, [
            self::STATUS_APPROVED,
            self::STATUS_PARTIALLY_APPROVED,
            self::STATUS_REJECTED,
            self::STATUS_CANCELLED,
        ], true);
    }

    public function scopePegawai($query, int $pegawaiId)
    {
        return $query->where('pegawai_id', $pegawaiId);
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', [
            self::STATUS_PENDING_ATASAN,
            self::STATUS_PENDING_KEPALA_BAGIAN,
            self::STATUS_PENDING_DIREKTUR,
        ]);
    }

    public function scopeApproved($query)
    {
        return $query->whereIn('status', [
            self::STATUS_APPROVED,
            self::STATUS_PARTIALLY_APPROVED,
        ]);
    }

    public function scopeMenungguPersetujuan($query, int $pegawaiId)
    {
        return $query
            ->where('current_approver_id', $pegawaiId)
            ->pending();
    }

    public function scopeTahun($query, int $tahun)
    {
        return $query->whereYear('tanggal_pengajuan', $tahun);
    }
}
