<?php

namespace App\Http\Controllers\KepalaBagian;

use App\Http\Controllers\Controller;
use App\Models\MasterKompetensi;
use App\Models\Pegawai;
use App\Models\PenilaianKompetensi;
use App\Models\PeriodePenilaian;
use App\Services\HasilPenilaianService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KompetensiAtasanController extends Controller
{
    public function edit(Request $request, Pegawai $pegawai)
    {
        $atasan = $this->cekAtasanBawahan($request, $pegawai);
        $periode = $this->resolvePeriode($request);

        abort_if(! $periode, 404, 'Periode penilaian aktif tidak ditemukan.');

        $masterKompetensis = MasterKompetensi::untukJabatan($atasan->jabatan_id)
            ->orderBy('urutan')
            ->orderBy('id')
            ->get();
        $penilaianTersimpan = PenilaianKompetensi::where('pegawai_id', $atasan->id)
            ->where('periode_id', $periode->id)
            ->get()
            ->keyBy('master_kompetensi_id');

        return view('atasan.kompetensi.edit', [
            'pegawai' => $atasan,
            'periode' => $periode,
            'masterKompetensis' => $masterKompetensis,
            'penilaianTersimpan' => $penilaianTersimpan,
            'assessmentRoutePrefix' => 'kepala_bagian.penilaian-atasan',
            'targetLabel' => 'Atasan',
        ]);
    }

    public function update(Request $request, Pegawai $pegawai, HasilPenilaianService $hasilPenilaianService)
    {
        $atasan = $this->cekAtasanBawahan($request, $pegawai);
        $periode = $this->resolvePeriode($request);

        abort_if(! $periode, 404, 'Periode penilaian aktif tidak ditemukan.');

        $masterKompetensis = MasterKompetensi::untukJabatan($atasan->jabatan_id)
            ->orderBy('urutan')
            ->orderBy('id')
            ->get();
        $validated = $request->validate([
            'kompetensi' => ['required', 'array'],
            'kompetensi.*.master_kompetensi_id' => ['required', 'integer', 'in:'.$masterKompetensis->pluck('id')->implode(',')],
            'kompetensi.*.skor' => ['required', 'integer', 'min:1', 'max:5'],
            'kompetensi.*.catatan' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($validated, $atasan, $periode) {
            foreach ($validated['kompetensi'] as $item) {
                PenilaianKompetensi::updateOrCreate(
                    [
                        'pegawai_id' => $atasan->id,
                        'periode_id' => $periode->id,
                        'master_kompetensi_id' => $item['master_kompetensi_id'],
                    ],
                    [
                        'skor' => $item['skor'],
                        'catatan' => $item['catatan'] ?? null,
                    ]
                );
            }
        });

        $hasilPenilaianService->syncForPegawai($atasan, $periode);

        return redirect()
            ->route('kepala_bagian.penilaian-atasan.show', ['pegawai' => $atasan, 'periode_id' => $periode->id])
            ->with('success', 'Penilaian kompetensi atasan berhasil disimpan.');
    }

    private function cekAtasanBawahan(Request $request, Pegawai $pegawai): Pegawai
    {
        $kepalaBagian = $request->user()->pegawai;

        abort_if(! $kepalaBagian, 403, 'Data pegawai untuk akun Anda tidak ditemukan.');

        $atasan = $kepalaBagian->bawahans()
            ->whereHas('user', fn ($query) => $query->where('role', 'atasan'))
            ->with('jabatan')
            ->find($pegawai->id);

        abort_if(! $atasan, 403, 'Anda tidak memiliki akses terhadap atasan ini.');

        return $atasan;
    }

    private function resolvePeriode(Request $request): ?PeriodePenilaian
    {
        $periodeId = $request->input('periode_id', $request->query('periode_id'));

        if ($periodeId) {
            return PeriodePenilaian::find($periodeId);
        }

        return PeriodePenilaian::where('status', 'berjalan')
            ->orderByDesc('tanggal_mulai')
            ->first()
            ?? PeriodePenilaian::orderByDesc('tanggal_mulai')->first();
    }
}
