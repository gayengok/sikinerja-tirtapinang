@extends('layouts.app')
@section('title', 'Hasil KPI')
@section('content')
    @php
        $periodeId = $periode?->id;
        $tanggalPenilaian = $hasilDiperbaruiPada?->translatedFormat('d M Y, H:i');
        $statusTunjangan = $hasilTunjangan?->status === 'dikirim'
            ? 'Menunggu Verifikasi Final'
            : 'Draft / Meninjau Ulang';
        $nilaiAkhir = min(100, max(0, (float) ($hasilTunjangan?->nilai_akhir ?? 0)));
        $tukinBruto = (float) ($hasilTunjangan?->tukin_bruto ?? 0);
        $totalPotongan = (float) ($hasilTunjangan?->total_potongan_presensi ?? 0);
        $nilaiPotongan = $tukinBruto * $totalPotongan / 100;
    @endphp

    <main class="kepala-kpi-result-page">
        <header class="kepala-kpi-result-header">
            <div>
                <h1><i class="fa-solid fa-clipboard-check"></i> Hasil Penilaian KPI</h1>
                <p>Rincian hasil KPI dan tunjangan kinerja atasan pada periode penilaian.</p>
            </div>
            <div class="kepala-kpi-result-meta">
                <span>Periode {{ $periode?->nama_periode ?? 'Belum tersedia' }}</span>
                <span class="kepala-kpi-result-status {{ $hasilTunjangan?->status === 'dikirim' ? 'is-sent' : '' }}">
                    <i class="fa-solid fa-circle"></i>{{ $hasilTunjangan ? $statusTunjangan : 'Hasil Belum Dihitung' }}
                </span>
            </div>
        </header>

        <section class="kepala-kpi-result-person">
            <span class="kepala-kpi-result-avatar">{{ strtoupper(substr($pegawai->nama, 0, 2)) }}</span>
            <div>
                <h2>{{ $pegawai->nama }}</h2>
                <p>{{ $pegawai->jabatan->nama_jabatan ?? 'Jabatan belum diatur' }}</p>
                <small>{{ $pegawai->jabatan->divisi->nama_divisi ?? 'Unit belum tersedia' }}</small>
            </div>
        </section>

        <section class="kepala-kpi-result-summary">
            <div class="kepala-kpi-result-section-title">
                <span><i class="fa-solid fa-chart-line"></i></span>
                <div>
                    <h2>Rincian Hasil KPI</h2>
                    <p>Rekap capaian berbobot dan skor indikator yang dinilai.</p>
                </div>
            </div>
            <div class="kepala-kpi-result-stat-row">
                <span>Capaian KPI tertimbang</span>
                <strong>{{ number_format($capaianKpi, 2, ',', '.') }}%</strong>
            </div>
            <div class="kepala-kpi-result-stat-row">
                <span>Rata-rata skor indikator</span>
                <strong>{{ number_format($rataSkorKpi, 2, ',', '.') }} <small>/ 5</small></strong>
            </div>
            <div class="kepala-kpi-result-total">
                <small>TOTAL INDIKATOR DINILAI</small>
                <strong>{{ $penilaianKpis->count() }}</strong>
            </div>
        </section>

        <section class="kepala-kpi-result-tukin">
            @if ($hasilTunjangan)
                <div class="kepala-kpi-result-section-title">
                    <span><i class="fa-solid fa-wallet"></i></span>
                    <div>
                        <h2>Rincian Tunjangan Kinerja</h2>
                        <p>Kalkulasi berdasarkan hasil penilaian dan presensi.</p>
                    </div>
                </div>
                <div class="kepala-kpi-result-money-row">
                    <span>Base Tukin Jabatan</span>
                    <strong>Rp {{ number_format((float) ($pegawai->jabatan->base_tukin ?? 0), 0, ',', '.') }}</strong>
                </div>
                <div class="kepala-kpi-result-money-row">
                    <span>Tukin Bruto (Capaian {{ number_format($nilaiAkhir, 1, ',', '.') }}%)</span>
                    <strong>Rp {{ number_format($tukinBruto, 0, ',', '.') }}</strong>
                </div>
                <div class="kepala-kpi-result-deduction">
                    <div class="kepala-kpi-result-deduction-heading">
                        <strong><i class="fa-solid fa-circle-minus"></i> Potongan Presensi
                            ({{ number_format($totalPotongan, 1, ',', '.') }}%)</strong>
                        <strong>-Rp {{ number_format($nilaiPotongan, 0, ',', '.') }}</strong>
                    </div>
                    @forelse ($presensiPotongans as $potongan)
                        <div class="kepala-kpi-result-deduction-row">
                            <span>{{ $potongan->jenisPotongan?->nama_potongan ?? 'Potongan Presensi' }}
                                ({{ $potongan->jumlah_kejadian }} kejadian)
                                <small>Tarif
                                    {{ number_format((float) ($potongan->jenisPotongan?->persen_potongan ?? 0), 2, ',', '.') }}%
                                    per kejadian</small>
                            </span>
                            <strong>-Rp
                                {{ number_format($tukinBruto * (float) $potongan->persen_potongan / 100, 0, ',', '.') }}</strong>
                        </div>
                    @empty
                        <div class="kepala-kpi-result-deduction-row">
                            <span>Tidak ada potongan presensi</span><strong>Rp 0</strong>
                        </div>
                    @endforelse
                </div>
                <div class="kepala-kpi-result-net">
                    <small>TUKIN NETO DITERIMA</small>
                    <strong>Rp {{ number_format((float) ($hasilTunjangan->tukin_neto ?? 0), 0, ',', '.') }}</strong>
                </div>
                <p class="kepala-kpi-result-tukin-date">
                    <i class="fa-regular fa-clock"></i>
                    Dihitung otomatis pada
                    <strong>{{ $hasilTunjangan->updated_at?->translatedFormat('d M Y, H:i') ?? '-' }} WIB</strong>
                </p>
            @else
                <div class="kepala-kpi-result-tukin-empty">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <div>
                        <strong>Hasil tunjangan belum tersedia</strong>
                        <p>Hasil Tukin akan muncul setelah penilaian KPI dan kompetensi atasan tersimpan.</p>
                    </div>
                </div>
            @endif
        </section>

        <p class="kepala-kpi-result-timestamp">
            <i class="fa-regular fa-clock"></i>
            <span>Penilaian terakhir diperbarui
                <strong>{{ $tanggalPenilaian ? $tanggalPenilaian . ' WIB' : '-' }}</strong>
            </span>
        </p>

        <div class="kepala-kpi-result-actions">
            <button type="button" class="kepala-kpi-result-print" id="kepalaKpiResultPrint">
                <i class="fa-solid fa-download" aria-hidden="true"></i> Cetak PDF
            </button>
            <a class="kepala-kpi-result-back"
                href="{{ route('kepala_bagian.penilaian-atasan.index', $periodeId ? ['periode_id' => $periodeId] : []) }}">
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Kembali
            </a>
        </div>
    </main>
