@extends('layouts.app')

@section('title', 'Data Jabatan')

@section('content')
    @php
        $divisiTabs = $jabatans->getCollection()->pluck('divisi.nama_divisi')->filter()->unique()->values();
    @endphp

    <div class="master-jabatan-page">


        <header class="master-jabatan-heading">
            <div>
                <h1>
                    <i class="fa-solid fa-briefcase"></i>
                    Data Jabatan
                </h1>
                <p>Kelola jabatan pada setiap divisi</p>
            </div>

            <a href="{{ route('master.jabatan.create') }}">
                <i class="fa-solid fa-plus"></i>
                <span>Tambah Jabatan</span>
            </a>
        </header>

        <div class="master-jabatan-search">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="search" placeholder="Cari nama jabatan, divisi...">
        </div>

        <div class="master-jabatan-tabs" role="tablist" aria-label="Filter divisi">
            <button type="button" class="active">Semua Divisi ({{ $jabatans->total() }})</button>
            @foreach ($divisiTabs as $divisi)
                <button type="button">{{ $divisi }}</button>
            @endforeach
        </div>

        <div class="master-jabatan-table-wrap">
            <table class="master-jabatan-table">
                <thead>
                    <tr>
                        <th scope="col">No</th>
                        <th scope="col">Nama Jabatan</th>
                        <th scope="col">Grade</th>
                        <th scope="col">Divisi</th>
                        <th scope="col">Base Tukin</th>
                        <th scope="col" class="master-jabatan-table-actions-heading">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($jabatans as $jabatan)
                        <tr>
                            <td><span
                                    class="master-jabatan-table-number">{{ $loop->iteration + ($jabatans->currentPage() - 1) * $jabatans->perPage() }}</span>
                            </td>
                            <td><strong class="master-jabatan-table-name">{{ $jabatan->nama_jabatan }}</strong></td>
                            <td><span class="master-jabatan-table-grade grade-{{ $jabatan->grade }}">Grade
                                    {{ $jabatan->grade }}</span></td>
                            <td><span class="master-jabatan-table-division"><i class="fa-regular fa-building"></i>
                                    {{ $jabatan->divisi->nama_divisi ?? '-' }}</span></td>
                            <td><strong class="master-jabatan-table-tukin">Rp
                                    {{ number_format($jabatan->base_tukin, 0, ',', '.') }}</strong></td>
                            <td>
                                <div class="master-jabatan-table-actions">
                                    <a href="{{ route('master.jabatan.show', $jabatan) }}"
                                        aria-label="Detail {{ $jabatan->nama_jabatan }}" title="Detail"><i
                                            class="fa-regular fa-eye"></i></a>
                                    <a href="{{ route('master.jabatan.edit', $jabatan) }}"
                                        aria-label="Edit {{ $jabatan->nama_jabatan }}" title="Edit"><i
                                            class="fa-solid fa-pen-to-square"></i></a>
                                    <form action="{{ route('master.jabatan.destroy', $jabatan) }}" method="POST"
                                        class="form-delete" data-confirm-message="Yakin ingin menghapus jabatan ini?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" aria-label="Hapus {{ $jabatan->nama_jabatan }}"
                                            title="Hapus"><i class="fa-regular fa-trash-can"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="master-jabatan-table-empty" colspan="6">Belum ada data jabatan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="master-jabatan-list">
            @forelse ($jabatans as $jabatan)
                <article class="master-jabatan-card">
                    <div class="master-jabatan-card-content">
                        <div class="master-jabatan-card-top">
                            <span class="master-jabatan-number">
                                {{ $loop->iteration + ($jabatans->currentPage() - 1) * $jabatans->perPage() }}
                            </span>
                            <span class="master-jabatan-grade grade-{{ $jabatan->grade }}">
                                Grade {{ $jabatan->grade }}
                            </span>
                        </div>

                        <h2>{{ $jabatan->nama_jabatan }}</h2>
                        <p class="master-jabatan-division">
                            <i class="fa-regular fa-building"></i>
                            {{ $jabatan->divisi->nama_divisi }}
                        </p>
                    </div>

                    <div class="master-jabatan-card-footer">
                        <div class="master-jabatan-tukin">
                            <small>BASE TUKIN</small>
                            <strong>Rp {{ number_format($jabatan->base_tukin, 0, ',', '.') }}</strong>
                        </div>

                        <div class="master-jabatan-actions">
                            <a href="{{ route('master.jabatan.show', $jabatan) }}" aria-label="Detail">
                                <i class="fa-regular fa-eye"></i>
                            </a>
                            <a href="{{ route('master.jabatan.edit', $jabatan) }}" aria-label="Edit">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>
                            <form action="{{ route('master.jabatan.destroy', $jabatan) }}" method="POST"
                                class="form-delete" data-confirm-message="Yakin ingin menghapus jabatan ini?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" aria-label="Hapus">
                                    <i class="fa-regular fa-trash-can"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </article>
            @empty
                <div class="master-jabatan-empty">
                    <i class="fa-regular fa-folder-open"></i>
                    <strong>Belum ada data jabatan.</strong>
                </div>
            @endforelse
        </div>

        <div class="master-jabatan-pagination">
            {{ $jabatans->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endsection

@push('styles')
    <style>
        body.role-master .master-jabatan-page {
            width: 100%;
            max-width: 560px;
            margin: auto;
            padding: 1rem 1rem 0;
            box-sizing: border-box;
            color: #14223d;
        }

        .master-jabatan-breadcrumb {
            display: flex;
            align-items: center;
            gap: .5rem;
            margin-bottom: .75rem;
            color: #0875ad;
            font-size: .72rem;
        }

        .master-jabatan-breadcrumb a {
            color: #0875ad;
            text-decoration: none;
        }

        .master-jabatan-breadcrumb i {
            color: #9caabd;
            font-size: .5rem;
        }

        .master-jabatan-heading {
            display: block;
            margin-bottom: 1.15rem;
        }

        .master-jabatan-heading h1 {
            margin: 0;
            font-size: 1rem;
        }

        .master-jabatan-heading p {
            margin: .3rem 0 .85rem;
            color: #71829c;
            font-size: .78rem;
        }

        .master-jabatan-heading>a {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            width: 100%;
            padding: .72rem 1rem;
            color: #fff;
            background: #087bad;
            border-radius: 13px;
            box-shadow: 0 4px 8px #087bad33;
            font-size: .78rem;
            font-weight: 700;
            text-decoration: none;
        }

        .master-jabatan-search {
            display: flex;
            align-items: center;
            gap: .65rem;
            margin-bottom: .8rem;
            padding: .72rem 1rem;
            background: #fff;
            border: 1px solid #dfe7f0;
            border-radius: 14px;
            box-shadow: 0 3px 8px #2334550a;
            color: #91a1b8;
        }

        .master-jabatan-search input {
            width: 100%;
            min-width: 0;
            border: 0;
            outline: 0;
            color: #263653;
            font: inherit;
            font-size: .75rem;
        }

        .master-jabatan-tabs {
            display: flex;
            gap: .5rem;
            overflow-x: auto;
            margin-bottom: 1.1rem;
            scrollbar-width: none;
        }

        .master-jabatan-tabs::-webkit-scrollbar {
            display: none;
        }

        .master-jabatan-tabs button {
            flex: 0 0 auto;
            padding: .48rem .78rem;
            color: #526480;
            background: #fff;
            border: 1px solid #dfe7f0;
            border-radius: 9px;
            font-size: .68rem;
            white-space: nowrap;
        }

        .master-jabatan-tabs button.active {
            color: #fff;
            background: #087bad;
            border-color: #087bad;
            font-weight: 600;
        }

        .master-jabatan-list {
            display: grid;
            gap: .85rem;
        }

        .master-jabatan-card {
            overflow: hidden;
            background: #fff;
            border: 1px solid #e1e9f2;
            border-radius: 19px;
            box-shadow: 0 5px 16px #2334550b;
        }

        .master-jabatan-card-content {
            padding: 1rem 1.1rem .8rem;
        }

        .master-jabatan-card-top {
            display: flex;
            align-items: center;
            gap: .65rem;
            margin-bottom: .35rem;
        }

        .master-jabatan-number {
            display: grid;
            place-items: center;
            width: 24px;
            height: 24px;
            color: #526480;
            background: #f0f4f8;
            border-radius: 50%;
            font-size: .7rem;
            font-weight: 600;
        }

        .master-jabatan-grade {
            padding: .2rem .6rem;
            color: #f59b00;
            border: 1px solid #ffc94f;
            border-radius: 10px;
            font-size: .65rem;
            line-height: 1;
        }

        .master-jabatan-grade.grade-1 {
            color: #2479e8;
            background: #eef6ff;
            border-color: #9cc5ff;
        }

        .master-jabatan-card h2 {
            margin: 0;
            font-size: .94rem;
            overflow-wrap: anywhere;
        }

        .master-jabatan-division {
            margin: .35rem 0 0;
            color: #71829c;
            font-size: .72rem;
        }

        .master-jabatan-division i {
            margin-right: .3rem;
            color: #91a1b8;
        }

        .master-jabatan-card-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .5rem;
            padding: .72rem 1.1rem .9rem;
            border-top: 1px solid #e8eef5;
        }

        .master-jabatan-tukin small {
            display: block;
            color: #91a1b8;
            font-size: .65rem;
            font-weight: 600;
        }

        .master-jabatan-tukin strong {
            display: block;
            margin-top: .25rem;
            color: #00a176;
            font-size: .82rem;
        }

        .master-jabatan-actions {
            display: flex;
            align-items: center;
            gap: .45rem;
        }

        .master-jabatan-actions a,
        .master-jabatan-actions button {
            display: grid;
            place-items: center;
            width: 37px;
            height: 37px;
            color: #526b8c;
            background: #f7f9fb;
            border: 0;
            border-radius: 10px;
            text-decoration: none;
            font-size: .88rem;
        }

        .master-jabatan-actions a:nth-child(2) {
            color: #008ed0;
            background: #eef9ff;
        }

        .master-jabatan-actions button {
            color: #f02f50;
            background: #fff0f3;
        }

        .master-jabatan-actions form {
            margin: 0;
        }

        .master-jabatan-empty {
            display: grid;
            place-items: center;
            gap: .45rem;
            padding: 2rem;
            color: #91a1b8;
        }

        .master-jabatan-empty i {
            font-size: 2rem;
        }

        .master-jabatan-pagination {
            margin-top: .9rem;
        }

        .master-jabatan-pagination .pagination {
            justify-content: center;
            flex-wrap: wrap;
        }

        .master-jabatan-pagination svg {
            width: 14px;
            height: 14px;
        }

        .master-jabatan-table-wrap {
            display: none;
        }

        @media (max-width: 380px) {
            body.role-master .master-jabatan-page {
                padding-inline: .75rem;
            }

            .master-jabatan-heading h1 {
                font-size: 1.25rem;
            }

            .master-jabatan-card-content {
                padding-inline: .9rem;
            }

            .master-jabatan-card-footer {
                padding-inline: .9rem;
            }
        }

        @media (min-width: 768px) {
            .master-jabatan-heading {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
                margin-bottom: 1rem;
            }

            .master-jabatan-heading h1 {
                color: #1b2938;
                font-size: 1.4rem;
                font-weight: 700;
            }

            .master-jabatan-heading p {
                margin: .25rem 0 0;
                font-size: .78rem;
            }

            .master-jabatan-heading>a {
                width: auto;
                padding: .65rem .85rem;
                background: #078dcc;
                border-radius: 6px;
                box-shadow: none;
                font-size: .72rem;
            }

            .master-jabatan-search {
                padding: .58rem .7rem;
                border-radius: 6px;
                box-shadow: none;
            }

            .master-jabatan-tabs {
                gap: .4rem;
                margin-bottom: .85rem;
            }

            .master-jabatan-tabs button {
                padding: .4rem .62rem;
                border-radius: 5px;
                font-size: .64rem;
            }

            .master-jabatan-tabs button.active {
                background: #078dcc;
                border-color: #078dcc;
            }

            .master-jabatan-table-wrap {
                display: block;
                width: 100%;
                overflow-x: auto;
                background: #fff;
                border: 1px solid #e2e8ef;
                border-radius: 6px;
            }

            .master-jabatan-table {
                width: 100%;
                min-width: 820px;
                border-collapse: separate;
                border-spacing: 0;
                color: #334155;
                font-size: .72rem;
            }

            .master-jabatan-table th {
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

            .master-jabatan-table td {
                padding: .63rem .75rem;
                border-bottom: 1px solid #edf1f5;
                vertical-align: middle;
            }

            .master-jabatan-table tbody tr:last-child td {
                border-bottom: 0;
            }

            .master-jabatan-table tbody tr:hover {
                background: #fafcfe;
            }

            .master-jabatan-table-number {
                display: grid;
                place-items: center;
                width: 23px;
                height: 23px;
                color: #526480;
                background: #f0f4f8;
                border-radius: 50%;
                font-size: .64rem;
            }

            .master-jabatan-table-name {
                display: block;
                min-width: 200px;
                color: #25364a;
                font-size: .72rem;
            }

            .master-jabatan-table-grade {
                display: inline-block;
                padding: .27rem .42rem;
                color: #a96800;
                background: #fff7e6;
                border: 1px solid #ffe2a8;
                border-radius: 999px;
                font-size: .62rem;
                white-space: nowrap;
            }

            .master-jabatan-table-grade.grade-1 {
                color: #2479e8;
                background: #eef6ff;
                border-color: #c9e0ff;
            }

            .master-jabatan-table-division {
                display: inline-flex;
                align-items: center;
                gap: .35rem;
                min-width: 150px;
                color: #617185;
                font-size: .67rem;
            }

            .master-jabatan-table-division i {
                color: #8290a0;
            }

            .master-jabatan-table-tukin {
                color: #00966f;
                font-size: .72rem;
                font-weight: 700;
                white-space: nowrap;
            }

            .master-jabatan-table-actions-heading {
                text-align: right !important;
            }

            .master-jabatan-table-actions {
                display: flex;
                align-items: center;
                justify-content: flex-end;
                gap: .2rem;
                white-space: nowrap;
            }

            .master-jabatan-table-actions a,
            .master-jabatan-table-actions button {
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

            .master-jabatan-table-actions a:hover {
                color: #1769d2;
                background: #edf4ff;
            }

            .master-jabatan-table-actions button {
                color: #d33755;
            }

            .master-jabatan-table-actions button:hover {
                background: #fff0f3;
            }

            .master-jabatan-table-actions form {
                margin: 0;
            }

            .master-jabatan-table-empty {
                height: 90px;
                color: #718092;
                text-align: center;
            }

            .master-jabatan-list {
                display: none !important;
            }

            .master-jabatan-pagination .pagination {
                justify-content: flex-end;
            }
        }
    </style>
@endpush
