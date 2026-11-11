@extends('layouts.app')

@section('title', 'Edit Jabatan')

@section('content')
    <div class="master-jabatan-edit">
        <header class="master-jabatan-edit-heading">
            <nav class="master-jabatan-edit-breadcrumb" aria-label="Breadcrumb">
                <a href="{{ route('master.dashboard') }}">Beranda</a>
                <i class="fa-solid fa-chevron-right"></i>
                <a href="{{ route('master.jabatan.index') }}">Master Data</a>
                <i class="fa-solid fa-chevron-right"></i>
                <span>Edit Jabatan</span>
            </nav>
            <h1>
                <i class="fa-solid fa-pen-to-square"></i>
                Edit Jabatan
            </h1>
            <p>Perbarui data informasi dan konfigurasi jabatan</p>
        </header>

        <section class="master-jabatan-edit-card">
            <div class="master-jabatan-edit-card-header">
                <span><i class="fa-solid fa-pen-to-square"></i></span>
                <div>
                    <h2>Formulir Master Jabatan</h2>
                    <p>Perbarui data jabatan dan konfigurasi tunjangan</p>
                </div>
                <b>Mode Edit</b>
            </div>
            <form action="{{ route('master.jabatan.update', $jabatan) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="master-jabatan-edit-field">
                    <label for="divisi_id">DIVISI <em>*</em></label>
                    <div class="master-jabatan-edit-select">
                        <select name="divisi_id" id="divisi_id" class="@error('divisi_id') invalid @enderror">
                            <option value="">-- Pilih Divisi --</option>
                            @foreach ($divisis as $divisi)
                                <option value="{{ $divisi->id }}"
                                    {{ old('divisi_id', $jabatan->divisi_id) == $divisi->id ? 'selected' : '' }}>
                                    {{ $divisi->nama_divisi }}
                                </option>
                            @endforeach
                        </select>
                        <i class="fa-solid fa-chevron-down"></i>
                    </div>
                    @error('divisi_id')
                        <b class="master-jabatan-edit-error">{{ $message }}</b>
                    @enderror
                </div>

                <div class="master-jabatan-edit-field">
                    <label for="nama_jabatan">NAMA JABATAN <em>*</em></label>
                    <input type="text" name="nama_jabatan" id="nama_jabatan"
                        class="@error('nama_jabatan') invalid @enderror"
                        value="{{ old('nama_jabatan', $jabatan->nama_jabatan) }}">
                    @error('nama_jabatan')
                        <b class="master-jabatan-edit-error">{{ $message }}</b>
                    @enderror
                </div>

                <div class="master-jabatan-edit-field-grid">
                    <div class="master-jabatan-edit-field">
                        <label for="grade">
                            GRADE
                            <span>Tingkat Jabatan</span>
                        </label>
                        <input type="text" name="grade" id="grade" class="@error('grade') invalid @enderror"
                            value="{{ old('grade', $jabatan->grade) }}">
                        @error('grade')
                            <b class="master-jabatan-edit-error">{{ $message }}</b>
                        @enderror
                    </div>

                    <div class="master-jabatan-edit-field">
                        <label for="base_tukin">
                            BASE TUKIN (RP)
                            <span>Bulan</span>
                        </label>
                        <div class="master-jabatan-edit-currency">
                            <strong>Rp</strong>
                            <input type="number" name="base_tukin" id="base_tukin" min="0" step="1000"
                                class="@error('base_tukin') invalid @enderror"
                                value="{{ old('base_tukin', $jabatan->base_tukin) }}">
                        </div>
                        @error('base_tukin')
                            <b class="master-jabatan-edit-error">{{ $message }}</b>
                        @enderror
                    </div>
                </div>

                <div class="master-jabatan-edit-actions">
                    <button type="submit">
                        <i class="fa-regular fa-bookmark"></i>
                        Update
                    </button>
                    <a href="{{ route('master.jabatan.index') }}">Batal</a>
                </div>
            </form>
        </section>

        <aside class="master-jabatan-edit-info">
            <span><i class="fa-solid fa-circle-info"></i></span>
            <p>
                <strong>Info:</strong> Perubahan pada data jabatan ini akan otomatis disinkronkan ke
                seluruh data pegawai terkait dan struktur penilaian KPI divisi.
            </p>
        </aside>
    </div>
