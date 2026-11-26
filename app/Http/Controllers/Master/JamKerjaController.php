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

    public function index()
    {
        $jamKerjas = MasterJamKerja::with('jabatan')
            ->orderBy('jabatan_id')
            ->orderBy('hari')
            ->paginate(15);

        return view('master.jam-kerja.index', compact('jamKerjas'));
    }

    public function create()
    {
        $jabatans = Jabatan::orderBy('nama_jabatan')->get();

        return view('master.jam-kerja.create', ['jabatans' => $jabatans, 'hariOptions' => self::HARI_VALID]);
    }

    public function store(Request $request)
    {
        $validated = $this->validasi($request);

        MasterJamKerja::create($validated);

        return redirect()
            ->route('master.jam-kerja.index')
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
        ]);
    }

    public function update(Request $request, MasterJamKerja $jamKerja)
    {
        $validated = $this->validasi($request);

        $jamKerja->update($validated);

        return redirect()
            ->route('master.jam-kerja.index')
            ->with('success', 'Jam kerja berhasil diperbarui.');
    }

    public function destroy(MasterJamKerja $jamKerja)
    {
        $jamKerja->delete();

        return redirect()
            ->route('master.jam-kerja.index')
            ->with('success', 'Jam kerja berhasil dihapus.');
    }

    private function validasi(Request $request): array
    {
        return $request->validate([
            'jabatan_id' => ['nullable', 'exists:jabatans,id'],
            'hari' => ['required', Rule::in(self::HARI_VALID)],
            'jam_masuk' => ['required', 'date_format:H:i'],
            'jam_pulang' => ['required', 'date_format:H:i', 'after:jam_masuk'],
            'toleransi_menit' => ['required', 'integer', 'min:0'],
            'jam_buka_absen_masuk' => ['required', 'date_format:H:i'],
            'batas_absen_masuk' => ['required', 'date_format:H:i', 'after_or_equal:jam_buka_absen_masuk'],
            'jam_buka_absen_pulang' => ['required', 'date_format:H:i'],
            'jam_tutup_absen_pulang' => ['required', 'date_format:H:i', 'after_or_equal:jam_buka_absen_pulang'],
        ], [
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
}
