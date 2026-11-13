<?php

namespace App\Http\Controllers\Direktur;

use App\Http\Controllers\Controller;
use App\Models\PenilaianKpi;
use App\Models\PeriodePenilaian;
use Illuminate\Http\Request;

class DashboardDirekturController extends Controller
{
    public function index(Request $request)
    {
        $direktur = $request->user()->pegawai;

        abort_if(! $direktur, 403, 'Data pegawai untuk akun Anda tidak ditemukan.');

        $kepalaBagians = $direktur->bawahans()
            ->whereHas('user', fn ($query) => $query->where('role', 'kepala_bagian'))
            ->where('status', 'aktif')
            ->with(['jabatan.divisi'])
            ->orderBy('nama')
            ->get();

        $periodeAktif = PeriodePenilaian::where('status', 'berjalan')
            ->orderByDesc('tanggal_mulai')
            ->first();

        $sudahDinilaiIds = $periodeAktif
            ? PenilaianKpi::whereIn('pegawai_id', $kepalaBagians->pluck('id'))
                ->where('periode_id', $periodeAktif->id)
                ->distinct()
                ->pluck('pegawai_id')
            : collect();

        return view('direktur.index', compact('kepalaBagians', 'periodeAktif', 'sudahDinilaiIds'));
    }
}
