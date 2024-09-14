<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Farm extends Model
{
    protected $collection = 'farms';
    protected $fillable = ['name', 'location', 'owner_id'];
    use HasFactory;
}
