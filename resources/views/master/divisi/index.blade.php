@extends('layouts.app')

@section('title', 'Data Divisi')

@section('content')
    @php
        $totalJabatan = $divisis->getCollection()->sum('jabatans_count');
    @endphp
    <div class="master-divisi-page">


        <header class="master-divisi-heading">
            <div>
                <h1>
                    <i class="fa-solid fa-building-user"></i>
                    Data Divisi
                </h1>
                <p>Kelola unit kerja Perumdam Tirta Pinang</p>
            </div>

            <a href="{{ route('master.divisi.create') }}">
                <i class="fa-solid fa-plus"></i>
                Tambah
            </a>
        </header>

        <div class="master-divisi-summary">
            <div><span class="summary-icon blue"><i class="fa-solid fa-building"></i></span>
                <div><small>Total Divisi</small><strong>{{ $divisis->total() }}</strong><em>Unit</em></div>
            </div>
            <div><span class="summary-icon purple"><i class="fa-solid fa-users"></i></span>
                <div><small>Total Jabatan</small><strong>{{ $totalJabatan }}</strong><em>Posisi</em></div>
            </div>
        </div>
        <div class="master-divisi-search">
            <div><i class="fa-solid fa-magnifying-glass"></i><input type="search" placeholder="Cari nama divisi..."></div>
            <button type="button" aria-label="Filter"><i class="fa-solid fa-filter"></i></button>
        </div>

        <div class="master-divisi-table-wrap">
            <table class="master-divisi-table">
                <thead>
                    <tr>
                        <th scope="col">No</th>
                        <th scope="col">Nama Divisi</th>
                        <th scope="col">Kategori</th>
                        <th scope="col">Jumlah Jabatan</th>
                        <th scope="col">Tanggal Pembaruan</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="master-divisi-table-actions-heading">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($divisis as $divisi)
                        <tr>
                            <td><span
                                    class="master-divisi-table-number">{{ $loop->iteration + ($divisis->currentPage() - 1) * $divisis->perPage() }}</span>
                            </td>
                            <td><strong class="master-divisi-table-name">{{ $divisi->nama_divisi }}</strong></td>
                            <td><span class="master-divisi-table-category">{{ $divisi->kategori ?? 'Unit Kerja' }}</span>
                            </td>
                            <td><span class="master-divisi-table-jabatan"><i class="fa-solid fa-circle"></i>
                                    {{ $divisi->jabatans_count }} Jabatan</span></td>
                            <td><span class="master-divisi-table-updated"><i class="fa-regular fa-calendar"></i>
                                    {{ \Carbon\Carbon::parse($divisi->updated_at)->translatedFormat('d F Y') }}</span></td>
                            <td><span class="master-divisi-table-status"><i class="fa-solid fa-circle"></i> Aktif</span>
                            </td>
                            <td>
                                <div class="master-divisi-table-actions">
                                    <a href="{{ route('master.divisi.show', $divisi) }}"
                                        aria-label="Detail {{ $divisi->nama_divisi }}" title="Detail"><i
                                            class="fa-regular fa-eye"></i></a>
                                    <a href="{{ route('master.divisi.edit', $divisi) }}"
                                        aria-label="Edit {{ $divisi->nama_divisi }}" title="Edit"><i
                                            class="fa-solid fa-pen-to-square"></i></a>
                                    <form action="{{ route('master.divisi.destroy', $divisi) }}" method="POST"
                                        class="form-delete" data-confirm-message="Yakin ingin menghapus divisi ini?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" aria-label="Hapus {{ $divisi->nama_divisi }}"
                                            title="Hapus"><i class="fa-regular fa-trash-can"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="master-divisi-table-empty" colspan="7">Belum ada data divisi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="master-divisi-list">
            @forelse($divisis as $divisi)
                <article class="master-divisi-card">
                    <div class="master-divisi-card-main"><span
                            class="master-divisi-number">{{ $loop->iteration + ($divisis->currentPage() - 1) * $divisis->perPage() }}</span>
                        <div class="master-divisi-card-content">
                            <h2>{{ $divisi->nama_divisi }}</h2>
                            <div class="master-divisi-meta"><span><i class="fa-solid fa-circle"></i>
                                    {{ $divisi->jabatans_count }}
                                    Jabatan</span><em>{{ $divisi->kategori ?? 'Unit Kerja' }}</em></div>
                        </div>
                    </div>
                    <div class="master-divisi-card-footer"><span><i class="fa-regular fa-calendar"></i>
                            {{ \Carbon\Carbon::parse($divisi->created_at)->translatedFormat('d F Y') }}</span>
                        <div class="master-divisi-actions"><a href="{{ route('master.divisi.show', $divisi) }}"
                                aria-label="Detail"><i class="fa-regular fa-eye"></i></a><a
                                href="{{ route('master.divisi.edit', $divisi) }}" aria-label="Edit"><i
                                    class="fa-solid fa-pen-to-square"></i></a>
                            <form action="{{ route('master.divisi.destroy', $divisi) }}" method="POST" class="form-delete"
                                data-confirm-message="Yakin ingin menghapus divisi ini?">@csrf @method('DELETE')<button
                                    type="submit" aria-label="Hapus"><i class="fa-regular fa-trash-can"></i></button>
                            </form>
                        </div>
                    </div>
                </article>
            @empty
                <div class="master-divisi-empty"><i class="fa-regular fa-folder-open"></i><strong>Belum ada data
                        divisi.</strong></div>
            @endforelse
        </div>
        <div class="master-divisi-pagination">{{ $divisis->links('pagination::bootstrap-5') }}</div>
        <aside class="master-divisi-note">
            <i class="fa-solid fa-circle-info"></i>
            <span>Divisi menjadi pengelompokan unit kerja dan jabatan dalam struktur organisasi Perumdam Tirta
                Pinang.</span>
        </aside>
    </div>
