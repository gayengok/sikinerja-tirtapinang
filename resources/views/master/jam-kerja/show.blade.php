@extends('layouts.app')

@section('title', 'Detail Jam Kerja')

@section('content')
    @php
        $jamMasuk = \Carbon\Carbon::parse($jamKerja->jam_masuk);
        $jamPulang = \Carbon\Carbon::parse($jamKerja->jam_pulang);
        $durasiMenit = $jamMasuk->diffInMinutes($jamPulang);
        $durasiJam = intdiv($durasiMenit, 60);
        $durasiSisaMenit = $durasiMenit % 60;
    @endphp

    <div class="master-jam-show-page">


        <header class="master-jam-show-heading">
            <div>
                <nav class="master-jam-show-breadcrumb" aria-label="Breadcrumb">
                    <a href="{{ route('master.dashboard') }}">Beranda</a>
                    <i class="fa-solid fa-chevron-right"></i>
                    <a href="{{ route('master.jam-kerja.index') }}">Jam Kerja</a>
                    <i class="fa-solid fa-chevron-right"></i>
                    <span>Detail</span>
                </nav>
                <h1>
                    <i class="fa-solid fa-clock"></i>
                    Detail Jam Kerja
                </h1>
                <p>Informasi spesifikasi parameter jadwal presensi.</p>
            </div>
        </header>

        <div class="master-jam-show-status">
            <span><i class="fa-solid fa-circle"></i> Jadwal Hari Aktif</span>
            <em>Terakhir disinkron: Hari ini</em>
        </div>

        <section class="master-jam-show-card">
            <div class="master-jam-show-card-title">
                <span><i class="fa-regular fa-clock"></i></span>
                <strong>INFORMASI JADWAL</strong>
                <code>ID: JAD-{{ str_pad($jamKerja->id, 2, '0', STR_PAD_LEFT) }}</code>
            </div>
            <div class="master-jam-show-divider"></div>

            <div class="master-jam-show-row">
                <div><label>Jabatan</label><strong>{{ $jamKerja->jabatan->nama_jabatan ?? 'Semua Jabatan' }}</strong><small>Berlaku
                        untuk seluruh staf &amp; struktural</small></div>
                <span class="badge">Umum</span>
            </div>
            <div class="master-jam-show-row">
                <div><label>Hari Penugasan</label><strong
                        class="blue">{{ $jamKerja->hari }}</strong><small>{{ $jamKerja->hari === 'Jumat' ? 'Jadwal khusus hari ibadah & senam pagi' : 'Jadwal kerja reguler' }}</small>
                </div>
                <span class="badge special">{{ $jamKerja->hari === 'Kerja' ? 'Reguler' : 'Khusus' }}</span>
            </div>

            <div class="master-jam-show-time-grid">
                <div><label><i class="fa-solid fa-plus"></i> Jam
                        Masuk</label><strong>{{ $jamMasuk->format('H:i') }}</strong><span>WIB Presensi Datang</span></div>
                <div><label><i class="fa-solid fa-arrow-down"></i> Jam
                        Pulang</label><strong>{{ $jamPulang->format('H:i') }}</strong><span>WIB Presensi Selesai</span>
                </div>
            </div>
            <div class="master-jam-show-duration"><i class="fa-solid fa-circle"></i> Total Durasi Kerja:
                <b>{{ $durasiJam }} Jam{{ $durasiSisaMenit ? ' ' . $durasiSisaMenit . ' Menit' : '' }}</b>
                <small>(Termasuk
                    Istirahat)</small>
            </div>

            <div class="master-jam-show-tolerance">
                <div><label>Toleransi Keterlambatan</label><strong>{{ $jamKerja->toleransi_menit }}
                        <small>menit</small></strong></div>
                <div><span>Batas Maksimal</span><small>Sebelum sistem memotong Tukin</small></div>
            </div>
        </section>

        <aside class="master-jam-show-note">
            <i class="fa-solid fa-circle-info"></i>
            <p><strong>Catatan Sinkronisasi Presensi:</strong> Perubahan atau aktivasi jam kerja hari
                <b>{{ $jamKerja->hari }}</b> ini diterapkan otomatis pada validasi presensi GPS / biometrik pegawai
                Perumdam Tirta Pinang. Keterlambatan di atas toleransi {{ $jamKerja->toleransi_menit }} menit akan dipotong
                secara akumulatif pada pelaporan kinerja.
            </p>
        </aside>
        <a href="{{ route('master.jam-kerja.index') }}" class="master-jam-show-secondary"><i
                class="fa-solid fa-arrow-left"></i> Kembali</a>

    </div>
