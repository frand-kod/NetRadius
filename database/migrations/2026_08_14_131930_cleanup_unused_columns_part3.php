<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Buang atribut yang tidak berguna untuk sistem RadiusRest + Hotspot.
     * Backup: database/backup-before-cleanup3-*.sqlite
     */
    public function up(): void
    {
        Schema::table('tbl_plans', function (Blueprint $table) {
            $table->dropColumn(['plan_type', 'price_old']);
        });

        Schema::table('tbl_customers', function (Blueprint $table) {
            $table->dropColumn(['account_type', 'balance', 'service_type', 'auto_renewal']);
        });
    }

    public function down(): void
    {
        // no-op — restore from database/backup-before-cleanup3-*.sqlite
    }
};
