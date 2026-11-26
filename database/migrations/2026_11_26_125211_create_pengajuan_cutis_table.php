<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengajuan_cuti', function (Blueprint $table) {
            $table->id();

            $table->string('nomor_pengajuan', 100)
                ->nullable()
                ->unique();

            $table->foreignId('pegawai_id')
                ->constrained('pegawais')
                ->restrictOnDelete();

            $table->foreignId('jenis_cuti_id')
                ->constrained('jenis_cuti')
                ->restrictOnDelete();

            $table->enum('role_pemohon', [
                'pegawai',
                'atasan',
                'kepala_bagian',
            ]);

            $table->date('tanggal_pengajuan')
                ->nullable();

            $table->date('tanggal_mulai');

            $table->date('tanggal_selesai');

            $table->unsignedInteger('jumlah_hari_diajukan')
                ->default(0);

            $table->unsignedInteger('jumlah_hari_disetujui')
                ->default(0);

            $table->text('alasan');

            $table->string('lampiran')
                ->nullable();

            $table->enum('status', [
                'draft',
                'pending_atasan',
                'pending_kepala_bagian',
                'pending_direktur',
                'approved',
                'partially_approved',
                'rejected',
                'cancelled',
            ])->default('draft');

            $table->foreignId('current_approver_id')
                ->nullable()
                ->constrained('pegawais')
                ->restrictOnDelete();

            $table->json('kebijakan_snapshot');

            $table->json('pemohon_snapshot');

            $table->timestamp('diajukan_pada')
                ->nullable();

            $table->timestamp('diselesaikan_pada')
                ->nullable();

            $table->timestamp('dibatalkan_pada')
                ->nullable();

            $table->text('alasan_pembatalan')
                ->nullable();

            $table->timestamps();

            $table->index(
                ['pegawai_id', 'status'],
                'pengajuan_cuti_pegawai_status_index'
            );

            $table->index(
                ['current_approver_id', 'status'],
                'pengajuan_cuti_approver_status_index'
            );

            $table->index(
                ['pegawai_id', 'tanggal_mulai', 'tanggal_selesai'],
                'pengajuan_cuti_pegawai_tanggal_index'
            );

            $table->index(
                ['jenis_cuti_id', 'status'],
                'pengajuan_cuti_jenis_status_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuan_cuti');
    }
};
