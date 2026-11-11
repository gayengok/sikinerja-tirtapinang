@extends('layouts.app')

@section('title', 'Detail Absensi')

@section('content')
    <div class="page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route($dashboardRoute) }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route($attendanceRoute . '.index') }}">Absensi Saya</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Detail</li>
                </ol>
            </nav>
            <h4 class="mb-0">Detail Absensi</h4>
        </div>
        <div>
            <a href="{{ route($attendanceRoute . '.index') }}" class="btn btn-outline-secondary">
                <i class="fa-solid fa-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            @php
                $labelStatus = match ($absensi->status) {
                    'hadir' => 'Hadir',
                    'izin' => 'Izin',
                    'sakit' => 'Sakit',
                    'alpa', 'tidak_hadir' => 'Tidak Hadir',
                    'cuti' => 'Cuti',
                    'dinas_luar' => 'Dinas Luar',
                    default => ucfirst($absensi->status),
                };
                $badgeStatus = match ($absensi->status) {
                    'hadir' => 'bg-success',
                    'izin' => 'bg-info',
                    'sakit' => 'bg-warning text-dark',
                    'alpa', 'tidak_hadir' => 'bg-danger',
                    'cuti' => 'bg-secondary',
                    'dinas_luar' => 'bg-primary',
                    default => 'bg-light text-dark border',
                };
            @endphp
            <div class="row mb-2">
                <div class="col-3 text-muted">Tanggal</div>
                <div class="col-9">: {{ \Carbon\Carbon::parse($absensi->tanggal)->translatedFormat('d F Y') }}</div>
            </div>
            <div class="row mb-2">
                <div class="col-3 text-muted">Jam Masuk</div>
                <div class="col-9">:
                    {{ $absensi->jam_masuk ? \Carbon\Carbon::parse($absensi->jam_masuk)->format('H:i') : '-' }}</div>
            </div>
            <div class="row mb-2">
                <div class="col-3 text-muted">Jam Pulang</div>
                <div class="col-9">:
                    {{ $absensi->jam_pulang ? \Carbon\Carbon::parse($absensi->jam_pulang)->format('H:i') : '-' }}</div>
            </div>
            <div class="row mb-2">
                <div class="col-3 text-muted">Status</div>
                <div class="col-9">: <span class="badge {{ $badgeStatus }}">{{ $labelStatus }}</span></div>
            </div>
            <div class="row mb-2">
                <div class="col-3 text-muted">Sumber</div>
                <div class="col-9">: {{ ucfirst($absensi->sumber) }}</div>
            </div>
            @if ($absensi->latitude !== null && $absensi->longitude !== null)
                <div class="row mb-2">
                    <div class="col-3 text-muted">Lokasi GPS</div>
                    <div class="col-9">:
                        {{ number_format($absensi->latitude, 7, ',', '.') }},
                        {{ number_format($absensi->longitude, 7, ',', '.') }}
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-3 text-muted">Jarak dari kantor</div>
                    <div class="col-9">:
                        {{ $absensi->jarak_lokasi !== null ? number_format($absensi->jarak_lokasi, 0, ',', '.') . ' m' : '-' }}
                        @if ($absensi->di_luar_kantor)
                            <span class="badge bg-warning text-dark">Terdeteksi di luar kantor</span>
                        @else
                            <span class="badge bg-success">Dalam area kantor</span>
                        @endif
                    </div>
                </div>
            @endif
            <div class="row">
                <div class="col-3 text-muted">Keterangan</div>
                <div class="col-9">: {{ $absensi->keterangan ?: '-' }}</div>
            </div>
        </div>
    </div>
@endsection
