@extends('layouts.app')

@section('title', 'Master KPI')

@section('content')
    @php
        $kpiGroups = $masterKpis->getCollection()->groupBy('jabatan_id');
        $validGroups = $kpiGroups->filter(fn($items) => $items->sum('bobot') <= 60);
    @endphp
    <div class="master-kpi-page">
        <header class="master-kpi-heading">
            <div>
                <h1>
                    <i class="fa-solid fa-bullseye"></i>
                    Master KPI
                </h1>
                <p>Total bobot KPI maksimal 60% per jabatan</p>
            </div>

            <a href="{{ route('master.master-kpi.create') }}">
                <i class="fa-solid fa-plus"></i>
                Tambah
            </a>
        </header>

        <section class="master-kpi-summary" aria-label="Ringkasan Master KPI">
            <article><span class="master-kpi-summary-icon blue"><i class="fa-solid fa-bullseye"></i></span>
                <div><small>Total Indikator</small><strong>{{ $masterKpis->total() }}</strong></div>
            </article>
            <article><span class="master-kpi-summary-icon cyan"><i class="fa-solid fa-briefcase"></i></span>
                <div><small>Jabatan Ditampilkan</small><strong>{{ $kpiGroups->count() }}</strong></div>
            </article>
            <article><span class="master-kpi-summary-icon green"><i class="fa-solid fa-circle-check"></i></span>
                <div><small>Bobot Valid</small><strong>{{ $validGroups->count() }}</strong></div>
            </article>
            <article><span class="master-kpi-summary-icon orange"><i class="fa-solid fa-triangle-exclamation"></i></span>
                <div><small>Perlu Ditinjau</small><strong>{{ $kpiGroups->count() - $validGroups->count() }}</strong></div>
            </article>
        </section>

        <div class="master-kpi-search"><i class="fa-solid fa-magnifying-glass"></i><input type="search"
                placeholder="Cari indikator KPI..."><i class="fa-solid fa-sliders"></i></div>
        <div class="master-kpi-tabs"><span class="active">Semua Jabatan ({{ $kpiGroups->count() }})</span>
            @foreach ($kpiGroups->take(4) as $items)
                <span>{{ $items->first()->jabatan->nama_jabatan }}</span>
            @endforeach
        </div>
        <div class="master-kpi-list">
            @forelse($kpiGroups as $items)
                @php
                    $jabatan = $items->first()->jabatan;
                    $totalBobot = $items->sum('bobot');
                @endphp
                <section class="master-kpi-group">
                    <header>
                        <div class="master-kpi-group-heading"><i class="fa-solid fa-circle"></i>
                            <h2>{{ $jabatan->nama_jabatan ?? '-' }}</h2>
                            <p>{{ $jabatan->divisi->nama_divisi ?? '-' }}</p>
                        </div><b class="{{ $totalBobot <= 60 ? 'valid' : 'invalid' }}"><i
                                class="fa-solid {{ $totalBobot <= 60 ? 'fa-check' : 'fa-triangle-exclamation' }}"></i>
                            {{ $totalBobot <= 60 ? $totalBobot . '% Valid' : $totalBobot . '% Melebihi 60%' }}</b>
                    </header>
                    @foreach ($items as $kpi)
                        <article class="master-kpi-item"><span>{{ $kpi->urutan }}</span>
                            <div class="master-kpi-item-content">
                                <h3>{{ $kpi->indikator }}</h3>
                                <p>Target:
                                    <strong>{{ $kpi->target_default !== null ? number_format($kpi->target_default, 2) : '-' }}</strong><i></i>
                                    Realisasi:
                                    <strong>{{ $kpi->realisasi !== null ? number_format($kpi->realisasi, 2) : '-' }}</strong><i></i>
                                    Capaian:
                                    <strong>{{ $kpi->persen_capaian !== null ? number_format($kpi->persen_capaian, 2) . '%' : '-' }}</strong>
                                </p>
                                <p>Skor: <strong>{{ $kpi->skor ?? '-' }}</strong><i></i>
                                    Bobot: <strong>{{ number_format($kpi->bobot, 2) }}%</strong>
                                    @if ($kpi->keterangan)
                                        <i></i> Keterangan: <strong>{{ $kpi->keterangan }}</strong>
                                    @endif
                                </p>
                            </div><em
                                class="{{ $kpi->arah_capaian === 'minimal' ? 'minimal' : '' }}">{{ ucfirst($kpi->arah_capaian) }}</em>
                            <div class="master-kpi-item-actions">
                                <a class="detail" href="{{ route('master.master-kpi.show', $kpi) }}">
                                    <i class="fa-regular fa-eye"></i><span>Detail</span>
                                </a>
                                <a class="edit" href="{{ route('master.master-kpi.edit', $kpi) }}">
                                    <i class="fa-solid fa-pen-to-square"></i><span>Edit</span>
                                </a>
                                <form action="{{ route('master.master-kpi.destroy', $kpi) }}" method="POST"
                                    class="form-delete" data-confirm-message="Yakin ingin menghapus KPI ini?">@csrf
                                    @method('DELETE')<button type="submit" aria-label="Hapus"><i
                                            class="fa-regular fa-trash-can"></i><span>Hapus</span></button></form>
                            </div>
                        </article>
                    @endforeach
                </section>
            @empty
                <div class="master-kpi-empty"><i class="fa-regular fa-folder-open"></i><strong>Belum ada data KPI.</strong>
                </div>
            @endforelse
        </div>
        <div class="master-kpi-pagination">{{ $masterKpis->links('pagination::bootstrap-5') }}</div>
    </div>
