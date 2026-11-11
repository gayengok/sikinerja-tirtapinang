<?php

namespace App\Http\Controllers\Atasan;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Pegawai;
use App\Services\TeamVisibilityService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AbsensiAtasanController extends Controller
{
    public function index(Request $request, TeamVisibilityService $teamVisibility)
    {
        $pegawaiIds = $teamVisibility->attendanceEmployeeIds($request->user());
        $jenisPegawai = $request->user()->role === 'kepala_bagian' ? 'Atasan' : 'Pegawai';

        $bulan = (int) $request->query('bulan', now()->month);
        $tahun = (int) $request->query('tahun', now()->year);
        $status = $request->query('status');
        $search = trim((string) $request->query('search', ''));

        $query = Pegawai::whereIn('id', $pegawaiIds)
            ->with(['jabatan.divisi', 'absensis' => function ($absensiQuery) use ($bulan, $tahun, $status) {
                $absensiQuery->whereMonth('tanggal', $bulan)
                    ->whereYear('tanggal', $tahun)
                    ->when($status, fn ($query) => $query->where('status', $status))
                    ->latest('tanggal');
            }])
            ->when($status, function ($query) use ($bulan, $tahun, $status) {
                $query->whereHas('absensis', function ($absensiQuery) use ($bulan, $tahun, $status) {
                    $absensiQuery->whereMonth('tanggal', $bulan)
                        ->whereYear('tanggal', $tahun)
                        ->where('status', $status);
                });
            })
            ->when($search, function ($query) use ($search) {
                $query->where(function ($employeeQuery) use ($search) {
                    $employeeQuery->where('nama', 'like', "%{$search}%")
                        ->orWhere('nip', 'like', "%{$search}%");
                });
            })
            ->orderBy('nama');

        $pegawais = $query->paginate(10)->withQueryString();

        $pegawaiRingkasan = Pegawai::whereIn('id', $pegawaiIds)
            ->with([
                'jabatan.jamKerjas',
                'absensis' => fn ($absensiQuery) => $absensiQuery
                    ->whereMonth('tanggal', $bulan)
                    ->whereYear('tanggal', $tahun),
            ])
            ->get();

        $ringkasan = [
            'total' => 0,
            'hadir' => 0,
            'izin' => 0,
            'sakit' => 0,
            'alpa' => 0,
            'telat' => 0,
        ];
        $namaHari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

        foreach ($pegawaiRingkasan as $pegawai) {
            foreach ($pegawai->absensis as $absensi) {
                $ringkasan['total']++;

                if (in_array($absensi->status, ['alpa', 'tidak_hadir'], true)) {
                    $ringkasan['alpa']++;

                    continue;
                }

                if ($absensi->status !== 'hadir') {
                    if (isset($ringkasan[$absensi->status])) {
                        $ringkasan[$absensi->status]++;
                    }

                    continue;
                }

                $jamKerjas = $pegawai->jabatan?->jamKerjas ?? collect();
                $jadwal = $jamKerjas->firstWhere('hari', $namaHari[$absensi->tanggal->dayOfWeek])
                    ?? $jamKerjas->firstWhere('hari', 'Kerja');

                $terlambat = false;
                if ($jadwal && $absensi->jam_masuk) {
                    $batasMasuk = Carbon::parse($jadwal->jam_masuk)
                        ->addMinutes((int) $jadwal->toleransi_menit);
                    $terlambat = Carbon::parse($absensi->jam_masuk)->gt($batasMasuk);
                }

                $ringkasan[$terlambat ? 'telat' : 'hadir']++;
            }
        }

        $awalPeriode = Carbon::create($tahun, $bulan, 1)->startOfDay();
        $akhirPeriode = $awalPeriode->copy()->endOfMonth()->min(now()->startOfDay());
        $hariKerja = 0;
        for ($tanggal = $awalPeriode->copy(); $tanggal->lte($akhirPeriode); $tanggal->addDay()) {
            if ($tanggal->isWeekday()) {
                $hariKerja++;
            }
        }

        return view('atasan.absensi.data-absensi', [
            'pegawais' => $pegawais,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'status' => $status,
            'search' => $search,
            'ringkasan' => $ringkasan,
            'hariKerja' => $hariKerja,
            'jumlahPegawai' => $pegawaiRingkasan->count(),
            'jenisPegawai' => $jenisPegawai,
            'routePrefix' => $teamVisibility->routePrefix($request->user()),
        ]);
    }

    public function show(Request $request, Absensi $absensi, TeamVisibilityService $teamVisibility)
    {
        $dataAbsensi = Absensi::query()
            ->whereKey($absensi->getKey())
            ->whereIn('pegawai_id', $teamVisibility->attendanceEmployeeIds($request->user()))
            ->with(['pegawai.jabatan.divisi', 'pegawai.jabatan.jamKerjas', 'diverifikasiOleh'])
            ->firstOrFail();

        $fotoTersedia = $dataAbsensi->foto_path
            && Storage::disk('public')->exists($dataAbsensi->foto_path);

        return view('atasan.absensi.show', [
            'absensi' => $dataAbsensi,
            'fotoTersedia' => $fotoTersedia,
            'routePrefix' => $teamVisibility->routePrefix($request->user()),
        ]);
    }

    public function foto(Request $request, Absensi $absensi, TeamVisibilityService $teamVisibility)
    {
        $dataAbsensi = Absensi::query()
            ->whereKey($absensi->getKey())
            ->whereIn('pegawai_id', $teamVisibility->attendanceEmployeeIds($request->user()))
            ->firstOrFail();

        abort_if(
            ! $dataAbsensi->foto_path || ! Storage::disk('public')->exists($dataAbsensi->foto_path),
            404,
            'Foto bukti absensi tidak ditemukan.'
        );

        $disk = Storage::disk('public');

        return response()->file($disk->path($dataAbsensi->foto_path), [
            'Cache-Control' => 'private, max-age=300',
        ]);
    }

    public function verifikasi(Request $request, Absensi $absensi, TeamVisibilityService $teamVisibility)
    {
        $atasan = $request->user()->pegawai;

        abort_if(! $atasan, 403, 'Data pegawai untuk akun Anda tidak ditemukan.');

        $dataAbsensi = Absensi::query()
            ->whereKey($absensi->getKey())
            ->whereIn('pegawai_id', $teamVisibility->attendanceEmployeeIds($request->user()))
            ->firstOrFail();

        $validated = $request->validate([
            'keputusan' => ['required', 'in:terverifikasi,perlu_klarifikasi'],
            'catatan_verifikasi' => ['nullable', 'required_if:keputusan,perlu_klarifikasi', 'string', 'max:1000'],
        ], [
            'catatan_verifikasi.required_if' => 'Catatan wajib diisi jika absensi perlu diklarifikasi.',
            'catatan_verifikasi.max' => 'Catatan verifikasi maksimal 1000 karakter.',
        ]);

        $dataAbsensi->forceFill([
            'status_verifikasi' => $validated['keputusan'],
            'catatan_verifikasi' => $validated['catatan_verifikasi'] ?? null,
            'diverifikasi_oleh' => $atasan->getKey(),
            'diverifikasi_pada' => now(),
        ])->save();

        $pesan = $validated['keputusan'] === 'terverifikasi'
            ? 'Data absensi berhasil diverifikasi.'
            : 'Data absensi ditandai perlu klarifikasi.';

        return to_route($teamVisibility->routePrefix($request->user()).'.absensi.show', $dataAbsensi)->with('success', $pesan);
    }
}
