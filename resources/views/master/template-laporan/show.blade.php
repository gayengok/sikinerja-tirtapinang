@extends('layouts.app')
@section('title', 'Detail Template Laporan')
@section('content')
    <div class="master-template-detail-page">

        <header class="master-template-detail-heading">
            <div>
                <nav class="master-template-detail-breadcrumb" aria-label="Breadcrumb">
                    <a href="{{ route('master.dashboard') }}">Beranda</a>
                    <i class="fa-solid fa-chevron-right"></i>
                    <a href="{{ route('master.template-laporan.index') }}">Template Laporan</a>
                    <i class="fa-solid fa-chevron-right"></i>
                    <span>Detail</span>
                </nav>
                <h1>
                    <i class="fas fa-file-circle-check"></i>
                    Detail Template Laporan
                </h1>
                <p>Informasi konfigurasi field pelaporan harian dan integrasi KPI.</p>
            </div>
        </header>


        <section class="master-template-detail-card">
            <div class="master-template-detail-title"><span><i class="fa-regular fa-file-lines"></i></span><strong>INFORMASI
                    TEMPLATE</strong><code>DATA MASTER</code></div>
            <div class="master-template-detail-divider"></div>
            <div class="master-template-detail-name"><label>Nama
                    Field</label><strong>{{ $templateLaporan->nama_field }}</strong><small>Indikator aktivitas pada laporan
                    harian divisi</small></div>
            <div class="master-template-detail-row">
                <div><label>Divisi</label><strong>{{ $templateLaporan->divisi->nama_divisi }}</strong></div>
                <span>{{ $templateLaporan->divisi->nama_divisi }}</span>
            </div>
            <div class="master-template-detail-row">
                <div><label>Jabatan</label><strong>{{ $templateLaporan->jabatan?->nama_jabatan ?? 'Semua jabatan (template lama)' }}</strong></div>
                <span>{{ $templateLaporan->jabatan?->nama_jabatan ?? 'Semua jabatan' }}</span>
            </div>
            <div class="master-template-detail-grid">
                <div><label>Tipe Field</label><strong>{{ ucfirst($templateLaporan->tipe_field) }}</strong></div>
                <div><label>Satuan</label><strong>{{ $templateLaporan->satuan ?: '-' }}</strong></div>
            </div>
            <div class="master-template-detail-kpi"><label>KPI
                    Terhubung</label><strong>{{ $templateLaporan->masterKpi->indikator ?? '- (Tidak Terhubung)' }}</strong><small>{{ $templateLaporan->masterKpi ? 'Nilai field diakumulasi ke penilaian KPI terkait.' : 'Field ini belum terhubung dengan indikator KPI.' }}</small>
            </div>
            <div class="master-template-detail-used"><i class="fa-solid fa-chart-line"></i><span>Digunakan pada
                    laporan</span><strong>{{ $templateLaporan->laporanHarianDetails->count() }} detail</strong></div>
        </section>
        <aside class="master-template-detail-note"><i class="fa-solid fa-circle-info"></i>
            <p><strong>Catatan:</strong> Template laporan digunakan sebagai konfigurasi field input harian divisi. Hubungkan
                dengan KPI agar nilai dapat mendukung perhitungan kinerja bulanan.</p>
        </aside>
        <div class="master-template-detail-actions">
            <a href="{{ route('master.template-laporan.index') }}" class="master-template-detail-secondary"><i
                    class="fa-solid fa-arrow-left"></i> Kembali</a>
            <a href="{{ route('master.template-laporan.edit', $templateLaporan) }}"
                class="master-template-detail-primary"><i class="fa-solid fa-pen"></i> Edit</a>
        </div>

    </div>
