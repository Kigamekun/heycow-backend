<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HealthRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'cattle_id',
        'checkup_time',
        'temperature',
        'heart_rate',
        'status',
        'weight',
        'veterinarian',
    ];

    public $timestamps = true;
}
