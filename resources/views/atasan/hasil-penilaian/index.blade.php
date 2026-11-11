@extends('layouts.app')
@section('title', 'Hasil Penilaian')
@section('content')
    @php
        $items = $hasilPenilaians->getCollection();
        $avg = $items->avg('nilai_akhir');
        $selesai = $items->filter(fn($hasil) => $hasil->exists && $hasil->nilai_akhir !== null)->count();
    @endphp
    <div class="hasil-page">
        <div class="atasan-mobile-breadcrumb">
            <a href="{{ route('atasan.dashboard') }}" aria-label="Kembali ke dashboard"><i
                    class="fa-solid fa-arrow-left"></i></a>
            <div><span>Atasan</span><strong>Hasil Penilaian</strong></div>
        </div>


        @php
            $totalPegawai = $hasilPenilaians->total();
            $tuntasPercent = $totalPegawai > 0 ? round(($selesai / $totalPegawai) * 100) : 0;
            $predikatRata =
                $avg === null
                    ? 'Belum tersedia'
                    : ($avg >= 90
                        ? 'Sangat Baik'
                        : ($avg >= 75
                            ? 'Baik'
                            : 'Perlu Perbaikan'));
        @endphp

        <section class="hasil-performance-card">
            <div class="hasil-performance-heading">
                <div>
                    <h1>Capaian Kinerja Eksekutif</h1>
                    <p>{{ $periode->nama_periode ?? 'Agregat Tim & Evaluasi Berkala' }}</p>
                </div>
                <span><i class="fa-solid fa-circle"></i> {{ $predikatRata }}</span>
            </div>

            <div class="hasil-performance-main">
                <div>
                    <small>RATA-RATA NILAI AKHIR</small>
                    <strong>{{ $avg !== null ? number_format($avg, 2, ',', '.') : '-' }} <em>/ 100 pt</em></strong>
                    <b><i class="fa-solid fa-arrow-up"></i> {{ $avg !== null ? $predikatRata : 'Belum dinilai' }}</b>
                </div>
                <div class="hasil-performance-ring" style="--progress: {{ min(100, max(0, $avg ?? 0)) * 3.6 }}deg">
                    <strong>{{ $avg !== null ? number_format($avg, 1, ',', '.') : '-' }}%</strong>
                    <small>Indeks</small>
                </div>
            </div>

            <div class="hasil-performance-trend">
                <span><i class="fa-solid fa-circle"></i> Periode Penilaian</span>
                <strong>{{ $periode->nama_periode ?? 'Belum tersedia' }}</strong>
            </div>
        </section>

        <div class="hasil-summary-cards">
            <article>
                <div><small>TUNTAS DINILAI</small><i class="fa-solid fa-check"></i></div>
                <strong>{{ $selesai }}<em>/ {{ $totalPegawai }} Pegawai</em></strong>
                <div class="hasil-summary-progress"><b style="width: {{ $tuntasPercent }}%"></b></div>
                <small class="summary-status">{{ $tuntasPercent }}% Selesai</small>
            </article>
            <article>
                <div><small>RERATA TUKIN</small><i class="fa-solid fa-sack-dollar"></i></div>
                <strong>Rp {{ number_format($items->avg('tukin_bruto') ?? 0, 0, ',', '.') }}</strong>
                <b>Bruto Resmi</b><small class="summary-status">Grade Golongan</small>
            </article>
        </div>



        <div class="hasil-search"><i class="fa-solid fa-magnifying-glass"></i><input type="search"
                placeholder="Cari nama pegawai atau predikat..."><i class="fa-solid fa-filter"></i></div>
        <div class="hasil-tabs"><span class="active">Semua ({{ $hasilPenilaians->total() }})</span><span>Sangat Baik
                ({{ $items->where('predikat', 'Sangat Baik')->count() }})</span><span>Baik
                ({{ $items->where('predikat', 'Baik')->count() }})</span><span>Butuh Perbaikan</span></div>
        <div class="hasil-list-title">
            <h2>Daftar Penilaian Pegawai ({{ $hasilPenilaians->total() }})</h2>
        </div>

        <div class="hasil-table-wrap">
            <table class="hasil-table">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Pegawai</th>
                        <th scope="col">Predikat</th>
                        <th scope="col">KPI Otomatis</th>
                        <th scope="col">Realisasi Laporan</th>
                        <th scope="col">Nilai Akhir</th>
                        <th scope="col">Tukin Bruto</th>
                        <th scope="col" class="hasil-table-action-heading">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($hasilPenilaians as $hasil)
                        <tr>
                            <td><span
                                    class="hasil-table-number">{{ $loop->iteration + ($hasilPenilaians->currentPage() - 1) * $hasilPenilaians->perPage() }}</span>
                            </td>
                            <td>
                                <div class="hasil-table-person">
                                    <span
                                        class="hasil-table-avatar">{{ strtoupper(substr($hasil->pegawai->nama, 0, 2)) }}</span>
                                    <span><strong>{{ $hasil->pegawai->nama }}</strong><small>{{ $hasil->pegawai->jabatan->nama_jabatan ?? '-' }}</small></span>
                                </div>
                            </td>
                            <td><span class="hasil-table-predicate">{{ $hasil->predikat ?? 'Belum dinilai' }}</span></td>
                            <td><strong class="hasil-table-score">{{ $hasil->rata_skor_kpi ?? '-' }}</strong><small
                                    class="hasil-table-hint">Dari laporan disetujui</small></td>
                            <td><strong class="hasil-table-auto">Otomatis</strong><small class="hasil-table-hint">Tanpa
                                    input atasan</small></td>
                            <td><strong class="hasil-table-score">{{ $hasil->nilai_akhir ?? '-' }}</strong><small
                                    class="hasil-table-hint">Skor KPI</small></td>
                            <td><strong class="hasil-table-money">Rp
                                    {{ number_format($hasil->tukin_bruto ?? 0, 0, ',', '.') }}</strong><small
                                    class="hasil-table-draft">{{ !$hasil->exists ? 'Belum dinilai' : ($hasil->status === 'dikirim' ? 'Dikirim' : 'Draft') }}</small>
                            </td>
                            <td><a class="hasil-table-detail"
                                    href="{{ route('atasan.hasil-penilaian.show', ['pegawai' => $hasil->pegawai, 'periode_id' => $periode?->id]) }}">Detail
                                    <i class="fa-solid fa-chevron-right"></i></a></td>
                        </tr>
                    @empty
                        <tr>
                            <td class="hasil-table-empty" colspan="8">Belum ada hasil penilaian untuk periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="hasil-list">
            @forelse($hasilPenilaians as $hasil)
                <article class="hasil-card">
                    <div class="hasil-person"><span
                            class="hasil-avatar">{{ strtoupper(substr($hasil->pegawai->nama, 0, 2)) }}</span>
                        <div>
                            <h3>{{ $hasil->pegawai->nama }}
                                <small>#{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</small>
                            </h3>

                            <p>{{ $hasil->pegawai->jabatan->nama_jabatan ?? '-' }}</p>
                        </div><b class="hasil-predikat">{{ $hasil->predikat ?? 'Belum dinilai' }}</b>
                    </div>
                    <div class="hasil-values">
                        <div><small>KPI OTOMATIS</small><strong>{{ $hasil->rata_skor_kpi ?? '-' }}</strong><span>Dari
                                laporan disetujui</span></div>
                        <div><small>REALISASI LAPORAN</small><strong>Otomatis</strong><span>Tanpa input atasan</span></div>
                        <div><small>NILAI AKHIR</small><strong>{{ $hasil->nilai_akhir ?? '-' }}</strong><span>Skor
                                KPI</span></div>
                    </div>
                    <div class="hasil-card-footer">
                        <div><small>TUKIN BRUTO</small><strong>Rp
                                {{ number_format($hasil->tukin_bruto ?? 0, 0, ',', '.') }}</strong><span>{{ !$hasil->exists ? 'Belum dinilai' : ($hasil->status === 'dikirim' ? 'Dikirim' : 'Draft') }}</span>
                        </div><a
                            href="{{ route('atasan.hasil-penilaian.show', ['pegawai' => $hasil->pegawai, 'periode_id' => $periode?->id]) }}">Detail
                            <i class="fa-solid fa-chevron-right"></i></a>
                    </div>
                </article>
            @empty <div class="hasil-empty"><i class="fa-regular fa-folder-open"></i><strong>Belum ada hasil
                        penilaian</strong><span>Data untuk periode ini belum tersedia.</span></div>
            @endforelse
        </div>
        <div class="hasil-pagination">{{ $hasilPenilaians->links('pagination::bootstrap-5') }}</div>
        <aside class="hasil-note"><i class="fa-solid fa-circle-info"></i>
            <div><strong>Catatan Tukin & Evaluasi</strong>
                <p>Perhitungan otomatis berdasarkan capaian KPI dari laporan pegawai yang telah disetujui. Atasan tidak
                    mengisi nilai capaian. KPI atau kompetensi yang belum memiliki nilai dihitung 0 pada hasil sementara.
                </p>
            </div>
        </aside>
    </div>
