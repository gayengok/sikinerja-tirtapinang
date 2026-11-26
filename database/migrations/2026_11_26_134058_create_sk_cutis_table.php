<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sk_cuti', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pengajuan_cuti_id')
                ->constrained('pengajuan_cuti')
                ->restrictOnDelete();

            $table->foreignId('persetujuan_cuti_id')
                ->constrained('persetujuan_cuti')
                ->restrictOnDelete();

            $table->foreignId('surat_permohonan_cuti_id')
                ->constrained('surat_permohonan_cuti')
                ->restrictOnDelete();

            $table->string('nomor_sk', 150)
                ->unique();

            $table->date('tanggal_sk');

            $table->date('tanggal_mulai_cuti');

            $table->date('tanggal_selesai_cuti');

            $table->unsignedInteger('jumlah_hari_disetujui');

            $table->json('tanggal_disetujui_snapshot');

            $table->json('pemohon_snapshot');

            $table->json('direktur_snapshot');

            $table->string('nama_file_asli');

            $table->string('file_path');

            $table->string('file_hash', 64);

            $table->string('mime_type', 100)
                ->default('application/pdf');

            $table->unsignedBigInteger('ukuran_file');

            $table->text('catatan')
                ->nullable();

            $table->foreignId('diunggah_oleh')
                ->constrained('users')
                ->restrictOnDelete();

            $table->timestamp('diunggah_pada');

            $table->timestamp('diterbitkan_pada')
                ->nullable();

            $table->timestamps();

            $table->unique(
                'pengajuan_cuti_id',
                'sk_cuti_pengajuan_unique'
            );

            $table->unique(
                'persetujuan_cuti_id',
                'sk_cuti_persetujuan_unique'
            );

            $table->unique(
                'surat_permohonan_cuti_id',
                'sk_cuti_surat_unique'
            );

            $table->index(
                'tanggal_sk',
                'sk_cuti_tanggal_index'
            );

            $table->index(
                'diterbitkan_pada',
                'sk_cuti_terbit_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sk_cuti');
    }
};
