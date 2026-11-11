@extends('layouts.app')

@section('title', 'Jenis Potongan')

@section('content')
    <div class="master-potongan-page">


        <header class="master-potongan-heading">
            <div>
                <h1>
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                    Jenis Potongan
                </h1>
                <p>Kelola daftar jenis potongan presensi &amp; pelanggaran kedisiplinan.</p>
            </div>
            <a href="{{ route('master.jenis-potongan.create') }}" class="master-potongan-add-desktop">
                <i class="fa-solid fa-plus"></i> Tambah Potongan
            </a>
        </header>

        <a href="{{ route('master.jenis-potongan.create') }}" class="master-potongan-add">
            <i class="fa-solid fa-plus"></i>
            <span>Tambah Potongan</span>
        </a>

        <section class="master-potongan-summary">
            <span class="master-potongan-summary-label">TOTAL KONFIGURASI</span>
            <div class="master-potongan-summary-main">
                <strong>{{ $jenisPotongans->total() }}</strong>
                <span>Aturan Potongan Aktif</span>
                <b><i class="fa-solid fa-circle"></i> Aktif</b>
            </div>
            <div class="master-potongan-summary-footer">
                <span>Peraturan Disiplin Kepegawaian</span>
                <strong>Tahun {{ now()->year }}</strong>
            </div>
        </section>

        <div class="master-potongan-search">
            <div><i class="fa-solid fa-magnifying-glass"></i><input type="search"
                    placeholder="Cari potongan atau jenis..."></div>
            <button type="button" aria-label="Filter"><i class="fa-solid fa-filter"></i></button>
        </div>

        <div class="master-potongan-table-wrap">
            <table class="master-potongan-table">
                <thead>
                    <tr>
                        <th scope="col">No</th>
                        <th scope="col">Nama / Jenis Potongan</th>
                        <th scope="col">Kategori &amp; Deskripsi</th>
                        <th scope="col">Rentang Waktu</th>
                        <th scope="col">Potongan Tukin</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="master-potongan-actions-heading">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($jenisPotongans as $jenis)
                        @php
                            $tipe = str_replace('_', ' ', ucfirst($jenis->tipe_pelanggaran));
                            $isAlpa = $jenis->tipe_pelanggaran === 'alpa';
                            $isTidakAbsen = $jenis->tipe_pelanggaran === 'tidak_absen';
                            $rentang =
                                $jenis->menit_min !== null || $jenis->menit_max !== null
                                    ? ($jenis->menit_min ?? 0) . ' – ' . ($jenis->menit_max ?? '∞') . ' menit'
                                    : '- (Tanpa Keterangan)';
                        @endphp
                        <tr>
                            <td><span
                                    class="master-potongan-table-number {{ $isAlpa ? 'is-alpa' : ($isTidakAbsen ? 'is-absen' : '') }}">{{ $loop->iteration + ($jenisPotongans->currentPage() - 1) * $jenisPotongans->perPage() }}</span>
                            </td>
                            <td>
                                <strong class="master-potongan-table-name">{{ $jenis->nama_potongan }}</strong>
                                <span
                                    class="master-potongan-table-category {{ $isAlpa ? 'is-alpa' : ($isTidakAbsen ? 'is-absen' : '') }}">{{ $tipe }}</span>
                            </td>
                            <td><span class="master-potongan-table-description">{{ $jenis->keterangan ?: '-' }}</span></td>
                            <td><span class="master-potongan-table-range">{{ $rentang }}</span></td>
                            <td>
                                <strong
                                    class="master-potongan-table-rate {{ $isAlpa ? 'is-alpa' : ($isTidakAbsen ? 'is-absen' : '') }}">{{ number_format((float) $jenis->persen_potongan, 2) }}%</strong>
                                <small class="master-potongan-table-rate-label">Potongan Tukin</small>
                            </td>
                            <td><span class="master-potongan-table-status"><i class="fa-solid fa-circle"></i> Aktif</span>
                            </td>
                            <td>
                                <div class="master-potongan-table-actions">
                                    <a href="{{ route('master.jenis-potongan.edit', $jenis) }}"
                                        aria-label="Edit {{ $jenis->nama_potongan }}" title="Edit"><i
                                            class="fa-regular fa-pen-to-square"></i></a>
                                    <form action="{{ route('master.jenis-potongan.destroy', $jenis) }}" method="POST"
                                        class="form-delete"
                                        data-confirm-message="Yakin ingin menghapus jenis potongan ini?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" aria-label="Hapus {{ $jenis->nama_potongan }}"
                                            title="Hapus"><i class="fa-regular fa-trash-can"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="master-potongan-table-empty" colspan="7">Belum ada data jenis potongan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="master-potongan-list">
            @forelse ($jenisPotongans as $jenis)
                @php
                    $tipe = str_replace('_', ' ', ucfirst($jenis->tipe_pelanggaran));
                    $isAlpa = $jenis->tipe_pelanggaran === 'alpa';
                    $isTidakAbsen = $jenis->tipe_pelanggaran === 'tidak_absen';
                    $rentang =
                        $jenis->menit_min !== null || $jenis->menit_max !== null
                            ? ($jenis->menit_min ?? 0) . ' – ' . ($jenis->menit_max ?? '∞') . ' menit'
                            : '- (Tanpa Keterangan)';
                @endphp
                <article class="master-potongan-card {{ $isAlpa ? 'is-alpa' : ($isTidakAbsen ? 'is-absen' : '') }}">
                    <div class="master-potongan-card-head">
                        <span
                            class="master-potongan-number">{{ $loop->iteration + ($jenisPotongans->currentPage() - 1) * $jenisPotongans->perPage() }}</span>
                        <div class="master-potongan-name">
                            <h2>{{ $jenis->nama_potongan }}</h2>
                            <span>{{ $tipe }}</span>
                        </div>
                        <div class="master-potongan-rate">
                            <strong>{{ number_format((float) $jenis->persen_potongan, 2) }}%</strong>
                            <small>Potongan Tukin</small>
                        </div>
                    </div>
                    <div class="master-potongan-range">
                        <span>{{ $jenis->menit_min !== null || $jenis->menit_max !== null ? 'Rentang Menit:' : 'Rentang Waktu:' }}</span>
                        <strong>{{ $rentang }}</strong>
                    </div>
                    @if ($jenis->keterangan)
                        <p class="master-potongan-description">{{ $jenis->keterangan }}</p>
                    @endif
                    <div class="master-potongan-actions">
                        <a href="{{ route('master.jenis-potongan.edit', $jenis) }}"><i
                                class="fa-regular fa-pen-to-square"></i> Edit</a>
                        <form action="{{ route('master.jenis-potongan.destroy', $jenis) }}" method="POST"
                            class="form-delete" data-confirm-message="Yakin ingin menghapus jenis potongan ini?">
                            @csrf
                            @method('DELETE')
                            <button type="submit"><i class="fa-regular fa-trash-can"></i> Hapus</button>
                        </form>
                    </div>
                </article>
            @empty
                <div class="master-potongan-empty">
                    <i class="fa-regular fa-folder-open"></i>
                    <strong>Belum ada data jenis potongan.</strong>
                    <span>Tambahkan aturan potongan untuk mulai mengatur disiplin presensi.</span>
                </div>
            @endforelse
        </div>

        <aside class="master-potongan-note">
            <i class="fa-solid fa-circle-info"></i>
            <p><strong>Catatan:</strong> Potongan presensi akan otomatis mengurangi kalkulasi Tunjangan Kinerja (Tukin)
                bruto pegawai pada periode berjalan.</p>
        </aside>

        <div class="master-potongan-pagination">{{ $jenisPotongans->links('pagination::bootstrap-5') }}</div>
    </div>
