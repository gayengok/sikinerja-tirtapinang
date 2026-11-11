@extends('layouts.app')

@section('title', 'Detail Divisi')

@section('content')
    <div class="master-divisi-show">

        <header class="master-divisi-show-heading">
            <div>
                <h1>
                    <i class="fa-solid fa-building"></i>
                    Detail Divisi
                </h1>
                <p>Informasi unit kerja, jabatan, dan struktur organisasi divisi.</p>
            </div>

            <span>
                <i class="fa-solid fa-circle"></i>
                Data Terdaftar
            </span>
        </header>

        <section class="master-divisi-info-card">
            <div class="master-divisi-info-title"><span><i class="fa-solid fa-building"></i></span>
                <h2>Informasi Divisi</h2><b><i class="fa-solid fa-circle"></i> Terdaftar</b>
            </div>
            <div class="master-divisi-info-rows">
                <div><span>Nama Divisi</span><strong>{{ $divisi->nama_divisi }}</strong></div>
                <div><span>Jumlah Jabatan</span><strong>{{ $divisi->jabatans->count() }} Jabatan</strong></div>
                <div><span>Jumlah Template Laporan</span><strong>{{ $divisi->templateLaporans->count() }} Template</strong>
                </div>
                <div><span>Dibuat</span><strong><i class="fa-regular fa-calendar"></i>
                        {{ \Carbon\Carbon::parse($divisi->created_at)->translatedFormat('d F Y') }}</strong></div>
            </div>
        </section>
        <div class="master-divisi-jabatan-heading">
            <h2>Daftar Jabatan pada Divisi Ini <span>{{ $divisi->jabatans->count() }}</span></h2>
        </div>
        <div class="master-divisi-jabatan-list">
            @forelse($divisi->jabatans as $jabatan)
                <article class="master-divisi-jabatan-card">
                    <div class="master-divisi-jabatan-main"><span>{{ $loop->iteration }}</span>
                        <div>
                            <h3>{{ $jabatan->nama_jabatan }}</h3>
                            <p>{{ $jabatan->kategori ?? 'Struktural / Fungsional' }}</p>
                        </div><b>Grade {{ $jabatan->grade }}</b>
                    </div>
                    <div class="master-divisi-jabatan-footer"><span>Base Tukin Bulanan</span><strong>Rp
                            {{ number_format($jabatan->base_tukin, 0, ',', '.') }}</strong></div>
            </article>@empty<div class="master-divisi-empty"><i class="fa-regular fa-folder-open"></i><strong>Belum ada
                        jabatan pada divisi ini.</strong></div>
            @endforelse
        </div>
        <a class="master-divisi-all-pegawai" href="{{ route('master.pegawai.index') }}"><i class="fa-solid fa-users"></i>
            Lihat Seluruh Pegawai di Divisi Ini</a>
        <div class="master-divisi-bottom-action">
            <a href="{{ route('master.divisi.index') }}">
                <i class="fa-solid fa-arrow-left"></i>
                Kembali
            </a>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        body.role-master .master-divisi-show {
            width: 100%;
            max-width: 560px;
            margin: auto;
            padding: 1rem 1rem 0;
            box-sizing: border-box;
            color: #14223d
        }

        .master-divisi-show-crumb {
            display: flex;
            align-items: center;
            gap: .5rem;
            margin-bottom: 1rem;
            color: #0875ad;
            font-size: .72rem
        }

        .master-divisi-show-crumb a {
            color: #71829c;
            text-decoration: none
        }

        .master-divisi-show-crumb i {
            font-size: .5rem;
            color: #9caabd
        }

        .master-divisi-show-heading {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: .75rem;
            margin-bottom: 1.2rem
        }

        .master-divisi-show-heading h1 {
            display: flex;
            align-items: center;
            gap: .55rem;
            margin: 0;
            font-size: 1rem
        }

        .master-divisi-show-heading h1 i {

            font-size: 1.2rem
        }

        .master-divisi-show-heading p {
            margin: .35rem 0 0;
            color: #71829c;
            font-size: .78rem;
            line-height: 1.5
        }

        .master-divisi-show-heading>span {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .45rem .65rem;
            color: #087b5d;
            background: #e0faec;
            border: 1px solid #9ceaca;
            border-radius: 999px;
            font-size: .68rem;
            font-weight: 600;
            white-space: nowrap
        }

        .master-divisi-show-heading>span i {
            font-size: .42rem
        }

        .master-divisi-bottom-action {
            margin: 1rem 0
        }

        .master-divisi-bottom-action a {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            padding: .75rem 1rem;
            color: #526480;
            background: #fff;
            border: 1px solid #cbd9e7;
            border-radius: 12px;
            font-size: .78rem;
            font-weight: 600;
            text-decoration: none
        }

        .master-divisi-bottom-action a:hover {
            color: #0875ad;
            border-color: #0875ad
        }

        .master-divisi-info-card,
        .master-divisi-jabatan-card {
            overflow: hidden;
            background: #fff;
            border: 1px solid #dfe8f1;
            border-radius: 20px;
            box-shadow: 0 5px 17px #2334550b
        }

        .master-divisi-info-card {
            padding: 1.15rem 1.2rem;
            margin-bottom: 1.45rem
        }

        .master-divisi-info-title {
            display: flex;
            align-items: center;
            gap: .7rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e7eef5
        }

        .master-divisi-info-title>span {
            display: grid;
            place-items: center;
            width: 41px;
            height: 41px;
            color: #078dcc;
            background: #e7f7ff;
            border-radius: 12px
        }

        .master-divisi-info-title h2 {
            flex: 1;
            margin: 0;
            font-size: 1rem
        }

        .master-divisi-info-title b {
            padding: .45rem .65rem;
            color: #087b5d;
            background: #e0faec;
            border: 1px solid #9ceaca;
            border-radius: 999px;
            font-size: .68rem;
            font-weight: 600
        }

        .master-divisi-info-title b i {
            font-size: .42rem
        }

        .master-divisi-info-rows>div {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .7rem;
            padding: .85rem 0;
            border-bottom: 1px solid #e7eef5;
            font-size: .78rem
        }

        .master-divisi-info-rows>div:last-child {
            border-bottom: 0;
            padding-bottom: 0
        }

        .master-divisi-info-rows span {
            color: #687d9b
        }

        .master-divisi-info-rows strong {
            max-width: 65%;
            padding: .38rem .55rem;
            text-align: right;
            overflow-wrap: anywhere;
            font-size: .8rem
        }

        .master-divisi-info-rows>div:first-child strong {
            color: #0875ad;
            background: #e9f6ff;
            border-radius: 9px
        }

        .master-divisi-info-rows>div:nth-child(2) strong,
        .master-divisi-info-rows>div:nth-child(3) strong {
            color: #263653;
            background: #f0f4f8;
            border-radius: 9px
        }

        .master-divisi-info-rows strong i {
            margin-right: .25rem;
            color: #91a1b8
        }

        .master-divisi-jabatan-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .5rem;
            margin: 0 .35rem .75rem
        }

        .master-divisi-jabatan-heading h2 {
            margin: 0;
            font-size: 1rem
        }

        .master-divisi-jabatan-heading h2 span {
            display: inline-grid;
            place-items: center;
            width: 25px;
            height: 25px;
            margin-left: .25rem;
            color: #0875ad;
            background: #e4f4ff;
            border-radius: 50%;
            font-size: .7rem
        }

        .master-divisi-jabatan-heading a {
            color: #078dcc;
            text-decoration: none;
            font-size: .75rem;
            font-weight: 600;
            white-space: nowrap
        }

        .master-divisi-jabatan-list {
            display: grid;
            gap: .8rem
        }

        .master-divisi-jabatan-card {
            border-radius: 17px
        }

        .master-divisi-jabatan-main {
            display: flex;
            align-items: flex-start;
            gap: .7rem;
            padding: 1rem 1.1rem
        }

        .master-divisi-jabatan-main>span {
            display: grid;
            place-items: center;
            width: 39px;
            height: 39px;
            flex: 0 0 39px;
            color: #0875ad;
            background: #e1f3ff;
            border-radius: 11px;
            font-size: .85rem;
            font-weight: 600
        }

        .master-divisi-jabatan-main>div {
            min-width: 0;
            flex: 1
        }

        .master-divisi-jabatan-main h3 {
            margin: 0;
            font-size: .82rem;
            overflow-wrap: anywhere
        }

        .master-divisi-jabatan-main p {
            margin: .3rem 0 0;
            color: #607492;
            font-size: .72rem
        }

        .master-divisi-jabatan-main>b {
            padding: .4rem .5rem;
            color: #254ba8;
            background: #eaf1ff;
            border: 1px solid #c8dbff;
            border-radius: 7px;
            font-size: .65rem;
            white-space: nowrap
        }

        .master-divisi-jabatan-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: .8rem 1.1rem;
            background: #fbfcfe;
            border-top: 1px solid #e8eef5;
            color: #607492;
            font-size: .75rem
        }

        .master-divisi-jabatan-footer strong {
            color: #14223d;
            font-size: .85rem
        }

        .master-divisi-empty {
            display: grid;
            place-items: center;
            gap: .45rem;
            padding: 2rem;
            color: #91a1b8
        }

        .master-divisi-empty i {
            font-size: 2rem
        }

        .master-divisi-all-pegawai {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .6rem;
            margin-top: 1.1rem;
            padding: .85rem;
            color: #0875ad;
            background: #effaff;
            border: 1px solid #91d9ff;
            border-radius: 14px;
            text-decoration: none;
            font-size: .8rem;
            font-weight: 600
        }

        .master-divisi-all-pegawai i {
            font-size: 1rem
        }

        @media(max-width:380px) {
            body.role-master .master-divisi-show {
                padding-inline: .75rem
            }

            .master-divisi-show-heading h1 {
                font-size: 1.3rem
            }

            .master-divisi-show-heading {
                display: block
            }

            .master-divisi-show-heading>span {
                display: inline-flex;
                margin-top: .7rem
            }

            .master-divisi-info-card {
                padding-inline: 1rem
            }

            .master-divisi-info-title h2 {
                font-size: .88rem
            }

            .master-divisi-info-title b {
                font-size: .6rem
            }

            .master-divisi-info-rows>div {
                font-size: .7rem
            }

            .master-divisi-jabatan-main,
            .master-divisi-jabatan-footer {
                padding-inline: .9rem
            }
        }
    </style>
@endpush
