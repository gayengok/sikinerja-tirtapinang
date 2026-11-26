<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TandaTanganPengguna extends Model
{
    use HasFactory;

    protected $table = 'tanda_tangan_pengguna';

    public const METODE_CANVAS = 'canvas';

    public const METODE_UPLOAD = 'upload';

    protected $fillable = [
        'user_id',
        'versi',
        'metode',
        'file_path',
        'file_hash',
        'mime_type',
        'ukuran_file',
        'lebar',
        'tinggi',
        'is_active',
        'diaktifkan_pada',
        'dinonaktifkan_pada',
    ];

    protected $hidden = [
        'file_path',
        'file_hash',
        'active_user_id',
    ];

    protected $attributes = [
        'is_active' => false,
        'mime_type' => 'image/png',
    ];

    protected $appends = [
        'metode_label',
        'status_label',
        'status_color',
        'versi_label',
        'ukuran_file_label',
    ];

    protected function casts(): array
    {
        return [
            'versi' => 'integer',
            'ukuran_file' => 'integer',
            'lebar' => 'integer',
            'tinggi' => 'integer',
            'is_active' => 'boolean',
            'diaktifkan_pada' => 'datetime',
            'dinonaktifkan_pada' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(
            User::class,
            'user_id'
        );
    }

    public function suratPermohonanSebagaiPemohon()
    {
        return $this->hasMany(
            SuratPermohonanCuti::class,
            'tanda_tangan_pemohon_id'
        );
    }

    public function getMetodeLabelAttribute(): string
    {
        return match ($this->metode) {
            self::METODE_CANVAS => 'Gambar Langsung',
            self::METODE_UPLOAD => 'Unggah Gambar',
            default => 'Tidak Diketahui',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->is_active
            ? 'Aktif'
            : 'Nonaktif';
    }

    public function getStatusColorAttribute(): string
    {
        return $this->is_active
            ? 'success'
            : 'secondary';
    }

    public function getVersiLabelAttribute(): string
    {
        return 'Versi ' . $this->versi;
    }

    public function getUkuranFileLabelAttribute(): string
    {
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

    public function isAktif(): bool
    {
        return $this->is_active === true;
    }

    public function isNonaktif(): bool
    {
        return $this->is_active === false;
    }

    public function isCanvas(): bool
    {
        return $this->metode === self::METODE_CANVAS;
    }

    public function isUpload(): bool
    {
        return $this->metode === self::METODE_UPLOAD;
    }

    public function scopeAktif($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeNonaktif($query)
    {
        return $query->where('is_active', false);
    }

    public function scopePengguna($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeMetode($query, string $metode)
    {
        return $query->where('metode', $metode);
    }

    public function scopeTerbaru($query)
    {
        return $query
            ->orderByDesc('versi')
            ->orderByDesc('id');
    }
}
