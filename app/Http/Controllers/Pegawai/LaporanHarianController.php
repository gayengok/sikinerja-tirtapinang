<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use App\Models\LaporanHarian;
use App\Models\MasterTemplateLaporan;
use App\Support\HariLibur;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class LaporanHarianController extends Controller
{
    public function index(Request $request)
    {
        if ($request->user()->role === 'atasan') {
            return redirect()->route('atasan.laporan-harian.create');
        }

        $pegawai = $this->pegawaiLogin($request);

        $query = $pegawai->laporanHarians()->orderByDesc('tanggal');

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal', $request->query('tanggal'));
        }

        if ($request->filled('bulan')) {
            $query->whereMonth('tanggal', $request->query('bulan'));
        }

        $laporans = $query->paginate(10)->withQueryString();
        $statusCounts = $pegawai->laporanHarians()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('pegawai.laporan-harian.index', [
            'laporans' => $laporans,
            'statusCounts' => $statusCounts,
            'routePrefix' => $this->routePrefix($request),
        ]);
    }

    public function create(Request $request)
    {
        $pegawai = $this->pegawaiLogin($request);
        $pegawai->load('jabatan');

        abort_if(! $pegawai->jabatan, 404, 'Jabatan pegawai belum ditentukan. Hubungi Master.');

        $templateLaporans = $this->templatesForPegawai($pegawai)
            ->with('options')
            ->orderBy('id')
            ->get();

        $laporanTanggal = $pegawai->laporanHarians()
            ->get(['tanggal', 'status'])
            ->mapWithKeys(fn (LaporanHarian $laporan) => [
                $laporan->tanggal->toDateString() => $laporan->status,
            ]);
        $tanggalTerpilih = old('tanggal', now()->toDateString());
        $hariLiburTerpilih = HariLibur::detail($tanggalTerpilih);
        $laporanHariIni = $request->user()->role === 'atasan'
            ? $pegawai->laporanHarians()
                ->with('details')
                ->whereDate('tanggal', now()->toDateString())
                ->first()
            : null;

        return view('pegawai.laporan-harian.create', [
            'templateLaporans' => $templateLaporans,
            'laporanTanggal' => $laporanTanggal,
            'tanggalTerpilih' => $tanggalTerpilih,
            'hariLiburTerpilih' => $hariLiburTerpilih,
            'laporanHariIni' => $laporanHariIni,
            'nilaiTersimpan' => $laporanHariIni?->details->keyBy('master_template_laporan_id') ?? collect(),
            'routePrefix' => $this->routePrefix($request),
        ]);
    }

    public function store(Request $request)
    {
        $pegawai = $this->pegawaiLogin($request);
        $pegawai->load('jabatan');

        $divisiId = $pegawai->jabatan->divisi_id ?? null;
        $templateIds = $this->templatesForPegawai($pegawai)->pluck('id');

        $validated = $request->validate([
            'tanggal' => ['required', 'date'],
            'catatan' => ['nullable', 'string', 'max:1000'],
            'bukti_gambar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'detail' => ['required', 'array', 'min:1'],
            'detail.*.master_template_laporan_id' => ['required', Rule::in($templateIds)],
            'detail.*.nilai' => ['nullable', 'string', 'max:255'],
        ], [
            'tanggal.required' => 'Tanggal wajib diisi.',
            'detail.required' => 'Minimal satu field laporan wajib diisi.',
            'detail.*.master_template_laporan_id.in' => 'Field laporan tidak valid untuk divisi Anda.',
        ]);
        $this->tolakJikaBukanHariIni($validated['tanggal']);
        $this->tolakJikaHariLibur($validated['tanggal']);
        $this->validateKpiDetails($validated['detail'], $divisiId);
        $this->validateDropdownDetails($validated['detail'], $templateIds);

        $sudahAda = $pegawai->laporanHarians()
            ->whereDate('tanggal', $validated['tanggal'])
            ->exists();

        if ($sudahAda) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Laporan untuk tanggal tersebut sudah ada. Silakan edit laporan yang sudah dibuat.');
        }

        $isAtasan = $request->user()->role === 'atasan';

        DB::transaction(function () use ($validated, $pegawai, $request, $isAtasan) {
            $buktiGambar = $request->hasFile('bukti_gambar')
                ? $request->file('bukti_gambar')->store('laporan-harian', 'public')
                : null;

            $laporan = LaporanHarian::create([
                'pegawai_id' => $pegawai->id,
                'tanggal' => $validated['tanggal'],
                'catatan' => $validated['catatan'] ?? null,
                'bukti_gambar' => $buktiGambar,
                'status' => $isAtasan ? 'diajukan' : 'draft',
            ]);

            foreach ($validated['detail'] as $item) {
                $laporan->details()->create([
                    'master_template_laporan_id' => $item['master_template_laporan_id'],
                    'nilai' => $item['nilai'] ?? null,
                ]);
            }
        });

        return redirect()
            ->route($this->routePrefix($request) . ($isAtasan
                ? '.laporan-harian.create'
                : '.laporan-harian.index'))
            ->with('success', $isAtasan
                ? 'Laporan harian berhasil dikirim kepada Kepala Bagian untuk ditinjau.'
                : 'Laporan harian berhasil disimpan sebagai draft.');
    }

    public function show(Request $request, LaporanHarian $laporanHarian)
    {
        $laporan = $this->cekMilikSendiri($request, $laporanHarian);

        if ($request->user()->role === 'atasan') {
            return redirect()->route('atasan.dashboard');
        }

        $laporan->load(['details.templateLaporan', 'penyetuju']);

        return view('pegawai.laporan-harian.show', [
            'laporan' => $laporan,
            'routePrefix' => $this->routePrefix($request),
        ]);
    }

    public function image(Request $request, LaporanHarian $laporanHarian)
    {
        $laporan = $this->cekMilikSendiri($request, $laporanHarian);

        abort_unless($laporan->bukti_gambar, 404, 'Bukti gambar tidak ditemukan.');

        $disk = Storage::disk('public');
        abort_unless($disk->exists($laporan->bukti_gambar), 404, 'File bukti gambar tidak ditemukan.');

        return response()->file($disk->path($laporan->bukti_gambar));
    }

    public function edit(Request $request, LaporanHarian $laporanHarian)
    {
        $laporan = $this->cekMilikSendiri($request, $laporanHarian);

        if (! in_array($laporan->status, ['draft', 'ditolak'])) {
            return redirect()
                ->route($this->routePrefix($request).'.laporan-harian.show', $laporan)
                ->with('error', 'Laporan dengan status "'.$laporan->status.'" tidak dapat diedit.');
        }

        $pegawai = $request->user()->pegawai;
        $pegawai->load('jabatan');
        $templateLaporans = $this->templatesForPegawai($pegawai)
            ->with('options')
            ->orderBy('id')
            ->get();

        $laporan->load('details');
        $nilaiTersimpan = $laporan->details->keyBy('master_template_laporan_id');

        return view('pegawai.laporan-harian.edit', [
            'laporan' => $laporan,
            'templateLaporans' => $templateLaporans,
            'nilaiTersimpan' => $nilaiTersimpan,
            'routePrefix' => $this->routePrefix($request),
        ]);
    }

    public function update(Request $request, LaporanHarian $laporanHarian)
    {
        $laporan = $this->cekMilikSendiri($request, $laporanHarian);

        if (! in_array($laporan->status, ['draft', 'ditolak'])) {
            return redirect()
                ->route($this->routePrefix($request).'.laporan-harian.show', $laporan)
                ->with('error', 'Laporan dengan status "'.$laporan->status.'" tidak dapat diedit.');
        }

        $pegawai = $request->user()->pegawai;
        $pegawai->load('jabatan');
        $divisiId = $pegawai->jabatan->divisi_id ?? null;
        $templateIds = $this->templatesForPegawai($pegawai)->pluck('id');

        $validated = $request->validate([
            'tanggal' => ['required', 'date'],
            'catatan' => ['nullable', 'string', 'max:1000'],
            'bukti_gambar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'detail' => ['required', 'array', 'min:1'],
            'detail.*.master_template_laporan_id' => ['required', Rule::in($templateIds)],
            'detail.*.nilai' => ['nullable', 'string', 'max:255'],
        ], [
            'tanggal.required' => 'Tanggal wajib diisi.',
            'detail.required' => 'Minimal satu field laporan wajib diisi.',
            'detail.*.master_template_laporan_id.in' => 'Field laporan tidak valid untuk divisi Anda.',
        ]);
        $this->tolakJikaHariLibur($validated['tanggal']);
        $this->validateKpiDetails($validated['detail'], $divisiId);
        $this->validateDropdownDetails($validated['detail'], $templateIds);

        $sudahAda = $pegawai->laporanHarians()
            ->whereDate('tanggal', $validated['tanggal'])
            ->where('id', '!=', $laporan->id)
            ->exists();

        if ($sudahAda) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Laporan lain untuk tanggal tersebut sudah ada.');
        }

        // Pendekatan yang digunakan: hapus semua detail lama, lalu simpan ulang detail baru.
        // Dipilih karena jumlah field per divisi relatif kecil dan berubah-ubah tiap submit,
        // sehingga lebih sederhana & aman dibanding mencocokkan updateOrCreate per template_id.
        DB::transaction(function () use ($validated, $laporan, $request) {
            $buktiGambar = $laporan->bukti_gambar;

            if ($request->hasFile('bukti_gambar')) {
                if ($buktiGambar) {
                    Storage::disk('public')->delete($buktiGambar);
                }

                $buktiGambar = $request->file('bukti_gambar')->store('laporan-harian', 'public');
            }

            $laporan->update([
                'tanggal' => $validated['tanggal'],
                'catatan' => $validated['catatan'] ?? null,
                'bukti_gambar' => $buktiGambar,
                'status' => 'draft',
            ]);

            $laporan->details()->delete();

            foreach ($validated['detail'] as $item) {
                $laporan->details()->create([
                    'master_template_laporan_id' => $item['master_template_laporan_id'],
                    'nilai' => $item['nilai'] ?? null,
                ]);
            }
        });

        return redirect()
            ->route($this->routePrefix($request).'.laporan-harian.show', $laporan)
            ->with('success', 'Laporan harian berhasil diperbarui dan berstatus draft.');
    }

    public function destroy(Request $request, LaporanHarian $laporanHarian)
    {
        $laporan = $this->cekMilikSendiri($request, $laporanHarian);

        if ($laporan->status !== 'draft') {
            return redirect()
                ->route($this->routePrefix($request).'.laporan-harian.index')
                ->with('error', 'Hanya laporan berstatus "draft" yang dapat dihapus.');
        }

        if ($laporan->bukti_gambar) {
            Storage::disk('public')->delete($laporan->bukti_gambar);
        }

        $laporan->delete();

        return redirect()
            ->route($this->routePrefix($request).'.laporan-harian.index')
            ->with('success', 'Laporan berhasil dihapus.');
    }

    public function ajukan(Request $request, LaporanHarian $laporanHarian)
    {
        $laporan = $this->cekMilikSendiri($request, $laporanHarian);

        if ($laporan->status !== 'draft') {
            return redirect()
                ->route($this->routePrefix($request).'.laporan-harian.show', $laporan)
                ->with('error', 'Hanya laporan berstatus "draft" yang dapat diajukan.');
        }

        if ($laporan->details()->count() < 1) {
            return redirect()
                ->route($this->routePrefix($request).'.laporan-harian.show', $laporan)
                ->with('error', 'Laporan harus memiliki minimal satu detail sebelum diajukan.');
        }

        $laporan->update(['status' => 'diajukan']);

        $reviewerLabel = $request->user()->role === 'atasan' ? 'Kepala Bagian' : 'Atasan';

        return redirect()
            ->route($this->routePrefix($request).'.laporan-harian.show', $laporan)
            ->with('success', "Laporan berhasil diajukan ke {$reviewerLabel}.");
    }

    private function pegawaiLogin(Request $request)
    {
        $pegawai = $request->user()->pegawai;

        abort_if(! $pegawai, 403, 'Data pegawai untuk akun Anda tidak ditemukan.');

        return $pegawai;
    }

    private function routePrefix(Request $request): string
    {
        return $request->user()->role === 'atasan' ? 'atasan' : 'pegawai';
    }

    private function templatesForPegawai(\App\Models\Pegawai $pegawai): Builder
    {
        return MasterTemplateLaporan::query()
            ->where('divisi_id', $pegawai->jabatan?->divisi_id)
            ->where(function (Builder $query) use ($pegawai) {
                $query->whereNull('jabatan_id')
                    ->orWhere('jabatan_id', $pegawai->jabatan_id);
            });
    }

    /**
     * Pastikan laporan yang diakses benar-benar milik pegawai yang sedang login.
     */
    private function cekMilikSendiri(Request $request, LaporanHarian $laporanHarian): LaporanHarian
    {
        $pegawai = $this->pegawaiLogin($request);

        $laporan = $pegawai->laporanHarians()->find($laporanHarian->id);

        abort_if(! $laporan, 404, 'Laporan tidak ditemukan.');

        return $laporan;
    }

    private function validateKpiDetails(array $details, ?int $divisiId): void
    {
        $templates = MasterTemplateLaporan::whereIn(
            'id',
            collect($details)->pluck('master_template_laporan_id')
        )->with('masterKpi.jabatan')->get()->keyBy('id');

        foreach ($details as $index => $detail) {
            $template = $templates->get($detail['master_template_laporan_id']);
            $kpi = $template?->masterKpi;

            if (! $kpi) {
                continue;
            }

            if ($template->tipe_field !== 'angka') {
                throw ValidationException::withMessages([
                    "detail.{$index}.nilai" => 'Field yang terhubung ke KPI harus bertipe angka.',
                ]);
            }

            $nilai = trim(str_replace(',', '.', (string) ($detail['nilai'] ?? '')));
            if ($nilai === '' || ! is_numeric($nilai)) {
                throw ValidationException::withMessages([
                    "detail.{$index}.nilai" => 'Nilai KPI wajib diisi dengan angka.',
                ]);
            }
        }
    }

    private function validateDropdownDetails(array $details, Collection $templateIds): void
    {
        $templates = MasterTemplateLaporan::with('options')
            ->whereIn('id', $templateIds)
            ->get()
            ->keyBy('id');

        foreach ($details as $index => $detail) {
            $template = $templates->get($detail['master_template_laporan_id']);

            if (! $template || $template->tipe_field !== 'dropdown' || blank($detail['nilai'] ?? null)) {
                continue;
            }

            if (! $template->options->contains('label', $detail['nilai'])) {
                throw ValidationException::withMessages([
                    "detail.{$index}.nilai" => 'Pilih salah satu opsi yang tersedia.',
                ]);
            }
        }
    }

    private function tolakJikaHariLibur(string $tanggal): void
    {
        $hariLibur = HariLibur::detail($tanggal);

        if ($hariLibur) {
            throw ValidationException::withMessages([
                'tanggal' => "Laporan harian tidak perlu diisi pada {$hariLibur['label']}.",
            ]);
        }
    }

    private function tolakJikaBukanHariIni(string $tanggal): void
    {
        if (! Carbon::parse($tanggal)->isToday()) {
            throw ValidationException::withMessages([
                'tanggal' => 'Laporan hanya dapat dibuat untuk tanggal hari ini.',
            ]);
        }
    }
}
