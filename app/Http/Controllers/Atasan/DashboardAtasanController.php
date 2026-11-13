<?php

namespace App\Http\Controllers\Atasan;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\LaporanHarian;
use App\Models\PeriodePenilaian;
use Illuminate\Http\Request;

class DashboardAtasanController extends Controller
{
    public function index(Request $request)
    {
        $atasan = $request->user()->pegawai;

        abort_if(! $atasan, 403, 'Data pegawai untuk akun Anda tidak ditemukan.');

        $presensiAtasanHariIni = Absensi::where('pegawai_id', $atasan->getKey())
            ->whereDate('tanggal', now()->toDateString())
            ->first();

        $laporanAtasanHariIni = $atasan->laporanHarians()
            ->whereDate('tanggal', now()->toDateString())
            ->first();

        $bawahanIds = $atasan->bawahans()->pluck('id');

        $jumlahBawahan = $bawahanIds->count();

        $laporanMenunggu = LaporanHarian::whereIn('pegawai_id', $bawahanIds)
            ->where('status', 'diajukan')
            ->count();

        $laporanDisetujui = LaporanHarian::whereIn('pegawai_id', $bawahanIds)
            ->where('status', 'disetujui')
            ->count();

        $laporanDitolak = LaporanHarian::whereIn('pegawai_id', $bawahanIds)
            ->where('status', 'ditolak')
            ->count();

        $periodeAktif = PeriodePenilaian::where('status', 'berjalan')
            ->orderByDesc('tanggal_mulai')
            ->first();

        return view('atasan.dashboard', [
            'jumlahBawahan' => $jumlahBawahan,
            'laporanMenunggu' => $laporanMenunggu,
            'laporanDisetujui' => $laporanDisetujui,
            'laporanDitolak' => $laporanDitolak,
            'laporanAtasanHariIni' => $laporanAtasanHariIni,
            'periodeAktif' => $periodeAktif,
            'presensiAtasanHariIni' => $presensiAtasanHariIni,
        ]);
    }
}
