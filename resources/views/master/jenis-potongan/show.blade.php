@extends('layouts.app')

@section('title', 'Detail Jenis Potongan')

@section('content')
    @php
        $tipe = str_replace('_', ' ', ucfirst($jenisPotongan->tipe_pelanggaran));
        $rentang =
            $jenisPotongan->menit_min !== null || $jenisPotongan->menit_max !== null
                ? ($jenisPotongan->menit_min ?? 0) . ' – ' . ($jenisPotongan->menit_max ?? '∞') . ' menit'
                : '- (Tanpa Keterangan)';
    @endphp

    <div class="master-potongan-show-page">


        <header class="master-potongan-show-heading">
            <div>
                <nav class="master-potongan-show-breadcrumb" aria-label="Breadcrumb">
                    <a href="{{ route('master.dashboard') }}">Beranda</a>
                    <i class="fa-solid fa-chevron-right"></i>
                    <a href="{{ route('master.jenis-potongan.index') }}">Jenis Potongan</a>
                    <i class="fa-solid fa-chevron-right"></i>
                    <span>Detail</span>
                </nav>
                <h1>
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                    Detail Jenis Potongan
                </h1>
                <p>Informasi lengkap aturan potongan presensi dan kedisiplinan pegawai.</p>
            </div>
        </header>



        <section class="master-potongan-show-card">
            <div class="master-potongan-show-card-title">
                <span><i class="fa-solid fa-receipt"></i></span>
                <strong>INFORMASI POTONGAN</strong>
                <code>DATA MASTER</code>
            </div>
            <div class="master-potongan-show-divider"></div>

            <div class="master-potongan-show-name">
                <label>Nama Potongan</label>
                <strong>{{ $jenisPotongan->nama_potongan }}</strong>
                <span>Aturan potongan presensi pegawai</span>
            </div>

            <div class="master-potongan-show-type">
                <div><label>Tipe Pelanggaran</label><strong>{{ $tipe }}</strong></div>
                <span
                    class="badge">{{ match ($jenisPotongan->tipe_pelanggaran) {
                        'alpa' => 'Tanpa Keterangan',
                        'tidak_absen' => 'Tidak Absen',
                        'pulang_awal' => 'Pulang Sebelum Waktu',
                        default => 'Terlambat',
                    } }}</span>
            </div>

            <div class="master-potongan-show-grid">
                <div><label>Rentang Menit</label><strong>{{ $rentang }}</strong></div>
                <div><label>Persentase Potongan</label><strong
                        class="rate">{{ number_format((float) $jenisPotongan->persen_potongan, 2) }}%</strong><small>Potongan
                        Tukin</small></div>
            </div>

            <div class="master-potongan-show-description">
                <label>Keterangan</label>
                <p>{{ $jenisPotongan->keterangan ?: 'Tidak ada keterangan tambahan untuk aturan ini.' }}</p>
            </div>
        </section>

        <aside class="master-potongan-show-note">
            <i class="fa-solid fa-circle-info"></i>
            <p><strong>Catatan Perhitungan:</strong> Potongan presensi akan otomatis mengurangi kalkulasi Tunjangan Kinerja
                (Tukin) bruto pegawai sesuai periode berjalan.</p>
        </aside>

        <div class="master-potongan-show-actions">
            <a href="{{ route('master.jenis-potongan.index') }}" class="master-potongan-show-secondary"><i
                    class="fa-solid fa-arrow-left"></i> Kembali</a>
            <a href="{{ route('master.jenis-potongan.edit', $jenisPotongan) }}" class="master-potongan-show-primary"><i
                    class="fa-solid fa-pen"></i> Edit</a>
        </div>

    </div>
@endsection

