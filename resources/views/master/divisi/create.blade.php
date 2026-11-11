@extends('layouts.app')

@section('title', 'Tambah Divisi')

@section('content')
    <div class="master-divisi-create">
        <header class="master-divisi-create-heading">
            <nav class="master-divisi-create-breadcrumb" aria-label="Breadcrumb">
                <a href="{{ route('master.dashboard') }}">Beranda</a>
                <i class="fa-solid fa-chevron-right"></i>
                <a href="{{ route('master.divisi.index') }}">Master Data</a>
                <i class="fa-solid fa-chevron-right"></i>
                <a href="{{ route('master.divisi.index') }}">Divisi</a>
                <i class="fa-solid fa-chevron-right"></i>
                <span>Tambah Divisi</span>
            </nav>
            <h1>
                <i class="fa-solid fa-building"></i>
                Tambah Divisi
            </h1>

            <p>Tambahkan unit atau seksi kerja baru ke dalam bagan struktur organisasi SIKINERJA.</p>
        </header>


        <section class="master-divisi-form-card">
            <div class="master-divisi-form-header"><span><i class="fa-solid fa-building"></i></span>
                <div>
                    <h2>Formulir Divisi Baru</h2>
                    <p>Pastikan data yang diisi telah valid</p>
                </div>
                <span class="master-divisi-form-type">Form Input</span>
            </div>
            <form action="{{ route('master.divisi.store') }}" method="POST">@csrf<div class="master-divisi-field"><label
                        for="nama_divisi">Nama Divisi <em>*</em></label><input type="text" name="nama_divisi"
                        id="nama_divisi" value="{{ old('nama_divisi') }}" placeholder="Contoh: Seksi Teknik"
                        class="@error('nama_divisi') invalid @enderror"><small><i class="fa-regular fa-circle-question"></i>
                        Gunakan nama resmi unit kerja sesuai SK Direksi.</small>
                    @error('nama_divisi')
                        <b class="master-divisi-error">{{ $message }}</b>
                    @enderror
                </div>
                <div class="master-divisi-form-actions"><button type="submit"><i class="fa-regular fa-floppy-disk"></i>
                        Simpan</button><a href="{{ route('master.divisi.index') }}">Batal</a></div>
            </form>
        </section>
        <aside class="master-divisi-tip"><span><i class="fa-solid fa-bolt"></i></span>
            <div><strong>Tips Pengaturan Struktur</strong>
                <p>Setelah disimpan, Anda dapat langsung menambahkan jabatan dan memetakan pegawai pada divisi baru ini
                    melalui menu <a href="{{ route('master.pegawai.index') }}">Pegawai</a>.</p>
            </div>
        </aside>
    </div>
@endsection

