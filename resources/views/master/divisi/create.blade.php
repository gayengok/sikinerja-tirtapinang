@extends('layouts.app')
@section('title', 'Tambah Divisi')
@section('content')
<div class="master-divisi-create">
    <header class="master-divisi-create-heading"><div><nav class="master-divisi-create-breadcrumb" aria-label="Breadcrumb"><a href="{{ route('master.dashboard') }}">Beranda</a><i class="fa-solid fa-chevron-right"></i><a href="{{ route('master.divisi.index') }}">Divisi</a><i class="fa-solid fa-chevron-right"></i><span>Tambah Divisi</span></nav><h1><i class="fa-solid fa-building"></i> Tambah Divisi</h1><p>Tambahkan unit atau seksi kerja baru ke dalam struktur organisasi SIKINERJA.</p></div></header>
    <section class="master-divisi-form-card"><div class="master-divisi-form-header"><span><i class="fa-solid fa-building"></i></span><div><h2>Formulir Divisi Baru</h2><p>Pastikan data yang diisi telah valid</p></div><span class="master-divisi-form-type">Form Input</span></div>
        <form action="{{ route('master.divisi.store') }}" method="POST">@csrf
            <div class="master-divisi-field"><label for="nama_divisi">Nama Divisi <em>*</em></label><input type="text" name="nama_divisi" id="nama_divisi" value="{{ old('nama_divisi') }}" placeholder="Contoh: Seksi Teknik" required class="@error('nama_divisi') invalid @enderror"><small><i class="fa-regular fa-circle-question"></i> Gunakan nama resmi unit kerja sesuai SK Direksi.</small>@error('nama_divisi')<b class="master-divisi-error">{{ $message }}</b>@enderror</div>
            <div class="master-divisi-form-actions"><button type="submit"><i class="fa-regular fa-floppy-disk"></i> Simpan Divisi</button><a href="{{ route('master.divisi.index') }}">Batal</a></div>
        </form></section>
    <aside class="master-divisi-tip"><span><i class="fa-solid fa-bolt"></i></span><div><strong>Tips Pengaturan Struktur</strong><p>Setelah disimpan, Anda dapat menambahkan jabatan dan memetakan pegawai melalui menu <a href="{{ route('master.pegawai.index') }}">Pegawai</a>.</p></div></aside>
</div>
@endsection
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/master/divisi-unified.css') }}?v={{ filemtime(public_path('css/master/divisi-unified.css')) }}">
@endpush
