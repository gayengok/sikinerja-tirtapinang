@extends('layouts.app')

@section('title', 'Detail Laporan Harian')

@section('content')
    @php
        $statusLabels = [
            'draft' => 'Draft',
            'diajukan' => 'Menunggu Review',
            'disetujui' => 'Disetujui',
            'ditolak' => 'Ditolak',
        ];
        $statusLabel = $statusLabels[$laporanHarian->status] ?? ucfirst($laporanHarian->status);
        $pegawai = $laporanHarian->pegawai;
        $details = $laporanHarian->details;
        $kpiDetails = $details->filter(fn($detail) => (bool) $detail->templateLaporan?->terhubung_ke_kpi_id);
        $reportNote = trim($laporanHarian->catatan ?? '');
        $rejectionReason = null;
        foreach (['[Ditolak oleh atasan]:', '[Ditolak oleh Kepala Bagian]:'] as $rejectionMarker) {
            if (str_contains($reportNote, $rejectionMarker)) {
                [$reportNote, $rejectionReason] = explode($rejectionMarker, $reportNote, 2);
                $reportNote = trim($reportNote);
                $rejectionReason = trim($rejectionReason);
                break;
            }
        }
    @endphp
    <div class="atasan-report-detail-page">
        <div class="report-approval-heading">
            <div><span>Evaluasi Detail</span><strong>#LPR-{{ str_pad($laporanHarian->id, 6, '0', STR_PAD_LEFT) }}</strong></div>
            <span class="report-status-pill status-{{ $laporanHarian->status }}">
                <i class="fa-solid fa-circle"></i> {{ $statusLabel }}
            </span>
        </div>

        <section class="report-employee-banner">
            <div class="report-employee-main">
                <span class="report-employee-avatar">{{ strtoupper(substr($pegawai->nama, 0, 2)) }}</span>
                <div class="report-employee-details">
                    <h1>{{ $pegawai->nama }}
                        @if ($laporanHarian->status === 'disetujui')
                            <i class="fa-solid fa-circle-check" aria-label="Terverifikasi"></i>
                        @endif
                    </h1>
                    <p>{{ $pegawai->jabatan->nama_jabatan ?? '-' }}</p>
                    <small><i class="fa-solid fa-building"></i>
                        {{ $pegawai->jabatan->divisi->nama_divisi ?? 'Divisi belum tersedia' }}</small>
                </div>
                <i class="fa-solid {{ $laporanHarian->status === 'disetujui' ? 'fa-shield-halved' : 'fa-file-circle-check' }} report-banner-watermark"
                    aria-hidden="true"></i>
            </div>
            <div class="report-validation-status status-{{ $laporanHarian->status }}">
                <span>Status Validasi</span>
                <strong>
                    <i class="fa-solid {{ $laporanHarian->status === 'disetujui' ? 'fa-check-double' : 'fa-clock' }}"></i>
                    @if ($laporanHarian->status === 'disetujui')
                        {{ auth()->user()->role === 'kepala_bagian' ? 'Disetujui Kepala Bagian' : 'Disetujui Atasan' }}
                    @elseif ($laporanHarian->status === 'diajukan')
                        Menunggu Persetujuan
                    @elseif ($laporanHarian->status === 'ditolak')
                        Perlu Perbaikan
                    @else
                        Belum Diajukan
                    @endif
                </strong>
            </div>
        </section>

        <div class="report-detail-layout">
            <main class="report-detail-main">
                <section class="report-info-card">
                    <div class="report-card-heading">
                        <span class="report-card-icon"><i class="fa-regular fa-file-lines"></i></span>
                        <div>
                            <h2>Informasi Laporan</h2>
                            <p>Metadata verifikasi dan tanggal pelaksanaan</p>
                        </div>
                    </div>
                    <div class="report-info-rows">
                        <div class="report-info-row">
                            <span>Tanggal Kegiatan</span>
                            <strong>{{ $laporanHarian->tanggal->translatedFormat('d F Y') }}</strong>
                        </div>
                        <div class="report-info-row">
                            <span>Divisi / Unit Kerja</span>
                            <strong>{{ $pegawai->jabatan->divisi->nama_divisi ?? '-' }}</strong>
                        </div>
                        <div class="report-info-row">
                            <span>Jabatan</span>
                            <strong>{{ $pegawai->jabatan->nama_jabatan ?? '-' }}</strong>
                        </div>
                        <div class="report-info-row">
                            <span>Status Verifikasi</span>
                            <strong class="report-inline-status status-{{ $laporanHarian->status }}">
                                <i class="fa-solid fa-circle"></i> {{ $statusLabel }}
                            </strong>
                        </div>
                        <div class="report-info-row">
                            <span>Diproses Oleh</span>
                            <strong>{{ $laporanHarian->penyetuju->nama ?? ($laporanHarian->status === 'diajukan' ? 'Menunggu atasan' : '-') }}</strong>
                        </div>
                    </div>
                    <div class="report-note">
                        <strong><i class="fa-solid fa-pen-to-square"></i> CATATAN KEGIATAN & RINGKASAN KERJA</strong>
                        <p>{{ $reportNote ?: 'Tidak ada catatan kegiatan.' }}</p>
                    </div>
                    @if ($rejectionReason)
                        <div class="report-rejection-note">
                            <strong><i class="fa-solid fa-circle-exclamation"></i> CATATAN PERBAIKAN</strong>
                            <p>{{ $rejectionReason }}</p>
                        </div>
                    @endif
                </section>

                <section class="report-work-card">
                    <div class="report-card-heading report-work-heading">
                        <span class="report-card-icon"><i class="fa-solid fa-list-check"></i></span>
                        <div>
                            <h2>Detail Pekerjaan</h2>
                            <p>Rincian indikator dan output yang dilaporkan</p>
                        </div>
                        <span class="report-output-count">{{ $details->count() }} Output</span>
                    </div>
                    <div class="report-work-list">
                        @forelse ($details as $detail)
                            @php
                                $isKpi = (bool) $detail->templateLaporan?->terhubung_ke_kpi_id;
                            @endphp
                            <article class="report-work-item {{ $isKpi ? 'is-kpi' : '' }}">
                                <div class="report-work-title">
                                    <span class="report-work-index">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                    <h3>{{ $detail->templateLaporan->nama_field ?? 'Indikator tidak tersedia' }}</h3>
                                    @if ($isKpi)
                                        <span class="report-kpi-badge"><i class="fa-solid fa-bullseye"></i> KPI</span>
                                    @endif
                                </div>
                                <div class="report-work-value">
                                    <span><small>DILAPORKAN</small><strong>{{ $detail->nilai ?? '-' }}</strong>
                                        {{ $detail->templateLaporan->satuan ?? '' }}</span>
                                </div>
                            </article>
                        @empty
                            <p class="report-empty">Tidak ada detail pekerjaan pada laporan ini.</p>
                        @endforelse
                    </div>
                </section>
            </main>

            <aside class="report-detail-sidebar">
                <section class="report-validation-card">
                    <div class="report-card-heading">
                        <span class="report-card-icon"><i class="fa-solid fa-shield-halved"></i></span>
                        <div>
                            <h2>Ringkasan Validasi</h2>
                            <p>Ikhtisar isi laporan</p>
                        </div>
                    </div>
                    <div class="report-validation-result status-{{ $laporanHarian->status }}">
                        <span class="report-validation-mark"><i class="fa-solid {{ $laporanHarian->status === 'disetujui' ? 'fa-check' : ($laporanHarian->status === 'ditolak' ? 'fa-xmark' : 'fa-clock') }}"></i></span>
                        <div><small>STATUS LAPORAN</small><strong>{{ $statusLabel }}</strong></div>
                    </div>
                    <div class="report-validation-metrics">
                        <div><span>Total indikator</span><strong>{{ $details->count() }}</strong></div>
                        <div><span>Terhubung ke KPI</span><strong>{{ $kpiDetails->count() }} / {{ $details->count() }}</strong></div>
                    </div>
                    <p class="report-validation-caption">
                        {{ $kpiDetails->count() > 0 ? 'Output bertanda KPI akan diperhitungkan pada evaluasi kinerja.' : 'Tidak ada indikator yang terhubung ke perhitungan KPI.' }}
                    </p>
                </section>

                <section class="report-history-card">
                    <div class="report-card-heading">
                        <span class="report-card-icon"><i class="fa-solid fa-clock-rotate-left"></i></span>
                        <div>
                            <h2>Alur Riwayat Approval</h2>
                            <p>Perjalanan laporan</p>
                        </div>
                    </div>
                    <ol class="report-history-list">
                        <li class="is-complete">
                            <span class="report-history-dot"><i class="fa-solid fa-check"></i></span>
                            <div><strong>Laporan dibuat</strong><small>{{ $laporanHarian->created_at?->translatedFormat('d M Y, H:i') ?? '-' }} WIB</small></div>
                        </li>
                        <li class="{{ $laporanHarian->status !== 'draft' ? 'is-complete' : '' }}">
                            <span class="report-history-dot"><i class="fa-solid {{ $laporanHarian->status !== 'draft' ? 'fa-check' : 'fa-minus' }}"></i></span>
                            <div><strong>Diajukan untuk review</strong><small>{{ $laporanHarian->status === 'draft' ? 'Belum diajukan' : 'Laporan masuk antrean approval' }}</small></div>
                        </li>
                        <li class="{{ in_array($laporanHarian->status, ['disetujui', 'ditolak']) ? 'is-complete' : '' }}">
                            <span class="report-history-dot"><i class="fa-solid {{ in_array($laporanHarian->status, ['disetujui', 'ditolak']) ? 'fa-check' : 'fa-minus' }}"></i></span>
                            <div>
                                <strong>{{ $laporanHarian->status === 'ditolak' ? 'Perlu perbaikan' : 'Verifikasi atasan' }}</strong>
                                <small>
                                    @if ($laporanHarian->disetujui_at)
                                        {{ $laporanHarian->disetujui_at->translatedFormat('d M Y, H:i') }} WIB
                                    @elseif ($laporanHarian->status === 'diajukan')
                                        Menunggu pemeriksaan
                                    @else
                                        Belum diproses
                                    @endif
                                </small>
                            </div>
                        </li>
                    </ol>
                    @if ($laporanHarian->penyetuju)
                        <div class="report-history-approver">
                            <span>{{ strtoupper(substr($laporanHarian->penyetuju->nama, 0, 2)) }}</span>
                            <div><small>Diproses oleh</small><strong>{{ $laporanHarian->penyetuju->nama }}</strong></div>
                        </div>
                    @endif
                </section>

                <section class="report-attachment-card">
                    <div class="report-card-heading">
                        <span class="report-card-icon"><i class="fa-solid fa-paperclip"></i></span>
                        <div><h2>Dokumen Lampiran</h2><p>Bukti kegiatan</p></div>
                    </div>
                    @if ($laporanHarian->bukti_gambar)
                        <a class="report-attachment-preview" href="{{ route($routePrefix . '.laporan.image', $laporanHarian) }}" target="_blank" rel="noopener">
                            <img src="{{ route($routePrefix . '.laporan.image', $laporanHarian) }}" alt="Bukti kegiatan {{ $laporanHarian->tanggal->translatedFormat('d F Y') }}">
                            <span><i class="fa-solid fa-up-right-from-square"></i> Lihat lampiran</span>
                        </a>
                    @else
                        <div class="report-attachment-empty"><i class="fa-regular fa-image"></i><span>Tidak ada lampiran untuk laporan ini.</span></div>
                    @endif
                </section>
            </aside>
        </div>

        @if ($laporanHarian->status === 'diajukan')
            <section class="report-action-card" id="reject">
                <div class="report-card-heading">
                    <span class="report-card-icon"><i class="fa-solid fa-shield-halved"></i></span>
                    <div>
                        <h2>Tindakan Approval</h2>
                        <p>Periksa laporan sebelum memberikan keputusan</p>
                    </div>
                </div>
                <form id="approval-form" action="{{ route($routePrefix . '.laporan.approve', $laporanHarian) }}" method="POST"
                    onsubmit="return confirm('Apakah Anda yakin ingin menyetujui laporan ini?');">
                    @csrf
                    <button class="report-approve-button" type="submit"><i class="fa-solid fa-check"></i> Setujui
                        Laporan</button>
                </form>
                <form action="{{ route($routePrefix . '.laporan.reject', $laporanHarian) }}" method="POST"
                    onsubmit="return confirm('Apakah Anda yakin ingin menolak laporan ini?');">
                    @csrf
                    <label for="alasan_penolakan">Alasan Penolakan</label>
                    <textarea name="alasan_penolakan" id="alasan_penolakan" rows="3"
                        placeholder="Jelaskan alasan penolakan laporan ini...">{{ old('alasan_penolakan') }}</textarea>
                    @error('alasan_penolakan')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                    <button class="report-reject-button" type="submit"><i class="fa-solid fa-xmark"></i> Tolak
                        Laporan</button>
                </form>
            </section>
        @endif
        <a class="report-back-button" href="{{ route($routePrefix . '.laporan.index') }}">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Approval Laporan
        </a>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/atasan/laporan/show.css') }}">
@endpush
