<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cattle extends Model
{
    use HasFactory;

    protected $hidden = [
        'created_at', 'updated_at','breed_id', 'farm_id', 'user_id'
    ];

    protected $fillable = [
        'name', 'breed_id', 'status', 'gender', 'type', 'birth_date',
        'birth_weight', 'birth_height', 'iot_device_id', 'last_vaccination',
        'farm_id', 'user_id'
    ];

    public function breed()
    {
        return $this->belongsTo(Breed::class);
    }

    public function farm()
    {
        return $this->belongsTo(Farm::class);
    }

    public function iotDevice()
    {
        return $this->belongsTo(IOTDevices::class, 'iot_device_id');
    }

    public function healthRecords()
    {
        return $this->hasMany(HealthRecord::class);
    }

    public function latestHealthRecord()
    {
        return $this->hasOne(HealthRecord::class)->latest();
    }

    public function historyRecords()
    {
        return $this->hasMany(HistoryRecord::class);
    }


}
