<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengangon extends Model
{
    use HasFactory;

    // Tentukan tabel yang digunakan
    protected $table = 'pengangons';

    // Tentukan kolom yang dapat diisi secara massal
    protected $fillable = [
        'user_id',      // ID User yang mengajukan request
        'nik',          // NIK Pengangon
        'ktp',          // Foto KTP
        'alamat',       // Alamat Pengangon
        'upah',         // Besar Upah
        'selfie_ktp',   // Foto Selfie KTP
    ];

    // Relasi dengan User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
