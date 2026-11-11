@extends('layouts.app')

@section('title', 'Dashboard Kepala Bagian')

@section('content')
    @php
        $totalLaporan = $laporanMenunggu + $laporanDisetujui + $laporanDitolak;
        $persentaseDisetujui = $totalLaporan > 0 ? round(($laporanDisetujui / $totalLaporan) * 100) : 0;
    @endphp

    <main class="kepala-bagian-dashboard">
        <section class="kepala-bagian-welcome">
            <div>
                <span class="kepala-bagian-eyebrow"><i class="fa-solid fa-circle"></i> Kepala Bagian / Penilai</span>
                <h1>Selamat Datang, {{ auth()->user()->name }}</h1>
                <p>Ringkasan kinerja atasan dan progres laporan tim untuk periode berjalan.</p>
            </div>
            <div class="kepala-bagian-period">
                <i class="fa-regular fa-calendar-check"></i>
                <span><small>PERIODE BERJALAN</small><strong>{{ $periodeAktif?->nama_periode ?? 'Belum tersedia' }}</strong></span>
            </div>
        </section>

        <section class="kepala-bagian-stats" aria-label="Ringkasan statistik">
            <article class="is-team"><span><i class="fa-solid fa-user-tie"></i></span><strong>{{ $jumlahAtasan }}</strong><b>Atasan yang Dinilai</b><small>Atasan aktif di bawah tanggung jawab Anda</small></article>
            <article class="is-approved"><span><i class="fa-solid fa-file-circle-check"></i></span><strong>{{ $laporanDisetujui }}</strong><b>Laporan Disetujui</b><small>Dari tim atasan yang ditugaskan</small></article>
            <article class="is-pending"><span><i class="fa-regular fa-clock"></i></span><strong>{{ $laporanMenunggu }}</strong><b>Laporan Menunggu</b><small>{{ $laporanDitolak }} laporan perlu revisi</small></article>
        </section>

        <section class="kepala-bagian-progress">
            <div><span>Progres laporan tim</span><strong>{{ $persentaseDisetujui }}% disetujui</strong></div>
            <div class="kepala-bagian-progress-track"><span style="width: {{ $persentaseDisetujui }}%"></span></div>
            <small>{{ $laporanDisetujui }} dari {{ $totalLaporan }} laporan telah disetujui.</small>
        </section>

        <section class="kepala-bagian-tools" aria-label="Fitur pengelolaan tim">
            <a href="{{ route('kepala_bagian.pegawai.index') }}">
                <i class="fa-solid fa-users"></i>
                <span><strong>Pegawai Atasan</strong><small>Lihat dan nilai Atasan di bawah tanggung jawab Anda.</small></span>
                <i class="fa-solid fa-arrow-right"></i>
            </a>
            <a href="{{ route('kepala_bagian.absensi.index') }}">
                <i class="fa-solid fa-calendar-check"></i>
                <span><strong>Data Absensi</strong><small>Pantau presensi pegawai dalam tim Atasan.</small></span>
                <i class="fa-solid fa-arrow-right"></i>
            </a>
            <a href="{{ route('kepala_bagian.laporan.index', ['status' => 'diajukan']) }}">
                <i class="fa-solid fa-file-circle-check"></i>
                <span><strong>Approval Laporan</strong><small>Tinjau, setujui, atau tolak laporan yang diajukan.</small></span>
                <i class="fa-solid fa-arrow-right"></i>
            </a>
        </section>

        <section class="kepala-bagian-action">
            <div class="kepala-bagian-action-icon"><i class="fa-solid fa-chart-line"></i></div>
            <div>
                <h2>Penilaian Kinerja Atasan</h2>
                <p>Tinjau dan berikan nilai KPI kepada atasan yang menjadi tanggung jawab Anda.</p>
            </div>
            <a href="{{ route('kepala_bagian.penilaian-atasan.index') }}">Buka Penilaian <i class="fa-solid fa-arrow-right"></i></a>
        </section>
    </main>
@endsection

