<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class BlogPost extends Model
{
    // protected $collection = "";
    protected $fillable = [
        'title',
        'content',
        'image',
        'category',
        'price',
        'cattle_id',
        'published',
        'published_at',
        'user_id'
    ];
    use HasFactory;
    protected $dates = ['published_at'];

    public function getPublishedAtAttribute($value)
    {
        return Carbon::parse($value)->diffForHumans();
    }

    function formatPrice($price)
    {
        return number_format($price, 0, ',', '.');
    }

    protected $appends = ['full_image_url'];

    public function getFullImageUrlAttribute()
    {
        return $this->image ? url('api/getFile/' . $this->image) : null;
    }


    protected $content;
    public $timestamps = true;
    public function likes()
    {
        return $this->hasMany(Like::class, 'post_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'post_id');
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