@endsection

@push('styles')
    <style>
        body.role-master .master-divisi-page {
            width: 100%;
            max-width: 560px;
            margin: auto;
            padding: 1rem 1rem 0;
            box-sizing: border-box;
            color: #14223d
        }

        .master-divisi-breadcrumb {
            display: flex;
            align-items: center;
            gap: .5rem;
            margin-bottom: .75rem;
            color: #0875ad;
            font-size: .72rem
        }

        .master-divisi-breadcrumb a {
            color: #91a1b8;
            text-decoration: none
        }

        .master-divisi-breadcrumb i {
            font-size: .5rem;
            color: #9caabd
        }

        .master-divisi-heading {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: .6rem;
            margin-bottom: 1.2rem
        }

        .master-divisi-heading h1 {
            margin: 0;
            font-size: 1rem
        }

        .master-divisi-heading p {
            margin: .3rem 0 0;
            color: #71829c;
            font-size: .78rem
        }

        .master-divisi-heading>a {
            padding: .7rem .85rem;
            color: #fff;
            background: #078dcc;
            border-radius: 12px;
            box-shadow: 0 4px 8px #078dcc33;
            text-decoration: none;
            font-size: .75rem;
            white-space: nowrap
        }

        .master-divisi-summary {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .7rem;
            margin-bottom: 1rem
        }

        .master-divisi-summary>div {
            display: flex;
            align-items: center;
            gap: .7rem;
            padding: .95rem;
            background: #fff;
            border: 1px solid #e0e8f1;
            border-radius: 17px;
            box-shadow: 0 4px 14px #2334550a
        }

        .summary-icon {
            display: grid;
            place-items: center;
            width: 52px;
            height: 52px;
            flex: 0 0 52px;
            border-radius: 15px;
            font-size: 1.2rem
        }

        .summary-icon.blue {
            color: #078dcc;
            background: #e9f7ff
        }

        .summary-icon.purple {
            color: #514be0;
            background: #eef0ff
        }

        .master-divisi-summary small,
        .master-divisi-summary em {
            display: block;
            color: #91a1b8;
            font-size: .68rem;
            font-style: normal
        }

        .master-divisi-summary strong {
            margin: .2rem .25rem 0 0;
            font-size: 1.2rem
        }

        .master-divisi-summary strong,
        .master-divisi-summary em {
            display: inline-block
        }

        .master-divisi-search {
            display: flex;
            gap: .65rem;
            margin-bottom: 1rem
        }

        .master-divisi-search>div {
            display: flex;
            align-items: center;
            gap: .6rem;
            min-width: 0;
            flex: 1;
            padding: .78rem 1rem;
            background: #fff;
            border: 1px solid #dfe7f0;
            border-radius: 14px;
            box-shadow: 0 3px 8px #2334550a;
            color: #91a1b8
        }

        .master-divisi-search input {
            width: 100%;
            min-width: 0;
            border: 0;
            outline: 0;
            color: #263653;
            font: inherit;
            font-size: .75rem
        }

        .master-divisi-search button {
            width: 50px;
            color: #526480;
            background: #fff;
            border: 1px solid #dfe7f0;
            border-radius: 14px;
            font-size: 1rem
        }

        .master-divisi-list {
            display: grid;
            gap: .85rem
        }

        .master-divisi-card {
            overflow: hidden;
            background: #fff;
            border: 1px solid #e1e9f2;
            border-radius: 19px;
            box-shadow: 0 5px 16px #2334550b
        }

        .master-divisi-card-main {
            display: flex;
            gap: .85rem;
            padding: 1.05rem 1.1rem 1rem
        }

        .master-divisi-number {
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

        .master-divisi-card-content {
            min-width: 0;
            padding-top: .05rem
        }

        .master-divisi-card h2 {
            margin: 0;
            font-size: .95rem;
            overflow-wrap: anywhere
        }

        .master-divisi-meta {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: .55rem;
            margin-top: .45rem
        }

        .master-divisi-meta span {
            padding: .32rem .55rem;
            color: #526480;
            background: #f0f4f8;
            border-radius: 10px;
            font-size: .65rem
        }

        .master-divisi-meta span i {
            color: #a6b5c7;
            font-size: .45rem
        }

        .master-divisi-meta em {
            color: #91a1b8;
            font-size: .68rem;
            font-style: normal
        }

        .master-divisi-card-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .5rem;
            padding: .85rem 1.1rem;
            border-top: 1px solid #e8eef5;
            color: #91a1b8;
            font-size: .7rem
        }

        .master-divisi-card-footer>span i {
            margin-right: .3rem
        }

        .master-divisi-actions {
            display: flex;
            align-items: center;
            gap: .8rem
        }

        .master-divisi-actions a,
        .master-divisi-actions button {
            padding: 0;
            color: #526b8c;
            background: none;
            border: 0;
            text-decoration: none;
            font-size: .95rem
        }

        .master-divisi-actions a:first-child {
            color: #526b8c
        }

        .master-divisi-actions a:nth-child(2) {
            color: #526b8c
        }

        .master-divisi-actions button {
            color: #526b8c
        }

        .master-divisi-actions form {
            margin: 0
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

        .master-divisi-pagination {
            margin-top: .9rem
        }

        .master-divisi-pagination .pagination {
            justify-content: center;
            flex-wrap: wrap
        }

        .master-divisi-pagination svg {
            width: 14px;
            height: 14px
        }

        .master-divisi-table-wrap,
        .master-divisi-note {
            display: none;
        }

        @media(max-width:380px) {
            body.role-master .master-divisi-page {
                padding-inline: .75rem
            }

            .master-divisi-heading h1 {
                font-size: 1.25rem
            }

            .master-divisi-heading p {
                font-size: .7rem
            }

            .master-divisi-summary {
                gap: .45rem
            }

            .master-divisi-summary>div {
                padding: .7rem;
                gap: .45rem
            }

            .summary-icon {
                width: 42px;
                height: 42px;
                flex-basis: 42px;
                font-size: 1rem
            }

            .master-divisi-summary small,
            .master-divisi-summary em {
                font-size: .58rem
            }

            .master-divisi-summary strong {
                font-size: 1rem
            }

            .master-divisi-card-main {
                padding-inline: .9rem
            }

            .master-divisi-card-footer {
                padding-inline: .9rem
            }

            .master-divisi-actions {
                gap: .6rem
            }
        }

        @media (min-width: 768px) {
            .master-divisi-heading {
                align-items: center;
                margin-bottom: 1rem;
            }

            .master-divisi-heading h1 {
                color: #1b2938;
                font-size: 1.4rem;
                font-weight: 700;
            }

            .master-divisi-heading p {
                margin-top: .25rem;
                font-size: .78rem;
            }

            .master-divisi-heading>a {
                padding: .65rem .85rem;
                background: #078dcc;
                border-radius: 6px;
                box-shadow: none;
                font-size: .72rem;
            }

            .master-divisi-summary {
                gap: .75rem;
                margin-bottom: .85rem;
            }

            .master-divisi-summary>div {
                min-height: 74px;
                padding: .75rem .9rem;
                border-radius: 7px;
                box-shadow: none;
            }

            .summary-icon {
                width: 38px;
                height: 38px;
                flex-basis: 38px;
                border-radius: 7px;
                font-size: .9rem;
            }

            .master-divisi-search {
                gap: .5rem;
                margin-bottom: .8rem;
            }

            .master-divisi-search>div {
                padding: .55rem .7rem;
                border-radius: 6px;
                box-shadow: none;
            }

            .master-divisi-search button {
                width: 38px;
                border-radius: 6px;
            }

            .master-divisi-table-wrap {
                display: block;
                width: 100%;
                overflow-x: auto;
                background: #fff;
                border: 1px solid #e2e8ef;
                border-radius: 7px;
            }

            .master-divisi-table {
                width: 100%;
                min-width: 970px;
                border-collapse: separate;
                border-spacing: 0;
                color: #334155;
                font-size: .7rem;
            }

            .master-divisi-table th {
                padding: .68rem .7rem;
                color: #617185;
                background: #f7f9fb;
                border-bottom: 1px solid #e2e8ef;
                font-size: .59rem;
                font-weight: 700;
                text-align: left;
                text-transform: uppercase;
                white-space: nowrap;
            }

            .master-divisi-table td {
                padding: .6rem .7rem;
                border-bottom: 1px solid #edf1f5;
                vertical-align: middle;
            }

            .master-divisi-table tbody tr:last-child td {
                border-bottom: 0;
            }

            .master-divisi-table tbody tr:hover {
                background: #fafcfe;
            }

            .master-divisi-table-number {
                display: grid;
                place-items: center;
                width: 23px;
                height: 23px;
                color: #1769d2;
                background: #edf5ff;
                border-radius: 5px;
                font-size: .63rem;
            }

            .master-divisi-table-name {
                display: block;
                min-width: 160px;
                color: #25364a;
                font-size: .69rem;
            }

            .master-divisi-table-category {
                display: inline-block;
                padding: .28rem .4rem;
                color: #526480;
                background: #f0f4f8;
                border-radius: 4px;
                font-size: .61rem;
                white-space: nowrap;
            }

            .master-divisi-table-jabatan,
            .master-divisi-table-updated {
                display: inline-flex;
                align-items: center;
                gap: .35rem;
                color: #617185;
                font-size: .64rem;
                white-space: nowrap;
            }

            .master-divisi-table-jabatan i,
            .master-divisi-table-updated i {
                color: #8290a0;
                font-size: .62rem;
            }

            .master-divisi-table-status {
                display: inline-flex;
                align-items: center;
                gap: .3rem;
                padding: .25rem .4rem;
                color: #087c50;
                background: #e6f7ef;
                border-radius: 999px;
                font-size: .59rem;
                white-space: nowrap;
            }

            .master-divisi-table-status i {
                font-size: .4rem;
            }

            .master-divisi-table-actions-heading {
                text-align: right !important;
            }

            .master-divisi-table-actions {
                display: flex;
                align-items: center;
                justify-content: flex-end;
                gap: .2rem;
                white-space: nowrap;
            }

            .master-divisi-table-actions a,
            .master-divisi-table-actions button {
                display: grid;
                place-items: center;
                width: 28px;
                height: 28px;
                padding: 0;
                color: #617185;
                background: transparent;
                border: 1px solid transparent;
                border-radius: 4px;
                text-decoration: none;
                cursor: pointer;
            }

            .master-divisi-table-actions a:hover {
                color: #1769d2;
                background: #edf4ff;
            }

            .master-divisi-table-actions button {
                color: #d33755;
            }

            .master-divisi-table-actions button:hover {
                background: #fff0f3;
            }

            .master-divisi-table-actions form {
                margin: 0;
            }

            .master-divisi-table-empty {
                height: 90px;
                color: #718092;
                text-align: center;
            }

            .master-divisi-list {
                display: none !important;
            }

            .master-divisi-pagination .pagination {
                justify-content: flex-end;
            }

            .master-divisi-note {
                display: flex;
                align-items: center;
                gap: .6rem;
                margin-top: .8rem;
                padding: .7rem .8rem;
                color: #2870aa;
                background: #eff8fd;
                border: 1px solid #d5eaf7;
                border-radius: 6px;
                font-size: .68rem;
            }
        }
    </style>
@endpush
