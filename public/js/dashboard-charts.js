/**
 * DASHBOARD MASTER SIKINERJA - Chart.js Initialization
 *
 * File ini membaca window.dashboardData yang disiapkan di dashboard.blade.php
 * (lihat @push('scripts') pada file blade).
 *
 * Setiap grafik hanya akan dibuat JIKA data terkait tidak null.
 * Jika Controller belum mengirim data tertentu, grafik itu otomatis
 * tidak dijalankan (Blade sudah menampilkan pesan "belum tersedia").
 */

document.addEventListener('DOMContentLoaded', function () {
    const data = window.dashboardData || {};

    const colorPrimary = '#2563eb';
    const colorSuccess = '#16a34a';
    const colorWarning = '#f59e0b';
    const colorDanger = '#dc2626';
    const colorInfo = '#0891b2';

    // ---------------------------------------------------
    // GRAFIK 1: Tren Laporan Harian (Line Chart)
    // ---------------------------------------------------
    if (data.laporanPerHari) {
        const ctx = document.getElementById('chartLaporanHarian');
        if (ctx) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.laporanPerHari.labels,
                    datasets: [{
                        label: 'Jumlah Laporan',
                        data: data.laporanPerHari.data,
                        borderColor: colorPrimary,
                        backgroundColor: 'rgba(37, 99, 235, 0.1)',
                        fill: true,
                        tension: 0.35,
                        pointRadius: 4,
                        pointBackgroundColor: colorPrimary,
                    }],
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { precision: 0 } },
                    },
                },
            });
        }
    }

    // ---------------------------------------------------
    // GRAFIK 2: Distribusi Pegawai per Divisi (Bar Chart)
    // ---------------------------------------------------
    if (data.pegawaiPerDivisi) {
        const ctx = document.getElementById('chartPegawaiPerDivisi');
        if (ctx) {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.pegawaiPerDivisi.labels,
                    datasets: [{
                        label: 'Jumlah Pegawai',
                        data: data.pegawaiPerDivisi.data,
                        backgroundColor: colorInfo,
                        borderRadius: 6,
                        maxBarThickness: 36,
                    }],
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { precision: 0 } },
                    },
                },
            });
        }
    }

    // ---------------------------------------------------
    // GRAFIK 3: Progress Penilaian KPI (Doughnut Chart)
    // ---------------------------------------------------
    if (data.statusPenilaian) {
        const ctx = document.getElementById('chartStatusPenilaian');
        if (ctx) {
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: data.statusPenilaian.labels,
                    datasets: [{
                        data: data.statusPenilaian.data,
                        backgroundColor: [colorDanger, colorWarning, colorSuccess],
                        borderWidth: 0,
                    }],
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'bottom' },
                    },
                    cutout: '65%',
                },
            });
        }
    }

    // ---------------------------------------------------
    // GRAFIK 4: Rata-rata Nilai Kinerja per Divisi (Bar Chart)
    // ---------------------------------------------------
    if (data.nilaiPerDivisi) {
        const ctx = document.getElementById('chartNilaiPerDivisi');
        if (ctx) {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.nilaiPerDivisi.labels,
                    datasets: [{
                        label: 'Rata-rata Nilai (%)',
                        data: data.nilaiPerDivisi.data,
                        backgroundColor: colorPrimary,
                        borderRadius: 6,
                        maxBarThickness: 36,
                    }],
                },
                options: {
                    responsive: true,
                    indexAxis: 'y',
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { beginAtZero: true, max: 100 },
                    },
                },
            });
        }
    }
});