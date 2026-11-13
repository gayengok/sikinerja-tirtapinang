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
    <link rel="stylesheet" href="{{ asset('css/kepala-bagian/penilaian-atasan/show.css') }}">
@endpush

@push('scripts')
    <script>
        document.getElementById('kepalaKpiResultPrint')?.addEventListener('click', () => window.print());
    </script>
@endpush
