@extends('layouts.app')

@section('title', 'Tambah Jam Kerja')

@section('content')
    <header class="master-jam-create-heading">
        <div>
            <nav class="master-jam-create-breadcrumb" aria-label="Breadcrumb">
                <a href="{{ route('master.dashboard') }}">Beranda</a>
                <i class="fa-solid fa-chevron-right"></i>
                <a href="{{ route('master.jam-kerja.index') }}">Jam Kerja</a>
                <i class="fa-solid fa-chevron-right"></i>
                <span>Tambah</span>
            </nav>
            <h1><i class="fa-regular fa-clock" aria-hidden="true"></i> Tambah Jam Kerja</h1>
            <p class="master-jam-create-subtitle">Atur jadwal jam masuk, pulang, dan batas toleransi keterlambatan presensi.
            </p>
        </div>
    </header>

    <div class="card border-0 shadow-sm master-jam-create-card">
        <div class="master-jam-create-card-header">
            <span><i class="fa-regular fa-clock"></i></span>
            <div><strong>FORMULIR PENGATURAN</strong><small>Lengkapi jadwal kerja dengan teliti</small></div>
            <b>Mode Tambah</b>
        </div>
        <div class="card-body">
            <form action="{{ route('master.jam-kerja.store') }}" method="POST" class="master-jam-create-form">
                @csrf

                <div class="mb-3">
                    <label for="jabatan_id" class="form-label">Jabatan (Opsional)</label>
                    <select name="jabatan_id" id="jabatan_id" class="form-select @error('jabatan_id') is-invalid @enderror">
                        <option value="">Semua Jabatan</option>
                        @foreach ($jabatans as $jabatan)
                            <option value="{{ $jabatan->id }}" {{ old('jabatan_id') == $jabatan->id ? 'selected' : '' }}>
                                {{ $jabatan->nama_jabatan }}
                            </option>
                        @endforeach
                    </select>
                    @error('jabatan_id')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="hari" class="form-label">Hari</label>
                    <select name="hari" id="hari" class="form-select @error('hari') is-invalid @enderror">
                        <option value="">-- Pilih Hari --</option>
                        @foreach ($hariOptions as $hari)
                            <option value="{{ $hari }}" {{ old('hari') === $hari ? 'selected' : '' }}>
                                {{ $hari }}</option>
                        @endforeach
                    </select>
                    @error('hari')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="jam_masuk" class="form-label">Jam Masuk</label>
                        <input type="time" name="jam_masuk" id="jam_masuk"
                            class="form-control @error('jam_masuk') is-invalid @enderror" value="{{ old('jam_masuk') }}">
                        @error('jam_masuk')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="jam_pulang" class="form-label">Jam Pulang</label>
                        <input type="time" name="jam_pulang" id="jam_pulang"
                            class="form-control @error('jam_pulang') is-invalid @enderror" value="{{ old('jam_pulang') }}">
                        @error('jam_pulang')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="toleransi_menit" class="form-label">Toleransi (menit)</label>
                        <input type="number" min="0" name="toleransi_menit" id="toleransi_menit"
                            class="form-control @error('toleransi_menit') is-invalid @enderror"
                            value="{{ old('toleransi_menit', 15) }}">
                        @error('toleransi_menit')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Simpan
                </button>
                <a href="{{ route('master.jam-kerja.index') }}" class="btn btn-outline-secondary">Batal</a>
            </form>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .master-jam-create-subtitle,
        .master-jam-create-card-header {
            display: none;
        }

        .master-jam-create-breadcrumb {
            display: none;
        }

        @media (min-width: 768px) {
            body.role-master .main-content>.master-jam-create-heading {
                display: block;
                padding: 1.25rem clamp(1.25rem, 2.4vw, 2.5rem) .9rem;
                margin: 0;
                background: transparent;
                border: 0;
            }

            .master-jam-create-heading {
                display: block;
                margin: 0;
                padding: 1.25rem clamp(1.25rem, 2.4vw, 2.5rem) .9rem;
                background: transparent;
                border: 0;
            }

            .master-jam-create-breadcrumb {
                display: flex;
                align-items: center;
                gap: .4rem;
                margin-bottom: .4rem;
                color: #718092;
                font-size: .64rem;
            }

            .master-jam-create-breadcrumb a {
                color: #718092;
                text-decoration: none;
            }

            .master-jam-create-breadcrumb a:hover,
            .master-jam-create-breadcrumb>span {
                color: #1769d2;
            }

            .master-jam-create-breadcrumb i {
                color: #a0acb9;
                font-size: .48rem;
            }

            .master-jam-create-heading h1 {
                display: flex;
                align-items: center;
                gap: .55rem;
                margin: 0;
                color: #1b2938;
                font-size: 1.35rem;
                font-weight: 700;
            }

            .master-jam-create-heading h1 i {
                display: grid;
                place-items: center;
                width: 30px;
                height: 30px;
                color: #078dcc;
                background: #e8f7ff;
                border-radius: 6px;
                font-size: .82rem;
            }

            .master-jam-create-subtitle {
                display: block;
                margin: .2rem 0 0;
                color: #718092;
                font-size: .72rem;
            }

            .master-jam-create-card {
                width: calc(100% - clamp(2.5rem, 4.8vw, 5rem));
                max-width: 1120px;
                margin: 0 auto;
                overflow: hidden;
                border: 1px solid #e2e8ef !important;
                border-radius: 8px;
                box-shadow: none !important;
            }

            .master-jam-create-card-header {
                display: flex;
                align-items: center;
                gap: .6rem;
                padding: .8rem 1rem;
                background: #f9fbfd;
                border-bottom: 1px solid #e8eef5;
            }

            .master-jam-create-card-header>span {
                display: grid;
                place-items: center;
                width: 32px;
                height: 32px;
                flex: 0 0 32px;
                color: #078dcc;
                background: #e8f7ff;
                border-radius: 6px;
            }

            .master-jam-create-card-header>div strong,
            .master-jam-create-card-header>div small {
                display: block;
            }

            .master-jam-create-card-header>div strong {
                color: #25364a;
                font-size: .76rem;
            }

            .master-jam-create-card-header>div small {
                margin-top: .12rem;
                color: #8290a0;
                font-size: .6rem;
            }

            .master-jam-create-card-header>b {
                margin-left: auto;
                padding: .25rem .4rem;
                color: #087b5d;
                background: #ecfff5;
                border: 1px solid #b8efd1;
                border-radius: 999px;
                font-size: .56rem;
            }

            .master-jam-create-card .card-body {
                padding: 1rem;
            }

            .master-jam-create-form .form-label {
                margin-bottom: .32rem;
                color: #34445e;
                font-size: .66rem;
            }

            .master-jam-create-form .form-control,
            .master-jam-create-form .form-select {
                min-height: 38px;
                border-color: #d6e1ed;
                border-radius: 5px;
                font-size: .68rem;
            }

            .master-jam-create-form .row {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: .8rem;
                margin: 0;
            }

            .master-jam-create-form .row>* {
                width: auto;
                max-width: none;
                padding: 0;
            }

            .master-jam-create-form>.btn,
            .master-jam-create-form>a {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-width: 95px;
                min-height: 38px;
                margin-top: .75rem;
                border-radius: 5px;
                font-size: .68rem;
            }

            .master-jam-create-form>.btn {
                float: right;
                background: #078dcc;
                border-color: #078dcc;
                box-shadow: none;
            }

            .master-jam-create-form>a {
                float: right;
                margin-right: .5rem;
            }
        }
    </style>
@endpush
