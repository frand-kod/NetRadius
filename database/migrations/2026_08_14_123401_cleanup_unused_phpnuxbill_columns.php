<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Buang bloatware warisan PHPNuxBill asli yang tidak dipakai aplikasi.
     *
     * - users            : tabel default Laravel; admin memakai tbl_users.
     * - tbl_plans        : routers/on_login/on_logout hanya jalur Mikrotik (non-Radius),
     *                      pool hanya untuk tipe PPPoE. Aplikasi hanya memakai
     *                      FreeRADIUS REST untuk Hotspot.
     * - tbl_users        : root/photo/data tidak pernah direferensikan di kode.
     */
    public function up(): void
    {
        Schema::dropIfExists('users');

        Schema::table('tbl_plans', function (Blueprint $table) {
            $table->dropColumn(['routers', 'on_login', 'on_logout', 'pool']);
        });

        Schema::table('tbl_users', function (Blueprint $table) {
            $table->dropColumn(['root', 'photo', 'data']);
        });
    }

    /**
     * Tidak bisa dibalik dengan aman (SQLite tidak memulihkan kolom yang dihapus
     * beserta datanya). Pulihkan dengan mengembalikan backup database.
     */
    public function down(): void
    {
        // no-op — restore from database/backup-before-cleanup-*.sqlite
    }
};
