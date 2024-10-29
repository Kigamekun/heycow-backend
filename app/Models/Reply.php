<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reply extends Model
{
    //
    protected $fillable = [
        'image',
        'content', 
        'post_id',
        'comment_id', 
        'user_id',
    ];

    // protected $content;
    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function comment()
    {
        return $this->belongsTo(Comment::class, 'comment_id');
    }
}
