<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Jabatan;
use App\Models\MasterKpi;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MasterKpiController extends Controller
{
    public function index()
    {
        $masterKpis = MasterKpi::with('jabatan.divisi')
            ->orderBy('jabatan_id')
            ->orderBy('urutan')
            ->paginate(15);

        return view('master.master-kpi.index', compact('masterKpis'));
    }

    public function create(Request $request)
    {
        $jabatans = Jabatan::orderBy('nama_jabatan')->get();
        $jabatanId = $request->integer('jabatan_id');

        return view('master.master-kpi.create', compact('jabatans', 'jabatanId'));
    }

    public function store(Request $request)
    {
        $validated = $this->validasi($request);
        $validated['arah_capaian'] = $validated['arah_capaian'] ?? 'maksimal';
        $validated['urutan'] = $validated['urutan'] ?? 0;

        $this->cekTotalBobot($validated['jabatan_id'], (float) $validated['bobot']);

        MasterKpi::create($validated);

        return redirect()
            ->route('master.master-kpi.index')
            ->with('success', 'KPI berhasil ditambahkan.');
    }

    public function show(MasterKpi $masterKpi)
    {
        $masterKpi->load(['jabatan.divisi', 'penilaianKpis', 'templateLaporans']);

        return view('master.master-kpi.show', compact('masterKpi'));
    }

    public function edit(MasterKpi $masterKpi)
    {
        $jabatans = Jabatan::orderBy('nama_jabatan')->get();

        return view('master.master-kpi.edit', compact('masterKpi', 'jabatans'));
    }

    public function update(Request $request, MasterKpi $masterKpi)
    {
        $validated = $this->validasi($request);

        $this->cekTotalBobot($validated['jabatan_id'], (float) $validated['bobot'], $masterKpi->id);

        $masterKpi->update($validated);

        return redirect()
            ->route('master.master-kpi.index')
            ->with('success', 'KPI berhasil diperbarui.');
    }

    public function destroy(MasterKpi $masterKpi)
    {
        $jumlahPenilaian = $masterKpi->penilaianKpis()->count();
        $jumlahTemplate = $masterKpi->templateLaporans()->count();

        if ($jumlahPenilaian > 0 || $jumlahTemplate > 0) {
            return redirect()
                ->route('master.master-kpi.index')
                ->with('error', 'KPI tidak dapat dihapus karena masih digunakan pada penilaian atau template laporan.');
        }

        $masterKpi->delete();

        return redirect()
            ->route('master.master-kpi.index')
            ->with('success', 'KPI berhasil dihapus.');
    }

    private function validasi(Request $request): array
    {
        return $request->validate([
            'jabatan_id' => ['required', 'exists:jabatans,id'],
            'indikator' => ['required', 'string', 'max:255'],
            'target_default' => ['nullable', 'numeric', 'min:0'],
            'realisasi' => ['nullable', 'numeric', 'min:0'],
            'persen_capaian' => ['nullable', 'numeric', 'min:0'],
            'skor' => ['nullable', 'integer', 'min:1', 'max:5'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
            'bobot' => ['required', 'numeric', 'min:0', 'max:60'],
            'arah_capaian' => ['nullable', Rule::in(['maksimal', 'minimal'])],
            'urutan' => ['nullable', 'integer', 'min:0'],
        ], [
            'jabatan_id.required' => 'Jabatan wajib dipilih.',
            'jabatan_id.exists' => 'Jabatan yang dipilih tidak valid.',
            'indikator.required' => 'Indikator wajib diisi.',
            'target_default.numeric' => 'Target harus berupa angka.',
            'bobot.required' => 'Bobot wajib diisi.',
            'bobot.numeric' => 'Bobot harus berupa angka.',
            'bobot.max' => 'Bobot satu indikator maksimal adalah 60%.',
            'arah_capaian.required' => 'Arah capaian wajib dipilih.',
            'arah_capaian.in' => 'Arah capaian harus maksimal atau minimal.',
        ]);
    }

    /**
     * Pastikan total bobot KPI untuk satu jabatan sesuai bobot aspek KPI, yaitu 60%.
     */
    private function cekTotalBobot(int $jabatanId, float $bobotBaru, ?int $exceptId = null): void
    {
        $query = MasterKpi::where('jabatan_id', $jabatanId);

        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        $totalBobotLain = (float) $query->sum('bobot');
        $totalSetelahInput = $totalBobotLain + $bobotBaru;

        if ($totalSetelahInput > 60) {
            throw ValidationException::withMessages([
                'bobot' => "Total bobot KPI untuk jabatan ini akan menjadi {$totalSetelahInput}%. Total bobot KPI tidak boleh melebihi 60%.",
            ]);
        }
    }
}
