@extends('layouts.app')

@section('title', 'Detail Pegawai')

@section('content')
    @php
        $role = $pegawai->user->role ?? 'pegawai';
        $isManager = in_array($role, ['atasan', 'kepala_bagian'], true);
        $roleLabel = match ($role) { 'kepala_bagian' => 'Kepala Bagian', 'atasan' => 'Atasan', default => 'Pegawai' };
        $initials = collect(explode(' ', trim($pegawai->nama)))
            ->filter()
            ->map(fn($part) => strtoupper(substr($part, 0, 1)))
            ->take(2)
            ->implode('');
        $tanggalMasuk = $pegawai->tanggal_masuk ? \Carbon\Carbon::parse($pegawai->tanggal_masuk) : null;
    @endphp
    <div class="master-pegawai-show">


        <header class="master-show-heading">
            <div>
                <nav class="master-show-breadcrumb" aria-label="Breadcrumb">
                    <a href="{{ route('master.dashboard') }}">Beranda</a>
                    <i class="fa-solid fa-chevron-right"></i>
                    <a href="{{ route('master.pegawai.index') }}">Data Pegawai</a>
                    <i class="fa-solid fa-chevron-right"></i>
                    <span>Detail Pegawai</span>
                </nav>
                <h1>
                    <i class="fa-solid fa-user"></i>
                    Detail Pegawai
                </h1>
                <p>Informasi lengkap akun, posisi, dan struktur kerja pegawai.</p>
            </div>
        </header>

        <section class="master-profile-card">
            <div class="master-profile-accent"></div>
            <div class="master-profile-main">
                <div class="master-profile-avatar">{{ $initials ?: 'PG' }}<span
                        class="{{ $pegawai->status === 'aktif' ? 'online' : 'offline' }}"></span></div>
                <div class="master-profile-heading">
                    <h2>{{ $pegawai->nama }}</h2>
                    <p>{{ $pegawai->jabatan->nama_jabatan ?? 'Jabatan belum diatur' }}</p>
                    <div class="master-profile-badges"><span class="role {{ $isManager ? 'atasan' : 'pegawai' }}"><i
                                class="fa-solid fa-circle"></i> Role: {{ $roleLabel }}</span><span
                            class="status {{ $pegawai->status === 'aktif' ? 'aktif' : 'nonaktif' }}"><i
                                class="fa-solid fa-circle"></i> {{ ucfirst($pegawai->status ?? 'nonaktif') }}</span></div>
                </div>
            </div>
            <div class="master-profile-contact">
                <div><span class="contact-icon email"><i class="fa-regular fa-envelope"></i></span>
                    <div><small>Email Akun</small><strong>{{ $pegawai->user->email ?? '-' }}</strong></div><button
                        type="button" class="copy-email" data-email="{{ $pegawai->user->email ?? '' }}">Salin</button>
                </div>
                <div><span class="contact-icon calendar"><i class="fa-regular fa-calendar"></i></span>
                    <div><small>Tanggal
                            Bergabung</small><strong>{{ $tanggalMasuk ? $tanggalMasuk->translatedFormat('d F Y') : '-' }}</strong>
                    </div>
                    @if ($tanggalMasuk)
                        <em>{{ $tanggalMasuk->diffForHumans(null, true) }}</em>
                    @endif
                </div>
            </div>
        </section>

        <div class="master-show-panels">
            <section class="master-show-card">
                <div class="master-section-title"><span class="section-icon blue"><i
                            class="fa-solid fa-building"></i></span>
                    <h2>Informasi Kepegawaian</h2><span class="section-tag">Struktur Organisasi</span>
                </div>
                <div class="master-detail-rows">
                    <div><span>Nama Lengkap</span><strong>{{ $pegawai->nama }}</strong></div>
                    <div><span>Divisi / Unit</span><strong
                            class="highlight">{{ $pegawai->jabatan->divisi->nama_divisi ?? '-' }}</strong></div>
                    <div><span>Jabatan</span><strong>{{ $pegawai->jabatan->nama_jabatan ?? '-' }}</strong></div>
                    <div><span>Atasan Langsung</span><strong class="muted"><i class="fa-solid fa-circle"></i>
                            {{ $pegawai->atasan->nama ?? '- (Direksi / Pimpinan Utama)' }}</strong></div>
                </div>
            </section>

            <section class="master-show-card master-subordinates">
                <div class="master-section-title"><span class="section-icon green"><i class="fa-solid fa-users"></i></span>
                    <h2>Bawahan Langsung</h2><span class="section-count">{{ $pegawai->bawahans->count() }} Orang</span>
                </div>
                <p class="master-section-description">Daftar pegawai dan atasan yang melapor langsung kepada pegawai ini.</p>
                <div class="master-subordinate-list">
                    @forelse($pegawai->bawahans as $bawahan)
                        <div class="master-subordinate-item"><span
                                class="subordinate-avatar">{{ collect(explode(' ', trim($bawahan->nama)))->filter()->map(fn($part) => strtoupper(substr($part, 0, 1)))->take(2)->implode('') }}</span>
                            <div>
                                <strong>{{ $bawahan->nama }}</strong><span>{{ $bawahan->jabatan->nama_jabatan ?? 'Pelaksana' }}</span><small
                                    class="{{ $bawahan->status === 'aktif' ? 'active' : '' }}">{{ ucfirst($bawahan->status ?? 'nonaktif') }}</small>
                            </div><i class="fa-solid fa-chevron-right"></i>
                    </div>@empty<div class="master-no-subordinate"><i class="fa-regular fa-folder-open"></i><span>Tidak
                                memiliki bawahan langsung.</span></div>
                    @endforelse
                </div>
            </section>
        </div>

        <div class="master-show-bottom-action">
            <a href="{{ route('master.pegawai.index') }}">
                <i class="fa-solid fa-arrow-left"></i>
                Kembali
            </a>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        body.role-master .master-pegawai-show {
            width: 100%;
            max-width: 560px;
            margin: auto;
            padding: 1rem 1rem 0;
            color: #14223d
        }

        .master-show-breadcrumb {
            display: none;
        }

        .master-show-panels {
            display: contents;
        }

        .master-show-heading {
            margin-bottom: 1.15rem
        }

        .master-show-heading h1 {
            display: flex;
            align-items: center;
            gap: .55rem;
            margin: 0;
            font-size: 1rem
        }

        .master-show-heading h1 i {

            font-size: 1.15rem
        }

        .master-show-heading p {
            margin: .35rem 0 0;
            color: #71829c;
            font-size: .78rem;
            line-height: 1.5
        }

        .master-show-bottom-action {
            margin: .25rem 0 1rem
        }

        .master-show-bottom-action a {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            padding: .75rem 1rem;
            color: #526480;
            background: #fff;
            border: 1px solid #cbd9e7;
            border-radius: 12px;
            font-size: .78rem;
            font-weight: 600;
            text-decoration: none
        }

        .master-show-bottom-action a:hover {
            color: #0875ad;
            border-color: #0875ad
        }

        .master-show-topbar {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            align-items: center;
            gap: .5rem;
            margin: 0 -1rem;
            padding: .8rem 1rem;
            background: #fff;
            border-bottom: 1px solid #e6edf5
        }

        .master-show-topbar>a {
            display: flex;
            align-items: center;
            gap: .45rem;
            color: #526480;
            text-decoration: none;
            font-size: .86rem;
            font-weight: 600
        }

        .master-show-topbar h1 {
            margin: 0;
            font-size: 1.05rem;
            text-align: center
        }

        .master-show-topbar .master-show-edit {
            justify-self: end;
            padding: .6rem .8rem;
            color: #fff;
            background: #0875ad;
            border-radius: 11px;
            font-size: .74rem
        }

        .master-show-breadcrumb {
            display: flex;
            align-items: center;
            gap: .55rem;
            margin: 1rem 0 .9rem;
            color: #7586a0;
            font-size: .72rem;
            white-space: nowrap
        }

        .master-show-breadcrumb a {
            color: #7586a0;
            text-decoration: none
        }

        .master-show-breadcrumb i {
            font-size: .55rem
        }

        .master-show-breadcrumb strong {
            color: #0875ad;
            overflow: hidden;
            text-overflow: ellipsis
        }

        .master-profile-card,
        .master-show-card {
            position: relative;
            overflow: hidden;
            margin-bottom: 1rem;
            background: #fff;
            border: 1px solid #e1e9f2;
            border-radius: 19px;
            box-shadow: 0 5px 18px #2334550c
        }

        .master-profile-accent {
            height: 7px;
            background: linear-gradient(90deg, #0875ad, #18b9b0)
        }

        .master-profile-main {
            display: flex;
            align-items: flex-start;
            gap: .85rem;
            padding: 1.25rem 1.2rem 1rem
        }

        .master-profile-avatar {
            position: relative;
            display: grid;
            place-items: center;
            width: 88px;
            height: 88px;
            flex: 0 0 88px;
            color: #fff;
            background: linear-gradient(145deg, #0875ad, #0e95cd);
            border: 3px solid #fff;
            border-radius: 20px;
            box-shadow: 0 4px 9px #0875ad35;
            font-size: 1.55rem;
            font-weight: 700
        }

        .master-profile-avatar>span {
            position: absolute;
            right: -3px;
            bottom: 0;
            width: 18px;
            height: 18px;
            border: 3px solid #fff;
            border-radius: 50%;
            background: #08b888
        }

        .master-profile-avatar>span.offline {
            background: #a9b5c5
        }

        .master-profile-heading {
            min-width: 0;
            padding-top: .1rem
        }

        .master-profile-heading h2 {
            margin: 0;
            font-size: 1rem;
            overflow-wrap: anywhere
        }

        .master-profile-heading p {
            margin: .3rem 0 .7rem;
            color: #607492;
            font-size: .78rem;
            overflow-wrap: anywhere
        }

        .master-profile-badges {
            display: flex;
            flex-wrap: wrap;
            gap: .4rem
        }

        .master-profile-badges span {
            padding: .4rem .65rem;
            border-radius: 999px;
            font-size: .67rem;
            font-weight: 600
        }

        .master-profile-badges i {
            font-size: .45rem
        }

        .master-profile-badges .role.atasan {
            color: #a55d00;
            background: #fff3ca
        }

        .master-profile-badges .role.pegawai {
            color: #0875ad;
            background: #e7f5ff
        }

        .master-profile-badges .status.aktif {
            color: #087b5d;
            background: #dff9eb
        }

        .master-profile-badges .status.nonaktif {
            color: #68758a;
            background: #edf1f5
        }

        .master-profile-contact {
            margin: 0 1.2rem 1.2rem;
            padding-top: 1rem;
            border-top: 1px solid #e8eef5;
            display: grid;
            gap: .65rem
        }

        .master-profile-contact>div {
            display: flex;
            align-items: center;
            gap: .65rem;
            padding: .65rem .75rem;
            background: #f7f9fc;
            border-radius: 14px;
            min-width: 0
        }

        .contact-icon {
            display: grid;
            place-items: center;
            width: 32px;
            height: 32px;
            flex: 0 0 32px;
            border-radius: 9px
        }

        .contact-icon.email {
            color: #0875ad;
            background: #e1f2ff
        }

        .contact-icon.calendar {
            color: #626fe1;
            background: #e9eaff
        }

        .master-profile-contact>div>div {
            min-width: 0;
            flex: 1
        }

        .master-profile-contact small,
        .master-profile-contact strong {
            display: block
        }

        .master-profile-contact small {
            margin-bottom: .15rem;
            color: #92a2b9;
            font-size: .65rem
        }

        .master-profile-contact strong {
            font-size: .78rem;
            overflow-wrap: anywhere
        }

        .master-profile-contact button,
        .master-profile-contact em {
            margin-left: auto;
            color: #0875ad;
            border: 0;
            background: none;
            font-size: .68rem;
            font-style: normal;
            font-weight: 600;
            white-space: nowrap
        }

        .master-profile-contact em {
            padding: .45rem .55rem;
            color: #526480;
            background: #eaf0f7;
            border-radius: 10px
        }

        .master-show-card {
            padding: 1.15rem 1.2rem
        }

        .master-section-title {
            display: flex;
            align-items: center;
            gap: .65rem
        }

        .section-icon {
            display: grid;
            place-items: center;
            width: 32px;
            height: 32px;
            flex: 0 0 32px;
            border-radius: 10px
        }

        .section-icon.blue {
            color: #0875ad;
            background: #e4f4ff
        }

        .section-icon.green {
            color: #08a778;
            background: #e4faef
        }

        .master-section-title h2 {
            margin: 0;
            flex: 1;
            font-size: .92rem;
            letter-spacing: .02em
        }

        .section-tag,
        .section-count {
            padding: .45rem .6rem;
            color: #526480;
            background: #f0f4f8;
            border-radius: 10px;
            font-size: .65rem;
            white-space: nowrap
        }

        .section-count {
            color: #0875ad;
            background: #e8f6ff
        }

        .master-detail-rows {
            margin-top: .8rem
        }

        .master-detail-rows>div {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: .8rem;
            padding: .8rem 0;
            border-bottom: 1px solid #e7edf4;
            font-size: .76rem
        }

        .master-detail-rows>div:last-child {
            border-bottom: 0;
            padding-bottom: .1rem
        }

        .master-detail-rows span {
            color: #91a1b8
        }

        .master-detail-rows strong {
            max-width: 62%;
            text-align: right;
            overflow-wrap: anywhere
        }

        .master-detail-rows strong.highlight {
            padding: .38rem .55rem;
            color: #0875ad;
            background: #e9f6ff;
            border-radius: 9px
        }

        .master-detail-rows strong.muted {
            color: #657895;
            font-weight: 500
        }

        .master-detail-rows strong.muted i {
            color: #c3cfdd;
            font-size: .5rem;
            vertical-align: middle
        }

        .master-detail-rows .mono {
            font-family: ui-monospace, SFMono-Regular, monospace;
            letter-spacing: .03em;
            font-weight: 500
        }

        .master-section-description {
            margin: .8rem 0 1rem;
            color: #607492;
            font-size: .76rem;
            line-height: 1.55
        }

        .master-subordinate-list {
            display: grid;
            gap: .6rem
        }

        .master-subordinate-item {
            display: flex;
            align-items: center;
            gap: .7rem;
            padding: .7rem;
            background: #fbfcfe;
            border: 1px solid #e0e8f1;
            border-radius: 14px
        }

        .subordinate-avatar {
            display: grid;
            place-items: center;
            width: 44px;
            height: 44px;
            flex: 0 0 44px;
            color: #526480;
            background: #e6edf6;
            border: 1px solid #c9d6e5;
            border-radius: 13px;
            font-size: .8rem;
            font-weight: 700
        }

        .master-subordinate-item>div {
            min-width: 0;
            flex: 1
        }

        .master-subordinate-item strong,
        .master-subordinate-item span,
        .master-subordinate-item small {
            display: block
        }

        .master-subordinate-item strong {
            font-size: .79rem;
            overflow-wrap: anywhere
        }

        .master-subordinate-item span {
            margin: .18rem 0;
            color: #607492;
            font-size: .7rem;
            overflow-wrap: anywhere
        }

        .master-subordinate-item small {
            width: max-content;
            padding: .2rem .4rem;
            color: #718096;
            background: #eef1f5;
            border-radius: 5px;
            font-size: .61rem
        }

        .master-subordinate-item small.active {
            color: #087b5d;
            background: #d9f8e9
        }

        .master-subordinate-item>i {
            color: #0875ad;
            font-size: .8rem
        }

        .master-no-subordinate {
            display: grid;
            place-items: center;
            gap: .35rem;
            padding: 1.4rem;
            color: #91a1b8;
            font-size: .75rem
        }

        .master-no-subordinate i {
            font-size: 1.5rem
        }

        @media(max-width:380px) {
            body.role-master .master-pegawai-show {
                padding-inline: .75rem
            }

            .master-show-topbar {
                margin-inline: -.75rem;
                padding-inline: .75rem
            }

            .master-show-topbar span {
                display: none
            }

            .master-show-topbar h1 {
                font-size: .95rem
            }

            .master-show-topbar .master-show-edit {
                padding: .55rem .65rem
            }

            .master-profile-main {
                padding-inline: 1rem
            }

            .master-profile-avatar {
                width: 72px;
                height: 72px;
                flex-basis: 72px;
                font-size: 1.2rem
            }

            .master-profile-heading h2 {
                font-size: 1rem
            }

            .master-profile-heading p {
                font-size: .7rem
            }

            .master-profile-contact {
                margin-inline: 1rem
            }

            .master-show-card {
                padding-inline: 1rem
            }

            .section-tag {
                display: none
            }

            .master-detail-rows>div {
                font-size: .7rem
            }

            .master-detail-rows strong {
                max-width: 58%
            }
        }

        @media (min-width: 768px) {
            body.role-master .master-pegawai-show {
                width: 100%;
                max-width: none;
                margin: 0;
                padding: 1rem clamp(1.25rem, 2.4vw, 2.5rem) 1.5rem;
                color: #1b2938;
            }

            .master-show-heading {
                margin-bottom: 1rem;
            }

            .master-show-breadcrumb {
                display: flex;
                align-items: center;
                gap: .4rem;
                margin-bottom: .45rem;
                color: #718092;
                font-size: .64rem;
            }

            .master-show-breadcrumb a {
                color: #718092;
                text-decoration: none;
            }

            .master-show-breadcrumb a:hover,
            .master-show-breadcrumb>span {
                color: #1769d2;
            }

            .master-show-breadcrumb i {
                color: #a0acb9;
                font-size: .48rem;
            }

            .master-show-heading h1 {
                color: #1b2938;
                font-size: 1.4rem;
                font-weight: 700;
            }

            .master-show-heading h1 i {
                color: #078dcc;
                font-size: 1rem;
            }

            .master-show-heading p {
                margin-top: .2rem;
                font-size: .74rem;
            }

            .master-profile-card,
            .master-show-card {
                margin-bottom: .9rem;
                border-color: #e2e8ef;
                border-radius: 9px;
                box-shadow: none;
            }

            .master-profile-card {
                display: grid;
                grid-template-columns: minmax(0, 1fr);
                margin-bottom: 1rem;
            }

            .master-profile-accent {
                height: 5px;
                background: linear-gradient(90deg, #0875ad, #18b9b0);
            }

            .master-profile-main {
                align-items: center;
                padding: 1.1rem 1.25rem .9rem;
            }

            .master-profile-avatar {
                width: 76px;
                height: 76px;
                flex-basis: 76px;
                border-radius: 12px;
                font-size: 1.35rem;
            }

            .master-profile-heading h2 {
                color: #1b2938;
                font-size: 1.1rem;
            }

            .master-profile-heading p {
                margin: .25rem 0 .55rem;
                font-size: .76rem;
            }

            .master-profile-badges {
                gap: .35rem;
            }

            .master-profile-badges span {
                padding: .3rem .48rem;
                font-size: .6rem;
            }

            .master-profile-contact {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: .65rem;
                margin: 0 1.25rem 1rem;
                padding-top: .8rem;
            }

            .master-profile-contact>div {
                min-height: 58px;
                padding: .55rem .65rem;
                background: #f7f9fc;
                border: 1px solid #edf1f5;
                border-radius: 7px;
            }

            .master-profile-contact strong {
                color: #25364a;
                font-size: .7rem;
            }

            .master-profile-contact em {
                padding: .3rem .42rem;
                border-radius: 999px;
                font-size: .59rem;
            }

            .master-show-panels {
                display: grid;
                grid-template-columns: minmax(0, 1.25fr) minmax(0, 1fr);
                align-items: stretch;
                gap: .9rem;
                margin-bottom: .85rem;
            }

            .master-show-panels>.master-show-card {
                min-width: 0;
                margin: 0;
                padding: 1rem;
            }

            .master-section-title {
                gap: .55rem;
                min-height: 34px;
            }

            .section-icon {
                width: 30px;
                height: 30px;
                flex-basis: 30px;
                border-radius: 6px;
            }

            .master-section-title h2 {
                color: #25364a;
                font-size: .8rem;
                letter-spacing: 0;
            }

            .section-tag,
            .section-count {
                padding: .3rem .45rem;
                border-radius: 999px;
                font-size: .58rem;
            }

            .master-detail-rows {
                margin-top: .65rem;
            }

            .master-detail-rows>div {
                padding: .62rem 0;
                font-size: .69rem;
            }

            .master-detail-rows strong {
                max-width: 68%;
            }

            .master-detail-rows strong.highlight {
                padding: .28rem .42rem;
                border-radius: 5px;
            }

            .master-section-description {
                margin: .4rem 0 .65rem;
                font-size: .67rem;
            }

            .master-subordinate-list {
                gap: .45rem;
            }

            .master-subordinate-item {
                gap: .55rem;
                padding: .55rem;
                border-radius: 6px;
            }

            .subordinate-avatar {
                width: 36px;
                height: 36px;
                flex-basis: 36px;
                border-radius: 7px;
                font-size: .68rem;
            }

            .master-subordinate-item strong {
                font-size: .7rem;
            }

            .master-subordinate-item span {
                font-size: .64rem;
            }

            .master-subordinate-item small {
                font-size: .57rem;
            }

            .master-no-subordinate {
                min-height: 130px;
                padding: 1rem;
                text-align: center;
            }

            .master-show-bottom-action {
                display: flex;
                justify-content: flex-end;
                padding-top: .75rem;
                margin: 0;
                border-top: 1px solid #e8eef5;
            }

            .master-show-bottom-action a {
                width: auto;
                min-width: 100px;
                padding: .6rem .8rem;
                border-radius: 6px;
                font-size: .68rem;
            }
        }

        @media (min-width: 768px) and (max-width: 1050px) {
            .master-show-panels {
                grid-template-columns: minmax(0, 1.1fr) minmax(0, 1fr);
                gap: .65rem;
            }

            .master-show-panels>.master-show-card {
                padding: .8rem;
            }
        }
    </style>
@endpush
