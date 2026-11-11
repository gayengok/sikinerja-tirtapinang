@extends('layouts.app')
@section('title', 'Detail Kompetensi')

@section('content')
    <div class="master-kompetensi-show">

        <header class="master-kompetensi-show-heading">
            <div>
                <nav class="master-kompetensi-show-breadcrumb" aria-label="Breadcrumb">
                    <a href="{{ route('master.dashboard') }}">Beranda</a>
                    <i class="fa-solid fa-chevron-right"></i>
                    <a href="{{ route('master.master-kompetensi.index') }}">Master Kompetensi</a>
                    <i class="fa-solid fa-chevron-right"></i>
                    <span>Detail</span>
                </nav>
                <h1>
                    <i class="fa-solid fa-clipboard-check"></i>
                    Detail Kompetensi
                </h1>
                <p>Parameter standar perilaku pegawai</p>
            </div>

            <div>
                <a href="{{ route('master.master-kompetensi.index') }}">
                    <i class="fa-solid fa-arrow-left"></i> Kembali
                </a>
            </div>
        </header>

        <section class="master-kompetensi-detail-card">
            <div class="master-kompetensi-detail-accent"></div>
            <div class="master-kompetensi-detail-top"><span
                    class="master-kompetensi-detail-number">{{ $masterKompetensi->urutan }}</span>
                <div><small>NAMA KOMPETENSI</small>
                    <h2>{{ $masterKompetensi->nama_kompetensi }}</h2>
                </div><b><i class="fa-solid fa-users"></i>
                    {{ $masterKompetensi->jabatan->nama_jabatan ?? 'Semua Jabatan' }}</b>
            </div>
            <div class="master-kompetensi-detail-stats">
                <div><small>Urutan Prioritas</small><strong><i class="fa-solid fa-arrow-down-wide-short"></i>
                        Ke-{{ $masterKompetensi->urutan }}
                        <em>({{ $masterKompetensi->urutan == 1 ? 'Pertama' : 'Prioritas' }})</em></strong></div>
                <div><small>Sudah Dinilai</small><strong class="count"><i class="fa-solid fa-circle"></i>
                        {{ $masterKompetensi->penilaianKompetensis->count() }} kali</strong><span>Periode ini</span></div>
            </div>
            <div class="master-kompetensi-definition"><strong><i class="fa-regular fa-circle-info"></i> DEFINISI
                    PERILAKU</strong>
                <blockquote>“{{ $masterKompetensi->definisi_perilaku ?: 'Definisi perilaku belum diatur.' }}”</blockquote>
            </div>
        </section>
        <section class="master-kompetensi-rubric">
            <header>
                <h2><i class="fa-regular fa-circle-check"></i> Rubrik &amp; Ketentuan Penilaian</h2><span>Skala 1 - 4</span>
            </header>
            <div class="rubric good"><span>4 • Selalu memenuhi standar perilaku</span><strong>Sangat Baik</strong></div>
            <div class="rubric normal"><span>3 • Memenuhi standar dengan konsisten</span><strong>Baik</strong></div>
            <div class="rubric warn"><span>2 • Terkadang memerlukan arahan</span><strong>Cukup</strong></div>
        </section>
        <aside class="master-kompetensi-note"><span><i class="fa-solid fa-circle-info"></i></span>
            <div><strong>Ketentuan Penilaian Kompetensi</strong>
                <p>Kompetensi dasar berlaku untuk seluruh jenjang jabatan pegawai Perumdam Tirta Pinang dan dinilai secara
                    periodik beriringan dengan capaian target KPI.</p>
            </div>
        </aside>
        <a class="master-kompetensi-history" href="#"><i class="fa-regular fa-clipboard"></i> Lihat Riwayat Penilaian
            {{ $masterKompetensi->nama_kompetensi }}</a>
    </div>
