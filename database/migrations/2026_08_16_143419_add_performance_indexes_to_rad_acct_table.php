<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add indexes that back the dashboard/usage queries (and the 5s realtime
     * poll): active-session detection by status, and time-window aggregation.
     */
    public function up(): void
    {
        Schema::table('rad_acct', function (Blueprint $table) {
            $table->index('acctstatustype');
            $table->index('dateAdded');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rad_acct', function (Blueprint $table) {
            $table->dropIndex(['acctstatustype']);
            $table->dropIndex(['dateAdded']);
        });
    }
};
