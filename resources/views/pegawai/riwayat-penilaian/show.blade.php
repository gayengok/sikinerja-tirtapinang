@extends('layouts.app')
@section('title', 'Detail Penilaian')
@section('content')
    @php
        $predicateClass = match ($hasil->predikat) {
            'Sangat Baik' => 'assessment-excellent',
            'Baik' => 'assessment-good',
            'Cukup' => 'assessment-fair',
            'Kurang' => 'assessment-poor',
            default => 'assessment-neutral',
        };
        $status = $hasil->status === 'dikirim' ? 'Dikirim' : 'Draft';
        $score = (float) ($hasil->nilai_akhir ?? 0);
    @endphp
    <div class="pegawai-page history-detail-page">
        <div class="history-detail-heading">
            <div class="assessment-breadcrumb">Dashboard <span>/</span> <a
                    href="{{ route('pegawai.riwayat-penilaian.index') }}">Riwayat Penilaian</a> <span>/</span>
                <strong>Detail</strong></div>
            <h1>Detail Penilaian</h1>
            <p>Rincian hasil evaluasi kinerja dan tunjangan Anda.</p>
        </div>
        <section class="history-profile-card">
            <div class="history-profile-main"><span
                    class="history-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</span>
                <div>
                    <h2>{{ auth()->user()->name }}</h2>
                    <p>NIP: {{ auth()->user()->pegawai->nip ?? '-' }}</p>
                </div><b>{{ $status }}</b>
            </div>
            <div class="history-profile-lines">
                <div><i
                        class="fa-regular fa-briefcase"></i><span>{{ auth()->user()->pegawai->jabatan->nama_jabatan ?? '-' }}</span>
                </div>
                <div><i
                        class="fa-regular fa-building"></i><span>{{ auth()->user()->pegawai->jabatan->divisi->nama_divisi ?? '-' }}</span>
                </div>
            </div>
            <div class="history-period"><i class="fa-regular fa-calendar"></i><span>Periode
                    Evaluasi</span><strong>{{ $hasil->periode->nama_periode ?? '-' }}</strong></div>
        </section>
        <div class="history-summary">
            <div><span><i
                        class="fa-solid fa-chart-column"></i></span><strong>{{ $hasil->nilai_akhir ?? '-' }}</strong><small>NILAI
                    AKHIR</small></div>
            <div><span><i class="fa-solid fa-sparkles"></i></span><strong
                    class="{{ $predicateClass }}">{{ $hasil->predikat ?? '-' }}</strong><small>PREDIKAT</small></div>
            <div><span><i class="fa-solid fa-wallet"></i></span><strong>Rp
                    {{ number_format($hasil->tukin_neto ?? 0, 0, ',', '.') }}</strong><small>TUKIN NETO</small></div>
        </div>
        <section class="history-evaluation">
            <div class="history-section-head">
                <h2>Evaluasi Capaian Kerja</h2><span class="{{ $predicateClass }}"><i class="fa-solid fa-circle"></i>
                    Predikat {{ $hasil->predikat ?? '-' }}</span>
            </div>
            <div class="history-score-line"><strong>{{ $hasil->nilai_akhir ?? '-' }}</strong><span>/
                    100</span><small>Standar Minimal: 75.00</small></div>
            <div class="history-progress-label"><span>Progress Bobot Kinerja</span><b>{{ round($score) }}% Tercapai</b>
            </div><i class="history-progress"><em style="width:{{ min(100, max(0, $score)) }}%"></em></i>
            <div class="history-scale"><span>0</span><span>Kurang (0-50)</span><span>Cukup (51-75)</span><span>Baik
                    (76-100)</span></div>
            @if ($score < 75)
                <div class="history-alert"><i class="fa-solid fa-triangle-exclamation"></i> Nilai akhir masih di bawah
                    standar minimal penilaian.</div>
            @endif
        </section>
        <section class="history-card">
            <div class="history-card-title"><span><i class="fa-solid fa-circle-dollar-to-slot"></i></span>
                <div>
                    <h2>Rincian Tunjangan Kinerja</h2><small>Perhitungan reward presensi & skor</small>
                </div><b><i class="fa-solid fa-circle-check"></i> Terverifikasi</b>
            </div>
            <div class="history-money-row"><span>Tukin Bruto</span><strong>Rp
                    {{ number_format($hasil->tukin_bruto ?? 0, 0, ',', '.') }}</strong></div>
            <div class="history-money-row"><span>Total Potongan Presensi</span><strong
                    class="deduction">{{ number_format((float) ($hasil->total_potongan_presensi ?? 0), 0) }}%</strong></div>
            @forelse ($presensiPotongans as $potongan)
                <div class="history-money-row">
                    <span>{{ $potongan->jenisPotongan?->nama_potongan ?? 'Potongan Presensi' }}
                        ({{ $potongan->jumlah_kejadian }} kejadian;
                        tarif {{ number_format((float) ($potongan->jenisPotongan?->persen_potongan ?? 0), 2) }}%
                        per kejadian)</span>
                    <strong class="deduction">Total {{ number_format((float) $potongan->persen_potongan, 0) }}%</strong>
                </div>
            @empty
                <div class="history-money-row"><span>Rincian potongan</span><strong>Tidak ada</strong></div>
            @endforelse
            <div class="history-money-row"><span>Nilai Potongan</span><strong class="deduction">Rp
                    {{ number_format((float) ($hasil->tukin_bruto ?? 0) * (float) ($hasil->total_potongan_presensi ?? 0) / 100, 0, ',', '.') }}</strong></div>
            <div class="history-money-row total">
                <div><span>Tukin Neto</span><small>Total pencairan bulan ini</small></div><strong>Rp
                    {{ number_format($hasil->tukin_neto ?? 0, 0, ',', '.') }}</strong>
            </div>
        </section>
        <section class="history-status"><span><i class="fa-solid fa-circle-info"></i></span>
            <div><strong>Status Hasil Penilaian <b>{{ $status }}</b></strong>
                <p>Data penilaian ini merupakan {{ strtolower($status) }} dan menjadi informasi resmi hasil evaluasi Anda.
                </p>
            </div>
        </section>
    </div>
