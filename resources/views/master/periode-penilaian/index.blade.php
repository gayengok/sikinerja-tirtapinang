@extends('layouts.app')

@section('title', 'Periode Penilaian')

@section('content')
    @php
        $periodeAktif = $periodePenilaians->getCollection()->firstWhere('status', 'berjalan');
    @endphp

    <div class="master-periode-page">
        <header class="master-periode-heading">
            <div>
                <h1>
                    <i class="fa-solid fa-calendar-check"></i>
                    Periode Penilaian
                </h1>
                <p>Kelola periode penilaian kinerja pegawai</p>
            </div>
            <a href="{{ route('master.periode-penilaian.create') }}" class="master-periode-add-desktop">
                <i class="fa-solid fa-plus"></i> Tambah Periode
            </a>
        </header>

        <a href="{{ route('master.periode-penilaian.create') }}" class="master-periode-add">
            <i class="fa-solid fa-plus"></i> Tambah Periode
        </a>

        <section class="master-periode-summary" aria-label="Ringkasan periode penilaian">
            <article><span class="master-periode-summary-icon blue"><i class="fa-regular fa-calendar"></i></span>
                <div><small>Total Periode</small><strong>{{ $statusCounts['semua'] }}</strong><span>Periode</span></div>
            </article>
            <article><span class="master-periode-summary-icon orange"><i class="fa-solid fa-arrows-rotate"></i></span>
                <div><small>Periode Berjalan</small><strong>{{ $statusCounts['berjalan'] }}</strong><span>Aktif</span></div>
            </article>
            <article><span class="master-periode-summary-icon green"><i class="fa-solid fa-circle-check"></i></span>
                <div><small>Selesai</small><strong>{{ $statusCounts['selesai'] }}</strong><span>Selesai</span></div>
            </article>
            <article><span class="master-periode-summary-icon gray"><i class="fa-regular fa-file-lines"></i></span>
                <div><small>Draft</small><strong>{{ $statusCounts['draft'] }}</strong><span>Draft</span></div>
            </article>
        </section>

        @if ($periodeAktif)
            <section class="master-periode-active">
                <div class="master-periode-active-top">
                    <span>PERIODE SEDANG AKTIF</span>
                    <b><i class="fa-solid fa-circle"></i> Berjalan</b>
                </div>
                <h2>{{ $periodeAktif->nama_periode }}</h2>
                <div class="master-periode-active-footer">
                    <span><i class="fa-regular fa-calendar"></i>
                        {{ \Carbon\Carbon::parse($periodeAktif->tanggal_mulai)->translatedFormat('d M Y') }} -
                        {{ \Carbon\Carbon::parse($periodeAktif->tanggal_selesai)->translatedFormat('d M Y') }}</span>
                    <strong>Aktif</strong>
                </div>
            </section>
        @endif

        <div class="master-periode-search">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="search" placeholder="Cari nama periode atau bulan...">
        </div>

        <div class="master-periode-tabs">
            <span class="active">Semua ({{ $statusCounts['semua'] }})</span>
            <span>Berjalan ({{ $statusCounts['berjalan'] }})</span>
            <span>Selesai ({{ $statusCounts['selesai'] }})</span>
            <span>Draft ({{ $statusCounts['draft'] }})</span>
            <label class="master-periode-status-filter">Filter Arsip:
                <select aria-label="Filter status arsip">
                    <option>Semua Status</option>
                    <option>Aktif</option>
                    <option>Arsip</option>
                </select>
            </label>
        </div>

        <div class="master-periode-table-wrap">
            <table class="master-periode-table">
                <thead>
                    <tr>
                        <th scope="col">No</th>
                        <th scope="col">Nama Periode</th>
                        <th scope="col">Tanggal Mulai</th>
                        <th scope="col">Tanggal Selesai</th>
                        <th scope="col">Durasi</th>
                        <th scope="col">Status</th>
                        <th scope="col">Status Arsip</th>
                        <th scope="col" class="master-periode-table-actions-heading">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($periodePenilaians as $periode)
                        @php
                            $isActive = $periode->status === 'berjalan';
                            $isFinished = in_array($periode->status, ['final', 'selesai'], true);
                            $statusLabel = $isFinished ? 'Selesai' : ucfirst($periode->status);
                            $durationDays =
                                \Carbon\Carbon::parse($periode->tanggal_mulai)->diffInDays(
                                    \Carbon\Carbon::parse($periode->tanggal_selesai),
                                ) + 1;
                        @endphp
                        <tr>
                            <td><span
                                    class="master-periode-table-number">#{{ $loop->iteration + ($periodePenilaians->currentPage() - 1) * $periodePenilaians->perPage() }}</span>
                            </td>
                            <td><strong class="master-periode-table-name">{{ $periode->nama_periode }}</strong></td>
                            <td><span class="master-periode-table-date start"><i class="fa-regular fa-calendar"></i>
                                    {{ \Carbon\Carbon::parse($periode->tanggal_mulai)->translatedFormat('d M Y') }}</span>
                            </td>
                            <td><span class="master-periode-table-date end"><i class="fa-regular fa-calendar"></i>
                                    {{ \Carbon\Carbon::parse($periode->tanggal_selesai)->translatedFormat('d M Y') }}</span>
                            </td>
                            <td><span class="master-periode-table-duration">{{ $durationDays }} Hari</span></td>
                            <td><span
                                    class="master-periode-table-status {{ $isActive ? 'is-running' : ($isFinished ? 'is-finished' : 'is-draft') }}"><i
                                        class="fa-solid fa-circle"></i> {{ $statusLabel }}</span></td>
                            <td><span
                                    class="master-periode-table-archive {{ $isFinished ? 'is-archived' : '' }}">{{ $isFinished ? 'Arsip' : ($isActive ? 'Aktif' : 'Draft') }}</span>
                            </td>
                            <td>
                                <div class="master-periode-table-actions">
                                    <a href="{{ route('master.periode-penilaian.show', $periode) }}"
                                        aria-label="Detail {{ $periode->nama_periode }}" title="Detail"><i
                                            class="fa-regular fa-eye"></i></a>
                                    <a href="{{ route('master.periode-penilaian.edit', $periode) }}"
                                        aria-label="Edit {{ $periode->nama_periode }}" title="Edit"><i
                                            class="fa-regular fa-pen-to-square"></i></a>
                                    <form action="{{ route('master.periode-penilaian.destroy', $periode) }}" method="POST"
                                        class="form-delete" data-confirm-message="Yakin ingin menghapus periode ini?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" aria-label="Hapus {{ $periode->nama_periode }}"
                                            title="Hapus"><i class="fa-regular fa-trash-can"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="master-periode-table-empty" colspan="8">Belum ada data periode penilaian.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="master-periode-list">
            @forelse ($periodePenilaians as $periode)
                @php
                    $isActive = $periode->status === 'berjalan';
                    $statusLabel = $periode->status === 'final' ? 'Selesai' : ucfirst($periode->status);
                @endphp
                <article class="master-periode-card">
                    <div class="master-periode-card-top">
                        <span
                            class="master-periode-number">#{{ $loop->iteration + ($periodePenilaians->currentPage() - 1) * $periodePenilaians->perPage() }}</span>
                        <span class="master-periode-status {{ $isActive ? 'running' : 'finished' }}"><i
                                class="fa-solid fa-circle"></i> {{ $statusLabel }}</span>
                        <span class="master-periode-kind">{{ $isActive ? 'Bulan Aktif' : 'Arsip' }}</span>
                    </div>
                    <h2>{{ $periode->nama_periode }}</h2>
                    <div class="master-periode-dates">
                        <div><label><i class="fa-regular fa-calendar"></i> TANGGAL
                                MULAI</label><strong>{{ \Carbon\Carbon::parse($periode->tanggal_mulai)->translatedFormat('d M Y') }}</strong>
                        </div>
                        <div><label><i class="fa-regular fa-calendar"></i> TANGGAL
                                SELESAI</label><strong>{{ \Carbon\Carbon::parse($periode->tanggal_selesai)->translatedFormat('d M Y') }}</strong>
                        </div>
                    </div>
                    <div class="master-periode-actions">
                        <a href="{{ route('master.periode-penilaian.show', $periode) }}"><i class="fa-regular fa-eye"></i>
                            Detail</a>
                        <a href="{{ route('master.periode-penilaian.edit', $periode) }}" class="edit"><i
                                class="fa-regular fa-pen-to-square"></i> Edit</a>
                        <form action="{{ route('master.periode-penilaian.destroy', $periode) }}" method="POST"
                            class="form-delete" data-confirm-message="Yakin ingin menghapus periode ini?">
                            @csrf
                            @method('DELETE')
                            <button type="submit"><i class="fa-regular fa-trash-can"></i> Hapus</button>
                        </form>
                    </div>
                </article>
            @empty
                <div class="master-periode-empty"><i class="fa-regular fa-calendar-xmark"></i><strong>Belum ada data periode
                        penilaian.</strong><span>Tambahkan periode untuk memulai pengelolaan penilaian.</span></div>
            @endforelse
        </div>

        <div class="master-periode-pagination">{{ $periodePenilaians->links('pagination::bootstrap-5') }}</div>
        <aside class="master-periode-note">
            <i class="fa-solid fa-circle-info"></i>
            <p><strong>Ketentuan Periode Penilaian Kinerja</strong> Periode penilaian menentukan rentang waktu pengisian
                capaian KPI harian, evaluasi kompetensi perilaku, dan kalkulasi potongan presensi pegawai secara otomatis.
            </p>
        </aside>
    </div>
