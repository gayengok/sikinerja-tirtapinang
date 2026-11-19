
@extends('layouts.app')

@section('title', 'Pegawai Bawahan')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/atasan/pegawai/index.css') }}?v=20261119-3">
@endpush

@section('content')
    @php
        $skorTampilan = $skorTim !== null
            ? min(100, max(0, round($skorTim, 1)))
            : null;

        $periodeLabel = $periodeAktif?->tanggal_mulai
            ? \Carbon\Carbon::parse($periodeAktif->tanggal_mulai)->translatedFormat('F Y')
            : 'Belum ada periode aktif';

        $statusKinerja = $skorTampilan === null
            ? 'Penilaian Belum Tersedia'
            : ($skorTampilan >= 90
                ? 'Kinerja Tim Optimal'
                : ($skorTampilan >= 75
                    ? 'Kinerja Tim Baik'
                    : 'Kinerja Tim Perlu Evaluasi'));

        $deskripsiKinerja = $skorTampilan === null
            ? 'Belum ada nilai akhir yang tercatat untuk periode aktif.'
            : 'Rata-rata nilai akhir penilaian bawahan pada periode aktif.';

        $gradeKinerja = $skorTampilan === null
            ? 'Belum Dinilai'
            : ($skorTampilan >= 90
                ? 'Sangat Baik'
                : ($skorTampilan >= 75
                    ? 'Baik'
                    : 'Perlu Evaluasi'));

        $gradeClass = $skorTampilan === null
            ? 'is-neutral'
            : ($skorTampilan >= 90
                ? 'is-excellent'
                : ($skorTampilan >= 75
                    ? 'is-good'
                    : 'is-warning'));
    @endphp

    <div class="atasan-team-page">



        <header class="atasan-team-header">
            <div class="atasan-team-header-content">
                <span class="atasan-team-kicker">
                    <i class="fa-solid fa-circle"></i> MANAJEMEN TIM
                </span>

                <h1>Pegawai Bawahan</h1>

                <p>
                    Kelola informasi dan pantau penilaian pegawai
                    yang berada di bawah tanggung jawab Anda.
                </p>
            </div>

            <div class="atasan-team-header-badge">
                <span class="atasan-team-header-badge-icon">
                    <i class="fa-solid fa-users"></i>
                </span>

                <div class="atasan-team-header-badge-info">
                    <strong>{{ $totalBawahan }}</strong>
                    <small>Total Bawahan</small>
                </div>
            </div>
        </header>


        <section class="atasan-team-summary">

            <div class="atasan-team-summary-heading">
                <span class="atasan-team-summary-label">
                    <i class="fa-solid fa-circle"></i>
                    TIM PENILAIAN ATASAN
                </span>

                <span class="atasan-team-period">
                    <i class="fa-regular fa-calendar"></i>
                    {{ $periodeLabel }}
                </span>
            </div>

            <div class="atasan-team-summary-main">

                <div
                    class="atasan-team-ring"
                    style="--progress: {{ ($skorTampilan ?? 0) * 3.6 }}deg"
                    role="img"
                    aria-label="Skor kinerja tim: {{ $skorTampilan !== null ? number_format($skorTampilan, 1, ',', '.') . ' persen' : 'Belum tersedia' }}"
                >
                    <div class="atasan-team-ring-inner">
                        <strong>
                            {{ $skorTampilan !== null ? number_format($skorTampilan, 1, ',', '.') . '%' : '-' }}
                        </strong>

                        <small>SKOR TIM</small>
                    </div>
                </div>

                <div class="atasan-team-summary-description">
                    <h2>{{ $statusKinerja }}</h2>
                    <p>{{ $deskripsiKinerja }}</p>
                </div>

                <span class="atasan-team-grade {{ $gradeClass }}">
                    @if ($gradeClass === 'is-warning')
                        <i class="fa-solid fa-circle-exclamation"></i>
                    @elseif ($gradeClass === 'is-neutral')
                        <i class="fa-regular fa-clock"></i>
                    @else
                        <i class="fa-solid fa-circle-check"></i>
                    @endif

                    {{ $gradeKinerja }}
                </span>

            </div>


            <div class="atasan-team-summary-stats">

                <div class="atasan-team-summary-stat">
                    <span class="atasan-team-summary-stat-icon">
                        <i class="fa-solid fa-user-group"></i>
                    </span>

                    <div>
                        <strong>{{ $bawahanAktif }}</strong>
                        <small>Bawahan Aktif</small>
                    </div>
                </div>

                <div class="atasan-team-summary-stat">
                    <span class="atasan-team-summary-stat-icon">
                        <i class="fa-solid fa-circle-check"></i>
                    </span>

                    <div>
                        <strong>{{ $jumlahSudahDinilai }}</strong>
                        <small>Sudah Memiliki Penilaian</small>
                    </div>
                </div>

                <div class="atasan-team-summary-stat">
                    <span class="atasan-team-summary-stat-icon">
                        <i class="fa-regular fa-clock"></i>
                    </span>

                    <div>
                        <strong>{{ $laporanTertunda }}</strong>
                        <small>Laporan Menunggu Approval</small>
                    </div>
                </div>

            </div>
        </section>


        <section class="atasan-team-directory">

            <div class="atasan-team-directory-heading">
                <div>
                    <span class="atasan-team-kicker">
                        DIREKTORI PEGAWAI
                    </span>

                    <h2>Daftar Anggota Tim</h2>

                    <p>
                        Cari dan lihat informasi pegawai bawahan Anda.
                    </p>
                </div>

                <span
                    class="atasan-team-result-count"
                    id="atasan-team-result-count"
                >
                    {{ $pegawais->total() }} Pegawai
                </span>
            </div>


            <form
                id="atasan-team-search-form"
                class="atasan-team-search"
                action="{{ route('atasan.pegawai.index') }}"
                method="GET"
                role="search"
            >
                <input
                    type="hidden"
                    name="filter"
                    value="{{ $filter }}"
                >

                <div class="atasan-team-search-field">
                    <i class="fa-solid fa-magnifying-glass"></i>

                    <input
                        id="atasan-team-search-input"
                        type="search"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Cari nama, NIP, jabatan, divisi, atau email..."
                        autocomplete="off"
                        aria-label="Cari pegawai bawahan"
                    >
                </div>

                <button type="submit">
                    <i class="fa-solid fa-search"></i>
                    Cari Pegawai
                </button>

                @if ($search !== '' || $filter !== 'semua')
                    <a
                        href="{{ route('atasan.pegawai.index') }}"
                        class="atasan-team-reset"
                        id="atasan-team-reset"
                    >
                        Reset
                    </a>
                @endif
            </form>


            <div class="atasan-team-tabs" id="atasan-team-tabs">

                <a
                    href="{{ route('atasan.pegawai.index', ['filter' => 'semua', 'search' => $search]) }}"
                    class="{{ $filter === 'semua' ? 'active' : '' }}"
                    @if ($filter === 'semua') aria-current="page" @endif
                >
                    Semua
                    <b>{{ $totalBawahan }}</b>
                </a>

                <a
                    href="{{ route('atasan.pegawai.index', ['filter' => 'aktif', 'search' => $search]) }}"
                    class="{{ $filter === 'aktif' ? 'active' : '' }}"
                    @if ($filter === 'aktif') aria-current="page" @endif
                >
                    Aktif
                    <b>{{ $bawahanAktif }}</b>
                </a>

                @if ($periodeAktif)
                    <a
                        href="{{ route('atasan.pegawai.index', ['filter' => 'perlu_penilaian', 'search' => $search]) }}"
                        class="{{ $filter === 'perlu_penilaian' ? 'active' : '' }}"
                        @if ($filter === 'perlu_penilaian') aria-current="page" @endif
                    >
                        Perlu Penilaian
                        <b>{{ $jumlahPerluPenilaian }}</b>
                    </a>
                @endif

            </div>


            <div
                id="atasan-team-results"
                aria-live="polite"
            >
                @include('atasan.pegawai.partials.list')
            </div>

        </section>


        <div class="atasan-team-info">
            <i class="fa-solid fa-circle-info"></i>

            <div>
                <strong>Informasi Manajemen Staf</strong>

                <span>
                    Daftar ini hanya menampilkan pegawai yang terhubung
                    langsung dengan akun Atasan Anda berdasarkan
                    struktur organisasi dalam database.
                </span>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('atasan-team-search-form');
    const input = document.getElementById('atasan-team-search-input');
    const results = document.getElementById('atasan-team-results');
    const tabs = document.getElementById('atasan-team-tabs');
    const resultCount = document.getElementById('atasan-team-result-count');

    if (!form || !input || !results) {
        return;
    }

    const baseUrl = form.action;
    const filterInput = form.querySelector('input[name="filter"]');

    let debounceTimer = null;
    let activeController = null;
    let requestNumber = 0;

    function buildUrl(pageUrl = baseUrl) {
        const url = new URL(pageUrl, window.location.origin);
        const search = input.value.trim();
        const filter = filterInput ? filterInput.value : 'semua';

        if (search !== '') {
            url.searchParams.set('search', search);
        } else {
            url.searchParams.delete('search');
        }

        if (filter !== 'semua') {
            url.searchParams.set('filter', filter);
        } else {
            url.searchParams.delete('filter');
        }

        return url;
    }

    function updateTabs(filter) {
        if (!tabs) {
            return;
        }

        tabs.querySelectorAll('a').forEach(function (link) {
            const linkUrl = new URL(link.href, window.location.origin);
            const linkFilter = linkUrl.searchParams.get('filter') || 'semua';
            const active = linkFilter === filter;

            link.classList.toggle('active', active);

            if (active) {
                link.setAttribute('aria-current', 'page');
            } else {
                link.removeAttribute('aria-current');
            }

            const url = buildUrl(link.href);

            if (linkFilter === 'semua') {
                url.searchParams.delete('filter');
            } else {
                url.searchParams.set('filter', linkFilter);
            }

            url.searchParams.delete('page');
            link.href = url.toString();
        });
    }

    async function loadPegawai(url, updateHistory = true) {
        if (activeController) {
            activeController.abort();
        }

        activeController = new AbortController();

        const controller = activeController;
        const currentRequest = ++requestNumber;

        results.classList.add('is-loading');
        results.setAttribute('aria-busy', 'true');

        try {
            const response = await fetch(url.toString(), {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin',
                signal: controller.signal
            });

            if (!response.ok) {
                throw new Error('Gagal memuat data pegawai.');
            }

            const data = await response.json();

            if (currentRequest !== requestNumber) {
                return;
            }

            if (!data.success || typeof data.html !== 'string') {
                throw new Error('Format data pegawai tidak sesuai.');
            }

            results.innerHTML = data.html;

            if (resultCount) {
                resultCount.textContent = data.total + ' Pegawai';
            }

            if (updateHistory) {
                window.history.replaceState(
                    {},
                    '',
                    url.pathname + url.search
                );
            }

            updateTabs(filterInput ? filterInput.value : 'semua');

        } catch (error) {
            if (error.name !== 'AbortError') {
                console.error(error);

                const message = document.createElement('div');
                message.className = 'alert alert-danger mt-3';
                message.setAttribute('role', 'alert');
                message.textContent = 'Pencarian gagal dimuat. Silakan coba lagi.';

                const oldMessage = results.querySelector('.alert-danger');

                if (oldMessage) {
                    oldMessage.remove();
                }

                results.prepend(message);
            }
        } finally {
            if (currentRequest === requestNumber) {
                results.classList.remove('is-loading');
                results.removeAttribute('aria-busy');
            }
        }
    }

    input.addEventListener('input', function () {
        clearTimeout(debounceTimer);

        debounceTimer = setTimeout(function () {
            const url = buildUrl();
            url.searchParams.delete('page');

            loadPegawai(url);
        }, 300);
    });

    form.addEventListener('submit', function (event) {
        event.preventDefault();

        clearTimeout(debounceTimer);

        const url = buildUrl();
        url.searchParams.delete('page');

        loadPegawai(url);
    });

    if (tabs) {
        tabs.addEventListener('click', function (event) {
            const link = event.target.closest('a');

            if (!link || !tabs.contains(link)) {
                return;
            }

            event.preventDefault();
            clearTimeout(debounceTimer);

            const clickedUrl = new URL(link.href);
            const selectedFilter =
                clickedUrl.searchParams.get('filter') || 'semua';

            if (filterInput) {
                filterInput.value = selectedFilter;
            }

            updateTabs(selectedFilter);

            const url = buildUrl();
            url.searchParams.delete('page');

            loadPegawai(url);
        });
    }

    results.addEventListener('click', function (event) {
        const link = event.target.closest('.pagination a');

        if (!link || !link.href) {
            return;
        }

        event.preventDefault();
        clearTimeout(debounceTimer);

        const url = buildUrl(link.href);

        loadPegawai(url);
    });

    window.addEventListener('popstate', function () {
        const url = new URL(window.location.href);

        input.value = url.searchParams.get('search') || '';

        if (filterInput) {
            filterInput.value =
                url.searchParams.get('filter') || 'semua';
        }

        updateTabs(filterInput ? filterInput.value : 'semua');

        loadPegawai(url, false);
    });
});
</script>
@endpush
