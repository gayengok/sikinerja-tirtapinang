<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('periode_penilaians', function (Blueprint $table) {
            $table->id();
            $table->string('nama_periode');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->string('status')->default('draft'); // draft / berjalan / final
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('periode_penilaians');
    }
};
