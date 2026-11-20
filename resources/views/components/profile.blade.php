
@extends('layouts.app')

@section('title', 'Profil Saya')

@push('styles')
    <link href="{{ asset('css/profile/index.css') }}?v={{ file_exists(public_path('css/profile/index.css')) ? filemtime(public_path('css/profile/index.css')) : '1' }}" rel="stylesheet">
@endpush

@section('content')
    @php
        $roleLabel = match ($user->role) {
            'master' => 'Admin',
            'atasan' => 'Atasan',
            'kepala_bagian' => 'Kepala Bagian',
            'direktur' => 'Direktur',
            default => 'Pengguna',
        };

        $nama = $user->name ?: 'Pengguna';
        $inisial = mb_strtoupper(mb_substr($nama, 0, 1));

        $jabatan = data_get($pegawai, 'jabatan.nama_jabatan')
            ?? data_get($pegawai, 'nama_jabatan')
            ?? $roleLabel;

        $divisi = data_get($pegawai, 'jabatan.divisi.nama_divisi')
            ?? data_get($pegawai, 'divisi.nama_divisi')
            ?? data_get($pegawai, 'nama_divisi')
            ?? 'Belum tersedia';

        $nip = data_get($pegawai, 'nip') ?? '-';

        $tanggalMasuk = data_get($pegawai, 'tanggal_masuk');

        $tanggalMasukFormat = $tanggalMasuk
            ? \Illuminate\Support\Carbon::parse($tanggalMasuk)->translatedFormat('d F Y')
            : '-';

        $masaKerja = $tanggalMasuk
            ? \Illuminate\Support\Carbon::parse($tanggalMasuk)->diffForHumans(
                \Illuminate\Support\Carbon::now(),
                \Carbon\CarbonInterface::DIFF_ABSOLUTE,
                false,
                3
            )
            : '-';

        $status = data_get($pegawai, 'status') ?? 'Aktif';

        $atasan = data_get($pegawai, 'atasan.nama')
            ?? data_get($pegawai, 'atasan.name')
            ?? 'Belum tersedia';
    @endphp

    <div class="account-profile">
        <section class="account-profile-hero">
            <div class="account-profile-photo-wrap">
                <div class="account-profile-photo">
                    @if ($foto)
                        <img src="{{ $foto }}" alt="Foto profil {{ $nama }}">
                    @else
                        <span>{{ $inisial }}</span>
                    @endif
                </div>

                <button type="button"
                    class="account-profile-camera"
                    id="profilePhotoButton"
                    aria-label="Ubah foto profil"
                    title="Ubah foto profil">
                    <i class="fa-solid fa-camera"></i>
                </button>
            </div>

            <div class="account-profile-name-row">
                <h1>{{ $nama }}</h1>
                <span class="account-profile-role">{{ $roleLabel }}</span>
            </div>

            <h2>{{ $jabatan }}</h2>

            <p>{{ $divisi }} &bull; PDAM Tirta Pinang</p>

            <div class="account-profile-badges">
                <span class="account-profile-status">
                    <i class="fa-solid fa-circle"></i>
                    {{ ucfirst($status) }}
                </span>

                <span class="account-profile-meta">
                    NIP: {{ $nip }}
                </span>

                <span class="account-profile-meta">
                    Masa Kerja: {{ $masaKerja }}
                </span>
            </div>
        </section>

        <section class="account-profile-card">
            <div class="account-profile-card-heading">
                <div class="account-profile-card-heading-left">
                    <span class="account-profile-heading-icon">
                        <i class="fa-solid fa-building"></i>
                    </span>

                    <div>
                        <h3>Informasi Kepegawaian</h3>
                    </div>
                </div>

                <span class="account-profile-verified">
                    <i class="fa-solid fa-circle-check"></i>
                    Data Kepegawaian
                </span>
            </div>

            <div class="account-profile-details">
                <div class="account-profile-detail">
                    <span>Jabatan</span>
                    <strong>{{ $jabatan }}</strong>
                </div>

                <div class="account-profile-detail">
                    <span>Divisi / Unit</span>
                    <strong>{{ $divisi }}</strong>
                </div>

                <div class="account-profile-detail">
                    <span>Tanggal Masuk</span>
                    <strong>{{ $tanggalMasukFormat }}</strong>
                </div>

                <div class="account-profile-detail">
                    <span>Status Pegawai</span>
                    <strong class="account-profile-active">
                        <i class="fa-solid fa-circle"></i>
                        {{ ucfirst($status) }}
                    </strong>
                </div>

                <div class="account-profile-detail">
                    <span>Atasan Langsung</span>
                    <strong>{{ $atasan }}</strong>
                </div>
            </div>

            <div class="account-profile-notice">
                <i class="fa-solid fa-circle-info"></i>
                <span>Informasi kepegawaian di atas hanya dapat diubah oleh Master.</span>
            </div>
        </section>

        <section class="account-profile-card">
            <div class="account-profile-card-heading">
                <div class="account-profile-card-heading-left">
                    <span class="account-profile-heading-icon">
                        <i class="fa-solid fa-user"></i>
                    </span>

                    <div>
                        <h3>Informasi Akun</h3>
                        <p>Kelola identitas akun dan kata sandi Anda</p>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('profile.update') }}" class="account-profile-form">
                @csrf
                @method('PUT')

                <div class="account-profile-form-group">
                    <label for="profileNip">Nomor Induk Pegawai (NIP)</label>
                    <div class="account-profile-input-wrap">
                        <input type="text"
                            id="profileNip"
                            value="{{ $nip }}"
                            readonly>
                        <i class="fa-regular fa-id-card"></i>
                    </div>
                </div>

                <div class="account-profile-form-group">
                    <label for="profileName">Nama Lengkap</label>
                    <div class="account-profile-input-wrap">
                        <input type="text"
                            name="name"
                            id="profileName"
                            value="{{ old('name', $user->name) }}"
                            maxlength="255"
                            autocomplete="name"
                            required>
                        <i class="fa-solid fa-pencil"></i>
                    </div>
                    @error('name')
                        <small class="account-profile-error">{{ $message }}</small>
                    @enderror
                </div>

                <div class="account-profile-form-group">
                    <label for="profileEmail">Alamat Email</label>
                    <div class="account-profile-input-wrap">
                        <input type="email"
                            name="email"
                            id="profileEmail"
                            value="{{ old('email', $user->email) }}"
                            maxlength="255"
                            autocomplete="email"
                            required>
                        <i class="fa-solid fa-circle-check account-profile-valid-icon"></i>
                    </div>
                    @error('email')
                        <small class="account-profile-error">{{ $message }}</small>
                    @enderror
                </div>

                <div class="account-profile-form-group">
                    <label for="profilePassword">Password Baru</label>
                    <div class="account-profile-input-wrap">
                        <input type="password"
                            name="password"
                            id="profilePassword"
                            placeholder="Kosongkan jika tidak ingin mengubah"
                            autocomplete="new-password">
                        <button type="button"
                            class="account-profile-password-toggle"
                            data-password-target="profilePassword"
                            aria-label="Tampilkan password">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                    @error('password')
                        <small class="account-profile-error">{{ $message }}</small>
                    @enderror
                </div>

                <div class="account-profile-form-group">
                    <label for="profilePasswordConfirmation">Konfirmasi Password Baru</label>
                    <div class="account-profile-input-wrap">
                        <input type="password"
                            name="password_confirmation"
                            id="profilePasswordConfirmation"
                            placeholder="Ketik ulang password baru..."
                            autocomplete="new-password">
                        <button type="button"
                            class="account-profile-password-toggle"
                            data-password-target="profilePasswordConfirmation"
                            aria-label="Tampilkan konfirmasi password">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="account-profile-save">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Simpan
                </button>
            </form>
        </section>

        <form method="POST"
            action="{{ route('profile.photo.update') }}"
            enctype="multipart/form-data"
            id="profilePhotoForm"
            hidden>
            @csrf
            <input type="file"
                name="foto"
                id="profilePhotoInput"
                accept="image/jpeg,image/png,image/webp">
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const photoButton = document.getElementById('profilePhotoButton');
            const photoInput = document.getElementById('profilePhotoInput');
            const photoForm = document.getElementById('profilePhotoForm');

            photoButton?.addEventListener('click', function () {
                photoInput?.click();
            });

            photoInput?.addEventListener('change', function () {
                if (!this.files.length) {
                    return;
                }

                if (this.files[0].size > 2 * 1024 * 1024) {
                    alert('Ukuran foto maksimal 2 MB.');
                    this.value = '';
                    return;
                }

                photoForm?.requestSubmit();
            });

            document.querySelectorAll('.account-profile-password-toggle').forEach(function (button) {
                button.addEventListener('click', function () {
                    const input = document.getElementById(this.dataset.passwordTarget);

                    if (!input) {
                        return;
                    }

                    const visible = input.type === 'password';
                    input.type = visible ? 'text' : 'password';

                    const icon = this.querySelector('i');

                    icon?.classList.toggle('fa-eye', !visible);
                    icon?.classList.toggle('fa-eye-slash', visible);

                    this.setAttribute(
                        'aria-label',
                        visible ? 'Sembunyikan password' : 'Tampilkan password'
                    );
                });
            });
        });
    </script>
@endpush
