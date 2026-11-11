@extends('layouts.app')

@section('title', auth()->user()->role === 'kepala_bagian' ? 'Approval Laporan Tim' : 'Approval Laporan Harian')

@section('content')
    <div class="atasan-approval-page">
        <div class="atasan-mobile-breadcrumb">
            <a href="{{ route($routePrefix . '.dashboard') }}" aria-label="Kembali ke dashboard"><i
                    class="fa-solid fa-arrow-left"></i></a>
            <div><span>{{ auth()->user()->role === 'kepala_bagian' ? 'Kepala Bagian' : 'Atasan' }}</span><strong>Approval Laporan</strong></div>
        </div>
        @php
            $waitingCount = (int) ($statusCounts['diajukan'] ?? 0);
            $approvedCount = (int) ($statusCounts['disetujui'] ?? 0);
            $rejectedCount = (int) ($statusCounts['ditolak'] ?? 0);
            $totalReports = $waitingCount + $approvedCount + $rejectedCount + (int) ($statusCounts['draft'] ?? 0);
            $processedCount = $approvedCount + $rejectedCount;
            $efficiency = $totalReports > 0 ? round(($processedCount / $totalReports) * 100) : 0;
            $days = collect(range(0, 6))->map(fn($daysAgo) => \Carbon\Carbon::today()->subDays(6 - $daysAgo));
            $maxActivity = max(1, (int) $activityCounts->max());
        @endphp

        <section class="atasan-approval-summary">
            <div class="atasan-approval-summary-heading">
                <div>
                    <h1>Statistik & Tren Approval</h1>
                    <p>Seluruh periode • Aktivitas laporan 7 hari terakhir</p>
                </div>
                <span><i class="fa-solid fa-circle"></i> {{ $efficiency }}% Efektif</span>
            </div>

            <div class="atasan-approval-summary-progress">
                <div class="atasan-approval-ring" style="--progress: {{ $efficiency * 3.6 }}deg">
                    <strong>{{ $efficiency }}%</strong>
                    <small>Selesai</small>
                </div>
                <div class="atasan-approval-breakdown">
                    <div><i></i><span>Terproses</span><b>{{ $processedCount }} laporan</b></div>
                    <div><i class="pending"></i><span>Antrean Pending</span><b>{{ $waitingCount }}</b></div>
                </div>
            </div>

            <div class="atasan-approval-activity">
                <div class="atasan-approval-activity-heading">
                    <strong>AKTIVITAS 7 HARI TERAKHIR</strong>
                    <span>Bebas Tunggakan</span>
                </div>
                <div class="atasan-approval-bars">
                    @foreach ($days as $day)
                        @php
                            $activityTotal = (int) ($activityCounts[$day->toDateString()] ?? 0);
                        @endphp
                        <div class="{{ $day->isToday() ? 'is-today' : '' }} {{ $activityTotal > 0 ? 'has-activity' : '' }}">
                            <span style="height: {{ max(5, round(($activityTotal / $maxActivity) * 42)) }}px"></span>
                            <small>{{ $day->translatedFormat('D') }}</small>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="atasan-approval-statuses">
                <div><small>Menunggu</small><strong>{{ $waitingCount }}</strong></div>
                <div><small>Disetujui</small><strong>{{ $approvedCount }}</strong></div>
                <div><small>Ditolak</small><strong>{{ $rejectedCount }}</strong></div>
            </div>
        </section>

        <div class="atasan-approval-tabs">
            @foreach (['diajukan' => 'Diajukan', 'disetujui' => 'Disetujui', 'ditolak' => 'Ditolak', 'draft' => 'Draft'] as $key => $label)
                <a class="{{ $statusAktif === $key ? 'active' : '' }}"
                    href="{{ route($routePrefix . '.laporan.index', array_merge(request()->query(), ['status' => $key])) }}">{{ $label }}
                    <span>{{ (int) ($statusCounts[$key] ?? 0) }}</span></a>
            @endforeach
        </div>

        <form class="atasan-approval-search" action="{{ route($routePrefix . '.laporan.index') }}" method="GET">
            <input type="hidden" name="status" value="{{ $statusAktif }}">
            <label class="atasan-approval-date">
                <i class="fa-regular fa-calendar" aria-hidden="true"></i>
                <input type="month" name="bulan" value="{{ $bulanAktif }}" aria-label="Pilih bulan laporan">
            </label>
            <label class="atasan-approval-search-field">
                <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                <input type="search" name="q" value="{{ $filters['q'] ?? '' }}"
                    placeholder="Cari pegawai, jabatan, atau tugas..." aria-label="Cari laporan">
            </label>
            <button type="submit">Terapkan</button>
            @if (!empty($filters['q']) || $bulanAktif !== now()->format('Y-m'))
                <a class="atasan-approval-clear"
                    href="{{ route($routePrefix . '.laporan.index', ['status' => $statusAktif]) }}">Reset</a>
            @endif
        </form>
        <div class="atasan-approval-list-heading">
            <h2>Laporan {{ $bulanLabel }}</h2><span>{{ $laporans->total() }} laporan ditemukan</span>
        </div>

        <div class="atasan-approval-list">
            @forelse ($laporans as $laporan)
                <article class="atasan-report-card">
                    <div class="atasan-report-top"><span
                            class="atasan-report-avatar">{{ strtoupper(substr($laporan->pegawai->nama, 0, 2)) }}</span>
                        <div>
                            <h3>{{ $laporan->pegawai->nama }}</h3>
                            <p>{{ $laporan->pegawai->jabatan->divisi->nama_divisi ?? '-' }}</p>
                        </div><span
                            class="atasan-report-status status-{{ $laporan->status }}">{{ ucfirst($laporan->status) }}</span>
                    </div>
                    <div class="atasan-report-body">
                        <div><span>Tanggal
                                Kegiatan</span><strong>{{ \Carbon\Carbon::parse($laporan->tanggal)->translatedFormat('d F Y') }}</strong>
                        </div>
                        <div><span>Jabatan</span><strong>{{ $laporan->pegawai->jabatan->nama_jabatan ?? '-' }}</strong>
                        </div>
                        <p><b>Uraian:</b>
                            {{ \Illuminate\Support\Str::limit($laporan->catatan ?: 'Laporan kegiatan harian pegawai.', 150) }}
                        </p>
                    </div>
                    <div class="atasan-report-footer"><a href="{{ route($routePrefix . '.laporan.show', $laporan) }}">Detail <i
                                class="fa-solid fa-chevron-right"></i></a>
                        @if ($laporan->status === 'diajukan')
                            <form action="{{ route($routePrefix . '.laporan.approve', $laporan) }}" method="POST"
                                onsubmit="return confirm('Apakah Anda yakin ingin menyetujui laporan ini?');">@csrf<button
                                    type="submit"><i class="fa-solid fa-check"></i> Setujui</button></form><a
                                class="reject" href="{{ route($routePrefix . '.laporan.show', $laporan) }}#reject">Tolak</a>
                        @endif
                    </div>
                </article>
            @empty
                <div class="atasan-approval-empty"><i class="fa-regular fa-folder-open"></i>
                    <h3>Belum Ada Laporan</h3>
                    <p>Tidak ada laporan berstatus <strong>"{{ ucfirst($statusAktif) }}"</strong>
                        pada bulan <strong>{{ $bulanLabel }}</strong>
                        @if (!empty($filters['q']))
                            yang cocok dengan filter ini.
                        @else
                            saat ini.
                        @endif
                    </p>
                    <a href="{{ route($routePrefix . '.laporan.index', ['status' => 'disetujui']) }}">Buka Laporan
                        Disetujui</a>
                </div>
            @endforelse
        </div>
        <div class="atasan-team-pagination">{{ $laporans->links('pagination::bootstrap-5') }}</div>
    </div>