@endsection



@push('styles')
    <style>
        body.role-atasan .hasil-page {
            box-sizing: border-box;
            width: 100%;
            max-width: 560px;
            margin: auto;
            padding: 1rem;
            color: #16233d;
            /* padding-bottom: calc(var(--bottom-nav-height) + 1.5rem) */
        }

        .hasil-heading {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: .7rem;
            margin: .8rem 0 1rem
        }

        .hasil-heading h1 {
            margin: 0;
            font-size: 1.25rem
        }

        .hasil-heading p {
            margin: .25rem 0;
            color: #71829c;
            font-size: .78rem
        }

        .hasil-heading p strong {
            color: #16233d
        }

        .hasil-heading>span {
            padding: .45rem .6rem;
            color: #078653;
            background: #eafbf4;
            border: 1px solid #8ce5c4;
            border-radius: 14px;
            font-size: .65rem;
            white-space: nowrap
        }

        .hasil-summary {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: .55rem
        }

        .hasil-summary>div {
            min-width: 0;
            min-height: 145px;
            padding: .85rem;
            background: #fff;
            border: 1px solid #dfe7f0;
            border-radius: 18px
        }

        .hasil-summary small,
        .hasil-summary em {
            display: block;
            color: #8495ae;
            font-size: .62rem;
            font-style: normal
        }

        .hasil-summary strong {
            display: block;
            margin: .65rem 0 .45rem;
            color: #0875ad;
            font-size: 1.35rem;
            overflow-wrap: anywhere
        }

        .hasil-summary strong i {
            color: #8495ae;
            font-size: .7rem;
            font-style: normal;
            font-weight: 400
        }

        .hasil-summary b {
            display: inline-block;
            padding: .25rem .35rem;
            color: #078653;
            background: #eafbf4;
            border-radius: 5px;
            font-size: .58rem
        }

        .hasil-summary .money {
            color: #16233d;
            font-size: .95rem
        }

        .hasil-progress {
            display: block;
            height: 7px;
            margin: .7rem 0;
            background: #e8eef4;
            border-radius: 9px
        }

        .hasil-progress b {
            display: block;
            height: 100%;
            background: #0bb889;
            border-radius: 9px
        }

        .hasil-search {
            display: flex;
            align-items: center;
            gap: .6rem;
            margin: 1.2rem 0 .8rem;
            padding: .75rem 1rem;
            background: #fff;
            border: 1px solid #dfe7f0;
            border-radius: 15px;
            color: #90a1ba
        }

        .hasil-search input {
            width: 100%;
            min-width: 0;
            border: 0;
            outline: 0;
            font-size: .75rem
        }

        .hasil-tabs {
            display: flex;
            gap: .45rem;
            overflow: auto;
            padding-bottom: .2rem
        }

        .hasil-tabs span {
            flex: 0 0 auto;
            padding: .55rem .75rem;
            border: 1px solid #dfe7f0;
            border-radius: 12px;
            color: #526480;
            font-size: .68rem;
            white-space: nowrap
        }

        .hasil-tabs .active {
            color: #fff;
            background: #078ac8;
            border-color: #078ac8
        }

        .hasil-list-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: .5rem;
            margin: 1.2rem .1rem .7rem
        }

        .hasil-list-title h2 {
            margin: 0;
            font-size: .82rem
        }

        .hasil-list-title a {
            color: #0875ad;
            text-decoration: none;
            font-size: .7rem;
            font-weight: 700;
            white-space: nowrap
        }

        .hasil-list {
            display: grid;
            gap: .75rem
        }

        .hasil-card {
            overflow: hidden;
            background: #fff;
            border: 1px solid #dfe7f0;
            border-radius: 18px
        }

        .hasil-person {
            display: flex;
            align-items: flex-start;
            gap: .65rem;
            padding: 1rem
        }

        .hasil-avatar {
            display: grid;
            place-items: center;
            width: 48px;
            height: 48px;
            flex: 0 0 48px;
            color: #fff;
            background: #079bd5;
            border-radius: 50%;
            font-weight: 700
        }

        .hasil-person>div {
            min-width: 0;
            flex: 1
        }

        .hasil-person h3 {
            margin: 0;
            font-size: .86rem;
            overflow-wrap: anywhere
        }

        .hasil-person h3 small {
            color: #899bb4;
            font-size: .68rem;
            font-weight: 400
        }

        .hasil-person p {
            margin: .2rem 0 0;
            color: #71829c;
            font-size: .67rem;
            overflow-wrap: anywhere
        }

        .hasil-predikat {
            padding: .38rem .5rem;
            color: #078653;
            background: #eafbf4;
            border: 1px solid #75dfb8;
            border-radius: 9px;
            font-size: .62rem;
            white-space: nowrap
        }

        .hasil-values {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: .5rem;
            padding: .8rem 1rem;
            background: #f7faff;
            border-top: 1px solid #edf1f6;
            border-bottom: 1px solid #edf1f6
        }

        .hasil-values div {
            min-width: 0;
            padding: .65rem .3rem;
            background: #fff;
            border: 1px solid #e4eaf1;
            border-radius: 12px;
            text-align: center
        }

        .hasil-values small,
        .hasil-values span {
            display: block;
            color: #8c9db5;
            font-size: .57rem;
            overflow-wrap: anywhere
        }

        .hasil-values strong {
            display: block;
            margin: .3rem 0;
            color: #16233d;
            font-size: 1rem
        }

        .hasil-values span {
            color: #079b74
        }

        .hasil-card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: .6rem;
            padding: .9rem 1rem
        }

        .hasil-card-footer small {
            display: block;
            color: #90a1ba;
            font-size: .6rem
        }

        .hasil-card-footer strong {
            font-size: 1.05rem
        }

        .hasil-card-footer span {
            margin-left: .35rem;
            padding: .25rem .35rem;
            color: #078653;
            background: #eafbf4;
            border-radius: 5px;
            font-size: .58rem
        }

        .hasil-card-footer a {
            padding: .65rem .85rem;
            color: #fff;
            background: #078ac8;
            border-radius: 11px;
            text-decoration: none;
            font-size: .7rem;
            font-weight: 700
        }

        .hasil-note {
            display: flex;
            gap: .7rem;
            margin-top: 1rem;
            padding: 1rem;
            color: #0875ad;
            background: #eff9ff;
            border: 1px solid #a7ddfb;
            border-radius: 18px
        }

        .hasil-note>i {
            font-size: 1.1rem
        }

        .hasil-note strong {
            display: block;
            font-size: .78rem
        }

        .hasil-note p {
            margin: .35rem 0 0;
            color: #477a9e;
            font-size: .68rem;
            line-height: 1.5
        }

        .hasil-empty {
            display: grid;
            place-items: center;
            gap: .35rem;
            padding: 2.5rem 1rem;
            color: #8394ac;
            text-align: center
        }

        .hasil-empty i {
            font-size: 2rem
        }

        .hasil-empty strong {
            color: #526480
        }

        .hasil-empty span {
            font-size: .7rem
        }

        .hasil-pagination {
            margin-top: .8rem
        }

        .hasil-pagination .pagination {
            justify-content: center;
            flex-wrap: wrap;
            gap: .2rem
        }

        .hasil-pagination svg {
            width: 14px;
            height: 14px
        }

        .hasil-performance-card {
            padding: 1rem;
            background: #fff;
            border: 1px solid #dfe7f0;
            border-radius: 20px;
            box-shadow: 0 4px 14px rgba(30, 50, 80, .05)
        }

        .hasil-performance-heading,
        .hasil-performance-trend,
        .hasil-performance-main {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: .7rem
        }

        .hasil-performance-heading {
            padding-bottom: .75rem;
            border-bottom: 1px solid #edf1f5
        }

        .hasil-performance-heading h1 {
            margin: 0;
            font-size: .88rem
        }

        .hasil-performance-heading p {
            margin: .2rem 0 0;
            color: #91a1b8;
            font-size: .65rem
        }

        .hasil-performance-heading>span {
            padding: .38rem .55rem;
            color: #08a77f;
            background: #effcf7;
            border: 1px solid #9de7c4;
            border-radius: 999px;
            font-size: .6rem;
            font-weight: 700;
            white-space: nowrap
        }

        .hasil-performance-heading>span i {
            font-size: .4rem
        }

        .hasil-performance-main {
            align-items: center;
            margin-top: .85rem
        }

        .hasil-performance-main small {
            color: #71829c;
            font-size: .6rem;
            font-weight: 600
        }

        .hasil-performance-main>div:first-child {
            min-width: 0
        }

        .hasil-performance-main>div:first-child>strong {
            display: block;
            margin: .3rem 0 .35rem;
            color: #14223d;
            font-size: 1.7rem;
            line-height: 1
        }

        .hasil-performance-main>div:first-child>strong em {
            color: #91a1b8;
            font-size: .62rem;
            font-style: normal;
            font-weight: 400
        }

        .hasil-performance-main>div:first-child>b {
            display: inline-block;
            padding: .25rem .4rem;
            color: #08a77f;
            background: #eafbf4;
            border-radius: 5px;
            font-size: .58rem
        }

        .hasil-performance-ring {
            position: relative;
            display: flex;
            flex: 0 0 72px;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 72px;
            height: 72px;
            background: conic-gradient(#079ed7 var(--progress), #e7f2f8 0);
            border-radius: 50%
        }

        .hasil-performance-ring::after {
            position: absolute;
            width: 54px;
            height: 54px;
            content: "";
            background: #fff;
            border-radius: 50%
        }

        .hasil-performance-ring strong,
        .hasil-performance-ring small {
            position: relative;
            z-index: 1
        }

        .hasil-performance-ring strong {
            color: #26344d;
            font-size: .82rem
        }

        .hasil-performance-ring small {
            color: #91a1b8;
            font-size: .5rem
        }

        .hasil-performance-trend {
            align-items: center;
            margin-top: .75rem;
            padding-top: .65rem;
            color: #526480;
            border-top: 1px solid #edf1f5;
            font-size: .63rem
        }

        .hasil-performance-trend span i {
            margin-right: .25rem;
            color: #078dcc;
            font-size: .4rem
        }

        .hasil-performance-trend strong {
            color: #078dcc;
            font-size: .62rem
        }

        .hasil-summary-cards {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .6rem;
            margin-top: .65rem
        }

        .hasil-summary-cards article {
            min-width: 0;
            padding: .75rem;
            background: #fff;
            border: 1px solid #dfe7f0;
            border-radius: 16px;
            box-shadow: 0 3px 10px rgba(30, 50, 80, .04)
        }

        .hasil-summary-cards article>div:first-child {
            display: flex;
            align-items: center;
            justify-content: space-between
        }

        .hasil-summary-cards article>div:first-child small {
            color: #71829c;
            font-size: .58rem
        }

        .hasil-summary-cards article>div:first-child i {
            display: grid;
            place-items: center;
            width: 20px;
            height: 20px;
            color: #08a77f;
            background: #eafbf4;
            border-radius: 6px;
            font-size: .6rem
        }

        .hasil-summary-cards article:nth-child(2)>div:first-child i {
            color: #078dcc;
            background: #eaf5ff
        }

        .hasil-summary-cards article>strong {
            display: block;
            margin: .65rem 0 .4rem;
            color: #14223d;
            font-size: 1.15rem
        }

        .hasil-summary-cards article>strong em {
            color: #71829c;
            font-size: .62rem;
            font-style: normal;
            font-weight: 400
        }

        .hasil-summary-progress {
            height: 5px;
            overflow: hidden;
            background: #eaf0f4;
            border-radius: 99px
        }

        .hasil-summary-progress b {
            display: block;
            height: 100%;
            background: #08a77f;
            border-radius: inherit
        }

        .hasil-summary-cards article>b {
            display: inline-block;
            padding: .25rem .35rem;
            color: #078653;
            background: #eafbf4;
            border-radius: 5px;
            font-size: .55rem
        }

        .hasil-summary-cards .summary-status {
            display: block;
            margin-top: .35rem;
            color: #91a1b8;
            font-size: .55rem
        }

        .hasil-table-wrap {
            display: none;
        }

        @media(max-width:380px) {
            body.role-atasan .hasil-page {
                padding: .75rem;
                padding-bottom: calc(var(--bottom-nav-height) + 1rem)
            }

            .hasil-heading h1 {
                font-size: 1rem
            }

            .hasil-heading>span {
                font-size: .57rem
            }

            .hasil-summary {
                gap: .3rem
            }

            .hasil-summary>div {
                min-height: 132px;
                padding: .6rem
            }

            .hasil-summary strong {
                font-size: 1.05rem
            }

            .hasil-summary .money {
                font-size: .78rem
            }

            .hasil-summary small,
            .hasil-summary em {
                font-size: .55rem
            }

            .hasil-person {
                padding: .8rem
            }

            .hasil-predikat {
                font-size: .55rem
            }

            .hasil-values {
                padding: .7rem .8rem;
                gap: .3rem
            }

            .hasil-values strong {
                font-size: .85rem
            }

            .hasil-card-footer {
                padding: .8rem
            }

            .hasil-card-footer strong {
                font-size: .9rem
            }

            .hasil-card-footer a {
                padding: .55rem .65rem
            }
        }

        @media (min-width: 992px) {
            body.role-atasan .hasil-page {
                width: 100%;
                max-width: none;
                margin: 0;
                padding: 1.4rem clamp(1.25rem, 2.2vw, 2rem) 2rem;
                color: #1b2938;
            }

            .hasil-performance-card,
            .hasil-summary-cards article,
            .hasil-search,
            .hasil-table-wrap,
            .hasil-note {
                border-radius: 7px;
                box-shadow: none;
            }

            .hasil-performance-card {
                padding: 1rem 1.1rem;
            }

            .hasil-performance-heading h1 {
                color: #25364a;
                font-size: 1rem;
            }

            .hasil-performance-heading p {
                font-size: .67rem;
            }

            .hasil-performance-progress {
                gap: .9rem;
                padding: .8rem;
                border-radius: 6px;
            }

            .hasil-performance-ring {
                width: 86px;
                height: 86px;
                flex-basis: 86px;
            }

            .hasil-performance-ring::after {
                width: 64px;
                height: 64px;
            }

            .hasil-summary-cards {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: .75rem;
                margin-top: .75rem;
            }

            .hasil-summary-cards article {
                padding: .75rem .85rem;
            }

            .hasil-summary-cards article>strong {
                margin: .4rem 0 .3rem;
                font-size: 1rem;
            }

            .hasil-search {
                margin: .85rem 0 .55rem;
                padding: .55rem .7rem;
            }

            .hasil-tabs {
                gap: .35rem;
                margin-bottom: .65rem;
            }

            .hasil-tabs span {
                padding: .38rem .55rem;
                border-radius: 5px;
                font-size: .62rem;
            }

            .hasil-tabs .active {
                background: #078ac8;
            }

            .hasil-list-title {
                margin: .7rem .1rem .5rem;
            }

            .hasil-list-title h2 {
                color: #34445e;
                font-size: .75rem;
            }

            .hasil-list-title a {
                font-size: .63rem;
            }

            .hasil-table-wrap {
                display: block;
                width: 100%;
                overflow-x: auto;
                background: #fff;
                border: 1px solid #dfe7f0;
            }

            .hasil-table {
                width: 100%;
                min-width: 1050px;
                table-layout: fixed;
                border-collapse: separate;
                border-spacing: 0;
                color: #334155;
                font-size: .65rem;
            }

            .hasil-table th {
                padding: .68rem .55rem;
                color: #617185;
                background: #f7f9fb;
                border-bottom: 1px solid #e2e8ef;
                font-size: .56rem;
                font-weight: 700;
                text-align: left;
                text-transform: uppercase;
                line-height: 1.3;
            }

            .hasil-table th:nth-child(1) {
                width: 42px;
            }

            .hasil-table th:nth-child(2) {
                width: 22%;
            }

            .hasil-table th:nth-child(3) {
                width: 10%;
            }

            .hasil-table th:nth-child(4) {
                width: 12%;
            }

            .hasil-table th:nth-child(5) {
                width: 14%;
            }

            .hasil-table th:nth-child(6) {
                width: 10%;
            }

            .hasil-table th:nth-child(7) {
                width: 12%;
            }

            .hasil-table th:nth-child(8) {
                width: 110px;
            }

            .hasil-table td {
                padding: .6rem .55rem;
                border-bottom: 1px solid #edf1f5;
                vertical-align: middle;
            }

            .hasil-table tbody tr:last-child td {
                border-bottom: 0;
            }

            .hasil-table tbody tr:hover {
                background: #fafcfe;
            }

            .hasil-table-number {
                display: grid;
                place-items: center;
                width: 22px;
                height: 22px;
                color: #526480;
                background: #f0f4f8;
                border-radius: 4px;
                font-size: .58rem;
            }

            .hasil-table-person {
                display: flex;
                align-items: center;
                gap: .45rem;
                min-width: 0;
            }

            .hasil-table-avatar {
                display: grid;
                place-items: center;
                width: 32px;
                height: 32px;
                flex: 0 0 32px;
                color: #fff;
                background: #079bd5;
                border-radius: 8px;
                font-size: .58rem;
                font-weight: 700;
            }

            .hasil-table-person>span:last-child {
                display: block;
                min-width: 0;
            }

            .hasil-table-person strong,
            .hasil-table-person small,
            .hasil-table-hint,
            .hasil-table-draft {
                display: block;
            }

            .hasil-table-person strong {
                overflow: hidden;
                color: #26344d;
                font-size: .65rem;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .hasil-table-person small,
            .hasil-table-hint {
                margin-top: .14rem;
                color: #8494ab;
                font-size: .56rem;
                line-height: 1.3;
            }

            .hasil-table-predicate {
                display: inline-block;
                padding: .24rem .4rem;
                color: #07966b;
                background: #eafbf4;
                border-radius: 999px;
                font-size: .58rem;
                white-space: nowrap;
            }

            .hasil-table-score,
            .hasil-table-auto,
            .hasil-table-money {
                display: block;
                color: #26344d;
                font-size: .68rem;
                font-weight: 700;
                white-space: nowrap;
            }

            .hasil-table-auto {
                color: #07966b;
            }

            .hasil-table-money {
                color: #26344d;
            }

            .hasil-table-draft {
                width: max-content;
                margin-top: .16rem;
                padding: .18rem .32rem;
                color: #64748b;
                background: #f0f4f8;
                border-radius: 4px;
                font-size: .53rem;
            }

            .hasil-table-action-heading {
                text-align: center !important;
            }

            .hasil-table-detail {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: .25rem;
                min-height: 28px;
                padding: .3rem .45rem;
                color: #fff;
                background: #078ac8;
                border-radius: 5px;
                font-size: .58rem;
                font-weight: 700;
                text-decoration: none;
                white-space: nowrap;
            }

            .hasil-table-empty {
                height: 90px;
                color: #8494ab;
                text-align: center;
            }

            .hasil-list {
                display: none !important;
            }

            .hasil-pagination .pagination {
                justify-content: flex-end;
            }

            .hasil-note {
                margin-top: .7rem;
                padding: .7rem .8rem;
                border-radius: 6px;
            }

            .hasil-note p {
                font-size: .62rem;
            }
        }
    </style>
@endpush
