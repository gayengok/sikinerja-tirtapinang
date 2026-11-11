<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laporan_harians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pegawai_id')->constrained('pegawais')->cascadeOnDelete();
            $table->date('tanggal');
            $table->text('catatan')->nullable();
            $table->string('status')->default('draft'); // draft/diajukan/disetujui/ditolak
            $table->foreignId('disetujui_oleh')->nullable()->constrained('pegawais')->nullOnDelete();
            $table->timestamp('disetujui_at')->nullable();
            $table->timestamps();

            $table->unique(['pegawai_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_harians');
    }
};
