<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_kompetensis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jabatan_id')->nullable()->constrained('jabatans')->nullOnDelete();
            $table->string('nama_kompetensi');
            $table->text('definisi_perilaku')->nullable();
            $table->integer('urutan')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_kompetensis');
    }
};
