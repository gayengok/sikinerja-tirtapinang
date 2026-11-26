<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mutasi_kuota_cuti', function (Blueprint $table) {
            $table->id();

            $table->foreignId('kuota_cuti_id')
                ->constrained('kuota_cuti')
                ->restrictOnDelete();

            $table->foreignId('pengajuan_cuti_id')
                ->nullable()
                ->constrained('pengajuan_cuti')
                ->restrictOnDelete();

            $table->foreignId('persetujuan_cuti_id')
                ->nullable()
                ->constrained('persetujuan_cuti')
                ->restrictOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->restrictOnDelete();

            $table->string('kode_mutasi', 100)
                ->unique();

            $table->enum('jenis_mutasi', [
                'pemberian_kuota',
                'akumulasi',
                'reservasi',
                'pelepasan_reservasi',
                'penggunaan',
                'penyesuaian_tambah',
                'penyesuaian_kurang',
                'kedaluwarsa',
                'reset_akumulasi',
                'koreksi',
            ]);

            $table->unsignedInteger('jumlah_hari')
                ->default(0);

            $table->integer('perubahan_sisa')
                ->nullable();

            $table->json('saldo_sebelum');

            $table->json('saldo_sesudah');

            $table->text('keterangan');

            $table->json('metadata')
                ->nullable();

            $table->timestamp('terjadi_pada')
                ->useCurrent();

            $table->timestamps();

            $table->index(
                ['kuota_cuti_id', 'terjadi_pada'],
                'mutasi_cuti_kuota_waktu_index'
            );

            $table->index(
                ['pengajuan_cuti_id', 'jenis_mutasi'],
                'mutasi_cuti_pengajuan_jenis_index'
            );

            $table->index(
                ['jenis_mutasi', 'terjadi_pada'],
                'mutasi_cuti_jenis_waktu_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mutasi_kuota_cuti');
    }
};
