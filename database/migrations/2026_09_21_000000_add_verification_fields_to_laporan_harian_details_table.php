<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laporan_harian_details', function (Blueprint $table) {
            $table->string('nilai_terverifikasi')->nullable()->after('nilai');
            $table->foreignId('diverifikasi_oleh')->nullable()->after('nilai_terverifikasi')
                ->constrained('pegawais')->nullOnDelete();
            $table->timestamp('diverifikasi_at')->nullable()->after('diverifikasi_oleh');
            $table->text('catatan_verifikasi')->nullable()->after('diverifikasi_at');
        });
    }

    public function down(): void
    {
        Schema::table('laporan_harian_details', function (Blueprint $table) {
            $table->dropForeign(['diverifikasi_oleh']);
            $table->dropColumn([
                'nilai_terverifikasi',
                'diverifikasi_oleh',
                'diverifikasi_at',
                'catatan_verifikasi',
            ]);
        });
    }
};
