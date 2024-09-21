<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IOTDevices extends Model
{
    protected $table = 'iot_devices';
    protected $fillable = ['serial_number', 'status', 'installation_date','qr_image'];
    use HasFactory;
}
