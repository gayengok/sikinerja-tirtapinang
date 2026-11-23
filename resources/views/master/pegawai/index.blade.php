@extends('layouts.app')
@section('title', 'Data Pegawai')
@section('content')
<div class="master-pegawai-page">
    <header class="master-pegawai-heading">
        <div><h1><i class="fa-solid fa-users-gear"></i> Data Pegawai</h1><p>Kelola akun Pegawai, Atasan, Kepala Bagian, dan Direktur. Akun Admin dikelola terpisah.</p></div>
        <a class="master-pegawai-add" href="{{ route('master.pegawai.create') }}"><i class="fa-solid fa-plus"></i> Tambah Pegawai</a>
    </header>
    <div class="master-pegawai-summary">
        <div><small>TOTAL PEGAWAI</small><strong>{{ $totalPegawai }}</strong><span><i class="fa-solid fa-circle"></i> Terdaftar</span></div>
        <div><small>ATASAN</small><strong>{{ $roleCounts['atasan'] }}</strong><span>Menilai Pegawai</span></div>
        <div><small>KEPALA BAGIAN</small><strong>{{ $roleCounts['kepalaBagian'] }}</strong><span>Menilai Atasan</span></div>
        <div><small>DIREKTUR</small><strong>{{ $roleCounts['direktur'] }}</strong><span>Menilai Kepala Bagian</span></div>
        <div><small>PEGAWAI</small><strong>{{ $roleCounts['pegawai'] }}</strong><span>Dinilai oleh Atasan</span></div>
    </div>
    <section class="master-pegawai-content" aria-label="Daftar pegawai">
        <div class="master-pegawai-controls">
            <label class="master-pegawai-search" for="pegawaiSearch"><i class="fa-solid fa-magnifying-glass"></i><input id="pegawaiSearch" type="search" value="{{ $search }}" placeholder="Cari nama, NIP, email, jabatan, divisi..." autocomplete="off"><span id="pegawaiSearchLoading" class="pegawai-search-loading" hidden><i class="fa-solid fa-spinner fa-spin"></i></span></label>
            <label class="master-pegawai-sort"><i class="fa-solid fa-arrow-down-wide-short"></i><select id="pegawaiSort" aria-label="Urutkan pegawai"><option value="nama_asc" @selected($sort === 'nama_asc')>Nama A–Z</option><option value="nama_desc" @selected($sort === 'nama_desc')>Nama Z–A</option><option value="terbaru" @selected($sort === 'terbaru')>Terbaru</option><option value="terlama" @selected($sort === 'terlama')>Terlama</option></select></label>
        </div>
        <div class="master-pegawai-tabs" id="pegawaiRoleTabs" role="group" aria-label="Filter peran">
            <button type="button" data-role="semua" class="{{ $role === 'semua' ? 'active' : '' }}" aria-pressed="{{ $role === 'semua' ? 'true' : 'false' }}">Semua ({{ $totalPegawai }})</button>
            <button type="button" data-role="atasan" class="{{ $role === 'atasan' ? 'active' : '' }}" aria-pressed="{{ $role === 'atasan' ? 'true' : 'false' }}">Atasan ({{ $roleCounts['atasan'] }})</button>
            <button type="button" data-role="kepala_bagian" class="{{ $role === 'kepala_bagian' ? 'active' : '' }}" aria-pressed="{{ $role === 'kepala_bagian' ? 'true' : 'false' }}">Kepala Bagian ({{ $roleCounts['kepalaBagian'] }})</button>
            <button type="button" data-role="direktur" class="{{ $role === 'direktur' ? 'active' : '' }}" aria-pressed="{{ $role === 'direktur' ? 'true' : 'false' }}">Direktur ({{ $roleCounts['direktur'] }})</button>
            <button type="button" data-role="pegawai" class="{{ $role === 'pegawai' ? 'active' : '' }}" aria-pressed="{{ $role === 'pegawai' ? 'true' : 'false' }}">Pegawai ({{ $roleCounts['pegawai'] }})</button>
        </div>
        <div id="pegawaiResults" aria-live="polite">@include('master.pegawai.partials.results')</div>
    </section>
