@extends('layouts.app')
@section('title', 'Edit Periode Penilaian')
@section('content')
    <div class="master-periode-form-page">

        <header class="master-periode-form-heading">
            <div>
                <h1>
                    <i class="fa-solid fa-calendar-check"></i>
                    Edit Periode Penilaian
                </h1>
                <p>Perbarui rentang waktu dan konfigurasi siklus penilaian kinerja pegawai.</p>
            </div>
        </header>

        <section class="master-periode-form-card">
            <form action="{{ route('master.periode-penilaian.update', $periodePenilaian) }}" method="POST">@csrf
                @method('PUT')
                <div class="master-periode-form-field"><label for="nama_periode">Nama Periode <b>*</b><small>Format: Bulan
                            Tahun</small></label><input type="text" name="nama_periode" id="nama_periode"
                        value="{{ old('nama_periode', $periodePenilaian->nama_periode) }}"
                        class="@error('nama_periode') is-invalid @enderror">
                    @error('nama_periode')
                        <em>{{ $message }}</em>
                    @enderror
                </div>
                <div class="master-periode-form-date">
                    <div class="master-periode-form-field"><label for="tanggal_mulai">Tanggal Mulai <b>*</b></label><input
                            type="date" name="tanggal_mulai" id="tanggal_mulai"
                            value="{{ old('tanggal_mulai', \Carbon\Carbon::parse($periodePenilaian->tanggal_mulai)->format('Y-m-d')) }}"
                            class="@error('tanggal_mulai') is-invalid @enderror">
                        <p>Mulai aktif</p>
                        @error('tanggal_mulai')
                            <em>{{ $message }}</em>
                        @enderror
                    </div>
                    <div class="master-periode-form-field"><label for="tanggal_selesai">Tanggal Selesai
                            <b>*</b></label><input type="date" name="tanggal_selesai" id="tanggal_selesai"
                            value="{{ old('tanggal_selesai', \Carbon\Carbon::parse($periodePenilaian->tanggal_selesai)->format('Y-m-d')) }}"
                            class="@error('tanggal_selesai') is-invalid @enderror">
                        <p>Batas akhir</p>
                        @error('tanggal_selesai')
                            <em>{{ $message }}</em>
                        @enderror
                    </div>
                </div>
                <div class="master-periode-estimate"><i class="fa-regular fa-clock"></i><span>Estimasi
                        Durasi:</span><strong>Perbarui tanggal</strong></div>
                <div class="master-periode-form-field"><label for="status">Status <b>*</b></label><select name="status"
                        id="status" class="@error('status') is-invalid @enderror">
                        <option value="draft" {{ old('status', $periodePenilaian->status) === 'draft' ? 'selected' : '' }}>
                            Draft (Belum
                            Dipublikasi)</option>
                        <option value="berjalan"
                            {{ old('status', $periodePenilaian->status) === 'berjalan' ? 'selected' : '' }}>Berjalan
                        </option>
                        <option value="final" {{ old('status', $periodePenilaian->status) === 'final' ? 'selected' : '' }}>
                            Final</option>
                    </select>
                    <p>Status menentukan apakah periode dapat menjadi acuan penilaian aktif.</p>
                    @error('status')
                        <em>{{ $message }}</em>
                    @enderror
                </div>
                <div class="master-periode-info"><i class="fa-solid fa-circle-info"></i>
                    <p><strong>Informasi Siklus:</strong> Perubahan periode akan digunakan sebagai acuan absensi, logbook,
                        dan kalkulasi Tukin sesuai statusnya.</p>
                </div>
                <div class="master-periode-form-actions"><button type="submit"><i class="fa-solid fa-floppy-disk"></i>
                        Perbarui Periode</button><a href="{{ route('master.periode-penilaian.index') }}">Batal</a></div>
            </form>
        </section>

    </div>
@endsection


