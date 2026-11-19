<?php

namespace App\Http\Controllers\Atasan;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Pegawai;
use App\Services\PegawaiNotificationService;
use App\Services\TeamVisibilityService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AbsensiAtasanController extends Controller
{
    public function index(Request $request, TeamVisibilityService $teamVisibility)
    {
        $pegawaiIds = $teamVisibility->attendanceEmployeeIds($request->user());

        $jenisPegawai = $request->user()->role === 'kepala_bagian'
            ? 'Atasan'
            : 'Pegawai';

        $validated = $request->validate([
            'bulan' => ['nullable', 'integer', 'between:1,12'],
            'tahun' => ['nullable', 'integer', 'between:2000,2100'],
            'tanggal' => ['nullable', 'integer', 'between:1,31'],
            'status' => [
                'nullable',
                Rule::in([
                    'hadir',
                    'izin',
                    'sakit',
                    'alpa',
                    'tidak_hadir',
                    'cuti',
                    'dinas_luar',
                ]),
            ],
            'search' => ['nullable', 'string', 'max:150'],
        ]);

        $bulan = (int) ($validated['bulan'] ?? now()->month);
        $tahun = (int) ($validated['tahun'] ?? now()->year);
        $status = $validated['status'] ?? null;
        $search = trim((string) ($validated['search'] ?? ''));

        $awalPeriode = Carbon::create($tahun, $bulan, 1)->startOfDay();
        $jumlahHari = $awalPeriode->daysInMonth;

        $tanggalDefault = $bulan === now()->month && $tahun === now()->year
            ? now()->day
            : 1;

        $tanggal = min(
            max((int) ($validated['tanggal'] ?? $tanggalDefault), 1),
            $jumlahHari
        );

        $tanggalDipilih = Carbon::create(
            $tahun,
            $bulan,
            $tanggal
        )->startOfDay();

        /*
         * Data tabel hanya untuk tanggal terpilih.
         * Status juga difilter berdasarkan tanggal tersebut.
         */
        $query = Pegawai::query()
            ->whereIn('id', $pegawaiIds)
            ->with([
                'jabatan.divisi',
                'absensis' => function ($absensiQuery) use ($tanggalDipilih) {
                    $absensiQuery
                        ->whereDate('tanggal', $tanggalDipilih->toDateString())
                        ->orderByDesc('id');
                },
            ])
            ->when($status, function ($pegawaiQuery) use ($tanggalDipilih, $status) {
                $pegawaiQuery->whereHas(
                    'absensis',
                    function ($absensiQuery) use ($tanggalDipilih, $status) {
                        $absensiQuery
                            ->whereDate('tanggal', $tanggalDipilih->toDateString())
                            ->where('status', $status);
                    }
                );
            })
            ->when($search !== '', function ($pegawaiQuery) use ($search) {
                $pegawaiQuery->where(function ($employeeQuery) use ($search) {
                    $employeeQuery
                        ->where('nama', 'like', '%' . $search . '%')
                        ->orWhere('nip', 'like', '%' . $search . '%')
                        ->orWhereHas('jabatan', function ($jabatanQuery) use ($search) {
                            $jabatanQuery
                                ->where('nama_jabatan', 'like', '%' . $search . '%')
                                ->orWhereHas('divisi', function ($divisiQuery) use ($search) {
                                    $divisiQuery->where(
                                        'nama_divisi',
                                        'like',
                                        '%' . $search . '%'
                                    );
                                });
                        });
                });
            })
            ->orderBy('nama');

        $pegawais = $query->paginate(10)->withQueryString();

        /*
         * Ringkasan bulanan tetap berdasarkan seluruh pegawai
         * yang boleh dilihat oleh atasan.
         */
        $pegawaiRingkasan = Pegawai::query()
            ->whereIn('id', $pegawaiIds)
            ->with([
                'jabatan.jamKerjas',
                'absensis' => function ($absensiQuery) use ($bulan, $tahun) {
                    $absensiQuery
                        ->whereMonth('tanggal', $bulan)
                        ->whereYear('tanggal', $tahun);
                },
            ])
            ->get();

        $ringkasan = [
            'total' => 0,
            'hadir' => 0,
            'izin' => 0,
            'sakit' => 0,
            'alpa' => 0,
            'telat' => 0,
            'cuti' => 0,
            'dinas_luar' => 0,
        ];

        $namaHari = [
            'Minggu',
            'Senin',
            'Selasa',
            'Rabu',
            'Kamis',
            'Jumat',
            'Sabtu',
        ];

        // Sediakan seluruh tanggal kalender, termasuk hari tanpa presensi.
        $grafikHarian = [];
        for ($hari = 1; $hari <= $jumlahHari; $hari++) {
            $grafikHarian[$hari] = [
                'day' => $hari,
                'counts' => [
                    'hadir' => 0,
                    'izin' => 0,
                    'sakit' => 0,
                    'alpa' => 0,
                    'telat' => 0,
                ],
            ];
        }

        foreach ($pegawaiRingkasan as $pegawai) {
            $jamKerjas = $pegawai->jabatan?->jamKerjas ?? collect();

            foreach ($pegawai->absensis as $absensi) {
                $ringkasan['total']++;
                $hariAbsensi = Carbon::parse($absensi->tanggal)->day;

                if (in_array($absensi->status, ['alpa', 'tidak_hadir'], true)) {
                    $ringkasan['alpa']++;
                    $grafikHarian[$hariAbsensi]['counts']['alpa']++;
                    continue;
                }

                if ($absensi->status !== 'hadir') {
                    if (array_key_exists($absensi->status, $ringkasan)) {
                        $ringkasan[$absensi->status]++;
                        if (array_key_exists($absensi->status, $grafikHarian[$hariAbsensi]['counts'])) {
                            $grafikHarian[$hariAbsensi]['counts'][$absensi->status]++;
                        }
                    }

                    continue;
                }

                $tanggalAbsensi = Carbon::parse($absensi->tanggal);

                $jadwal = $jamKerjas->firstWhere(
                    'hari',
                    $namaHari[$tanggalAbsensi->dayOfWeek]
                ) ?? $jamKerjas->firstWhere('hari', 'Kerja');

                $terlambat = false;

                if ($jadwal && $absensi->jam_masuk) {
                    $batasMasuk = Carbon::parse($jadwal->jam_masuk)
                        ->addMinutes((int) ($jadwal->toleransi_menit ?? 0));

                    $terlambat = Carbon::parse($absensi->jam_masuk)
                        ->gt($batasMasuk);
                }

                $kategori = $terlambat ? 'telat' : 'hadir';
                $ringkasan[$kategori]++;
                $grafikHarian[$hariAbsensi]['counts'][$kategori]++;
            }
        }

        $akhirPeriode = $awalPeriode->copy()->endOfMonth();
        $hariKerja = 0;

        if ($awalPeriode->lte(now())) {
            if ($akhirPeriode->gt(now())) {
                $akhirPeriode = now()->startOfDay();
            }

            for (
                $hari = $awalPeriode->copy();
                $hari->lte($akhirPeriode);
                $hari->addDay()
            ) {
                if ($hari->isWeekday()) {
                    $hariKerja++;
                }
            }
        }

        $data = [
            'pegawais' => $pegawais,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'tanggal' => $tanggal,
            'tanggalDipilih' => $tanggalDipilih,
            'jumlahHari' => $jumlahHari,
            'status' => $status,
            'search' => $search,
            'ringkasan' => $ringkasan,
            'grafikHarian' => array_values($grafikHarian),
            'hariKerja' => $hariKerja,
            'jumlahPegawai' => $pegawaiRingkasan->count(),
            'jenisPegawai' => $jenisPegawai,
            'routePrefix' => $teamVisibility->routePrefix($request->user()),
        ];

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('atasan.absensi.partials.list', $data)->render(),
                'total' => $pegawais->total(),
                'tanggal' => $tanggal,
                'tanggal_label' => $tanggalDipilih->translatedFormat('l, d F Y'),
            ]);
        }

        return view('atasan.absensi.data-absensi', $data);
    }

    public function show(
        Request $request,
        Absensi $absensi,
        TeamVisibilityService $teamVisibility
    ) {
        $dataAbsensi = Absensi::query()
            ->whereKey($absensi->getKey())
            ->whereIn(
                'pegawai_id',
                $teamVisibility->attendanceEmployeeIds($request->user())
            )
            ->with([
                'pegawai.jabatan.divisi',
                'pegawai.jabatan.jamKerjas',
                'diverifikasiOleh',
            ])
            ->firstOrFail();

        $fotoTersedia = $dataAbsensi->foto_path
            && Storage::disk('public')->exists($dataAbsensi->foto_path);

        return view('atasan.absensi.show', [
            'absensi' => $dataAbsensi,
            'fotoTersedia' => $fotoTersedia,
            'routePrefix' => $teamVisibility->routePrefix($request->user()),
        ]);
    }

    public function foto(
        Request $request,
        Absensi $absensi,
        TeamVisibilityService $teamVisibility
    ) {
        $dataAbsensi = Absensi::query()
            ->whereKey($absensi->getKey())
            ->whereIn(
                'pegawai_id',
                $teamVisibility->attendanceEmployeeIds($request->user())
            )
            ->firstOrFail();

        abort_if(
            ! $dataAbsensi->foto_path
                || ! Storage::disk('public')->exists($dataAbsensi->foto_path),
            404,
            'Foto bukti absensi tidak ditemukan.'
        );

        $disk = Storage::disk('public');

        return response()->file(
            $disk->path($dataAbsensi->foto_path),
            ['Cache-Control' => 'private, max-age=300']
        );
    }



    public function verifikasi(
        Request $request,
        Absensi $absensi,
        TeamVisibilityService $teamVisibility,
        PegawaiNotificationService $notifications
    ) {
        $atasan = $request->user()->pegawai;

        abort_if(
            ! $atasan,
            403,
            'Data pegawai untuk akun Anda tidak ditemukan.'
        );

        $dataAbsensi = Absensi::query()
            ->whereKey($absensi->getKey())
            ->whereIn(
                'pegawai_id',
                $teamVisibility->attendanceEmployeeIds($request->user())
            )
            ->firstOrFail();

        $validated = $request->validate([
            'keputusan' => [
                'required',
                'in:terverifikasi,perlu_klarifikasi',
            ],
            'catatan_verifikasi' => [
                'nullable',
                'required_if:keputusan,perlu_klarifikasi',
                'string',
                'max:1000',
            ],
        ], [
            'catatan_verifikasi.required_if' =>
            'Catatan wajib diisi jika absensi perlu diklarifikasi.',
            'catatan_verifikasi.max' =>
            'Catatan verifikasi maksimal 1000 karakter.',
        ]);

        $statusVerifikasiSebelumnya = $dataAbsensi->status_verifikasi;

        DB::transaction(function () use (
            $dataAbsensi,
            $validated,
            $atasan,
            $statusVerifikasiSebelumnya,
            $notifications
        ) {
            $dataAbsensi->forceFill([
                'status_verifikasi' => $validated['keputusan'],
                'catatan_verifikasi' => $validated['catatan_verifikasi'] ?? null,
                'diverifikasi_oleh' => $atasan->getKey(),
                'diverifikasi_pada' => now(),
            ])->save();

            if ($statusVerifikasiSebelumnya === $validated['keputusan']) {
                return;
            }

            $tanggal = $dataAbsensi->tanggal->translatedFormat('d F Y');
            $terverifikasi = $validated['keputusan'] === 'terverifikasi';
            $pesan = $terverifikasi
                ? "Absensi tanggal {$tanggal} telah diverifikasi atasan."
                : "Absensi tanggal {$tanggal} perlu klarifikasi. Catatan: {$validated['catatan_verifikasi']}";

            $notifications->send(
                $dataAbsensi->pegawai,
                'absensi',
                $terverifikasi ? 'Absensi terverifikasi' : 'Absensi perlu diperbaiki',
                $pesan,
                route('pegawai.absensi.show', $dataAbsensi, false)
            );
        });

        $pesan = $validated['keputusan'] === 'terverifikasi'
            ? 'Data absensi berhasil diverifikasi.'
            : 'Data absensi ditandai perlu klarifikasi.';

        return to_route(
            $teamVisibility->routePrefix($request->user()) . '.absensi.show',
            $dataAbsensi
        )->with('success', $pesan);
    }
}
