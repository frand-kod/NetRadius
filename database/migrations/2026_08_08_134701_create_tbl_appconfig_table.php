<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_appconfig', function (Blueprint $table) {
            $table->id();
            $table->text('setting');
            $table->text('value')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_appconfig');
    }
};
