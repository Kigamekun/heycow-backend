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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); 
            $table->decimal('amount', 10, 2); // Menambahkan kolom amount
            $table->string('herder_name'); 
            $table->string('cattle_name'); 
            $table->string('duration'); 
            $table->decimal('cost', 10, 2); 
            $table->string('activity');
            $table->enum('type', ['credit', 'debit']); 
            $table->enum('status', ['pending', 'completed', 'failed']);
            $table->integer('cattle_count')->default(0); // Menambahkan kolom cattle_count
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        
        Schema::dropIfExists('transactions');
    }
};
