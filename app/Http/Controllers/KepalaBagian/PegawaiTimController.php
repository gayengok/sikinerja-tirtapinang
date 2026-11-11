<?php

namespace App\Http\Controllers\KepalaBagian;

use App\Http\Controllers\Controller;
use App\Models\HasilPenilaian;
use App\Models\LaporanHarian;
use App\Models\Pegawai;
use App\Models\PeriodePenilaian;
use App\Services\TeamVisibilityService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PegawaiTimController extends Controller
{
    public function index(Request $request, TeamVisibilityService $teamVisibility)
    {
        $pegawaiIds = $teamVisibility->teamMemberIds($request->user());
        $kepalaBagian = $request->user()->pegawai;
        $periodeAktif = PeriodePenilaian::where('status', 'berjalan')
            ->orderByDesc('tanggal_mulai')
            ->first();

        $laporanQuery = LaporanHarian::whereIn('pegawai_id', $pegawaiIds);
        $hasilQuery = HasilPenilaian::whereIn('pegawai_id', $pegawaiIds);
        if ($periodeAktif) {
            $laporanQuery->whereBetween('tanggal', [
                $periodeAktif->tanggal_mulai,
                $periodeAktif->tanggal_selesai,
            ]);
            $hasilQuery->where('periode_id', $periodeAktif->id);
        }

        $laporanCounts = $laporanQuery
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');
        $hasilPenilaian = $hasilQuery
            ->whereNotNull('nilai_akhir')
            ->get(['pegawai_id', 'nilai_akhir']);

        $scoreQuery = DB::table('hasil_penilaians')
            ->select('pegawai_id')
            ->selectRaw('AVG(nilai_akhir) as nilai_akhir')
            ->when($periodeAktif, fn ($query) => $query->where('periode_id', $periodeAktif->id))
            ->groupBy('pegawai_id');

        $query = Pegawai::query()
            ->whereIn('pegawais.id', $pegawaiIds)
            ->leftJoinSub($scoreQuery, 'hasil_tim', fn ($join) => $join->on('hasil_tim.pegawai_id', '=', 'pegawais.id'))
            ->leftJoin('users as team_users', 'team_users.id', '=', 'pegawais.user_id')
            ->select('pegawais.*', 'hasil_tim.nilai_akhir as skp_score')
            ->with(['user', 'jabatan.divisi', 'atasan.jabatan'])
            ->withCount(['laporanHarians as laporan_menunggu_count' => function (Builder $laporanQuery) use ($periodeAktif) {
                $laporanQuery->where('status', 'diajukan');
                if ($periodeAktif) {
                    $laporanQuery->whereBetween('tanggal', [
                        $periodeAktif->tanggal_mulai,
                        $periodeAktif->tanggal_selesai,
                    ]);
                }
            }]);

        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $query->where(function (Builder $builder) use ($search) {
                $builder->where('pegawais.nama', 'like', "%{$search}%")
                    ->orWhereHas('jabatan', fn (Builder $jabatanQuery) => $jabatanQuery->where('nama_jabatan', 'like', "%{$search}%"));
            });
        }

        match ($request->query('status')) {
            'aktif' => $query->where('pegawais.status', 'aktif'),
            'review' => $query->whereHas('laporanHarians', function (Builder $laporanQuery) use ($periodeAktif) {
                $laporanQuery->where('status', 'diajukan');
                if ($periodeAktif) {
                    $laporanQuery->whereBetween('tanggal', [
                        $periodeAktif->tanggal_mulai,
                        $periodeAktif->tanggal_selesai,
                    ]);
                }
            }),
            'nonaktif' => $query->where('pegawais.status', '!=', 'aktif'),
            default => null,
        };

        $query->orderByRaw("CASE WHEN team_users.role = 'atasan' THEN 0 ELSE 1 END");

        match ($request->query('sort', 'nama_az')) {
            'nama_za' => $query->orderByDesc('pegawais.nama'),
            'skp_desc' => $query->orderByRaw('hasil_tim.nilai_akhir IS NULL')->orderByDesc('hasil_tim.nilai_akhir')->orderBy('pegawais.nama'),
            'laporan_desc' => $query->orderByDesc('laporan_menunggu_count')->orderBy('pegawais.nama'),
            default => $query->orderBy('pegawais.nama'),
        };

        $pegawais = $query
            ->paginate(10)
            ->withQueryString();

        $atasan = $kepalaBagian->bawahans()
            ->whereHas('user', fn (Builder $builder) => $builder->where('role', 'atasan'))
            ->where('status', 'aktif')
            ->with('jabatan.divisi')
            ->get();
        $unitKerja = $atasan->pluck('jabatan.divisi.nama_divisi')->filter()->unique()->implode(', ');
        $pegawaiPerluReview = Pegawai::whereIn('pegawais.id', $pegawaiIds)
            ->whereHas('laporanHarians', function (Builder $laporanQuery) use ($periodeAktif) {
                $laporanQuery->where('status', 'diajukan');
                if ($periodeAktif) {
                    $laporanQuery->whereBetween('tanggal', [
                        $periodeAktif->tanggal_mulai,
                        $periodeAktif->tanggal_selesai,
                    ]);
                }
            })
            ->count();
        $totalLaporan = (int) $laporanCounts->sum();
        $laporanDisetujui = (int) ($laporanCounts['disetujui'] ?? 0);
        $totalPegawai = Pegawai::whereIn('id', $pegawaiIds)->count();
        $pegawaiAktif = Pegawai::whereIn('id', $pegawaiIds)->where('status', 'aktif')->count();
        $rataSkp = $hasilPenilaian->isNotEmpty()
            ? round((float) $hasilPenilaian->avg('nilai_akhir'), 1)
            : null;
        $persentaseLaporanDisetujui = $totalLaporan > 0
            ? (int) round(($laporanDisetujui / $totalLaporan) * 100)
            : null;

        return view('kepala_bagian.pegawai.index', compact(
            'pegawais',
            'periodeAktif',
            'totalPegawai',
            'pegawaiAktif',
            'rataSkp',
            'totalLaporan',
            'laporanDisetujui',
            'laporanCounts',
            'persentaseLaporanDisetujui',
            'unitKerja',
            'pegawaiPerluReview',
            'search'
        ));
    }
}
