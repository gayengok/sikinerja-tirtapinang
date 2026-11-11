@extends('layouts.app')

@section('title', 'Tambah Jabatan')

@section('content')
    <div class="master-jabatan-create">


        <header class="master-jabatan-create-heading">
            <nav class="master-jabatan-create-breadcrumb" aria-label="Breadcrumb">
                <a href="{{ route('master.dashboard') }}">Beranda</a>
                <i class="fa-solid fa-chevron-right"></i>
                <a href="{{ route('master.jabatan.index') }}">Master Data</a>
                <i class="fa-solid fa-chevron-right"></i>
                <span>Tambah Jabatan</span>
            </nav>
            <h1>
                <i class="fa-solid fa-briefcase"></i>
                Tambah Jabatan
            </h1>
            <p>Lengkapi formulir master jabatan baru</p>
        </header>

        <section class="master-jabatan-form-card">
            <div class="master-jabatan-form-card-header">
                <span><i class="fa-solid fa-briefcase"></i></span>
                <div>
                    <h2>Formulir Master Jabatan</h2>
                    <p>Silakan lengkapi data atribut jabatan dengan teliti</p>
                </div>
                <b>Mode Tambah</b>
            </div>
            <form action="{{ route('master.jabatan.store') }}" method="POST">
                @csrf

                <div class="master-jabatan-field">
                    <label for="divisi_id">DIVISI <em>*</em></label>
                    <div class="master-jabatan-select-wrap">
                        <select name="divisi_id" id="divisi_id" class="@error('divisi_id') invalid @enderror">
                            <option value="">-- Pilih Divisi --</option>
                            @foreach ($divisis as $divisi)
                                <option value="{{ $divisi->id }}" {{ old('divisi_id') == $divisi->id ? 'selected' : '' }}>
                                    {{ $divisi->nama_divisi }}
                                </option>
                            @endforeach
                        </select>
                        <i class="fa-solid fa-chevron-down"></i>
                    </div>
                    @error('divisi_id')
                        <b class="master-jabatan-error">{{ $message }}</b>
                    @enderror
                </div>

                <div class="master-jabatan-field">
                    <label for="nama_jabatan">NAMA JABATAN <em>*</em></label>
                    <input type="text" name="nama_jabatan" id="nama_jabatan"
                        class="@error('nama_jabatan') invalid @enderror" value="{{ old('nama_jabatan') }}"
                        placeholder="Contoh: Pelaksana Pembaca Meter">
                    @error('nama_jabatan')
                        <b class="master-jabatan-error">{{ $message }}</b>
                    @enderror
                </div>

                <div class="master-jabatan-field-grid">
                    <div class="master-jabatan-field">
                        <label for="grade">
                            GRADE
                            <span>Opsional</span>
                        </label>
                        <input type="text" name="grade" id="grade" class="@error('grade') invalid @enderror"
                            value="{{ old('grade') }}" placeholder="Contoh: Grade 2">
                        @error('grade')
                            <b class="master-jabatan-error">{{ $message }}</b>
                        @enderror
                    </div>

                    <div class="master-jabatan-field">
                        <label for="base_tukin">
                            BASE TUKIN (RP)
                            <span>Bulan</span>
                        </label>
                        <div class="master-jabatan-currency">
                            <strong>Rp</strong>
                            <input type="number" name="base_tukin" id="base_tukin" min="0" step="1000"
                                class="@error('base_tukin') invalid @enderror" value="{{ old('base_tukin') }}"
                                placeholder="Contoh: 2000000">
                        </div>
                        @error('base_tukin')
                            <b class="master-jabatan-error">{{ $message }}</b>
                        @enderror
                    </div>
                </div>

                <div class="master-jabatan-form-actions">
                    <button type="submit">
                        <i class="fa-regular fa-floppy-disk"></i>
                        Simpan
                    </button>
                    <a href="{{ route('master.jabatan.index') }}">Batal</a>
                </div>
            </form>
        </section>

        <aside class="master-jabatan-info">
            <span><i class="fa-solid fa-circle-info"></i></span>
            <p>
                <strong>Info:</strong> Jabatan baru akan langsung terhubung dengan master divisi dan
                dapat ditetapkan target KPI serta base tunjangan kinerja (tukin).
            </p>
        </aside>
    </div>
@endsection

