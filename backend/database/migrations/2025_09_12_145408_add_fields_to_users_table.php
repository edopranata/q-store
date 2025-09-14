<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan field baru ke tabel users sesuai dengan spesifikasi DATABASE_SCHEMA.md
     * 
     * Spesifikasi tabel users berdasarkan DATABASE_SCHEMA.md:
     * - id: INT PRIMARY KEY AUTO_INCREMENT (sudah ada)
     * - username: VARCHAR(50) UNIQUE NOT NULL
     * - email: VARCHAR(100) UNIQUE NOT NULL (sudah ada)
     * - password_hash: VARCHAR(255) NOT NULL (sudah ada sebagai 'password')
     * - full_name: VARCHAR(100) NOT NULL
     * - is_active: BOOLEAN DEFAULT TRUE
     * - last_login: DATETIME NULL
     * - created_at: TIMESTAMP DEFAULT CURRENT_TIMESTAMP (sudah ada)
     * - updated_at: TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP (sudah ada)
     * 
     * Note: Field 'role' tidak diperlukan karena menggunakan spatie/laravel-permission
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Validasi struktur tabel yang ada
            if (!Schema::hasColumn('users', 'email')) {
                throw new \Exception('Tabel users tidak memiliki kolom email yang diperlukan');
            }
            
            // Tambahkan kolom username jika belum ada
            if (!Schema::hasColumn('users', 'username')) {
                $table->string('username', 50)->unique()->after('name');
            }
            
            // Tambahkan kolom full_name jika belum ada
            if (!Schema::hasColumn('users', 'full_name')) {
                $table->string('full_name', 100)->after('username');
            }
            
            // Field 'role' tidak ditambahkan karena menggunakan spatie/laravel-permission
            
            // Tambahkan kolom is_active jika belum ada
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('full_name');
            }
            
            // Tambahkan kolom last_login jika belum ada
            if (!Schema::hasColumn('users', 'last_login')) {
                $table->datetime('last_login')->nullable()->after('is_active');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Hapus kolom yang ditambahkan sesuai spesifikasi DATABASE_SCHEMA.md
            $columnsToCheck = ['last_login', 'is_active', 'full_name', 'username'];
            foreach ($columnsToCheck as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
