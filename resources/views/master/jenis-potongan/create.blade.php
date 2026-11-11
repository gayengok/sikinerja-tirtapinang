@extends('layouts.app')

@section('title', 'Tambah Jenis Potongan')

@section('content')
    @php
        $tipe = old('tipe_pelanggaran', 'terlambat');
    @endphp

    <div class="master-potongan-create-page">


        <header class="master-potongan-create-heading">
            <div>
                <nav class="master-potongan-create-breadcrumb" aria-label="Breadcrumb">
                    <a href="{{ route('master.dashboard') }}">Beranda</a>
                    <i class="fa-solid fa-chevron-right"></i>
                    <a href="{{ route('master.jenis-potongan.index') }}">Master Data</a>
                    <i class="fa-solid fa-chevron-right"></i>
                    <span>Tambah Potongan</span>
                </nav>
                <h1>
                    <i class="fa-solid fa-file-circle-plus"></i>
                    Tambah Jenis Potongan
                </h1>
                <p>Atur parameter formula pemotongan tunjangan kinerja pegawai berdasarkan kategori disiplin.</p>
            </div>
        </header>



        <section class="master-potongan-create-card">
            <div class="master-potongan-create-card-header">
                <span><i class="fa-solid fa-sliders"></i></span>
                <div><strong>FORMULIR ATURAN POTONGAN</strong><small>Atur kategori dan formula pemotongan Tukin</small>
                </div>
                <b>Mode Tambah</b>
            </div>
            <form action="{{ route('master.jenis-potongan.store') }}" method="POST">
                @csrf
                <div class="master-potongan-create-field">
                    <label for="nama_potongan">Nama Potongan <b>*</b></label>
                    <input type="text" name="nama_potongan" id="nama_potongan"
                        class="@error('nama_potongan') is-invalid @enderror" value="{{ old('nama_potongan') }}"
                        placeholder="Contoh: Terlambat Tingkat I (1–15 Menit)">
                    <p class="master-potongan-create-help">Deskripsi singkat yang tampil pada slip tunjangan kinerja
                        bulanan.</p>
                    @error('nama_potongan')
                        <div class="master-potongan-create-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="master-potongan-create-field">
                    <label for="tipe_pelanggaran">Tipe Pelanggaran <b>*</b></label>
                    <select name="tipe_pelanggaran" id="tipe_pelanggaran"
                        class="@error('tipe_pelanggaran') is-invalid @enderror">
                        <option value="terlambat" {{ $tipe === 'terlambat' ? 'selected' : '' }}>Terlambat</option>
                        <option value="tidak_absen" {{ $tipe === 'tidak_absen' ? 'selected' : '' }}>Tidak Absen</option>
                        <option value="alpa" {{ $tipe === 'alpa' ? 'selected' : '' }}>Alpa</option>
                        <option value="pulang_awal" {{ $tipe === 'pulang_awal' ? 'selected' : '' }}>Pulang Sebelum Waktu
                        </option>
                    </select>
                    @error('tipe_pelanggaran')
                        <div class="master-potongan-create-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="master-potongan-create-section-title"><strong>PARAMETER BATAS WAKTU</strong><span>Unit
                        Menit</span></div>
                <div class="master-potongan-create-range">
                    <div class="master-potongan-create-field">
                        <label for="menit_min">Menit Minimum <small>(Opsional)</small></label>
                        <div class="master-potongan-create-unit"><input type="number" min="0" name="menit_min"
                                id="menit_min" class="@error('menit_min') is-invalid @enderror"
                                value="{{ old('menit_min') }}" placeholder="0"><span>Mnt</span></div>
                        @error('menit_min')
                            <div class="master-potongan-create-error">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="master-potongan-create-field">
                        <label for="menit_max">Menit Maksimum <small>(Opsional)</small></label>
                        <div class="master-potongan-create-unit"><input type="number" min="0" name="menit_max"
                                id="menit_max" class="@error('menit_max') is-invalid @enderror"
                                value="{{ old('menit_max') }}" placeholder="15"><span>Mnt</span></div>
                        @error('menit_max')
                            <div class="master-potongan-create-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="master-potongan-create-field">
                    <label for="persen_potongan">Persentase Potongan Tunjangan <b>*</b></label>
                    <div class="master-potongan-create-unit"><input type="number" step="0.01" min="0"
                            max="100" name="persen_potongan" id="persen_potongan"
                            class="@error('persen_potongan') is-invalid @enderror" value="{{ old('persen_potongan') }}"
                            placeholder="Contoh: 1.50"><span>%</span></div>
                    <p class="master-potongan-create-help">Persen potongan yang diterapkan terhadap komponen Tukin reguler.
                    </p>
                    @error('persen_potongan')
                        <div class="master-potongan-create-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="master-potongan-create-field">
                    <label for="keterangan">Keterangan <small>(Opsional)</small><em>Maks. 500 Karakter</em></label>
                    <textarea name="keterangan" id="keterangan" rows="3" maxlength="500"
                        class="@error('keterangan') is-invalid @enderror"
                        placeholder="Tambahkan catatan khusus, nomor SK Direksi, atau ketentuan pengecualian...">{{ old('keterangan') }}</textarea>
                    @error('keterangan')
                        <div class="master-potongan-create-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="master-potongan-create-info"><i class="fa-solid fa-sun"></i>
                    <p><strong>Info Perhitungan:</strong> Potongan ini akan secara otomatis dikurangkan dari Tunjangan
                        Kinerja (Tukin) bruto pegawai pada kalkulasi rekapitulasi bulanan.</p>
                </div>
                <div class="master-potongan-create-actions"><button type="submit"><i
                            class="fa-regular fa-square-check"></i> Simpan Jenis Potongan</button><a
                        href="{{ route('master.jenis-potongan.index') }}">Batal</a></div>
            </form>
        </section>

        <aside class="master-potongan-create-standard">
            <div><i class="fa-regular fa-circle-question"></i> <strong>Standar Potongan SIKINERJA</strong></div><span>SK
                Direksi 2024</span>
            <p>Potongan berlaku kumulatif dalam satu periode siklus penilaian (tanggal 26 hingga 25 bulan berikutnya).</p>
        </aside>
    </div>
