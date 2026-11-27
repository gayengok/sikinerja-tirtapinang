<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('master_jam_kerjas', function (Blueprint $table) {
            $table->string('jenis_jadwal')->default('normal')->after('hari');
            $table->unsignedTinyInteger('nomor_shift')->nullable()->after('jenis_jadwal');
            $table->index(['jenis_jadwal', 'nomor_shift', 'hari'], 'master_jam_kerjas_schedule_lookup_index');
        });
    }

    public function down(): void
    {
        Schema::table('master_jam_kerjas', function (Blueprint $table) {
            $table->dropIndex('master_jam_kerjas_schedule_lookup_index');
            $table->dropColumn(['jenis_jadwal', 'nomor_shift']);
        });
    }
};
