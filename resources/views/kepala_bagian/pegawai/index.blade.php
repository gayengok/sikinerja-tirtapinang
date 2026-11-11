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
    <style>
        body.role-kepala_bagian .kepala-bagian-team-page {
            display: grid;
            gap: 16px;
            width: min(1280px, 100%);
            margin: 0 auto;
            padding: 18px 20px 30px;
            color: #17213b;
        }

        body.role-kepala_bagian .team-overview {
            display: grid;
            gap: 16px;
            padding: 20px;
            background: linear-gradient(140deg, #fff 0%, #f5fbff 100%);
            border: 1px solid #dfe8f3;
            border-radius: 17px;
        }

        body.role-kepala_bagian .team-heading {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
        }

        body.role-kepala_bagian .team-heading-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #078ac8;
            font-size: .68rem;
            font-weight: 700;
        }

        body.role-kepala_bagian .team-heading h1 {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 9px;
            margin: 5px 0;
            font-size: clamp(1.2rem, 2.3vw, 1.65rem);
            font-weight: 800;
            letter-spacing: -.025em;
        }

        body.role-kepala_bagian .team-heading p {
            max-width: 720px;
            margin: 0;
            color: #64748b;
            font-size: .8rem;
            line-height: 1.55;
        }

        body.role-kepala_bagian .team-active-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 9px;
            color: #0875ad;
            background: #e0f2fe;
            border-radius: 99px;
            font-size: .61rem;
            font-weight: 700;
            letter-spacing: 0;
        }

        body.role-kepala_bagian .team-active-pill i {
            color: #10b981;
            font-size: .42rem;
        }

        body.role-kepala_bagian .team-back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 11px;
            color: #0875ad;
            background: #fff;
            border: 1px solid #dce8f3;
            border-radius: 9px;
            font-size: .72rem;
            font-weight: 700;
            text-decoration: none;
            white-space: nowrap;
        }

        body.role-kepala_bagian .team-summary {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 10px;
        }

        body.role-kepala_bagian .team-stat {
            min-width: 0;
            padding: 13px;
            background: rgba(255, 255, 255, .9);
            border: 1px solid #e3ebf4;
            border-radius: 13px;
        }

        body.role-kepala_bagian .team-stat-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            color: #526783;
            font-size: .65rem;
        }

        body.role-kepala_bagian .team-stat-heading i {
            display: grid;
            width: 25px;
            height: 25px;
            place-items: center;
            color: #078ac8;
            background: #e9f7ff;
            border-radius: 8px;
            font-size: .68rem;
        }

        body.role-kepala_bagian .team-stat-skp .team-stat-heading i {
            color: #059669;
            background: #e9fbf4;
        }

        body.role-kepala_bagian .team-stat-unit .team-stat-heading i {
            color: #8b5cf6;
            background: #f3edff;
        }

        body.role-kepala_bagian .team-stat > strong {
            display: block;
            margin-top: 6px;
            color: #10233f;
            font-size: 1.08rem;
            line-height: 1.3;
        }

        body.role-kepala_bagian .team-stat-skp > strong {
            color: #059669;
        }

        body.role-kepala_bagian .team-stat > strong span {
            display: inline-block;
            padding: 3px 6px;
            color: #047857;
            background: #e8fbf2;
            border-radius: 6px;
            font-size: .56rem;
            vertical-align: middle;
        }

        body.role-kepala_bagian .team-stat > strong.team-stat-unavailable {
            color: #64748b;
            font-size: .85rem;
        }

        body.role-kepala_bagian .team-stat > small {
            display: block;
            margin-top: 5px;
            color: #71829c;
            font-size: .58rem;
            line-height: 1.45;
        }

        body.role-kepala_bagian .team-stat-unit > strong {
            overflow: hidden;
            font-size: .82rem;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        body.role-kepala_bagian .team-stat-progress,
        body.role-kepala_bagian .team-member-progress {
            height: 5px;
            margin-top: 8px;
            overflow: hidden;
            background: #edf2f7;
            border-radius: 99px;
        }

        body.role-kepala_bagian .team-stat-progress span,
        body.role-kepala_bagian .team-member-progress span {
            display: block;
            height: 100%;
            background: linear-gradient(90deg, #10b981, #059669);
            border-radius: inherit;
        }

        body.role-kepala_bagian .team-toolbar {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
            padding: 10px;
            background: #fff;
            border: 1px solid #e0e8f1;
            border-radius: 13px;
        }

        body.role-kepala_bagian .team-search {
            display: flex;
            align-items: center;
            flex: 1 1 280px;
            gap: 8px;
            min-width: 170px;
            padding: 0 9px;
            background: #f8fafc;
            border: 1px solid #e5ebf2;
            border-radius: 9px;
        }

        body.role-kepala_bagian .team-search label {
            margin: 0;
            color: #8ba0b8;
            font-size: .72rem;
        }

        body.role-kepala_bagian .team-search input {
            width: 100%;
            min-width: 0;
            height: 34px;
            padding: 0;
            color: #263653;
            background: transparent;
            border: 0;
            outline: 0;
            font: inherit;
            font-size: .67rem;
        }

        body.role-kepala_bagian .team-search button {
            padding: 5px 8px;
            color: #fff;
            background: #078ac8;
            border: 0;
            border-radius: 6px;
            font-size: .62rem;
            font-weight: 700;
        }

        body.role-kepala_bagian .team-filters {
            display: flex;
            align-items: center;
            gap: 5px;
            white-space: nowrap;
        }

        body.role-kepala_bagian .team-filters a {
            padding: 7px 9px;
            color: #526783;
            background: #f6f9fc;
            border: 1px solid #e7edf4;
            border-radius: 7px;
            font-size: .62rem;
            font-weight: 600;
            text-decoration: none;
        }

        body.role-kepala_bagian .team-filters a.is-active {
            color: #fff;
            background: #15213a;
            border-color: #15213a;
        }

        body.role-kepala_bagian .team-sort {
            display: flex;
            align-items: center;
            gap: 5px;
            color: #64748b;
            font-size: .64rem;
            white-space: nowrap;
        }

        body.role-kepala_bagian .team-sort select {
            max-width: 175px;
            height: 34px;
            padding: 0 7px;
            color: #526783;
            background: #f8fafc;
            border: 1px solid #e5ebf2;
            border-radius: 8px;
            font: inherit;
        }

        body.role-kepala_bagian .kepala-bagian-team-list {
            display: grid;
            gap: 10px;
        }

        body.role-kepala_bagian .team-member-card {
            display: grid;
            grid-template-columns: minmax(190px, 1.3fr) minmax(155px, 1fr) minmax(155px, 1fr) minmax(140px, .75fr) auto;
            align-items: center;
            gap: 13px;
            min-width: 0;
            padding: 15px;
            background: #fff;
            border: 1px solid #e1e9f2;
            border-radius: 14px;
            box-shadow: 0 3px 10px rgba(27, 55, 87, .025);
        }

        body.role-kepala_bagian .team-person {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }

        body.role-kepala_bagian .team-avatar {
            position: relative;
            display: grid;
            flex: 0 0 42px;
            width: 42px;
            height: 42px;
            place-items: center;
            color: #fff;
            background: linear-gradient(145deg, #15b9e8, #0879bf);
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(7, 137, 200, .2);
            font-size: .88rem;
            font-weight: 800;
        }

        body.role-kepala_bagian .team-person:nth-child(odd) .team-avatar {
            background: linear-gradient(145deg, #8173f6, #4266df);
        }

        body.role-kepala_bagian .team-online-dot {
            position: absolute;
            right: -2px;
            bottom: -2px;
            width: 11px;
            height: 11px;
            background: #10b981;
            border: 2px solid #fff;
            border-radius: 50%;
        }

        body.role-kepala_bagian .team-person-copy {
            min-width: 0;
        }

        body.role-kepala_bagian .team-person-copy strong,
        body.role-kepala_bagian .team-person-copy span {
            display: block;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        body.role-kepala_bagian .team-person-copy strong {
            color: #10233f;
            font-size: .8rem;
        }

        body.role-kepala_bagian .team-person-copy span {
            margin-top: 4px;
            color: #64748b;
            font-size: .67rem;
            white-space: nowrap;
        }

        body.role-kepala_bagian .team-member-detail {
            min-width: 0;
            padding: 9px 10px;
            background: #f9fbfd;
            border: 1px solid #edf1f6;
            border-radius: 9px;
        }

        body.role-kepala_bagian .team-member-detail small,
        body.role-kepala_bagian .team-member-detail strong,
        body.role-kepala_bagian .team-member-detail > span {
            display: block;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        body.role-kepala_bagian .team-member-detail small {
            color: #8293aa;
            font-size: .53rem;
            font-weight: 700;
        }

        body.role-kepala_bagian .team-member-detail small i {
            margin-right: 3px;
            color: #8ba0b8;
        }

        body.role-kepala_bagian .team-member-detail strong {
            margin-top: 5px;
            color: #263653;
            font-size: .66rem;
        }

        body.role-kepala_bagian .team-member-detail > span {
            margin-top: 3px;
            color: #8293aa;
            font-size: .56rem;
        }

        body.role-kepala_bagian .team-member-skp {
            min-width: 0;
        }

        body.role-kepala_bagian .team-member-skp > div:first-child {
            display: flex;
            justify-content: space-between;
            gap: 5px;
            color: #526783;
            font-size: .59rem;
        }

        body.role-kepala_bagian .team-member-skp > div:first-child strong {
            color: #059669;
        }

        body.role-kepala_bagian .team-member-progress {
            height: 5px;
            margin-top: 5px;
        }

        body.role-kepala_bagian .team-member-progress span {
            background: linear-gradient(90deg, #12b8ec, #08a3d8);
        }

        body.role-kepala_bagian .team-member-skp > small {
            display: flex;
            justify-content: space-between;
            gap: 4px;
            margin-top: 4px;
            color: #8293aa;
            font-size: .5rem;
        }

        body.role-kepala_bagian .team-member-skp > small b {
            color: #059669;
            font-weight: 600;
        }

        body.role-kepala_bagian .team-member-actions {
            display: grid;
            justify-items: end;
            gap: 5px;
        }

        body.role-kepala_bagian .team-status,
        body.role-kepala_bagian .team-review-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 6px 9px;
            color: #64748b;
            background: #f1f5f9;
            border-radius: 99px;
            font-size: .6rem;
            white-space: nowrap;
        }

        body.role-kepala_bagian .team-status.is-active {
            color: #047857;
            background: #d1fae5;
        }

        body.role-kepala_bagian .team-review-badge {
            color: #9a5b05;
            background: #fff6db;
        }

        body.role-kepala_bagian .team-empty {
            padding: 40px 20px;
            background: #fff;
            border: 1px dashed #cbd5e1;
            border-radius: 14px;
            text-align: center;
        }

        body.role-kepala_bagian .team-empty i {
            color: #078ac8;
            font-size: 1.8rem;
        }

        body.role-kepala_bagian .team-empty h2 {
            margin: 9px 0 5px;
            font-size: 1rem;
        }

        body.role-kepala_bagian .team-empty p {
            margin: 0;
            color: #64748b;
            font-size: .78rem;
        }

        body.role-kepala_bagian .team-pagination nav {
            display: flex;
            justify-content: center;
        }

        body.role-kepala_bagian .team-footer-panels {
            display: grid;
            grid-template-columns: minmax(0, 2fr) minmax(240px, 1fr);
            gap: 12px;
        }

        body.role-kepala_bagian .team-guidance,
        body.role-kepala_bagian .team-deadline {
            display: flex;
            gap: 11px;
            min-width: 0;
            padding: 16px;
            border-radius: 13px;
        }

        body.role-kepala_bagian .team-guidance {
            color: #12426a;
            background: #f4faff;
            border: 1px solid #d2eaff;
        }

        body.role-kepala_bagian .team-guidance > span {
            display: grid;
            flex: 0 0 30px;
            width: 30px;
            height: 30px;
            place-items: center;
            color: #fff;
            background: #12a9df;
            border-radius: 9px;
        }

        body.role-kepala_bagian .team-guidance strong {
            font-size: .72rem;
        }

        body.role-kepala_bagian .team-guidance p {
            margin: 5px 0 8px;
            color: #526783;
            font-size: .63rem;
            line-height: 1.55;
        }

        body.role-kepala_bagian .team-guidance a {
            color: #0875ad;
            font-size: .61rem;
            font-weight: 700;
            text-decoration: none;
        }

        body.role-kepala_bagian .team-deadline {
            display: block;
            color: #fff;
            background: linear-gradient(135deg, #111b31, #173955);
            box-shadow: 0 4px 10px rgba(17, 27, 49, .12);
        }

        body.role-kepala_bagian .team-deadline-label,
        body.role-kepala_bagian .team-deadline > small {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }

        body.role-kepala_bagian .team-deadline-label {
            color: #9edbff;
            font-size: .53rem;
            font-weight: 700;
        }

        body.role-kepala_bagian .team-deadline-label b {
            padding: 3px 6px;
            color: #9edbff;
            background: #173a58;
            border: 1px solid #285577;
            border-radius: 99px;
            font-size: .5rem;
        }

        body.role-kepala_bagian .team-deadline > strong {
            display: block;
            margin-top: 10px;
            font-size: .75rem;
        }

        body.role-kepala_bagian .team-deadline p {
            margin: 5px 0 10px;
            color: #c4d2e0;
            font-size: .59rem;
            line-height: 1.5;
        }

        body.role-kepala_bagian .team-deadline > small {
            color: #a7b8ca;
            font-size: .55rem;
        }

        body.role-kepala_bagian .team-deadline > small b {
            color: #61e2b2;
            font-weight: 600;
        }

        body.role-kepala_bagian .team-deadline > small b i {
            margin-right: 3px;
        }

        @media (max-width: 1050px) {
            body.role-kepala_bagian .team-toolbar {
                flex-wrap: wrap;
            }

            body.role-kepala_bagian .team-search {
                flex-basis: 100%;
            }

            body.role-kepala_bagian .team-member-card {
                grid-template-columns: minmax(180px, 1.2fr) minmax(150px, 1fr) minmax(150px, 1fr) minmax(130px, .8fr);
            }

            body.role-kepala_bagian .team-member-actions {
                grid-column: 1 / -1;
                display: flex;
                justify-content: flex-end;
            }
        }

        @media (max-width: 720px) {
            body.role-kepala_bagian .kepala-bagian-team-page {
                gap: 12px;
                padding: 12px;
            }

            body.role-kepala_bagian .team-overview {
                gap: 13px;
                padding: 14px;
            }

            body.role-kepala_bagian .team-heading {
                align-items: flex-start;
            }

            body.role-kepala_bagian .team-heading h1 {
                font-size: 1.2rem;
            }

            body.role-kepala_bagian .team-heading p {
                font-size: .72rem;
            }

            body.role-kepala_bagian .team-back-link {
                padding: 7px;
                font-size: 0;
            }

            body.role-kepala_bagian .team-back-link i {
                font-size: .8rem;
            }

            body.role-kepala_bagian .team-summary {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 8px;
            }

            body.role-kepala_bagian .team-stat {
                padding: 10px;
            }

            body.role-kepala_bagian .team-stat > strong {
                font-size: .95rem;
            }

            body.role-kepala_bagian .team-toolbar {
                gap: 8px;
                padding: 8px;
            }

            body.role-kepala_bagian .team-filters {
                flex: 1 1 100%;
                overflow-x: auto;
            }

            body.role-kepala_bagian .team-filters a {
                padding: 6px 8px;
            }

            body.role-kepala_bagian .team-sort {
                margin-left: auto;
            }

            body.role-kepala_bagian .team-member-card {
                grid-template-columns: minmax(0, 1fr) auto;
                gap: 9px;
                padding: 12px;
            }

            body.role-kepala_bagian .team-person {
                grid-column: 1 / -1;
            }

            body.role-kepala_bagian .team-member-detail {
                min-width: 0;
            }

            body.role-kepala_bagian .team-member-skp {
                grid-column: 1;
            }

            body.role-kepala_bagian .team-member-actions {
                grid-column: 2;
                grid-row: 3;
                display: grid;
                align-self: end;
            }

            body.role-kepala_bagian .team-footer-panels {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 390px) {
            body.role-kepala_bagian .team-summary {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush
