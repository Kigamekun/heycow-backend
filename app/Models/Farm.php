<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// use MongoDB\Laravel\Eloquent\Model;

class Farm extends Model
{
    protected $collection = 'farms';
    protected $fillable = ['name', 'address', 'user_id'];
    use HasFactory;

    public function owner(){
        return $this->belongsTo(User::class,'user_id');
    }
}

