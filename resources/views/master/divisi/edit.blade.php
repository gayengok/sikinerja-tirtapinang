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
    <link rel="stylesheet" href="{{ asset('css/master/divisi-unified.css') }}?v={{ filemtime(public_path('css/master/divisi-unified.css')) }}">
@endpush
