@extends('layouts.app')
@section('title', 'Penilaian KPI')
@section('content')
    @php
        $assessmentRoutePrefix = $assessmentRoutePrefix ?? 'atasan.penilaian-kpi';
        $targetLabel = $targetLabel ?? 'pegawai';
        $dashboardRoute = $dashboardRoute ?? ($assessmentRoutePrefix === 'atasan.penilaian-kpi' ? 'atasan.dashboard' : 'kepala_bagian.dashboard');
        $isHeadAssessment = $targetLabel === 'atasan';
    @endphp
    @if ($isHeadAssessment)
        @php
            $headCount = $bawahans->count();
            $ratedCount = $sudahDinilaiIds->count();
            $pendingCount = max(0, $headCount - $ratedCount);
            $ratingPercent = $headCount > 0 ? round(($ratedCount / $headCount) * 100) : 0;
            $units = $bawahans->pluck('jabatan.divisi.nama_divisi')->filter()->unique()->sort()->values();
        @endphp
        <main class="kepala-kpi-page">
            <header class="kepala-kpi-header">
                <a class="kepala-kpi-back" href="{{ route('kepala_bagian.dashboard') }}" aria-label="Kembali ke dashboard">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <div class="kepala-kpi-title">
                    <span>KEPALA BAGIAN</span>
                    <h1>Penilaian KPI
                        @if ($periodeAktif)
                            <small>{{ $periodeAktif->nama_periode }}</small>
                        @endif
                    </h1>
                </div>
            </header>

            <section class="kepala-kpi-summary" aria-label="Ringkasan penilaian">
                <article class="is-total">
                    <div><small>TOTAL ATASAN</small><span><i class="fa-solid fa-users"></i></span></div>
                    <strong>{{ $headCount }}</strong>
                    <p>Atasan terdaftar dalam unit kerja</p>
                </article>
                <article class="is-done">
                    <div><small>SUDAH DINILAI</small><span><i class="fa-regular fa-circle-check"></i></span></div>
                    <strong>{{ $ratedCount }}</strong>
                    <p><i class="fa-solid fa-circle"></i> {{ $ratingPercent }}% selesai diverifikasi</p>
                </article>
                <article class="is-pending">
                    <div><small>BELUM DINILAI</small><span><i class="fa-regular fa-clock"></i></span></div>
                    <strong>{{ $pendingCount }}</strong>
                    <p><b>Perlu tindakan</b>
                        @if ($periodeAktif)
                            Batas akhir: {{ $periodeAktif->tanggal_selesai->translatedFormat('d M Y') }}
                        @else
                            Periode belum tersedia
                        @endif
                    </p>
                </article>
            </section>

            <div class="kepala-kpi-search-row">
                <label class="kepala-kpi-search">
                    <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                    <input type="search" id="kepalaKpiSearch" placeholder="Cari nama pegawai, NIP, atau jabatan atasan..."
                        aria-label="Cari atasan">
                    <button type="button" id="kepalaKpiReset">Reset</button>
                </label>
            </div>

            <div class="kepala-kpi-controls">
                <div class="kepala-kpi-tabs" role="tablist" aria-label="Filter status penilaian">
                    <button type="button" class="is-active" data-kpi-filter="all">Semua ({{ $headCount }})</button>
                    <button type="button" data-kpi-filter="rated">Sudah Dinilai ({{ $ratedCount }})</button>
                    <button type="button" data-kpi-filter="pending">Belum Dinilai ({{ $pendingCount }}) <i></i></button>
                </div>
                <div class="kepala-kpi-tools">
                    <label class="kepala-kpi-unit-filter">
                        <i class="fa-solid fa-filter" aria-hidden="true"></i>
                        <select id="kepalaKpiUnit" aria-label="Filter unit">
                            <option value="">Filter Unit</option>
                            @foreach ($units as $unit)
                                <option value="{{ \Illuminate\Support\Str::lower($unit) }}">{{ $unit }}</option>
                            @endforeach
                        </select>
                    </label>
                    <button type="button" class="kepala-kpi-export" id="kepalaKpiExport">
                        <i class="fa-regular fa-file-lines"></i> Ekspor Rekap
                    </button>
                </div>
            </div>

            <section class="kepala-kpi-table-card">
                <div class="kepala-kpi-table-heading">
                    <div>
                        <h2>Daftar Atasan yang Dinilai</h2>
                        <p>Daftar pejabat struktural/atasan langsung yang wajib dievaluasi kinerjanya.</p>
                    </div>
                    <span id="kepalaKpiResultCount">Menampilkan {{ $headCount }} dari {{ $headCount }} pegawai</span>
                </div>
                <div class="kepala-kpi-table">
                    <div class="kepala-kpi-table-head" aria-hidden="true">
                        <span>ATASAN / PEGAWAI</span>
                        <span>JABATAN</span>
                        <span>PERIODE</span>
                        <span>STATUS</span>
                        <span>PROGRES KPI</span>
                        <span>AKSI</span>
                    </div>
                    <div class="kepala-kpi-rows" id="kepalaKpiRows">
                        @forelse ($bawahans as $pegawai)
                            @php
                                $hasRating = $sudahDinilaiIds->contains($pegawai->id);
                                $kpiTotal = (int) ($kpiCountsByJabatan[$pegawai->jabatan_id] ?? 0);
                                $kpiCompleted = (int) ($penilaianCountsByPegawai[$pegawai->id] ?? 0);
                                $kpiPercent =
                                    $kpiTotal > 0 ? min(100, (int) round(($kpiCompleted / $kpiTotal) * 100)) : 0;
                                $unitName = $pegawai->jabatan->divisi->nama_divisi ?? '-';
                            @endphp
                            <article class="kepala-kpi-row" data-kpi-status="{{ $hasRating ? 'rated' : 'pending' }}"
                                data-kpi-search="{{ \Illuminate\Support\Str::lower($pegawai->nama . ' ' . ($pegawai->nip ?? '') . ' ' . ($pegawai->jabatan->nama_jabatan ?? '')) }}"
                                data-kpi-unit="{{ \Illuminate\Support\Str::lower($unitName) }}">
                                <div class="kepala-kpi-person">
                                    <span class="kepala-kpi-avatar">{{ strtoupper(substr($pegawai->nama, 0, 2)) }}</span>
                                    <span><strong>{{ $pegawai->nama }}</strong><small>{{ $pegawai->nip ? 'NIP: ' . $pegawai->nip : 'Atasan langsung' }}</small></span>
                                </div>
                                <div class="kepala-kpi-position">
                                    <strong>{{ $pegawai->jabatan->nama_jabatan ?? '-' }}</strong>
                                    <small>{{ $unitName }}</small>
                                </div>
                                <div class="kepala-kpi-period">{{ $periodeAktif?->nama_periode ?? 'Belum tersedia' }}</div>
                                <div><span class="kepala-kpi-status {{ $hasRating ? 'is-rated' : 'is-pending' }}">
                                        <i
                                            class="fa-solid fa-circle"></i>{{ $hasRating ? 'Sudah Dinilai' : 'Belum Dinilai' }}
                                    </span></div>
                                <div class="kepala-kpi-progress">
                                    <div><span>Capaian</span><strong>{{ $kpiPercent }}%</strong></div>
                                    <i><em style="width: {{ $kpiPercent }}%"></em></i>
                                    <small>{{ $kpiCompleted }} dari {{ $kpiTotal }} butir indikator</small>
                                </div>
                                <div class="kepala-kpi-actions">
                                    <a class="primary"
                                        href="{{ route($assessmentRoutePrefix . ($hasRating ? '.show' : '.create'), $pegawai) }}">
                                        {{ $hasRating ? 'Hasil Penilaian KPI' : 'Nilai KPI' }} <i
                                            class="fa-solid fa-chevron-right"></i>
                                    </a>
                                    <a class="secondary"
                                        href="{{ route($assessmentRoutePrefix . '.kompetensi.edit', $pegawai) }}">
                                        Kompetensi <i class="fa-solid fa-chevron-right"></i>
                                    </a>
                                </div>
                            </article>
                        @empty
                            <div class="kepala-kpi-empty">
                                <i class="fa-solid fa-users"></i>
                                <strong>Belum ada atasan yang ditugaskan</strong>
                                <span>Atasan yang berada di bawah tanggung jawab Anda akan muncul di sini.</span>
                            </div>
                        @endforelse
                        <div class="kepala-kpi-no-results" id="kepalaKpiNoResults" hidden>
                            Tidak ada atasan yang cocok dengan pencarian atau filter ini.
                        </div>
                    </div>
                </div>
                <footer class="kepala-kpi-table-footer">
                    <span>Menampilkan <b id="kepalaKpiVisibleCount">{{ $headCount }}</b> dari total {{ $headCount }}
                        entri</span>
                </footer>
            </section>

            <aside class="kepala-kpi-tip">
                <i class="fa-regular fa-circle-info"></i>
                <div><strong>Petunjuk Pengisian Penilaian KPI Atasan Langsung</strong>
                    <p>Pastikan Anda telah memeriksa laporan realisasi bulanan dan sasaran kerja unit sebelum mengklik
                        tombol <b>Nilai KPI</b>. Nilai yang telah difinalisasi akan diteruskan ke Bagian SDM / Direksi untuk
                        proses validasi insentif kinerja.</p>
                </div>
            </aside>
        </main>
    @else
        <div class="atasan-kpi-page">
            <div class="atasan-mobile-breadcrumb">
            <a href="{{ route($dashboardRoute) }}"
                    aria-label="Kembali ke dashboard"><i class="fa-solid fa-arrow-left"></i></a>
                <div><span>{{ ucfirst($targetLabel) }}</span><strong>Penilaian
                        KPI</strong></div>
            </div>
            @if ($periodeAktif)
                <section class="kpi-hero">
                    <div><span><i class="fa-solid fa-circle"></i> Periode Aktif</span><strong><i
                                class="fa-regular fa-clock"></i>
                            Sisa Waktu</strong></div>
                    <h1>Penilaian KPI</h1>
                    <p>Periode aktif: <b>{{ $periodeAktif->nama_periode }}</b></p>
                    <div class="kpi-progress"><span>Kelengkapan Penilaian
                            Unit</span><b>{{ $sudahDinilaiIds->count() }}/{{ $bawahans->count() }} Selesai</b><i><em
                                style="width:{{ $bawahans->count() ? min(100, ($sudahDinilaiIds->count() / $bawahans->count()) * 100) : 0 }}%"></em></i>
                    </div>
                </section>
            @else
                <div class="alert alert-warning">Belum ada periode penilaian aktif.</div>
            @endif
            <div class="kpi-summary">
                <div><small>Total
                        {{ ucfirst($targetLabel) }}</small><strong>{{ $bawahans->count() }}</strong><span>{{ ucfirst($targetLabel) }}</span>
                </div>
                <div><small>Sudah
                        Dinilai</small><strong>{{ $sudahDinilaiIds->count() }}</strong><span>{{ $bawahans->count() ? round(($sudahDinilaiIds->count() / $bawahans->count()) * 100) : 0 }}%
                        selesai</span></div>
                <div><small>Belum
                        Dinilai</small><strong>{{ max(0, $bawahans->count() - $sudahDinilaiIds->count()) }}</strong><span>Perlu
                        tindakan</span></div>
            </div>
            <div class="kpi-search"><i class="fa-solid fa-magnifying-glass"></i><input type="search"
                    placeholder="Cari pegawai..."></div>
            <div class="kpi-tabs"><span class="active">Semua ({{ $bawahans->count() }})</span><span>Sudah Dinilai
                    ({{ $sudahDinilaiIds->count() }})</span><span>Belum Dinilai
                    ({{ max(0, $bawahans->count() - $sudahDinilaiIds->count()) }})</span></div>
            <div class="kpi-list-heading">
                <h2>Daftar {{ ucfirst($targetLabel) }} yang Dinilai</h2><span>Menampilkan {{ $bawahans->count() }} dari
                    {{ $bawahans->count() }}</span>
            </div>
            <div class="kpi-list">
                @forelse($bawahans as $pegawai)
                    <article class="kpi-employee">
                        <div class="kpi-employee-top"><span
                                class="kpi-avatar">{{ strtoupper(substr($pegawai->nama, 0, 2)) }}</span>
                            <div>
                                <h3>{{ $pegawai->nama }}</h3>
                                <p>NIP: {{ $pegawai->nip ?? '-' }}</p>
                            </div><span
                                class="{{ $sudahDinilaiIds->contains($pegawai->id) ? 'kpi-done' : 'kpi-pending' }}">{{ $sudahDinilaiIds->contains($pegawai->id) ? 'Sudah Dinilai' : 'Belum Dinilai' }}</span>
                        </div>
                        <div class="kpi-employee-meta">
                            <div><small>JABATAN</small><b>{{ $pegawai->jabatan->nama_jabatan ?? '-' }}</b></div>
                            <div><small>UNIT / SEKSI</small><b>{{ $pegawai->jabatan->divisi->nama_divisi ?? '-' }}</b>
                            </div>
                        </div>
                        <div class="kpi-employee-action"><a
                                href="{{ route($assessmentRoutePrefix . '.create', $pegawai) }}">
                                @if ($sudahDinilaiIds->contains($pegawai->id))
                                    Lihat / Edit KPI
                                @else
                                    Nilai KPI
                                @endif
                                <i class="fa-solid fa-chevron-right"></i>
                            </a>
                            @if ($sudahDinilaiIds->contains($pegawai->id))
                                <a class="view" href="{{ route($assessmentRoutePrefix . '.show', $pegawai) }}"><i
                                        class="fa-regular fa-eye"></i></a>
                            @endif
                            @if (in_array($targetLabel, ['atasan', 'Kepala Bagian'], true))
                                <a class="view"
                                    href="{{ route($assessmentRoutePrefix . '.kompetensi.edit', $pegawai) }}">Kompetensi
                                    <i class="fa-solid fa-chevron-right"></i></a>
                            @endif
                        </div>
                </article>@empty<div class="kpi-empty">Belum ada {{ $targetLabel }} yang ditugaskan kepada Anda.
                    </div>
                @endforelse
            </div>
        </div>
    @endif
