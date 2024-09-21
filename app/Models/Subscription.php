<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $table = 'subscriptions'; // Nama tabel di database

    protected $fillable = [
        'user_id',
        'type',
        'start_date',
        'end_date',
        'status',
        'active',
    ];
}
