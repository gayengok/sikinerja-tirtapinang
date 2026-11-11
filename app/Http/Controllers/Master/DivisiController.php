<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Divisi;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DivisiController extends Controller
{
    public function index()
    {
        $divisis = Divisi::withCount(['jabatans', 'templateLaporans'])
            ->orderBy('nama_divisi')
            ->paginate(10);

        return view('master.divisi.index', compact('divisis'));
    }

    public function create()
    {
        return view('master.divisi.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_divisi' => ['required', 'string', 'max:255', 'unique:divisis,nama_divisi'],
        ], [
            'nama_divisi.required' => 'Nama divisi wajib diisi.',
            'nama_divisi.unique' => 'Nama divisi sudah terdaftar.',
        ]);

        Divisi::create($validated);

        return redirect()
            ->route('master.divisi.index')
            ->with('success', 'Divisi berhasil ditambahkan.');
    }

    public function show(Divisi $divisi)
    {
        $divisi->load(['jabatans', 'templateLaporans']);

        return view('master.divisi.show', compact('divisi'));
    }

    public function edit(Divisi $divisi)
    {
        return view('master.divisi.edit', compact('divisi'));
    }

    public function update(Request $request, Divisi $divisi)
    {
        $validated = $request->validate([
            'nama_divisi' => [
                'required',
                'string',
                'max:255',
                Rule::unique('divisis', 'nama_divisi')->ignore($divisi->id),
            ],
        ], [
            'nama_divisi.required' => 'Nama divisi wajib diisi.',
            'nama_divisi.unique' => 'Nama divisi sudah terdaftar.',
        ]);

        $divisi->update($validated);

        return redirect()
            ->route('master.divisi.index')
            ->with('success', 'Divisi berhasil diperbarui.');
    }

    public function destroy(Divisi $divisi)
    {
        $jumlahJabatan = $divisi->jabatans()->count();
        $jumlahTemplate = $divisi->templateLaporans()->count();

        if ($jumlahJabatan > 0 || $jumlahTemplate > 0) {
            return redirect()
                ->route('master.divisi.index')
                ->with('error', "Divisi tidak dapat dihapus karena masih memiliki {$jumlahJabatan} jabatan dan {$jumlahTemplate} template laporan yang terhubung.");
        }

        $divisi->delete();

        return redirect()
            ->route('master.divisi.index')
            ->with('success', 'Divisi berhasil dihapus.');
    }
}
