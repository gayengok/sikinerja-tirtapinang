<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('master', 'kepala_bagian', 'atasan', 'pegawai') NOT NULL DEFAULT 'pegawai'");
        } elseif (Schema::getConnection()->getDriverName() === 'sqlite') {
            Schema::table('users', function (Blueprint $table) {
                $table->enum('role', ['master', 'kepala_bagian', 'atasan', 'pegawai'])->default('pegawai')->change();
            });
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("UPDATE users SET role = 'atasan' WHERE role = 'kepala_bagian'");
            DB::statement("ALTER TABLE users MODIFY role ENUM('master', 'atasan', 'pegawai') NOT NULL DEFAULT 'pegawai'");
        } elseif (Schema::getConnection()->getDriverName() === 'sqlite') {
            DB::table('users')->where('role', 'kepala_bagian')->update(['role' => 'atasan']);
            Schema::table('users', function (Blueprint $table) {
                $table->enum('role', ['master', 'atasan', 'pegawai'])->default('pegawai')->change();
            });
        }
    }
};