@push('styles')
    <style>
        body.role-master .master-periode-form-page {
            max-width: 560px;
            margin: auto;
            padding: 1rem 1rem 0;
            color: #14223d
        }

        .master-periode-form-crumb {
            display: flex;
            gap: .45rem;
            align-items: center;
            margin-bottom: 1rem;
            font-size: .68rem;
            white-space: nowrap
        }

        .master-periode-form-crumb a {
            color: #0875ad;
            text-decoration: none
        }

        .master-periode-form-crumb i {
            font-size: .45rem;
            color: #9caabd
        }

        .master-periode-form-heading {
            display: flex;
            justify-content: space-between;
            gap: .6rem;
            margin-bottom: .9rem
        }

        .master-periode-form-heading h1 {
            margin: 0;
            font-size: 1rem
        }

        .master-periode-form-heading p {
            margin: .35rem 0;
            color: #71829c;
            font-size: .75rem;
            line-height: 1.5
        }

        .master-periode-form-heading>a {
            height: max-content;
            padding: .55rem .7rem;
            color: #14223d;
            background: #fff;
            border: 1px solid #d4dfeb;
            border-radius: 11px;
            text-decoration: none;
            font-size: .7rem;
            white-space: nowrap
        }

        .master-periode-edit-status {
            display: flex;
            gap: .7rem;
            align-items: center;
            margin-bottom: 1.1rem;
            font-size: .68rem
        }

        .master-periode-edit-status span {
            padding: .4rem .7rem;
            color: #078dcc;
            background: #eff9ff;
            border: 1px solid #bce7ff;
            border-radius: 999px
        }

        .master-periode-edit-status span i {
            font-size: .4rem
        }

        .master-periode-edit-status em {
            color: #91a1b8;
            font-style: normal
        }

        .master-periode-form-card {
            padding: 1.2rem 1rem;
            background: #fff;
            border: 1px solid #e0e8f1;
            border-radius: 20px;
            box-shadow: 0 5px 16px #2334550b
        }

        .master-periode-form-field {
            margin-bottom: 1rem
        }

        .master-periode-form-field label {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: .45rem;
            font-size: .76rem
        }

        .master-periode-form-field label b {
            color: #e8475f
        }

        .master-periode-form-field label small {
            color: #91a1b8;
            font-size: .68rem;
            font-weight: 400
        }

        .master-periode-form-field input,
        .master-periode-form-field select {
            width: 100%;
            min-height: 48px;
            padding: .7rem .8rem;
            color: #14223d;
            background: #fff;
            border: 1px solid #c9d6e5;
            border-radius: 12px;
            box-sizing: border-box;
            font: inherit;
            font-size: .77rem
        }

        .master-periode-form-field p {
            margin: .4rem 0 0;
            color: #91a1b8;
            font-size: .68rem
        }

        .master-periode-form-field em {
            display: block;
            margin-top: .25rem;
            color: #dc3545;
            font-size: .66rem;
            font-style: normal
        }

        .master-periode-form-date {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .8rem
        }

        .master-periode-estimate {
            display: flex;
            align-items: center;
            gap: .55rem;
            margin: 0 0 1.1rem;
            padding: .7rem;
            color: #0875ad;
            background: #effaff;
            border: 1px solid #bce7ff;
            border-radius: 12px;
            font-size: .75rem
        }

        .master-periode-estimate strong {
            margin-left: auto;
            padding: .35rem .5rem;
            color: #07527c;
            background: #fff;
            border: 1px solid #bce7ff;
            border-radius: 7px;
            font-size: .68rem
        }

        .master-periode-info {
            display: flex;
            gap: .7rem;
            padding: .9rem;
            margin: 1rem 0;
            color: #526b8c;
            background: #effaff;
            border-left: 4px solid #078dcc;
            border-radius: 0 14px 14px 0
        }

        .master-periode-info>i {
            color: #078dcc
        }

        .master-periode-info p {
            margin: 0;
            font-size: .7rem;
            line-height: 1.55
        }

        .master-periode-info strong {
            color: #07527c
        }

        .master-periode-form-actions {
            display: grid;
            gap: .7rem
        }

        .master-periode-form-actions button,
        .master-periode-form-actions a {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .45rem;
            min-height: 51px;
            border-radius: 13px;
            text-decoration: none;
            font: inherit;
            font-size: .8rem
        }

        .master-periode-form-actions button {
            color: #fff;
            background: #078dcc;
            border: 0;
            box-shadow: 0 4px 8px #078dcc33;
            font-weight: 700
        }

        .master-periode-form-actions a {
            color: #14223d;
            background: #f3f6f9
        }

        .master-periode-delete {
            margin: 1.7rem 0;
            text-align: center
        }

        .master-periode-delete button {
            color: #f03d5c;
            background: none;
            border: 0;
            font: inherit;
            font-size: .76rem
        }

        @media(max-width:380px) {
            body.role-master .master-periode-form-page {
                padding-inline: .75rem
            }

            .master-periode-form-heading h1 {
                font-size: 1.25rem
            }

            .master-periode-form-heading p {
                font-size: .69rem
            }

            .master-periode-form-card {
                padding-inline: .8rem
            }
        }
    </style>
@endpush
