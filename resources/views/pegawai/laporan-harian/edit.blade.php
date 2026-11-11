@extends('layouts.app')

@section('title', 'Edit Laporan Harian')

@section('content')
    <div class="pegawai-page daily-report-form-page">
        <div class="daily-report-breadcrumb"><a href="{{ route($routePrefix . '.laporan-harian.show', $laporan) }}"><i class="fa-solid fa-chevron-left"></i> Detail Laporan</a> <span>/ Edit Laporan</span></div>
        <div class="daily-report-banner">
            <span>EDIT LAPORAN <i>â€¢</i> {{ auth()->user()->pegawai->jabatan->divisi->nama_divisi ?? 'Laporan Pegawai' }}</span>
            <h1>Edit Laporan Harian</h1>
            <p>Perbarui catatan kegiatan dan realisasi laporan harian Anda.</p>
            <i class="fa-solid fa-pen-to-square daily-report-banner-icon"></i>
        </div>
        @if ($laporan->status === 'ditolak')
            <div class="daily-report-revision"><i class="fa-solid fa-circle-exclamation"></i> Laporan sebelumnya ditolak. Perbaiki data lalu simpan kembali sebagai draft.</div>
        @endif
        <form action="{{ route($routePrefix . '.laporan-harian.update', $laporan) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <section class="daily-report-form-card">
                <div class="daily-report-card-title"><i class="fa-regular fa-file-lines"></i><strong>Informasi Pelaporan</strong><span>Langkah 1 dari 2</span></div>
                <div class="daily-report-field">
                    <label for="tanggal">Tanggal Pelaporan <b>*</b></label>
                    <input type="date" name="tanggal" id="tanggal" value="{{ old('tanggal', \Carbon\Carbon::parse($laporan->tanggal)->format('Y-m-d')) }}" class="@error('tanggal') is-invalid @enderror">
                    <small><i class="fa-regular fa-calendar"></i> Sesuai tanggal jadwal kehadiran shift kerja aktif.</small>
                    @error('tanggal')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="daily-report-field">
                    <label for="catatan">Catatan & Uraian Aktivitas <span>(Opsional)</span> <a href="#">Gunakan Template</a></label>
                    <textarea name="catatan" id="catatan" rows="4" maxlength="1000" placeholder="Jelaskan ringkas kegiatan, kendala, atau progres tugas hari ini...">{{ old('catatan', $laporan->catatan) }}</textarea>
                    <small>Catatan kegiatan harian. <em>Maksimal 1000 karakter</em></small>
                    @error('catatan')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="daily-report-field">
                    <label for="bukti_gambar">Bukti Kegiatan <span>(Opsional)</span></label>
                    @if ($laporan->bukti_gambar)
                        <img src="{{ route($routePrefix . '.laporan-harian.image', $laporan) }}" alt="Bukti kegiatan saat ini"
                            style="display:block;width:100%;max-height:180px;object-fit:cover;border-radius:12px;margin-bottom:.6rem;">
                    @endif
                    <input type="file" name="bukti_gambar" id="bukti_gambar" accept="image/jpeg,image/png,image/webp"
                        class="@error('bukti_gambar') is-invalid @enderror">
                    <small><i class="fa-regular fa-image"></i> Upload gambar baru untuk mengganti bukti sebelumnya. Maksimal 5 MB.</small>
                    @error('bukti_gambar')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
            </section>
            <section class="daily-report-form-card">
                <div class="daily-report-card-title"><i class="fa-solid fa-chart-simple"></i><strong>Realisasi Laporan Harian</strong><span>{{ $templateLaporans->count() }} indikator</span></div>
                <p class="daily-report-hint">Periksa dan perbarui realisasi pekerjaan Anda hari ini.</p>
                <div class="daily-report-indicators">
                    @foreach ($templateLaporans as $index => $template)
                        @php $tersimpan = $nilaiTersimpan->get($template->id); @endphp
                        <div class="daily-report-indicator">
                            <div class="daily-report-indicator-label"><strong>{{ $template->nama_field }}</strong><span>{{ $template->satuan ?: 'Realisasi' }}</span></div>
                            @if ($template->tipe_field === 'dropdown')
                                <select name="detail[{{ $index }}][nilai]"
                                    class="@error('detail.' . $index . '.nilai') is-invalid @enderror">
                                    <option value="">-- Pilih --</option>
                                    @foreach ($template->options as $option)
                                        <option value="{{ $option->label }}"
                                            {{ old('detail.' . $index . '.nilai', $tersimpan->nilai ?? '') === $option->label ? 'selected' : '' }}>
                                            {{ $option->label }}</option>
                                    @endforeach
                                </select>
                            @else
                                <input type="{{ $template->tipe_field === 'angka' ? 'number' : 'text' }}"
                                    {{ $template->tipe_field === 'angka' ? 'step=0.01' : '' }}
                                    name="detail[{{ $index }}][nilai]"
                                    value="{{ old('detail.' . $index . '.nilai', $tersimpan->nilai ?? '') }}"
                                    placeholder="Masukkan nilai"
                                    class="@error('detail.' . $index . '.nilai') is-invalid @enderror">
                            @endif
                            <input type="hidden" name="detail[{{ $index }}][master_template_laporan_id]" value="{{ $template->id }}">
                            @error('detail.' . $index . '.nilai')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                    @endforeach
                </div>
                <div class="daily-report-submit-bar">
                    <a href="{{ route($routePrefix . '.laporan-harian.show', $laporan) }}">Batal</a>
                    <button type="submit"><i class="fa-solid fa-floppy-disk"></i> Perbarui Laporan</button>
                </div>
            </section>
        </form>
    </div>
@endsection
