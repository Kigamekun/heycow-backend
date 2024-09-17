<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Cattle extends Model
{
    protected $fillable = [
        'name',
        'breed',
        'status',
        'birth_date',
        'birth_weight',
        'farm_id',
        'user_id',
        'iot_device_id',
        'image'
    ];

    public function farm()
    {
        return $this->belongsTo(Farm::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function iotDevice()
    {
        return $this->belongsTo(IoTDevices::class, 'iot_device_id');
    }

    use HasFactory;
}
