<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_message_logs', function (Blueprint $table) {
            $table->id();
            $table->string('message_type', 50)->nullable();
            $table->string('recipient')->nullable();
            $table->text('message_content')->nullable();
            $table->string('status', 50)->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_message_logs');
    }
};
