<?php

namespace App\Http\Controllers\Atasan;

use App\Http\Controllers\Controller;
use App\Models\LaporanHarian;
use App\Models\MasterKpi;
use App\Models\Pegawai;
use App\Models\PenilaianKpi;
use App\Models\PeriodePenilaian;
use App\Services\PegawaiNotificationService;
use App\Services\TeamVisibilityService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ApprovalLaporanController extends Controller
{
    // GANTI HANYA method index() di ApprovalLaporanController.php dengan method ini.
    public function index(Request $request, TeamVisibilityService $teamVisibility)
    {
        $bawahanIds = $teamVisibility->reportEmployeeIds($request->user());

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'bulan' => ['nullable', 'date_format:Y-m'],
            'tanggal' => ['nullable', 'integer', 'between:1,31'],
        ]);

        $bulanAktif = $filters['bulan'] ?? now()->format('Y-m');
        $awalBulan = Carbon::createFromFormat('!Y-m', $bulanAktif)->startOfMonth();
        $akhirBulan = $awalBulan->copy()->endOfMonth();
        $jumlahHari = $awalBulan->daysInMonth;
        $tanggalAktif = isset($filters['tanggal']) && (int) $filters['tanggal'] <= $jumlahHari
            ? (int) $filters['tanggal']
            : null;

        $status = $request->query('status', 'diajukan');
        if (! in_array($status, ['draft', 'diajukan', 'disetujui', 'ditolak'], true)) {
            $status = 'diajukan';
        }

        // Query dasar khusus bulan aktif; tidak mengikuti filter status / tanggal.
        $periodeQuery = LaporanHarian::query()
            ->whereIn('pegawai_id', $bawahanIds)
            ->whereBetween('tanggal', [$awalBulan->toDateString(), $akhirBulan->toDateString()]);

        $statusCounts = (clone $periodeQuery)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $activityCounts = (clone $periodeQuery)
            ->select('tanggal', DB::raw('COUNT(*) as total'))
            ->groupBy('tanggal')
            ->get()
            ->mapWithKeys(fn ($row) => [
                Carbon::parse($row->tanggal)->toDateString() => (int) $row->total,
            ]);

        // Distribusi status per tanggal untuk grafik batang bertumpuk.
        $activityStatusCounts = (clone $periodeQuery)
            ->select('tanggal', 'status', DB::raw('COUNT(*) as total'))
            ->groupBy('tanggal', 'status')
            ->get()
            ->groupBy(fn($row) => Carbon::parse($row->tanggal)->toDateString())
            ->map(fn($rows) => $rows->pluck('total', 'status')->map(fn($total) => (int) $total)->all())
            ->all();

        // Daftar laporan mengikuti status, pencarian, dan tanggal yang diklik.
        $laporanQuery = (clone $periodeQuery)->where('status', $status);

        if ($tanggalAktif !== null) {
            $laporanQuery->whereDate('tanggal', $awalBulan->copy()->day($tanggalAktif)->toDateString());
        }

        if (! empty($filters['q'])) {
            $search = trim($filters['q']);
            $laporanQuery->where(function ($query) use ($search) {
                $query->where('catatan', 'like', "%{$search}%")
                    ->orWhereHas('pegawai', function ($pegawaiQuery) use ($search) {
                        $pegawaiQuery->where('nama', 'like', "%{$search}%")
                            ->orWhereHas('jabatan', function ($jabatanQuery) use ($search) {
                                $jabatanQuery->where('nama_jabatan', 'like', "%{$search}%")
                                    ->orWhereHas('divisi', fn($divisiQuery) => $divisiQuery->where('nama_divisi', 'like', "%{$search}%"));
                            });
                    })
                    ->orWhereHas('details.templateLaporan', fn($templateQuery) => $templateQuery->where('nama_field', 'like', "%{$search}%"));
            });
        }

        $laporans = $laporanQuery
            ->with(['pegawai.jabatan.divisi'])
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('atasan.laporan.index', [
            'laporans' => $laporans,
            'statusAktif' => $status,
            'statusCounts' => $statusCounts,
            'activityCounts' => $activityCounts,
            'activityStatusCounts' => $activityStatusCounts,
            'filters' => $filters,
            'bulanAktif' => $bulanAktif,
            'bulanLabel' => $awalBulan->copy()->locale('id')->translatedFormat('F Y'),
            'awalBulan' => $awalBulan,
            'jumlahHari' => $jumlahHari,
            'tanggalAktif' => $tanggalAktif,
            'routePrefix' => $teamVisibility->routePrefix($request->user()),
        ]);
    }



    public function show(Request $request, LaporanHarian $laporanHarian, TeamVisibilityService $teamVisibility)
    {
        $bawahanIds = $teamVisibility->reportEmployeeIds($request->user());

        abort_unless($bawahanIds->contains($laporanHarian->pegawai_id), 403, 'Anda tidak memiliki akses terhadap laporan ini.');

        $laporanHarian->load(['pegawai.jabatan.divisi', 'details.templateLaporan', 'penyetuju']);

        return view('atasan.laporan.show', [
            'laporanHarian' => $laporanHarian,
            'routePrefix' => $teamVisibility->routePrefix($request->user()),
        ]);
    }

    public function image(Request $request, LaporanHarian $laporanHarian, TeamVisibilityService $teamVisibility)
    {
        $bawahanIds = $teamVisibility->reportEmployeeIds($request->user());

        abort_unless($bawahanIds->contains($laporanHarian->pegawai_id), 403, 'Anda tidak memiliki akses terhadap laporan ini.');
        abort_unless($laporanHarian->bukti_gambar, 404, 'Bukti gambar tidak ditemukan.');

        $disk = Storage::disk('public');
        abort_unless($disk->exists($laporanHarian->bukti_gambar), 404, 'File bukti gambar tidak ditemukan.');

        return response()->file($disk->path($laporanHarian->bukti_gambar));
    }

    public function approve(
        Request $request,
        LaporanHarian $laporanHarian,
        TeamVisibilityService $teamVisibility,
        PegawaiNotificationService $notifications
    ) {
        $atasan = $request->user()->pegawai;

        abort_if(! $atasan, 403, 'Data pegawai untuk akun Anda tidak ditemukan.');

        $bawahanIds = $teamVisibility->reportEmployeeIds($request->user());

        abort_unless($bawahanIds->contains($laporanHarian->pegawai_id), 403, 'Anda tidak memiliki akses terhadap laporan ini.');

        if ($laporanHarian->status !== 'diajukan') {
            return redirect()
                ->route($teamVisibility->routePrefix($request->user()) . '.laporan.show', $laporanHarian)
                ->with('error', 'Hanya laporan berstatus "diajukan" yang dapat disetujui.');
        }

        DB::transaction(function () use ($laporanHarian, $atasan, $notifications) {
            $laporanHarian->update([
                'status' => 'disetujui',
                'disetujui_oleh' => $atasan->id,
                'disetujui_at' => now(),
            ]);

            $notifications->send(
                $laporanHarian->pegawai,
                'laporan',
                'Laporan disetujui',
                'Laporan harian tanggal ' . $laporanHarian->tanggal->translatedFormat('d F Y') . ' telah disetujui atasan.',
                route('pegawai.laporan-harian.create', [
                    'tanggal' => $laporanHarian->tanggal->toDateString(),
                ], false)
            );

            $this->hitungKpiOtomatis($laporanHarian->pegawai_id, $laporanHarian->tanggal);
        });

        return redirect()
            ->route($teamVisibility->routePrefix($request->user()) . '.laporan.index')
            ->with('success', 'Laporan berhasil disetujui.');
    }

    /**
     * Menghitung KPI dari seluruh laporan yang sudah disetujui pada periode aktif.
     * Atasan tidak mengisi skor; skor ditentukan dari persentase capaian.
     */
    private function hitungKpiOtomatis(int $pegawaiId, string $tanggalLaporan): void
    {
        $pegawai = Pegawai::with('jabatan')->findOrFail($pegawaiId);
        $periode = PeriodePenilaian::whereDate('tanggal_mulai', '<=', $tanggalLaporan)
            ->whereDate('tanggal_selesai', '>=', $tanggalLaporan)
            ->orderByDesc('tanggal_mulai')
            ->first();

        if (! $periode || ! $pegawai->jabatan) {
            return;
        }

        $masterKpis = MasterKpi::where('jabatan_id', $pegawai->jabatan_id)
            ->orderBy('urutan')
            ->get();

        $laporans = LaporanHarian::where('pegawai_id', $pegawaiId)
            ->where('status', 'disetujui')
            ->whereBetween('tanggal', [$periode->tanggal_mulai, $periode->tanggal_selesai])
            ->with('details.templateLaporan')
            ->get();

        $realisasiPerKpi = [];
        foreach ($laporans as $laporan) {
            foreach ($laporan->details as $detail) {
                $kpiId = $detail->templateLaporan?->terhubung_ke_kpi_id;
                $nilai = str_replace(',', '.', trim((string) $detail->nilai));

                if (! $kpiId || $nilai === '' || ! is_numeric($nilai)) {
                    continue;
                }

                $realisasiPerKpi[$kpiId] = ($realisasiPerKpi[$kpiId] ?? 0) + (float) $nilai;
            }
        }

        foreach ($masterKpis as $masterKpi) {
            if (! array_key_exists($masterKpi->id, $realisasiPerKpi)) {
                continue;
            }

            $target = (float) ($masterKpi->target_default ?? 0);
            $realisasi = (float) $realisasiPerKpi[$masterKpi->id];
            $persenCapaian = $this->hitungPersenCapaian($masterKpi->arah_capaian, $target, $realisasi);

            PenilaianKpi::updateOrCreate(
                [
                    'pegawai_id' => $pegawaiId,
                    'periode_id' => $periode->id,
                    'master_kpi_id' => $masterKpi->id,
                ],
                [
                    'target' => $target,
                    'realisasi_otomatis' => $realisasi,
                    'realisasi_final' => $realisasi,
                    'persen_capaian' => $persenCapaian,
                    'skor' => $this->skorDariCapaian($persenCapaian),
                    'keterangan' => 'Dihitung otomatis dari laporan harian yang disetujui.',
                ]
            );
        }
    }

    private function hitungPersenCapaian(string $arahCapaian, float $target, float $realisasi): float
    {
        if ($target <= 0) {
            return 0;
        }

        if ($arahCapaian === 'minimal') {
            return $realisasi <= 0 ? 0 : round(($target / $realisasi) * 100, 2);
        }

        return round(($realisasi / $target) * 100, 2);
    }

    private function skorDariCapaian(float $persenCapaian): int
    {
        return match (true) {
            $persenCapaian >= 100 => 5,
            $persenCapaian >= 90 => 4,
            $persenCapaian >= 75 => 3,
            $persenCapaian >= 60 => 2,
            default => 1,
        };
    }

    public function reject(
        Request $request,
        LaporanHarian $laporanHarian,
        TeamVisibilityService $teamVisibility,
        PegawaiNotificationService $notifications
    ) {
        $atasan = $request->user()->pegawai;

        abort_if(! $atasan, 403, 'Data pegawai untuk akun Anda tidak ditemukan.');

        $bawahanIds = $teamVisibility->reportEmployeeIds($request->user());

        abort_unless($bawahanIds->contains($laporanHarian->pegawai_id), 403, 'Anda tidak memiliki akses terhadap laporan ini.');

        if ($laporanHarian->status !== 'diajukan') {
            return redirect()
                ->route($teamVisibility->routePrefix($request->user()) . '.laporan.show', $laporanHarian)
                ->with('error', 'Hanya laporan berstatus "diajukan" yang dapat ditolak.');
        }

        $validated = $request->validate([
            'alasan_penolakan' => ['required', 'string', 'max:500'],
        ], [
            'alasan_penolakan.required' => 'Alasan penolakan wajib diisi.',
        ]);

        // Catatan: tabel laporan_harians saat ini belum memiliki kolom khusus
        // untuk alasan penolakan. Sebagai solusi sementara, alasan disimpan
        // pada kolom "catatan" dengan prefix agar mudah dibedakan dari catatan pegawai.
        // Rekomendasi jangka panjang: tambahkan kolom "alasan_penolakan" (text, nullable)
        // pada migration laporan_harians agar catatan pegawai dan alasan atasan terpisah.
        $penolak = match ($request->user()->role) {
            'kepala_bagian' => 'Kepala Bagian',
            'direktur' => 'Direktur',
            default => 'atasan',
        };
        $catatanBaru = trim(($laporanHarian->catatan ? $laporanHarian->catatan . "\n\n" : '')
            . '[Ditolak oleh ' . $penolak . ']: ' . $validated['alasan_penolakan']);

        DB::transaction(function () use ($laporanHarian, $atasan, $catatanBaru, $validated, $notifications) {
            $laporanHarian->update([
                'status' => 'ditolak',
                'catatan' => $catatanBaru,
                'disetujui_oleh' => $atasan->id,
                'disetujui_at' => now(),
            ]);

            $notifications->send(
                $laporanHarian->pegawai,
                'laporan',
                'Laporan ditolak',
                'Laporan harian tanggal ' . $laporanHarian->tanggal->translatedFormat('d F Y')
                    . ' ditolak. Alasan: ' . $validated['alasan_penolakan'],
                route('pegawai.laporan-harian.create', [
                    'tanggal' => $laporanHarian->tanggal->toDateString(),
                ], false)
            );
        });

        return redirect()
            ->route($teamVisibility->routePrefix($request->user()) . '.laporan.index')
            ->with('success', 'Laporan berhasil ditolak.');
    }
}
