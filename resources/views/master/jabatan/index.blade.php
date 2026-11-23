@extends('layouts.app')

@section('title', 'Data Jabatan')

@section('content')
<div class="master-jabatan-page mj-page">

    <header class="mj-heading">
        <div>
            <h1>
                <i class="fa-solid fa-briefcase"></i>
                Data Jabatan
            </h1>
            <p>Kelola jabatan pada setiap divisi</p>
        </div>

        <a href="{{ route('master.jabatan.create') }}" class="mj-add">
            <i class="fa-solid fa-plus"></i>
            Tambah Jabatan
        </a>
    </header>

    <section class="mj-panel">

        <div class="mj-toolbar">
            <div class="mj-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input
                    type="search"
                    id="mjSearch"
                    value="{{ $search }}"
                    placeholder="Cari nama jabatan, grade, divisi..."
                    autocomplete="off"
                    aria-label="Cari jabatan"
                >
                <span id="mjSearchLoading" class="mj-search-loading" hidden>
                    <i class="fa-solid fa-spinner fa-spin"></i>
                </span>
            </div>
        </div>

        <div class="mj-tabs" id="mjTabs" aria-label="Filter divisi">
            <button
                type="button"
                class="mj-tab {{ $divisiId === '' ? 'active' : '' }}"
                data-divisi=""
                aria-pressed="{{ $divisiId === '' ? 'true' : 'false' }}"
            >
                Semua Divisi
            </button>

            @foreach ($divisis as $divisi)
                <button
                    type="button"
                    class="mj-tab {{ (string) $divisiId === (string) $divisi->id ? 'active' : '' }}"
                    data-divisi="{{ $divisi->id }}"
                    aria-pressed="{{ (string) $divisiId === (string) $divisi->id ? 'true' : 'false' }}"
                >
                    {{ $divisi->nama_divisi }}
                </button>
            @endforeach
        </div>

        <div id="mjResults" aria-live="polite">
            @include('master.jabatan.partials.results')
        </div>

    </section>
</div>
@endsection

@push('styles')
    <link
        rel="stylesheet"
        href="{{ asset('css/master/master-jabatan.css') }}?v={{ filemtime(public_path('css/master/master-jabatan.css')) }}"
    >
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('mjSearch');
    const tabs = document.getElementById('mjTabs');
    const results = document.getElementById('mjResults');
    const loading = document.getElementById('mjSearchLoading');

    if (!searchInput || !tabs || !results) {
        return;
    }

    const endpoint = @json(route('master.jabatan.index'));

    let activeDivisi = @json((string) $divisiId);
    let debounceTimer;
    let controller;

    function setLoading(isLoading) {
        loading.hidden = !isLoading;
        results.classList.toggle('mj-is-loading', isLoading);
        results.setAttribute('aria-busy', isLoading ? 'true' : 'false');
    }

    function updateTabs() {
        tabs.querySelectorAll('[data-divisi]').forEach(function (button) {
            const active = button.dataset.divisi === activeDivisi;

            button.classList.toggle('active', active);
            button.setAttribute('aria-pressed', active ? 'true' : 'false');
        });
    }

    async function loadResults(page = 1, updateHistory = true) {
        if (controller) {
            controller.abort();
        }

        controller = new AbortController();

        const currentController = controller;
        const url = new URL(endpoint, window.location.origin);

        const search = searchInput.value.trim();

        if (search) {
            url.searchParams.set('search', search);
        }

        if (activeDivisi) {
            url.searchParams.set('divisi_id', activeDivisi);
        }

        if (page > 1) {
            url.searchParams.set('page', page);
        }

        setLoading(true);

        try {
            const response = await fetch(url.toString(), {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                signal: currentController.signal
            });

            if (!response.ok) {
                throw new Error('Gagal memuat data jabatan.');
            }

            const data = await response.json();

            if (currentController !== controller) {
                return;
            }

            results.innerHTML = data.html;

            if (updateHistory) {
                window.history.replaceState({}, '', url.toString());
            }
        } catch (error) {
            if (error.name !== 'AbortError') {
                results.innerHTML = `
                    <div class="mj-empty">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        <strong>Data gagal dimuat.</strong>
                        <p>Silakan coba kembali.</p>
                    </div>
                `;
            }
        } finally {
            if (currentController === controller) {
                setLoading(false);
            }
        }
    }

    searchInput.addEventListener('input', function () {
        clearTimeout(debounceTimer);

        debounceTimer = setTimeout(function () {
            loadResults(1);
        }, 300);
    });

    tabs.addEventListener('click', function (event) {
        const button = event.target.closest('[data-divisi]');

        if (!button) {
            return;
        }

        clearTimeout(debounceTimer);

        activeDivisi = button.dataset.divisi;
        updateTabs();
        loadResults(1);
    });

    results.addEventListener('click', function (event) {
        const link = event.target.closest('.mj-pagination a');

        if (!link) {
            return;
        }

        event.preventDefault();

        const url = new URL(link.href);
        const page = Number(url.searchParams.get('page') || 1);

        loadResults(page);
    });
});
</script>
@endpush
