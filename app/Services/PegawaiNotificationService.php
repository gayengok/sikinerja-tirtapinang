<?php

namespace App\Services;

use App\Models\Pegawai;

class PegawaiNotificationService
{
    public function send(
        Pegawai $pegawai,
        string $jenis,
        string $judul,
        string $pesan,
        string $url
    ): void {
        $user = $pegawai->user;

        if (! $user || $user->role !== 'pegawai') {
            return;
        }

        $user->pegawaiNotifications()->create([
            'jenis' => $jenis,
            'judul' => $judul,
            'pesan' => $pesan,
            'url' => $url,
        ]);
    }
}
