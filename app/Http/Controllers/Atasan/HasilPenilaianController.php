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

        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'predikat' => ['nullable', 'in:semua,sangat_baik,baik,perbaikan'],
            'periode_id' => ['nullable', 'integer', 'exists:periode_penilaians,id'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $search = trim($validated['search'] ?? '');
        $filterPredikat = $validated['predikat'] ?? 'semua';

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

        // Hasil penilaian hanya dari periode yang dipilih.
        // Jika belum ada periode, jangan mengambil data lintas periode.
        $hasilByPegawai = $periode
            ? HasilPenilaian::query()
            ->whereIn('pegawai_id', $bawahan->pluck('id'))
            ->where('periode_id', $periode->id)
            ->get()
            ->keyBy('pegawai_id')
            : collect();

        $hasilTim = $bawahan
            ->map(function (Pegawai $pegawaiBawahan) use ($hasilByPegawai) {
                $hasil = $hasilByPegawai->get($pegawaiBawahan->id)
                    ?? new HasilPenilaian(['status' => 'belum dinilai']);

                return $hasil->setRelation('pegawai', $pegawaiBawahan);
            })
            ->sortBy(fn(HasilPenilaian $hasil) => mb_strtolower($hasil->pegawai->nama))
            ->values();

        // Statistik dihitung dari seluruh pegawai, bukan halaman pagination.
        $hasilDinilai = $hasilTim->filter(
            fn(HasilPenilaian $hasil) =>
            $hasil->exists && $hasil->nilai_akhir !== null
        );

        $totalPegawai = $hasilTim->count();
        $selesai = $hasilDinilai->count();

        $avg = $hasilDinilai->avg('nilai_akhir');
        $avgTukin = $hasilDinilai->avg('tukin_bruto');

        $tuntasPercent = $totalPegawai > 0
            ? round(($selesai / $totalPegawai) * 100)
            : 0;

        $jumlahPredikat = [
            'semua' => $totalPegawai,
            'sangat_baik' => $hasilTim->where('predikat', 'Sangat Baik')->count(),
            'baik' => $hasilTim->where('predikat', 'Baik')->count(),
            'perbaikan' => $hasilTim->filter(
                fn($hasil) => in_array(
                    $hasil->predikat,
                    ['Cukup', 'Kurang', 'Perlu Perbaikan'],
                    true
                )
            )->count(),
        ];

        // Filter hanya memengaruhi daftar pegawai.
        $hasilFiltered = $hasilTim
            ->filter(function (HasilPenilaian $hasil) use ($search, $filterPredikat) {
                $nama = mb_strtolower($hasil->pegawai->nama ?? '');
                $jabatan = mb_strtolower(
                    $hasil->pegawai->jabatan->nama_jabatan ?? ''
                );
                $predikat = mb_strtolower($hasil->predikat ?? 'Belum dinilai');

                $cocokSearch = $search === ''
                    || str_contains($nama, mb_strtolower($search))
                    || str_contains($jabatan, mb_strtolower($search))
                    || str_contains($predikat, mb_strtolower($search));

                $cocokPredikat = match ($filterPredikat) {
                    'sangat_baik' => $hasil->predikat === 'Sangat Baik',
                    'baik' => $hasil->predikat === 'Baik',
                    'perbaikan' => in_array(
                        $hasil->predikat,
                        ['Cukup', 'Kurang', 'Perlu Perbaikan'],
                        true
                    ),
                    default => true,
                };

                return $cocokSearch && $cocokPredikat;
            })
            ->values();

        $perPage = 10;
        $page = LengthAwarePaginator::resolveCurrentPage();

        $hasilPenilaians = new LengthAwarePaginator(
            $hasilFiltered->forPage($page, $perPage)->values(),
            $hasilFiltered->count(),
            $perPage,
            $page,
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'query' => $request->query(),
            ]
        );

        $hasilPenilaians->withQueryString();

        // Tren menggunakan hasil penilaian aktual dari database.
        $bulanAkhirTren = $periode
            ? $periode->tanggal_mulai->copy()->startOfMonth()
            : now()->startOfMonth();

        $bulanMulaiTren = $bulanAkhirTren->copy()->subMonths(4);

        $trenPeriods = PeriodePenilaian::query()
            ->whereDate('tanggal_mulai', '>=', $bulanMulaiTren->toDateString())
            ->whereDate(
                'tanggal_mulai',
                '<',
                $bulanAkhirTren->copy()->addMonth()->toDateString()
            )
            ->get(['id', 'tanggal_mulai']);

        $periodeBulan = $trenPeriods->mapWithKeys(
            fn(PeriodePenilaian $item) => [
                $item->id => $item->tanggal_mulai->format('Y-m')
            ]
        );

        $trenHasil = HasilPenilaian::query()
            ->whereIn('pegawai_id', $bawahan->pluck('id'))
            ->whereIn('periode_id', $trenPeriods->pluck('id'))
            ->get(['periode_id', 'rata_skor_kpi', 'nilai_akhir'])
            ->groupBy(
                fn(HasilPenilaian $hasil) =>
                $periodeBulan->get($hasil->periode_id)
            );

        $trenPenilaian = collect(range(0, 4))
            ->map(function (int $offset) use ($bulanMulaiTren, $trenHasil) {
                $bulan = $bulanMulaiTren->copy()->addMonths($offset);
                $data = $trenHasil->get($bulan->format('Y-m'), collect());

                return [
                    'label' => $bulan->translatedFormat('M'),
                    'periode' => $bulan->translatedFormat('F Y'),
                    'avg_kpi' => $data->whereNotNull('rata_skor_kpi')
                        ->avg('rata_skor_kpi'),
                    'avg_nilai' => $data->whereNotNull('nilai_akhir')
                        ->avg('nilai_akhir'),
                ];
            });

        return view('atasan.hasil-penilaian.index', [
            'hasilPenilaians' => $hasilPenilaians,
            'hasilTim' => $hasilTim,
            'periode' => $periode,
            'periodeOptions' => $periodeOptions,
            'trenPenilaian' => $trenPenilaian,
            'rentangTren' => $bulanMulaiTren->translatedFormat('M')
                . ' - ' . $bulanAkhirTren->translatedFormat('M Y'),
            'search' => $search,
            'filterPredikat' => $filterPredikat,
            'jumlahPredikat' => $jumlahPredikat,
            'totalPegawai' => $totalPegawai,
            'selesai' => $selesai,
            'avg' => $avg,
            'avgTukin' => $avgTukin,
            'tuntasPercent' => $tuntasPercent,
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
