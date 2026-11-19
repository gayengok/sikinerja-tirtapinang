@extends('layouts.app')

@section('title', 'Form Penilaian Kompetensi')

@php
    $assessmentRoutePrefix = $assessmentRoutePrefix ?? 'atasan.kompetensi';
    $targetLabel = $targetLabel ?? 'Pegawai';

    $updateRoute = $assessmentRoutePrefix === 'atasan.kompetensi'
        ? $assessmentRoutePrefix . '.update'
        : $assessmentRoutePrefix . '.kompetensi.update';

    $indexRoute = $assessmentRoutePrefix . '.index';

    $dashboardRoute = match (true) {
        $assessmentRoutePrefix === 'kepala_bagian.penilaian-atasan' => 'kepala_bagian.dashboard',
        str_starts_with($assessmentRoutePrefix, 'direktur.') => 'direktur.dashboard',
        default => 'atasan.dashboard',
    };

    $routeKembali = \Illuminate\Support\Facades\Route::has($indexRoute)
        ? route($indexRoute, ['periode_id' => $periode->id])
        : route($dashboardRoute);

    $namaPegawai = $pegawai->nama ?? 'Pegawai';
    $inisialPegawai = mb_strtoupper(mb_substr($namaPegawai, 0, 2));

    $namaJabatan = $pegawai->jabatan->nama_jabatan ?? '-';
    $namaDivisi = $pegawai->jabatan->divisi->nama_divisi ?? '-';

    $namaPeriode = $periode->nama_periode ?? $periode->nama ?? '-';

    $tanggalMulai = $periode->tanggal_mulai
        ? \Carbon\Carbon::parse($periode->tanggal_mulai)->translatedFormat('d M Y')
        : '-';

    $tanggalSelesai = $periode->tanggal_selesai
        ? \Carbon\Carbon::parse($periode->tanggal_selesai)->translatedFormat('d M Y')
        : '-';

    $jumlahKompetensi = $masterKompetensis->count();

    $jumlahTerisi = 0;
    $totalSkor = 0;

    foreach ($masterKompetensis as $index => $kompetensi) {
        $tersimpan = $penilaianTersimpan->get($kompetensi->id);

        $skor = old(
            'kompetensi.' . $index . '.skor',
            $tersimpan->skor ?? null
        );

        if (is_numeric($skor) && (int) $skor >= 1 && (int) $skor <= 5) {
            $jumlahTerisi++;
            $totalSkor += (int) $skor;
        }
    }

    $persentaseTerisi = $jumlahKompetensi > 0
        ? round(($jumlahTerisi / $jumlahKompetensi) * 100)
        : 0;

    $rataRata = $jumlahTerisi > 0
        ? number_format($totalSkor / $jumlahTerisi, 1, ',', '.')
        : '-';
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/atasan/kompetensi/create.css') }}?v=20261119">
@endpush

