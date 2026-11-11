<?php

namespace App\Services;

use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Support\Collection;

class TeamVisibilityService
{
    public function employeeIds(User $user): Collection
    {
        $pegawai = $user->pegawai;

        abort_if(! $pegawai, 403, 'Data pegawai untuk akun Anda tidak ditemukan.');

        if ($user->role === 'atasan') {
            return $pegawai->bawahans()->pluck('id');
        }

        abort_unless($user->role === 'kepala_bagian', 403);

        $atasanIds = $this->assignedAtasanIds($pegawai);

        return Pegawai::whereIn('atasan_id', $atasanIds)->pluck('id');
    }

    public function attendanceEmployeeIds(User $user): Collection
    {
        if ($user->role !== 'kepala_bagian') {
            return $this->employeeIds($user);
        }

        $pegawai = $user->pegawai;
        abort_if(! $pegawai, 403, 'Data pegawai untuk akun Anda tidak ditemukan.');

        return $this->assignedAtasanIds($pegawai);
    }

    private function assignedAtasanIds(Pegawai $pegawai): Collection
    {
        return $pegawai->bawahans()
            ->whereHas('user', fn ($query) => $query->where('role', 'atasan'))
            ->where('status', 'aktif')
            ->pluck('id');
    }

    public function reportEmployeeIds(User $user): Collection
    {
        $pegawaiIds = $this->employeeIds($user);

        if ($user->role !== 'kepala_bagian') {
            return $pegawaiIds;
        }

        $kepalaBagian = $user->pegawai;
        $atasanIds = $this->assignedAtasanIds($kepalaBagian);

        return $pegawaiIds->merge($atasanIds)->unique()->values();
    }

    public function teamMemberIds(User $user): Collection
    {
        $pegawaiIds = $this->employeeIds($user);

        if ($user->role !== 'kepala_bagian') {
            return $pegawaiIds;
        }

        $kepalaBagian = $user->pegawai;
        abort_if(! $kepalaBagian, 403, 'Data pegawai untuk akun Anda tidak ditemukan.');

        return $pegawaiIds
            ->merge($this->assignedAtasanIds($kepalaBagian))
            ->unique()
            ->values();
    }

    public function routePrefix(User $user): string
    {
        return $user->role === 'kepala_bagian' ? 'kepala_bagian' : 'atasan';
    }
}
