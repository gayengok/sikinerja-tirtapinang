<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuratPermohonanCuti extends Model
{
    use HasFactory;

    protected $table = 'surat_permohonan_cuti';

    public const STATUS_PREVIEW = 'preview';

    public const STATUS_FINAL = 'final';

    public const STATUS_SUPERSEDED = 'superseded';

    protected $fillable = [
        'pengajuan_cuti_id',
        'template_surat_cuti_id',
        'tanda_tangan_pemohon_id',
        'persetujuan_cuti_id',
        'nomor_surat',
        'versi',
        'status_dokumen',
        'status_pengajuan_snapshot',
        'template_snapshot',
        'pemohon_snapshot',
        'pengajuan_snapshot',
        'detail_snapshot',
        'persetujuan_snapshot',
        'tanda_tangan_snapshot',
        'file_path',
        'file_hash',
        'ukuran_file',
        'token_verifikasi',
        'is_latest',
        'dibuat_oleh',
        'diterbitkan_pada',
    ];

    protected $hidden = [
        'file_path',
        'file_hash',
        'token_verifikasi',
        'latest_pengajuan_id',
        'template_snapshot',
        'pemohon_snapshot',
        'pengajuan_snapshot',
        'detail_snapshot',
        'persetujuan_snapshot',
        'tanda_tangan_snapshot',
    ];

    protected $attributes = [
        'status_dokumen' => self::STATUS_PREVIEW,
        'is_latest' => false,
    ];

    protected $appends = [
        'status_label',
        'status_color',
        'versi_label',
        'ukuran_file_label',
    ];

    protected function casts(): array
    {
        return [
            'versi' => 'integer',
            'template_snapshot' => 'array',
            'pemohon_snapshot' => 'array',
            'pengajuan_snapshot' => 'array',
            'detail_snapshot' => 'array',
            'persetujuan_snapshot' => 'array',
            'tanda_tangan_snapshot' => 'array',
            'ukuran_file' => 'integer',
            'is_latest' => 'boolean',
            'diterbitkan_pada' => 'datetime',
        ];
    }

    public function pengajuanCuti()
    {
        return $this->belongsTo(
            PengajuanCuti::class,
            'pengajuan_cuti_id'
        );
    }

    public function templateSurat()
    {
        return $this->belongsTo(
            TemplateSuratCuti::class,
            'template_surat_cuti_id'
        );
    }

    public function tandaTanganPemohon()
    {
        return $this->belongsTo(
            TandaTanganPengguna::class,
            'tanda_tangan_pemohon_id'
        );
    }

    public function persetujuanCuti()
    {
        return $this->belongsTo(
            PersetujuanCuti::class,
            'persetujuan_cuti_id'
        );
    }

    public function pembuat()
    {
        return $this->belongsTo(
            User::class,
            'dibuat_oleh'
        );
    }

    public function skCuti()
    {
        return $this->hasOne(
            SkCuti::class,
            'surat_permohonan_cuti_id'
        );
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status_dokumen) {
            self::STATUS_PREVIEW => 'Preview',
            self::STATUS_FINAL => 'Final',
            self::STATUS_SUPERSEDED => 'Versi Lama',
            default => 'Tidak Diketahui',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status_dokumen) {
            self::STATUS_PREVIEW => 'warning',
            self::STATUS_FINAL => 'success',
            self::STATUS_SUPERSEDED => 'secondary',
            default => 'secondary',
        };
    }

    public function getVersiLabelAttribute(): string
    {
        return 'Versi ' . $this->versi;
    }

    public function getUkuranFileLabelAttribute(): string
    {
        if ($this->ukuran_file === null) {
            return '-';
        }

        $ukuran = (int) $this->ukuran_file;

        if ($ukuran >= 1048576) {
            return number_format(
                $ukuran / 1048576,
                2,
                ',',
                '.'
            ) . ' MB';
        }

        if ($ukuran >= 1024) {
            return number_format(
                $ukuran / 1024,
                2,
                ',',
                '.'
            ) . ' KB';
        }

        return $ukuran . ' B';
    }

    public function isPreview(): bool
    {
        return $this->status_dokumen === self::STATUS_PREVIEW;
    }

    public function isFinal(): bool
    {
        return $this->status_dokumen === self::STATUS_FINAL;
    }

    public function isSuperseded(): bool
    {
        return $this->status_dokumen === self::STATUS_SUPERSEDED;
    }

    public function isLatest(): bool
    {
        return $this->is_latest === true;
    }

    public function memilikiFile(): bool
    {
        return filled($this->file_path)
            && filled($this->file_hash)
            && $this->ukuran_file > 0;
    }

    public function sudahDiterbitkan(): bool
    {
        return $this->diterbitkan_pada !== null;
    }

    public function siapDiverifikasi(): bool
    {
        return $this->memilikiFile()
            && $this->sudahDiterbitkan()
            && filled($this->token_verifikasi);
    }

    public function scopePengajuan($query, int $pengajuanId)
    {
        return $query->where(
            'pengajuan_cuti_id',
            $pengajuanId
        );
    }

    public function scopeNomorSurat($query, string $nomorSurat)
    {
        return $query->where(
            'nomor_surat',
            $nomorSurat
        );
    }

    public function scopePreview($query)
    {
        return $query->where(
            'status_dokumen',
            self::STATUS_PREVIEW
        );
    }

    public function scopeFinal($query)
    {
        return $query->where(
            'status_dokumen',
            self::STATUS_FINAL
        );
    }

    public function scopeSuperseded($query)
    {
        return $query->where(
            'status_dokumen',
            self::STATUS_SUPERSEDED
        );
    }

    public function scopeVersiAktif($query)
    {
        return $query->where('is_latest', true);
    }

    public function scopeDiterbitkan($query)
    {
        return $query->whereNotNull('diterbitkan_pada');
    }

    public function scopeTokenVerifikasi($query, string $token)
    {
        return $query->where(
            'token_verifikasi',
            $token
        );
    }

    public function scopeTerbaru($query)
    {
        return $query
            ->orderByDesc('versi')
            ->orderByDesc('id');
    }
}