@endsection

@push('styles')
    <style>
        body.role-master .master-periode-page {
            width: 100%;
            max-width: 560px;
            margin: auto;
            padding: 1rem 1rem 0;
            box-sizing: border-box;
            color: #14223d
        }

        .master-periode-breadcrumb {
            display: flex;
            align-items: center;
            gap: .5rem;
            margin-bottom: 1rem;
            color: #14223d;
            font-size: .7rem
        }

        .master-periode-breadcrumb a {
            color: #0875ad;
            text-decoration: none
        }

        .master-periode-breadcrumb i {
            font-size: .48rem;
            color: #9caabd
        }

        .master-periode-heading {
            margin-bottom: .9rem
        }

        .master-periode-heading h1 {
            margin: 0;
            font-size: 1rem
        }

        .master-periode-heading p {
            margin: .3rem 0;
            color: #526b8c;
            font-size: .76rem
        }

        .master-periode-add {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .55rem;
            padding: .8rem;
            margin-bottom: 1.2rem;
            color: #fff;
            background: #078dcc;
            border-radius: 14px;
            box-shadow: 0 4px 8px #078dcc33;
            text-decoration: none;
            font-size: .8rem;
            font-weight: 700
        }

        .master-periode-active {
            padding: 1rem 1.1rem .8rem;
            margin-bottom: 1.1rem;
            color: #fff;
            background: #0875ad;
            border-radius: 20px;
            box-shadow: 0 5px 12px #23345525
        }

        .master-periode-active-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #d6f1ff;
            font-size: .7rem
        }

        .master-periode-active-top b {
            padding: .4rem .65rem;
            color: #54e0ac;
            background: #0b5b5a;
            border: 1px solid #168c7c;
            border-radius: 999px;
            font-size: .67rem;
            font-weight: 500
        }

        .master-periode-active-top b i {
            font-size: .4rem
        }

        .master-periode-active h2 {
            margin: 1rem 0 1rem;
            font-size: 1.05rem
        }

        .master-periode-active-footer {
            display: flex;
            justify-content: space-between;
            gap: .5rem;
            padding-top: .75rem;
            border-top: 1px solid #ffffff2e;
            color: #d8e7f2;
            font-size: .7rem
        }

        .master-periode-active-footer i {
            margin-right: .3rem
        }

        .master-periode-active-footer strong {
            padding: .35rem .55rem;
            color: #d6f1ff;
            background: #ffffff18;
            border-radius: 8px;
            font-weight: 500
        }

        .master-periode-search {
            display: flex;
            align-items: center;
            gap: .6rem;
            padding: .75rem .85rem;
            margin-bottom: .8rem;
            color: #91a1b8;
            background: #fff;
            border: 1px solid #dce5ee;
            border-radius: 13px;
            box-shadow: 0 3px 8px #2334550a
        }

        .master-periode-search input {
            width: 100%;
            border: 0;
            outline: 0;
            font: inherit;
            font-size: .72rem
        }

        .master-periode-tabs {
            display: flex;
            gap: .45rem;
            overflow: auto;
            margin-bottom: 1.1rem
        }

        .master-periode-tabs span {
            flex: 0 0 auto;
            padding: .6rem .75rem;
            color: #526480;
            background: #fff;
            border: 1px solid #dce5ee;
            border-radius: 10px;
            font-size: .68rem;
            white-space: nowrap
        }

        .master-periode-tabs .active {
            color: #fff;
            background: #078dcc;
            border-color: #078dcc
        }

        .master-periode-list {
            display: grid;
            gap: .85rem
        }

        .master-periode-card {
            padding: 1rem;
            background: #fff;
            border: 1px solid #e0e8f1;
            border-radius: 19px;
            box-shadow: 0 4px 12px #2334550b
        }

        .master-periode-card-top {
            display: flex;
            align-items: center;
            gap: .55rem;
            padding-bottom: .75rem;
            border-bottom: 1px solid #edf2f7
        }

        .master-periode-number {
            padding: .4rem .55rem;
            color: #91a1b8;
            background: #f0f5f9;
            border-radius: 7px;
            font-size: .72rem;
            font-weight: 700
        }

        .master-periode-status {
            padding: .4rem .6rem;
            color: #607492;
            background: #f0f5f9;
            border-radius: 999px;
            font-size: .66rem
        }

        .master-periode-status i {
            font-size: .4rem;
            color: #91a1b8
        }

        .master-periode-status.running {
            color: #07986b;
            background: #effff8;
            border: 1px solid #a6edce
        }

        .master-periode-status.running i {
            color: #16c990
        }

        .master-periode-kind {
            margin-left: auto;
            color: #526480;
            font-size: .68rem
        }

        .master-periode-card h2 {
            margin: 1rem 0;
            font-size: 1rem
        }

        .master-periode-dates {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .6rem;
            padding: .8rem;
            background: #f8fafc;
            border-radius: 13px
        }

        .master-periode-dates label {
            display: block;
            color: #526480;
            font-size: .61rem;
            font-weight: 600
        }

        .master-periode-dates label i {
            margin-right: .25rem;
            color: #078dcc
        }

        .master-periode-dates div:last-child label i {
            color: #f03d5c
        }

        .master-periode-dates strong {
            display: block;
            margin-top: .4rem;
            font-size: .78rem
        }

        .master-periode-actions {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: .5rem;
            margin-top: .8rem
        }

        .master-periode-actions a,
        .master-periode-actions button {
            display: flex;
            align-items: center;
            justify-content: center;
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

        .master-periode-actions .edit {
            color: #078dcc;
            background: #eff9ff;
            border: 1px solid #bce7ff
        }

        .master-periode-actions button {
            color: #f03d5c;
            background: #fff0f2
        }

        .master-periode-actions form {
            margin: 0
        }

        .master-periode-empty {
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

        .master-periode-empty i {
            font-size: 2rem
        }

        .master-periode-empty strong {
            color: #526480;
            font-size: .82rem
        }

        .master-periode-empty span {
            font-size: .7rem
        }

        .master-periode-pagination {
            margin-top: .9rem
        }

        .master-periode-pagination .pagination {
            justify-content: center;
            flex-wrap: wrap
        }

        .master-periode-pagination svg {
            width: 14px;
            height: 14px
        }

        .master-periode-add-desktop,
        .master-periode-summary,
        .master-periode-table-wrap,
        .master-periode-status-filter,
        .master-periode-note {
            display: none;
        }

        @media(max-width:380px) {
            body.role-master .master-periode-page {
                padding-inline: .75rem
            }

            .master-periode-heading h1 {
                font-size: 1.25rem
            }

            .master-periode-heading p {
                font-size: .7rem
            }

            .master-periode-card {
                padding: .8rem
            }

            .master-periode-actions {
                gap: .3rem
            }

            .master-periode-actions a,
            .master-periode-actions button {
                font-size: .62rem
            }

            .master-periode-dates {
                gap: .4rem;
                padding: .65rem
            }

            .master-periode-dates strong {
                font-size: .7rem
            }
        }

        @media (min-width: 768px) {
            .master-periode-heading {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
                margin-bottom: .85rem;
            }

            .master-periode-heading h1 {
                color: #1b2938;
                font-size: 1.4rem;
                font-weight: 700;
            }

            .master-periode-heading p {
                margin-top: .25rem;
                font-size: .76rem;
            }

            .master-periode-heading>.master-periode-add-desktop {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: .45rem;
                padding: .65rem .85rem;
                color: #fff;
                background: #078dcc;
                border-radius: 6px;
                text-decoration: none;
                font-size: .7rem;
                font-weight: 700;
                white-space: nowrap;
            }

            .master-periode-page>.master-periode-add {
                display: none;
            }

            .master-periode-summary {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: .7rem;
                margin-bottom: .8rem;
            }

            .master-periode-summary article {
                display: flex;
                align-items: center;
                gap: .65rem;
                min-height: 65px;
                padding: .7rem .8rem;
                background: #fff;
                border: 1px solid #e2e8ef;
                border-radius: 6px;
            }

            .master-periode-summary-icon {
                display: grid;
                place-items: center;
                width: 34px;
                height: 34px;
                flex: 0 0 34px;
                border-radius: 5px;
                font-size: .78rem;
            }

            .master-periode-summary-icon.blue {
                color: #1769d2;
                background: #eaf2ff;
            }

            .master-periode-summary-icon.orange {
                color: #b77500;
                background: #fff5df;
            }

            .master-periode-summary-icon.green {
                color: #087c50;
                background: #e6f7ef;
            }

            .master-periode-summary-icon.gray {
                color: #64748b;
                background: #f0f3f6;
            }

            .master-periode-summary small,
            .master-periode-summary strong,
            .master-periode-summary article>div>span {
                display: block;
            }

            .master-periode-summary small {
                color: #718092;
                font-size: .61rem;
            }

            .master-periode-summary strong {
                display: inline-block;
                margin: .1rem .25rem 0 0;
                color: #1b2938;
                font-size: 1.05rem;
            }

            .master-periode-summary article>div>span {
                display: inline-block;
                color: #8290a0;
                font-size: .59rem;
            }

            .master-periode-active {
                display: none;
            }

            .master-periode-search {
                padding: .55rem .7rem;
                margin-bottom: .55rem;
                border-radius: 6px;
                box-shadow: none;
            }

            .master-periode-tabs {
                align-items: center;
                gap: .35rem;
                margin-bottom: .65rem;
            }

            .master-periode-tabs span {
                padding: .38rem .55rem;
                border-radius: 5px;
                font-size: .62rem;
            }

            .master-periode-tabs .active {
                background: #078dcc;
                border-color: #078dcc;
            }

            .master-periode-status-filter {
                display: flex;
                align-items: center;
                gap: .35rem;
                margin-left: auto;
                color: #718092;
                font-size: .62rem;
                white-space: nowrap;
            }

            .master-periode-status-filter select {
                min-height: 29px;
                padding: .25rem .45rem;
                color: #526480;
                background: #fff;
                border: 1px solid #e2e8ef;
                border-radius: 4px;
                font: inherit;
            }

            .master-periode-table-wrap {
                display: block;
                width: 100%;
                overflow-x: auto;
                background: #fff;
                border: 1px solid #e2e8ef;
                border-radius: 7px;
            }

            .master-periode-table {
                width: 100%;
                min-width: 1050px;
                border-collapse: separate;
                border-spacing: 0;
                color: #334155;
                font-size: .68rem;
            }

            .master-periode-table th {
                padding: .63rem .62rem;
                color: #617185;
                background: #f7f9fb;
                border-bottom: 1px solid #e2e8ef;
                font-size: .57rem;
                font-weight: 700;
                text-align: left;
                text-transform: uppercase;
                white-space: nowrap;
            }

            .master-periode-table td {
                padding: .57rem .62rem;
                border-bottom: 1px solid #edf1f5;
                vertical-align: middle;
            }

            .master-periode-table tbody tr:last-child td {
                border-bottom: 0;
            }

            .master-periode-table tbody tr:hover {
                background: #fafcfe;
            }

            .master-periode-table-number {
                display: inline-block;
                padding: .25rem .38rem;
                color: #526480;
                background: #f0f4f8;
                border-radius: 4px;
                font-size: .6rem;
                white-space: nowrap;
            }

            .master-periode-table-name {
                display: block;
                min-width: 145px;
                color: #25364a;
                font-size: .68rem;
            }

            .master-periode-table-date {
                display: inline-flex;
                align-items: center;
                gap: .3rem;
                color: #526480;
                font-size: .62rem;
                white-space: nowrap;
            }

            .master-periode-table-date i {
                color: #1769d2;
            }

            .master-periode-table-date.end i {
                color: #d33755;
            }

            .master-periode-table-duration {
                display: inline-block;
                padding: .25rem .38rem;
                color: #526480;
                background: #f0f4f8;
                border-radius: 4px;
                font-size: .61rem;
                white-space: nowrap;
            }

            .master-periode-table-status,
            .master-periode-table-archive {
                display: inline-flex;
                align-items: center;
                gap: .28rem;
                padding: .25rem .38rem;
                color: #526480;
                background: #f0f4f8;
                border-radius: 999px;
                font-size: .59rem;
                white-space: nowrap;
            }

            .master-periode-table-status i {
                font-size: .38rem;
            }

            .master-periode-table-status.is-running,
            .master-periode-table-status.is-finished {
                color: #087c50;
                background: #e6f7ef;
            }

            .master-periode-table-status.is-draft {
                color: #a96800;
                background: #fff5df;
            }

            .master-periode-table-archive.is-archived {
                color: #526480;
                background: #edf1f5;
            }

            .master-periode-table-actions-heading {
                text-align: right !important;
            }

            .master-periode-table-actions {
                display: flex;
                align-items: center;
                justify-content: flex-end;
                gap: .15rem;
                white-space: nowrap;
            }

            .master-periode-table-actions a,
            .master-periode-table-actions button {
                display: grid;
                place-items: center;
                width: 27px;
                height: 27px;
                padding: 0;
                color: #617185;
                background: transparent;
                border: 1px solid transparent;
                border-radius: 4px;
                text-decoration: none;
                cursor: pointer;
            }

            .master-periode-table-actions a:hover {
                color: #1769d2;
                background: #edf4ff;
            }

            .master-periode-table-actions button {
                color: #d33755;
            }

            .master-periode-table-actions button:hover {
                background: #fff0f3;
            }

            .master-periode-table-actions form {
                margin: 0;
            }

            .master-periode-table-empty {
                height: 90px;
                color: #718092;
                text-align: center;
            }

            .master-periode-list {
                display: none !important;
            }

            .master-periode-pagination .pagination {
                justify-content: flex-end;
            }

            .master-periode-note {
                display: flex;
                align-items: flex-start;
                gap: .55rem;
                margin-top: .75rem;
                padding: .7rem .8rem;
                color: #2870aa;
                background: #eff8fd;
                border: 1px solid #d5eaf7;
                border-radius: 6px;
            }

            .master-periode-note>i {
                margin-top: .1rem;
            }

            .master-periode-note p {
                margin: 0;
                color: #526b8c;
                font-size: .65rem;
                line-height: 1.45;
            }

            .master-periode-note strong {
                display: block;
                margin-bottom: .15rem;
                color: #07527c;
            }
        }

        @media (min-width: 1200px) {
            .master-periode-summary {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }
    </style>
@endpush
