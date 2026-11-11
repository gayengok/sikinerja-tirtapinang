<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_template_laporans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('divisi_id')->constrained('divisis')->cascadeOnDelete();
            $table->string('nama_field');
            $table->string('tipe_field')->default('angka'); // angka / teks / dropdown
            $table->string('satuan')->nullable();
            $table->foreignId('terhubung_ke_kpi_id')->nullable()->constrained('master_kpis')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_template_laporans');
    }
};
