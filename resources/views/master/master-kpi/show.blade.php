@extends('layouts.app')

@section('title', 'Detail Master KPI')

@section('content')
    @php
        $isMinimal = $masterKpi->arah_capaian === 'minimal';
    @endphp
    <div class="master-kpi-show">
        <header class="master-kpi-show-heading">
            <div>
                <h1>
                    <i class="fa-solid fa-circle-info"></i>
                    Detail Master KPI
                </h1>
                <p>Informasi lengkap indikator kinerja jabatan.</p>
            </div>
        </header>

        <section class="master-kpi-show-card">
            <div class="master-kpi-show-card-title">
                <span><i class="fa-solid fa-bullseye"></i></span>
                <div>
                    <strong>{{ $masterKpi->indikator }}</strong>
                    <small>{{ $masterKpi->jabatan->nama_jabatan ?? '-' }}</small>
                </div>
                <em class="{{ $isMinimal ? 'minimal' : '' }}">{{ ucfirst($masterKpi->arah_capaian) }}</em>
            </div>

            <div class="master-kpi-show-grid">
                <div><small>Jabatan</small><strong>{{ $masterKpi->jabatan->nama_jabatan ?? '-' }}</strong></div>
                <div><small>Divisi</small><strong>{{ $masterKpi->jabatan->divisi->nama_divisi ?? '-' }}</strong></div>
                <div><small>Target
                        Default</small><strong>{{ $masterKpi->target_default !== null ? number_format($masterKpi->target_default, 2) : '-' }}</strong>
                </div>
                <div><small>Urutan Tampil</small><strong>{{ $masterKpi->urutan }}</strong></div>
                <div>
                    <small>Realisasi</small><strong>{{ $masterKpi->realisasi !== null ? number_format($masterKpi->realisasi, 2) : '-' }}</strong>
                </div>
                <div><small>%
                        Capaian</small><strong>{{ $masterKpi->persen_capaian !== null ? number_format($masterKpi->persen_capaian, 2) . '%' : '-' }}</strong>
                </div>
                <div><small>Skor (1–5)</small><strong>{{ $masterKpi->skor ?? '-' }}</strong></div>
                <div><small>Keterangan</small><strong>{{ $masterKpi->keterangan ?: '-' }}</strong></div>
            </div>

            <div class="master-kpi-show-weight">
                <div>
                    <small>Bobot Indikator</small>
                    <strong>{{ number_format($masterKpi->bobot, 2) }}%</strong>
                </div>
                <div class="master-kpi-show-progress"><i style="width: {{ min((float) $masterKpi->bobot, 100) }}%"></i>
                </div>
                <p>Total bobot KPI untuk satu jabatan tidak boleh melebihi <b>60%</b>.</p>
            </div>

            <div class="master-kpi-show-direction">
                <i class="fa-solid fa-circle-info"></i>
                <p><strong>Arah capaian {{ ucfirst($masterKpi->arah_capaian) }}:</strong>
                    {{ $isMinimal ? 'semakin rendah realisasi capaian, semakin baik.' : 'semakin tinggi realisasi capaian, semakin baik.' }}
                </p>
            </div>
        </section>

        <section class="master-kpi-show-usage">
            <div class="master-kpi-show-section-title"><i class="fa-solid fa-link"></i><strong>Penggunaan Data</strong>
            </div>
            <div class="master-kpi-show-usage-row"><span>Penilaian KPI</span><b>{{ $masterKpi->penilaianKpis->count() }}
                    data</b></div>
            <div class="master-kpi-show-usage-row"><span>Template
                    Laporan</span><b>{{ $masterKpi->templateLaporans->count() }} data</b></div>
        </section>

        <div class="master-kpi-show-actions">
            <a href="{{ route('master.master-kpi.index') }}">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar Master KPI
            </a>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        body.role-master .master-kpi-show {
            width: 100%;
            max-width: 560px;
            margin: auto;
            padding: 1rem 1rem 0;
            box-sizing: border-box;
            color: #14223d
        }

        .master-kpi-show-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .6rem;
            margin: 0 -1rem;
            padding: .9rem 1rem;
            background: #fff;
            border-bottom: 1px solid #e3ebf3
        }

        .master-kpi-show-breadcrumb {
            display: flex;
            align-items: center;
            gap: .45rem;
            min-width: 0;
            color: #0875ad;
            font-size: .68rem;
            white-space: nowrap
        }

        .master-kpi-show-breadcrumb a {
            color: #91a1b8;
            text-decoration: none
        }

        .master-kpi-show-breadcrumb i {
            color: #9caabd;
            font-size: .48rem
        }

        .master-kpi-show-back {
            flex: 0 0 auto;
            padding: .58rem .7rem;
            color: #526480;
            border: 1px solid #dce5ee;
            border-radius: 10px;
            font-size: .7rem;
            font-weight: 600;
            text-decoration: none
        }

        .master-kpi-show-heading {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: .6rem;
            padding: 1.4rem 0 1rem
        }

        .master-kpi-show-heading h1 {
            margin: 0;
            font-size: 1rem
        }

        .master-kpi-show-heading p {
            margin: .3rem 0 0;
            color: #71829c;
            font-size: .75rem
        }

        .master-kpi-show-id {
            padding: .4rem .55rem;
            color: #0875ad;
            background: #eaf7ff;
            border-radius: 8px;
            font-size: .65rem;
            font-weight: 700;
            white-space: nowrap
        }

        .master-kpi-show-card,
        .master-kpi-show-usage {
            overflow: hidden;
            background: #fff;
            border: 1px solid #dfe8f1;
            border-radius: 17px;
            box-shadow: 0 5px 16px #2334550b
        }

        .master-kpi-show-card-title {
            display: flex;
            align-items: flex-start;
            gap: .65rem;
            padding: 1rem;
            background: #f3faff;
            border-bottom: 1px solid #e3edf5
        }

        .master-kpi-show-card-title>span {
            display: grid;
            place-items: center;
            width: 34px;
            height: 34px;
            flex: 0 0 34px;
            color: #078dcc;
            background: #dff3ff;
            border-radius: 10px
        }

        .master-kpi-show-card-title>div {
            min-width: 0;
            flex: 1
        }

        .master-kpi-show-card-title strong {
            display: block;
            font-size: .82rem;
            overflow-wrap: anywhere
        }

        .master-kpi-show-card-title small {
            display: block;
            margin-top: .25rem;
            color: #71829c;
            font-size: .68rem
        }

        .master-kpi-show-card-title em,
        .master-kpi-show-usage-row b {
            padding: .35rem .5rem;
            color: #087b5d;
            background: #ecfff5;
            border: 1px solid #9be6c0;
            border-radius: 6px;
            font-size: .6rem;
            font-style: normal;
            white-space: nowrap
        }

        .master-kpi-show-card-title em.minimal {
            color: #b14e00;
            background: #fff8e8;
            border-color: #ffd17b
        }

        .master-kpi-show-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
            padding: .3rem 1rem
        }

        .master-kpi-show-grid>div {
            padding: .75rem 0;
            border-bottom: 1px solid #edf1f5
        }

        .master-kpi-show-grid small,
        .master-kpi-show-weight small {
            display: block;
            color: #7b8da5;
            font-size: .65rem
        }

        .master-kpi-show-grid strong {
            display: block;
            margin-top: .25rem;
            font-size: .76rem;
            overflow-wrap: anywhere
        }

        .master-kpi-show-weight {
            margin: .7rem 1rem 1rem;
            padding: .85rem;
            background: #f7fbfe;
            border-radius: 11px
        }

        .master-kpi-show-weight>div:first-child {
            display: flex;
            justify-content: space-between;
            align-items: center
        }

        .master-kpi-show-weight strong {
            color: #078dcc;
            font-size: .85rem
        }

        .master-kpi-show-progress {
            height: 7px;
            margin-top: .6rem;
            overflow: hidden;
            background: #dfeaf2;
            border-radius: 99px
        }

        .master-kpi-show-progress i {
            display: block;
            height: 100%;
            background: #078dcc;
            border-radius: inherit
        }

        .master-kpi-show-weight p {
            margin: .55rem 0 0;
            color: #71829c;
            font-size: .64rem
        }

        .master-kpi-show-direction {
            display: flex;
            gap: .55rem;
            margin: 0 1rem 1rem;
            padding: .75rem;
            color: #526b8c;
            background: #f7f9fc;
            border-radius: 10px
        }

        .master-kpi-show-direction i {
            color: #078dcc;
            margin-top: .1rem
        }

        .master-kpi-show-direction p {
            margin: 0;
            font-size: .67rem;
            line-height: 1.45
        }

        .master-kpi-show-usage {
            margin-top: 1rem;
            padding: 1rem
        }

        .master-kpi-show-section-title {
            display: flex;
            gap: .5rem;
            align-items: center;
            margin-bottom: .65rem;
            color: #14223d;
            font-size: .8rem
        }

        .master-kpi-show-section-title i {
            color: #078dcc
        }

        .master-kpi-show-usage-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: .7rem 0;
            border-top: 1px solid #edf1f5;
            color: #607492;
            font-size: .7rem
        }

        .master-kpi-show-usage-row b {
            color: #526480;
            background: #f3f6fa;
            border-color: #dce5ee
        }

        .master-kpi-show-actions {
            display: grid;
            gap: .6rem;
            margin-top: 1rem
        }

        .master-kpi-show-actions a,
        .master-kpi-show-actions button {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: .5rem;
            width: 100%;
            padding: .8rem;
            border: 0;
            border-radius: 11px;
            font: inherit;
            font-size: .76rem;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer
        }

        .master-kpi-show-actions .edit {
            color: #fff;
            background: #078dcc;
            box-shadow: 0 4px 8px #078dcc33
        }

        .master-kpi-show-actions form {
            margin: 0
        }

        .master-kpi-show-actions button {
            color: #e24559;
            background: #fff0f2
        }

        @media(max-width:380px) {
            body.role-master .master-kpi-show {
                padding-inline: .75rem
            }

            .master-kpi-show-heading h1 {
                font-size: 1.2rem
            }

            .master-kpi-show-grid {
                padding-inline: .85rem
            }

            .master-kpi-show-card-title {
                padding: .85rem
            }
        }
    </style>
@endpush
