<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cattle extends Model
{
    protected $fillable = [
        'name',
        'breed',
        'status',
        'gender',
        'birth_date',
        'birth_weight',
        'birth_height',
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
        return $this->belongsTo(IOTDevices::class, 'iot_device_id');
    }

    use HasFactory;
}
