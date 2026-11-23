@extends('layouts.app')
@section('title', 'Tambah Jabatan')
@section('content')
<div class="mj-page mj-form-page">
    <header class="mj-heading"><div><nav class="mj-breadcrumb"><a href="{{ route('master.dashboard') }}">Dashboard</a><i class="fa-solid fa-chevron-right"></i><a href="{{ route('master.jabatan.index') }}">Jabatan</a><i class="fa-solid fa-chevron-right"></i><span>Tambah Jabatan</span></nav><h1>Tambah Jabatan</h1><p>Tambahkan posisi baru ke struktur organisasi.</p></div><a class="mj-btn mj-btn-outline" href="{{ route('master.jabatan.index') }}"><i class="fa-solid fa-arrow-left"></i> Kembali</a></header>
    <section class="mj-panel mj-form-panel"><div class="mj-panel-header"><span class="mj-form-icon"><i class="fa-solid fa-briefcase"></i></span><div><h2>Formulir Jabatan</h2><p>Lengkapi seluruh kolom bertanda wajib.</p></div><span class="mj-chip">Mode Tambah</span></div>
        <form method="POST" action="{{ route('master.jabatan.store') }}" class="mj-form">@csrf
            <div class="mj-field"><label for="divisi_id">Divisi <em>*</em></label><select id="divisi_id" name="divisi_id" required class="@error('divisi_id') is-invalid @enderror"><option value="">Pilih Divisi</option>@foreach($divisis as $divisi)<option value="{{ $divisi->id }}" @selected((string) old('divisi_id') === (string) $divisi->id)>{{ $divisi->nama_divisi }}</option>@endforeach</select>@error('divisi_id')<span class="mj-error">{{ $message }}</span>@enderror</div>
            <div class="mj-field"><label for="nama_jabatan">Nama Jabatan <em>*</em></label><input id="nama_jabatan" name="nama_jabatan" type="text" maxlength="255" required value="{{ old('nama_jabatan') }}" placeholder="Contoh: Pelaksana Pembaca Meter" class="@error('nama_jabatan') is-invalid @enderror">@error('nama_jabatan')<span class="mj-error">{{ $message }}</span>@enderror</div>
            <div class="mj-form-grid"><div class="mj-field"><label for="grade">Grade <em>*</em></label><input id="grade" name="grade" type="text" maxlength="50" required value="{{ old('grade') }}" placeholder="Contoh: 2" class="@error('grade') is-invalid @enderror">@error('grade')<span class="mj-error">{{ $message }}</span>@enderror</div>
            <div class="mj-field"><label for="base_tukin">Base Tukin (Rp) <em>*</em></label><input id="base_tukin" name="base_tukin" type="number" min="0" step="any" required value="{{ old('base_tukin') }}" placeholder="Contoh: 2000000" class="@error('base_tukin') is-invalid @enderror">@error('base_tukin')<span class="mj-error">{{ $message }}</span>@enderror</div></div>
            <div class="mj-form-actions"><a class="mj-btn mj-btn-outline" href="{{ route('master.jabatan.index') }}">Batal</a><button type="submit" class="mj-btn mj-btn-primary"><i class="fa-regular fa-floppy-disk"></i> Simpan Jabatan</button></div>
        </form>
    </section>
    <aside class="mj-info"><i class="fa-solid fa-circle-info"></i><span>Jabatan terhubung dengan divisi, pegawai, dan pengaturan penilaian KPI.</span></aside>
</div>
@endsection
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/master/master-jabatan.css') }}?v={{ filemtime(public_path('css/master/master-jabatan.css')) }}">
@endpush
