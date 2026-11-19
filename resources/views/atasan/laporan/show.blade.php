@extends('layouts.app')

@section('title', 'Detail Laporan Harian')

@section('content')
    @php
        $labels = ['draft' => 'Draft', 'diajukan' => 'Menunggu Review', 'disetujui' => 'Disetujui', 'ditolak' => 'Ditolak'];
        $statusLabel = $labels[$laporanHarian->status] ?? ucfirst($laporanHarian->status);
        $pegawai = $laporanHarian->pegawai;
        $details = $laporanHarian->details;
        $kpiCount = $details->filter(fn ($detail) => (bool) $detail->templateLaporan?->terhubung_ke_kpi_id)->count();
        $reportNote = trim((string) $laporanHarian->catatan);
        $rejectionReason = null;
        foreach (['[Ditolak oleh atasan]:', '[Ditolak oleh Kepala Bagian]:', '[Ditolak oleh Direktur]:'] as $marker) {
            if (str_contains($reportNote, $marker)) {
                [$reportNote, $rejectionReason] = explode($marker, $reportNote, 2);
                $reportNote = trim($reportNote);
                $rejectionReason = trim($rejectionReason);
                break;
            }
        }
    @endphp

    <div class="atasan-report-detail-page">
        <div class="atasan-mobile-breadcrumb atasan-report-breadcrumb"><a href="{{ route($routePrefix . '.laporan.index') }}" aria-label="Kembali ke daftar laporan"><i class="fa-solid fa-arrow-left"></i></a><div><span>Approval Laporan</span><strong>Detail Laporan</strong></div></div>
        <header class="report-approval-heading">
            <div><span class="report-eyebrow"><i class="fa-solid fa-circle"></i> VERIFIKASI LAPORAN</span><h1>Detail Laporan Harian</h1><p>Nomor laporan #LPR-{{ str_pad($laporanHarian->id, 6, '0', STR_PAD_LEFT) }}</p></div>
            <span class="report-status-pill status-{{ $laporanHarian->status }}"><i class="fa-solid fa-circle"></i> {{ $statusLabel }}</span>
        </header>
        @if (session('error'))<div class="report-alert is-error" role="alert">{{ session('error') }}</div>@endif
        @if (session('success'))<div class="report-alert is-success" role="status">{{ session('success') }}</div>@endif

        <section class="report-employee-banner">
            <div class="report-employee-main"><span class="report-employee-avatar">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($pegawai?->nama ?? '?', 0, 2)) }}</span><div class="report-employee-details"><h2>{{ $pegawai?->nama ?? 'Pegawai tidak tersedia' }} @if ($laporanHarian->status === 'disetujui')<i class="fa-solid fa-circle-check" aria-label="Disetujui"></i>@endif</h2><p>{{ $pegawai?->jabatan?->nama_jabatan ?? '-' }}</p><small><i class="fa-regular fa-building"></i> {{ $pegawai?->jabatan?->divisi?->nama_divisi ?? 'Divisi tidak tersedia' }}</small></div><i class="fa-regular fa-file-lines report-banner-watermark" aria-hidden="true"></i></div>
            <div class="report-validation-status status-{{ $laporanHarian->status }}"><span><i class="fa-solid fa-shield-halved"></i> Status Validasi</span><strong>{{ $laporanHarian->status === 'disetujui' ? 'Sudah disetujui' : ($laporanHarian->status === 'diajukan' ? 'Menunggu persetujuan' : ($laporanHarian->status === 'ditolak' ? 'Perlu perbaikan' : 'Belum diajukan')) }}</strong></div>
        </section>

        <div class="report-detail-layout">
            <main class="report-detail-main">
                <section class="report-info-card">
                    <div class="report-card-heading"><span class="report-card-icon"><i class="fa-regular fa-file-lines"></i></span><div><h2>Informasi Laporan</h2><p>Data kegiatan dan informasi verifikasi</p></div></div>
                    <div class="report-info-rows">
                        <div class="report-info-row"><span>Tanggal Kegiatan</span><strong>{{ \Carbon\Carbon::parse($laporanHarian->tanggal)->translatedFormat('d F Y') }}</strong></div>
                        <div class="report-info-row"><span>Divisi / Unit Kerja</span><strong>{{ $pegawai?->jabatan?->divisi?->nama_divisi ?? '-' }}</strong></div>
                        <div class="report-info-row"><span>Jabatan</span><strong>{{ $pegawai?->jabatan?->nama_jabatan ?? '-' }}</strong></div>
                        <div class="report-info-row"><span>Status Verifikasi</span><strong><span class="report-inline-status status-{{ $laporanHarian->status }}">{{ $statusLabel }}</span></strong></div>
                        <div class="report-info-row"><span>Diproses Oleh</span><strong>{{ $laporanHarian->penyetuju?->nama ?? 'Belum diproses' }}</strong></div>
                        @if ($laporanHarian->disetujui_at)<div class="report-info-row"><span>Waktu Keputusan</span><strong>{{ \Carbon\Carbon::parse($laporanHarian->disetujui_at)->translatedFormat('d M Y, H:i') }}</strong></div>@endif
                    </div>
                    <div class="report-note"><strong><i class="fa-regular fa-pen-to-square"></i> CATATAN KEGIATAN</strong><p>{{ $reportNote ?: 'Tidak ada catatan kegiatan.' }}</p></div>
                    @if ($rejectionReason)<div class="report-rejection-note"><strong><i class="fa-solid fa-circle-exclamation"></i> ALASAN PENOLAKAN</strong><p>{{ $rejectionReason }}</p></div>@endif
                </section>
                <section class="report-work-card">
                    <div class="report-card-heading"><span class="report-card-icon"><i class="fa-solid fa-list-check"></i></span><div><h2>Detail Pekerjaan</h2><p>Indikator dan hasil yang dilaporkan pegawai</p></div><span class="report-output-count">{{ $details->count() }} Output</span></div>
                    <div class="report-work-list">
                        @forelse ($details as $detail)
                            @php $isKpi = (bool) $detail->templateLaporan?->terhubung_ke_kpi_id; @endphp
                            <article class="report-work-item {{ $isKpi ? 'is-kpi' : '' }}"><div class="report-work-title"><span class="report-work-index">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span><h3>{{ $detail->templateLaporan?->nama_field ?? 'Indikator tidak tersedia' }}</h3>@if ($isKpi)<span class="report-kpi-badge"><i class="fa-solid fa-bullseye"></i> KPI</span>@endif</div><div class="report-work-value"><small>DILAPORKAN</small><strong>{{ $detail->nilai ?? '-' }}</strong><span>{{ $detail->templateLaporan?->satuan ?? '' }}</span></div></article>
                        @empty
                            <div class="report-empty"><i class="fa-regular fa-folder-open"></i> Belum ada detail pekerjaan pada laporan ini.</div>
                        @endforelse
                    </div>
                </section>
            </main>
            <aside class="report-detail-sidebar">
                <section class="report-validation-card"><div class="report-card-heading"><span class="report-card-icon"><i class="fa-solid fa-shield-halved"></i></span><div><h2>Ringkasan Validasi</h2><p>Ikhtisar laporan</p></div></div><div class="report-validation-result status-{{ $laporanHarian->status }}"><span class="report-validation-mark"><i class="fa-solid {{ $laporanHarian->status === 'disetujui' ? 'fa-check' : ($laporanHarian->status === 'ditolak' ? 'fa-xmark' : 'fa-clock') }}"></i></span><div><small>STATUS LAPORAN</small><strong>{{ $statusLabel }}</strong></div></div><div class="report-validation-metrics"><div><span>Total indikator</span><strong>{{ $details->count() }}</strong></div><div><span>Terhubung ke KPI</span><strong>{{ $kpiCount }} / {{ $details->count() }}</strong></div></div><p class="report-validation-caption">{{ $kpiCount ? 'Terdapat indikator yang terhubung dengan KPI.' : 'Tidak ada indikator yang terhubung ke KPI.' }}</p></section>
                <section class="report-history-card"><div class="report-card-heading"><span class="report-card-icon"><i class="fa-solid fa-clock-rotate-left"></i></span><div><h2>Status Proses</h2><p>Tahapan berdasarkan status saat ini</p></div></div><ol class="report-history-list"><li class="is-complete"><span class="report-history-dot"><i class="fa-solid fa-check"></i></span><div><strong>Laporan dibuat</strong><small>{{ $laporanHarian->created_at?->translatedFormat('d M Y, H:i') ?? '-' }}</small></div></li><li class="{{ $laporanHarian->status !== 'draft' ? 'is-complete' : '' }}"><span class="report-history-dot"><i class="fa-solid {{ $laporanHarian->status !== 'draft' ? 'fa-check' : 'fa-minus' }}"></i></span><div><strong>Pengajuan laporan</strong><small>{{ $laporanHarian->status === 'draft' ? 'Belum diajukan' : 'Sudah diajukan' }}</small></div></li><li class="{{ in_array($laporanHarian->status, ['disetujui', 'ditolak']) ? 'is-complete' : '' }}"><span class="report-history-dot"><i class="fa-solid {{ in_array($laporanHarian->status, ['disetujui', 'ditolak']) ? 'fa-check' : 'fa-minus' }}"></i></span><div><strong>Keputusan atasan</strong><small>{{ $laporanHarian->status === 'disetujui' ? 'Disetujui' : ($laporanHarian->status === 'ditolak' ? 'Ditolak' : 'Belum diproses') }}@if ($laporanHarian->disetujui_at) · {{ \Carbon\Carbon::parse($laporanHarian->disetujui_at)->translatedFormat('d M Y, H:i') }}@endif</small></div></li></ol>@if ($laporanHarian->penyetuju)<div class="report-history-approver"><span>{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($laporanHarian->penyetuju->nama, 0, 2)) }}</span><div><small>Diproses oleh</small><strong>{{ $laporanHarian->penyetuju->nama }}</strong></div></div>@endif</section>
                <section class="report-attachment-card"><div class="report-card-heading"><span class="report-card-icon"><i class="fa-solid fa-paperclip"></i></span><div><h2>Lampiran Kegiatan</h2><p>Dokumentasi pendukung</p></div></div>@if ($laporanHarian->bukti_gambar)<a class="report-attachment-preview" href="{{ route($routePrefix . '.laporan.image', $laporanHarian) }}" target="_blank" rel="noopener"><img src="{{ route($routePrefix . '.laporan.image', $laporanHarian) }}" alt="Bukti kegiatan"><span><i class="fa-solid fa-up-right-from-square"></i> Lihat bukti kegiatan</span></a>@else<div class="report-attachment-empty"><i class="fa-regular fa-image"></i> Tidak ada lampiran pada laporan ini.</div>@endif</section>
            </aside>
        </div>

        @if ($laporanHarian->status === 'diajukan')
            <section class="report-action-card" id="reject"><div class="report-card-heading"><span class="report-card-icon"><i class="fa-solid fa-clipboard-check"></i></span><div><h2>Tindakan Approval</h2><p>Pastikan laporan sudah diperiksa sebelum memberikan keputusan.</p></div></div><div class="report-action-layout"><div class="report-approve-panel"><h3>Setujui laporan</h3><p>Setujui apabila informasi dan hasil kegiatan sudah sesuai.</p><form action="{{ route($routePrefix . '.laporan.approve', $laporanHarian) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menyetujui laporan ini?');">@csrf<button class="report-approve-button" type="submit"><i class="fa-solid fa-check"></i> Setujui Laporan</button></form></div><div class="report-reject-panel"><h3>Tolak laporan</h3><p>Berikan alasan agar pegawai mengetahui bagian yang perlu diperbaiki.</p><form action="{{ route($routePrefix . '.laporan.reject', $laporanHarian) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menolak laporan ini?');">@csrf<label for="alasan_penolakan">Alasan Penolakan <span>*</span></label><textarea id="alasan_penolakan" name="alasan_penolakan" rows="3" maxlength="500" required placeholder="Jelaskan alasan penolakan...">{{ old('alasan_penolakan') }}</textarea>@error('alasan_penolakan')<small class="report-field-error">{{ $message }}</small>@enderror<button class="report-reject-button" type="submit"><i class="fa-solid fa-xmark"></i> Tolak Laporan</button></form></div></div></section>
        @endif
        <a class="report-back-button" href="{{ route($routePrefix . '.laporan.index') }}"><i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar Laporan</a>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/atasan/laporan/show.css') }}">
@endpush
