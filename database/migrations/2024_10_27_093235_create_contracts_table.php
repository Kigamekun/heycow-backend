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
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('request_id');
            $table->unsignedBigInteger('cattle_id');
            $table->unsignedBigInteger('farm_id');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('rate', 10, 2);
            $table->decimal('initial_weight', 5, 2);
            $table->decimal('initial_height', 5, 2);
            $table->decimal('final_weight', 5, 2)->nullable();
            $table->decimal('final_height', 5, 2)->nullable();
            $table->enum('status', ['pending', 'active', 'completed'])->default('pending');
            $table->decimal('total_cost', 10, 2)->nullable();
            $table->timestamps();

            $table->foreign('request_id')->references('id')->on('request_ngangons')->onDelete('cascade');
            $table->foreign('cattle_id')->references('id')->on('cattle')->onDelete('cascade');
            $table->foreign('farm_id')->references('id')->on('farms')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
