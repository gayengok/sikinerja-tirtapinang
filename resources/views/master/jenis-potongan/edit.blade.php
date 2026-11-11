@extends('layouts.app')

@section('title', 'Edit Jenis Potongan')

@section('content')
    @php
        $tipe = old('tipe_pelanggaran', $jenisPotongan->tipe_pelanggaran);
    @endphp

    <div class="master-potongan-edit-page">
        <header class="master-potongan-edit-heading">
            <div>
                <nav class="master-potongan-edit-breadcrumb" aria-label="Breadcrumb">
                    <a href="{{ route('master.dashboard') }}">Beranda</a>
                    <i class="fa-solid fa-chevron-right"></i>
                    <a href="{{ route('master.jenis-potongan.index') }}">Master Data</a>
                    <i class="fa-solid fa-chevron-right"></i>
                    <span>Edit Potongan</span>
                </nav>
                <h1>
                    <i class="fa-solid fa-pen-to-square"></i>
                    Edit Jenis Potongan
                </h1>
                <p>Perbarui aturan potongan presensi dan pelanggaran kedisiplinan pegawai.</p>
            </div>
        </header>

        <section class="master-potongan-edit-card">
            <div class="master-potongan-edit-card-title">
                <span><i class="fa-solid fa-sliders"></i></span>
                <strong>FORMULIR ATURAN POTONGAN</strong>
                <b class="master-potongan-edit-mode">Mode Edit</b>
            </div>
            <div class="master-potongan-edit-divider"></div>

            <form action="{{ route('master.jenis-potongan.update', $jenisPotongan) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="master-potongan-edit-field">
                    <label for="nama_potongan">Nama Potongan <b>*</b></label>
                    <input type="text" name="nama_potongan" id="nama_potongan"
                        class="@error('nama_potongan') is-invalid @enderror"
                        value="{{ old('nama_potongan', $jenisPotongan->nama_potongan) }}"
                        placeholder="Contoh: Terlambat Ringan">
                    <p class="master-potongan-edit-help">Nama aturan yang tampil pada perhitungan potongan Tukin.</p>
                    @error('nama_potongan')
                        <div class="master-potongan-edit-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="master-potongan-edit-field">
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
                        <div class="master-potongan-edit-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="master-potongan-edit-range">
                    <div class="master-potongan-edit-field">
                        <label for="menit_min">Menit Minimum <small>(Opsional)</small></label>
                        <input type="number" min="0" name="menit_min" id="menit_min"
                            class="@error('menit_min') is-invalid @enderror"
                            value="{{ old('menit_min', $jenisPotongan->menit_min) }}" placeholder="0">
                        @error('menit_min')
                            <div class="master-potongan-edit-error">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="master-potongan-edit-field">
                        <label for="menit_max">Menit Maksimum <small>(Opsional)</small></label>
                        <input type="number" min="0" name="menit_max" id="menit_max"
                            class="@error('menit_max') is-invalid @enderror"
                            value="{{ old('menit_max', $jenisPotongan->menit_max) }}" placeholder="60">
                        @error('menit_max')
                            <div class="master-potongan-edit-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="master-potongan-edit-field">
                    <label for="persen_potongan">Persentase Potongan <b>*</b> <a href="#">Maksimal 100%</a></label>
                    <div class="master-potongan-edit-percent">
                        <input type="number" step="0.01" min="0" max="100" name="persen_potongan"
                            id="persen_potongan" class="@error('persen_potongan') is-invalid @enderror"
                            value="{{ old('persen_potongan', $jenisPotongan->persen_potongan) }}" placeholder="0.00">
                        <span>%</span>
                    </div>
                    @error('persen_potongan')
                        <div class="master-potongan-edit-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="master-potongan-edit-field">
                    <label for="keterangan">Keterangan <small>(Opsional)</small></label>
                    <textarea name="keterangan" id="keterangan" rows="3" maxlength="500"
                        class="@error('keterangan') is-invalid @enderror" placeholder="Tambahkan keterangan aturan potongan...">{{ old('keterangan', $jenisPotongan->keterangan) }}</textarea>
                    @error('keterangan')
                        <div class="master-potongan-edit-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="master-potongan-edit-actions">
                    <button type="submit"><i class="fa-solid fa-floppy-disk"></i> Perbarui</button>
                    <a href="{{ route('master.jenis-potongan.index') }}">Batal</a>
                </div>
            </form>
        </section>

        <aside class="master-potongan-edit-note">
            <i class="fa-solid fa-circle-info"></i>
            <p><strong>Catatan Perhitungan:</strong> Perubahan persentase akan diterapkan pada kalkulasi potongan Tukin
                periode berjalan sesuai tipe pelanggaran dan rentang menit yang ditentukan.</p>
        </aside>

        <aside class="master-potongan-edit-standard">
            <div><i class="fa-regular fa-circle-question"></i> <strong>Standar Potongan SIKINERJA</strong></div>
            <span>SK Direksi 2024</span>
            <p>Potongan berlaku kumulatif dalam satu periode siklus penilaian (tanggal 26 hingga 25 bulan berikutnya).</p>
        </aside>
    </div>
