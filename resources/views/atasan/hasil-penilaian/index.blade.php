
@extends('layouts.app')
@section('title', 'Hasil Penilaian')

@section('content')
@php
    $predikatRata = $avg === null
        ? 'Belum tersedia'
        : ($avg >= 90 ? 'Sangat Baik'
            : ($avg >= 75 ? 'Baik' : 'Perlu Perbaikan'));

    $ringProgress = min(100, max(0, (float) ($avg ?? 0)));

    $tabOptions = [
        'semua' => 'Semua',
        'sangat_baik' => 'Sangat Baik',
        'baik' => 'Baik',
        'perbaikan' => 'Butuh Perbaikan',
    ];
@endphp

<div class="hasil-page" id="hasilPage">
    <section class="hasil-performance-card">
        <div class="hasil-performance-heading">
            <div>
                <h1>Capaian Kinerja Eksekutif</h1>
                <p>
                    {{ $periode->nama_periode ?? 'Belum ada periode penilaian' }}
                </p>
            </div>

            <span>
                <i class="fa-solid fa-circle"></i>
                {{ $predikatRata }}
            </span>
        </div>

        <div class="hasil-performance-main">
            <div>
                <small>RATA-RATA NILAI AKHIR TIM</small>

                <strong>
                    {{ $avg !== null ? number_format($avg, 2, ',', '.') : '-' }}
                    <em>/ 100 pt</em>
                </strong>

                <b>
                    <i class="fa-solid fa-chart-line"></i>
                    {{ $predikatRata }}
                </b>

                <p class="hasil-performance-description">
                    Berdasarkan {{ $selesai }} dari
                    {{ $totalPegawai }} pegawai yang memiliki nilai akhir.
                </p>
            </div>

            <div class="hasil-performance-ring"
                 style="--progress: {{ $ringProgress * 3.6 }}deg">
                <strong>
                    {{ $avg !== null ? number_format($avg, 1, ',', '.') : '-' }}%
                </strong>
                <small>Indeks</small>
            </div>
        </div>

        <div class="hasil-performance-trend">
            <span>
                <i class="fa-solid fa-circle"></i>
                Periode Penilaian
            </span>

            <strong>
                {{ $periode->nama_periode ?? 'Belum tersedia' }}
            </strong>
        </div>
    </section>

    <div class="hasil-summary-cards">
        <article>
            <div>
                <small>TUNTAS DINILAI</small>
                <i class="fa-solid fa-check"></i>
            </div>

            <strong>
                {{ $selesai }}
                <em>/ {{ $totalPegawai }} Pegawai</em>
            </strong>

            <div class="hasil-summary-progress">
                <b style="width: {{ $tuntasPercent }}%"></b>
            </div>

            <small class="summary-status">
                {{ $tuntasPercent }}% Selesai
            </small>
        </article>

        <article>
            <div>
                <small>RERATA TUKIN</small>
                <i class="fa-solid fa-sack-dollar"></i>
            </div>

            <strong>
                {{ $avgTukin !== null
                    ? 'Rp '.number_format($avgTukin, 0, ',', '.')
                    : '-' }}
            </strong>

            <b>Bruto Resmi</b>
            <small class="summary-status">
                Rata-rata pegawai yang telah dinilai
            </small>
        </article>
    </div>

    <section class="hasil-trend-panel">
        <div class="hasil-trend-heading">
            <div>
                <span class="hasil-section-kicker">
                    <i class="fa-solid fa-chart-column"></i>
                    ANALISIS KINERJA
                </span>

                <h2>Tren Penilaian Tim</h2>
                <p>
                    Rata-rata nilai aktual berdasarkan periode penilaian.
                </p>
            </div>

            <span class="hasil-trend-period">
                {{ $rentangTren }}
            </span>
        </div>

        <div class="hasil-trend-legend">
            <span>
                <i class="hasil-legend-dot is-kpi"></i>
                KPI Otomatis
            </span>
            <span>
                <i class="hasil-legend-dot is-final"></i>
                Nilai Akhir
            </span>
        </div>

        <div class="hasil-trend-scroll">
            <div class="hasil-trend-chart" id="hasilTrendChart">
                @foreach ($trenPenilaian as $tren)
                    @php
                        $nilaiKpi = $tren['avg_kpi'];
                        $nilaiAkhir = $tren['avg_nilai'];

                        $tinggiKpi = min(
                            100,
                            max(0, (float) ($nilaiKpi ?? 0))
                        );

                        $tinggiAkhir = min(
                            100,
                            max(0, (float) ($nilaiAkhir ?? 0))
                        );
                    @endphp

                    <button type="button"
                            class="hasil-trend-item"
                            data-period="{{ $tren['periode'] }}"
                            data-kpi="{{ $nilaiKpi !== null ? number_format($nilaiKpi, 2, ',', '.') : '-' }}"
                            data-final="{{ $nilaiAkhir !== null ? number_format($nilaiAkhir, 2, ',', '.') : '-' }}"
                            aria-label="Lihat nilai {{ $tren['periode'] }}">

                        <span class="hasil-trend-bars">
                            <span class="hasil-trend-bar is-kpi"
                                  style="height: {{ $tinggiKpi }}%">
                            </span>

                            <span class="hasil-trend-bar is-final"
                                  style="height: {{ $tinggiAkhir }}%">
                            </span>
                        </span>

                        <span class="hasil-trend-month">
                            {{ $tren['label'] }}
                        </span>
                    </button>
                @endforeach
            </div>
        </div>

        <div class="hasil-trend-detail" id="hasilTrendDetail">
            <span>
                <i class="fa-solid fa-hand-pointer"></i>
                Klik batang grafik untuk melihat nilainya.
            </span>
        </div>
    </section>

    <section class="hasil-directory">
        <div class="hasil-directory-heading">
            <div>
                <span class="hasil-section-kicker">
                    <i class="fa-solid fa-users"></i>
                    DATA PENILAIAN
                </span>
                <h2>Daftar Penilaian Pegawai</h2>
                <p>Cari dan filter hasil penilaian seluruh bawahan.</p>
            </div>

            <form method="GET"
                  action="{{ route('atasan.hasil-penilaian.index') }}"
                  class="hasil-period-form">

                <label for="hasilPeriode">Periode</label>

                <select id="hasilPeriode" name="periode_id"
                        onchange="this.form.submit()">
                    @forelse ($periodeOptions as $option)
                        <option value="{{ $option->id }}"
                            @selected($periode?->id === $option->id)>
                            {{ $option->nama_periode }}
                        </option>
                    @empty
                        <option value="">Belum tersedia</option>
                    @endforelse
                </select>
            </form>
        </div>

        <form id="hasilFilterForm"
              method="GET"
              action="{{ route('atasan.hasil-penilaian.index') }}">

            @if ($periode)
                <input type="hidden"
                       name="periode_id"
                       value="{{ $periode->id }}">
            @endif

            <input type="hidden"
                   name="predikat"
                   id="hasilPredikatInput"
                   value="{{ $filterPredikat }}">

            <div class="hasil-search">
                <i class="fa-solid fa-magnifying-glass"></i>

                <input type="search"
                       id="hasilSearchInput"
                       name="search"
                       value="{{ $search }}"
                       placeholder="Cari nama, jabatan, atau predikat..."
                       autocomplete="off"
                       aria-label="Cari pegawai">

                <i class="fa-solid fa-filter"></i>
            </div>
        </form>

        <div class="hasil-tabs" id="hasilTabs">
            @foreach ($tabOptions as $key => $label)
                <button type="button"
                        class="{{ $filterPredikat === $key ? 'active' : '' }}"
                        data-predikat="{{ $key }}">
                    {{ $label }}
                    ({{ $jumlahPredikat[$key] }})
                </button>
            @endforeach
        </div>

        <div id="hasilResults" aria-live="polite">
            <div class="hasil-list-title">
                <h2>
                    Daftar Penilaian Pegawai
                    ({{ $hasilPenilaians->total() }})
                </h2>
            </div>

            <div class="hasil-table-wrap">
                <table class="hasil-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Pegawai</th>
                            <th>Predikat</th>
                            <th>KPI Otomatis</th>
                            <th>Realisasi Laporan</th>
                            <th>Nilai Akhir</th>
                            <th>Tukin Bruto</th>
                            <th class="hasil-table-action-heading">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($hasilPenilaians as $hasil)
                            <tr>
                                <td>
                                    <span class="hasil-table-number">
                                        {{ $loop->iteration + $hasilPenilaians->firstItem() - 1 }}
                                    </span>
                                </td>

                                <td>
                                    <div class="hasil-table-person">
                                        <span class="hasil-table-avatar">
                                            {{ mb_strtoupper(mb_substr($hasil->pegawai->nama, 0, 2)) }}
                                        </span>

                                        <span>
                                            <strong>
                                                {{ $hasil->pegawai->nama }}
                                            </strong>

                                            <small>
                                                {{ $hasil->pegawai->jabatan->nama_jabatan ?? '-' }}
                                            </small>
                                        </span>
                                    </div>
                                </td>

                                <td>
                                    <span class="hasil-table-predicate">
                                        {{ $hasil->predikat ?? 'Belum dinilai' }}
                                    </span>
                                </td>

                                <td>
                                    <strong class="hasil-table-score">
                                        {{ $hasil->rata_skor_kpi !== null
                                            ? number_format((float) $hasil->rata_skor_kpi, 2, ',', '.')
                                            : '-' }}
                                    </strong>
                                    <small class="hasil-table-hint">
                                        Dari laporan disetujui
                                    </small>
                                </td>

                                <td>
                                    <strong class="hasil-table-auto">
                                        Otomatis
                                    </strong>
                                    <small class="hasil-table-hint">
                                        Tanpa input atasan
                                    </small>
                                </td>

                                <td>
                                    <strong class="hasil-table-score">
                                        {{ $hasil->nilai_akhir !== null
                                            ? number_format((float) $hasil->nilai_akhir, 2, ',', '.')
                                            : '-' }}
                                    </strong>
                                    <small class="hasil-table-hint">
                                        Skor akhir
                                    </small>
                                </td>

                                <td>
                                    <strong class="hasil-table-money">
                                        {{ $hasil->exists
                                            ? 'Rp '.number_format((float) $hasil->tukin_bruto, 0, ',', '.')
                                            : '-' }}
                                    </strong>

                                    <small class="hasil-table-draft">
                                        {{ !$hasil->exists
                                            ? 'Belum dinilai'
                                            : ($hasil->status === 'dikirim'
                                                ? 'Dikirim'
                                                : 'Draft') }}
                                    </small>
                                </td>

                                <td class="hasil-table-action-cell">
                                    <a class="hasil-table-detail"
                                       href="{{ route('atasan.hasil-penilaian.show', [
                                           'pegawai' => $hasil->pegawai,
                                           'periode_id' => $periode?->id,
                                       ]) }}">
                                        Detail
                                        <i class="fa-solid fa-chevron-right"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="hasil-table-empty" colspan="8">
                                    Tidak ada pegawai yang sesuai dengan pencarian.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="hasil-list">
                @forelse ($hasilPenilaians as $hasil)
                    <article class="hasil-card">
                        <div class="hasil-person">
                            <span class="hasil-avatar">
                                {{ mb_strtoupper(mb_substr($hasil->pegawai->nama, 0, 2)) }}
                            </span>

                            <div>
                                <h3>{{ $hasil->pegawai->nama }}</h3>
                                <p>
                                    {{ $hasil->pegawai->jabatan->nama_jabatan ?? '-' }}
                                </p>
                            </div>

                            <b class="hasil-predikat">
                                {{ $hasil->predikat ?? 'Belum dinilai' }}
                            </b>
                        </div>

                        <div class="hasil-values">
                            <div>
                                <small>KPI OTOMATIS</small>
                                <strong>
                                    {{ $hasil->rata_skor_kpi !== null
                                        ? number_format((float) $hasil->rata_skor_kpi, 2, ',', '.')
                                        : '-' }}
                                </strong>
                                <span>Dari laporan disetujui</span>
                            </div>

                            <div>
                                <small>REALISASI LAPORAN</small>
                                <strong>Otomatis</strong>
                                <span>Tanpa input atasan</span>
                            </div>

                            <div>
                                <small>NILAI AKHIR</small>
                                <strong>
                                    {{ $hasil->nilai_akhir !== null
                                        ? number_format((float) $hasil->nilai_akhir, 2, ',', '.')
                                        : '-' }}
                                </strong>
                                <span>Skor akhir</span>
                            </div>
                        </div>

                        <div class="hasil-card-footer">
                            <div>
                                <small>TUKIN BRUTO</small>

                                <strong>
                                    {{ $hasil->exists
                                        ? 'Rp '.number_format((float) $hasil->tukin_bruto, 0, ',', '.')
                                        : '-' }}
                                </strong>

                                <span>
                                    {{ !$hasil->exists
                                        ? 'Belum dinilai'
                                        : ($hasil->status === 'dikirim'
                                            ? 'Dikirim'
                                            : 'Draft') }}
                                </span>
                            </div>

                            <a href="{{ route('atasan.hasil-penilaian.show', [
                                'pegawai' => $hasil->pegawai,
                                'periode_id' => $periode?->id,
                            ]) }}">
                                Detail
                                <i class="fa-solid fa-chevron-right"></i>
                            </a>
                        </div>
                    </article>
                @empty
                    <div class="hasil-empty">
                        <i class="fa-regular fa-folder-open"></i>
                        <strong>Tidak ada hasil pencarian</strong>
                        <span>Coba kata kunci atau filter lainnya.</span>
                    </div>
                @endforelse
            </div>

            <div class="hasil-pagination">
                {{ $hasilPenilaians->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </section>

    <aside class="hasil-note">
        <i class="fa-solid fa-circle-info"></i>

        <div>
            <strong>Catatan Tukin & Evaluasi</strong>
            <p>
                Perhitungan otomatis berdasarkan capaian KPI dari laporan
                pegawai yang telah disetujui. Atasan tidak mengisi nilai
                capaian. KPI atau kompetensi yang belum memiliki nilai
                dihitung 0 pada hasil sementara.
            </p>
        </div>
    </aside>
</div>
@endsection

@push('styles')
<link rel="stylesheet"
      href="{{ asset('css/atasan/hasil-penilaian/index.css') }}">
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const page = document.getElementById('hasilPage');
    const form = document.getElementById('hasilFilterForm');
    const search = document.getElementById('hasilSearchInput');
    const predikatInput = document.getElementById('hasilPredikatInput');
    const tabs = document.getElementById('hasilTabs');
    const results = document.getElementById('hasilResults');

    if (!page || !form || !search || !results) return;

    let debounceTimer;
    let controller;
    let requestNumber = 0;

    async function loadResults(url, updateHistory = true) {
        if (controller) controller.abort();

        controller = new AbortController();
        const currentRequest = ++requestNumber;

        results.classList.add('is-loading');
        results.setAttribute('aria-busy', 'true');

        try {
            const response = await fetch(url, {
                signal: controller.signal,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                }
            });

            if (!response.ok) {
                throw new Error('Gagal mengambil data penilaian.');
            }

            const html = await response.text();
            if (currentRequest !== requestNumber) return;

            const doc = new DOMParser().parseFromString(
                html,
                'text/html'
            );

            const newResults = doc.getElementById('hasilResults');

            if (!newResults) {
                throw new Error('Elemen hasil penilaian tidak ditemukan.');
            }

            results.innerHTML = newResults.innerHTML;

            if (updateHistory) {
                window.history.replaceState({}, '', url);
            }
        } catch (error) {
            if (error.name !== 'AbortError') {
                console.error(error);
                results.innerHTML =
                    '<div class="hasil-ajax-error">' +
                    'Gagal memuat data. Silakan coba lagi.' +
                    '</div>';
            }
        } finally {
            if (currentRequest === requestNumber) {
                results.classList.remove('is-loading');
                results.removeAttribute('aria-busy');
            }
        }
    }

    function buildUrl(pageNumber = null) {
        const url = new URL(form.action, window.location.origin);
        const data = new FormData(form);

        for (const [key, value] of data.entries()) {
            if (String(value).trim() !== '') {
                url.searchParams.set(key, value);
            }
        }

        if (pageNumber) {
            url.searchParams.set('page', pageNumber);
        }

        return url.toString();
    }

    search.addEventListener('input', function () {
        clearTimeout(debounceTimer);

        debounceTimer = setTimeout(() => {
            loadResults(buildUrl());
        }, 300);
    });

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        clearTimeout(debounceTimer);
        loadResults(buildUrl());
    });

    tabs?.addEventListener('click', function (event) {
        const button = event.target.closest('[data-predikat]');
        if (!button) return;

        predikatInput.value = button.dataset.predikat;

        tabs.querySelectorAll('[data-predikat]').forEach(tab => {
            tab.classList.toggle('active', tab === button);
        });

        clearTimeout(debounceTimer);
        loadResults(buildUrl());
    });

    results.addEventListener('click', function (event) {
        const link = event.target.closest('.hasil-pagination a');
        if (!link) return;

        event.preventDefault();
        loadResults(link.href);
    });

    const chart = document.getElementById('hasilTrendChart');
    const detail = document.getElementById('hasilTrendDetail');

    chart?.addEventListener('click', function (event) {
        const item = event.target.closest('.hasil-trend-item');
        if (!item) return;

        chart.querySelectorAll('.hasil-trend-item').forEach(el => {
            el.classList.toggle('is-selected', el === item);
        });

        detail.textContent =
            item.dataset.period +
            ' — KPI: ' + item.dataset.kpi +
            ' | Nilai akhir: ' + item.dataset.final;
    });
});
</script>
@endpush
