<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemplateSuratCuti extends Model
{
    use HasFactory;

    protected $table = 'template_surat_cuti';

    public const KODE_PERMOHONAN_CUTI = 'PERMOHONAN-CUTI';

    protected $fillable = [
        'kode_template',
        'nama_template',
        'versi',
        'nama_perusahaan',
        'alamat_perusahaan',
        'telepon_perusahaan',
        'email_perusahaan',
        'website_perusahaan',
        'logo_path',
        'judul_surat',
        'format_nomor_surat',
        'isi_pembuka',
        'isi_penutup',
        'tempat_terbit',
        'footer_surat',
        'pengaturan_tampilan',
        'is_active',
        'dibuat_oleh',
        'diaktifkan_pada',
        'dinonaktifkan_pada',
    ];

    protected $hidden = [
        'active_slot',
    ];

    protected $attributes = [
        'kode_template' => self::KODE_PERMOHONAN_CUTI,
        'judul_surat' => 'SURAT PERMOHONAN CUTI',
        'is_active' => false,
    ];

    protected $appends = [
        'status_label',
        'status_color',
        'versi_label',
    ];

    protected function casts(): array
    {
        return [
            'versi' => 'integer',
            'pengaturan_tampilan' => 'array',
            'is_active' => 'boolean',
            'diaktifkan_pada' => 'datetime',
            'dinonaktifkan_pada' => 'datetime',
        ];
    }

    public function pembuat()
    {
        return $this->belongsTo(
            User::class,
            'dibuat_oleh'
        );
    }

    public function suratPermohonans()
    {
        return $this->hasMany(
            SuratPermohonanCuti::class,
            'template_surat_cuti_id'
        );
    }

    public function sudahDigunakan(): bool
    {
        return $this->suratPermohonans()->exists();
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

    public function isAktif(): bool
    {
        return $this->is_active === true;
    }

    public function isNonaktif(): bool
    {
        return $this->is_active === false;
    }

    public function memilikiLogo(): bool
    {
        return filled($this->logo_path);
    }

    public function pengaturanDefault(): array
    {
        return [
            'ukuran_kertas' => 'A4',
            'orientasi' => 'portrait',
            'font_family' => 'DejaVu Sans',
            'font_size' => 11,
            'margin_atas' => 20,
            'margin_bawah' => 20,
            'margin_kiri' => 20,
            'margin_kanan' => 20,
            'lebar_logo' => 25,
            'tinggi_logo' => 25,
            'tampilkan_qr_code' => true,
            'tampilkan_nip' => true,
            'tampilkan_divisi' => true,
            'tampilkan_jabatan' => true,
        ];
    }

    public function pengaturanEfektif(): array
    {
        return array_replace(
            $this->pengaturanDefault(),
            $this->pengaturan_tampilan ?? []
        );
    }

    public function snapshotTemplate(): array
    {
        return [
            'template_id' => $this->id,
            'kode_template' => $this->kode_template,
            'nama_template' => $this->nama_template,
            'versi' => $this->versi,
            'nama_perusahaan' => $this->nama_perusahaan,
            'alamat_perusahaan' => $this->alamat_perusahaan,
            'telepon_perusahaan' => $this->telepon_perusahaan,
            'email_perusahaan' => $this->email_perusahaan,
            'website_perusahaan' => $this->website_perusahaan,
            'logo_path' => $this->logo_path,
            'judul_surat' => $this->judul_surat,
            'format_nomor_surat' => $this->format_nomor_surat,
            'isi_pembuka' => $this->isi_pembuka,
            'isi_penutup' => $this->isi_penutup,
            'tempat_terbit' => $this->tempat_terbit,
            'footer_surat' => $this->footer_surat,
            'pengaturan_tampilan' => $this->pengaturanEfektif(),
        ];
    }

    public function scopeAktif($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeNonaktif($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeKode($query, string $kode)
    {
        return $query->where('kode_template', $kode);
    }

    public function scopePermohonanCuti($query)
    {
        return $query->where(
            'kode_template',
            self::KODE_PERMOHONAN_CUTI
        );
    }

    public function scopeTerbaru($query)
    {
        return $query
            ->orderByDesc('versi')
            ->orderByDesc('id');
    }
}