@endsection

@push('styles')
    <style>
        body.role-master .master-potongan-page {
            width: 100%;
            max-width: 560px;
            margin: auto;
            padding: 1rem 1rem 0;
            box-sizing: border-box;
            color: #14223d
        }

        .master-potongan-breadcrumb {
            display: flex;
            align-items: center;
            gap: .45rem;
            margin-bottom: 1rem;
            color: #14223d;
            font-size: .68rem;
            white-space: nowrap
        }

        .master-potongan-breadcrumb a {
            color: #0875ad;
            text-decoration: none
        }

        .master-potongan-breadcrumb i {
            font-size: .46rem;
            color: #9caabd
        }

        .master-potongan-heading {
            margin-bottom: .85rem
        }

        .master-potongan-heading h1 {
            margin: 0;
            font-size: 1rem
        }

        .master-potongan-heading p {
            max-width: 390px;
            margin: .3rem 0 0;
            color: #71829c;
            font-size: .73rem;
            line-height: 1.45
        }

        .master-potongan-add {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .55rem;
            padding: .78rem;
            margin-bottom: 1rem;
            color: #fff;
            background: #078dcc;
            border-radius: 13px;
            box-shadow: 0 4px 8px #078dcc33;
            text-decoration: none;
            font-size: .76rem;
            font-weight: 700
        }

        .master-potongan-summary {
            padding: 1rem .8rem .8rem;
            color: #fff;
            background: #14223d;
            border-radius: 18px;
            margin-bottom: 1rem
        }

        .master-potongan-summary-label {
            display: block;
            font-size: .65rem;
            letter-spacing: .03em
        }

        .master-potongan-summary-main {
            display: flex;
            align-items: center;
            gap: .45rem;
            padding: .35rem 0 .8rem;
            border-bottom: 1px solid #ffffff20
        }

        .master-potongan-summary-main>strong {
            font-size: 1.6rem
        }

        .master-potongan-summary-main>span {
            font-size: .72rem
        }

        .master-potongan-summary-main>b {
            margin-left: auto;
            padding: .4rem .55rem;
            color: #54e0ac;
            background: #0b5b5a;
            border: 1px solid #168c7c;
            border-radius: 999px;
            font-size: .64rem;
            font-weight: 500
        }

        .master-potongan-summary-main b i {
            font-size: .4rem;
            color: #1fe0a0
        }

        .master-potongan-summary-footer {
            display: flex;
            justify-content: space-between;
            gap: .5rem;
            padding-top: .7rem;
            color: #aebbd0;
            font-size: .64rem
        }

        .master-potongan-summary-footer strong {
            color: #fff;
            font-weight: 500
        }

        .master-potongan-search {
            display: flex;
            gap: .55rem;
            margin-bottom: 1rem
        }

        .master-potongan-search>div {
            display: flex;
            align-items: center;
            gap: .55rem;
            flex: 1;
            padding: .65rem .75rem;
            color: #91a1b8;
            background: #fff;
            border: 1px solid #dce5ee;
            border-radius: 12px
        }

        .master-potongan-search input {
            width: 100%;
            border: 0;
            outline: 0;
            font: inherit;
            font-size: .7rem
        }

        .master-potongan-search button {
            width: 40px;
            color: #526480;
            background: #fff;
            border: 1px solid #dce5ee;
            border-radius: 12px
        }

        .master-potongan-list {
            display: grid;
            gap: .8rem
        }

        .master-potongan-card {
            padding: 1rem .8rem .75rem;
            background: #fff;
            border: 1px solid #e0e8f1;
            border-radius: 18px;
            box-shadow: 0 4px 12px #23345508
        }

        .master-potongan-card-head {
            display: flex;
            align-items: flex-start;
            gap: .6rem
        }

        .master-potongan-number {
            display: grid;
            place-items: center;
            width: 30px;
            height: 30px;
            flex: 0 0 30px;
            color: #f03d5c;
            background: #fff0f2;
            border: 1px solid #ffb9c8;
            border-radius: 9px;
            font-size: .75rem
        }

        .master-potongan-card.is-absen .master-potongan-number {
            color: #175bd6;
            background: #eff5ff;
            border-color: #b4d2ff
        }

        .master-potongan-card:not(.is-alpa):not(.is-absen) .master-potongan-number {
            color: #bd7100;
            background: #fffbef;
            border-color: #ffd56a
        }

        .master-potongan-name {
            min-width: 0;
            flex: 1
        }

        .master-potongan-name h2 {
            margin: .15rem 0 .35rem;
            font-size: .84rem;
            overflow-wrap: anywhere
        }

        .master-potongan-name>span {
            display: inline-block;
            padding: .25rem .45rem;
            color: #f03d5c;
            background: #ffe5e8;
            border-radius: 5px;
            font-size: .6rem
        }

        .master-potongan-card:not(.is-alpa):not(.is-absen) .master-potongan-name>span {
            color: #a85d00;
            background: #fff0bf
        }

        .master-potongan-card.is-absen .master-potongan-name>span {
            color: #175bd6;
            background: #dceaff
        }

        .master-potongan-rate {
            text-align: right
        }

        .master-potongan-rate strong {
            display: block;
            padding: .4rem .55rem;
            color: #f03d5c;
            background: #fff4f5;
            border: 1px solid #ffb9c8;
            border-radius: 10px;
            font-size: .72rem;
            white-space: nowrap
        }

        .master-potongan-card:not(.is-alpa):not(.is-absen) .master-potongan-rate strong {
            color: #b56400;
            background: #fffdf3;
            border-color: #ffd56a
        }

        .master-potongan-card.is-absen .master-potongan-rate strong {
            color: #175bd6;
            background: #eff5ff;
            border-color: #b4d2ff
        }

        .master-potongan-rate small {
            display: block;
            margin-top: .25rem;
            color: #91a1b8;
            font-size: .6rem
        }

        .master-potongan-range {
            display: flex;
            justify-content: space-between;
            gap: .6rem;
            margin-top: .8rem;
            padding: .65rem .7rem;
            color: #71829c;
            background: #f8fafc;
            border-radius: 11px;
            font-size: .65rem
        }

        .master-potongan-range strong {
            color: #14223d;
            text-align: right;
            font-weight: 500
        }

        .master-potongan-description {
            margin: .55rem 0 0;
            color: #71829c;
            font-size: .65rem;
            line-height: 1.4
        }

        .master-potongan-actions {
            display: flex;
            justify-content: flex-end;
            gap: .45rem;
            margin-top: .7rem;
            padding-top: .65rem;
            border-top: 1px solid #edf2f7
        }

        .master-potongan-actions a,
        .master-potongan-actions button {
            padding: .45rem .6rem;
            color: #526480;
            background: #f3f6f9;
            border: 0;
            border-radius: 9px;
            text-decoration: none;
            font: inherit;
            font-size: .65rem
        }

        .master-potongan-actions button {
            color: #f03d5c;
            background: #fff0f2
        }

        .master-potongan-actions form {
            margin: 0
        }

        .master-potongan-empty {
            display: grid;
            place-items: center;
            gap: .45rem;
            padding: 2.2rem 1rem;
            color: #91a1b8;
            text-align: center;
            background: #fff;
            border: 1px solid #e0e8f1;
            border-radius: 18px
        }

        .master-potongan-empty i {
            font-size: 2rem
        }

        .master-potongan-empty strong {
            color: #526480;
            font-size: .8rem
        }

        .master-potongan-empty span {
            font-size: .68rem
        }

        .master-potongan-note {
            display: flex;
            gap: .65rem;
            margin-top: 1rem;
            padding: 1rem;
            color: #526b8c;
            background: #effaff;
            border: 1px solid #bce7ff;
            border-radius: 16px;
            font-size: .68rem;
            line-height: 1.55
        }

        .master-potongan-note>i {
            margin-top: .15rem;
            color: #078dcc
        }

        .master-potongan-note p {
            margin: 0
        }

        .master-potongan-note strong {
            color: #07527c
        }

        .master-potongan-pagination {
            margin-top: .9rem
        }

        .master-potongan-pagination .pagination {
            justify-content: center;
            flex-wrap: wrap
        }

        .master-potongan-pagination svg {
            width: 14px;
            height: 14px
        }

        .master-potongan-add-desktop,
        .master-potongan-table-wrap {
            display: none;
        }

        @media(max-width:380px) {
            body.role-master .master-potongan-page {
                padding-inline: .75rem
            }

            .master-potongan-heading h1 {
                font-size: 1.25rem
            }

            .master-potongan-heading p {
                font-size: .69rem
            }

            .master-potongan-card {
                padding-inline: .7rem
            }

            .master-potongan-name h2 {
                font-size: .78rem
            }

            .master-potongan-rate strong {
                font-size: .65rem
            }

            .master-potongan-actions {
                gap: .3rem
            }

            .master-potongan-actions a,
            .master-potongan-actions button {
                padding-inline: .45rem;
                font-size: .61rem
            }
        }

        @media (min-width: 768px) {
            .master-potongan-heading {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
                margin-bottom: 1rem;
            }

            .master-potongan-heading h1 {
                color: #1b2938;
                font-size: 1.4rem;
                font-weight: 700;
            }

            .master-potongan-heading p {
                font-size: .78rem;
            }

            .master-potongan-heading>.master-potongan-add-desktop {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: .45rem;
                padding: .65rem .85rem;
                color: #fff;
                background: #078dcc;
                border-radius: 6px;
                text-decoration: none;
                font-size: .72rem;
                font-weight: 700;
                white-space: nowrap;
            }

            .master-potongan-page>.master-potongan-add {
                display: none;
            }

            .master-potongan-summary {
                padding: .85rem 1rem .7rem;
                margin-bottom: .85rem;
                background: #111827;
                border: 1px solid #263449;
                border-radius: 7px;
            }

            .master-potongan-summary-label {
                color: #a7b6ce;
                font-size: .61rem;
                font-weight: 700;
                letter-spacing: .06em;
            }

            .master-potongan-summary-main {
                padding: .2rem 0 .65rem;
            }

            .master-potongan-summary-main>strong {
                font-size: 1.4rem;
            }

            .master-potongan-summary-main>span {
                color: #d7e0ec;
                font-size: .72rem;
            }

            .master-potongan-summary-main>b {
                color: #63e3b2;
                background: #123b3b;
                border-color: #236357;
            }

            .master-potongan-summary-footer {
                padding-top: .55rem;
                font-size: .62rem;
            }

            .master-potongan-search {
                align-items: center;
                padding: .55rem .65rem;
                margin-bottom: .8rem;
                background: #fff;
                border: 1px solid #e2e8ef;
                border-radius: 6px;
            }

            .master-potongan-search>div {
                max-width: 330px;
                padding: .48rem .6rem;
                border-radius: 5px;
            }

            .master-potongan-search button {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: .4rem;
                width: auto;
                min-height: 32px;
                padding: .35rem .65rem;
                border-radius: 5px;
                font: inherit;
                font-size: .68rem;
            }

            .master-potongan-search button::after {
                content: "Filter";
            }

            .master-potongan-list {
                display: none !important;
            }

            .master-potongan-table-wrap {
                display: block;
                width: 100%;
                overflow-x: auto;
                background: #fff;
                border: 1px solid #e2e8ef;
                border-radius: 6px;
            }

            .master-potongan-table {
                width: 100%;
                min-width: 1020px;
                border-collapse: separate;
                border-spacing: 0;
                color: #334155;
                font-size: .7rem;
            }

            .master-potongan-table th {
                padding: .68rem .7rem;
                color: #617185;
                background: #f7f9fb;
                border-bottom: 1px solid #e2e8ef;
                font-size: .6rem;
                font-weight: 700;
                text-align: left;
                text-transform: uppercase;
                white-space: nowrap;
            }

            .master-potongan-table td {
                padding: .58rem .7rem;
                border-bottom: 1px solid #edf1f5;
                vertical-align: middle;
            }

            .master-potongan-table tbody tr:last-child td {
                border-bottom: 0;
            }

            .master-potongan-table tbody tr:hover {
                background: #fafcfe;
            }

            .master-potongan-table-number {
                display: grid;
                place-items: center;
                width: 23px;
                height: 23px;
                color: #a85d00;
                background: #fff5df;
                border-radius: 5px;
                font-size: .64rem;
            }

            .master-potongan-table-number.is-alpa {
                color: #d33755;
                background: #fff0f3;
            }

            .master-potongan-table-number.is-absen {
                color: #175bd6;
                background: #eff5ff;
            }

            .master-potongan-table-name,
            .master-potongan-table-category {
                display: block;
            }

            .master-potongan-table-name {
                min-width: 145px;
                color: #25364a;
                font-size: .7rem;
                line-height: 1.4;
            }

            .master-potongan-table-category {
                width: fit-content;
                margin-top: .25rem;
                padding: .2rem .38rem;
                color: #a85d00;
                background: #fff4d8;
                border-radius: 4px;
                font-size: .59rem;
            }

            .master-potongan-table-category.is-alpa {
                color: #d33755;
                background: #ffe5e8;
            }

            .master-potongan-table-category.is-absen {
                color: #175bd6;
                background: #dceaff;
            }

            .master-potongan-table-description {
                display: block;
                min-width: 200px;
                max-width: 340px;
                color: #718092;
                font-size: .65rem;
                line-height: 1.4;
            }

            .master-potongan-table-range {
                color: #526480;
                font-size: .64rem;
                white-space: nowrap;
            }

            .master-potongan-table-rate {
                display: inline-block;
                padding: .3rem .45rem;
                color: #a85d00;
                background: #fff5df;
                border-radius: 4px;
                font-size: .68rem;
                white-space: nowrap;
            }

            .master-potongan-table-rate.is-alpa {
                color: #d33755;
                background: #fff0f3;
            }

            .master-potongan-table-rate.is-absen {
                color: #175bd6;
                background: #eff5ff;
            }

            .master-potongan-table-rate-label {
                display: block;
                margin-top: .16rem;
                color: #8290a0;
                font-size: .56rem;
            }

            .master-potongan-table-status {
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

            .master-potongan-table-status i {
                font-size: .4rem;
            }

            .master-potongan-actions-heading {
                text-align: center !important;
            }

            .master-potongan-table-actions {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: .2rem;
                white-space: nowrap;
            }

            .master-potongan-table-actions a,
            .master-potongan-table-actions button {
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

            .master-potongan-table-actions a:hover {
                color: #1769d2;
                background: #edf4ff;
            }

            .master-potongan-table-actions button {
                color: #d33755;
            }

            .master-potongan-table-actions button:hover {
                background: #fff0f3;
            }

            .master-potongan-table-actions form {
                margin: 0;
            }

            .master-potongan-table-empty {
                height: 90px;
                color: #718092;
                text-align: center;
            }

            .master-potongan-note {
                border-radius: 6px;
            }

            .master-potongan-pagination .pagination {
                justify-content: flex-end;
            }
        }
    </style>
@endpush
