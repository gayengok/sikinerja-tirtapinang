@extends('layouts.app')
@section('title', 'Edit Kompetensi')
@section('content')
    <div class="master-kompetensi-edit">

        <header class="master-kompetensi-edit-heading">
            <div>
                <h1>
                    <i class="fa-solid fa-pen-to-square"></i>
                    Edit Kompetensi
                </h1>
                <p>Perbarui parameter standar perilaku dan ketentuan evaluasi pegawai.</p>
            </div>
        </header>

        <section class="master-kompetensi-edit-card">
            <div class="master-kompetensi-edit-title"><span><i class="fa-solid fa-pen-to-square"></i></span>
                <h2>Data Parameter</h2>

            </div>
            <form action="{{ route('master.master-kompetensi.update', $masterKompetensi) }}" method="POST">@csrf
                @method('PUT')
                <div class="master-kompetensi-edit-field"><label for="jabatan_id">Jabatan
                        <span>Opsional</span><small>Pilihan Target</small></label><select name="jabatan_id" id="jabatan_id"
                        class="@error('jabatan_id') invalid @enderror">
                        <option value="">Semua Jabatan</option>
                        @foreach ($jabatans as $jabatan)
                            <option value="{{ $jabatan->id }}"
                                {{ old('jabatan_id', $masterKompetensi->jabatan_id) == $jabatan->id ? 'selected' : '' }}>
                                {{ $jabatan->nama_jabatan }}</option>
                        @endforeach
                    </select>
                    <p>Berlaku umum untuk seluruh jenjang jabatan pegawai.</p>
                    @error('jabatan_id')
                        <b>{{ $message }}</b>
                    @enderror
                </div>
                <div class="master-kompetensi-edit-field"><label for="nama_kompetensi">Nama Kompetensi <em>*</em><small><i
                                class="fa-solid fa-check"></i> Terisi</small></label>
                    <div class="edit-kompetensi-input"><input type="text" name="nama_kompetensi" id="nama_kompetensi"
                            value="{{ old('nama_kompetensi', $masterKompetensi->nama_kompetensi) }}"
                            class="@error('nama_kompetensi') invalid @enderror"><i class="fa-solid fa-check"></i></div>
                    @error('nama_kompetensi')
                        <b>{{ $message }}</b>
                    @enderror
                </div>
                <div class="master-kompetensi-edit-field"><label for="definisi_perilaku">Definisi Perilaku
                        <em>*</em><small><span
                                id="definitionCount">{{ strlen(old('definisi_perilaku', $masterKompetensi->definisi_perilaku)) }}</span>
                            / 250 karakter</small></label>
                    <textarea name="definisi_perilaku" id="definisi_perilaku" rows="4"
                        class="@error('definisi_perilaku') invalid @enderror">{{ old('definisi_perilaku', $masterKompetensi->definisi_perilaku) }}</textarea>
                    @error('definisi_perilaku')
                        <b>{{ $message }}</b>
                    @enderror
                </div>
                <div class="master-kompetensi-edit-field"><label for="urutan">Urutan Prioritas <em>*</em><small>Tampilan
                            Evaluasi</small></label><input type="number" name="urutan" id="urutan"
                        value="{{ old('urutan', $masterKompetensi->urutan) }}" class="@error('urutan') invalid @enderror">
                    <p>Menentukan urutan tampilan pada lembar form evaluasi bulanan.</p>
                    @error('urutan')
                        <b>{{ $message }}</b>
                    @enderror
                </div>
                <div class="master-kompetensi-sync"><i class="fa-solid fa-circle-info"></i> Perubahan pada definisi atau
                    nama kompetensi akan langsung tersinkronisasi pada formulir evaluasi berkala atasan di periode aktif.
                </div>
                <div class="master-kompetensi-edit-actions"><button type="submit"><i class="fa-regular fa-floppy-disk"></i>
                        Update Kompetensi</button><a href="{{ route('master.master-kompetensi.index') }}">Batal</a></div>
            </form>

        </section>
    </div>
