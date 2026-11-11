<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_jam_kerjas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jabatan_id')->nullable()->constrained('jabatans')->nullOnDelete();
            $table->string('hari');
            $table->time('jam_masuk');
            $table->time('jam_pulang');
            $table->integer('toleransi_menit')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_jam_kerjas');
    }
};
