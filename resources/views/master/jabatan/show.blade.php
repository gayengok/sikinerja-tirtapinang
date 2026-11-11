@extends('layouts.app')

@section('title', 'Detail Jabatan')

@section('content')
    <div class="master-jabatan-show">

        <header class="master-jabatan-show-heading">
            <div>
                <h1>
                    <i class="fa-solid fa-circle-info"></i>
                    Detail Jabatan
                </h1>
                <p>Informasi struktur dan target posisi</p>
            </div>

            <div class="master-jabatan-show-actions">
                <a href="{{ route('master.jabatan.index') }}">
                    <i class="fa-solid fa-arrow-left"></i>
                    Kembali
                </a>
            </div>
        </header>

        <section class="master-jabatan-detail-card">
            <div class="master-jabatan-detail-title">
                <span></span>
                <h2>Informasi Jabatan</h2>
                <b>Aktif</b>
            </div>

            <div class="master-jabatan-detail-field">
                <label>Nama Jabatan</label>
                <strong><i class="fa-solid fa-briefcase"></i>{{ $jabatan->nama_jabatan }}</strong>
            </div>

            <div class="master-jabatan-detail-field">
                <label>Divisi / Unit Kerja</label>
                <strong><i class="fa-regular fa-building"></i>{{ $jabatan->divisi->nama_divisi }}</strong>
            </div>

            <div class="master-jabatan-summary">
                <div>
                    <small>GRADE</small>
                    <strong>Grade {{ $jabatan->grade ?: '-' }}</strong>
                </div>
                <div class="tukin">
                    <small>BASE TUKIN</small>
                    <strong>Rp {{ number_format($jabatan->base_tukin / 1000000, 1, ',', '.') }} jt</strong>
                </div>
                <div>
                    <small>PEGAWAI</small>
                    <strong>{{ $jabatan->pegawais->count() }} <em>Orang</em></strong>
                </div>
            </div>

            <div class="master-jabatan-trend-heading">
                <div>
                    <h3>Tren & Distribusi Beban Kerja</h3>
                    <p>Target KPI & realisasi triwulan</p>
                </div>
                <span>{{ $currentPerformance !== null && $currentPerformance >= 90 ? 'Tukin Optimal' : 'Perlu Evaluasi' }}</span>
            </div>

            <div class="master-jabatan-chart">
                <div class="master-jabatan-chart-heading">
                    <span>Tren Performa Bulanan</span>
                    <strong>
                        <i></i>
                        Rata-rata
                        {{ $performanceAverage !== null ? number_format($performanceAverage, 1, ',', '.') . '%' : '-' }}
                    </strong>
                </div>
                <div class="master-jabatan-bars">
                    @forelse ($performanceData as $performance)
                        <div class="master-jabatan-bar-item {{ $loop->last ? 'active' : '' }}">
                            <small>{{ number_format($performance['average'], 0) }}%</small>
                            <span style="height: {{ max(8, round($performance['average'] * 0.55)) }}px"></span>
                            <label>{{ $performance['label'] }}</label>
                        </div>
                    @empty
                        <div class="master-jabatan-chart-empty">Belum ada data penilaian KPI.</div>
                    @endforelse
                    <em>Target 100%</em>
                </div>
            </div>

            <div class="master-jabatan-achievement">
                <span>Pencapaian KPI Berjalan</span>
                <strong>
                    {{ $currentPerformance !== null ? number_format($currentPerformance, 0) . '%' : '-' }}
                    @if ($currentPerformance !== null)
                        <em>({{ $currentPerformance >= 90 ? 'Sangat Baik' : ($currentPerformance >= 75 ? 'Baik' : 'Perlu Evaluasi') }})</em>
                    @endif
                </strong>
                <div><i style="width: {{ min(100, max(0, $currentPerformance ?? 0)) }}%"></i></div>
            </div>
        </section>
    </div>
@endsection

