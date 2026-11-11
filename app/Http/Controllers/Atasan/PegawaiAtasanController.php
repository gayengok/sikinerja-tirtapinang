<?php

namespace App\Http\Controllers\Atasan;

use App\Http\Controllers\Controller;
use App\Models\HasilPenilaian;
use App\Models\Pegawai;
use App\Models\PeriodePenilaian;
use Illuminate\Http\Request;

class PegawaiAtasanController extends Controller
{
    public function index(Request $request)
    {
        $atasan = $request->user()->pegawai;

        abort_if(!$atasan, 403, 'Data pegawai untuk akun Anda tidak ditemukan.');

        $pegawais = $atasan->bawahans()
            ->with(['jabatan.divisi', 'user'])
            ->orderBy('nama')
            ->paginate(10);

        $bawahanIds = $atasan->bawahans()->pluck('id');
        $bawahanAktif = Pegawai::whereIn('id', $bawahanIds)->where('status', 'aktif')->count();
        $periodeAktif = PeriodePenilaian::where('status', 'berjalan')
            ->orderByDesc('tanggal_mulai')
            ->first();
        $hasilTim = HasilPenilaian::whereIn('pegawai_id', $bawahanIds)
            ->when($periodeAktif, fn ($query) => $query->where('periode_id', $periodeAktif->id))
            ->get(['nilai_akhir']);
        $skorTim = $hasilTim->avg('nilai_akhir');
        $laporanTertunda = \App\Models\LaporanHarian::whereIn('pegawai_id', $bawahanIds)
            ->where('status', 'diajukan')
            ->count();

        return view('atasan.pegawai.index', compact(
            'pegawais',
            'periodeAktif',
            'bawahanAktif',
            'skorTim',
            'laporanTertunda'
        ));
    }

    public function show(Request $request, Pegawai $pegawai)
    {
        $atasan = $request->user()->pegawai;

        abort_if(!$atasan, 403, 'Data pegawai untuk akun Anda tidak ditemukan.');

        // Pastikan pegawai yang diakses benar-benar bawahan dari atasan yang login
        $pegawaiBawahan = $atasan->bawahans()
            ->with(['user', 'jabatan.divisi', 'atasan'])
            ->find($pegawai->id);

        abort_if(!$pegawaiBawahan, 403, 'Anda tidak memiliki akses terhadap data pegawai ini.');

        $jumlahLaporan = $pegawaiBawahan->laporanHarians()->count();
        $laporanMenunggu = $pegawaiBawahan->laporanHarians()->where('status', 'diajukan')->count();
        $jumlahKehadiran = $pegawaiBawahan->absensis()->where('status', 'hadir')->count();
        $hasilTerakhir = HasilPenilaian::where('pegawai_id', $pegawaiBawahan->id)
            ->latest('id')
            ->first();

        return view('atasan.pegawai.show', [
            'pegawai' => $pegawaiBawahan,
            'jumlahLaporan' => $jumlahLaporan,
            'laporanMenunggu' => $laporanMenunggu,
            'jumlahKehadiran' => $jumlahKehadiran,
            'hasilTerakhir' => $hasilTerakhir,
        ]);
    }
}
