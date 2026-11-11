<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penilaian_kpis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pegawai_id')->constrained('pegawais')->cascadeOnDelete();
            $table->foreignId('periode_id')->constrained('periode_penilaians')->cascadeOnDelete();
            $table->foreignId('master_kpi_id')->constrained('master_kpis')->cascadeOnDelete();
            $table->decimal('target', 15, 2)->nullable();
            $table->decimal('realisasi_otomatis', 15, 2)->nullable();
            $table->decimal('realisasi_final', 15, 2)->nullable();
            $table->decimal('persen_capaian', 8, 2)->nullable();
            $table->integer('skor')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->unique(['pegawai_id', 'periode_id', 'master_kpi_id'], 'penilaian_kpi_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penilaian_kpis');
    }
};
