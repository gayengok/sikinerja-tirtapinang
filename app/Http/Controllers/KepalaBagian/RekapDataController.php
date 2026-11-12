<?php

namespace App\Http\Controllers\KepalaBagian;

use App\Http\Controllers\Controller;
use App\Models\HasilPenilaian;
use App\Models\Pegawai;
use App\Models\PeriodePenilaian;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class RekapDataController extends Controller
{
    public function index(Request $request)
    {
        return view('kepala_bagian.rekap-data.rekap_data', $this->rekapData($request));
    }

    public function downloadPdf(Request $request)
    {
        $data = $this->rekapData($request);
        $data['logo'] = 'data:image/jpeg;base64,'.base64_encode(file_get_contents(public_path('img/logo-pdf.jpg')));

        return Pdf::loadView('kepala_bagian.rekap-data.pdf', $data)
            ->setPaper('a4', 'landscape')
            ->download(sprintf('rekap-kehadiran-%04d-%02d.pdf', $data['tahun'], $data['bulan']));
    }

    private function rekapData(Request $request): array
    {
        $validated = $request->validate([
            'bulan' => ['sometimes', 'integer', 'between:1,12'],
            'tahun' => ['sometimes', 'integer', 'between:2000,'.(now()->year + 1)],
        ]);
        $bulan = (int) ($validated['bulan'] ?? now()->month);
        $tahun = (int) ($validated['tahun'] ?? now()->year);
        $tanggalMulai = Carbon::create($tahun, $bulan, 1)->startOfMonth();
        $tanggalSelesai = $tanggalMulai->copy()->endOfMonth();
        $periode = PeriodePenilaian::query()
            ->whereDate('tanggal_mulai', '<=', $tanggalSelesai->toDateString())
            ->whereDate('tanggal_selesai', '>=', $tanggalMulai->toDateString())
            ->orderByRaw(
                'CASE WHEN tanggal_mulai BETWEEN ? AND ? THEN 0 ELSE 1 END',
                [$tanggalMulai->toDateString(), $tanggalSelesai->toDateString()]
            )
            ->orderByDesc('tanggal_mulai')
            ->orderByDesc('id')
            ->first();

        $tukinByPegawai = $periode
            ? HasilPenilaian::query()
                ->where('periode_id', $periode->id)
                ->pluck('tukin_neto', 'pegawai_id')
            : collect();

        $pegawais = Pegawai::query()
            ->whereHas('user', fn (Builder $query) => $query->whereIn('role', ['atasan', 'pegawai']))
            ->with('jabatan.divisi')
            ->withCount(['absensis as jumlah_kehadiran' => function (Builder $query) use ($tanggalMulai, $tanggalSelesai) {
                $query->whereBetween('tanggal', [
                    $tanggalMulai->toDateString(),
                    $tanggalSelesai->toDateString(),
                ])->where('status', 'hadir');
            }])
            ->orderBy('nama')
            ->get()
            ->map(function (Pegawai $pegawai) use ($tukinByPegawai) {
                $pegawai->setAttribute('tukin_neto', $tukinByPegawai->get($pegawai->id));

                return $pegawai;
            });

        return [
            'pegawais' => $pegawais,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'tanggalMulai' => $tanggalMulai,
            'tanggalSelesai' => $tanggalSelesai,
            'totalKehadiran' => $pegawais->sum('jumlah_kehadiran'),
            'totalTukin' => $pegawais->sum(fn (Pegawai $pegawai) => (float) ($pegawai->tukin_neto ?? 0)),
            'periodeTukinTersedia' => $periode !== null,
            'tukinTersedia' => $pegawais->contains(fn (Pegawai $pegawai) => $pegawai->tukin_neto !== null),
            'tanggalCetak' => Carbon::now(),
        ];
    }
}
