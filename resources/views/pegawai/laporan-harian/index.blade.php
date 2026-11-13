@extends('layouts.app')

@section('title', 'Laporan Harian')

@section('content')
    @php
        $statusSummary = [
            [
                'key' => 'total',
                'label' => 'Total',
                'value' => $laporans->total(),
                'class' => 'report-total',
                'icon' => 'fa-file-lines',
            ],
            [
                'key' => 'disetujui',
                'label' => 'Disetujui',
                'value' => (int) ($statusCounts['disetujui'] ?? 0),
                'class' => 'report-approved',
                'icon' => 'fa-check',
            ],
            [
                'key' => 'diajukan',
                'label' => 'Menunggu',
                'value' => (int) ($statusCounts['diajukan'] ?? 0),
                'class' => 'report-pending',
                'icon' => 'fa-clock',
            ],
            [
                'key' => 'ditolak',
                'label' => 'Perlu Revisi',
                'value' => (int) ($statusCounts['ditolak'] ?? 0),
                'class' => 'report-revision',
                'icon' => 'fa-circle-exclamation',
            ],
        ];
    @endphp

    <div class="pegawai-page report-page">

        <div class="pegawai-page-heading report-heading">
            <div>
                <h1>
                    <i class="fa-solid fa-file-lines"></i>
                    Laporan Harian Saya
                </h1>
                <p>Buat laporan aktivitas kerja dan pantau status pengajuannya.</p>
            </div>

            <a href="{{ route($routePrefix . '.laporan-harian.create') }}" class="report-create-button">
                <i class="fa-solid fa-plus"></i>
                Buat Laporan
            </a>
        </div>




        <div class="report-summary-grid">
            @foreach ($statusSummary as $summary)
                <div class="report-summary-card {{ $summary['class'] }}">
                    <span class="report-summary-icon"><i class="fa-solid {{ $summary['icon'] }}"></i></span>
                    <strong>{{ $summary['value'] }}</strong>
                    <span>{{ $summary['label'] }}</span>
                </div>
            @endforeach
        </div>

        <section class="pegawai-panel report-filter-panel">
            <div class="report-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="search" id="reportSearch" placeholder="Cari catatan / kegiatan pekerjaan...">
            </div>
            <div class="report-filter-tabs" role="tablist">
                <button type="button" class="active" data-report-filter="all">Semua</button>
                <button type="button" data-report-filter="disetujui">Disetujui</button>
                <button type="button" data-report-filter="diajukan">Menunggu</button>
                <button type="button" data-report-filter="ditolak">Perlu Revisi</button>
            </div>
        </section>

        <div class="attendance-history-heading report-history-heading">
            <h2>Riwayat Laporan <span>{{ $laporans->total() }} Catatan</span></h2>
            <small>Terbaru</small>
        </div>

        <div class="report-history-list" id="reportHistoryList">
            @forelse ($laporans as $laporan)
                @php
                    $statusLabel = match ($laporan->status) {
                        'disetujui' => 'Disetujui',
                        'diajukan' => 'Menunggu Review',
                        'ditolak' => 'Perlu Revisi',
                        default => 'Draft',
                    };
                @endphp
                <article class="report-history-card" data-report-status="{{ $laporan->status }}"
                    data-report-text="{{ strtolower($laporan->catatan ?? '') }}">
                    <div class="report-card-top">
                        <span class="report-date-badge">
                            <strong>{{ $laporan->tanggal->format('d') }}</strong>
                            <small>{{ $laporan->tanggal->translatedFormat('M') }}</small>
                        </span>
                        <div class="report-card-title">
                            <strong>{{ $laporan->tanggal->translatedFormat('l, d M Y') }}</strong>
                            <span><i class="fa-regular fa-clock"></i> Dibuat:
                                {{ optional($laporan->created_at)->format('H:i') ?? '-' }} WIB</span>
                        </div>
                        <span class="report-status report-status-{{ $laporan->status }}">
                            <i
                                class="fa-solid {{ $laporan->status === 'disetujui' ? 'fa-check' : ($laporan->status === 'ditolak' ? 'fa-triangle-exclamation' : 'fa-circle') }}"></i>
                            {{ $statusLabel }}
                        </span>
                    </div>
                    <div class="report-note {{ $laporan->status === 'ditolak' ? 'report-note-review' : '' }}">
                        <strong>{{ $laporan->status === 'ditolak' ? 'Catatan Reviewer:' : 'Catatan Kegiatan:' }}</strong>
                        <p>{{ $laporan->catatan ?: 'Tidak ada catatan kegiatan.' }}</p>
                    </div>
                    <div class="report-card-footer">
                        <span class="report-attachment"><i
                                class="fa-solid {{ $laporan->bukti_gambar ? 'fa-image' : 'fa-paperclip' }}"></i>
                            {{ $laporan->bukti_gambar ? 'Ada bukti gambar' : 'Detail pekerjaan' }}</span>
                        <span class="report-actions">
                            <a href="{{ route($routePrefix . '.laporan-harian.show', $laporan) }}"
                                class="report-detail-link">Detail <i class="fa-solid fa-chevron-right"></i></a>
                            @if (in_array($laporan->status, ['draft', 'ditolak']))
                                <a href="{{ route($routePrefix . '.laporan-harian.edit', $laporan) }}"
                                    class="report-action-edit">Edit</a>
                            @endif
                            @if ($laporan->status === 'draft')
                                <form action="{{ route($routePrefix . '.laporan-harian.ajukan', $laporan) }}" method="POST"
                                    class="d-inline"
                                    onsubmit="return confirm('Apakah Anda yakin ingin mengajukan laporan ini?');">
                                    @csrf
                                    <button type="submit" class="report-action-submit">Ajukan</button>
                                </form>
                                <form action="{{ route($routePrefix . '.laporan-harian.destroy', $laporan) }}" method="POST"
                                    class="d-inline form-delete" data-confirm-message="Yakin ingin menghapus laporan ini?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="report-action-delete" aria-label="Hapus laporan"><i
                                            class="fa-solid fa-trash"></i></button>
                                </form>
                            @endif
                        </span>
                    </div>
                    <a href="{{ route($routePrefix . '.laporan-harian.show', $laporan) }}" class="report-card-link"
                        aria-label="Lihat detail laporan"></a>
                </article>
            @empty
                <div class="attendance-empty">
                    <i class="fa-regular fa-folder-open"></i>
                    <strong>Belum ada laporan harian</strong>
                    <span>Laporan pekerjaan Anda akan tampil di sini.</span>
                </div>
            @endforelse
        </div>

        <div class="attendance-pagination">{{ $laporans->links() }}</div>
    </div>

    @if ($routePrefix === 'atasan')
        @push('styles')
            <link rel="stylesheet" href="{{ asset('css/pegawai/laporan-harian/index.css') }}">
        @endpush
    @endif

    @push('scripts')
        <script>
            (() => {
                const search = document.getElementById('reportSearch');
                const cards = [...document.querySelectorAll('.report-history-card')];
                const tabs = [...document.querySelectorAll('[data-report-filter]')];
                let status = 'all';
                const update = () => {
                    const query = (search.value || '').toLowerCase().trim();
                    cards.forEach(card => {
                        const matchesStatus = status === 'all' || card.dataset.reportStatus === status;
                        const matchesText = !query || card.dataset.reportText.includes(query);
                        card.hidden = !(matchesStatus && matchesText);
                    });
                };
                tabs.forEach(tab => tab.addEventListener('click', () => {
                    status = tab.dataset.reportFilter;
                    tabs.forEach(item => item.classList.toggle('active', item === tab));
                    update();
                }));
                search.addEventListener('input', update);
            })();
        </script>
    @endpush
@endsection
