@extends('layouts.app')
@section('title', 'Tambah Template Laporan')

@section('content')
    <div class="master-template-form-page">
        <header class="master-template-form-heading">
            <div>
                <nav class="master-template-form-breadcrumb" aria-label="Breadcrumb">
                    <a href="{{ route('master.dashboard') }}">Beranda</a>
                    <i class="fa-solid fa-chevron-right"></i>
                    <a href="{{ route('master.template-laporan.index') }}">Master Data</a>
                    <i class="fa-solid fa-chevron-right"></i>
                    <span>Tambah Template</span>
                </nav>
                <h1>
                    <i class="fas fa-file-circle-plus"></i>
                    Tambah Template Laporan
                </h1>
                <p>Atur konfigurasi field pelaporan harian kinerja divisi dan integrasi KPI.</p>
            </div>
        </header>


        <section class="master-template-form-card">
            <div class="master-template-form-card-heading">
                <span><i class="fa-regular fa-file-lines"></i></span>
                <div>
                    <h2>Formulir Template Laporan</h2>
                    <p>Silakan lengkapi data konfigurasi atribut pelaporan dengan teliti</p>
                </div>
                <b>Mode Tambah</b>
            </div>
            <form action="{{ route('master.template-laporan.store') }}" method="POST">@csrf
                <div class="master-template-field"><label for="jabatan_id">Jabatan <b>*</b></label><select name="jabatan_id"
                        id="jabatan_id" class="@error('jabatan_id') is-invalid @enderror">
                        <option value="">-- Pilih Jabatan --</option>
                        @foreach ($jabatans as $jabatan)
                            <option value="{{ $jabatan->id }}"
                                {{ old('jabatan_id') == $jabatan->id ? 'selected' : '' }}>
                                {{ $jabatan->nama_jabatan }} ({{ $jabatan->divisi->nama_divisi }})</option>
                        @endforeach
                    </select>
                    <p>Pilih jabatan yang akan mengisi field laporan ini.</p>
                    @error('jabatan_id')
                        <em>{{ $message }}</em>
                    @enderror
                </div>
                <div class="master-template-field"><label for="nama_field">Nama Field <b>*</b></label><input type="text"
                        name="nama_field" id="nama_field" value="{{ old('nama_field') }}"
                        placeholder="Contoh: Jumlah Pelanggan Dilayani" class="@error('nama_field') is-invalid @enderror">
                    <p>Nama indikator aktivitas harian yang muncul pada form staf.</p>
                    @error('nama_field')
                        <em>{{ $message }}</em>
                    @enderror
                </div>
                <div class="master-template-split">
                    <div class="master-template-field"><label for="tipe_field">Tipe Field <b>*</b></label><select
                            name="tipe_field" id="tipe_field" class="@error('tipe_field') is-invalid @enderror">
                            <option value="angka" {{ old('tipe_field', 'angka') === 'angka' ? 'selected' : '' }}>Angka
                            </option>
                            <option value="teks" {{ old('tipe_field') === 'teks' ? 'selected' : '' }}>Teks</option>
                            <option value="dropdown" {{ old('tipe_field') === 'dropdown' ? 'selected' : '' }}>Dropdown
                            </option>
                        </select>
                        @error('tipe_field')
                            <em>{{ $message }}</em>
                        @enderror
                    </div>
                    <div class="master-template-field"><label for="satuan">Satuan <small>(Opsional)</small></label><input
                            type="text" name="satuan" id="satuan" value="{{ old('satuan') }}"
                            placeholder="pelanggan, meter" class="@error('satuan') is-invalid @enderror">
                        @error('satuan')
                            <em>{{ $message }}</em>
                        @enderror
                    </div>
                </div>

                <div class="master-template-dropdown-options" data-dropdown-options>
                    <label>Daftar Opsi Dropdown <small>(Opsional)</small></label>
                    <p>Isi pilihan yang dapat dipilih pegawai. Kosongkan jika field bukan Dropdown.</p>
                    <div data-option-list>
                        @foreach (old('opsi', ['']) as $option)
                            <div class="master-template-option">
                                <input type="text" name="opsi[]" value="{{ $option }}" maxlength="100"
                                    placeholder="Contoh: Selesai">
                                <button type="button" data-remove-option aria-label="Hapus opsi">&times;</button>
                            </div>
                        @endforeach
                    </div>
                    <button type="button" class="master-template-add-option" data-add-option>
                        <i class="fa-solid fa-plus"></i> Tambah Opsi
                    </button>
                    @error('opsi')
                        <em>{{ $message }}</em>
                    @enderror
                </div>


                <div class="master-template-field"><label for="terhubung_ke_kpi_id">KPI Terhubung
                        <small>(Opsional)</small></label><select name="terhubung_ke_kpi_id" id="terhubung_ke_kpi_id"
                        class="@error('terhubung_ke_kpi_id') is-invalid @enderror">
                        <option value="">-- Tidak Terhubung --</option>
                        @foreach ($masterKpis->groupBy(fn($kpi) => $kpi->jabatan_id) as $jabatanKpis)
                            <optgroup
                                label="{{ $jabatanKpis->first()->jabatan?->nama_jabatan ?? 'Jabatan belum ditentukan' }}">
                                @foreach ($jabatanKpis as $kpi)
                                    <option value="{{ $kpi->id }}" data-jabatan-id="{{ $kpi->jabatan_id }}"
                                        {{ old('terhubung_ke_kpi_id') == $kpi->id ? 'selected' : '' }}>
                                        {{ $kpi->indikator }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    <p>Pilih KPI dari jabatan yang sama agar nilai angka laporan ini dapat diakumulasi ke penilaian
                        kinerja bulanan. Pilihan akan menyesuaikan jabatan di atas.</p>
                    <p class="master-template-kpi-empty" data-kpi-empty-state hidden>
                        Belum ada KPI untuk jabatan ini.
                        <a href="{{ route('master.master-kpi.create') }}"
                            data-base-url="{{ route('master.master-kpi.create') }}" data-create-kpi-link>
                            Tambahkan KPI untuk jabatan ini
                        </a>
                    </p>
                    @error('terhubung_ke_kpi_id')
                        <em>{{ $message }}</em>
                    @enderror
                </div>


                <div class="master-template-actions"><button type="submit"><i class="fa-solid fa-floppy-disk"></i> Simpan
                    </button><a href="{{ route('master.template-laporan.index') }}">Batal</a></div>
            </form>
        </section>

    </div>
@endsection

@push('styles')
    <style>
        .master-template-dropdown-options {
            display: none;
            margin-bottom: 1rem;
            padding: .8rem;
            background: #f8fafc;
            border: 1px solid #dce5ee;
            border-radius: 12px
        }

        .master-template-kpi-empty {
            margin-top: .5rem;
            padding: .65rem .75rem;
            color: #825b16;
            background: #fff8e8;
            border: 1px solid #f1d89d;
            border-radius: 8px;
        }

        .master-template-kpi-empty a {
            display: inline-block;
            margin-left: .25rem;
            color: #0875ad;
            font-weight: 600;
        }

        .master-template-dropdown-options.is-visible {
            display: block
        }

        .master-template-dropdown-options>label {
            display: flex;
            justify-content: space-between;
            margin-bottom: .35rem;
            font-size: .76rem;
            font-weight: 700
        }

        .master-template-dropdown-options label small,
        .master-template-dropdown-options>p {
            color: #71829c;
            font-size: .67rem;
            font-weight: 400
        }

        .master-template-dropdown-options>p {
            margin: 0 0 .7rem
        }

        .master-template-option {
            display: flex;
            gap: .4rem;
            margin-bottom: .45rem
        }

        .master-template-option input {
            min-height: 40px;
            flex: 1;
            padding: .55rem .7rem;
            border: 1px solid #c9d6e5;
            border-radius: 9px
        }

        .master-template-option button {
            width: 40px;
            color: #f03d5c;
            background: #fff;
            border: 1px solid #f3bcc7;
            border-radius: 9px;
            font-size: 1.1rem
        }

        .master-template-add-option {
            padding: .45rem .65rem;
            color: #0875ad;
            background: #eff9ff;
            border: 1px solid #bce7ff;
            border-radius: 9px;
            font-size: .7rem
        }

        body.role-master .master-template-form-page {
            max-width: 560px;
            margin: auto;
            padding: 1rem 1rem 0;
            color: #14223d
        }

        .master-template-form-crumb {
            display: flex;
            gap: .45rem;
            align-items: center;
            margin-bottom: 1rem;
            font-size: .68rem;
            white-space: nowrap
        }

        .master-template-form-crumb a {
            color: #0875ad;
            text-decoration: none
        }

        .master-template-form-crumb i {
            font-size: .45rem;
            color: #9caabd
        }

        .master-template-form-heading {
            display: flex;
            justify-content: space-between;
            gap: .6rem;
            margin-bottom: 1.2rem
        }

        .master-template-form-heading h1 {
            margin: 0;
            font-size: 1rem
        }

        .master-template-form-heading p {
            margin: .35rem 0;
            color: #71829c;
            font-size: .75rem;
            line-height: 1.5
        }

        .master-template-form-heading>a {
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

        .master-template-form-card {
            padding: 1.2rem 1rem;
            background: #fff;
            border: 1px solid #e0e8f1;
            border-radius: 20px;
            box-shadow: 0 5px 16px #2334550b
        }

        .master-template-field {
            margin-bottom: 1rem
        }

        .master-template-field label {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: .45rem;
            font-size: .76rem
        }

        .master-template-field label b {
            color: #e8475f
        }

        .master-template-field label small {
            color: #71829c;
            font-size: .67rem;
            font-weight: 400
        }

        .master-template-field input,
        .master-template-field select {
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

        .master-template-field p {
            margin: .4rem 0 0;
            color: #91a1b8;
            font-size: .68rem;
            line-height: 1.45
        }

        .master-template-field em {
            display: block;
            margin-top: .25rem;
            color: #dc3545;
            font-size: .66rem;
            font-style: normal
        }

        .master-template-split {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .75rem
        }

        .master-template-warning {
            display: flex;
            gap: .65rem;
            margin: -.2rem 0 1rem;
            padding: .85rem;
            color: #a85d00;
            background: #fff9e9;
            border: 1px solid #ffd56a;
            border-radius: 14px
        }

        .master-template-warning i {
            margin-top: .15rem
        }

        .master-template-warning p {
            margin: 0;
            font-size: .7rem;
            line-height: 1.55
        }

        .master-template-warning strong {
            color: #875000
        }

        .master-template-actions {
            display: grid;
            gap: .7rem;
            margin-top: 1.1rem
        }

        .master-template-actions button,
        .master-template-actions a {
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

        .master-template-actions button {
            color: #fff;
            background: #078dcc;
            border: 0;
            box-shadow: 0 4px 8px #078dcc33;
            font-weight: 700
        }

        .master-template-actions a {
            color: #14223d;
            background: #f3f6f9
        }

        @media(max-width:380px) {
            body.role-master .master-template-form-page {
                padding-inline: .75rem
            }

            .master-template-form-heading h1 {
                font-size: 1.25rem
            }

            .master-template-form-heading p {
                font-size: .69rem
            }

            .master-template-form-card {
                padding-inline: .8rem
            }

            .master-template-split {
                gap: .5rem
            }
        }

        .master-template-form-breadcrumb,
        .master-template-form-card-heading {
            display: none;
        }

        @media (min-width: 768px) {
            body.role-master .master-template-form-page {
                width: 100%;
                max-width: none;
                margin: 0;
                padding: 1rem clamp(1.25rem, 2.4vw, 2.5rem) 2rem;
                color: #1b2938;
            }

            .master-template-form-heading {
                margin-bottom: .9rem;
            }

            .master-template-form-breadcrumb {
                display: flex;
                align-items: center;
                gap: .4rem;
                margin-bottom: .45rem;
                color: #718092;
                font-size: .64rem;
            }

            .master-template-form-breadcrumb a {
                color: #718092;
                text-decoration: none;
            }

            .master-template-form-breadcrumb>span,
            .master-template-form-breadcrumb a:hover {
                color: #1769d2;
            }

            .master-template-form-breadcrumb i {
                color: #a0acb9;
                font-size: .48rem;
            }

            .master-template-form-heading h1 {
                color: #1b2938;
                font-size: 1.4rem;
                font-weight: 700;
            }

            .master-template-form-heading h1 i {
                color: #078dcc;
                font-size: 1rem;
            }

            .master-template-form-heading p {
                margin-top: .22rem;
                font-size: .73rem;
            }

            .master-template-form-card {
                width: 100%;
                max-width: 1120px;
                margin: 0 auto;
                padding: 0;
                overflow: hidden;
                border-color: #e2e8ef;
                border-radius: 8px;
                box-shadow: none;
            }

            .master-template-form-card-heading {
                display: flex;
                align-items: center;
                gap: .6rem;
                padding: .8rem 1rem;
                background: #f9fbfd;
                border-bottom: 1px solid #e8eef5;
            }

            .master-template-form-card-heading>span {
                display: grid;
                place-items: center;
                width: 32px;
                height: 32px;
                flex: 0 0 32px;
                color: #078dcc;
                background: #e8f7ff;
                border-radius: 6px;
                font-size: .8rem;
            }

            .master-template-form-card-heading>div {
                min-width: 0;
            }

            .master-template-form-card-heading h2,
            .master-template-form-card-heading p {
                margin: 0;
            }

            .master-template-form-card-heading h2 {
                color: #25364a;
                font-size: .78rem;
            }

            .master-template-form-card-heading p {
                margin-top: .15rem;
                color: #8290a0;
                font-size: .61rem;
            }

            .master-template-form-card-heading>b {
                margin-left: auto;
                padding: .25rem .4rem;
                color: #087b5d;
                background: #ecfff5;
                border: 1px solid #b8efd1;
                border-radius: 999px;
                font-size: .56rem;
                white-space: nowrap;
            }

            .master-template-form-card-heading>b.is-editing {
                color: #a96800;
                background: #fff5df;
                border-color: #ffe2a8;
            }

            .master-template-form-card form {
                padding: 1rem;
            }

            .master-template-field {
                margin-bottom: .75rem;
            }

            .master-template-field label {
                margin-bottom: .32rem;
                color: #34445e;
                font-size: .66rem;
            }

            .master-template-field label small {
                font-size: .59rem;
            }

            .master-template-field input,
            .master-template-field select {
                min-height: 38px;
                padding: .45rem .7rem;
                border-radius: 5px;
                font-size: .68rem;
            }

            .master-template-field p {
                margin-top: .3rem;
                font-size: .6rem;
            }

            .master-template-field em {
                font-size: .6rem;
            }

            .master-template-split {
                gap: .8rem;
            }

            .master-template-dropdown-options {
                margin-bottom: .75rem;
                padding: .7rem;
                border-radius: 5px;
            }

            .master-template-dropdown-options>label {
                font-size: .67rem;
            }

            .master-template-dropdown-options>p {
                font-size: .6rem;
            }

            .master-template-option input {
                min-height: 36px;
                border-radius: 5px;
                font-size: .66rem;
            }

            .master-template-option button {
                width: 36px;
                border-radius: 5px;
            }

            .master-template-add-option {
                padding: .38rem .55rem;
                border-radius: 5px;
                font-size: .63rem;
            }

            .master-template-actions {
                display: flex;
                justify-content: flex-end;
                gap: .5rem;
                margin-top: .9rem;
                padding-top: .75rem;
                border-top: 1px solid #edf1f5;
            }

            .master-template-actions button,
            .master-template-actions a {
                width: auto;
                min-width: 95px;
                min-height: 38px;
                padding: .5rem .75rem;
                border-radius: 5px;
                font-size: .68rem;
            }

            .master-template-actions button {
                order: 2;
                box-shadow: none;
            }

            .master-template-actions a {
                order: 1;
            }
        }
    </style>
@endpush
@push('scripts')
    <script>
        (() => {
            const typeField = document.getElementById('tipe_field');
            const wrapper = document.querySelector('[data-dropdown-options]');
            const list = wrapper?.querySelector('[data-option-list]');
            const addButton = wrapper?.querySelector('[data-add-option]');
            const jobField = document.getElementById('jabatan_id');
            const kpiField = document.getElementById('terhubung_ke_kpi_id');
            const emptyState = document.querySelector('[data-kpi-empty-state]');
            const createKpiLink = document.querySelector('[data-create-kpi-link]');

            const syncVisibility = () => {
                if (!typeField || !wrapper) return;
                wrapper.classList.toggle('is-visible', typeField.value === 'dropdown');
            };

            addButton?.addEventListener('click', () => {
                const option = document.createElement('div');
                option.className = 'master-template-option';
                option.innerHTML =
                    '<input type="text" name="opsi[]" maxlength="100" placeholder="Contoh: Selesai">' +
                    '<button type="button" data-remove-option aria-label="Hapus opsi">&times;</button>';
                list.appendChild(option);
            });

            list?.addEventListener('click', (event) => {
                if (event.target.matches('[data-remove-option]')) {
                    event.target.closest('.master-template-option').remove();
                }
            });

            typeField?.addEventListener('change', syncVisibility);
            syncVisibility();

            const filterKpis = () => {
                if (!jobField || !kpiField || !emptyState || !createKpiLink) return;

                const jobId = jobField.value;
                let selectedIsVisible = false;
                let matchingKpiCount = 0;

                [...kpiField.options].forEach((option, index) => {
                    if (index === 0) return;

                    const visible = option.dataset.jabatanId === jobId;
                    option.hidden = !visible;
                    option.disabled = !visible;
                    selectedIsVisible = selectedIsVisible || (visible && option.selected);
                    if (visible) matchingKpiCount += 1;
                });

                if (!selectedIsVisible) kpiField.value = '';
                emptyState.hidden = !jobId || matchingKpiCount > 0;
                createKpiLink.href = `${createKpiLink.dataset.baseUrl}?jabatan_id=${encodeURIComponent(jobId)}`;
            };

            jobField?.addEventListener('change', filterKpis);
            filterKpis();
        })();
    </script>
@endpush
