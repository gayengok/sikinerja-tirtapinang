<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tanda_tangan_pengguna', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->unsignedInteger('versi');

            $table->enum('metode', [
                'canvas',
                'upload',
            ]);

            $table->string('file_path');

            $table->string('file_hash', 64);

            $table->string('mime_type', 100)
                ->default('image/png');

            $table->unsignedBigInteger('ukuran_file');

            $table->unsignedInteger('lebar');

            $table->unsignedInteger('tinggi');

            $table->boolean('is_active')
                ->default(false);

            $table->unsignedBigInteger('active_user_id')
                ->nullable()
                ->storedAs(
                    'CASE WHEN is_active = 1 THEN user_id ELSE NULL END'
                );

            $table->timestamp('diaktifkan_pada')
                ->nullable();

            $table->timestamp('dinonaktifkan_pada')
                ->nullable();

            $table->timestamps();

            $table->unique(
                ['user_id', 'versi'],
                'ttd_pengguna_versi_unique'
            );

            $table->unique(
                'active_user_id',
                'ttd_pengguna_aktif_unique'
            );

            $table->index(
                ['user_id', 'is_active'],
                'ttd_pengguna_status_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tanda_tangan_pengguna');
    }
};