@endsection
@push('styles')
    <style>
        body.role-master .master-kompetensi-show {
            width: 100%;
            max-width: 560px;
            margin: auto;
            padding: 1rem 1rem 0;
            box-sizing: border-box;
            color: #14223d
        }

        .master-kompetensi-show-breadcrumb {
            display: none;
        }

        .master-kompetensi-show-crumb {
            display: flex;
            align-items: center;
            gap: .5rem;
            margin-bottom: 1rem;
            color: #0875ad;
            font-size: .7rem
        }

        .master-kompetensi-show-crumb a {
            color: #71829c;
            text-decoration: none
        }

        .master-kompetensi-show-crumb i {
            font-size: .5rem;
            color: #9caabd
        }

        .master-kompetensi-show-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .7rem;
            margin-bottom: 1.2rem
        }

        .master-kompetensi-show-heading h1 {
            margin: 0;
            font-size: 1rem
        }

        .master-kompetensi-show-heading p {
            margin: .3rem 0 0;
            color: #647895;
            font-size: .78rem
        }

        .master-kompetensi-show-heading>div:last-child {
            display: flex;
            gap: .45rem
        }

        .master-kompetensi-show-heading a {
            padding: .7rem .8rem;
            color: #526480;
            border: 1px solid #dce5ee;
            border-radius: 11px;
            text-decoration: none;
            font-size: .72rem;
            white-space: nowrap
        }

        .master-kompetensi-show-heading a.edit {
            color: #fff;
            background: #078dcc;
            border-color: #078dcc;
            box-shadow: 0 4px 8px #078dcc33
        }

        .master-kompetensi-detail-card,
        .master-kompetensi-rubric {
            overflow: hidden;
            background: #fff;
            border: 1px solid #dfe8f1;
            border-radius: 20px;
            box-shadow: 0 5px 17px #2334550b
        }

        .master-kompetensi-detail-card {
            margin-bottom: 1.2rem
        }

        .master-kompetensi-detail-accent {
            height: 7px;
            background: linear-gradient(90deg, #18bde5, #2368ed)
        }

        .master-kompetensi-detail-top {
            display: flex;
            align-items: center;
            gap: .8rem;
            padding: 1.15rem 1.2rem 1rem
        }

        .master-kompetensi-detail-number {
            display: grid;
            place-items: center;
            width: 60px;
            height: 60px;
            flex: 0 0 60px;
            color: #0875ad;
            background: #effaff;
            border: 1px solid #ccecff;
            border-radius: 16px;
            font-size: 1.35rem
        }

        .master-kompetensi-detail-top>div {
            min-width: 0;
            flex: 1
        }

        .master-kompetensi-detail-top small {
            color: #91a1b8;
            font-size: .65rem;
            font-weight: 600
        }

        .master-kompetensi-detail-top h2 {
            margin: .25rem 0 0;
            font-size: 1rem;
            overflow-wrap: anywhere
        }

        .master-kompetensi-detail-top>b {
            padding: .55rem .65rem;
            color: #0875ad;
            background: #effaff;
            border: 1px solid #aee1ff;
            border-radius: 999px;
            font-size: .68rem;
            white-space: nowrap
        }

        .master-kompetensi-detail-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .7rem;
            padding: 0 1.2rem 1rem
        }

        .master-kompetensi-detail-stats>div {
            min-width: 0;
            padding: .85rem;
            background: #f8fafc;
            border: 1px solid #edf2f7;
            border-radius: 14px
        }

        .master-kompetensi-detail-stats small {
            display: block;
            color: #91a1b8;
            font-size: .67rem
        }

        .master-kompetensi-detail-stats strong {
            display: block;
            margin-top: .45rem;
            color: #14223d;
            font-size: .9rem
        }

        .master-kompetensi-detail-stats strong i {
            color: #078dcc;
            font-size: .8rem
        }

        .master-kompetensi-detail-stats strong em {
            display: block;
            margin-top: .2rem;
            font-style: normal;
            font-size: .7rem
        }

        .master-kompetensi-detail-stats strong.count {
            color: #087b5d
        }

        .master-kompetensi-detail-stats strong.count i {
            color: #0bb889;
            font-size: .55rem
        }

        .master-kompetensi-detail-stats span {
            display: block;
            margin-top: .2rem;
            color: #91a1b8;
            font-size: .67rem
        }

        .master-kompetensi-definition {
            margin: 0 1.2rem 1.2rem;
            padding: 1rem;
            color: #0875ad;
            background: #f0fbff;
            border: 1px solid #c9edff;
            border-radius: 15px
        }

        .master-kompetensi-definition strong {
            font-size: .78rem
        }

        .master-kompetensi-definition strong i {
            margin-right: .25rem
        }

        .master-kompetensi-definition blockquote {
            margin: .7rem 0 0;
            padding-left: .8rem;
            border-left: 3px solid #11b9ed;
            color: #263653;
            font-size: .88rem;
            font-style: italic;
            line-height: 1.6
        }

        .master-kompetensi-rubric {
            padding: 1.1rem 1.2rem;
            margin-bottom: 1.1rem
        }

        .master-kompetensi-rubric header {
            display: flex;
            justify-content: space-between;
            gap: .5rem;
            align-items: center;
            margin-bottom: .85rem
        }

        .master-kompetensi-rubric h2 {
            margin: 0;
            font-size: .88rem
        }

        .master-kompetensi-rubric h2 i {
            color: #078dcc
        }

        .master-kompetensi-rubric header span {
            color: #078dcc;
            font-size: .7rem
        }

        .rubric {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .6rem;
            padding: .7rem .8rem;
            margin-bottom: .55rem;
            border-radius: 11px;
            font-size: .72rem
        }

        .rubric:last-child {
            margin-bottom: 0
        }

        .rubric strong {
            font-size: .75rem
        }

        .rubric.good {
            color: #087b5d;
            background: #e5faef;
            border: 1px solid #b5edce
        }

        .rubric.normal {
            color: #0875ad;
            background: #eff9ff;
            border: 1px solid #c8eaff
        }

        .rubric.warn {
            color: #a95600;
            background: #fff6df;
            border: 1px solid #ffe1a1
        }

        .master-kompetensi-note {
            display: flex;
            gap: .75rem;
            margin-bottom: 1rem;
            padding: 1.1rem 1rem;
            color: #0875ad;
            background: #eaf7ff;
            border: 1px solid #bfe7ff;
            border-radius: 17px
        }

        .master-kompetensi-note>span {
            display: grid;
            place-items: center;
            width: 34px;
            height: 34px;
            flex: 0 0 34px;
            color: #fff;
            background: #078dcc;
            border-radius: 9px
        }

        .master-kompetensi-note strong {
            display: block;
            margin: .1rem 0 .3rem;
            color: #07527c;
            font-size: .82rem
        }

        .master-kompetensi-note p {
            margin: 0;
            color: #526b8c;
            font-size: .73rem;
            line-height: 1.55
        }

        .master-kompetensi-history {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .55rem;
            padding: .85rem;
            color: #fff;
            background: #078dcc;
            border-radius: 13px;
            text-decoration: none;
            font-size: .78rem;
            font-weight: 600
        }

        @media(max-width:380px) {
            body.role-master .master-kompetensi-show {
                padding-inline: .75rem
            }

            .master-kompetensi-show-heading {
                align-items: flex-start;
                flex-direction: column
            }

            .master-kompetensi-show-heading>div:last-child {
                align-self: stretch;
                justify-content: flex-end
            }

            .master-kompetensi-show-heading h1 {
                font-size: 1.3rem
            }

            .master-kompetensi-detail-top,
            .master-kompetensi-detail-stats,
            .master-kompetensi-definition,
            .master-kompetensi-rubric {
                padding-inline: 1rem
            }

            .master-kompetensi-detail-top h2 {
                font-size: 1.1rem
            }

            .master-kompetensi-detail-top>b {
                font-size: .58rem;
                padding: .45rem
            }

            .master-kompetensi-detail-stats {
                gap: .45rem
            }

            .rubric {
                align-items: flex-start;
                flex-direction: column;
                gap: .3rem
            }
        }

        @media (min-width: 768px) {
            body.role-master .master-kompetensi-show {
                width: 100%;
                max-width: none;
                margin: 0;
                padding: 1rem clamp(1.25rem, 2.4vw, 2.5rem) 2rem;
                color: #1b2938;
            }

            .master-kompetensi-show-heading {
                align-items: flex-start;
                margin-bottom: .85rem;
            }

            .master-kompetensi-show-breadcrumb {
                display: flex;
                align-items: center;
                gap: .4rem;
                margin-bottom: .4rem;
                color: #718092;
                font-size: .64rem;
            }

            .master-kompetensi-show-breadcrumb a {
                color: #718092;
                text-decoration: none;
            }

            .master-kompetensi-show-breadcrumb>span,
            .master-kompetensi-show-breadcrumb a:hover {
                color: #1769d2;
            }

            .master-kompetensi-show-breadcrumb i {
                color: #a0acb9;
                font-size: .48rem;
            }

            .master-kompetensi-show-heading h1 {
                display: flex;
                align-items: center;
                gap: .5rem;
                color: #1b2938;
                font-size: 1.35rem;
                font-weight: 700;
            }

            .master-kompetensi-show-heading h1 i {
                display: grid;
                place-items: center;
                width: 30px;
                height: 30px;
                color: #078dcc;
                background: #e8f7ff;
                border-radius: 6px;
                font-size: .82rem;
            }

            .master-kompetensi-show-heading p {
                margin: .2rem 0 0 2.4rem;
                font-size: .7rem;
            }

            .master-kompetensi-show-heading>div:last-child {
                align-self: flex-end;
            }

            .master-kompetensi-show-heading a {
                padding: .5rem .75rem;
                border-radius: 5px;
                font-size: .67rem;
            }

            .master-kompetensi-detail-card,
            .master-kompetensi-rubric,
            .master-kompetensi-note,
            .master-kompetensi-history {
                width: 100%;
                max-width: 1120px;
                margin-right: auto;
                margin-left: auto;
            }

            .master-kompetensi-detail-card,
            .master-kompetensi-rubric {
                border-color: #e2e8ef;
                border-radius: 8px;
                box-shadow: none;
            }

            .master-kompetensi-detail-accent {
                height: 5px;
            }

            .master-kompetensi-detail-top {
                gap: .7rem;
                padding: .9rem 1rem .75rem;
            }

            .master-kompetensi-detail-number {
                width: 44px;
                height: 44px;
                flex-basis: 44px;
                border-radius: 7px;
                font-size: 1.05rem;
            }

            .master-kompetensi-detail-top small {
                font-size: .59rem;
            }

            .master-kompetensi-detail-top h2 {
                margin-top: .15rem;
                color: #25364a;
                font-size: .9rem;
            }

            .master-kompetensi-detail-top>b {
                padding: .35rem .5rem;
                border-radius: 5px;
                font-size: .62rem;
            }

            .master-kompetensi-detail-stats {
                gap: .65rem;
                padding: 0 1rem .8rem;
            }

            .master-kompetensi-detail-stats>div {
                padding: .65rem .75rem;
                border-radius: 5px;
            }

            .master-kompetensi-detail-stats small,
            .master-kompetensi-detail-stats span {
                font-size: .61rem;
            }

            .master-kompetensi-detail-stats strong {
                margin-top: .3rem;
                font-size: .76rem;
            }

            .master-kompetensi-detail-stats strong em {
                display: inline;
                margin-left: .2rem;
                font-size: .63rem;
            }

            .master-kompetensi-definition {
                margin: 0 1rem .9rem;
                padding: .7rem .8rem;
                border-radius: 5px;
            }

            .master-kompetensi-definition strong {
                font-size: .67rem;
            }

            .master-kompetensi-definition blockquote {
                margin-top: .45rem;
                font-size: .72rem;
            }

            .master-kompetensi-rubric {
                padding: .9rem 1rem;
                margin-bottom: .75rem;
            }

            .master-kompetensi-rubric header {
                margin-bottom: .65rem;
            }

            .master-kompetensi-rubric h2 {
                color: #25364a;
                font-size: .78rem;
            }

            .master-kompetensi-rubric header span {
                font-size: .62rem;
            }

            .rubric {
                padding: .55rem .65rem;
                margin-bottom: .4rem;
                border-radius: 5px;
                font-size: .65rem;
            }

            .rubric strong {
                font-size: .65rem;
            }

            .master-kompetensi-note {
                gap: .5rem;
                margin-bottom: .7rem;
                padding: .7rem .8rem;
                border-radius: 6px;
            }

            .master-kompetensi-note>span {
                width: 28px;
                height: 28px;
                flex-basis: 28px;
                border-radius: 5px;
            }

            .master-kompetensi-note strong {
                font-size: .69rem;
            }

            .master-kompetensi-note p {
                font-size: .63rem;
            }

            .master-kompetensi-history {
                display: flex;
                justify-content: center;
                max-width: max-content;
                margin-top: .7rem;
                padding: .55rem .75rem;
                border-radius: 5px;
                font-size: .66rem;
            }
        }
    </style>
@endpush
