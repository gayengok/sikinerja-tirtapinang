@extends('layouts.app')
@section('title', 'Penilaian KPI')
@section('content')
    @php
        $assessmentRoutePrefix = $assessmentRoutePrefix ?? 'atasan.penilaian-kpi';
        $targetLabel = $targetLabel ?? 'pegawai';
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
                                $kpiPercent = $kpiTotal > 0 ? min(100, (int) round(($kpiCompleted / $kpiTotal) * 100)) : 0;
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
                                        <i class="fa-solid fa-circle"></i>{{ $hasRating ? 'Sudah Dinilai' : 'Belum Dinilai' }}
                                    </span></div>
                                <div class="kepala-kpi-progress">
                                    <div><span>Capaian</span><strong>{{ $kpiPercent }}%</strong></div>
                                    <i><em style="width: {{ $kpiPercent }}%"></em></i>
                                    <small>{{ $kpiCompleted }} dari {{ $kpiTotal }} butir indikator</small>
                                </div>
                                <div class="kepala-kpi-actions">
                                    <a class="primary"
                                        href="{{ route($assessmentRoutePrefix . ($hasRating ? '.show' : '.create'), $pegawai) }}">
                                        {{ $hasRating ? 'Hasil Penilaian KPI' : 'Nilai KPI' }} <i class="fa-solid fa-chevron-right"></i>
                                    </a>
                                    <a class="secondary" href="{{ route($assessmentRoutePrefix . '.kompetensi.edit', $pegawai) }}">
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
                    <span>Menampilkan <b id="kepalaKpiVisibleCount">{{ $headCount }}</b> dari total {{ $headCount }} entri</span>
                </footer>
            </section>

            <aside class="kepala-kpi-tip">
                <i class="fa-regular fa-circle-info"></i>
                <div><strong>Petunjuk Pengisian Penilaian KPI Atasan Langsung</strong>
                    <p>Pastikan Anda telah memeriksa laporan realisasi bulanan dan sasaran kerja unit sebelum mengklik tombol <b>Nilai KPI</b>. Nilai yang telah difinalisasi akan diteruskan ke Bagian SDM / Direksi untuk proses validasi insentif kinerja.</p>
                </div>
            </aside>
        </main>
    @else
    <div class="atasan-kpi-page">
        <div class="atasan-mobile-breadcrumb">
            <a href="{{ route($assessmentRoutePrefix === 'atasan.penilaian-kpi' ? 'atasan.dashboard' : 'kepala_bagian.dashboard') }}" aria-label="Kembali ke dashboard"><i
                    class="fa-solid fa-arrow-left"></i></a>
            <div><span>{{ $targetLabel === 'atasan' ? 'Kepala Bagian' : 'Atasan' }}</span><strong>Penilaian KPI</strong></div>
        </div>
        @if ($periodeAktif)
            <section class="kpi-hero">
                <div><span><i class="fa-solid fa-circle"></i> Periode Aktif</span><strong><i class="fa-regular fa-clock"></i>
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
            <div><small>Total {{ ucfirst($targetLabel) }}</small><strong>{{ $bawahans->count() }}</strong><span>{{ ucfirst($targetLabel) }}</span></div>
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
            <h2>Daftar {{ ucfirst($targetLabel) }} yang Dinilai</h2><span>Menampilkan {{ $bawahans->count() }} dari {{ $bawahans->count() }}</span>
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
                        <div><small>UNIT / SEKSI</small><b>{{ $pegawai->jabatan->divisi->nama_divisi ?? '-' }}</b></div>
                    </div>
                    <div class="kpi-employee-action"><a href="{{ route($assessmentRoutePrefix . '.create', $pegawai) }}">
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
                        @if ($targetLabel === 'atasan')
                            <a class="view" href="{{ route($assessmentRoutePrefix . '.kompetensi.edit', $pegawai) }}">Kompetensi <i
                                    class="fa-solid fa-chevron-right"></i></a>
                        @endif
                    </div>
            </article>@empty<div class="kpi-empty">Belum ada {{ $targetLabel }} yang ditugaskan kepada Anda.</div>
            @endforelse
        </div>
    </div>
    @endif
