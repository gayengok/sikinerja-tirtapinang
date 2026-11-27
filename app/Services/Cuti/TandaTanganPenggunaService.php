<?php

namespace App\Services\Cuti;

use App\Models\Pegawai;
use App\Models\User;
use App\Models\TandaTanganPengguna;
use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use LogicException;
use RuntimeException;
use Throwable;

class TandaTanganPenggunaService
{
    public function __construct(
        protected GambarTandaTanganService $gambarService
    ) {}

    public function validasiPengguna(int $userId, bool $gunakanLock = false): User
    {
        if ($gunakanLock && DB::transactionLevel() === 0) {
            throw new LogicException('Lock pengguna wajib berada dalam transaksi database.');
        }

        $pegawaiQuery = Pegawai::query()->where('user_id', $userId);
        $userQuery = User::query()->whereKey($userId);

        if ($gunakanLock) {
            $pegawaiQuery->lockForUpdate();
            $userQuery->lockForUpdate();
        }

        $pegawai = $pegawaiQuery->first();
        $user = $userQuery->first();

        if (!$user || !$pegawai || $pegawai->status !== 'aktif' || !in_array($user->role, ['pegawai', 'atasan', 'kepala_bagian', 'direktur'], true)) {
            throw ValidationException::withMessages([
                'user_id' => 'Pengguna tidak berhak mengelola tanda tangan.',
            ]);
        }

        return $user;
    }

    public function simpanDariCanvas(int $userId, string $dataUrl): TandaTanganPengguna
    {
        $this->validasiPengguna($userId);

        return $this->simpanTandaTangan($userId, TandaTanganPengguna::METODE_CANVAS, $this->gambarService->dariCanvas($dataUrl));
    }

    public function simpanDariUpload(int $userId, UploadedFile $file): TandaTanganPengguna
    {
        $this->validasiPengguna($userId);

        return $this->simpanTandaTangan($userId, TandaTanganPengguna::METODE_UPLOAD, $this->gambarService->dariUpload($file));
    }

    public function gantiDariCanvas(int $userId, string $dataUrl): TandaTanganPengguna
    {
        $this->validasiPengguna($userId);

        return $this->simpanTandaTangan($userId, TandaTanganPengguna::METODE_CANVAS, $this->gambarService->dariCanvas($dataUrl), true);
    }

    public function gantiDariUpload(int $userId, UploadedFile $file): TandaTanganPengguna
    {
        $this->validasiPengguna($userId);

        return $this->simpanTandaTangan($userId, TandaTanganPengguna::METODE_UPLOAD, $this->gambarService->dariUpload($file), true);
    }

    public function aktifkan(int $userId, int $tandaTanganId): TandaTanganPengguna
    {
        return $this->ubahStatus($userId, $tandaTanganId, true);
    }

    public function nonaktifkan(int $userId, int $tandaTanganId): TandaTanganPengguna
    {
        return $this->ubahStatus($userId, $tandaTanganId, false);
    }

    public function cariAktif(int $userId): ?TandaTanganPengguna
    {
        $this->validasiPengguna($userId);

        return TandaTanganPengguna::query()->pengguna($userId)->aktif()->first();
    }

    public function wajibAktif(int $userId): TandaTanganPengguna
    {
        $tandaTangan = $this->cariAktif($userId);
        if (!$tandaTangan) {
            throw ValidationException::withMessages(['tanda_tangan' => 'Pengguna belum memiliki tanda tangan aktif.']);
        }
        $this->pastikanIntegritasFile($tandaTangan);

        return $tandaTangan;
    }

    public function riwayat(int $userId): Collection
    {
        $this->validasiPengguna($userId);

        return TandaTanganPengguna::query()->pengguna($userId)->terbaru()->get();
    }

    public function verifikasiIntegritas(int $userId, int $tandaTanganId): bool
    {
        $tandaTangan = $this->cariMilikPengguna($userId, $tandaTanganId);
        try {
            $this->pastikanIntegritasFile($tandaTangan);
            return true;
        } catch (ValidationException $e) {
            return false;
        }
    }

    public function bacaFile(int $userId, int $tandaTanganId): string
    {
        return $this->pastikanIntegritasFile($this->cariMilikPengguna($userId, $tandaTanganId));
    }

