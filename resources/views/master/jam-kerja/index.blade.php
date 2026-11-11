@extends('layouts.app')

@section('title', 'Jam Kerja')

@section('content')
    <div class="master-jam-kerja-page">


        <header class="master-jam-kerja-heading">
            <div>
                <h1>
                    <i class="fa-solid fa-clock"></i>
                    Jam Kerja
                </h1>
                <p>Kelola jadwal jam masuk, pulang, dan batas toleransi keterlambatan.</p>
            </div>
            <a href="{{ route('master.jam-kerja.create') }}" class="master-jam-kerja-add-desktop">
                <i class="fa-solid fa-plus"></i> Tambah Jam Kerja
            </a>
        </header>

        <a href="{{ route('master.jam-kerja.create') }}" class="master-jam-kerja-add">
            <i class="fa-solid fa-plus"></i>
            <span>Tambah Jam Kerja</span>
        </a>
        <div class="master-jam-kerja-filters" aria-label="Filter hari">
            @foreach (['Semua Hari', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'] as $hari)
                <span class="{{ $hari === 'Semua Hari' ? 'active' : '' }}">{{ $hari }}</span>
            @endforeach
        </div>

        <div class="master-jam-kerja-list-head">
            <span>DAFTAR JAM KERJA AKTIF</span>
            <span>{{ $jamKerjas->total() }} JADWAL</span>
        </div>

        <div class="master-jam-kerja-table-wrap">
            <table class="master-jam-kerja-table">
                <thead>
                    <tr>
                        <th scope="col">No</th>
                        <th scope="col">Hari / Jadwal</th>
                        <th scope="col">Target Jabatan</th>
                        <th scope="col">Jam Masuk</th>
                        <th scope="col">Jam Pulang</th>
                        <th scope="col">Toleransi</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="master-jam-kerja-table-actions-heading">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($jamKerjas as $jam)
                        @php
                            $jamMasuk = \Carbon\Carbon::parse($jam->jam_masuk)->format('H:i');
                            $jamPulang = \Carbon\Carbon::parse($jam->jam_pulang)->format('H:i');
                        @endphp
                        <tr>
                            <td><span
                                    class="master-jam-kerja-table-number">{{ $loop->iteration + ($jamKerjas->currentPage() - 1) * $jamKerjas->perPage() }}</span>
                            </td>
                            <td>
                                <span class="master-jam-kerja-table-day">{{ $jam->hari }}</span>
                                <small
                                    class="master-jam-kerja-table-day-note">{{ $jam->hari === 'Jumat' ? 'Jadwal Khusus Hari Jumat' : 'Hari Kerja Reguler' }}</small>
                            </td>
                            <td><span
                                    class="master-jam-kerja-table-role">{{ $jam->jabatan->nama_jabatan ?? 'Semua Jabatan' }}</span>
                            </td>
                            <td><span class="master-jam-kerja-table-time"><i class="fa-regular fa-clock"></i>
                                    {{ $jamMasuk }}</span></td>
                            <td><span class="master-jam-kerja-table-time"><i class="fa-regular fa-clock"></i>
                                    {{ $jamPulang }}</span></td>
                            <td><span class="master-jam-kerja-table-tolerance">{{ $jam->toleransi_menit }} mnt</span></td>
                            <td><span class="master-jam-kerja-table-status"><i class="fa-solid fa-circle"></i> Aktif</span>
                            </td>
                            <td>
                                <div class="master-jam-kerja-table-actions">
                                    <a href="{{ route('master.jam-kerja.edit', $jam) }}"
                                        aria-label="Ubah jadwal {{ $jam->hari }}" title="Ubah"><i
                                            class="fa-regular fa-pen-to-square"></i></a>
                                    <form action="{{ route('master.jam-kerja.destroy', $jam) }}" method="POST"
                                        class="form-delete" data-confirm-message="Yakin ingin menghapus jam kerja ini?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" aria-label="Hapus jadwal {{ $jam->hari }}"
                                            title="Hapus"><i class="fa-regular fa-trash-can"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="master-jam-kerja-table-empty" colspan="8">Belum ada data jam kerja.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="master-jam-kerja-list">
            @forelse ($jamKerjas as $jam)
                @php
                    $jamMasuk = \Carbon\Carbon::parse($jam->jam_masuk)->format('H:i');
                    $jamPulang = \Carbon\Carbon::parse($jam->jam_pulang)->format('H:i');
                @endphp
                <article class="master-jam-kerja-card">
                    <div class="master-jam-kerja-card-body">
                        <div class="master-jam-kerja-card-top">
                            <span
                                class="master-jam-kerja-number">{{ $loop->iteration + ($jamKerjas->currentPage() - 1) * $jamKerjas->perPage() }}</span>
                            <div class="master-jam-kerja-day">
                                <h2>{{ $jam->hari }}</h2>
                                <p>{{ $jam->hari === 'Jumat' ? 'Jadwal Khusus Hari Jumat' : 'Hari Kerja Reguler' }}</p>
                            </div>
                            <span class="master-jam-kerja-role">
                                {{ $jam->jabatan->nama_jabatan ?? 'Semua Jabatan' }}
                            </span>
                        </div>

                        <div class="master-jam-kerja-times">
                            <div>
                                <span>Jam Masuk</span>
                                <strong>{{ $jamMasuk }}</strong>
                            </div>
                            <div>
                                <span>Jam Pulang</span>
                                <strong>{{ $jamPulang }}</strong>
                            </div>
                            <div>
                                <span>Toleransi</span>
                                <strong>{{ $jam->toleransi_menit }} mnt</strong>
                            </div>
                        </div>

                        <div class="master-jam-kerja-actions">
                            <a href="{{ route('master.jam-kerja.edit', $jam) }}" class="edit">
                                <i class="fa-solid fa-pen-to-square"></i> Ubah
                            </a>
                            <form action="{{ route('master.jam-kerja.destroy', $jam) }}" method="POST" class="form-delete"
                                data-confirm-message="Yakin ingin menghapus jam kerja ini?">
                                @csrf
                                @method('DELETE')
                                <button type="submit">
                                    <i class="fa-regular fa-trash-can"></i> Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                </article>
            @empty
                <div class="master-jam-kerja-empty">
                    <i class="fa-regular fa-calendar-xmark"></i>
                    <strong>Belum ada data jam kerja.</strong>
                    <span>Tambahkan jadwal kerja untuk mulai mengatur presensi.</span>
                </div>
            @endforelse
        </div>

        <aside class="master-jam-kerja-note">
            <span><i class="fa-solid fa-circle-info"></i></span>
            <div>
                <strong>Ketentuan Jam &amp; Presensi Kerja</strong>
                <p>Toleransi keterlambatan maksimal <b>15 menit</b>. Keterlambatan di atas toleransi akan tercatat pada log
                    absensi dan berdampak pada kalkulasi pemotongan Tukin bulanan.</p>
            </div>
        </aside>

        <div class="master-jam-kerja-pagination">{{ $jamKerjas->links('pagination::bootstrap-5') }}</div>
    </div>
@endsection

@push('styles')
    <style>
        body.role-master .master-jam-kerja-page {
            width: 100%;
            max-width: 560px;
            margin: auto;
            padding: 1rem 1rem 0;
            box-sizing: border-box;
            color: #14223d
        }

        .master-jam-kerja-breadcrumb {
            display: flex;
            align-items: center;
            gap: .5rem;
            margin-bottom: .8rem;
            color: #0875ad;
            font-size: .7rem
        }

        .master-jam-kerja-breadcrumb a {
            color: #71829c;
            text-decoration: none
        }

        .master-jam-kerja-breadcrumb i {
            font-size: .5rem;
            color: #9caabd
        }

        .master-jam-kerja-heading {
            margin-bottom: 1rem
        }

        .master-jam-kerja-heading h1 {
            margin: 0;
            font-size: 1rem
        }

        .master-jam-kerja-heading p {
            max-width: 390px;
            margin: .3rem 0 0;
            color: #71829c;
            font-size: .75rem;
            line-height: 1.45
        }

        .master-jam-kerja-add {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .7rem;
            padding: .8rem;
            margin-bottom: 1rem;
            color: #fff;
            background: #078dcc;
            border-radius: 14px;
            box-shadow: 0 4px 8px #078dcc33;
            text-decoration: none;
            font-size: .78rem;
            font-weight: 700
        }

        .master-jam-kerja-add i {
            font-size: 1rem
        }

        .master-jam-kerja-filters {
            display: flex;
            gap: .5rem;
            overflow: auto;
            margin-bottom: 1.1rem;
            padding-bottom: .1rem
        }

        .master-jam-kerja-filters span {
            flex: 0 0 auto;
            padding: .58rem .8rem;
            color: #526480;
            background: #fff;
            border: 1px solid #dce5ee;
            border-radius: 10px;
            font-size: .67rem;
            white-space: nowrap
        }

        .master-jam-kerja-filters .active {
            color: #fff;
            background: #14223d;
            border-color: #14223d
        }

        .master-jam-kerja-list-head {
            display: flex;
            justify-content: space-between;
            gap: .5rem;
            margin: 0 .35rem .8rem;
            color: #91a1b8;
            font-size: .64rem
        }

        .master-jam-kerja-list {
            display: grid;
            gap: .8rem
        }

        .master-jam-kerja-card {
            position: relative;
            overflow: hidden;
            background: #fff;
            border: 1px solid #e0e8f1;
            border-radius: 19px;
            box-shadow: 0 5px 16px #2334550b
        }

        .master-jam-kerja-card:before {
            position: absolute;
            inset: 0 auto 0 0;
            width: 6px;
            background: #10a9df;
            content: ""
        }

        .master-jam-kerja-card:first-child:before {
            background: #18bb83
        }

        .master-jam-kerja-card-body {
            padding: 1rem 1rem 1rem 1.15rem
        }

        .master-jam-kerja-card-top {
            display: flex;
            align-items: center;
            gap: .65rem;
            min-width: 0
        }

        .master-jam-kerja-number {
            display: grid;
            place-items: center;
            width: 34px;
            height: 34px;
            flex: 0 0 34px;
            color: #526480;
            background: #f0f5f9;
            border-radius: 11px;
            font-size: .78rem
        }

        .master-jam-kerja-day {
            min-width: 0;
            flex: 1
        }

        .master-jam-kerja-day h2 {
            margin: 0;
            font-size: .86rem
        }

        .master-jam-kerja-day p {
            margin: .25rem 0 0;
            color: #91a1b8;
            font-size: .65rem;
            overflow-wrap: anywhere
        }

        .master-jam-kerja-role {
            max-width: 112px;
            padding: .38rem .55rem;
            color: #078dcc;
            background: #eff9ff;
            border: 1px solid #bce7ff;
            border-radius: 8px;
            font-size: .64rem;
            line-height: 1.15;
            overflow-wrap: anywhere
        }

        .master-jam-kerja-times {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            margin-top: 1rem;
            padding: .75rem .7rem;
            background: #f8fafc;
            border: 1px solid #edf2f7;
            border-radius: 14px
        }

        .master-jam-kerja-times div {
            min-width: 0;
            padding: 0 .55rem;
            border-right: 1px solid #dce5ee
        }

        .master-jam-kerja-times div:first-child {
            padding-left: 0
        }

        .master-jam-kerja-times div:last-child {
            padding-right: 0;
            border-right: 0
        }

        .master-jam-kerja-times span {
            display: block;
            color: #91a1b8;
            font-size: .62rem
        }

        .master-jam-kerja-times strong {
            display: block;
            margin-top: .3rem;
            color: #14223d;
            font-size: .92rem
        }

        .master-jam-kerja-times div:last-child strong {
            color: #ef8700
        }

        .master-jam-kerja-actions {
            display: flex;
            justify-content: flex-end;
            gap: .5rem;
            margin-top: .85rem;
            padding-top: .1rem
        }

        .master-jam-kerja-actions a,
        .master-jam-kerja-actions button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .35rem;
            padding: .48rem .7rem;
            color: #607492;
            background: #fff;
            border: 1px solid #dce5ee;
            border-radius: 10px;
            text-decoration: none;
            font: inherit;
            font-size: .68rem;
            white-space: nowrap
        }

        .master-jam-kerja-actions .edit {
            color: #078dcc;
            border-color: #a9e0ff
        }

        .master-jam-kerja-actions button {
            color: #f03d5c;
            border-color: #ffb9c8
        }

        .master-jam-kerja-actions form {
            margin: 0
        }

        .master-jam-kerja-empty {
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

        .master-jam-kerja-empty i {
            font-size: 2rem
        }

        .master-jam-kerja-empty strong {
            color: #526480;
            font-size: .82rem
        }

        .master-jam-kerja-empty span {
            font-size: .7rem
        }

        .master-jam-kerja-note {
            display: flex;
            gap: .75rem;
            margin-top: 1.1rem;
            padding: 1.1rem 1rem;
            color: #0875ad;
            background: #eaf7ff;
            border: 1px solid #bfe7ff;
            border-radius: 17px
        }

        .master-jam-kerja-note>span {
            display: grid;
            place-items: center;
            width: 34px;
            height: 34px;
            flex: 0 0 34px;
            color: #fff;
            background: #078dcc;
            border-radius: 9px
        }

        .master-jam-kerja-note strong {
            display: block;
            margin: .1rem 0 .3rem;
            color: #07527c;
            font-size: .8rem
        }

        .master-jam-kerja-note p {
            margin: 0;
            color: #526b8c;
            font-size: .71rem;
            line-height: 1.55
        }

        .master-jam-kerja-pagination {
            margin-top: .9rem
        }

        .master-jam-kerja-pagination .pagination {
            justify-content: center;
            flex-wrap: wrap
        }

        .master-jam-kerja-pagination svg {
            width: 14px;
            height: 14px
        }

        .master-jam-kerja-add-desktop,
        .master-jam-kerja-table-wrap {
            display: none;
        }

        @media(max-width:380px) {
            body.role-master .master-jam-kerja-page {
                padding-inline: .75rem
            }

            .master-jam-kerja-heading h1 {
                font-size: 1.25rem
            }

            .master-jam-kerja-heading p {
                font-size: .69rem
            }

            .master-jam-kerja-card-body {
                padding-left: 1rem
            }

            .master-jam-kerja-role {
                max-width: 94px
            }

            .master-jam-kerja-times {
                padding-inline: .5rem
            }

            .master-jam-kerja-times div {
                padding-inline: .35rem
            }

            .master-jam-kerja-times strong {
                font-size: .82rem
            }

            .master-jam-kerja-actions {
                gap: .3rem
            }

            .master-jam-kerja-actions a,
            .master-jam-kerja-actions button {
                padding-inline: .48rem;
                font-size: .63rem
            }
        }

        @media (min-width: 768px) {
            .master-jam-kerja-heading {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
                margin-bottom: 1rem;
            }

            .master-jam-kerja-heading h1 {
                color: #1b2938;
                font-size: 1.4rem;
                font-weight: 700;
            }

            .master-jam-kerja-heading p {
                font-size: .78rem;
            }

            .master-jam-kerja-heading>.master-jam-kerja-add-desktop {
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

            .master-jam-kerja-page>.master-jam-kerja-add {
                display: none;
            }

            .master-jam-kerja-filters {
                gap: .4rem;
                margin-bottom: .85rem;
            }

            .master-jam-kerja-filters span {
                padding: .42rem .65rem;
                border-radius: 999px;
                font-size: .66rem;
            }

            .master-jam-kerja-filters .active {
                background: #14223d;
                border-color: #14223d;
            }

            .master-jam-kerja-list-head {
                margin: 0 .35rem .55rem;
                font-size: .62rem;
            }

            .master-jam-kerja-list-head span:last-child {
                padding: .2rem .4rem;
                color: #526480;
                background: #e9eef4;
                border-radius: 3px;
                font-size: .57rem;
            }

            .master-jam-kerja-table-wrap {
                display: block;
                width: 100%;
                overflow-x: auto;
                background: #fff;
                border: 1px solid #e2e8ef;
                border-radius: 6px;
            }

            .master-jam-kerja-table {
                width: 100%;
                min-width: 920px;
                border-collapse: separate;
                border-spacing: 0;
                color: #334155;
                font-size: .72rem;
            }

            .master-jam-kerja-table th {
                padding: .68rem .7rem;
                color: #617185;
                background: #f7f9fb;
                border-bottom: 1px solid #e2e8ef;
                font-size: .61rem;
                font-weight: 700;
                text-align: left;
                text-transform: uppercase;
                white-space: nowrap;
            }

            .master-jam-kerja-table td {
                padding: .6rem .7rem;
                border-bottom: 1px solid #edf1f5;
                vertical-align: middle;
            }

            .master-jam-kerja-table tbody tr:last-child td {
                border-bottom: 0;
            }

            .master-jam-kerja-table tbody tr:hover {
                background: #fafcfe;
            }

            .master-jam-kerja-table-number {
                display: grid;
                place-items: center;
                width: 24px;
                height: 24px;
                color: #526480;
                background: #f0f4f8;
                border-radius: 50%;
                font-size: .64rem;
            }

            .master-jam-kerja-table-day,
            .master-jam-kerja-table-day-note {
                display: block;
            }

            .master-jam-kerja-table-day {
                color: #25364a;
                font-size: .73rem;
                font-weight: 700;
            }

            .master-jam-kerja-table-day-note {
                margin-top: .12rem;
                color: #8290a0;
                font-size: .62rem;
            }

            .master-jam-kerja-table-role {
                display: inline-block;
                padding: .3rem .45rem;
                color: #2870aa;
                background: #eff8fd;
                border-radius: 4px;
                font-size: .63rem;
            }

            .master-jam-kerja-table-time {
                display: inline-flex;
                align-items: center;
                gap: .3rem;
                padding: .28rem .4rem;
                color: #25364a;
                background: #f3f6f9;
                border-radius: 4px;
                font-size: .66rem;
                font-weight: 700;
                white-space: nowrap;
            }

            .master-jam-kerja-table-time i {
                color: #8290a0;
                font-size: .62rem;
            }

            .master-jam-kerja-table-tolerance {
                display: inline-block;
                padding: .28rem .4rem;
                color: #a96800;
                background: #fff5df;
                border-radius: 4px;
                font-size: .63rem;
                white-space: nowrap;
            }

            .master-jam-kerja-table-status {
                display: inline-flex;
                align-items: center;
                gap: .3rem;
                padding: .28rem .42rem;
                color: #087c50;
                background: #e6f7ef;
                border-radius: 999px;
                font-size: .62rem;
                white-space: nowrap;
            }

            .master-jam-kerja-table-status i {
                font-size: .4rem;
            }

            .master-jam-kerja-table-actions-heading {
                text-align: center !important;
            }

            .master-jam-kerja-table-actions {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: .2rem;
                white-space: nowrap;
            }

            .master-jam-kerja-table-actions a,
            .master-jam-kerja-table-actions button {
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

            .master-jam-kerja-table-actions a:hover {
                color: #1769d2;
                background: #edf4ff;
            }

            .master-jam-kerja-table-actions button {
                color: #d33755;
            }

            .master-jam-kerja-table-actions button:hover {
                background: #fff0f3;
            }

            .master-jam-kerja-table-actions form {
                margin: 0;
            }

            .master-jam-kerja-table-empty {
                height: 90px;
                color: #718092;
                text-align: center;
            }

            .master-jam-kerja-list {
                display: none !important;
            }

            .master-jam-kerja-note {
                border-radius: 6px;
            }

            .master-jam-kerja-pagination .pagination {
                justify-content: flex-end;
            }
        }
    </style>
@endpush
