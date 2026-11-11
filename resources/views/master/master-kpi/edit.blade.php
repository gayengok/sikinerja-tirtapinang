@extends('layouts.app')
@section('title', 'Edit Master KPI')
@section('content')
    <div class="master-kpi-edit">

        <header class="master-kpi-edit-heading">
            <div>
                <nav class="master-kpi-edit-breadcrumb" aria-label="Breadcrumb">
                    <a href="{{ route('master.dashboard') }}">Beranda</a>
                    <i class="fa-solid fa-chevron-right"></i>
                    <a href="{{ route('master.master-kpi.index') }}">Master KPI</a>
                    <i class="fa-solid fa-chevron-right"></i>
                    <span>Edit</span>
                </nav>
                <h1>
                    <i class="fa-solid fa-pen-to-square"></i>
                    Edit Master KPI
                </h1>
                <p>Kelola data parameter target kinerja organisasi</p>
            </div>
        </header>

        <aside class="master-kpi-rule"><i class="fa-solid fa-circle-info"></i>
            <div><strong>Informasi Ketentuan Bobot:</strong>
                <p>Total bobot KPI untuk satu jabatan tidak boleh melebihi <b>60%</b>.</p>
            </div>
        </aside>
        <section class="master-kpi-edit-card">
            <div class="master-kpi-edit-card-title"><span><i class="fa-solid fa-pen-to-square"></i></span>
                <h2>Formulir Indikator</h2><code>ID: KPI-{{ str_pad($masterKpi->id, 3, '0', STR_PAD_LEFT) }}</code>
            </div>
            <form action="{{ route('master.master-kpi.update', $masterKpi) }}" method="POST">@csrf @method('PUT')
                <div class="master-kpi-edit-field"><label for="jabatan_id">Jabatan
                        <em>*</em><small>{{ $masterKpi->jabatan->divisi->nama_divisi ?? '-' }}</small></label><select
                        name="jabatan_id" id="jabatan_id" class="@error('jabatan_id') invalid @enderror">
                        <option value="">-- Pilih Jabatan --</option>
                        @foreach ($jabatans as $jabatan)
                            <option value="{{ $jabatan->id }}"
                                {{ old('jabatan_id', $masterKpi->jabatan_id) == $jabatan->id ? 'selected' : '' }}>
                                {{ $jabatan->nama_jabatan }} ({{ $jabatan->divisi->nama_divisi ?? '-' }})</option>
                        @endforeach
                    </select>
                    @error('jabatan_id')
                        <b>{{ $message }}</b>
                    @enderror
                </div>
                <div class="master-kpi-edit-field"><label for="indikator">Indikator Kinerja <em>*</em><small>Nama Metrik
                            Utama</small></label>
                    <div class="edit-kpi-input"><input type="text" name="indikator" id="indikator"
                            value="{{ old('indikator', $masterKpi->indikator) }}"
                            class="@error('indikator') invalid @enderror"><i class="fa-solid fa-pen"></i></div>
                    @error('indikator')
                        <b>{{ $message }}</b>
                    @enderror
                </div>
                <div class="master-kpi-edit-two">
                    <div class="master-kpi-edit-field"><label for="target_default">Target</label>
                        <input type="number" step="0.01" name="target_default" id="target_default"
                            value="{{ old('target_default', $masterKpi->target_default) }}"
                            class="@error('target_default') invalid @enderror">
                        @error('target_default')
                            <b>{{ $message }}</b>
                        @enderror
                    </div>
                    <div class="master-kpi-edit-field"><label for="bobot">Bobot (%) <em>*</em></label>
                        <div class="suffix-input"><input type="number" step="0.01" min="0" max="60"
                                name="bobot" id="bobot" value="{{ old('bobot', $masterKpi->bobot) }}"
                                class="@error('bobot') invalid @enderror"><span>%</span></div>
                        @error('bobot')
                            <b>{{ $message }}</b>
                        @enderror
                    </div>
                </div>
                <div class="master-kpi-edit-two">
                    <div class="master-kpi-edit-field"><label for="realisasi">Realisasi</label>
                        <input type="number" step="0.01" min="0" name="realisasi" id="realisasi"
                            value="{{ old('realisasi', $masterKpi->realisasi) }}"
                            class="@error('realisasi') invalid @enderror">
                        @error('realisasi')
                            <b>{{ $message }}</b>
                        @enderror
                    </div>
                    <div class="master-kpi-edit-field"><label for="persen_capaian">% Capaian</label>
                        <div class="suffix-input"><input type="number" step="0.01" min="0" name="persen_capaian"
                                id="persen_capaian" value="{{ old('persen_capaian', $masterKpi->persen_capaian) }}"
                                class="@error('persen_capaian') invalid @enderror"><span>%</span></div>
                        @error('persen_capaian')
                            <b>{{ $message }}</b>
                        @enderror
                    </div>
                </div>
                <div class="master-kpi-edit-two">
                    <div class="master-kpi-edit-field"><label for="skor">Skor (1–5)</label>
                        <input type="number" min="1" max="5" name="skor" id="skor"
                            value="{{ old('skor', $masterKpi->skor) }}" class="@error('skor') invalid @enderror">
                        @error('skor')
                            <b>{{ $message }}</b>
                        @enderror
                    </div>
                    <div class="master-kpi-edit-field"><label for="keterangan">Keterangan</label>
                        <textarea name="keterangan" id="keterangan" rows="2" class="@error('keterangan') invalid @enderror">{{ old('keterangan', $masterKpi->keterangan) }}</textarea>
                        @error('keterangan')
                            <b>{{ $message }}</b>
                        @enderror
                    </div>
                </div>
                <div class="master-kpi-accumulation">
                    <div><span>Bobot indikator ini:</span><strong>{{ number_format($masterKpi->bobot, 2) }}%
                            (Tersimpan)</strong></div>
                    <div class="master-kpi-progress"><i style="width:{{ min((float) $masterKpi->bobot, 100) }}%"></i></div>
                    <p><b>Keterangan:</b> <em>{{ ucfirst($masterKpi->arah_capaian) }}</em> berarti
                        {{ $masterKpi->arah_capaian === 'maksimal' ? 'semakin tinggi realisasi capaian, semakin baik.' : 'semakin rendah realisasi capaian, semakin baik.' }}
                    </p>
                </div>
                <div class="master-kpi-edit-actions"><button type="submit"><i class="fa-regular fa-floppy-disk"></i>
                        Update</button><a href="{{ route('master.master-kpi.index') }}">Batal</a></div>
            </form>

        </section>
    </div>
