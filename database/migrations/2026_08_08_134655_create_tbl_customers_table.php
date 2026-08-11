<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_customers', function (Blueprint $table) {
            $table->id();
            $table->string('username', 45)->unique();
            $table->string('password', 255);
            $table->string('photo', 128)->default('/user.default.jpg');
            $table->string('pppoe_username', 32)->default('');
            $table->string('pppoe_password', 45)->default('');
            $table->string('pppoe_ip', 32)->default('');
            $table->string('fullname', 45);
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('district')->nullable();
            $table->string('state')->nullable();
            $table->string('zip', 10)->nullable();
            $table->string('phonenumber', 20)->default('0');
            $table->string('email', 128)->default('');
            $table->string('coordinates', 50)->default('');
            $table->enum('account_type', ['Business', 'Personal'])->default('Personal');
            $table->decimal('balance', 15, 2)->default(0);
            $table->enum('service_type', ['Hotspot', 'PPPoE', 'VPN', 'Others'])->default('Others');
            $table->boolean('auto_renewal')->default(true);
            $table->enum('status', ['Active', 'Banned', 'Disabled', 'Inactive', 'Limited', 'Suspended'])->default('Active');
            $table->unsignedInteger('created_by')->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->dateTime('last_login')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_customers');
    }
};
