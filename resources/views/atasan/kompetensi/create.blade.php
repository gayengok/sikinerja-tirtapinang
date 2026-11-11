@extends('layouts.app')
@section('title', 'Form Penilaian Kompetensi')
@section('content')
    <div class="kompetensi-form-page">
        <div class="atasan-mobile-breadcrumb"><a
                href="{{ route('atasan.kompetensi.index', ['periode_id' => $periode->id]) }}"><i
                    class="fa-solid fa-arrow-left"></i></a>
            <div><span>Penilaian Kompetensi</span><strong>Form Penilaian</strong></div>
        </div>

        <nav class="kompetensi-form-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('atasan.dashboard') }}">Beranda</a>
            <i class="fa-solid fa-chevron-right"></i>
            <a href="{{ route('atasan.kompetensi.index', ['periode_id' => $periode->id]) }}">Penilaian Kompetensi</a>
            <i class="fa-solid fa-chevron-right"></i>
            <span>Form Penilaian</span>
        </nav>

        <section class="kompetensi-form-person"><span
                class="kompetensi-avatar">{{ strtoupper(substr($pegawai->nama, 0, 2)) }}</span>
            <div><small>PEGAWAI YANG DINILAI</small>
                <h2>{{ $pegawai->nama }}</h2>
                <p>{{ $pegawai->jabatan->nama_jabatan ?? '-' }}</p>
            </div><b>{{ $periode->nama_periode }}</b>
        </section>
        @if ($masterKompetensis->isEmpty())
            <div class="alert alert-warning mt-3">Belum ada master kompetensi untuk jabatan ini.</div>
        @else
            <form action="{{ route('atasan.kompetensi.update', $pegawai) }}" method="POST">@csrf @method('PUT')<input
                    type="hidden" name="periode_id" value="{{ $periode->id }}">
                <div class="kompetensi-form-intro"><strong><i class="fa-solid fa-user-check"></i> ASPEK PERILAKU KERJA
                        (BOBOT 40%)</strong><span>Skala Penilaian 1–5</span></div>
                @foreach ($masterKompetensis as $index => $kompetensi)
                    @php $tersimpan = $penilaianTersimpan->get($kompetensi->id); @endphp
                    <article class="kompetensi-indicator">
                        <div class="kompetensi-indicator-title"><span>{{ $index + 1 }}</span>
                            <div>
                                <h2>{{ $kompetensi->nama_kompetensi }}</h2>
                                <p>{{ $kompetensi->definisi_perilaku ?: 'Tidak ada definisi perilaku.' }}</p>
                            </div>
                        </div><input type="hidden" name="kompetensi[{{ $index }}][master_kompetensi_id]"
                            value="{{ $kompetensi->id }}">
                        <fieldset class="kompetensi-score-field">
                            <legend>Skor Penilaian (1–5) <em>*</em></legend>
                            <div class="kompetensi-score-options" role="radiogroup"
                                aria-label="Skor {{ $kompetensi->nama_kompetensi }}">
                                @for ($skor = 1; $skor <= 5; $skor++)
                                    <label>
                                        <input type="radio" name="kompetensi[{{ $index }}][skor]"
                                            value="{{ $skor }}" required @checked((int) old('kompetensi.' . $index . '.skor', $tersimpan->skor ?? 0) === $skor)>
                                        <span>{{ $skor }}</span>
                                    </label>
                                @endfor
                                <small>1 = Sangat Kurang, 5 = Sangat Baik</small>
                            </div>
                        </fieldset>
                        <label>Catatan
                            penilai
                            <textarea name="kompetensi[{{ $index }}][catatan]" rows="2" maxlength="1000"
                                placeholder="Catatan pengamatan (opsional)">{{ old('kompetensi.' . $index . '.catatan', $tersimpan->catatan ?? '') }}</textarea>
                        </label>
                        @error('kompetensi.' . $index . '.skor')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </article>
                @endforeach
                <div class="kompetensi-form-actions"><a href="{{ route('atasan.pegawai.index') }}">Batal</a><button
                        type="submit"><i class="fa-solid fa-floppy-disk"></i> Simpan Penilaian</button></div>
            </form>
        @endif
    </div>
