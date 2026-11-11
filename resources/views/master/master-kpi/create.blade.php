@extends('layouts.app')
@section('title', 'Tambah Master KPI')
@section('content')
    <div class="master-kpi-create">
        <header class="master-kpi-create-heading">
            <div>
                <nav class="master-kpi-create-breadcrumb" aria-label="Breadcrumb">
                    <a href="{{ route('master.dashboard') }}">Beranda</a>
                    <i class="fa-solid fa-chevron-right"></i>
                    <a href="{{ route('master.master-kpi.index') }}">Master KPI</a>
                    <i class="fa-solid fa-chevron-right"></i>
                    <span>Tambah</span>
                </nav>
                <h1>
                    <i class="fa-solid fa-bullseye"></i>
                    Tambah Master KPI
                </h1>
                <p>Tambahkan indikator kinerja utama baru untuk jabatan target.</p>
            </div>
        </header>


        <section class="master-kpi-create-card">
            <div class="master-kpi-create-card-title"><span><i class="fa-solid fa-bullseye"></i></span>
                <h2>Indikator Kinerja Utama (KPI)</h2><b>Mode Input</b>
            </div>



            <form action="{{ route('master.master-kpi.store') }}" method="POST">@csrf
                <div class="master-kpi-create-field"><label for="jabatan_id">Jabatan <em>*</em><small>Wajib
                            dipilih</small></label><select name="jabatan_id" id="jabatan_id"
                        class="@error('jabatan_id') invalid @enderror">
                        <option value="">-- Pilih Jabatan --</option>
                        @foreach ($jabatans as $jabatan)
                            <option value="{{ $jabatan->id }}"
                                {{ old('jabatan_id', $jabatanId) == $jabatan->id ? 'selected' : '' }}>
                                {{ $jabatan->nama_jabatan }} ({{ $jabatan->divisi->nama_divisi ?? '-' }})</option>
                        @endforeach
                    </select>
                    <p>Pilih jabatan yang akan dievaluasi melalui indikator ini.</p>
                    @error('jabatan_id')
                        <b>{{ $message }}</b>
                    @enderror
                </div>
                <div class="master-kpi-create-field"><label for="indikator">Indikator Kinerja Utama (KPI)
                        <em>*</em><small>Wajib
                            diisi</small></label><input type="text" name="indikator" id="indikator"
                        value="{{ old('indikator') }}" placeholder="Contoh: Jumlah Meter Dibaca"
                        class="@error('indikator') invalid @enderror">
                    @error('indikator')
                        <b>{{ $message }}</b>
                    @enderror
                </div>


                <div class="master-kpi-create-two">
                    <div class="master-kpi-create-field"><label for="target_default">Target <em>*</em></label><input
                            type="number" step="0.01" name="target_default" id="target_default"
                            value="{{ old('target_default') }}" placeholder="0"
                            class="@error('target_default') invalid @enderror">
                        @error('target_default')
                            <b>{{ $message }}</b>
                        @enderror
                    </div>


                    <div class="master-kpi-create-field"><label for="bobot">Bobot (%) <em>*</em></label>
                        <div class="suffix-input"><input type="number" step="0.01" min="0" max="60"
                                name="bobot" id="bobot" value="{{ old('bobot') }}" placeholder="0"
                                class="@error('bobot') invalid @enderror"><span>%</span></div>
                        @error('bobot')
                            <b>{{ $message }}</b>
                        @enderror
                    </div>
                </div><small class="master-kpi-total">Total akumulasi bobot KPI adalah maksimal 60% per jabatan.</small>

                <div class="master-kpi-create-two">
                    <div class="master-kpi-create-field"><label for="realisasi">Realisasi</label><input type="number"
                            step="0.01" min="0" name="realisasi" id="realisasi" value="{{ old('realisasi') }}"
                            placeholder="0" class="@error('realisasi') invalid @enderror">
                        @error('realisasi')
                            <b>{{ $message }}</b>
                        @enderror
                    </div>
                    <div class="master-kpi-create-field"><label for="persen_capaian">% Capaian</label>
                        <div class="suffix-input"><input type="number" step="0.01" min="0" name="persen_capaian"
                                id="persen_capaian" value="{{ old('persen_capaian') }}" placeholder="0"
                                class="@error('persen_capaian') invalid @enderror"><span>%</span></div>
                        @error('persen_capaian')
                            <b>{{ $message }}</b>
                        @enderror
                    </div>
                </div>

                <div class="master-kpi-create-two">
                    <div class="master-kpi-create-field"><label for="skor">Skor (1–5)</label><input type="number"
                            min="1" max="5" name="skor" id="skor" value="{{ old('skor') }}"
                            placeholder="1 - 5" class="@error('skor') invalid @enderror">
                        @error('skor')
                            <b>{{ $message }}</b>
                        @enderror
                    </div>
                    <div class="master-kpi-create-field"><label for="keterangan">Keterangan</label>
                        <textarea name="keterangan" id="keterangan" rows="3" placeholder="Tambahkan keterangan KPI..."
                            class="@error('keterangan') invalid @enderror">{{ old('keterangan') }}</textarea>
                        @error('keterangan')
                            <b>{{ $message }}</b>
                        @enderror
                    </div>
                </div>

                <div class="master-kpi-direction-note"><strong>Keterangan Arah:</strong> <span>Maksimal</span> = semakin
                    tinggi capaian semakin baik. <b>Minimal</b> = semakin rendah capaian semakin baik (misal angka
                    kebocoran).</div>
                <div class="master-kpi-create-actions"><button type="submit"><i class="fa-regular fa-floppy-disk"></i>
                        Simpan Master KPI</button><a href="{{ route('master.master-kpi.index') }}">Batal</a></div>


            </form>

        </section>


        <aside class="master-kpi-tip"><span><i class="fa-regular fa-circle-check"></i></span>
            <div><strong>Standar Indikator KPI</strong>
                <p>Setiap indikator yang disimpan akan langsung terhubung ke modul penilaian kinerja bulanan pegawai pada
                    unit jabatan terkait.</p>
            </div>
        </aside>
    </div>
