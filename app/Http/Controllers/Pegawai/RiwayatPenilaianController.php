<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use App\Models\HasilPenilaian;
use App\Models\PresensiPotongan;
use Illuminate\Http\Request;

class RiwayatPenilaianController extends Controller
{
    public function index(Request $request)
    {
        $pegawai = $this->pegawaiLogin($request);

        // Hanya ambil kolom ringkasan hasil akhir. Tidak menyentuh
        // penilaian_kpis / penilaian_kompetensis (detail internal),
        // sesuai aturan bahwa Pegawai tidak boleh melihat indikator KPI internal.
        $hasilPenilaians = HasilPenilaian::where('pegawai_id', $pegawai->id)
            ->with('periode')
            ->orderByDesc('id')
            ->paginate(10);

        return view('pegawai.riwayat-penilaian.index', compact('hasilPenilaians'));
    }

    public function show(Request $request, HasilPenilaian $hasilPenilaian)
    {
        $pegawai = $this->pegawaiLogin($request);

        abort_unless($hasilPenilaian->pegawai_id === $pegawai->id, 403, 'Anda tidak memiliki akses terhadap data penilaian ini.');

        $hasilPenilaian->load('periode');
        $presensiPotongans = PresensiPotongan::with('jenisPotongan')
            ->where('pegawai_id', $pegawai->id)
            ->where('periode_id', $hasilPenilaian->periode_id)
            ->get();

        return view('pegawai.riwayat-penilaian.show', [
            'hasil' => $hasilPenilaian,
            'presensiPotongans' => $presensiPotongans,
        ]);
    }

    private function pegawaiLogin(Request $request)
    {
        $pegawai = $request->user()->pegawai;

        abort_if(!$pegawai, 403, 'Data pegawai untuk akun Anda tidak ditemukan.');

        return $pegawai;
    }
}
