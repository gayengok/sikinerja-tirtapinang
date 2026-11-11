# Skema Database E-KPI

Dokumen ini merangkum skema akhir berdasarkan seluruh migration Laravel di `database/migrations`. Diagram relasi aplikasi tersedia di [database-schema.drawio](database-schema.drawio) dan dapat dibuka atau diedit di diagrams.net.

## Tabel Aplikasi

`id` yang dibuat dengan `$table->id()` bertipe `BIGINT UNSIGNED` dan menjadi primary key. `timestamps` berarti `created_at` dan `updated_at`. Kolom `string` tanpa panjang eksplisit adalah `VARCHAR(255)`.

| Tabel | Kolom dan tipe | Kunci dan catatan |
|---|---|---|
| `users` | `id BIGINT UNSIGNED`, `name VARCHAR`, `email VARCHAR`, `email_verified_at TIMESTAMP NULL`, `password VARCHAR`, `role ENUM('master','atasan','pegawai')`, `remember_token VARCHAR(100) NULL`, timestamps | PK `id`; unique `email`; `role` default `pegawai` |
| `divisis` | `id BIGINT UNSIGNED`, `nama_divisi VARCHAR`, timestamps | PK `id` |
| `jabatans` | `id BIGINT UNSIGNED`, `divisi_id BIGINT UNSIGNED`, `nama_jabatan VARCHAR`, `grade VARCHAR NULL`, `base_tukin DECIMAL(15,2)`, timestamps | PK `id`; FK `divisi_id` -> `divisis.id` (cascade delete); `base_tukin` default `0` |
| `pegawais` | `id BIGINT UNSIGNED`, `user_id BIGINT UNSIGNED`, `nama VARCHAR`, `jabatan_id BIGINT UNSIGNED`, `atasan_id BIGINT UNSIGNED NULL`, `tanggal_masuk DATE NULL`, `status VARCHAR`, timestamps | PK `id`; FK `user_id` -> `users.id` (cascade), `jabatan_id` -> `jabatans.id` (cascade), `atasan_id` -> `pegawais.id` (set null); `status` default `aktif` |
| `master_kpis` | `id BIGINT UNSIGNED`, `jabatan_id BIGINT UNSIGNED`, `indikator VARCHAR`, `target_default DECIMAL(15,2) NULL`, `realisasi DECIMAL(15,2) NULL`, `persen_capaian DECIMAL(8,2) NULL`, `skor TINYINT UNSIGNED NULL`, `keterangan TEXT NULL`, `bobot DECIMAL(5,2)`, `arah_capaian VARCHAR`, `urutan INT`, timestamps | PK `id`; FK `jabatan_id` -> `jabatans.id` (cascade); defaults `bobot=0`, `arah_capaian='maksimal'`, `urutan=0` |
| `master_kompetensis` | `id BIGINT UNSIGNED`, `jabatan_id BIGINT UNSIGNED NULL`, `nama_kompetensi VARCHAR`, `definisi_perilaku TEXT NULL`, `urutan INT`, timestamps | PK `id`; FK `jabatan_id` -> `jabatans.id` (set null); `urutan` default `0` |
| `master_template_laporans` | `id BIGINT UNSIGNED`, `divisi_id BIGINT UNSIGNED`, `nama_field VARCHAR`, `tipe_field VARCHAR`, `satuan VARCHAR NULL`, `terhubung_ke_kpi_id BIGINT UNSIGNED NULL`, timestamps | PK `id`; FK `divisi_id` -> `divisis.id` (cascade), `terhubung_ke_kpi_id` -> `master_kpis.id` (set null); `tipe_field` default `angka` |
| `master_template_laporan_options` | `id BIGINT UNSIGNED`, `master_template_laporan_id BIGINT UNSIGNED`, `label VARCHAR(100)`, `urutan INT UNSIGNED`, timestamps | PK `id`; FK `master_template_laporan_id` -> `master_template_laporans.id` (cascade); unique gabungan (`master_template_laporan_id`, `label`); `urutan` default `0` |
| `master_jam_kerjas` | `id BIGINT UNSIGNED`, `jabatan_id BIGINT UNSIGNED NULL`, `hari VARCHAR`, `jam_masuk TIME`, `jam_pulang TIME`, `toleransi_menit INT`, timestamps | PK `id`; FK `jabatan_id` -> `jabatans.id` (set null); `toleransi_menit` default `0` |
| `periode_penilaians` | `id BIGINT UNSIGNED`, `nama_periode VARCHAR`, `tanggal_mulai DATE`, `tanggal_selesai DATE`, `status VARCHAR`, timestamps | PK `id`; `status` default `draft` |
| `master_jenis_potongans` | `id BIGINT UNSIGNED`, `nama_potongan VARCHAR`, `tipe_pelanggaran VARCHAR`, `menit_min INT NULL`, `menit_max INT NULL`, `keterangan VARCHAR NULL`, `persen_potongan DECIMAL(5,2)`, timestamps | PK `id`; `persen_potongan` default `0` |
| `absensis` | `id BIGINT UNSIGNED`, `pegawai_id BIGINT UNSIGNED`, `tanggal DATE`, `jenis_presensi VARCHAR`, `jam_masuk TIME NULL`, `jam_pulang TIME NULL`, `status VARCHAR`, `sumber VARCHAR`, `keterangan TEXT NULL`, `latitude DECIMAL(10,7) NULL`, `longitude DECIMAL(10,7) NULL`, `jarak_lokasi DECIMAL(8,2) NULL`, `akurasi_gps DECIMAL(8,2) NULL`, `di_luar_kantor BOOLEAN`, `foto_path VARCHAR NULL`, timestamps | PK `id`; FK `pegawai_id` -> `pegawais.id` (cascade); unique (`pegawai_id`, `tanggal`); index (`pegawai_id`, `tanggal`, `jenis_presensi`); default `jenis_presensi='masuk'`, `status='hadir'`, `sumber='manual'`, `di_luar_kantor=false` |
| `laporan_harians` | `id BIGINT UNSIGNED`, `pegawai_id BIGINT UNSIGNED`, `tanggal DATE`, `catatan TEXT NULL`, `bukti_gambar VARCHAR NULL`, `status VARCHAR`, `disetujui_oleh BIGINT UNSIGNED NULL`, `disetujui_at TIMESTAMP NULL`, timestamps | PK `id`; FK `pegawai_id` -> `pegawais.id` (cascade), `disetujui_oleh` -> `pegawais.id` (set null); unique (`pegawai_id`, `tanggal`); `status` default `draft` |
| `laporan_harian_details` | `id BIGINT UNSIGNED`, `laporan_harian_id BIGINT UNSIGNED`, `master_template_laporan_id BIGINT UNSIGNED`, `nilai VARCHAR NULL`, timestamps | PK `id`; FK ke `laporan_harians.id` dan `master_template_laporans.id` (keduanya cascade) |
| `penilaian_kpis` | `id BIGINT UNSIGNED`, `pegawai_id BIGINT UNSIGNED`, `periode_id BIGINT UNSIGNED`, `master_kpi_id BIGINT UNSIGNED`, `target DECIMAL(15,2) NULL`, `realisasi_otomatis DECIMAL(15,2) NULL`, `realisasi_final DECIMAL(15,2) NULL`, `persen_capaian DECIMAL(8,2) NULL`, `skor INT NULL`, `keterangan TEXT NULL`, timestamps | PK `id`; FK ke `pegawais.id`, `periode_penilaians.id`, `master_kpis.id` (cascade); unique gabungan (`pegawai_id`, `periode_id`, `master_kpi_id`) |
| `penilaian_kompetensis` | `id BIGINT UNSIGNED`, `pegawai_id BIGINT UNSIGNED`, `periode_id BIGINT UNSIGNED`, `master_kompetensi_id BIGINT UNSIGNED`, `skor INT NULL`, `catatan TEXT NULL`, timestamps | PK `id`; FK ke `pegawais.id`, `periode_penilaians.id`, `master_kompetensis.id` (cascade); unique gabungan (`pegawai_id`, `periode_id`, `master_kompetensi_id`) |
| `presensi_potongans` | `id BIGINT UNSIGNED`, `pegawai_id BIGINT UNSIGNED`, `periode_id BIGINT UNSIGNED`, `jenis_potongan_id BIGINT UNSIGNED`, `jumlah_kejadian INT`, `persen_potongan DECIMAL(5,2)`, timestamps | PK `id`; FK ke `pegawais.id`, `periode_penilaians.id`, `master_jenis_potongans.id` (cascade); defaults `jumlah_kejadian=0`, `persen_potongan=0` |
| `hasil_penilaians` | `id BIGINT UNSIGNED`, `pegawai_id BIGINT UNSIGNED`, `periode_id BIGINT UNSIGNED`, `rata_skor_kpi DECIMAL(8,2) NULL`, `rata_skor_kompetensi DECIMAL(8,2) NULL`, `total_skor_a DECIMAL(8,2) NULL`, `total_skor_b DECIMAL(8,2) NULL`, `nilai_akhir DECIMAL(8,2) NULL`, `predikat VARCHAR NULL`, `total_potongan_presensi DECIMAL(8,2)`, `tukin_bruto DECIMAL(15,2) NULL`, `tukin_neto DECIMAL(15,2) NULL`, `status VARCHAR`, `dikirim_at TIMESTAMP NULL`, timestamps | PK `id`; FK ke `pegawais.id` dan `periode_penilaians.id` (cascade); unique gabungan (`pegawai_id`, `periode_id`); defaults `total_potongan_presensi=0`, `status='draft'` |

