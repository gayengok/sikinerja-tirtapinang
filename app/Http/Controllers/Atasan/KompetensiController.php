<?php

namespace App\Http\Controllers\Atasan;

use App\Http\Controllers\Controller;
use App\Models\MasterKompetensi;
use App\Models\Pegawai;
use App\Models\PenilaianKompetensi;
use App\Models\PeriodePenilaian;
use App\Services\HasilPenilaianService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KompetensiController extends Controller
{
    public function index(Request $request)
    {
        $atasan = $request->user()->pegawai;
        abort_if(! $atasan, 403, 'Data pegawai untuk akun Anda tidak ditemukan.');

        $periode = $this->resolvePeriode($request);
        $bawahans = $atasan->bawahans()->with('jabatan.divisi')->orderBy('nama')->get();
        $sudahDinilaiIds = $periode
            ? PenilaianKompetensi::where('periode_id', $periode->id)
            ->whereIn('pegawai_id', $bawahans->pluck('id'))
            ->whereNotNull('skor')
            ->select('pegawai_id')
            ->distinct()
            ->pluck('pegawai_id')
            : collect();

        return view('atasan.kompetensi.index', compact('bawahans', 'periode', 'sudahDinilaiIds'));
    }

    public function edit(Request $request, Pegawai $pegawai)
    {
        $pegawai = $this->cekBawahan($request, $pegawai);
        $periode = $this->resolvePeriode($request);
        abort_if(! $periode, 404, 'Periode penilaian aktif tidak ditemukan.');

        $masterKompetensis = MasterKompetensi::untukJabatan($pegawai->jabatan_id)
            ->orderBy('urutan')
            ->orderBy('id')
            ->get();
        $penilaianTersimpan = PenilaianKompetensi::where('pegawai_id', $pegawai->id)
            ->where('periode_id', $periode->id)
            ->get()
            ->keyBy('master_kompetensi_id');

        return view('atasan.kompetensi.edit', compact(
            'pegawai',
            'periode',
            'masterKompetensis',
            'penilaianTersimpan'
        ));
    }




    public function update(Request $request, Pegawai $pegawai, HasilPenilaianService $hasilPenilaianService)
    {
        $pegawai = $this->cekBawahan($request, $pegawai);
        $periode = $this->resolvePeriode($request);
        abort_if(! $periode, 404, 'Periode penilaian aktif tidak ditemukan.');

        $masterKompetensis = MasterKompetensi::untukJabatan($pegawai->jabatan_id)
            ->orderBy('urutan')
            ->orderBy('id')
            ->get();
        $validated = $request->validate([
            'kompetensi' => ['required', 'array'],
            'kompetensi.*.master_kompetensi_id' => ['required', 'integer', 'in:' . $masterKompetensis->pluck('id')->implode(',')],
            'kompetensi.*.skor' => ['required', 'integer', 'min:1', 'max:5'],
            'kompetensi.*.catatan' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($validated, $pegawai, $periode) {
            foreach ($validated['kompetensi'] as $item) {
                PenilaianKompetensi::updateOrCreate(
                    [
                        'pegawai_id' => $pegawai->id,
                        'periode_id' => $periode->id,
                        'master_kompetensi_id' => $item['master_kompetensi_id'],
                    ],
                    ['skor' => $item['skor'], 'catatan' => $item['catatan'] ?? null]
                );
            }
        });

        $hasilPenilaianService->syncForPegawai($pegawai, $periode);

        return redirect()
            ->route('atasan.pegawai.index', ['periode_id' => $periode->id])
            ->with('success', 'Penilaian kompetensi berhasil disimpan.');
    }




    private function cekBawahan(Request $request, Pegawai $pegawai): Pegawai
    {
        $atasan = $request->user()->pegawai;
        abort_if(! $atasan, 403, 'Data pegawai untuk akun Anda tidak ditemukan.');
        $bawahan = $atasan->bawahans()->with('jabatan')->find($pegawai->id);
        abort_if(! $bawahan, 403, 'Anda tidak memiliki akses terhadap pegawai ini.');

        return $bawahan;
    }

    private function resolvePeriode(Request $request): ?PeriodePenilaian
    {
        $periodeId = $request->input('periode_id', $request->query('periode_id'));

        return $periodeId
            ? PeriodePenilaian::find($periodeId)
            : PeriodePenilaian::where('status', 'berjalan')->orderByDesc('tanggal_mulai')->first()
            ?? PeriodePenilaian::orderByDesc('tanggal_mulai')->first();
    }
}
