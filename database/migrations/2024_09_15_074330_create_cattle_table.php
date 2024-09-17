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
        Schema::create('cattle', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('breed');
            $table->enum('status', ['alive', 'dead', 'sold']);

            $table->date('birth_date');
            $table->integer('birth_weight');

            $table->unsignedBigInteger('farm_id');
            $table->foreign('farm_id')->references('id')->on('farms')->onDelete('cascade')->onUpdate('cascade');

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');

            $table->unsignedBigInteger('iot_device_id');
            $table->foreign('iot_device_id')->references('id')->on('IOTDevices')->onDelete('cascade')->onUpdate('cascade');

            $table->string('image')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cattle');
    }
};