@section('content')
<div class="kompetensi-form-page">

    <div class="kompetensi-mobile-navigation">
        <a href="{{ $routeKembali }}" aria-label="Kembali">
            <i class="fa-solid fa-arrow-left"></i>
        </a>

        <div>
            <span>Manajemen Penilaian</span>
            <strong>Penilaian Kompetensi</strong>
        </div>
    </div>

    <nav class="kompetensi-form-breadcrumb" aria-label="Breadcrumb">
        <a href="{{ route($dashboardRoute) }}">Dashboard</a>
        <i class="fa-solid fa-chevron-right"></i>
        <a href="{{ $routeKembali }}">Penilaian Kompetensi</a>
        <i class="fa-solid fa-chevron-right"></i>
        <span>Form Penilaian</span>
    </nav>

    <section class="kompetensi-page-hero">
        <div class="kompetensi-hero-content">
            <div class="kompetensi-hero-eyebrow">
                <span class="kompetensi-hero-dot"></span>
                <span>ATASAN / PENILAI</span>
                <span class="kompetensi-hero-divider"></span>
                <i class="fa-solid fa-clipboard-check"></i>
                <span>Evaluasi Kompetensi</span>
            </div>

            <h1>Penilaian Kompetensi</h1>

            <p>
                Berikan penilaian perilaku kerja berdasarkan indikator
                kompetensi yang berlaku untuk jabatan pegawai.
            </p>
        </div>

        <div class="kompetensi-hero-period">
            <span class="kompetensi-hero-period-icon">
                <i class="fa-regular fa-calendar-check"></i>
            </span>

            <div>
                <small>PERIODE PENILAIAN</small>
                <strong>{{ $namaPeriode }}</strong>
                <span>{{ $tanggalMulai }} — {{ $tanggalSelesai }}</span>
            </div>
        </div>
    </section>

    <section class="kompetensi-person-card">
        <div class="kompetensi-person-main">
            <div class="kompetensi-avatar">
                {{ $inisialPegawai }}
            </div>

            <div class="kompetensi-person-info">
                <span class="kompetensi-person-label">
                    {{ strtoupper($targetLabel) }} YANG DINILAI
                </span>

                <h2>{{ $namaPegawai }}</h2>

                <p>
                    <i class="fa-solid fa-briefcase"></i>
                    {{ $namaJabatan }}
                </p>

                <p>
                    <i class="fa-regular fa-building"></i>
                    {{ $namaDivisi }}
                </p>
            </div>
        </div>

        <div class="kompetensi-person-side">
            <span class="kompetensi-person-side-label">
                <i class="fa-solid fa-list-check"></i>
                Indikator Kompetensi
            </span>

            <strong>{{ $jumlahKompetensi }}</strong>
            <small>Aspek yang dinilai</small>
        </div>
    </section>

    @if ($masterKompetensis->isEmpty())
        <section class="kompetensi-empty-state">
            <div class="kompetensi-empty-icon">
                <i class="fa-solid fa-clipboard-list"></i>
            </div>

            <h2>Belum Ada Indikator Kompetensi</h2>

            <p>
                Master kompetensi untuk jabatan pegawai ini belum tersedia.
                Penilaian dapat dilakukan setelah indikator kompetensi ditambahkan.
            </p>

            <a href="{{ $routeKembali }}">
                <i class="fa-solid fa-arrow-left"></i>
                Kembali
            </a>
        </section>
    @else

        <section class="kompetensi-progress-card">
            <div class="kompetensi-progress-header">
                <div class="kompetensi-progress-title">
                    <span class="kompetensi-progress-icon">
                        <i class="fa-solid fa-chart-simple"></i>
                    </span>

                    <div>
                        <span>PROGRES PENILAIAN</span>
                        <h2>Kelangkapan Pengisian</h2>
                    </div>
                </div>

                <div class="kompetensi-progress-percentage">
                    <strong id="kompetensi-progress-percent">
                        {{ $persentaseTerisi }}%
                    </strong>
                    <span>Terisi</span>
                </div>
            </div>

            <div class="kompetensi-progress-track">
                <div
                    id="kompetensi-progress-bar"
                    style="width: {{ $persentaseTerisi }}%"
                ></div>
            </div>

            <div class="kompetensi-progress-footer">
                <span>
                    <i class="fa-solid fa-circle-check"></i>
                    <strong id="kompetensi-filled-count">{{ $jumlahTerisi }}</strong>
                    dari {{ $jumlahKompetensi }} indikator telah dinilai
                </span>

                <span>
                    Rata-rata skor:
                    <strong id="kompetensi-average-score">{{ $rataRata }}</strong>
                    / 5
                </span>
            </div>
        </section>

        <div class="kompetensi-section-heading">
            <div>
                <span class="kompetensi-section-kicker">
                    FORM EVALUASI
                </span>

                <h2>Aspek Perilaku Kerja</h2>

                <p>
                    Pilih skor 1 sampai 5 untuk setiap indikator,
                    kemudian tambahkan catatan penilai jika diperlukan.
                </p>
            </div>

            <div class="kompetensi-weight-badge">
                <i class="fa-solid fa-scale-balanced"></i>
                Bobot 40%
            </div>
        </div>

        @if ($errors->any())
            <div class="kompetensi-validation-alert" role="alert">
                <i class="fa-solid fa-circle-exclamation"></i>

                <div>
                    <strong>Periksa kembali penilaian Anda</strong>
                    <p>
                        Pastikan seluruh indikator telah diberi skor
                        dan data yang dimasukkan sudah sesuai.
                    </p>
                </div>
            </div>
        @endif

        <form
            id="kompetensi-assessment-form"
            action="{{ route($updateRoute, $pegawai) }}"
            method="POST"
        >
            @csrf
            @method('PUT')

            <input
                type="hidden"
                name="periode_id"
                value="{{ $periode->id }}"
            >

            <div class="kompetensi-indicator-grid">

                @foreach ($masterKompetensis as $index => $kompetensi)
                    @php
                        $tersimpan = $penilaianTersimpan->get($kompetensi->id);

                        $skorTerpilih = old(
                            'kompetensi.' . $index . '.skor',
                            $tersimpan->skor ?? null
                        );

                        $catatanTerpilih = old(
                            'kompetensi.' . $index . '.catatan',
                            $tersimpan->catatan ?? ''
                        );
                    @endphp

                    <article
                        class="kompetensi-indicator {{ $skorTerpilih ? 'is-filled' : '' }}"
                        data-kompetensi-card
                    >
                        <div class="kompetensi-indicator-header">
                            <div class="kompetensi-indicator-title">
                                <span class="kompetensi-indicator-number">
                                    {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                                </span>

                                <div>
                                    <span class="kompetensi-indicator-kicker">
                                        INDIKATOR {{ $index + 1 }}
                                    </span>

                                    <h3>{{ $kompetensi->nama_kompetensi }}</h3>
                                </div>
                            </div>

                            <span
                                class="kompetensi-indicator-status {{ $skorTerpilih ? 'is-complete' : '' }}"
                                data-kompetensi-status
                            >
                                <i class="fa-solid {{ $skorTerpilih ? 'fa-circle-check' : 'fa-clock' }}"></i>
                                <span>{{ $skorTerpilih ? 'Terisi' : 'Belum Dinilai' }}</span>
                            </span>
                        </div>

                        @if ($kompetensi->definisi_perilaku)
                            <p class="kompetensi-indicator-description">
                                {{ $kompetensi->definisi_perilaku }}
                            </p>
                        @endif

                        <input
                            type="hidden"
                            name="kompetensi[{{ $index }}][master_kompetensi_id]"
                            value="{{ $kompetensi->id }}"
                        >

                        <fieldset class="kompetensi-score-field">
                            <legend>
                                Skor Penilaian
                                <span class="kompetensi-required">*</span>
                            </legend>

                            <div class="kompetensi-score-options">
                                @for ($skor = 1; $skor <= 5; $skor++)
                                    <label class="kompetensi-score-option">
                                        <input
                                            type="radio"
                                            name="kompetensi[{{ $index }}][skor]"
                                            value="{{ $skor }}"
                                            required
                                            data-kompetensi-score
                                            @checked((int) $skorTerpilih === $skor)
                                        >

                                        <span>{{ $skor }}</span>
                                    </label>
                                @endfor
                            </div>

                            <div class="kompetensi-score-scale">
                                <span>Sangat Kurang</span>
                                <span>Sangat Baik</span>
                            </div>

                            @error('kompetensi.' . $index . '.skor')
                                <small class="kompetensi-field-error">
                                    {{ $message }}
                                </small>
                            @enderror
                        </fieldset>

                        <div class="kompetensi-note-field">
                            <label for="kompetensi-catatan-{{ $index }}">
                                <i class="fa-regular fa-comment-dots"></i>
                                Catatan Penilai
                                <span>Opsional</span>
                            </label>

                            <textarea
                                id="kompetensi-catatan-{{ $index }}"
                                name="kompetensi[{{ $index }}][catatan]"
                                rows="3"
                                maxlength="1000"
                                placeholder="Tuliskan hasil pengamatan atau alasan pemberian skor..."
                            >{{ $catatanTerpilih }}</textarea>

                            <div class="kompetensi-note-footer">
                                @error('kompetensi.' . $index . '.catatan')
                                    <small class="kompetensi-field-error">
                                        {{ $message }}
                                    </small>
                                @else
                                    <small>Catatan membantu proses evaluasi.</small>
                                @enderror

                                <small>
                                    <span data-note-count>{{ mb_strlen($catatanTerpilih) }}</span>/1000
                                </small>
                            </div>
                        </div>
                    </article>
                @endforeach

            </div>

            <div class="kompetensi-form-bottom">
                <div class="kompetensi-form-bottom-info">
                    <span class="kompetensi-form-bottom-icon">
                        <i class="fa-solid fa-circle-info"></i>
                    </span>

                    <div>
                        <strong>Pastikan penilaian sudah sesuai</strong>
                        <p>
                            Nilai yang disimpan akan digunakan dalam
                            perhitungan hasil penilaian pegawai.
                        </p>
                    </div>
                </div>

                <div class="kompetensi-form-actions">
                    <a href="{{ $routeKembali }}">
                        Batal
                    </a>

                    <button type="submit" id="kompetensi-submit-button">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span>Simpan Penilaian</span>
                    </button>
                </div>
            </div>
        </form>
    @endif

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('kompetensi-assessment-form');

    if (!form) {
        return;
    }

    const cards = Array.from(
        form.querySelectorAll('[data-kompetensi-card]')
    );

    const totalIndikator = cards.length;

    const progressPercent = document.getElementById(
        'kompetensi-progress-percent'
    );

    const progressBar = document.getElementById(
        'kompetensi-progress-bar'
    );

    const filledCount = document.getElementById(
        'kompetensi-filled-count'
    );

    const averageScore = document.getElementById(
        'kompetensi-average-score'
    );

    const submitButton = document.getElementById(
        'kompetensi-submit-button'
    );

    function updateProgress() {
        let jumlahTerisi = 0;
        let totalSkor = 0;

        cards.forEach(function (card) {
            const selected = card.querySelector(
                'input[data-kompetensi-score]:checked'
            );

            const status = card.querySelector(
                '[data-kompetensi-status]'
            );

            if (selected) {
                jumlahTerisi++;
                totalSkor += Number(selected.value);

                card.classList.add('is-filled');

                if (status) {
                    status.classList.add('is-complete');

                    status.innerHTML =
                        '<i class="fa-solid fa-circle-check"></i>' +
                        '<span>Terisi</span>';
                }
            } else {
                card.classList.remove('is-filled');

                if (status) {
                    status.classList.remove('is-complete');

                    status.innerHTML =
                        '<i class="fa-regular fa-clock"></i>' +
                        '<span>Belum Dinilai</span>';
                }
            }
        });

        const persen = totalIndikator > 0
            ? Math.round(jumlahTerisi / totalIndikator * 100)
            : 0;

        const rataRata = jumlahTerisi > 0
            ? (totalSkor / jumlahTerisi).toLocaleString('id-ID', {
                minimumFractionDigits: 1,
                maximumFractionDigits: 1
            })
            : '-';

        if (progressPercent) {
            progressPercent.textContent = persen + '%';
        }

        if (progressBar) {
            progressBar.style.width = persen + '%';
        }

        if (filledCount) {
            filledCount.textContent = jumlahTerisi;
        }

        if (averageScore) {
            averageScore.textContent = rataRata;
        }
    }

    form.querySelectorAll('[data-kompetensi-score]').forEach(
        function (input) {
            input.addEventListener('change', updateProgress);
        }
    );

    form.querySelectorAll('textarea[maxlength]').forEach(
        function (textarea) {
            const field = textarea.closest('.kompetensi-note-field');

            const counter = field
                ? field.querySelector('[data-note-count]')
                : null;

            function updateCounter() {
                if (counter) {
                    counter.textContent = Array.from(
                        textarea.value
                    ).length;
                }
            }

            textarea.addEventListener('input', updateCounter);
            updateCounter();
        }
    );

    form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) {
            event.preventDefault();

            const invalidInput = form.querySelector(':invalid');

            if (invalidInput) {
                const card = invalidInput.closest(
                    '[data-kompetensi-card]'
                );

                if (card) {
                    card.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                }

                invalidInput.focus({
                    preventScroll: true
                });
            }

            return;
        }

        if (submitButton) {
            submitButton.disabled = true;

            submitButton.innerHTML =
                '<i class="fa-solid fa-spinner fa-spin"></i>' +
                '<span>Menyimpan...</span>';
        }
    });

    updateProgress();
});
</script>
@endpush
