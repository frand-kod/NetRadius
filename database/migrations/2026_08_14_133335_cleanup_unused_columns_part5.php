<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Hapus field plan yang tidak berguna / redundan:
     * - allow_purchase : tak dipakai logika
     * - plan_expired   : notifikasi expired sudah global (Notification settings)
     * - expired_date   : tak dipakai (reminder pakai H-1/3/7)
     * Backup: database/backup-before-cleanup5-*.sqlite
     */
    public function up(): void
    {
        Schema::table('tbl_plans', function (Blueprint $table) {
            $table->dropColumn(['allow_purchase', 'plan_expired', 'expired_date']);
        });
    }

    public function down(): void
    {
        // no-op — restore from database/backup-before-cleanup5-*.sqlite
    }
};