@endsection
@push('styles')
    <style>
        body.role-master .master-kompetensi-edit {
            width: 100%;
            max-width: 560px;
            margin: auto;
            padding: 1rem 1rem 0;
            box-sizing: border-box;
            color: #14223d
        }

        .master-kompetensi-edit-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .5rem;
            margin: 0 -1rem;
            padding: .9rem 1rem;
            background: #fff;
            border-bottom: 1px solid #e3ebf3
        }

        .master-kompetensi-edit-topbar>a {
            padding: .62rem .8rem;
            color: #0875ad;
            border: 1px solid #a9ddf8;
            border-radius: 11px;
            text-decoration: none;
            font-size: .76rem
        }

        .master-kompetensi-edit-crumb {
            display: flex;
            align-items: center;
            gap: .5rem;
            color: #526480;
            font-size: .68rem
        }

        .master-kompetensi-edit-crumb a {
            color: #91a1b8;
            text-decoration: none
        }

        .master-kompetensi-edit-crumb i {
            font-size: .45rem
        }

        .master-kompetensi-edit-heading {
            display: flex;
            justify-content: space-between;
            gap: .5rem;
            padding: 1.4rem 0 1.1rem
        }

        .master-kompetensi-edit-heading h1 {
            margin: 0;
            font-size: 1rem
        }

        .master-kompetensi-edit-heading p {
            margin: .35rem 0 0;
            color: #647895;
            font-size: .8rem;
            line-height: 1.5
        }

        .master-kompetensi-edit-heading>span {
            height: max-content;
            padding: .42rem .6rem;
            color: #b15c00;
            background: #fff3d4;
            border: 1px solid #ffd27b;
            border-radius: 999px;
            font-size: .64rem;
            white-space: nowrap
        }

        .master-kompetensi-edit-card {
            overflow: hidden;
            background: #fff;
            border: 1px solid #dfe8f1;
            border-radius: 19px;
            box-shadow: 0 5px 17px #2334550b
        }

        .master-kompetensi-edit-title {
            display: flex;
            align-items: center;
            gap: .7rem;
            padding: 1.1rem 1.2rem;
            border-bottom: 1px solid #e8eef5;
            background: #fbfcfe
        }

        .master-kompetensi-edit-title>span {
            display: grid;
            place-items: center;
            width: 41px;
            height: 41px;
            color: #078dcc;
            background: #e8f7ff;
            border-radius: 12px
        }

        .master-kompetensi-edit-title h2 {
            flex: 1;
            margin: 0;
            font-size: .9rem
        }

        .master-kompetensi-edit-title code {
            padding: .45rem .55rem;
            color: #71829c;
            background: #f0f4f8;
            border-radius: 6px;
            font-size: .65rem
        }

        .master-kompetensi-edit-card form {
            padding: 1.45rem 1.2rem 1.2rem
        }

        .master-kompetensi-edit-field {
            margin-bottom: .9rem
        }

        .master-kompetensi-edit-field label {
            display: flex;
            gap: .2rem;
            align-items: center;
            margin-bottom: .42rem;
            font-size: .78rem;
            font-weight: 600
        }

        .master-kompetensi-edit-field label em {
            color: #e54862;
            font-style: normal
        }

        .master-kompetensi-edit-field label span {
            color: #91a1b8;
            font-weight: 400
        }

        .master-kompetensi-edit-field label small {
            margin-left: auto;
            color: #91a1b8;
            font-size: .66rem;
            font-weight: 400
        }

        .master-kompetensi-edit-field label small i {
            color: #08a778
        }

        .master-kompetensi-edit-field input,
        .master-kompetensi-edit-field select,
        .master-kompetensi-edit-field textarea {
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

        .master-kompetensi-edit-field input,
        .master-kompetensi-edit-field select {
            height: 48px
        }

        .master-kompetensi-edit-field textarea {
            min-height: 130px;
            resize: vertical;
            line-height: 1.55
        }

        .master-kompetensi-edit-field input:focus,
        .master-kompetensi-edit-field select:focus,
        .master-kompetensi-edit-field textarea:focus {
            border-color: #078dcc;
            box-shadow: 0 0 0 3px #078dcc18
        }

        .master-kompetensi-edit-field .invalid {
            border-color: #e54862
        }

        .edit-kompetensi-input {
            position: relative
        }

        .edit-kompetensi-input input {
            padding-right: 2.4rem
        }

        .edit-kompetensi-input i {
            position: absolute;
            right: .85rem;
            top: 50%;
            transform: translateY(-50%);
            color: #08a778
        }

        .master-kompetensi-edit-field>p {
            margin: .4rem 0 0;
            color: #91a1b8;
            font-size: .67rem;
            font-style: italic
        }

        .master-kompetensi-edit-field>b {
            display: block;
            margin-top: .3rem;
            color: #e54862;
            font-size: .65rem;
            font-weight: 500
        }

        .master-kompetensi-sync {
            display: flex;
            gap: .55rem;
            padding: .75rem;
            margin: .1rem 0 1.2rem;
            color: #526b8c;
            background: #edf9ff;
            border: 1px solid #c4eaff;
            border-radius: 12px;
            font-size: .7rem;
            line-height: 1.5
        }

        .master-kompetensi-sync i {
            color: #0875ad
        }

        .master-kompetensi-edit-actions {
            display: grid;
            gap: .55rem
        }

        .master-kompetensi-edit-actions button,
        .master-kompetensi-edit-actions a {
            width: 100%;
            box-sizing: border-box;
            padding: .86rem;
            text-align: center;
            border-radius: 13px;
            font: inherit;
            font-size: .8rem;
            text-decoration: none
        }

        .master-kompetensi-edit-actions button {
            color: #fff;
            background: #078dcc;
            border: 0;
            box-shadow: 0 4px 9px #078dcc35;
            font-weight: 600
        }

        .master-kompetensi-edit-actions a {
            color: #526480;
            background: #fff;
            border: 1px solid #cbd9e7
        }

        .master-kompetensi-delete {
            padding: 1.1rem;
            text-align: center;
            border-top: 1px solid #e8eef5
        }

        .master-kompetensi-delete form {
            margin: 0
        }

        .master-kompetensi-delete button {
            color: #e54862;
            background: none;
            border: 0;
            font: inherit;
            font-size: .75rem
        }

        @media(max-width:380px) {
            body.role-master .master-kompetensi-edit {
                padding-inline: .75rem
            }

            .master-kompetensi-edit-topbar {
                margin-inline: -.75rem;
                padding-inline: .75rem
            }

            .master-kompetensi-edit-crumb a,
            .master-kompetensi-edit-crumb i {
                display: none
            }

            .master-kompetensi-edit-heading h1 {
                font-size: 1.3rem
            }

            .master-kompetensi-edit-heading p {
                font-size: .72rem
            }

            .master-kompetensi-edit-title,
            .master-kompetensi-edit-card form {
                padding-inline: 1rem
            }

            .master-kompetensi-edit-title h2 {
                font-size: .8rem
            }

            .master-kompetensi-edit-title code {
                font-size: .58rem
            }
        }
    </style>
@endpush
@push('scripts')
    <script>
        (function() {
            const textarea = document.getElementById('definisi_perilaku'),
                count = document.getElementById('definitionCount');
            if (textarea && count) textarea.addEventListener('input', function() {
                count.textContent = this.value.length;
            });
        })();
    </script>
@endpush
