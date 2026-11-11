<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('master_kpis', function (Blueprint $table) {
            $table->decimal('realisasi', 15, 2)->nullable()->after('target_default');
            $table->decimal('persen_capaian', 8, 2)->nullable()->after('realisasi');
            $table->unsignedTinyInteger('skor')->nullable()->after('persen_capaian');
            $table->text('keterangan')->nullable()->after('skor');
        });
    }

    public function down(): void
    {
        Schema::table('master_kpis', function (Blueprint $table) {
            $table->dropColumn(['realisasi', 'persen_capaian', 'skor', 'keterangan']);
        });
    }
};
