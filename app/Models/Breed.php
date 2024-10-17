<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Breed extends Model
{
    use HasFactory;

    protected $fillable = ['name']; // Pastikan 'name' bisa diisi saat membuat breed

    // Relasi one-to-many dengan model Cattle
    public function cattles()
    {
        return $this->hasMany(Cattle::class);
    }

}