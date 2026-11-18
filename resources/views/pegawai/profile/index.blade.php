
@extends('layouts.apps')

@section('title', 'Profil Saya')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pegawai/profile/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pegawai/profile/avatar-autosave.css') }}">
@endpush

@section('content')
    @php
        $namaPegawai = $pegawai->user->name ?? $pegawai->nama ?? 'Pegawai';
        $inisial = mb_strtoupper(mb_substr($namaPegawai, 0, 1));

        $masaKerja = '-';

        if ($pegawai->tanggal_masuk) {
            $masuk = \Carbon\Carbon::parse($pegawai->tanggal_masuk)->startOfDay();
            $sekarang = now()->startOfDay();

            if ($masuk->lessThanOrEqualTo($sekarang)) {
                $selisih = $masuk->diff($sekarang);

                $masaKerja = $selisih->y . ' tahun '
                    . $selisih->m . ' bulan '
                    . $selisih->d . ' hari';
            } else {
                $masaKerja = 'Belum mulai bekerja';
            }
        }

        $fotoProfil = $pegawai->foto_profil
            ? \Illuminate\Support\Facades\Storage::disk('public')->url($pegawai->foto_profil)
            : null;
    @endphp

    <div class="pegawai-page profile-page">

        {{-- HERO --}}
        <section class="profile-hero">

            <div class="profile-avatar-wrapper">

                <div class="profile-avatar-circle">

                    <img
                        id="profile-photo-preview"
                        src="{{ $fotoProfil ?: '' }}"
                        alt=""
                        class="profile-avatar-image"
                        @if (!$fotoProfil) hidden @endif
                    >

                    <span
                        id="profile-avatar-initial"
                        class="profile-avatar-initial"
                        @if ($fotoProfil) hidden @endif
                    >
                        {{ $inisial }}
                    </span>

                </div>

                <label
                    for="foto_profil"
                    class="profile-avatar-camera"
                    title="Ganti foto profil"
                >
                    <i class="fa-solid fa-camera"></i>
                </label>

                <input
                    type="file"
                    id="foto_profil"
                    name="foto_profil"
                    accept="image/jpeg,image/png,image/webp"
                    class="profile-photo-input"
                    aria-label="Pilih foto profil"
                >

            </div>

            <div
                id="profile-photo-status"
                class="profile-photo-status"
                role="status"
                aria-live="polite"
                hidden
            ></div>

            <h1>
                {{ $namaPegawai }}
                <small>Pegawai</small>
            </h1>

            <p>{{ $pegawai->jabatan->nama_jabatan ?? '-' }}</p>

            <span>
                {{ $pegawai->jabatan->divisi->nama_divisi ?? '-' }}
                • PDAM Tirta Pinang
            </span>

            <div class="profile-hero-meta">
                <b>
                    <i class="fa-solid fa-circle"></i>
                    {{ ucfirst($pegawai->status ?? 'aktif') }}
                </b>

                <b>
                    NIP: {{ $pegawai->nip ?: '-' }}
                </b>

                <b>
                    Masa Kerja: {{ $masaKerja }}
                </b>
            </div>

        </section>

        {{-- INFORMASI KEPEGAWAIAN --}}
        <section class="profile-card">

            <div class="profile-card-heading">
                <i class="fa-solid fa-building"></i>

                <strong>Informasi Kepegawaian</strong>

                <span>
                    <i class="fa-solid fa-circle-check"></i>
                    Data Terverifikasi
                </span>
            </div>

            <div class="profile-data-list">

                <div>
                    <label>Jabatan</label>
                    <strong>
                        {{ $pegawai->jabatan->nama_jabatan ?? '-' }}
                    </strong>
                </div>

                <div>
                    <label>Divisi / Unit</label>
                    <strong>
                        {{ $pegawai->jabatan->divisi->nama_divisi ?? '-' }}
                    </strong>
                </div>

                <div>
                    <label>Tanggal Masuk</label>
                    <strong>
                        {{ $pegawai->tanggal_masuk
                            ? \Carbon\Carbon::parse($pegawai->tanggal_masuk)->translatedFormat('d F Y')
                            : '-' }}
                    </strong>
                </div>

                <div>
                    <label>Status Pegawai</label>
                    <strong class="profile-active">
                        ● {{ ucfirst($pegawai->status ?? 'aktif') }}
                    </strong>
                </div>

                <div>
                    <label>Atasan Langsung</label>
                    <strong>
                        {{ $pegawai->atasan->nama ?? '-' }}
                    </strong>
                </div>

            </div>

            <div class="profile-info-note">
                <i class="fa-solid fa-circle-info"></i>
                Informasi kepegawaian di atas hanya dapat diubah oleh Master.
            </div>

        </section>

        {{-- INFORMASI AKUN --}}
        <section class="profile-card">

            <div class="profile-card-heading">
                <i class="fa-solid fa-user"></i>

                <div>
                    <strong>Informasi Akun</strong>
                    <small>Kelola NIP, identitas akun, dan kata sandi Anda</small>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <form
                action="{{ route('pegawai.profile.update') }}"
                method="POST"
                class="profile-form"
            >
                @csrf
                @method('PUT')

                <label for="nip">Nomor Induk Pegawai (NIP)</label>

                <div class="profile-input-wrap">
                    <input
                        type="text"
                        name="nip"
                        id="nip"
                        value="{{ old('nip', $pegawai->nip ?? '') }}"
                        placeholder="Masukkan NIP Anda"
                        maxlength="50"
                    >
                    <i class="fa-regular fa-id-card"></i>
                </div>

                @error('nip')
                    <div class="text-danger small">{{ $message }}</div>
                @enderror

                <label for="name">Nama Lengkap</label>

                <div class="profile-input-wrap">
                    <input
                        type="text"
                        name="name"
                        id="name"
                        value="{{ old('name', $pegawai->user->name ?? '') }}"
                        required
                    >
                    <i class="fa-solid fa-pen"></i>
                </div>

                @error('name')
                    <div class="text-danger small">{{ $message }}</div>
                @enderror

                <label for="email">Alamat Email</label>

                <div class="profile-input-wrap">
                    <input
                        type="email"
                        name="email"
                        id="email"
                        value="{{ old('email', $pegawai->user->email ?? '') }}"
                        required
                    >
                    <i class="fa-solid fa-circle-check valid"></i>
                </div>

                @error('email')
                    <div class="text-danger small">{{ $message }}</div>
                @enderror

                <label for="password">Password Baru</label>

                <div class="profile-input-wrap">
                    <input
                        type="password"
                        name="password"
                        id="password"
                        placeholder="Kosongkan jika tidak ingin mengubah"
                    >
                    <i class="fa-regular fa-eye"></i>
                </div>

                @error('password')
                    <div class="text-danger small">{{ $message }}</div>
                @enderror

                <label for="password_confirmation">
                    Konfirmasi Password Baru
                </label>

                <div class="profile-input-wrap">
                    <input
                        type="password"
                        name="password_confirmation"
                        id="password_confirmation"
                        placeholder="Ketik ulang password baru..."
                    >
                    <i class="fa-regular fa-eye-slash"></i>
                </div>

                <button type="submit">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Simpan
                </button>

            </form>

        </section>

        {{-- MENU LAINNYA --}}
        <section class="profile-options-card">

            <a href="#">
                <i class="fa-regular fa-bell"></i>

                <span>
                    <strong>Notifikasi & Preferensi Presensi</strong>
                    <small>Pengingat check-in & batas waktu laporan</small>
                </span>

                <i class="fa-solid fa-chevron-right"></i>
            </a>

            <a href="#">
                <i class="fa-regular fa-file-lines"></i>

                <span>
                    <strong>Dokumen & SK Kepegawaian (PDF)</strong>
                    <small>Arsip digital SK & Riwayat Golongan</small>
                </span>

                <i class="fa-solid fa-chevron-right"></i>
            </a>

        </section>

    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const input = document.getElementById('foto_profil');
    const preview = document.getElementById('profile-photo-preview');
    const initial = document.getElementById('profile-avatar-initial');
    const status = document.getElementById('profile-photo-status');
    const camera = document.querySelector('.profile-avatar-camera');

    if (!input || !preview || !status) return;

    function showStatus(message, type = 'success') {
        status.textContent = message;
        status.hidden = false;

        status.classList.remove(
            'is-success',
            'is-error',
            'is-loading'
        );

        status.classList.add('is-' + type);
    }

    function setLoading(loading) {
        input.disabled = loading;

        if (camera) {
            camera.classList.toggle('is-loading', loading);
        }
    }

    input.addEventListener('change', async function () {

        const file = input.files && input.files[0];

        if (!file) return;

        const allowedTypes = [
            'image/jpeg',
            'image/png',
            'image/webp'
        ];

        if (!allowedTypes.includes(file.type)) {
            showStatus(
                'Foto harus JPG, PNG, atau WebP.',
                'error'
            );

            input.value = '';
            return;
        }

        if (file.size > 2 * 1024 * 1024) {
            showStatus(
                'Ukuran foto maksimal 2 MB.',
                'error'
            );

            input.value = '';
            return;
        }

        const formData = new FormData();
        formData.append('foto_profil', file);
        formData.append('_method', 'PUT');

        setLoading(true);
        showStatus('Sedang mengunggah foto...', 'loading');

        try {
            const response = await fetch(
                @json(route('pegawai.profile.foto')),
                {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': @json(csrf_token()),
                        'Accept': 'application/json',
                    },
                    body: formData,
                    credentials: 'same-origin',
                }
            );

            const result = await response.json();

            if (!response.ok || !result.success) {
                throw new Error(
                    result.message || 'Upload foto gagal.'
                );
            }

            // Tampilkan foto setelah server berhasil menyimpan.
            const separator = result.foto_url.includes('?')
                ? '&'
                : '?';

            preview.src = result.foto_url
                + separator
                + 'v='
                + Date.now();

            preview.hidden = false;

            if (initial) {
                initial.hidden = true;
            }

            showStatus(
                'Foto profil berhasil disimpan.',
                'success'
            );

        } catch (error) {
            showStatus(
                error.message || 'Terjadi kesalahan saat upload.',
                'error'
            );
        } finally {
            setLoading(false);
            input.value = '';
        }

    });

    preview.addEventListener('error', function () {
        preview.hidden = true;

        if (initial) {
            initial.hidden = false;
        }

        showStatus(
            'Foto tersimpan, tetapi gambar tidak dapat ditampilkan. Periksa Storage Laravel.',
            'error'
        );
    });

});
</script>
@endpush
