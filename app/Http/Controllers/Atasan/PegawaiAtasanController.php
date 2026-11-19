<?php

namespace App\Http\Controllers\Atasan;

use App\Http\Controllers\Controller;
use App\Models\HasilPenilaian;
use App\Models\LaporanHarian;
use App\Models\Pegawai;
use App\Models\PeriodePenilaian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PegawaiAtasanController extends Controller
{
    public function index(Request $request)
    {
        $atasan = $request->user()->pegawai;

        abort_if(!$atasan, 403, 'Data pegawai untuk akun Anda tidak ditemukan.');

        $search = trim((string) $request->query('search', ''));
        $filter = (string) $request->query('filter', 'semua');

        if (!in_array($filter, ['semua', 'aktif', 'perlu_penilaian'], true)) {
            $filter = 'semua';
        }

        $bawahanIds = $atasan->bawahans()->pluck('id');

        $totalBawahan = $bawahanIds->count();

        $bawahanAktif = Pegawai::query()
            ->whereIn('id', $bawahanIds)
            ->where('status', 'aktif')
            ->count();

        $periodeAktif = PeriodePenilaian::query()
            ->where('status', 'berjalan')
            ->orderByDesc('tanggal_mulai')
            ->first();

        $skorTim = null;
        $jumlahSudahDinilai = 0;
        $jumlahPerluPenilaian = 0;

        if ($periodeAktif) {
            $hasilTim = HasilPenilaian::query()
                ->whereIn('pegawai_id', $bawahanIds)
                ->where('periode_id', $periodeAktif->id)
                ->get(['pegawai_id', 'nilai_akhir']);

            $skorTim = $hasilTim
                ->whereNotNull('nilai_akhir')
                ->avg('nilai_akhir');

            $jumlahSudahDinilai = $hasilTim
                ->pluck('pegawai_id')
                ->unique()
                ->count();

            $jumlahPerluPenilaian = max(
                0,
                $totalBawahan - $jumlahSudahDinilai
            );
        }

        $laporanTertunda = LaporanHarian::query()
            ->whereIn('pegawai_id', $bawahanIds)
            ->where('status', 'diajukan')
            ->count();

        $query = $atasan->bawahans()
            ->with(['jabatan.divisi', 'user']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%')
                    ->orWhere('nip', 'like', '%' . $search . '%')
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('email', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('jabatan', function ($jabatanQuery) use ($search) {
                        $jabatanQuery->where('nama_jabatan', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('jabatan.divisi', function ($divisiQuery) use ($search) {
                        $divisiQuery->where('nama_divisi', 'like', '%' . $search . '%');
                    });
            });
        }

        if ($filter === 'aktif') {
            $query->where('status', 'aktif');
        }

        if ($filter === 'perlu_penilaian') {
            if ($periodeAktif) {
                $query->whereNotIn('id', function ($subquery) use ($periodeAktif) {
                    $subquery->select('pegawai_id')
                        ->from('hasil_penilaians')
                        ->where('periode_id', $periodeAktif->id);
                });
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        $pegawais = $query
            ->orderBy('nama')
            ->paginate(10)
            ->withQueryString();

        $data = [
            'pegawais' => $pegawais,
            'periodeAktif' => $periodeAktif,
            'bawahanAktif' => $bawahanAktif,
            'totalBawahan' => $totalBawahan,
            'skorTim' => $skorTim,
            'laporanTertunda' => $laporanTertunda,
            'jumlahSudahDinilai' => $jumlahSudahDinilai,
            'jumlahPerluPenilaian' => $jumlahPerluPenilaian,
            'search' => $search,
            'filter' => $filter,
        ];

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('atasan.pegawai.partials.list', $data)->render(),
                'total' => $pegawais->total(),
            ]);
        }

        return view('atasan.pegawai.index', $data);
    }

    public function show(Request $request, Pegawai $pegawai)
    {
        $atasan = $request->user()->pegawai;

        abort_if(!$atasan, 403, 'Data pegawai untuk akun Anda tidak ditemukan.');

        $pegawaiBawahan = $atasan->bawahans()
            ->with(['user', 'jabatan.divisi', 'atasan'])
            ->find($pegawai->id);

        abort_if(
            !$pegawaiBawahan,
            403,
            'Anda tidak memiliki akses terhadap data pegawai ini.'
        );

        $jumlahLaporan = $pegawaiBawahan->laporanHarians()->count();

        $laporanMenunggu = $pegawaiBawahan->laporanHarians()
            ->where('status', 'diajukan')
            ->count();

        $jumlahKehadiran = $pegawaiBawahan->absensis()
            ->where('status', 'hadir')
            ->count();

        $hasilTerakhir = HasilPenilaian::query()
            ->where('pegawai_id', $pegawaiBawahan->id)
            ->latest('id')
            ->first();

        return view('atasan.pegawai.show', [
            'pegawai' => $pegawaiBawahan,
            'jumlahLaporan' => $jumlahLaporan,
            'laporanMenunggu' => $laporanMenunggu,
            'jumlahKehadiran' => $jumlahKehadiran,
            'hasilTerakhir' => $hasilTerakhir,
        ]);
    }
}
