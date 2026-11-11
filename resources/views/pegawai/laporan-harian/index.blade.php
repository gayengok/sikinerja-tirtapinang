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
            <style>
                body.role-atasan .report-page {
                    display: grid;
                    gap: 18px;
                    width: min(1120px, 100%);
                    margin: 0 auto;
                    padding: 24px;
                    color: #17213b;
                }

                body.role-atasan .report-heading {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    gap: 20px;
                    margin: 0;
                    padding: 22px 24px;
                    background: #fff;
                    border: 1px solid #dfe7f0;
                    border-radius: 16px;
                    box-shadow: 0 5px 16px rgba(35, 52, 85, .045);
                }

                body.role-atasan .report-heading h1 {
                    display: flex;
                    align-items: center;
                    gap: 11px;
                    margin: 0;
                    color: #17213b;
                    font-size: 1.25rem;
                    font-weight: 800;
                }

                body.role-atasan .report-heading h1 i {
                    display: grid;
                    width: 40px;
                    height: 40px;
                    place-items: center;
                    color: #078ac8;
                    background: #e8f6fc;
                    border-radius: 11px;
                    font-size: 1rem;
                }

                body.role-atasan .report-heading p {
                    margin: 8px 0 0 51px;
                    color: #64748b;
                    font-size: .84rem;
                    line-height: 1.5;
                }

                body.role-atasan .report-create-button {
                    display: inline-flex;
                    flex: 0 0 auto;
                    align-items: center;
                    justify-content: center;
                    gap: 8px;
                    min-height: 42px;
                    padding: 10px 15px;
                    color: #fff;
                    background: #078ac8;
                    border-radius: 9px;
                    font-size: .82rem;
                    font-weight: 700;
                    text-decoration: none;
                    transition: background .2s, transform .2s;
                }

                body.role-atasan .report-create-button:hover {
                    color: #fff;
                    background: #0578b0;
                    transform: translateY(-1px);
                }

                body.role-atasan .report-summary-grid {
                    display: grid;
                    grid-template-columns: repeat(4, minmax(0, 1fr));
                    gap: 12px;
                }

                body.role-atasan .report-summary-card {
                    display: grid;
                    grid-template-columns: 42px 1fr;
                    align-items: center;
                    gap: 3px 12px;
                    min-height: 82px;
                    padding: 14px;
                    background: #fff;
                    border: 1px solid #dfe7f0;
                    border-radius: 13px;
                }

                body.role-atasan .report-summary-icon {
                    display: grid;
                    grid-row: span 2;
                    width: 40px;
                    height: 40px;
                    place-items: center;
                    color: #078ac8;
                    background: #e8f6fc;
                    border-radius: 11px;
                }

                body.role-atasan .report-summary-card strong {
                    color: #17213b;
                    font-size: 1.2rem;
                    line-height: 1.1;
                }

                body.role-atasan .report-summary-card > span:last-child {
                    color: #64748b;
                    font-size: .74rem;
                }

                body.role-atasan .report-filter-panel {
                    display: grid;
                    gap: 14px;
                    margin: 0;
                    padding: 16px;
                    background: #fff;
                    border: 1px solid #dfe7f0;
                    border-radius: 14px;
                }

                body.role-atasan .report-search {
                    position: relative;
                }

                body.role-atasan .report-search > i {
                    position: absolute;
                    top: 50%;
                    left: 13px;
                    color: #8292ab;
                    transform: translateY(-50%);
                }

                body.role-atasan .report-search input {
                    box-sizing: border-box;
                    width: 100%;
                    min-height: 42px;
                    padding: 10px 12px 10px 38px;
                    color: #26344d;
                    background: #f8fafc;
                    border: 1px solid #dfe7f0;
                    border-radius: 9px;
                    outline: 0;
                    font: inherit;
                    font-size: .82rem;
                }

                body.role-atasan .report-search input:focus {
                    border-color: #42a8d1;
                    box-shadow: 0 0 0 3px rgba(8, 138, 200, .1);
                }

                body.role-atasan .report-filter-tabs {
                    display: flex;
                    flex-wrap: wrap;
                    gap: 8px;
                }

                body.role-atasan .report-filter-tabs button {
                    padding: 8px 12px;
                    color: #526480;
                    background: #f1f5f9;
                    border: 1px solid transparent;
                    border-radius: 8px;
                    font: inherit;
                    font-size: .75rem;
                    font-weight: 700;
                    cursor: pointer;
                }

                body.role-atasan .report-filter-tabs button.active {
                    color: #fff;
                    background: #078ac8;
                }

                body.role-atasan .report-history-heading {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    gap: 12px;
                    margin: 2px 0 -6px;
                }

                body.role-atasan .report-history-heading h2 {
                    margin: 0;
                    color: #26344d;
                    font-size: 1rem;
                    font-weight: 800;
                }

                body.role-atasan .report-history-heading h2 span {
                    margin-left: 5px;
                    color: #71829c;
                    font-size: .75rem;
                    font-weight: 500;
                }

                body.role-atasan .report-history-heading > small {
                    color: #71829c;
                    font-size: .75rem;
                }

                body.role-atasan .report-history-list {
                    display: grid;
                    gap: 12px;
                }

                body.role-atasan .report-history-card {
                    position: relative;
                    padding: 16px;
                    background: #fff;
                    border: 1px solid #dfe7f0;
                    border-radius: 14px;
                    box-shadow: 0 4px 12px rgba(35, 52, 85, .035);
                    transition: border-color .2s, box-shadow .2s;
                }

                body.role-atasan .report-history-card:hover {
                    border-color: #b7d9e8;
                    box-shadow: 0 7px 18px rgba(35, 52, 85, .075);
                }

                body.role-atasan .report-card-top {
                    display: flex;
                    align-items: center;
                    gap: 12px;
                    min-width: 0;
                    padding-bottom: 13px;
                    border-bottom: 1px solid #edf1f5;
                }

                body.role-atasan .report-date-badge {
                    display: flex;
                    flex: 0 0 48px;
                    width: 48px;
                    height: 48px;
                    align-items: center;
                    justify-content: center;
                    flex-direction: column;
                    color: #087eae;
                    background: #e8f6fc;
                    border: 1px solid #d4edf9;
                    border-radius: 11px;
                    line-height: 1;
                }

                body.role-atasan .report-date-badge strong {
                    font-size: 1rem;
                }

                body.role-atasan .report-date-badge small {
                    margin-top: 4px;
                    font-size: .62rem;
                    text-transform: uppercase;
                }

                body.role-atasan .report-card-title {
                    flex: 1;
                    min-width: 0;
                }

                body.role-atasan .report-card-title strong,
                body.role-atasan .report-card-title span {
                    display: block;
                }

                body.role-atasan .report-card-title strong {
                    color: #26344d;
                    font-size: .84rem;
                }

                body.role-atasan .report-card-title span {
                    margin-top: 5px;
                    color: #71829c;
                    font-size: .72rem;
                }

                body.role-atasan .report-status {
                    flex: 0 0 auto;
                    padding: 6px 9px;
                    border: 1px solid;
                    border-radius: 99px;
                    font-size: .7rem;
                    font-weight: 700;
                    white-space: nowrap;
                }

                body.role-atasan .report-status-disetujui {
                    color: #087653;
                    background: #effcf7;
                    border-color: #b9efd5;
                }

                body.role-atasan .report-status-diajukan {
                    color: #a56800;
                    background: #fffaf0;
                    border-color: #f7df9a;
                }

                body.role-atasan .report-status-ditolak {
                    color: #bb2440;
                    background: #fff5f6;
                    border-color: #ffd3da;
                }

                body.role-atasan .report-status-draft {
                    color: #65748d;
                    background: #f3f5f8;
                    border-color: #dce3ed;
                }

                body.role-atasan .report-note {
                    position: relative;
                    z-index: 1;
                    margin-top: 12px;
                    padding: 12px;
                    background: #f8fafc;
                    border: 1px solid #edf1f6;
                    border-radius: 10px;
                    pointer-events: none;
                }

                body.role-atasan .report-note strong {
                    color: #71829c;
                    font-size: .68rem;
                    text-transform: uppercase;
                }

                body.role-atasan .report-note p {
                    margin: 5px 0 0;
                    color: #3f4f69;
                    font-size: .8rem;
                    line-height: 1.55;
                    overflow-wrap: anywhere;
                }

                body.role-atasan .report-note-review {
                    background: #fff5f6;
                    border-color: #ffd3da;
                }

                body.role-atasan .report-note-review strong,
                body.role-atasan .report-note-review p {
                    color: #a51e39;
                }

                body.role-atasan .report-card-footer {
                    position: relative;
                    z-index: 1;
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    gap: 12px;
                    min-width: 0;
                    margin-top: 12px;
                    pointer-events: none;
                }

                body.role-atasan .report-attachment {
                    min-width: 0;
                    color: #71829c;
                    font-size: .72rem;
                    overflow: hidden;
                    text-overflow: ellipsis;
                    white-space: nowrap;
                }

                body.role-atasan .report-actions {
                    display: inline-flex;
                    flex: 0 0 auto;
                    align-items: center;
                    gap: 12px;
                }

                body.role-atasan .report-detail-link,
                body.role-atasan .report-action-edit,
                body.role-atasan .report-action-submit,
                body.role-atasan .report-action-delete {
                    color: #087eae;
                    font-size: .74rem;
                    font-weight: 700;
                    text-decoration: none;
                    pointer-events: auto;
                }

                body.role-atasan .report-action-submit,
                body.role-atasan .report-action-delete {
                    padding: 0;
                    background: transparent;
                    border: 0;
                    cursor: pointer;
                }

                body.role-atasan .report-action-submit {
                    color: #087653;
                }

                body.role-atasan .report-action-edit {
                    color: #5c6ee6;
                }

                body.role-atasan .report-action-delete {
                    color: #bb2440;
                }

                body.role-atasan .report-card-link {
                    position: absolute;
                    inset: 0;
                    border-radius: 14px;
                }

                body.role-atasan .attendance-empty {
                    display: grid;
                    justify-items: center;
                    gap: 8px;
                    padding: 38px 20px;
                    color: #71829c;
                    background: #fff;
                    border: 1px dashed #cbd5e1;
                    border-radius: 14px;
                    text-align: center;
                }

                body.role-atasan .attendance-empty i {
                    color: #078ac8;
                    font-size: 1.6rem;
                }

                body.role-atasan .attendance-empty strong {
                    color: #26344d;
                }

                body.role-atasan .attendance-pagination {
                    display: flex;
                    justify-content: center;
                }

                body.role-atasan .attendance-pagination svg {
                    width: 16px;
                    height: 16px;
                }

                @media (max-width: 700px) {
                    body.role-atasan .report-page {
                        gap: 13px;
                        padding: 14px;
                    }

                    body.role-atasan .report-heading {
                        align-items: stretch;
                        flex-direction: column;
                        gap: 14px;
                        padding: 17px;
                    }

                    body.role-atasan .report-heading h1 {
                        font-size: 1.08rem;
                    }

                    body.role-atasan .report-heading p {
                        margin-left: 0;
                        font-size: .78rem;
                    }

                    body.role-atasan .report-create-button {
                        width: 100%;
                        box-sizing: border-box;
                    }

                    body.role-atasan .report-summary-grid {
                        grid-template-columns: repeat(2, minmax(0, 1fr));
                        gap: 8px;
                    }

                    body.role-atasan .report-summary-card {
                        grid-template-columns: 34px 1fr;
                        min-height: 68px;
                        gap: 2px 9px;
                        padding: 10px;
                    }

                    body.role-atasan .report-summary-icon {
                        width: 32px;
                        height: 32px;
                    }

                    body.role-atasan .report-summary-card strong {
                        font-size: 1rem;
                    }

                    body.role-atasan .report-summary-card > span:last-child {
                        font-size: .68rem;
                    }

                    body.role-atasan .report-filter-panel {
                        gap: 11px;
                        padding: 12px;
                    }

                    body.role-atasan .report-filter-tabs {
                        flex-wrap: nowrap;
                        overflow-x: auto;
                        padding-bottom: 2px;
                    }

                    body.role-atasan .report-filter-tabs button {
                        flex: 0 0 auto;
                    }

                    body.role-atasan .report-history-card {
                        padding: 12px;
                        border-radius: 12px;
                    }

                    body.role-atasan .report-card-top {
                        flex-wrap: wrap;
                        gap: 9px;
                    }

                    body.role-atasan .report-date-badge {
                        flex-basis: 42px;
                        width: 42px;
                        height: 42px;
                    }

                    body.role-atasan .report-card-title {
                        flex-basis: calc(100% - 60px);
                    }

                    body.role-atasan .report-status {
                        margin-left: 51px;
                    }

                    body.role-atasan .report-card-footer {
                        align-items: flex-start;
                        flex-direction: column;
                    }

                    body.role-atasan .report-actions {
                        width: 100%;
                        justify-content: flex-end;
                    }
                }
            </style>
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
