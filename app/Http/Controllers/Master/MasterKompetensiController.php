<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Jabatan;
use App\Models\MasterKompetensi;
use Illuminate\Http\Request;

class MasterKompetensiController extends Controller
{
    public function index()
    {
        $masterKompetensis = MasterKompetensi::with('jabatan')
            ->orderBy('urutan')
            ->paginate(15);

        return view('master.master-kompetensi.index', compact('masterKompetensis'));
    }

    public function create()
    {
        $jabatans = Jabatan::orderBy('nama_jabatan')->get();

        return view('master.master-kompetensi.create', compact('jabatans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'jabatan_id' => ['nullable', 'exists:jabatans,id'],
            'nama_kompetensi' => ['required', 'string', 'max:255'],
            'definisi_perilaku' => ['nullable', 'string'],
            'urutan' => ['nullable', 'integer', 'min:0'],
        ], [
            'jabatan_id.exists' => 'Jabatan yang dipilih tidak valid.',
            'nama_kompetensi.required' => 'Nama kompetensi wajib diisi.',
        ]);

        MasterKompetensi::create($validated);

        return redirect()
            ->route('master.master-kompetensi.index')
            ->with('success', 'Kompetensi berhasil ditambahkan.');
    }

    public function show(MasterKompetensi $masterKompetensi)
    {
        $masterKompetensi->load(['jabatan', 'penilaianKompetensis']);

        return view('master.master-kompetensi.show', compact('masterKompetensi'));
    }

    public function edit(MasterKompetensi $masterKompetensi)
    {
        $jabatans = Jabatan::orderBy('nama_jabatan')->get();

        return view('master.master-kompetensi.edit', compact('masterKompetensi', 'jabatans'));
    }

    public function update(Request $request, MasterKompetensi $masterKompetensi)
    {
        $validated = $request->validate([
            'jabatan_id' => ['nullable', 'exists:jabatans,id'],
            'nama_kompetensi' => ['required', 'string', 'max:255'],
            'definisi_perilaku' => ['nullable', 'string'],
            'urutan' => ['nullable', 'integer', 'min:0'],
        ], [
            'jabatan_id.exists' => 'Jabatan yang dipilih tidak valid.',
            'nama_kompetensi.required' => 'Nama kompetensi wajib diisi.',
        ]);

        $masterKompetensi->update($validated);

        return redirect()
            ->route('master.master-kompetensi.index')
            ->with('success', 'Kompetensi berhasil diperbarui.');
    }

    public function destroy(MasterKompetensi $masterKompetensi)
    {
        $jumlahPenilaian = $masterKompetensi->penilaianKompetensis()->count();

        if ($jumlahPenilaian > 0) {
            return redirect()
                ->route('master.master-kompetensi.index')
                ->with('error', 'Kompetensi tidak dapat dihapus karena sudah digunakan pada data penilaian.');
        }

        $masterKompetensi->delete();

        return redirect()
            ->route('master.master-kompetensi.index')
            ->with('success', 'Kompetensi berhasil dihapus.');
    }
}
