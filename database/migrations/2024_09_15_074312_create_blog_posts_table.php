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
            // $table->unsignedBigInteger('cattle_id')->nullable(); // ID ternak yang terkait (opsional)
            $table->foreign('cattle_id')->references('id')->on('cattles')->onDelete('cascade')->onUpdate('cascade'); 
            $table->string('title');
            $table->text('content');
            $table->enum('category', ['forum','jual'])->default('forum'); // Kategori postingan
            $table->string('image')->nullable(); // URL gambar postingan (opsional)
            // $table->enum('published')->default(false); // Status apakah postingan sudah dipublish
            $table->enum('published', ['draft', 'published'])->default('draft'); // Status apakah postingan sudah dipublish
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
