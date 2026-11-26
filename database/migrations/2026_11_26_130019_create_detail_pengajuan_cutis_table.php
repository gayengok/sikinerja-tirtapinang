<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_pengajuan_cuti', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pengajuan_cuti_id')
                ->constrained('pengajuan_cuti')
                ->restrictOnDelete();

            $table->foreignId('kuota_cuti_id')
                ->nullable()
                ->constrained('kuota_cuti')
                ->restrictOnDelete();

            $table->date('tanggal');

            $table->boolean('is_hari_kerja')
                ->default(true);

            $table->boolean('dihitung_kuota')
                ->default(true);

            $table->enum('status', [
                'pending',
                'approved',
                'rejected',
                'excluded',
                'cancelled',
            ])->default('pending');

            $table->foreignId('ditolak_oleh')
                ->nullable()
                ->constrained('pegawais')
                ->restrictOnDelete();

            $table->timestamp('ditolak_pada')
                ->nullable();

            $table->timestamp('disetujui_pada')
                ->nullable();

            $table->timestamps();

            $table->unique(
                ['pengajuan_cuti_id', 'tanggal'],
                'detail_cuti_pengajuan_tanggal_unique'
            );

            $table->index(
                ['pengajuan_cuti_id', 'status'],
                'detail_cuti_pengajuan_status_index'
            );

            $table->index(
                ['kuota_cuti_id', 'status'],
                'detail_cuti_kuota_status_index'
            );

            $table->index(
                ['tanggal', 'status'],
                'detail_cuti_tanggal_status_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_pengajuan_cuti');
    }
};