@endsection
@push('styles')
    <style>
        body.role-pegawai .history-detail-page {
            box-sizing: border-box;
            max-width: 560px;
            margin: auto;
            padding: 0 1rem calc(var(--bottom-nav-height) + 1.5rem);
            color: #15233d
        }

        .history-detail-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .6rem;
            padding: .3rem 0 1rem
        }

        .history-detail-top h1 {
            margin: 0;
            font-size: 1.05rem
        }

        .history-detail-top a,
        .history-detail-top button {
            padding: .6rem .75rem;
            border: 0;
            border-radius: 18px;
            color: #0875ad;
            background: #eef8ff;
            text-decoration: none;
            font-size: .7rem
        }

        .history-detail-top button {
            color: #526480;
            background: #f3f6fa
        }

        .assessment-breadcrumb {
            margin-bottom: 1rem;
            color: #90a0b7;
            font-size: .68rem
        }

        .assessment-breadcrumb span {
            margin: 0 .3rem
        }

        .assessment-breadcrumb a {
            color: #0875ad;
            text-decoration: none
        }

        .history-profile-card,
        .history-card {
            padding: 1rem;
            background: #fff;
            border: 1px solid #dfe7f0;
            border-radius: 18px
        }

        .history-profile-main {
            display: flex;
            align-items: flex-start;
            gap: .7rem
        }

        .history-avatar {
            display: grid;
            place-items: center;
            width: 58px;
            height: 58px;
            flex: 0 0 58px;
            color: #fff;
            background: #079bd5;
            border-radius: 15px;
            font-size: 1.2rem;
            font-weight: 700
        }

        .history-profile-main>div {
            min-width: 0;
            flex: 1
        }

        .history-profile-main h2 {
            margin: .3rem 0 .2rem;
            font-size: 1rem;
            overflow-wrap: anywhere
        }

        .history-profile-main p {
            margin: 0;
            color: #71829c;
            font-size: .7rem
        }

        .history-profile-main>b {
            padding: .4rem .5rem;
            color: #a15a00;
            background: #fff6da;
            border: 1px solid #f2cb5d;
            border-radius: 8px;
            font-size: .62rem
        }

        .history-profile-lines {
            display: grid;
            gap: .5rem;
            margin: 1rem 0;
            padding-top: .8rem;
            border-top: 1px solid #edf1f6
        }

        .history-profile-lines div {
            display: flex;
            gap: .7rem;
            align-items: center;
            color: #526480;
            font-size: .72rem
        }

        .history-profile-lines i {
            width: 16px;
            color: #91a1b8;
            text-align: center
        }

        .history-period {
            display: flex;
            align-items: center;
            gap: .6rem;
            padding: .75rem;
            color: #526480;
            background: #effaff;
            border: 1px solid #c5eaff;
            border-radius: 12px;
            font-size: .7rem
        }

        .history-period i {
            color: #078ac8
        }

        .history-period strong {
            margin-left: auto;
            color: #0875ad
        }

        .history-summary {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: .6rem;
            margin: 1rem 0
        }

        .history-summary div {
            min-width: 0;
            padding: .8rem .45rem;
            background: #fff;
            border: 1px solid #dfe7f0;
            border-radius: 15px;
            text-align: center
        }

        .history-summary span {
            display: grid;
            place-items: center;
            width: 32px;
            height: 32px;
            margin: auto;
            color: #078ac8;
            background: #eff7ff;
            border-radius: 9px
        }

        .history-summary strong {
            display: block;
            margin: .55rem 0 .3rem;
            font-size: 1.15rem;
            overflow-wrap: anywhere
        }

        .history-summary strong.assessment-excellent,
        .history-summary strong.assessment-good,
        .history-summary strong.assessment-fair,
        .history-summary strong.assessment-poor {
            font-size: .7rem
        }

        .history-summary small {
            color: #71829c;
            font-size: .6rem
        }

        .history-evaluation {
            padding: 1.1rem;
            color: #fff;
            background: #121c32;
            border-radius: 19px
        }

        .history-section-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: .5rem
        }

        .history-section-head h2 {
            margin: 0;
            font-size: .78rem;
            text-transform: uppercase
        }

        .history-section-head span {
            padding: .35rem .5rem;
            border-radius: 12px;
            font-size: .62rem
        }

        .history-section-head span.assessment-poor {
            color: #ff879d;
            background: #48263d
        }

        .history-section-head span.assessment-excellent,
        .history-section-head span.assessment-good {
            color: #5be0b3;
            background: #164b4a
        }

        .history-score-line {
            position: relative;
            margin: 1rem 0
        }

        .history-score-line strong {
            font-size: 2.5rem
        }

        .history-score-line>span {
            color: #9cabbd;
            font-size: .8rem
        }

        .history-score-line small {
            position: absolute;
            right: 0;
            bottom: .4rem;
            color: #a8b5c6;
            font-size: .62rem
        }

        .history-progress-label {
            display: flex;
            justify-content: space-between;
            margin-top: 1.3rem;
            font-size: .68rem
        }

        .history-progress-label b {
            color: #ff6686
        }

        .history-progress {
            display: block;
            height: 7px;
            margin: .45rem 0;
            background: #26334b;
            border-radius: 8px
        }

        .history-progress em {
            display: block;
            height: 100%;
            background: #ff4c70;
            border-radius: 8px
        }

        .history-scale {
            display: flex;
            justify-content: space-between;
            color: #a8b5c6;
            font-size: .56rem
        }

        .history-alert {
            display: flex;
            gap: .6rem;
            margin-top: 1rem;
            padding-top: .8rem;
            border-top: 1px solid #ffffff22;
            color: #e1e8f1;
            font-size: .68rem;
            line-height: 1.5
        }

        .history-alert i {
            color: #ffca28
        }

        .history-card {
            margin-top: 1rem
        }

        .history-card-title {
            display: flex;
            align-items: center;
            gap: .6rem
        }

        .history-card-title>span {
            display: grid;
            place-items: center;
            width: 38px;
            height: 38px;
            color: #08a676;
            background: #e9fbf4;
            border-radius: 10px
        }

        .history-card-title div {
            min-width: 0;
            flex: 1
        }

        .history-card-title h2 {
            margin: 0;
            font-size: .82rem
        }

        .history-card-title small {
            color: #91a1b8;
            font-size: .62rem
        }

        .history-card-title>b {
            padding: .35rem .45rem;
            color: #078653;
            background: #eafbf4;
            border-radius: 12px;
            font-size: .58rem;
            white-space: nowrap
        }

        .history-money-row {
            display: flex;
            justify-content: space-between;
            gap: .6rem;
            padding: .8rem 0;
            border-top: 1px solid #edf1f6;
            color: #71829c;
            font-size: .7rem
        }

        .history-money-row:first-of-type {
            margin-top: .8rem
        }

        .history-money-row strong {
            color: #15233d
        }

        .history-money-row small {
            margin-left: .3rem;
            padding: .2rem .3rem;
            color: #71829c;
            background: #f0f3f7;
            border-radius: 4px;
            font-size: .58rem
        }

        .history-money-row .deduction {
            color: #078653
        }

        .history-money-row.total {
            align-items: center
        }

        .history-money-row.total div span,
        .history-money-row.total div small {
            display: block;
            margin: 0
        }

        .history-money-row.total div span {
            color: #15233d;
            font-weight: 700
        }

        .history-money-row.total div small {
            color: #91a1b8
        }

        .history-money-row.total>strong {
            color: #0875ad;
            font-size: 1.15rem
        }

        .history-status {
            display: flex;
            gap: .7rem;
            margin-top: 1rem;
            padding: 1rem;
            background: #f8fbff;
            border: 1px solid #dfe7f0;
            border-radius: 16px
        }

        .history-status>span {
            display: grid;
            place-items: center;
            width: 31px;
            height: 31px;
            flex: 0 0 31px;
            color: #f0a900;
            background: #fff2bd;
            border-radius: 50%
        }

        .history-status strong {
            display: block;
            font-size: .75rem
        }

        .history-status strong b {
            float: right;
            padding: .25rem .4rem;
            color: #526480;
            background: #e8edf4;
            border-radius: 5px;
            font-size: .6rem
        }

        .history-status p {
            margin: .4rem 0 0;
            color: #71829c;
            font-size: .68rem;
            line-height: 1.5
        }

        @media(max-width:380px) {
            body.role-pegawai .history-detail-page {
                padding: 0 .75rem calc(var(--bottom-nav-height) + 1rem)
            }

            .history-detail-top h1 {
                font-size: .92rem
            }

            .history-profile-card,
            .history-card {
                padding: .8rem
            }

            .history-avatar {
                width: 52px;
                height: 52px;
                flex-basis: 52px
            }

            .history-profile-main>b {
                font-size: .55rem
            }

            .history-summary {
                gap: .35rem
            }

            .history-summary div {
                padding: .65rem .25rem
            }

            .history-summary strong {
                font-size: 1rem
            }

            .history-summary strong.assessment-excellent,
            .history-summary strong.assessment-good,
            .history-summary strong.assessment-fair,
            .history-summary strong.assessment-poor {
                font-size: .6rem
            }

            .history-section-head h2 {
                font-size: .68rem
            }

            .history-section-head span {
                font-size: .55rem
            }

            .history-score-line strong {
                font-size: 2.1rem
            }

            .history-scale {
                font-size: .49rem
            }

            .history-card-title>b {
                font-size: .5rem
            }

            .history-money-row.total>strong {
                font-size: .95rem
            }

            .history-status strong b {
                float: none;
                margin-left: .3rem
            }
        }

        body.role-pegawai .history-detail-page > section,
        body.role-pegawai .history-detail-page > .history-summary {
            width: 100%;
        }

        body.role-pegawai .history-detail-heading {
            padding: .35rem 0 1rem;
        }

        body.role-pegawai .history-detail-heading .assessment-breadcrumb {
            margin-bottom: .35rem;
        }

        body.role-pegawai .history-detail-heading h1 {
            margin: 0;
            color: #17213b;
            font-size: 1.45rem;
            font-weight: 800;
            letter-spacing: -.02em;
        }

        body.role-pegawai .history-detail-heading p {
            margin: .35rem 0 0;
            color: #71829c;
            font-size: .76rem;
        }

        body.role-pegawai .history-detail-top {
            min-height: 48px;
            margin-inline: -.25rem;
        }

        body.role-pegawai .history-detail-top a {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            font-weight: 600;
        }

        body.role-pegawai .history-detail-top button {
            width: 38px;
            height: 38px;
            display: grid;
            place-items: center;
            cursor: pointer;
        }

        body.role-pegawai .history-profile-card,
        body.role-pegawai .history-card {
            box-shadow: 0 5px 18px rgba(35, 52, 85, .06);
        }

        body.role-pegawai .history-profile-card {
            position: relative;
            overflow: hidden;
        }

        body.role-pegawai .history-profile-card::after {
            content: "";
            position: absolute;
            width: 105px;
            height: 105px;
            right: -52px;
            bottom: -52px;
            border-radius: 50%;
            background: #f1faff;
            pointer-events: none;
        }

        body.role-pegawai .history-period {
            position: relative;
            z-index: 1;
        }

        body.role-pegawai .history-summary > div {
            box-shadow: 0 4px 13px rgba(35, 52, 85, .04);
        }

        body.role-pegawai .history-summary > div:first-child {
            background: linear-gradient(180deg, #fff, #f7fbff);
        }

        body.role-pegawai .history-summary > div:nth-child(2) {
            background: linear-gradient(180deg, #fff, #fbfcff);
        }

        body.role-pegawai .history-summary > div:last-child {
            background: linear-gradient(180deg, #fff, #f7fffb);
        }

        body.role-pegawai .history-evaluation {
            box-shadow: 0 7px 20px rgba(18, 28, 50, .16);
        }

        body.role-pegawai .history-evaluation .history-progress-label b {
            white-space: nowrap;
        }

        body.role-pegawai .history-card-title {
            padding-bottom: .8rem;
            border-bottom: 1px solid #edf1f6;
        }

        body.role-pegawai .history-status {
            box-shadow: 0 4px 13px rgba(35, 52, 85, .03);
        }

        @media (max-width: 400px) {
            body.role-pegawai .history-detail-page {
                padding-inline: .85rem;
            }

            body.role-pegawai .history-detail-top h1 {
                font-size: .98rem;
            }

            body.role-pegawai .history-profile-main {
                gap: .55rem;
            }

            body.role-pegawai .history-profile-main>b {
                padding-inline: .4rem;
            }
        }
    </style>
@endpush
