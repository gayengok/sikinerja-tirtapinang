@extends('layouts.app')
@section('title', 'Data Pegawai')
@section('content')
    @php
        $atasanCount = $roleCounts['atasan'];
        $kepalaBagianCount = $roleCounts['kepalaBagian'];
        $direkturCount = $roleCounts['direktur'];
        $pegawaiCount = $roleCounts['pegawai'];
    @endphp
    <div class="master-pegawai-page">

        <header class="master-pegawai-heading">
            <div class="heading-title">
                <div>
                    <h1>
                        <i class="fa-solid fa-users-gear"></i>
                        Data Pegawai
                    </h1>
                    <p>Kelola akun Pegawai, Atasan, Kepala Bagian, dan Direktur. Akun Admin dikelola terpisah.</p>
                </div>
            </div>

            <a href="{{ route('master.pegawai.create') }}">
                <i class="fa-solid fa-user-plus"></i>
                Tambah
            </a>
        </header>



        <div class="master-pegawai-summary">
            <div><small>TOTAL</small><strong>{{ $pegawais->total() }}</strong><span><i class="fa-solid fa-circle"></i>
                    Terdaftar</span></div>
            <div><small>ATASAN</small><strong>{{ $atasanCount }}</strong><span>Menilai Pegawai</span></div>
            <div><small>KEPALA BAGIAN</small><strong>{{ $kepalaBagianCount }}</strong><span>Menilai Atasan</span></div>
            <div><small>DIREKTUR</small><strong>{{ $direkturCount }}</strong><span>Menilai Kepala Bagian</span></div>
            <div><small>PEGAWAI</small><strong>{{ $pegawaiCount }}</strong><span>Dinilai oleh Atasan</span></div>
        </div>
        <div class="master-pegawai-search"><i class="fa-solid fa-magnifying-glass"></i><input type="search"
                placeholder="Cari nama, email, jabatan..."></div>
        <div class="master-pegawai-tabs"><span class="active">Semua ({{ $pegawais->total() }})</span><span>Atasan
                ({{ $atasanCount }})</span><span>Kepala Bagian ({{ $kepalaBagianCount }})</span><span>Direktur ({{ $direkturCount }})</span><span>Pegawai ({{ $pegawaiCount }})</span></div>
        <div class="master-pegawai-list-head"><span>Menampilkan <b>{{ $pegawais->count() }}</b> dari
                {{ $pegawais->total() }} pegawai</span><a href="#"><i class="fa-solid fa-arrow-down-wide-short"></i>
                Urutkan</a></div>

        <div class="master-pegawai-table-wrap">
            <table class="master-pegawai-table">
                <thead>
                    <tr>
                        <th scope="col">Pegawai</th>
                        <th scope="col">Peran</th>
                        <th scope="col">Divisi</th>
                        <th scope="col">Jabatan</th>
                        <th scope="col">Atasan Langsung</th>
                        <th scope="col" class="master-pegawai-table-actions-heading">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pegawais as $pegawai)
                        @php
                            $role = $pegawai->user->role ?? 'pegawai';
                            $isManager = in_array($role, ['atasan', 'kepala_bagian', 'direktur'], true);
                            $roleLabel = match ($role) { 'direktur' => 'Direktur', 'kepala_bagian' => 'Kepala Bagian', 'atasan' => 'Atasan', default => 'Pegawai' };
                        @endphp
                        <tr>
                            <td>
                                <div class="master-pegawai-table-person">
                                    <span
                                        class="master-pegawai-table-avatar">{{ strtoupper(substr($pegawai->nama, 0, 2)) }}</span>
                                    <span class="master-pegawai-table-person-copy">
                                        <strong>{{ $pegawai->nama }}</strong>
                                        <small>{{ $pegawai->user->email ?? '-' }}</small>
                                    </span>
                                </div>
                            </td>
                            <td><span
                                    class="master-pegawai-role {{ $isManager ? 'is-atasan' : 'is-staff' }}">{{ $roleLabel }}</span>
                            </td>
                            <td>{{ $pegawai->jabatan->divisi->nama_divisi ?? '-' }}</td>
                            <td>{{ $pegawai->jabatan->nama_jabatan ?? '-' }}</td>
                            <td>{{ $pegawai->atasan->nama ?? '-' }}</td>
                            <td>
                                <div class="master-pegawai-table-actions">
                                    <a href="{{ route('master.pegawai.show', $pegawai) }}"
                                        aria-label="Detail {{ $pegawai->nama }}" title="Detail"><i
                                            class="fa-regular fa-eye"></i></a>
                                    <a href="{{ route('master.pegawai.edit', $pegawai) }}"
                                        aria-label="Edit {{ $pegawai->nama }}" title="Edit"><i
                                            class="fa-solid fa-pen-to-square"></i></a>
                                    <form action="{{ route('master.pegawai.destroy', $pegawai) }}" method="POST"
                                        class="form-delete" data-confirm-message="Yakin ingin menghapus pegawai ini?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" aria-label="Hapus {{ $pegawai->nama }}" title="Hapus"><i
                                                class="fa-regular fa-trash-can"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="master-pegawai-table-empty" colspan="6">Belum ada data pegawai.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="master-pegawai-list">
            @forelse($pegawais as $pegawai)
                @php
                    $role = $pegawai->user->role ?? 'pegawai';
                    $isManager = in_array($role, ['atasan', 'kepala_bagian', 'direktur'], true);
                    $roleLabel = match ($role) { 'direktur' => 'Direktur', 'kepala_bagian' => 'Kepala Bagian', 'atasan' => 'Atasan', default => 'Pegawai' };
                @endphp
                <article class="master-pegawai-card {{ $isManager ? 'atasan' : 'staff' }}">
                    <div class="master-pegawai-card-top"><span
                            class="master-pegawai-avatar">{{ strtoupper(substr($pegawai->nama, 0, 2)) }}</span>
                        <div>
                            <h2>{{ $pegawai->nama }} <b>{{ $roleLabel }}</b></h2>
                            <p><i class="fa-regular fa-envelope"></i> {{ $pegawai->user->email ?? '-' }}</p>
                        </div><button type="button" class="master-more" aria-label="Menu"><i
                                class="fa-solid fa-ellipsis-vertical"></i></button>
                    </div>
                    <div class="master-pegawai-info">
                        <div><span>Divisi</span><strong>{{ $pegawai->jabatan->divisi->nama_divisi ?? '-' }}</strong></div>
                        <div><span>Jabatan</span><strong>{{ $pegawai->jabatan->nama_jabatan ?? '-' }}</strong></div>
                        <div><span>Atasan Langsung</span><strong>{{ $pegawai->atasan->nama ?? '-' }}</strong></div>
                    </div>
                    <div class="master-pegawai-actions"><a href="{{ route('master.pegawai.show', $pegawai) }}"><i
                                class="fa-regular fa-eye"></i> Detail</a><a class="edit"
                            href="{{ route('master.pegawai.edit', $pegawai) }}"><i class="fa-solid fa-pen"></i> Edit
                            Data</a>
                        <form action="{{ route('master.pegawai.destroy', $pegawai) }}" method="POST" class="form-delete"
                            data-confirm-message="Yakin ingin menghapus pegawai ini?">@csrf @method('DELETE')<button
                                type="submit" aria-label="Hapus"><i class="fa-solid fa-trash"></i></button></form>
                    </div>
                </article>
            @empty <div class="master-pegawai-empty"><i class="fa-regular fa-folder-open"></i><strong>Belum ada data
                        pegawai</strong></div>
            @endforelse
        </div>
        <div class="master-pegawai-pagination">{{ $pegawais->links('pagination::bootstrap-5') }}</div>
    </div>
