<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penilaian_kompetensis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pegawai_id')->constrained('pegawais')->cascadeOnDelete();
            $table->foreignId('periode_id')->constrained('periode_penilaians')->cascadeOnDelete();
            $table->foreignId('master_kompetensi_id')->constrained('master_kompetensis')->cascadeOnDelete();
            $table->integer('skor')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->unique(['pegawai_id', 'periode_id', 'master_kompetensi_id'], 'penilaian_kompetensi_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penilaian_kompetensis');
    }
};
