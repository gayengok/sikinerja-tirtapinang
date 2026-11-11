<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Divisi;
use App\Models\Jabatan;
use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class PegawaiController extends Controller
{
    public function index()
    {
        $pegawais = Pegawai::with(['user', 'jabatan.divisi', 'atasan'])
            ->orderBy('nama')
            ->paginate(10);

        $roleCounts = [
            'atasan' => Pegawai::whereHas('user', fn ($query) => $query->where('role', 'atasan'))->count(),
            'kepalaBagian' => Pegawai::whereHas('user', fn ($query) => $query->where('role', 'kepala_bagian'))->count(),
            'pegawai' => Pegawai::whereHas('user', fn ($query) => $query->where('role', 'pegawai'))->count(),
        ];

        return view('master.pegawai.index', compact('pegawais', 'roleCounts'));
    }

    public function create()
    {
        $divisis = Divisi::orderBy('nama_divisi')->get();
        $jabatans = Jabatan::with('divisi')->orderBy('nama_jabatan')->get();
        $atasans = $this->daftarAtasan();

        return view('master.pegawai.create', compact('divisis', 'jabatans', 'atasans'));
    }

    public function store(Request $request)
    {
        $validated = $this->validasi($request);

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['nama'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => $validated['role'],
            ]);

            Pegawai::create([
                'user_id' => $user->id,
                'nama' => $validated['nama'],
                'jabatan_id' => $validated['jabatan_id'],
                'atasan_id' => $validated['atasan_id'] ?? null,
                'tanggal_masuk' => $validated['tanggal_masuk'] ?? null,
                'status' => $validated['status'],
            ]);
        });

        return redirect()
            ->route('master.pegawai.index')
            ->with('success', 'Pegawai berhasil ditambahkan.');
    }

    public function show(Pegawai $pegawai)
    {
        $pegawai->load(['user', 'jabatan.divisi', 'atasan', 'bawahans']);

        return view('master.pegawai.show', compact('pegawai'));
    }

    public function edit(Pegawai $pegawai)
    {
        $pegawai->load('user');
        $divisis = Divisi::orderBy('nama_divisi')->get();
        $jabatans = Jabatan::with('divisi')->orderBy('nama_jabatan')->get();
        $atasans = $this->daftarAtasan($pegawai->id);

        return view('master.pegawai.edit', compact('pegawai', 'divisis', 'jabatans', 'atasans'));
    }

    public function update(Request $request, Pegawai $pegawai)
    {
        $validated = $this->validasi($request, $pegawai);

        DB::transaction(function () use ($validated, $pegawai) {
            $userData = [
                'name' => $validated['nama'],
                'email' => $validated['email'],
                'role' => $validated['role'],
            ];

            if (! empty($validated['password'])) {
                $userData['password'] = Hash::make($validated['password']);
            }

            $pegawai->user()->update($userData);

            $pegawai->update([
                'nama' => $validated['nama'],
                'jabatan_id' => $validated['jabatan_id'],
                'atasan_id' => $validated['atasan_id'] ?? null,
                'tanggal_masuk' => $validated['tanggal_masuk'] ?? null,
                'status' => $validated['status'],
            ]);
        });

        return redirect()
            ->route('master.pegawai.index')
            ->with('success', 'Data pegawai berhasil diperbarui.');
    }

    public function destroy(Pegawai $pegawai)
    {
        $jumlahBawahan = $pegawai->bawahans()->count();

        if ($jumlahBawahan > 0) {
            return redirect()
                ->route('master.pegawai.index')
                ->with('error', "Pegawai tidak dapat dihapus karena masih menjadi atasan bagi {$jumlahBawahan} pegawai lain.");
        }

        DB::transaction(function () use ($pegawai) {
            $user = $pegawai->user;
            $pegawai->delete();
            $user?->delete();
        });

        return redirect()
            ->route('master.pegawai.index')
            ->with('success', 'Pegawai berhasil dihapus.');
    }

    /**
     * Validasi bersama untuk store() dan update().
     * Termasuk aturan baru: atasan_id wajib berasal dari divisi yang sama
     * dengan jabatan_id yang dipilih, dan atasan wajib berstatus aktif.
     * Divisi diturunkan dari jabatan_id (bukan disimpan di tabel pegawais),
     * sesuai instruksi bahwa pegawais tidak menyimpan divisi_id secara terpisah.
     */
    private function validasi(Request $request, ?Pegawai $pegawai = null): array
    {
        $emailRule = $pegawai
            ? Rule::unique('users', 'email')->ignore($pegawai->user_id)
            : 'unique:users,email';

        $atasanExcludeRule = $pegawai ? Rule::notIn([$pegawai->id]) : Rule::notIn([]);

        $rules = [
            'nama' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', $emailRule],
            'password' => [$pegawai ? 'nullable' : 'required', 'string', 'min:6'],
            'role' => ['required', Rule::in(['pegawai', 'atasan', 'kepala_bagian'])],
            'jabatan_id' => ['required', 'exists:jabatans,id'],
            'atasan_id' => [
                in_array($request->input('role'), ['pegawai', 'atasan'], true) ? 'required' : 'nullable',
                'exists:pegawais,id',
                $atasanExcludeRule,
                function ($attribute, $value, $fail) use ($request) {
                    $role = $request->input('role');
                    $targetRole = match ($role) {
                        'pegawai' => 'atasan',
                        'atasan' => 'kepala_bagian',
                        default => null,
                    };

                    if (! $targetRole) {
                        if ($value) {
                            $fail('Kepala Bagian tidak boleh memiliki atasan langsung.');
                        }

                        return;
                    }

                    if (! $value) {
                        return;
                    }

                    $jabatan = Jabatan::find($request->input('jabatan_id'));
                    $atasan = Pegawai::with('jabatan')->whereHas('user', function ($query) use ($targetRole) {
                        $query->where('role', $targetRole);
                    })->find($value);

                    if (! $jabatan || ! $atasan) {
                        $fail('Atasan langsung yang dipilih tidak valid.');

                        return;
                    }

                    if ($atasan->status !== 'aktif') {
                        $fail('Atasan langsung yang dipilih harus berstatus aktif.');

                        return;
                    }

                    if (! $atasan->jabatan || $atasan->jabatan->divisi_id !== $jabatan->divisi_id) {
                        $fail('Atasan langsung harus berasal dari divisi yang sama.');
                    }
                },
            ],
            'tanggal_masuk' => ['nullable', 'date'],
            'status' => ['required', Rule::in(['aktif', 'nonaktif'])],
        ];

        $messages = [
            'nama.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email sudah digunakan oleh akun lain.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
            'role.required' => 'Role wajib dipilih.',
            'jabatan_id.required' => 'Jabatan wajib dipilih.',
            'jabatan_id.exists' => 'Jabatan yang dipilih tidak valid.',
            'atasan_id.exists' => 'Atasan yang dipilih tidak valid.',
            'atasan_id.required' => 'Pegawai dan atasan wajib memiliki atasan langsung sesuai divisinya.',
            'atasan_id.not_in' => 'Pegawai tidak boleh menjadi atasan bagi dirinya sendiri.',
            'status.required' => 'Status wajib dipilih.',
        ];

        return $request->validate($rules, $messages);
    }

    /**
     * Daftar atasan dan kepala bagian aktif yang dapat dipilih sebagai atasan langsung.
     * Eager-load jabatan agar Blade dapat menampilkan
     * atribut data-divisi untuk cascading dropdown Divisi -> Jabatan -> Atasan.
     */
    private function daftarAtasan(?int $exceptPegawaiId = null)
    {
        $query = Pegawai::whereHas('user', function ($q) {
            $q->whereIn('role', ['atasan', 'kepala_bagian']);
        })
            ->where('status', 'aktif')
            ->with(['user', 'jabatan']);

        if ($exceptPegawaiId) {
            $query->where('id', '!=', $exceptPegawaiId);
        }

        return $query->orderBy('nama')->get();
    }
}