@endsection

@push('styles')
    <style>
        body.role-master .master-jam-show-page {
            width: 100%;
            max-width: 560px;
            margin: auto;
            padding: 1rem 1rem 0;
            box-sizing: border-box;
            color: #14223d
        }

        .master-jam-show-breadcrumb {
            display: none;
        }

        .master-jam-show-breadcrumb {
            display: flex;
            align-items: center;
            gap: .55rem;
            margin-bottom: 1rem;
            color: #14223d;
            font-size: .72rem
        }

        .master-jam-show-breadcrumb a {
            color: #71829c;
            text-decoration: none
        }

        .master-jam-show-breadcrumb i {
            font-size: .5rem;
            color: #9caabd
        }

        .master-jam-show-heading {
            display: flex;
            justify-content: space-between;
            gap: .5rem
        }

        .master-jam-show-heading h1 {
            margin: 0;
            font-size: 1rem
        }

        .master-jam-show-heading p {
            margin: .3rem 0 0;
            color: #71829c;
            font-size: .76rem
        }

        .master-jam-show-heading-actions {
            display: flex;
            flex-direction: column;
            gap: .5rem
        }

        .master-jam-show-heading-actions a {
            padding: .55rem .75rem;
            border-radius: 11px;
            text-decoration: none;
            font-size: .72rem;
            white-space: nowrap
        }

        .master-jam-show-heading-actions .back {
            color: #14223d;
            background: #fff;
            border: 1px solid #c9d6e5
        }

        .master-jam-show-heading-actions .edit {
            color: #fff;
            background: #078dcc;
            text-align: center
        }

        .master-jam-show-status {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 1rem .3rem 1.2rem;
            font-size: .7rem
        }

        .master-jam-show-status span {
            padding: .42rem .75rem;
            color: #07986b;
            background: #effff8;
            border: 1px solid #a6edce;
            border-radius: 999px
        }

        .master-jam-show-status span i {
            font-size: .4rem
        }

        .master-jam-show-status em {
            color: #91a1b8;
            font-style: normal
        }

        .master-jam-show-card {
            padding: 1.2rem 1rem;
            background: #fff;
            border: 1px solid #e0e8f1;
            border-radius: 20px;
            box-shadow: 0 5px 16px #2334550b
        }

        .master-jam-show-card-title {
            display: flex;
            align-items: center;
            gap: .7rem
        }

        .master-jam-show-card-title>span {
            display: grid;
            place-items: center;
            width: 36px;
            height: 36px;
            color: #078dcc;
            background: #effaff;
            border-radius: 10px
        }

        .master-jam-show-card-title strong {
            font-size: .82rem
        }

        .master-jam-show-card-title code {
            margin-left: auto;
            padding: .45rem .55rem;
            color: #526480;
            background: #f0f5f9;
            border-radius: 6px;
            font-size: .64rem
        }

        .master-jam-show-divider {
            height: 1px;
            margin: 1rem 0;
            background: #e5edf4
        }

        .master-jam-show-row {
            display: flex;
            justify-content: space-between;
            gap: .5rem;
            padding: .8rem 0;
            border-bottom: 1px solid #edf2f7
        }

        .master-jam-show-row label {
            display: block;
            color: #91a1b8;
            font-size: .72rem
        }

        .master-jam-show-row strong {
            display: block;
            margin-top: .25rem;
            font-size: .85rem
        }

        .master-jam-show-row strong.blue {
            color: #0875ad;
            font-size: 1rem
        }

        .master-jam-show-row small {
            display: block;
            margin-top: .25rem;
            color: #91a1b8;
            font-size: .67rem
        }

        .master-jam-show-row .badge {
            height: max-content;
            padding: .45rem .65rem;
            color: #526480;
            background: #f0f5f9;
            border-radius: 8px;
            font-size: .7rem
        }

        .master-jam-show-row .badge.special {
            color: #0875ad;
            background: #eff9ff;
            border: 1px solid #bce7ff
        }

        .master-jam-show-time-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .8rem;
            margin-top: 1rem
        }

        .master-jam-show-time-grid>div {
            padding: .8rem;
            border: 1px solid #dce5ee;
            border-radius: 14px;
            background: #f8fafc
        }

        .master-jam-show-time-grid label {
            display: block;
            color: #607492;
            font-size: .72rem
        }

        .master-jam-show-time-grid label i {
            margin-right: .3rem;
            color: #10a9df
        }

        .master-jam-show-time-grid>div:last-child label i {
            color: #f03d5c
        }

        .master-jam-show-time-grid strong {
            display: block;
            margin: .5rem 0;
            color: #14223d;
            font-size: 1.35rem
        }

        .master-jam-show-time-grid span {
            padding: .25rem .35rem;
            color: #07986b;
            background: #effff8;
            border-radius: 4px;
            font-size: .6rem
        }

        .master-jam-show-time-grid>div:last-child span {
            color: #f03d5c;
            background: #fff0f2
        }

        .master-jam-show-duration {
            margin-top: 1rem;
            padding: .7rem;
            color: #526480;
            background: #effaff;
            border: 1px solid #bce7ff;
            border-radius: 12px;
            font-size: .73rem
        }

        .master-jam-show-duration i {
            margin-right: .45rem;
            color: #10a9df;
            font-size: .55rem
        }

        .master-jam-show-duration b {
            margin-left: .7rem;
            color: #07527c
        }

        .master-jam-show-duration small {
            color: #71829c
        }

        .master-jam-show-tolerance {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #e5edf4
        }

        .master-jam-show-tolerance label {
            display: block;
            color: #91a1b8;
            font-size: .72rem
        }

        .master-jam-show-tolerance strong {
            display: block;
            margin-top: .45rem;
            color: #d56f00;
            font-size: 1.35rem
        }

        .master-jam-show-tolerance strong small {
            color: #14223d;
            font-size: .7rem
        }

        .master-jam-show-tolerance>div:last-child {
            text-align: right
        }

        .master-jam-show-tolerance>div:last-child span {
            display: block;
            padding: .35rem .55rem;
            color: #d56f00;
            border: 1px solid #ffc65c;
            border-radius: 8px;
            font-size: .68rem
        }

        .master-jam-show-tolerance>div:last-child small {
            display: block;
            margin-top: .35rem;
            color: #91a1b8;
            font-size: .62rem
        }

        .master-jam-show-note {
            display: flex;
            gap: .8rem;
            margin-top: 1.2rem;
            padding: 1.1rem 1rem;
            background: #effaff;
            border: 1px solid #bce7ff;
            border-radius: 17px
        }

        .master-jam-show-note>i {
            margin-top: .15rem;
            color: #078dcc
        }

        .master-jam-show-note p {
            margin: 0;
            color: #526b8c;
            font-size: .73rem;
            line-height: 1.6
        }

        .master-jam-show-note strong {
            color: #07527c
        }

        .master-jam-show-primary,
        .master-jam-show-secondary {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            min-height: 52px;
            margin-top: 1.2rem;
            border-radius: 14px;
            text-decoration: none;
            font-size: .82rem
        }

        .master-jam-show-primary {
            color: #fff;
            background: #078dcc;
            box-shadow: 0 4px 8px #078dcc33;
            font-weight: 700
        }

        .master-jam-show-secondary {
            color: #14223d;
            background: #fff;
            border: 1px solid #dce5ee
        }

        .master-jam-show-delete {
            margin: 1.7rem 0;
            text-align: center
        }

        .master-jam-show-delete button {
            color: #f03d5c;
            background: none;
            border: 0;
            font: inherit;
            font-size: .77rem
        }

        @media(max-width:380px) {
            body.role-master .master-jam-show-page {
                padding-inline: .75rem
            }

            .master-jam-show-heading h1 {
                font-size: 1.25rem
            }

            .master-jam-show-heading p {
                font-size: .69rem
            }

            .master-jam-show-card {
                padding-inline: .8rem
            }

            .master-jam-show-card-title strong {
                font-size: .75rem
            }

            .master-jam-show-card-title code {
                font-size: .58rem
            }

            .master-jam-show-time-grid {
                gap: .5rem
            }

            .master-jam-show-time-grid>div {
                padding: .65rem
            }

            .master-jam-show-time-grid strong {
                font-size: 1.15rem
            }
        }

        @media (min-width: 768px) {
            body.role-master .master-jam-show-page {
                width: 100%;
                max-width: none;
                margin: 0;
                padding: 1rem clamp(1.25rem, 2.4vw, 2.5rem) 2rem;
                color: #1b2938;
            }

            .master-jam-show-heading {
                margin-bottom: .75rem;
            }

            .master-jam-show-breadcrumb {
                display: flex;
                align-items: center;
                gap: .4rem;
                margin-bottom: .4rem;
                color: #718092;
                font-size: .64rem;
            }

            .master-jam-show-breadcrumb a {
                color: #718092;
                text-decoration: none;
            }

            .master-jam-show-breadcrumb>span,
            .master-jam-show-breadcrumb a:hover {
                color: #1769d2;
            }

            .master-jam-show-breadcrumb i {
                color: #a0acb9;
                font-size: .48rem;
            }

            .master-jam-show-heading h1 {
                color: #1b2938;
                font-size: 1.35rem;
                font-weight: 700;
            }

            .master-jam-show-heading h1 i {
                color: #078dcc;
                font-size: 1rem;
            }

            .master-jam-show-heading p {
                margin: .2rem 0 0;
                font-size: .7rem;
            }

            .master-jam-show-status,
            .master-jam-show-card,
            .master-jam-show-note,
            .master-jam-show-secondary {
                width: 100%;
                max-width: 1120px;
                margin-right: auto;
                margin-left: auto;
            }

            .master-jam-show-status {
                margin-top: .6rem;
                margin-bottom: .65rem;
                font-size: .62rem;
            }

            .master-jam-show-status span {
                padding: .3rem .48rem;
                border-radius: 999px;
            }

            .master-jam-show-card {
                padding: 0 1rem 1rem;
                border-color: #e2e8ef;
                border-radius: 8px;
                box-shadow: none;
            }

            .master-jam-show-card-title {
                gap: .55rem;
                margin: 0 -1rem;
                padding: .8rem 1rem;
                background: #f9fbfd;
                border-bottom: 1px solid #e8eef5;
            }

            .master-jam-show-card-title>span {
                width: 32px;
                height: 32px;
                border-radius: 6px;
            }

            .master-jam-show-card-title strong {
                color: #25364a;
                font-size: .72rem;
            }

            .master-jam-show-card-title code {
                padding: .25rem .4rem;
                border-radius: 4px;
                font-size: .57rem;
            }

            .master-jam-show-divider {
                display: none;
            }

            .master-jam-show-row {
                padding: .65rem 0;
            }

            .master-jam-show-row label,
            .master-jam-show-row .badge {
                font-size: .62rem;
            }

            .master-jam-show-row strong {
                margin-top: .18rem;
                font-size: .75rem;
            }

            .master-jam-show-row small {
                margin-top: .12rem;
                font-size: .59rem;
            }

            .master-jam-show-time-grid {
                gap: .75rem;
                margin-top: .75rem;
            }

            .master-jam-show-time-grid>div {
                padding: .7rem;
                border-radius: 6px;
            }

            .master-jam-show-time-grid label {
                font-size: .63rem;
            }

            .master-jam-show-time-grid strong {
                margin: .3rem 0;
                font-size: 1.15rem;
            }

            .master-jam-show-duration,
            .master-jam-show-tolerance {
                margin-top: .75rem;
                padding: .65rem;
                border-radius: 5px;
                font-size: .64rem;
            }

            .master-jam-show-tolerance {
                padding: .7rem 0 0;
            }

            .master-jam-show-tolerance label,
            .master-jam-show-tolerance>div:last-child span,
            .master-jam-show-tolerance>div:last-child small {
                font-size: .6rem;
            }

            .master-jam-show-tolerance strong {
                margin-top: .25rem;
                font-size: 1.1rem;
            }

            .master-jam-show-note {
                gap: .5rem;
                margin-top: .7rem;
                padding: .7rem .8rem;
                border-radius: 6px;
            }

            .master-jam-show-note p {
                font-size: .63rem;
            }

            .master-jam-show-secondary {
                display: flex;
                justify-content: flex-end;
                width: max-content;
                min-width: 95px;
                min-height: 38px;
                margin-top: .7rem;
                margin-right: max(calc((100% - 1120px) / 2), 0px);
                margin-left: auto;
                padding: .5rem .75rem;
                border-radius: 5px;
                font-size: .67rem;
            }
        }
    </style>
@endpush
