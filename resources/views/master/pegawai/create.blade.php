@extends('layouts.app')

@section('title', 'Tambah Pegawai')

@section('content')
    <div class="master-pegawai-create">

        <header class="master-create-heading">
            <div>
                <nav class="master-create-breadcrumb" aria-label="Breadcrumb">
                    <a href="{{ route('master.dashboard') }}">Kepegawaian</a>
                    <i class="fa-solid fa-chevron-right"></i>
                    <a href="{{ route('master.pegawai.index') }}">Data Pegawai</a>
                    <i class="fa-solid fa-chevron-right"></i>
                    <span>Tambah Pegawai</span>
                </nav>
                <h1>
                    <i class="fa-solid fa-user-plus"></i>
                    Tambah Profil Pegawai
                </h1>
                <p>Lengkapi akun, penempatan, dan status kerja pegawai baru.</p>
            </div>
        </header>

        <div class="master-create-layout">
            <aside class="master-create-aside">
                <section class="master-create-profile-card">
                    <div class="master-create-profile-top">
                        <span class="master-create-avatar">PG</span>
                        <div><strong>Pegawai Baru</strong><small>Profil akan dibuat setelah disimpan</small></div>
                    </div>
                    <span class="master-create-profile-status"><i class="fa-solid fa-circle"></i> Belum dibuat</span>
                    <div class="master-create-profile-details">
                        <div><small>Nama</small><strong>{{ old('nama') ?: 'Belum diisi' }}</strong></div>
                        <div><small>Email Akun</small><strong>{{ old('email') ?: 'Belum diisi' }}</strong></div>
                        <div><small>Role
                                Awal</small><strong>{{ match (old('role', 'pegawai')) { 'atasan' => 'Atasan', 'kepala_bagian' => 'Kepala Bagian', default => 'Pegawai' } }}</strong>
                        </div>
                    </div>
                </section>
                <section class="master-create-progress-card">
                    <div class="master-create-progress-heading"><strong>Langkah Pengisian</strong><span>3 langkah</span>
                    </div>
                    <ol>
                        <li><span>1</span>
                            <div><strong>Akun &amp; Akses</strong><small>Identitas dan kredensial</small></div>
                        </li>
                        <li><span>2</span>
                            <div><strong>Penempatan &amp; Hierarki</strong><small>Divisi, jabatan, dan atasan</small></div>
                        </li>
                        <li><span>3</span>
                            <div><strong>Status &amp; Periode Kerja</strong><small>Tanggal masuk dan status</small></div>
                        </li>
                    </ol>
                </section>
                <aside class="master-create-aside-note">
                    <i class="fa-solid fa-circle-info"></i>
                    <span>Pilih divisi terlebih dahulu agar pilihan jabatan dan atasan sesuai dengan struktur kerja.</span>
                </aside>
            </aside>

            <form action="{{ route('master.pegawai.store') }}" method="POST">
                @csrf
                <section class="master-form-card">
                    <div class="master-form-title"><b>1</b>
                        <div>
                            <h2>Informasi Akun Pegawai</h2>
                            <p>Identitas dan kredensial login portal</p>
                        </div>
                    </div>
                    <div class="master-field"><label for="nama">Nama Lengkap <em>*</em></label><input type="text"
                            name="nama" id="nama" value="{{ old('nama') }}"
                            placeholder="Contoh: Budi Santoso, S.T." class="@error('nama') invalid @enderror">
                        @error('nama')
                            <small class="field-error">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="master-field"><label for="email">Email Perusahaan / Akun <em>*</em></label>
                        <div class="input-icon"><i class="fa-regular fa-envelope"></i><input type="email" name="email"
                                id="email" value="{{ old('email') }}" placeholder="nama@perusahaan.co.id"
                                class="@error('email') invalid @enderror"></div><small class="field-help">Digunakan untuk
                            akses
                            login dan notifikasi kinerja resmi.</small>
                        @error('email')
                            <small class="field-error">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="master-field"><label for="password">Password Awal <em>*</em></label>
                        <div class="input-icon password-input"><i class="fa-solid fa-lock"></i><input type="password"
                                name="password" id="password" placeholder="Minimal 8 karakter"
                                class="@error('password') invalid @enderror"><button type="button" id="togglePassword"><i
                                    class="fa-regular fa-eye"></i></button></div><small class="field-help">Minimal 8
                            karakter
                            (dapat diubah mandiri).</small>
                        @error('password')
                            <small class="field-error">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="master-field"><label for="role">Peran Akun <em>*</em></label><select name="role"
                            id="role" class="@error('role') invalid @enderror">
                            <option value="pegawai" {{ old('role', 'pegawai') === 'pegawai' ? 'selected' : '' }}>Pegawai</option>
                            <option value="atasan" {{ old('role') === 'atasan' ? 'selected' : '' }}>Atasan</option>
                            <option value="kepala_bagian" {{ old('role') === 'kepala_bagian' ? 'selected' : '' }}>Kepala Bagian</option>
                        </select>
                        @error('role')
                            <small class="field-error">{{ $message }}</small>
                        @enderror
                        <small class="field-help">Admin tidak dibuat dari formulir pegawai; akun Admin dikelola terpisah.</small>
                    </div>
                </section>
                <section class="master-form-card">
                    <div class="master-form-title"><b>2</b>
                        <div>
                            <h2>Penempatan &amp; Hierarki Kerja</h2>
                            <p>Departemen dan garis pelaporan pimpinan</p>
                        </div>
                    </div>
                    <div class="master-field"><label for="divisi_filter">Divisi / Unit Kerja <em>*</em></label><select
                            id="divisi_filter">
                            <option value="">-- Pilih Divisi --</option>
                            @foreach ($divisis as $divisi)
                                <option value="{{ $divisi->id }}">{{ $divisi->nama_divisi }}</option>
                            @endforeach
                        </select>
                        <div class="master-info-note"><i class="fa-regular fa-circle-question"></i><span>Pilih divisi
                                terlebih
                                dahulu untuk menyaring daftar Jabatan dan Atasan di bawah.</span></div>
                    </div>
                    <div class="master-field"><label for="jabatan_id">Jabatan / Posisi <em>*</em></label><select
                            name="jabatan_id" id="jabatan_id" class="@error('jabatan_id') invalid @enderror">
                            <option value="">-- Pilih Jabatan --</option>
                            @foreach ($jabatans as $jabatan)
                                <option value="{{ $jabatan->id }}" data-divisi="{{ $jabatan->divisi_id }}"
                                    {{ old('jabatan_id') == $jabatan->id ? 'selected' : '' }}>{{ $jabatan->nama_jabatan }}
                                    ({{ $jabatan->divisi->nama_divisi }})
                                </option>
                            @endforeach
                        </select>
                        @error('jabatan_id')
                            <small class="field-error">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="master-field"><label for="atasan_id">Atasan Langsung <em>*</em><span>Wajib untuk pegawai dan atasan</span></label><select name="atasan_id" id="atasan_id"
                            class="@error('atasan_id') invalid @enderror">
                            <option value="">-- Pilih atasan sesuai role dan divisi --</option>
                            @foreach ($atasans as $atasan)
                                <option value="{{ $atasan->id }}" data-divisi="{{ $atasan->jabatan->divisi_id ?? '' }}" data-role="{{ $atasan->user->role ?? '' }}"
                                    {{ old('atasan_id') == $atasan->id ? 'selected' : '' }}>{{ $atasan->nama }}
                                    @if ($atasan->jabatan)
                                        ({{ $atasan->jabatan->nama_jabatan }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        <small class="field-help italic">Pegawai memilih atasan; atasan memilih kepala bagian. Kepala bagian tidak memiliki atasan langsung.</small>
                        @error('atasan_id')
                            <small class="field-error">{{ $message }}</small>
                        @enderror
                    </div>
                </section>
                <section class="master-form-card">
                    <div class="master-form-title"><b>3</b>
                        <div>
                            <h2>Status &amp; Periode Kerja</h2>
                            <p>TMT masuk dan keaktifan akun</p>
                        </div>
                    </div>
                    <div class="master-form-two">
                        <div class="master-field"><label for="tanggal_masuk">Tanggal Masuk <em>*</em></label><input
                                type="date" name="tanggal_masuk" id="tanggal_masuk"
                                value="{{ old('tanggal_masuk') }}" class="@error('tanggal_masuk') invalid @enderror">
                            @error('tanggal_masuk')
                                <small class="field-error">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="master-field"><label for="status">Status Akun <em>*</em></label><select
                                name="status" id="status" class="@error('status') invalid @enderror">
                                <option value="aktif" {{ old('status', 'aktif') === 'aktif' ? 'selected' : '' }}>● Aktif
                                </option>
                                <option value="nonaktif" {{ old('status') === 'nonaktif' ? 'selected' : '' }}>● Nonaktif
                                </option>
                            </select>
                            @error('status')
                                <small class="field-error">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                </section>
                <div class="master-form-actions"><button type="submit"><i class="fa-regular fa-floppy-disk"></i> Simpan
                    </button><a href="{{ route('master.pegawai.index') }}">Batal</a></div>
            </form>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        body.role-master .master-pegawai-create {
            width: 100%;
            max-width: 560px;
            margin: auto;
            padding: 1rem 1rem 0;
            color: #14223d
        }

        .master-create-breadcrumb,
        .master-create-aside {
            display: none;
        }

        .master-create-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .6rem;
            margin: 0 -1rem;
            padding: .8rem 1rem;
            background: #fff;
            border-bottom: 1px solid #e3ebf3
        }

        .master-create-topbar>a {
            color: #526480;
            text-decoration: none;
            font-size: .78rem;
            font-weight: 600
        }

        .master-create-crumb {
            display: flex;
            align-items: center;
            gap: .45rem;
            color: #0875ad;
            font-size: .65rem;
            white-space: nowrap
        }

        .master-create-crumb a {
            color: #91a1b8;
            text-decoration: none
        }

        .master-create-crumb i {
            font-size: .48rem;
            color: #9aa9bc
        }

        .master-create-heading {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: .5rem;
            padding: 1.15rem 0 .85rem
        }

        .master-create-heading h1 {
            margin: 0;
            font-size: 1rem
        }

        .master-create-heading p {
            max-width: 380px;
            margin: .3rem 0 0;
            color: #71829c;
            font-size: .75rem;
            line-height: 1.55
        }

        .master-create-heading>span {
            padding: .4rem .55rem;
            color: #0875ad;
            background: #e9f7ff;
            border-radius: 999px;
            font-size: .65rem;
            white-space: nowrap
        }

        .master-create-heading>span i {
            font-size: .4rem;
            vertical-align: middle
        }

        .master-form-card {
            margin-bottom: 1rem;
            padding: 1rem 1rem .9rem;
            background: #fff;
            border: 1px solid #dfe8f1;
            border-radius: 17px;
            box-shadow: 0 4px 14px #2334550a
        }

        .master-form-title {
            display: flex;
            align-items: center;
            gap: .65rem;
            padding-bottom: .8rem;
            margin-bottom: .85rem;
            border-bottom: 1px solid #e8eef5
        }

        .master-form-title>b {
            display: grid;
            place-items: center;
            width: 29px;
            height: 29px;
            flex: 0 0 29px;
            color: #0875ad;
            background: #e0f3ff;
            border-radius: 9px;
            font-size: .8rem;
            font-weight: 500
        }

        .master-form-title h2 {
            margin: 0;
            font-size: .78rem;
            letter-spacing: .04em
        }

        .master-form-title p {
            margin: .18rem 0 0;
            color: #91a1b8;
            font-size: .65rem
        }

        .master-field {
            margin-bottom: .85rem
        }

        .master-field:last-child {
            margin-bottom: 0
        }

        .master-field label {
            display: flex;
            align-items: center;
            gap: .2rem;
            margin-bottom: .38rem;
            font-size: .7rem
        }

        .master-field label em {
            color: #e54862;
            font-style: normal
        }

        .master-field label span {
            margin-left: auto;
            padding: .25rem .4rem;
            color: #71829c;
            background: #f0f4f8;
            border-radius: 5px;
            font-size: .58rem
        }

        .master-field input,
        .master-field select {
            box-sizing: border-box;
            width: 100%;
            height: 40px;
            padding: 0 .75rem;
            color: #263653;
            background: #fff;
            border: 1px solid #d6e1ed;
            border-radius: 12px;
            outline: 0;
            font: inherit;
            font-size: .72rem
        }

        .master-field input:focus,
        .master-field select:focus {
            border-color: #0b91ce;
            box-shadow: 0 0 0 3px #0b91ce16
        }

        .master-field input::placeholder {
            color: #9aabc0
        }

        .master-field input.invalid,
        .master-field select.invalid {
            border-color: #e54862
        }

        .input-icon {
            position: relative
        }

        .input-icon>i {
            position: absolute;
            left: .75rem;
            top: 50%;
            transform: translateY(-50%);
            color: #91a1b8;
            font-size: .8rem
        }

        .input-icon input {
            padding-left: 2.25rem
        }

        .password-input button {
            position: absolute;
            right: .7rem;
            top: 50%;
            transform: translateY(-50%);
            border: 0;
            background: none;
            color: #91a1b8
        }

        .password-input input {
            padding-right: 2.5rem
        }

        .field-help {
            display: block;
            margin-top: .35rem;
            color: #91a1b8;
            font-size: .61rem;
            line-height: 1.4
        }

        .field-help.italic {
            font-style: italic
        }

        .field-error {
            display: block;
            margin-top: .3rem;
            color: #e54862;
            font-size: .62rem
        }

        .master-info-note {
            display: flex;
            gap: .45rem;
            margin-top: .45rem;
            padding: .55rem .65rem;
            color: #607492;
            background: #f5f8fb;
            border: 1px solid #e2eaf2;
            border-radius: 9px;
            font-size: .61rem;
            line-height: 1.4
        }

        .master-info-note i {
            color: #0875ad
        }

        .master-form-two {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .65rem
        }

        .master-form-actions {
            display: grid;
            gap: .5rem;
            margin-top: .4rem
        }

        .master-form-actions button,
        .master-form-actions a {
            width: 100%;
            box-sizing: border-box;
            padding: .78rem;
            text-align: center;
            border-radius: 11px;
            font: inherit;
            font-size: .76rem;
            text-decoration: none
        }

        .master-form-actions button {
            color: #fff;
            background: #078dcc;
            border: 0;
            box-shadow: 0 4px 9px #078dcc35
        }

        .master-form-actions a {
            color: #526480;
            background: #fff;
            border: 1px solid #dce5ee
        }

        @media(max-width:380px) {
            body.role-master .master-pegawai-create {
                padding-inline: .75rem
            }

            .master-create-topbar {
                margin-inline: -.75rem;
                padding-inline: .75rem
            }

            .master-create-crumb a,
            .master-create-crumb i {
                display: none
            }

            .master-create-heading h1 {
                font-size: 1.2rem
            }

            .master-create-heading p {
                font-size: .68rem
            }

            .master-form-card {
                padding-inline: .8rem
            }

            .master-form-two {
                gap: .45rem
            }

            .master-field input,
            .master-field select {
                font-size: .67rem
            }
        }

        @media (min-width: 768px) {
            body.role-master .master-pegawai-create {
                width: 100%;
                max-width: none;
                margin: 0;
                padding: 1rem clamp(1.25rem, 2.4vw, 2.5rem) 1.5rem;
                color: #1b2938;
            }

            .master-create-heading {
                align-items: flex-end;
                justify-content: space-between;
                gap: 1rem;
                padding: .75rem 0 1.1rem;
            }

            .master-create-breadcrumb {
                display: flex;
                align-items: center;
                gap: .4rem;
                margin-bottom: .45rem;
                color: #718092;
                font-size: .64rem;
            }

            .master-create-breadcrumb a {
                color: #718092;
                text-decoration: none;
            }

            .master-create-breadcrumb a:hover,
            .master-create-breadcrumb>span {
                color: #1769d2;
            }

            .master-create-breadcrumb i {
                color: #a0acb9;
                font-size: .48rem;
            }

            .master-create-heading h1 {
                color: #1b2938;
                font-size: 1.4rem;
                font-weight: 700;
            }

            .master-create-heading p {
                max-width: 560px;
                margin-top: .25rem;
                font-size: .76rem;
            }

            .master-create-layout {
                display: grid;
                grid-template-columns: minmax(230px, .7fr) minmax(0, 1.8fr);
                align-items: start;
                gap: 1rem;
            }

            .master-create-aside {
                display: grid;
                gap: .8rem;
                min-width: 0;
            }

            .master-create-profile-card,
            .master-create-progress-card {
                padding: .9rem;
                background: #fff;
                border: 1px solid #e2e8ef;
                border-radius: 7px;
            }

            .master-create-profile-top {
                display: flex;
                align-items: center;
                gap: .65rem;
                min-width: 0;
                padding-bottom: .8rem;
                border-bottom: 1px solid #edf1f5;
            }

            .master-create-avatar {
                display: grid;
                place-items: center;
                width: 42px;
                height: 42px;
                flex: 0 0 42px;
                color: #1769d2;
                background: #eaf2ff;
                border-radius: 8px;
                font-size: .82rem;
                font-weight: 700;
            }

            .master-create-profile-top>div {
                min-width: 0;
            }

            .master-create-profile-top strong,
            .master-create-profile-top small {
                display: block;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .master-create-profile-top strong {
                color: #25364a;
                font-size: .78rem;
            }

            .master-create-profile-top small {
                margin-top: .15rem;
                color: #8290a0;
                font-size: .62rem;
            }

            .master-create-profile-status {
                display: inline-flex;
                align-items: center;
                gap: .3rem;
                margin-top: .7rem;
                padding: .28rem .42rem;
                color: #94620a;
                background: #fff5df;
                border-radius: 999px;
                font-size: .6rem;
            }

            .master-create-profile-status i {
                font-size: .4rem;
            }

            .master-create-profile-details {
                display: grid;
                gap: .6rem;
                padding-top: .75rem;
            }

            .master-create-profile-details>div small,
            .master-create-profile-details>div strong {
                display: block;
            }

            .master-create-profile-details small {
                margin-bottom: .12rem;
                color: #8290a0;
                font-size: .59rem;
            }

            .master-create-profile-details strong {
                color: #40546a;
                font-size: .66rem;
                font-weight: 600;
                overflow-wrap: anywhere;
            }

            .master-create-progress-heading {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: .5rem;
                padding-bottom: .65rem;
                border-bottom: 1px solid #edf1f5;
            }

            .master-create-progress-heading strong {
                color: #25364a;
                font-size: .72rem;
            }

            .master-create-progress-heading span {
                color: #1769d2;
                background: #edf4ff;
                padding: .2rem .38rem;
                border-radius: 4px;
                font-size: .57rem;
            }

            .master-create-progress-card ol {
                display: grid;
                gap: .6rem;
                margin: .7rem 0 0;
                padding: 0;
                list-style: none;
            }

            .master-create-progress-card li {
                display: flex;
                align-items: center;
                gap: .55rem;
                min-width: 0;
            }

            .master-create-progress-card li>span {
                display: grid;
                place-items: center;
                width: 26px;
                height: 26px;
                flex: 0 0 26px;
                color: #1769d2;
                background: #edf4ff;
                border-radius: 50%;
                font-size: .64rem;
                font-weight: 700;
            }

            .master-create-progress-card li>div {
                min-width: 0;
            }

            .master-create-progress-card li strong,
            .master-create-progress-card li small {
                display: block;
            }

            .master-create-progress-card li strong {
                color: #40546a;
                font-size: .65rem;
            }

            .master-create-progress-card li small {
                margin-top: .1rem;
                color: #8290a0;
                font-size: .57rem;
            }

            .master-create-aside-note {
                display: flex;
                align-items: flex-start;
                gap: .5rem;
                padding: .75rem;
                color: #94620a;
                background: #fff8e8;
                border: 1px solid #ffe2a8;
                border-radius: 6px;
                font-size: .63rem;
                line-height: 1.45;
            }

            .master-create-aside-note>i {
                margin-top: .1rem;
            }

            .master-create-layout>form {
                min-width: 0;
            }

            .master-form-card {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: .1rem 1rem;
                padding: 1rem;
                margin-bottom: .8rem;
                border-radius: 7px;
                box-shadow: none;
            }

            .master-form-title {
                grid-column: 1 / -1;
                gap: .6rem;
                margin-bottom: .65rem;
                padding-bottom: .7rem;
            }

            .master-form-title>b {
                width: 28px;
                height: 28px;
                flex-basis: 28px;
                border-radius: 6px;
            }

            .master-form-title h2 {
                color: #25364a;
                font-size: .82rem;
                letter-spacing: 0;
            }

            .master-form-title p {
                font-size: .63rem;
            }

            .master-field {
                min-width: 0;
                margin-bottom: .7rem;
            }

            .master-field label {
                font-size: .67rem;
            }

            .master-field input,
            .master-field select {
                height: 38px;
                border-radius: 5px;
                font-size: .68rem;
            }

            .master-info-note {
                border-radius: 5px;
            }

            .master-form-two {
                grid-column: 1 / -1;
                gap: .8rem;
            }

            .master-form-two .master-field {
                margin-bottom: .1rem;
            }

            .master-form-actions {
                display: flex;
                justify-content: flex-end;
                gap: .55rem;
                margin-top: .25rem;
            }

            .master-form-actions button,
            .master-form-actions a {
                width: auto;
                min-width: 110px;
                padding: .65rem .85rem;
                border-radius: 5px;
                font-size: .7rem;
            }

            .master-form-actions button {
                order: 2;
                background: #1769d2;
                box-shadow: none;
            }
        }

        @media (min-width: 768px) and (max-width: 1050px) {
            .master-create-layout {
                grid-template-columns: minmax(200px, .65fr) minmax(0, 1.7fr);
                gap: .7rem;
            }

            .master-form-card {
                gap: .1rem .65rem;
                padding: .8rem;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        (function() {
            const filter = document.getElementById('divisi_filter'),
                jabatan = document.getElementById('jabatan_id'),
                atasan = document.getElementById('atasan_id'),
                role = document.getElementById('role');

            function applyFilter() {
                const id = filter.value;
                const supervisorRole = role.value === 'pegawai' ? 'atasan' : role.value === 'atasan' ? 'kepala_bagian' : null;
                Array.from(jabatan.options).forEach(function(option) {
                    if (option.value) option.hidden = !!id && option.dataset.divisi !== id;
                });
                Array.from(atasan.options).forEach(function(option) {
                    option.hidden = !!option.value && (!supervisorRole || (!!id && option.dataset.divisi !== id) || option.dataset.role !== supervisorRole);
                });
                [jabatan, atasan].forEach(function(select) {
                    if (select.selectedOptions[0] && select.selectedOptions[0].hidden) select.value = '';
                });
            }
            filter.addEventListener('change', applyFilter);
            role.addEventListener('change', applyFilter);
            applyFilter();
            const toggle = document.getElementById('togglePassword'),
                password = document.getElementById('password');
            toggle.addEventListener('click', function() {
                password.type = password.type === 'password' ? 'text' : 'password';
                toggle.innerHTML = password.type === 'password' ? '<i class="fa-regular fa-eye"></i>' :
                    '<i class="fa-regular fa-eye-slash"></i>';
            });
        })();
    </script>
@endpush
