<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class CutiController extends Controller
{
    public function index(): View
    {
        $tahun = now()->year;

        $kuota = [
            'dasar' => 0,
            'akumulasi' => 0,
            'total' => 0,
            'terpakai' => 0,
            'pengajuan' => 0,
            'tersedia' => 0,
        ];

        $pengajuan = collect();

        return view('pegawai.cuti.index', compact(
            'tahun',
            'kuota',
            'pengajuan'
        ));
    }
}
