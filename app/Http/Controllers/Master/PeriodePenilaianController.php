<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\PeriodePenilaian;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PeriodePenilaianController extends Controller
{
    public function index()
    {
        PeriodePenilaian::finalizeExpired();

        $periodePenilaians = PeriodePenilaian::orderByDesc('tanggal_mulai')->paginate(10);
        $statusTotals = PeriodePenilaian::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');
        $statusCounts = [
            'semua' => $statusTotals->sum(),
            'berjalan' => $statusTotals->get('berjalan', 0),
            'selesai' => $statusTotals->get('final', 0) + $statusTotals->get('selesai', 0),
            'draft' => $statusTotals->get('draft', 0),
        ];

        return view('master.periode-penilaian.index', compact('periodePenilaians', 'statusCounts'));
    }

    public function create()
    {
        return view('master.periode-penilaian.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_periode' => ['required', 'string', 'max:255', 'unique:periode_penilaians,nama_periode'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'status' => ['required', Rule::in(['draft', 'berjalan', 'final'])],
        ], [
            'nama_periode.required' => 'Nama periode wajib diisi.',
            'nama_periode.unique' => 'Nama periode sudah digunakan.',
            'tanggal_mulai.required' => 'Tanggal mulai wajib diisi.',
            'tanggal_selesai.required' => 'Tanggal selesai wajib diisi.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai.',
            'status.required' => 'Status wajib dipilih.',
        ]);

        PeriodePenilaian::create($validated);

        return redirect()
            ->route('master.periode-penilaian.index')
            ->with('success', 'Periode penilaian berhasil ditambahkan.');
    }

    public function show(PeriodePenilaian $periodePenilaian)
    {
        $periodePenilaian->load(['penilaianKpis', 'penilaianKompetensis', 'hasilPenilaians']);

        return view('master.periode-penilaian.show', compact('periodePenilaian'));
    }

    public function edit(PeriodePenilaian $periodePenilaian)
    {
        return view('master.periode-penilaian.edit', compact('periodePenilaian'));
    }

    public function update(Request $request, PeriodePenilaian $periodePenilaian)
    {
        $validated = $request->validate([
            'nama_periode' => [
                'required',
                'string',
                'max:255',
                Rule::unique('periode_penilaians', 'nama_periode')->ignore($periodePenilaian->id),
            ],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'status' => ['required', Rule::in(['draft', 'berjalan', 'final'])],
        ], [
            'nama_periode.required' => 'Nama periode wajib diisi.',
            'nama_periode.unique' => 'Nama periode sudah digunakan.',
            'tanggal_mulai.required' => 'Tanggal mulai wajib diisi.',
            'tanggal_selesai.required' => 'Tanggal selesai wajib diisi.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai.',
            'status.required' => 'Status wajib dipilih.',
        ]);

        $periodePenilaian->update($validated);

        return redirect()
            ->route('master.periode-penilaian.index')
            ->with('success', 'Periode penilaian berhasil diperbarui.');
    }

    public function destroy(PeriodePenilaian $periodePenilaian)
    {
        $jumlahHasil = $periodePenilaian->hasilPenilaians()->count();
        $jumlahPenilaianKpi = $periodePenilaian->penilaianKpis()->count();
        $jumlahPenilaianKompetensi = $periodePenilaian->penilaianKompetensis()->count();

        if ($jumlahHasil > 0 || $jumlahPenilaianKpi > 0 || $jumlahPenilaianKompetensi > 0) {
            return redirect()
                ->route('master.periode-penilaian.index')
                ->with('error', 'Periode penilaian tidak dapat dihapus karena sudah memiliki data penilaian terkait.');
        }

        $periodePenilaian->delete();

        return redirect()
            ->route('master.periode-penilaian.index')
            ->with('success', 'Periode penilaian berhasil dihapus.');
    }
}
