<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sederhanakan customer: hanya username, password, fullname, phonenumber, status.
     * Backup: database/backup-before-cleanup4-*.sqlite
     */
    public function up(): void
    {
        Schema::table('tbl_customers', function (Blueprint $table) {
            $table->dropColumn(['email', 'pppoe_username', 'pppoe_password', 'address', 'city', 'district', 'state', 'zip']);
        });
    }

    public function down(): void
    {
        // no-op — restore from database/backup-before-cleanup4-*.sqlite
    }
};
