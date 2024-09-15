<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class IOTDevices extends Model
{
    protected $collection = 'IOTDevices';
    protected $fillable = ['device_type', 'serial_number', 'status', 'installation_date', 'location'];
    use HasFactory;
}
