<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surat_permohonan_cuti', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pengajuan_cuti_id')
                ->constrained('pengajuan_cuti')
                ->restrictOnDelete();

            $table->foreignId('template_surat_cuti_id')
                ->constrained('template_surat_cuti')
                ->restrictOnDelete();

            $table->foreignId('tanda_tangan_pemohon_id')
                ->constrained('tanda_tangan_pengguna')
                ->restrictOnDelete();

            $table->foreignId('persetujuan_cuti_id')
                ->nullable()
                ->constrained('persetujuan_cuti')
                ->restrictOnDelete();

            $table->string('nomor_surat', 150);

            $table->unsignedInteger('versi');

            $table->enum('status_dokumen', [
                'preview',
                'final',
                'superseded',
            ])->default('preview');

            $table->string('status_pengajuan_snapshot', 50);

            $table->json('template_snapshot');

            $table->json('pemohon_snapshot');

            $table->json('pengajuan_snapshot');

            $table->json('detail_snapshot');

            $table->json('persetujuan_snapshot');

            $table->json('tanda_tangan_snapshot');

            $table->string('file_path')
                ->nullable();

            $table->string('file_hash', 64)
                ->nullable();

            $table->unsignedBigInteger('ukuran_file')
                ->nullable();

            $table->string('token_verifikasi', 64)
                ->unique();

            $table->boolean('is_latest')
                ->default(false);

            $table->unsignedBigInteger('latest_pengajuan_id')
                ->nullable()
                ->storedAs(
                    'CASE WHEN is_latest = 1 THEN pengajuan_cuti_id ELSE NULL END'
                );

            $table->foreignId('dibuat_oleh')
                ->nullable()
                ->constrained('users')
                ->restrictOnDelete();

            $table->timestamp('diterbitkan_pada')
                ->nullable();

            $table->timestamps();

            $table->unique(
                ['pengajuan_cuti_id', 'versi'],
                'surat_cuti_pengajuan_versi_unique'
            );

            $table->unique(
                'latest_pengajuan_id',
                'surat_cuti_latest_unique'
            );

            $table->index(
                ['pengajuan_cuti_id', 'status_dokumen'],
                'surat_cuti_pengajuan_status_index'
            );

            $table->index(
                'nomor_surat',
                'surat_cuti_nomor_index'
            );

            $table->index(
                ['status_dokumen', 'diterbitkan_pada'],
                'surat_cuti_status_terbit_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_permohonan_cuti');
    }
};
