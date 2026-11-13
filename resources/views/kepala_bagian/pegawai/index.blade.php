@extends('layouts.app')

@section('title', 'Pegawai dalam Tim Atasan')

@section('content')
    @php
        $statusFilter = request()->query('status', 'semua');
        $filterQuery = request()->except('status', 'page');
        $periodeSelesai = $periodeAktif?->tanggal_selesai;
        $sisaHari = $periodeSelesai ? (int) now()->startOfDay()->diffInDays($periodeSelesai->startOfDay(), false) : null;
    @endphp

    <main class="kepala-bagian-team-page">
        <section class="team-overview">
            <header class="team-heading">
                <div>
                    <span class="team-heading-eyebrow"><i class="fa-solid fa-people-group"></i> Pemantauan Tim</span>
                    <h1>Atasan &amp; Pegawai dalam Tim <span class="team-active-pill"><i class="fa-solid fa-circle"></i>
                            {{ $pegawaiAktif }} Personil Aktif</span></h1>
                    <p>Pantau data Atasan dan pegawai di bawah supervisi Anda, termasuk progres SKP serta laporan tim.</p>
                </div>
                <a class="team-back-link" href="{{ route('kepala_bagian.dashboard') }}">
                    <i class="fa-solid fa-arrow-left"></i> Dashboard
                </a>
            </header>

            <section class="team-summary" aria-label="Ringkasan tim">
                <article class="team-stat team-stat-members">
                    <div class="team-stat-heading"><span>Total Atasan &amp; Pegawai</span><i class="fa-solid fa-users"></i></div>
                    <strong>{{ $totalPegawai }} Orang</strong>
                    <small>{{ $pegawaiAktif }} personil aktif bekerja</small>
                </article>
                <article class="team-stat team-stat-skp">
                    <div class="team-stat-heading"><span>Rata-rata Capaian SKP</span><i class="fa-solid fa-chart-line"></i></div>
                    @if ($rataSkp !== null)
                        <strong>{{ number_format($rataSkp, 1) }}% <span>{{ $rataSkp >= 90 ? 'Sangat Baik' : ($rataSkp >= 80 ? 'Baik' : 'Perlu Evaluasi') }}</span></strong>
                        <div class="team-stat-progress"><span style="width: {{ min(100, max(0, $rataSkp)) }}%"></span></div>
                        <small>Periode {{ $periodeAktif?->nama_periode ?? 'terakhir tersedia' }}</small>
                    @else
                        <strong class="team-stat-unavailable">Belum tersedia</strong>
                        <small>Belum ada hasil penilaian untuk periode ini</small>
                    @endif
                </article>
                <article class="team-stat team-stat-reports">
                    <div class="team-stat-heading"><span>Logbook &amp; Laporan</span><i class="fa-solid fa-clipboard-check"></i></div>
                    @if ($persentaseLaporanDisetujui !== null)
                        <strong>{{ $persentaseLaporanDisetujui }}% <span>Tervalidasi</span></strong>
                        <small>{{ $laporanCounts['diajukan'] ?? 0 }} laporan menunggu verifikasi</small>
                    @else
                        <strong class="team-stat-unavailable">Belum ada data</strong>
                        <small>Tidak ada laporan pada periode ini</small>
                    @endif
                </article>
                <article class="team-stat team-stat-unit">
                    <div class="team-stat-heading"><span>Unit Kerja Supervisi</span><i class="fa-solid fa-chart-column"></i></div>
                    <strong>{{ $unitKerja ?: 'Belum ditentukan' }}</strong>
                    <small>{{ $unitKerja ? 'Unit di bawah supervisi Kepala Seksi' : 'Belum ada Kepala Seksi aktif' }}</small>
                </article>
            </section>
        </section>

        <section class="team-toolbar" aria-label="Pencarian dan filter anggota">
            <form method="GET" action="{{ route('kepala_bagian.pegawai.index') }}" class="team-search">
                <label for="team-search-input"><i class="fa-solid fa-magnifying-glass"></i></label>
                <input id="team-search-input" type="search" name="search" value="{{ $search }}"
                    placeholder="Cari nama pegawai atau jabatan..." aria-label="Cari nama pegawai atau jabatan">
                @if ($statusFilter !== 'semua')
                    <input type="hidden" name="status" value="{{ $statusFilter }}">
                @endif
                @if (request()->query('sort'))
                    <input type="hidden" name="sort" value="{{ request()->query('sort') }}">
                @endif
                <button type="submit">Cari</button>
            </form>

            <nav class="team-filters" aria-label="Filter status anggota tim">
                <a class="{{ $statusFilter === 'semua' ? 'is-active' : '' }}"
                    href="{{ route('kepala_bagian.pegawai.index', $filterQuery) }}">Semua ({{ $totalPegawai }})</a>
                <a class="{{ $statusFilter === 'aktif' ? 'is-active' : '' }}"
                    href="{{ route('kepala_bagian.pegawai.index', array_merge($filterQuery, ['status' => 'aktif'])) }}">Aktif ({{ $pegawaiAktif }})</a>
                <a class="{{ $statusFilter === 'review' ? 'is-active' : '' }}"
                    href="{{ route('kepala_bagian.pegawai.index', array_merge($filterQuery, ['status' => 'review'])) }}">Perlu Review ({{ $pegawaiPerluReview }})</a>
            </nav>

            <form method="GET" action="{{ route('kepala_bagian.pegawai.index') }}" class="team-sort">
                @if ($search !== '')
                    <input type="hidden" name="search" value="{{ $search }}">
                @endif
                @if ($statusFilter !== 'semua')
                    <input type="hidden" name="status" value="{{ $statusFilter }}">
                @endif
                <label for="team-sort-select">Urut:</label>
                <select id="team-sort-select" name="sort" onchange="this.form.submit()">
                    <option value="nama_az" @selected(request()->query('sort', 'nama_az') === 'nama_az')>Nama A - Z</option>
                    <option value="nama_za" @selected(request()->query('sort') === 'nama_za')>Nama Z - A</option>
                    <option value="skp_desc" @selected(request()->query('sort') === 'skp_desc')>Capaian SKP tertinggi</option>
                    <option value="laporan_desc" @selected(request()->query('sort') === 'laporan_desc')>Laporan perlu review</option>
                </select>
            </form>
        </section>

        <section class="kepala-bagian-team-list" aria-label="Daftar tim">
            @forelse ($pegawais as $pegawai)
                @php
                    $namaTampilan = $pegawai->user?->name ?: $pegawai->nama;
                    $nilaiSkp = is_numeric($pegawai->skp_score) ? (float) $pegawai->skp_score : null;
                    $statusSkp = $nilaiSkp === null
                        ? 'Belum dinilai'
                        : ($nilaiSkp >= 90 ? 'Sangat Baik' : ($nilaiSkp >= 80 ? 'Baik' : 'Perlu Evaluasi'));
                @endphp
                <article class="team-member-card">
                    <div class="team-person">
                        <div class="team-avatar">{{ strtoupper(substr($namaTampilan, 0, 1)) }}<i class="team-online-dot"></i></div>
                        <div class="team-person-copy">
                            <strong>{{ $namaTampilan }}</strong>
                            <span>{{ $pegawai->jabatan->nama_jabatan ?? 'Jabatan belum diatur' }} ·
                                {{ $pegawai->user?->role === 'atasan' ? 'Atasan' : 'Pegawai' }}</span>
                        </div>
                    </div>
                    <div class="team-member-detail">
                        <small><i class="fa-solid fa-user-tie"></i>
                            {{ $pegawai->user?->role === 'atasan' ? 'KEPALA BAGIAN' : 'KEPALA SEKSI' }}</small>
                        <strong>{{ $pegawai->atasan->nama ?? '-' }}</strong>
                        <span>{{ $pegawai->atasan?->jabatan?->nama_jabatan ?? 'Jabatan belum diatur' }}</span>
                    </div>
                    <div class="team-member-detail">
                        <small><i class="fa-regular fa-building"></i> DIVISI &amp; BAGIAN</small>
                        <strong>{{ $pegawai->jabatan->divisi->nama_divisi ?? '-' }}</strong>
                        <span>Unit kerja pegawai</span>
                    </div>
                    <div class="team-member-skp">
                        <div><span>Capaian SKP</span><strong>{{ $nilaiSkp !== null ? number_format($nilaiSkp, 0) . '%' : '-' }}</strong></div>
                        <div class="team-member-progress"><span style="width: {{ $nilaiSkp !== null ? min(100, max(0, $nilaiSkp)) : 0 }}%"></span></div>
                        <small>{{ $periodeAktif?->nama_periode ?? 'Periode berjalan' }} <b>{{ $statusSkp }}</b></small>
                    </div>
                    <div class="team-member-actions">
                        @if ($pegawai->status === 'aktif')
                            <span class="team-status is-active">Aktif</span>
                        @else
                            <span class="team-status">{{ ucfirst($pegawai->status) }}</span>
                        @endif
                        @if ($pegawai->laporan_menunggu_count > 0)
                            <span class="team-review-badge">{{ $pegawai->laporan_menunggu_count }} laporan review</span>
                        @endif
                    </div>
                </article>
            @empty
                <div class="team-empty">
                    <i class="fa-solid fa-users"></i>
                    <h2>{{ $search !== '' || $statusFilter !== 'semua' ? 'Tidak ada anggota yang cocok' : 'Belum ada anggota dalam tim' }}</h2>
                    <p>{{ $search !== '' || $statusFilter !== 'semua' ? 'Ubah kata pencarian atau filter untuk melihat anggota tim lainnya.' : 'Atasan yang ditugaskan dan pegawai di bawah mereka akan muncul di halaman ini.' }}</p>
                </div>
            @endforelse
        </section>

        <div class="team-pagination">{{ $pegawais->links() }}</div>

        <section class="team-footer-panels">
            <article class="team-guidance">
                <span><i class="fa-regular fa-circle-check"></i></span>
                <div>
                    <strong>Petunjuk Verifikasi &amp; Evaluasi Kinerja Anggota Tim</strong>
                    <p>Sebagai Kepala Bagian, Anda berwenang memvalidasi laporan harian pegawai, memantau progres SKP, dan memberikan penilaian kinerja kepada anggota tim. Tinjau data secara berkala agar target kerja dan laporan tetap akurat.</p>
                    <a href="{{ route('kepala_bagian.laporan.index') }}">Tinjau laporan anggota tim <i class="fa-solid fa-arrow-right"></i></a>
                </div>
            </article>
            <article class="team-deadline">
                <div class="team-deadline-label"><span>BATAS WAKTU VALIDASI</span>
                    @if ($periodeAktif && $sisaHari !== null)
                        <b>{{ $sisaHari >= 0 ? $sisaHari . ' Hari Lagi' : 'Periode Berakhir' }}</b>
                    @endif
                </div>
                @if ($periodeAktif)
                    <strong>Penutupan SKP {{ $periodeAktif->nama_periode }}</strong>
                    <p>Pastikan laporan tim sudah ditinjau sebelum {{ $periodeSelesai?->format('d/m/Y') }}.</p>
                    <small>Status periode: <b><i class="fa-solid fa-circle-check"></i> Berjalan</b></small>
                @else
                    <strong>Belum ada periode SKP aktif</strong>
                    <p>Jadwal batas waktu validasi akan tampil setelah periode penilaian dibuka.</p>
                    <small>Status periode: <b>Belum tersedia</b></small>
                @endif
            </article>
        </section>
    </main>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/kepala-bagian/pegawai/index.css') }}">
@endpush
