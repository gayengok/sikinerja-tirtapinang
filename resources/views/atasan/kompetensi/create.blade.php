@extends('layouts.app')
@section('title', 'Form Penilaian Kompetensi')
@section('content')
    @php
        $assessmentRoutePrefix = $assessmentRoutePrefix ?? 'atasan.kompetensi';
        $targetLabel = $targetLabel ?? 'Pegawai';
    @endphp
    <div class="kompetensi-form-page">
        <div class="atasan-mobile-breadcrumb"><a
                href="{{ route($assessmentRoutePrefix . '.index', ['periode_id' => $periode->id]) }}"><i
                    class="fa-solid fa-arrow-left"></i></a>
            <div><span>Penilaian Kompetensi</span><strong>{{ $targetLabel }}</strong></div>
        </div>

        <nav class="kompetensi-form-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route($assessmentRoutePrefix === 'atasan.kompetensi' ? 'atasan.dashboard' : ($assessmentRoutePrefix === 'kepala_bagian.penilaian-atasan' ? 'kepala_bagian.dashboard' : 'direktur.dashboard')) }}">Beranda</a>
            <i class="fa-solid fa-chevron-right"></i>
            <a href="{{ route($assessmentRoutePrefix . '.index', ['periode_id' => $periode->id]) }}">Penilaian Kompetensi</a>
            <i class="fa-solid fa-chevron-right"></i>
            <span>Form Penilaian</span>
        </nav>

        <section class="kompetensi-form-person"><span
                class="kompetensi-avatar">{{ strtoupper(substr($pegawai->nama, 0, 2)) }}</span>
            <div><small>{{ strtoupper($targetLabel) }} YANG DINILAI</small>
                <h2>{{ $pegawai->nama }}</h2>
                <p>{{ $pegawai->jabatan->nama_jabatan ?? '-' }}</p>
            </div><b>{{ $periode->nama_periode }}</b>
        </section>
        @if ($masterKompetensis->isEmpty())
            <div class="alert alert-warning mt-3">Belum ada master kompetensi untuk jabatan ini.</div>
        @else
            <form action="{{ route($assessmentRoutePrefix . '.kompetensi.update', $pegawai) }}" method="POST">@csrf @method('PUT')<input
                    type="hidden" name="periode_id" value="{{ $periode->id }}">
                <div class="kompetensi-form-intro"><strong><i class="fa-solid fa-user-check"></i> ASPEK PERILAKU KERJA
                        (BOBOT 40%)</strong><span>Skala Penilaian 1–5</span></div>
                @foreach ($masterKompetensis as $index => $kompetensi)
                    @php $tersimpan = $penilaianTersimpan->get($kompetensi->id); @endphp
                    <article class="kompetensi-indicator">
                        <div class="kompetensi-indicator-title"><span>{{ $index + 1 }}</span>
                            <div>
                                <h2>{{ $kompetensi->nama_kompetensi }}</h2>
                                <p>{{ $kompetensi->definisi_perilaku ?: 'Tidak ada definisi perilaku.' }}</p>
                            </div>
                        </div><input type="hidden" name="kompetensi[{{ $index }}][master_kompetensi_id]"
                            value="{{ $kompetensi->id }}">
                        <fieldset class="kompetensi-score-field">
                            <legend>Skor Penilaian (1–5) <em>*</em></legend>
                            <div class="kompetensi-score-options" role="radiogroup"
                                aria-label="Skor {{ $kompetensi->nama_kompetensi }}">
                                @for ($skor = 1; $skor <= 5; $skor++)
                                    <label>
                                        <input type="radio" name="kompetensi[{{ $index }}][skor]"
                                            value="{{ $skor }}" required @checked((int) old('kompetensi.' . $index . '.skor', $tersimpan->skor ?? 0) === $skor)>
                                        <span>{{ $skor }}</span>
                                    </label>
                                @endfor
                                <small>1 = Sangat Kurang, 5 = Sangat Baik</small>
                            </div>
                        </fieldset>
                        <label>Catatan
                            penilai
                            <textarea name="kompetensi[{{ $index }}][catatan]" rows="2" maxlength="1000"
                                placeholder="Catatan pengamatan (opsional)">{{ old('kompetensi.' . $index . '.catatan', $tersimpan->catatan ?? '') }}</textarea>
                        </label>
                        @error('kompetensi.' . $index . '.skor')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </article>
                @endforeach
                <div class="kompetensi-form-actions"><a href="{{ route($assessmentRoutePrefix . '.index') }}">Batal</a><button
                        type="submit"><i class="fa-solid fa-floppy-disk"></i> Simpan Penilaian</button></div>
            </form>
        @endif
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/atasan/kompetensi/create.css') }}">
@endpush
