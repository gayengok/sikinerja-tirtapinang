@extends('layouts.app')
@section('title', 'Detail Hasil Penilaian')
@section('content')
    @php
        $score = (float) ($hasil->nilai_akhir ?? 0);
        $scoreProgress = min(100, max(0, $score));
        $statusLabel = $hasil?->status === 'dikirim' ? 'Dikirim' : 'Draft / Meninjau Ulang';
        $badge = match ($hasil?->predikat) {
            'Sangat Baik' => 'excellent',
            'Baik' => 'good',
            'Cukup' => 'fair',
            default => 'poor',
        };
        $tukinBruto = (float) ($hasil->tukin_bruto ?? 0);
        $totalPotongan = (float) ($hasil->total_potongan_presensi ?? 0);
        $nilaiPotongan = ($tukinBruto * $totalPotongan) / 100;
        $skorKompetensi = (float) ($hasil->total_skor_b ?? 0);
    @endphp


    <div class="hasil-detail-page">
        <header class="hasil-detail-header">
            <div class="hasil-detail-heading-copy">
                <h1>
                    <i class="fa-solid fa-clipboard-check"></i>
                    Evaluasi Detail
                </h1>
                <p>Penilaian akuntabilitas kinerja &amp; kalkulasi tunjangan kinerja pegawai.</p>
            </div>
            <div class="hasil-detail-heading-meta">
                <span class="hasil-detail-period">Periode {{ $periode?->nama_periode ?? 'Belum tersedia' }}</span>
                <span class="hasil-detail-review-status {{ $statusLabel === 'Dikirim' ? 'is-sent' : 'is-draft' }}">
                    <i class="fa-solid fa-circle"></i>
                    {{ $statusLabel === 'Dikirim' ? 'Menunggu Verifikasi Final' : $statusLabel }}
                </span>
            </div>
        </header>



        <section class="hasil-detail-person">
            <span class="hasil-avatar">{{ strtoupper(substr($pegawai->nama, 0, 2)) }}</span>
            <div class="hasil-detail-person-main">
                <h2>{{ $pegawai->nama }}</h2>
                <p>{{ $pegawai->jabatan->nama_jabatan ?? '-' }}</p>
                <small>{{ $pegawai->jabatan->divisi->nama_divisi ?? 'Divisi belum tersedia' }}</small>
            </div>
        </section>

        @if (!$hasil)
            <div class="hasil-warning">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <div><strong>Hasil belum dihitung</strong>
                    <p>Hasil akan tersimpan otomatis setelah seluruh penilaian KPI dan kompetensi pegawai pada periode ini
                        lengkap.</p>
                </div>
            </div>
        @else
            <section class="hasil-final">
                <div class="hasil-final-heading">
                    <small>CAPAIAN KINERJA AKHIR</small>
                    <span class="{{ $badge }}"><i class="fa-solid fa-circle"></i> {{ $hasil->predikat }}</span>
                </div>
                <strong>{{ number_format($score, 1, ',', '.') }} <i>/ 100</i></strong>
                <div class="hasil-score-progress" role="progressbar" aria-label="Capaian kinerja" aria-valuemin="0"
                    aria-valuemax="100" aria-valuenow="{{ $scoreProgress }}">
                    <span style="width: {{ $scoreProgress }}%"></span>
                </div>
                <div class="hasil-score-scale">
                    <span>0 (Batas Kritis)</span>
                    <span>Target Min: 75.0</span>
                    <span>100</span>
                </div>
                <div class="hasil-final-status">
                    <i class="fa-solid fa-hourglass-half" aria-hidden="true"></i>
                    <strong>Status: {{ $statusLabel }}</strong>
                    <span>Kompetensi: {{ number_format($skorKompetensi, 1, ',', '.') }} / 40</span>
                </div>
            </section>

            <div class="hasil-score-grid">
                <article class="hasil-score-card">
                    <div class="hasil-score-card-heading"><small>KPI OTOMATIS</small><i class="fa-solid fa-robot"></i></div>
                    <strong>{{ number_format((float) $hasil->total_skor_a, 1, ',', '.') }} <i>/ 60</i></strong>
                    <p>Skor KPI: <b>{{ number_format((float) $hasil->rata_skor_kpi, 1, ',', '.') }}</b></p>
                    <div class="hasil-kpi-progress"><span
                            style="width: {{ min(100, max(0, ((float) $hasil->total_skor_a / 60) * 100)) }}%"></span></div>
                </article>
                <article class="hasil-score-card">
                    <div class="hasil-score-card-heading"><small>BASIS PENILAIAN</small><i class="fa-solid fa-award"></i>
                    </div>
                    <strong class="hasil-score-source">Laporan Mandiri</strong>
                    <p>Evaluasi berkala staf</p>
                    <span class="hasil-approved"><i class="fa-solid fa-circle-check"></i> KPI dihitung otomatis</span>
                </article>
            </div>

            <section class="hasil-tukin">
                <div class="hasil-section-title">
                    <span class="hasil-tukin-icon"><i class="fa-solid fa-wallet"></i></span>
                    <div>
                        <h2>Rincian Tunjangan Kinerja</h2>
                        <p>Kalkulasi berbasis capaian &amp; presensi</p>
                    </div>
                </div>
                <div class="hasil-money-row">
                    <span>Base Tukin Jabatan</span>
                    <strong>Rp {{ number_format($pegawai->jabatan->base_tukin ?? 0, 0, ',', '.') }}</strong>
                </div>
                <div class="hasil-money-row">
                    <span>Tukin Bruto (Capaian {{ number_format($scoreProgress, 1, ',', '.') }}%)</span>
                    <strong>Rp {{ number_format($tukinBruto, 0, ',', '.') }}</strong>
                </div>
                <div class="hasil-deduction">
                    <div class="hasil-deduction-heading">
                        <strong><i class="fa-solid fa-circle-minus"></i> Potongan Presensi
                            ({{ number_format($totalPotongan, 1, ',', '.') }}%)</strong>
                        <strong>-Rp {{ number_format($nilaiPotongan, 0, ',', '.') }}</strong>
                    </div>
                    @forelse ($presensiPotongans as $potongan)
                        <div class="hasil-deduction-row">
                            <span>{{ $potongan->jenisPotongan?->nama_potongan ?? 'Potongan Presensi' }}
                                ({{ $potongan->jumlah_kejadian }} kejadian)
                                <small>Tarif
                                    {{ number_format((float) ($potongan->jenisPotongan?->persen_potongan ?? 0), 2, ',', '.') }}%
                                    per kejadian</small>
                            </span>
                            <strong>-Rp
                                {{ number_format(($tukinBruto * (float) $potongan->persen_potongan) / 100, 0, ',', '.') }}</strong>
                        </div>
                    @empty
                        <div class="hasil-deduction-row"><span>Tidak ada potongan presensi</span><strong>Rp 0</strong>
                        </div>
                    @endforelse
                </div>
                <div class="hasil-net-total">
                    <small>TUKIN NETO DITERIMA</small>
                    <strong>Rp {{ number_format($hasil->tukin_neto ?? 0, 0, ',', '.') }}</strong>
                </div>
            </section>

            <div class="hasil-date">
                <i class="fa-regular fa-clock"></i>
                <span>Dihitung otomatis oleh sistem pada
                    <strong>{{ $hasil->updated_at?->translatedFormat('d M Y, H:i') ?? '-' }} WIB</strong>
                </span>
            </div>
            <button class="hasil-print-button" id="hasilPrintButton" type="button">
                <i class="fa-solid fa-download"></i> Cetak PDF
            </button>
            <a class="hasil-back-button"
                href="{{ route('atasan.hasil-penilaian.index', ['periode_id' => $periode?->id]) }}">
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Kembali
            </a>
        @endif
    </div>
