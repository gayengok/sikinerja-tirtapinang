@extends('layouts.app')

@section('title', 'Edit Jam Kerja')

@section('content')
    <div class="master-jam-edit-page">


        <header class="master-jam-edit-heading">
            <div>
                <nav class="master-jam-edit-breadcrumb" aria-label="Breadcrumb">
                    <a href="{{ route('master.dashboard') }}">Beranda</a>
                    <i class="fa-solid fa-chevron-right"></i>
                    <a href="{{ route('master.jam-kerja.index') }}">Jam Kerja</a>
                    <i class="fa-solid fa-chevron-right"></i>
                    <span>Edit</span>
                </nav>
                <h1>
                    <i class="fa-solid fa-pen-to-square"></i>
                    Edit Jam Kerja
                </h1>
                <p>Perbarui pengaturan jam masuk, pulang, dan batas toleransi keterlambatan presensi.</p>
            </div>
        </header>



        <section class="master-jam-edit-form-card">
            <div class="master-jam-edit-form-title">
                <span><i class="fa-regular fa-clock"></i></span>
                <strong>FORMULIR PENGATURAN</strong>
                <code>ID: JAD-{{ str_pad($jamKerja->id, 2, '0', STR_PAD_LEFT) }}</code>
            </div>
            <div class="master-jam-edit-divider"></div>

            <form action="{{ route('master.jam-kerja.update', $jamKerja) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="master-jam-edit-field">
                    <label for="jabatan_id">Jabatan <small>(Opsional)</small></label>
                    <select name="jabatan_id" id="jabatan_id" class="@error('jabatan_id') is-invalid @enderror">
                        <option value="">Semua Jabatan</option>
                        @foreach ($jabatans as $jabatan)
                            <option value="{{ $jabatan->id }}"
                                {{ old('jabatan_id', $jamKerja->jabatan_id) == $jabatan->id ? 'selected' : '' }}>
                                {{ $jabatan->nama_jabatan }}</option>
                        @endforeach
                    </select>
                    <p class="master-jam-edit-help"><i class="fa-regular fa-circle-question"></i> Berlaku umum untuk semua
                        jabatan apabila tidak dipilih spesifik.</p>
                    @error('jabatan_id')
                        <div class="master-jam-edit-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="master-jam-edit-field">
                    <label for="hari">Hari</label>
                    <select name="hari" id="hari" class="@error('hari') is-invalid @enderror">
                        <option value="">-- Pilih Hari --</option>
                        @foreach ($hariOptions as $hari)
                            <option value="{{ $hari }}"
                                {{ old('hari', $jamKerja->hari) === $hari ? 'selected' : '' }}>{{ $hari }}
                            </option>
                        @endforeach
                    </select>
                    @error('hari')
                        <div class="master-jam-edit-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="master-jam-edit-time-grid">
                    <div class="master-jam-edit-field">
                        <label for="jam_masuk">Jam Masuk</label>
                        <input type="time" name="jam_masuk" id="jam_masuk"
                            class="@error('jam_masuk') is-invalid @enderror"
                            value="{{ old('jam_masuk', \Carbon\Carbon::parse($jamKerja->jam_masuk)->format('H:i')) }}">
                        @error('jam_masuk')
                            <div class="master-jam-edit-error">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="master-jam-edit-field">
                        <label for="jam_pulang">Jam Pulang</label>
                        <input type="time" name="jam_pulang" id="jam_pulang"
                            class="@error('jam_pulang') is-invalid @enderror"
                            value="{{ old('jam_pulang', \Carbon\Carbon::parse($jamKerja->jam_pulang)->format('H:i')) }}">
                        @error('jam_pulang')
                            <div class="master-jam-edit-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="master-jam-edit-field">
                    <label for="toleransi_menit">Toleransi (menit) <a href="#">Batas Maksimal</a></label>
                    <div class="master-jam-edit-number">
                        <input type="number" min="0" name="toleransi_menit" id="toleransi_menit"
                            class="@error('toleransi_menit') is-invalid @enderror"
                            value="{{ old('toleransi_menit', $jamKerja->toleransi_menit) }}">
                        <span>Menit</span>
                    </div>
                    <p class="master-jam-edit-help">Toleransi jeda sebelum sistem memotong poin disiplin.</p>
                    @error('toleransi_menit')
                        <div class="master-jam-edit-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="master-jam-edit-submit">
                    <button type="submit"><i class="fa-solid fa-download"></i> Update</button>
                    <a href="{{ route('master.jam-kerja.index') }}">Batal</a>
                </div>
            </form>
        </section>

        <aside class="master-jam-edit-note">
            <i class="fa-solid fa-circle-info"></i>
            <p><strong>Catatan Sinkronisasi Presensi:</strong> Perubahan jam kerja ini akan otomatis diterapkan pada
                validasi absensi GPS/biometrik pegawai pada hari <b>{{ $jamKerja->hari }}</b>. Keterlambatan melebihi
                toleransi <b>{{ $jamKerja->toleransi_menit }} menit</b> akan tercatat sebagai keterlambatan pemotongan
                Tukin.</p>
        </aside>
    </div>
