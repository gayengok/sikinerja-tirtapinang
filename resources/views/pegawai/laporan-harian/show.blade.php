@extends('layouts.app')

@section('title', 'Detail Laporan Harian')

@section('content')
    @php
        $statusLabel = match ($laporan->status) {
            'disetujui' => 'Disetujui',
            'diajukan' => 'Menunggu Review',
            'ditolak' => 'Perlu Revisi',
            default => 'Draft',
        };
    @endphp
    <div class="pegawai-page report-detail-page">
        <div class="report-detail-header">
            <a href="{{ route($routePrefix . '.laporan-harian.index') }}" class="report-back-button"><i
                    class="fa-solid fa-arrow-left"></i></a>
            <div><span>LAPORAN HARIAN / DETAIL</span>
                <h1>Detail Laporan Harian</h1>
            </div>
        </div>

        @if ($laporan->status === 'ditolak')
            <div class="report-detail-alert"><i class="fa-solid fa-circle-exclamation"></i> Laporan ditolak. Perbaiki data
                lalu ajukan kembali.</div>
        @endif

        <section class="report-detail-panel report-overview-panel">
            <div class="report-overview-heading">
                <div><span>ID LAPORAN</span><strong>#LPR-{{ $laporan->id }}</strong></div>
                <span class="report-status report-status-{{ $laporan->status }}">{{ $statusLabel }}</span>
            </div>
            <div class="report-timeline">
                <div class="{{ in_array($laporan->status, ['diajukan', 'disetujui', 'ditolak']) ? 'done' : '' }}"><i
                        class="fa-solid fa-check"></i><span>Diajukan</span></div>
                <div class="{{ in_array($laporan->status, ['disetujui']) ? 'done' : '' }}"><i
                        class="fa-solid fa-check"></i><span>Diverifikasi</span></div>
                <div class="{{ $laporan->status === 'disetujui' ? 'done' : '' }}"><i
                        class="fa-solid fa-check"></i><span>Disetujui</span></div>
            </div>
            <div class="report-overview-meta">
                <div><i class="fa-regular fa-calendar"></i><span>Tanggal
                        Pelaporan</span><strong>{{ $laporan->tanggal->translatedFormat('l, d F Y') }}</strong></div>
                <div><i class="fa-regular fa-user"></i><span>Disetujui
                        Oleh</span><strong>{{ $laporan->penyetuju->nama ?? '-' }}</strong></div>
                <div><i class="fa-regular fa-clock"></i><span>Waktu
                        Verifikasi</span><strong>{{ $laporan->disetujui_at?->translatedFormat('d M Y â€¢ H:i') ?? '-' }}
                        WIB</strong></div>
            </div>
        </section>

        <section class="report-detail-panel">
            <div class="report-section-heading">
                <h2><i class="fa-solid fa-circle"></i> Uraian / Catatan Kegiatan</h2><span>Deskripsi</span>
            </div>
            <div class="report-description">{{ $laporan->catatan ?: 'Tidak ada catatan kegiatan.' }}</div>
            <div class="report-attachment-block">
                <span>Lampiran Dokumentasi</span>
                @if ($laporan->bukti_gambar)
                    <a href="{{ route($routePrefix . '.laporan-harian.image', $laporan) }}" target="_blank" rel="noopener"
                        class="report-image-preview"
                        style="display:block;position:relative;overflow:hidden;border-radius:12px;text-decoration:none;">
                        <img src="{{ route($routePrefix . '.laporan-harian.image', $laporan) }}"
                            alt="Bukti kegiatan laporan harian"
                            style="display:block;width:100%;max-height:260px;object-fit:cover;">
                        <span
                            style="display:block;padding:.6rem;color:#0875ad;background:#eff9ff;font-size:.72rem;text-align:center;"><i
                                class="fa-solid fa-expand"></i> Buka gambar</span>
                    </a>
                @else
                    <div class="report-attachment-item"><i class="fa-regular fa-image"></i>
                        <div><strong>Belum ada bukti gambar</strong><small>Tambahkan melalui menu Edit Laporan</small></div>
                        <span class="report-attachment-placeholder">Opsional</span>
                    </div>
                @endif
            </div>
        </section>

        <section class="report-detail-panel">
            <div class="report-section-heading">
                <h2><i class="fa-solid fa-circle report-green-dot"></i> Detail Pekerjaan & Output</h2>
                <span>{{ $laporan->details->count() }} Indikator</span>
            </div>
            <div class="report-output-list">
                @forelse ($laporan->details as $detail)
                    <div class="report-output-item">
                        <span class="report-output-icon">
                            <i class="fa-solid fa-chart-simple"></i>
                        </span>

                        <div>
                            <strong>{{ $detail->templateLaporan->nama_field ?? '-' }}</strong>
                            <small>{{ $detail->templateLaporan->satuan ?? 'Output pekerjaan' }}</small>
                        </div>

                        <b>
                            Dilaporkan: {{ $detail->nilai ?? '-' }}
                            <em>{{ $detail->templateLaporan->satuan ?? '' }}</em>
                        </b>
                    </div>
                @empty
                    <div class="report-detail-empty">Tidak ada detail pekerjaan.</div>
                @endforelse
            </div>
        </section>

        <div class="report-detail-actions">
            @if (in_array($laporan->status, ['draft', 'ditolak']))
                <a href="{{ route($routePrefix . '.laporan-harian.edit', $laporan) }}" class="report-primary-action"><i
                        class="fa-solid fa-pen"></i> Edit Laporan</a>
            @endif
            @if ($laporan->status === 'draft')
                <form action="{{ route($routePrefix . '.laporan-harian.ajukan', $laporan) }}" method="POST"
                    onsubmit="return confirm('Apakah Anda yakin ingin mengajukan laporan ini?');">
                    @csrf
                    <button type="submit" class="report-primary-action"><i class="fa-solid fa-paper-plane"></i> Ajukan
                        Laporan</button>
                </form>
            @endif
            <a href="{{ route($routePrefix . '.laporan-harian.index') }}" class="report-secondary-action"><i
                    class="fa-solid fa-arrow-left"></i> Kembali ke Daftar Laporan</a>
        </div>
    </div>
@endsection
