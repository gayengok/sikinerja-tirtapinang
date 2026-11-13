@extends('layouts.app')

@section('title', auth()->user()->role === 'kepala_bagian' ? 'Approval Laporan Tim' : 'Approval Laporan Harian')

@section('content')
    <div class="atasan-approval-page">
        <div class="atasan-mobile-breadcrumb">
            <a href="{{ route($routePrefix . '.dashboard') }}" aria-label="Kembali ke dashboard"><i
                    class="fa-solid fa-arrow-left"></i></a>
            <div><span>{{ auth()->user()->role === 'kepala_bagian' ? 'Kepala Bagian' : 'Atasan' }}</span><strong>Approval Laporan</strong></div>
        </div>
        @php
            $waitingCount = (int) ($statusCounts['diajukan'] ?? 0);
            $approvedCount = (int) ($statusCounts['disetujui'] ?? 0);
            $rejectedCount = (int) ($statusCounts['ditolak'] ?? 0);
            $totalReports = $waitingCount + $approvedCount + $rejectedCount + (int) ($statusCounts['draft'] ?? 0);
            $processedCount = $approvedCount + $rejectedCount;
            $efficiency = $totalReports > 0 ? round(($processedCount / $totalReports) * 100) : 0;
            $days = collect(range(0, 6))->map(fn($daysAgo) => \Carbon\Carbon::today()->subDays(6 - $daysAgo));
            $maxActivity = max(1, (int) $activityCounts->max());
        @endphp

        <section class="atasan-approval-summary">
            <div class="atasan-approval-summary-heading">
                <div>
                    <h1>Statistik & Tren Approval</h1>
                    <p>Seluruh periode • Aktivitas laporan 7 hari terakhir</p>
                </div>
                <span><i class="fa-solid fa-circle"></i> {{ $efficiency }}% Efektif</span>
            </div>

            <div class="atasan-approval-summary-progress">
                <div class="atasan-approval-ring" style="--progress: {{ $efficiency * 3.6 }}deg">
                    <strong>{{ $efficiency }}%</strong>
                    <small>Selesai</small>
                </div>
                <div class="atasan-approval-breakdown">
                    <div><i></i><span>Terproses</span><b>{{ $processedCount }} laporan</b></div>
                    <div><i class="pending"></i><span>Antrean Pending</span><b>{{ $waitingCount }}</b></div>
                </div>
            </div>

            <div class="atasan-approval-activity">
                <div class="atasan-approval-activity-heading">
                    <strong>AKTIVITAS 7 HARI TERAKHIR</strong>
                    <span>Bebas Tunggakan</span>
                </div>
                <div class="atasan-approval-bars">
                    @foreach ($days as $day)
                        @php
                            $activityTotal = (int) ($activityCounts[$day->toDateString()] ?? 0);
                        @endphp
                        <div class="{{ $day->isToday() ? 'is-today' : '' }} {{ $activityTotal > 0 ? 'has-activity' : '' }}">
                            <span style="height: {{ max(5, round(($activityTotal / $maxActivity) * 42)) }}px"></span>
                            <small>{{ $day->translatedFormat('D') }}</small>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="atasan-approval-statuses">
                <div><small>Menunggu</small><strong>{{ $waitingCount }}</strong></div>
                <div><small>Disetujui</small><strong>{{ $approvedCount }}</strong></div>
                <div><small>Ditolak</small><strong>{{ $rejectedCount }}</strong></div>
            </div>
        </section>

        <div class="atasan-approval-tabs">
            @foreach (['diajukan' => 'Diajukan', 'disetujui' => 'Disetujui', 'ditolak' => 'Ditolak', 'draft' => 'Draft'] as $key => $label)
                <a class="{{ $statusAktif === $key ? 'active' : '' }}"
                    href="{{ route($routePrefix . '.laporan.index', array_merge(request()->query(), ['status' => $key])) }}">{{ $label }}
                    <span>{{ (int) ($statusCounts[$key] ?? 0) }}</span></a>
            @endforeach
        </div>

        <form class="atasan-approval-search" action="{{ route($routePrefix . '.laporan.index') }}" method="GET">
            <input type="hidden" name="status" value="{{ $statusAktif }}">
            <label class="atasan-approval-date">
                <i class="fa-regular fa-calendar" aria-hidden="true"></i>
                <input type="month" name="bulan" value="{{ $bulanAktif }}" aria-label="Pilih bulan laporan">
            </label>
            <label class="atasan-approval-search-field">
                <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                <input type="search" name="q" value="{{ $filters['q'] ?? '' }}"
                    placeholder="Cari pegawai, jabatan, atau tugas..." aria-label="Cari laporan">
            </label>
            <button type="submit">Terapkan</button>
            @if (!empty($filters['q']) || $bulanAktif !== now()->format('Y-m'))
                <a class="atasan-approval-clear"
                    href="{{ route($routePrefix . '.laporan.index', ['status' => $statusAktif]) }}">Reset</a>
            @endif
        </form>
        <div class="atasan-approval-list-heading">
            <h2>Laporan {{ $bulanLabel }}</h2><span>{{ $laporans->total() }} laporan ditemukan</span>
        </div>

        <div class="atasan-approval-list">
            @forelse ($laporans as $laporan)
                <article class="atasan-report-card">
                    <div class="atasan-report-top"><span
                            class="atasan-report-avatar">{{ strtoupper(substr($laporan->pegawai->nama, 0, 2)) }}</span>
                        <div>
                            <h3>{{ $laporan->pegawai->nama }}</h3>
                            <p>{{ $laporan->pegawai->jabatan->divisi->nama_divisi ?? '-' }}</p>
                        </div><span
                            class="atasan-report-status status-{{ $laporan->status }}">{{ ucfirst($laporan->status) }}</span>
                    </div>
                    <div class="atasan-report-body">
                        <div><span>Tanggal
                                Kegiatan</span><strong>{{ \Carbon\Carbon::parse($laporan->tanggal)->translatedFormat('d F Y') }}</strong>
                        </div>
                        <div><span>Jabatan</span><strong>{{ $laporan->pegawai->jabatan->nama_jabatan ?? '-' }}</strong>
                        </div>
                        <p><b>Uraian:</b>
                            {{ \Illuminate\Support\Str::limit($laporan->catatan ?: 'Laporan kegiatan harian pegawai.', 150) }}
                        </p>
                    </div>
                    <div class="atasan-report-footer"><a href="{{ route($routePrefix . '.laporan.show', $laporan) }}">Detail <i
                                class="fa-solid fa-chevron-right"></i></a>
                        @if ($laporan->status === 'diajukan')
                            <form action="{{ route($routePrefix . '.laporan.approve', $laporan) }}" method="POST"
                                onsubmit="return confirm('Apakah Anda yakin ingin menyetujui laporan ini?');">@csrf<button
                                    type="submit"><i class="fa-solid fa-check"></i> Setujui</button></form><a
                                class="reject" href="{{ route($routePrefix . '.laporan.show', $laporan) }}#reject">Tolak</a>
                        @endif
                    </div>
                </article>
            @empty
                <div class="atasan-approval-empty"><i class="fa-regular fa-folder-open"></i>
                    <h3>Belum Ada Laporan</h3>
                    <p>Tidak ada laporan berstatus <strong>"{{ ucfirst($statusAktif) }}"</strong>
                        pada bulan <strong>{{ $bulanLabel }}</strong>
                        @if (!empty($filters['q']))
                            yang cocok dengan filter ini.
                        @else
                            saat ini.
                        @endif
                    </p>
                    <a href="{{ route($routePrefix . '.laporan.index', ['status' => 'disetujui']) }}">Buka Laporan
                        Disetujui</a>
                </div>
            @endforelse
        </div>
        <div class="atasan-team-pagination">{{ $laporans->links('pagination::bootstrap-5') }}</div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/atasan/laporan/index.css') }}">
@endpush
