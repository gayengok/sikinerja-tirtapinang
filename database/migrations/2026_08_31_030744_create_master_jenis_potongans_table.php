<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_jenis_potongans', function (Blueprint $table) {
            $table->id();
            $table->string('nama_potongan');
            $table->string('tipe_pelanggaran'); // terlambat / tidak_absen / alpa
            $table->integer('menit_min')->nullable();
            $table->integer('menit_max')->nullable();
            $table->string('keterangan')->nullable();
            $table->decimal('persen_potongan', 5, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_jenis_potongans');
    }
};
