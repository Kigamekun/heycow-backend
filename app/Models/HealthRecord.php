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

    // Menambahkan properti $casts untuk memastikan kolom tanggal di-cast menjadi objek Carbon
    protected $casts = [
        'checkup_time' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public $timestamps = true;
}
