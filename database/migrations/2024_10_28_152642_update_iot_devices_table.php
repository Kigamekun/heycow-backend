<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateIotDevicesTable extends Migration
{
    public function up()
    {
        Schema::table('iot_devices', function (Blueprint $table) {
         
            $table->string('serial_number')->nullable()->change(); 
            $table->integer('battery_percentage')->default(0)->change(); 
            $table->string('status')->default('inactive')->change(); 
            $table->timestamp('installation_date')->nullable()->change(); 
            $table->string('qr_image')->nullable()->change();
            $table->unsignedBigInteger('user_id')->nullable()->change(); 
            
         
        });
    }

    public function down()
    {
        Schema::table('iot_devices', function (Blueprint $table) {
            
            $table->string('serial_number')->change(); 
            $table->integer('battery_percentage')->change(); 
            $table->string('status')->change(); 
            $table->timestamp('installation_date')->change(); 
            $table->string('qr_image')->change(); 
            $table->unsignedBigInteger('user_id')->change(); 
     
        });
    }
}
