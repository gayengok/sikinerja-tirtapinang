<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('persetujuan_cuti', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pengajuan_cuti_id')
                ->constrained('pengajuan_cuti')
                ->restrictOnDelete();

            $table->foreignId('approver_id')
                ->constrained('pegawais')
                ->restrictOnDelete();

            $table->enum('role_approver', [
                'atasan',
                'kepala_bagian',
                'direktur',
            ]);

            $table->unsignedSmallInteger('urutan');

            $table->enum('status', [
                'waiting',
                'pending',
                'approved',
                'partially_approved',
                'rejected',
                'skipped',
                'cancelled',
            ])->default('waiting');

            $table->unsignedInteger('jumlah_hari_diterima')
                ->default(0);

            $table->unsignedInteger('jumlah_hari_disetujui')
                ->default(0);

            $table->unsignedInteger('jumlah_hari_ditolak')
                ->default(0);

            $table->json('tanggal_diterima')
                ->nullable();

            $table->json('tanggal_disetujui')
                ->nullable();

            $table->json('tanggal_ditolak')
                ->nullable();

            $table->text('catatan')
                ->nullable();

            $table->json('approver_snapshot');

            $table->string('tanda_tangan_path')
                ->nullable();

            $table->string('token_verifikasi', 100)
                ->nullable()
                ->unique();

            $table->string('hash_verifikasi', 64)
                ->nullable();

            $table->timestamp('diterima_pada')
                ->nullable();

            $table->timestamp('diproses_pada')
                ->nullable();

            $table->timestamps();

            $table->unique(
                ['pengajuan_cuti_id', 'urutan'],
                'persetujuan_cuti_pengajuan_urutan_unique'
            );

            $table->unique(
                ['pengajuan_cuti_id', 'approver_id'],
                'persetujuan_cuti_pengajuan_approver_unique'
            );

            $table->index(
                ['approver_id', 'status'],
                'persetujuan_cuti_approver_status_index'
            );

            $table->index(
                ['pengajuan_cuti_id', 'status'],
                'persetujuan_cuti_pengajuan_status_index'
            );

            $table->index(
                ['role_approver', 'status'],
                'persetujuan_cuti_role_status_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('persetujuan_cuti');
    }
};
