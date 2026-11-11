<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Divisi;
use App\Models\Jabatan;
use App\Models\LaporanHarian;
use App\Models\MasterKompetensi;
use App\Models\MasterKpi;
use App\Models\Pegawai;
use App\Models\PeriodePenilaian;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->role === 'master', 403, 'Anda tidak memiliki akses ke halaman ini.');

        $totalDivisi = Divisi::count();
        $totalJabatan = Jabatan::count();
        $totalPegawaiAktif = Pegawai::where('status', 'aktif')->count();
        $totalPeriode = PeriodePenilaian::count();
        $periodeAktif = PeriodePenilaian::where('status', 'berjalan')->first();
        $totalKpi = MasterKpi::count();
        $totalKompetensi = MasterKompetensi::count();
        $laporanHariIni = LaporanHarian::whereDate('tanggal', now()->toDateString())->count();

        return view('master.dashboard', [
            'totalDivisi' => $totalDivisi,
            'totalJabatan' => $totalJabatan,
            'totalPegawaiAktif' => $totalPegawaiAktif,
            'totalPeriode' => $totalPeriode,
            'periodeAktif' => $periodeAktif,
            'totalKpi' => $totalKpi,
            'totalKompetensi' => $totalKompetensi,
            'laporanHariIni' => $laporanHariIni,
        ]);
    }
}
