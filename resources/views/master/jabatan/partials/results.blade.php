<div class="mj-results-meta">
    <span>
        <i class="fa-solid fa-list"></i>
        {{ $jabatans->total() }} jabatan ditemukan
    </span>
</div>

<div class="mj-table-wrap">
    <table class="mj-table">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Jabatan</th>
                <th>Grade</th>
                <th>Divisi</th>
                <th>Base Tukin</th>
                <th>Pegawai</th>
                <th class="mj-actions-heading">Aksi</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($jabatans as $jabatan)
                <tr>
                    <td>
                        <span class="mj-number">
                            {{ $jabatans->firstItem() + $loop->index }}
                        </span>
                    </td>

                    <td>
                        <strong class="mj-name">
                            {{ $jabatan->nama_jabatan }}
                        </strong>
                    </td>

                    <td>
                        <span class="mj-chip">
                            Grade {{ $jabatan->grade }}
                        </span>
                    </td>

                    <td>
                        <span class="mj-division">
                            <i class="fa-regular fa-building"></i>
                            {{ $jabatan->divisi?->nama_divisi ?? '-' }}
                        </span>
                    </td>

                    <td>
                        <strong class="mj-tukin">
                            Rp {{ number_format($jabatan->base_tukin, 0, ',', '.') }}
                        </strong>
                    </td>

                    <td>
                        {{ $jabatan->pegawais_count }}
                    </td>

                    <td>
                        <div class="mj-actions">
                            <a
                                href="{{ route('master.jabatan.show', $jabatan) }}"
                                aria-label="Detail {{ $jabatan->nama_jabatan }}"
                                title="Detail"
                            >
                                <i class="fa-regular fa-eye"></i>
                            </a>

                            <a
                                href="{{ route('master.jabatan.edit', $jabatan) }}"
                                aria-label="Edit {{ $jabatan->nama_jabatan }}"
                                title="Edit"
                            >
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>

                            <form
                                action="{{ route('master.jabatan.destroy', $jabatan) }}"
                                method="POST"
                                class="form-delete"
                                data-confirm-message="Yakin ingin menghapus jabatan ini?"
                            >
                                @csrf
                                @method('DELETE')

                                <button
                                    type="submit"
                                    aria-label="Hapus {{ $jabatan->nama_jabatan }}"
                                    title="Hapus"
                                >
                                    <i class="fa-regular fa-trash-can"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">
                        <div class="mj-empty">
                            <i class="fa-regular fa-folder-open"></i>
                            <strong>Tidak ada jabatan ditemukan</strong>
                            <p>Coba gunakan kata kunci atau filter lain.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mj-mobile-list">
    @forelse ($jabatans as $jabatan)
        <article class="mj-mobile-card">
            <div class="mj-mobile-top">
                <span class="mj-number">
                    {{ $jabatans->firstItem() + $loop->index }}
                </span>

                <span class="mj-chip">
                    Grade {{ $jabatan->grade }}
                </span>
            </div>

            <h3>{{ $jabatan->nama_jabatan }}</h3>

            <p>
                <i class="fa-regular fa-building"></i>
                {{ $jabatan->divisi?->nama_divisi ?? '-' }}
            </p>

            <div class="mj-mobile-bottom">
                <div>
                    <small>BASE TUKIN</small>

                    <strong>
                        Rp {{ number_format($jabatan->base_tukin, 0, ',', '.') }}
                    </strong>

                    <small>{{ $jabatan->pegawais_count }} pegawai</small>
                </div>

                <div class="mj-actions">
                    <a
                        href="{{ route('master.jabatan.show', $jabatan) }}"
                        aria-label="Detail"
                    >
                        <i class="fa-regular fa-eye"></i>
                    </a>

                    <a
                        href="{{ route('master.jabatan.edit', $jabatan) }}"
                        aria-label="Edit"
                    >
                        <i class="fa-solid fa-pen-to-square"></i>
                    </a>

                    <form
                        method="POST"
                        action="{{ route('master.jabatan.destroy', $jabatan) }}"
                        class="form-delete"
                        data-confirm-message="Yakin ingin menghapus jabatan ini?"
                    >
                        @csrf
                        @method('DELETE')

                        <button type="submit" aria-label="Hapus">
                            <i class="fa-regular fa-trash-can"></i>
                        </button>
                    </form>
                </div>
            </div>
        </article>
    @empty
        <div class="mj-empty">
            <i class="fa-regular fa-folder-open"></i>
            <strong>Tidak ada jabatan ditemukan</strong>
            <p>Coba gunakan kata kunci atau filter lain.</p>
        </div>
    @endforelse
</div>

@if ($jabatans->hasPages())
    <nav class="mj-pagination" aria-label="Navigasi halaman jabatan">
        {{ $jabatans->links('pagination::bootstrap-5') }}
    </nav>
@endif
