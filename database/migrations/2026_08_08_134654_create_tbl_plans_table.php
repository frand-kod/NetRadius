<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name_plan', 40);
            $table->foreignId('id_bw')->constrained('tbl_bandwidth');
            $table->string('price', 40);
            $table->string('price_old', 40)->default('');
            $table->enum('type', ['Hotspot', 'PPPOE', 'VPN', 'Balance']);
            $table->enum('typebp', ['Unlimited', 'Limited'])->nullable();
            $table->enum('limit_type', ['Time_Limit', 'Data_Limit', 'Both_Limit'])->nullable();
            $table->unsignedInteger('time_limit')->nullable();
            $table->enum('time_unit', ['Mins', 'Hrs'])->nullable();
            $table->unsignedInteger('data_limit')->nullable();
            $table->enum('data_unit', ['MB', 'GB'])->nullable();
            $table->integer('validity');
            $table->enum('validity_unit', ['Mins', 'Hrs', 'Days', 'Months', 'Period']);
            $table->integer('shared_users')->nullable();
            $table->string('routers', 32);
            $table->boolean('is_radius')->default(false);
            $table->string('pool', 40)->nullable();
            $table->integer('plan_expired')->default(0);
            $table->tinyInteger('expired_date')->default(20);
            $table->boolean('enabled')->default(true);
            $table->enum('allow_purchase', ['yes', 'no'])->default('yes');
            $table->enum('prepaid', ['yes', 'no'])->default('yes');
            $table->enum('plan_type', ['Business', 'Personal'])->default('Personal');
            $table->string('device', 32)->default('');
            $table->text('on_login')->nullable();
            $table->text('on_logout')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_plans');
    }
};
