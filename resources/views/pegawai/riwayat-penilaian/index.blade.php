@extends('layouts.app')

@section('title', 'Riwayat Penilaian')

@section('content')
    @php
        $currentResult = $hasilPenilaians->first();
        $statusLabel = fn ($status) => $status === 'dikirim' ? 'Dikirim' : 'Draft';
        $predicateClass = fn ($predikat) => match ($predikat) {
            'Sangat Baik' => 'assessment-excellent',
            'Baik' => 'assessment-good',
            'Cukup' => 'assessment-fair',
            'Kurang' => 'assessment-poor',
            default => 'assessment-neutral',
        };
    @endphp

    <div class="pegawai-page assessment-history-page">
        <div class="pegawai-page-heading">
            <div>
                <span class="assessment-breadcrumb">Dashboard / <strong>Riwayat Penilaian</strong></span>
                <h1>Riwayat Penilaian</h1>
                <p>Rekap capaian kinerja dan pencairan tunjangan kinerja.</p>
            </div>
        </div>

        <div class="assessment-filter-tabs" aria-label="Filter periode">
            <span class="active">Tahun {{ now()->year }}</span>
            <span>Tahun {{ now()->year - 1 }}</span>
            <span>Triwulan III</span>
            <span>Semua</span>
        </div>

        @if ($currentResult && $currentResult->status !== 'dikirim')
            <section class="assessment-current-card">
                <div class="assessment-current-top">
                    <span><i class="fa-solid fa-circle"></i> PENILAIAN TERBARU</span>
                    <b>{{ $statusLabel($currentResult->status) }}</b>
                </div>
                <div class="assessment-current-main">
                    <div>
                        <span>{{ $currentResult->periode->nama_periode ?? 'Periode Penilaian' }}</span>
                        <strong>Rp {{ number_format($currentResult->tukin_neto ?? 0, 0, ',', '.') }}</strong>
                        <small>Tunjangan Kinerja Bersih</small>
                    </div>
                    <div class="assessment-score">
                        <span>Nilai Akhir</span>
                        <strong>{{ $currentResult->nilai_akhir ?? '-' }}</strong>
                        <em>{{ $currentResult->predikat ?? '-' }}</em>
                    </div>
                </div>
                <div class="assessment-current-details">
                    <div><span>Tukin Bruto</span><strong>Rp {{ number_format($currentResult->tukin_bruto ?? 0, 0, ',', '.') }}</strong></div>
                    <div><span>Potongan Presensi</span><strong>{{ $currentResult->total_potongan_presensi ?? 0 }}%</strong></div>
                </div>
                <div class="assessment-current-footer">
                    <span><i class="fa-regular fa-calendar"></i> {{ $currentResult->periode->nama_periode ?? '-' }}</span>
                    <a href="{{ route('pegawai.riwayat-penilaian.show', $currentResult) }}">Lihat detail <i class="fa-solid fa-arrow-right"></i></a>
                </div>
            </section>
        @endif

        <div class="assessment-history-heading">
            <h2><i class="fa-regular fa-clipboard"></i> Riwayat Periode Selesai</h2>
            <span>Menampilkan {{ $hasilPenilaians->count() }} periode terakhir</span>
        </div>

        <div class="assessment-history-list">
            @forelse ($hasilPenilaians as $hasil)
                <article class="assessment-history-card">
                    <div class="assessment-card-heading">
                        <div>
                            <h3>{{ $hasil->periode->nama_periode ?? '-' }}</h3>
                            <span>{{ $hasil->dikirim_at?->translatedFormat('d F Y') ?? 'Tanggal penetapan belum tersedia' }}</span>
                        </div>
                        <div class="assessment-final-score">
                            <span>Nilai Akhir</span>
                            <strong>{{ $hasil->nilai_akhir ?? '-' }}</strong>
                        </div>
                    </div>
                    <div class="assessment-card-tags">
                        <span class="{{ $predicateClass($hasil->predikat) }}">{{ $hasil->predikat ?? '-' }}</span>
                        <span class="assessment-status-{{ $hasil->status }}">{{ $statusLabel($hasil->status) }}</span>
                    </div>
                    <div class="assessment-money-row">
                        <div><span>Tukin Bruto</span><strong>Rp {{ number_format($hasil->tukin_bruto ?? 0, 0, ',', '.') }}</strong></div>
                        <div><span>Potongan Presensi</span><strong class="deduction">- {{ $hasil->total_potongan_presensi ?? 0 }}%</strong></div>
                        <div><span>Tukin Bersih</span><strong>Rp {{ number_format($hasil->tukin_neto ?? 0, 0, ',', '.') }}</strong></div>
                    </div>
                    <a href="{{ route('pegawai.riwayat-penilaian.show', $hasil) }}" class="assessment-detail-button">
                        <i class="fa-regular fa-eye"></i> Lihat Rincian Nilai & Tukin
                    </a>
                </article>
            @empty
                <div class="assessment-empty">
                    <i class="fa-regular fa-folder-open"></i>
                    <strong>Belum ada riwayat penilaian</strong>
                    <span>Data hasil penilaian akan tampil di halaman ini.</span>
                </div>
            @endforelse
        </div>

        <div class="assessment-help"><i class="fa-regular fa-circle-question"></i><span>Contoh tampilan jika periode kosong</span><i class="fa-solid fa-chevron-down"></i></div>
        <a href="#" class="assessment-download"><i class="fa-solid fa-download"></i> Unduh Rekapitulasi Tahunan (PDF)</a>
        <div class="assessment-pagination">{{ $hasilPenilaians->links() }}</div>
    </div>
@endsection
