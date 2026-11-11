@extends('layouts.app')

@section('title', 'Template Laporan')

@section('content')
    @php
        $templateSections = $templateLaporans
            ->getCollection()
            ->map(fn($template) => $template->divisi->nama_divisi ?? null)
            ->filter()
            ->unique();
    @endphp
    <div class="master-template-page">
        <header class="master-template-heading">
            <div>
                <h1>
                    <i class="fas fa-file-lines"></i>
                    Template Laporan
                </h1>
                <p>Kelola konfigurasi template pelaporan harian kinerja divisi</p>
            </div>
            <a href="{{ route('master.template-laporan.create') }}" class="master-template-add">
                <i class="fa-solid fa-plus"></i> Tambah Template
            </a>
        </header>

        <section class="master-template-tools">
            <div class="master-template-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="search" placeholder="Cari field, divisi, atau KPI...">
            </div>
            <div class="master-template-tools-footer">
                <div class="master-template-section-filters" aria-label="Filter seksi">
                    <button type="button" class="active">Semua Seksi</button>
                    @foreach ($templateSections as $section)
                        <button type="button">{{ $section }}</button>
                    @endforeach
                </div>
                <span class="master-template-total">Total: <strong>{{ $templateLaporans->total() }} Template</strong></span>
            </div>
        </section>

        <div class="master-template-table-wrap">
            <table class="master-template-table">
                <thead>
                    <tr>
                        <th scope="col">No</th>
                        <th scope="col">Jabatan / Divisi</th>
                        <th scope="col">Nama Field / Template</th>
                        <th scope="col">Tipe</th>
                        <th scope="col">Satuan</th>
                        <th scope="col">Terhubung KPI</th>
                        <th scope="col" class="master-template-actions-heading">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($templateLaporans as $template)
                        <tr>
                            <td><span
                                    class="master-template-table-number">{{ $loop->iteration + ($templateLaporans->currentPage() - 1) * $templateLaporans->perPage() }}</span>
                            </td>
                            <td><span class="master-template-table-section">
                                    {{ $template->jabatan?->nama_jabatan ?? 'Semua jabatan' }} /
                                    {{ $template->divisi->nama_divisi ?? '-' }}
                                </span>
                            </td>
                            <td><strong class="master-template-table-name">{{ $template->nama_field }}</strong></td>
                            <td><span class="master-template-table-type">{{ ucfirst($template->tipe_field) }}</span></td>
                            <td>{{ $template->satuan ?: '-' }}</td>
                            <td>
                                @if ($template->masterKpi)
                                    <span class="master-template-table-kpi">{{ $template->masterKpi->indikator }}</span>
                                @else
                                    <span class="master-template-table-muted">- (Tidak Ada)</span>
                                @endif
                            </td>
                            <td>
                                <div class="master-template-table-actions">
                                    <a href="{{ route('master.template-laporan.edit', $template) }}"
                                        aria-label="Edit {{ $template->nama_field }}" title="Edit"><i
                                            class="fa-regular fa-pen-to-square"></i></a>
                                    <form action="{{ route('master.template-laporan.destroy', $template) }}" method="POST"
                                        class="form-delete" data-confirm-message="Yakin ingin menghapus template ini?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" aria-label="Hapus {{ $template->nama_field }}"
                                            title="Hapus"><i class="fa-regular fa-trash-can"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="master-template-table-empty" colspan="7">Belum ada template laporan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="master-template-list">
            @forelse ($templateLaporans as $template)
                <article class="master-template-card">
                    <div class="master-template-card-top">
                        <span
                            class="master-template-number">{{ $loop->iteration + ($templateLaporans->currentPage() - 1) * $templateLaporans->perPage() }}</span>
                        <span class="master-template-section">{{ $template->jabatan?->nama_jabatan ?? 'Semua jabatan' }}</span>
                        <span class="master-template-type">{{ ucfirst($template->tipe_field) }}</span>
                    </div>
                    <h2>{{ $template->nama_field }}</h2>
                    <div class="master-template-meta">
                        <div><label>SATUAN</label><strong>{{ $template->satuan ?: '-' }}</strong></div>
                        <div><label>TERHUBUNG KPI</label><strong
                                class="{{ $template->masterKpi ? 'linked' : 'muted' }}">{{ $template->masterKpi->indikator ?? '- (Tidak Ada)' }}</strong>
                        </div>
                    </div>
                    <div class="master-template-actions">
                        <a href="{{ route('master.template-laporan.edit', $template) }}" class="edit"><i
                                class="fa-regular fa-pen-to-square"></i> Edit</a>
                        <form action="{{ route('master.template-laporan.destroy', $template) }}" method="POST"
                            class="form-delete" data-confirm-message="Yakin ingin menghapus template ini?">
                            @csrf
                            @method('DELETE')
                            <button type="submit"><i class="fa-regular fa-trash-can"></i> Hapus</button>
                        </form>
                    </div>
                </article>
            @empty
                <div class="master-template-empty"><i class="fa-regular fa-folder-open"></i><strong>Belum ada template
                        laporan.</strong><span>Tambahkan template untuk mulai mengatur pelaporan harian.</span></div>
            @endforelse
        </div>

        <div class="master-template-footer">
            Menampilkan {{ $templateLaporans->firstItem() ?? 0 }}–{{ $templateLaporans->lastItem() ?? 0 }} dari
            {{ $templateLaporans->total() }} template laporan
        </div>
        <div class="master-template-pagination">{{ $templateLaporans->links('pagination::bootstrap-5') }}</div>
    </div>
