<?php

namespace App\Http\Controllers\Direktur;

use App\Http\Controllers\Controller;
use App\Models\MasterKompetensi;
use App\Models\MasterKpi;
use App\Models\Pegawai;
use App\Models\PenilaianKompetensi;
use App\Models\PenilaianKpi;
use App\Models\PeriodePenilaian;
use App\Services\HasilPenilaianService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PenilaianKepalaBagianController extends Controller
{
    private const ROUTE_PREFIX = 'direktur.penilaian-kepala-bagian';

    public function index(Request $request)
    {
        $direktur = $this->direkturLogin($request);
        $kepalaBagians = $direktur->bawahans()
            ->whereHas('user', fn ($query) => $query->where('role', 'kepala_bagian'))
            ->where('status', 'aktif')
            ->with(['jabatan.divisi'])
            ->orderBy('nama')
            ->get();
        $periodeAktif = $this->resolvePeriode($request);
        $sudahDinilaiIds = $periodeAktif
            ? PenilaianKpi::whereIn('pegawai_id', $kepalaBagians->pluck('id'))
                ->where('periode_id', $periodeAktif->id)
                ->distinct()
                ->pluck('pegawai_id')
            : collect();
        $kpiCountsByJabatan = MasterKpi::whereIn('jabatan_id', $kepalaBagians->pluck('jabatan_id')->filter()->unique())
            ->selectRaw('jabatan_id, COUNT(*) as total')
            ->groupBy('jabatan_id')
            ->pluck('total', 'jabatan_id');
        $penilaianCountsByPegawai = $periodeAktif
            ? PenilaianKpi::whereIn('pegawai_id', $kepalaBagians->pluck('id'))
                ->where('periode_id', $periodeAktif->id)
                ->selectRaw('pegawai_id, COUNT(*) as total')
                ->groupBy('pegawai_id')
                ->pluck('total', 'pegawai_id')
            : collect();

        return view('atasan.penilaian-kpi.index', [
            'bawahans' => $kepalaBagians,
            'periodeAktif' => $periodeAktif,
            'sudahDinilaiIds' => $sudahDinilaiIds,
            'kpiCountsByJabatan' => $kpiCountsByJabatan,
            'penilaianCountsByPegawai' => $penilaianCountsByPegawai,
            'assessmentRoutePrefix' => self::ROUTE_PREFIX,
            'dashboardRoute' => 'direktur.dashboard',
            'targetLabel' => 'Kepala Bagian',
        ]);
    }

    public function create(Request $request, Pegawai $pegawai)
    {
        $kepalaBagian = $this->cekKepalaBagian($request, $pegawai);
        $periode = $this->resolvePeriode($request);

        abort_if(! $periode, 404, 'Periode penilaian aktif tidak ditemukan.');

        $masterKpis = MasterKpi::where('jabatan_id', $kepalaBagian->jabatan_id)
            ->orderBy('urutan')
            ->get();
        $penilaianTersimpan = PenilaianKpi::where('pegawai_id', $kepalaBagian->id)
            ->where('periode_id', $periode->id)
            ->get()
            ->keyBy('master_kpi_id');

        return view('atasan.penilaian-kpi.create', [
            'pegawai' => $kepalaBagian,
            'periode' => $periode,
            'masterKpis' => $masterKpis,
            'penilaianTersimpan' => $penilaianTersimpan,
            'assessmentRoutePrefix' => self::ROUTE_PREFIX,
            'targetLabel' => 'Kepala Bagian',
        ]);
    }

    public function store(Request $request, Pegawai $pegawai, HasilPenilaianService $hasilPenilaianService)
    {
        $kepalaBagian = $this->cekKepalaBagian($request, $pegawai);
        $periode = $this->resolvePeriode($request);

        abort_if(! $periode, 404, 'Periode penilaian aktif tidak ditemukan.');

        $masterKpis = MasterKpi::where('jabatan_id', $kepalaBagian->jabatan_id)->get();
        $validated = $request->validate([
            'kpi' => ['required', 'array', 'min:1'],
            'kpi.*.master_kpi_id' => ['required', Rule::in($masterKpis->pluck('id')->all())],
            'kpi.*.target' => ['nullable', 'numeric', 'min:0'],
            'kpi.*.realisasi_final' => ['required', 'numeric', 'min:0'],
            'kpi.*.skor' => ['required', 'integer', 'min:1', 'max:5'],
            'kpi.*.keterangan' => ['nullable', 'string', 'max:500'],
        ], [
            'kpi.required' => 'Data penilaian KPI wajib diisi.',
            'kpi.*.master_kpi_id.required' => 'KPI tidak valid.',
            'kpi.*.master_kpi_id.in' => 'KPI yang dipilih bukan milik jabatan Kepala Bagian ini.',
            'kpi.*.realisasi_final.required' => 'Realisasi wajib diisi.',
            'kpi.*.realisasi_final.numeric' => 'Realisasi harus berupa angka.',
            'kpi.*.skor.required' => 'Skor wajib diisi.',
            'kpi.*.skor.integer' => 'Skor harus berupa angka bulat.',
            'kpi.*.skor.max' => 'Skor maksimal adalah 5.',
        ]);

        DB::transaction(function () use ($validated, $masterKpis, $kepalaBagian, $periode) {
            $masterKpiById = $masterKpis->keyBy('id');

            foreach ($validated['kpi'] as $item) {
                $masterKpi = $masterKpiById->get($item['master_kpi_id']);
                $target = $item['target'] ?? $masterKpi->target_default;
                $realisasi = (float) $item['realisasi_final'];
                $persenCapaian = $this->hitungPersenCapaian($masterKpi->arah_capaian, $target, $realisasi);

                PenilaianKpi::updateOrCreate(
                    [
                        'pegawai_id' => $kepalaBagian->id,
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

        $hasilPenilaianService->syncForPegawai($kepalaBagian, $periode);

        return redirect()
            ->route(self::ROUTE_PREFIX . '.show', ['pegawai' => $kepalaBagian, 'periode_id' => $periode->id])
            ->with('success', 'Penilaian KPI Kepala Bagian berhasil disimpan.');
    }

    public function show(Request $request, Pegawai $pegawai)
    {
        $kepalaBagian = $this->cekKepalaBagian($request, $pegawai);
        $periode = $this->resolvePeriode($request);
        $penilaianKpis = PenilaianKpi::where('pegawai_id', $kepalaBagian->id)
            ->when($periode, fn ($query) => $query->where('periode_id', $periode->id))
            ->with('masterKpi')
            ->get();

        return view('atasan.penilaian-kpi.show', [
            'pegawai' => $kepalaBagian,
            'periode' => $periode,
            'penilaianKpis' => $penilaianKpis,
            'assessmentRoutePrefix' => self::ROUTE_PREFIX,
            'assessmentEvaluatorLabel' => 'Direktur',
            'targetLabel' => 'Kepala Bagian',
        ]);
    }

    public function edit(Request $request, Pegawai $pegawai)
    {
        return $this->create($request, $pegawai);
    }

    public function editKompetensi(Request $request, Pegawai $pegawai)
    {
        $kepalaBagian = $this->cekKepalaBagian($request, $pegawai);
        $periode = $this->resolvePeriode($request);

        abort_if(! $periode, 404, 'Periode penilaian aktif tidak ditemukan.');

        $masterKompetensis = MasterKompetensi::untukJabatan($kepalaBagian->jabatan_id)
            ->orderBy('urutan')
            ->orderBy('id')
            ->get();
        $penilaianTersimpan = PenilaianKompetensi::where('pegawai_id', $kepalaBagian->id)
            ->where('periode_id', $periode->id)
            ->get()
            ->keyBy('master_kompetensi_id');

        return view('atasan.kompetensi.edit', [
            'pegawai' => $kepalaBagian,
            'periode' => $periode,
            'masterKompetensis' => $masterKompetensis,
            'penilaianTersimpan' => $penilaianTersimpan,
            'assessmentRoutePrefix' => self::ROUTE_PREFIX,
            'targetLabel' => 'Kepala Bagian',
        ]);
    }

    public function updateKompetensi(Request $request, Pegawai $pegawai, HasilPenilaianService $hasilPenilaianService)
    {
        $kepalaBagian = $this->cekKepalaBagian($request, $pegawai);
        $periode = $this->resolvePeriode($request);

        abort_if(! $periode, 404, 'Periode penilaian aktif tidak ditemukan.');

        $masterKompetensis = MasterKompetensi::untukJabatan($kepalaBagian->jabatan_id)
            ->orderBy('urutan')
            ->orderBy('id')
            ->get();
        $validated = $request->validate([
            'kompetensi' => ['required', 'array', 'min:1'],
            'kompetensi.*.master_kompetensi_id' => ['required', Rule::in($masterKompetensis->pluck('id')->all())],
            'kompetensi.*.skor' => ['required', 'integer', 'min:1', 'max:5'],
            'kompetensi.*.catatan' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($validated, $kepalaBagian, $periode) {
            foreach ($validated['kompetensi'] as $item) {
                PenilaianKompetensi::updateOrCreate(
                    [
                        'pegawai_id' => $kepalaBagian->id,
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

        $hasilPenilaianService->syncForPegawai($kepalaBagian, $periode);

        return redirect()
            ->route(self::ROUTE_PREFIX . '.show', ['pegawai' => $kepalaBagian, 'periode_id' => $periode->id])
            ->with('success', 'Penilaian kompetensi Kepala Bagian berhasil disimpan.');
    }

    private function direkturLogin(Request $request): Pegawai
    {
        $direktur = $request->user()->pegawai;

        abort_if(! $direktur, 403, 'Data pegawai untuk akun Direktur tidak ditemukan.');

        return $direktur;
    }

    private function cekKepalaBagian(Request $request, Pegawai $pegawai): Pegawai
    {
        $direktur = $this->direkturLogin($request);
        $kepalaBagian = $direktur->bawahans()
            ->whereHas('user', fn ($query) => $query->where('role', 'kepala_bagian'))
            ->where('status', 'aktif')
            ->with('jabatan')
            ->find($pegawai->id);

        abort_if(! $kepalaBagian, 403, 'Anda tidak memiliki akses untuk menilai Kepala Bagian ini.');

        return $kepalaBagian;
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
