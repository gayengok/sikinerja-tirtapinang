<?php

namespace App\Services\Cuti;

use App\Models\TemplateSuratCuti;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class SnapshotTemplateSuratCutiService
{
    public function __construct(
        protected LogoTemplateCutiService $logoService
    ) {}

    public function ambilTemplateAktif(): TemplateSuratCuti
    {
        $template = TemplateSuratCuti::query()
            ->permohonanCuti()
            ->aktif()
            ->first();

        if (!$template) {
            throw ValidationException::withMessages([
                'template' => 'Belum tersedia template surat permohonan cuti yang aktif.',
            ]);
        }

        if (
            $template->kode_template !== TemplateSuratCuti::KODE_PERMOHONAN_CUTI
            || !$template->isAktif()
        ) {
            throw new RuntimeException(
                'Template aktif tidak memenuhi ketentuan.'
            );
        }

        return $template;
    }

    public function buatSnapshotAktif(): array
    {
        $template = $this->ambilTemplateAktif();

        $snapshot = $template->snapshotTemplate();

        $snapshot['logo_metadata'] = $template->logo_path !== null
            ? $this->ambilMetadataLogo($template->logo_path)
            : null;

        return $snapshot;
    }

    public function bacaLogoSnapshot(array $snapshot): ?string
    {
        $path = $snapshot['logo_path'] ?? null;
        $metadataSnapshot = $snapshot['logo_metadata'] ?? null;

        if ($path === null) {
            if ($metadataSnapshot !== null) {
                throw new RuntimeException(
                    'Metadata logo tidak sesuai dengan snapshot template.'
                );
            }

            return null;
        }

        if (
            !is_string($path)
            || !is_array($metadataSnapshot)
        ) {
            throw new RuntimeException(
                'Snapshot logo tidak lengkap.'
            );
        }

        $this->validasiMetadataSnapshot(
            $path,
            $metadataSnapshot
        );

        $metadataAktual = $this->logoService->metadata($path);

        $this->bandingkanMetadata(
            $metadataSnapshot,
            $metadataAktual
        );

        $isiFile = $this->logoService->bacaFile($path);

        if (
            !hash_equals(
                $metadataSnapshot['file_hash'],
                hash('sha256', $isiFile)
            )
        ) {
            throw new RuntimeException(
                'Integritas file logo historis tidak valid.'
            );
        }

        return $isiFile;
    }

    public function logoDataUriSnapshot(array $snapshot): ?string
    {
        $isiFile = $this->bacaLogoSnapshot($snapshot);

        if ($isiFile === null) {
            return null;
        }

        return 'data:image/png;base64,' . base64_encode($isiFile);
    }

    private function ambilMetadataLogo(string $path): array
    {
        $metadata = $this->logoService->metadata($path);

        if ($metadata['mime_type'] !== 'image/png') {
            throw new RuntimeException(
                'Logo template wajib menggunakan format PNG.'
            );
        }

        return [
            'file_path' => $metadata['file_path'],
            'file_hash' => $metadata['file_hash'],
            'mime_type' => $metadata['mime_type'],
            'ukuran_file' => $metadata['ukuran_file'],
            'lebar' => $metadata['lebar'],
            'tinggi' => $metadata['tinggi'],
        ];
    }

    private function validasiMetadataSnapshot(
        string $path,
        array $metadata
    ): void {
        $fieldWajib = [
            'file_path',
            'file_hash',
            'mime_type',
            'ukuran_file',
            'lebar',
            'tinggi',
        ];

        if (
            array_diff($fieldWajib, array_keys($metadata)) !== []
            || $metadata['file_path'] !== $path
            || !is_string($metadata['file_hash'])
            || !preg_match('/\A[a-f0-9]{64}\z/', $metadata['file_hash'])
            || $metadata['mime_type'] !== 'image/png'
            || !is_int($metadata['ukuran_file'])
            || $metadata['ukuran_file'] <= 0
            || !is_int($metadata['lebar'])
            || !is_int($metadata['tinggi'])
        ) {
            throw new RuntimeException(
                'Metadata logo historis tidak valid.'
            );
        }
    }

    private function bandingkanMetadata(
        array $snapshot,
        array $aktual
    ): void {
        foreach (
            [
                'file_path',
                'mime_type',
                'ukuran_file',
                'lebar',
                'tinggi',
            ] as $field
        ) {
            if ($snapshot[$field] !== $aktual[$field]) {
                throw new RuntimeException(
                    'Metadata logo historis mengalami perubahan.'
                );
            }
        }

        if (
            !hash_equals(
                $snapshot['file_hash'],
                $aktual['file_hash']
            )
        ) {
            throw new RuntimeException(
                'Hash logo historis tidak sesuai.'
            );
        }
    }
}