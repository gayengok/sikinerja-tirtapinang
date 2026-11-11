<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\MasterJenisPotongan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class JenisPotonganController extends Controller
{
    public function index()
    {
        $jenisPotongans = MasterJenisPotongan::orderBy('tipe_pelanggaran')
            ->orderBy('menit_min')
            ->paginate(15);

        return view('master.jenis-potongan.index', compact('jenisPotongans'));
    }

    public function create()
    {
        return view('master.jenis-potongan.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validasi($request);

        MasterJenisPotongan::create($validated);

        return redirect()
            ->route('master.jenis-potongan.index')
            ->with('success', 'Jenis potongan berhasil ditambahkan.');
    }

    public function show(MasterJenisPotongan $jenisPotongan)
    {
        return view('master.jenis-potongan.show', compact('jenisPotongan'));
    }

    public function edit(MasterJenisPotongan $jenisPotongan)
    {
        return view('master.jenis-potongan.edit', compact('jenisPotongan'));
    }



    public function update(Request $request, MasterJenisPotongan $jenisPotongan)
    {
        $validated = $this->validasi($request);

        $jenisPotongan->update($validated);

        return redirect()
            ->route('master.jenis-potongan.index')
            ->with('success', 'Jenis potongan berhasil diperbarui.');
    }

    public function destroy(MasterJenisPotongan $jenisPotongan)
    {
        $jumlahPresensiPotongan = $jenisPotongan->presensiPotongans()->count();

        if ($jumlahPresensiPotongan > 0) {
            return redirect()
                ->route('master.jenis-potongan.index')
                ->with('error', 'Jenis potongan tidak dapat dihapus karena sudah digunakan pada data presensi potongan.');
        }

        $jenisPotongan->delete();

        return redirect()
            ->route('master.jenis-potongan.index')
            ->with('success', 'Jenis potongan berhasil dihapus.');
    }

    private function validasi(Request $request): array
    {
        return $request->validate([
            'nama_potongan' => ['required', 'string', 'max:255'],
            'tipe_pelanggaran' => ['required', Rule::in(['terlambat', 'tidak_absen', 'alpa', 'pulang_awal'])],
            'menit_min' => ['nullable', 'integer', 'min:0'],
            'menit_max' => ['nullable', 'integer', 'min:0', 'gte:menit_min'],
            'keterangan' => ['nullable', 'string', 'max:500'],
            'persen_potongan' => ['required', 'numeric', 'min:0', 'max:100'],
        ], [
            'nama_potongan.required' => 'Nama potongan wajib diisi.',
            'tipe_pelanggaran.required' => 'Tipe pelanggaran wajib dipilih.',
            'tipe_pelanggaran.in' => 'Tipe pelanggaran tidak valid.',
            'menit_max.gte' => 'Menit maksimal harus lebih besar atau sama dengan menit minimal.',
            'persen_potongan.required' => 'Persentase potongan wajib diisi.',
            'persen_potongan.numeric' => 'Persentase potongan harus berupa angka.',
            'persen_potongan.max' => 'Persentase potongan maksimal 100.',
        ]);
    }
}