    public function buatSnapshot(int $userId, int $tandaTanganId): array
    {
        return DB::transaction(function () use ($userId, $tandaTanganId) {
            $this->validasiPengguna($userId, true);
            $tandaTangan = TandaTanganPengguna::query()->pengguna($userId)->whereKey($tandaTanganId)->lockForUpdate()->first();
            if (!$tandaTangan || !$tandaTangan->is_active) {
                throw ValidationException::withMessages(['tanda_tangan' => 'Tanda tangan aktif tidak ditemukan.']);
            }
            $this->pastikanIntegritasFile($tandaTangan);

            return [
                'tanda_tangan_id' => $tandaTangan->id,
                'user_id' => $tandaTangan->user_id,
                'versi' => $tandaTangan->versi,
                'metode' => $tandaTangan->metode,
                'file_path' => $tandaTangan->file_path,
                'file_hash' => $tandaTangan->file_hash,
                'mime_type' => $tandaTangan->mime_type,
                'ukuran_file' => $tandaTangan->ukuran_file,
                'lebar' => $tandaTangan->lebar,
                'tinggi' => $tandaTangan->tinggi,
                'snapshot_pada' => now()->toIso8601String(),
            ];
        }, 1);
    }

    protected function cariMilikPengguna(int $userId, int $tandaTanganId): TandaTanganPengguna
    {
        $this->validasiPengguna($userId);
        $tandaTangan = TandaTanganPengguna::query()->pengguna($userId)->whereKey($tandaTanganId)->first();
        if (!$tandaTangan) {
            throw ValidationException::withMessages(['tanda_tangan' => 'Tanda tangan tidak ditemukan.']);
        }

        return $tandaTangan;
    }

    protected function ubahStatus(int $userId, int $tandaTanganId, bool $aktif): TandaTanganPengguna
    {
        return DB::transaction(function () use ($userId, $tandaTanganId, $aktif) {
            $this->validasiPengguna($userId, true);
            $riwayat = TandaTanganPengguna::query()->pengguna($userId)->orderByDesc('versi')->lockForUpdate()->get();
            $target = $riwayat->firstWhere('id', $tandaTanganId);

            if (!$target) {
                throw ValidationException::withMessages(['tanda_tangan' => 'Tanda tangan tidak ditemukan.']);
            }

            if ($aktif) {
                $this->pastikanIntegritasFile($target);
            }

            if ($target->is_active === $aktif) {
                return $target;
            }

            $waktu = now();

            if ($aktif) {
                foreach ($riwayat as $lama) {
                    if ($lama->is_active) {
                        $lama->is_active = false;
                        $lama->dinonaktifkan_pada = $waktu;
                        if (!$lama->save()) {
                            throw new RuntimeException('Gagal menonaktifkan tanda tangan sebelumnya.');
                        }
                    }
                }
                $target->diaktifkan_pada = $waktu;
                $target->dinonaktifkan_pada = null;
            } else {
                $target->dinonaktifkan_pada = $waktu;
            }

            $target->is_active = $aktif;
            if (!$target->save()) {
                throw new RuntimeException('Gagal mengubah status tanda tangan.');
            }

            return $target->refresh();
        }, 1);
    }

    protected function pastikanIntegritasFile(TandaTanganPengguna $tandaTangan): string
    {
        $prefix = sprintf('cuti/tanda-tangan/%d/v%d-', $tandaTangan->user_id, $tandaTangan->versi);
        $path = $tandaTangan->file_path;
        if (!is_string($path) || !preg_match('~^' . preg_quote($prefix, '~') . '[0-9a-fA-F-]{36}\.png$~D', $path)) {
            throw ValidationException::withMessages(['tanda_tangan' => 'Path tanda tangan tidak valid.']);
        }

        $disk = Storage::disk('local');
        if (!$disk->exists($path) || $disk->size($path) !== $tandaTangan->ukuran_file || $tandaTangan->ukuran_file <= 0 || $tandaTangan->ukuran_file > GambarTandaTanganService::MAKSIMAL_BYTE) {
            throw ValidationException::withMessages(['tanda_tangan' => 'File tanda tangan hilang atau tidak valid.']);
        }

        $isi = $disk->get($path);
        if (!is_string($isi) || strlen($isi) !== $tandaTangan->ukuran_file || !hash_equals($tandaTangan->file_hash, hash('sha256', $isi))) {
            throw ValidationException::withMessages(['tanda_tangan' => 'Integritas file tanda tangan tidak valid.']);
        }

        return $isi;
    }

