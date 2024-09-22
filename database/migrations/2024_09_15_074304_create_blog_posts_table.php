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
        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Penulis (user yang memposting)
            $table->string('title');
            $table->text('content');
            $table->string('image')->nullable(); // URL gambar postingan (opsional)
            $table->boolean('published')->default(false); // Status apakah postingan sudah dipublish
            $table->unsignedBigInteger('iot_device_id');
            $table->foreign('iot_device_id')->references('id')->on('iot_devices')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamp('published_at')->nullable(); // Waktu publikasi postingan
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blog_posts');
    }
};
