<div class="master-pegawai-list-head"><span><i class="fa-solid fa-list-ul"></i> Menampilkan <b>{{ $pegawais->firstItem() ?? 0 }}–{{ $pegawais->lastItem() ?? 0 }}</b> dari <b>{{ $pegawais->total() }}</b> pegawai</span></div>
<div class="master-pegawai-table-wrap">
    <table class="master-pegawai-table">
        <thead><tr><th>Pegawai</th><th>NIP</th><th>Peran</th><th>Divisi</th><th>Jabatan</th><th>Atasan Langsung</th><th class="actions-col">Aksi</th></tr></thead>
        <tbody>
            @forelse ($pegawais as $pegawai)
                @php
                    $role = $pegawai->user->role ?? 'pegawai';
                    $roleLabel = match ($role) { 'direktur' => 'Direktur', 'kepala_bagian' => 'Kepala Bagian', 'atasan' => 'Atasan', default => 'Pegawai' };
                    $initials = collect(explode(' ', trim($pegawai->nama)))->filter()->map(fn ($word) => mb_strtoupper(mb_substr($word, 0, 1)))->take(2)->implode('');
                @endphp
                <tr>
                    <td><div class="master-pegawai-person"><span class="master-pegawai-avatar">{{ $initials ?: 'PG' }}</span><span><strong>{{ $pegawai->nama }}</strong><small>{{ $pegawai->user->email ?? '-' }}</small></span></div></td>
                    <td>{{ $pegawai->nip ?: '-' }}</td>
                    <td><span class="master-pegawai-role {{ $role === 'pegawai' ? 'staff' : 'manager' }}">{{ $roleLabel }}</span></td>
                    <td>{{ $pegawai->jabatan->divisi->nama_divisi ?? '-' }}</td>
                    <td>{{ $pegawai->jabatan->nama_jabatan ?? '-' }}</td>
                    <td>{{ $pegawai->atasan->nama ?? '-' }}</td>
                    <td><div class="master-pegawai-actions">
                        <a href="{{ route('master.pegawai.show', $pegawai) }}" title="Detail {{ $pegawai->nama }}" aria-label="Detail {{ $pegawai->nama }}"><i class="fa-regular fa-eye"></i></a>
                        <a href="{{ route('master.pegawai.edit', $pegawai) }}" title="Edit {{ $pegawai->nama }}" aria-label="Edit {{ $pegawai->nama }}"><i class="fa-solid fa-pen-to-square"></i></a>
                        <form action="{{ route('master.pegawai.destroy', $pegawai) }}" method="POST" class="form-delete" data-confirm-message="Yakin ingin menghapus pegawai ini?">@csrf @method('DELETE')<button type="submit" title="Hapus {{ $pegawai->nama }}" aria-label="Hapus {{ $pegawai->nama }}"><i class="fa-regular fa-trash-can"></i></button></form>
                    </div></td>
                </tr>
            @empty
                <tr><td colspan="7" class="master-pegawai-empty"><i class="fa-regular fa-folder-open"></i><strong>Tidak ada pegawai yang cocok</strong><span>Coba kata kunci atau filter lainnya.</span></td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="master-pegawai-mobile-list">
    @forelse ($pegawais as $pegawai)
        @php
            $role = $pegawai->user->role ?? 'pegawai';
            $roleLabel = match ($role) { 'direktur' => 'Direktur', 'kepala_bagian' => 'Kepala Bagian', 'atasan' => 'Atasan', default => 'Pegawai' };
            $initials = collect(explode(' ', trim($pegawai->nama)))->filter()->map(fn ($word) => mb_strtoupper(mb_substr($word, 0, 1)))->take(2)->implode('');
        @endphp
        <article class="master-pegawai-mobile-card">
            <div class="master-pegawai-mobile-top"><span class="master-pegawai-avatar">{{ $initials ?: 'PG' }}</span><div><strong>{{ $pegawai->nama }}</strong><small>{{ $pegawai->user->email ?? '-' }}</small><span class="master-pegawai-role {{ $role === 'pegawai' ? 'staff' : 'manager' }}">{{ $roleLabel }}</span></div></div>
            <dl><div><dt>NIP</dt><dd>{{ $pegawai->nip ?: '-' }}</dd></div><div><dt>Divisi</dt><dd>{{ $pegawai->jabatan->divisi->nama_divisi ?? '-' }}</dd></div><div><dt>Jabatan</dt><dd>{{ $pegawai->jabatan->nama_jabatan ?? '-' }}</dd></div><div><dt>Atasan Langsung</dt><dd>{{ $pegawai->atasan->nama ?? '-' }}</dd></div></dl>
            <div class="master-pegawai-mobile-actions"><a href="{{ route('master.pegawai.show', $pegawai) }}"><i class="fa-regular fa-eye"></i> Detail</a><a href="{{ route('master.pegawai.edit', $pegawai) }}"><i class="fa-solid fa-pen-to-square"></i> Edit</a><form action="{{ route('master.pegawai.destroy', $pegawai) }}" method="POST" class="form-delete" data-confirm-message="Yakin ingin menghapus pegawai ini?">@csrf @method('DELETE')<button type="submit" aria-label="Hapus {{ $pegawai->nama }}"><i class="fa-regular fa-trash-can"></i></button></form></div>
        </article>
    @empty
        <div class="master-pegawai-empty"><i class="fa-regular fa-folder-open"></i><strong>Tidak ada pegawai yang cocok</strong><span>Coba kata kunci atau filter lainnya.</span></div>
    @endforelse
</div>
@if ($pegawais->hasPages())
    <nav class="master-pegawai-pagination" aria-label="Navigasi halaman pegawai">{{ $pegawais->links('pagination::bootstrap-5') }}</nav>
@endif
