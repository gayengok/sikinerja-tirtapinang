<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Jabatan;
use App\Models\MasterJamKerja;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class JamKerjaController extends Controller
{
    private const HARI_VALID = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu', 'Kerja'];
    private const JENIS_JADWAL_VALID = ['normal', 'shift'];
    private const SHIFT_VALID = [1, 2, 3];

    public function index(Request $request)
    {
        $jenisJadwal = $this->jenisJadwal($request);
        $jamKerjas = MasterJamKerja::with('jabatan')
            ->where('jenis_jadwal', $jenisJadwal)
            ->orderBy('jabatan_id')
            ->orderBy('hari')
            ->orderBy('nomor_shift')
            ->paginate(15);

        return view('master.jam-kerja.index', [
            'jamKerjas' => $jamKerjas,
            'jenisJadwal' => $jenisJadwal,
        ]);
    }

    public function create(Request $request)
    {
        $jabatans = Jabatan::orderBy('nama_jabatan')->get();

        return view('master.jam-kerja.create', [
            'jabatans' => $jabatans,
            'hariOptions' => self::HARI_VALID,
            'jenisJadwal' => $this->jenisJadwal($request),
            'shiftOptions' => self::SHIFT_VALID,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validasi($request);

        MasterJamKerja::create($validated);

        return redirect()
            ->route('master.jam-kerja.index', $this->parameterJenisJadwal($validated['jenis_jadwal']))
            ->with('success', 'Jam kerja berhasil ditambahkan.');
    }

    public function show(MasterJamKerja $jamKerja)
    {
        $jamKerja->load('jabatan');

        return view('master.jam-kerja.show', compact('jamKerja'));
    }

    public function edit(MasterJamKerja $jamKerja)
    {
        $jabatans = Jabatan::orderBy('nama_jabatan')->get();

        return view('master.jam-kerja.edit', [
            'jamKerja' => $jamKerja,
            'jabatans' => $jabatans,
            'hariOptions' => self::HARI_VALID,
            'jenisJadwal' => $jamKerja->jenis_jadwal,
            'shiftOptions' => self::SHIFT_VALID,
        ]);
    }

    public function update(Request $request, MasterJamKerja $jamKerja)
    {
        $validated = $this->validasi($request);

        $jamKerja->update($validated);

        return redirect()
            ->route('master.jam-kerja.index', $this->parameterJenisJadwal($validated['jenis_jadwal']))
            ->with('success', 'Jam kerja berhasil diperbarui.');
    }

    public function destroy(MasterJamKerja $jamKerja)
    {
        $jenisJadwal = $jamKerja->jenis_jadwal;
        $jamKerja->delete();

        return redirect()
            ->route('master.jam-kerja.index', $this->parameterJenisJadwal($jenisJadwal))
            ->with('success', 'Jam kerja berhasil dihapus.');
    }

    private function validasi(Request $request): array
    {
        if (! $request->has('jenis_jadwal')) {
            $request->merge(['jenis_jadwal' => 'normal']);
        }

        $jenisJadwal = $request->input('jenis_jadwal');
        $aturanJamPulang = ['required', 'date_format:H:i'];
        $aturanRentang = ['required', 'date_format:H:i'];

        if ($jenisJadwal === 'normal') {
            $aturanJamPulang[] = 'after:jam_masuk';
            $aturanRentang[] = 'after_or_equal:jam_buka_absen_masuk';
        }

        return $request->validate([
            'jenis_jadwal' => ['required', Rule::in(self::JENIS_JADWAL_VALID)],
            'nomor_shift' => [
                'required_if:jenis_jadwal,shift',
                'nullable',
                Rule::in(self::SHIFT_VALID),
            ],
            'jabatan_id' => ['nullable', 'exists:jabatans,id'],
            'hari' => ['required', Rule::in(self::HARI_VALID)],
            'jam_masuk' => ['required', 'date_format:H:i'],
            'jam_pulang' => $aturanJamPulang,
            'toleransi_menit' => ['required', 'integer', 'min:0'],
            'jam_buka_absen_masuk' => ['required', 'date_format:H:i'],
            'batas_absen_masuk' => $aturanRentang,
            'jam_buka_absen_pulang' => ['required', 'date_format:H:i'],
            'jam_tutup_absen_pulang' => $jenisJadwal === 'normal'
                ? ['required', 'date_format:H:i', 'after_or_equal:jam_buka_absen_pulang']
                : ['required', 'date_format:H:i'],
        ], [
            'jenis_jadwal.in' => 'Jenis jadwal tidak valid.',
            'nomor_shift.required_if' => 'Nomor shift wajib dipilih untuk jadwal shift.',
            'nomor_shift.in' => 'Nomor shift harus Shift 1, 2, atau 3.',
            'jabatan_id.exists' => 'Jabatan yang dipilih tidak valid.',
            'hari.required' => 'Hari wajib dipilih.',
            'hari.in' => 'Hari tidak valid.',
            'jam_masuk.required' => 'Jam masuk wajib diisi.',
            'jam_pulang.required' => 'Jam pulang wajib diisi.',
            'jam_pulang.after' => 'Jam pulang harus setelah jam masuk.',
            'toleransi_menit.required' => 'Toleransi menit wajib diisi.',
            'toleransi_menit.integer' => 'Toleransi menit harus berupa angka bulat.',
            'jam_buka_absen_masuk.required' => 'Jam buka absen masuk wajib diisi.',
            'batas_absen_masuk.required' => 'Batas absen masuk wajib diisi.',
            'batas_absen_masuk.after_or_equal' => 'Batas absen masuk harus sama dengan atau setelah jam bukanya.',
            'jam_buka_absen_pulang.required' => 'Jam buka absen pulang wajib diisi.',
            'jam_tutup_absen_pulang.required' => 'Jam tutup absen pulang wajib diisi.',
            'jam_tutup_absen_pulang.after_or_equal' => 'Jam tutup absen pulang harus sama dengan atau setelah jam bukanya.',
        ]);
    }

    private function jenisJadwal(Request $request): string
    {
        $jenisJadwal = $request->query('jenis_jadwal', 'normal');

        return in_array($jenisJadwal, self::JENIS_JADWAL_VALID, true)
            ? $jenisJadwal
            : 'normal';
    }

    private function parameterJenisJadwal(string $jenisJadwal): array
    {
        return $jenisJadwal === 'shift'
            ? ['jenis_jadwal' => 'shift']
            : [];
    }
}
