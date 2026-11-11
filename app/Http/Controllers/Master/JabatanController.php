<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Divisi;
use App\Models\Jabatan;
use Illuminate\Http\Request;

class JabatanController extends Controller
{
    public function index()
    {
        $jabatans = Jabatan::with('divisi')
            ->withCount('pegawais')
            ->orderBy('nama_jabatan')
            ->paginate(10);

        return view('master.jabatan.index', compact('jabatans'));
    }

    public function create()
    {
        $divisis = Divisi::orderBy('nama_divisi')->get();

        return view('master.jabatan.create', compact('divisis'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'divisi_id' => ['required', 'exists:divisis,id'],
            'nama_jabatan' => ['required', 'string', 'max:255'],
            'grade' => ['required', 'string', 'max:50'],
            'base_tukin' => ['required', 'numeric', 'min:0'],
        ], [
            'divisi_id.required' => 'Divisi wajib dipilih.',
            'divisi_id.exists' => 'Divisi yang dipilih tidak valid.',
            'nama_jabatan.required' => 'Nama jabatan wajib diisi.',
            'grade.required' => 'Grade wajib diisi.',
            'base_tukin.required' => 'Base tukin wajib diisi.',
            'base_tukin.numeric' => 'Base tukin harus berupa angka.',
        ]);

        Jabatan::create($validated);

        return redirect()
            ->route('master.jabatan.index')
            ->with('success', 'Jabatan berhasil ditambahkan.');
    }

    public function show(Jabatan $jabatan)
    {
        $jabatan->load([
            'divisi',
            'pegawais.penilaianKpis.periode',
            'masterKpis',
            'masterKompetensis',
        ]);

        $performanceData = $jabatan->pegawais
            ->flatMap(fn ($pegawai) => $pegawai->penilaianKpis)
            ->filter(fn ($penilaian) => $penilaian->persen_capaian !== null && $penilaian->periode)
            ->groupBy('periode_id')
            ->map(function ($penilaians) {
                $periode = $penilaians->first()->periode;

                return [
                    'label' => $periode->tanggal_mulai
                        ? $periode->tanggal_mulai->translatedFormat('M')
                        : $periode->nama_periode,
                    'average' => round((float) $penilaians->avg('persen_capaian'), 2),
                    'date' => $periode->tanggal_mulai?->timestamp ?? 0,
                ];
            })
            ->sortBy('date')
            ->values()
            ->take(-4)
            ->values();

        $performanceAverage = $performanceData->isNotEmpty()
            ? round((float) $performanceData->avg('average'), 2)
            : null;
        $currentPerformance = $performanceData->last()['average'] ?? null;

        return view('master.jabatan.show', compact(
            'jabatan',
            'performanceData',
            'performanceAverage',
            'currentPerformance'
        ));
    }

    public function edit(Jabatan $jabatan)
    {
        $divisis = Divisi::orderBy('nama_divisi')->get();

        return view('master.jabatan.edit', compact('jabatan', 'divisis'));
    }

    public function update(Request $request, Jabatan $jabatan)
    {
        $validated = $request->validate([
            'divisi_id' => ['required', 'exists:divisis,id'],
            'nama_jabatan' => ['required', 'string', 'max:255'],
            'grade' => ['required', 'string', 'max:50'],
            'base_tukin' => ['required', 'numeric', 'min:0'],
        ], [
            'divisi_id.required' => 'Divisi wajib dipilih.',
            'divisi_id.exists' => 'Divisi yang dipilih tidak valid.',
            'nama_jabatan.required' => 'Nama jabatan wajib diisi.',
            'grade.required' => 'Grade wajib diisi.',
            'base_tukin.required' => 'Base tukin wajib diisi.',
            'base_tukin.numeric' => 'Base tukin harus berupa angka.',
        ]);

        $jabatan->update($validated);

        return redirect()
            ->route('master.jabatan.index')
            ->with('success', 'Jabatan berhasil diperbarui.');
    }

    public function destroy(Jabatan $jabatan)
    {
        $jumlahPegawai = $jabatan->pegawais()->count();

        if ($jumlahPegawai > 0) {
            return redirect()
                ->route('master.jabatan.index')
                ->with('error', "Jabatan tidak dapat dihapus karena masih digunakan oleh {$jumlahPegawai} pegawai.");
        }

        $jabatan->delete();

        return redirect()
            ->route('master.jabatan.index')
            ->with('success', 'Jabatan berhasil dihapus.');
    }
}
