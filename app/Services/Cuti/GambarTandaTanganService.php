<?php

namespace App\Services\Cuti;

use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class GambarTandaTanganService
{
    public const MAKSIMAL_BYTE = 2097152;
    public const MINIMAL_LEBAR = 100;
    public const MINIMAL_TINGGI = 50;
    public const MAKSIMAL_DIMENSI = 2000;

    public function dariCanvas(string $dataUrl): array
    {
        $prefix = 'data:image/png;base64,';
        if (!str_starts_with($dataUrl, $prefix) || strlen($dataUrl) > strlen($prefix) + 4 * (int) ceil(self::MAKSIMAL_BYTE / 3)) {
            $this->gagal('Data canvas tidak valid atau terlalu besar.');
        }

        $encoded = substr($dataUrl, strlen($prefix));
        $bytes = base64_decode($encoded, true);
        if ($bytes === false || base64_encode($bytes) !== $encoded) {
            $this->gagal('Encoding Base64 tidak valid.');
        }

        return $this->normalisasi($bytes, ['image/png']);
    }

    public function dariUpload(UploadedFile $file): array
    {
        if (!$file->isValid() || $file->getSize() > self::MAKSIMAL_BYTE) {
            $this->gagal('Upload tidak valid atau melebihi 2 MB.');
        }

        $bytes = @file_get_contents($file->getPathname(), false, null, 0, self::MAKSIMAL_BYTE + 1);
        if ($bytes === false) {
            $this->gagal('File tidak dapat dibaca.');
        }

        return $this->normalisasi($bytes, ['image/png', 'image/jpeg']);
    }

    protected function normalisasi(string $bytes, array $mimeDiizinkan): array
    {
        if ($bytes === '' || strlen($bytes) > self::MAKSIMAL_BYTE) {
            $this->gagal('Ukuran gambar tidak valid.');
        }

        $mime = (new \finfo(FILEINFO_MIME_TYPE))->buffer($bytes);
        $info = @getimagesizefromstring($bytes);
        if ($info === false || !in_array($mime, $mimeDiizinkan, true) || $info['mime'] !== $mime) {
            $this->gagal('Format gambar tidak diizinkan.');
        }

        [$lebar, $tinggi] = $info;
        if ($lebar < self::MINIMAL_LEBAR || $tinggi < self::MINIMAL_TINGGI || $lebar > self::MAKSIMAL_DIMENSI || $tinggi > self::MAKSIMAL_DIMENSI) {
            $this->gagal('Dimensi gambar harus antara 100 x 50 dan 2000 x 2000 pixel.');
        }

        $gambar = @imagecreatefromstring($bytes);
        if ($gambar === false) {
            $this->gagal('Gambar rusak atau tidak dapat diproses.');
        }

        if (!imageistruecolor($gambar) && !imagepalettetotruecolor($gambar)) {
            $this->gagal('Gambar tidak dapat dinormalisasi.');
        }

        imagealphablending($gambar, false);
        imagesavealpha($gambar, true);
        $gelap = 0;
        $terang = 0;
        for ($y = 0; $y < $tinggi; $y++) {
            for ($x = 0; $x < $lebar; $x++) {
                $pixel = imagecolorat($gambar, $x, $y);
                $alpha = ($pixel >> 24) & 127;
                $r = ($pixel >> 16) & 255;
                $g = ($pixel >> 8) & 255;
                $b = $pixel & 255;
                $luma = (($r * 299 + $g * 587 + $b * 114) / 1000) * (127 - $alpha) / 127 + 255 * $alpha / 127;
                if ($luma < 200) {
                    $gelap++;
                } elseif ($luma > 235) {
                    $terang++;
                }
            }
        }

        if ($gelap < 20 || $terang < 20) {
            $this->gagal('Gambar harus memiliki goresan gelap dan latar terang atau transparan.');
        }

        ob_start();
        try {
            $berhasil = imagepng($gambar, null, 6);
            $png = ob_get_contents();
        } finally {
            ob_end_clean();
        }

        if (!$berhasil || !is_string($png) || $png === '' || strlen($png) > self::MAKSIMAL_BYTE) {
            $this->gagal('Hasil normalisasi PNG tidak valid atau melebihi 2 MB.');
        }

        return [
            'isi_file' => $png,
            'file_hash' => hash('sha256', $png),
            'mime_type' => 'image/png',
            'ukuran_file' => strlen($png),
            'lebar' => $lebar,
            'tinggi' => $tinggi,
        ];
    }

    protected function gagal(string $pesan): never
    {
        throw ValidationException::withMessages(['tanda_tangan' => $pesan]);
    }
}