@endsection
@push('styles')
    <style>
        body.role-master .master-pegawai-page {
            box-sizing: border-box;
            width: 100%;
            max-width: 560px;
            margin: auto;
            padding: 1rem;
            color: #14223d;
            padding: 1rem 1rem 0;
        }

        .master-mobile-breadcrumb {
            margin-bottom: 1rem;
            color: #71829c;
            font-size: .72rem
        }

        .master-mobile-breadcrumb span {
            margin: 0 .35rem
        }

        .master-mobile-breadcrumb a {
            color: #0875ad;
            text-decoration: none
        }

        .master-pegawai-heading {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: .6rem
        }

        .master-pegawai-heading h1 {
            margin: 0;
            font-size: 1rem
        }

        .master-pegawai-heading p {
            margin: .3rem 0 1rem;
            color: #71829c;
            font-size: .78rem
        }

        .master-pegawai-heading>a {
            padding: .7rem .8rem;
            color: #fff;
            background: #0875ad;
            border-radius: 11px;
            text-decoration: none;
            font-size: .72rem;
            white-space: nowrap
        }

        .master-pegawai-summary {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .55rem;
            margin: .6rem 0 1rem
        }

        .master-pegawai-summary>div {
            min-width: 0;
            padding: .8rem;
            background: #fff;
            border: 1px solid #dfe7f0;
            border-radius: 16px
        }

        .master-pegawai-summary small,
        .master-pegawai-summary span {
            display: block;
            color: #91a1b8;
            font-size: .62rem
        }

        .master-pegawai-summary strong {
            display: block;
            margin: .4rem 0;
            font-size: 1.35rem
        }

        .master-pegawai-summary span i {
            color: #09ad82;
            font-size: .45rem
        }

        .master-pegawai-search {
            display: flex;
            align-items: center;
            gap: .6rem;
            padding: .75rem 1rem;
            background: #fff;
            border: 1px solid #dfe7f0;
            border-radius: 14px;
            color: #91a1b8
        }

        .master-pegawai-search input {
            width: 100%;
            min-width: 0;
            border: 0;
            outline: 0;
            font-size: .74rem
        }

        .master-pegawai-tabs {
            display: flex;
            gap: .45rem;
            overflow: auto;
            margin: 1rem 0 .9rem
        }

        .master-pegawai-tabs span {
            flex: 0 0 auto;
            padding: .55rem .75rem;
            border: 1px solid #dfe7f0;
            border-radius: 13px;
            color: #526480;
            font-size: .68rem;
            white-space: nowrap
        }

        .master-pegawai-tabs .active {
            color: #fff;
            background: #0875ad;
            border-color: #0875ad
        }

        .master-pegawai-list-head {
            display: flex;
            justify-content: space-between;
            gap: .5rem;
            margin-bottom: .7rem;
            color: #91a1b8;
            font-size: .7rem
        }

        .master-pegawai-list-head a {
            color: #0875ad;
            text-decoration: none;
            white-space: nowrap
        }

        .master-pegawai-list {
            display: grid;
            gap: .8rem
        }

        .master-pegawai-card {
            overflow: hidden;
            background: #fff;
            border: 1px solid #dfe7f0;
            border-top: 4px solid #18aee0;
            border-radius: 18px;
            box-shadow: 0 4px 14px #2334550a
        }

        .master-pegawai-card.staff {
            border-top-color: #0bb889
        }

        .master-pegawai-card-top {
            display: flex;
            align-items: flex-start;
            gap: .7rem;
            padding: 1rem
        }

        .master-pegawai-avatar {
            display: grid;
            place-items: center;
            width: 50px;
            height: 50px;
            flex: 0 0 50px;
            color: #0875ad;
            background: #fff8df;
            border: 1px solid #f4ca43;
            border-radius: 15px;
            font-weight: 700
        }

        .staff .master-pegawai-avatar {
            color: #0875ad;
            background: #eff7ff;
            border-color: #9dcfff
        }

        .master-pegawai-card-top>div {
            min-width: 0;
            flex: 1
        }

        .master-pegawai-card h2 {
            margin: 0;
            font-size: .9rem;
            overflow-wrap: anywhere
        }

        .master-pegawai-card h2 b {
            display: inline-block;
            margin-left: .25rem;
            padding: .3rem .45rem;
            color: #9b5a00;
            background: #fff2c7;
            border-radius: 10px;
            font-size: .58rem;
            font-weight: 500;
            vertical-align: middle
        }

        .staff .master-pegawai-card h2 b {
            color: #087b5c;
            background: #d9f9ea
        }

        .master-pegawai-card p {
            margin: .3rem 0 0;
            color: #71829c;
            font-size: .7rem;
            overflow-wrap: anywhere
        }

        .master-more {
            border: 0;
            background: none;
            color: #91a1b8
        }

        .master-pegawai-info {
            display: grid;
            gap: .5rem;
            padding: 0 1rem .85rem
        }

        .master-pegawai-info>div {
            display: flex;
            justify-content: space-between;
            gap: .6rem;
            padding: .65rem .7rem;
            background: #f8fafc;
            border-radius: 10px;
            font-size: .7rem
        }

        .master-pegawai-info span {
            color: #91a1b8
        }

        .master-pegawai-info strong {
            max-width: 65%;
            text-align: right;
            overflow-wrap: anywhere
        }

        .master-pegawai-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: .5rem;
            padding: .8rem 1rem;
            border-top: 1px solid #edf1f6
        }

        .master-pegawai-actions a,
        .master-pegawai-actions button {
            padding: .6rem .75rem;
            color: #526480;
            background: #f1f5f9;
            border: 1px solid transparent;
            border-radius: 10px;
            text-decoration: none;
            font-size: .7rem
        }

        .master-pegawai-actions a.edit {
            color: #0875ad;
            background: #eff9ff;
            border-color: #a9dcf6
        }

        .master-pegawai-actions form {
            margin: 0
        }

        .master-pegawai-actions form button {
            color: #d33755;
            background: #fff0f3
        }

        .master-pegawai-empty {
            display: grid;
            place-items: center;
            gap: .4rem;
            padding: 2rem;
            color: #91a1b8
        }

        .master-pegawai-empty i {
            font-size: 2rem
        }

        .master-pegawai-pagination {
            margin-top: .8rem
        }

        .master-pegawai-pagination .pagination {
            justify-content: center;
            flex-wrap: wrap
        }

        .master-pegawai-pagination svg {
            width: 14px;
            height: 14px
        }

        @media(max-width:380px) {
            body.role-master .master-pegawai-page {
                padding: .75rem;
                padding-bottom: calc(var(--bottom-nav-height) + 1rem)
            }

            .master-pegawai-heading h1 {
                font-size: 1.2rem
            }

            .master-pegawai-heading p {
                font-size: .68rem
            }

            .master-pegawai-summary {
                gap: .35rem
            }

            .master-pegawai-summary>div {
                padding: .6rem
            }

            .master-pegawai-summary strong {
                font-size: 1.1rem
            }

            .master-pegawai-summary small,
            .master-pegawai-summary span {
                font-size: .55rem
            }

            .master-pegawai-card-top {
                padding: .8rem
            }

            .master-pegawai-info {
                padding-inline: .8rem
            }

            .master-pegawai-actions {
                padding: .7rem .8rem
            }

            .master-pegawai-actions a {
                padding: .55rem .6rem;
                font-size: .64rem
            }

            .master-pegawai-actions a.edit {
                flex: 1;
                text-align: center
            }
        }

        .master-pegawai-table-wrap {
            display: none;
        }

        @media (min-width: 768px) {
            .master-pegawai-summary {
                grid-template-columns: repeat(5, minmax(0, 1fr));
                gap: .75rem;
                margin: .25rem 0 1rem;
            }

            .master-pegawai-summary>div {
                padding: .9rem 1rem;
                border-radius: 6px;
            }

            .master-pegawai-search {
                border-radius: 6px;
            }

            .master-pegawai-tabs span {
                border-radius: 5px;
            }

            .master-pegawai-tabs .active {
                background: #1769d2;
                border-color: #1769d2;
            }

            .master-pegawai-list {
                display: none !important;
            }

            .master-pegawai-table-wrap {
                display: block;
                width: 100%;
                overflow-x: auto;
                background: #fff;
                border: 1px solid #e2e8ef;
                border-radius: 6px;
            }

            .master-pegawai-table {
                width: 100%;
                min-width: 850px;
                border-collapse: separate;
                border-spacing: 0;
                color: #334155;
                font-size: .76rem;
            }

            .master-pegawai-table th {
                padding: .75rem .85rem;
                color: #617185;
                background: #f7f9fb;
                border-bottom: 1px solid #e2e8ef;
                font-size: .65rem;
                font-weight: 700;
                text-align: left;
                text-transform: uppercase;
                white-space: nowrap;
            }

            .master-pegawai-table td {
                padding: .65rem .85rem;
                border-bottom: 1px solid #edf1f5;
                vertical-align: middle;
            }

            .master-pegawai-table tbody tr:last-child td {
                border-bottom: 0;
            }

            .master-pegawai-table tbody tr:hover {
                background: #f9fbfd;
            }

            .master-pegawai-table-person {
                display: flex;
                align-items: center;
                gap: .65rem;
                min-width: 190px;
            }

            .master-pegawai-table-avatar {
                display: grid;
                place-items: center;
                width: 34px;
                height: 34px;
                flex: 0 0 34px;
                color: #1769d2;
                background: #eaf2ff;
                border-radius: 50%;
                font-size: .68rem;
                font-weight: 700;
            }

            .master-pegawai-table-person-copy {
                display: block;
                min-width: 0;
            }

            .master-pegawai-table-person-copy strong,
            .master-pegawai-table-person-copy small {
                display: block;
                max-width: 240px;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .master-pegawai-table-person-copy strong {
                color: #24364b;
                font-size: .76rem;
            }

            .master-pegawai-table-person-copy small {
                margin-top: .15rem;
                color: #8290a0;
                font-size: .66rem;
            }

            .master-pegawai-role {
                display: inline-flex;
                align-items: center;
                padding: .28rem .45rem;
                border-radius: 4px;
                font-size: .64rem;
                white-space: nowrap;
            }

            .master-pegawai-role.is-atasan {
                color: #986000;
                background: #fff4d8;
            }

            .master-pegawai-role.is-staff {
                color: #087c50;
                background: #e6f7ef;
            }

            .master-pegawai-table-actions-heading {
                text-align: right !important;
            }

            .master-pegawai-table-actions {
                display: flex;
                align-items: center;
                justify-content: flex-end;
                gap: .35rem;
                white-space: nowrap;
            }

            .master-pegawai-table-actions a,
            .master-pegawai-table-actions button {
                display: grid;
                place-items: center;
                width: 30px;
                height: 30px;
                padding: 0;
                color: #526480;
                background: transparent;
                border: 1px solid transparent;
                border-radius: 4px;
                text-decoration: none;
                cursor: pointer;
            }

            .master-pegawai-table-actions a:hover {
                color: #1769d2;
                background: #edf4ff;
            }

            .master-pegawai-table-actions button {
                color: #d33755;
            }

            .master-pegawai-table-actions button:hover {
                background: #fff0f3;
            }

            .master-pegawai-table-actions form {
                margin: 0;
            }

            .master-pegawai-table-empty {
                height: 100px;
                color: #718092;
                text-align: center;
            }
        }
    </style>
@endpush
