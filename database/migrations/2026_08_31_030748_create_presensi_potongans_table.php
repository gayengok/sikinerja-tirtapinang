<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('presensi_potongans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pegawai_id')->constrained('pegawais')->cascadeOnDelete();
            $table->foreignId('periode_id')->constrained('periode_penilaians')->cascadeOnDelete();
            $table->foreignId('jenis_potongan_id')->constrained('master_jenis_potongans')->cascadeOnDelete();
            $table->integer('jumlah_kejadian')->default(0);
            $table->decimal('persen_potongan', 5, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presensi_potongans');
    }
};
