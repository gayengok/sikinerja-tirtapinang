
@extends('layouts.app')

@section('title', 'Hasil Penilaian KPI')

@section('content')
    @php
        $assessmentRoutePrefix = $assessmentRoutePrefix ?? 'kepala_bagian.penilaian-atasan';
        $targetLabel = $targetLabel ?? 'Atasan';
        $periodeId = $periode?->id;
        $statusTunjangan = $hasilTunjangan?->status === 'dikirim'
            ? 'Menunggu Verifikasi Final'
            : 'Draft / Meninjau Ulang';
        $nilaiAkhir = min(100, max(0, (float) ($hasilTunjangan?->nilai_akhir ?? 0)));
        $tukinBruto = (float) ($hasilTunjangan?->tukin_bruto ?? 0);
        $totalPotongan = (float) ($hasilTunjangan?->total_potongan_presensi ?? 0);
        $nilaiPotongan = $tukinBruto * $totalPotongan / 100;
        $namaPegawai = $pegawai->nama ?? 'Pegawai';
        $inisialPegawai = mb_strtoupper(mb_substr($namaPegawai, 0, 2));
    @endphp

    <div class="kepala-kpi-result-page">

        <header class="kepala-kpi-result-hero">
            <div class="kepala-kpi-result-hero-top">
                <div class="kepala-kpi-result-hero-heading">
                    <span class="kepala-kpi-result-eyebrow">
                        <i class="fa-solid fa-circle"></i>
                        PENILAIAN KINERJA
                    </span>

                    <h1>Hasil Penilaian KPI</h1>

                    <p>
                        Ringkasan capaian indikator, hasil penilaian,
                        dan tunjangan kinerja {{ strtolower($targetLabel) }}.
                    </p>
                </div>

                <div class="kepala-kpi-result-hero-period">
                    <span class="kepala-kpi-result-hero-period-icon">
                        <i class="fa-regular fa-calendar-check"></i>
                    </span>

                    <span class="kepala-kpi-result-hero-period-info">
                        <small>PERIODE PENILAIAN</small>
                        <strong>{{ $periode?->nama_periode ?? 'Belum tersedia' }}</strong>
                    </span>
                </div>
            </div>

            <div class="kepala-kpi-result-hero-divider"></div>

            <div class="kepala-kpi-result-hero-bottom">
                <div class="kepala-kpi-result-hero-person">
                    <span class="kepala-kpi-result-avatar">
                        {{ $inisialPegawai }}
                    </span>

                    <div class="kepala-kpi-result-hero-person-info">
                        <small>{{ $targetLabel }} yang Dinilai</small>
                        <h2>{{ $namaPegawai }}</h2>
                        <p>
                            {{ $pegawai->jabatan->nama_jabatan ?? 'Jabatan belum diatur' }}
                            <span class="kepala-kpi-result-person-separator">•</span>
                            {{ $pegawai->jabatan->divisi->nama_divisi ?? 'Unit belum tersedia' }}
                        </p>
                    </div>
                </div>

                <span class="kepala-kpi-result-status {{ $hasilTunjangan?->status === 'dikirim' ? 'is-sent' : ($hasilTunjangan ? 'is-draft' : 'is-empty') }}">
                    <i class="fa-solid fa-circle"></i>
                    {{ $hasilTunjangan ? $statusTunjangan : 'Hasil Belum Dihitung' }}
                </span>
            </div>
        </header>

        <section class="kepala-kpi-result-overview">
            <div class="kepala-kpi-result-overview-heading">
                <div>
                    <span class="kepala-kpi-result-section-eyebrow">OVERVIEW</span>
                    <h2>Ringkasan Hasil Penilaian</h2>
                    <p>Statistik capaian KPI berdasarkan indikator yang telah dinilai.</p>
                </div>
            </div>

            <div class="kepala-kpi-result-stats">
                <article class="kepala-kpi-result-stat-card is-achievement">
                    <div class="kepala-kpi-result-stat-top">
                        <span class="kepala-kpi-result-stat-icon">
                            <i class="fa-solid fa-chart-line"></i>
                        </span>
                        <small>KPI</small>
                    </div>

                    <strong>
                        {{ number_format($capaianKpi, 2, ',', '.') }}%
                    </strong>

                    <h3>Capaian KPI</h3>
                    <p>Persentase capaian KPI tertimbang.</p>
                </article>

                <article class="kepala-kpi-result-stat-card is-score">
                    <div class="kepala-kpi-result-stat-top">
                        <span class="kepala-kpi-result-stat-icon">
                            <i class="fa-solid fa-star"></i>
                        </span>
                        <small>Skor</small>
                    </div>

                    <strong>
                        {{ number_format($rataSkorKpi, 2, ',', '.') }}
                        <span>/ 5</span>
                    </strong>

                    <h3>Rata-rata Skor</h3>
                    <p>Nilai rata-rata indikator KPI.</p>
                </article>

                <article class="kepala-kpi-result-stat-card is-indicator">
                    <div class="kepala-kpi-result-stat-top">
                        <span class="kepala-kpi-result-stat-icon">
                            <i class="fa-solid fa-list-check"></i>
                        </span>
                        <small>Indikator</small>
                    </div>

                    <strong>{{ $penilaianKpis->count() }}</strong>

                    <h3>Indikator Dinilai</h3>
                    <p>Total indikator KPI yang tercatat.</p>
                </article>

                <article class="kepala-kpi-result-stat-card is-final">
                    <div class="kepala-kpi-result-stat-top">
                        <span class="kepala-kpi-result-stat-icon">
                            <i class="fa-solid fa-award"></i>
                        </span>
                        <small>Hasil Akhir</small>
                    </div>

                    <strong>
                        {{ $hasilTunjangan ? number_format($nilaiAkhir, 2, ',', '.') . '%' : '-' }}
                    </strong>

                    <h3>Nilai Akhir</h3>
                    <p>Nilai akhir pada hasil tunjangan.</p>
                </article>
            </div>
        </section>

        @if ($penilaianKpis->isEmpty())
            <div class="kepala-kpi-result-notice">
                <span>
                    <i class="fa-solid fa-circle-info"></i>
                </span>
                <div>
                    <strong>Belum ada indikator KPI yang dinilai</strong>
                    <p>
                        Hasil KPI akan terisi setelah indikator dinilai
                        atau tercatat dari laporan yang disetujui.
                    </p>
                </div>
            </div>
        @endif

        <section class="kepala-kpi-result-summary">
            <div class="kepala-kpi-result-section-title">
                <span>
                    <i class="fa-solid fa-chart-simple"></i>
                </span>

                <div>
                    <span class="kepala-kpi-result-section-eyebrow">
                        ANALISIS PENILAIAN
                    </span>
                    <h2>Rincian Hasil KPI</h2>
                    <p>Rekap capaian berbobot dan skor indikator yang dinilai.</p>
                </div>
            </div>

            <div class="kepala-kpi-result-summary-content">
                <div class="kepala-kpi-result-stat-row">
                    <span>Capaian KPI tertimbang</span>
                    <strong>
                        {{ number_format($capaianKpi, 2, ',', '.') }}%
                    </strong>
                </div>

                <div class="kepala-kpi-result-stat-row">
                    <span>Rata-rata skor indikator</span>
                    <strong>
                        {{ number_format($rataSkorKpi, 2, ',', '.') }}
                        <small>/ 5</small>
                    </strong>
                </div>

                <div class="kepala-kpi-result-total">
                    <div>
                        <small>TOTAL INDIKATOR DINILAI</small>
                        <span>Indikator yang telah memiliki hasil penilaian</span>
                    </div>

                    <strong>{{ $penilaianKpis->count() }}</strong>
                </div>
            </div>
        </section>

        <section class="kepala-kpi-result-tukin">
            <div class="kepala-kpi-result-section-title">
                <span>
                    <i class="fa-solid fa-wallet"></i>
                </span>

                <div>
                    <span class="kepala-kpi-result-section-eyebrow">
                        TUNJANGAN KINERJA
                    </span>
                    <h2>Rincian Perhitungan Tukin</h2>
                    <p>Kalkulasi tunjangan berdasarkan hasil penilaian dan presensi.</p>
                </div>
            </div>

            @if ($hasilTunjangan)
                <div class="kepala-kpi-result-money-content">
                    <div class="kepala-kpi-result-money-row">
                        <span>Base Tukin Jabatan</span>
                        <strong>
                            Rp {{ number_format((float) ($pegawai->jabatan->base_tukin ?? 0), 0, ',', '.') }}
                        </strong>
                    </div>

                    <div class="kepala-kpi-result-money-row">
                        <span>
                            Tukin Bruto
                            <small>
                                Berdasarkan capaian
                                {{ number_format($nilaiAkhir, 1, ',', '.') }}%
                            </small>
                        </span>

                        <strong>
                            Rp {{ number_format($tukinBruto, 0, ',', '.') }}
                        </strong>
                    </div>

                    <div class="kepala-kpi-result-deduction">
                        <div class="kepala-kpi-result-deduction-heading">
                            <div>
                                <span class="kepala-kpi-result-deduction-icon">
                                    <i class="fa-solid fa-circle-minus"></i>
                                </span>

                                <div>
                                    <strong>Potongan Presensi</strong>
                                    <small>
                                        Total potongan
                                        {{ number_format($totalPotongan, 1, ',', '.') }}%
                                    </small>
                                </div>
                            </div>

                            <strong>
                                -Rp {{ number_format($nilaiPotongan, 0, ',', '.') }}
                            </strong>
                        </div>

                        <div class="kepala-kpi-result-deduction-list">
                            @forelse ($presensiPotongans as $potongan)
                                <div class="kepala-kpi-result-deduction-row">
                                    <span>
                                        {{ $potongan->jenisPotongan?->nama_potongan ?? 'Potongan Presensi' }}
                                        ({{ $potongan->jumlah_kejadian }} kejadian)

                                        <small>
                                            Tarif
                                            {{ number_format((float) ($potongan->jenisPotongan?->persen_potongan ?? 0), 2, ',', '.') }}%
                                            per kejadian
                                        </small>
                                    </span>

                                    <strong>
                                        -Rp {{ number_format($tukinBruto * (float) $potongan->persen_potongan / 100, 0, ',', '.') }}
                                    </strong>
                                </div>
                            @empty
                                <div class="kepala-kpi-result-deduction-row">
                                    <span>Tidak ada potongan presensi</span>
                                    <strong>Rp 0</strong>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="kepala-kpi-result-net">
                        <div>
                            <small>TUKIN NETO DITERIMA</small>
                            <span>Jumlah tunjangan setelah potongan presensi</span>
                        </div>

                        <strong>
                            Rp {{ number_format((float) ($hasilTunjangan->tukin_neto ?? 0), 0, ',', '.') }}
                        </strong>
                    </div>

                    <p class="kepala-kpi-result-tukin-date">
                        <i class="fa-regular fa-clock"></i>
                        Dihitung otomatis pada
                        <strong>
                            {{ $hasilTunjangan->updated_at?->translatedFormat('d M Y, H:i') ?? '-' }} WIB
                        </strong>
                    </p>
                </div>
            @else
                <div class="kepala-kpi-result-tukin-empty">
                    <i class="fa-solid fa-triangle-exclamation"></i>

                    <div>
                        <strong>Hasil tunjangan belum tersedia</strong>
                        <p>
                            Hasil Tukin akan muncul setelah penilaian KPI
                            dan kompetensi atasan tersimpan.
                        </p>
                    </div>
                </div>
            @endif
        </section>

        <div class="kepala-kpi-result-actions">
            @if ($assessmentRoutePrefix === 'direktur.penilaian-kepala-bagian')
                <a
                    class="kepala-kpi-result-print"
                    href="{{ route($assessmentRoutePrefix . '.pdf', ['pegawai' => $pegawai, 'periode_id' => $periodeId]) }}"
                >
                    <i class="fa-solid fa-download" aria-hidden="true"></i>
                    Cetak PDF
                </a>
            @else
                <button
                    type="button"
                    class="kepala-kpi-result-print"
                    id="kepalaKpiResultPrint"
                >
                    <i class="fa-solid fa-download" aria-hidden="true"></i>
                    Cetak PDF
                </button>
            @endif

            <a
                class="kepala-kpi-result-back"
                href="{{ route($assessmentRoutePrefix . '.index', $periodeId ? ['periode_id' => $periodeId] : []) }}"
            >
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                Kembali
            </a>
        </div>

    </div>
@endsection

@push('styles')
    <link
        rel="stylesheet"
        href="{{ asset('css/kepala-bagian/penilaian-atasan/show.css') }}?v={{ file_exists(public_path('css/kepala-bagian/penilaian-atasan/show.css')) ? filemtime(public_path('css/kepala-bagian/penilaian-atasan/show.css')) : '1' }}"
    >
@endpush

@push('scripts')
    @if ($assessmentRoutePrefix !== 'direktur.penilaian-kepala-bagian')
        <script>
            document.getElementById('kepalaKpiResultPrint')?.addEventListener('click', () => window.print());
        </script>
    @endif
@endpush
