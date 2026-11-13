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
    <link rel="stylesheet" href="{{ asset('css/pegawai/riwayat-penilaian/show.css') }}">
@endpush