@push('styles')
    <style>
        body.role-kepala_bagian .kepala-bagian-dashboard {
            display: grid;
            gap: 12px;
            width: min(1280px, 100%);
            margin: 0 auto;
            padding: 16px 20px 24px;
            color: #17213b;
        }

        body.role-kepala_bagian .kepala-bagian-welcome,
        body.role-kepala_bagian .kepala-bagian-progress,
        body.role-kepala_bagian .kepala-bagian-action {
            padding: 22px;
            background: #fff;
            border: 1px solid #dfe7f0;
            border-radius: 16px;
        }

        body.role-kepala_bagian .kepala-bagian-welcome {
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            min-height: 104px;
            padding: 20px 22px;
            color: #fff;
            background: linear-gradient(110deg, #0784c4 0%, #078dc3 55%, #149fae 100%);
            border: 0;
            border-radius: 12px;
            box-shadow: 0 7px 16px rgba(7, 124, 177, .12);
        }

        body.role-kepala_bagian .kepala-bagian-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 9px;
            color: #e2f5fc;
            background: rgba(255, 255, 255, .16);
            border: 1px solid rgba(255, 255, 255, .13);
            border-radius: 999px;
            font-size: .64rem;
            font-weight: 700;
            letter-spacing: .035em;
        }

        body.role-kepala_bagian .kepala-bagian-eyebrow i {
            color: #4be0b1;
            font-size: .4rem;
        }

        body.role-kepala_bagian .kepala-bagian-welcome h1 {
            margin: 7px 0 4px;
            font-size: clamp(1.2rem, 2.4vw, 1.65rem);
            font-weight: 800;
            letter-spacing: -.025em;
        }

        body.role-kepala_bagian .kepala-bagian-welcome p {
            margin: 0;
            color: #e0f2fe;
            font-size: .72rem;
            line-height: 1.5;
        }

        body.role-kepala_bagian .kepala-bagian-period {
            display: flex;
            align-items: center;
            gap: 9px;
            min-width: 174px;
            padding: 10px 12px;
            background: rgba(255, 255, 255, .16);
            border: 1px solid rgba(255, 255, 255, .13);
            border-radius: 10px;
        }

        body.role-kepala_bagian .kepala-bagian-period i {
            display: grid;
            place-items: center;
            width: 30px;
            height: 30px;
            flex: 0 0 30px;
            color: #e8f7fd;
            background: rgba(255, 255, 255, .18);
            border-radius: 8px;
            font-size: .8rem;
        }

        body.role-kepala_bagian .kepala-bagian-period small,
        body.role-kepala_bagian .kepala-bagian-period strong {
            display: block;
        }

        body.role-kepala_bagian .kepala-bagian-period small {
            margin-bottom: 2px;
            color: #d6effb;
            font-size: .55rem;
            font-weight: 700;
        }

        body.role-kepala_bagian .kepala-bagian-period strong {
            color: #fff;
            font-size: .68rem;
        }

        body.role-kepala_bagian .kepala-bagian-stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }

        body.role-kepala_bagian .kepala-bagian-stats article {
            display: grid;
            gap: 4px;
            min-width: 0;
            padding: 12px;
            background: #fff;
            border: 1px solid #dfe7f0;
            border-radius: 12px;
            box-shadow: 0 2px 6px rgba(35, 52, 85, .025);
        }

        body.role-kepala_bagian .kepala-bagian-stats article > span {
            display: grid;
            place-items: center;
            width: 30px;
            height: 30px;
            color: #078ac8;
            background: #e8f6fc;
            border-radius: 9px;
            font-size: .78rem;
        }

        body.role-kepala_bagian .kepala-bagian-stats strong {
            color: #111827;
            font-size: 1.3rem;
            line-height: 1.1;
        }

        body.role-kepala_bagian .kepala-bagian-stats b {
            color: #17213b;
            font-size: .72rem;
        }

        body.role-kepala_bagian .kepala-bagian-stats small,
        body.role-kepala_bagian .kepala-bagian-progress small {
            color: #71829c;
            font-size: .62rem;
            line-height: 1.4;
        }

        body.role-kepala_bagian .kepala-bagian-stats .is-approved > span {
            color: #2563eb;
            background: #edf4ff;
        }

        body.role-kepala_bagian .kepala-bagian-stats .is-pending > span {
            color: #d97706;
            background: #fff4ca;
        }

        body.role-kepala_bagian .kepala-bagian-progress {
            padding: 12px;
            border-radius: 12px;
            box-shadow: 0 2px 6px rgba(35, 52, 85, .025);
        }

        body.role-kepala_bagian .kepala-bagian-progress > div:first-child {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 8px;
            color: #17213b;
            font-size: .7rem;
            font-weight: 700;
        }

        body.role-kepala_bagian .kepala-bagian-progress > div:first-child strong {
            padding: 4px 8px;
            color: #075985;
            background: #edf7fc;
            border: 1px solid #d5edf7;
            border-radius: 999px;
            font-size: .58rem;
            font-weight: 700;
        }

        body.role-kepala_bagian .kepala-bagian-progress-track {
            height: 7px;
            margin-bottom: 7px;
            overflow: hidden;
            background: #e8eef5;
            border-radius: 99px;
        }

        body.role-kepala_bagian .kepala-bagian-progress-track span {
            display: block;
            height: 100%;
            background: #0bb887;
            border-radius: inherit;
        }

        body.role-kepala_bagian .kepala-bagian-action {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 14px;
            border-radius: 12px;
            box-shadow: 0 2px 6px rgba(35, 52, 85, .025);
        }

        body.role-kepala_bagian .kepala-bagian-tools {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }

        body.role-kepala_bagian .kepala-bagian-tools a {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
            padding: 12px;
            color: #17213b;
            background: #fff;
            border: 1px solid #dfe7f0;
            border-radius: 12px;
            text-decoration: none;
            transition: border-color .2s, transform .2s;
        }

        body.role-kepala_bagian .kepala-bagian-tools a:hover {
            border-color: #078ac8;
            transform: translateY(-2px);
        }

        body.role-kepala_bagian .kepala-bagian-tools a > i:first-child,
        body.role-kepala_bagian .kepala-bagian-tools a > i:last-child {
            color: #078ac8;
        }

        body.role-kepala_bagian .kepala-bagian-tools a > i:first-child {
            display: grid;
            place-items: center;
            width: 30px;
            height: 30px;
            flex: 0 0 30px;
            background: #edf7fc;
            border-radius: 9px;
            font-size: .75rem;
        }

        body.role-kepala_bagian .kepala-bagian-tools a > i:last-child {
            margin-left: auto;
        }

        body.role-kepala_bagian .kepala-bagian-tools strong,
        body.role-kepala_bagian .kepala-bagian-tools small {
            display: block;
        }

        body.role-kepala_bagian .kepala-bagian-tools small {
            margin-top: 3px;
            color: #64748b;
            font-size: .61rem;
            line-height: 1.4;
        }

        body.role-kepala_bagian .kepala-bagian-tools strong {
            font-size: .7rem;
        }

        body.role-kepala_bagian .kepala-bagian-action-icon {
            display: grid;
            place-items: center;
            width: 34px;
            height: 34px;
            flex: 0 0 34px;
            color: #078ac8;
            background: #e8f6fc;
            border-radius: 9px;
            font-size: .85rem;
        }

        body.role-kepala_bagian .kepala-bagian-action h2 {
            margin: 0 0 3px;
            font-size: .75rem;
        }

        body.role-kepala_bagian .kepala-bagian-action p {
            margin: 0;
            color: #71829c;
            font-size: .62rem;
            line-height: 1.4;
        }

        body.role-kepala_bagian .kepala-bagian-action a {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            margin-left: auto;
            padding: 8px 11px;
            color: #fff;
            background: #078ac8;
            border-radius: 9px;
            font-size: .64rem;
            font-weight: 700;
            text-decoration: none;
            white-space: nowrap;
            box-shadow: 0 3px 8px rgba(7, 138, 200, .15);
        }

        @media (max-width: 767.98px) {
            body.role-kepala_bagian .kepala-bagian-dashboard {
                gap: 10px;
                padding: 14px;
            }

            body.role-kepala_bagian .kepala-bagian-welcome {
                align-items: stretch;
                flex-direction: column;
                padding: 18px;
            }

            body.role-kepala_bagian .kepala-bagian-period {
                min-width: 0;
            }

            body.role-kepala_bagian .kepala-bagian-stats {
                grid-template-columns: 1fr;
            }

            body.role-kepala_bagian .kepala-bagian-tools {
                grid-template-columns: 1fr;
                gap: 8px;
            }

            body.role-kepala_bagian .kepala-bagian-action {
                align-items: flex-start;
                flex-wrap: wrap;
                padding: 16px;
            }

            body.role-kepala_bagian .kepala-bagian-action a {
                width: 100%;
                justify-content: center;
                margin-left: 0;
            }
        }
    </style>
@endpush