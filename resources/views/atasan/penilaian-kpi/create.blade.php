@extends('layouts.app')
@section('title', ($assessmentRoutePrefix ?? '') === 'kepala_bagian.penilaian-atasan' ? 'Hasil KPI' : 'Form Penilaian KPI')
@section('content')
    @php
        $assessmentRoutePrefix = $assessmentRoutePrefix ?? 'atasan.penilaian-kpi';
        $targetLabel = $targetLabel ?? 'Pegawai';
        $isHeadAssessment = $assessmentRoutePrefix === 'kepala_bagian.penilaian-atasan';
    @endphp
    <div class="kpi-form-page">
        <div class="atasan-mobile-breadcrumb">
            <a href="{{ route($assessmentRoutePrefix . '.index') }}" aria-label="Kembali ke penilaian KPI"><i
                    class="fa-solid fa-arrow-left"></i></a>
            <div><span>Penilaian KPI</span><strong>{{ $isHeadAssessment ? 'Hasil KPI' : 'Form Penilaian' }}</strong></div>
        </div>
        <div class="kpi-form-actions-top"><a href="{{ route($assessmentRoutePrefix . '.index') }}"><i
                    class="fa-solid fa-arrow-left"></i> Kembali</a>
            <h1>{{ $isHeadAssessment ? 'Hasil KPI' : 'Form Penilaian' }}</h1><span></span>
        </div>
        <section class="kpi-person-card"><span class="kpi-avatar">{{ strtoupper(substr($pegawai->nama, 0, 2)) }}</span>
            <div><small>{{ strtoupper($targetLabel) }} YANG DINILAI</small>
                <h1>{{ $pegawai->nama }}</h1>
                <p>{{ $pegawai->jabatan->nama_jabatan ?? '-' }}</p>
            </div><b><small>PERIODE</small>{{ $periode->nama_periode }}</b>
        </section>
        @if ($masterKpis->isEmpty())
            <div class="alert alert-warning">Belum ada Master KPI untuk jabatan ini.</div>
        @else
            <form action="{{ route($assessmentRoutePrefix . '.store', $pegawai) }}" method="POST">@csrf<input type="hidden"
                    name="periode_id" value="{{ $periode->id }}">
                <div class="kpi-form-score"><span><i class="fa-solid fa-calculator"></i> ASPEK HASIL KERJA / KPI (BOBOT
                        60%)</span><strong>— <small>/ 5</small></strong><em>Skor setiap indikator menggunakan skala 1-5</em>
                </div>
                <div class="kpi-indicator-heading">
                    <h2>Rincian Indikator Kinerja ({{ $masterKpis->count() }})</h2><span>Sesuaikan realisasi jika
                        diperlukan</span>
                </div>
                @foreach ($masterKpis as $index => $kpi)
                    @php $tersimpan=$penilaianTersimpan->get($kpi->id); @endphp
                    <article class="kpi-indicator-card">
                        <div class="kpi-indicator-title"><span>{{ $index + 1 }}</span>
                            <div>
                                <h3>{{ $kpi->indikator }}</h3>
                                <p>Arah capaian: {{ ucfirst($kpi->arah_capaian) }}</p>
                            </div><b>Bobot {{ $kpi->bobot }}%</b>
                        </div><input type="hidden" name="kpi[{{ $index }}][master_kpi_id]"
                            value="{{ $kpi->id }}">
                        <div class="kpi-input-grid"><label>Target<input type="number" step="0.01"
                                    name="kpi[{{ $index }}][target]"
                                    value="{{ old('kpi.' . $index . '.target', $tersimpan->target ?? $kpi->target_default) }}"></label><label>Realisasi<input
                                    type="number" step="0.01" name="kpi[{{ $index }}][realisasi_final]"
                                    value="{{ old('kpi.' . $index . '.realisasi_final', $tersimpan->realisasi_final ?? '') }}"></label><label>Skor
                                (1-5)
                                <input type="number" min="1" max="5" name="kpi[{{ $index }}][skor]"
                                    value="{{ old('kpi.' . $index . '.skor', $tersimpan->skor ?? '') }}"></label></div>
                        <div class="kpi-auto-value">Persentase Capaian:
                            <strong>{{ $tersimpan?->persen_capaian !== null ? $tersimpan->persen_capaian . '%' : 'Dihitung otomatis' }}</strong>
                        </div><label class="kpi-note">Keterangan<input type="text"
                                name="kpi[{{ $index }}][keterangan]"
                                value="{{ old('kpi.' . $index . '.keterangan', $tersimpan->keterangan ?? '') }}"
                                placeholder="Tambahkan keterangan..."></label>
                    </article>
                @endforeach
                <label class="kpi-form-note">Catatan Evaluasi Atasan (Opsional)
                    <textarea name="catatan" rows="3" placeholder="Tuliskan catatan pendukung atau rekomendasi kerja..."></textarea>
                </label>
                <div class="kpi-form-actions"><a href="{{ route($assessmentRoutePrefix . '.index') }}">Batal</a><button
                        type="submit"><i class="fa-solid fa-floppy-disk"></i> Simpan Penilaian KPI</button></div>
            </form>
        @endif
</div>@endsection
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/atasan/penilaian-kpi/create.css') }}">
@endpush
