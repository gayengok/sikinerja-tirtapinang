<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Support\HariLibur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class AbsensiController extends Controller
{
    private const MAX_GPS_ACCURACY = 25;

    public function index(Request $request)
    {
        $pegawai = $this->pegawaiLogin($request);
        $query = $pegawai->absensis()->orderByDesc('tanggal');
        $bulan = $request->query('bulan', now()->month);
        $tahun = $request->query('tahun', now()->year);

        if ($request->filled('bulan') || $request->filled('tahun')) {
            $query->whereMonth('tanggal', $bulan)
                ->whereYear('tanggal', $tahun);
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

        $tanggal = now()->toDateString();

        $cutiHariIni = $pegawai->absensis()
            ->whereDate('tanggal', $tanggal)
            ->where('status', 'cuti')
            ->exists();

        $absensiHariIni = $pegawai->absensis()
            ->whereDate('tanggal', $tanggal)
            ->first();

        $hariLiburHariIni = HariLibur::detail($tanggal);
        $attendanceCalendarMonth = now()->copy()->startOfMonth();

        $attendanceCalendar = $request->user()->role === 'atasan'
            ? $pegawai->absensis()
            ->whereBetween('tanggal', [
                $attendanceCalendarMonth->toDateString(),
                $attendanceCalendarMonth->copy()->endOfMonth()->toDateString(),
            ])
            ->get(['tanggal', 'jam_masuk', 'jam_pulang'])
            ->keyBy(fn(Absensi $absensi) => (int) $absensi->tanggal->day)
            : collect();

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
            'attendanceCalendarMonth' => $attendanceCalendarMonth,
            'attendanceCalendar' => $attendanceCalendar,
            'attendanceRoute' => $this->attendanceRoute($request),
            'dashboardRoute' => $request->user()->role === 'atasan'
                ? 'atasan.dashboard'
                : 'pegawai.dashboard',
            'geofence' => $this->loadGeofence(),
        ]);
    }

    public function store(Request $request)
    {
        $pegawai = $this->pegawaiLogin($request);
        $hariLiburHariIni = HariLibur::detail(now()->toDateString());

        if ($hariLiburHariIni) {
            return to_route($this->attendanceRoute($request) . '.index')
                ->with('error', "Presensi tidak tersedia pada {$hariLiburHariIni['label']}.");
        }

        $validated = $request->validate([
            'status' => ['required', 'in:hadir,izin,alpa'],
            'jenis_presensi' => ['required', 'in:masuk,pulang'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'accuracy' => [
                'required',
                'numeric',
                'min:0',
                'max:' . self::MAX_GPS_ACCURACY,
            ],
            'foto' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ], [
            'accuracy.max' => 'Akurasi lokasi melebihi 25 meter. Aktifkan Layanan lokasi dan Wi-Fi; laptop tanpa GPS fisik mungkin tetap kurang akurat.',
            'latitude.required' => 'Lokasi GPS belum tersedia.',
            'longitude.required' => 'Lokasi GPS belum tersedia.',
            'foto.required' => 'Foto presensi wajib diambil.',
        ]);

        $latitude = (float) $validated['latitude'];
        $longitude = (float) $validated['longitude'];

        if (!$this->isInsideGeofence($latitude, $longitude)) {
            return back()->with(
                'error',
                'Presensi ditolak. Lokasi berada di luar area kantor. Jika menggunakan laptop, periksa Layanan lokasi Windows dan Wi-Fi; jangan kirim presensi sampai titik lokasi valid.'
            );
        }

        $distance = $this->distanceInMeters(
            $latitude,
            $longitude,
            -2.146435,
            106.101327
        );

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
                return [
                    'error' => 'Absensi tidak dapat diubah karena Anda sedang cuti hari ini.',
                ];
            }

            if (filled($absensiHariIni?->{$kolomWaktu})) {
                return [
                    'warning' => "Presensi {$jenisPresensi} hari ini sudah tercatat.",
                ];
            }

            if ($jenisPresensi === 'pulang' && !filled($absensiHariIni?->jam_masuk)) {
                return [
                    'error' => 'Anda harus melakukan presensi masuk sebelum presensi pulang.',
                ];
            }

            $fotoPath = $request->file('foto')->store('absensi', 'public');

            $dataAbsensi = [
                'jenis_presensi' => $jenisPresensi,
                'jam_masuk' => $jenisPresensi === 'masuk'
                    ? now()->format('H:i:s')
                    : $absensiHariIni?->jam_masuk,
                'jam_pulang' => $jenisPresensi === 'pulang'
                    ? now()->format('H:i:s')
                    : $absensiHariIni?->jam_pulang,
                'status' => $validated['status'],
                'sumber' => 'manual',
                'keterangan' => $validated['keterangan'] ?? null,
                'foto_path' => $fotoPath,
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'jarak_lokasi' => round($distance, 2),
                'di_luar_kantor' => false,
                'akurasi_gps' => round((float) $validated['accuracy'], 2),
            ];

            if ($absensiHariIni) {
                $absensiHariIni->update($dataAbsensi);
            } else {
                $pegawai->absensis()->create(
                    array_merge(['tanggal' => $tanggal], $dataAbsensi)
                );
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
            return to_route($this->attendanceRoute($request) . '.index')
                ->with('attendance_warning', $result['warning']);
        }

        if (
            $result['old_foto_path']
            && $result['old_foto_path'] !== $result['foto_path']
        ) {
            Storage::disk('public')->delete($result['old_foto_path']);
        }

        return to_route($this->attendanceRoute($request) . '.index')
            ->with('success', 'Presensi berhasil disimpan.');
    }

    public function show(Request $request, Absensi $absensi)
    {
        $pegawai = $this->pegawaiLogin($request);
        $data = $pegawai->absensis()->find($absensi->id);

        abort_if(!$data, 404, 'Data absensi tidak ditemukan.');

        return view('pegawai.absensi.show', [
            'absensi' => $data,
            'attendanceRoute' => $this->attendanceRoute($request),
            'dashboardRoute' => $request->user()->role === 'atasan'
                ? 'atasan.dashboard'
                : 'pegawai.dashboard',
        ]);
    }

    private function attendanceRoute(Request $request): string
    {
        return $request->user()->role === 'atasan'
            ? 'atasan.absensi-saya'
            : 'pegawai.absensi';
    }

    private function pegawaiLogin(Request $request)
    {
        $pegawai = $request->user()->pegawai;

        abort_if(
            !$pegawai,
            403,
            'Data pegawai untuk akun Anda tidak ditemukan.'
        );

        return $pegawai;
    }

    private function loadGeofence(): array
    {
        $path = resource_path('geojson/GeofencingPDAM.geojson');

        if (!is_file($path)) {
            throw new RuntimeException('File GeoJSON tidak ditemukan: ' . $path);
        }

        $geojson = json_decode(
            file_get_contents($path),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        if (($geojson['type'] ?? null) !== 'FeatureCollection') {
            throw new RuntimeException('GeoJSON harus berupa FeatureCollection.');
        }

        $features = $geojson['features'] ?? [];

        if (count($features) !== 1) {
            throw new RuntimeException('GeoJSON harus memiliki tepat satu feature.');
        }

        $geometry = $features[0]['geometry'] ?? [];

        if (($geometry['type'] ?? null) !== 'Polygon') {
            throw new RuntimeException('Geometry GeoJSON harus bertipe Polygon.');
        }

        $rings = $geometry['coordinates'] ?? [];

        if (empty($rings) || count($rings[0]) < 4) {
            throw new RuntimeException('Koordinat polygon GeoJSON tidak valid.');
        }

        return $geojson;
    }

    private function isInsideGeofence(float $latitude, float $longitude): bool
    {
        $rings = $this->loadGeofence()['features'][0]['geometry']['coordinates'];

        if (!$this->pointInRing($longitude, $latitude, $rings[0])) {
            return false;
        }

        foreach (array_slice($rings, 1) as $hole) {
            if ($this->pointInRing($longitude, $latitude, $hole)) {
                return false;
            }
        }

        return true;
    }

    private function pointInRing(float $x, float $y, array $ring): bool
    {
        $inside = false;
        $count = count($ring);

        if ($count < 4) {
            return false;
        }

        for ($i = 0, $j = $count - 1; $i < $count; $j = $i++) {
            [$xi, $yi] = $ring[$i];
            [$xj, $yj] = $ring[$j];

            $cross = ($x - $xi) * ($yj - $yi)
                - ($y - $yi) * ($xj - $xi);

            if (
                abs($cross) < 1e-12
                && $x >= min($xi, $xj)
                && $x <= max($xi, $xj)
                && $y >= min($yi, $yj)
                && $y <= max($yi, $yj)
            ) {
                return true;
            }

            if (($yi > $y) !== ($yj > $y)) {
                $intersectionX = $xi
                    + ($y - $yi) * ($xj - $xi) / ($yj - $yi);

                if ($x < $intersectionX) {
                    $inside = !$inside;
                }
            }
        }

        return $inside;
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

        return $earthRadius * 2 * asin(min(1, sqrt($a)));
    }
}