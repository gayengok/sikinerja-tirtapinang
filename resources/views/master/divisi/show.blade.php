@extends('layouts.app')

@section('title', 'Detail Divisi')

@section('content')
    <div class="master-divisi-show">

        <header class="master-divisi-show-heading">
            <div>
                <h1>
                    <i class="fa-solid fa-building"></i>
                    Detail Divisi
                </h1>
                <p>Informasi unit kerja, jabatan, dan struktur organisasi divisi.</p>
            </div>

            <span>
                <i class="fa-solid fa-circle"></i>
                Data Terdaftar
            </span>
        </header>

        <section class="master-divisi-info-card">
            <div class="master-divisi-info-title"><span><i class="fa-solid fa-building"></i></span>
                <h2>Informasi Divisi</h2><b><i class="fa-solid fa-circle"></i> Terdaftar</b>
            </div>
            <div class="master-divisi-info-rows">
                <div><span>Nama Divisi</span><strong>{{ $divisi->nama_divisi }}</strong></div>
                <div><span>Jumlah Jabatan</span><strong>{{ $divisi->jabatans->count() }} Jabatan</strong></div>
                <div><span>Jumlah Template Laporan</span><strong>{{ $divisi->templateLaporans->count() }} Template</strong>
                </div>
                <div><span>Dibuat</span><strong><i class="fa-regular fa-calendar"></i>
                        {{ \Carbon\Carbon::parse($divisi->created_at)->translatedFormat('d F Y') }}</strong></div>
            </div>
        </section>
        <div class="master-divisi-jabatan-heading">
            <h2>Daftar Jabatan pada Divisi Ini <span>{{ $divisi->jabatans->count() }}</span></h2>
        </div>
        <div class="master-divisi-jabatan-list">
            @forelse($divisi->jabatans as $jabatan)
                <article class="master-divisi-jabatan-card">
                    <div class="master-divisi-jabatan-main"><span>{{ $loop->iteration }}</span>
                        <div>
                            <h3>{{ $jabatan->nama_jabatan }}</h3>
                            <p>{{ $jabatan->kategori ?? 'Struktural / Fungsional' }}</p>
                        </div><b>Grade {{ $jabatan->grade }}</b>
                    </div>
                    <div class="master-divisi-jabatan-footer"><span>Base Tukin Bulanan</span><strong>Rp
                            {{ number_format($jabatan->base_tukin, 0, ',', '.') }}</strong></div>
            </article>@empty<div class="master-divisi-empty"><i class="fa-regular fa-folder-open"></i><strong>Belum ada
                        jabatan pada divisi ini.</strong></div>
            @endforelse
        </div>
        <a class="master-divisi-all-pegawai" href="{{ route('master.pegawai.index') }}"><i class="fa-solid fa-users"></i>
            Lihat Seluruh Pegawai di Divisi Ini</a>
        <div class="master-divisi-bottom-action">
            <a href="{{ route('master.divisi.index') }}">
                <i class="fa-solid fa-arrow-left"></i>
                Kembali
            </a>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/master/divisi-unified.css') }}?v={{ filemtime(public_path('css/master/divisi-unified.css')) }}">
@endpush