@endsection

@push('styles')
    <style>
        body.role-kepala_bagian .kepala-kpi-result-page {
            box-sizing: border-box;
            width: min(1120px, 100%);
            margin: 0 auto;
            padding: 1.4rem 1.5rem 2rem;
            color: #172b43;
        }

        .kepala-kpi-result-header,
        .kepala-kpi-result-meta,
        .kepala-kpi-result-person,
        .kepala-kpi-result-section-title,
        .kepala-kpi-result-stat-row,
        .kepala-kpi-result-progress-label,
        .kepala-kpi-result-actions {
            display: flex;
            align-items: center;
        }

        .kepala-kpi-result-header {
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .kepala-kpi-result-header h1 {
            margin: 0;
            font-size: 1.35rem;
            font-weight: 800;
        }

        .kepala-kpi-result-header h1 i {
            color: #078ac8;
        }

        .kepala-kpi-result-header p {
            margin: .25rem 0 0;
            color: #708198;
            font-size: .82rem;
        }

        .kepala-kpi-result-meta {
            justify-content: flex-end;
            flex-wrap: wrap;
            gap: .5rem;
        }

        .kepala-kpi-result-meta span {
            padding: .5rem .7rem;
            color: #526480;
            background: #edf3f8;
            border-radius: .55rem;
            font-size: .72rem;
        }

        .kepala-kpi-result-meta .kepala-kpi-result-status {
            color: #8a5700;
            background: #fff4d9;
        }

        .kepala-kpi-result-status i {
            margin-right: .25rem;
            color: #d18800;
            font-size: .55rem;
        }

        .kepala-kpi-result-status.is-sent {
            color: #126341;
            background: #e7f8ef;
        }

        .kepala-kpi-result-status.is-sent i {
            color: #15945e;
        }

        .kepala-kpi-result-person,
        .kepala-kpi-result-summary,
        .kepala-kpi-result-tukin {
            padding: 1rem;
            background: #fff;
            border: 1px solid #e1eaf2;
            border-radius: .85rem;
        }

        .kepala-kpi-result-person {
            gap: .8rem;
            margin-bottom: .8rem;
        }

        .kepala-kpi-result-avatar {
            display: grid;
            place-items: center;
            width: 3rem;
            height: 3rem;
            flex: 0 0 3rem;
            color: #fff;
            background: linear-gradient(145deg, #0b4e79, #1689aa);
            border-radius: .7rem;
            font-size: .9rem;
            font-weight: 800;
        }

        .kepala-kpi-result-person h2 {
            margin: 0 0 .2rem;
            font-size: 1rem;
        }

        .kepala-kpi-result-person p,
        .kepala-kpi-result-person small {
            display: block;
            margin: 0;
            color: #0879ad;
            font-size: .78rem;
        }

        .kepala-kpi-result-person small {
            margin-top: .15rem;
            color: #708198;
        }

        .kepala-kpi-result-summary {
            margin-bottom: .8rem;
        }

        .kepala-kpi-result-section-title {
            gap: .65rem;
            margin-bottom: .6rem;
        }

        .kepala-kpi-result-section-title > span {
            display: grid;
            place-items: center;
            width: 2.5rem;
            height: 2.5rem;
            flex: 0 0 2.5rem;
            color: #075078;
            background: #e2efff;
            border-radius: .65rem;
        }

        .kepala-kpi-result-section-title h2 {
            margin: 0;
            font-size: 1rem;
        }

        .kepala-kpi-result-section-title p {
            margin: .2rem 0 0;
            color: #7b8493;
            font-size: .72rem;
        }

        .kepala-kpi-result-stat-row {
            justify-content: space-between;
            gap: .75rem;
            padding: .65rem 0;
            color: #596270;
            border-bottom: 1px solid #edf1f6;
            font-size: .82rem;
        }

        .kepala-kpi-result-stat-row strong {
            color: #172b43;
            text-align: right;
        }

        .kepala-kpi-result-stat-row strong small {
            color: #708198;
            font-size: .7rem;
            font-weight: 500;
        }

        .kepala-kpi-result-total {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-top: .75rem;
            padding: .8rem 1rem;
            color: #073b62;
            background: #e2efff;
            border-radius: .8rem;
        }

        .kepala-kpi-result-total small {
            font-size: .72rem;
            font-weight: 800;
        }

        .kepala-kpi-result-total strong {
            font-size: 1.25rem;
        }

        .kepala-kpi-result-tukin {
            margin-top: .8rem;
        }

        .kepala-kpi-result-money-row {
            display: flex;
            justify-content: space-between;
            gap: .75rem;
            padding: .65rem 0;
            color: #596270;
            font-size: .82rem;
        }

        .kepala-kpi-result-money-row strong {
            flex: 0 0 auto;
            color: #172b43;
            text-align: right;
        }

        .kepala-kpi-result-deduction {
            margin-top: .15rem;
            padding: .75rem;
            color: #bd1e2d;
            background: #eff4ff;
            border-radius: .75rem;
        }

        .kepala-kpi-result-deduction-heading,
        .kepala-kpi-result-deduction-row {
            display: flex;
            justify-content: space-between;
            gap: .6rem;
            font-size: .75rem;
        }

        .kepala-kpi-result-deduction-heading {
            align-items: flex-start;
            padding-bottom: .45rem;
        }

        .kepala-kpi-result-deduction-heading > strong:last-child,
        .kepala-kpi-result-deduction-row > strong {
            flex: 0 0 auto;
            text-align: right;
        }

        .kepala-kpi-result-deduction-row {
            padding-top: .45rem;
            color: #505a69;
        }

        .kepala-kpi-result-deduction-row > span {
            min-width: 0;
        }

        .kepala-kpi-result-deduction-row small {
            display: block;
            margin-top: .15rem;
            color: #8490a2;
        }

        .kepala-kpi-result-net {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-top: .8rem;
            padding: 1rem;
            color: #073b62;
            background: #e2efff;
            border-radius: .8rem;
        }

        .kepala-kpi-result-net small {
            font-size: .75rem;
            font-weight: 800;
        }

        .kepala-kpi-result-net strong {
            font-size: 1.3rem;
        }

        .kepala-kpi-result-tukin-date {
            display: flex;
            align-items: center;
            gap: .45rem;
            margin: .75rem 0 0;
            color: #708198;
            font-size: .75rem;
        }

        .kepala-kpi-result-tukin-empty {
            display: flex;
            gap: .7rem;
            color: #936000;
            background: #fff7df;
            padding: .9rem;
            border: 1px solid #f4d68a;
            border-radius: .7rem;
        }

        .kepala-kpi-result-tukin-empty > i {
            margin-top: .1rem;
        }

        .kepala-kpi-result-tukin-empty strong {
            font-size: .8rem;
        }

        .kepala-kpi-result-tukin-empty p {
            margin: .25rem 0 0;
            font-size: .72rem;
            line-height: 1.5;
        }

        .kepala-kpi-result-timestamp {
            display: flex;
            align-items: center;
            gap: .5rem;
            margin: .8rem 0;
            color: #708198;
            font-size: .78rem;
        }

        .kepala-kpi-result-timestamp i {
            color: #8293aa;
        }

        .kepala-kpi-result-timestamp strong {
            color: #526480;
        }

        .kepala-kpi-result-actions {
            gap: .7rem;
        }

        .kepala-kpi-result-actions > * {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            min-height: 3.2rem;
            flex: 1;
            padding: .75rem 1rem;
            border: 1px solid transparent;
            border-radius: .8rem;
            font: inherit;
            font-size: .88rem;
            font-weight: 750;
            text-decoration: none;
            cursor: pointer;
        }

        .kepala-kpi-result-print {
            color: #fff;
            background: #073b62;
        }

        .kepala-kpi-result-back {
            color: #36506b;
            background: #edf3f8;
            border-color: #dce6ef;
        }

        @media (max-width: 700px) {
            body.role-kepala_bagian .kepala-kpi-result-page {
                padding: 1rem 1rem 1.5rem;
            }

            .kepala-kpi-result-header {
                align-items: flex-start;
                flex-direction: column;
            }

            .kepala-kpi-result-meta {
                justify-content: flex-start;
            }
        }

        @media (max-width: 420px) {
            .kepala-kpi-result-actions {
                align-items: stretch;
                flex-direction: column;
            }

            .kepala-kpi-result-values {
                gap: .35rem;
            }

            .kepala-kpi-result-values > div {
                padding: .5rem .25rem;
            }

            .kepala-kpi-result-deduction-heading,
            .kepala-kpi-result-deduction-row {
                font-size: .68rem;
            }

        }

        @media print {
            body.role-kepala_bagian .kepala-kpi-result-page {
                width: 100%;
                max-width: none;
                padding: 0;
            }

            .kepala-kpi-result-actions {
                display: none;
            }

            .kepala-kpi-result-person,
            .kepala-kpi-result-summary,
            .kepala-kpi-result-tukin {
                break-inside: avoid;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.getElementById('kepalaKpiResultPrint')?.addEventListener('click', () => window.print());
    </script>
@endpush
