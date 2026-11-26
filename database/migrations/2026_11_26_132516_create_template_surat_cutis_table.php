<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('template_surat_cuti', function (Blueprint $table) {
            $table->id();

            $table->string('kode_template', 50)
                ->default('PERMOHONAN-CUTI');

            $table->string('nama_template', 150);

            $table->unsignedInteger('versi');

            $table->string('nama_perusahaan', 200);

            $table->text('alamat_perusahaan')
                ->nullable();

            $table->string('telepon_perusahaan', 50)
                ->nullable();

            $table->string('email_perusahaan', 150)
                ->nullable();

            $table->string('website_perusahaan', 255)
                ->nullable();

            $table->string('logo_path')
                ->nullable();

            $table->string('judul_surat', 200)
                ->default('SURAT PERMOHONAN CUTI');

            $table->string('format_nomor_surat', 150);

            $table->text('isi_pembuka');

            $table->text('isi_penutup');

            $table->string('tempat_terbit', 100)
                ->nullable();

            $table->text('footer_surat')
                ->nullable();

            $table->json('pengaturan_tampilan')
                ->nullable();

            $table->boolean('is_active')
                ->default(false);

            $table->unsignedTinyInteger('active_slot')
                ->nullable()
                ->storedAs(
                    'CASE WHEN is_active = 1 THEN 1 ELSE NULL END'
                );

            $table->foreignId('dibuat_oleh')
                ->constrained('users')
                ->restrictOnDelete();

            $table->timestamp('diaktifkan_pada')
                ->nullable();

            $table->timestamp('dinonaktifkan_pada')
                ->nullable();

            $table->timestamps();

            $table->unique(
                ['kode_template', 'versi'],
                'template_cuti_kode_versi_unique'
            );

            $table->unique(
                'active_slot',
                'template_cuti_aktif_unique'
            );

            $table->index(
                ['kode_template', 'is_active'],
                'template_cuti_kode_status_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('template_surat_cuti');
    }
};
