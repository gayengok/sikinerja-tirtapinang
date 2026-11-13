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
            DB::statement("ALTER TABLE users MODIFY role ENUM('master', 'direktur', 'kepala_bagian', 'atasan', 'pegawai') NOT NULL DEFAULT 'pegawai'");
        } elseif (Schema::getConnection()->getDriverName() === 'sqlite') {
            Schema::table('users', function (Blueprint $table) {
                $table->enum('role', ['master', 'direktur', 'kepala_bagian', 'atasan', 'pegawai'])->default('pegawai')->change();
            });
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            $direkturIds = DB::table('pegawais')
                ->join('users', 'users.id', '=', 'pegawais.user_id')
                ->where('users.role', 'direktur')
                ->pluck('pegawais.id');
            DB::table('pegawais')->whereIn('atasan_id', $direkturIds)->update(['atasan_id' => null]);
            DB::statement("UPDATE users SET role = 'kepala_bagian' WHERE role = 'direktur'");
            DB::statement("ALTER TABLE users MODIFY role ENUM('master', 'kepala_bagian', 'atasan', 'pegawai') NOT NULL DEFAULT 'pegawai'");
        } elseif (Schema::getConnection()->getDriverName() === 'sqlite') {
            $direkturIds = DB::table('pegawais')
                ->join('users', 'users.id', '=', 'pegawais.user_id')
                ->where('users.role', 'direktur')
                ->pluck('pegawais.id');
            DB::table('pegawais')->whereIn('atasan_id', $direkturIds)->update(['atasan_id' => null]);
            DB::table('users')->where('role', 'direktur')->update(['role' => 'kepala_bagian']);
            Schema::table('users', function (Blueprint $table) {
                $table->enum('role', ['master', 'kepala_bagian', 'atasan', 'pegawai'])->default('pegawai')->change();
            });
        }
    }
};
