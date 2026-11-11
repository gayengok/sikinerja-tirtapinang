<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_kpis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jabatan_id')->constrained('jabatans')->cascadeOnDelete();
            $table->string('indikator');
            $table->decimal('target_default', 15, 2)->nullable();
            $table->decimal('bobot', 5, 2)->default(0);
            $table->string('arah_capaian')->default('maksimal'); // maksimal / minimal
            $table->integer('urutan')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_kpis');
    }
};
