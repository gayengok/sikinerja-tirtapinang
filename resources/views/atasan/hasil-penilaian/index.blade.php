@extends('layouts.app')
@section('title', 'Hasil Penilaian')

@section('content')
    @php
        $items = $hasilPenilaians->getCollection();
        $avg = $items->avg('nilai_akhir');
        $selesai = $items->filter(fn($hasil) => $hasil->exists && $hasil->nilai_akhir !== null)->count();
    @endphp
    <div class="hasil-page">
        <div class="atasan-mobile-breadcrumb">
            <a href="{{ route('atasan.dashboard') }}" aria-label="Kembali ke dashboard"><i
                    class="fa-solid fa-arrow-left"></i></a>
            <div><span>Atasan</span><strong>Hasil Penilaian</strong></div>
        </div>


        @php
            $totalPegawai = $hasilPenilaians->total();
            $tuntasPercent = $totalPegawai > 0 ? round(($selesai / $totalPegawai) * 100) : 0;
            $predikatRata =
                $avg === null
                    ? 'Belum tersedia'
                    : ($avg >= 90
                        ? 'Sangat Baik'
                        : ($avg >= 75
                            ? 'Baik'
                            : 'Perlu Perbaikan'));
        @endphp

        <section class="hasil-performance-card">
            <div class="hasil-performance-heading">
                <div>
                    <h1>Capaian Kinerja Eksekutif</h1>
                    <p>{{ $periode->nama_periode ?? 'Agregat Tim & Evaluasi Berkala' }}</p>
                </div>
                <span><i class="fa-solid fa-circle"></i> {{ $predikatRata }}</span>
            </div>

            <div class="hasil-performance-main">
                <div>
                    <small>RATA-RATA NILAI AKHIR</small>
                    <strong>{{ $avg !== null ? number_format($avg, 2, ',', '.') : '-' }} <em>/ 100 pt</em></strong>
                    <b><i class="fa-solid fa-arrow-up"></i> {{ $avg !== null ? $predikatRata : 'Belum dinilai' }}</b>
                </div>
                <div class="hasil-performance-ring" style="--progress: {{ min(100, max(0, $avg ?? 0)) * 3.6 }}deg">
                    <strong>{{ $avg !== null ? number_format($avg, 1, ',', '.') : '-' }}%</strong>
                    <small>Indeks</small>
                </div>
            </div>

            <div class="hasil-performance-trend">
                <span><i class="fa-solid fa-circle"></i> Periode Penilaian</span>
                <strong>{{ $periode->nama_periode ?? 'Belum tersedia' }}</strong>
            </div>
        </section>

        <div class="hasil-summary-cards">
            <article>
                <div><small>TUNTAS DINILAI</small><i class="fa-solid fa-check"></i></div>
                <strong>{{ $selesai }}<em>/ {{ $totalPegawai }} Pegawai</em></strong>
                <div class="hasil-summary-progress"><b style="width: {{ $tuntasPercent }}%"></b></div>
                <small class="summary-status">{{ $tuntasPercent }}% Selesai</small>
            </article>
            <article>
                <div><small>RERATA TUKIN</small><i class="fa-solid fa-sack-dollar"></i></div>
                <strong>Rp {{ number_format($items->avg('tukin_bruto') ?? 0, 0, ',', '.') }}</strong>
                <b>Bruto Resmi</b><small class="summary-status">Grade Golongan</small>
            </article>
        </div>



        <div class="hasil-search"><i class="fa-solid fa-magnifying-glass"></i><input type="search"
                placeholder="Cari nama pegawai atau predikat..."><i class="fa-solid fa-filter"></i></div>
        <div class="hasil-tabs"><span class="active">Semua ({{ $hasilPenilaians->total() }})</span><span>Sangat Baik
                ({{ $items->where('predikat', 'Sangat Baik')->count() }})</span><span>Baik
                ({{ $items->where('predikat', 'Baik')->count() }})</span><span>Butuh Perbaikan</span></div>
        <div class="hasil-list-title">
            <h2>Daftar Penilaian Pegawai ({{ $hasilPenilaians->total() }})</h2>
        </div>

        <div class="hasil-table-wrap">
            <table class="hasil-table">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Pegawai</th>
                        <th scope="col">Predikat</th>
                        <th scope="col">KPI Otomatis</th>
                        <th scope="col">Realisasi Laporan</th>
                        <th scope="col">Nilai Akhir</th>
                        <th scope="col">Tukin Bruto</th>
                        <th scope="col" class="hasil-table-action-heading">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($hasilPenilaians as $hasil)
                        <tr>
                            <td><span
                                    class="hasil-table-number">{{ $loop->iteration + ($hasilPenilaians->currentPage() - 1) * $hasilPenilaians->perPage() }}</span>
                            </td>
                            <td>
                                <div class="hasil-table-person">
                                    <span
                                        class="hasil-table-avatar">{{ strtoupper(substr($hasil->pegawai->nama, 0, 2)) }}</span>
                                    <span><strong>{{ $hasil->pegawai->nama }}</strong><small>{{ $hasil->pegawai->jabatan->nama_jabatan ?? '-' }}</small></span>
                                </div>
                            </td>
                            <td><span class="hasil-table-predicate">{{ $hasil->predikat ?? 'Belum dinilai' }}</span></td>
                            <td><strong class="hasil-table-score">{{ $hasil->rata_skor_kpi ?? '-' }}</strong><small
                                    class="hasil-table-hint">Dari laporan disetujui</small></td>
                            <td><strong class="hasil-table-auto">Otomatis</strong><small class="hasil-table-hint">Tanpa
                                    input atasan</small></td>
                            <td><strong class="hasil-table-score">{{ $hasil->nilai_akhir ?? '-' }}</strong><small
                                    class="hasil-table-hint">Skor KPI</small></td>
                            <td><strong class="hasil-table-money">Rp
                                    {{ number_format($hasil->tukin_bruto ?? 0, 0, ',', '.') }}</strong><small
                                    class="hasil-table-draft">{{ !$hasil->exists ? 'Belum dinilai' : ($hasil->status === 'dikirim' ? 'Dikirim' : 'Draft') }}</small>
                            </td>
                            <td><a class="hasil-table-detail"
                                    href="{{ route('atasan.hasil-penilaian.show', ['pegawai' => $hasil->pegawai, 'periode_id' => $periode?->id]) }}">Detail
                                    <i class="fa-solid fa-chevron-right"></i></a></td>
                        </tr>
                    @empty
                        <tr>
                            <td class="hasil-table-empty" colspan="8">Belum ada hasil penilaian untuk periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="hasil-list">
            @forelse($hasilPenilaians as $hasil)
                <article class="hasil-card">
                    <div class="hasil-person"><span
                            class="hasil-avatar">{{ strtoupper(substr($hasil->pegawai->nama, 0, 2)) }}</span>
                        <div>
                            <h3>{{ $hasil->pegawai->nama }}
                                <small>#{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</small>
                            </h3>

                            <p>{{ $hasil->pegawai->jabatan->nama_jabatan ?? '-' }}</p>
                        </div><b class="hasil-predikat">{{ $hasil->predikat ?? 'Belum dinilai' }}</b>
                    </div>
                    <div class="hasil-values">
                        <div><small>KPI OTOMATIS</small><strong>{{ $hasil->rata_skor_kpi ?? '-' }}</strong><span>Dari
                                laporan disetujui</span></div>
                        <div><small>REALISASI LAPORAN</small><strong>Otomatis</strong><span>Tanpa input atasan</span></div>
                        <div><small>NILAI AKHIR</small><strong>{{ $hasil->nilai_akhir ?? '-' }}</strong><span>Skor
                                KPI</span></div>
                    </div>
                    <div class="hasil-card-footer">
                        <div><small>TUKIN BRUTO</small><strong>Rp
                                {{ number_format($hasil->tukin_bruto ?? 0, 0, ',', '.') }}</strong><span>{{ !$hasil->exists ? 'Belum dinilai' : ($hasil->status === 'dikirim' ? 'Dikirim' : 'Draft') }}</span>
                        </div><a
                            href="{{ route('atasan.hasil-penilaian.show', ['pegawai' => $hasil->pegawai, 'periode_id' => $periode?->id]) }}">Detail
                            <i class="fa-solid fa-chevron-right"></i></a>
                    </div>
                </article>
            @empty <div class="hasil-empty"><i class="fa-regular fa-folder-open"></i><strong>Belum ada hasil
                        penilaian</strong><span>Data untuk periode ini belum tersedia.</span></div>
            @endforelse
        </div>
        <div class="hasil-pagination">{{ $hasilPenilaians->links('pagination::bootstrap-5') }}</div>
        <aside class="hasil-note"><i class="fa-solid fa-circle-info"></i>
            <div><strong>Catatan Tukin & Evaluasi</strong>
                <p>Perhitungan otomatis berdasarkan capaian KPI dari laporan pegawai yang telah disetujui. Atasan tidak
                    mengisi nilai capaian. KPI atau kompetensi yang belum memiliki nilai dihitung 0 pada hasil sementara.
                </p>
            </div>
        </aside>
    </div>
@endsection



@push('styles')
    <link rel="stylesheet" href="{{ asset('css/atasan/hasil-penilaian/index.css') }}">
@endpush
