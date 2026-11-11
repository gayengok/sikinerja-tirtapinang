<?php

namespace App\Http\Controllers\Atasan;

use App\Http\Controllers\Controller;
use App\Models\MasterKpi;
use App\Models\Pegawai;
use App\Models\PenilaianKpi;
use App\Models\PeriodePenilaian;
use App\Services\HasilPenilaianService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenilaianKpiController extends Controller
{
    public function index(Request $request)
    {
        $atasan = $request->user()->pegawai;

        abort_if(! $atasan, 403, 'Data pegawai untuk akun Anda tidak ditemukan.');

        $periodeAktif = PeriodePenilaian::where('status', 'berjalan')->first();

        $bawahans = $atasan->bawahans()
            ->with('jabatan')
            ->orderBy('nama')
            ->get();

        $sudahDinilaiIds = collect();
        if ($periodeAktif) {
            $sudahDinilaiIds = PenilaianKpi::whereIn('pegawai_id', $bawahans->pluck('id'))
                ->where('periode_id', $periodeAktif->id)
                ->distinct()
                ->pluck('pegawai_id');
        }

        return view('atasan.penilaian-kpi.index', [
            'bawahans' => $bawahans,
            'periodeAktif' => $periodeAktif,
            'sudahDinilaiIds' => $sudahDinilaiIds,
        ]);
    }

    public function create(Request $request, Pegawai $pegawai)
    {
        $pegawaiBawahan = $this->cekBawahan($request, $pegawai);

        $periode = $this->resolvePeriode($request);

        abort_if(! $periode, 404, 'Periode penilaian aktif tidak ditemukan. Hubungi Master untuk mengaktifkan periode.');

        $pegawaiBawahan->load('jabatan');

        $masterKpis = MasterKpi::where('jabatan_id', $pegawaiBawahan->jabatan_id)
            ->orderBy('urutan')
            ->get();

        $penilaianTersimpan = PenilaianKpi::where('pegawai_id', $pegawaiBawahan->id)
            ->where('periode_id', $periode->id)
            ->get()
            ->keyBy('master_kpi_id');

        return view('atasan.penilaian-kpi.create', [
            'pegawai' => $pegawaiBawahan,
            'periode' => $periode,
            'masterKpis' => $masterKpis,
            'penilaianTersimpan' => $penilaianTersimpan,
        ]);
    }

    public function store(Request $request, Pegawai $pegawai, HasilPenilaianService $hasilPenilaianService)
    {
        $pegawaiBawahan = $this->cekBawahan($request, $pegawai);

        $periode = $this->resolvePeriode($request);

        abort_if(! $periode, 404, 'Periode penilaian aktif tidak ditemukan.');

        $validated = $request->validate([
            'kpi' => ['required', 'array', 'min:1'],
            'kpi.*.master_kpi_id' => ['required', 'exists:master_kpis,id'],
            'kpi.*.target' => ['nullable', 'numeric', 'min:0'],
            'kpi.*.realisasi_final' => ['required', 'numeric', 'min:0'],
            'kpi.*.skor' => ['required', 'integer', 'min:1', 'max:5'],
            'kpi.*.keterangan' => ['nullable', 'string', 'max:500'],
        ], [
            'kpi.required' => 'Data penilaian KPI wajib diisi.',
            'kpi.*.master_kpi_id.required' => 'KPI tidak valid.',
            'kpi.*.master_kpi_id.exists' => 'KPI tidak ditemukan.',
            'kpi.*.realisasi_final.required' => 'Realisasi wajib diisi.',
            'kpi.*.realisasi_final.numeric' => 'Realisasi harus berupa angka.',
            'kpi.*.skor.required' => 'Skor wajib diisi.',
            'kpi.*.skor.integer' => 'Skor harus berupa angka bulat.',
            'kpi.*.skor.max' => 'Skor maksimal adalah 5.',
        ]);

        DB::transaction(function () use ($validated, $pegawaiBawahan, $periode) {
            foreach ($validated['kpi'] as $item) {
                $masterKpi = MasterKpi::find($item['master_kpi_id']);

                // Keamanan tambahan: pastikan KPI ini memang milik jabatan pegawai yang dinilai
                if (! $masterKpi || $masterKpi->jabatan_id !== $pegawaiBawahan->jabatan_id) {
                    continue;
                }

                $target = $item['target'] ?? $masterKpi->target_default;
                $realisasi = (float) $item['realisasi_final'];
                $persenCapaian = $this->hitungPersenCapaian($masterKpi->arah_capaian, $target, $realisasi);

                PenilaianKpi::updateOrCreate(
                    [
                        'pegawai_id' => $pegawaiBawahan->id,
                        'periode_id' => $periode->id,
                        'master_kpi_id' => $masterKpi->id,
                    ],
                    [
                        'target' => $target,
                        'realisasi_final' => $realisasi,
                        'persen_capaian' => $persenCapaian,
                        'skor' => $item['skor'],
                        'keterangan' => $item['keterangan'] ?? null,
                    ]
                );
            }
        });

        $hasilPenilaianService->syncForPegawai($pegawaiBawahan, $periode);

        return redirect()
            ->route('atasan.penilaian-kpi.show', $pegawaiBawahan)
            ->with('success', 'Penilaian KPI berhasil disimpan.');
    }

    public function show(Request $request, Pegawai $pegawai)
    {
        $pegawaiBawahan = $this->cekBawahan($request, $pegawai);

        $periode = $this->resolvePeriode($request);

        $penilaianKpis = PenilaianKpi::where('pegawai_id', $pegawaiBawahan->id)
            ->when($periode, fn ($q) => $q->where('periode_id', $periode->id))
            ->with('masterKpi')
            ->get();

        return view('atasan.penilaian-kpi.show', [
            'pegawai' => $pegawaiBawahan,
            'periode' => $periode,
            'penilaianKpis' => $penilaianKpis,
        ]);
    }

    public function edit(Request $request, Pegawai $pegawai)
    {
        return $this->create($request, $pegawai);
    }

    public function update(Request $request, Pegawai $pegawai, HasilPenilaianService $hasilPenilaianService)
    {
        return $this->store($request, $pegawai, $hasilPenilaianService);
    }

    /**
     * Pastikan pegawai adalah bawahan dari atasan yang login. Return instance Pegawai (bawahan).
     */
    private function cekBawahan(Request $request, Pegawai $pegawai): Pegawai
    {
        $atasan = $request->user()->pegawai;

        abort_if(! $atasan, 403, 'Data pegawai untuk akun Anda tidak ditemukan.');

        $pegawaiBawahan = $atasan->bawahans()->find($pegawai->id);

        abort_if(! $pegawaiBawahan, 403, 'Anda tidak memiliki akses untuk menilai pegawai ini.');

        return $pegawaiBawahan;
    }

    private function resolvePeriode(Request $request): ?PeriodePenilaian
    {
        $periodeId = $request->input('periode_id', $request->query('periode_id'));

        if ($periodeId) {
            return PeriodePenilaian::find($periodeId);
        }

        return PeriodePenilaian::where('status', 'berjalan')
            ->orderByDesc('tanggal_mulai')
            ->first();
    }

    private function hitungPersenCapaian(string $arahCapaian, ?float $target, float $realisasi): float
    {
        if (! $target || $target == 0) {
            return 0;
        }

        if ($arahCapaian === 'minimal') {
            // Semakin rendah realisasi dari target, semakin baik.
            // Persen capaian dihitung terbalik agar tetap 100% saat realisasi = target,
            // dan lebih dari 100% saat realisasi lebih baik (lebih rendah) dari target.
            return round(($target / $realisasi) * 100, 2);
        }

        // arah_capaian = maksimal
        return round(($realisasi / $target) * 100, 2);
    }
}
