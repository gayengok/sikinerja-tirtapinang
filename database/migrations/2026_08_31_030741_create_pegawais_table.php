<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pegawais', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('nama');
            $table->foreignId('jabatan_id')->constrained('jabatans')->cascadeOnDelete();
            $table->foreignId('atasan_id')->nullable()->constrained('pegawais')->nullOnDelete();
            $table->date('tanggal_masuk')->nullable();
            $table->string('status')->default('aktif'); // aktif / nonaktif
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pegawais');
    }
};
