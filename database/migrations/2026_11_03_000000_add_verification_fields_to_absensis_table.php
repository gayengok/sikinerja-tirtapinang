<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('absensis', function (Blueprint $table) {
            $table->string('status_verifikasi')->nullable()->after('akurasi_gps');
            $table->text('catatan_verifikasi')->nullable()->after('status_verifikasi');
            $table->foreignId('diverifikasi_oleh')->nullable()->after('catatan_verifikasi')
                ->constrained('pegawais')->nullOnDelete();
            $table->timestamp('diverifikasi_pada')->nullable()->after('diverifikasi_oleh');
        });
    }

    public function down(): void
    {
        Schema::table('absensis', function (Blueprint $table) {
            $table->dropForeign(['diverifikasi_oleh']);
            $table->dropColumn([
                'status_verifikasi',
                'catatan_verifikasi',
                'diverifikasi_oleh',
                'diverifikasi_pada',
            ]);
        });
    }
};
