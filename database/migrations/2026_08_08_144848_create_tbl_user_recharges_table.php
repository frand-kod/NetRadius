<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_user_recharges', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('customer_id')->default(0);
            $table->string('username', 32)->index();
            $table->foreignId('plan_id')->constrained('tbl_plans');
            $table->string('namebp', 40);
            $table->date('recharged_on');
            $table->time('recharged_time')->default('00:00:00');
            $table->date('expiration');
            $table->time('time');
            $table->string('status', 20);
            $table->string('method', 128)->default('');
            $table->string('routers', 32);
            $table->string('type', 15);
            $table->unsignedInteger('admin_id')->default(1);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_user_recharges');
    }
};
