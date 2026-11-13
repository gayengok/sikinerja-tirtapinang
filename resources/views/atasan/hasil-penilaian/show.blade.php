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
    <link rel="stylesheet" href="{{ asset('css/atasan/hasil-penilaian/show.css') }}">
@endpush
@push('scripts')
    <script>
        document.getElementById('hasilPrintButton')?.addEventListener('click', () => window.print());
    </script>
@endpush
