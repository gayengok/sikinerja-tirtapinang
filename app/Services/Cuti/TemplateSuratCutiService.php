<?php

namespace App\Services\Cuti;

use App\Models\TemplateSuratCuti;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use LogicException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class TemplateSuratCutiService
{
    private const MAKSIMAL_TEXT_BYTE = 65535;

    private const UKURAN_KERTAS = [
        'A4',
        'A5',
        'F4',
        'Letter',
        'Legal',
    ];

    private const ORIENTASI = [
        'portrait',
        'landscape',
    ];

    private const FONT_FAMILY = [
        'DejaVu Sans',
        'DejaVu Serif',
        'Helvetica',
        'Times',
        'Courier',
    ];

    private const PLACEHOLDER_NOMOR = [
        'nomor_pengajuan',
        'id_pengajuan',
        'nomor_urut',
        'tahun',
        'bulan',
        'bulan_romawi',
        'tanggal',
        'kode_jenis_cuti',
    ];

    private const PLACEHOLDER_UNIK_NOMOR = [
        'nomor_pengajuan',
        'id_pengajuan',
        'nomor_urut',
    ];

    private const FIELD_TEMPLATE = [
        'nama_template',
        'nama_perusahaan',
        'alamat_perusahaan',
        'telepon_perusahaan',
        'email_perusahaan',
        'website_perusahaan',
        'judul_surat',
        'format_nomor_surat',
        'isi_pembuka',
        'isi_penutup',
        'tempat_terbit',
        'footer_surat',
        'pengaturan_tampilan',
    ];

    private const FIELD_OPSIONAL = [
        'alamat_perusahaan',
        'telepon_perusahaan',
        'email_perusahaan',
        'website_perusahaan',
        'tempat_terbit',
        'footer_surat',
    ];

    public function __construct(
        protected LogoTemplateCutiService $logoService
    ) {}

    public function buatTemplate(
        int $userId,
        array $data,
        string $tindakanLogo = 'tanpa_logo',
        ?UploadedFile $logo = null,
        ?string $aset = null
    ): TemplateSuratCuti {
        return $this->simpanTemplate($userId, null, $data, $tindakanLogo, $logo, $aset);
    }

    public function ubahTemplate(
        int $userId,
        int $templateId,
        array $data,
        string $tindakanLogo = 'tetap',
        ?UploadedFile $logo = null,
        ?string $aset = null
    ): TemplateSuratCuti {
        return $this->simpanTemplate($userId, $templateId, $data, $tindakanLogo, $logo, $aset);
    }

    public function buatVersiBaru(
        int $userId,
        int $templateId,
        array $data,
        string $tindakanLogo = 'tetap',
        ?UploadedFile $logo = null,
        ?string $aset = null
    ): TemplateSuratCuti {
        return $this->simpanTemplate($userId, null, $data, $tindakanLogo, $logo, $aset, $templateId);
    }

    public function aktifkan(int $userId, int $templateId): TemplateSuratCuti
    {
        return $this->ubahStatus($userId, $templateId, true);
    }

    public function nonaktifkan(int $userId, int $templateId): TemplateSuratCuti
    {
        return $this->ubahStatus($userId, $templateId, false);
    }

    private function ubahStatus(int $userId, int $templateId, bool $aktif): TemplateSuratCuti
    {
        return DB::transaction(function () use ($userId, $templateId, $aktif) {
            $this->validasiMaster($userId, true);
            $riwayat = TemplateSuratCuti::query()->permohonanCuti()->terbaru()->lockForUpdate()->get();
            $template = $riwayat->firstWhere('id', $templateId);
            if (!$template) {
                throw ValidationException::withMessages(['template' => 'Template tidak ditemukan.']);
            }
            if ($aktif) {
                $this->validasiData($template->only(self::FIELD_TEMPLATE));
                if ($template->logo_path !== null) {
                    $this->logoService->metadata($template->logo_path);
                }
            }
            if ($template->isAktif() === $aktif) {
                return $template;
            }
            $waktu = now();
            if ($aktif) {
                foreach ($riwayat as $lama) {
                    if ($lama->isAktif()) {
                        $lama->is_active = false;
                        $lama->dinonaktifkan_pada = $waktu;
                        if (!$lama->save()) {
                            throw new RuntimeException('Gagal menonaktifkan template sebelumnya.');
                        }
                    }
                }
                $template->diaktifkan_pada = $waktu;
                $template->dinonaktifkan_pada = null;
            } else {
                $template->dinonaktifkan_pada = $waktu;
            }
            $template->is_active = $aktif;
            if (!$template->save()) {
                throw new RuntimeException('Gagal mengubah status template.');
            }
            return $template->refresh();
        }, 1);
    }

    public function hapusTemplate(int $userId, int $templateId): void
    {
        DB::transaction(function () use ($userId, $templateId) {
            $this->validasiMaster($userId, true);
            $riwayat = TemplateSuratCuti::query()->permohonanCuti()->terbaru()->lockForUpdate()->get();
            $template = $riwayat->firstWhere('id', $templateId);
            if (!$template) {
                throw ValidationException::withMessages(['template' => 'Template tidak ditemukan.']);
            }
            $this->validasiTemplateDapatDiubah($template);
            if (!$template->delete()) {
                throw new RuntimeException('Gagal menghapus template.');
            }
        }, 1);
    }

    private function simpanTemplate(
        int $userId,
        ?int $templateId,
        array $data,
        string $tindakanLogo,
        ?UploadedFile $logo,
        ?string $aset,
        ?int $sumberId = null
    ): TemplateSuratCuti {
        if (DB::transactionLevel() !== 0) {
            throw new LogicException('Penyimpanan template wajib memiliki transaksi sendiri.');
        }

        $pathBaru = null;
        $siapCommit = false;

        try {
            return DB::transaction(function () use ($userId, $templateId, $data, $tindakanLogo, $logo, $aset, $sumberId, &$pathBaru, &$siapCommit) {
                $this->validasiMaster($userId, true);
                $hasil = $this->validasiData($data);
                $riwayat = TemplateSuratCuti::query()->permohonanCuti()->terbaru()->lockForUpdate()->get();

                if ($templateId === null) {
                    $versi = ($riwayat->first()?->versi ?? 0) + 1;
                    if ($versi > 4294967295) {
                        throw new LogicException('Versi template mencapai batas penyimpanan.');
                    }
                    $template = new TemplateSuratCuti([
                        'kode_template' => TemplateSuratCuti::KODE_PERMOHONAN_CUTI,
                        'versi' => $versi,
                        'dibuat_oleh' => $userId,
                        'is_active' => false,
                    ]);
                } else {
                    $template = $riwayat->firstWhere('id', $templateId);
                    if (!$template) {
                        throw ValidationException::withMessages(['template' => 'Template tidak ditemukan.']);
                    }
                    $this->validasiTemplateDapatDiubah($template);
                }

                $sumber = $sumberId === null ? $template : $riwayat->firstWhere('id', $sumberId);
                if (!$sumber) {
                    throw ValidationException::withMessages(['template' => 'Template sumber tidak ditemukan.']);
                }
                $hasil['logo_path'] = $this->simpanLogo($sumber, $tindakanLogo, $logo, $aset, $pathBaru);
                $template->fill($hasil);
                if (!$template->save()) {
                    throw new RuntimeException('Gagal menyimpan template surat cuti.');
                }
                $template->refresh();
                $siapCommit = true;
                return $template;
            }, 1);
        } catch (Throwable $e) {
            if ($pathBaru !== null && !$siapCommit && DB::transactionLevel() === 0) {
                $this->bersihkanLogoGagal($pathBaru);
            }
            throw $e;
        }
    }

    private function validasiTemplateDapatDiubah(TemplateSuratCuti $template): void
    {
        if ($template->isAktif() || $template->sudahDigunakan() || $template->diaktifkan_pada !== null) {
            throw ValidationException::withMessages(['template' => 'Template aktif, pernah aktif atau pernah digunakan tidak dapat diubah langsung.']);
        }
    }

    private function simpanLogo(
        TemplateSuratCuti $template,
        string $tindakan,
        ?UploadedFile $logo,
        ?string $aset,
        ?string &$pathBaru
    ): ?string {
        if (
            !in_array($tindakan, ['tetap', 'tanpa_logo', 'bawaan', 'upload'], true)
            || ($tindakan === 'upload') !== ($logo !== null)
            || ($aset !== null && $tindakan !== 'bawaan')
            || ($tindakan === 'tetap' && !$template->exists)
        ) {
            throw ValidationException::withMessages(['logo' => 'Tindakan logo tidak valid.']);
        }

        if ($tindakan === 'tetap') {
            if ($template->logo_path !== null) {
                $this->logoService->metadata($template->logo_path);
            }
            return $template->logo_path;
        }
        if ($tindakan === 'tanpa_logo') {
            return null;
        }
        $metadata = $tindakan === 'upload'
            ? $this->logoService->simpanDariUpload($logo)
            : $this->logoService->simpanDariAsetBawaan($aset ?? 'img/logo-pdf.jpg');
        $pathBaru = $metadata['file_path'];
        return $pathBaru;
    }

    private function bersihkanLogoGagal(string $path): void
    {
        try {
            $disk = Storage::disk('local');
            if ($disk->exists($path) && !$disk->delete($path)) {
                throw new RuntimeException('Gagal membersihkan logo template.');
            }
        } catch (Throwable $e) {
            report($e);
        }
    }

    public function validasiMaster(
        int $userId,
        bool $gunakanLock = false
    ): User {
        if (
            $gunakanLock
            && DB::transactionLevel() === 0
        ) {
            throw new LogicException(
                'Lock pengguna wajib berada dalam transaksi database.'
            );
        }

        $query = User::query()
            ->whereKey($userId);

        if ($gunakanLock) {
            $query->lockForUpdate();
        }

        $user = $query->first();

        if (!$user || $user->role !== 'master') {
            throw ValidationException::withMessages([
                'user_id' =>
                'Pengguna tidak berhak mengelola template surat cuti.',
            ]);
        }

        return $user;
    }

    public function validasiData(
        array $data
    ): array {
        $fieldTidakValid = array_diff(
            array_keys($data),
            self::FIELD_TEMPLATE
        );

        if ($fieldTidakValid !== []) {
            throw ValidationException::withMessages([
                'template' =>
                'Terdapat field template yang tidak diperbolehkan.',
            ]);
        }

        foreach ($data as $field => $nilai) {
            if (is_string($nilai)) {
                $data[$field] = trim($nilai);
            }
        }

        foreach (self::FIELD_OPSIONAL as $field) {
            if (
                !array_key_exists($field, $data)
                || $data[$field] === ''
            ) {
                $data[$field] = null;
            }
        }

        if (!array_key_exists('judul_surat', $data)) {
            $data['judul_surat'] =
                'SURAT PERMOHONAN CUTI';
        }

        $hasil = Validator::make($data, [
            'nama_template' => [
                'required',
                'string',
                'max:150',
            ],
            'nama_perusahaan' => [
                'required',
                'string',
                'max:200',
            ],
            'alamat_perusahaan' => [
                'nullable',
                'string',
            ],
            'telepon_perusahaan' => [
                'nullable',
                'string',
                'max:50',
            ],
            'email_perusahaan' => [
                'nullable',
                'string',
                'email:rfc',
                'max:150',
            ],
            'website_perusahaan' => [
                'nullable',
                'string',
                'url:http,https',
                'max:255',
            ],
            'judul_surat' => [
                'required',
                'string',
                'max:200',
            ],
            'format_nomor_surat' => [
                'required',
                'string',
                'max:150',
            ],
            'isi_pembuka' => [
                'required',
                'string',
            ],
            'isi_penutup' => [
                'required',
                'string',
            ],
            'tempat_terbit' => [
                'nullable',
                'string',
                'max:100',
            ],
            'footer_surat' => [
                'nullable',
                'string',
            ],
            'pengaturan_tampilan' => [
                'nullable',
                'array',
            ],
        ])->validate();

        foreach (
            [
                'alamat_perusahaan',
                'isi_pembuka',
                'isi_penutup',
                'footer_surat',
            ] as $field
        ) {
            $nilai = $hasil[$field] ?? null;

            if (
                $nilai !== null
                && strlen($nilai) > self::MAKSIMAL_TEXT_BYTE
            ) {
                throw ValidationException::withMessages([
                    $field =>
                    'Isi teks melebihi kapasitas penyimpanan.',
                ]);
            }
        }

        foreach ($hasil as $field => $nilai) {
            if (!is_string($nilai)) {
                continue;
            }

            if (
                !mb_check_encoding($nilai, 'UTF-8')
                || preg_match(
                    '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/',
                    $nilai
                )
            ) {
                throw ValidationException::withMessages([
                    $field =>
                    'Teks mengandung karakter yang tidak valid.',
                ]);
            }
        }

        $hasil['format_nomor_surat'] =
            $this->validasiFormatNomor(
                $hasil['format_nomor_surat']
            );

        $hasil['pengaturan_tampilan'] =
            $this->validasiPengaturan(
                $hasil['pengaturan_tampilan'] ?? []
            );

        return $hasil;
    }

    public function validasiPengaturan(
        array $pengaturan
    ): array {
        $default = (new TemplateSuratCuti())
            ->pengaturanDefault();

        $fieldTidakValid = array_diff(
            array_keys($pengaturan),
            array_keys($default)
        );

        if ($fieldTidakValid !== []) {
            throw ValidationException::withMessages([
                'pengaturan_tampilan' =>
                'Terdapat pengaturan tampilan yang tidak diperbolehkan.',
            ]);
        }

        $hasil = array_replace(
            $default,
            $pengaturan
        );

        if (
            !in_array(
                $hasil['ukuran_kertas'],
                self::UKURAN_KERTAS,
                true
            )
        ) {
            throw ValidationException::withMessages([
                'pengaturan_tampilan.ukuran_kertas' =>
                'Ukuran kertas tidak valid.',
            ]);
        }

        if (
            !in_array(
                $hasil['orientasi'],
                self::ORIENTASI,
                true
            )
        ) {
            throw ValidationException::withMessages([
                'pengaturan_tampilan.orientasi' =>
                'Orientasi kertas tidak valid.',
            ]);
        }

        if (
            !in_array(
                $hasil['font_family'],
                self::FONT_FAMILY,
                true
            )
        ) {
            throw ValidationException::withMessages([
                'pengaturan_tampilan.font_family' =>
                'Jenis font tidak diperbolehkan.',
            ]);
        }

        $batasNumerik = [
            'font_size' => [8, 18],
            'margin_atas' => [5, 40],
            'margin_bawah' => [5, 40],
            'margin_kiri' => [5, 40],
            'margin_kanan' => [5, 40],
            'lebar_logo' => [10, 60],
            'tinggi_logo' => [10, 60],
        ];

        foreach ($batasNumerik as $field => $batas) {
            $nilai = $hasil[$field];

            if (
                !is_int($nilai)
                || $nilai < $batas[0]
                || $nilai > $batas[1]
            ) {
                throw ValidationException::withMessages([
                    'pengaturan_tampilan.' . $field =>
                    "Nilai {$field} harus berupa bilangan bulat antara {$batas[0]} dan {$batas[1]}.",
                ]);
            }
        }

        foreach (
            [
                'tampilkan_qr_code',
                'tampilkan_nip',
                'tampilkan_divisi',
                'tampilkan_jabatan',
            ] as $field
        ) {
            if (!is_bool($hasil[$field])) {
                throw ValidationException::withMessages([
                    'pengaturan_tampilan.' . $field =>
                    "Nilai {$field} harus berupa boolean.",
                ]);
            }
        }

        return $hasil;
    }

    public function validasiFormatNomor(
        string $format
    ): string {
        $format = trim($format);

        if (
            $format === ''
            || mb_strlen($format) > 150
        ) {
            throw ValidationException::withMessages([
                'format_nomor_surat' =>
                'Format nomor surat wajib diisi dan maksimal 150 karakter.',
            ]);
        }

        preg_match_all(
            '/\{([a-z_]+)\}/',
            $format,
            $matches
        );

        $placeholder = $matches[1] ?? [];

        if (
            array_diff(
                $placeholder,
                self::PLACEHOLDER_NOMOR
            ) !== []
        ) {
            throw ValidationException::withMessages([
                'format_nomor_surat' =>
                'Format nomor surat memiliki placeholder yang tidak dikenali.',
            ]);
        }

        if (
            array_intersect(
                $placeholder,
                self::PLACEHOLDER_UNIK_NOMOR
            ) === []
        ) {
            throw ValidationException::withMessages([
                'format_nomor_surat' =>
                'Format nomor surat wajib memiliki identitas unik pengajuan.',
            ]);
        }

        $teksTetap = preg_replace(
            '/\{[a-z_]+\}/',
            '',
            $format
        );

        if (
            !is_string($teksTetap)
            || !preg_match(
                '/\A[A-Za-z0-9\/._ -]*\z/',
                $teksTetap
            )
        ) {
            throw ValidationException::withMessages([
                'format_nomor_surat' =>
                'Format nomor surat mengandung karakter yang tidak diperbolehkan.',
            ]);
        }

        return $format;
    }
}