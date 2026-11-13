@extends('layouts.app')
@section('title', 'Detail Penilaian KPI')
@section('content')
    @php
        $assessmentRoutePrefix = $assessmentRoutePrefix ?? 'atasan.penilaian-kpi';
        $targetLabel = $targetLabel ?? 'Pegawai';
        $assessmentEvaluatorLabel = $assessmentEvaluatorLabel ?? 'Kepala Bagian (Atasan)';
    @endphp
    <div class="kpi-detail-page">
        <div class="atasan-mobile-breadcrumb">
            <a href="{{ route($assessmentRoutePrefix . '.index') }}" aria-label="Kembali ke penilaian KPI"><i
                    class="fa-solid fa-arrow-left"></i></a>
            <div><span>Penilaian KPI</span><strong>Detail Penilaian</strong></div>
        </div>
        <div class="kpi-detail-actions-top"><a href="{{ route($assessmentRoutePrefix . '.index') }}"><i
                    class="fa-solid fa-arrow-left"></i> Kembali</a>
            <h1>Detail Penilaian</h1><a class="edit" href="{{ route($assessmentRoutePrefix . '.edit', $pegawai) }}"><i
                    class="fa-solid fa-pen"></i> Edit</a>
        </div>
        <section class="kpi-detail-person"><span class="kpi-avatar">PD</span>
            <div>
                <h2>{{ $pegawai->nama }} <i class="fa-solid fa-circle-check"></i></h2>
                <p>NIP: {{ $pegawai->nip ?? '-' }}</p>
            </div><b>Terverifikasi</b>
            <div class="kpi-person-grid">
                <div><small>Jabatan</small><strong>{{ $pegawai->jabatan->nama_jabatan ?? '-' }}</strong></div>
                <div><small>Divisi / Unit</small><strong>{{ $pegawai->jabatan->divisi->nama_divisi ?? '-' }}</strong></div>
                <div><small>Periode Evaluasi</small><strong>{{ $periode->nama_periode ?? '-' }}</strong></div>
                <div><small>Penilai KPI</small><strong>{{ $assessmentEvaluatorLabel }}</strong></div>
            </div>
        </section>
        <section class="kpi-result-card"><span>ASPEK HASIL KERJA / KPI (BOBOT
                60%)</span><strong>{{ round($penilaianKpis->avg('skor'), 2) }} <small>/ 5</small></strong><b>Skala KPI</b>
            <div>Tanggal Penilaian <strong>{{ now()->translatedFormat('d M Y') }}</strong></div>
        </section>
        <div class="kpi-detail-heading">
            <h2>Capaian Indikator Kinerja</h2><span>{{ $penilaianKpis->count() }} Indikator Terdaftar</span>
        </div>
        <div class="kpi-detail-list">
            @forelse($penilaianKpis as $index=>$item)
                <article>
                    <div class="kpi-detail-item-head"><span>{{ $index + 1 }}</span>
                        <h3>{{ $item->masterKpi->indikator ?? '-' }}</h3><b>Bobot:
                            {{ $item->masterKpi->bobot ?? '-' }}%</b>
                    </div>
                    <p>Persentase Realisasi
                        <strong>{{ $item->persen_capaian !== null ? $item->persen_capaian . '%' : '-' }}</strong>
                    </p><i class="kpi-bar"><em
                            style="width:{{ min(100, max(0, (float) ($item->persen_capaian ?? 0))) }}%"></em></i>
                    <div class="kpi-detail-values">
                        <span>Target<strong>{{ $item->target ?? '-' }}</strong></span><span>Otomatis<strong>{{ $item->realisasi_otomatis ?? '-' }}</strong></span><span>Realisasi
                            Final<strong>{{ $item->realisasi_final ?? '-' }}</strong></span>
                    </div>
                    @if ($item->keterangan)
                        <small class="kpi-detail-note">{{ $item->keterangan }}</small>
                    @endif
                </article>
            @empty<div class="kpi-empty">Belum ada penilaian KPI untuk periode ini.</div>
            @endforelse
        </div>
        <div class="kpi-bottom-actions"><a href="{{ route($assessmentRoutePrefix . '.edit', $pegawai) }}">Edit Penilaian
                Ini</a><a href="#">Cetak / Unduh PDF Penilaian</a></div>
</div>@endsection
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/atasan/penilaian-kpi/show.css') }}">
@endpush
