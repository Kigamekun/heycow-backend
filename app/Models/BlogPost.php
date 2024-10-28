<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlogPost extends Model
{
    // protected $collection = "";
    protected $fillable = [
        'title', 
        'content', 
        'image',
        'category',
        'published', 
        'user_id'
    ];
    use HasFactory;
    protected $content;
    public $timestamps = true;
    public function comments()
    {
        return $this->hasMany(Comment::class, 'post_id');
    }

    // public function reply()
    // {
    //     return $this->hasMany(Reply::class, 'comment_id');
    // }

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}
