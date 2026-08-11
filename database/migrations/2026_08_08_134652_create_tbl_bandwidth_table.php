<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_bandwidth', function (Blueprint $table) {
            $table->id();
            $table->string('name_bw');
            $table->unsignedInteger('rate_down');
            $table->enum('rate_down_unit', ['Kbps', 'Mbps']);
            $table->unsignedInteger('rate_up');
            $table->enum('rate_up_unit', ['Kbps', 'Mbps']);
            $table->string('burst', 128)->default('');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_bandwidth');
    }
};
