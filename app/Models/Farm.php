<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Farm extends Model
{
<<<<<<< HEAD
=======
    protected $collection = 'farms';
    protected $fillable = ['name', 'address', 'user_id'];

    public function owner(){
        return $this->belongsTo(User::class, 'user_id');
    }
>>>>>>> 991cec93b5dfb4d710afb79557ad503bbc3ddfab
    use HasFactory;

    protected $collection = 'farms';

    protected $fillable = ['name', 'address', 'user_id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

