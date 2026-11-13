@extends('layouts.app')

@section('title', 'Edit Pegawai')

@section('content')
    <div class="master-pegawai-edit">

        @php
            $currentRole = old('role', $pegawai->user->role ?? 'pegawai');
            $currentStatus = old('status', $pegawai->status);
            $currentDivisi = old('divisi_filter', $pegawai->jabatan->divisi_id ?? '');
        @endphp


        <header class="master-edit-heading">
            <div>
                <nav class="master-edit-breadcrumb" aria-label="Breadcrumb">
                    <a href="{{ route('master.pegawai.index') }}">Kepegawaian</a>
                    <i class="fa-solid fa-chevron-right"></i>
                    <a href="{{ route('master.pegawai.index') }}">Data Pegawai</a>
                    <i class="fa-solid fa-chevron-right"></i>
                    <span>Edit Pegawai</span>
                </nav>
                <h1>
                    <i class="fa-solid fa-user-pen"></i>
                    Perbarui Profil Pegawai
                </h1>
                <p>Konfigurasi akun, struktur hierarki kerja, dan status pegawai.</p>
            </div>
        </header>


        <div class="master-edit-layout">
            <aside class="master-edit-aside">
                <section class="master-edit-profile-card">
                    <div class="master-edit-profile-top">
                        <span class="master-edit-avatar">{{ strtoupper(substr($pegawai->nama, 0, 2)) }}</span>
                        <div><strong>{{ $pegawai->nama }}</strong><small>{{ $pegawai->user->email ?? '-' }}</small></div>
                    </div>
                    <span class="master-edit-profile-status {{ $currentStatus === 'aktif' ? 'is-active' : 'is-inactive' }}">
                        <i class="fa-solid fa-circle"></i> {{ $currentStatus === 'aktif' ? 'Aktif Berdinas' : 'Nonaktif' }}
                    </span>
                    <div class="master-edit-profile-details">
                        <div><small>Jabatan</small><strong>{{ $pegawai->jabatan->nama_jabatan ?? '-' }}</strong></div>
                        <div><small>Divisi</small><strong>{{ $pegawai->jabatan->divisi->nama_divisi ?? '-' }}</strong></div>
                        <div><small>Atasan Langsung</small><strong>{{ $pegawai->atasan->nama ?? '-' }}</strong></div>
                        <div><small>Tanggal
                                Masuk</small><strong>{{ optional($pegawai->tanggal_masuk)->translatedFormat('d M Y') ?? '-' }}</strong>
                        </div>
                    </div>
                </section>
                <aside class="master-edit-aside-note">
                    <i class="fa-solid fa-circle-info"></i>
                    <span>Perubahan jabatan dan atasan mengikuti divisi yang dipilih. Pastikan rantai pelaporan sudah
                        sesuai.</span>
                </aside>
            </aside>

            <form action="{{ route('master.pegawai.update', $pegawai) }}" method="POST">
                @csrf @method('PUT')
                <section class="master-edit-card">
                    <div class="master-edit-title"><b>1</b>
                        <div>
                            <h2>Informasi Akun &amp; Akses</h2>
                            <p>Data identitas login sistem</p>
                        </div>
                    </div>
                    <div class="master-edit-field"><label for="nama">Nama Lengkap <em>*</em></label>
                        <div class="edit-input-icon"><input type="text" name="nama" id="nama"
                                value="{{ old('nama', $pegawai->nama) }}" class="@error('nama') invalid @enderror"><i
                                class="fa-solid fa-pen"></i></div>
                        @error('nama')
                            <small class="edit-error">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="master-edit-field"><label for="email">Email Perusahaan / Akun <em>*</em></label>
                        <div class="edit-input-icon"><i class="fa-regular fa-envelope"></i><input type="email"
                                name="email" id="email" value="{{ old('email', $pegawai->user->email ?? '') }}"
                                class="@error('email') invalid @enderror"></div>
                        @error('email')
                            <small class="edit-error">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="master-edit-field"><label for="password">Password Baru
                            <span>Opsional</span><small>Tersedia</small></label>
                        <div class="edit-input-icon password-input"><input type="password" name="password" id="password"
                                placeholder="Kosongkan jika tidak diubah"
                                class="@error('password') invalid @enderror"><button type="button" id="togglePassword"><i
                                    class="fa-regular fa-eye"></i></button></div><small class="edit-help">Jika dikosongkan,
                            password lama tidak berubah.</small>
                        @error('password')
                            <small class="edit-error">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="master-edit-field"><label for="role">Peran Akun <em>*</em></label><select name="role"
                            id="role" class="@error('role') invalid @enderror">
                            <option value="pegawai" {{ $currentRole === 'pegawai' ? 'selected' : '' }}>Pegawai</option>
                            <option value="atasan" {{ $currentRole === 'atasan' ? 'selected' : '' }}>Atasan</option>
                            <option value="kepala_bagian" {{ $currentRole === 'kepala_bagian' ? 'selected' : '' }}>Kepala Bagian</option>
                            <option value="direktur" {{ $currentRole === 'direktur' ? 'selected' : '' }}>Direktur</option>
                        </select>
                        @error('role')
                            <small class="edit-error">{{ $message }}</small>
                        @enderror
                        <small class="edit-help">Admin tidak dibuat dari formulir pegawai; akun Admin dikelola terpisah.</small>
                    </div>
                </section>
                <section class="master-edit-card">
                    <div class="master-edit-title"><b>2</b>
                        <div>
                            <h2>Penempatan &amp; Hierarki Kerja</h2>
                            <p>Unit divisi dan rantai komando pelaporan</p>
                        </div>
                    </div>
                    <div class="master-edit-field"><label for="divisi_filter">Divisi / Unit Kerja <em>*</em></label><select
                            id="divisi_filter">
                            <option value="">-- Pilih Divisi --</option>
                            @foreach ($divisis as $divisi)
                                <option value="{{ $divisi->id }}" {{ $currentDivisi == $divisi->id ? 'selected' : '' }}>
                                    {{ $divisi->nama_divisi }}</option>
                            @endforeach
                        </select>
                        <div class="master-edit-note"><i class="fa-solid fa-circle-info"></i><span>Pilih divisi untuk
                                menyaring
                                pilihan <strong>Jabatan</strong> dan <strong>Atasan</strong> di bawah.</span></div>
                    </div>
                    <div class="master-edit-field"><label for="jabatan_id">Jabatan / Posisi <em>*</em></label><select
                            name="jabatan_id" id="jabatan_id" class="@error('jabatan_id') invalid @enderror">
                            <option value="">-- Pilih Jabatan --</option>
                            @foreach ($jabatans as $jabatan)
                                <option value="{{ $jabatan->id }}" data-divisi="{{ $jabatan->divisi_id }}"
                                    {{ old('jabatan_id', $pegawai->jabatan_id) == $jabatan->id ? 'selected' : '' }}>
                                    {{ $jabatan->nama_jabatan }} ({{ $jabatan->divisi->nama_divisi }})</option>
                            @endforeach
                        </select>
                        @error('jabatan_id')
                            <small class="edit-error">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="master-edit-field"><label for="atasan_id">Atasan Langsung <span>Wajib untuk Pegawai dan Atasan; opsional untuk Kepala Bagian</span></label><select name="atasan_id" id="atasan_id"
                            class="@error('atasan_id') invalid @enderror">
                            <option value="">-- Pilih atasan sesuai role dan divisi --</option>
                            @foreach ($atasans as $atasan)
                                <option value="{{ $atasan->id }}" data-divisi="{{ $atasan->jabatan->divisi_id ?? '' }}" data-role="{{ $atasan->user->role ?? '' }}"
                                    {{ old('atasan_id', $pegawai->atasan_id) == $atasan->id ? 'selected' : '' }}>
                                    {{ $atasan->nama }} @if ($atasan->jabatan)
                                        ({{ $atasan->jabatan->nama_jabatan }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        <small class="edit-help">Pegawai memilih Atasan, Atasan memilih Kepala Bagian, dan Kepala Bagian dapat ditugaskan kepada Direktur. Direktur tidak memiliki atasan langsung.</small>
                        @error('atasan_id')
                            <small class="edit-error">{{ $message }}</small>
                        @enderror
                    </div>
                </section>
                <section class="master-edit-card">
                    <div class="master-edit-title"><b>3</b>
                        <div>
                            <h2>Status &amp; Masa Kerja</h2>
                            <p>Pencatatan tanggal awal dinas dan keaktifan</p>
                        </div>
                    </div>
                    <div class="master-edit-two">
                        <div class="master-edit-field"><label for="tanggal_masuk">Tanggal Masuk <em>*</em></label><input
                                type="date" name="tanggal_masuk" id="tanggal_masuk"
                                value="{{ old('tanggal_masuk', optional($pegawai->tanggal_masuk)->format('Y-m-d')) }}"
                                class="@error('tanggal_masuk') invalid @enderror">
                            @error('tanggal_masuk')
                                <small class="edit-error">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="master-edit-field"><label for="status">Status Kepegawaian <em>*</em></label><select
                                name="status" id="status" class="@error('status') invalid @enderror">
                                <option value="aktif" {{ $currentStatus === 'aktif' ? 'selected' : '' }}>● Aktif Berdinas
                                </option>
                                <option value="nonaktif" {{ $currentStatus === 'nonaktif' ? 'selected' : '' }}>● Nonaktif
                                </option>
                            </select>
                            @error('status')
                                <small class="edit-error">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                </section>
                <div class="master-edit-actions"><button type="submit"><i class="fa-regular fa-floppy-disk"></i> Update
                    </button><a href="{{ route('master.pegawai.index') }}">Batal</a></div>
            </form>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        body.role-master .master-pegawai-edit {
            width: 100%;
            max-width: 560px;
            margin: auto;
            padding: 1rem 1rem 0;
            color: #14223d
        }

        .master-edit-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .5rem;
            margin: 0 -1rem;
            padding: .8rem 1rem;
            background: #fff;
            border-bottom: 1px solid #e3ebf3
        }

        .master-edit-topbar>a {
            color: #526480;
            text-decoration: none;
            font-size: .78rem;
            font-weight: 600
        }

        .master-edit-crumb {
            display: flex;
            align-items: center;
            gap: .45rem;
            color: #0875ad;
            font-size: .65rem;
            white-space: nowrap
        }

        .master-edit-crumb a {
            color: #91a1b8;
            text-decoration: none
        }

        .master-edit-crumb i {
            font-size: .48rem;
            color: #9aa9bc
        }

        .master-edit-heading {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: .5rem;
            padding: 1.15rem 0 .85rem
        }

        .master-edit-heading h1 {
            margin: 0;
            font-size: 1rem
        }

        .master-edit-heading p {
            max-width: 380px;
            margin: .3rem 0 0;
            color: #71829c;
            font-size: .75rem;
            line-height: 1.55
        }

        .master-edit-heading>span {
            padding: .4rem .6rem;
            color: #0875ad;
            background: #e7f5ff;
            border: 1px solid #9bd9fa;
            border-radius: 999px;
            font-size: .65rem;
            white-space: normal;
            text-align: center
        }

        .master-edit-breadcrumb,
        .master-edit-aside {
            display: none;
        }

        .master-edit-card {
            margin-bottom: 1rem;
            padding: 1rem 1rem .9rem;
            background: #fff;
            border: 1px solid #dfe8f1;
            border-radius: 17px;
            box-shadow: 0 4px 14px #2334550a
        }

        .master-edit-title {
            display: flex;
            align-items: center;
            gap: .65rem;
            padding-bottom: .8rem;
            margin-bottom: .85rem;
            border-bottom: 1px solid #e8eef5
        }

        .master-edit-title>b {
            display: grid;
            place-items: center;
            width: 29px;
            height: 29px;
            flex: 0 0 29px;
            color: #fff;
            background: #078dcc;
            border-radius: 50%;
            font-size: .8rem;
            font-weight: 500
        }

        .master-edit-title h2 {
            margin: 0;
            font-size: .78rem;
            letter-spacing: .04em
        }

        .master-edit-title p {
            margin: .18rem 0 0;
            color: #91a1b8;
            font-size: .65rem
        }

        .master-edit-field {
            margin-bottom: .85rem
        }

        .master-edit-field:last-child {
            margin-bottom: 0
        }

        .master-edit-field label {
            display: flex;
            align-items: center;
            gap: .2rem;
            margin-bottom: .38rem;
            color: #34445e;
            font-size: .7rem;
            font-weight: 600
        }

        .master-edit-field label em {
            color: #e54862;
            font-style: normal
        }

        .master-edit-field label span {
            color: #91a1b8;
            font-weight: 400
        }

        .master-edit-field label small {
            margin-left: auto;
            padding: .25rem .4rem;
            color: #91a1b8;
            background: #f0f4f8;
            border-radius: 5px;
            font-size: .58rem;
            font-weight: 400
        }

        .master-edit-field input,
        .master-edit-field select {
            box-sizing: border-box;
            width: 100%;
            height: 40px;
            padding: 0 .75rem;
            color: #263653;
            background: #f9fbfd;
            border: 1px solid #c8d8e7;
            border-radius: 12px;
            outline: 0;
            font: inherit;
            font-size: .72rem
        }

        .master-edit-field input:focus,
        .master-edit-field select:focus {
            border-color: #0b91ce;
            box-shadow: 0 0 0 3px #0b91ce16
        }

        .master-edit-field input.invalid,
        .master-edit-field select.invalid {
            border-color: #e54862
        }

        .edit-input-icon {
            position: relative
        }

        .edit-input-icon>i {
            position: absolute;
            left: .75rem;
            top: 50%;
            transform: translateY(-50%);
            color: #91a1b8;
            font-size: .8rem
        }

        .edit-input-icon input {
            padding-left: 2.25rem
        }

        .edit-input-icon input:not([type="email"]) {
            padding-left: .9rem
        }

        .edit-input-icon input[type="email"] {
            padding-left: 2.25rem
        }

        .edit-input-icon>i.fa-pen {
            left: auto;
            right: .75rem
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

        .edit-help {
            display: block;
            margin-top: .35rem;
            color: #91a1b8;
            font-size: .61rem;
            line-height: 1.4
        }

        .edit-error {
            display: block;
            margin-top: .3rem;
            color: #e54862;
            font-size: .62rem
        }

        .master-edit-note {
            display: flex;
            gap: .45rem;
            margin-top: .45rem;
            padding: .6rem .7rem;
            color: #0875ad;
            background: #eaf7ff;
            border: 1px solid #cbeaff;
            border-radius: 10px;
            font-size: .62rem;
            line-height: 1.45
        }

        .master-edit-note i {
            margin-top: .12rem
        }

        .master-edit-two {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .65rem
        }

        .master-edit-actions {
            display: grid;
            gap: .5rem;
            margin-top: .4rem
        }

        .master-edit-actions button,
        .master-edit-actions a {
            width: 100%;
            box-sizing: border-box;
            padding: .8rem;
            text-align: center;
            border-radius: 11px;
            font: inherit;
            font-size: .76rem;
            text-decoration: none
        }

        .master-edit-actions button {
            color: #fff;
            background: #078dcc;
            border: 0;
            box-shadow: 0 4px 9px #078dcc35
        }

        .master-edit-actions a {
            color: #526480;
            background: #fff;
            border: 1px solid #dce5ee
        }

        @media(max-width:380px) {
            body.role-master .master-pegawai-edit {
                padding-inline: .75rem
            }

            .master-edit-topbar {
                margin-inline: -.75rem;
                padding-inline: .75rem
            }

            .master-edit-crumb a,
            .master-edit-crumb i {
                display: none
            }

            .master-edit-heading h1 {
                font-size: 1.2rem
            }

            .master-edit-heading p {
                font-size: .68rem
            }

            .master-edit-card {
                padding-inline: .8rem
            }

            .master-edit-two {
                gap: .45rem
            }

            .master-edit-field input,
            .master-edit-field select {
                font-size: .67rem
            }
        }

        @media (min-width: 768px) {
            .master-edit-heading {
                display: flex;
                align-items: flex-end;
                justify-content: space-between;
                gap: 1rem;
                padding: .75rem 0 1.1rem;
            }

            .master-edit-breadcrumb {
                display: flex;
                align-items: center;
                gap: .45rem;
                margin-bottom: .45rem;
                color: #718092;
                font-size: .65rem;
            }

            .master-edit-breadcrumb a {
                color: #718092;
                text-decoration: none;
            }

            .master-edit-breadcrumb a:hover {
                color: #1769d2;
            }

            .master-edit-breadcrumb i {
                color: #a0acb9;
                font-size: .5rem;
            }

            .master-edit-heading h1 {
                color: #1b2938;
                font-size: 1.4rem;
                font-weight: 700;
            }

            .master-edit-heading p {
                max-width: 560px;
                margin-top: .25rem;
                font-size: .76rem;
            }

            .master-edit-layout {
                display: grid;
                grid-template-columns: minmax(230px, .7fr) minmax(0, 1.8fr);
                align-items: start;
                gap: 1rem;
            }

            .master-edit-aside {
                display: grid;
                gap: .8rem;
                min-width: 0;
            }

            .master-edit-profile-card {
                padding: .9rem;
                background: #fff;
                border: 1px solid #e2e8ef;
                border-radius: 7px;
            }

            .master-edit-profile-top {
                display: flex;
                align-items: center;
                gap: .65rem;
                min-width: 0;
                padding-bottom: .8rem;
                border-bottom: 1px solid #edf1f5;
            }

            .master-edit-avatar {
                display: grid;
                place-items: center;
                width: 42px;
                height: 42px;
                flex: 0 0 42px;
                color: #fff;
                background: #078dcc;
                border-radius: 8px;
                font-size: .82rem;
                font-weight: 700;
            }

            .master-edit-profile-top>div {
                min-width: 0;
            }

            .master-edit-profile-top strong,
            .master-edit-profile-top small {
                display: block;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .master-edit-profile-top strong {
                color: #25364a;
                font-size: .78rem;
            }

            .master-edit-profile-top small {
                margin-top: .15rem;
                color: #8290a0;
                font-size: .62rem;
            }

            .master-edit-profile-status {
                display: inline-flex;
                align-items: center;
                gap: .3rem;
                margin-top: .7rem;
                padding: .28rem .42rem;
                border-radius: 999px;
                font-size: .6rem;
            }

            .master-edit-profile-status i {
                font-size: .4rem;
            }

            .master-edit-profile-status.is-active {
                color: #087c50;
                background: #e6f7ef;
            }

            .master-edit-profile-status.is-inactive {
                color: #a96800;
                background: #fff5df;
            }

            .master-edit-profile-details {
                display: grid;
                gap: .6rem;
                padding-top: .75rem;
            }

            .master-edit-profile-details>div small,
            .master-edit-profile-details>div strong {
                display: block;
            }

            .master-edit-profile-details small {
                margin-bottom: .12rem;
                color: #8290a0;
                font-size: .59rem;
            }

            .master-edit-profile-details strong {
                color: #40546a;
                font-size: .66rem;
                font-weight: 600;
                overflow-wrap: anywhere;
            }

            .master-edit-aside-note {
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

            .master-edit-aside-note>i {
                margin-top: .1rem;
            }

            .master-edit-layout>form {
                min-width: 0;
            }

            .master-edit-card {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: .1rem 1rem;
                padding: 1rem;
                margin-bottom: .8rem;
                border-radius: 7px;
                box-shadow: none;
            }

            .master-edit-title {
                grid-column: 1 / -1;
                gap: .6rem;
                margin-bottom: .65rem;
                padding-bottom: .7rem;
            }

            .master-edit-title>b {
                width: 28px;
                height: 28px;
                flex-basis: 28px;
                border-radius: 6px;
            }

            .master-edit-title h2 {
                color: #25364a;
                font-size: .82rem;
                letter-spacing: 0;
            }

            .master-edit-title p {
                font-size: .63rem;
            }

            .master-edit-field {
                min-width: 0;
                margin-bottom: .7rem;
            }

            .master-edit-field label {
                font-size: .67rem;
            }

            .master-edit-field input,
            .master-edit-field select {
                height: 38px;
                background: #fff;
                border-radius: 5px;
                font-size: .68rem;
            }

            .master-edit-note {
                border-radius: 5px;
            }

            .master-edit-two {
                grid-column: 1 / -1;
                gap: .8rem;
            }

            .master-edit-two .master-edit-field {
                margin-bottom: .1rem;
            }

            .master-edit-actions {
                display: flex;
                justify-content: flex-end;
                gap: .55rem;
                margin-top: .25rem;
            }

            .master-edit-actions button,
            .master-edit-actions a {
                width: auto;
                min-width: 110px;
                padding: .65rem .85rem;
                border-radius: 5px;
                font-size: .7rem;
            }

            .master-edit-actions button {
                order: 2;
                background: #1769d2;
                box-shadow: none;
            }
        }

        @media (min-width: 768px) and (max-width: 1050px) {
            .master-edit-layout {
                grid-template-columns: minmax(200px, .65fr) minmax(0, 1.7fr);
                gap: .7rem;
            }

            .master-edit-card {
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
                const supervisorRole = role.value === 'pegawai' ? 'atasan' : role.value === 'atasan' ? 'kepala_bagian' : role.value === 'kepala_bagian' ? 'direktur' : null;
                Array.from(jabatan.options).forEach(function(option) {
                    if (option.value) option.hidden = !!id && option.dataset.divisi !== id;
                });
                Array.from(atasan.options).forEach(function(option) {
                    const wrongDivision = supervisorRole !== 'direktur' && !!id && option.dataset.divisi !== id;
                    option.hidden = !!option.value && (!supervisorRole || wrongDivision || option.dataset.role !== supervisorRole);
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
