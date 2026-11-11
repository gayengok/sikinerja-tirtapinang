<?php

namespace App\Http\Controllers\KepalaBagian;

use App\Http\Controllers\Controller;
use App\Models\LaporanHarian;
use App\Models\PeriodePenilaian;
use App\Services\TeamVisibilityService;
use Illuminate\Http\Request;

class DashboardKepalaBagianController extends Controller
{
    public function index(Request $request, TeamVisibilityService $teamVisibility)
    {
        $kepalaBagian = $request->user()->pegawai;

        abort_if(! $kepalaBagian, 403, 'Data pegawai untuk akun Anda tidak ditemukan.');

        $atasanIds = $kepalaBagian->bawahans()
            ->whereHas('user', fn ($query) => $query->where('role', 'atasan'))
            ->where('status', 'aktif')
            ->pluck('id');

        $pegawaiIds = $teamVisibility->reportEmployeeIds($request->user());

        $laporanMenunggu = LaporanHarian::whereIn('pegawai_id', $pegawaiIds)
            ->where('status', 'diajukan')
            ->count();
        $laporanDisetujui = LaporanHarian::whereIn('pegawai_id', $pegawaiIds)
            ->where('status', 'disetujui')
            ->count();
        $laporanDitolak = LaporanHarian::whereIn('pegawai_id', $pegawaiIds)
            ->where('status', 'ditolak')
            ->count();

        $periodeAktif = PeriodePenilaian::where('status', 'berjalan')
            ->orderByDesc('tanggal_mulai')
            ->first();

        return view('kepala_bagian.dashboard', [
            'jumlahAtasan' => $atasanIds->count(),
            'laporanMenunggu' => $laporanMenunggu,
            'laporanDisetujui' => $laporanDisetujui,
            'laporanDitolak' => $laporanDitolak,
            'periodeAktif' => $periodeAktif,
        ]);
    }
}
