@extends('layouts.apps')

@section('title', 'Cuti Saya')

@push('styles')
    <link href="{{ asset('css/pegawai/cuti.css') }}" rel="stylesheet">
@endpush

@section('content')
    <div class="cuti-page">

        <div class="cuti-page-header">
            <div class="cuti-page-heading">
                <a href="{{ route('pegawai.dashboard') }}"
                    class="cuti-back-button"
                    aria-label="Kembali ke dashboard">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>

                <div>
                    <h1>Cuti Saya</h1>
                    <p>Kelola kuota dan pengajuan cuti Anda.</p>
                </div>
            </div>

            @if (\Illuminate\Support\Facades\Route::has('pegawai.cuti.create'))
                <a href="{{ route('pegawai.cuti.create') }}"
                    class="cuti-primary-button">
                    <i class="fa-solid fa-plus"></i>
                    <span>Ajukan Cuti</span>
                </a>
            @else
                <button type="button"
                    class="cuti-primary-button"
                    disabled>
                    <i class="fa-solid fa-plus"></i>
                    <span>Ajukan Cuti</span>
                </button>
            @endif
        </div>

        @php
            $summaryCards = [
                [
                    'label' => 'Total Kuota Tahunan',
                    'value' => $kuota['total'],
                    'icon' => 'fa-calendar-days',
                    'color' => 'blue',
                    'description' => 'Total hak cuti tahun ini',
                ],
                [
                    'label' => 'Cuti Terpakai',
                    'value' => $kuota['terpakai'],
                    'icon' => 'fa-calendar-check',
                    'color' => 'purple',
                    'description' => 'Cuti yang telah disetujui',
                ],
                [
                    'label' => 'Dalam Pengajuan',
                    'value' => $kuota['pengajuan'],
                    'icon' => 'fa-clock',
                    'color' => 'orange',
                    'description' => 'Menunggu persetujuan',
                ],
                [
                    'label' => 'Sisa Kuota',
                    'value' => $kuota['tersedia'],
                    'icon' => 'fa-wallet',
                    'color' => 'green',
                    'description' => 'Kuota yang dapat digunakan',
                ],
            ];

            $totalKuota = $kuota['total'];

            $persenTerpakai = $totalKuota > 0
                ? ($kuota['terpakai'] / $totalKuota) * 100
                : 0;

            $persenPengajuan = $totalKuota > 0
                ? ($kuota['pengajuan'] / $totalKuota) * 100
                : 0;

            $persenTersedia = $totalKuota > 0
                ? ($kuota['tersedia'] / $totalKuota) * 100
                : 0;
        @endphp

        <div class="cuti-summary-grid">
            @foreach ($summaryCards as $item)
                <div class="cuti-summary-card">
                    <div class="cuti-summary-icon cuti-icon-{{ $item['color'] }}">
                        <i class="fa-solid {{ $item['icon'] }}"></i>
                    </div>

                    <div class="cuti-summary-content">
                        <span class="cuti-summary-label">
                            {{ $item['label'] }}
                        </span>

                        <div class="cuti-summary-value">
                            {{ $item['value'] }}
                            <small>hari</small>
                        </div>

                        <span class="cuti-summary-description">
                            {{ $item['description'] }}
                        </span>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="cuti-information-grid">

            <section class="cuti-panel">
                <div class="cuti-panel-header">
                    <div class="cuti-panel-heading">
                        <span class="cuti-panel-icon">
                            <i class="fa-solid fa-chart-pie"></i>
                        </span>

                        <div>
                            <h2>Rincian Kuota Tahunan</h2>
                            <p>Informasi hak cuti Anda</p>
                        </div>
                    </div>

                    <span class="cuti-year-label">
                        {{ $tahun }}
                    </span>
                </div>

                <div class="cuti-panel-body">
                    <div class="cuti-quota-detail">
                        <div class="cuti-quota-row">
                            <span>Kuota dasar</span>
                            <strong>{{ $kuota['dasar'] }} hari</strong>
                        </div>

                        <div class="cuti-quota-row">
                            <span>Tambahan akumulasi</span>
                            <strong>{{ $kuota['akumulasi'] }} hari</strong>
                        </div>

                        <div class="cuti-quota-row cuti-quota-total">
                            <span>Total kuota</span>
                            <strong>{{ $kuota['total'] }} hari</strong>
                        </div>
                    </div>

                    <div class="cuti-progress-bar">
                        <span class="cuti-progress-used"
                            style="width: {{ $persenTerpakai }}%">
                        </span>

                        <span class="cuti-progress-pending"
                            style="width: {{ $persenPengajuan }}%">
                        </span>

                        <span class="cuti-progress-available"
                            style="width: {{ $persenTersedia }}%">
                        </span>
                    </div>

                    <div class="cuti-progress-legend">
                        <div>
                            <span class="cuti-legend-dot cuti-dot-blue"></span>
                            Terpakai
                        </div>

                        <div>
                            <span class="cuti-legend-dot cuti-dot-orange"></span>
                            Pengajuan
                        </div>

                        <div>
                            <span class="cuti-legend-dot cuti-dot-green"></span>
                            Tersedia
                        </div>
                    </div>
                </div>
            </section>

            <section class="cuti-panel">
                <div class="cuti-panel-header">
                    <div class="cuti-panel-heading">
                        <span class="cuti-panel-icon">
                            <i class="fa-solid fa-circle-info"></i>
                        </span>

                        <div>
                            <h2>Informasi Cuti</h2>
                            <p>Ketentuan pengajuan cuti</p>
                        </div>
                    </div>
                </div>

                <div class="cuti-panel-body">
                    <div class="cuti-information-list">

                        <div class="cuti-information-item">
                            <i class="fa-regular fa-calendar-check"></i>
                            <div>
                                <strong>Ketentuan Pengajuan</strong>
                                <p>
                                    Waktu pengajuan dan jumlah hari cuti
                                    mengikuti ketentuan setiap jenis cuti.
                                </p>
                            </div>
                        </div>

                        <div class="cuti-information-item">
                            <i class="fa-solid fa-users"></i>
                            <div>
                                <strong>Persetujuan Bertingkat</strong>
                                <p>
                                    Pengajuan diproses oleh Atasan,
                                    Kepala Bagian, dan Direktur.
                                </p>
                            </div>
                        </div>

                        <div class="cuti-information-item">
                            <i class="fa-solid fa-file-circle-check"></i>
                            <div>
                                <strong>Surat Keputusan Cuti</strong>
                                <p>
                                    Cuti dinyatakan resmi setelah
                                    persetujuan akhir dan penerbitan SK.
                                </p>
                            </div>
                        </div>

                    </div>
                </div>
            </section>

        </div>

        <section class="cuti-panel cuti-history-panel">
            <div class="cuti-panel-header">
                <div class="cuti-panel-heading">
                    <span class="cuti-panel-icon">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                    </span>

                    <div>
                        <h2>Riwayat Pengajuan Cuti</h2>
                        <p>Pantau status pengajuan cuti Anda</p>
                    </div>
                </div>

                <span class="cuti-year-label">
                    {{ $pengajuan->count() }} Pengajuan
                </span>
            </div>

            <div class="cuti-panel-body">

                <div class="cuti-history-toolbar">
                    <div class="cuti-search">
                        <i class="fa-solid fa-magnifying-glass"></i>

                        <input type="search"
                            placeholder="Cari pengajuan cuti..."
                            aria-label="Cari pengajuan cuti"
                            disabled>
                    </div>

                    <select class="cuti-filter"
                        aria-label="Filter status cuti"
                        disabled>
                        <option>Semua Status</option>
                        <option>Dalam Pengajuan</option>
                        <option>Disetujui</option>
                        <option>Disetujui Sebagian</option>
                        <option>Ditolak</option>
                    </select>
                </div>

                <div class="cuti-empty-state">
                    <div class="cuti-empty-icon">
                        <i class="fa-regular fa-calendar-days"></i>
                    </div>

                    <h3>Belum Ada Pengajuan Cuti</h3>

                    <p>
                        Riwayat pengajuan cuti Anda akan ditampilkan
                        pada halaman ini setelah pengajuan dibuat.
                    </p>
                </div>

            </div>
        </section>

    </div>
@endsection