</div>
@endsection
@push('styles')
<style>
body.role-master .master-pegawai-page{width:100%;max-width:none;margin:0;padding:1rem clamp(1rem,2.4vw,2.5rem) 2rem;color:#26364a;box-sizing:border-box}
.master-pegawai-heading{display:flex;justify-content:space-between;align-items:center;gap:1rem;margin-bottom:1.4rem}
.master-pegawai-heading h1{display:flex;align-items:center;gap:.7rem;margin:0;color:#1b2938;font-size:1.65rem;font-weight:750}
.master-pegawai-heading h1 i{color:#087db5;font-size:1.35rem}
.master-pegawai-heading p{margin:.35rem 0 0;color:#71829c;font-size:.84rem}
.master-pegawai-add{display:inline-flex;align-items:center;justify-content:center;gap:.55rem;padding:.8rem 1.15rem;background:#087db5;color:#fff;text-decoration:none;border-radius:12px;font-size:.83rem;font-weight:650;white-space:nowrap;box-shadow:0 5px 12px #087db525}
.master-pegawai-add:hover{background:#076b9d;color:#fff}
.master-pegawai-summary{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:.8rem;margin-bottom:1.25rem}
.master-pegawai-summary>div{min-width:0;padding:1.05rem 1.1rem;background:#fff;border:1px solid #e1eaf3;border-radius:14px;box-shadow:0 3px 12px #23345508}
.master-pegawai-summary small,.master-pegawai-summary span{display:block;color:#8192a8;font-size:.72rem}
.master-pegawai-summary small{font-weight:700;letter-spacing:.04em}
.master-pegawai-summary strong{display:block;margin:.35rem 0;color:#1c3047;font-size:1.65rem}
.master-pegawai-summary span i{color:#09ad82;font-size:.5rem}
.master-pegawai-content{padding:1.3rem 1.4rem 1.5rem;background:#fff;border:1px solid #e1eaf3;border-radius:22px;box-shadow:0 6px 24px #23345508}
.master-pegawai-controls{display:flex;align-items:stretch;gap:.8rem;margin-bottom:1rem}
.master-pegawai-search{display:flex;align-items:center;gap:.8rem;flex:1;min-width:0;padding:0 1rem;background:#f8fafc;border:1px solid #dfe8f1;border-radius:13px;color:#91a1b8}
.master-pegawai-search:focus-within{border-color:#087db5;box-shadow:0 0 0 3px #087db515}
.master-pegawai-search input{width:100%;min-width:0;height:48px;background:transparent;border:0;outline:0;color:#26364a;font-size:.82rem}
.master-pegawai-search input::-webkit-search-cancel-button{cursor:pointer}
.pegawai-search-loading[hidden]{display:none}
.master-pegawai-sort{display:flex;align-items:center;gap:.5rem;padding:0 .9rem;background:#f8fafc;border:1px solid #dfe8f1;border-radius:13px;color:#087db5}
.master-pegawai-sort select{max-width:160px;height:48px;background:transparent;border:0;outline:0;color:#405570;font-size:.8rem;cursor:pointer}
.master-pegawai-tabs{display:flex;gap:.55rem;overflow-x:auto;padding:0 0 .55rem;margin-bottom:1rem;scrollbar-width:thin}
.master-pegawai-tabs button{flex:0 0 auto;padding:.65rem .95rem;border:1px solid #dce7f2;border-radius:999px;background:#fff;color:#536b87;font-size:.77rem;font-weight:650;white-space:nowrap;cursor:pointer;transition:background .15s,border-color .15s}
.master-pegawai-tabs button:hover{border-color:#87c5e3;color:#087db5}
.master-pegawai-tabs button.active{background:#087db5;border-color:#087db5;color:#fff}
.master-pegawai-list-head{margin:.1rem 0 .85rem;color:#7487a0;font-size:.78rem}
.master-pegawai-list-head i{color:#087db5;margin-right:.4rem}
.master-pegawai-table-wrap{overflow-x:auto;border:1px solid #e3ebf4;border-radius:16px}
.master-pegawai-table{width:100%;min-width:920px;border-collapse:collapse;font-size:.78rem}
.master-pegawai-table th{padding:1rem .85rem;background:#f4f8fb;color:#70869f;font-size:.7rem;text-align:left;text-transform:uppercase;letter-spacing:.025em;white-space:nowrap}
.master-pegawai-table td{padding:.8rem .85rem;border-top:1px solid #edf1f6;color:#526783;vertical-align:middle}
.master-pegawai-table tbody tr:hover{background:#f9fbfd}
.master-pegawai-person{display:flex;align-items:center;gap:.65rem;min-width:185px}
.master-pegawai-avatar{display:grid;place-items:center;flex:0 0 38px;width:38px;height:38px;border-radius:12px;background:#e7f4fd;color:#087db5;font-size:.73rem;font-weight:750}
.master-pegawai-person>span:last-child{min-width:0}
.master-pegawai-person strong,.master-pegawai-person small{display:block;max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.master-pegawai-person strong{color:#293d55;font-size:.79rem}
.master-pegawai-person small{margin-top:.15rem;color:#91a1b8;font-size:.7rem}
.master-pegawai-role{display:inline-flex;padding:.32rem .6rem;border-radius:999px;font-size:.7rem;font-weight:650;white-space:nowrap}
.master-pegawai-role.manager{background:#fff2d8;color:#986000}
.master-pegawai-role.staff{background:#e5f8ee;color:#087c50}
.master-pegawai-table th.actions-col{text-align:right}
.master-pegawai-actions{display:flex;justify-content:flex-end;gap:.4rem;white-space:nowrap}
.master-pegawai-actions a,.master-pegawai-actions button{display:grid;place-items:center;width:34px;height:34px;border:1px solid #e3ebf4;border-radius:10px;background:#f8fafc;color:#587692;text-decoration:none;cursor:pointer}
.master-pegawai-actions a:hover{background:#e7f4fd;color:#087db5}
.master-pegawai-actions button{color:#e04c70}
.master-pegawai-actions button:hover{background:#fff0f3}
.master-pegawai-actions form,.master-pegawai-mobile-actions form{margin:0}
.master-pegawai-empty{padding:2.4rem 1rem!important;text-align:center;color:#8495a9}
.master-pegawai-empty i,.master-pegawai-empty strong,.master-pegawai-empty span{display:block;margin:.35rem auto}
.master-pegawai-empty i{font-size:1.5rem;color:#087db5}
.master-pegawai-pagination{margin-top:1.25rem}
.master-pegawai-pagination .pagination{display:flex;justify-content:center;flex-wrap:wrap;gap:.4rem;margin:0}
.master-pegawai-pagination .page-link{display:grid;place-items:center;min-width:36px;height:36px;padding:.4rem .65rem;border:1px solid #dce7f2!important;border-radius:10px!important;background:#fff;color:#526b86;font-size:.8rem;font-weight:650;box-shadow:none!important}
.master-pegawai-pagination .page-item.active .page-link{background:#087db5!important;border-color:#087db5!important;color:#fff!important}
.master-pegawai-pagination .page-item:not(.disabled) .page-link:hover{background:#e7f4fd;color:#087db5}
.master-pegawai-pagination .page-item.disabled .page-link{opacity:.45}
.master-pegawai-mobile-list{display:none}
@media(max-width:767.98px){body.role-master .master-pegawai-page{max-width:560px;margin:auto;padding:1rem 1rem 7rem}.master-pegawai-heading{align-items:flex-start;margin-bottom:1rem}.master-pegawai-heading h1{font-size:1.25rem}.master-pegawai-heading h1 i{font-size:1.1rem}.master-pegawai-heading p{font-size:.73rem;line-height:1.5}.master-pegawai-add{padding:.7rem .75rem;font-size:.7rem;border-radius:10px}.master-pegawai-summary{grid-template-columns:repeat(2,minmax(0,1fr));gap:.6rem}.master-pegawai-summary>div{padding:.85rem;border-radius:14px}.master-pegawai-summary>div:first-child{grid-column:1/-1}.master-pegawai-summary strong{font-size:1.35rem}.master-pegawai-summary small,.master-pegawai-summary span{font-size:.64rem}.master-pegawai-content{padding:1rem;border-radius:18px}.master-pegawai-controls{flex-direction:column;gap:.6rem}.master-pegawai-search{flex:auto}.master-pegawai-search input,.master-pegawai-sort select{height:44px}.master-pegawai-sort{align-self:flex-end}.master-pegawai-tabs{margin-top:.2rem;margin-bottom:.8rem}.master-pegawai-tabs button{padding:.55rem .75rem;font-size:.7rem}.master-pegawai-table-wrap{display:none}.master-pegawai-mobile-list{display:grid;gap:.75rem}.master-pegawai-mobile-card{padding:1rem;background:#fff;border:1px solid #e1eaf3;border-radius:15px;box-shadow:0 3px 10px #23345508}.master-pegawai-mobile-top{display:flex;align-items:flex-start;gap:.7rem}.master-pegawai-mobile-top>div{min-width:0}.master-pegawai-mobile-top strong,.master-pegawai-mobile-top small{display:block;overflow-wrap:anywhere}.master-pegawai-mobile-top strong{font-size:.85rem}.master-pegawai-mobile-top small{margin:.2rem 0 .4rem;color:#8192a8;font-size:.7rem}.master-pegawai-mobile-card dl{display:grid;gap:.5rem;margin:1rem 0}.master-pegawai-mobile-card dl>div{display:flex;justify-content:space-between;gap:.6rem;padding:.55rem .7rem;background:#f8fafc;border-radius:9px;font-size:.7rem}.master-pegawai-mobile-card dt{color:#8495a9;font-weight:400}.master-pegawai-mobile-card dd{max-width:65%;margin:0;color:#334155;font-weight:650;text-align:right;overflow-wrap:anywhere}.master-pegawai-mobile-actions{display:flex;gap:.5rem;align-items:center}.master-pegawai-mobile-actions a,.master-pegawai-mobile-actions button{display:inline-flex;align-items:center;justify-content:center;gap:.4rem;padding:.6rem .8rem;border:1px solid #dce7f2;border-radius:9px;background:#f8fafc;color:#526b86;font-size:.72rem;text-decoration:none}.master-pegawai-mobile-actions a:nth-child(2){background:#e7f4fd;color:#087db5}.master-pegawai-mobile-actions form{margin-left:auto}.master-pegawai-mobile-actions button{color:#d33755;background:#fff0f3}}
@media(max-width:380px){.master-pegawai-heading{flex-wrap:wrap}.master-pegawai-add{margin-left:auto}.master-pegawai-content{padding:.8rem}}
@media (min-width: 768px) {
    body.role-master .master-pegawai-page .master-pegawai-mobile-list {
        display: none !important;
    }

    body.role-master .master-pegawai-page .master-pegawai-table-wrap {
        display: block !important;
    }
}

@media (max-width: 767.98px) {
    body.role-master .master-pegawai-page .master-pegawai-table-wrap {
        display: none !important;
    }

    body.role-master .master-pegawai-page .master-pegawai-mobile-list {
        display: grid !important;
        grid-template-columns: minmax(0, 1fr);
        gap: 12px;
    }

    body.role-master .master-pegawai-page .master-pegawai-mobile-card {
        display: block;
        min-width: 0;
        padding: 16px;
        background: #fff;
        border: 1px solid #e1eaf3;
        border-radius: 16px;
    }
}
</style>
@endpush
@push('scripts')
<script>
(function(){
    const search=document.getElementById('pegawaiSearch');
    const sort=document.getElementById('pegawaiSort');
    const tabs=document.getElementById('pegawaiRoleTabs');
    const results=document.getElementById('pegawaiResults');
    const loading=document.getElementById('pegawaiSearchLoading');
    if(!search||!sort||!tabs||!results)return;
    let role=tabs.querySelector('button.active')?.dataset.role||'semua';
    let debounce;
    let controller;
    let sequence=0;
    const baseUrl=@json(route('master.pegawai.index'));
    function makeUrl(page=1){
        const url=new URL(baseUrl,window.location.origin);
        if(search.value.trim())url.searchParams.set('search',search.value.trim());
        if(role!=='semua')url.searchParams.set('role',role);
        if(sort.value!=='nama_asc')url.searchParams.set('sort',sort.value);
        if(page>1)url.searchParams.set('page',String(page));
        return url;
    }
    async function refresh(page=1,push=false){
        const url=makeUrl(page);
        if(controller)controller.abort();
        controller=new AbortController();
        const current=++sequence;
        loading.hidden=false;
        results.setAttribute('aria-busy','true');
        try{
            const response=await fetch(url.toString(),{headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'},credentials:'same-origin',signal:controller.signal});
            if(!response.ok)throw new Error('HTTP '+response.status);
            const data=await response.json();
            if(current!==sequence)return;
            results.innerHTML=data.html;
            history[push?'pushState':'replaceState']({},'',url.toString());
        }catch(error){
            if(error.name!=='AbortError'&&current===sequence){results.insertAdjacentHTML('afterbegin','<p class="alert alert-danger" role="alert">Gagal memuat data pegawai. Silakan coba lagi.</p>');}
        }finally{
            if(current===sequence){loading.hidden=true;results.removeAttribute('aria-busy');}
        }
    }
    search.addEventListener('input',()=>{clearTimeout(debounce);debounce=setTimeout(()=>refresh(1),300)});
    sort.addEventListener('change',()=>refresh(1));
    tabs.addEventListener('click',event=>{
        const button=event.target.closest('button[data-role]');
        if(!button)return;
        role=button.dataset.role;
        tabs.querySelectorAll('button[data-role]').forEach(item=>{const active=item===button;item.classList.toggle('active',active);item.setAttribute('aria-pressed',String(active));});
        refresh(1);
    });
    results.addEventListener('click',event=>{
        const link=event.target.closest('.master-pegawai-pagination a.page-link');
        if(!link)return;
        event.preventDefault();
        const page=Number(new URL(link.href,window.location.origin).searchParams.get('page')||1);
        refresh(page,true);
    });
    window.addEventListener('popstate',()=>{
        const params=new URLSearchParams(location.search);
        search.value=params.get('search')||'';
        sort.value=params.get('sort')||'nama_asc';
        role=params.get('role')||'semua';
        tabs.querySelectorAll('button[data-role]').forEach(item=>{const active=item.dataset.role===role;item.classList.toggle('active',active);item.setAttribute('aria-pressed',String(active));});
        refresh(Number(params.get('page')||1));
    });
})();
</script>
@endpush
