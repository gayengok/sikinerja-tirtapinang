@extends('master.template-laporan.create')
@section('title', 'Edit Template Laporan')
@section('content')
    <div class="master-template-form-page">

        <header class="master-template-form-heading">
            <div>
                <nav class="master-template-form-breadcrumb" aria-label="Breadcrumb">
                    <a href="{{ route('master.dashboard') }}">Beranda</a>
                    <i class="fa-solid fa-chevron-right"></i>
                    <a href="{{ route('master.template-laporan.index') }}">Master Data</a>
                    <i class="fa-solid fa-chevron-right"></i>
                    <span>Edit Template</span>
                </nav>
                <h1>
                    <i class="fas fa-file-pen"></i>
                    Edit Template Laporan
                </h1>
                <p>Perbarui konfigurasi field pelaporan harian dan integrasi KPI.</p>
            </div>
        </header>

        <section class="master-template-form-card">
            <div class="master-template-form-card-heading">
                <span><i class="fa-regular fa-pen-to-square"></i></span>
                <div>
                    <h2>Formulir Template Laporan</h2>
                    <p>Perbarui konfigurasi atribut pelaporan dan integrasi KPI</p>
                </div>
                <b class="is-editing">Mode Edit</b>
            </div>
            <form action="{{ route('master.template-laporan.update', $templateLaporan) }}" method="POST">@csrf
                @method('PUT')
                <div class="master-template-field"><label for="jabatan_id">Jabatan <b>*</b></label><select name="jabatan_id"
                        id="jabatan_id" class="@error('jabatan_id') is-invalid @enderror">
                        <option value="">-- Pilih Jabatan --</option>
                        @foreach ($jabatans as $jabatan)
                            <option value="{{ $jabatan->id }}"
                                {{ old('jabatan_id', $templateLaporan->jabatan_id) == $jabatan->id ? 'selected' : '' }}>
                                {{ $jabatan->nama_jabatan }} ({{ $jabatan->divisi->nama_divisi }})</option>
                        @endforeach
                    </select>
                    @error('jabatan_id')
                        <em>{{ $message }}</em>
                    @enderror
                </div>
                <div class="master-template-field"><label for="nama_field">Nama Field <b>*</b></label><input type="text"
                        name="nama_field" id="nama_field" value="{{ old('nama_field', $templateLaporan->nama_field) }}"
                        class="@error('nama_field') is-invalid @enderror">
                    @error('nama_field')
                        <em>{{ $message }}</em>
                    @enderror
                </div>
                <div class="master-template-split">
                    <div class="master-template-field"><label for="tipe_field">Tipe Field <b>*</b></label><select
                            name="tipe_field" id="tipe_field">
                            @foreach (['angka' => 'Angka', 'teks' => 'Teks', 'dropdown' => 'Dropdown'] as $value => $label)
                                <option value="{{ $value }}"
                                    {{ old('tipe_field', $templateLaporan->tipe_field) === $value ? 'selected' : '' }}>
                                    {{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="master-template-field"><label for="satuan">Satuan <small>(Opsional)</small></label><input
                            type="text" name="satuan" id="satuan"
                            value="{{ old('satuan', $templateLaporan->satuan) }}"></div>
                </div>
                <div class="master-template-dropdown-options" data-dropdown-options>
                    <label>Daftar Opsi Dropdown <small>(Opsional)</small></label>
                    <p>Isi pilihan yang dapat dipilih pegawai. Kosongkan jika field bukan Dropdown.</p>
                    <div data-option-list>
                        @forelse (old('opsi', $templateLaporan->options->pluck('label')->all()) as $option)
                            <div class="master-template-option">
                                <input type="text" name="opsi[]" value="{{ $option }}" maxlength="100"
                                    placeholder="Contoh: Selesai">
                                <button type="button" data-remove-option aria-label="Hapus opsi">&times;</button>
                            </div>
                        @empty
                            <div class="master-template-option">
                                <input type="text" name="opsi[]" maxlength="100" placeholder="Contoh: Selesai">
                                <button type="button" data-remove-option aria-label="Hapus opsi">&times;</button>
                            </div>
                        @endforelse
                    </div>
                    <button type="button" class="master-template-add-option" data-add-option>
                        <i class="fa-solid fa-plus"></i> Tambah Opsi
                    </button>
                    @error('opsi')
                        <em>{{ $message }}</em>
                    @enderror
                </div>
                <div class="master-template-field"><label for="terhubung_ke_kpi_id">KPI Terhubung
                        <small>(Opsional)</small></label><select name="terhubung_ke_kpi_id" id="terhubung_ke_kpi_id">
                        <option value="">-- Tidak Terhubung --</option>
                        @foreach ($masterKpis->groupBy(fn($kpi) => $kpi->jabatan_id) as $jabatanKpis)
                            <optgroup
                                label="{{ $jabatanKpis->first()->jabatan?->nama_jabatan ?? 'Jabatan belum ditentukan' }}">
                                @foreach ($jabatanKpis as $kpi)
                                    <option value="{{ $kpi->id }}" data-jabatan-id="{{ $kpi->jabatan_id }}"
                                        {{ old('terhubung_ke_kpi_id', $templateLaporan->terhubung_ke_kpi_id) == $kpi->id ? 'selected' : '' }}>
                                        {{ $kpi->indikator }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    <p>Pilih KPI dari jabatan yang sama agar nilai angka laporan ini terhubung ke penilaian kinerja bulanan.
                    </p>
                    <p class="master-template-kpi-empty" data-kpi-empty-state hidden>
                        Belum ada KPI untuk jabatan ini.
                        <a href="{{ route('master.master-kpi.create') }}"
                            data-base-url="{{ route('master.master-kpi.create') }}" data-create-kpi-link>
                            Tambahkan KPI untuk jabatan ini
                        </a>
                    </p>
                </div>
                <div class="master-template-actions"><button type="submit"><i class="fa-solid fa-floppy-disk"></i> Update

                    </button><a href="{{ route('master.template-laporan.index') }}">Batal</a></div>
            </form>
        </section>

    </div>
@endsection
@push('styles')
    <style>
        .master-template-delete {
            margin: 1.5rem 0;
            text-align: center
        }

        .master-template-delete button {
            color: #f03d5c;
            background: none;
            border: 0;
            font: inherit;
            font-size: .75rem
        }
    </style>
@endpush
@push('scripts')
    <script>
        (() => {
            const jobField = document.getElementById('jabatan_id');
            const kpiField = document.getElementById('terhubung_ke_kpi_id');
            const emptyState = document.querySelector('[data-kpi-empty-state]');
            const createKpiLink = document.querySelector('[data-create-kpi-link]');

            if (!jobField || !kpiField || !emptyState || !createKpiLink) return;

            const filterKpis = () => {
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

            jobField.addEventListener('change', filterKpis);
            filterKpis();
        })();
    </script>
@endpush
