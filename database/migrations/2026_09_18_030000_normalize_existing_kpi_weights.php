<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('master_kpis')
            ->where('bobot', '>', 0)
            ->update(['bobot' => DB::raw('ROUND(bobot * 0.6, 2)')]);
    }

    public function down(): void
    {
        DB::table('master_kpis')
            ->where('bobot', '>', 0)
            ->update(['bobot' => DB::raw('ROUND(bobot / 0.6, 2)')]);
    }
};
