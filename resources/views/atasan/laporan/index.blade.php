@extends('layouts.app')

@section('title', auth()->user()->role === 'kepala_bagian' ? 'Approval Laporan Tim' : 'Approval Laporan Harian')

@section('content')
    @php
        $waitingCount = (int) ($statusCounts['diajukan'] ?? 0);
        $approvedCount = (int) ($statusCounts['disetujui'] ?? 0);
        $rejectedCount = (int) ($statusCounts['ditolak'] ?? 0);
        $draftCount = (int) ($statusCounts['draft'] ?? 0);
        $totalReports = $waitingCount + $approvedCount + $rejectedCount + $draftCount;
        $processedCount = $approvedCount + $rejectedCount;
        $submittedCount = $processedCount + $waitingCount;
        $completionRate = $submittedCount > 0 ? round($processedCount / $submittedCount * 100) : 0;
        $days = collect(range(1, $jumlahHari))->map(fn ($hari) => $awalBulan->copy()->day($hari));
        $maxActivity = max(1, (int) $activityCounts->max());
        $chartStatuses = ['diajukan' => 'Menunggu', 'disetujui' => 'Disetujui', 'ditolak' => 'Ditolak', 'draft' => 'Draft'];
        $statusLabels = ['diajukan' => 'Diajukan', 'disetujui' => 'Disetujui', 'ditolak' => 'Ditolak', 'draft' => 'Draft'];
        $statusIcons = ['diajukan' => 'fa-clock', 'disetujui' => 'fa-circle-check', 'ditolak' => 'fa-circle-xmark', 'draft' => 'fa-file-pen'];
    @endphp

    <div class="atasan-approval-page">
        <header class="atasan-approval-page-heading">
            <div>
                <span class="atasan-approval-eyebrow"><i class="fa-solid fa-circle"></i> MANAJEMEN LAPORAN</span>
                <h1>Approval Laporan Harian</h1>
                <p>Pantau laporan kegiatan pegawai dan tindak lanjuti pengajuan yang membutuhkan persetujuan.</p>
            </div>
            <span class="atasan-approval-heading-count"><i class="fa-regular fa-file-lines"></i> {{ number_format($totalReports, 0, ',', '.') }} total laporan</span>
        </header>

        @if (session('success'))
            <div class="atasan-approval-alert is-success" role="status"><i class="fa-solid fa-circle-check"></i><span>{{ session('success') }}</span></div>
        @endif
        @if (session('error'))
            <div class="atasan-approval-alert is-error" role="alert"><i class="fa-solid fa-circle-exclamation"></i><span>{{ session('error') }}</span></div>
        @endif

        <section class="atasan-approval-summary" aria-label="Ringkasan laporan">
            <div class="atasan-approval-summary-heading">
                <div>
                    <span class="atasan-approval-section-label">RINGKASAN PERIODE</span>
                    <h2>Statistik & Aktivitas Laporan</h2>
                    <p>Rekap status dan aktivitas berdasarkan tanggal kegiatan pada {{ $bulanLabel }}.</p>
                </div>
                <span class="atasan-approval-summary-tag"><i class="fa-regular fa-calendar"></i> {{ $bulanLabel }}</span>
            </div>

            <div class="atasan-approval-stats">
                <div class="atasan-approval-stat is-waiting"><span class="atasan-approval-stat-icon"><i class="fa-regular fa-clock"></i></span><strong>{{ number_format($waitingCount, 0, ',', '.') }}</strong><span>Menunggu</span><small>Perlu ditinjau</small></div>
                <div class="atasan-approval-stat is-approved"><span class="atasan-approval-stat-icon"><i class="fa-solid fa-check-double"></i></span><strong>{{ number_format($approvedCount, 0, ',', '.') }}</strong><span>Disetujui</span><small>Laporan diterima</small></div>
                <div class="atasan-approval-stat is-rejected"><span class="atasan-approval-stat-icon"><i class="fa-solid fa-xmark"></i></span><strong>{{ number_format($rejectedCount, 0, ',', '.') }}</strong><span>Ditolak</span><small>Perlu perbaikan</small></div>
                <div class="atasan-approval-stat is-draft"><span class="atasan-approval-stat-icon"><i class="fa-regular fa-file-lines"></i></span><strong>{{ number_format($draftCount, 0, ',', '.') }}</strong><span>Draft</span><small>Belum diajukan</small></div>
                <div class="atasan-approval-stat is-total"><span class="atasan-approval-stat-icon"><i class="fa-solid fa-layer-group"></i></span><strong>{{ number_format($totalReports, 0, ',', '.') }}</strong><span>Total Laporan</span><small>Seluruh status</small></div>
            </div>

            <div class="atasan-approval-insights">
                <div class="atasan-approval-progress-card">
                    <div class="atasan-approval-insight-title"><strong>Progres Penanganan</strong><span>Dari laporan yang telah diajukan</span></div>
                    <div class="atasan-approval-progress-content">
                        <div class="atasan-approval-ring" style="--progress: {{ $completionRate * 3.6 }}deg"><strong>{{ $completionRate }}%</strong><small>Terproses</small></div>
                        <div class="atasan-approval-breakdown">
                            <div><i class="is-processed"></i><span>Sudah diproses</span><b>{{ $processedCount }}</b></div>
                            <div><i class="is-pending"></i><span>Menunggu keputusan</span><b>{{ $waitingCount }}</b></div>
                            <div><i class="is-submitted"></i><span>Total diajukan</span><b>{{ $submittedCount }}</b></div>
                        </div>
                    </div>
                    <div class="atasan-approval-decision-summary">
                        <div class="atasan-approval-decision-heading"><strong>Komposisi Keputusan</strong><span>{{ $submittedCount }} laporan diajukan</span></div>
                        <div class="atasan-approval-decision-track" role="img" aria-label="{{ $approvedCount }} disetujui, {{ $rejectedCount }} ditolak, {{ $waitingCount }} menunggu keputusan">
                            @if ($submittedCount > 0)
                                <span class="is-approved" style="width: {{ $approvedCount / $submittedCount * 100 }}%"></span>
                                <span class="is-rejected" style="width: {{ $rejectedCount / $submittedCount * 100 }}%"></span>
                                <span class="is-waiting" style="width: {{ $waitingCount / $submittedCount * 100 }}%"></span>
                            @endif
                        </div>
                        <div class="atasan-approval-decision-grid">
                            <div><span><i class="is-approved"></i> Disetujui</span><strong>{{ $approvedCount }}</strong></div>
                            <div><span><i class="is-rejected"></i> Ditolak</span><strong>{{ $rejectedCount }}</strong></div>
                            <div><span><i class="is-waiting"></i> Menunggu</span><strong>{{ $waitingCount }}</strong></div>
                        </div>
                        <p>{{ $submittedCount === 0 ? 'Belum ada laporan yang diajukan pada periode ini.' : ($waitingCount > 0 ? $waitingCount . ' laporan masih menunggu keputusan.' : 'Semua laporan yang diajukan telah mendapat keputusan.') }}</p>
                    </div>
                </div>
                <div class="atasan-approval-activity-card">
                    <div class="atasan-approval-insight-title"><strong>Aktivitas Selama Periode</strong><span>Klik tanggal untuk memfilter daftar laporan · Geser grafik ke samping</span></div>
                    <div class="atasan-approval-chart-legend"><span class="is-waiting">Menunggu</span><span class="is-approved">Disetujui</span><span class="is-rejected">Ditolak</span><span class="is-draft">Draft</span></div>
                    <div class="atasan-approval-chart-scroll" id="approvalChartScroll" tabindex="0" aria-label="Grafik aktivitas laporan per tanggal, dapat digeser horizontal">
                    <div class="atasan-approval-bars" aria-label="Jumlah laporan per tanggal selama periode">
                        @foreach ($days as $day)
                            @php
                                $count = (int) ($activityCounts[$day->toDateString()] ?? 0);
                                $dayStatuses = $activityStatusCounts[$day->toDateString()] ?? [];
                            @endphp
                            <a class="atasan-approval-bar-item {{ (int) $tanggalAktif === $day->day ? 'is-selected' : '' }} {{ $day->isToday() ? 'is-today' : '' }}" href="{{ route($routePrefix . '.laporan.index', array_merge(request()->except('page'), ['bulan' => $bulanAktif, 'tanggal' => (int) $tanggalAktif === $day->day ? null : $day->day])) }}" title="{{ $day->locale('id')->translatedFormat('d F Y') }}: {{ $count }} laporan" aria-label="Lihat laporan tanggal {{ $day->day }}, {{ $count }} laporan" {{ (int) $tanggalAktif === $day->day ? 'aria-current=date' : '' }}>
                                <strong>{{ $count }}</strong>
                                <div class="atasan-approval-bar-track">
                                    <div class="atasan-approval-bar-stack" style="height: {{ $count > 0 ? round($count / $maxActivity * 100, 2) : 0 }}%">
                                        @foreach ($chartStatuses as $chartStatus => $chartLabel)
                                            @php $statusCount = (int) ($dayStatuses[$chartStatus] ?? 0); @endphp
                                            @if ($statusCount > 0 && $count > 0)
                                                <span class="atasan-approval-bar-segment segment-{{ $chartStatus }}" style="height: {{ round($statusCount / $count * 100, 2) }}%" title="{{ $chartLabel }}: {{ $statusCount }}"></span>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                                <small>{{ $day->day }}</small>
                            </a>
                        @endforeach
                    </div>
                    </div>
                    <div class="atasan-approval-chart-footer"><span><i class="fa-regular fa-hand-pointer"></i> Pilih tanggal untuk melihat laporan harian</span><span>{{ $bulanLabel }}</span></div>
                </div>
            </div>
        </section>

        <section class="atasan-approval-directory" aria-label="Daftar laporan">
            <div class="atasan-approval-directory-heading">
                <div><span class="atasan-approval-section-label">DAFTAR LAPORAN</span><h2>Kelola Pengajuan</h2><p>Pilih status dan gunakan filter untuk menemukan laporan pegawai.</p></div>
                <span class="atasan-approval-directory-count"><i class="fa-solid fa-list-check"></i> {{ $laporans->total() }} ditemukan</span>
            </div>

            <nav class="atasan-approval-tabs" aria-label="Filter status laporan">
                @foreach ($statusLabels as $key => $label)
                    <a class="{{ $statusAktif === $key ? 'active' : '' }}" href="{{ route($routePrefix . '.laporan.index', array_merge(request()->except('page'), ['status' => $key])) }}" {{ $statusAktif === $key ? 'aria-current=page' : '' }}>
                        <i class="fa-solid {{ $statusIcons[$key] }}"></i> {{ $label }} <span>{{ (int) ($statusCounts[$key] ?? 0) }}</span>
                    </a>
                @endforeach
            </nav>

            <form class="atasan-approval-search" action="{{ route($routePrefix . '.laporan.index') }}" method="GET">
                <input type="hidden" name="status" value="{{ $statusAktif }}">
                @if($tanggalAktif)<input type="hidden" name="tanggal" value="{{ $tanggalAktif }}">@endif
                <label class="atasan-approval-search-field"><span>Cari laporan</span><span class="atasan-approval-input-wrap"><i class="fa-solid fa-magnifying-glass"></i><input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Nama pegawai, jabatan, uraian kegiatan..." autocomplete="off"></span></label>
                <label class="atasan-approval-date"><span>Periode bulan</span><span class="atasan-approval-input-wrap"><i class="fa-regular fa-calendar"></i><input type="month" name="bulan" value="{{ $bulanAktif }}"></span></label>
                <button class="atasan-approval-apply" type="submit"><i class="fa-solid fa-filter"></i> Terapkan</button>
                @if (!empty($filters['q']) || $bulanAktif !== now()->format('Y-m'))
                    <a class="atasan-approval-clear" href="{{ route($routePrefix . '.laporan.index', ['status' => $statusAktif]) }}" title="Reset filter"><i class="fa-solid fa-rotate-left"></i> Reset</a>
                @endif
            </form>

            <div class="atasan-approval-list-heading"><div><h3>Laporan {{ $tanggalAktif ? $awalBulan->copy()->day($tanggalAktif)->locale('id')->translatedFormat('d F Y') : $bulanLabel }}</h3><p>Status: {{ $statusLabels[$statusAktif] }}{{ $tanggalAktif ? ' · Filter tanggal aktif' : '' }}</p></div><span>{{ $laporans->total() }} laporan @if($tanggalAktif)<a class="atasan-approval-remove-day" href="{{ route($routePrefix . '.laporan.index', request()->except('tanggal', 'page')) }}">Hapus filter tanggal</a>@endif</span></div>
            <div class="atasan-approval-list">
                @forelse ($laporans as $laporan)
                    @php
                        $pegawai = $laporan->pegawai;
                        $detailUrl = route($routePrefix . '.laporan.show', $laporan);
                        $statusText = $statusLabels[$laporan->status] ?? ucfirst($laporan->status);
                    @endphp
                    <article class="atasan-report-card">
                        <div class="atasan-report-top">
                            <span class="atasan-report-avatar">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($pegawai?->nama ?? '?', 0, 2)) }}</span>
                            <div class="atasan-report-identity"><h3>{{ $pegawai?->nama ?? 'Pegawai tidak tersedia' }}</h3><p>{{ $pegawai?->jabatan?->divisi?->nama_divisi ?? 'Divisi tidak tersedia' }}</p></div>
                            <span class="atasan-report-status status-{{ $laporan->status }}"><i class="fa-solid fa-circle"></i> {{ $statusText }}</span>
                        </div>
                        <div class="atasan-report-body">
                            <div class="atasan-report-meta"><div><span><i class="fa-regular fa-calendar"></i> Tanggal kegiatan</span><strong>{{ \Carbon\Carbon::parse($laporan->tanggal)->translatedFormat('d F Y') }}</strong></div><div><span><i class="fa-solid fa-briefcase"></i> Jabatan</span><strong>{{ $pegawai?->jabatan?->nama_jabatan ?? '-' }}</strong></div></div>
                            <div class="atasan-report-description"><span>CATATAN KEGIATAN</span><p>{{ \Illuminate\Support\Str::limit(trim((string) $laporan->catatan) ?: 'Tidak ada catatan kegiatan.', 170) }}</p></div>
                        </div>
                        <div class="atasan-report-footer">
                            <a class="atasan-report-detail" href="{{ $detailUrl }}"><i class="fa-regular fa-eye"></i> Lihat Detail <i class="fa-solid fa-chevron-right"></i></a>
                            @if ($laporan->status === 'diajukan')
                                <form action="{{ route($routePrefix . '.laporan.approve', $laporan) }}" method="POST" onsubmit="return confirm('Yakin ingin menyetujui laporan ini?');">@csrf<button class="atasan-report-approve" type="submit"><i class="fa-solid fa-check"></i> Setujui</button></form>
                                <a class="atasan-report-reject" href="{{ $detailUrl }}#reject"><i class="fa-solid fa-xmark"></i> Tolak</a>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="atasan-approval-empty"><i class="fa-regular fa-folder-open"></i><h3>Tidak Ada Laporan Ditemukan</h3><p>Belum ada laporan dengan status <strong>{{ $statusLabels[$statusAktif] }}</strong> pada {{ $bulanLabel }}{{ !empty($filters['q']) ? ' yang sesuai dengan pencarian Anda' : '' }}.</p><a href="{{ route($routePrefix . '.laporan.index', ['status' => $statusAktif]) }}">Lihat tanpa filter <i class="fa-solid fa-arrow-right"></i></a></div>
                @endforelse
            </div>
            @if ($laporans->hasPages())
                <div class="atasan-approval-pagination">{{ $laporans->links('pagination::bootstrap-5') }}</div>
            @endif
        </section>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/atasan/laporan/index.css') }}?v=2">
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const chart = document.getElementById('approvalChartScroll');
    const selected = chart?.querySelector('.atasan-approval-bar-item.is-selected');
    if (chart && selected) {
        chart.scrollLeft = Math.max(0, selected.offsetLeft - chart.offsetLeft - chart.clientWidth / 2 + selected.clientWidth / 2);
    }
});
</script>
@endpush
