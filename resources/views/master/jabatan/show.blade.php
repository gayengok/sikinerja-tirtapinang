@extends('layouts.app')
@section('title', 'Detail Jabatan')
@section('content')
<div class="mj-page mj-detail-page">
    <header class="mj-heading"><div><nav class="mj-breadcrumb"><a href="{{ route('master.dashboard') }}">Dashboard</a><i class="fa-solid fa-chevron-right"></i><a href="{{ route('master.jabatan.index') }}">Jabatan</a><i class="fa-solid fa-chevron-right"></i><span>Detail</span></nav><h1>Detail Jabatan</h1><p>Informasi jabatan dan pencapaian KPI pegawai.</p></div><div class="mj-heading-actions"><a class="mj-btn mj-btn-outline" href="{{ route('master.jabatan.index') }}"><i class="fa-solid fa-arrow-left"></i> Kembali</a><a class="mj-btn mj-btn-primary" href="{{ route('master.jabatan.edit', $jabatan) }}"><i class="fa-solid fa-pen-to-square"></i> Edit</a></div></header>
    <section class="mj-panel mj-detail-panel"><div class="mj-panel-header"><span class="mj-form-icon"><i class="fa-solid fa-briefcase"></i></span><div><h2>Informasi Jabatan</h2><p>Data posisi dalam struktur organisasi.</p></div></div>
        <div class="mj-detail-body"><div class="mj-detail-grid"><div class="mj-detail-field"><small>Nama Jabatan</small><strong>{{ $jabatan->nama_jabatan }}</strong></div><div class="mj-detail-field"><small>Divisi / Unit Kerja</small><strong>{{ $jabatan->divisi?->nama_divisi ?? '-' }}</strong></div></div>
        <div class="mj-stats mj-stats-three"><div class="mj-stat"><span class="mj-stat-icon"><i class="fa-solid fa-ranking-star"></i></span><div><small>Grade</small><strong>{{ $jabatan->grade ?: '-' }}</strong></div></div><div class="mj-stat"><span class="mj-stat-icon mj-stat-icon-teal"><i class="fa-solid fa-wallet"></i></span><div><small>Base Tukin</small><strong class="mj-stat-money">Rp {{ number_format($jabatan->base_tukin, 0, ',', '.') }}</strong></div></div><div class="mj-stat"><span class="mj-stat-icon"><i class="fa-solid fa-users"></i></span><div><small>Pegawai</small><strong>{{ $jabatan->pegawais->count() }}</strong></div></div></div></div>
    </section>
    <section class="mj-panel mj-chart-panel"><div class="mj-panel-header"><div><h2>Tren Performa KPI</h2><p>Rata-rata pencapaian pada empat periode terakhir.</p></div><span class="mj-chip">{{ $performanceAverage !== null ? number_format($performanceAverage, 1, ',', '.') . '% rata-rata' : 'Belum ada data' }}</span></div>
        @if($performanceData->isNotEmpty())<div class="mj-chart-body"><div class="mj-chart-container"><canvas id="jabatanPerformanceChart" role="img" aria-label="Grafik tren rata-rata performa KPI per periode"></canvas></div><div class="mj-performance-summary"><span>Pencapaian periode terakhir</span><strong>{{ number_format($currentPerformance, 1, ',', '.') }}%</strong></div></div>
        @else<div class="mj-empty"><i class="fa-solid fa-chart-column"></i><p>Belum ada data penilaian KPI untuk jabatan ini.</p></div>@endif
    </section>
</div>
@endsection
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/master/master-jabatan.css') }}?v={{ filemtime(public_path('css/master/master-jabatan.css')) }}">
@endpush
@if($performanceData->isNotEmpty())
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.8/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const canvas = document.getElementById('jabatanPerformanceChart');
    if (!canvas || typeof Chart === 'undefined') return;
    const labels = @json($performanceData->pluck('label')->values());
    const values = @json($performanceData->pluck('average')->values());
    new Chart(canvas, {
        type: 'bar',
        data: { labels: labels, datasets: [{ label: 'Rata-rata KPI (%)', data: values, backgroundColor: '#078dbf', hoverBackgroundColor: '#086e9b', borderRadius: 9, maxBarThickness: 58 }] },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { callbacks: { label: function (ctx) { return 'Rata-rata: ' + ctx.parsed.y.toLocaleString('id-ID') + '%'; } } } }, scales: { y: { beginAtZero: true, suggestedMax: 100, ticks: { callback: function (value) { return value + '%'; } }, grid: { color: '#e8eef5' } }, x: { grid: { display: false } } } }
    });
});
</script>
@endpush
@endif
