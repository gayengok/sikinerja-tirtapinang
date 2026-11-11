<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('master_template_laporans', 'jabatan_id')) {
            Schema::table('master_template_laporans', function (Blueprint $table) {
                $table->unsignedBigInteger('jabatan_id')->nullable()->after('divisi_id');
            });
        }

        $hasJabatanForeignKey = collect(Schema::getForeignKeys('master_template_laporans'))
            ->contains(fn (array $foreignKey) => in_array('jabatan_id', $foreignKey['columns'], true)
                && $foreignKey['foreign_table'] === 'jabatans');

        if (! $hasJabatanForeignKey) {
            Schema::table('master_template_laporans', function (Blueprint $table) {
                $table->foreign('jabatan_id')
                    ->references('id')
                    ->on('jabatans')
                    ->nullOnDelete();
            });
        }

        DB::table('master_template_laporans')
            ->whereNotNull('terhubung_ke_kpi_id')
            ->get(['id', 'terhubung_ke_kpi_id'])
            ->each(function (object $template): void {
                $jabatanId = DB::table('master_kpis')
                    ->where('id', $template->terhubung_ke_kpi_id)
                    ->value('jabatan_id');

                if ($jabatanId) {
                    DB::table('master_template_laporans')
                        ->where('id', $template->id)
                        ->update(['jabatan_id' => $jabatanId]);
                }
            });
    }

    public function down(): void
    {
        if (Schema::hasColumn('master_template_laporans', 'jabatan_id')) {
            Schema::table('master_template_laporans', function (Blueprint $table) {
                $table->dropConstrainedForeignId('jabatan_id');
            });
        }
    }
};
