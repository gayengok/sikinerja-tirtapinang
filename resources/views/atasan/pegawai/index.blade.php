@extends('layouts.app')

@section('title', 'Pegawai Bawahan')

@section('content')
    <div class="atasan-team-page">
        <div class="atasan-mobile-breadcrumb">
            <a href="{{ route('atasan.dashboard') }}" aria-label="Kembali ke dashboard"><i
                    class="fa-solid fa-arrow-left"></i></a>
            <div><span>Manajemen Tim</span><strong>Pegawai Bawahan</strong></div>
        </div>


        {{-- <div class="atasan-team-heading">
            <div>
                <h1>Pegawai Bawahan</h1>
                <p>Daftar pegawai yang berada di bawah tanggung jawab dan penilaian Anda.</p>
            </div>
            <span>{{ $pegawais->total() }} Pegawai<br>Terdaftar</span>
        </div> --}}

        @php
            $totalBawahan = $pegawais->total();
            $skorTampilan = $skorTim !== null ? round($skorTim, 1) : 0;
            $periodeLabel = $periodeAktif?->tanggal_mulai
                ? \Carbon\Carbon::parse($periodeAktif->tanggal_mulai)->translatedFormat('F Y')
                : 'Belum ada periode';
        @endphp



        <section class="atasan-team-summary">
            <div class="atasan-team-summary-heading">
                <span><i class="fa-solid fa-circle"></i> TIM PENILAIAN ATASAN</span>
                <b><i class="fa-regular fa-calendar"></i> {{ $periodeLabel }}</b>
            </div>
            <div class="atasan-team-summary-main">
                <div class="atasan-team-ring" style="--progress: {{ $skorTampilan * 3.6 }}deg">
                    <strong>{{ $skorTim !== null ? number_format($skorTampilan, 0) : '-' }}%</strong>
                    <small>SKOR</small>
                </div>
                <div>
                    <h2>{{ $skorTim !== null && $skorTampilan >= 90 ? 'Kinerja Tim Optimal' : 'Kinerja Tim Berjalan' }}</h2>
                    <p>{{ $totalBawahan > 0 ? 'Seluruh target KPI & verifikasi laporan dipantau.' : 'Belum ada pegawai bawahan yang terdaftar.' }}
                    </p>
                </div>
                <span
                    class="atasan-team-grade">{{ $skorTim !== null ? ($skorTampilan >= 90 ? 'A+ Sangat Baik' : ($skorTampilan >= 75 ? 'B Baik' : 'Perlu Evaluasi')) : '-' }}</span>
            </div>
            <div class="atasan-team-trend"><span><i class="fa-solid fa-circle"></i> Tren Capaian 30
                    Hari</span><strong>{{ $skorTim !== null ? 'Stabil' : 'Belum tersedia' }}</strong><b>+0%</b></div>
            <div class="atasan-team-summary-stats">
                <strong><i class="fa-solid fa-user-group"></i>{{ $bawahanAktif }}<small>Bawahan Aktif</small></strong>
                <strong><i
                        class="fa-regular fa-circle-check"></i>{{ $skorTim !== null ? number_format($skorTampilan, 0) . '%' : '-' }}<small>Kinerja
                        Tim</small></strong>
                <strong><i class="fa-regular fa-clock"></i>{{ $laporanTertunda }}<small>Laporan Tertunda</small></strong>
            </div>
        </section>

        <div class="atasan-team-search"><i class="fa-solid fa-magnifying-glass"></i><input type="search"
                placeholder="Cari nama, NIP, atau jabatan pegawai..."></div>
        <div class="atasan-team-tabs"><span class="active">Semua ({{ $pegawais->total() }})</span><span>Aktif
                ({{ $pegawais->where('status', 'aktif')->count() }})</span><span>Perlu Penilaian (0)</span><span>Cuti /
                Izin</span></div>
        <div class="atasan-team-list-heading">
            <h2>Daftar Anggota Tim</h2><span>Urutan Terkini</span>
        </div>

        <div class="atasan-team-table-wrap">
            <table class="atasan-team-table">
                <thead>
                    <tr>
                        <th scope="col">No</th>
                        <th scope="col">Pegawai</th>
                        <th scope="col">Kontak &amp; Email</th>
                        <th scope="col">Unit / Divisi</th>
                        <th scope="col">Status Kepegawaian</th>
                        <th scope="col" class="atasan-team-table-actions-heading">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pegawais as $pegawai)
                        <tr>
                            <td><span
                                    class="atasan-team-table-number">{{ $loop->iteration + ($pegawais->currentPage() - 1) * $pegawais->perPage() }}</span>
                            </td>
                            <td>
                                <div class="atasan-team-table-person">
                                    <span
                                        class="atasan-team-table-avatar">{{ strtoupper(substr($pegawai->nama, 0, 2)) }}</span>
                                    <span><strong>{{ $pegawai->nama }}</strong><small>{{ $pegawai->jabatan->nama_jabatan ?? '-' }}</small></span>
                                </div>
                            </td>
                            <td><span class="atasan-team-table-email"><i
                                        class="fa-regular fa-envelope"></i>{{ $pegawai->user->email ?? '-' }}</span></td>
                            <td><span class="atasan-team-table-division"><i
                                        class="fa-regular fa-building"></i>{{ $pegawai->jabatan->divisi->nama_divisi ?? '-' }}</span>
                            </td>
                            <td><span
                                    class="atasan-team-table-status {{ $pegawai->status === 'aktif' ? 'is-active' : '' }}"><i
                                        class="fa-solid fa-circle"></i>{{ ucfirst($pegawai->status) }}</span></td>
                            <td>
                                <div class="atasan-team-table-actions">
                                    <a href="{{ route('atasan.pegawai.show', $pegawai) }}"><i
                                            class="fa-regular fa-clipboard"></i> Riwayat</a>
                                    <a href="{{ route('atasan.kompetensi.edit', $pegawai) }}" class="primary">Beri Nilai <i
                                            class="fa-solid fa-chevron-right"></i></a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="atasan-team-table-empty" colspan="6">Anda belum memiliki pegawai bawahan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="atasan-team-list">
            @forelse ($pegawais as $pegawai)
                <article class="atasan-employee-card">
                    <div class="atasan-employee-top">
                        <span class="atasan-employee-avatar">{{ strtoupper(substr($pegawai->nama, 0, 2)) }}</span>
                        <div>
                            <h3>{{ $pegawai->nama }} <em
                                    class="{{ $pegawai->status === 'aktif' ? 'is-active' : '' }}">{{ ucfirst($pegawai->status) }}</em>
                            </h3>
                            <p>{{ $pegawai->jabatan->nama_jabatan ?? '-' }}</p>
                        </div>
                    </div>



                    <div class="atasan-employee-meta"><span><i class="fa-regular fa-envelope"></i>
                            Email</span><strong>{{ $pegawai->user->email ?? '-' }}</strong></div>
                    <div class="atasan-employee-meta"><span><i class="fa-regular fa-building"></i> Unit /
                            Divisi</span><strong>{{ $pegawai->jabatan->divisi->nama_divisi ?? '-' }}</strong></div>
                    <div class="atasan-employee-meta"><span><i class="fa-regular fa-circle-check"></i> Status
                            Kepegawaian</span><strong class="approval-ok">{{ ucfirst($pegawai->status) }}</strong></div>

                    <div class="atasan-employee-actions"><a href="{{ route('atasan.pegawai.show', $pegawai) }}"
                            class="secondary"><i class="fa-regular fa-clipboard"></i> Riwayat Laporan</a>
                        <a href="{{ route('atasan.kompetensi.edit', $pegawai) }}" class="primary">Beri Nilai <i
                                class="fa-solid fa-chevron-right"></i></a>
                    </div>
                </article>

            @empty
                <div class="atasan-team-empty">Anda belum memiliki pegawai bawahan.</div>
            @endforelse
        </div>


        <div class="atasan-team-info"><i class="fa-solid fa-circle-info"></i>
            <div><strong>Informasi Manajemen Staf</strong><span>Bawahan otomatis terdaftar sesuai struktur organisasi dan
                    penempatan unit kerja.</span></div>
        </div>
        <div class="atasan-team-pagination">{{ $pegawais->links('pagination::bootstrap-5') }}</div>
    </div>


