<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('iot_devices', function (Blueprint $table) {
            $table->id();
            $table->string('serial_number')->unique();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->date('installation_date');
            $table->text('qr_image')->nullable();
            $table->unsignedBigInteger('user_id')->nullable(); // Tambahkan kolom user_id
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('iot_devices');
    }
};
