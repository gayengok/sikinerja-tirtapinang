@extends('layouts.app')
@section('title', 'Detail Periode Penilaian')
@section('content')
    <div class="master-periode-detail-page">

        <header class="master-periode-detail-heading">
            <div>
                <h1>
                    <i class="fa-solid fa-calendar-check"></i>
                    Detail Periode Penilaian
                </h1>
                <p>Informasi lengkap konfigurasi siklus penilaian kinerja pegawai.</p>
            </div>
        </header>

        <section class="master-periode-detail-card">
            <div class="master-periode-detail-title"><span><i class="fa-regular fa-calendar"></i></span><strong>INFORMASI
                    PERIODE</strong><code>DATA MASTER</code></div>
            <div class="master-periode-detail-divider"></div>
            <div class="master-periode-detail-name"><label>Nama
                    Periode</label><strong>{{ $periodePenilaian->nama_periode }}</strong><small>Siklus penilaian kinerja
                    pegawai</small></div>
            <div class="master-periode-detail-dates">
                <div><label>Tanggal
                        Mulai</label><strong>{{ \Carbon\Carbon::parse($periodePenilaian->tanggal_mulai)->translatedFormat('d F Y') }}</strong><small>Awal
                        periode aktif</small></div>
                <div><label>Tanggal
                        Selesai</label><strong>{{ \Carbon\Carbon::parse($periodePenilaian->tanggal_selesai)->translatedFormat('d F Y') }}</strong><small>Batas
                        akhir periode</small></div>
            </div>
            <div class="master-periode-detail-cycle"><i class="fa-regular fa-clock"></i><span>Durasi
                    Periode</span><strong>{{ \Carbon\Carbon::parse($periodePenilaian->tanggal_mulai)->diffInDays(\Carbon\Carbon::parse($periodePenilaian->tanggal_selesai)) + 1 }}
                    Hari</strong></div>
            <div class="master-periode-detail-state"><label>Status
                    Periode</label><strong>{{ ucfirst($periodePenilaian->status) }}</strong>
                <p>{{ $periodePenilaian->status === 'berjalan' ? 'Periode ini menjadi acuan aktif untuk proses penilaian.' : 'Periode ini tersimpan sebagai riwayat konfigurasi penilaian.' }}
                </p>
            </div>
        </section>
        <div class="master-periode-detail-stats">
            <div><strong>{{ $periodePenilaian->penilaianKpis->count() }}</strong><span>Penilaian KPI</span></div>
            <div><strong>{{ $periodePenilaian->penilaianKompetensis->count() }}</strong><span>Kompetensi</span></div>
            <div><strong>{{ $periodePenilaian->hasilPenilaians->count() }}</strong><span>Hasil Penilaian</span></div>
        </div>
        <aside class="master-periode-detail-note"><i class="fa-solid fa-circle-info"></i>
            <p><strong>Informasi Siklus:</strong> Periode aktif menjadi acuan langsung perhitungan absensi, pengisian
                logbook harian, dan kalkulasi Tukin pegawai.</p>
        </aside>
        <a href="{{ route('master.periode-penilaian.edit', $periodePenilaian) }}" class="master-periode-detail-primary"><i
                class="fa-solid fa-pen"></i> Edit</a>
        <a href="{{ route('master.periode-penilaian.index') }}" class="master-periode-detail-secondary"><i
                class="fa-solid fa-arrow-left"></i> Kembali</a>
    </div>
