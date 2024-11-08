<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestAngon extends Model
{
    protected $table = 'request_ngangons';

    // Kolom yang dapat diisi
    protected $fillable = [
        'user_id',
        'peternak_id',
        'cattle_id',
        'duration',
        'status',
    ];

    // Relasi ke tabel User (user_id) - Pengguna yang membuat permintaan Ngangon
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }

    // Relasi ke tabel Cattle (cattle_id) - Sapi yang diangkat
    public function cattle()
    {
        return $this->belongsTo('App\Models\Cattle', 'cattle_id');
    }

    // Relasi ke tabel User (peternak_id) - Peternak yang akan mengelola sapi
    public function peternak()
    {
        return $this->belongsTo('App\Models\User', 'peternak_id');
    }
}
