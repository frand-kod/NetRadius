<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Buang atribut yang tidak berguna untuk sistem RadiusRest + Hotspot.
     * Backup tersedia di database/backup-before-cleanup2-*.sqlite.
     */
    public function up(): void
    {
        Schema::dropIfExists('tbl_routers');

        Schema::table('tbl_customers', function (Blueprint $table) {
            $table->dropColumn(['photo', 'coordinates', 'pppoe_ip', 'created_by']);
        });

        Schema::table('tbl_users', function (Blueprint $table) {
            $table->dropColumn(['city', 'subdistrict', 'ward', 'user_type', 'login_token']);
        });

        Schema::table('tbl_transactions', function (Blueprint $table) {
            $table->dropColumn(['routers', 'note']);
        });

        Schema::table('tbl_user_recharges', function (Blueprint $table) {
            $table->dropColumn(['routers']);
        });

        Schema::table('tbl_voucher', function (Blueprint $table) {
            $table->dropColumn(['routers', 'generated_by']);
        });
    }

    /**
     * Tidak bisa dibalik dengan aman (SQLite). Restore dari backup.
     */
    public function down(): void
    {
        // no-op — restore from database/backup-before-cleanup2-*.sqlite
    }
};
