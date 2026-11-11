<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = 'master_template_laporan_options';

        if (! Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('master_template_laporan_id');
                $table->string('label', 100);
                $table->unsignedInteger('urutan')->default(0);
                $table->timestamps();
            });
        }

        $hasUniqueIndex = collect(Schema::getIndexes($tableName))->contains(
            fn (array $index) => $index['unique']
                && $index['columns'] === ['master_template_laporan_id', 'label']
        );

        if (! $hasUniqueIndex) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->unique(
                    ['master_template_laporan_id', 'label'],
                    'mtlo_template_label_uq'
                );
            });
        }

        $hasForeignKey = collect(Schema::getForeignKeys($tableName))->contains(
            fn (array $foreignKey) => $foreignKey['columns'] === ['master_template_laporan_id']
        );

        if (! $hasForeignKey) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->foreign(
                    'master_template_laporan_id',
                    'mtlo_template_fk'
                )
                    ->references('id')
                    ->on('master_template_laporans')
                    ->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('master_template_laporan_options');
    }
};
