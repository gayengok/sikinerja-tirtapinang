@extends('layouts.app')

@section('title', 'Pegawai Bawahan')

@section('content')
    <div class="atasan-team-page">
        <div class="atasan-mobile-breadcrumb">
            <a href="{{ route('atasan.dashboard') }}" aria-label="Kembali ke dashboard"><i
                    class="fa-solid fa-arrow-left"></i></a>
            <div><span>Manajemen Tim</span><strong>Pegawai Bawahan</strong></div>
        </div>


        {{-- <div class="atasan-team-heading">
            <div>
                <h1>Pegawai Bawahan</h1>
                <p>Daftar pegawai yang berada di bawah tanggung jawab dan penilaian Anda.</p>
            </div>
            <span>{{ $pegawais->total() }} Pegawai<br>Terdaftar</span>
        </div> --}}

        @php
            $totalBawahan = $pegawais->total();
            $skorTampilan = $skorTim !== null ? round($skorTim, 1) : 0;
            $periodeLabel = $periodeAktif?->tanggal_mulai
                ? \Carbon\Carbon::parse($periodeAktif->tanggal_mulai)->translatedFormat('F Y')
                : 'Belum ada periode';
        @endphp



        <section class="atasan-team-summary">
            <div class="atasan-team-summary-heading">
                <span><i class="fa-solid fa-circle"></i> TIM PENILAIAN ATASAN</span>
                <b><i class="fa-regular fa-calendar"></i> {{ $periodeLabel }}</b>
            </div>
            <div class="atasan-team-summary-main">
                <div class="atasan-team-ring" style="--progress: {{ $skorTampilan * 3.6 }}deg">
                    <strong>{{ $skorTim !== null ? number_format($skorTampilan, 0) : '-' }}%</strong>
                    <small>SKOR</small>
                </div>
                <div>
                    <h2>{{ $skorTim !== null && $skorTampilan >= 90 ? 'Kinerja Tim Optimal' : 'Kinerja Tim Berjalan' }}</h2>
                    <p>{{ $totalBawahan > 0 ? 'Seluruh target KPI & verifikasi laporan dipantau.' : 'Belum ada pegawai bawahan yang terdaftar.' }}
                    </p>
                </div>
                <span
                    class="atasan-team-grade">{{ $skorTim !== null ? ($skorTampilan >= 90 ? 'A+ Sangat Baik' : ($skorTampilan >= 75 ? 'B Baik' : 'Perlu Evaluasi')) : '-' }}</span>
            </div>
            <div class="atasan-team-trend"><span><i class="fa-solid fa-circle"></i> Tren Capaian 30
                    Hari</span><strong>{{ $skorTim !== null ? 'Stabil' : 'Belum tersedia' }}</strong><b>+0%</b></div>
            <div class="atasan-team-summary-stats">
                <strong><i class="fa-solid fa-user-group"></i>{{ $bawahanAktif }}<small>Bawahan Aktif</small></strong>
                <strong><i
                        class="fa-regular fa-circle-check"></i>{{ $skorTim !== null ? number_format($skorTampilan, 0) . '%' : '-' }}<small>Kinerja
                        Tim</small></strong>
                <strong><i class="fa-regular fa-clock"></i>{{ $laporanTertunda }}<small>Laporan Tertunda</small></strong>
            </div>
        </section>

        <div class="atasan-team-search"><i class="fa-solid fa-magnifying-glass"></i><input type="search"
                placeholder="Cari nama, NIP, atau jabatan pegawai..."></div>
        <div class="atasan-team-tabs"><span class="active">Semua ({{ $pegawais->total() }})</span><span>Aktif
                ({{ $pegawais->where('status', 'aktif')->count() }})</span><span>Perlu Penilaian (0)</span><span>Cuti /
                Izin</span></div>
        <div class="atasan-team-list-heading">
            <h2>Daftar Anggota Tim</h2><span>Urutan Terkini</span>
        </div>

        <div class="atasan-team-table-wrap">
            <table class="atasan-team-table">
                <thead>
                    <tr>
                        <th scope="col">No</th>
                        <th scope="col">Pegawai</th>
                        <th scope="col">Kontak &amp; Email</th>
                        <th scope="col">Unit / Divisi</th>
                        <th scope="col">Status Kepegawaian</th>
                        <th scope="col" class="atasan-team-table-actions-heading">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pegawais as $pegawai)
                        <tr>
                            <td><span
                                    class="atasan-team-table-number">{{ $loop->iteration + ($pegawais->currentPage() - 1) * $pegawais->perPage() }}</span>
                            </td>
                            <td>
                                <div class="atasan-team-table-person">
                                    <span
                                        class="atasan-team-table-avatar">{{ strtoupper(substr($pegawai->nama, 0, 2)) }}</span>
                                    <span><strong>{{ $pegawai->nama }}</strong><small>{{ $pegawai->jabatan->nama_jabatan ?? '-' }}</small></span>
                                </div>
                            </td>
                            <td><span class="atasan-team-table-email"><i
                                        class="fa-regular fa-envelope"></i>{{ $pegawai->user->email ?? '-' }}</span></td>
                            <td><span class="atasan-team-table-division"><i
                                        class="fa-regular fa-building"></i>{{ $pegawai->jabatan->divisi->nama_divisi ?? '-' }}</span>
                            </td>
                            <td><span
                                    class="atasan-team-table-status {{ $pegawai->status === 'aktif' ? 'is-active' : '' }}"><i
                                        class="fa-solid fa-circle"></i>{{ ucfirst($pegawai->status) }}</span></td>
                            <td>
                                <div class="atasan-team-table-actions">
                                    <a href="{{ route('atasan.pegawai.show', $pegawai) }}"><i
                                            class="fa-regular fa-clipboard"></i> Riwayat</a>
                                    <a href="{{ route('atasan.kompetensi.edit', $pegawai) }}" class="primary">Beri Nilai <i
                                            class="fa-solid fa-chevron-right"></i></a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="atasan-team-table-empty" colspan="6">Anda belum memiliki pegawai bawahan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="atasan-team-list">
            @forelse ($pegawais as $pegawai)
                <article class="atasan-employee-card">
                    <div class="atasan-employee-top">
                        <span class="atasan-employee-avatar">{{ strtoupper(substr($pegawai->nama, 0, 2)) }}</span>
                        <div>
                            <h3>{{ $pegawai->nama }} <em
                                    class="{{ $pegawai->status === 'aktif' ? 'is-active' : '' }}">{{ ucfirst($pegawai->status) }}</em>
                            </h3>
                            <p>{{ $pegawai->jabatan->nama_jabatan ?? '-' }}</p>
                        </div>
                    </div>



                    <div class="atasan-employee-meta"><span><i class="fa-regular fa-envelope"></i>
                            Email</span><strong>{{ $pegawai->user->email ?? '-' }}</strong></div>
                    <div class="atasan-employee-meta"><span><i class="fa-regular fa-building"></i> Unit /
                            Divisi</span><strong>{{ $pegawai->jabatan->divisi->nama_divisi ?? '-' }}</strong></div>
                    <div class="atasan-employee-meta"><span><i class="fa-regular fa-circle-check"></i> Status
                            Kepegawaian</span><strong class="approval-ok">{{ ucfirst($pegawai->status) }}</strong></div>

                    <div class="atasan-employee-actions"><a href="{{ route('atasan.pegawai.show', $pegawai) }}"
                            class="secondary"><i class="fa-regular fa-clipboard"></i> Riwayat Laporan</a>
                        <a href="{{ route('atasan.kompetensi.edit', $pegawai) }}" class="primary">Beri Nilai <i
                                class="fa-solid fa-chevron-right"></i></a>
                    </div>
                </article>

            @empty
                <div class="atasan-team-empty">Anda belum memiliki pegawai bawahan.</div>
            @endforelse
        </div>


        <div class="atasan-team-info"><i class="fa-solid fa-circle-info"></i>
            <div><strong>Informasi Manajemen Staf</strong><span>Bawahan otomatis terdaftar sesuai struktur organisasi dan
                    penempatan unit kerja.</span></div>
        </div>
        <div class="atasan-team-pagination">{{ $pegawais->links('pagination::bootstrap-5') }}</div>
    </div>


@endsection



@push('styles')
    <link rel="stylesheet" href="{{ asset('css/atasan/pegawai/index.css') }}">
@endpush