@endsection

@push('styles')
    <style>
        body.role-master .master-potongan-create-page {
            width: 100%;
            max-width: 560px;
            margin: auto;
            padding: 1rem 1rem 0;
            box-sizing: border-box;
            color: #14223d
        }

        .master-potongan-create-breadcrumb {
            display: flex;
            align-items: center;
            gap: .4rem;
            margin-bottom: 1rem;
            color: #14223d;
            font-size: .65rem;
            white-space: nowrap
        }

        .master-potongan-create-breadcrumb a {
            color: #71829c;
            text-decoration: none
        }

        .master-potongan-create-breadcrumb i {
            font-size: .43rem;
            color: #9caabd
        }

        .master-potongan-create-heading {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: .6rem;
            margin-bottom: 1.2rem
        }

        .master-potongan-create-heading h1 {
            margin: 0;
            font-size: 1rem
        }

        .master-potongan-create-heading p {
            max-width: 390px;
            margin: .3rem 0;
            color: #71829c;
            font-size: .73rem;
            line-height: 1.5
        }

        .master-potongan-create-back {
            padding: .55rem .7rem;
            color: #14223d;
            background: #f3f6f9;
            border-radius: 10px;
            text-decoration: none;
            font-size: .7rem;
            white-space: nowrap
        }

        .master-potongan-create-card {
            padding: 1.2rem 1rem;
            background: #fff;
            border: 1px solid #e0e8f1;
            border-radius: 20px;
            box-shadow: 0 5px 16px #2334550b
        }

        .master-potongan-create-field {
            margin-bottom: 1rem
        }

        .master-potongan-create-field label {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: .45rem;
            font-size: .75rem
        }

        .master-potongan-create-field label b {
            color: #e8475f
        }

        .master-potongan-create-field label small {
            color: #71829c;
            font-size: .66rem
        }

        .master-potongan-create-field label em {
            margin-left: auto;
            color: #91a1b8;
            font-size: .63rem;
            font-style: normal;
            font-weight: 400
        }

        .master-potongan-create-field input,
        .master-potongan-create-field select,
        .master-potongan-create-field textarea {
            width: 100%;
            padding: .7rem .8rem;
            color: #14223d;
            background: #fff;
            border: 1px solid #c9d6e5;
            border-radius: 12px;
            box-sizing: border-box;
            font: inherit;
            font-size: .76rem;
            outline: none
        }

        .master-potongan-create-field input,
        .master-potongan-create-field select {
            min-height: 47px
        }

        .master-potongan-create-field textarea {
            resize: vertical;
            line-height: 1.5
        }

        .master-potongan-create-field input:focus,
        .master-potongan-create-field select:focus,
        .master-potongan-create-field textarea:focus {
            border-color: #078dcc;
            box-shadow: 0 0 0 3px #078dcc1a
        }

        .master-potongan-create-help {
            margin: .4rem 0 0;
            color: #91a1b8;
            font-size: .67rem;
            line-height: 1.45
        }

        .master-potongan-create-error {
            margin-top: .3rem;
            color: #dc3545;
            font-size: .66rem
        }

        .master-potongan-create-field .is-invalid {
            border-color: #dc3545
        }

        .master-potongan-create-section-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 1.1rem 0 .8rem;
            padding-top: .9rem;
            border-top: 1px solid #e5edf4;
            font-size: .72rem;
            letter-spacing: .03em
        }

        .master-potongan-create-section-title span {
            padding: .35rem .5rem;
            color: #078dcc;
            background: #eff9ff;
            border-radius: 999px;
            font-size: .63rem;
            font-weight: 500;
            letter-spacing: 0
        }

        .master-potongan-create-range {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .75rem
        }

        .master-potongan-create-unit {
            display: flex;
            align-items: center;
            border: 1px solid #c9d6e5;
            border-radius: 12px;
            overflow: hidden
        }

        .master-potongan-create-unit input {
            border: 0;
            border-radius: 0
        }

        .master-potongan-create-unit span {
            margin-right: .6rem;
            padding: .4rem .5rem;
            color: #526480;
            background: #f0f5f9;
            border-radius: 6px;
            font-size: .69rem
        }

        .master-potongan-create-info {
            display: flex;
            gap: .7rem;
            margin: 1.1rem 0;
            padding: .95rem;
            color: #526b8c;
            background: #effaff;
            border: 1px solid #bce7ff;
            border-radius: 15px
        }

        .master-potongan-create-info>i {
            margin-top: .15rem;
            color: #078dcc
        }

        .master-potongan-create-info p {
            margin: 0;
            font-size: .69rem;
            line-height: 1.55
        }

        .master-potongan-create-info strong {
            color: #07527c
        }

        .master-potongan-create-actions {
            display: grid;
            gap: .7rem
        }

        .master-potongan-create-actions button,
        .master-potongan-create-actions a {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .45rem;
            min-height: 51px;
            border-radius: 13px;
            font: inherit;
            font-size: .8rem;
            text-decoration: none
        }

        .master-potongan-create-actions button {
            color: #fff;
            background: #078dcc;
            border: 0;
            box-shadow: 0 4px 8px #078dcc33;
            font-weight: 700
        }

        .master-potongan-create-actions a {
            color: #14223d;
            background: #f3f6f9
        }

        .master-potongan-create-standard {
            position: relative;
            margin-top: 1rem;
            padding: 1rem;
            color: #526b8c;
            background: #f8fbfe;
            border: 1px solid #dce7f2;
            border-radius: 15px;
            font-size: .7rem
        }

        .master-potongan-create-standard>div {
            color: #14223d
        }

        .master-potongan-create-standard>div i {
            margin-right: .35rem;
            color: #7c91ab
        }

        .master-potongan-create-standard>span {
            position: absolute;
            top: .85rem;
            right: .75rem;
            padding: .35rem .5rem;
            color: #526480;
            background: #eaf0f6;
            border-radius: 6px;
            font-size: .62rem
        }

        .master-potongan-create-standard p {
            margin: .7rem 0 0;
            line-height: 1.5
        }

        .master-potongan-create-breadcrumb,
        .master-potongan-create-card-header {
            display: none;
        }

        @media(max-width:380px) {
            body.role-master .master-potongan-create-page {
                padding-inline: .75rem
            }

            .master-potongan-create-heading h1 {
                font-size: 1.22rem
            }

            .master-potongan-create-heading p {
                font-size: .68rem
            }

            .master-potongan-create-card {
                padding-inline: .8rem
            }

            .master-potongan-create-range {
                gap: .5rem
            }

            .master-potongan-create-field label {
                font-size: .7rem
            }
        }

        @media (min-width: 768px) {
            body.role-master .master-potongan-create-page {
                width: 100%;
                max-width: none;
                margin: 0;
                padding: 1rem clamp(1.25rem, 2.4vw, 2.5rem) 2rem;
                color: #1b2938;
            }

            .master-potongan-create-heading {
                align-items: flex-start;
                margin-bottom: .85rem;
            }

            .master-potongan-create-breadcrumb {
                display: flex;
                align-items: center;
                gap: .4rem;
                margin-bottom: .4rem;
                color: #718092;
                font-size: .64rem;
            }

            .master-potongan-create-breadcrumb a {
                color: #718092;
                text-decoration: none;
            }

            .master-potongan-create-breadcrumb>span,
            .master-potongan-create-breadcrumb a:hover {
                color: #1769d2;
            }

            .master-potongan-create-breadcrumb i {
                color: #a0acb9;
                font-size: .48rem;
            }

            .master-potongan-create-heading h1 {
                display: flex;
                align-items: center;
                gap: .5rem;
                color: #1b2938;
                font-size: 1.35rem;
                font-weight: 700;
            }

            .master-potongan-create-heading h1 i {
                display: grid;
                place-items: center;
                width: 30px;
                height: 30px;
                color: #078dcc;
                background: #e8f7ff;
                border-radius: 6px;
                font-size: .82rem;
            }

            .master-potongan-create-heading p {
                margin: .2rem 0 0 2.4rem;
                font-size: .7rem;
            }

            .master-potongan-create-card,
            .master-potongan-create-standard {
                width: 100%;
                max-width: 1120px;
                margin-right: auto;
                margin-left: auto;
            }

            .master-potongan-create-card {
                padding: 0;
                border-color: #e2e8ef;
                border-radius: 8px;
                box-shadow: none;
            }

            .master-potongan-create-card-header {
                display: flex;
                align-items: center;
                gap: .6rem;
                padding: .8rem 1rem;
                background: #f9fbfd;
                border-bottom: 1px solid #e8eef5;
            }

            .master-potongan-create-card-header>span {
                display: grid;
                place-items: center;
                width: 32px;
                height: 32px;
                flex: 0 0 32px;
                color: #078dcc;
                background: #e8f7ff;
                border-radius: 6px;
            }

            .master-potongan-create-card-header>div strong,
            .master-potongan-create-card-header>div small {
                display: block;
            }

            .master-potongan-create-card-header>div strong {
                color: #25364a;
                font-size: .72rem;
            }

            .master-potongan-create-card-header>div small {
                margin-top: .12rem;
                color: #8290a0;
                font-size: .59rem;
            }

            .master-potongan-create-card-header>b {
                margin-left: auto;
                padding: .24rem .4rem;
                color: #087c50;
                background: #e6f7ef;
                border-radius: 999px;
                font-size: .56rem;
            }

            .master-potongan-create-card form {
                padding: .9rem 1rem;
            }

            .master-potongan-create-field {
                margin-bottom: .7rem;
            }

            .master-potongan-create-field label {
                margin-bottom: .3rem;
                font-size: .65rem;
            }

            .master-potongan-create-field label small,
            .master-potongan-create-field label em {
                font-size: .58rem;
            }

            .master-potongan-create-field input,
            .master-potongan-create-field select,
            .master-potongan-create-field textarea {
                min-height: 36px;
                padding: .42rem .65rem;
                border-radius: 5px;
                font-size: .66rem;
            }

            .master-potongan-create-field textarea {
                min-height: 58px;
                padding: .55rem .65rem;
            }

            .master-potongan-create-help,
            .master-potongan-create-error {
                margin-top: .25rem;
                font-size: .59rem;
            }

            .master-potongan-create-section-title {
                margin: .75rem 0 .55rem;
                padding-top: .65rem;
                font-size: .65rem;
            }

            .master-potongan-create-section-title span {
                padding: .22rem .35rem;
                border-radius: 4px;
                font-size: .56rem;
            }

            .master-potongan-create-range {
                gap: .75rem;
            }

            .master-potongan-create-unit {
                border-radius: 5px;
            }

            .master-potongan-create-unit input {
                min-height: 34px;
                border: 0;
                border-radius: 0;
            }

            .master-potongan-create-unit span {
                margin-right: .4rem;
                padding: .22rem .32rem;
                border-radius: 3px;
                font-size: .56rem;
            }

            .master-potongan-create-info {
                gap: .5rem;
                margin: .65rem 0 .8rem;
                padding: .65rem .75rem;
                border-radius: 5px;
            }

            .master-potongan-create-info p {
                font-size: .61rem;
            }

            .master-potongan-create-actions {
                display: flex;
                justify-content: flex-end;
                gap: .5rem;
                padding-top: .7rem;
                border-top: 1px solid #edf1f5;
            }

            .master-potongan-create-actions button,
            .master-potongan-create-actions a {
                width: auto;
                min-width: 95px;
                min-height: 38px;
                padding: .5rem .75rem;
                border-radius: 5px;
                font-size: .67rem;
            }

            .master-potongan-create-actions button {
                order: 2;
                box-shadow: none;
            }

            .master-potongan-create-actions a {
                order: 1;
            }

            .master-potongan-create-standard {
                margin-top: .7rem;
                padding: .7rem .8rem;
                border-radius: 6px;
                font-size: .63rem;
            }

            .master-potongan-create-standard p {
                margin-top: .4rem;
            }
        }
    </style>
@endpush
