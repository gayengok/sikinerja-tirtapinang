@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')
    <div class="pegawai-page profile-page">

        <section class="profile-hero">
            <div class="profile-avatar">{{ strtoupper(substr($pegawai->user->name ?? 'P', 0, 1)) }}<span><i
                        class="fa-solid fa-camera"></i></span></div>
            <h1>{{ $pegawai->user->name ?? '-' }} <small>Pegawai</small></h1>
            <p>{{ $pegawai->jabatan->nama_jabatan ?? '-' }}</p>
            <span>{{ $pegawai->jabatan->divisi->nama_divisi ?? '-' }} • PDAM Tirta Pinang</span>
            <div class="profile-hero-meta">
                <b><i class="fa-solid fa-circle"></i> {{ ucfirst($pegawai->status ?? 'aktif') }}</b>
                <b>ID: EMP-{{ $pegawai->nip ?? '-' }}</b>
                <b>Masa Kerja: Aktif</b>
            </div>
        </section>

        <section class="profile-card">
            <div class="profile-card-heading"><i class="fa-solid fa-building"></i><strong>Informasi
                    Kepegawaian</strong><span><i class="fa-solid fa-circle-check"></i> Data Terverifikasi</span></div>
            <div class="profile-data-list">
                <div><label>Jabatan</label><strong>{{ $pegawai->jabatan->nama_jabatan ?? '-' }}</strong></div>
                <div><label>Divisi / Unit</label><strong>{{ $pegawai->jabatan->divisi->nama_divisi ?? '-' }}</strong></div>
                <div><label>Tanggal
                        Masuk</label><strong>{{ $pegawai->tanggal_masuk ? \Carbon\Carbon::parse($pegawai->tanggal_masuk)->translatedFormat('d F Y') : '-' }}</strong>
                </div>
                <div><label>Status Pegawai</label><strong class="profile-active">●
                        {{ ucfirst($pegawai->status ?? 'aktif') }}</strong></div>
                <div><label>Atasan Langsung</label><strong>{{ $pegawai->atasan->nama ?? '-' }}</strong></div>
            </div>
            <div class="profile-info-note"><i class="fa-solid fa-circle-info"></i> Informasi kepegawaian di atas hanya dapat
                diubah oleh Master.</div>
        </section>

        <section class="profile-card">
            <div class="profile-card-heading"><i class="fa-solid fa-user"></i>
                <div><strong>Informasi Akun</strong><small>Kelola identitas akun dan kata sandi Anda</small></div>
            </div>
            <form action="{{ route('pegawai.profile.update') }}" method="POST" class="profile-form">
                @csrf
                @method('PUT')
                <label for="name">Nama Lengkap</label>
                <div class="profile-input-wrap"><input type="text" name="name" id="name"
                        value="{{ old('name', $pegawai->user->name ?? '') }}"><i class="fa-solid fa-pen"></i></div>
                @error('name')
                    <div class="text-danger small">{{ $message }}</div>
                @enderror
                <label for="email">Alamat Email</label>
                <div class="profile-input-wrap"><input type="email" name="email" id="email"
                        value="{{ old('email', $pegawai->user->email ?? '') }}"><i
                        class="fa-solid fa-circle-check valid"></i></div>
                @error('email')
                    <div class="text-danger small">{{ $message }}</div>
                @enderror
                <label for="password">Password Baru</label>
                <div class="profile-input-wrap"><input type="password" name="password" id="password"
                        placeholder="Kosongkan jika tidak ingin mengubah"><i class="fa-regular fa-eye"></i></div>
                @error('password')
                    <div class="text-danger small">{{ $message }}</div>
                @enderror
                <label for="password_confirmation">Konfirmasi Password Baru</label>
                <div class="profile-input-wrap"><input type="password" name="password_confirmation"
                        id="password_confirmation" placeholder="Ketik ulang password baru..."><i
                        class="fa-regular fa-eye-slash"></i></div>
                <button type="submit"><i class="fa-solid fa-floppy-disk"></i> Simpan</button>
            </form>
        </section>

        <section class="profile-options-card">
            <a href="#"><i class="fa-regular fa-bell"></i><span><strong>Notifikasi & Preferensi
                        Presensi</strong><small>Pengingat check-in & batas waktu laporan</small></span><i
                    class="fa-solid fa-chevron-right"></i></a>
            <a href="#"><i class="fa-regular fa-file-lines"></i><span><strong>Dokumen & SK Kepegawaian
                        (PDF)</strong><small>Arsip digital SK & Riwayat Golongan</small></span><i
                    class="fa-solid fa-chevron-right"></i></a>

        </section>
    </div>
@endsection
