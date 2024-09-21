<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlogPost extends Model
{
    // protected $collection = "";
    protected $fillable = ['title', 'content', 'image', 'published', 'user_id'];
    
    use HasFactory;
<<<<<<< HEAD

    protected $fillable = [
        'user_id',
        'content',
    ];

    public $timestamps = true; 
=======
    
    
    
   
>>>>>>> 991cec93b5dfb4d710afb79557ad503bbc3ddfab
}
