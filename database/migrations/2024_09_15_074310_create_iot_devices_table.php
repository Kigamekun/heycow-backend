<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('IOTDevices', function (Blueprint $table) {
            $table->id();
            $table->string('device_type');
            $table->string('serial_number')->unique();
            $table->string('status')->default('active');
            $table->date('installation_date');
            $table->string('location')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('i_o_t_devices');
    }
};
