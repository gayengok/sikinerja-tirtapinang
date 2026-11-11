<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Jabatan;
use App\Models\MasterKpi;
use App\Models\MasterTemplateLaporan;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TemplateLaporanController extends Controller
{
    public function index()
    {
        $templateLaporans = MasterTemplateLaporan::with(['divisi', 'jabatan', 'masterKpi'])
            ->orderBy('divisi_id')
            ->paginate(15);

        return view('master.template-laporan.index', compact('templateLaporans'));
    }

    public function create()
    {
        $jabatans = Jabatan::with('divisi')->orderBy('nama_jabatan')->get();
        $masterKpis = MasterKpi::with('jabatan.divisi')
            ->orderBy('indikator')
            ->get();

        return view('master.template-laporan.create', compact('jabatans', 'masterKpis'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'jabatan_id' => ['required', 'exists:jabatans,id'],
            'nama_field' => ['required', 'string', 'max:255'],
            'tipe_field' => ['required', Rule::in(['angka', 'teks', 'dropdown'])],
            'satuan' => ['nullable', 'string', 'max:50'],
            'terhubung_ke_kpi_id' => ['nullable', 'exists:master_kpis,id'],
            'opsi' => ['nullable', 'array'],
            'opsi.*' => ['nullable', 'string', 'max:100', 'distinct'],
        ], [
            'jabatan_id.required' => 'Jabatan wajib dipilih.',
            'jabatan_id.exists' => 'Jabatan yang dipilih tidak valid.',
            'nama_field.required' => 'Nama field wajib diisi.',
            'tipe_field.required' => 'Tipe field wajib dipilih.',
            'tipe_field.in' => 'Tipe field tidak valid.',
            'terhubung_ke_kpi_id.exists' => 'KPI yang dipilih tidak valid.',
        ]);
        $validated['divisi_id'] = Jabatan::findOrFail($validated['jabatan_id'])->divisi_id;
        $this->validateKpiMapping($validated);
        $this->validateDropdownOptions($validated);

        $template = DB::transaction(function () use ($validated) {
            $options = $validated['opsi'] ?? [];
            unset($validated['opsi']);

            $template = MasterTemplateLaporan::create($validated);
            $this->syncOptions($template, $options);

            return $template;
        });

        return redirect()
            ->route('master.template-laporan.index')
            ->with('success', 'Template laporan berhasil ditambahkan.');
    }

    public function show(MasterTemplateLaporan $templateLaporan)
    {
        $templateLaporan->load(['divisi', 'jabatan', 'masterKpi', 'laporanHarianDetails']);

        return view('master.template-laporan.show', compact('templateLaporan'));
    }

    public function edit(MasterTemplateLaporan $templateLaporan)
    {
        $templateLaporan->load('options');
        $jabatans = Jabatan::with('divisi')->orderBy('nama_jabatan')->get();
        $masterKpis = MasterKpi::with('jabatan.divisi')
            ->orderBy('indikator')
            ->get();

        return view('master.template-laporan.edit', compact('templateLaporan', 'jabatans', 'masterKpis'));
    }

    public function update(Request $request, MasterTemplateLaporan $templateLaporan)
    {
        $validated = $request->validate([
            'jabatan_id' => ['required', 'exists:jabatans,id'],
            'nama_field' => ['required', 'string', 'max:255'],
            'tipe_field' => ['required', Rule::in(['angka', 'teks', 'dropdown'])],
            'satuan' => ['nullable', 'string', 'max:50'],
            'terhubung_ke_kpi_id' => ['nullable', 'exists:master_kpis,id'],
            'opsi' => ['nullable', 'array'],
            'opsi.*' => ['nullable', 'string', 'max:100', 'distinct'],
        ], [
            'jabatan_id.required' => 'Jabatan wajib dipilih.',
            'jabatan_id.exists' => 'Jabatan yang dipilih tidak valid.',
            'nama_field.required' => 'Nama field wajib diisi.',
            'tipe_field.required' => 'Tipe field wajib dipilih.',
            'tipe_field.in' => 'Tipe field tidak valid.',
            'terhubung_ke_kpi_id.exists' => 'KPI yang dipilih tidak valid.',
        ]);
        $validated['divisi_id'] = Jabatan::findOrFail($validated['jabatan_id'])->divisi_id;
        $this->validateKpiMapping($validated);
        $this->validateDropdownOptions($validated);

        DB::transaction(function () use ($validated, $templateLaporan) {
            $options = $validated['opsi'] ?? [];
            unset($validated['opsi']);

            $templateLaporan->update($validated);
            $this->syncOptions($templateLaporan, $options);
        });

        return redirect()
            ->route('master.template-laporan.index')
            ->with('success', 'Template laporan berhasil diperbarui.');
    }

    public function destroy(MasterTemplateLaporan $templateLaporan)
    {
        $jumlahDetail = $templateLaporan->laporanHarianDetails()->count();

        if ($jumlahDetail > 0) {
            return redirect()
                ->route('master.template-laporan.index')
                ->with('error', 'Template laporan tidak dapat dihapus karena sudah digunakan pada laporan harian.');
        }

        $templateLaporan->delete();

        return redirect()
            ->route('master.template-laporan.index')
            ->with('success', 'Template laporan berhasil dihapus.');
    }

    private function validateKpiMapping(array $validated): void
    {
        if (empty($validated['terhubung_ke_kpi_id'])) {
            return;
        }

        if ($validated['tipe_field'] !== 'angka') {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'terhubung_ke_kpi_id' => 'Template yang terhubung ke KPI harus bertipe angka.',
            ]);
        }

        $kpi = MasterKpi::find($validated['terhubung_ke_kpi_id']);
        if (!$kpi || $kpi->jabatan_id !== (int) $validated['jabatan_id']) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'terhubung_ke_kpi_id' => 'KPI harus berasal dari jabatan yang sama dengan template laporan.',
            ]);
        }
    }

    private function syncOptions(MasterTemplateLaporan $template, array $options): void
    {
        if (($template->tipe_field ?? null) !== 'dropdown') {
            $template->options()->delete();

            return;
        }

        $options = collect($options)
            ->map(fn ($option) => trim((string) $option))
            ->filter()
            ->unique()
            ->values();

        $template->options()->delete();
        $template->options()->createMany(
            $options->map(fn (string $label, int $index) => [
                'label' => $label,
                'urutan' => $index,
            ])->all()
        );
    }

    private function validateDropdownOptions(array $validated): void
    {
        if ($validated['tipe_field'] === 'dropdown' && !collect($validated['opsi'] ?? [])->filter()->isNotEmpty()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'opsi' => 'Minimal satu opsi wajib diisi untuk field Dropdown.',
            ]);
        }
    }
}