@endsection
@push('styles')
    <style>
        body.role-atasan .atasan-kpi-page {
            box-sizing: border-box;
            width: 100%;
            padding: 1rem;
            max-width: 560px;
            margin: auto;
            color: #17213b;
            padding-bottom: calc(var(--bottom-nav-height) + 1.5rem)
        }



        .kpi-hero {
            padding: 1rem;
            color: #fff;
            background: linear-gradient(145deg, #075073, #087fb0);
            border-radius: 20px
        }

        .kpi-hero>div:first-child,
        .kpi-progress {
            display: flex;
            justify-content: space-between;
            gap: .5rem
        }

        .kpi-hero span,
        .kpi-hero strong {
            font-size: .68rem
        }

        .kpi-hero span i {
            color: #34d399;
            font-size: .45rem
        }

        .kpi-hero h1 {
            margin: 1rem 0 .2rem;
            font-size: 1.25rem
        }

        .kpi-hero p {
            margin: 0;
            color: #cde7f0;
            font-size: .75rem;
            overflow-wrap: anywhere
        }

        .kpi-progress {
            flex-wrap: wrap;
            margin-top: 1rem;
            padding-top: .7rem;
            border-top: 1px solid #ffffff33;
            color: #cde7f0
        }

        .kpi-progress b {
            color: #4be0b0
        }

        .kpi-progress i {
            width: 100%;
            height: 5px;
            background: #ffffff33;
            border-radius: 5px
        }

        .kpi-progress em {
            display: block;
            height: 100%;
            background: #4be0b0;
            border-radius: 5px
        }

        .kpi-summary {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: .6rem;
            margin: 1rem 0
        }

        .kpi-summary div {
            min-width: 0;
            padding: .75rem;
            background: #fff;
            border: 1px solid #dfe7f0;
            border-radius: 15px
        }

        .kpi-summary small,
        .kpi-summary strong,
        .kpi-summary span {
            display: block;
            overflow-wrap: anywhere
        }

        .kpi-summary small,
        .kpi-summary span {
            color: #8293aa;
            font-size: .65rem
        }

        .kpi-summary strong {
            margin: .4rem 0;
            font-size: 1.35rem
        }

        .kpi-search {
            display: flex;
            gap: .5rem;
            padding: .75rem;
            background: #fff;
            border: 1px solid #dfe7f0;
            border-radius: 14px
        }

        .kpi-search input {
            box-sizing: border-box;
            width: 100%;
            min-width: 0;
            border: 0;
            outline: 0;
            font-size: .75rem
        }

        .kpi-tabs {
            display: flex;
            gap: .45rem;
            margin: 1rem 0;
            overflow: auto
        }

        .kpi-tabs span {
            flex: 0 0 auto;
            padding: .55rem .7rem;
            border: 1px solid #dfe7f0;
            border-radius: 10px;
            color: #62738d;
            font-size: .68rem;
            white-space: nowrap
        }

        .kpi-tabs .active {
            color: #fff;
            background: #078ac8
        }

        .kpi-list-heading {
            display: flex;
            justify-content: space-between;
            gap: .5rem;
            margin-bottom: .7rem
        }

        .kpi-list-heading h2 {
            margin: 0;
            font-size: .8rem
        }

        .kpi-list-heading span {
            color: #8a9ab4;
            font-size: .65rem;
            text-align: right
        }

        .kpi-list {
            display: grid;
            gap: .7rem
        }

        .kpi-employee {
            overflow: hidden;
            background: #fff;
            border: 1px solid #dfe7f0;
            border-radius: 18px
        }

        .kpi-employee-top {
            display: flex;
            align-items: center;
            gap: .65rem;
            padding: 1rem
        }

        .kpi-avatar {
            display: grid;
            place-items: center;
            width: 48px;
            height: 48px;
            flex: 0 0 48px;
            color: #fff;
            background: linear-gradient(145deg, #1676df, #08a5c9);
            border-radius: 14px;
            font-weight: 800
        }

        .kpi-employee-top div {
            min-width: 0;
            flex: 1
        }

        .kpi-employee-top h3 {
            margin: 0;
            font-size: .85rem;
            overflow-wrap: anywhere
        }

        .kpi-employee-top p {
            margin: .2rem 0 0;
            color: #7f90a8;
            font-size: .68rem
        }

        .kpi-done,
        .kpi-pending {
            padding: .35rem .5rem;
            border-radius: 12px;
            font-size: .62rem;
            font-weight: 700;
            white-space: nowrap
        }

        .kpi-done {
            color: #078653;
            background: #eafbf4
        }

        .kpi-pending {
            color: #a85b00;
            background: #fff4ca
        }

        .kpi-employee-meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .5rem;
            margin: 0 1rem;
            padding: .7rem;
            background: #f8fafc;
            border-radius: 12px
        }

        .kpi-employee-meta small,
        .kpi-employee-meta b {
            display: block
        }

        .kpi-employee-meta small {
            color: #91a1b8;
            font-size: .58rem
        }

        .kpi-employee-meta b {
            margin-top: .25rem;
            font-size: .68rem;
            overflow-wrap: anywhere
        }

        .kpi-employee-action {
            display: flex;
            gap: .5rem;
            padding: .8rem 1rem
        }

        .kpi-employee-action a {
            flex: 1;
            min-width: 0;
            padding: .65rem;
            color: #fff;
            background: #078ac8;
            border-radius: 11px;
            text-align: center;
            text-decoration: none;
            font-size: .7rem;
            font-weight: 700
        }

        .kpi-employee-action a.view {
            flex: 0 0 42px;
            color: #526480;
            background: #f1f5f9
        }

        .kpi-empty {
            text-align: center;
            padding: 2rem;
            color: #8293aa
        }

        @media(max-width:380px) {
            body.role-atasan .atasan-kpi-page {
                padding: .75rem;
                padding-bottom: calc(var(--bottom-nav-height) + 1rem)
            }

            .kpi-summary {
                gap: .35rem
            }

            .kpi-summary div {
                padding: .6rem
            }

            .kpi-summary small,
            .kpi-summary span {
                font-size: .58rem
            }

            .kpi-summary strong {
                font-size: 1.15rem
            }

            .kpi-employee-top {
                align-items: flex-start;
                padding: .8rem
            }

            .kpi-employee-top .kpi-done,
            .kpi-employee-top .kpi-pending {
                font-size: .55rem
            }

            .kpi-employee-meta {
                margin: 0 .8rem
            }

            .kpi-employee-action {
                padding: .7rem .8rem
            }
        }

        body.role-kepala_bagian .kepala-kpi-page {
            display: grid;
            gap: 12px;
            box-sizing: border-box;
            width: min(1420px, 100%);
            margin: 0 auto;
            padding: 16px 20px 24px;
            color: #17213b;
        }

        body.role-kepala_bagian .kepala-kpi-header,
        body.role-kepala_bagian .kepala-kpi-table-card,
        body.role-kepala_bagian .kepala-kpi-search,
        body.role-kepala_bagian .kepala-kpi-tip {
            background: #fff;
            border: 1px solid #e0e7f0;
            border-radius: 12px;
        }

        body.role-kepala_bagian .kepala-kpi-header {
            display: flex;
            align-items: center;
            gap: 12px;
            min-height: 68px;
            padding: 12px 16px;
            box-shadow: 0 2px 6px rgba(35, 52, 85, .025);
        }

        body.role-kepala_bagian .kepala-kpi-back {
            display: grid;
            place-items: center;
            width: 34px;
            height: 34px;
            flex: 0 0 34px;
            color: #078ac8;
            background: #edf7fc;
            border-radius: 9px;
            text-decoration: none;
        }

        body.role-kepala_bagian .kepala-kpi-title > span {
            color: #8a9ab4;
            font-size: .58rem;
            font-weight: 800;
            letter-spacing: .1em;
        }

        body.role-kepala_bagian .kepala-kpi-title h1 {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
            margin: 2px 0 0;
            color: #17213b;
            font-size: 1.08rem;
            font-weight: 800;
        }

        body.role-kepala_bagian .kepala-kpi-title h1 small {
            padding: 4px 8px;
            color: #087653;
            background: #effcf7;
            border: 1px solid #b9efd5;
            border-radius: 999px;
            font-size: .58rem;
            font-weight: 700;
        }

        body.role-kepala_bagian .kepala-kpi-summary {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }

        body.role-kepala_bagian .kepala-kpi-summary article {
            min-width: 0;
            padding: 12px 14px;
            background: #fff;
            border: 1px solid #e0e7f0;
            border-bottom: 3px solid #dfe7f0;
            border-radius: 11px;
            box-shadow: 0 2px 6px rgba(35, 52, 85, .025);
        }

        body.role-kepala_bagian .kepala-kpi-summary article.is-done {
            border-bottom-color: #9de7c4;
        }

        body.role-kepala_bagian .kepala-kpi-summary article.is-pending {
            border-bottom-color: #f5b52e;
        }

        body.role-kepala_bagian .kepala-kpi-summary article > div {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }

        body.role-kepala_bagian .kepala-kpi-summary article > div > small {
            color: #71829c;
            font-size: .58rem;
            font-weight: 700;
            letter-spacing: .045em;
        }

        body.role-kepala_bagian .kepala-kpi-summary article > div > span {
            display: grid;
            place-items: center;
            width: 26px;
            height: 26px;
            color: #64748b;
            background: #f1f5f9;
            border-radius: 7px;
            font-size: .67rem;
        }

        body.role-kepala_bagian .kepala-kpi-summary .is-done > div > span {
            color: #059669;
            background: #e9fbf3;
        }

        body.role-kepala_bagian .kepala-kpi-summary .is-pending > div > span {
            color: #d97706;
            background: #fff4ca;
        }

        body.role-kepala_bagian .kepala-kpi-summary article > strong {
            display: block;
            margin-top: 6px;
            color: #111827;
            font-size: 1.35rem;
            line-height: 1.1;
        }

        body.role-kepala_bagian .kepala-kpi-summary .is-pending > strong {
            color: #d97706;
        }

        body.role-kepala_bagian .kepala-kpi-summary article > p {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 5px;
            margin: 4px 0 0;
            color: #71829c;
            font-size: .59rem;
        }

        body.role-kepala_bagian .kepala-kpi-summary .is-done p i {
            color: #08a77f;
            font-size: .4rem;
        }

        body.role-kepala_bagian .kepala-kpi-summary .is-pending p b {
            padding: 3px 5px;
            color: #9a6200;
            background: #fff4ca;
            border-radius: 4px;
            font-size: .52rem;
        }

        body.role-kepala_bagian .kepala-kpi-search-row {
            display: flex;
            min-width: 0;
        }

        body.role-kepala_bagian .kepala-kpi-search {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            min-width: 0;
            padding: 9px 11px;
        }

        body.role-kepala_bagian .kepala-kpi-search > i {
            color: #91a1b8;
            font-size: .78rem;
        }

        body.role-kepala_bagian .kepala-kpi-search input {
            flex: 1;
            min-width: 0;
            padding: 3px 0;
            color: #26344d;
            background: transparent;
            border: 0;
            outline: 0;
            font: inherit;
            font-size: .72rem;
        }

        body.role-kepala_bagian .kepala-kpi-search input::placeholder {
            color: #91a1b8;
        }

        body.role-kepala_bagian .kepala-kpi-search button,
        body.role-kepala_bagian .kepala-kpi-unit-filter,
        body.role-kepala_bagian .kepala-kpi-export {
            padding: 6px 9px;
            color: #526480;
            background: #f4f7fa;
            border: 1px solid #e6ecf2;
            border-radius: 7px;
            font: inherit;
            font-size: .61rem;
            font-weight: 600;
            cursor: pointer;
        }

        body.role-kepala_bagian .kepala-kpi-controls {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        body.role-kepala_bagian .kepala-kpi-tabs,
        body.role-kepala_bagian .kepala-kpi-tools {
            display: flex;
            align-items: center;
            gap: 6px;
            min-width: 0;
        }

        body.role-kepala_bagian .kepala-kpi-tabs {
            padding: 4px;
            background: #eaf0f6;
            border-radius: 9px;
        }

        body.role-kepala_bagian .kepala-kpi-tabs button {
            padding: 7px 10px;
            color: #526480;
            background: transparent;
            border: 0;
            border-radius: 7px;
            font: inherit;
            font-size: .62rem;
            white-space: nowrap;
            cursor: pointer;
        }

        body.role-kepala_bagian .kepala-kpi-tabs button.is-active {
            color: #fff;
            background: #078ac8;
            box-shadow: 0 2px 4px rgba(7, 138, 200, .15);
        }

        body.role-kepala_bagian .kepala-kpi-tabs button > i {
            display: inline-block;
            width: 5px;
            height: 5px;
            margin-left: 3px;
            background: #f5a623;
            border-radius: 50%;
            vertical-align: middle;
        }

        body.role-kepala_bagian .kepala-kpi-unit-filter {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 9px;
            background: #fff;
            cursor: default;
        }

        body.role-kepala_bagian .kepala-kpi-unit-filter > i {
            color: #71829c;
        }

        body.role-kepala_bagian .kepala-kpi-unit-filter select {
            max-width: 120px;
            color: #526480;
            background: transparent;
            border: 0;
            outline: 0;
            font: inherit;
            font-size: .61rem;
            cursor: pointer;
        }

        body.role-kepala_bagian .kepala-kpi-export {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 9px;
            background: #fff;
        }

        body.role-kepala_bagian .kepala-kpi-table-card {
            min-width: 0;
            overflow: hidden;
            box-shadow: 0 2px 6px rgba(35, 52, 85, .025);
        }

        body.role-kepala_bagian .kepala-kpi-table-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 13px 16px 11px;
        }

        body.role-kepala_bagian .kepala-kpi-table-heading h2 {
            margin: 0;
            color: #26344d;
            font-size: .78rem;
            font-weight: 800;
        }

        body.role-kepala_bagian .kepala-kpi-table-heading p {
            margin: 3px 0 0;
            color: #71829c;
            font-size: .61rem;
        }

        body.role-kepala_bagian .kepala-kpi-table-heading > span {
            flex: 0 0 auto;
            color: #71829c;
            font-size: .59rem;
        }

        body.role-kepala_bagian .kepala-kpi-table-head,
        body.role-kepala_bagian .kepala-kpi-row {
            display: grid;
            grid-template-columns: minmax(150px, 1.45fr) minmax(100px, 1.05fr) minmax(100px, .9fr) minmax(95px, .8fr) minmax(112px, 1.05fr) minmax(158px, 1.45fr);
            align-items: center;
            gap: 10px;
        }

        body.role-kepala_bagian .kepala-kpi-table-head {
            padding: 9px 16px;
            color: #64748b;
            background: #f8fafc;
            border-top: 1px solid #edf1f5;
            border-bottom: 1px solid #edf1f5;
            font-size: .53rem;
            font-weight: 800;
            letter-spacing: .04em;
        }

        body.role-kepala_bagian .kepala-kpi-row {
            padding: 12px 16px;
            border-bottom: 1px solid #edf1f5;
        }

        body.role-kepala_bagian .kepala-kpi-person {
            display: flex;
            align-items: center;
            gap: 9px;
            min-width: 0;
        }

        body.role-kepala_bagian .kepala-kpi-avatar {
            display: grid;
            place-items: center;
            width: 34px;
            height: 34px;
            flex: 0 0 34px;
            color: #fff;
            background: #078ac8;
            border-radius: 9px;
            font-size: .68rem;
            font-weight: 800;
        }

        body.role-kepala_bagian .kepala-kpi-person > span:last-child,
        body.role-kepala_bagian .kepala-kpi-person strong,
        body.role-kepala_bagian .kepala-kpi-person small,
        body.role-kepala_bagian .kepala-kpi-position strong,
        body.role-kepala_bagian .kepala-kpi-position small {
            display: block;
            min-width: 0;
            overflow-wrap: anywhere;
        }

        body.role-kepala_bagian .kepala-kpi-person strong,
        body.role-kepala_bagian .kepala-kpi-position strong {
            color: #26344d;
            font-size: .62rem;
            line-height: 1.4;
        }

        body.role-kepala_bagian .kepala-kpi-person small,
        body.role-kepala_bagian .kepala-kpi-position small {
            margin-top: 2px;
            color: #71829c;
            font-size: .54rem;
        }

        body.role-kepala_bagian .kepala-kpi-period {
            color: #526480;
            font-size: .59rem;
        }

        body.role-kepala_bagian .kepala-kpi-status {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 7px;
            border: 1px solid;
            border-radius: 999px;
            font-size: .53rem;
            font-weight: 700;
            white-space: nowrap;
        }

        body.role-kepala_bagian .kepala-kpi-status i {
            font-size: .38rem;
        }

        body.role-kepala_bagian .kepala-kpi-status.is-rated {
            color: #087653;
            background: #effcf7;
            border-color: #b9efd5;
        }

        body.role-kepala_bagian .kepala-kpi-status.is-pending {
            color: #a56800;
            background: #fffaf0;
            border-color: #f7df9a;
        }

        body.role-kepala_bagian .kepala-kpi-progress {
            min-width: 0;
        }

        body.role-kepala_bagian .kepala-kpi-progress > div {
            display: flex;
            justify-content: space-between;
            gap: 6px;
            color: #71829c;
            font-size: .54rem;
        }

        body.role-kepala_bagian .kepala-kpi-progress > div strong {
            color: #26344d;
        }

        body.role-kepala_bagian .kepala-kpi-progress > i {
            display: block;
            height: 4px;
            margin: 4px 0;
            overflow: hidden;
            background: #e9eef4;
            border-radius: 999px;
        }

        body.role-kepala_bagian .kepala-kpi-progress > i em {
            display: block;
            height: 100%;
            background: #078ac8;
            border-radius: inherit;
        }

        body.role-kepala_bagian .kepala-kpi-progress > small {
            color: #8a9ab4;
            font-size: .51rem;
        }

        body.role-kepala_bagian .kepala-kpi-actions {
            display: flex;
            align-items: center;
            gap: 5px;
            min-width: 0;
        }

        body.role-kepala_bagian .kepala-kpi-actions a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            min-width: 0;
            padding: 7px 8px;
            border: 1px solid #e0e7f0;
            border-radius: 7px;
            color: #526480;
            background: #fff;
            font-size: .53rem;
            font-weight: 700;
            text-decoration: none;
            white-space: nowrap;
        }

        body.role-kepala_bagian .kepala-kpi-actions a.primary {
            flex: 1;
            color: #fff;
            background: #078ac8;
            border-color: #078ac8;
        }

        body.role-kepala_bagian .kepala-kpi-actions a.secondary {
            flex: 0 0 auto;
        }

        body.role-kepala_bagian .kepala-kpi-empty,
        body.role-kepala_bagian .kepala-kpi-no-results {
            padding: 28px 16px;
            color: #71829c;
            text-align: center;
            font-size: .68rem;
        }

        body.role-kepala_bagian .kepala-kpi-empty > i {
            display: block;
            margin-bottom: 8px;
            color: #078ac8;
            font-size: 1.35rem;
        }

        body.role-kepala_bagian .kepala-kpi-empty > strong,
        body.role-kepala_bagian .kepala-kpi-empty > span {
            display: block;
        }

        body.role-kepala_bagian .kepala-kpi-empty > strong {
            color: #34445e;
        }

        body.role-kepala_bagian .kepala-kpi-empty > span {
            margin-top: 4px;
        }

        body.role-kepala_bagian .kepala-kpi-table-footer {
            padding: 9px 16px;
            color: #71829c;
            font-size: .57rem;
        }

        body.role-kepala_bagian .kepala-kpi-table-footer b {
            color: #34445e;
        }

        body.role-kepala_bagian .kepala-kpi-tip {
            display: flex;
            gap: 9px;
            padding: 12px 14px;
            color: #285d83;
            background: #f2f8fe;
            border-color: #dcecfb;
        }

        body.role-kepala_bagian .kepala-kpi-tip > i {
            margin-top: 2px;
            color: #078ac8;
            font-size: .75rem;
        }

        body.role-kepala_bagian .kepala-kpi-tip strong {
            display: block;
            font-size: .62rem;
        }

        body.role-kepala_bagian .kepala-kpi-tip p {
            margin: 3px 0 0;
            color: #64819b;
            font-size: .57rem;
            line-height: 1.5;
        }

        @media (max-width: 1100px) {
            body.role-kepala_bagian .kepala-kpi-table-head {
                display: none;
            }

            body.role-kepala_bagian .kepala-kpi-row {
                grid-template-columns: minmax(0, 1fr) auto;
                gap: 10px 12px;
                padding: 13px;
            }

            body.role-kepala_bagian .kepala-kpi-row > .kepala-kpi-person {
                grid-column: 1;
            }

            body.role-kepala_bagian .kepala-kpi-row > .kepala-kpi-position {
                grid-column: 1;
                padding-left: 43px;
            }

            body.role-kepala_bagian .kepala-kpi-row > .kepala-kpi-period {
                display: none;
            }

            body.role-kepala_bagian .kepala-kpi-row > div:nth-child(4) {
                grid-column: 2;
                grid-row: 1;
                justify-self: end;
            }

            body.role-kepala_bagian .kepala-kpi-row > .kepala-kpi-progress {
                grid-column: 1;
                padding-left: 43px;
            }

            body.role-kepala_bagian .kepala-kpi-row > .kepala-kpi-actions {
                grid-column: 1 / -1;
            }
        }

        @media (max-width: 767.98px) {
            body.role-kepala_bagian .kepala-kpi-page {
                gap: 10px;
                padding: 12px;
                padding-bottom: calc(var(--bottom-nav-height) + 1rem);
            }

            body.role-kepala_bagian .kepala-kpi-header {
                min-height: 60px;
                padding: 10px 12px;
            }

            body.role-kepala_bagian .kepala-kpi-summary {
                gap: 7px;
            }

            body.role-kepala_bagian .kepala-kpi-summary article {
                padding: 9px;
            }

            body.role-kepala_bagian .kepala-kpi-summary article > div > small {
                font-size: .5rem;
            }

            body.role-kepala_bagian .kepala-kpi-summary article > div > span {
                width: 22px;
                height: 22px;
                font-size: .58rem;
            }

            body.role-kepala_bagian .kepala-kpi-summary article > strong {
                font-size: 1.15rem;
            }

            body.role-kepala_bagian .kepala-kpi-summary article > p {
                font-size: .52rem;
            }

            body.role-kepala_bagian .kepala-kpi-controls {
                align-items: stretch;
                flex-direction: column;
            }

            body.role-kepala_bagian .kepala-kpi-tabs {
                overflow-x: auto;
            }

            body.role-kepala_bagian .kepala-kpi-tabs button {
                flex: 0 0 auto;
            }

            body.role-kepala_bagian .kepala-kpi-tools {
                justify-content: flex-end;
            }

            body.role-kepala_bagian .kepala-kpi-table-heading {
                align-items: flex-start;
                flex-direction: column;
                gap: 5px;
                padding: 12px;
            }
        }

        @media print {
            body.role-kepala_bagian .kepala-kpi-page {
                width: 100%;
                padding: 0;
            }

            body.role-kepala_bagian .kepala-kpi-back,
            body.role-kepala_bagian .kepala-kpi-search-row,
            body.role-kepala_bagian .kepala-kpi-controls,
            body.role-kepala_bagian .kepala-kpi-actions,
            body.role-kepala_bagian .kepala-kpi-tip {
                display: none !important;
            }
        }
    </style>
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
                        const matches = row.dataset.kpiSearch.includes(query)
                            && (!selectedUnit || row.dataset.kpiUnit === selectedUnit)
                            && (activeFilter === 'all' || row.dataset.kpiStatus === activeFilter);
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
                    filters.forEach((filter) => filter.classList.toggle('is-active', filter.dataset.kpiFilter === 'all'));
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
