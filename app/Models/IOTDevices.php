<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IOTDevices extends Model
{
<<<<<<< HEAD
=======
    protected $table = 'iot_devices';
    protected $fillable = ['serial_number', 'status', 'installation_date','qr_image'];
>>>>>>> 991cec93b5dfb4d710afb79557ad503bbc3ddfab
    use HasFactory;

    protected $table = 'iot_devices';

    protected $fillable = ['device_type', 'serial_number', 'status', 'installation_date', 'location'];
}