    protected function simpanTandaTangan(int $userId, string $metode, array $gambar, bool $penggantian = false): TandaTanganPengguna
    {
        if (DB::transactionLevel() !== 0) {
            throw new LogicException('Penyimpanan tanda tangan wajib memiliki transaksi sendiri.');
        }

        $path = null;
        $siapCommit = false;

        try {
            return DB::transaction(function () use ($userId, $metode, $gambar, $penggantian, &$path, &$siapCommit) {
                $this->validasiPengguna($userId, true);

                $riwayat = TandaTanganPengguna::query()->pengguna($userId)->orderByDesc('versi')->lockForUpdate()->get();

                if (!$penggantian && $riwayat->isNotEmpty()) {
                    throw ValidationException::withMessages([
                        'tanda_tangan' => 'Pengguna sudah memiliki riwayat tanda tangan.',
                    ]);
                }

                if ($penggantian && $riwayat->isEmpty()) {
                    throw ValidationException::withMessages([
                        'tanda_tangan' => 'Pengguna belum memiliki riwayat tanda tangan.',
                    ]);
                }

                $versi = $riwayat->isEmpty() ? 1 : $riwayat->first()->versi + 1;

                if ($versi > 4294967295) {
                    throw new LogicException('Batas versi tanda tangan tercapai.');
                }

                $path = $this->buatPathFile($userId, $versi);
                $this->simpanFile($path, $gambar);

                $waktu = now();

                foreach ($riwayat as $lama) {
                    if ($lama->is_active) {
                        $lama->is_active = false;
                        $lama->dinonaktifkan_pada = $waktu;

                        if (!$lama->save()) {
                            throw new RuntimeException('Gagal menonaktifkan tanda tangan sebelumnya.');
                        }
                    }
                }

                $tandaTangan = new TandaTanganPengguna([
                    'user_id' => $userId,
                    'versi' => $versi,
                    'metode' => $metode,
                    'file_path' => $path,
                    'file_hash' => $gambar['file_hash'],
                    'mime_type' => $gambar['mime_type'],
                    'ukuran_file' => $gambar['ukuran_file'],
                    'lebar' => $gambar['lebar'],
                    'tinggi' => $gambar['tinggi'],
                    'is_active' => true,
                    'diaktifkan_pada' => $waktu,
                    'dinonaktifkan_pada' => null,
                ]);

                if (!$tandaTangan->save()) {
                    throw new RuntimeException('Gagal menyimpan metadata tanda tangan.');
                }

                $tandaTangan->refresh();
                $siapCommit = true;

                return $tandaTangan;
            }, 1);
        } catch (Throwable $e) {
            if ($path !== null && !$siapCommit && DB::transactionLevel() === 0) {
                try {
                    $this->bersihkanFile($path);
                } catch (Throwable $cleanupError) {
                    report($cleanupError);
                }
            }

            throw $e;
        }
    }

    protected function buatPathFile(int $userId, int $versi): string
    {
        return sprintf('cuti/tanda-tangan/%d/v%d-%s.png', $userId, $versi, Str::uuid());
    }

    protected function simpanFile(string $path, array $gambar): void
    {
        $disk = Storage::disk('local');

        if (!$disk->put($path, $gambar['isi_file'])) {
            throw new RuntimeException('Gagal menyimpan file tanda tangan.');
        }

        $isi = $disk->get($path);

        if (!is_string($isi) || strlen($isi) !== $gambar['ukuran_file'] || !hash_equals($gambar['file_hash'], hash('sha256', $isi))) {
            throw new RuntimeException('Integritas file tanda tangan tidak valid.');
        }
    }

    protected function bersihkanFile(string $path): void
    {
        $disk = Storage::disk('local');

        if ($disk->exists($path) && !$disk->delete($path)) {
            throw new RuntimeException('Gagal membersihkan file tanda tangan: ' . $path);
        }
    }
}