@endsection



@push('styles')
    <style>
        body.role-atasan .atasan-team-page {
            padding: 1rem;
            color: #17213b
        }

        .atasan-mobile-breadcrumb {
            display: flex;
            gap: .45rem;
            margin-bottom: 1rem;
            color: #7b8ca6;
            font-size: .74rem
        }

        .atasan-mobile-breadcrumb a {
            color: #078ac8;
            text-decoration: none
        }

        .atasan-team-heading {
            display: flex;
            justify-content: space-between;
            gap: .7rem;
            align-items: flex-start
        }

        .atasan-team-heading h1 {
            margin: 0;
            font-size: 1.35rem;
            font-weight: 800
        }

        .atasan-team-heading p {
            margin: .3rem 0 1rem;
            color: #7789a4;
            font-size: .78rem;
            line-height: 1.5
        }

        .atasan-team-heading>span {
            min-width: 112px;
            padding: .35rem .6rem;
            color: #078ac8;
            border: 1px solid #8edaff;
            border-radius: 18px;
            font-size: .72rem;
            line-height: 1.15
        }

        .atasan-team-summary {
            padding: 1rem;
            background: #fff;
            border: 1px solid #dfe7f0;
            border-radius: 20px;
            box-shadow: 0 4px 14px rgba(30, 50, 80, .05)
        }

        .atasan-team-summary-heading,
        .atasan-team-list-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: .5rem
        }

        .atasan-team-summary-heading span {
            color: #078653;
            background: #eafbf4;
            border: 1px solid #9de7c4;
            border-radius: 999px;
            padding: .4rem .6rem;
            font-size: .68rem;
            font-weight: 700
        }

        .atasan-team-summary-heading span i {
            font-size: .45rem
        }

        .atasan-team-summary-heading b {
            padding: .35rem .55rem;
            color: #526480;
            background: #f4f7fa;
            border-radius: 8px;
            font-size: .62rem;
            font-weight: 500
        }

        .atasan-team-summary-main {
            display: flex;
            align-items: center;
            gap: .7rem;
            margin-top: .8rem;
            padding: .75rem;
            background: #f8fafc;
            border: 1px solid #edf1f5;
            border-radius: 15px
        }

        .atasan-team-ring {
            position: relative;
            display: flex;
            flex: 0 0 72px;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 72px;
            height: 72px;
            background: conic-gradient(#079ed7 var(--progress), #e5f2f8 0);
            border-radius: 50%
        }

        .atasan-team-ring::after {
            position: absolute;
            width: 54px;
            height: 54px;
            content: "";
            background: #fff;
            border-radius: 50%
        }

        .atasan-team-ring strong,
        .atasan-team-ring small {
            position: relative;
            z-index: 1
        }

        .atasan-team-ring strong {
            color: #26344d;
            font-size: .95rem;
            line-height: 1
        }

        .atasan-team-ring small {
            margin-top: .2rem;
            color: #078ac8;
            font-size: .48rem
        }

        .atasan-team-summary-main>div:nth-child(2) {
            min-width: 0;
            flex: 1
        }

        .atasan-team-summary-main h2 {
            margin: 0;
            color: #26344d;
            font-size: .85rem
        }

        .atasan-team-summary-main p {
            margin: .3rem 0 0;
            color: #71829c;
            font-size: .65rem;
            line-height: 1.4
        }

        .atasan-team-grade {
            padding: .35rem .45rem;
            color: #08a77f;
            background: #eafbf4;
            border: 1px solid #9de7c4;
            border-radius: 5px;
            font-size: .58rem;
            white-space: nowrap
        }

        .atasan-team-trend {
            display: flex;
            align-items: center;
            gap: .4rem;
            margin-top: .7rem;
            padding: .6rem 0;
            color: #71829c;
            border-bottom: 1px solid #edf1f5;
            font-size: .62rem
        }

        .atasan-team-trend span {
            flex: 1
        }

        .atasan-team-trend span i {
            margin-right: .2rem;
            color: #08a77f;
            font-size: .4rem
        }

        .atasan-team-trend strong {
            color: #526480;
            font-weight: 600
        }

        .atasan-team-trend b {
            color: #08a77f;
            font-size: .65rem
        }

        .atasan-team-summary-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: .5rem;
            margin-top: .7rem
        }

        .atasan-team-summary-stats strong {
            display: block;
            padding: .7rem .3rem;
            color: #26344d;
            background: #f8fafc;
            border: 1px solid #edf1f5;
            border-radius: 13px;
            text-align: center;
            font-size: 1.05rem;
            line-height: 1
        }

        .atasan-team-summary-stats strong:nth-child(2) {
            color: #08a77f;
            background: #f2fff9;
            border-color: #b8f1d8
        }

        .atasan-team-summary-stats strong:nth-child(3) {
            color: #526480
        }

        .atasan-team-summary-stats strong>i {
            display: block;
            margin-bottom: .3rem;
            color: #078dcc;
            font-size: .7rem
        }

        .atasan-team-summary-stats small {
            display: block;
            margin-top: .2rem;
            color: #71829c;
            font-size: .58rem;
            font-weight: 500;
            line-height: 1.2
        }

        .atasan-team-search {
            display: flex;
            align-items: center;
            gap: .6rem;
            margin-top: 1rem;
            padding: .75rem 1rem;
            background: #fff;
            border: 1px solid #dfe7f0;
            border-radius: 15px
        }

        .atasan-team-search i {
            color: #8fa1b9
        }

        .atasan-team-search input {
            width: 100%;
            border: 0;
            outline: 0;
            color: #27344c;
            font-size: .8rem
        }

        .atasan-team-tabs {
            display: flex;
            gap: .45rem;
            margin: 1rem 0;
            overflow-x: auto
        }

        .atasan-team-tabs span {
            flex: 0 0 auto;
            padding: .55rem .7rem;
            color: #536682;
            background: #fff;
            border: 1px solid #dfe7f0;
            border-radius: 10px;
            font-size: .68rem;
            white-space: nowrap
        }

        .atasan-team-tabs .active {
            color: #fff;
            background: #078ac8;
            border-color: #078ac8
        }

        .atasan-team-list-heading {
            margin-bottom: .7rem;
            padding: 0 .15rem
        }

        .atasan-team-list-heading h2 {
            margin: 0;
            color: #667994;
            font-size: .78rem;
            text-transform: uppercase
        }

        .atasan-team-list-heading span {
            color: #8a9ab4;
            font-size: .68rem
        }

        .atasan-team-list {
            display: grid;
            gap: .75rem
        }

        .atasan-employee-card {
            overflow: hidden;
            background: #fff;
            border: 1px solid #dfe7f0;
            border-radius: 19px;
            box-shadow: 0 4px 12px #2434550b
        }

        .atasan-employee-top {
            display: flex;
            align-items: center;
            gap: .7rem;
            padding: 1rem
        }

        .atasan-employee-avatar {
            display: grid;
            place-items: center;
            width: 54px;
            height: 54px;
            flex: 0 0 54px;
            color: #fff;
            background: linear-gradient(145deg, #4f46e5, #0ea5e9);
            border-radius: 14px;
            font-weight: 800
        }

        .atasan-employee-top>div {
            min-width: 0;
            flex: 1
        }

        .atasan-employee-top h3 {
            margin: 0;
            font-size: .9rem
        }

        .atasan-employee-top h3 em {
            margin-left: .25rem;
            padding: .25rem .45rem;
            color: #6b7f98;
            background: #f1f5f9;
            border-radius: 10px;
            font-size: .6rem;
            font-style: normal
        }

        .atasan-employee-top h3 em.is-active {
            color: #07966b;
            background: #eafbf4
        }

        .atasan-employee-top p,
        .atasan-employee-top small {
            display: block;
            margin: .2rem 0 0;
            color: #078ac8;
            font-size: .72rem
        }

        .atasan-employee-top small {
            color: #8a9ab4
        }

        .atasan-employee-number {
            align-self: flex-start;
            color: #c0cada
        }

        .atasan-employee-meta {
            display: flex;
            justify-content: space-between;
            gap: .6rem;
            padding: .55rem 1rem;
            border-top: 1px solid #edf1f6;
            color: #93a3bb;
            font-size: .72rem
        }

        .atasan-employee-meta span {
            white-space: nowrap
        }

        .atasan-employee-meta i {
            width: 16px
        }

        .atasan-employee-meta strong {
            overflow: hidden;
            color: #34435b;
            font-weight: 500;
            text-align: right;
            text-overflow: ellipsis;
            white-space: nowrap
        }

        .atasan-employee-meta .approval-ok {
            padding: .25rem .45rem;
            color: #07966b;
            background: #eafbf4;
            border-radius: 7px;
            font-weight: 700
        }

        .atasan-employee-actions {
            display: flex;
            gap: .55rem;
            padding: .8rem;
            border-top: 1px solid #edf1f6
        }

        .atasan-employee-actions a {
            flex: 1;
            padding: .7rem .4rem;
            border-radius: 12px;
            text-align: center;
            text-decoration: none;
            font-size: .7rem;
            font-weight: 700
        }

        .atasan-employee-actions .secondary {
            color: #078ac8;
            background: #eef9ff
        }

        .atasan-employee-actions .primary {
            color: #fff;
            background: #078ac8
        }

        .atasan-team-empty {
            padding: 2rem;
            text-align: center;
            color: #8494ab
        }

        .atasan-team-pagination {
            margin-top: 1rem
        }

        .atasan-team-pagination .pagination {
            justify-content: center;
            gap: .2rem
        }

        .atasan-team-pagination .page-link {
            font-size: .7rem;
            border-radius: 8px
        }

        .atasan-team-info {
            display: flex;
            gap: .7rem;
            margin-top: 1rem;
            padding: 1rem;
            color: #526480;
            background: #eef7ff;
            border: 1px solid #cfe8ff;
            border-radius: 16px;
            font-size: .72rem
        }

        .atasan-team-info>i {
            color: #078ac8;
            font-size: 1rem
        }

        .atasan-team-info strong,
        .atasan-team-info span {
            display: block
        }

        .atasan-team-info strong {
            color: #14527c;
            font-size: .76rem
        }

        .atasan-team-info span {
            margin-top: .25rem;
            line-height: 1.5
        }

        .atasan-team-table-wrap {
            display: none;
        }

        @media (min-width: 992px) {
            .atasan-team-table-wrap {
                display: block;
                width: 100%;
                overflow-x: auto;
                background: #fff;
                border: 1px solid #dfe7f0;
                border-radius: 8px;
            }

            .atasan-team-table {
                width: 100%;
                min-width: 760px;
                table-layout: fixed;
                border-collapse: separate;
                border-spacing: 0;
                color: #334155;
                font-size: .68rem;
            }

            .atasan-team-table th {
                padding: .65rem .55rem;
                color: #617185;
                background: #f7f9fb;
                border-bottom: 1px solid #e2e8ef;
                font-size: .57rem;
                font-weight: 700;
                text-align: left;
                text-transform: uppercase;
                white-space: nowrap;
            }

            .atasan-team-table th:nth-child(1) {
                width: 42px;
            }

            .atasan-team-table th:nth-child(2) {
                width: 19%;
            }

            .atasan-team-table th:nth-child(3) {
                width: 21%;
            }

            .atasan-team-table th:nth-child(4) {
                width: 19%;
            }

            .atasan-team-table th:nth-child(5) {
                width: 15%;
            }

            .atasan-team-table th:nth-child(6) {
                width: 190px;
            }

            .atasan-team-table td {
                padding: .6rem .55rem;
                border-bottom: 1px solid #edf1f5;
                vertical-align: middle;
            }

            .atasan-team-table tbody tr:last-child td {
                border-bottom: 0;
            }

            .atasan-team-table tbody tr:hover {
                background: #fafcfe;
            }

            .atasan-team-table-number {
                display: grid;
                place-items: center;
                width: 22px;
                height: 22px;
                color: #526480;
                background: #f0f4f8;
                border-radius: 50%;
                font-size: .62rem;
            }

            .atasan-team-table-person {
                display: flex;
                align-items: center;
                gap: .45rem;
                min-width: 0;
            }

            .atasan-team-table-avatar {
                display: grid;
                place-items: center;
                width: 32px;
                height: 32px;
                flex: 0 0 32px;
                color: #fff;
                background: #078ac8;
                border-radius: 50%;
                font-size: .58rem;
                font-weight: 700;
            }

            .atasan-team-table-person>span:last-child {
                display: block;
                min-width: 0;
            }

            .atasan-team-table-person strong,
            .atasan-team-table-person small {
                display: block;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .atasan-team-table-person strong {
                color: #26344d;
                font-size: .66rem;
            }

            .atasan-team-table-person small {
                margin-top: .12rem;
                color: #078ac8;
                font-size: .58rem;
            }

            .atasan-team-table-email,
            .atasan-team-table-division {
                display: flex;
                align-items: center;
                gap: .3rem;
                min-width: 0;
                color: #64748b;
                font-size: .61rem;
            }

            .atasan-team-table-email i,
            .atasan-team-table-division i {
                flex: 0 0 auto;
                color: #91a1b8;
                font-size: .6rem;
            }

            .atasan-team-table-status {
                display: inline-flex;
                align-items: center;
                gap: .28rem;
                padding: .25rem .4rem;
                color: #64748b;
                background: #f1f5f9;
                border-radius: 999px;
                font-size: .59rem;
                white-space: nowrap;
            }

            .atasan-team-table-status i {
                font-size: .38rem;
            }

            .atasan-team-table-status.is-active {
                color: #07966b;
                background: #eafbf4;
            }

            .atasan-team-table-actions-heading {
                text-align: center !important;
            }

            .atasan-team-table-actions {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: .3rem;
                white-space: nowrap;
            }

            .atasan-team-table-actions a {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: .25rem;
                min-height: 29px;
                padding: .28rem .4rem;
                color: #078ac8;
                background: #eef9ff;
                border-radius: 4px;
                font-size: .58rem;
                font-weight: 700;
                text-decoration: none;
            }

            .atasan-team-table-actions a.primary {
                color: #fff;
                background: #078ac8;
            }

            .atasan-team-table-empty {
                height: 95px;
                color: #8494ab;
                text-align: center;
            }

            .atasan-team-list {
                display: none !important;
            }

            .atasan-team-summary {
                border-radius: 8px;
                box-shadow: none;
            }

            .atasan-team-search {
                border-radius: 6px;
            }

            .atasan-team-tabs span {
                border-radius: 5px;
            }

            .atasan-team-pagination .pagination {
                justify-content: flex-end;
            }
        }
    </style>
    </style>
@endpush
