@extends('layouts.app')
@section('title', 'Tambah Kompetensi')
@section('content')
    <div class="master-kompetensi-create">

        <header class="master-kompetensi-create-heading">
            <div>
                <h1>
                    <i class="fa-solid fa-user-plus"></i>
                    Tambah Kompetensi
                </h1>
                <p>Tambahkan parameter standar perilaku dan ketentuan evaluasi pegawai baru.</p>
            </div>
        </header>

        <section class="master-kompetensi-create-card">
            <div class="master-kompetensi-create-title"><span><i class="fa-regular fa-file-lines"></i></span>
                <h2>Formulir Parameter Baru</h2>
            </div>
            <form action="{{ route('master.master-kompetensi.store') }}" method="POST">@csrf
                <div class="master-kompetensi-create-field"><label for="jabatan_id">Jabatan
                        <span>Opsional</span><small>Pilihan Target</small></label><select name="jabatan_id" id="jabatan_id"
                        class="@error('jabatan_id') invalid @enderror">
                        <option value="">Semua Jabatan</option>
                        @foreach ($jabatans as $jabatan)
                            <option value="{{ $jabatan->id }}" {{ old('jabatan_id') == $jabatan->id ? 'selected' : '' }}>
                                {{ $jabatan->nama_jabatan }}</option>
                        @endforeach
                    </select>
                    <p>Kosongkan jika kompetensi berlaku untuk semua jabatan.</p>
                    @error('jabatan_id')
                        <b>{{ $message }}</b>
                    @enderror
                </div>
                <div class="master-kompetensi-create-field"><label for="nama_kompetensi">Nama Kompetensi
                        <em>*</em><small>Wajib diisi</small></label><input type="text" name="nama_kompetensi"
                        id="nama_kompetensi" value="{{ old('nama_kompetensi') }}"
                        placeholder="Contoh: Integritas, Kerja Sama, Inisiatif..."
                        class="@error('nama_kompetensi') invalid @enderror">
                    @error('nama_kompetensi')
                        <b>{{ $message }}</b>
                    @enderror
                </div>
                <div class="master-kompetensi-create-field"><label for="definisi_perilaku">Definisi Perilaku
                        <em>*</em><small><span id="definitionCount">0</span> / 250 karakter</small></label>
                    <textarea name="definisi_perilaku" id="definisi_perilaku" rows="4" maxlength="250"
                        placeholder="Uraikan deskripsi tindakan dan standar perilaku kerja yang diharapkan secara spesifik dan terukur..."
                        class="@error('definisi_perilaku') invalid @enderror">{{ old('definisi_perilaku') }}</textarea>
                    @error('definisi_perilaku')
                        <b>{{ $message }}</b>
                    @enderror
                </div>
                <div class="master-kompetensi-create-field master-kompetensi-order"><label for="urutan">Urutan Tampilan
                        <em>*</em><small>Urutan Prioritas Form</small></label>
                    <div><input type="number" name="urutan" id="urutan" value="{{ old('urutan', 0) }}"
                            class="@error('urutan') invalid @enderror">
                        <p>Menentukan nomor urut pada lembar formulir evaluasi bulanan pegawai.</p>
                    </div>
                    @error('urutan')
                        <b>{{ $message }}</b>
                    @enderror
                </div>
                <div class="master-kompetensi-create-actions"><button type="submit"><i
                            class="fa-regular fa-floppy-disk"></i> Simpan Kompetensi</button><a
                        href="{{ route('master.master-kompetensi.index') }}">Batal</a></div>
            </form>
        </section>
        <aside class="master-kompetensi-create-note"><i class="fa-solid fa-circle-info"></i>
            <div><strong>Ketentuan Master Data</strong>
                <p>Standar kompetensi yang ditambahkan akan otomatis terhubung ke instrumen penilaian perilaku kerja bulanan
                    untuk jabatan yang relevan.</p>
            </div>
        </aside>
    </div>
