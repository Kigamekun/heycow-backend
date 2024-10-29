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
            $table->unsignedBigInteger('breed_id')->after('name');
            $table->foreign('breed_id')->references('id')->on('breeds')->onDelete('cascade')->onUpdate('cascade');
            $table->enum('status', ['sehat', 'sakit', 'mati', 'dijual']);
            $table->enum('gender', ['jantan', 'betina']);
            $table->enum('type', ['pedaging', 'perah', 'peranakan', 'lainnya']);

            $table->date('birth_date');
            $table->integer('birth_weight');
            $table->integer('birth_height');

            $table->unsignedBigInteger('farm_id');
            $table->foreign('farm_id')->references('id')->on('farms')->onDelete('cascade')->onUpdate('cascade');

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');

            // Tambahkan nullable() sebelum foreign key
            $table->unsignedBigInteger('iot_device_id')->nullable();
            $table->foreign('iot_device_id')->references('id')->on('iot_devices')->onDelete('cascade')->onUpdate('cascade')->nullable(); // tidak perlu nullable di sini

            $table->string('image')->nullable();
            $table->date('last_vaccination')->nullable();
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