@endsection

@push('styles')
    <style>
        body.role-master .master-kpi-page {
            width: 100%;
            max-width: 560px;
            margin: auto;
            padding: 1rem 1rem 0;
            box-sizing: border-box;
            color: #14223d
        }

        .master-kpi-breadcrumb {
            display: flex;
            align-items: center;
            gap: .5rem;
            margin-bottom: .8rem;
            color: #0875ad;
            font-size: .7rem
        }

        .master-kpi-breadcrumb a {
            color: #0875ad;
            text-decoration: none
        }

        .master-kpi-breadcrumb i {
            font-size: .5rem;
            color: #9caabd
        }

        .master-kpi-heading {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: .6rem;
            margin-bottom: 1rem
        }

        .master-kpi-heading h1 {
            margin: 0;
            font-size: 1rem
        }

        .master-kpi-heading p {
            margin: .3rem 0 0;
            color: #71829c;
            font-size: .75rem
        }

        .master-kpi-heading>a {
            padding: .7rem .85rem;
            color: #fff;
            background: #078dcc;
            border-radius: 12px;
            box-shadow: 0 4px 8px #078dcc33;
            text-decoration: none;
            font-size: .75rem;
            white-space: nowrap
        }

        .master-kpi-search {
            display: flex;
            align-items: center;
            gap: .65rem;
            padding: .78rem 1rem;
            margin-bottom: .9rem;
            color: #91a1b8;
            background: #fff;
            border: 1px solid #dfe7f0;
            border-radius: 14px;
            box-shadow: 0 3px 8px #2334550a
        }

        .master-kpi-search input {
            width: 100%;
            min-width: 0;
            border: 0;
            outline: 0;
            font: inherit;
            font-size: .75rem
        }

        .master-kpi-search>i:last-child {
            font-size: .8rem
        }

        .master-kpi-tabs {
            display: flex;
            gap: .5rem;
            overflow: auto;
            margin-bottom: 1rem
        }

        .master-kpi-tabs span {
            flex: 0 0 auto;
            padding: .58rem .8rem;
            color: #526480;
            border: 1px solid #dce5ee;
            border-radius: 999px;
            font-size: .67rem;
            white-space: nowrap
        }

        .master-kpi-tabs .active {
            color: #fff;
            background: #078dcc;
            border-color: #078dcc
        }

        .master-kpi-list {
            display: grid;
            gap: 1rem
        }

        .master-kpi-group {
            overflow: hidden;
            background: #fff;
            border: 1px solid #dfe8f1;
            border-radius: 18px;
            box-shadow: 0 5px 16px #2334550b
        }

        .master-kpi-group>header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: .6rem;
            padding: 1rem;
            background: #f3faff;
            border-bottom: 1px solid #e3edf5
        }

        .master-kpi-group>header>div {
            min-width: 0
        }

        .master-kpi-group-heading {
            flex: 1
        }

        .master-kpi-group>header h2 {
            display: inline;
            margin: 0 0 0 .35rem;
            font-size: .8rem;
            overflow-wrap: anywhere
        }

        .master-kpi-group>header p {
            margin: .35rem 0 0 1rem;
            color: #526b8c;
            font-size: .7rem
        }

        .master-kpi-group>header>div>i {
            color: #078dcc;
            font-size: .55rem
        }

        .master-kpi-group>header>b {
            padding: .4rem .55rem;
            color: #087b5d;
            background: #e2faed;
            border: 1px solid #89e5ba;
            border-radius: 999px;
            font-size: .62rem;
            white-space: nowrap
        }

        .master-kpi-group>header>b.invalid {
            color: #b14e00;
            background: #fff0d2;
            border-color: #ffd17b
        }

        .master-kpi-group>header>b i {
            font-size: .52rem
        }

        .master-kpi-item {
            position: relative;
            display: flex;
            flex-wrap: wrap;
            align-items: flex-start;
            gap: .65rem;
            padding: 1rem;
            border-bottom: 1px solid #e8eef5
        }

        .master-kpi-item:last-child {
            border-bottom: 0
        }

        .master-kpi-item>span {
            display: grid;
            place-items: center;
            width: 24px;
            height: 24px;
            flex: 0 0 24px;
            color: #526480;
            background: #f0f4f8;
            border-radius: 7px;
            font-size: .65rem
        }

        .master-kpi-item>div:not(.master-kpi-item-actions) {
            min-width: 0;
            flex: 1
        }

        .master-kpi-item-content {
            padding-right: .1rem
        }

        .master-kpi-item h3 {
            margin: 0;
            font-size: .76rem;
            line-height: 1.35;
            overflow-wrap: anywhere
        }

        .master-kpi-item p {
            margin: .35rem 0 0;
            color: #607492;
            font-size: .66rem
        }

        .master-kpi-item p strong {
            color: #078dcc
        }

        .master-kpi-item p i {
            display: inline-block;
            width: 4px;
            height: 4px;
            margin: 0 .45rem;
            background: #bdcad7;
            border-radius: 50%;
            vertical-align: middle
        }

        .master-kpi-item>em {
            padding: .35rem .5rem;
            color: #087b5d;
            background: #ecfff5;
            border: 1px solid #9be6c0;
            border-radius: 5px;
            font-size: .61rem;
            font-style: normal;
            white-space: nowrap
        }

        .master-kpi-item>em.minimal {
            color: #b14e00;
            background: #fff8e8;
            border-color: #ffd17b
        }

        .master-kpi-item-actions {
            display: flex;
            flex: 0 0 100%;
            gap: .4rem;
            justify-content: flex-end;
            align-self: flex-end;
            margin-top: .1rem
        }

        .master-kpi-item-actions a,
        .master-kpi-item-actions button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .3rem;
            min-height: 30px;
            padding: .35rem .55rem;
            color: #526480;
            background: #f3f6fa;
            border: 1px solid transparent;
            border-radius: 8px;
            text-decoration: none;
            font: inherit;
            font-size: .62rem;
            white-space: nowrap
        }

        .master-kpi-item-actions a.edit {
            color: #087bcb;
            background: #eef8ff
        }

        .master-kpi-item-actions button {
            color: #e24559;
            background: #fff0f2;
            cursor: pointer
        }

        .master-kpi-item-actions a i,
        .master-kpi-item-actions button i {
            font-size: .72rem
        }

        .master-kpi-item-actions form {
            margin: 0
        }

        .master-kpi-empty {
            display: grid;
            place-items: center;
            gap: .45rem;
            padding: 2rem;
            color: #91a1b8
        }

        .master-kpi-empty i {
            font-size: 2rem
        }

        .master-kpi-pagination {
            margin-top: .9rem
        }

        .master-kpi-pagination .pagination {
            justify-content: center;
            flex-wrap: wrap
        }

        .master-kpi-pagination svg {
            width: 14px;
            height: 14px
        }

        @media(max-width:380px) {
            body.role-master .master-kpi-page {
                padding-inline: .75rem
            }

            .master-kpi-heading h1 {
                font-size: 1.25rem
            }

            .master-kpi-heading p {
                font-size: .69rem
            }

            .master-kpi-group>header {
                padding: .85rem
            }

            .master-kpi-group>header h2 {
                font-size: .73rem
            }

            .master-kpi-group>header>b {
                font-size: .56rem;
                padding: .35rem .4rem
            }

            .master-kpi-item {
                gap: .45rem;
                padding: .85rem
            }

            .master-kpi-item>em {
                font-size: .55rem
            }

            .master-kpi-item-actions {
                gap: .3rem
            }

            .master-kpi-item-actions a,
            .master-kpi-item-actions button {
                padding-inline: .42rem;
                font-size: .56rem
            }
        }

        .master-kpi-summary {
            display: none;
        }

        @media (min-width: 768px) {
            .master-kpi-page {
                color: #1b2938;
            }

            .master-kpi-heading {
                align-items: center;
                margin-bottom: 1.1rem;
            }

            .master-kpi-heading h1 {
                font-size: 1.45rem;
                font-weight: 700;
            }

            .master-kpi-heading p {
                margin-top: .25rem;
                font-size: .8rem;
            }

            .master-kpi-heading>a {
                border-radius: 6px;
                box-shadow: none;
            }

            .master-kpi-summary {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: .75rem;
                margin-bottom: 1rem;
            }

            .master-kpi-summary article {
                display: flex;
                align-items: center;
                gap: .75rem;
                min-height: 78px;
                padding: .8rem .9rem;
                background: #fff;
                border: 1px solid #e2e8ef;
                border-radius: 6px;
            }

            .master-kpi-summary-icon {
                display: grid;
                place-items: center;
                width: 38px;
                height: 38px;
                flex: 0 0 38px;
                border-radius: 6px;
                font-size: .85rem;
            }

            .master-kpi-summary-icon.blue {
                color: #1769d2;
                background: #eaf2ff;
            }

            .master-kpi-summary-icon.cyan {
                color: #008ba3;
                background: #e4faff;
            }

            .master-kpi-summary-icon.green {
                color: #087c50;
                background: #e6f7ef;
            }

            .master-kpi-summary-icon.orange {
                color: #a96800;
                background: #fff4dc;
            }

            .master-kpi-summary small,
            .master-kpi-summary strong {
                display: block;
            }

            .master-kpi-summary small {
                color: #718092;
                font-size: .7rem;
            }

            .master-kpi-summary strong {
                margin-top: .1rem;
                color: #1b2938;
                font-size: 1.2rem;
                line-height: 1.2;
            }

            .master-kpi-search {
                border-radius: 6px;
                box-shadow: none;
            }

            .master-kpi-tabs span {
                border-radius: 5px;
            }

            .master-kpi-tabs .active {
                background: #1769d2;
                border-color: #1769d2;
            }

            .master-kpi-list {
                grid-template-columns: minmax(0, 1fr) !important;
                gap: .85rem;
            }

            .master-kpi-group {
                border-color: #e2e8ef;
                border-radius: 7px;
                box-shadow: 0 2px 8px rgba(27, 41, 56, .04);
            }

            .master-kpi-group>header {
                align-items: center;
                padding: .9rem 1rem;
                background: #f8fafc;
            }

            .master-kpi-group>header h2 {
                color: #25364a;
                font-size: .88rem;
            }

            .master-kpi-group>header p {
                color: #718092;
                font-size: .72rem;
            }

            .master-kpi-item {
                display: grid;
                grid-template-columns: 30px minmax(0, 1fr) auto;
                align-items: start;
                gap: .55rem .75rem;
                padding: .95rem 1rem;
            }

            .master-kpi-item>span {
                width: 28px;
                height: 28px;
                color: #1769d2;
                background: #edf4ff;
                border-radius: 5px;
                font-size: .7rem;
            }

            .master-kpi-item h3 {
                color: #25364a;
                font-size: .82rem;
                line-height: 1.45;
            }

            .master-kpi-item p {
                margin-top: .4rem;
                color: #718092;
                font-size: .7rem;
                line-height: 1.5;
            }

            .master-kpi-item p strong {
                color: #40546a;
                font-weight: 600;
            }

            .master-kpi-item>em {
                border-radius: 4px;
                font-size: .65rem;
            }

            .master-kpi-item-actions {
                grid-column: 2 / -1;
                flex: initial;
                justify-content: flex-end;
                margin-top: .1rem;
            }

            .master-kpi-item-actions a,
            .master-kpi-item-actions button {
                min-height: 32px;
                padding: .4rem .65rem;
                border-radius: 5px;
                font-size: .68rem;
            }

            .master-kpi-pagination .pagination {
                justify-content: flex-end;
            }
        }

        @media (min-width: 1200px) {
            .master-kpi-summary {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }

            .master-kpi-item {
                grid-template-columns: 30px minmax(0, 1fr) auto minmax(190px, auto);
                align-items: center;
            }

            .master-kpi-item-actions {
                grid-column: 4;
                justify-content: flex-end;
                margin: 0;
            }
        }
    </style>
@endpush