@endsection
@push('styles')
    <style>
        body.role-master .master-kompetensi-create {
            width: 100%;
            max-width: 560px;
            margin: auto;
            padding: 1rem 1rem 0;
            box-sizing: border-box;
            color: #14223d
        }

        .master-kompetensi-create-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .6rem;
            margin: 0 -1rem;
            padding: .9rem 1rem;
            background: #fff;
            border-bottom: 1px solid #e3ebf3
        }

        .master-kompetensi-create-topbar>a {
            order: 2;
            padding: .62rem .8rem;
            color: #526480;
            border: 1px solid #dce5ee;
            border-radius: 11px;
            text-decoration: none;
            font-size: .76rem;
            white-space: nowrap
        }

        .master-kompetensi-create-crumb {
            display: flex;
            align-items: center;
            gap: .5rem;
            color: #0875ad;
            font-size: .68rem;
            white-space: nowrap
        }

        .master-kompetensi-create-crumb a {
            color: #91a1b8;
            text-decoration: none
        }

        .master-kompetensi-create-crumb i {
            font-size: .48rem;
            color: #9caabd
        }

        .master-kompetensi-create-heading {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: .5rem;
            padding: 1.4rem 0 1.2rem
        }

        .master-kompetensi-create-heading h1 {
            margin: 0;
            font-size: 1rem
        }

        .master-kompetensi-create-heading p {
            max-width: 420px;
            margin: .4rem 0 0;
            color: #647895;
            font-size: .8rem;
            line-height: 1.55
        }

        .master-kompetensi-create-heading>span {
            padding: .42rem .62rem;
            color: #087b5d;
            background: #dff9eb;
            border: 1px solid #9ceaca;
            border-radius: 999px;
            font-size: .65rem;
            white-space: nowrap
        }

        .master-kompetensi-create-heading>span i {
            font-size: .4rem
        }

        .master-kompetensi-create-card {
            overflow: hidden;
            background: #fff;
            border: 1px solid #dfe8f1;
            border-radius: 19px;
            box-shadow: 0 5px 17px #2334550b
        }

        .master-kompetensi-create-title {
            display: flex;
            align-items: center;
            gap: .7rem;
            padding: 1.1rem 1.2rem;
            border-bottom: 1px solid #e8eef5;
            background: #fbfcfe
        }

        .master-kompetensi-create-title>span {
            display: grid;
            place-items: center;
            width: 41px;
            height: 41px;
            color: #078dcc;
            background: #e8f7ff;
            border-radius: 12px
        }

        .master-kompetensi-create-title h2 {
            flex: 1;
            margin: 0;
            font-size: .9rem;
            letter-spacing: .03em
        }

        .master-kompetensi-create-title code {
            padding: .45rem .55rem;
            color: #71829c;
            background: #f0f4f8;
            border-radius: 6px;
            font-size: .65rem
        }

        .master-kompetensi-create-card form {
            padding: 1.45rem 1.2rem 1.2rem
        }

        .master-kompetensi-create-field {
            margin-bottom: .9rem
        }

        .master-kompetensi-create-field label {
            display: flex;
            align-items: center;
            gap: .2rem;
            margin-bottom: .42rem;
            font-size: .78rem;
            font-weight: 600
        }

        .master-kompetensi-create-field label em {
            color: #e54862;
            font-style: normal
        }

        .master-kompetensi-create-field label span {
            color: #91a1b8;
            font-weight: 400
        }

        .master-kompetensi-create-field label small {
            margin-left: auto;
            color: #91a1b8;
            font-size: .66rem;
            font-weight: 400
        }

        .master-kompetensi-create-field input,
        .master-kompetensi-create-field select,
        .master-kompetensi-create-field textarea {
            box-sizing: border-box;
            width: 100%;
            padding: .75rem;
            color: #263653;
            background: #fff;
            border: 1px solid #cbd9e7;
            border-radius: 14px;
            outline: 0;
            font: inherit;
            font-size: .78rem
        }

        .master-kompetensi-create-field input,
        .master-kompetensi-create-field select {
            height: 48px
        }

        .master-kompetensi-create-field textarea {
            min-height: 140px;
            resize: vertical;
            line-height: 1.55
        }

        .master-kompetensi-create-field input:focus,
        .master-kompetensi-create-field select:focus,
        .master-kompetensi-create-field textarea:focus {
            border-color: #078dcc;
            box-shadow: 0 0 0 3px #078dcc18
        }

        .master-kompetensi-create-field input.invalid,
        .master-kompetensi-create-field select.invalid,
        .master-kompetensi-create-field textarea.invalid {
            border-color: #e54862
        }

        .master-kompetensi-create-field input::placeholder,
        .master-kompetensi-create-field textarea::placeholder {
            color: #94a6be
        }

        .master-kompetensi-create-field>p {
            margin: .42rem 0 0;
            color: #91a1b8;
            font-size: .68rem;
            font-style: italic
        }

        .master-kompetensi-create-field>b {
            display: block;
            margin-top: .3rem;
            color: #e54862;
            font-size: .65rem;
            font-weight: 500
        }

        .master-kompetensi-order>div {
            display: flex;
            align-items: center;
            gap: .75rem
        }

        .master-kompetensi-order>div input {
            max-width: 145px
        }

        .master-kompetensi-order>div p {
            margin: 0;
            color: #91a1b8;
            font-size: .68rem;
            line-height: 1.4
        }

        .master-kompetensi-create-actions {
            display: flex;
            gap: .7rem;
            margin-top: 1.4rem
        }

        .master-kompetensi-create-actions button,
        .master-kompetensi-create-actions a {
            box-sizing: border-box;
            padding: .86rem;
            text-align: center;
            border-radius: 13px;
            font: inherit;
            font-size: .8rem;
            text-decoration: none
        }

        .master-kompetensi-create-actions button {
            flex: 1;
            color: #fff;
            background: #078dcc;
            border: 0;
            box-shadow: 0 4px 9px #078dcc35;
            font-weight: 600
        }

        .master-kompetensi-create-actions a {
            min-width: 105px;
            color: #526480;
            background: #fff;
            border: 1px solid #cbd9e7
        }

        .master-kompetensi-create-note {
            display: flex;
            gap: .75rem;
            margin-top: 1rem;
            padding: 1.1rem 1rem;
            color: #0875ad;
            background: #eaf7ff;
            border: 1px solid #bfe7ff;
            border-radius: 17px
        }

        .master-kompetensi-create-note>i {
            margin-top: .2rem;
            font-size: 1rem
        }

        .master-kompetensi-create-note strong {
            display: block;
            margin: .1rem 0 .3rem;
            color: #07527c;
            font-size: .82rem
        }

        .master-kompetensi-create-note p {
            margin: 0;
            color: #526b8c;
            font-size: .73rem;
            line-height: 1.55
        }

        @media(max-width:380px) {
            body.role-master .master-kompetensi-create {
                padding-inline: .75rem
            }

            .master-kompetensi-create-topbar {
                margin-inline: -.75rem;
                padding-inline: .75rem
            }

            .master-kompetensi-create-crumb a,
            .master-kompetensi-create-crumb i {
                display: none
            }

            .master-kompetensi-create-heading h1 {
                font-size: 1.3rem
            }

            .master-kompetensi-create-heading p {
                font-size: .72rem
            }

            .master-kompetensi-create-title,
            .master-kompetensi-create-card form {
                padding-inline: 1rem
            }

            .master-kompetensi-create-title h2 {
                font-size: .76rem
            }

            .master-kompetensi-create-title code {
                font-size: .58rem
            }

            .master-kompetensi-order>div {
                align-items: flex-start;
                flex-direction: column;
                gap: .35rem
            }

            .master-kompetensi-order>div input {
                max-width: none
            }

            .master-kompetensi-create-actions {
                display: grid;
                gap: .5rem
            }

            .master-kompetensi-create-actions a {
                width: 100%
            }
        }
    </style>
@endpush
@push('scripts')
    <script>
        (function() {
            const textarea = document.getElementById('definisi_perilaku'),
                count = document.getElementById('definitionCount');
            if (textarea && count) {
                textarea.addEventListener('input', function() {
                    count.textContent = this.value.length;
                });
                count.textContent = textarea.value.length;
            }
        })();
    </script>
@endpush