@endsection
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/atasan/penilaian-kpi/index.css') }}">
@endpush

@if ($isHeadAssessment)
    @push('scripts')
        <script>
            (() => {
                const search = document.getElementById('kepalaKpiSearch');
                const reset = document.getElementById('kepalaKpiReset');
                const unit = document.getElementById('kepalaKpiUnit');
                const exportButton = document.getElementById('kepalaKpiExport');
                const rows = [...document.querySelectorAll('.kepala-kpi-row')];
                const filters = [...document.querySelectorAll('[data-kpi-filter]')];
                const visibleCount = document.getElementById('kepalaKpiVisibleCount');
                const resultCount = document.getElementById('kepalaKpiResultCount');
                const noResults = document.getElementById('kepalaKpiNoResults');
                let activeFilter = 'all';

                function updateRows() {
                    const query = search.value.trim().toLocaleLowerCase('id');
                    const selectedUnit = unit.value;
                    let visible = 0;

                    rows.forEach((row) => {
                        const matches = row.dataset.kpiSearch.includes(query) &&
                            (!selectedUnit || row.dataset.kpiUnit === selectedUnit) &&
                            (activeFilter === 'all' || row.dataset.kpiStatus === activeFilter);
                        row.hidden = !matches;
                        if (matches) visible += 1;
                    });

                    visibleCount.textContent = visible;
                    resultCount.textContent = `Menampilkan ${visible} dari ${rows.length} pegawai`;
                    noResults.hidden = visible > 0;
                }

                search.addEventListener('input', updateRows);
                unit.addEventListener('change', updateRows);
                reset.addEventListener('click', () => {
                    search.value = '';
                    unit.value = '';
                    activeFilter = 'all';
                    filters.forEach((filter) => filter.classList.toggle('is-active', filter.dataset.kpiFilter ===
                        'all'));
                    updateRows();
                });
                filters.forEach((filter) => filter.addEventListener('click', () => {
                    activeFilter = filter.dataset.kpiFilter;
                    filters.forEach((item) => item.classList.toggle('is-active', item === filter));
                    updateRows();
                }));
                exportButton.addEventListener('click', () => window.print());
                updateRows();
            })();
        </script>
    @endpush
@endif
