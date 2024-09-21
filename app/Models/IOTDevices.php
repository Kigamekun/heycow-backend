<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IOTDevices extends Model
{
    use HasFactory;

    protected $table = 'iot_devices';

    protected $fillable = ['device_type', 'serial_number', 'status', 'installation_date', 'location'];
}
