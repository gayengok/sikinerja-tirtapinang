@extends('layouts.app')

@section('title', 'Master Kompetensi')

@section('content')
    @php
        $kompetensi = $masterKompetensis->getCollection();
    @endphp
    <div class="master-kompetensi-page">

        <header class="master-kompetensi-heading">
            <div>
                <h1>
                    <i class="fa-solid fa-user-check"></i>
                    Master Kompetensi
                </h1>
                <p>Kelola standar perilaku &amp; penilaian pegawai</p>
            </div>
            <a href="{{ route('master.master-kompetensi.create') }}">
                <i class="fa-solid fa-plus"></i> Tambah
            </a>
        </header>


        <div class="master-kompetensi-search"><i class="fa-solid fa-magnifying-glass"></i><input type="search"
                placeholder="Cari indikator atau kompetensi..."></div>
        <div class="master-kompetensi-tabs"><span class="active">Semua ({{ $masterKompetensis->total() }})</span><span>Semua
                Jabatan ({{ $kompetensi->count() }})</span><span>Struktural</span><span>Fungsional</span></div>
        <div class="master-kompetensi-list-head"><span>DAFTAR KOMPETENSI AKTIF</span><span>URUTAN 1 -
                {{ $masterKompetensis->total() }}</span></div>

        <div class="master-kompetensi-table-wrap">
            <table class="master-kompetensi-table">
                <thead>
                    <tr>
                        <th scope="col">No</th>
                        <th scope="col">Nama Kompetensi</th>
                        <th scope="col">Target Jabatan</th>
                        <th scope="col">Urutan Tampil</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="master-kompetensi-table-actions-heading">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($masterKompetensis as $komp)
                        <tr>
                            <td><span
                                    class="master-kompetensi-table-number">{{ $loop->iteration + ($masterKompetensis->currentPage() - 1) * $masterKompetensis->perPage() }}</span>
                            </td>
                            <td><strong class="master-kompetensi-table-name">{{ $komp->nama_kompetensi }}</strong></td>
                            <td><span
                                    class="master-kompetensi-table-role">{{ $komp->jabatan_id ? $komp->jabatan->nama_jabatan ?? '-' : 'Semua Jabatan' }}</span>
                            </td>
                            <td><span class="master-kompetensi-table-order">Urutan ke-{{ $komp->urutan }}</span></td>
                            <td><span class="master-kompetensi-table-status"><i class="fa-solid fa-circle"></i> Aktif</span>
                            </td>
                            <td>
                                <div class="master-kompetensi-table-actions">
                                    <a href="{{ route('master.master-kompetensi.edit', $komp) }}"
                                        aria-label="Edit {{ $komp->nama_kompetensi }}" title="Edit"><i
                                            class="fa-regular fa-pen-to-square"></i></a>
                                    <form action="{{ route('master.master-kompetensi.destroy', $komp) }}" method="POST"
                                        class="form-delete" data-confirm-message="Yakin ingin menghapus kompetensi ini?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" aria-label="Hapus {{ $komp->nama_kompetensi }}"
                                            title="Hapus"><i class="fa-regular fa-trash-can"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="master-kompetensi-table-empty" colspan="6">Belum ada data kompetensi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="master-kompetensi-list">
            @forelse($masterKompetensis as $komp)
                <article class="master-kompetensi-card">
                    <div class="master-kompetensi-card-main"><span
                            class="master-kompetensi-number">{{ $loop->iteration + ($masterKompetensis->currentPage() - 1) * $masterKompetensis->perPage() }}</span>
                        <div class="master-kompetensi-content">
                            <h2>{{ $komp->nama_kompetensi }}</h2>
                            <div>
                                <b>{{ $komp->jabatan_id ? $komp->jabatan->nama_jabatan ?? '-' : 'Semua Jabatan' }}</b><span>Urutan
                                    ke-{{ $komp->urutan }}</span>
                            </div>
                        </div>
                        <div class="master-kompetensi-actions"><a
                                href="{{ route('master.master-kompetensi.edit', $komp) }}" aria-label="Edit"><i
                                    class="fa-solid fa-pen-to-square"></i></a>
                            <form action="{{ route('master.master-kompetensi.destroy', $komp) }}" method="POST"
                                class="form-delete" data-confirm-message="Yakin ingin menghapus kompetensi ini?">@csrf
                                @method('DELETE')<button type="submit" aria-label="Hapus"><i
                                        class="fa-regular fa-trash-can"></i></button></form>
                        </div>
                    </div>
                </article>
            @empty
                <div class="master-kompetensi-empty"><i class="fa-regular fa-folder-open"></i><strong>Belum ada data
                        kompetensi.</strong></div>
            @endforelse
        </div>
        <aside class="master-kompetensi-note"><span><i class="fa-solid fa-circle-info"></i></span>
            <div><strong>Ketentuan Penilaian Kompetensi</strong>
                <p>Kompetensi dasar berlaku untuk seluruh jenjang jabatan pegawai Perumdam Tirta Pinang dan dinilai secara
                    periodik beriringan dengan capaian target KPI.</p>
            </div>
        </aside>
        <div class="master-kompetensi-pagination">{{ $masterKompetensis->links('pagination::bootstrap-5') }}</div>
    </div>