@endsection
@push('styles')
    <style>
        body.role-atasan .kompetensi-form-page {
            box-sizing: border-box;
            width: 100%;
            max-width: 560px;
            margin: auto;
            padding: 1rem 1rem 0;
            color: #17213b
        }

        .kompetensi-form-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .5rem;
            margin: .8rem 0 1rem
        }

        .kompetensi-form-top h1 {
            margin: 0;
            font-size: 1rem
        }

        .kompetensi-form-top a {
            padding: .55rem .7rem;
            color: #526480;
            background: #eef4fa;
            border-radius: 9px;
            text-decoration: none;
            font-size: .68rem
        }

        .kompetensi-form-person {
            display: flex;
            align-items: center;
            gap: .7rem;
            padding: 1rem;
            background: #fff;
            border: 1px solid #dfe7f0;
            border-radius: 17px
        }

        .kompetensi-form-person>div {
            min-width: 0;
            flex: 1
        }

        .kompetensi-form-person small {
            color: #91a1b8;
            font-size: .59rem
        }

        .kompetensi-form-person h2 {
            margin: .2rem 0;
            font-size: .9rem
        }

        .kompetensi-form-person p {
            margin: 0;
            color: #078ac8;
            font-size: .67rem
        }

        .kompetensi-form-person>b {
            padding: .4rem .5rem;
            color: #0875ad;
            background: #eff9ff;
            border-radius: 8px;
            font-size: .6rem
        }

        .kompetensi-avatar {
            display: grid;
            place-items: center;
            width: 45px;
            height: 45px;
            flex: 0 0 45px;
            color: #fff;
            background: linear-gradient(145deg, #1676df, #08a5c9);
            border-radius: 13px;
            font-weight: 700
        }

        .kompetensi-form-intro {
            display: flex;
            justify-content: space-between;
            gap: .5rem;
            margin: 1rem 0 .7rem;
            padding: .85rem;
            color: #0875ad;
            background: #eff9ff;
            border: 1px solid #c7eaff;
            border-radius: 13px;
            font-size: .66rem
        }

        .kompetensi-form-intro span {
            color: #71829c
        }

        .kompetensi-indicator {
            margin-bottom: .75rem;
            padding: 1rem;
            background: #fff;
            border: 1px solid #dfe7f0;
            border-radius: 16px
        }

        .kompetensi-indicator-title {
            display: flex;
            gap: .65rem
        }

        .kompetensi-indicator-title>span {
            display: grid;
            place-items: center;
            width: 28px;
            height: 28px;
            flex: 0 0 28px;
            color: #0875ad;
            background: #eaf7ff;
            border-radius: 8px;
            font-size: .7rem;
            font-weight: 700
        }

        .kompetensi-indicator-title h2 {
            margin: 0;
            font-size: .82rem
        }

        .kompetensi-indicator-title p {
            margin: .3rem 0 .8rem;
            color: #71829c;
            font-size: .68rem;
            line-height: 1.45
        }

        .kompetensi-indicator label {
            display: block;
            margin-top: .65rem;
            color: #526480;
            font-size: .68rem;
            font-weight: 600
        }

        .kompetensi-indicator input,
        .kompetensi-indicator textarea {
            box-sizing: border-box;
            width: 100%;
            margin-top: .35rem;
            padding: .65rem;
            color: #17213b;
            background: #fff;
            border: 1px solid #cbd9e7;
            border-radius: 10px;
            font: inherit;
            font-size: .75rem;
            outline: 0
        }

        .kompetensi-indicator input {
            max-width: 110px
        }

        .kompetensi-indicator textarea {
            resize: vertical
        }

        .kompetensi-form-actions {
            display: flex;
            gap: .6rem;
            margin-top: 1rem
        }

        .kompetensi-form-actions a,
        .kompetensi-form-actions button {
            padding: .78rem;
            text-align: center;
            border-radius: 11px;
            font: inherit;
            font-size: .72rem;
            text-decoration: none
        }

        .kompetensi-form-actions a {
            min-width: 90px;
            color: #526480;
            background: #fff;
            border: 1px solid #cbd9e7
        }

        .kompetensi-form-actions button {
            flex: 1;
            color: #fff;
            background: #078ac8;
            border: 0;
            font-weight: 700
        }

        .kompetensi-form-breadcrumb {
            display: none;
        }

        .kompetensi-score-field {
            min-width: 0;
            margin: .55rem 0 0;
            padding: 0;
            border: 0;
        }

        .kompetensi-score-field legend {
            margin-bottom: .35rem;
            color: #526480;
            font-size: .68rem;
            font-weight: 600;
        }

        .kompetensi-score-field legend em {
            color: #e54862;
            font-style: normal;
        }

        .kompetensi-score-options {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: .35rem;
        }

        .kompetensi-score-options label {
            position: relative;
            display: inline-flex;
            margin: 0;
            cursor: pointer;
        }

        .kompetensi-score-options input {
            position: absolute;
            width: 1px;
            height: 1px;
            margin: 0;
            overflow: hidden;
            opacity: 0;
        }

        .kompetensi-score-options label>span {
            display: grid;
            place-items: center;
            width: 32px;
            height: 30px;
            color: #526480;
            background: #fff;
            border: 1px solid #dfe7f0;
            border-radius: 5px;
            font-size: .68rem;
            font-weight: 700;
            transition: color .15s ease, background .15s ease, border-color .15s ease;
        }

        .kompetensi-score-options input:checked+span {
            color: #fff;
            background: #0b8db3;
            border-color: #0b8db3;
        }

        .kompetensi-score-options input:focus-visible+span {
            outline: 2px solid #1769d2;
            outline-offset: 2px;
        }

        .kompetensi-score-options>small {
            margin-left: .35rem;
            color: #8290a0;
            font-size: .58rem;
        }

        @media (min-width: 992px) {
            body.role-atasan .kompetensi-form-page {
                width: 100%;
                max-width: 1120px;
                margin: 0 auto;
                padding: 1.4rem 1.5rem 2rem;
                color: #1b2938;
            }

            .atasan-mobile-breadcrumb {
                display: none;
            }

            .kompetensi-form-breadcrumb {
                display: flex;
                align-items: center;
                gap: .4rem;
                margin-bottom: .4rem;
                color: #718092;
                font-size: .64rem;
            }

            .kompetensi-form-breadcrumb a {
                color: #718092;
                text-decoration: none;
            }

            .kompetensi-form-breadcrumb>span,
            .kompetensi-form-breadcrumb a:hover {
                color: #1769d2;
            }

            .kompetensi-form-breadcrumb i {
                color: #a0acb9;
                font-size: .48rem;
            }

            .kompetensi-form-person {
                gap: .7rem;
                margin-bottom: .75rem;
                padding: .75rem .9rem;
                border-color: #e2e8ef;
                border-radius: 8px;
                box-shadow: none;
            }

            .kompetensi-avatar {
                width: 40px;
                height: 40px;
                flex-basis: 40px;
                border-radius: 7px;
                font-size: .7rem;
            }

            .kompetensi-form-person small {
                font-size: .57rem;
            }

            .kompetensi-form-person h2 {
                font-size: .8rem;
            }

            .kompetensi-form-person p {
                font-size: .61rem;
            }

            .kompetensi-form-person>b {
                padding: .3rem .42rem;
                border-radius: 5px;
                font-size: .57rem;
            }

            .kompetensi-form-intro {
                align-items: center;
                margin: .65rem 0 .55rem;
                padding: .6rem .75rem;
                border-radius: 6px;
                font-size: .62rem;
            }

            .kompetensi-indicator {
                display: grid;
                grid-template-columns: minmax(0, 1fr);
                gap: .1rem;
                margin-bottom: .6rem;
                padding: .8rem .9rem;
                border-color: #e2e8ef;
                border-radius: 7px;
                box-shadow: none;
            }

            .kompetensi-indicator-title {
                gap: .5rem;
            }

            .kompetensi-indicator-title>span {
                width: 26px;
                height: 26px;
                flex-basis: 26px;
                border-radius: 5px;
                font-size: .63rem;
            }

            .kompetensi-indicator-title h2 {
                color: #25364a;
                font-size: .73rem;
            }

            .kompetensi-indicator-title p {
                margin: .2rem 0 .25rem;
                font-size: .62rem;
            }

            .kompetensi-score-field {
                margin-top: .35rem;
            }

            .kompetensi-score-field legend {
                margin-bottom: .28rem;
                font-size: .62rem;
            }

            .kompetensi-score-options {
                gap: .3rem;
            }

            .kompetensi-score-options label>span {
                width: 30px;
                height: 28px;
                border-radius: 4px;
                font-size: .62rem;
            }

            .kompetensi-score-options>small {
                font-size: .56rem;
            }

            .kompetensi-indicator>label {
                margin-top: .45rem;
                font-size: .62rem;
            }

            .kompetensi-indicator textarea {
                min-height: 34px;
                margin-top: .25rem;
                padding: .45rem .55rem;
                border-radius: 5px;
                font-size: .62rem;
            }

            .kompetensi-indicator>.text-danger {
                margin-top: .3rem;
                font-size: .59rem;
            }

            .kompetensi-form-actions {
                justify-content: flex-end;
                gap: .5rem;
                margin-top: .75rem;
            }

            .kompetensi-form-actions a,
            .kompetensi-form-actions button {
                min-height: 38px;
                padding: .5rem .75rem;
                border-radius: 5px;
                font-size: .67rem;
            }

            .kompetensi-form-actions a {
                min-width: 92px;
            }
        }
    </style>
@endpush
