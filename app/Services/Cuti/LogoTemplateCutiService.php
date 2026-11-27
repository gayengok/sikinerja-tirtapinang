<?php

namespace App\Services\Cuti;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use LogicException;
use RuntimeException;
use Throwable;

class LogoTemplateCutiService
{
    public const MAKSIMAL_BYTE = 2097152;

    public const MINIMAL_DIMENSI = 50;

    public const MAKSIMAL_DIMENSI = 2000;

    public const DIREKTORI = 'cuti/template/logo';

    private const ASET_BAWAAN = [
        'img/logo.png',
        'img/logo-pdf.jpg',
    ];

    public function dariUpload(
        UploadedFile $file
    ): array {
        if (
            !$file->isValid()
            || $file->getSize() === false
            || $file->getSize() <= 0
            || $file->getSize() > self::MAKSIMAL_BYTE
        ) {
            throw ValidationException::withMessages([
                'logo' => 'File logo tidak valid atau melebihi 2 MB.',
            ]);
        }

        $path = $file->getRealPath();

        if ($path === false) {
            throw ValidationException::withMessages([
                'logo' => 'File logo tidak dapat dibaca.',
            ]);
        }

        $isi = file_get_contents(
            $path,
            false,
            null,
            0,
            self::MAKSIMAL_BYTE + 1
        );

        if ($isi === false) {
            throw ValidationException::withMessages([
                'logo' => 'File logo tidak dapat dibaca.',
            ]);
        }

        return $this->normalisasi($isi);
    }

    public function dariAsetBawaan(
        string $path = 'img/logo-pdf.jpg'
    ): array {
        if (!in_array($path, self::ASET_BAWAAN, true)) {
            throw ValidationException::withMessages([
                'logo' => 'Aset logo bawaan tidak diperbolehkan.',
            ]);
        }

        $lokasi = public_path($path);

        if (
            !is_file($lokasi)
            || !is_readable($lokasi)
        ) {
            throw ValidationException::withMessages([
                'logo' => 'Aset logo bawaan tidak tersedia.',
            ]);
        }

        $isi = file_get_contents(
            $lokasi,
            false,
            null,
            0,
            self::MAKSIMAL_BYTE + 1
        );

        if ($isi === false) {
            throw ValidationException::withMessages([
                'logo' => 'Aset logo bawaan tidak dapat dibaca.',
            ]);
        }

        return $this->normalisasi($isi);
    }

    public function simpanDariUpload(
        UploadedFile $file
    ): array {
        return $this->simpan(
            $this->dariUpload($file)
        );
    }

    public function simpanDariAsetBawaan(
        string $path = 'img/logo-pdf.jpg'
    ): array {
        return $this->simpan(
            $this->dariAsetBawaan($path)
        );
    }

    public function bacaFile(
        string $path
    ): string {
        $this->validasiPath($path);

        $disk = Storage::disk('local');

        if (!$disk->exists($path)) {
            throw ValidationException::withMessages([
                'logo' => 'File logo tidak ditemukan.',
            ]);
        }

        $ukuran = $disk->size($path);

        if (
            $ukuran <= 0
            || $ukuran > self::MAKSIMAL_BYTE
        ) {
            throw ValidationException::withMessages([
                'logo' => 'Ukuran file logo tidak valid.',
            ]);
        }

        $isi = $disk->get($path);

        if (
            !is_string($isi)
            || strlen($isi) !== $ukuran
        ) {
            throw ValidationException::withMessages([
                'logo' => 'File logo tidak dapat dibaca.',
            ]);
        }

        $metadata = $this->periksaGambar($isi);

        if ($metadata['mime_type'] !== 'image/png') {
            throw ValidationException::withMessages([
                'logo' => 'Format logo tersimpan tidak valid.',
            ]);
        }

        return $isi;
    }

    public function metadata(
        string $path
    ): array {
        $isi = $this->bacaFile($path);

        return array_merge(
            ['file_path' => $path],
            $this->periksaGambar($isi)
        );
    }

    public function verifikasiIntegritas(
        string $path,
        string $hash
    ): bool {
        if (
            !preg_match(
                '/\A[a-f0-9]{64}\z/',
                $hash
            )
        ) {
            return false;
        }

        try {
            $isi = $this->bacaFile($path);

            return hash_equals(
                $hash,
                hash('sha256', $isi)
            );
        } catch (ValidationException) {
            return false;
        }
    }

