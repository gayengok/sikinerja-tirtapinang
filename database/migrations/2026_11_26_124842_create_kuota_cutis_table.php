<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kuota_cuti', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pegawai_id')
                ->constrained('pegawais')
                ->restrictOnDelete();

            $table->foreignId('jenis_cuti_id')
                ->constrained('jenis_cuti')
                ->restrictOnDelete();

            $table->string('kode_periode', 100);

            $table->unsignedSmallInteger('tahun')
                ->nullable();

            $table->date('tanggal_mulai_periode');

            $table->date('tanggal_akhir_periode')
                ->nullable();

            $table->unsignedInteger('kuota_dasar')
                ->nullable();

            $table->unsignedInteger('kuota_akumulasi')
                ->default(0);

            $table->integer('kuota_penyesuaian')
                ->default(0);

            $table->unsignedInteger('kuota_terpakai')
                ->default(0);

            $table->unsignedInteger('kuota_direservasi')
                ->default(0);

            $table->unsignedInteger('kuota_hangus')
                ->default(0);

            $table->unsignedSmallInteger('tahun_ke_siklus')
                ->nullable();

            $table->json('kebijakan_snapshot');

            $table->boolean('is_active')
                ->default(true);

            $table->timestamps();

            $table->unique(
                ['pegawai_id', 'jenis_cuti_id', 'kode_periode'],
                'kuota_cuti_pegawai_jenis_periode_unique'
            );

            $table->index(
                ['pegawai_id', 'tahun'],
                'kuota_cuti_pegawai_tahun_index'
            );

            $table->index(
                ['pegawai_id', 'is_active'],
                'kuota_cuti_pegawai_active_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kuota_cuti');
    }
};
