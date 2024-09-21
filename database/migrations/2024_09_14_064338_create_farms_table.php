<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
<<<<<<< HEAD
    public function up()
{
    Schema::create('farms', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id'); // Foreign key
        $table->string('name');
        $table->string('address');
        $table->string('image')->nullable();
        $table->timestamps();

        // Definisikan foreign key untuk user_id yang mengacu ke id di tabel users
        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
    });
}

public function down()
{
    Schema::dropIfExists('farms');
}
=======
    public function up(): void
    {
        Schema::create('farms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Pemilik peternakan (user yang terdaftar)
            $table->string('name');
            $table->text('address');
            $table->string('contact_number')->nullable();
            $table->boolean('verified')->default(false);
            $table->timestamps();
        });
    }
>>>>>>> 991cec93b5dfb4d710afb79557ad503bbc3ddfab

};