@endsection

@push('styles')
    <style>
        body.role-atasan .atasan-approval-page {
            padding: 1rem;
            color: #17213b
        }

        .atasan-approval-heading h1 {
            margin: 0;
            font-size: 1.3rem;
            font-weight: 800
        }

        .atasan-approval-heading h1 span {
            margin-left: .3rem;
            padding: .35rem .55rem;
            color: #a85b00;
            background: #fff1bf;
            border-radius: 14px;
            font-size: .7rem;
            vertical-align: middle
        }

        .atasan-approval-heading p {
            margin: .35rem 0 1rem;
            color: #71839e;
            font-size: .78rem;
            line-height: 1.5
        }

        .atasan-approval-summary {
            margin-bottom: 1rem;
            padding: 1rem;
            background: #fff;
            border: 1px solid #dfe7f0;
            border-radius: 20px;
            box-shadow: 0 4px 14px rgba(30, 50, 80, .05)
        }

        .atasan-approval-summary-heading,
        .atasan-approval-activity-heading {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: .6rem
        }

        .atasan-approval-summary-heading {
            padding-bottom: .75rem;
            border-bottom: 1px solid #edf1f5
        }

        .atasan-approval-summary-heading h1 {
            margin: 0;
            font-size: .92rem
        }

        .atasan-approval-summary-heading p {
            margin: .2rem 0 0;
            color: #91a1b8;
            font-size: .68rem
        }

        .atasan-approval-summary-heading>span {
            padding: .4rem .55rem;
            color: #08a77f;
            background: #effcf7;
            border: 1px solid #9de7c4;
            border-radius: 999px;
            font-size: .62rem;
            font-weight: 700;
            white-space: nowrap
        }

        .atasan-approval-summary-heading>span i {
            font-size: .4rem
        }

        .atasan-approval-summary-progress {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-top: .85rem;
            padding: .75rem;
            background: #f8fafc;
            border: 1px solid #edf1f5;
            border-radius: 15px
        }

        .atasan-approval-ring {
            position: relative;
            display: flex;
            flex: 0 0 82px;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 82px;
            height: 82px;
            background: conic-gradient(#079ed7 var(--progress), #e5f2f8 0);
            border-radius: 50%
        }

        .atasan-approval-ring::after {
            position: absolute;
            width: 62px;
            height: 62px;
            content: "";
            background: #fff;
            border-radius: 50%
        }

        .atasan-approval-ring strong,
        .atasan-approval-ring small {
            position: relative;
            z-index: 1
        }

        .atasan-approval-ring strong {
            color: #26344d;
            font-size: 1.15rem;
            line-height: 1
        }

        .atasan-approval-ring small {
            margin-top: .2rem;
            color: #078ac8;
            font-size: .52rem
        }

        .atasan-approval-breakdown {
            display: grid;
            flex: 1;
            gap: .65rem;
            min-width: 0;
            color: #526480;
            font-size: .7rem
        }

        .atasan-approval-breakdown div {
            display: flex;
            align-items: center;
            gap: .4rem
        }

        .atasan-approval-breakdown i {
            width: 8px;
            height: 8px;
            background: #079ed7;
            border-radius: 50%
        }

        .atasan-approval-breakdown i.pending {
            background: #f5b51b
        }

        .atasan-approval-breakdown span {
            flex: 1
        }

        .atasan-approval-breakdown b {
            color: #26344d;
            white-space: nowrap
        }

        .atasan-approval-activity {
            margin-top: .75rem;
            padding: .7rem;
            background: #f8fafc;
            border: 1px solid #edf1f5;
            border-radius: 14px
        }

        .atasan-approval-activity-heading {
            align-items: center
        }

        .atasan-approval-activity-heading strong {
            color: #71829c;
            font-size: .6rem
        }

        .atasan-approval-activity-heading span {
            color: #078ac8;
            font-size: .62rem
        }

        .atasan-approval-bars {
            display: flex;
            align-items: flex-end;
            justify-content: space-around;
            height: 67px;
            margin-top: .35rem
        }

        .atasan-approval-bars>div {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-end;
            gap: .25rem;
            height: 100%
        }

        .atasan-approval-bars span {
            display: block;
            width: 14px;
            min-height: 5px;
            background: #079ed7;
            border-radius: 3px 3px 0 0
        }

        .atasan-approval-bars>div.is-today span {
            background: #078ac8
        }

        .atasan-approval-bars>div:not(.has-activity):not(.is-today) span {
            background: #dfe7ef
        }

        .atasan-approval-bars small {
            color: #71829c;
            font-size: .55rem
        }

        .atasan-approval-bars>div.is-today small {
            color: #078ac8;
            font-weight: 700
        }

        .atasan-approval-statuses {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: .5rem;
            margin-top: .75rem
        }

        .atasan-approval-statuses>div {
            padding: .55rem;
            text-align: center;
            background: #fffdf5;
            border: 1px solid #f7dfa1;
            border-radius: 12px
        }

        .atasan-approval-statuses>div:nth-child(2) {
            background: #f2fff9;
            border-color: #b8efd8
        }

        .atasan-approval-statuses>div:nth-child(3) {
            background: #fff6f7;
            border-color: #ffd9df
        }

        .atasan-approval-statuses small,
        .atasan-approval-statuses strong {
            display: block
        }

        .atasan-approval-statuses small {
            color: #71829c;
            font-size: .6rem
        }

        .atasan-approval-statuses strong {
            margin-top: .2rem;
            color: #b57900;
            font-size: .95rem
        }

        .atasan-approval-statuses>div:nth-child(2) strong {
            color: #08a77f
        }

        .atasan-approval-statuses>div:nth-child(3) strong {
            color: #e2465d
        }

        .atasan-approval-tabs {
            display: flex;
            gap: .45rem;
            margin-bottom: .8rem;
            overflow-x: auto
        }

        .atasan-approval-tabs a {
            flex: 0 0 auto;
            padding: .6rem .8rem;
            color: #536682;
            background: #fff;
            border: 1px solid #dfe7f0;
            border-radius: 20px;
            text-decoration: none;
            font-size: .72rem
        }

        .atasan-approval-tabs a.active {
            color: #fff;
            background: #078ac8;
            border-color: #078ac8
        }

        .atasan-approval-tabs a span {
            margin-left: .2rem
        }

        .atasan-approval-search {
            display: flex;
            align-items: center;
            gap: .5rem;
            padding: .65rem .75rem;
            background: #fff;
            border: 1px solid #dfe7f0;
            border-radius: 14px
        }

        .atasan-approval-search-field,
        .atasan-approval-date {
            display: flex;
            align-items: center;
            gap: .5rem;
            min-width: 0;
        }

        .atasan-approval-search-field {
            flex: 1;
        }

        .atasan-approval-search i {
            color: #8fa1b9
        }

        .atasan-approval-search-field input {
            min-width: 0;
            flex: 1;
            border: 0;
            outline: 0;
            font-size: .76rem
        }

        .atasan-approval-date {
            padding: .4rem .5rem;
            border: 1px solid #dfe7f0;
            border-radius: 8px;
        }

        .atasan-approval-date input {
            width: 120px;
            min-width: 0;
            padding: 0;
            color: #536682;
            background: transparent;
            border: 0;
            outline: 0;
            font-size: .65rem;
        }

        .atasan-approval-search button,
        .atasan-approval-clear {
            padding: .45rem .55rem;
            color: #fff;
            background: #078ac8;
            border: 1px solid #dfe7f0;
            border-radius: 8px;
            font-size: .65rem;
            font-weight: 700;
            text-decoration: none;
            white-space: nowrap;
        }

        .atasan-approval-search button {
            border-color: #078ac8;
        }

        .atasan-approval-clear {
            color: #536682;
            background: #fff;
        }

        .atasan-approval-list-heading {
            display: flex;
            justify-content: space-between;
            margin: 1.2rem .1rem .65rem
        }

        .atasan-approval-list-heading h2 {
            margin: 0;
            font-size: .82rem;
            text-transform: uppercase
        }

        .atasan-approval-list-heading span {
            color: #8a9ab4;
            font-size: .68rem
        }

        .atasan-approval-list {
            display: grid;
            gap: .75rem
        }

        .atasan-report-card {
            overflow: hidden;
            background: #fff;
            border: 1px solid #dfe7f0;
            border-radius: 19px;
            box-shadow: 0 4px 12px #2434550b
        }

        .atasan-report-top {
            display: flex;
            align-items: center;
            gap: .65rem;
            padding: 1rem
        }

        .atasan-report-avatar {
            display: grid;
            place-items: center;
            width: 44px;
            height: 44px;
            flex: 0 0 44px;
            color: #078ac8;
            background: #eef8ff;
            border-radius: 50%;
            font-weight: 800
        }

        .atasan-report-top div {
            min-width: 0;
            flex: 1
        }

        .atasan-report-top h3 {
            margin: 0;
            font-size: .84rem
        }

        .atasan-report-top p {
            margin: .2rem 0 0;
            color: #7789a4;
            font-size: .68rem
        }

        .atasan-report-status {
            padding: .3rem .5rem;
            border-radius: 9px;
            font-size: .62rem;
            font-weight: 700
        }

        .status-diajukan {
            color: #a85b00;
            background: #fff4ca
        }

        .status-disetujui {
            color: #078653;
            background: #eafbf4
        }

        .status-ditolak {
            color: #c6314a;
            background: #fff0f3
        }

        .status-draft {
            color: #667994;
            background: #f1f5f9
        }

        .atasan-report-body {
            margin: 0 1rem;
            padding: .7rem;
            background: #f8fafc;
            border: 1px solid #edf1f6;
            border-radius: 13px
        }

        .atasan-report-body>div {
            display: flex;
            justify-content: space-between;
            gap: .5rem;
            font-size: .7rem
        }

        .atasan-report-body>div+div {
            margin-top: .45rem
        }

        .atasan-report-body span {
            color: #7789a4
        }

        .atasan-report-body strong {
            color: #27344c;
            text-align: right
        }

        .atasan-report-body p {
            margin: .65rem 0 0;
            color: #526480;
            font-size: .74rem;
            line-height: 1.5
        }

        .atasan-report-footer {
            display: flex;
            align-items: center;
            gap: .5rem;
            padding: .8rem 1rem
        }

        .atasan-report-footer a,
        .atasan-report-footer button {
            padding: .5rem .65rem;
            border: 0;
            border-radius: 9px;
            color: #078ac8;
            background: #eef8ff;
            text-decoration: none;
            font-size: .68rem;
            font-weight: 700
        }

        .atasan-report-footer a:first-child {
            margin-right: auto;
            background: none
        }

        .atasan-report-footer button {
            color: #fff;
            background: #078ac8
        }

        .atasan-report-footer .reject {
            color: #d63752;
            background: #fff0f3
        }

        .atasan-approval-empty {
            padding: 2rem 1rem;
            text-align: center;
            background: #fff;
            border: 1px solid #dfe7f0;
            border-radius: 20px
        }

        .atasan-approval-empty>i {
            display: grid;
            place-items: center;
            width: 72px;
            height: 72px;
            margin: 0 auto .8rem;
            color: #078ac8;
            background: #eef8ff;
            border-radius: 20px;
            font-size: 2rem
        }

        .atasan-approval-empty h3 {
            margin: 0;
            font-size: 1rem
        }

        .atasan-approval-empty p {
            color: #71839e;
            font-size: .78rem;
            line-height: 1.5
        }

        .atasan-approval-empty a {
            display: block;
            margin: 1rem auto .8rem;
            padding: .75rem;
            color: #078ac8;
            background: #eef8ff;
            border-radius: 12px;
            text-decoration: none;
            font-size: .75rem;
            font-weight: 700
        }

        .atasan-approval-empty span {
            color: #9aabc1;
            font-size: .72rem
        }

        @media (max-width: 520px) {
            .atasan-approval-search {
                flex-wrap: wrap;
            }

            .atasan-approval-search-field {
                flex-basis: 100%;
            }

            .atasan-approval-date {
                flex: 1;
            }

            .atasan-approval-date input {
                width: 100%;
            }
        }

        @media (min-width: 768px) {
            body.role-atasan .atasan-approval-page {
                width: 100%;
                max-width: 1480px;
                margin: 0 auto;
                padding: 1.25rem clamp(1.25rem, 2vw, 2rem) 2rem;
            }

            .atasan-approval-summary {
                display: grid;
                grid-template-columns: minmax(230px, .75fr) minmax(0, 1.5fr);
                gap: .75rem;
                padding: 1rem;
                border-radius: 12px;
            }

            .atasan-approval-summary-heading,
            .atasan-approval-statuses {
                grid-column: 1 / -1;
            }

            .atasan-approval-summary-progress,
            .atasan-approval-activity {
                min-width: 0;
                margin: 0;
            }

            .atasan-approval-summary-progress {
                justify-content: center;
            }

            .atasan-approval-activity {
                display: flex;
                flex-direction: column;
                justify-content: space-between;
            }

            .atasan-approval-bars {
                flex: 1;
                min-height: 67px;
            }

            .atasan-approval-statuses {
                margin-top: 0;
            }

            .atasan-approval-statuses>div {
                padding: .75rem;
                border-radius: 10px;
            }

            .atasan-approval-tabs {
                margin-bottom: .65rem;
            }

            .atasan-approval-tabs a {
                padding: .5rem .75rem;
                border-radius: 999px;
            }

            .atasan-approval-search {
                border-radius: 10px;
            }

            .atasan-approval-list {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                grid-auto-rows: 310px;
                align-items: stretch;
            }

            body.role-atasan .atasan-report-card {
                display: flex;
                flex-direction: column;
                height: 100%;
                min-height: 0;
                border-radius: 12px;
            }

            .atasan-report-top {
                min-height: 62px;
                padding: .85rem;
            }

            .atasan-report-top h3,
            .atasan-report-top p {
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .atasan-report-avatar {
                width: 38px;
                height: 38px;
                flex-basis: 38px;
                border-radius: 12px;
            }

            .atasan-report-body {
                flex: 1;
                min-height: 0;
                margin: 0 .85rem;
                border-radius: 10px;
            }

            .atasan-report-body strong {
                display: -webkit-box;
                overflow: hidden;
                -webkit-box-orient: vertical;
                -webkit-line-clamp: 2;
            }

            .atasan-report-body p {
                display: -webkit-box;
                overflow: hidden;
                -webkit-box-orient: vertical;
                -webkit-line-clamp: 3;
            }

            .atasan-report-footer {
                min-height: 58px;
                margin-top: auto;
                padding: .7rem .85rem;
            }

            .atasan-report-footer a,
            .atasan-report-footer button {
                border-radius: 7px;
            }
        }

        @media (min-width: 1200px) {
            .atasan-approval-list {
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: .85rem;
            }

            .atasan-approval-summary {
                grid-template-columns: minmax(250px, .7fr) minmax(0, 1.5fr);
                gap: .85rem;
                padding: 1.1rem;
            }
        }
    </style>
@endpush
