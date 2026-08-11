<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_logs', function (Blueprint $table) {
            $table->id();
            $table->dateTime('date')->nullable();
            $table->string('type', 50);
            $table->text('description');
            $table->unsignedInteger('userid');
            $table->text('ip');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_logs');
    }
};