@endsection

@push('styles')
    <style>
        body.role-master .master-kompetensi-page {
            width: 100%;
            max-width: 560px;
            margin: auto;
            padding: 1rem 1rem 0;
            box-sizing: border-box;
            color: #14223d
        }

        .master-kompetensi-breadcrumb {
            display: flex;
            align-items: center;
            gap: .5rem;
            margin-bottom: .8rem;
            color: #0875ad;
            font-size: .7rem
        }

        .master-kompetensi-breadcrumb a {
            color: #71829c;
            text-decoration: none
        }

        .master-kompetensi-breadcrumb i {
            font-size: .5rem;
            color: #9caabd
        }

        .master-kompetensi-heading {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: .5rem;
            margin-bottom: 1rem
        }

        .master-kompetensi-heading h1 {
            margin: 0;
            font-size: 1rem
        }

        .master-kompetensi-heading p {
            margin: .3rem 0 0;
            color: #71829c;
            font-size: .75rem
        }

        .master-kompetensi-heading>a {
            padding: .7rem .85rem;
            color: #fff;
            background: #078dcc;
            border-radius: 12px;
            box-shadow: 0 4px 8px #078dcc33;
            text-decoration: none;
            font-size: .75rem;
            white-space: nowrap
        }

        .master-kompetensi-search {
            display: flex;
            align-items: center;
            gap: .65rem;
            padding: .8rem 1rem;
            margin-bottom: .9rem;
            color: #91a1b8;
            background: #fff;
            border: 1px solid #dfe7f0;
            border-radius: 14px;
            box-shadow: 0 3px 8px #2334550a
        }

        .master-kompetensi-search input {
            width: 100%;
            border: 0;
            outline: 0;
            font: inherit;
            font-size: .75rem
        }

        .master-kompetensi-tabs {
            display: flex;
            gap: .5rem;
            overflow: auto;
            margin-bottom: 1rem
        }

        .master-kompetensi-tabs span {
            flex: 0 0 auto;
            padding: .58rem .8rem;
            color: #526480;
            border: 1px solid #dce5ee;
            border-radius: 999px;
            font-size: .67rem;
            white-space: nowrap
        }

        .master-kompetensi-tabs .active {
            color: #fff;
            background: #078dcc;
            border-color: #078dcc
        }

        .master-kompetensi-list-head {
            display: flex;
            justify-content: space-between;
            gap: .5rem;
            margin: 0 .35rem .8rem;
            color: #91a1b8;
            font-size: .66rem
        }

        .master-kompetensi-card {
            background: #fff;
            border: 1px solid #e0e8f1;
            border-radius: 19px;
            box-shadow: 0 5px 16px #2334550b
        }

        .master-kompetensi-list {
            display: grid;
            gap: .8rem
        }

        .master-kompetensi-card-main {
            display: flex;
            align-items: flex-start;
            gap: .7rem;
            padding: 1.05rem 1rem
        }

        .master-kompetensi-number {
            display: grid;
            place-items: center;
            width: 43px;
            height: 43px;
            flex: 0 0 43px;
            color: #0875ad;
            background: #eef9ff;
            border: 1px solid #ccecff;
            border-radius: 14px;
            font-size: .85rem
        }

        .master-kompetensi-content {
            min-width: 0;
            flex: 1
        }

        .master-kompetensi-content h2 {
            margin: .15rem 0 .45rem;
            font-size: .9rem;
            overflow-wrap: anywhere
        }

        .master-kompetensi-content>div {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: .5rem
        }

        .master-kompetensi-content b {
            padding: .4rem .55rem;
            color: #0875ad;
            background: #eff9ff;
            border: 1px solid #bae7ff;
            border-radius: 7px;
            font-size: .65rem;
            font-weight: 500
        }

        .master-kompetensi-content span {
            color: #91a1b8;
            font-size: .68rem
        }

        .master-kompetensi-actions {
            display: flex;
            gap: .65rem;
            padding: .55rem .7rem;
            background: #f8fafc;
            border: 1px solid #edf2f7;
            border-radius: 13px
        }

        .master-kompetensi-actions a,
        .master-kompetensi-actions button {
            padding: 0;
            color: #607492;
            background: none;
            border: 0;
            text-decoration: none;
            font-size: .9rem
        }

        .master-kompetensi-actions button {
            color: #f03d5c
        }

        .master-kompetensi-actions form {
            margin: 0
        }

        .master-kompetensi-empty {
            display: grid;
            place-items: center;
            gap: .45rem;
            padding: 2rem;
            color: #91a1b8
        }

        .master-kompetensi-empty i {
            font-size: 2rem
        }

        .master-kompetensi-note {
            display: flex;
            gap: .75rem;
            margin-top: 1.1rem;
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

        .master-kompetensi-pagination {
            margin-top: .9rem
        }

        .master-kompetensi-pagination .pagination {
            justify-content: center;
            flex-wrap: wrap
        }

        .master-kompetensi-pagination svg {
            width: 14px;
            height: 14px
        }

        .master-kompetensi-table-wrap {
            display: none;
        }

        @media(max-width:380px) {
            body.role-master .master-kompetensi-page {
                padding-inline: .75rem
            }

            .master-kompetensi-heading h1 {
                font-size: 1.25rem
            }

            .master-kompetensi-heading p {
                font-size: .69rem
            }

            .master-kompetensi-card-main {
                padding-inline: .8rem;
                gap: .5rem
            }

            .master-kompetensi-number {
                width: 36px;
                height: 36px;
                flex-basis: 36px
            }

            .master-kompetensi-content h2 {
                font-size: .78rem
            }

            .master-kompetensi-actions {
                gap: .5rem;
                padding: .5rem
            }

            .master-kompetensi-actions a,
            .master-kompetensi-actions button {
                font-size: .78rem
            }
        }

        @media (min-width: 768px) {
            .master-kompetensi-heading {
                align-items: center;
                margin-bottom: 1rem;
            }

            .master-kompetensi-heading h1 {
                color: #1b2938;
                font-size: 1.4rem;
                font-weight: 700;
            }

            .master-kompetensi-heading p {
                font-size: .78rem;
            }

            .master-kompetensi-heading>a {
                padding: .65rem .85rem;
                background: #078dcc;
                border-radius: 6px;
                box-shadow: none;
                font-size: .72rem;
            }

            .master-kompetensi-search {
                padding: .58rem .7rem;
                border-radius: 6px;
                box-shadow: none;
            }

            .master-kompetensi-tabs {
                gap: .4rem;
                margin-bottom: .85rem;
            }

            .master-kompetensi-tabs span {
                padding: .4rem .62rem;
                border-radius: 5px;
                font-size: .64rem;
            }

            .master-kompetensi-tabs .active {
                background: #078dcc;
                border-color: #078dcc;
            }

            .master-kompetensi-list-head {
                margin: 0 .35rem .55rem;
                font-size: .61rem;
            }

            .master-kompetensi-table-wrap {
                display: block;
                width: 100%;
                overflow-x: auto;
                background: #fff;
                border: 1px solid #e2e8ef;
                border-radius: 6px;
            }

            .master-kompetensi-table {
                width: 100%;
                min-width: 800px;
                border-collapse: separate;
                border-spacing: 0;
                color: #334155;
                font-size: .72rem;
            }

            .master-kompetensi-table th {
                padding: .68rem .75rem;
                color: #617185;
                background: #f7f9fb;
                border-bottom: 1px solid #e2e8ef;
                font-size: .61rem;
                font-weight: 700;
                text-align: left;
                text-transform: uppercase;
                white-space: nowrap;
            }

            .master-kompetensi-table td {
                padding: .63rem .75rem;
                border-bottom: 1px solid #edf1f5;
                vertical-align: middle;
            }

            .master-kompetensi-table tbody tr:last-child td {
                border-bottom: 0;
            }

            .master-kompetensi-table tbody tr:hover {
                background: #fafcfe;
            }

            .master-kompetensi-table-number {
                display: grid;
                place-items: center;
                width: 23px;
                height: 23px;
                color: #1769d2;
                background: #edf5ff;
                border-radius: 50%;
                font-size: .64rem;
            }

            .master-kompetensi-table-name {
                display: block;
                min-width: 180px;
                color: #25364a;
                font-size: .72rem;
            }

            .master-kompetensi-table-role {
                display: inline-block;
                padding: .3rem .45rem;
                color: #2870aa;
                background: #eff8fd;
                border-radius: 4px;
                font-size: .64rem;
                white-space: nowrap;
            }

            .master-kompetensi-table-order {
                color: #526480;
                font-size: .68rem;
                white-space: nowrap;
            }

            .master-kompetensi-table-status {
                display: inline-flex;
                align-items: center;
                gap: .3rem;
                padding: .25rem .4rem;
                color: #087c50;
                background: #e6f7ef;
                border-radius: 999px;
                font-size: .6rem;
                white-space: nowrap;
            }

            .master-kompetensi-table-status i {
                font-size: .4rem;
            }

            .master-kompetensi-table-actions-heading {
                text-align: center !important;
            }

            .master-kompetensi-table-actions {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: .2rem;
                white-space: nowrap;
            }

            .master-kompetensi-table-actions a,
            .master-kompetensi-table-actions button {
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

            .master-kompetensi-table-actions a:hover {
                color: #1769d2;
                background: #edf4ff;
            }

            .master-kompetensi-table-actions button {
                color: #d33755;
            }

            .master-kompetensi-table-actions button:hover {
                background: #fff0f3;
            }

            .master-kompetensi-table-actions form {
                margin: 0;
            }

            .master-kompetensi-table-empty {
                height: 90px;
                color: #718092;
                text-align: center;
            }

            .master-kompetensi-list {
                display: none !important;
            }

            .master-kompetensi-note {
                border-radius: 6px;
            }

            .master-kompetensi-pagination .pagination {
                justify-content: flex-end;
            }
        }
    </style>
@endpush
