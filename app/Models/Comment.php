<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'user_id',
        'content',
    ];

    public function reply()
    {
        return $this->hasMany(Reply::class, 'comment_id');
    }
    
    public function blogPost()
    {
        return $this->belongsTo(BlogPost::class, 'post_id');
    }
    public $timestamps = true; // Ini secara default sudah true, tetapi bisa dinyatakan eksplisit
}
