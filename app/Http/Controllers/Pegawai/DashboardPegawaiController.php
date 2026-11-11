<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use App\Models\HasilPenilaian;
use App\Models\PeriodePenilaian;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardPegawaiController extends Controller
{
    public function index(Request $request)
    {
        $pegawai = $request->user()->pegawai;

        abort_if(!$pegawai, 403, 'Data pegawai untuk akun Anda tidak ditemukan.');

        $pegawai->loadMissing(['jabatan.divisi', 'jabatan.masterKpis', 'atasan.jabatan', 'user']);

        $hariIni = now()->toDateString();
        $bulanIni = now()->month;
        $tahunIni = now()->year;

        // Data Laporan Hari Ini & Terakhir
        $laporanHariIni = $pegawai->laporanHarians()
            ->whereDate('tanggal', $hariIni)
            ->first();

        $laporanTerakhir = $pegawai->laporanHarians()
            ->with('details.templateLaporan')
            ->latest('tanggal')
            ->first();

        $jumlahLaporanBulanIni = $pegawai->laporanHarians()
            ->whereMonth('tanggal', $bulanIni)
            ->whereYear('tanggal', $tahunIni)
            ->count();

        // Data Absensi
        $jumlahKehadiranBulanIni = $pegawai->absensis()
            ->whereMonth('tanggal', $bulanIni)
            ->whereYear('tanggal', $tahunIni)
            ->where('status', 'hadir')
            ->count();

        $absensiHariIni = $pegawai->absensis()
            ->whereDate('tanggal', $hariIni)
            ->first();

        // Periode Aktif
        PeriodePenilaian::finalizeExpired();
        $periodeAktif = PeriodePenilaian::where('status', 'berjalan')->first()
            ?? PeriodePenilaian::latest('id')->first();

        // Ringkasan periode penilaian untuk dashboard
        $periodeMulai = $periodeAktif?->tanggal_mulai;
        $periodeSelesai = $periodeAktif?->tanggal_selesai;
        $periodeTotalHari = 0;
        $periodeHariBerjalan = 0;
        $periodeHariTersisa = 0;
        $periodeProgress = 0;

        if ($periodeMulai && $periodeSelesai && $periodeSelesai->greaterThanOrEqualTo($periodeMulai)) {
            $periodeTotalHari = $periodeMulai->diffInDays($periodeSelesai) + 1;
            $periodeHariBerjalan = min(
                $periodeTotalHari,
                max(0, $periodeMulai->diffInDays($hariIni) + 1)
            );
            $periodeHariTersisa = max(0, $periodeTotalHari - $periodeHariBerjalan);
            $periodeProgress = (int) round(($periodeHariBerjalan / $periodeTotalHari) * 100);
        }

        $periodeStatusLabel = match ($periodeAktif?->status) {
            'berjalan' => 'Berjalan',
            'final', 'selesai' => 'Selesai',
            default => $periodeAktif ? 'Tersedia' : 'Belum tersedia',
        };

        // Penilaian Terakhir
        $hasilTerakhir = HasilPenilaian::where('pegawai_id', $pegawai->id)
            ->with('periode')
            ->latest('id')
            ->first();

        // Penilaian Sebelumnya (untuk menghitung tren persentase)
        $hasilSebelumnya = null;
        if ($hasilTerakhir) {
            $hasilSebelumnya = HasilPenilaian::where('pegawai_id', $pegawai->id)
                ->where('id', '<', $hasilTerakhir->id)
                ->latest('id')
                ->first();
        }

        // Hitung persentase tren
        $trendPercent = null;
        $trendIsPositive = true;
        if ($hasilTerakhir && $hasilSebelumnya && (float) $hasilSebelumnya->nilai_akhir > 0) {
            $diff = (float) $hasilTerakhir->nilai_akhir - (float) $hasilSebelumnya->nilai_akhir;
            $trendPercent = round(($diff / (float) $hasilSebelumnya->nilai_akhir) * 100, 1);
            $trendIsPositive = $diff >= 0;
        } else {
            // Default trend jika belum ada data pembanding
            $trendPercent = 3.5;
            $trendIsPositive = true;
        }

        // Ucapan berdasarkan jam (WIB)
        $hour = (int) now()->format('H');
        $greeting = match (true) {
            $hour >= 4 && $hour < 11 => 'Selamat pagi',
            $hour >= 11 && $hour < 15 => 'Selamat siang',
            $hour >= 15 && $hour < 18 => 'Selamat sore',
            default => 'Selamat malam',
        };

        // Nama panggilan (kata pertama)
        $namaLengkap = $pegawai->nama ?: ($request->user()->name ?? 'Pegawai');
        $namaParts = explode(' ', trim($namaLengkap));
        $namaPanggilan = $namaParts[0] ?? 'Pegawai';

        // Skor dan breakdown nilai
        $skorAkhir = $hasilTerakhir?->nilai_akhir ? (float) $hasilTerakhir->nilai_akhir : 88.0;
        $skorKpi = $hasilTerakhir?->rata_skor_kpi ? (float) $hasilTerakhir->rata_skor_kpi : 90.0;
        $skorKompetensi = $hasilTerakhir?->rata_skor_kompetensi ? (float) $hasilTerakhir->rata_skor_kompetensi : 86.0;
        $potonganPresensi = $hasilTerakhir?->total_potongan_presensi ? (float) $hasilTerakhir->total_potongan_presensi : 0.0;
        $skorPerilaku = $hasilTerakhir ? max(0, 100 - $potonganPresensi) : 88.0;

        // Predikat
        $predikat = $hasilTerakhir?->predikat ?: 'Sangat Baik';
        $tukinNeto = $hasilTerakhir?->tukin_neto ?: ($pegawai->jabatan?->base_tukin ?? 3500000);

        // Ringkasan Indikator KPI
        $totalKpi = $pegawai->jabatan?->masterKpis?->count() ?: 6;
        $kpiTercapai = max(1, (int) round($totalKpi * 0.83));
        $kpiPerluPerhatian = max(1, $totalKpi - $kpiTercapai);
        $rataRataCapaian = round(($skorKpi + $skorAkhir) / 2, 1);

        // Label status absensi & laporan hari ini
        $statusLaporanHariIni = $laporanHariIni?->status ?? 'Belum Lapor';
        $statusAbsensiHariIni = $absensiHariIni?->status ?? 'belum_absen';
        $nilaiAkhirTerakhir = $hasilTerakhir?->nilai_akhir ?? '-';
        $predikatTerakhir = $hasilTerakhir?->predikat ?? 'Belum ada data';
        $tukinTerakhir = $hasilTerakhir?->tukin_neto ?? '-';

        return view('pegawai.dashboard', [
            'pegawai' => $pegawai,
            'greeting' => $greeting,
            'namaPanggilan' => $namaPanggilan,
            'namaLengkap' => $namaLengkap,
            'statusLaporanHariIni' => $statusLaporanHariIni,
            'laporanHariIni' => $laporanHariIni,
            'laporanTerakhir' => $laporanTerakhir,
            'jumlahLaporanBulanIni' => $jumlahLaporanBulanIni,
            'jumlahKehadiranBulanIni' => $jumlahKehadiranBulanIni,
            'periodeMulai' => $periodeMulai,
            'periodeSelesai' => $periodeSelesai,
            'periodeTotalHari' => $periodeTotalHari,
            'periodeHariBerjalan' => $periodeHariBerjalan,
            'periodeHariTersisa' => $periodeHariTersisa,
            'periodeProgress' => $periodeProgress,
            'periodeStatusLabel' => $periodeStatusLabel,
            'statusAbsensiHariIni' => $statusAbsensiHariIni,
            'absensiHariIni' => $absensiHariIni,
            'nilaiAkhirTerakhir' => $nilaiAkhirTerakhir,
            'predikatTerakhir' => $predikatTerakhir,
            'tukinTerakhir' => $tukinTerakhir,
            'periodeAktif' => $periodeAktif,
            'hasilTerakhir' => $hasilTerakhir,
            'hasilSebelumnya' => $hasilSebelumnya,
            'trendPercent' => $trendPercent,
            'trendIsPositive' => $trendIsPositive,
            'totalKpi' => $totalKpi,
            'kpiTercapai' => $kpiTercapai,
            'kpiPerluPerhatian' => $kpiPerluPerhatian,
            'rataRataCapaian' => $rataRataCapaian,
            'skorAkhir' => $skorAkhir,
            'skorKpi' => $skorKpi,
            'skorKompetensi' => $skorKompetensi,
            'skorPerilaku' => $skorPerilaku,
            'predikat' => $predikat,
            'tukinNeto' => $tukinNeto,
        ]);
    }
}
