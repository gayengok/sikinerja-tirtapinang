<?php

namespace App\Http\Controllers\Atasan;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\LaporanHarian;
use App\Models\PeriodePenilaian;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardAtasanController extends Controller
{
    public function index(Request $request)
    {
        $atasan = $request->user()->pegawai;

        abort_if(! $atasan, 403, 'Data pegawai untuk akun Anda tidak ditemukan.');

        $hariIni = Carbon::today();
        $tanggalHariIni = $hariIni->toDateString();
        $awalBulan = $hariIni->copy()->startOfMonth();
        $akhirBulan = $hariIni->copy()->endOfMonth();

        $bawahanIds = $atasan->bawahans()->pluck('id');
        $jumlahBawahan = $bawahanIds->count();

        $statusLaporan = ['diajukan', 'disetujui', 'ditolak'];

        $laporanBawahan = LaporanHarian::query()
            ->whereIn('pegawai_id', $bawahanIds)
            ->whereIn('status', $statusLaporan)
            ->whereDate('tanggal', '>=', $awalBulan->toDateString())
            ->whereDate('tanggal', '<=', $akhirBulan->toDateString())
            ->get(['id', 'pegawai_id', 'tanggal', 'status']);

        $absensiBawahan = Absensi::query()
            ->whereIn('pegawai_id', $bawahanIds)
            ->whereDate('tanggal', '>=', $awalBulan->toDateString())
            ->whereDate('tanggal', '<=', $akhirBulan->toDateString())
            ->get(['id', 'pegawai_id', 'tanggal']);

        $laporanPerTanggal = $laporanBawahan->groupBy(function ($laporan) {
            return Carbon::parse($laporan->tanggal)->toDateString();
        });

        $absensiPerTanggal = $absensiBawahan->groupBy(function ($absensi) {
            return Carbon::parse($absensi->tanggal)->toDateString();
        });

        $laporanDisetujui = $laporanBawahan
            ->where('status', 'disetujui')
            ->count();

        $laporanMenunggu = $laporanBawahan
            ->where('status', 'diajukan')
            ->count();

        $laporanDitolak = $laporanBawahan
            ->where('status', 'ditolak')
            ->count();

        $totalLaporan = $laporanBawahan->count();

        $persenDisetujui = $totalLaporan > 0
            ? round($laporanDisetujui / $totalLaporan * 100, 1)
            : 0;

        $persenMenunggu = $totalLaporan > 0
            ? round($laporanMenunggu / $totalLaporan * 100, 1)
            : 0;

        $persenDitolak = $totalLaporan > 0
            ? round($laporanDitolak / $totalLaporan * 100, 1)
            : 0;

        $grafikLaporan = [];
        $grafikAbsensi = [];

        for (
            $tanggal = $awalBulan->copy();
            $tanggal->lte($akhirBulan);
            $tanggal->addDay()
        ) {
            $tanggalString = $tanggal->toDateString();

            $laporanTanggal = $laporanPerTanggal->get(
                $tanggalString,
                collect()
            );

            $absensiTanggal = $absensiPerTanggal->get(
                $tanggalString,
                collect()
            );

            $disetujui = $laporanTanggal
                ->where('status', 'disetujui')
                ->count();

            $menunggu = $laporanTanggal
                ->where('status', 'diajukan')
                ->count();

            $ditolak = $laporanTanggal
                ->where('status', 'ditolak')
                ->count();

            $total = $disetujui + $menunggu + $ditolak;

            $terkirim = $laporanTanggal
                ->pluck('pegawai_id')
                ->unique()
                ->count();

            $tercatat = $absensiTanggal
                ->pluck('pegawai_id')
                ->unique()
                ->count();

            $grafikLaporan[] = [
                'tanggal' => $tanggalString,
                'label' => $tanggal->translatedFormat('d F Y'),
                'label_pendek' => $tanggal->translatedFormat('d M'),
                'disetujui' => $disetujui,
                'menunggu' => $menunggu,
                'ditolak' => $ditolak,
                'total' => $total,
                'persen_disetujui' => $total > 0
                    ? round($disetujui / $total * 100, 1)
                    : 0,
                'persen_menunggu' => $total > 0
                    ? round($menunggu / $total * 100, 1)
                    : 0,
                'persen_ditolak' => $total > 0
                    ? round($ditolak / $total * 100, 1)
                    : 0,
                'terkirim' => $terkirim,
                'belum_terkirim' => max(0, $jumlahBawahan - $terkirim),
            ];

            $grafikAbsensi[] = [
                'tanggal' => $tanggalString,
                'label' => $tanggal->translatedFormat('d F Y'),
                'label_pendek' => $tanggal->translatedFormat('d M'),
                'tercatat' => $tercatat,
                'belum_tercatat' => max(0, $jumlahBawahan - $tercatat),
            ];
        }

        $laporanHariIni = collect($grafikLaporan)
            ->firstWhere('tanggal', $tanggalHariIni);

        $absensiHariIniData = collect($grafikAbsensi)
            ->firstWhere('tanggal', $tanggalHariIni);

        $laporanTerkirimHariIni = $laporanHariIni['terkirim'] ?? 0;

        $laporanBelumTerkirimHariIni =
            $laporanHariIni['belum_terkirim'] ?? $jumlahBawahan;

        $absensiHariIni = $absensiHariIniData['tercatat'] ?? 0;

        $absensiBelumTercatatHariIni =
            $absensiHariIniData['belum_tercatat'] ?? $jumlahBawahan;

        $periodeAktif = PeriodePenilaian::query()
            ->where('status', 'berjalan')
            ->orderByDesc('tanggal_mulai')
            ->first();

        $progresPeriode = 0;

        if (
            $periodeAktif &&
            $periodeAktif->tanggal_mulai &&
            $periodeAktif->tanggal_selesai
        ) {
            $mulai = Carbon::parse(
                $periodeAktif->tanggal_mulai
            )->startOfDay();

            $selesai = Carbon::parse(
                $periodeAktif->tanggal_selesai
            )->startOfDay();

            $totalHari = max(
                1,
                (int) $mulai->diffInDays($selesai) + 1
            );

            if ($hariIni->lt($mulai)) {
                $hariBerjalan = 0;
            } elseif ($hariIni->gt($selesai)) {
                $hariBerjalan = $totalHari;
            } else {
                $hariBerjalan = (int) $mulai->diffInDays($hariIni) + 1;
            }

            $progresPeriode = (int) round(
                $hariBerjalan / $totalHari * 100
            );
        }

        $presensiAtasanHariIni = Absensi::query()
            ->where('pegawai_id', $atasan->getKey())
            ->whereDate('tanggal', $tanggalHariIni)
            ->first();

        $laporanAtasanHariIni = $atasan->laporanHarians()
            ->whereDate('tanggal', $tanggalHariIni)
            ->first();

        return view('atasan.dashboard', [
            'atasan' => $atasan,
            'tanggalHariIni' => $tanggalHariIni,
            'namaBulan' => $hariIni->translatedFormat('F Y'),
            'jumlahBawahan' => $jumlahBawahan,
            'laporanMenunggu' => $laporanMenunggu,
            'laporanDisetujui' => $laporanDisetujui,
            'laporanDitolak' => $laporanDitolak,
            'totalLaporan' => $totalLaporan,
            'persenDisetujui' => $persenDisetujui,
            'persenMenunggu' => $persenMenunggu,
            'persenDitolak' => $persenDitolak,
            'laporanTerkirimHariIni' => $laporanTerkirimHariIni,
            'laporanBelumTerkirimHariIni' => $laporanBelumTerkirimHariIni,
            'absensiHariIni' => $absensiHariIni,
            'absensiBelumTercatatHariIni' => $absensiBelumTercatatHariIni,
            'grafikLaporan' => $grafikLaporan,
            'grafikAbsensi' => $grafikAbsensi,
            'periodeAktif' => $periodeAktif,
            'progresPeriode' => $progresPeriode,
            'presensiAtasanHariIni' => $presensiAtasanHariIni,
            'laporanAtasanHariIni' => $laporanAtasanHariIni,
        ]);
    }
}
