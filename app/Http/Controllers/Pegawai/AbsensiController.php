<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Support\HariLibur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AbsensiController extends Controller
{
    public function index(Request $request)
    {
        $pegawai = $this->pegawaiLogin($request);

        $query = $pegawai->absensis()->orderByDesc('tanggal');

        $bulan = $request->query('bulan', now()->month);
        $tahun = $request->query('tahun', now()->year);

        if ($request->filled('bulan') || $request->filled('tahun')) {
            $query->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        $absensis = $query->paginate(15)->withQueryString();

        $ringkasanQuery = $pegawai->absensis()
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun);

        $ringkasan = [
            'hadir' => (clone $ringkasanQuery)->where('status', 'hadir')->count(),
            'izin' => (clone $ringkasanQuery)->where('status', 'izin')->count(),
            'sakit' => (clone $ringkasanQuery)->where('status', 'sakit')->count(),
            'alpa' => (clone $ringkasanQuery)->where('status', 'alpa')->count(),
            'cuti' => (clone $ringkasanQuery)->where('status', 'cuti')->count(),
            'dinas_luar' => (clone $ringkasanQuery)->where('status', 'dinas_luar')->count(),
        ];

        $cutiHariIni = $pegawai->absensis()
            ->whereDate('tanggal', now()->toDateString())
            ->where('status', 'cuti')
            ->exists();
        $absensiHariIni = $pegawai->absensis()
            ->whereDate('tanggal', now()->toDateString())
            ->first();
        $hariLiburHariIni = HariLibur::detail(now()->toDateString());

        return view('pegawai.absensi.index', [
            'pegawai' => $pegawai,
            'absensis' => $absensis,
            'ringkasan' => $ringkasan,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'cutiHariIni' => $cutiHariIni,
            'hariLiburHariIni' => $hariLiburHariIni,
            'absensiMasukHariIni' => filled($absensiHariIni?->jam_masuk),
            'absensiPulangHariIni' => filled($absensiHariIni?->jam_pulang),
            'attendanceRoute' => $this->attendanceRoute($request),
            'dashboardRoute' => $request->user()->role === 'atasan' ? 'atasan.dashboard' : 'pegawai.dashboard',
        ]);
    }

    public function store(Request $request)
    {
        $pegawai = $this->pegawaiLogin($request);
        $hariLiburHariIni = HariLibur::detail(now()->toDateString());

        if ($hariLiburHariIni) {
            return to_route($this->attendanceRoute($request).'.index')
                ->with('error', "Presensi tidak tersedia pada {$hariLiburHariIni['label']}.");
        }

        $validated = $request->validate([
            'status' => ['required', 'in:hadir,izin,alpa'],
            'jenis_presensi' => ['required', 'in:masuk,pulang'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            // GPS accuracy is measured in meters and can legitimately exceed
            // 100 m indoors. The geofence below caps its tolerance at 50 m.
            'accuracy' => ['required', 'numeric', 'min:0'],
            'foto' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        // PDAM Kota Pangkal Pinang, Jl. Bandes No. 200, Parit Lalang.
        $officeLatitude = -2.146435;
        $officeLongitude = 106.101327;
        $maximumDistance = 50;
        $gpsUncertainty = min((float) $validated['accuracy'], 50);
        $distance = $this->distanceInMeters(
            (float) $validated['latitude'],
            (float) $validated['longitude'],
            $officeLatitude,
            $officeLongitude
        );

        // GPS di dalam gedung dapat bergeser. Kurangi jarak dengan
        // ketidakpastian perangkat, tetapi batasi toleransinya agar geofence
        // tidak melebar tanpa batas.
        $distanceAfterUncertainty = max(0, $distance - $gpsUncertainty);
        $isOutsideOffice = $distanceAfterUncertainty > $maximumDistance;

        $tanggal = now()->toDateString();
        $jenisPresensi = $validated['jenis_presensi'];
        $kolomWaktu = $jenisPresensi === 'masuk' ? 'jam_masuk' : 'jam_pulang';
        $result = DB::transaction(function () use (
            $pegawai,
            $tanggal,
            $jenisPresensi,
            $kolomWaktu,
            $validated,
            $distance,
            $isOutsideOffice,
            $request
        ): array {
            $pegawai->newQuery()
                ->whereKey($pegawai->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $absensiHariIni = $pegawai->absensis()
                ->whereDate('tanggal', $tanggal)
                ->lockForUpdate()
                ->first();

            if ($absensiHariIni?->status === 'cuti') {
                return ['error' => 'Absensi tidak dapat diubah karena Anda sedang cuti hari ini.'];
            }

            if (filled($absensiHariIni?->{$kolomWaktu})) {
                $labelPresensi = $jenisPresensi === 'masuk' ? 'masuk' : 'pulang';

                return [
                    'warning' => "Presensi {$labelPresensi} hari ini sudah tercatat. Setiap jenis presensi hanya dapat dilakukan satu kali dalam sehari.",
                ];
            }

            $fotoPath = $request->file('foto')->store('absensi', 'public');
            $dataAbsensi = [
                'jenis_presensi' => $jenisPresensi,
                'jam_masuk' => $jenisPresensi === 'masuk' ? now()->format('H:i:s') : $absensiHariIni?->jam_masuk,
                'jam_pulang' => $jenisPresensi === 'pulang' ? now()->format('H:i:s') : $absensiHariIni?->jam_pulang,
                'status' => $validated['status'],
                'sumber' => 'manual',
                'keterangan' => $validated['keterangan'] ?? null,
                'foto_path' => $fotoPath,
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'jarak_lokasi' => round($distance, 2),
                'di_luar_kantor' => $isOutsideOffice,
                'akurasi_gps' => round((float) $validated['accuracy'], 2),
            ];

            if ($absensiHariIni) {
                $absensiHariIni->update($dataAbsensi);
            } else {
                $pegawai->absensis()->create(array_merge(
                    ['tanggal' => $tanggal],
                    $dataAbsensi
                ));
            }

            return [
                'foto_path' => $fotoPath,
                'old_foto_path' => $absensiHariIni?->foto_path,
            ];
        });

        if (isset($result['error'])) {
            return back()->with('error', $result['error']);
        }

        if (isset($result['warning'])) {
            return to_route($this->attendanceRoute($request).'.index')
                ->with('attendance_warning', $result['warning']);
        }

        if ($result['old_foto_path'] && $result['old_foto_path'] !== $result['foto_path']) {
            Storage::disk('public')->delete($result['old_foto_path']);
        }

        return to_route($this->attendanceRoute($request).'.index')
            ->with('success', $isOutsideOffice
                ? 'Data absensi berhasil disimpan. Lokasi terdeteksi di luar kantor dan telah dicatat untuk verifikasi.'
                : 'Data absensi hari ini berhasil disimpan.');
    }

    public function show(Request $request, Absensi $absensi)
    {
        $pegawai = $this->pegawaiLogin($request);

        $data = $pegawai->absensis()->find($absensi->id);

        abort_if(!$data, 404, 'Data absensi tidak ditemukan.');

        return view('pegawai.absensi.show', [
            'absensi' => $data,
            'attendanceRoute' => $this->attendanceRoute($request),
            'dashboardRoute' => $request->user()->role === 'atasan' ? 'atasan.dashboard' : 'pegawai.dashboard',
        ]);
    }

    private function attendanceRoute(Request $request): string
    {
        return $request->user()->role === 'atasan' ? 'atasan.absensi-saya' : 'pegawai.absensi';
    }

    private function pegawaiLogin(Request $request)
    {
        $pegawai = $request->user()->pegawai;

        abort_if(!$pegawai, 403, 'Data pegawai untuk akun Anda tidak ditemukan.');

        return $pegawai;
    }

    private function distanceInMeters(
        float $latitude,
        float $longitude,
        float $officeLatitude,
        float $officeLongitude
    ): float {
        $earthRadius = 6371000;
        $latitudeDifference = deg2rad($latitude - $officeLatitude);
        $longitudeDifference = deg2rad($longitude - $officeLongitude);
        $a = sin($latitudeDifference / 2) ** 2
            + cos(deg2rad($officeLatitude))
            * cos(deg2rad($latitude))
            * sin($longitudeDifference / 2) ** 2;

        return $earthRadius * 2 * asin(sqrt($a));
    }
}
