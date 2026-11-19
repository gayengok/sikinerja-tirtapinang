
@php
    $offset = ($pegawais->currentPage() - 1) * $pegawais->perPage();
@endphp

<div class="atasan-team-list-heading">
    <span>
        <i class="fa-solid fa-list"></i>
        Hasil Pencarian
    </span>

    <span>
        {{ $pegawais->total() }} pegawai ditemukan
    </span>
</div>

<div class="atasan-team-table-wrap">
    <table class="atasan-team-table">
        <thead>
            <tr>
                <th scope="col">No</th>
                <th scope="col">Pegawai</th>
                <th scope="col">Kontak & Email</th>
                <th scope="col">Unit / Divisi</th>
                <th scope="col">Status</th>
                <th scope="col" class="atasan-team-table-actions-heading">Aksi</th>
            </tr>
        </thead>

        <tbody>
            @forelse($pegawais as $pegawai)
                @php
                    $inisial = mb_strtoupper(mb_substr($pegawai->nama, 0, 2));

                    $fotoUrl = $pegawai->foto_profil
                        ? \Illuminate\Support\Facades\Storage::disk('public')->url($pegawai->foto_profil)
                        : null;


                    $routePenilaian = \Illuminate\Support\Facades\Route::has('atasan.kompetensi.edit')
                        ? route('atasan.kompetensi.edit', $pegawai)
                        : null;
                @endphp

                <tr>
                    <td>
                        <span class="atasan-team-table-number">
                            {{ $offset + $loop->iteration }}
                        </span>
                    </td>

                    <td>
                        <div class="atasan-team-table-person">
                            <span class="atasan-team-table-avatar">
                                {{ $inisial }}

                                @if($fotoUrl)
                                    <img
                                        src="{{ $fotoUrl }}"
                                        alt=""
                                        loading="lazy"
                                        onerror="this.remove()"
                                    >
                                @endif
                            </span>

                            <span class="atasan-team-person-copy">
                                <strong>{{ $pegawai->nama }}</strong>
                                <small>{{ $pegawai->jabatan->nama_jabatan ?? '-' }}</small>
                            </span>
                        </div>
                    </td>

                    <td>
                        <div class="atasan-team-contact">
                            <strong>{{ $pegawai->user->email ?? '-' }}</strong>
                            <small>NIP: {{ $pegawai->nip ?: '-' }}</small>
                        </div>
                    </td>

                    <td>
                        <span class="atasan-team-table-division">
                            <i class="fa-regular fa-building"></i>
                            {{ $pegawai->jabatan->divisi->nama_divisi ?? '-' }}
                        </span>
                    </td>

                    <td>
                        <span class="atasan-team-table-status {{ $pegawai->status === 'aktif' ? 'is-active' : '' }}">
                            <i class="fa-solid fa-circle"></i>
                            {{ ucfirst($pegawai->status ?? '-') }}
                        </span>
                    </td>

                    <td>
                        <div class="atasan-team-table-actions">
    @if($routePenilaian)
        <a href="{{ $routePenilaian }}" class="primary">
            Kompetensi
            <i class="fa-solid fa-chevron-right"></i>
        </a>
    @endif
</div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="atasan-team-table-empty">
                        <i class="fa-solid fa-user-slash"></i>

                        <strong>Tidak ada pegawai ditemukan</strong>

                        <span>
                            Coba gunakan kata kunci lain atau ubah filter pencarian.
                        </span>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="atasan-team-list">
    @forelse($pegawais as $pegawai)
        @php
            $inisial = mb_strtoupper(mb_substr($pegawai->nama, 0, 2));

            $fotoUrl = $pegawai->foto_profil
                ? \Illuminate\Support\Facades\Storage::disk('public')->url($pegawai->foto_profil)
                : null;

            $routePenilaian = \Illuminate\Support\Facades\Route::has('atasan.kompetensi.edit')
                ? route('atasan.kompetensi.edit', $pegawai)
                : null;
        @endphp

        <article class="atasan-employee-card">
            <div class="atasan-employee-top">
                <span class="atasan-employee-avatar">
                    {{ $inisial }}

                    @if($fotoUrl)
                        <img
                            src="{{ $fotoUrl }}"
                            alt=""
                            loading="lazy"
                            onerror="this.remove()"
                        >
                    @endif
                </span>

                <div class="atasan-employee-identity">
                    <h3>{{ $pegawai->nama }}</h3>

                    <p>{{ $pegawai->jabatan->nama_jabatan ?? '-' }}</p>

                    <span class="atasan-team-table-status {{ $pegawai->status === 'aktif' ? 'is-active' : '' }}">
                        <i class="fa-solid fa-circle"></i>
                        {{ ucfirst($pegawai->status ?? '-') }}
                    </span>
                </div>
            </div>

            <div class="atasan-employee-meta">
                <span>
                    <i class="fa-regular fa-id-card"></i>
                    NIP
                </span>

                <strong>{{ $pegawai->nip ?: '-' }}</strong>
            </div>

            <div class="atasan-employee-meta">
                <span>
                    <i class="fa-regular fa-envelope"></i>
                    Email
                </span>

                <strong>{{ $pegawai->user->email ?? '-' }}</strong>
            </div>

            <div class="atasan-employee-meta">
                <span>
                    <i class="fa-regular fa-building"></i>
                    Unit / Divisi
                </span>

                <strong>
                    {{ $pegawai->jabatan->divisi->nama_divisi ?? '-' }}
                </strong>
            </div>

            <div class="atasan-employee-actions">
    @if($routePenilaian)
        <a href="{{ $routePenilaian }}" class="primary">
            Kompetensi
            <i class="fa-solid fa-chevron-right"></i>
        </a>
    @endif
</div>
        </article>
    @empty
        <div class="atasan-team-empty">
            <i class="fa-solid fa-user-slash"></i>

            <strong>Tidak ada pegawai ditemukan</strong>

            <p>
                Coba gunakan kata kunci lain atau ubah filter pencarian.
            </p>
        </div>
    @endforelse
</div>

@if($pegawais->hasPages())
    <div class="atasan-team-pagination">
        {{ $pegawais->links('pagination::bootstrap-5') }}
    </div>
@endif
