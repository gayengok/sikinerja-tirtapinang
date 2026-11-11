<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('absensis', function (Blueprint $table) {
            $table->dropColumn([
                'jam_mulai_istirahat',
                'jam_selesai_istirahat',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('absensis', function (Blueprint $table) {
            $table->time('jam_mulai_istirahat')->nullable()->after('jam_masuk');
            $table->time('jam_selesai_istirahat')->nullable()->after('jam_mulai_istirahat');
        });
    }
};
