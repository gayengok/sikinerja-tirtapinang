<?php

namespace App\Http\Controllers\KepalaBagian;

use App\Http\Controllers\Controller;
use App\Models\HasilPenilaian;
use App\Models\MasterKpi;
use App\Models\Pegawai;
use App\Models\PenilaianKpi;
use App\Models\PeriodePenilaian;
use App\Models\PresensiPotongan;
use App\Services\HasilPenilaianService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenilaianAtasanController extends Controller
{
    public function index(Request $request)
    {
        $kepalaBagian = $request->user()->pegawai;

        abort_if(! $kepalaBagian, 403, 'Data pegawai untuk akun Anda tidak ditemukan.');

        $atasans = $kepalaBagian->bawahans()
            ->whereHas('user', fn ($query) => $query->where('role', 'atasan'))
            ->where('status', 'aktif')
            ->with(['jabatan.divisi'])
            ->orderBy('nama')
            ->get();

        $periodeAktif = PeriodePenilaian::where('status', 'berjalan')
            ->orderByDesc('tanggal_mulai')
            ->first();

        $sudahDinilaiIds = $periodeAktif
            ? PenilaianKpi::whereIn('pegawai_id', $atasans->pluck('id'))
                ->where('periode_id', $periodeAktif->id)
                ->distinct()
                ->pluck('pegawai_id')
            : collect();

        $kpiCountsByJabatan = MasterKpi::whereIn('jabatan_id', $atasans->pluck('jabatan_id')->filter()->unique())
            ->selectRaw('jabatan_id, COUNT(*) as total')
            ->groupBy('jabatan_id')
            ->pluck('total', 'jabatan_id');

        $penilaianCountsByPegawai = $periodeAktif
            ? PenilaianKpi::whereIn('pegawai_id', $atasans->pluck('id'))
                ->where('periode_id', $periodeAktif->id)
                ->selectRaw('pegawai_id, COUNT(*) as total')
                ->groupBy('pegawai_id')
                ->pluck('total', 'pegawai_id')
            : collect();

        return view('atasan.penilaian-kpi.index', [
            'bawahans' => $atasans,
            'periodeAktif' => $periodeAktif,
            'sudahDinilaiIds' => $sudahDinilaiIds,
            'kpiCountsByJabatan' => $kpiCountsByJabatan,
            'penilaianCountsByPegawai' => $penilaianCountsByPegawai,
            'assessmentRoutePrefix' => 'kepala_bagian.penilaian-atasan',
            'targetLabel' => 'atasan',
        ]);
    }

    public function create(Request $request, Pegawai $pegawai)
    {
        $atasan = $this->cekAtasanBawahan($request, $pegawai);
        $periode = $this->resolvePeriode($request);

        abort_if(! $periode, 404, 'Periode penilaian aktif tidak ditemukan. Hubungi Master untuk mengaktifkan periode.');

        $atasan->load('jabatan');
        $masterKpis = MasterKpi::where('jabatan_id', $atasan->jabatan_id)
            ->orderBy('urutan')
            ->get();
        $penilaianTersimpan = PenilaianKpi::where('pegawai_id', $atasan->id)
            ->where('periode_id', $periode->id)
            ->get()
            ->keyBy('master_kpi_id');

        return view('atasan.penilaian-kpi.create', [
            'pegawai' => $atasan,
            'periode' => $periode,
            'masterKpis' => $masterKpis,
            'penilaianTersimpan' => $penilaianTersimpan,
            'assessmentRoutePrefix' => 'kepala_bagian.penilaian-atasan',
            'targetLabel' => 'Atasan',
        ]);
    }

    public function store(Request $request, Pegawai $pegawai, HasilPenilaianService $hasilPenilaianService)
    {
        $atasan = $this->cekAtasanBawahan($request, $pegawai);
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

        DB::transaction(function () use ($validated, $atasan, $periode) {
            foreach ($validated['kpi'] as $item) {
                $masterKpi = MasterKpi::where('jabatan_id', $atasan->jabatan_id)
                    ->findOrFail($item['master_kpi_id']);
                $target = $item['target'] ?? $masterKpi->target_default;
                $realisasi = (float) $item['realisasi_final'];

                PenilaianKpi::updateOrCreate(
                    [
                        'pegawai_id' => $atasan->id,
                        'periode_id' => $periode->id,
                        'master_kpi_id' => $masterKpi->id,
                    ],
                    [
                        'target' => $target,
                        'realisasi_final' => $realisasi,
                        'persen_capaian' => $this->hitungPersenCapaian($masterKpi->arah_capaian, $target, $realisasi),
                        'skor' => $item['skor'],
                        'keterangan' => $item['keterangan'] ?? null,
                    ]
                );
            }
        });

        $hasilPenilaianService->syncForPegawai($atasan, $periode);

        return redirect()
            ->route('kepala_bagian.penilaian-atasan.show', $atasan)
            ->with('success', 'Penilaian KPI atasan berhasil disimpan.');
    }

    public function show(Request $request, Pegawai $pegawai)
    {
        $atasan = $this->cekAtasanBawahan($request, $pegawai);
        $atasan->load('jabatan.divisi');
        $periode = $this->resolvePeriode($request);
        $penilaianKpis = PenilaianKpi::where('pegawai_id', $atasan->id)
            ->when($periode, fn ($query) => $query->where('periode_id', $periode->id))
            ->with('masterKpi')
            ->get();
        $masterKpis = MasterKpi::where('jabatan_id', $atasan->jabatan_id)->get(['id', 'bobot']);
        $totalBobot = (float) $masterKpis->sum('bobot');
        $penilaianKpiByMasterId = $penilaianKpis->keyBy('master_kpi_id');
        $capaianKpi = $totalBobot > 0
            ? round($masterKpis->sum(function (MasterKpi $masterKpi) use ($penilaianKpiByMasterId, $totalBobot) {
                $penilaian = $penilaianKpiByMasterId->get($masterKpi->id);
                $capaian = min(100, max(0, (float) ($penilaian->persen_capaian ?? 0)));

                return $capaian * ((float) $masterKpi->bobot / $totalBobot);
            }), 2)
            : 0;
        $penilaianDenganSkor = $penilaianKpis->filter(
            fn (PenilaianKpi $penilaian) => $penilaian->skor !== null
                && (float) ($penilaian->masterKpi?->bobot ?? 0) > 0
        );
        $totalBobotBerskor = (float) $penilaianDenganSkor->sum(
            fn (PenilaianKpi $penilaian) => (float) $penilaian->masterKpi->bobot
        );
        $rataSkorKpi = $totalBobotBerskor > 0
            ? round($penilaianDenganSkor->sum(
                fn (PenilaianKpi $penilaian) => (float) $penilaian->skor
                    * ((float) $penilaian->masterKpi->bobot / $totalBobotBerskor)
            ), 2)
            : 0;
        $hasilDiperbaruiPada = $penilaianKpis->sortByDesc('updated_at')->first()?->updated_at;
        $hasilTunjangan = $periode
            ? HasilPenilaian::where('pegawai_id', $atasan->id)
                ->where('periode_id', $periode->id)
                ->first()
            : null;
        $presensiPotongans = $hasilTunjangan
            ? PresensiPotongan::with('jenisPotongan')
                ->where('pegawai_id', $atasan->id)
                ->where('periode_id', $periode->id)
                ->get()
            : collect();

        return view('kepala_bagian.penilaian-atasan.show', [
            'pegawai' => $atasan,
            'periode' => $periode,
            'penilaianKpis' => $penilaianKpis,
            'capaianKpi' => $capaianKpi,
            'rataSkorKpi' => $rataSkorKpi,
            'hasilDiperbaruiPada' => $hasilDiperbaruiPada,
            'hasilTunjangan' => $hasilTunjangan,
            'presensiPotongans' => $presensiPotongans,
        ]);
    }

    private function cekAtasanBawahan(Request $request, Pegawai $pegawai): Pegawai
    {
        $kepalaBagian = $request->user()->pegawai;

        abort_if(! $kepalaBagian, 403, 'Data pegawai untuk akun Anda tidak ditemukan.');

        $atasan = $kepalaBagian->bawahans()
            ->whereHas('user', fn ($query) => $query->where('role', 'atasan'))
            ->where('status', 'aktif')
            ->find($pegawai->id);

        abort_if(! $atasan, 403, 'Anda tidak memiliki akses untuk menilai atasan ini.');

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
            ->first();
    }

    private function hitungPersenCapaian(string $arahCapaian, ?float $target, float $realisasi): float
    {
        if (! $target || $target == 0) {
            return 0;
        }

        if ($arahCapaian === 'minimal') {
            return $realisasi == 0 ? 0 : round(($target / $realisasi) * 100, 2);
        }

        return round(($realisasi / $target) * 100, 2);
    }
}
