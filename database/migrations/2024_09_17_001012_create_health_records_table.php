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
        Schema::create('health_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cattle_id');
            $table->foreign('cattle_id')->references('id')->on('cattle')->onDelete('cascade')->onUpdate('cascade');
            $table->datetime('checkup_time');
            $table->decimal('temperature');
            $table->integer('heart_rate');
            $table->enum('status', ['sick', 'healthy']);
            $table->decimal('weight')->nullable();
            $table->string('veterniarian')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('health_records');
    }
};
