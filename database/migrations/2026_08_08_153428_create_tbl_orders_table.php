<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tbl_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('tbl_customers');
            $table->foreignId('plan_id')->constrained('tbl_plans');
            $table->string('price', 40);
            $table->enum('status', ['pending', 'paid', 'cancelled'])->default('pending');
            $table->string('invoice_token', 64)->unique();
            $table->unsignedInteger('admin_id')->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->dateTime('paid_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_orders');
    }
};
