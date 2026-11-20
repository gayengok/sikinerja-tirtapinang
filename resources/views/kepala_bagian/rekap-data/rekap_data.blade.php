
@extends('layouts.app')

@section('title', 'Rekap Data')

@section('content')
    @php
        $routePrefix = auth()->user()->role === 'direktur' ? 'direktur' : 'kepala_bagian';

        $namaBulan = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];
    @endphp

    <main class="kepala-rekap">
        <header class="kepala-rekap-heading">
            <div class="kepala-rekap-heading-copy">
                <span class="kepala-rekap-eyebrow">
                    <i class="fa-solid fa-table-list"></i>
                    LAPORAN TIM
                </span>

                <h1>Rekap Data Kehadiran</h1>

                <p>Ringkasan kehadiran dan tunjangan pegawai dalam tim Anda.</p>
            </div>

            <form method="GET"
                action="{{ route($routePrefix . '.rekap-data.index') }}"
                class="kepala-rekap-filter">

                <label>
                    <span>Bulan</span>

                    <select name="bulan" aria-label="Pilih bulan">
                        @foreach ($namaBulan as $nomorBulan => $labelBulan)
                            <option value="{{ $nomorBulan }}" @selected($bulan === $nomorBulan)>
                                {{ $labelBulan }}
                            </option>
                        @endforeach
                    </select>
                </label>

                <label>
                    <span>Tahun</span>

                    <input type="number"
                        name="tahun"
                        min="2000"
                        max="{{ now()->year + 1 }}"
                        value="{{ $tahun }}"
                        aria-label="Pilih tahun">
                </label>

                <button type="submit" class="kepala-rekap-apply">
                    <i class="fa-solid fa-filter"></i>
                    <span>Terapkan</span>
                </button>

                <a class="kepala-rekap-print"
                    href="{{ route($routePrefix . '.rekap-data.pdf', ['bulan' => $bulan, 'tahun' => $tahun]) }}"
                    aria-label="Unduh rekap PDF"
                    title="Unduh rekap PDF">
                    <i class="fa-solid fa-download" aria-hidden="true"></i>
                </a>
            </form>
        </header>

        @unless ($periodeTukinTersedia)
            <div class="kepala-rekap-notice" role="status">
                <span class="kepala-rekap-notice-icon">
                    <i class="fa-solid fa-circle-info"></i>
                </span>

                <div>
                    <strong>Informasi Periode Tunjangan</strong>
                    <p>Nilai tunjangan belum tersedia karena periode penilaian dengan rentang bulan ini belum dibuat.</p>
                </div>
            </div>
        @endunless

        <section class="kepala-rekap-card" aria-label="Rekap kehadiran tim">
            <div class="kepala-rekap-card-heading">
                <div class="kepala-rekap-card-title">
                    <span class="kepala-rekap-card-eyebrow">
                        <i class="fa-solid fa-users"></i>
                        DATA PEGAWAI
                    </span>

                    <h2>
                        Daftar Pegawai
                        <span>{{ $pegawais->count() }} Personel</span>
                    </h2>

                    <p>Daftar kehadiran dan tunjangan pegawai pada periode yang dipilih.</p>
                </div>

                <div class="kepala-rekap-period">
                    <span class="kepala-rekap-period-icon">
                        <i class="fa-regular fa-calendar"></i>
                    </span>

                    <div>
                        <small>PERIODE REKAP</small>
                        <strong>{{ $namaBulan[$bulan] }} {{ $tahun }}</strong>
                    </div>
                </div>
            </div>

            <div class="kepala-rekap-table-wrap">
                <table class="kepala-rekap-table">
                    <colgroup>
                        <col class="kepala-rekap-col-number">
                        <col class="kepala-rekap-col-name">
                        <col class="kepala-rekap-col-position">
                        <col class="kepala-rekap-col-division">
                        <col class="kepala-rekap-col-date">
                        <col class="kepala-rekap-col-attendance">
                        <col class="kepala-rekap-col-tukin">
                    </colgroup>

                    <thead>
                        <tr>
                            <th scope="col">NO</th>
                            <th scope="col">PEGAWAI</th>
                            <th scope="col">JABATAN</th>
                            <th scope="col">DIVISI / BAGIAN</th>
                            <th scope="col">PERIODE</th>
                            <th scope="col">KEHADIRAN</th>
                            <th scope="col">TUNJANGAN</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($pegawais as $pegawai)
                            <tr class="kepala-rekap-data-row">
                                <td data-label="No" class="kepala-rekap-number-cell">
                                    <span class="kepala-rekap-number">
                                        {{ $loop->iteration }}
                                    </span>
                                </td>

                                <td data-label="Pegawai" class="kepala-rekap-person-cell">
                                    <div class="kepala-rekap-person">
                                        <span class="kepala-rekap-avatar" aria-hidden="true">
                                            {{ strtoupper(substr($pegawai->nama, 0, 1)) }}
                                        </span>

                                        <span class="kepala-rekap-person-copy">
                                            <strong>{{ $pegawai->nama }}</strong>
                                            <small>Anggota Tim</small>
                                        </span>
                                    </div>
                                </td>

                                <td data-label="Jabatan">
                                    <span class="kepala-rekap-cell-value">
                                        {{ $pegawai->jabatan?->nama_jabatan ?? '-' }}
                                    </span>
                                </td>

                                <td data-label="Divisi / Bagian">
                                    <span class="kepala-rekap-cell-value">
                                        {{ $pegawai->jabatan?->divisi?->nama_divisi ?? '-' }}
                                    </span>
                                </td>

                                <td data-label="Periode" class="kepala-rekap-date">
                                    <div class="kepala-rekap-date-value">
                                        <span>{{ $tanggalMulai->format('d/m/Y') }}</span>
                                        <small>sampai</small>
                                        <span>{{ $tanggalSelesai->format('d/m/Y') }}</span>
                                    </div>
                                </td>

                                <td data-label="Kehadiran" class="kepala-rekap-attendance-cell">
                                    <span class="kepala-rekap-attendance {{ $pegawai->jumlah_kehadiran > 0 ? 'is-present' : '' }}">
                                        <i class="fa-solid fa-calendar-check"></i>
                                        {{ $pegawai->jumlah_kehadiran }} hari
                                    </span>
                                </td>

                                <td data-label="Tunjangan" class="kepala-rekap-money">
                                    <span class="kepala-rekap-tukin {{ $pegawai->tukin_neto !== null ? 'is-calculated' : '' }}">
                                        @if ($pegawai->tukin_neto !== null)
                                            Rp {{ number_format((float) $pegawai->tukin_neto, 0, ',', '.') }}
                                        @else
                                            Belum dihitung
                                        @endif
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr class="kepala-rekap-empty-row">
                                <td colspan="7" class="kepala-rekap-empty">
                                    <div class="kepala-rekap-empty-content">
                                        <span>
                                            <i class="fa-solid fa-users-slash"></i>
                                        </span>

                                        <strong>Belum Ada Data Pegawai</strong>

                                        <p>Belum ada pegawai dalam tim yang dapat ditampilkan pada halaman ini.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                    @if ($pegawais->isNotEmpty())
                        <tfoot>
                            <tr>
                                <th colspan="5" scope="row" class="kepala-rekap-total-label">
                                    <span>
                                        <i class="fa-solid fa-chart-simple"></i>
                                        TOTAL REKAP
                                    </span>
                                </th>

                                <th class="kepala-rekap-total-attendance">
                                    {{ $totalKehadiran }} hari
                                </th>

                                <th class="kepala-rekap-total-money">
                                    {{ $tukinTersedia ? 'Rp ' . number_format($totalTukin, 0, ',', '.') : 'Belum dihitung' }}
                                </th>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>

            <div class="kepala-rekap-card-footer">
                <span class="kepala-rekap-footer-count">
                    <i class="fa-solid fa-users"></i>
                    Menampilkan <strong>{{ $pegawais->count() }}</strong> data pegawai
                </span>

                <span class="kepala-rekap-footer-status">
                    Periode tunjangan:

                    <strong class="{{ $tukinTersedia ? 'is-available' : 'is-pending' }}">
                        <i class="fa-solid fa-circle" aria-hidden="true"></i>
                        {{ $tukinTersedia ? 'Tersedia' : 'Belum dihitung' }}
                    </strong>
                </span>
            </div>
        </section>
    </main>
@endsection

@push('styles')
    <link href="{{ asset('css/kepala-bagian/rekap-data.css') }}" rel="stylesheet">
@endpush