@endsection
@push('styles')
    <style>
        body.role-master .master-kpi-edit {
            width: 100%;
            max-width: 560px;
            margin: auto;
            padding: 1rem 1rem 0;
            box-sizing: border-box;
            color: #14223d
        }

        .master-kpi-edit-breadcrumb {
            display: none;
        }

        .master-kpi-edit-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .5rem;
            margin: 0 -1rem;
            padding: .9rem 1rem;
            background: #fff;
            border-bottom: 1px solid #e3ebf3
        }

        .master-kpi-edit-topbar>a {
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

        .master-kpi-edit-crumb {
            display: flex;
            align-items: center;
            gap: .5rem;
            color: #0875ad;
            font-size: .68rem;
            white-space: nowrap
        }

        .master-kpi-edit-crumb a {
            color: #91a1b8;
            text-decoration: none
        }

        .master-kpi-edit-crumb i {
            font-size: .48rem;
            color: #9caabd
        }

        .master-kpi-edit-heading {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: .5rem;
            padding: 1.4rem 0 1rem
        }

        .master-kpi-edit-heading h1 {
            margin: 0;
            font-size: 1rem
        }

        .master-kpi-edit-heading p {
            margin: .35rem 0 0;
            color: #647895;
            font-size: .8rem
        }

        .master-kpi-edit-heading>span {
            padding: .42rem .62rem;
            color: #b15c00;
            background: #fff3d4;
            border: 1px solid #ffd27b;
            border-radius: 999px;
            font-size: .65rem;
            white-space: nowrap
        }

        .master-kpi-edit-heading>span i {
            font-size: .4rem
        }

        .master-kpi-rule {
            display: flex;
            gap: .75rem;
            margin-bottom: 1.1rem;
            padding: 1rem;
            color: #0875ad;
            background: #e9fbff;
            border: 1px solid #85eaff;
            border-radius: 16px
        }

        .master-kpi-rule>i {
            margin-top: .15rem;
            font-size: 1rem
        }

        .master-kpi-rule strong {
            display: block;
            color: #14223d;
            font-size: .78rem
        }

        .master-kpi-rule p {
            margin: .3rem 0 0;
            color: #526b8c;
            font-size: .74rem;
            line-height: 1.6
        }

        .master-kpi-rule b {
            color: #14223d
        }

        .master-kpi-edit-card {
            overflow: hidden;
            background: #fff;
            border: 1px solid #dfe8f1;
            border-radius: 19px;
            box-shadow: 0 5px 17px #2334550b
        }

        .master-kpi-edit-card-title {
            display: flex;
            align-items: center;
            gap: .7rem;
            padding: 1.1rem 1.2rem;
            border-bottom: 1px solid #e8eef5;
            background: #fbfcfe
        }

        .master-kpi-edit-card-title>span {
            display: grid;
            place-items: center;
            width: 41px;
            height: 41px;
            color: #078dcc;
            background: #e8f7ff;
            border-radius: 12px
        }

        .master-kpi-edit-card-title h2 {
            flex: 1;
            margin: 0;
            font-size: .9rem;
            letter-spacing: .03em
        }

        .master-kpi-edit-card-title code {
            padding: .45rem .55rem;
            color: #526480;
            background: #eef2f6;
            border-radius: 6px;
            font-size: .65rem
        }

        .master-kpi-edit-card form {
            padding: 1.45rem 1.2rem 1.2rem
        }

        .master-kpi-edit-field {
            margin-bottom: .85rem
        }

        .master-kpi-edit-field label {
            display: flex;
            align-items: center;
            gap: .2rem;
            margin-bottom: .42rem;
            font-size: .78rem;
            font-weight: 600
        }

        .master-kpi-edit-field label em {
            color: #e54862;
            font-style: normal
        }

        .master-kpi-edit-field label small {
            margin-left: auto;
            color: #71829c;
            font-size: .66rem;
            font-weight: 400
        }

        .master-kpi-edit-field input,
        .master-kpi-edit-field select,
        .master-kpi-edit-field textarea {
            box-sizing: border-box;
            width: 100%;
            height: 48px;
            padding: 0 .9rem;
            color: #263653;
            background: #f9fbfd;
            border: 1px solid #cbd9e7;
            border-radius: 14px;
            outline: 0;
            font: inherit;
            font-size: .78rem
        }

        .master-kpi-edit-field textarea {
            min-height: 48px;
            padding-top: .75rem;
            padding-bottom: .75rem;
            resize: vertical
        }

        .master-kpi-edit-field input:focus,
        .master-kpi-edit-field select:focus,
        .master-kpi-edit-field textarea:focus {
            border-color: #078dcc;
            box-shadow: 0 0 0 3px #078dcc18
        }

        .master-kpi-edit-field input.invalid,
        .master-kpi-edit-field select.invalid,
        .master-kpi-edit-field textarea.invalid {
            border-color: #e54862
        }

        .edit-kpi-input {
            position: relative
        }

        .edit-kpi-input input {
            padding-right: 2.5rem
        }

        .edit-kpi-input i {
            position: absolute;
            right: .9rem;
            top: 50%;
            transform: translateY(-50%);
            color: #078dcc
        }

        .suffix-input {
            position: relative
        }

        .suffix-input input {
            padding-right: 3.2rem
        }

        .suffix-input span {
            position: absolute;
            right: .8rem;
            top: 50%;
            transform: translateY(-50%);
            color: #91a1b8;
            font-size: .7rem
        }

        .master-kpi-edit-field>b {
            display: block;
            margin-top: .3rem;
            color: #e54862;
            font-size: .65rem;
            font-weight: 500
        }

        .master-kpi-edit-two {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .7rem
        }

        .master-kpi-accumulation {
            margin: .15rem 0 1.25rem;
            padding: .85rem;
            color: #607492;
            background: #f5f8fb;
            border: 1px solid #dce6f0;
            border-radius: 14px;
            font-size: .7rem
        }

        .master-kpi-accumulation>div:first-child {
            display: flex;
            justify-content: space-between;
            gap: .5rem
        }

        .master-kpi-accumulation strong {
            padding: .38rem .5rem;
            color: #087b5d;
            background: #e0faec;
            border: 1px solid #91e2b8;
            border-radius: 6px;
            font-size: .65rem;
            white-space: nowrap
        }

        .master-kpi-progress {
            height: 7px;
            margin: .7rem 0 .55rem;
            background: #d9e5f1;
            border-radius: 99px
        }

        .master-kpi-progress i {
            display: block;
            height: 100%;
            background: linear-gradient(90deg, #2168ed, #25b9e4);
            border-radius: inherit
        }

        .master-kpi-accumulation p {
            margin: 0;
            line-height: 1.5
        }

        .master-kpi-accumulation em {
            color: #078dcc;
            font-weight: 600;
            font-style: normal
        }

        .master-kpi-edit-actions {
            display: grid;
            gap: .55rem
        }

        .master-kpi-edit-actions button,
        .master-kpi-edit-actions a {
            width: 100%;
            box-sizing: border-box;
            padding: .86rem;
            text-align: center;
            border-radius: 13px;
            font: inherit;
            font-size: .8rem;
            text-decoration: none
        }

        .master-kpi-edit-actions button {
            color: #fff;
            background: #078dcc;
            border: 0;
            box-shadow: 0 4px 9px #078dcc35;
            font-weight: 600
        }

        .master-kpi-edit-actions a {
            color: #526480;
            background: #fff;
            border: 1px solid #cbd9e7
        }

        .master-kpi-delete {
            padding: 1.1rem 1.2rem;
            border-top: 1px solid #e8eef5;
            text-align: center
        }

        .master-kpi-delete form {
            margin: 0
        }

        .master-kpi-delete button {
            color: #e54862;
            background: none;
            border: 0;
            font: inherit;
            font-size: .75rem
        }

        .master-kpi-delete button i {
            margin-right: .25rem
        }

        @media(max-width:380px) {
            body.role-master .master-kpi-edit {
                padding-inline: .75rem
            }

            .master-kpi-edit-topbar {
                margin-inline: -.75rem;
                padding-inline: .75rem
            }

            .master-kpi-edit-crumb a,
            .master-kpi-edit-crumb i {
                display: none
            }

            .master-kpi-edit-heading h1 {
                font-size: 1.3rem
            }

            .master-kpi-edit-heading p {
                font-size: .72rem
            }

            .master-kpi-edit-card-title,
            .master-kpi-edit-card form {
                padding-inline: 1rem
            }

            .master-kpi-edit-card-title h2 {
                font-size: .78rem
            }

            .master-kpi-edit-card-title code {
                font-size: .58rem
            }

            .master-kpi-edit-two {
                gap: .45rem
            }

            .master-kpi-edit-field input,
            .master-kpi-edit-field select,
            .master-kpi-edit-field textarea {
                font-size: .68rem
            }

            .master-kpi-accumulation>div:first-child {
                align-items: flex-start;
                flex-direction: column
            }
        }

        @media (min-width: 768px) {
            body.role-master .master-kpi-edit {
                width: 100%;
                max-width: none;
                margin: 0;
                padding: 1rem clamp(1.25rem, 2.4vw, 2.5rem) 2rem;
                color: #1b2938;
            }

            .master-kpi-edit-heading {
                padding: .5rem 0 .85rem;
            }

            .master-kpi-edit-breadcrumb {
                display: flex;
                align-items: center;
                gap: .4rem;
                margin-bottom: .45rem;
                color: #718092;
                font-size: .64rem;
            }

            .master-kpi-edit-breadcrumb a {
                color: #718092;
                text-decoration: none;
            }

            .master-kpi-edit-breadcrumb a:hover,
            .master-kpi-edit-breadcrumb>span {
                color: #1769d2;
            }

            .master-kpi-edit-breadcrumb i {
                color: #a0acb9;
                font-size: .48rem;
            }

            .master-kpi-edit-heading h1 {
                color: #1b2938;
                font-size: 1.4rem;
                font-weight: 700;
            }

            .master-kpi-edit-heading h1 i {
                color: #1769d2;
                font-size: 1rem;
            }

            .master-kpi-edit-heading p {
                margin-top: .25rem;
                font-size: .75rem;
            }

            .master-kpi-rule,
            .master-kpi-edit-card {
                width: 100%;
                max-width: 1120px;
                margin-right: auto;
                margin-left: auto;
            }

            .master-kpi-rule {
                gap: .55rem;
                margin-bottom: .8rem;
                padding: .7rem .8rem;
                border-radius: 6px;
            }

            .master-kpi-rule strong {
                font-size: .7rem;
            }

            .master-kpi-rule p {
                margin-top: .15rem;
                font-size: .63rem;
            }

            .master-kpi-edit-card {
                border-color: #e2e8ef;
                border-radius: 8px;
                box-shadow: none;
            }

            .master-kpi-edit-card-title {
                gap: .6rem;
                padding: .8rem 1rem;
            }

            .master-kpi-edit-card-title>span {
                width: 32px;
                height: 32px;
                border-radius: 6px;
            }

            .master-kpi-edit-card-title h2 {
                font-size: .78rem;
                letter-spacing: 0;
            }

            .master-kpi-edit-card-title code {
                padding: .28rem .42rem;
                border-radius: 4px;
                font-size: .59rem;
            }

            .master-kpi-edit-card form {
                padding: 1rem;
            }

            .master-kpi-edit-field {
                margin-bottom: .7rem;
            }

            .master-kpi-edit-field label {
                margin-bottom: .32rem;
                color: #34445e;
                font-size: .66rem;
            }

            .master-kpi-edit-field label small {
                font-size: .6rem;
            }

            .master-kpi-edit-field input,
            .master-kpi-edit-field select,
            .master-kpi-edit-field textarea {
                height: 38px;
                padding: 0 .7rem;
                background: #fff;
                border-radius: 5px;
                font-size: .68rem;
            }

            .master-kpi-edit-field textarea {
                height: auto;
                min-height: 56px;
                padding: .6rem .7rem;
            }

            .master-kpi-edit-two {
                gap: .8rem;
            }

            .master-kpi-edit-field>b {
                font-size: .61rem;
            }

            .master-kpi-accumulation {
                margin: .15rem 0 .85rem;
                padding: .7rem .75rem;
                border-radius: 5px;
                font-size: .64rem;
            }

            .master-kpi-accumulation strong {
                padding: .28rem .4rem;
                font-size: .6rem;
            }

            .master-kpi-progress {
                height: 6px;
                margin: .55rem 0 .45rem;
            }

            .master-kpi-edit-actions {
                display: flex;
                justify-content: flex-end;
                gap: .5rem;
            }

            .master-kpi-edit-actions button,
            .master-kpi-edit-actions a {
                width: auto;
                min-width: 100px;
                padding: .62rem .8rem;
                border-radius: 5px;
                font-size: .68rem;
            }

            .master-kpi-edit-actions button {
                order: 2;
                background: #078dcc;
                box-shadow: none;
            }

            .master-kpi-edit-actions a {
                order: 1;
            }

            .master-kpi-delete {
                width: 100%;
                max-width: 1120px;
                margin: 0 auto;
                padding: .8rem 0;
                text-align: right;
            }
        }
    </style>
@endpush
