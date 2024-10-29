<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    //
    protected $fillable = [
        'like',
        'post_id',
        'user_id'
    ];
    public function post()
    {
        return $this->belongsTo(BlogPost::class, 'post_id');
    }
    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