@endsection


@push('styles')
    <style>
        body.role-master .master-jabatan-edit {
            width: 100%;
            max-width: 560px;
            margin: auto;
            padding: 1rem 1rem 0;
            box-sizing: border-box;
            color: #14223d;
        }

        .master-jabatan-edit-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .6rem;
            margin: 0 -1rem;
            padding: .9rem 1rem;
            background: #fff;
            border-bottom: 1px solid #e3ebf3;
        }

        .master-jabatan-edit-crumb {
            display: flex;
            align-items: center;
            gap: .5rem;
            color: #0875ad;
            font-size: .68rem;
            white-space: nowrap;
        }

        .master-jabatan-edit-crumb a {
            color: #0875ad;
            text-decoration: none;
        }

        .master-jabatan-edit-crumb i {
            color: #9caabd;
            font-size: .48rem;
        }

        .master-jabatan-edit-topbar>a {
            order: 2;
            padding: .62rem .8rem;
            color: #526480;
            border: 1px solid #cbd9e7;
            border-radius: 11px;
            font-size: .76rem;
            font-weight: 600;
            text-decoration: none;
            white-space: nowrap;
        }

        .master-jabatan-edit-heading {
            padding: 1.4rem 0 1.2rem;
        }

        .master-jabatan-edit-heading h1 {
            margin: 0;
            font-size: 1rem;
        }

        .master-jabatan-edit-heading p {
            max-width: 400px;
            margin: .4rem 0 0;
            color: #647895;
            font-size: .82rem;
            line-height: 1.5;
        }

        .master-jabatan-edit-card {
            overflow: hidden;
            background: #fff;
            border: 1px solid #dfe8f1;
            border-radius: 19px;
            box-shadow: 0 5px 17px #2334550b;
        }

        .master-jabatan-edit-card form {
            padding: 1.65rem 1.2rem 1.2rem;
        }

        .master-jabatan-edit-field {
            margin-bottom: 1.25rem;
        }

        .master-jabatan-edit-field label {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: .55rem;
            color: #263653;
            font-size: .8rem;
            font-weight: 700;
            letter-spacing: .02em;
        }

        .master-jabatan-edit-field label em {
            color: #ef4058;
            font-style: normal;
        }

        .master-jabatan-edit-field label span {
            color: #91a1b8;
            font-size: .7rem;
            font-weight: 500;
            letter-spacing: 0;
        }

        .master-jabatan-edit-field input,
        .master-jabatan-edit-select select {
            box-sizing: border-box;
            width: 100%;
            height: 54px;
            padding: 0 1rem;
            color: #263653;
            background: #fff;
            border: 1px solid #cbd9e7;
            border-radius: 15px;
            outline: 0;
            font: inherit;
            font-size: .82rem;
        }

        .master-jabatan-edit-field input:focus,
        .master-jabatan-edit-select select:focus {
            border-color: #078dcc;
            box-shadow: 0 0 0 3px #078dcc18;
        }

        .master-jabatan-edit-field input.invalid,
        .master-jabatan-edit-select select.invalid {
            border-color: #e54862;
        }

        .master-jabatan-edit-select {
            position: relative;
        }

        .master-jabatan-edit-select select {
            appearance: none;
            padding-right: 2.5rem;
        }

        .master-jabatan-edit-select i {
            position: absolute;
            top: 50%;
            right: 1rem;
            color: #647895;
            transform: translateY(-50%);
            pointer-events: none;
        }

        .master-jabatan-edit-currency {
            position: relative;
        }

        .master-jabatan-edit-currency strong {
            position: absolute;
            top: 50%;
            left: 1rem;
            color: #91a1b8;
            font-size: .85rem;
            transform: translateY(-50%);
        }

        .master-jabatan-edit-currency input {
            padding-left: 3.4rem;
        }

        .master-jabatan-edit-error {
            display: block;
            margin-top: .35rem;
            color: #e54862;
            font-size: .68rem;
            font-weight: 500;
        }

        .master-jabatan-edit-actions {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: .7rem;
            margin-top: 1.65rem;
        }

        .master-jabatan-edit-actions button,
        .master-jabatan-edit-actions a {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .55rem;
            min-height: 54px;
            padding: .8rem 1.35rem;
            box-sizing: border-box;
            border-radius: 14px;
            font: inherit;
            font-size: .84rem;
            text-decoration: none;
        }

        .master-jabatan-edit-actions button {
            color: #fff;
            background: #078dcc;
            border: 0;
            box-shadow: 0 4px 9px #078dcc35;
            font-weight: 600;
        }

        .master-jabatan-edit-actions a {
            min-width: 105px;
            color: #526480;
            background: #fff;
            border: 1px solid #cbd9e7;
            font-weight: 600;
        }

        .master-jabatan-edit-info {
            display: flex;
            align-items: flex-start;
            gap: .75rem;
            margin-top: 1rem;
            padding: 1rem;
            color: #0875ad;
            background: #eaf7ff;
            border: 1px solid #c8eaff;
            border-radius: 17px;
        }

        .master-jabatan-edit-info>span {
            display: grid;
            place-items: center;
            width: 34px;
            height: 34px;
            flex: 0 0 34px;
            color: #0875ad;
            background: #d9f0ff;
            border-radius: 10px;
            font-size: 1rem;
        }

        .master-jabatan-edit-info p {
            margin: .1rem 0 0;
            color: #0875ad;
            font-size: .75rem;
            line-height: 1.55;
        }

        .master-jabatan-edit-info strong {
            color: #07527c;
        }

        @media (max-width: 380px) {
            body.role-master .master-jabatan-edit {
                padding-inline: .75rem;
            }

            .master-jabatan-edit-topbar {
                margin-inline: -.75rem;
                padding-inline: .75rem;
            }

            .master-jabatan-edit-crumb a,
            .master-jabatan-edit-crumb i {
                display: none;
            }

            .master-jabatan-edit-heading {
                padding-top: 1.15rem;
            }

            .master-jabatan-edit-heading h1 {
                font-size: 1.3rem;
            }

            .master-jabatan-edit-card form {
                padding-inline: 1rem;
            }
        }

        .master-jabatan-edit-breadcrumb,
        .master-jabatan-edit-card-header,
        .master-jabatan-edit-field-grid {
            display: none;
        }

        @media (min-width: 768px) {
            body.role-master .master-jabatan-edit {
                width: 100%;
                max-width: none;
                margin: 0;
                padding: 1rem clamp(1.25rem, 2.4vw, 2.5rem) 2rem;
                color: #1b2938;
            }

            .master-jabatan-edit-heading {
                padding: .5rem 0 1rem;
            }

            .master-jabatan-edit-breadcrumb {
                display: flex;
                align-items: center;
                gap: .4rem;
                margin-bottom: .45rem;
                color: #718092;
                font-size: .64rem;
            }

            .master-jabatan-edit-breadcrumb a {
                color: #718092;
                text-decoration: none;
            }

            .master-jabatan-edit-breadcrumb>span,
            .master-jabatan-edit-breadcrumb a:hover {
                color: #1769d2;
            }

            .master-jabatan-edit-breadcrumb i {
                color: #a0acb9;
                font-size: .48rem;
            }

            .master-jabatan-edit-heading h1 {
                color: #1b2938;
                font-size: 1.4rem;
                font-weight: 700;
            }

            .master-jabatan-edit-heading h1 i {
                color: #1769d2;
                font-size: 1rem;
            }

            .master-jabatan-edit-heading p {
                margin-top: .25rem;
                font-size: .75rem;
            }

            .master-jabatan-edit-card,
            .master-jabatan-edit-info {
                width: 100%;
                max-width: 1120px;
                margin-right: auto;
                margin-left: auto;
            }

            .master-jabatan-edit-card {
                border-color: #e2e8ef;
                border-radius: 8px;
                box-shadow: none;
            }

            .master-jabatan-edit-card-header {
                display: flex;
                align-items: center;
                gap: .6rem;
                padding: .8rem 1rem;
                background: #f9fbfd;
                border-bottom: 1px solid #e8eef5;
            }

            .master-jabatan-edit-card-header>span {
                display: grid;
                place-items: center;
                width: 32px;
                height: 32px;
                flex: 0 0 32px;
                color: #078dcc;
                background: #e8f7ff;
                border-radius: 6px;
            }

            .master-jabatan-edit-card-header h2,
            .master-jabatan-edit-card-header p {
                margin: 0;
            }

            .master-jabatan-edit-card-header h2 {
                color: #25364a;
                font-size: .78rem;
            }

            .master-jabatan-edit-card-header p {
                margin-top: .15rem;
                color: #8290a0;
                font-size: .61rem;
            }

            .master-jabatan-edit-card-header>b {
                margin-left: auto;
                padding: .25rem .4rem;
                color: #a96800;
                background: #fff5df;
                border: 1px solid #ffe2a8;
                border-radius: 999px;
                font-size: .56rem;
                white-space: nowrap;
            }

            .master-jabatan-edit-card form {
                display: grid;
                grid-template-columns: minmax(0, 1fr);
                padding: 1rem;
            }

            .master-jabatan-edit-field {
                margin-bottom: .8rem;
            }

            .master-jabatan-edit-field label {
                margin-bottom: .35rem;
                font-size: .67rem;
            }

            .master-jabatan-edit-field label span {
                font-size: .59rem;
            }

            .master-jabatan-edit-field input,
            .master-jabatan-edit-select select {
                height: 40px;
                padding: 0 .75rem;
                border-radius: 5px;
                font-size: .69rem;
            }

            .master-jabatan-edit-select select {
                padding-right: 2rem;
            }

            .master-jabatan-edit-select i {
                right: .75rem;
                font-size: .65rem;
            }

            .master-jabatan-edit-field-grid {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: .8rem;
            }

            .master-jabatan-edit-field-grid .master-jabatan-edit-field {
                min-width: 0;
                margin-bottom: .4rem;
            }

            .master-jabatan-edit-currency strong {
                left: .65rem;
                padding: .2rem .3rem;
                color: #1769d2;
                background: #edf4ff;
                border-radius: 3px;
                font-size: .61rem;
            }

            .master-jabatan-edit-currency input {
                padding-left: 2.8rem;
            }

            .master-jabatan-edit-error {
                font-size: .61rem;
            }

            .master-jabatan-edit-actions {
                display: flex;
                justify-content: flex-end;
                gap: .5rem;
                margin-top: .5rem;
                padding-top: .75rem;
                border-top: 1px solid #edf1f5;
            }

            .master-jabatan-edit-actions button,
            .master-jabatan-edit-actions a {
                min-width: 95px;
                min-height: 38px;
                padding: .5rem .75rem;
                border-radius: 5px;
                font-size: .68rem;
            }

            .master-jabatan-edit-actions button {
                order: 2;
                background: #078dcc;
                box-shadow: none;
            }

            .master-jabatan-edit-actions a {
                order: 1;
            }

            .master-jabatan-edit-info {
                gap: .55rem;
                margin-top: .75rem;
                padding: .7rem .8rem;
                border-radius: 6px;
            }

            .master-jabatan-edit-info>span {
                width: 28px;
                height: 28px;
                flex-basis: 28px;
                border-radius: 5px;
                font-size: .78rem;
            }

            .master-jabatan-edit-info p {
                font-size: .63rem;
            }
        }
    </style>
@endpush
