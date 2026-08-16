<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Hapus is_radius (selalu true, tak dipakai logika) & prepaid (postpaid
     * hanya utk expired_date yg sudah dihapus).
     * Backup: database/backup-before-cleanup6-*.sqlite
     */
    public function up(): void
    {
        Schema::table('tbl_plans', function (Blueprint $table) {
            $table->dropColumn(['is_radius', 'prepaid']);
        });
    }

    public function down(): void
    {
        // no-op — restore from database/backup-before-cleanup6-*.sqlite
    }
};
