@extends('layouts.app')

@section('title', 'Edit Divisi')

@section('content')
    <div class="master-divisi-edit">
        <header class="master-divisi-edit-heading">
            <div>
                <h1>
                    <i class="fa-solid fa-pen-to-square"></i>
                    Edit Divisi
                </h1>
                <p>Perbarui identitas dan informasi unit divisi kerja Perumdam Tirta Pinang.</p>
            </div>
        </header>


        <section class="master-divisi-edit-card">
            <div class="master-divisi-edit-header"><span><i class="fa-solid fa-building"></i></span>
                <h2>Informasi Unit Divisi</h2><code>ID: DIV-{{ str_pad($divisi->id, 3, '0', STR_PAD_LEFT) }}</code>
            </div>
            <form action="{{ route('master.divisi.update', $divisi) }}" method="POST">@csrf @method('PUT')<div
                    class="master-divisi-edit-field"><label for="nama_divisi">Nama Divisi <em>*</em><small>Wajib
                            diisi</small></label>
                    <div class="master-divisi-edit-input"><input type="text" name="nama_divisi" id="nama_divisi"
                            value="{{ old('nama_divisi', $divisi->nama_divisi) }}"
                            class="@error('nama_divisi') invalid @enderror"><i class="fa-solid fa-pen-to-square"></i></div>
                    <p><i class="fa-regular fa-circle-question"></i> Gunakan nama resmi unit kerja sesuai SK Direksi.</p>
                    @error('nama_divisi')
                        <b>{{ $message }}</b>
                    @enderror
                </div>
                <div class="master-divisi-edit-actions"><button type="submit"><i class="fa-regular fa-floppy-disk"></i>
                        Update</button><a href="{{ route('master.divisi.index') }}">Batal</a></div>
            </form>
        </section>
        <aside class="master-divisi-sync"><span><i class="fa-solid fa-circle-info"></i></span>
            <div><strong>Informasi Sinkronisasi</strong>
                <p>Perubahan nama divisi akan otomatis tersinkronisasi ke rekap kinerja seluruh pegawai yang terdaftar pada
                    unit kerja ini.</p>
            </div>
        </aside>
    </div>
@endsection

