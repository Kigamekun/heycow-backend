<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoryRecord extends Model
{
    use HasFactory;

    // Nama tabel di database
    protected $table = 'history_records';

    // Nonaktifkan otomatis timestamps
    public $timestamps = false;

    // Kolom yang bisa diisi (mass assignable)
    protected $fillable = [
        'cattle_id',
        'record_type',
        'old_value',
        'new_value',
        'recorded_at',
        'iot_device_id',
        'created_by',
    ];

    // Kolom yang akan diperlakukan sebagai tanggal
    protected $dates = [
        'recorded_at',
    ];

    // Mengonversi old_value dan new_value menjadi array saat diambil dari database
    protected $casts = [
        'old_value' => 'array',
        'new_value' => 'array',
    ];

    /**
     * Relasi dengan model Cattle
     */
    public function cattle()
    {
        return $this->belongsTo(Cattle::class, 'cattle_id');
    }

    /**
     * Relasi dengan model IotDevice
     */
    public function iotDevice()
    {
        return $this->belongsTo(IotDevice::class, 'iot_device_id');
    }

    /**
     * Relasi dengan model User
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
