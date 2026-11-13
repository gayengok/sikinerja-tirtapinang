@extends('layouts.app')

@section('title', 'Rekap Data')

@section('content')
    @php
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
            <div>
                <span class="kepala-rekap-eyebrow"><i class="fa-solid fa-table-list"></i> LAPORAN TIM</span>
                <h1>Rekap Data Kehadiran</h1>
                <p>Ringkasan kehadiran dan tunjangan pegawai dalam tim Anda.</p>
            </div>

            <form method="GET" action="{{ route('kepala_bagian.rekap-data.index') }}" class="kepala-rekap-filter">
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
                    <input type="number" name="tahun" min="2000" max="{{ now()->year + 1 }}"
                        value="{{ $tahun }}" aria-label="Pilih tahun">
                </label>
                <button type="submit"><i class="fa-solid fa-filter"></i> Terapkan</button>
                <a class="kepala-rekap-print"
                    href="{{ route('kepala_bagian.rekap-data.pdf', request()->query()) }}"
                    aria-label="Unduh rekap PDF" title="Unduh rekap PDF">
                    <i class="fa-solid fa-download" aria-hidden="true"></i>
                </a>
            </form>
        </header>

        @unless ($periodeTukinTersedia)
            <div class="kepala-rekap-notice" role="status">
                <i class="fa-solid fa-circle-info"></i>
                Nilai tunjangan belum tersedia karena periode penilaian dengan rentang bulan ini belum dibuat.
            </div>
        @endunless

        <section class="kepala-rekap-card" aria-label="Rekap kehadiran tim">
            <div class="kepala-rekap-card-heading">
                <h2>Daftar Pegawai <span>{{ $pegawais->count() }} Personel</span></h2>
                <p><i class="fa-regular fa-calendar" aria-hidden="true"></i>
                    Periode dipilih: {{ $namaBulan[$bulan] }} {{ $tahun }}</p>
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
                            <th scope="col">No</th>
                            <th scope="col">Nama</th>
                            <th scope="col">Jabatan</th>
                            <th scope="col">Divisi</th>
                            <th scope="col">Tanggal</th>
                            <th scope="col">Jumlah Kehadiran</th>
                            <th scope="col">Tunjangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pegawais as $pegawai)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <div class="kepala-rekap-person">
                                        <span class="kepala-rekap-avatar" aria-hidden="true">
                                            {{ strtoupper(substr($pegawai->nama, 0, 1)) }}
                                        </span>
                                        <span class="kepala-rekap-person-copy">
                                            <strong>{{ $pegawai->nama }}</strong>
                                        </span>
                                    </div>
                                </td>
                                <td>{{ $pegawai->jabatan?->nama_jabatan ?? '-' }}</td>
                                <td>{{ $pegawai->jabatan?->divisi?->nama_divisi ?? '-' }}</td>
                                <td class="kepala-rekap-date">
                                    {{ $tanggalMulai->format('d-m-Y') }} s/d {{ $tanggalSelesai->format('d-m-Y') }}
                                </td>
                                <td>
                                    <span
                                        class="kepala-rekap-attendance {{ $pegawai->jumlah_kehadiran > 0 ? 'is-present' : '' }}">
                                        {{ $pegawai->jumlah_kehadiran }} hari
                                    </span>
                                </td>
                                <td class="kepala-rekap-money">
                                    <span
                                        class="kepala-rekap-tukin {{ $pegawai->tukin_neto !== null ? 'is-calculated' : '' }}">
                                        {{ $pegawai->tukin_neto !== null ? 'Rp ' . number_format((float) $pegawai->tukin_neto, 0, ',', '.') : 'Belum dihitung' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="kepala-rekap-empty">
                                    Belum ada pegawai dalam tim yang dapat ditampilkan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if ($pegawais->isNotEmpty())
                        <tfoot>
                            <tr>
                                <th colspan="5" scope="row">Total</th>
                                <th>{{ $totalKehadiran }} hari</th>
                                <th class="kepala-rekap-money">
                                    {{ $tukinTersedia ? 'Rp ' . number_format($totalTukin, 0, ',', '.') : 'Belum dihitung' }}
                                </th>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
            <div class="kepala-rekap-card-footer">
                <span>Menampilkan {{ $pegawais->count() }} data pegawai</span>
                <span>Periode tunjangan:
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