@endsection



@push('styles')
    <style>
        body.role-master .master-potongan-edit-page {
            width: 100%;
            max-width: 560px;
            margin: auto;
            padding: 1rem 1rem 0;
            box-sizing: border-box;
            color: #14223d
        }

        .master-potongan-edit-breadcrumb {
            display: flex;
            align-items: center;
            gap: .45rem;
            margin-bottom: 1rem;
            color: #14223d;
            font-size: .67rem;
            white-space: nowrap
        }

        .master-potongan-edit-breadcrumb a {
            color: #0875ad;
            text-decoration: none
        }

        .master-potongan-edit-breadcrumb i {
            font-size: .45rem;
            color: #9caabd
        }

        .master-potongan-edit-heading {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: .6rem
        }

        .master-potongan-edit-heading h1 {
            margin: 0;
            font-size: 1rem
        }

        .master-potongan-edit-heading p {
            max-width: 390px;
            margin: .3rem 0;
            color: #71829c;
            font-size: .73rem;
            line-height: 1.5
        }

        .master-potongan-edit-back {
            padding: .55rem .7rem;
            color: #14223d;
            background: #fff;
            border: 1px solid #c9d6e5;
            border-radius: 11px;
            text-decoration: none;
            font-size: .7rem;
            white-space: nowrap
        }

        .master-potongan-edit-status {
            display: flex;
            align-items: center;
            gap: .7rem;
            margin: 1rem 0 1.15rem;
            font-size: .68rem
        }

        .master-potongan-edit-status span {
            padding: .4rem .7rem;
            color: #07986b;
            background: #effff8;
            border: 1px solid #a6edce;
            border-radius: 999px
        }

        .master-potongan-edit-status span i {
            font-size: .4rem
        }

        .master-potongan-edit-status em {
            color: #91a1b8;
            font-style: normal
        }

        .master-potongan-edit-card {
            padding: 1.15rem 1rem;
            background: #fff;
            border: 1px solid #e0e8f1;
            border-radius: 20px;
            box-shadow: 0 5px 16px #2334550b
        }

        .master-potongan-edit-card-title {
            display: flex;
            align-items: center;
            gap: .65rem
        }

        .master-potongan-edit-card-title>span {
            display: grid;
            place-items: center;
            width: 36px;
            height: 36px;
            color: #078dcc;
            background: #effaff;
            border-radius: 10px
        }

        .master-potongan-edit-card-title strong {
            font-size: .78rem;
            letter-spacing: .02em
        }

        .master-potongan-edit-card-title code {
            margin-left: auto;
            padding: .4rem .5rem;
            color: #526480;
            background: #f0f5f9;
            border-radius: 6px;
            font-size: .58rem
        }

        .master-potongan-edit-divider {
            height: 1px;
            margin: 1rem 0 1.15rem;
            background: #e5edf4
        }

        .master-potongan-edit-field {
            margin-bottom: 1rem
        }

        .master-potongan-edit-field label {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: .45rem;
            font-size: .75rem
        }

        .master-potongan-edit-field label b {
            color: #e8475f;
            font-weight: 600
        }

        .master-potongan-edit-field label small {
            color: #71829c;
            font-size: .66rem
        }

        .master-potongan-edit-field label a {
            color: #078dcc;
            text-decoration: none;
            font-size: .66rem
        }

        .master-potongan-edit-help {
            margin: .4rem 0 0;
            color: #91a1b8;
            font-size: .67rem;
            line-height: 1.4
        }

        .master-potongan-edit-field input,
        .master-potongan-edit-field select,
        .master-potongan-edit-field textarea {
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

        .master-potongan-edit-field input,
        .master-potongan-edit-field select {
            min-height: 47px
        }

        .master-potongan-edit-field textarea {
            resize: vertical;
            line-height: 1.5
        }

        .master-potongan-edit-field input:focus,
        .master-potongan-edit-field select:focus,
        .master-potongan-edit-field textarea:focus {
            border-color: #078dcc;
            box-shadow: 0 0 0 3px #078dcc1a
        }

        .master-potongan-edit-range {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .75rem
        }

        .master-potongan-edit-percent {
            display: flex;
            align-items: center;
            border: 1px solid #c9d6e5;
            border-radius: 12px;
            overflow: hidden
        }

        .master-potongan-edit-percent input {
            border: 0;
            border-radius: 0
        }

        .master-potongan-edit-percent span {
            margin-right: .65rem;
            padding: .4rem .55rem;
            color: #526480;
            background: #f0f5f9;
            border-radius: 6px;
            font-size: .72rem
        }

        .master-potongan-edit-error {
            margin-top: .3rem;
            color: #dc3545;
            font-size: .66rem
        }

        .master-potongan-edit-field .is-invalid {
            border-color: #dc3545
        }

        .master-potongan-edit-actions {
            display: grid;
            grid-template-columns: 1fr 105px;
            gap: .75rem;
            margin-top: 1.1rem
        }

        .master-potongan-edit-actions button,
        .master-potongan-edit-actions a {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .45rem;
            min-height: 50px;
            border-radius: 13px;
            font: inherit;
            font-size: .8rem;
            text-decoration: none
        }

        .master-potongan-edit-actions button {
            color: #fff;
            background: #078dcc;
            border: 0;
            box-shadow: 0 4px 8px #078dcc33;
            font-weight: 700
        }

        .master-potongan-edit-actions a {
            color: #14223d;
            background: #fff;
            border: 1px solid #c9d6e5
        }

        .master-potongan-edit-note {
            display: flex;
            gap: .7rem;
            margin-top: 1.1rem;
            padding: 1rem;
            color: #526b8c;
            background: #effaff;
            border: 1px solid #bce7ff;
            border-radius: 16px
        }

        .master-potongan-edit-note>i {
            margin-top: .15rem;
            color: #078dcc
        }

        .master-potongan-edit-note p {
            margin: 0;
            font-size: .7rem;
            line-height: 1.55
        }

        .master-potongan-edit-note strong {
            color: #07527c
        }

        .master-potongan-edit-standard {
            position: relative;
            margin-top: 1rem;
            padding: 1rem;
            color: #526b8c;
            background: #f8fbfe;
            border: 1px solid #dce7f2;
            border-radius: 15px;
            font-size: .7rem
        }

        .master-potongan-edit-standard>div {
            color: #14223d
        }

        .master-potongan-edit-standard>div i {
            margin-right: .35rem;
            color: #7c91ab
        }

        .master-potongan-edit-standard>span {
            position: absolute;
            top: .85rem;
            right: .75rem;
            padding: .35rem .5rem;
            color: #526480;
            background: #eaf0f6;
            border-radius: 6px;
            font-size: .62rem
        }

        .master-potongan-edit-standard p {
            margin: .7rem 0 0;
            line-height: 1.5
        }

        .master-potongan-edit-delete {
            margin: 1.7rem 0;
            text-align: center
        }

        .master-potongan-edit-delete button {
            color: #f03d5c;
            background: none;
            border: 0;
            font: inherit;
            font-size: .75rem
        }

        @media(max-width:380px) {
            body.role-master .master-potongan-edit-page {
                padding-inline: .75rem
            }

            .master-potongan-edit-heading h1 {
                font-size: 1.22rem
            }

            .master-potongan-edit-heading p {
                font-size: .68rem
            }

            .master-potongan-edit-card {
                padding-inline: .8rem
            }

            .master-potongan-edit-card-title strong {
                font-size: .7rem
            }

            .master-potongan-edit-range {
                gap: .5rem
            }

            .master-potongan-edit-field label {
                font-size: .7rem
            }

            .master-potongan-edit-actions {
                grid-template-columns: 1fr 90px
            }
        }

        .master-potongan-edit-breadcrumb {
            display: none;
        }

        @media (min-width: 768px) {
            body.role-master .master-potongan-edit-page {
                width: 100%;
                max-width: none;
                margin: 0;
                padding: 1rem clamp(1.25rem, 2.4vw, 2.5rem) 2rem;
                color: #1b2938;
            }

            .master-potongan-edit-heading {
                margin-bottom: .8rem;
            }

            .master-potongan-edit-breadcrumb {
                display: flex;
                align-items: center;
                gap: .4rem;
                margin-bottom: .4rem;
                color: #718092;
                font-size: .64rem;
            }

            .master-potongan-edit-breadcrumb a {
                color: #718092;
                text-decoration: none;
            }

            .master-potongan-edit-breadcrumb>span,
            .master-potongan-edit-breadcrumb a:hover {
                color: #1769d2;
            }

            .master-potongan-edit-breadcrumb i {
                color: #a0acb9;
                font-size: .48rem;
            }

            .master-potongan-edit-heading h1 {
                display: flex;
                align-items: center;
                gap: .5rem;
                color: #1b2938;
                font-size: 1.35rem;
                font-weight: 700;
            }

            .master-potongan-edit-heading h1 i {
                display: grid;
                place-items: center;
                width: 30px;
                height: 30px;
                color: #078dcc;
                background: #e8f7ff;
                border-radius: 6px;
                font-size: .82rem;
            }

            .master-potongan-edit-heading p {
                margin: .2rem 0 0 2.4rem;
                font-size: .7rem;
            }

            .master-potongan-edit-card,
            .master-potongan-edit-note,
            .master-potongan-edit-standard {
                width: 100%;
                max-width: 1120px;
                margin-right: auto;
                margin-left: auto;
            }

            .master-potongan-edit-card {
                padding: 0;
                border-color: #e2e8ef;
                border-radius: 8px;
                box-shadow: none;
            }

            .master-potongan-edit-card-title {
                gap: .6rem;
                padding: .8rem 1rem;
                background: #f9fbfd;
                border-bottom: 1px solid #e8eef5;
            }

            .master-potongan-edit-card-title>span {
                width: 32px;
                height: 32px;
                border-radius: 6px;
            }

            .master-potongan-edit-card-title strong {
                flex: 1;
                font-size: .72rem;
                letter-spacing: 0;
            }

            .master-potongan-edit-mode {
                margin-left: auto;
                padding: .24rem .4rem;
                color: #a96800;
                background: #fff5df;
                border-radius: 999px;
                font-size: .56rem;
            }

            .master-potongan-edit-divider {
                display: none;
            }

            .master-potongan-edit-card form {
                padding: .9rem 1rem;
            }

            .master-potongan-edit-field {
                margin-bottom: .7rem;
            }

            .master-potongan-edit-field label {
                margin-bottom: .3rem;
                font-size: .65rem;
            }

            .master-potongan-edit-field label small,
            .master-potongan-edit-field label a {
                font-size: .58rem;
            }

            .master-potongan-edit-field input,
            .master-potongan-edit-field select,
            .master-potongan-edit-field textarea {
                min-height: 36px;
                padding: .42rem .65rem;
                border-radius: 5px;
                font-size: .66rem;
            }

            .master-potongan-edit-field textarea {
                min-height: 58px;
                padding: .55rem .65rem;
            }

            .master-potongan-edit-help,
            .master-potongan-edit-error {
                margin-top: .25rem;
                font-size: .59rem;
            }

            .master-potongan-edit-range {
                gap: .75rem;
            }

            .master-potongan-edit-percent {
                border-radius: 5px;
            }

            .master-potongan-edit-percent input {
                min-height: 34px;
                border-radius: 0;
            }

            .master-potongan-edit-percent span {
                padding: .22rem .32rem;
                border-radius: 3px;
                font-size: .56rem;
            }

            .master-potongan-edit-actions {
                display: flex;
                justify-content: flex-end;
                gap: .5rem;
                margin-top: .8rem;
                padding-top: .7rem;
                border-top: 1px solid #edf1f5;
            }

            .master-potongan-edit-actions button,
            .master-potongan-edit-actions a {
                min-width: 95px;
                min-height: 38px;
                padding: .5rem .75rem;
                border-radius: 5px;
                font-size: .67rem;
            }

            .master-potongan-edit-actions button {
                order: 2;
                box-shadow: none;
            }

            .master-potongan-edit-actions a {
                order: 1;
            }

            .master-potongan-edit-note,
            .master-potongan-edit-standard {
                margin-top: .7rem;
                padding: .7rem .8rem;
                border-radius: 6px;
                font-size: .63rem;
            }

            .master-potongan-edit-note p,
            .master-potongan-edit-standard p {
                font-size: .63rem;
            }
        }
    </style>
@endpush
