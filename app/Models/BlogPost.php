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
        'cattle_id',
        'published',
        'user_id'
    ];
    use HasFactory;

    protected $appends = ['full_image_url'];

    public function getFullImageUrlAttribute()
    {
        return $this->image ? url('api/getFile/' . $this->image) : null;
    }

    protected $content;
    public $timestamps = true;
    public function comments()
    {
        return $this->hasMany(Comment::class, 'post_id');
    }

    public function likes()
    {
        return $this->hasMany(Like::class, 'post_id');
    }

    public function cattle()
    {
        return $this->belongsTo(Cattle::class, 'cattle_id');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function user()
{
    return $this->belongsTo(User::class);
}


}