@endsection
@push('styles')
    <style>
        body.role-atasan .hasil-detail-page {
            box-sizing: border-box;
            width: 100%;
            max-width: 560px;
            margin: auto;
            padding: 1rem 1rem 0;
            color: #15263d;
            font-family: Inter, sans-serif;
        }

        .hasil-detail-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            min-height: 4.2rem;
            margin: 0 -1rem;
            padding: 0 1.25rem;

            border-bottom: 1px solid #edf1f6;
        }

        .hasil-detail-header h1 {
            flex: 1;
            margin: 0;
            font-size: 1.1rem;
            font-weight: 750;
        }

        .hasil-detail-header>a,
        .hasil-detail-header-icon {
            display: grid;
            place-items: center;
            width: 2.5rem;
            height: 2.5rem;
            color: #073b62;
            font-size: 1.2rem;
        }

        .hasil-detail-header>a {
            text-decoration: none;
        }

        .hasil-detail-header-icon {
            color: #fff;
            background: #063b62;
            border-radius: 50%;
            font-size: 1rem;
        }

        .hasil-detail-kicker {
            display: flex;
            align-items: center;
            gap: .65rem;
            margin: .75rem 0 1rem;
            color: #4b5563;
            font-size: .88rem;
        }

        .hasil-detail-kicker>span {
            display: grid;
            place-items: center;
            width: 2rem;
            height: 2rem;
            color: #075078;
            background: #d8ebff;
            border-radius: 50%;
        }

        .hasil-detail-person {
            display: flex;
            align-items: center;
            gap: .8rem;
            margin-bottom: 1rem;
            padding: 1rem;
            background: #fff;
            border: 1px solid #edf1f6;
            border-radius: 1rem;
            box-shadow: 0 2px 8px #18315308;
        }

        .hasil-avatar {
            display: grid;
            place-items: center;
            width: 2.7rem;
            height: 2.7rem;
            flex: 0 0 2.7rem;
            color: #fff;
            background: linear-gradient(145deg, #0b4e79, #1689aa);
            border-radius: 50%;
            font-size: .9rem;
            font-weight: 800;
        }

        .hasil-detail-person-main {
            min-width: 0;
            flex: 1;
        }

        .hasil-detail-person-main h2 {
            margin: 0 0 .2rem;
            font-size: 1.05rem;
            font-weight: 750;
            overflow-wrap: anywhere;
        }

        .hasil-detail-person-main p {
            margin: 0;
            color: #0879ad;
            font-size: .83rem;
        }

        .hasil-detail-person-main small {
            display: block;
            margin-top: .2rem;
            color: #667085;
            font-size: .75rem;
        }

        .hasil-detail-period {
            flex: 0 0 auto;
            max-width: 45%;
            padding: .45rem .7rem;
            color: #073b62;
            background: #d8ebff;
            border-radius: 999px;
            font-size: .7rem;
        }

        .hasil-warning {
            display: flex;
            gap: .7rem;
            margin-top: 1rem;
            padding: 1rem;
            color: #936000;
            background: #fff7df;
            border: 1px solid #f4d68a;
            border-radius: 16px
        }

        .hasil-warning i {
            font-size: 1.1rem
        }

        .hasil-warning strong {
            font-size: .78rem
        }

        .hasil-warning p {
            margin: .3rem 0 0;
            font-size: .68rem;
            line-height: 1.5
        }

        .hasil-final {
            position: relative;
            overflow: hidden;
            margin: 0 0 1rem;
            padding: 1.15rem;
            color: #fff;
            background: linear-gradient(135deg, #073c68, #0c6494);
            border-radius: 1rem;
            box-shadow: 0 5px 12px #073c6826;
        }

        .hasil-final::after {
            position: absolute;
            right: -1.4rem;
            bottom: -3rem;
            width: 10rem;
            height: 10rem;
            content: "";
            background: #ffffff12;
            border-radius: 50%;
            pointer-events: none;
        }

        .hasil-final-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .5rem;
        }

        .hasil-final-heading small {
            color: #d1e7f7;
            font-size: .78rem;
            font-weight: 800;
            letter-spacing: .03em;
        }

        .hasil-final-heading>span {
            flex: 0 0 auto;
            padding: .35rem .6rem;
            border-radius: 999px;
            font-size: .68rem;
            font-weight: 700;
        }

        .hasil-final-heading>span.excellent,
        .hasil-final-heading>span.good {
            color: #075a43;
            background: #bff5df;
        }

        .hasil-final-heading>span.fair {
            color: #765000;
            background: #ffedb0;
        }

        .hasil-final-heading>span.poor {
            color: #a30e18;
            background: #ffe0df;
        }

        .hasil-final>strong {
            display: block;
            margin: .7rem 0 1rem;
            font-size: 2.65rem;
            line-height: 1;
            font-weight: 800;
        }

        .hasil-final>strong i {
            color: #c6ddf3;
            font-size: 1.2rem;
            font-style: normal;
            font-weight: 400
        }

        .hasil-score-progress {
            height: .6rem;
            overflow: hidden;
            background: #ffffff38;
            border-radius: 999px;
        }

        .hasil-score-progress span {
            display: block;
            height: 100%;
            background: linear-gradient(90deg, #e64b57, #43b7f2);
            border-radius: inherit;
        }

        .hasil-score-scale {
            display: flex;
            justify-content: space-between;
            gap: .4rem;
            margin-top: .45rem;
            color: #d8e8f5;
            font-size: .65rem;
            font-weight: 700;
        }

        .hasil-final-status {
            display: flex;
            align-items: center;
            gap: .65rem;
            margin-top: 1rem;
            padding: .75rem;
            background: #ffffff20;
            border-radius: .7rem;
            font-size: .75rem;
        }

        .hasil-final-status>i {
            color: #cce4f8;
            font-size: 1.15rem;
        }

        .hasil-final-status strong {
            flex: 1;
        }

        .hasil-final-status span {
            color: #d8e8f5;
            font-size: .68rem;
        }

        .hasil-score-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .6rem;
            margin: 0 0 1rem
        }

        .hasil-score-card {
            min-width: 0;
            padding: .9rem;
            background: #fff;
            border: 1px solid #edf1f6;
            border-radius: 1rem;
            box-shadow: 0 2px 8px #18315308;
        }

        .hasil-score-card-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .hasil-score-card-heading small {
            color: #7b8493;
            font-size: .68rem;
            font-weight: 700;
        }

        .hasil-score-card-heading i {
            color: #0879ad;
            font-size: 1rem;
        }

        .hasil-score-card>strong {
            display: block;
            margin: .65rem 0 .4rem;
            color: #172b43;
            font-size: 1.3rem;
            line-height: 1.2;
            overflow-wrap: anywhere;
        }

        .hasil-score-card>strong i {
            color: #778397;
            font-size: .78rem;
            font-style: normal;
            font-weight: 500;
        }

        .hasil-score-card>strong.hasil-score-source {
            font-size: 1.05rem;
        }

        .hasil-score-card p {
            margin: 0 0 .7rem;
            color: #5c6675;
            font-size: .72rem;
        }

        .hasil-kpi-progress {
            height: .4rem;
            overflow: hidden;
            background: #e7efff;
            border-radius: 999px;
        }

        .hasil-kpi-progress span {
            display: block;
            height: 100%;
            background: #0875ad;
            border-radius: inherit;
        }

        .hasil-approved {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .4rem .55rem;
            color: #087451;
            background: #d7f9ea;
            border-radius: 999px;
            font-size: .65rem;
            font-weight: 700;
        }

        .hasil-tukin {
            padding: 1rem;
            background: #fff;
            border: 1px solid #edf1f6;
            border-radius: 1rem;
            box-shadow: 0 2px 8px #18315308;
        }

        .hasil-section-title {
            display: flex;
            align-items: center;
            gap: .65rem;
            margin-bottom: .5rem;
        }

        .hasil-section-title h2 {
            margin: 0;
            font-size: 1rem;
            font-weight: 750;
        }

        .hasil-section-title p {
            margin: .2rem 0 0;
            color: #7b8493;
            font-size: .72rem;
        }

        .hasil-tukin-icon {
            display: grid;
            place-items: center;
            width: 2.5rem;
            height: 2.5rem;
            flex: 0 0 2.5rem;
            color: #075078;
            background: #e2efff;
            border-radius: .7rem;
        }

        .hasil-tukin>div.hasil-money-row {
            display: flex;
            justify-content: space-between;
            gap: .75rem;
            padding: .7rem 0;
            color: #596270;
            font-size: .82rem;
        }

        .hasil-money-row strong {
            flex: 0 0 auto;
            color: #172b43;
            text-align: right;
        }

        .hasil-deduction {
            margin-top: .15rem;
            padding: .75rem;
            color: #bd1e2d;
            background: #eff4ff;
            border-radius: .75rem;
        }

        .hasil-deduction-heading,
        .hasil-deduction-row {
            display: flex;
            justify-content: space-between;
            gap: .6rem;
            font-size: .75rem;
        }

        .hasil-deduction-heading {
            align-items: flex-start;
            padding-bottom: .45rem;
        }


        @media (min-width: 992px) {
            body.role-atasan .hasil-detail-page {
                width: 100%;
                max-width: 1120px;
                margin: 0 auto;
                padding: 1.4rem 1.5rem 2rem;
                color: #1b2938;
            }

            .hasil-detail-header {
                min-height: 0;
                margin: 0 0 .9rem;
                padding: 0;
                border: 0;
            }

            .hasil-detail-heading-copy {
                min-width: 0;
                flex: 1;
            }

            .hasil-detail-header h1 {
                color: #1b2938;
                font-size: 1.25rem;
            }

            .hasil-detail-header h1 i {
                color: #078dcc;
            }

            .hasil-detail-heading-copy p {
                margin: .2rem 0 0;
                color: #718092;
                font-size: .68rem;
            }

            .hasil-detail-heading-meta {
                display: flex;
                align-items: center;
                gap: .45rem;
            }

            .hasil-detail-period,
            .hasil-detail-review-status {
                display: inline-flex;
                align-items: center;
                gap: .3rem;
                padding: .35rem .5rem;
                border-radius: 5px;
                font-size: .59rem;
                white-space: nowrap;
            }

            .hasil-detail-period {
                color: #526480;
                background: #f0f4f8;
            }

            .hasil-detail-review-status {
                color: #a96800;
                background: #fff5df;
            }

            .hasil-detail-review-status.is-sent {
                color: #087c50;
                background: #e6f7ef;
            }

            .hasil-detail-review-status i {
                font-size: .38rem;
            }

            .hasil-detail-person,
            .hasil-final,
            .hasil-score-card,
            .hasil-tukin {
                border-radius: 8px;
                box-shadow: none;
            }

            .hasil-detail-person {
                margin-bottom: .75rem;
                padding: .75rem .9rem;
            }

            .hasil-avatar {
                width: 42px;
                height: 42px;
                flex-basis: 42px;
                border-radius: 8px;
                font-size: .72rem;
            }

            .hasil-detail-person-main h2 {
                font-size: .88rem;
            }

            .hasil-detail-person-main p,
            .hasil-detail-person-main small {
                font-size: .65rem;
            }

            .hasil-detail-period {
                max-width: none;
            }

            .hasil-final {
                margin-bottom: .75rem;
                padding: .95rem 1rem;
                border-radius: 8px;
                box-shadow: none;
            }

            .hasil-final-heading small {
                font-size: .65rem;
            }

            .hasil-final-heading>span {
                padding: .28rem .45rem;
                border-radius: 5px;
                font-size: .6rem;
            }

            .hasil-final>strong {
                margin: .55rem 0 .75rem;
                font-size: 2.2rem;
            }

            .hasil-score-progress {
                height: .45rem;
            }

            .hasil-score-scale {
                margin-top: .35rem;
                font-size: .58rem;
            }

            .hasil-final-status {
                gap: .5rem;
                margin-top: .75rem;
                padding: .55rem .65rem;
                border-radius: 5px;
                font-size: .65rem;
            }

            .hasil-final-status>i {
                font-size: .9rem;
            }

            .hasil-final-status span {
                font-size: .6rem;
            }

            .hasil-score-grid {
                gap: .75rem;
                margin-bottom: .75rem;
            }

            .hasil-score-card {
                padding: .8rem .9rem;
                border-color: #e2e8ef;
            }

            .hasil-score-card-heading small {
                font-size: .61rem;
            }

            .hasil-score-card-heading i {
                display: grid;
                place-items: center;
                width: 28px;
                height: 28px;
                background: #eaf5ff;
                border-radius: 5px;
                font-size: .75rem;
            }

            .hasil-score-card>strong {
                margin: .45rem 0 .3rem;
                font-size: 1.1rem;
            }

            .hasil-score-card>strong.hasil-score-source {
                font-size: .9rem;
            }

            .hasil-score-card p {
                margin-bottom: .5rem;
                font-size: .63rem;
            }

            .hasil-approved {
                padding: .3rem .42rem;
                font-size: .58rem;
            }

            .hasil-tukin {
                padding: .9rem 1rem;
                border-color: #e2e8ef;
            }

            .hasil-section-title {
                gap: .55rem;
                margin-bottom: .4rem;
            }

            .hasil-tukin-icon {
                width: 32px;
                height: 32px;
                flex-basis: 32px;
                border-radius: 6px;
            }

            .hasil-section-title h2 {
                font-size: .82rem;
            }

            .hasil-section-title p {
                margin-top: .12rem;
                font-size: .62rem;
            }

            .hasil-tukin>div.hasil-money-row {
                padding: .55rem 0;
                font-size: .67rem;
            }

            .hasil-deduction {
                padding: .65rem;
                border-radius: 6px;
            }

            .hasil-deduction-heading,
            .hasil-deduction-row {
                font-size: .64rem;
            }

            .hasil-deduction-row small {
                font-size: .58rem;
            }

            .hasil-net-total {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: .8rem;
                margin-top: .65rem;
                padding: .65rem .75rem;
                border-radius: 6px;
            }

            .hasil-net-total small {
                font-size: .61rem;
            }

            .hasil-net-total strong {
                margin: 0;
                font-size: 1.2rem;
            }

            .hasil-date {
                margin: .65rem .2rem;
                font-size: .62rem;
            }

            .hasil-print-button,
            .hasil-back-button {
                display: inline-flex;
                width: auto;
                min-width: 110px;
                min-height: 38px;
                margin-top: .4rem;
                padding: .5rem .75rem;
                border-radius: 5px;
                font-size: .68rem;
            }

            .hasil-print-button {
                float: left;
                background: #063b62;
            }

            .hasil-back-button {
                float: right;
            }
        }

        .hasil-deduction-heading strong:last-child,
        .hasil-deduction-row>strong {
            flex: 0 0 auto;
            text-align: right;
        }

        .hasil-deduction-row {
            padding-top: .45rem;
            color: #505a69;
        }

        .hasil-deduction-row>span {
            min-width: 0;
        }

        .hasil-deduction-row small {
            display: block;
            margin-top: .15rem;
            color: #748094;
            font-size: .66rem;
        }

        .hasil-net-total {
            margin-top: .8rem;
            padding: .85rem;
            color: #092f4b;
            background: #e2f1ff;
            border-radius: .8rem;
        }

        .hasil-net-total small,
        .hasil-net-total strong {
            display: block;
        }

        .hasil-net-total small {
            font-size: .68rem;
            font-weight: 800;
            letter-spacing: .04em;
        }

        .hasil-net-total strong {
            margin-top: .25rem;
            font-size: 1.45rem;
        }

        .hasil-date {
            display: flex;
            align-items: flex-start;
            gap: .6rem;
            margin: 1rem .5rem;
            color: #7b8493;
            font-size: .75rem;
            line-height: 1.5;
        }

        .hasil-date strong {
            color: #657083;
        }

        .hasil-print-button {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .6rem;
            width: 100%;
            min-height: 3.7rem;
            margin-top: 1.2rem;
            color: #fff;
            background: #063b62;
            border: 0;
            border-radius: .9rem;
            font: inherit;
            font-size: .9rem;
            font-weight: 750;
            cursor: pointer;
        }

        .hasil-back-button {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .6rem;
            width: 100%;
            min-height: 3.25rem;
            margin-top: .65rem;
            color: #31516b;
            background: #eef4fa;
            border: 1px solid #dce6f0;
            border-radius: .9rem;
            font-size: .88rem;
            font-weight: 700;
            text-decoration: none;
        }

        @media(max-width:380px) {
            body.role-atasan .hasil-detail-page {
                padding-right: .75rem;
                padding-left: .75rem;
            }

            .hasil-detail-header {
                margin-right: -.75rem;
                margin-left: -.75rem;
                padding: 0 .9rem;
            }

            .hasil-detail-person {
                gap: .55rem;
                padding: .8rem;
            }

            .hasil-detail-period {
                max-width: 42%;
                padding: .4rem .5rem;
                font-size: .62rem;
            }

            .hasil-final {
                padding: .95rem;
            }

            .hasil-final>strong {
                font-size: 2.3rem;
            }

            .hasil-final-status {
                flex-wrap: wrap;
            }

            .hasil-final-status span {
                width: 100%;
                margin-left: 1.8rem;
            }

            .hasil-score-grid {
                gap: .4rem;
            }

            .hasil-score-card {
                padding: .75rem;
            }

            .hasil-score-card>strong {
                font-size: 1.1rem;
            }

            .hasil-score-card>strong.hasil-score-source {
                font-size: .92rem;
            }

            .hasil-deduction {
                padding: .65rem;
            }

            .hasil-deduction-heading,
            .hasil-deduction-row {
                font-size: .68rem;
            }
        }

        @media print {
            body.role-atasan .hasil-detail-page {
                max-width: none;
                padding: 0;
                color: #15263d;
            }

            .mobile-app-shell>nav,
            .mobile-app-shell>.main-content>.footer,
            .mobile-app-shell>.bottom-nav,
            body.role-atasan .hasil-detail-header>a,
            body.role-atasan .hasil-detail-header-icon,
            body.role-atasan .hasil-print-button,
            body.role-atasan .hasil-back-button,
            body.role-atasan .hasil-detail-kicker {
                display: none !important;
            }

            .hasil-detail-header {
                min-height: auto;
                margin: 0 0 1rem;
                padding: 0 0 .8rem;
            }

            .hasil-final,
            .hasil-tukin,
            .hasil-score-card,
            .hasil-detail-person {
                break-inside: avoid;
                box-shadow: none;
            }
        }
    </style>
@endpush
@push('scripts')
    <script>
        document.getElementById('hasilPrintButton')?.addEventListener('click', () => window.print());
    </script>
@endpush
