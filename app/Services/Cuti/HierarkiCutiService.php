<?php

namespace App\Services\Cuti;

use App\Models\Pegawai;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class HierarkiCutiService
{
    private const TIMEZONE = 'Asia/Jakarta';

    private const ALUR_PERSETUJUAN = [
        'pegawai' => [
            'atasan',
            'kepala_bagian',
            'direktur',
        ],
        'atasan' => [
            'kepala_bagian',
            'direktur',
        ],
        'kepala_bagian' => [
            'direktur',
        ],
    ];

    public function tentukanHierarki(
        int $pegawaiId
    ): array {
        return DB::transaction(function () use (
            $pegawaiId
        ) {
            $pegawai = Pegawai::query()
                ->lockForUpdate()
                ->findOrFail($pegawaiId);

            $user = User::query()
                ->lockForUpdate()
                ->find($pegawai->user_id);

            if (!$user) {
                $this->gagal(
                    'Akun pemohon tidak ditemukan.'
                );
            }

            if ($pegawai->status !== 'aktif') {
                $this->gagal(
                    'Pegawai tidak aktif tidak dapat mengajukan cuti.'
                );
            }

            $rolePemohon = $user->role;

            $alur = self::ALUR_PERSETUJUAN[$rolePemohon] ?? null;

            if ($alur === null) {
                $this->gagal(
                    'Role pemohon tidak diperbolehkan mengajukan cuti.'
                );
            }

            $dikunjungi = [
                (int) $pegawai->getKey(),
            ];

            $akunDikunjungi = [
                (int) $user->getKey(),
            ];

            $pegawaiSaatIni = $pegawai;

            $hierarki = [];

            foreach ($alur as $index => $roleTujuan) {
                $atasanId = $pegawaiSaatIni->atasan_id;

                if ($atasanId === null) {
                    $this->gagal(
                        "Hierarki persetujuan belum lengkap. {$pegawaiSaatIni->nama} belum memiliki atasan untuk tahap {$roleTujuan}."
                    );
                }

                $atasanId = (int) $atasanId;

                if (in_array(
                    $atasanId,
                    $dikunjungi,
                    true
                )) {
                    $this->gagal(
                        'Terdapat hubungan atasan yang berputar dalam hierarki persetujuan.'
                    );
                }

                $approver = Pegawai::query()
                    ->lockForUpdate()
                    ->find($atasanId);

                if (!$approver) {
                    $this->gagal(
                        'Data approver tidak ditemukan.'
                    );
                }

                if ($approver->status !== 'aktif') {
                    $this->gagal(
                        "Approver {$approver->nama} tidak berstatus aktif."
                    );
                }

                $akunApprover = User::query()
                    ->lockForUpdate()
                    ->find($approver->user_id);

                if (!$akunApprover) {
                    $this->gagal(
                        "Akun approver {$approver->nama} tidak ditemukan."
                    );
                }

                if (in_array(
                    (int) $akunApprover->getKey(),
                    $akunDikunjungi,
                    true
                )) {
                    $this->gagal(
                        'Akun approver tidak boleh digunakan lebih dari satu kali dalam hierarki.'
                    );
                }

                if ($akunApprover->role !== $roleTujuan) {
                    $this->gagal(
                        "Role approver {$approver->nama} tidak sesuai. Role yang diperlukan adalah {$roleTujuan}."
                    );
                }

                $hierarki[] = [
                    'urutan' => $index + 1,
                    'approver_id' => $approver->getKey(),
                    'user_id' => $akunApprover->getKey(),
                    'role_approver' => $roleTujuan,
                    'approver_snapshot' =>
                    $this->buatSnapshotApprover(
                        $approver,
                        $akunApprover
                    ),
                ];

                $dikunjungi[] =
                    (int) $approver->getKey();

                $akunDikunjungi[] =
                    (int) $akunApprover->getKey();

                $pegawaiSaatIni = $approver;
            }

            if ($pegawaiSaatIni->atasan_id !== null) {
                $this->gagal(
                    'Direktur sebagai approver terakhir tidak boleh memiliki atasan.'
                );
            }

            $approverPertama = $hierarki[0];

            return [
                'pegawai_id' => $pegawai->getKey(),
                'role_pemohon' => $rolePemohon,
                'jumlah_tahapan' => count($hierarki),
                'current_approver_id' =>
                $approverPertama['approver_id'],
                'status_pengajuan' =>
                'pending_' . $approverPertama['role_approver'],
                'hierarki' => $hierarki,
            ];
        }, 3);
    }

    private function buatSnapshotApprover(
        Pegawai $pegawai,
        User $user
    ): array {
        $pegawai->loadMissing('jabatan.divisi');

        return [
            'pegawai_id' => $pegawai->getKey(),
            'user_id' => $user->getKey(),
            'nama' => $pegawai->nama,
            'nip' => $pegawai->nip,
            'role' => $user->role,
            'jabatan_id' => $pegawai->jabatan_id,
            'nama_jabatan' =>
            $pegawai->jabatan?->nama_jabatan,
            'divisi_id' =>
            $pegawai->jabatan?->divisi_id,
            'nama_divisi' =>
            $pegawai->jabatan?->divisi?->nama_divisi,
            'atasan_id' => $pegawai->atasan_id,
            'status' => $pegawai->status,
            'snapshot_pada' => CarbonImmutable::now(
                self::TIMEZONE
            )->toIso8601String(),
        ];
    }

    private function gagal(
        string $pesan
    ): never {
        throw ValidationException::withMessages([
            'hierarki' => $pesan,
        ]);
    }
}