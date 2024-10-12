<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBreedsTable extends Migration
{
    public function up()
    {
        Schema::create('breeds', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Nama breed
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('breeds');
    }
}
