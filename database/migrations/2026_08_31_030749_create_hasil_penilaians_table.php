<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hasil_penilaians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pegawai_id')->constrained('pegawais')->cascadeOnDelete();
            $table->foreignId('periode_id')->constrained('periode_penilaians')->cascadeOnDelete();
            $table->decimal('rata_skor_kpi', 8, 2)->nullable();
            $table->decimal('rata_skor_kompetensi', 8, 2)->nullable();
            $table->decimal('total_skor_a', 8, 2)->nullable();
            $table->decimal('total_skor_b', 8, 2)->nullable();
            $table->decimal('nilai_akhir', 8, 2)->nullable();
            $table->string('predikat')->nullable();
            $table->decimal('total_potongan_presensi', 8, 2)->default(0);
            $table->decimal('tukin_bruto', 15, 2)->nullable();
            $table->decimal('tukin_neto', 15, 2)->nullable();
            $table->string('status')->default('draft'); // draft/dikirim
            $table->timestamp('dikirim_at')->nullable();
            $table->timestamps();

            $table->unique(['pegawai_id', 'periode_id'], 'hasil_penilaian_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hasil_penilaians');
    }
};
