@extends('layouts.app')

@section('title', 'Dashboard Direktur')

@section('content')
    @php
        $totalKepalaBagian = $kepalaBagians->count();
        $sudahDinilai = $sudahDinilaiIds->count();
        $belumDinilai = max(0, $totalKepalaBagian - $sudahDinilai);
        $persentaseDinilai = $totalKepalaBagian > 0 ? round(($sudahDinilai / $totalKepalaBagian) * 100) : 0;
    @endphp

    <main class="direktur-dashboard">
        <header class="direktur-dashboard-hero">
            <div>
                <span class="direktur-dashboard-eyebrow"><i class="fa-solid fa-circle"></i> Direktur / Penilai</span>
                <h1>Selamat Datang, {{ auth()->user()->name }}</h1>
                <p>Ringkasan penilaian kinerja Kepala Bagian yang menjadi tanggung jawab Anda.</p>
            </div>
            <div class="direktur-dashboard-period">
                <i class="fa-regular fa-calendar-check"></i>
                <span>
                    <small>PERIODE BERJALAN</small>
                    <strong>{{ $periodeAktif?->nama_periode ?? 'Belum tersedia' }}</strong>
                </span>
            </div>
        </header>

        <section class="direktur-dashboard-stats" aria-label="Ringkasan statistik penilaian">
            <article class="is-team">
                <span><i class="fa-solid fa-users"></i></span>
                <strong>{{ $totalKepalaBagian }}</strong>
                <b>Kepala Bagian</b>
                <small>Kepala Bagian aktif yang ditugaskan kepada Anda</small>
            </article>
            <article class="is-complete">
                <span><i class="fa-solid fa-circle-check"></i></span>
                <strong>{{ $sudahDinilai }}</strong>
                <b>Sudah Dinilai</b>
                <small>Penilaian KPI telah diberikan pada periode berjalan</small>
            </article>
            <article class="is-pending">
                <span><i class="fa-regular fa-clock"></i></span>
                <strong>{{ $belumDinilai }}</strong>
                <b>Belum Dinilai</b>
                <small>Kepala Bagian yang masih menunggu penilaian</small>
            </article>
        </section>

        <section class="direktur-dashboard-progress" aria-label="Progres penilaian">
            <div>
                <span>Progres penilaian Kepala Bagian</span>
                <strong>{{ $persentaseDinilai }}% selesai</strong>
            </div>
            <div class="direktur-dashboard-progress-track">
                <span style="width: {{ $persentaseDinilai }}%"></span>
            </div>
            <small>{{ $sudahDinilai }} dari {{ $totalKepalaBagian }} Kepala Bagian telah dinilai.</small>
        </section>

        <section class="direktur-dashboard-tools" aria-label="Daftar Kepala Bagian yang dinilai">
            @forelse ($kepalaBagians as $kepalaBagian)
                @php $hasRating = $sudahDinilaiIds->contains($kepalaBagian->id); @endphp
                <a class="direktur-dashboard-person" href="{{ $periodeAktif
                    ? route('direktur.penilaian-kepala-bagian.' . ($hasRating ? 'show' : 'create'), ['pegawai' => $kepalaBagian, 'periode_id' => $periodeAktif->id])
                    : route('direktur.penilaian-kepala-bagian.index') }}">
                    <i class="fa-solid fa-user-tie"></i>
                    <div class="direktur-dashboard-person-info">
                        <strong>{{ $kepalaBagian->nama }}</strong>
                        <small>{{ $kepalaBagian->jabatan->nama_jabatan ?? 'Jabatan belum diatur' }} · {{ $hasRating ? 'Sudah dinilai' : 'Belum dinilai' }}</small>
                    </div>
                    <i class="fa-solid fa-arrow-right"></i>
                </a>
            @empty
                <div class="direktur-dashboard-empty">
                    <i class="fa-solid fa-user-tie"></i>
                    <strong>Belum ada Kepala Bagian yang ditugaskan</strong>
                    <span>Master dapat menghubungkan Kepala Bagian dengan akun Direktur melalui Data Pegawai.</span>
                </div>
            @endforelse
        </section>

        <section class="direktur-dashboard-action">
            <div class="direktur-dashboard-action-icon"><i class="fa-solid fa-chart-line"></i></div>
            <div>
                <h2>Penilaian Kinerja Kepala Bagian</h2>
                <p>Tinjau dan berikan nilai KPI kepada Kepala Bagian yang menjadi tanggung jawab Anda.</p>
            </div>
            <a href="{{ route('direktur.penilaian-kepala-bagian.index') }}">Buka Penilaian <i class="fa-solid fa-arrow-right"></i></a>
        </section>

        <section class="direktur-dashboard-action">
            <div class="direktur-dashboard-action-icon"><i class="fa-solid fa-table-list"></i></div>
            <div>
                <h2>Rekap Data Kehadiran dan Tunjangan</h2>
                <p>Lihat rekap bulanan seluruh pegawai di semua divisi.</p>
            </div>
            <a href="{{ route('direktur.rekap-data.index') }}">Buka Rekap Data <i class="fa-solid fa-arrow-right"></i></a>
        </section>
    </main>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/direktur/index.css') }}">
@endpush