@push('styles')
    <style>
        body.role-master .master-divisi-create {
            width: 100%;
            max-width: 560px;
            margin: auto;
            padding: 1rem 1rem 0;
            box-sizing: border-box;
            color: #14223d
        }

        .master-divisi-create-breadcrumb,
        .master-divisi-form-type {
            display: none;
        }

        .master-divisi-create-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .6rem;
            margin: 0 -1rem;
            padding: .9rem 1rem;
            background: #fff;
            border-bottom: 1px solid #e3ebf3
        }

        .master-divisi-create-topbar>a {
            order: 2;
            padding: .62rem .8rem;
            color: #526480;
            border: 1px solid #dce5ee;
            border-radius: 11px;
            text-decoration: none;
            font-size: .76rem;
            font-weight: 600;
            white-space: nowrap
        }

        .master-divisi-create-crumb {
            display: flex;
            align-items: center;
            gap: .5rem;
            color: #0875ad;
            font-size: .68rem;
            white-space: nowrap
        }

        .master-divisi-create-crumb a {
            color: #91a1b8;
            text-decoration: none
        }

        .master-divisi-create-crumb i {
            font-size: .48rem;
            color: #9caabd
        }

        .master-divisi-create-heading {
            padding: 1.4rem 0 1.2rem
        }

        .master-divisi-create-heading h1 {
            margin: 0;
            font-size: 1rem
        }

        .master-divisi-create-heading p {
            max-width: 480px;
            margin: .4rem 0 0;
            color: #647895;
            font-size: .82rem;
            line-height: 1.55
        }

        .master-divisi-form-card {
            overflow: hidden;
            background: #fff;
            border: 1px solid #dfe8f1;
            border-radius: 19px;
            box-shadow: 0 5px 17px #2334550b
        }

        .master-divisi-form-header {
            display: flex;
            align-items: center;
            gap: .8rem;
            padding: 1.15rem 1.2rem;
            border-bottom: 1px solid #e8eef5;
            background: #fbfcfe
        }

        .master-divisi-form-header>span {
            display: grid;
            place-items: center;
            width: 43px;
            height: 43px;
            flex: 0 0 43px;
            color: #078dcc;
            background: #e8f7ff;
            border-radius: 13px;
            font-size: 1.1rem
        }

        .master-divisi-form-header h2 {
            margin: 0;
            font-size: 1rem
        }

        .master-divisi-form-header p {
            margin: .25rem 0 0;
            color: #91a1b8;
            font-size: .73rem
        }

        .master-divisi-form-card form {
            padding: 1.5rem 1.2rem 1.2rem
        }

        .master-divisi-field label {
            display: block;
            margin-bottom: .45rem;
            font-size: .8rem;
            font-weight: 600
        }

        .master-divisi-field label em {
            color: #e54862;
            font-style: normal
        }

        .master-divisi-field input {
            box-sizing: border-box;
            width: 100%;
            height: 50px;
            padding: 0 1rem;
            color: #263653;
            background: #fff;
            border: 1px solid #cbd9e7;
            border-radius: 15px;
            outline: 0;
            font: inherit;
            font-size: .82rem
        }

        .master-divisi-field input:focus {
            border-color: #078dcc;
            box-shadow: 0 0 0 3px #078dcc18
        }

        .master-divisi-field input.invalid {
            border-color: #e54862
        }

        .master-divisi-field input::placeholder {
            color: #94a6be
        }

        .master-divisi-field>small {
            display: block;
            margin-top: .55rem;
            color: #91a1b8;
            font-size: .68rem;
            line-height: 1.4
        }

        .master-divisi-field>small i {
            margin-right: .25rem;
            color: #7c91aa
        }

        .master-divisi-error {
            display: block;
            margin-top: .35rem;
            color: #e54862;
            font-size: .68rem;
            font-weight: 500
        }

        .master-divisi-form-actions {
            display: grid;
            gap: .55rem;
            margin-top: 1.55rem
        }

        .master-divisi-form-actions button,
        .master-divisi-form-actions a {
            box-sizing: border-box;
            width: 100%;
            padding: .86rem;
            text-align: center;
            border-radius: 13px;
            font: inherit;
            font-size: .83rem;
            text-decoration: none
        }

        .master-divisi-form-actions button {
            color: #fff;
            background: #078dcc;
            border: 0;
            box-shadow: 0 4px 9px #078dcc35;
            font-weight: 600
        }

        .master-divisi-form-actions a {
            color: #526480;
            background: #fff;
            border: 1px solid #cbd9e7;
            font-weight: 600
        }

        .master-divisi-tip {
            display: flex;
            gap: .75rem;
            margin-top: 1rem;
            padding: 1.1rem 1rem;
            color: #0875ad;
            background: #eaf7ff;
            border: 1px solid #c8eaff;
            border-radius: 17px
        }

        .master-divisi-tip>span {
            display: grid;
            place-items: center;
            width: 34px;
            height: 34px;
            flex: 0 0 34px;
            color: #fff;
            background: #078dcc;
            border-radius: 9px
        }

        .master-divisi-tip strong {
            display: block;
            margin: .1rem 0 .3rem;
            color: #07527c;
            font-size: .82rem
        }

        .master-divisi-tip p {
            margin: 0;
            color: #0875ad;
            font-size: .73rem;
            line-height: 1.5
        }

        .master-divisi-tip a {
            color: #07527c;
            font-weight: 600
        }

        @media(max-width:380px) {
            body.role-master .master-divisi-create {
                padding-inline: .75rem
            }

            .master-divisi-create-topbar {
                margin-inline: -.75rem;
                padding-inline: .75rem
            }

            .master-divisi-create-crumb a,
            .master-divisi-create-crumb i {
                display: none
            }

            .master-divisi-create-heading {
                padding-top: 1.15rem
            }

            .master-divisi-create-heading h1 {
                font-size: 1.3rem
            }

            .master-divisi-create-heading p {
                font-size: .74rem
            }

            .master-divisi-form-header,
            .master-divisi-form-card form {
                padding-inline: 1rem
            }
        }

        @media (min-width: 768px) {
            body.role-master .master-divisi-create {
                width: 100%;
                max-width: none;
                margin: 0;
                padding: 1rem clamp(1.25rem, 2.4vw, 2.5rem) 2rem;
                color: #1b2938;
            }

            .master-divisi-create-heading {
                padding: .5rem 0 1.1rem;
            }

            .master-divisi-create-breadcrumb {
                display: flex;
                align-items: center;
                gap: .4rem;
                margin-bottom: .45rem;
                color: #718092;
                font-size: .64rem;
            }

            .master-divisi-create-breadcrumb a {
                color: #718092;
                text-decoration: none;
            }

            .master-divisi-create-breadcrumb a:hover,
            .master-divisi-create-breadcrumb>span {
                color: #1769d2;
            }

            .master-divisi-create-breadcrumb i {
                color: #a0acb9;
                font-size: .48rem;
            }

            .master-divisi-create-heading h1 {
                color: #1b2938;
                font-size: 1.4rem;
                font-weight: 700;
            }

            .master-divisi-create-heading h1 i {
                color: #087c67;
                font-size: 1rem;
            }

            .master-divisi-create-heading p {
                margin-top: .25rem;
                font-size: .75rem;
            }

            .master-divisi-form-card {
                width: 100%;
                max-width: 1120px;
                margin-right: auto;
                margin-left: auto;
                border-color: #e2e8ef;
                border-radius: 8px;
                box-shadow: none;
            }

            .master-divisi-form-header {
                gap: .65rem;
                padding: .85rem 1rem;
                background: #f9fbfd;
            }

            .master-divisi-form-header>span {
                width: 34px;
                height: 34px;
                flex-basis: 34px;
                border-radius: 6px;
                font-size: .85rem;
            }

            .master-divisi-form-header h2 {
                color: #25364a;
                font-size: .8rem;
            }

            .master-divisi-form-header p {
                font-size: .63rem;
            }

            .master-divisi-form-header>.master-divisi-form-type {
                display: inline-flex;
                width: auto;
                height: auto;
                flex: 0 0 auto;
                align-items: center;
                margin-left: auto;
                padding: .28rem .45rem;
                color: #718092;
                background: #fff;
                border: 1px solid #e2e8ef;
                border-radius: 4px;
                font-size: .58rem;
                font-weight: 400;
                line-height: 1;
                white-space: nowrap;
            }

            .master-divisi-form-card form {
                width: 100%;
                max-width: none;
                padding: 1rem;
            }

            .master-divisi-field label {
                margin-bottom: .35rem;
                color: #34445e;
                font-size: .68rem;
            }

            .master-divisi-field input {
                height: 40px;
                border-radius: 6px;
                font-size: .7rem;
            }

            .master-divisi-field>small {
                margin-top: .4rem;
                font-size: .62rem;
            }

            .master-divisi-form-actions {
                display: flex;
                justify-content: flex-end;
                gap: .5rem;
                margin-top: 1rem;
                padding-top: .75rem;
                border-top: 1px solid #edf1f5;
            }

            .master-divisi-form-actions button,
            .master-divisi-form-actions a {
                width: auto;
                min-width: 95px;
                padding: .58rem .8rem;
                border-radius: 5px;
                font-size: .68rem;
            }

            .master-divisi-form-actions button {
                order: 2;
                background: #078dcc;
                box-shadow: none;
            }

            .master-divisi-form-actions a {
                order: 1;
            }

            .master-divisi-tip {
                max-width: 1120px;
                gap: .55rem;
                margin-top: .8rem;
                padding: .75rem .85rem;
                border-radius: 6px;
            }

            .master-divisi-tip>span {
                width: 28px;
                height: 28px;
                flex-basis: 28px;
                border-radius: 5px;
            }

            .master-divisi-tip strong {
                font-size: .7rem;
            }

            .master-divisi-tip p {
                font-size: .63rem;
            }
        }
    </style>
@endpush
