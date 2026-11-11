<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('absensis', function (Blueprint $table) {
            $table->string('jenis_presensi')->default('masuk')->after('tanggal');
            $table->time('jam_mulai_istirahat')->nullable()->after('jam_masuk');
            $table->time('jam_selesai_istirahat')->nullable()->after('jam_mulai_istirahat');
            $table->string('foto_path')->nullable()->after('keterangan');
            $table->decimal('akurasi_gps', 8, 2)->nullable()->after('jarak_lokasi');

            $table->index(['pegawai_id', 'tanggal', 'jenis_presensi']);
        });
    }

    public function down(): void
    {
        Schema::table('absensis', function (Blueprint $table) {
            $table->dropIndex('absensis_pegawai_id_tanggal_jenis_presensi_index');
            $table->dropColumn([
                'jenis_presensi',
                'jam_mulai_istirahat',
                'jam_selesai_istirahat',
                'foto_path',
                'akurasi_gps',
            ]);
        });
    }
};
