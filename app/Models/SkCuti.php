<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SkCuti extends Model
{
    use HasFactory;

    protected $table = 'sk_cuti';

    protected $fillable = [
        'pengajuan_cuti_id',
        'persetujuan_cuti_id',
        'surat_permohonan_cuti_id',
        'nomor_sk',
        'tanggal_sk',
        'tanggal_mulai_cuti',
        'tanggal_selesai_cuti',
        'jumlah_hari_disetujui',
        'tanggal_disetujui_snapshot',
        'pemohon_snapshot',
        'direktur_snapshot',
        'nama_file_asli',
        'file_path',
        'file_hash',
        'mime_type',
        'ukuran_file',
        'catatan',
        'diunggah_oleh',
        'diunggah_pada',
        'diterbitkan_pada',
    ];

    protected $hidden = [
        'file_path',
        'file_hash',
        'tanggal_disetujui_snapshot',
        'pemohon_snapshot',
        'direktur_snapshot',
    ];

    protected $attributes = [
        'mime_type' => 'application/pdf',
    ];

    protected $appends = [
        'status_label',
        'status_color',
        'ukuran_file_label',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_sk' => 'date',
            'tanggal_mulai_cuti' => 'date',
            'tanggal_selesai_cuti' => 'date',
            'jumlah_hari_disetujui' => 'integer',
            'tanggal_disetujui_snapshot' => 'array',
            'pemohon_snapshot' => 'array',
            'direktur_snapshot' => 'array',
            'ukuran_file' => 'integer',
            'diunggah_pada' => 'datetime',
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

    public function persetujuanCuti()
    {
        return $this->belongsTo(
            PersetujuanCuti::class,
            'persetujuan_cuti_id'
        );
    }

    public function suratPermohonan()
    {
        return $this->belongsTo(
            SuratPermohonanCuti::class,
            'surat_permohonan_cuti_id'
        );
    }

    public function pengunggah()
    {
        return $this->belongsTo(
            User::class,
            'diunggah_oleh'
        );
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->sudahDiterbitkan()
            ? 'Diterbitkan'
            : 'Belum Diterbitkan';
    }

    public function getStatusColorAttribute(): string
    {
        return $this->sudahDiterbitkan()
            ? 'success'
            : 'warning';
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

    public function sudahDiterbitkan(): bool
    {
        return $this->diterbitkan_pada !== null;
    }

    public function belumDiterbitkan(): bool
    {
        return !$this->sudahDiterbitkan();
    }

    public function sudahDiunggah(): bool
    {
        return $this->diunggah_pada !== null;
    }

    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    public function memilikiFile(): bool
    {
        return filled($this->file_path)
            && filled($this->file_hash)
            && $this->ukuran_file > 0;
    }

    public function daftarTanggalDisetujui(): array
    {
        return $this->tanggal_disetujui_snapshot ?? [];
    }

    public function jumlahTanggalSnapshot(): int
    {
        return count(array_unique(
            $this->daftarTanggalDisetujui()
        ));
    }

    public function jumlahHariSesuaiSnapshot(): bool
    {
        return $this->jumlah_hari_disetujui > 0
            && $this->jumlah_hari_disetujui
            === $this->jumlahTanggalSnapshot();
    }

    public function metadataLengkap(): bool
    {
        return filled($this->nomor_sk)
            && $this->tanggal_sk !== null
            && $this->tanggal_mulai_cuti !== null
            && $this->tanggal_selesai_cuti !== null
            && $this->tanggal_mulai_cuti
            <= $this->tanggal_selesai_cuti
            && $this->jumlahHariSesuaiSnapshot()
            && $this->memilikiFile()
            && $this->isPdf()
            && $this->sudahDiunggah();
    }

    public function scopeDiterbitkan($query)
    {
        return $query->whereNotNull('diterbitkan_pada');
    }

    public function scopeBelumTerbit($query)
    {
        return $query->whereNull('diterbitkan_pada');
    }

    public function scopePengajuan($query, int $pengajuanId)
    {
        return $query->where(
            'pengajuan_cuti_id',
            $pengajuanId
        );
    }

    public function scopeNomorSk($query, string $nomorSk)
    {
        return $query->where('nomor_sk', $nomorSk);
    }

    public function scopeTahun($query, int $tahun)
    {
        return $query->whereYear('tanggal_sk', $tahun);
    }

    public function scopeTerbaru($query)
    {
        return $query
            ->orderByDesc('tanggal_sk')
            ->orderByDesc('id');
    }
}