## Tabel Infrastruktur Laravel

Tabel berikut dibuat untuk autentikasi, cache, antrean, dan pencatatan migration. Tabel-tabel ini tidak dimasukkan ke ERD relasi bisnis.

| Tabel | Kolom utama |
|---|---|
| `password_reset_tokens` | `email VARCHAR` (PK), `token VARCHAR`, `created_at TIMESTAMP NULL` |
| `sessions` | `id VARCHAR` (PK), `user_id BIGINT UNSIGNED NULL` (index, tanpa FK), `ip_address VARCHAR(45) NULL`, `user_agent TEXT NULL`, `payload TEXT`, `last_activity INT` (index) |
| `cache` | `key VARCHAR` (PK), `value MEDIUMTEXT`, `expiration INT` (index) |
| `cache_locks` | `key VARCHAR` (PK), `owner VARCHAR`, `expiration INT` (index) |
| `jobs` | `id BIGINT UNSIGNED` (PK), `queue VARCHAR` (index), `payload LONGTEXT`, `attempts TINYINT UNSIGNED`, `reserved_at INT UNSIGNED NULL`, `available_at INT UNSIGNED`, `created_at INT UNSIGNED` |
| `job_batches` | `id VARCHAR` (PK), `name VARCHAR`, `total_jobs INT`, `pending_jobs INT`, `failed_jobs INT`, `failed_job_ids LONGTEXT`, `options MEDIUMTEXT NULL`, `cancelled_at INT NULL`, `created_at INT`, `finished_at INT NULL` |
| `failed_jobs` | `id BIGINT UNSIGNED` (PK), `uuid VARCHAR` (unique), `connection TEXT`, `queue TEXT`, `payload LONGTEXT`, `exception LONGTEXT`, `failed_at TIMESTAMP` |
| `migrations` | `id INT` (PK), `migration VARCHAR`, `batch INT` |

## Relasi dan Batasan Penting

- Satu divisi memiliki banyak jabatan dan template laporan.
- Satu jabatan memiliki banyak pegawai, KPI, kompetensi, dan jadwal kerja. Seorang pegawai terhubung ke satu akun dan dapat memiliki atasan dari tabel pegawai yang sama.
- Satu template laporan dapat memiliki banyak opsi dan detail laporan. Template dapat dihubungkan ke KPI secara opsional.
- Penilaian KPI dan kompetensi, potongan presensi, serta hasil penilaian terikat pada pegawai dan periode.
- `absensis` dan `laporan_harians` masing-masing membatasi satu baris untuk kombinasi pegawai dan tanggal. Karena unique index ini tidak menyertakan `jenis_presensi`, beberapa jenis presensi pada tanggal yang sama tidak dapat menjadi baris terpisah untuk pegawai yang sama.
- Tabel `sessions` hanya memiliki index pada `user_id`, bukan foreign key yang dideklarasikan.

Skema mencerminkan migration yang ada di repository dan tidak mengubah database atau menjalankan migration.