@endsection




@push('styles')
    <style>
        body.role-master .master-kpi-create {
            width: 100%;
            max-width: 560px;
            margin: auto;
            padding: 1rem 1rem 0;
            box-sizing: border-box;
            color: #14223d
        }

        .master-kpi-create-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .6rem;
            margin: 0 -1rem;
            padding: .9rem 1rem;
            background: #fff;
            border-bottom: 1px solid #e3ebf3
        }

        .master-kpi-create-topbar>a {
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

        .master-kpi-create-crumb {
            display: flex;
            align-items: center;
            gap: .5rem;
            color: #0875ad;
            font-size: .68rem;
            white-space: nowrap
        }

        .master-kpi-create-crumb a {
            color: #91a1b8;
            text-decoration: none
        }

        .master-kpi-create-crumb i {
            font-size: .48rem;
            color: #9caabd
        }

        .master-kpi-create-heading {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: .5rem;
            padding: 1.4rem 0 1.2rem
        }

        .master-kpi-create-heading h1 {
            margin: 0;
            font-size: 1rem
        }

        .master-kpi-create-heading p {
            margin: .35rem 0 0;
            color: #647895;
            font-size: .8rem
        }

        .master-kpi-create-heading>span {
            padding: .42rem .62rem;
            color: #087b5d;
            background: #dff9eb;
            border: 1px solid #9ceaca;
            border-radius: 999px;
            font-size: .65rem;
            white-space: nowrap
        }

        .master-kpi-create-card {
            overflow: hidden;
            background: #fff;
            border: 1px solid #dfe8f1;
            border-radius: 19px;
            box-shadow: 0 5px 17px #2334550b
        }

        .master-kpi-create-card-title {
            display: flex;
            align-items: center;
            gap: .7rem;
            padding: 1rem 1.2rem;
            background: #f3faff;
            border-bottom: 1px solid #e3edf5
        }

        .master-kpi-create-card-title>span {
            display: grid;
            place-items: center;
            width: 34px;
            height: 34px;
            flex: 0 0 34px;
            color: #078dcc;
            background: #dff3ff;
            border-radius: 10px
        }

        .master-kpi-create-card-title h2 {
            margin: 0;
            font-size: .84rem
        }

        .master-kpi-create-card-title>b {
            margin-left: auto;
            padding: .35rem .5rem;
            color: #087b5d;
            background: #ecfff5;
            border: 1px solid #9be6c0;
            border-radius: 999px;
            font-size: .6rem;
            font-weight: 600;
            white-space: nowrap
        }

        .master-kpi-create-card form {
            padding: 1.45rem 1.2rem 1.2rem
        }

        .master-kpi-create-field {
            margin-bottom: .9rem
        }

        .master-kpi-create-field label {
            display: flex;
            align-items: center;
            gap: .2rem;
            margin-bottom: .42rem;
            font-size: .78rem;
            font-weight: 600
        }

        .master-kpi-create-field label em {
            color: #e54862;
            font-style: normal
        }

        .master-kpi-create-field label small {
            margin-left: auto;
            color: #91a1b8;
            font-size: .68rem;
            font-weight: 400
        }

        .master-kpi-create-field input,
        .master-kpi-create-field select,
        .master-kpi-create-field textarea {
            box-sizing: border-box;
            width: 100%;
            height: 48px;
            padding: 0 .9rem;
            color: #263653;
            background: #fff;
            border: 1px solid #cbd9e7;
            border-radius: 14px;
            outline: 0;
            font: inherit;
            font-size: .78rem
        }

        .master-kpi-create-field textarea {
            min-height: 48px;
            padding-top: .75rem;
            padding-bottom: .75rem;
            resize: vertical
        }

        .master-kpi-create-field input:focus,
        .master-kpi-create-field select:focus,
        .master-kpi-create-field textarea:focus {
            border-color: #078dcc;
            box-shadow: 0 0 0 3px #078dcc18
        }

        .master-kpi-create-field input.invalid,
        .master-kpi-create-field select.invalid,
        .master-kpi-create-field textarea.invalid {
            border-color: #e54862
        }

        .master-kpi-create-field input::placeholder {
            color: #94a6be
        }

        .master-kpi-create-field p,
        .master-kpi-create-field>b {
            display: block;
            margin: .4rem 0 0;
            color: #91a1b8;
            font-size: .66rem;
            font-weight: 400
        }

        .master-kpi-create-field>b {
            color: #e54862
        }

        .suffix-input {
            position: relative
        }

        .suffix-input input {
            padding-right: 2.5rem
        }

        .suffix-input span {
            position: absolute;
            right: .9rem;
            top: 50%;
            transform: translateY(-50%);
            color: #91a1b8;
            font-weight: 600
        }

        .master-kpi-create-two {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .7rem
        }

        .master-kpi-total {
            display: block;
            margin: -.15rem 0 1rem;
            color: #91a1b8;
            font-size: .66rem
        }

        .master-kpi-direction-note {
            margin: .1rem 0 1.25rem;
            padding: .8rem;
            color: #607492;
            background: #f5f8fb;
            border: 1px solid #e3ebf3;
            border-radius: 13px;
            font-size: .68rem;
            line-height: 1.55
        }

        .master-kpi-direction-note strong {
            color: #263653
        }

        .master-kpi-direction-note span {
            color: #087b5d
        }

        .master-kpi-direction-note b {
            color: #e07a00
        }

        .master-kpi-create-actions {
            display: grid;
            gap: .55rem
        }

        .master-kpi-create-actions button,
        .master-kpi-create-actions a {
            width: 100%;
            box-sizing: border-box;
            padding: .86rem;
            text-align: center;
            border-radius: 13px;
            font: inherit;
            font-size: .8rem;
            text-decoration: none
        }

        .master-kpi-create-actions button {
            color: #fff;
            background: #078dcc;
            border: 0;
            box-shadow: 0 4px 9px #078dcc35;
            font-weight: 600
        }

        .master-kpi-create-actions a {
            color: #526480;
            background: #f2f6fa;
            border: 1px solid #d5e0eb
        }

        .master-kpi-tip {
            display: flex;
            gap: .75rem;
            margin-top: 1rem;
            padding: 1.1rem 1rem;
            color: #0875ad;
            background: #eaf7ff;
            border: 1px solid #bfe7ff;
            border-radius: 17px
        }

        .master-kpi-tip>span {
            display: grid;
            place-items: center;
            width: 34px;
            height: 34px;
            flex: 0 0 34px;
            color: #fff;
            background: #078dcc;
            border-radius: 9px
        }

        .master-kpi-tip strong {
            display: block;
            margin: .1rem 0 .3rem;
            color: #07527c;
            font-size: .82rem
        }

        .master-kpi-tip p {
            margin: 0;
            color: #0875ad;
            font-size: .73rem;
            line-height: 1.5
        }

        @media(max-width:380px) {
            body.role-master .master-kpi-create {
                padding-inline: .75rem
            }

            .master-kpi-create-topbar {
                margin-inline: -.75rem;
                padding-inline: .75rem
            }

            .master-kpi-create-crumb a,
            .master-kpi-create-crumb i {
                display: none
            }

            .master-kpi-create-heading h1 {
                font-size: 1.3rem
            }

            .master-kpi-create-heading p {
                font-size: .72rem
            }

            .master-kpi-create-card-title,
            .master-kpi-create-card form {
                padding-inline: 1rem
            }

            .master-kpi-create-card-title h2 {
                font-size: .78rem
            }

            .master-kpi-create-card-title>b {
                font-size: .56rem
            }

            .master-kpi-create-two {
                gap: .45rem
            }

            .master-kpi-create-field input,
            .master-kpi-create-field select {
                font-size: .68rem
            }
        }

        @media (min-width: 768px) {
            body.role-master .master-kpi-create {
                width: 100%;
                max-width: none;
                margin: 0;
                padding: 1rem clamp(1.25rem, 2.4vw, 2.5rem) 2rem;
                color: #1b2938;
            }

            .master-kpi-create-heading {
                padding: .5rem 0 1rem;
            }

            .master-kpi-create-breadcrumb {
                display: flex;
                align-items: center;
                gap: .4rem;
                margin-bottom: .45rem;
                color: #718092;
                font-size: .64rem;
            }

            .master-kpi-create-breadcrumb a {
                color: #718092;
                text-decoration: none;
            }

            .master-kpi-create-breadcrumb a:hover,
            .master-kpi-create-breadcrumb>span {
                color: #1769d2;
            }

            .master-kpi-create-breadcrumb i {
                color: #a0acb9;
                font-size: .48rem;
            }

            .master-kpi-create-heading h1 {
                color: #1b2938;
                font-size: 1.4rem;
                font-weight: 700;
            }

            .master-kpi-create-heading h1 i {
                color: #1769d2;
                font-size: 1rem;
            }

            .master-kpi-create-heading p {
                margin-top: .25rem;
                font-size: .75rem;
            }

            .master-kpi-create-card {
                width: 100%;
                max-width: 1120px;
                margin: 0 auto;
                border-color: #e2e8ef;
                border-radius: 8px;
                box-shadow: none;
            }

            .master-kpi-create-card-title {
                gap: .6rem;
                padding: .8rem 1rem;
                background: #f9fbfd;
            }

            .master-kpi-create-card-title>span {
                width: 32px;
                height: 32px;
                flex-basis: 32px;
                border-radius: 6px;
                font-size: .8rem;
            }

            .master-kpi-create-card-title h2 {
                flex: 1;
                color: #25364a;
                font-size: .78rem;
                letter-spacing: 0;
            }

            .master-kpi-create-card-title>b {
                padding: .25rem .4rem;
                font-size: .57rem;
            }

            .master-kpi-create-card form {
                padding: 1rem;
            }

            .master-kpi-create-field {
                margin-bottom: .7rem;
            }

            .master-kpi-create-field label {
                margin-bottom: .32rem;
                color: #34445e;
                font-size: .66rem;
            }

            .master-kpi-create-field label small {
                font-size: .6rem;
            }

            .master-kpi-create-field input,
            .master-kpi-create-field select,
            .master-kpi-create-field textarea {
                height: 38px;
                padding: 0 .7rem;
                border-radius: 5px;
                font-size: .68rem;
            }

            .master-kpi-create-field textarea {
                height: auto;
                min-height: 56px;
                padding: .6rem .7rem;
            }

            .master-kpi-create-field p,
            .master-kpi-create-field>b {
                font-size: .61rem;
            }

            .master-kpi-create-two {
                gap: .8rem;
            }

            .master-kpi-total {
                margin-bottom: .75rem;
                font-size: .61rem;
            }

            .master-kpi-direction-note {
                margin-bottom: .9rem;
                padding: .65rem .75rem;
                border-radius: 5px;
                font-size: .63rem;
            }

            .master-kpi-create-actions {
                display: flex;
                justify-content: flex-end;
                gap: .5rem;
            }

            .master-kpi-create-actions button,
            .master-kpi-create-actions a {
                width: auto;
                min-width: 100px;
                padding: .62rem .8rem;
                border-radius: 5px;
                font-size: .68rem;
            }

            .master-kpi-create-actions button {
                order: 2;
                background: #078dcc;
                box-shadow: none;
            }

            .master-kpi-create-actions a {
                order: 1;
            }

            .master-kpi-tip {
                max-width: 1120px;
                margin: .75rem auto 0;
                padding: .7rem .8rem;
                border-radius: 6px;
            }

            .master-kpi-tip>span {
                width: 28px;
                height: 28px;
                flex-basis: 28px;
                border-radius: 5px;
            }

            .master-kpi-tip strong {
                font-size: .7rem;
            }

            .master-kpi-tip p {
                font-size: .63rem;
            }
        }
    </style>
@endpush