@endsection
@push('styles')
    <style>
        body.role-master .master-periode-detail-page {
            max-width: 560px;
            margin: auto;
            padding: 1rem 1rem 0;
            color: #14223d
        }

        .master-periode-detail-crumb {
            display: flex;
            gap: .45rem;
            align-items: center;
            margin-bottom: 1rem;
            font-size: .68rem
        }

        .master-periode-detail-crumb a {
            color: #0875ad;
            text-decoration: none
        }

        .master-periode-detail-crumb i {
            font-size: .45rem;
            color: #9caabd
        }

        .master-periode-detail-heading {
            display: flex;
            justify-content: space-between;
            gap: .5rem
        }

        .master-periode-detail-heading h1 {
            margin: 0;
            font-size: 1rem
        }

        .master-periode-detail-heading p {
            margin: .3rem 0;
            color: #71829c;
            font-size: .73rem
        }

        .master-periode-detail-heading>div:last-child {
            display: flex;
            flex-direction: column;
            gap: .45rem
        }

        .master-periode-detail-heading a {
            padding: .52rem .65rem;
            border-radius: 10px;
            text-decoration: none;
            font-size: .68rem;
            white-space: nowrap
        }

        .master-periode-detail-heading .back {
            color: #14223d;
            background: #fff;
            border: 1px solid #c9d6e5
        }

        .master-periode-detail-heading .edit {
            color: #fff;
            background: #078dcc;
            text-align: center
        }

        .master-periode-detail-status {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 1rem .3rem 1.1rem;
            font-size: .68rem
        }

        .master-periode-detail-status span {
            padding: .4rem .7rem;
            color: #07986b;
            background: #effff8;
            border: 1px solid #a6edce;
            border-radius: 999px
        }

        .master-periode-detail-status span i {
            font-size: .4rem
        }

        .master-periode-detail-status em {
            color: #91a1b8;
            font-style: normal
        }

        .master-periode-detail-card {
            padding: 1.15rem 1rem;
            background: #fff;
            border: 1px solid #e0e8f1;
            border-radius: 20px;
            box-shadow: 0 5px 16px #2334550b
        }

        .master-periode-detail-title {
            display: flex;
            align-items: center;
            gap: .65rem
        }

        .master-periode-detail-title>span {
            display: grid;
            place-items: center;
            width: 36px;
            height: 36px;
            color: #078dcc;
            background: #effaff;
            border-radius: 10px
        }

        .master-periode-detail-title strong {
            font-size: .78rem
        }

        .master-periode-detail-title code {
            margin-left: auto;
            padding: .4rem .5rem;
            color: #526480;
            background: #f0f5f9;
            border-radius: 6px;
            font-size: .58rem
        }

        .master-periode-detail-divider {
            height: 1px;
            margin: 1rem 0;
            background: #e5edf4
        }

        .master-periode-detail-name label,
        .master-periode-detail-dates label,
        .master-periode-detail-state label {
            display: block;
            color: #91a1b8;
            font-size: .7rem
        }

        .master-periode-detail-name strong {
            display: block;
            margin-top: .3rem;
            font-size: 1rem
        }

        .master-periode-detail-name small,
        .master-periode-detail-dates small {
            display: block;
            margin-top: .25rem;
            color: #91a1b8;
            font-size: .65rem
        }

        .master-periode-detail-dates {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .7rem;
            margin-top: 1rem;
            padding: .8rem;
            background: #f8fafc;
            border-radius: 13px
        }

        .master-periode-detail-dates strong {
            display: block;
            margin-top: .4rem;
            font-size: .76rem
        }

        .master-periode-detail-cycle {
            display: flex;
            align-items: center;
            gap: .5rem;
            margin-top: 1rem;
            padding: .7rem;
            color: #526b8c;
            background: #effaff;
            border: 1px solid #bce7ff;
            border-radius: 12px;
            font-size: .72rem
        }

        .master-periode-detail-cycle i {
            color: #078dcc
        }

        .master-periode-detail-cycle strong {
            margin-left: auto;
            color: #07527c
        }

        .master-periode-detail-state {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #edf2f7
        }

        .master-periode-detail-state strong {
            display: block;
            margin-top: .3rem;
            color: #078dcc;
            font-size: .9rem
        }

        .master-periode-detail-state p {
            margin: .35rem 0 0;
            color: #71829c;
            font-size: .68rem
        }

        .master-periode-detail-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: .6rem;
            margin-top: 1rem
        }

        .master-periode-detail-stats div {
            padding: .75rem .4rem;
            text-align: center;
            background: #fff;
            border: 1px solid #e0e8f1;
            border-radius: 13px
        }

        .master-periode-detail-stats strong {
            display: block;
            color: #078dcc;
            font-size: 1.15rem
        }

        .master-periode-detail-stats span {
            color: #71829c;
            font-size: .61rem
        }

        .master-periode-detail-note {
            display: flex;
            gap: .7rem;
            margin-top: 1rem;
            padding: 1rem;
            color: #526b8c;
            background: #effaff;
            border: 1px solid #bce7ff;
            border-radius: 16px
        }

        .master-periode-detail-note>i {
            color: #078dcc
        }

        .master-periode-detail-note p {
            margin: 0;
            font-size: .69rem;
            line-height: 1.55
        }

        .master-periode-detail-note strong {
            color: #07527c
        }

        .master-periode-detail-primary,
        .master-periode-detail-secondary {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            min-height: 51px;
            margin-top: 1rem;
            border-radius: 14px;
            text-decoration: none;
            font-size: .8rem
        }

        .master-periode-detail-primary {
            color: #fff;
            background: #078dcc;
            box-shadow: 0 4px 8px #078dcc33;
            font-weight: 700
        }

        .master-periode-detail-secondary {
            color: #14223d;
            background: #fff;
            border: 1px solid #dce5ee
        }

        .master-periode-detail-delete {
            margin: 1.6rem 0;
            text-align: center
        }

        .master-periode-detail-delete button {
            color: #f03d5c;
            background: none;
            border: 0;
            font: inherit;
            font-size: .75rem
        }

        @media(max-width:380px) {
            body.role-master .master-periode-detail-page {
                padding-inline: .75rem
            }

            .master-periode-detail-heading h1 {
                font-size: 1.22rem
            }

            .master-periode-detail-card {
                padding-inline: .8rem
            }

            .master-periode-detail-title strong {
                font-size: .7rem
            }

            .master-periode-detail-dates {
                gap: .45rem
            }

            .master-periode-detail-dates strong {
                font-size: .68rem
            }
        }
    </style>
@endpush
