<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_voucher', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['Hotspot', 'PPPOE']);
            $table->string('routers', 32);
            $table->foreignId('id_plan')->constrained('tbl_plans');
            $table->string('code', 55)->unique();
            $table->string('user', 45)->default('');
            $table->string('status', 25);
            $table->timestamp('created_at')->useCurrent();
            $table->dateTime('used_date')->nullable();
            $table->unsignedInteger('generated_by')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_voucher');
    }
};
