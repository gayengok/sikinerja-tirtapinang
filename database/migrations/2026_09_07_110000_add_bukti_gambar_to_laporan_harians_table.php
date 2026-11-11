<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laporan_harians', function (Blueprint $table) {
            $table->string('bukti_gambar')->nullable()->after('catatan');
        });
    }

    public function down(): void
    {
        Schema::table('laporan_harians', function (Blueprint $table) {
            $table->dropColumn('bukti_gambar');
        });
    }
};