@endsection

@push('styles')
    <style>
        body.role-master .master-template-page {
            width: 100%;
            max-width: 560px;
            margin: auto;
            padding: 1rem 1rem 0;
            box-sizing: border-box;
            color: #14223d
        }

        .master-template-breadcrumb {
            display: flex;
            align-items: center;
            gap: .5rem;
            margin-bottom: 1rem;
            font-size: .7rem
        }

        .master-template-breadcrumb a {
            color: #0875ad;
            text-decoration: none
        }

        .master-template-breadcrumb i {
            font-size: .48rem;
            color: #9caabd
        }

        .master-template-heading {
            margin-bottom: .9rem
        }

        .master-template-heading h1 {
            margin: 0;
            font-size: 1rem
        }

        .master-template-heading p {
            margin: .3rem 0;
            color: #526b8c;
            font-size: .76rem
        }

        .master-template-add {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: .55rem;
            padding: .8rem;
            margin-bottom: 1.1rem;
            color: #fff;
            background: #078dcc;
            border-radius: 14px;
            box-shadow: 0 4px 8px #078dcc33;
            text-decoration: none;
            font-size: .8rem;
            font-weight: 700
        }

        .master-template-tools {
            padding: .8rem .75rem .7rem;
            margin-bottom: 1.1rem;
            background: #fff;
            border: 1px solid #e0e8f1;
            border-radius: 15px;
            box-shadow: 0 3px 10px #2334550a
        }

        .master-template-search {
            display: flex;
            align-items: center;
            gap: .55rem;
            padding: .65rem .7rem;
            color: #91a1b8;
            background: #f8fafc;
            border: 1px solid #dce5ee;
            border-radius: 10px
        }

        .master-template-search input {
            width: 100%;
            border: 0;
            outline: 0;
            background: transparent;
            font: inherit;
            font-size: .72rem
        }

        .master-template-tools-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: .65rem;
            margin-top: .65rem;
            border-top: 1px solid #edf2f7;
            color: #526b8c;
            font-size: .7rem
        }

        .master-template-tools-footer b {
            padding: .4rem .6rem;
            color: #078dcc;
            background: #eff9ff;
            border-radius: 999px;
            font-size: .65rem;
            font-weight: 500
        }

        .master-template-tools-footer b i {
            font-size: .4rem
        }

        .master-template-list {
            display: grid;
            gap: .85rem
        }

        .master-template-card {
            padding: 1rem;
            background: #fff;
            border: 1px solid #e0e8f1;
            border-radius: 19px;
            box-shadow: 0 4px 12px #2334550b
        }

        .master-template-card-top {
            display: flex;
            align-items: center;
            gap: .55rem
        }

        .master-template-number {
            display: grid;
            place-items: center;
            width: 30px;
            height: 30px;
            color: #0875ad;
            background: #eff9ff;
            border: 1px solid #bce7ff;
            border-radius: 9px;
            font-size: .75rem
        }

        .master-template-section {
            padding: .35rem .55rem;
            color: #526480;
            background: #f0f5f9;
            border-radius: 6px;
            font-size: .64rem
        }

        .master-template-type {
            margin-left: auto;
            padding: .4rem .55rem;
            color: #526480;
            background: #f0f5f9;
            border: 1px solid #dce5ee;
            border-radius: 7px;
            font-size: .68rem
        }

        .master-template-card h2 {
            margin: 1rem 0 .9rem;
            text-align: center;
            font-size: .95rem
        }

        .master-template-meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .8rem;
            padding: .8rem;
            background: #f8fafc;
            border-radius: 13px
        }

        .master-template-meta label {
            display: block;
            color: #91a1b8;
            font-size: .61rem;
            font-weight: 600
        }

        .master-template-meta strong {
            display: block;
            margin-top: .35rem;
            color: #14223d;
            font-size: .76rem;
            font-weight: 500;
            overflow-wrap: anywhere
        }

        .master-template-meta strong.linked {
            padding: .3rem .4rem;
            color: #078dcc;
            background: #eff9ff;
            border: 1px solid #d6ebf7;
            border-radius: 5px;
            font-size: .65rem
        }

        .master-template-meta strong.muted {
            color: #91a1b8;
            font-style: italic
        }

        .master-template-actions {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: .5rem;
            margin-top: .8rem
        }

        .master-template-actions a,
        .master-template-actions button {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: .3rem;
            padding: .55rem .35rem;
            color: #526480;
            background: #f3f6f9;
            border: 0;
            border-radius: 10px;
            text-decoration: none;
            font: inherit;
            font-size: .68rem;
            white-space: nowrap
        }

        .master-template-actions .edit {
            color: #078dcc;
            background: #eff9ff;
            border: 1px solid #bce7ff
        }

        .master-template-actions button {
            color: #f03d5c;
            background: #fff0f2
        }

        .master-template-actions form {
            margin: 0
        }

        .master-template-empty {
            display: grid;
            place-items: center;
            gap: .45rem;
            padding: 2.2rem 1rem;
            color: #91a1b8;
            text-align: center;
            background: #fff;
            border: 1px solid #e0e8f1;
            border-radius: 19px
        }

        .master-template-empty i {
            font-size: 2rem
        }

        .master-template-empty strong {
            color: #526480;
            font-size: .82rem
        }

        .master-template-empty span {
            font-size: .7rem
        }

        .master-template-footer {
            margin: 1.1rem .35rem .4rem;
            color: #71829c;
            font-size: .72rem
        }

        .master-template-pagination {
            margin-top: .3rem
        }

        .master-template-pagination .pagination {
            justify-content: center;
            flex-wrap: wrap
        }

        .master-template-pagination svg {
            width: 14px;
            height: 14px
        }

        .master-template-table-wrap {
            display: none;
        }

        @media(max-width:380px) {
            body.role-master .master-template-page {
                padding-inline: .75rem
            }

            .master-template-heading h1 {
                font-size: 1.25rem
            }

            .master-template-heading p {
                font-size: .7rem
            }

            .master-template-card {
                padding: .8rem
            }

            .master-template-actions {
                gap: .3rem
            }

            .master-template-actions a,
            .master-template-actions button {
                font-size: .62rem
            }
        }

        @media (min-width: 768px) {
            .master-template-heading {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
                margin-bottom: 1rem;
            }

            .master-template-heading h1 {
                color: #1b2938;
                font-size: 1.4rem;
                font-weight: 700;
            }

            .master-template-heading p {
                margin-top: .25rem;
                color: #718092;
                font-size: .78rem;
            }

            .master-template-add {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                flex: 0 0 auto;
                gap: .45rem;
                padding: .65rem .85rem;
                margin: 0;
                color: #fff;
                background: #078dcc;
                border-radius: 6px;
                box-shadow: none;
                font-size: .72rem;
                text-decoration: none;
            }

            .master-template-tools {
                display: flex;
                align-items: center;
                gap: 1rem;
                padding: .65rem .75rem;
                margin-bottom: 0;
                border-radius: 6px 6px 0 0;
                box-shadow: none;
            }

            .master-template-search {
                flex: 0 1 300px;
                min-width: 220px;
                padding: .55rem .65rem;
                border-radius: 5px;
            }

            .master-template-tools-footer {
                flex: 1;
                justify-content: space-between;
                gap: .75rem;
                min-width: 0;
                padding: 0;
                margin: 0;
                border: 0;
                font-size: .68rem;
            }

            .master-template-section-filters {
                display: flex;
                align-items: center;
                gap: .4rem;
                min-width: 0;
                overflow-x: auto;
            }

            .master-template-section-filters button {
                flex: 0 0 auto;
                padding: .38rem .55rem;
                color: #526480;
                background: #fff;
                border: 1px solid #e2e8ef;
                border-radius: 999px;
                font: inherit;
                font-size: .64rem;
                white-space: nowrap;
            }

            .master-template-section-filters button.active {
                color: #fff;
                background: #078dcc;
                border-color: #078dcc;
            }

            .master-template-total {
                flex: 0 0 auto;
                white-space: nowrap;
            }

            .master-template-table-wrap {
                display: block;
                width: 100%;
                overflow-x: auto;
                background: #fff;
                border: 1px solid #e2e8ef;
                border-top: 0;
                border-radius: 0 0 6px 6px;
            }

            .master-template-table {
                width: 100%;
                min-width: 980px;
                border-collapse: separate;
                border-spacing: 0;
                color: #334155;
                font-size: .72rem;
            }

            .master-template-table th {
                padding: .7rem .75rem;
                color: #617185;
                background: #f7f9fb;
                border-bottom: 1px solid #e2e8ef;
                font-size: .62rem;
                font-weight: 700;
                text-align: left;
                text-transform: uppercase;
                white-space: nowrap;
            }

            .master-template-table td {
                padding: .65rem .75rem;
                border-bottom: 1px solid #edf1f5;
                vertical-align: middle;
            }

            .master-template-table tbody tr:last-child td {
                border-bottom: 0;
            }

            .master-template-table tbody tr:hover {
                background: #fafcfe;
            }

            .master-template-table-number {
                display: grid;
                place-items: center;
                width: 24px;
                height: 24px;
                color: #526480;
                background: #f0f4f8;
                border-radius: 50%;
                font-size: .65rem;
            }

            .master-template-table-section {
                display: inline-block;
                max-width: 150px;
                padding: .32rem .45rem;
                color: #526480;
                background: #f0f5f9;
                border-radius: 4px;
                font-size: .65rem;
                line-height: 1.35;
            }

            .master-template-table-name {
                display: block;
                min-width: 175px;
                max-width: 290px;
                color: #25364a;
                font-size: .72rem;
                line-height: 1.4;
            }

            .master-template-table-type {
                display: inline-block;
                padding: .25rem .45rem;
                color: #526480;
                background: #f0f4f8;
                border-radius: 999px;
                font-size: .64rem;
            }

            .master-template-table-kpi {
                display: block;
                min-width: 190px;
                max-width: 310px;
                padding: .45rem .55rem;
                color: #2870aa;
                background: #f3f9fd;
                border: 1px solid #e6f0f7;
                border-radius: 5px;
                font-size: .65rem;
                line-height: 1.4;
            }

            .master-template-table-muted {
                color: #8a98a8;
                font-size: .66rem;
                font-style: italic;
            }

            .master-template-actions-heading {
                text-align: center !important;
            }

            .master-template-table-actions {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: .25rem;
                white-space: nowrap;
            }

            .master-template-table-actions a,
            .master-template-table-actions button {
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

            .master-template-table-actions a:hover {
                color: #1769d2;
                background: #edf4ff;
            }

            .master-template-table-actions button {
                color: #d33755;
            }

            .master-template-table-actions button:hover {
                background: #fff0f3;
            }

            .master-template-table-actions form {
                margin: 0;
            }

            .master-template-table-empty {
                height: 100px;
                color: #718092;
                text-align: center;
            }

            .master-template-list {
                display: none !important;
            }

            .master-template-footer {
                display: inline-block;
                margin: .75rem 0;
                font-size: .68rem;
            }

            .master-template-pagination {
                float: right;
                margin: .5rem 0;
            }

            .master-template-pagination .pagination {
                justify-content: flex-end;
            }
        }

        @media (min-width: 768px) and (max-width: 1050px) {
            .master-template-tools {
                align-items: stretch;
                flex-direction: column;
            }

            .master-template-search {
                flex-basis: auto;
                width: 100%;
            }

            .master-template-tools-footer {
                min-height: 32px;
            }
        }
    </style>
@endpush