@push('styles')
    <style>
        body.role-master .master-divisi-edit {
            width: 100%;
            max-width: 560px;
            margin: auto;
            padding: 1rem 1rem 0;
            box-sizing: border-box;
            color: #14223d
        }

        .master-divisi-edit-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .6rem;
            margin: 0 -1rem;
            padding: .9rem 1rem;
            background: #fff;
            border-bottom: 1px solid #e3ebf3
        }

        .master-divisi-edit-topbar>a {
            order: 2;
            padding: .62rem .8rem;
            color: #526480;
            border: 1px solid #dce5ee;
            border-radius: 11px;
            text-decoration: none;
            font-size: .76rem;
            font-weight: 600;
            white-space: nowrap
        }

        .master-divisi-edit-crumb {
            display: flex;
            align-items: center;
            gap: .5rem;
            color: #0875ad;
            font-size: .68rem;
            white-space: nowrap
        }

        .master-divisi-edit-crumb a {
            color: #91a1b8;
            text-decoration: none
        }

        .master-divisi-edit-crumb i {
            font-size: .48rem;
            color: #9caabd
        }

        .master-divisi-edit-heading {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: .5rem;
            padding: 1.4rem 0 1.2rem
        }

        .master-divisi-edit-heading h1 {
            margin: 0;
            font-size: 1rem
        }

        .master-divisi-edit-heading p {
            max-width: 400px;
            margin: .4rem 0 0;
            color: #647895;
            font-size: .82rem;
            line-height: 1.55
        }

        .master-divisi-edit-heading>span {
            padding: .42rem .62rem;
            color: #087b5d;
            background: #dff9eb;
            border: 1px solid #9ceaca;
            border-radius: 999px;
            font-size: .65rem;
            white-space: nowrap
        }

        .master-divisi-edit-heading>span i {
            font-size: .4rem
        }

        .master-divisi-edit-card {
            overflow: hidden;
            background: #fff;
            border: 1px solid #dfe8f1;
            border-radius: 19px;
            box-shadow: 0 5px 17px #2334550b
        }

        .master-divisi-edit-header {
            display: flex;
            align-items: center;
            gap: .75rem;
            padding: 1.1rem 1.2rem;
            border-bottom: 1px solid #e8eef5;
            background: #fbfcfe
        }

        .master-divisi-edit-header>span {
            display: grid;
            place-items: center;
            width: 41px;
            height: 41px;
            flex: 0 0 41px;
            color: #078dcc;
            background: #e8f7ff;
            border-radius: 12px
        }

        .master-divisi-edit-header h2 {
            flex: 1;
            margin: 0;
            font-size: .9rem;
            letter-spacing: .03em
        }

        .master-divisi-edit-header code {
            padding: .45rem .55rem;
            color: #71829c;
            border: 1px solid #d9e3ed;
            border-radius: 6px;
            font-size: .65rem
        }

        .master-divisi-edit-card form {
            padding: 1.45rem 1.2rem 1.2rem
        }

        .master-divisi-edit-field label {
            display: flex;
            align-items: center;
            gap: .2rem;
            margin-bottom: .45rem;
            font-size: .8rem;
            font-weight: 600
        }

        .master-divisi-edit-field label em {
            color: #e54862;
            font-style: normal
        }

        .master-divisi-edit-field label small {
            margin-left: auto;
            color: #91a1b8;
            font-size: .68rem;
            font-weight: 400
        }

        .master-divisi-edit-input {
            position: relative
        }

        .master-divisi-edit-input input {
            box-sizing: border-box;
            width: 100%;
            height: 52px;
            padding: 0 2.8rem 0 1rem;
            color: #17243d;
            background: #fff;
            border: 1px solid #bfd0e1;
            border-radius: 15px;
            outline: 0;
            font: inherit;
            font-size: .86rem
        }

        .master-divisi-edit-input input:focus {
            border-color: #078dcc;
            box-shadow: 0 0 0 3px #078dcc18
        }

        .master-divisi-edit-input input.invalid {
            border-color: #e54862
        }

        .master-divisi-edit-input i {
            position: absolute;
            right: .95rem;
            top: 50%;
            transform: translateY(-50%);
            color: #078dcc
        }

        .master-divisi-edit-field>p {
            margin: .55rem 0 0;
            color: #91a1b8;
            font-size: .69rem;
            font-style: italic
        }

        .master-divisi-edit-field>p i {
            margin-right: .25rem;
            font-style: normal
        }

        .master-divisi-edit-field>b {
            display: block;
            margin-top: .35rem;
            color: #e54862;
            font-size: .68rem;
            font-weight: 500
        }

        .master-divisi-edit-actions {
            display: grid;
            gap: .55rem;
            margin-top: 1.55rem
        }

        .master-divisi-edit-actions button,
        .master-divisi-edit-actions a {
            box-sizing: border-box;
            width: 100%;
            padding: .86rem;
            text-align: center;
            border-radius: 13px;
            font: inherit;
            font-size: .83rem;
            text-decoration: none
        }

        .master-divisi-edit-actions button {
            color: #fff;
            background: #078dcc;
            border: 0;
            box-shadow: 0 4px 9px #078dcc35;
            font-weight: 600
        }

        .master-divisi-edit-actions a {
            color: #526480;
            background: #f2f6fa;
            border: 1px solid #d5e0eb;
            font-weight: 600
        }

        .master-divisi-sync {
            display: flex;
            gap: .75rem;
            margin-top: 1rem;
            padding: 1.1rem 1rem;
            color: #0875ad;
            background: #eaf7ff;
            border: 1px solid #bfe7ff;
            border-radius: 17px
        }

        .master-divisi-sync>span {
            display: grid;
            place-items: center;
            width: 34px;
            height: 34px;
            flex: 0 0 34px;
            color: #0875ad;
            background: #d9f1ff;
            border-radius: 9px
        }

        .master-divisi-sync strong {
            display: block;
            margin: .1rem 0 .3rem;
            color: #07527c;
            font-size: .82rem
        }

        .master-divisi-sync p {
            margin: 0;
            color: #526b8c;
            font-size: .74rem;
            line-height: 1.55
        }

        .master-divisi-delete {
            margin: 1.25rem 0;
            text-align: center
        }

        .master-divisi-delete button {
            padding: .5rem;
            color: #e54862;
            background: none;
            border: 0;
            font: inherit;
            font-size: .78rem
        }

        .master-divisi-delete button i {
            margin-right: .25rem
        }

        @media(max-width:380px) {
            body.role-master .master-divisi-edit {
                padding-inline: .75rem
            }

            .master-divisi-edit-topbar {
                margin-inline: -.75rem;
                padding-inline: .75rem
            }

            .master-divisi-edit-crumb a,
            .master-divisi-edit-crumb i {
                display: none
            }

            .master-divisi-edit-heading {
                padding-top: 1.15rem
            }

            .master-divisi-edit-heading h1 {
                font-size: 1.3rem
            }

            .master-divisi-edit-heading p {
                font-size: .74rem
            }

            .master-divisi-edit-header,
            .master-divisi-edit-card form {
                padding-inline: 1rem
            }

            .master-divisi-edit-header h2 {
                font-size: .78rem
            }

            .master-divisi-edit-header code {
                font-size: .58rem
            }
        }
    </style>
@endpush
