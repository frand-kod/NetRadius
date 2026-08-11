<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rad_acct', function (Blueprint $table) {
            $table->id();
            $table->string('acctsessionid', 64)->default('');
            $table->string('username', 64)->default('')->index();
            $table->string('realm', 128)->default('');
            $table->string('nasid', 32)->default('');
            $table->string('nasipaddress', 15)->default('')->index();
            $table->string('nasportid', 32)->nullable();
            $table->string('nasporttype', 32)->nullable();
            $table->string('framedipaddress', 15)->default('')->index();
            $table->unsignedBigInteger('acctsessiontime')->default(0);
            $table->unsignedBigInteger('acctinputoctets')->default(0);
            $table->unsignedBigInteger('acctoutputoctets')->default(0);
            $table->string('acctstatustype', 32)->nullable();
            $table->string('macaddr', 50);
            $table->timestamp('dateAdded')->useCurrent();

            $table->index('acctsessionid');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rad_acct');
    }
};