@endsection

@push('styles')
    <style>
        body.role-master .master-jam-edit-page {
            width: 100%;
            max-width: 560px;
            margin: auto;
            padding: 1rem 1rem 0;
            box-sizing: border-box;
            color: #14223d
        }

        .master-jam-edit-breadcrumb {
            display: flex;
            align-items: center;
            gap: .55rem;
            margin-bottom: 1rem;
            color: #14223d;
            font-size: .72rem
        }

        .master-jam-edit-breadcrumb a {
            color: #0875ad;
            text-decoration: none
        }

        .master-jam-edit-breadcrumb i {
            font-size: .52rem;
            color: #9caabd
        }

        .master-jam-edit-heading {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: .7rem
        }

        .master-jam-edit-heading h1 {
            margin: 0;
            font-size: 1rem
        }

        .master-jam-edit-heading p {
            max-width: 390px;
            margin: .35rem 0;
            color: #71829c;
            font-size: .76rem;
            line-height: 1.55
        }

        .master-jam-edit-back {
            flex: 0 0 auto;
            padding: .55rem .75rem;
            color: #14223d;
            background: #fff;
            border: 1px solid #c9d6e5;
            border-radius: 11px;
            text-decoration: none;
            font-size: .72rem;
            white-space: nowrap
        }

        .master-jam-edit-status {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: .75rem;
            margin: 1rem 0 1.2rem;
            font-size: .7rem
        }

        .master-jam-edit-status span {
            padding: .42rem .75rem;
            color: #07986b;
            background: #effff8;
            border: 1px solid #a6edce;
            border-radius: 999px
        }

        .master-jam-edit-status span i {
            font-size: .42rem;
            vertical-align: middle
        }

        .master-jam-edit-status em {
            color: #91a1b8;
            font-style: normal
        }

        .master-jam-edit-status em i {
            font-size: .4rem;
            color: #9caabd
        }

        .master-jam-edit-form-card {
            padding: 1.2rem 1rem;
            background: #fff;
            border: 1px solid #e0e8f1;
            border-radius: 20px;
            box-shadow: 0 5px 16px #2334550b
        }

        .master-jam-edit-form-title {
            display: flex;
            align-items: center;
            gap: .7rem
        }

        .master-jam-edit-form-title>span {
            display: grid;
            place-items: center;
            width: 36px;
            height: 36px;
            color: #078dcc;
            background: #effaff;
            border-radius: 10px
        }

        .master-jam-edit-form-title strong {
            font-size: .88rem;
            letter-spacing: .02em
        }

        .master-jam-edit-form-title code {
            margin-left: auto;
            padding: .45rem .55rem;
            color: #526480;
            background: #f0f5f9;
            border-radius: 6px;
            font-size: .66rem
        }

        .master-jam-edit-divider {
            height: 1px;
            margin: 1rem 0 1.2rem;
            background: #e5edf4
        }

        .master-jam-edit-field {
            margin-bottom: 1rem
        }

        .master-jam-edit-field label {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: .45rem;
            font-size: .78rem
        }

        .master-jam-edit-field label small {
            font-size: .7rem;
            color: #71829c
        }

        .master-jam-edit-field label a {
            color: #078dcc;
            text-decoration: none;
            font-size: .7rem
        }

        .master-jam-edit-field select,
        .master-jam-edit-field input {
            width: 100%;
            min-height: 48px;
            padding: .7rem .85rem;
            color: #14223d;
            background: #fff;
            border: 1px solid #c9d6e5;
            border-radius: 13px;
            box-sizing: border-box;
            font: inherit;
            font-size: .78rem;
            outline: none
        }

        .master-jam-edit-field select:focus,
        .master-jam-edit-field input:focus {
            border-color: #078dcc;
            box-shadow: 0 0 0 3px #078dcc1a
        }

        .master-jam-edit-help {
            display: flex;
            gap: .45rem;
            margin: .45rem 0 0;
            color: #91a1b8;
            font-size: .7rem;
            line-height: 1.5
        }

        .master-jam-edit-help i {
            margin-top: .15rem
        }

        .master-jam-edit-time-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .9rem
        }

        .master-jam-edit-number {
            display: flex;
            align-items: center;
            border: 1px solid #c9d6e5;
            border-radius: 13px;
            overflow: hidden
        }

        .master-jam-edit-number input {
            border: 0;
            border-radius: 0
        }

        .master-jam-edit-number span {
            margin-right: .65rem;
            padding: .4rem .6rem;
            color: #526480;
            background: #f0f5f9;
            border-radius: 7px;
            font-size: .7rem
        }

        .master-jam-edit-error {
            margin-top: .3rem;
            color: #dc3545;
            font-size: .68rem
        }

        .master-jam-edit-field .is-invalid {
            border-color: #dc3545
        }

        .master-jam-edit-submit {
            display: grid;
            grid-template-columns: 1fr 106px;
            gap: .8rem;
            margin-top: 1.2rem
        }

        .master-jam-edit-submit button,
        .master-jam-edit-submit a {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            min-height: 52px;
            border-radius: 14px;
            font: inherit;
            font-size: .82rem;
            text-decoration: none
        }

        .master-jam-edit-submit button {
            color: #fff;
            background: #078dcc;
            border: 0;
            box-shadow: 0 4px 8px #078dcc33;
            font-weight: 700
        }

        .master-jam-edit-submit a {
            color: #14223d;
            background: #fff;
            border: 1px solid #c9d6e5
        }

        .master-jam-edit-note {
            display: flex;
            gap: .8rem;
            margin-top: 1.2rem;
            padding: 1.1rem 1rem;
            color: #07527c;
            background: #effaff;
            border: 1px solid #bce7ff;
            border-radius: 17px
        }

        .master-jam-edit-note>i {
            margin-top: .15rem;
            color: #078dcc;
            font-size: 1rem
        }

        .master-jam-edit-note p {
            margin: 0;
            color: #526b8c;
            font-size: .73rem;
            line-height: 1.6
        }

        .master-jam-edit-note strong {
            color: #07527c
        }

        .master-jam-edit-delete {
            margin: 1.7rem 0;
            text-align: center
        }

        .master-jam-edit-delete button {
            color: #f03d5c;
            background: none;
            border: 0;
            font: inherit;
            font-size: .78rem
        }

        .master-jam-edit-delete i {
            margin-right: .35rem
        }

        @media(max-width:380px) {
            body.role-master .master-jam-edit-page {
                padding-inline: .75rem
            }

            .master-jam-edit-heading h1 {
                font-size: 1.25rem
            }

            .master-jam-edit-heading p {
                font-size: .7rem
            }

            .master-jam-edit-back {
                padding-inline: .6rem
            }

            .master-jam-edit-form-card {
                padding-inline: .8rem
            }

            .master-jam-edit-form-title strong {
                font-size: .78rem
            }

            .master-jam-edit-form-title code {
                font-size: .59rem
            }

            .master-jam-edit-time-grid {
                gap: .55rem
            }
        }

        @media (min-width: 768px) {
            body.role-master .master-jam-edit-page {
                width: 100%;
                max-width: none;
                margin: 0;
                padding: 1rem clamp(1.25rem, 2.4vw, 2.5rem) 2rem;
                color: #1b2938;
            }

            .master-jam-edit-heading {
                padding: .4rem 0 .85rem;
            }

            .master-jam-edit-breadcrumb {
                gap: .4rem;
                margin-bottom: .4rem;
                color: #718092;
                font-size: .64rem;
            }

            .master-jam-edit-breadcrumb a {
                color: #718092;
            }

            .master-jam-edit-breadcrumb>span,
            .master-jam-edit-breadcrumb a:hover {
                color: #1769d2;
            }

            .master-jam-edit-heading h1 {
                color: #1b2938;
                font-size: 1.35rem;
                font-weight: 700;
            }

            .master-jam-edit-heading h1 i {
                color: #1769d2;
                font-size: 1rem;
            }

            .master-jam-edit-heading p {
                margin-top: .2rem;
                font-size: .7rem;
            }

            .master-jam-edit-form-card {
                width: 100%;
                max-width: 1120px;
                margin: 0 auto;
                padding: .9rem 1rem;
                border-color: #e2e8ef;
                border-radius: 8px;
                box-shadow: none;
            }

            .master-jam-edit-form-title {
                gap: .55rem;
            }

            .master-jam-edit-form-title>span {
                width: 32px;
                height: 32px;
                border-radius: 6px;
            }

            .master-jam-edit-form-title strong {
                font-size: .72rem;
            }

            .master-jam-edit-form-title code {
                padding: .25rem .4rem;
                border-radius: 4px;
                font-size: .58rem;
            }

            .master-jam-edit-divider {
                margin: .75rem 0 .9rem;
            }

            .master-jam-edit-field {
                margin-bottom: .75rem;
            }

            .master-jam-edit-field label {
                margin-bottom: .32rem;
                font-size: .65rem;
            }

            .master-jam-edit-field label small,
            .master-jam-edit-field label a {
                font-size: .59rem;
            }

            .master-jam-edit-field select,
            .master-jam-edit-field input {
                min-height: 38px;
                padding: .45rem .7rem;
                border-radius: 5px;
                font-size: .68rem;
            }

            .master-jam-edit-time-grid {
                gap: .8rem;
            }

            .master-jam-edit-number {
                border-radius: 5px;
            }

            .master-jam-edit-number input {
                border-radius: 0;
            }

            .master-jam-edit-number span {
                font-size: .61rem;
            }

            .master-jam-edit-help {
                margin-top: .3rem;
                font-size: .61rem;
            }

            .master-jam-edit-submit {
                display: flex;
                justify-content: flex-end;
                gap: .5rem;
                margin-top: .85rem;
                padding-top: .7rem;
                border-top: 1px solid #edf1f5;
            }

            .master-jam-edit-submit button,
            .master-jam-edit-submit a {
                min-width: 95px;
                min-height: 38px;
                padding: .5rem .75rem;
                border-radius: 5px;
                font-size: .68rem;
            }

            .master-jam-edit-submit button {
                order: 2;
                background: #078dcc;
                box-shadow: none;
            }

            .master-jam-edit-submit a {
                order: 1;
            }

            .master-jam-edit-note {
                width: 100%;
                max-width: 1120px;
                margin: .75rem auto 0;
                padding: .7rem .8rem;
                border-radius: 6px;
            }

            .master-jam-edit-note p {
                font-size: .63rem;
            }
        }
    </style>
@endpush