    private function normalisasi(
        string $isi
    ): array {
        $this->periksaGambar($isi);

        $gambar = @imagecreatefromstring($isi);

        if ($gambar === false) {
            throw ValidationException::withMessages([
                'logo' => 'Gambar logo tidak dapat diproses.',
            ]);
        }

        $stream = null;

        try {
            if (
                !imageistruecolor($gambar)
                && !imagepalettetotruecolor($gambar)
            ) {
                throw new RuntimeException(
                    'Gagal mengonversi warna logo.'
                );
            }

            imagealphablending($gambar, false);
            imagesavealpha($gambar, true);

            $stream = fopen('php://memory', 'w+b');

            if ($stream === false) {
                throw new RuntimeException(
                    'Gagal membuka stream logo.'
                );
            }

            if (!imagepng($gambar, $stream, 6)) {
                throw new RuntimeException(
                    'Gagal menormalisasi logo.'
                );
            }

            rewind($stream);

            $png = stream_get_contents(
                $stream,
                self::MAKSIMAL_BYTE + 1
            );

            if (
                !is_string($png)
                || $png === ''
            ) {
                throw new RuntimeException(
                    'Hasil normalisasi logo tidak valid.'
                );
            }

            $metadata = $this->periksaGambar($png);

            if ($metadata['mime_type'] !== 'image/png') {
                throw new LogicException(
                    'Hasil normalisasi wajib berupa PNG.'
                );
            }

            return array_merge(
                ['isi_file' => $png],
                $metadata
            );
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }

            imagedestroy($gambar);
        }
    }

    private function periksaGambar(
        string $isi
    ): array {
        $ukuran = strlen($isi);

        if (
            $ukuran <= 0
            || $ukuran > self::MAKSIMAL_BYTE
        ) {
            throw ValidationException::withMessages([
                'logo' => 'Ukuran logo wajib lebih dari 0 dan maksimal 2 MB.',
            ]);
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);

        $mime = $finfo->buffer($isi);

        if (
            !in_array(
                $mime,
                ['image/png', 'image/jpeg'],
                true
            )
        ) {
            throw ValidationException::withMessages([
                'logo' => 'Logo wajib berupa gambar PNG atau JPEG.',
            ]);
        }

        $informasi = @getimagesizefromstring($isi);

        if (
            $informasi === false
            || ($informasi['mime'] ?? null) !== $mime
        ) {
            throw ValidationException::withMessages([
                'logo' => 'Struktur gambar logo tidak valid.',
            ]);
        }

        $lebar = (int) $informasi[0];
        $tinggi = (int) $informasi[1];

        if (
            $lebar < self::MINIMAL_DIMENSI
            || $tinggi < self::MINIMAL_DIMENSI
            || $lebar > self::MAKSIMAL_DIMENSI
            || $tinggi > self::MAKSIMAL_DIMENSI
        ) {
            throw ValidationException::withMessages([
                'logo' => 'Dimensi logo wajib antara 50 dan 2000 piksel.',
            ]);
        }

        return [
            'file_hash' => hash('sha256', $isi),
            'mime_type' => $mime,
            'ukuran_file' => $ukuran,
            'lebar' => $lebar,
            'tinggi' => $tinggi,
        ];
    }

    private function simpan(
        array $gambar
    ): array {
        $disk = Storage::disk('local');

        $path = sprintf(
            '%s/%s.png',
            self::DIREKTORI,
            Str::uuid()->toString()
        );

        if ($disk->exists($path)) {
            throw new RuntimeException(
                'Path penyimpanan logo sudah digunakan.'
            );
        }

        try {
            if (!$disk->put($path, $gambar['isi_file'])) {
                throw new RuntimeException(
                    'Gagal menyimpan file logo.'
                );
            }

            $metadata = $this->metadata($path);

            if (
                !hash_equals(
                    $gambar['file_hash'],
                    $metadata['file_hash']
                )
                || $gambar['ukuran_file']
                !== $metadata['ukuran_file']
                || $gambar['lebar']
                !== $metadata['lebar']
                || $gambar['tinggi']
                !== $metadata['tinggi']
            ) {
                throw new RuntimeException(
                    'Integritas logo tersimpan tidak sesuai.'
                );
            }

            return $metadata;
        } catch (Throwable $e) {
            try {
                if (
                    $disk->exists($path)
                    && !$disk->delete($path)
                ) {
                    throw new RuntimeException(
                        'Gagal membersihkan file logo.'
                    );
                }
            } catch (Throwable $cleanup) {
                report($cleanup);
            }

            throw $e;
        }
    }

    private function validasiPath(
        string $path
    ): void {
        if (
            !preg_match(
                '/\Acuti\/template\/logo\/[a-f0-9]{8}-[a-f0-9]{4}-4[a-f0-9]{3}-[89ab][a-f0-9]{3}-[a-f0-9]{12}\.png\z/',
                $path
            )
        ) {
            throw ValidationException::withMessages([
                'logo' => 'Path file logo tidak valid.',
            ]);
        }
    }
}