@push('styles')
    <style>
        body.role-master .master-jabatan-show {
            width: 100%;
            max-width: 560px;
            margin: auto;
            padding: 1rem 1rem 0;
            box-sizing: border-box;
            color: #14223d;
        }

        .master-jabatan-show-crumb {
            display: flex;
            align-items: center;
            gap: .5rem;
            margin-bottom: 1rem;
            color: #0875ad;
            font-size: .72rem;
        }

        .master-jabatan-show-crumb a {
            color: #71829c;
            text-decoration: none;
        }

        .master-jabatan-show-crumb i {
            color: #9caabd;
            font-size: .5rem;
        }

        .master-jabatan-show-heading {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: .7rem;
            margin-bottom: 1.2rem;
        }

        .master-jabatan-show-heading h1 {
            margin: 0;
            font-size: 1rem;
        }

        .master-jabatan-show-heading p {
            max-width: 220px;
            margin: .35rem 0 0;
            color: #647895;
            font-size: .8rem;
            line-height: 1.4;
        }

        .master-jabatan-show-actions {
            display: flex;
            gap: .5rem;
            padding-top: .1rem;
        }

        .master-jabatan-show-actions a {
            display: flex;
            align-items: center;
            gap: .4rem;
            padding: .62rem .75rem;
            color: #526480;
            background: #fff;
            border: 1px solid #dfe7f0;
            border-radius: 11px;
            font-size: .72rem;
            text-decoration: none;
            white-space: nowrap;
        }

        .master-jabatan-show-actions a.edit {
            color: #fff;
            background: #078dcc;
            border-color: #078dcc;
            box-shadow: 0 4px 8px #078dcc33;
            font-weight: 600;
        }

        .master-jabatan-detail-card {
            overflow: hidden;
            padding: 1.1rem 1.2rem 1.15rem;
            background: #fff;
            border: 1px solid #dfe8f1;
            border-radius: 20px;
            box-shadow: 0 5px 17px #2334550b;
        }

        .master-jabatan-detail-title {
            display: flex;
            align-items: center;
            gap: .6rem;
            padding-bottom: .85rem;
            border-bottom: 1px solid #e7eef5;
        }

        .master-jabatan-detail-title>span {
            width: 8px;
            height: 20px;
            background: #08a8e4;
            border-radius: 5px;
        }

        .master-jabatan-detail-title h2 {
            flex: 1;
            margin: 0;
            font-size: .94rem;
        }

        .master-jabatan-detail-title b {
            padding: .35rem .6rem;
            color: #526480;
            background: #f2f5f8;
            border-radius: 999px;
            font-size: .62rem;
            font-weight: 500;
        }

        .master-jabatan-detail-field {
            margin-top: .85rem;
        }

        .master-jabatan-detail-field label {
            display: block;
            margin-bottom: .35rem;
            color: #526480;
            font-size: .7rem;
        }

        .master-jabatan-detail-field strong {
            display: block;
            padding: .72rem .8rem;
            color: #14223d;
            background: #f8fafc;
            border: 1px solid #edf1f5;
            border-radius: 13px;
            font-size: .76rem;
        }

        .master-jabatan-detail-field strong i {
            width: 22px;
            margin-right: .35rem;
            color: #078dcc;
        }

        .master-jabatan-summary {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: .55rem;
            margin-top: 1rem;
            padding-bottom: .9rem;
            border-bottom: 1px solid #e7eef5;
        }

        .master-jabatan-summary>div {
            min-width: 0;
            padding: .62rem .6rem;
            background: #f8fafc;
            border: 1px solid #edf1f5;
            border-radius: 13px;
        }

        .master-jabatan-summary>div.tukin {
            background: #f1fff9;
            border-color: #a8edd0;
        }

        .master-jabatan-summary small {
            display: block;
            color: #526480;
            font-size: .58rem;
            font-weight: 500;
        }

        .master-jabatan-summary strong {
            display: block;
            margin-top: .28rem;
            color: #0875ad;
            font-size: .75rem;
            overflow-wrap: anywhere;
        }

        .master-jabatan-summary .tukin strong {
            color: #00865f;
        }

        .master-jabatan-summary em {
            color: #526480;
            font-size: .62rem;
            font-style: normal;
            font-weight: 400;
        }

        .master-jabatan-trend-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .5rem;
            padding: .85rem 0 .7rem;
        }

        .master-jabatan-trend-heading h3 {
            margin: 0;
            font-size: .72rem;
        }

        .master-jabatan-trend-heading p {
            margin: .25rem 0 0;
            color: #71829c;
            font-size: .63rem;
        }

        .master-jabatan-trend-heading>span {
            padding: .35rem .55rem;
            color: #00865f;
            background: #effff8;
            border: 1px solid #b5efd7;
            border-radius: 999px;
            font-size: .6rem;
            white-space: nowrap;
        }

        .master-jabatan-chart {
            position: relative;
            padding: .75rem .65rem .55rem;
            background: #f8fafc;
            border: 1px solid #edf1f5;
            border-radius: 14px;
        }

        .master-jabatan-chart-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: #526480;
            font-size: .62rem;
        }

        .master-jabatan-chart-heading strong {
            color: #008dcc;
            font-size: .62rem;
        }

        .master-jabatan-chart-heading i {
            display: inline-block;
            width: 6px;
            height: 6px;
            margin-right: .2rem;
            background: #08a8e4;
            border-radius: 50%;
        }

        .master-jabatan-bars {
            display: flex;
            align-items: flex-end;
            justify-content: space-around;
            height: 105px;
            margin-top: .5rem;
            padding: 0 .2rem;
            border-top: 1px dashed #cbd9e7;
        }

        .master-jabatan-bar-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-end;
            height: 100%;
            gap: .25rem;
        }

        .master-jabatan-bar-item small {
            color: #526480;
            font-size: .58rem;
        }

        .master-jabatan-bar-item span {
            display: block;
            width: 33px;
            background: linear-gradient(#4fc2ed, #24a8df);
            border-radius: 7px 7px 5px 5px;
        }

        .master-jabatan-bar-item.active small,
        .master-jabatan-bar-item.active label {
            color: #008dcc;
            font-weight: 600;
        }

        .master-jabatan-bar-item.active span {
            background: linear-gradient(#20b2ea, #008dcc);
        }

        .master-jabatan-bar-item label {
            color: #71829c;
            font-size: .62rem;
        }

        .master-jabatan-chart-empty {
            align-self: center;
            color: #91a1b8;
            font-size: .65rem;
        }

        .master-jabatan-bars>em {
            position: absolute;
            right: .75rem;
            top: 3.5rem;
            color: #91a1b8;
            font-size: .55rem;
            font-style: normal;
        }

        .master-jabatan-achievement {
            margin-top: .65rem;
            padding: .65rem .7rem .55rem;
            background: #fff;
            border: 1px solid #dfe8f1;
            border-radius: 13px;
        }

        .master-jabatan-achievement>span {
            color: #526480;
            font-size: .65rem;
        }

        .master-jabatan-achievement>strong {
            float: right;
            color: #14223d;
            font-size: .68rem;
        }

        .master-jabatan-achievement>strong em {
            color: #00865f;
            font-style: normal;
        }

        .master-jabatan-achievement>div {
            clear: both;
            height: 8px;
            margin-top: .55rem;
            overflow: hidden;
            background: #edf2f6;
            border-radius: 99px;
        }

        .master-jabatan-achievement>div i {
            display: block;
            height: 100%;
            background: linear-gradient(90deg, #078dcc, #3fc1ef);
            border-radius: inherit;
        }

        @media (max-width: 380px) {
            body.role-master .master-jabatan-show {
                padding-inline: .75rem;
            }

            .master-jabatan-show-heading {
                display: block;
            }

            .master-jabatan-show-actions {
                margin-top: .75rem;
            }

            .master-jabatan-show-actions a {
                flex: 1;
                justify-content: center;
            }

            .master-jabatan-detail-card {
                padding-inline: .9rem;
            }

            .master-jabatan-summary {
                gap: .4rem;
            }

            .master-jabatan-summary>div {
                padding-inline: .45rem;
            }
        }
    </style>
@endpush
