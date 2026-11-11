<?php

namespace App\Http\Controllers\Atasan;

use App\Http\Controllers\Controller;
use App\Models\HasilPenilaian;
use App\Models\Pegawai;
use App\Models\PeriodePenilaian;
use App\Models\PresensiPotongan;
use App\Services\HasilPenilaianService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class HasilPenilaianController extends Controller
{
    public function index(Request $request, HasilPenilaianService $hasilPenilaianService)
    {
        $atasan = $request->user()->pegawai;

        abort_if(! $atasan, 403, 'Data pegawai untuk akun Anda tidak ditemukan.');

        $periode = $this->resolvePeriode($request);
        $periodeOptions = PeriodePenilaian::query()
            ->orderByDesc('tanggal_mulai')
            ->orderByDesc('id')
            ->get();

        $bawahan = $atasan->bawahans()
            ->with('jabatan')
            ->orderBy('nama')
            ->get();

        if ($this->canSyncPeriode($periode)) {
            foreach ($bawahan as $pegawaiBawahan) {
                $hasilPenilaianService->syncForPegawai($pegawaiBawahan, $periode);
            }
        }

        $hasilByPegawai = HasilPenilaian::whereIn('pegawai_id', $bawahan->pluck('id'))
            ->when($periode, fn($q) => $q->where('periode_id', $periode->id))
            ->get()
            ->keyBy('pegawai_id');

        $hasilTim = $bawahan
            ->map(function (Pegawai $pegawaiBawahan) use ($hasilByPegawai) {
                $hasil = $hasilByPegawai->get($pegawaiBawahan->id)
                    ?? new HasilPenilaian(['status' => 'belum dinilai']);

                return $hasil->setRelation('pegawai', $pegawaiBawahan);
            })
            ->sortByDesc(fn(HasilPenilaian $hasil) => $hasil->created_at?->getTimestamp() ?? 0)
            ->values();

        $bulanAkhirTren = now()->startOfMonth();
        $bulanMulaiTren = $bulanAkhirTren->copy()->subMonths(4);
        $trenPeriods = PeriodePenilaian::query()
            ->whereDate('tanggal_mulai', '>=', $bulanMulaiTren->toDateString())
            ->whereDate('tanggal_mulai', '<', $bulanAkhirTren->copy()->addMonth()->toDateString())
            ->get(['id', 'tanggal_mulai']);
        $periodeBulan = $trenPeriods->mapWithKeys(
            fn(PeriodePenilaian $trenPeriode) => [$trenPeriode->id => $trenPeriode->tanggal_mulai->format('Y-m')]
        );

        $trenHasil = HasilPenilaian::query()
            ->whereIn('pegawai_id', $bawahan->pluck('id'))
            ->whereIn('periode_id', $trenPeriods->pluck('id'))
            ->get(['periode_id', 'rata_skor_kpi', 'nilai_akhir'])
            ->groupBy(fn(HasilPenilaian $hasil) => $periodeBulan->get($hasil->periode_id));

        $trenPenilaian = collect(range(0, 4))->map(function (int $offset) use ($bulanMulaiTren, $trenHasil) {
            $bulan = $bulanMulaiTren->copy()->addMonths($offset);
            $hasilPeriode = $trenHasil->get($bulan->format('Y-m'), collect());

            return [
                'label' => $bulan->translatedFormat('M'),
                'avg_kpi' => $hasilPeriode->whereNotNull('rata_skor_kpi')->avg('rata_skor_kpi'),
                'avg_nilai' => $hasilPeriode->whereNotNull('nilai_akhir')->avg('nilai_akhir'),
            ];
        });

        $perPage = 10;
        $hasilPenilaians = (new LengthAwarePaginator(
            $hasilTim->forPage(LengthAwarePaginator::resolveCurrentPage(), $perPage)->values(),
            $hasilTim->count(),
            $perPage,
            LengthAwarePaginator::resolveCurrentPage(),
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'query' => $request->query(),
            ]
        ))->withQueryString();

        return view('atasan.hasil-penilaian.index', [
            'hasilPenilaians' => $hasilPenilaians,
            'hasilTim' => $hasilTim,
            'periode' => $periode,
            'periodeOptions' => $periodeOptions,
            'trenPenilaian' => $trenPenilaian,
            'rentangTren' => $bulanMulaiTren->translatedFormat('M') . ' - ' . $bulanAkhirTren->translatedFormat('M Y'),
        ]);
    }



    public function show(Request $request, Pegawai $pegawai, HasilPenilaianService $hasilPenilaianService)
    {
        $pegawaiBawahan = $this->cekBawahan($request, $pegawai);

        $periode = $this->resolvePeriode($request);

        $hasil = null;
        if ($periode) {
            $hasil = ($this->canSyncPeriode($periode)
                ? $hasilPenilaianService->syncForPegawai($pegawaiBawahan, $periode)
                : null)
                ?? HasilPenilaian::where('pegawai_id', $pegawaiBawahan->id)
                ->where('periode_id', $periode->id)
                ->first();
        }
        $presensiPotongans = $hasil && $periode
            ? PresensiPotongan::with('jenisPotongan')
            ->where('pegawai_id', $pegawaiBawahan->id)
            ->where('periode_id', $periode->id)
            ->get()
            : collect();

        return view('atasan.hasil-penilaian.show', [
            'pegawai' => $pegawaiBawahan,
            'periode' => $periode,
            'hasil' => $hasil,
            'presensiPotongans' => $presensiPotongans,
        ]);
    }



    public function generate(Request $request, Pegawai $pegawai, HasilPenilaianService $hasilPenilaianService)
    {
        $pegawaiBawahan = $this->cekBawahan($request, $pegawai);

        $periode = $this->resolvePeriode($request);

        abort_if(! $periode, 404, 'Periode penilaian aktif tidak ditemukan.');

        $hasil = $hasilPenilaianService->syncForPegawai($pegawaiBawahan, $periode);

        if (! $hasil) {
            return redirect()
                ->back()
                ->with('error', 'Penilaian KPI dan Kompetensi untuk pegawai ini pada periode terpilih belum lengkap.');
        }

        return redirect()
            ->route('atasan.hasil-penilaian.show', $pegawaiBawahan)
            ->with('success', 'Hasil penilaian berhasil dihitung dan disimpan (status: draft).')
            ->with('hasil_id', $hasil->id);
    }

    private function cekBawahan(Request $request, Pegawai $pegawai): Pegawai
    {
        $atasan = $request->user()->pegawai;

        abort_if(! $atasan, 403, 'Data pegawai untuk akun Anda tidak ditemukan.');

        $pegawaiBawahan = $atasan->bawahans()->find($pegawai->id);

        abort_if(! $pegawaiBawahan, 403, 'Anda tidak memiliki akses terhadap hasil penilaian pegawai ini.');

        return $pegawaiBawahan;
    }



    private function resolvePeriode(Request $request): ?PeriodePenilaian
    {
        $periodeId = $request->input('periode_id', $request->query('periode_id'));

        if ($periodeId) {
            return PeriodePenilaian::find($periodeId);
        }

        $periodeAktif = PeriodePenilaian::where('status', 'berjalan')
            ->orderByDesc('tanggal_mulai')
            ->first();

        if ($periodeAktif) {
            return $periodeAktif;
        }

        return PeriodePenilaian::orderByDesc('tanggal_mulai')->first();
    }

    private function canSyncPeriode(?PeriodePenilaian $periode): bool
    {
        return $periode !== null
            && $periode->status === 'berjalan'
            && $periode->tanggal_selesai->gte(now()->toDateString());
    }
}