@push('styles')
    <style>
        body.role-master .master-jabatan-create {
            width: 100%;
            max-width: 560px;
            margin: auto;
            padding: 1rem 1rem 0;
            box-sizing: border-box;
            color: #14223d;
        }

        .master-jabatan-create-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .6rem;
            margin: 0 -1rem;
            padding: .9rem 1rem;
            background: #fff;
            border-bottom: 1px solid #e3ebf3;
        }

        .master-jabatan-create-crumb {
            display: flex;
            align-items: center;
            gap: .5rem;
            color: #0875ad;
            font-size: .68rem;
            white-space: nowrap;
        }

        .master-jabatan-create-crumb a {
            color: #0875ad;
            text-decoration: none;
        }

        .master-jabatan-create-crumb i {
            color: #9caabd;
            font-size: .48rem;
        }

        .master-jabatan-create-topbar>a {
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

        .master-jabatan-create-heading {
            padding: 1.4rem 0 1.2rem;
        }

        .master-jabatan-create-heading h1 {
            margin: 0;
            font-size: 1rem;
        }

        .master-jabatan-create-heading p {
            margin: .4rem 0 0;
            color: #647895;
            font-size: .82rem;
        }

        .master-jabatan-form-card {
            overflow: hidden;
            background: #fff;
            border: 1px solid #dfe8f1;
            border-radius: 19px;
            box-shadow: 0 5px 17px #2334550b;
        }

        .master-jabatan-form-card form {
            padding: 1.65rem 1.2rem 1.2rem;
        }

        .master-jabatan-field {
            margin-bottom: 1.25rem;
        }

        .master-jabatan-field label {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: .55rem;
            color: #263653;
            font-size: .8rem;
            font-weight: 700;
            letter-spacing: .02em;
        }

        .master-jabatan-field label em {
            margin-left: .2rem;
            color: #ef4058;
            font-style: normal;
        }

        .master-jabatan-field label span {
            color: #91a1b8;
            font-size: .7rem;
            font-weight: 500;
            letter-spacing: 0;
        }

        .master-jabatan-field input,
        .master-jabatan-select-wrap select {
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

        .master-jabatan-field input::placeholder,
        .master-jabatan-select-wrap select:invalid {
            color: #94a6be;
        }

        .master-jabatan-field input:focus,
        .master-jabatan-select-wrap select:focus {
            border-color: #078dcc;
            box-shadow: 0 0 0 3px #078dcc18;
        }

        .master-jabatan-field input.invalid,
        .master-jabatan-select-wrap select.invalid {
            border-color: #e54862;
        }

        .master-jabatan-select-wrap {
            position: relative;
        }

        .master-jabatan-select-wrap select {
            appearance: none;
            padding-right: 2.5rem;
        }

        .master-jabatan-select-wrap i {
            position: absolute;
            top: 50%;
            right: 1rem;
            color: #647895;
            transform: translateY(-50%);
            pointer-events: none;
        }

        .master-jabatan-currency {
            position: relative;
        }

        .master-jabatan-currency strong {
            position: absolute;
            top: 50%;
            left: 1rem;
            color: #91a1b8;
            font-size: .85rem;
            transform: translateY(-50%);
        }

        .master-jabatan-currency input {
            padding-left: 3.4rem;
        }

        .master-jabatan-error {
            display: block;
            margin-top: .35rem;
            color: #e54862;
            font-size: .68rem;
            font-weight: 500;
        }

        .master-jabatan-form-actions {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: .7rem;
            margin-top: 1.65rem;
        }

        .master-jabatan-form-actions button,
        .master-jabatan-form-actions a {
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

        .master-jabatan-form-actions button {
            color: #fff;
            background: #078dcc;
            border: 0;
            box-shadow: 0 4px 9px #078dcc35;
            font-weight: 600;
        }

        .master-jabatan-form-actions a {
            min-width: 105px;
            color: #526480;
            background: #fff;
            border: 1px solid #cbd9e7;
            font-weight: 600;
        }

        .master-jabatan-info {
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

        .master-jabatan-info>span {
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

        .master-jabatan-info p {
            margin: .1rem 0 0;
            color: #0875ad;
            font-size: .75rem;
            line-height: 1.55;
        }

        .master-jabatan-info strong {
            color: #07527c;
        }

        @media (max-width: 380px) {
            body.role-master .master-jabatan-create {
                padding-inline: .75rem;
            }

            .master-jabatan-create-topbar {
                margin-inline: -.75rem;
                padding-inline: .75rem;
            }

            .master-jabatan-create-crumb a,
            .master-jabatan-create-crumb i {
                display: none;
            }

            .master-jabatan-create-heading {
                padding-top: 1.15rem;
            }

            .master-jabatan-create-heading h1 {
                font-size: 1.3rem;
            }

            .master-jabatan-form-card form {
                padding-inline: 1rem;
            }
        }

        .master-jabatan-create-breadcrumb,
        .master-jabatan-form-card-header,
        .master-jabatan-field-grid {
            display: none;
        }

        @media (min-width: 768px) {
            body.role-master .master-jabatan-create {
                width: 100%;
                max-width: none;
                margin: 0;
                padding: 1rem clamp(1.25rem, 2.4vw, 2.5rem) 2rem;
                color: #1b2938;
            }

            .master-jabatan-create-heading {
                padding: .5rem 0 1rem;
            }

            .master-jabatan-create-breadcrumb {
                display: flex;
                align-items: center;
                gap: .4rem;
                margin-bottom: .45rem;
                color: #718092;
                font-size: .64rem;
            }

            .master-jabatan-create-breadcrumb a {
                color: #718092;
                text-decoration: none;
            }

            .master-jabatan-create-breadcrumb>span,
            .master-jabatan-create-breadcrumb a:hover {
                color: #1769d2;
            }

            .master-jabatan-create-breadcrumb i {
                color: #a0acb9;
                font-size: .48rem;
            }

            .master-jabatan-create-heading h1 {
                color: #1b2938;
                font-size: 1.4rem;
                font-weight: 700;
            }

            .master-jabatan-create-heading h1 i {
                color: #1769d2;
                font-size: 1rem;
            }

            .master-jabatan-create-heading p {
                margin-top: .25rem;
                font-size: .75rem;
            }

            .master-jabatan-form-card,
            .master-jabatan-info {
                width: 100%;
                max-width: 1120px;
                margin-right: auto;
                margin-left: auto;
            }

            .master-jabatan-form-card {
                border-color: #e2e8ef;
                border-radius: 8px;
                box-shadow: none;
            }

            .master-jabatan-form-card-header {
                display: flex;
                align-items: center;
                gap: .6rem;
                padding: .8rem 1rem;
                background: #f9fbfd;
                border-bottom: 1px solid #e8eef5;
            }

            .master-jabatan-form-card-header>span {
                display: grid;
                place-items: center;
                width: 32px;
                height: 32px;
                flex: 0 0 32px;
                color: #078dcc;
                background: #e8f7ff;
                border-radius: 6px;
            }

            .master-jabatan-form-card-header h2,
            .master-jabatan-form-card-header p {
                margin: 0;
            }

            .master-jabatan-form-card-header h2 {
                color: #25364a;
                font-size: .78rem;
            }

            .master-jabatan-form-card-header p {
                margin-top: .15rem;
                color: #8290a0;
                font-size: .61rem;
            }

            .master-jabatan-form-card-header>b {
                margin-left: auto;
                padding: .25rem .4rem;
                color: #087b5d;
                background: #ecfff5;
                border: 1px solid #b8efd1;
                border-radius: 999px;
                font-size: .56rem;
                white-space: nowrap;
            }

            .master-jabatan-form-card form {
                display: grid;
                grid-template-columns: minmax(0, 1fr);
                padding: 1rem;
            }

            .master-jabatan-field {
                margin-bottom: .8rem;
            }

            .master-jabatan-field label {
                margin-bottom: .35rem;
                font-size: .67rem;
            }

            .master-jabatan-field label span {
                font-size: .59rem;
            }

            .master-jabatan-field input,
            .master-jabatan-select-wrap select {
                height: 40px;
                padding: 0 .75rem;
                border-radius: 5px;
                font-size: .69rem;
            }

            .master-jabatan-select-wrap select {
                padding-right: 2rem;
            }

            .master-jabatan-select-wrap i {
                right: .75rem;
                font-size: .65rem;
            }

            .master-jabatan-field-grid {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: .8rem;
            }

            .master-jabatan-field-grid .master-jabatan-field {
                min-width: 0;
                margin-bottom: .4rem;
            }

            .master-jabatan-currency strong {
                left: .65rem;
                padding: .2rem .3rem;
                color: #1769d2;
                background: #edf4ff;
                border-radius: 3px;
                font-size: .61rem;
            }

            .master-jabatan-currency input {
                padding-left: 2.8rem;
            }

            .master-jabatan-error {
                font-size: .61rem;
            }

            .master-jabatan-form-actions {
                display: flex;
                justify-content: flex-end;
                gap: .5rem;
                margin-top: .5rem;
                padding-top: .75rem;
                border-top: 1px solid #edf1f5;
            }

            .master-jabatan-form-actions button,
            .master-jabatan-form-actions a {
                min-width: 95px;
                min-height: 38px;
                padding: .5rem .75rem;
                border-radius: 5px;
                font-size: .68rem;
            }

            .master-jabatan-form-actions button {
                order: 2;
                background: #078dcc;
                box-shadow: none;
            }

            .master-jabatan-form-actions a {
                order: 1;
            }

            .master-jabatan-info {
                gap: .55rem;
                margin-top: .75rem;
                padding: .7rem .8rem;
                border-radius: 6px;
            }

            .master-jabatan-info>span {
                width: 28px;
                height: 28px;
                flex-basis: 28px;
                border-radius: 5px;
                font-size: .78rem;
            }

            .master-jabatan-info p {
                font-size: .63rem;
            }
        }
    </style>
@endpush
