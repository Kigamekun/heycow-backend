<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IOTDevices extends Model
{

    protected $table = 'iot_devices';
    protected $hidden = ['created_at', 'updated_at'];

    protected $fillable = ['serial_number', 'status', 'installation_date', 'qr_image','user_id'];

    // protected $appends = ['qr_image_url'];
    // public function getFulllQrImage()
    // {
    //     return $this->qr_image ? url('api/getFile/' . $this->qr_image) : null;
    // }

    public function user()
    {
        return $this->belongsTo(User::class);
    }


}
