<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jenis_cuti', function (Blueprint $table) {
            $table->id();

            $table->string('kode', 30)->unique();
            $table->string('nama', 150);
            $table->text('deskripsi')->nullable();

            $table->unsignedInteger('kuota_hari')->nullable();

            $table->enum('periode_kuota', [
                'tahunan',
                'per_kejadian',
                'seumur_kerja',
                'tanpa_batas',
            ])->default('tahunan');

            $table->unsignedInteger('minimal_hari_pengajuan')
                ->default(0);

            $table->unsignedInteger('maksimal_hari_pengajuan')
                ->nullable();

            $table->boolean('boleh_hari_h')
                ->default(false);

            $table->enum('metode_hitung_hari', [
                'hari_kerja',
                'hari_kalender',
            ])->default('hari_kerja');

            $table->unsignedInteger('minimal_masa_kerja_bulan')
                ->default(0);

            $table->boolean('bisa_akumulasi')
                ->default(false);

            $table->unsignedInteger('tambahan_akumulasi_hari')
                ->default(0);

            $table->unsignedInteger('maksimal_tahun_akumulasi')
                ->default(0);

            $table->unsignedSmallInteger('tahun_awal_siklus')
                ->nullable();

            $table->boolean('wajib_lampiran')
                ->default(false);

            $table->boolean('is_active')
                ->default(true);

            $table->timestamps();

            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jenis_cuti');
    }
};