@push('styles')
    <style>
        body.role-master .master-potongan-show-page {
            width: 100%;
            max-width: 560px;
            margin: auto;
            padding: 1rem 1rem 0;
            box-sizing: border-box;
            color: #14223d
        }

        .master-potongan-show-breadcrumb {
            display: none;
        }

        .master-potongan-show-breadcrumb {
            display: flex;
            align-items: center;
            gap: .45rem;
            margin-bottom: 1rem;
            color: #14223d;
            font-size: .67rem;
            white-space: nowrap
        }

        .master-potongan-show-breadcrumb a {
            color: #0875ad;
            text-decoration: none
        }

        .master-potongan-show-breadcrumb i {
            font-size: .45rem;
            color: #9caabd
        }

        .master-potongan-show-heading {
            display: flex;
            justify-content: space-between;
            gap: .6rem
        }

        .master-potongan-show-heading h1 {
            margin: 0;
            font-size: 1rem
        }

        .master-potongan-show-heading p {
            margin: .3rem 0;
            color: #71829c;
            font-size: .73rem;
            line-height: 1.5
        }

        .master-potongan-show-heading-actions {
            display: flex;
            flex-direction: column;
            gap: .45rem
        }

        .master-potongan-show-heading-actions a {
            padding: .55rem .7rem;
            border-radius: 10px;
            text-decoration: none;
            font-size: .69rem;
            white-space: nowrap
        }

        .master-potongan-show-heading-actions .back {
            color: #14223d;
            background: #fff;
            border: 1px solid #c9d6e5
        }

        .master-potongan-show-heading-actions .edit {
            color: #fff;
            background: #078dcc;
            text-align: center
        }

        .master-potongan-show-status {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 1rem .3rem 1.1rem;
            font-size: .68rem
        }

        .master-potongan-show-status span {
            padding: .4rem .7rem;
            color: #07986b;
            background: #effff8;
            border: 1px solid #a6edce;
            border-radius: 999px
        }

        .master-potongan-show-status span i {
            font-size: .4rem
        }

        .master-potongan-show-status em {
            color: #91a1b8;
            font-style: normal
        }

        .master-potongan-show-card {
            padding: 1.15rem 1rem;
            background: #fff;
            border: 1px solid #e0e8f1;
            border-radius: 20px;
            box-shadow: 0 5px 16px #2334550b
        }

        .master-potongan-show-card-title {
            display: flex;
            align-items: center;
            gap: .65rem
        }

        .master-potongan-show-card-title>span {
            display: grid;
            place-items: center;
            width: 36px;
            height: 36px;
            color: #078dcc;
            background: #effaff;
            border-radius: 10px
        }

        .master-potongan-show-card-title strong {
            font-size: .78rem
        }

        .master-potongan-show-card-title code {
            margin-left: auto;
            padding: .4rem .5rem;
            color: #526480;
            background: #f0f5f9;
            border-radius: 6px;
            font-size: .58rem
        }

        .master-potongan-show-divider {
            height: 1px;
            margin: 1rem 0;
            background: #e5edf4
        }

        .master-potongan-show-name {
            padding: .25rem 0 .9rem
        }

        .master-potongan-show-name label,
        .master-potongan-show-type label,
        .master-potongan-show-grid label,
        .master-potongan-show-description label {
            display: block;
            color: #91a1b8;
            font-size: .71rem
        }

        .master-potongan-show-name strong {
            display: block;
            margin-top: .3rem;
            font-size: 1rem
        }

        .master-potongan-show-name span {
            display: block;
            margin-top: .25rem;
            color: #91a1b8;
            font-size: .67rem
        }

        .master-potongan-show-type {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: .9rem 0;
            border-top: 1px solid #edf2f7
        }

        .master-potongan-show-type strong {
            display: block;
            margin-top: .3rem;
            font-size: .82rem
        }

        .master-potongan-show-type .badge {
            padding: .45rem .6rem;
            color: #a85d00;
            background: #fff0bf;
            border-radius: 8px;
            font-size: .67rem
        }

        .master-potongan-show-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .7rem;
            padding: 1rem 0;
            border-top: 1px solid #edf2f7
        }

        .master-potongan-show-grid>div {
            padding: .75rem;
            background: #f8fafc;
            border: 1px solid #edf2f7;
            border-radius: 12px
        }

        .master-potongan-show-grid strong {
            display: block;
            margin-top: .4rem;
            font-size: .78rem
        }

        .master-potongan-show-grid strong.rate {
            color: #d56f00;
            font-size: 1.2rem
        }

        .master-potongan-show-grid small {
            display: block;
            margin-top: .2rem;
            color: #91a1b8;
            font-size: .61rem
        }

        .master-potongan-show-description {
            padding-top: 1rem;
            border-top: 1px solid #edf2f7
        }

        .master-potongan-show-description p {
            margin: .4rem 0 0;
            color: #526b8c;
            font-size: .72rem;
            line-height: 1.55
        }

        .master-potongan-show-note {
            display: flex;
            gap: .7rem;
            margin-top: 1.1rem;
            padding: 1rem;
            color: #526b8c;
            background: #effaff;
            border: 1px solid #bce7ff;
            border-radius: 16px
        }

        .master-potongan-show-note>i {
            margin-top: .15rem;
            color: #078dcc
        }

        .master-potongan-show-note p {
            margin: 0;
            font-size: .7rem;
            line-height: 1.55
        }

        .master-potongan-show-note strong {
            color: #07527c
        }

        .master-potongan-show-primary,
        .master-potongan-show-secondary {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            min-height: 51px;
            margin-top: 1.1rem;
            border-radius: 14px;
            text-decoration: none;
            font-size: .8rem
        }

        .master-potongan-show-primary {
            color: #fff;
            background: #078dcc;
            box-shadow: 0 4px 8px #078dcc33;
            font-weight: 700
        }

        .master-potongan-show-secondary {
            color: #14223d;
            background: #fff;
            border: 1px solid #dce5ee
        }

        .master-potongan-show-delete {
            margin: 1.6rem 0;
            text-align: center
        }

        .master-potongan-show-delete button {
            color: #f03d5c;
            background: none;
            border: 0;
            font: inherit;
            font-size: .75rem
        }

        @media(max-width:380px) {
            body.role-master .master-potongan-show-page {
                padding-inline: .75rem
            }

            .master-potongan-show-heading h1 {
                font-size: 1.22rem
            }

            .master-potongan-show-heading p {
                font-size: .68rem
            }

            .master-potongan-show-card {
                padding-inline: .8rem
            }

            .master-potongan-show-card-title strong {
                font-size: .7rem
            }

            .master-potongan-show-grid {
                gap: .5rem
            }

            .master-potongan-show-grid>div {
                padding: .6rem
            }

            .master-potongan-show-grid strong.rate {
                font-size: 1.05rem
            }
        }

        @media (min-width: 768px) {
            body.role-master .master-potongan-show-page {
                width: 100%;
                max-width: none;
                margin: 0;
                padding: 1rem clamp(1.25rem, 2.4vw, 2.5rem) 2rem;
                color: #1b2938;
            }

            .master-potongan-show-heading {
                margin-bottom: .9rem;
            }

            .master-potongan-show-breadcrumb {
                display: flex;
                align-items: center;
                gap: .4rem;
                margin-bottom: .4rem;
                color: #718092;
                font-size: .64rem;
            }

            .master-potongan-show-breadcrumb a {
                color: #718092;
                text-decoration: none;
            }

            .master-potongan-show-breadcrumb>span,
            .master-potongan-show-breadcrumb a:hover {
                color: #1769d2;
            }

            .master-potongan-show-breadcrumb i {
                color: #a0acb9;
                font-size: .48rem;
            }

            .master-potongan-show-heading h1 {
                display: flex;
                align-items: center;
                gap: .5rem;
                color: #1b2938;
                font-size: 1.35rem;
                font-weight: 700;
            }

            .master-potongan-show-heading h1 i {
                display: grid;
                place-items: center;
                width: 30px;
                height: 30px;
                color: #078dcc;
                background: #e8f7ff;
                border-radius: 6px;
                font-size: .82rem;
            }

            .master-potongan-show-heading p {
                margin: .2rem 0 0 2.4rem;
                font-size: .7rem;
            }

            .master-potongan-show-card,
            .master-potongan-show-note {
                width: 100%;
                max-width: 1120px;
                margin-right: auto;
                margin-left: auto;
            }

            .master-potongan-show-card {
                padding: 0 1rem 1rem;
                border-color: #e2e8ef;
                border-radius: 8px;
                box-shadow: none;
            }

            .master-potongan-show-card-title {
                gap: .6rem;
                margin: 0 -1rem;
                padding: .8rem 1rem;
                background: #f9fbfd;
                border-bottom: 1px solid #e8eef5;
            }

            .master-potongan-show-card-title>span {
                width: 32px;
                height: 32px;
                border-radius: 6px;
            }

            .master-potongan-show-card-title strong {
                color: #25364a;
                font-size: .72rem;
            }

            .master-potongan-show-card-title code {
                padding: .25rem .4rem;
                border-radius: 4px;
                font-size: .57rem;
            }

            .master-potongan-show-divider {
                display: none;
            }

            .master-potongan-show-name {
                padding: .9rem 0;
            }

            .master-potongan-show-name label,
            .master-potongan-show-type label,
            .master-potongan-show-grid label,
            .master-potongan-show-description label {
                font-size: .63rem;
            }

            .master-potongan-show-name strong {
                margin-top: .2rem;
                color: #1b2938;
                font-size: 1rem;
            }

            .master-potongan-show-name span {
                margin-top: .15rem;
                font-size: .62rem;
            }

            .master-potongan-show-type {
                padding: .7rem 0;
            }

            .master-potongan-show-type strong {
                margin-top: .18rem;
                font-size: .73rem;
            }

            .master-potongan-show-type .badge {
                padding: .3rem .45rem;
                border-radius: 4px;
                font-size: .61rem;
            }

            .master-potongan-show-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: .7rem;
                padding: .75rem 0;
            }

            .master-potongan-show-grid>div {
                padding: .7rem;
                border-radius: 5px;
            }

            .master-potongan-show-grid strong {
                margin-top: .25rem;
                font-size: .72rem;
            }

            .master-potongan-show-grid strong.rate {
                color: #d56f00;
                font-size: 1.1rem;
            }

            .master-potongan-show-grid small {
                font-size: .58rem;
            }

            .master-potongan-show-description {
                padding-top: .75rem;
            }

            .master-potongan-show-description p {
                margin-top: .25rem;
                color: #526b8c;
                font-size: .68rem;
            }

            .master-potongan-show-note {
                gap: .5rem;
                margin-top: .7rem;
                padding: .7rem .8rem;
                border-radius: 6px;
            }

            .master-potongan-show-note p {
                font-size: .63rem;
            }

            .master-potongan-show-actions {
                display: flex;
                justify-content: flex-end;
                gap: .5rem;
                width: 100%;
                max-width: 1120px;
                margin: .75rem auto 0;
                padding-top: .7rem;
                border-top: 1px solid #e8eef5;
            }

            .master-potongan-show-primary,
            .master-potongan-show-secondary {
                width: auto;
                min-width: 95px;
                min-height: 38px;
                margin-top: 0;
                padding: .5rem .75rem;
                border-radius: 5px;
                font-size: .67rem;
            }

            .master-potongan-show-primary {
                order: 2;
                box-shadow: none;
            }

            .master-potongan-show-secondary {
                order: 1;
            }
        }
    </style>
@endpush
