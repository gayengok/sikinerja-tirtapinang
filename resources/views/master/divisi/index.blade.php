@extends('layouts.app')
@section('title', 'Data Divisi')
@section('content')
@php
    $totalJabatanHalaman = $divisis->getCollection()->sum('jabatans_count');
@endphp
<div class="master-divisi-page">
    <header class="master-divisi-heading">
        <div><h1><i class="fa-solid fa-building-user"></i> Data Divisi</h1><p>Kelola unit kerja Perumdam Tirta Pinang</p></div>
        <a href="{{ route('master.divisi.create') }}"><i class="fa-solid fa-plus"></i> Tambah Divisi</a>
    </header>
    <div class="master-divisi-summary">
        <div><span class="summary-icon blue"><i class="fa-solid fa-building"></i></span><div><small>Total Divisi</small><strong>{{ $divisis->total() }}</strong><em>Unit terdaftar</em></div></div>
        <div><span class="summary-icon purple"><i class="fa-solid fa-users"></i></span><div><small>Jabatan pada Halaman Ini</small><strong>{{ $totalJabatanHalaman }}</strong><em>Posisi</em></div></div>
    </div>
    <div class="master-divisi-search"><div><i class="fa-solid fa-magnifying-glass"></i><input id="masterDivisiSearch" type="search" placeholder="Cari divisi pada halaman ini..." aria-label="Cari divisi pada halaman ini"></div><button type="button" id="masterDivisiSearchReset" aria-label="Bersihkan pencarian" title="Bersihkan pencarian"><i class="fa-solid fa-xmark"></i></button></div>
    <div class="master-divisi-table-wrap"><table class="master-divisi-table"><thead><tr><th>No</th><th>Nama Divisi</th><th>Kategori</th><th>Jumlah Jabatan</th><th>Tanggal Pembaruan</th><th>Status</th><th class="master-divisi-table-actions-heading">Aksi</th></tr></thead><tbody>
        @forelse($divisis as $divisi)
        <tr data-divisi-name="{{ $divisi->nama_divisi }}"><td><span class="master-divisi-table-number">{{ $loop->iteration + ($divisis->currentPage()-1)*$divisis->perPage() }}</span></td><td><strong class="master-divisi-table-name">{{ $divisi->nama_divisi }}</strong></td><td><span class="master-divisi-table-category">{{ $divisi->kategori ?? 'Unit Kerja' }}</span></td><td><span class="master-divisi-table-jabatan"><i class="fa-solid fa-circle"></i>{{ $divisi->jabatans_count }} Jabatan</span></td><td><span class="master-divisi-table-updated"><i class="fa-regular fa-calendar"></i>{{ $divisi->updated_at->translatedFormat('d F Y') }}</span></td><td><span class="master-divisi-table-status"><i class="fa-solid fa-circle"></i>Aktif</span></td><td><div class="master-divisi-table-actions"><a href="{{ route('master.divisi.show',$divisi) }}" aria-label="Detail {{ $divisi->nama_divisi }}" title="Detail"><i class="fa-regular fa-eye"></i></a><a href="{{ route('master.divisi.edit',$divisi) }}" aria-label="Edit {{ $divisi->nama_divisi }}" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a><form action="{{ route('master.divisi.destroy',$divisi) }}" method="POST" class="form-delete" data-confirm-message="Yakin ingin menghapus divisi ini?">@csrf @method('DELETE')<button type="submit" aria-label="Hapus {{ $divisi->nama_divisi }}" title="Hapus"><i class="fa-regular fa-trash-can"></i></button></form></div></td></tr>
        @empty <tr><td class="master-divisi-table-empty" colspan="7">Belum ada data divisi.</td></tr> @endforelse
    </tbody></table></div>
    <div class="master-divisi-list">@forelse($divisis as $divisi)
        <article class="master-divisi-card" data-divisi-name="{{ $divisi->nama_divisi }}"><div class="master-divisi-card-main"><span class="master-divisi-number">{{ $loop->iteration + ($divisis->currentPage()-1)*$divisis->perPage() }}</span><div class="master-divisi-card-content"><h2>{{ $divisi->nama_divisi }}</h2><div class="master-divisi-meta"><span><i class="fa-solid fa-circle"></i>{{ $divisi->jabatans_count }} Jabatan</span><em>{{ $divisi->kategori ?? 'Unit Kerja' }}</em></div></div></div><div class="master-divisi-card-footer"><span><i class="fa-regular fa-calendar"></i> {{ $divisi->updated_at->translatedFormat('d F Y') }}</span><div class="master-divisi-actions"><a href="{{ route('master.divisi.show',$divisi) }}" aria-label="Detail"><i class="fa-regular fa-eye"></i></a><a href="{{ route('master.divisi.edit',$divisi) }}" aria-label="Edit"><i class="fa-solid fa-pen-to-square"></i></a><form action="{{ route('master.divisi.destroy',$divisi) }}" method="POST" class="form-delete" data-confirm-message="Yakin ingin menghapus divisi ini?">@csrf @method('DELETE')<button type="submit" aria-label="Hapus"><i class="fa-regular fa-trash-can"></i></button></form></div></div></article>
        @empty <div class="master-divisi-empty"><i class="fa-regular fa-folder-open"></i><strong>Belum ada data divisi.</strong></div> @endforelse</div>
    <div class="master-divisi-pagination">{{ $divisis->links('pagination::bootstrap-5') }}</div>
    <aside class="master-divisi-note"><i class="fa-solid fa-circle-info"></i><span>Divisi menjadi pengelompokan unit kerja dan jabatan dalam struktur organisasi Perumdam Tirta Pinang.</span></aside>
</div>
@endsection
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/master/divisi-unified.css') }}?v={{ filemtime(public_path('css/master/divisi-unified.css')) }}">
@endpush
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded',function(){const input=document.getElementById('masterDivisiSearch');const reset=document.getElementById('masterDivisiSearchReset');if(!input)return;const items=document.querySelectorAll('.master-divisi-page [data-divisi-name]');function filter(){const term=input.value.trim().toLocaleLowerCase('id');items.forEach(item=>{item.hidden=!item.dataset.divisiName.toLocaleLowerCase('id').includes(term);});}input.addEventListener('input',filter);reset?.addEventListener('click',()=>{input.value='';filter();input.focus();});});
</script>
@endpush