@endsection
@push('styles')
    <style>
        body.role-master .master-template-detail-page {
            max-width: 560px;
            margin: auto;
            padding: 1rem 1rem 0;
            color: #14223d
        }

        .master-template-detail-breadcrumb {
            display: none;
        }

        .master-template-detail-crumb {
            display: flex;
            gap: .45rem;
            align-items: center;
            margin-bottom: 1rem;
            font-size: .68rem;
            white-space: nowrap
        }

        .master-template-detail-crumb a {
            color: #0875ad;
            text-decoration: none
        }

        .master-template-detail-crumb i {
            font-size: .45rem;
            color: #9caabd
        }

        .master-template-detail-heading {
            display: flex;
            justify-content: space-between;
            gap: .6rem
        }

        .master-template-detail-heading h1 {
            margin: 0;
            font-size: 1rem
        }

        .master-template-detail-heading p {
            margin: .3rem 0;
            color: #71829c;
            font-size: .73rem
        }

        .master-template-detail-heading>div:last-child {
            display: flex;
            flex-direction: column;
            gap: .45rem
        }

        .master-template-detail-heading a {
            padding: .52rem .65rem;
            border-radius: 10px;
            text-decoration: none;
            font-size: .68rem;
            white-space: nowrap
        }

        .master-template-detail-heading .back {
            color: #14223d;
            background: #fff;
            border: 1px solid #c9d6e5
        }

        .master-template-detail-heading .edit {
            color: #fff;
            background: #078dcc;
            text-align: center
        }

        .master-template-detail-status {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 1rem .3rem 1.1rem;
            font-size: .68rem
        }

        .master-template-detail-status span {
            padding: .4rem .7rem;
            color: #07986b;
            background: #effff8;
            border: 1px solid #a6edce;
            border-radius: 999px
        }

        .master-template-detail-status span i {
            font-size: .4rem
        }

        .master-template-detail-status em {
            color: #91a1b8;
            font-style: normal
        }

        .master-template-detail-card {
            padding: 1.15rem 1rem;
            background: #fff;
            border: 1px solid #e0e8f1;
            border-radius: 20px;
            box-shadow: 0 5px 16px #2334550b
        }

        .master-template-detail-title {
            display: flex;
            align-items: center;
            gap: .65rem
        }

        .master-template-detail-title>span {
            display: grid;
            place-items: center;
            width: 36px;
            height: 36px;
            color: #078dcc;
            background: #effaff;
            border-radius: 10px
        }

        .master-template-detail-title strong {
            font-size: .78rem
        }

        .master-template-detail-title code {
            margin-left: auto;
            padding: .4rem .5rem;
            color: #526480;
            background: #f0f5f9;
            border-radius: 6px;
            font-size: .58rem
        }

        .master-template-detail-divider {
            height: 1px;
            margin: 1rem 0;
            background: #e5edf4
        }

        .master-template-detail-name label,
        .master-template-detail-row label,
        .master-template-detail-grid label,
        .master-template-detail-kpi label {
            display: block;
            color: #91a1b8;
            font-size: .69rem
        }

        .master-template-detail-name strong {
            display: block;
            margin-top: .3rem;
            font-size: 1rem
        }

        .master-template-detail-name small,
        .master-template-detail-kpi small {
            display: block;
            margin-top: .25rem;
            color: #91a1b8;
            font-size: .65rem
        }

        .master-template-detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1rem;
            padding: 1rem 0;
            border-top: 1px solid #edf2f7
        }

        .master-template-detail-row strong,
        .master-template-detail-grid strong {
            display: block;
            margin-top: .3rem;
            font-size: .8rem
        }

        .master-template-detail-row span {
            padding: .4rem .55rem;
            color: #526480;
            background: #f0f5f9;
            border-radius: 7px;
            font-size: .65rem
        }

        .master-template-detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .7rem;
            padding: 1rem 0;
            border-top: 1px solid #edf2f7
        }

        .master-template-detail-grid>div {
            padding: .75rem;
            background: #f8fafc;
            border-radius: 12px
        }

        .master-template-detail-kpi {
            padding: 1rem 0;
            border-top: 1px solid #edf2f7
        }

        .master-template-detail-kpi strong {
            display: block;
            margin-top: .3rem;
            color: #078dcc;
            font-size: .78rem;
            overflow-wrap: anywhere
        }

        .master-template-detail-used {
            display: flex;
            align-items: center;
            gap: .5rem;
            margin-top: .2rem;
            padding: .7rem;
            color: #526b8c;
            background: #effaff;
            border: 1px solid #bce7ff;
            border-radius: 12px;
            font-size: .7rem
        }

        .master-template-detail-used i {
            color: #078dcc
        }

        .master-template-detail-used strong {
            margin-left: auto;
            color: #07527c
        }

        .master-template-detail-note {
            display: flex;
            gap: .7rem;
            margin-top: 1rem;
            padding: 1rem;
            color: #526b8c;
            background: #effaff;
            border: 1px solid #bce7ff;
            border-radius: 16px
        }

        .master-template-detail-note>i {
            color: #078dcc
        }

        .master-template-detail-note p {
            margin: 0;
            font-size: .69rem;
            line-height: 1.55
        }

        .master-template-detail-note strong {
            color: #07527c
        }

        .master-template-detail-primary,
        .master-template-detail-secondary {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            min-height: 51px;
            margin-top: 1rem;
            border-radius: 14px;
            text-decoration: none;
            font-size: .8rem
        }

        .master-template-detail-primary {
            color: #fff;
            background: #078dcc;
            box-shadow: 0 4px 8px #078dcc33;
            font-weight: 700
        }

        .master-template-detail-secondary {
            color: #14223d;
            background: #fff;
            border: 1px solid #dce5ee
        }

        .master-template-detail-delete {
            margin: 1.6rem 0;
            text-align: center
        }

        .master-template-detail-delete button {
            color: #f03d5c;
            background: none;
            border: 0;
            font: inherit;
            font-size: .75rem
        }

        @media(max-width:380px) {
            body.role-master .master-template-detail-page {
                padding-inline: .75rem
            }

            .master-template-detail-heading h1 {
                font-size: 1.22rem
            }

            .master-template-detail-card {
                padding-inline: .8rem
            }
        }

        @media (min-width: 768px) {
            body.role-master .master-template-detail-page {
                width: 100%;
                max-width: none;
                margin: 0;
                padding: 1rem clamp(1.25rem, 2.4vw, 2.5rem) 2rem;
                color: #1b2938;
            }

            .master-template-detail-heading {
                margin-bottom: .85rem;
            }

            .master-template-detail-breadcrumb {
                display: flex;
                align-items: center;
                gap: .4rem;
                margin-bottom: .4rem;
                color: #718092;
                font-size: .64rem;
            }

            .master-template-detail-breadcrumb a {
                color: #718092;
                text-decoration: none;
            }

            .master-template-detail-breadcrumb>span,
            .master-template-detail-breadcrumb a:hover {
                color: #1769d2;
            }

            .master-template-detail-breadcrumb i {
                color: #a0acb9;
                font-size: .48rem;
            }

            .master-template-detail-heading h1 {
                display: flex;
                align-items: center;
                gap: .5rem;
                color: #1b2938;
                font-size: 1.35rem;
                font-weight: 700;
            }

            .master-template-detail-heading h1 i {
                color: #078dcc;
                font-size: 1rem;
            }

            .master-template-detail-heading p {
                margin: .2rem 0 0 2.4rem;
                font-size: .7rem;
            }

            .master-template-detail-card,
            .master-template-detail-note,
            .master-template-detail-actions {
                width: 100%;
                max-width: 1120px;
                margin-right: auto;
                margin-left: auto;
            }

            .master-template-detail-card {
                padding: 0 1rem 1rem;
                border-color: #e2e8ef;
                border-radius: 8px;
                box-shadow: none;
            }

            .master-template-detail-title {
                gap: .55rem;
                margin: 0 -1rem;
                padding: .8rem 1rem;
                background: #f9fbfd;
                border-bottom: 1px solid #e8eef5;
            }

            .master-template-detail-title>span {
                width: 32px;
                height: 32px;
                border-radius: 6px;
            }

            .master-template-detail-title strong {
                color: #25364a;
                font-size: .72rem;
            }

            .master-template-detail-title code {
                padding: .25rem .4rem;
                border-radius: 4px;
                font-size: .57rem;
            }

            .master-template-detail-divider {
                display: none;
            }

            .master-template-detail-name {
                padding: .9rem 0 .7rem;
            }

            .master-template-detail-name label,
            .master-template-detail-row label,
            .master-template-detail-grid label,
            .master-template-detail-kpi label {
                font-size: .63rem;
            }

            .master-template-detail-name strong {
                margin-top: .2rem;
                color: #25364a;
                font-size: .9rem;
            }

            .master-template-detail-name small,
            .master-template-detail-kpi small {
                font-size: .6rem;
            }

            .master-template-detail-row {
                margin-top: .2rem;
                padding: .65rem 0;
            }

            .master-template-detail-row strong,
            .master-template-detail-grid strong {
                margin-top: .2rem;
                font-size: .7rem;
            }

            .master-template-detail-row span {
                padding: .28rem .42rem;
                border-radius: 4px;
                font-size: .59rem;
            }

            .master-template-detail-grid {
                gap: .65rem;
                padding: .7rem 0;
            }

            .master-template-detail-grid>div {
                padding: .65rem;
                border: 1px solid #edf1f5;
                border-radius: 5px;
            }

            .master-template-detail-kpi {
                padding: .7rem 0;
            }

            .master-template-detail-kpi strong {
                margin-top: .2rem;
                font-size: .7rem;
            }

            .master-template-detail-used {
                margin-top: .1rem;
                padding: .55rem .65rem;
                border-radius: 5px;
                font-size: .62rem;
            }

            .master-template-detail-note {
                gap: .5rem;
                margin-top: .7rem;
                padding: .7rem .8rem;
                border-radius: 6px;
            }

            .master-template-detail-note p {
                font-size: .63rem;
            }

            .master-template-detail-actions {
                display: flex;
                justify-content: flex-end;
                gap: .5rem;
                margin-top: .75rem;
                padding-top: .7rem;
                border-top: 1px solid #e8eef5;
            }

            .master-template-detail-primary,
            .master-template-detail-secondary {
                width: auto;
                min-width: 95px;
                min-height: 38px;
                margin-top: 0;
                padding: .5rem .75rem;
                border-radius: 5px;
                font-size: .67rem;
            }

            .master-template-detail-primary {
                order: 2;
                box-shadow: none;
            }

            .master-template-detail-secondary {
                order: 1;
            }
        }
    </style>
@endpush
